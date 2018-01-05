<?php
class ReportAccount extends \Eloquent{

    public static $database_columns = array(
        'NetUnbilledAmount' => 'COALESCE(tblAccountBalance.UnbilledAmount,0) - COALESCE(tblAccountBalance.VendorUnbilledAmount  )',
        'AvailableCreditLimit' => 'IF(COALESCE(tblAccountBalance.PermanentCredit,0) - COALESCE(tblAccountBalance.BalanceAmount,0)<0,0,COALESCE(tblAccountBalance.PermanentCredit,0) - COALESCE(tblAccountBalance.BalanceAmount,0))',
    );
    public static $AccountJoin = false;

    public static function generateQuery($CompanyID, $data, $filters){
        $select_columns = array();

        if (count($data['row'])) {
            $query_distinct = self::commonQuery($CompanyID, $data, $filters);
            foreach ($data['row'] as $column) {
                if(isset(self::$database_columns[$column])){
                    $query_distinct->orderby($column);
                    $select_columns[] = DB::raw(self::$database_columns[$column].' as '.$column) ;
                }else{
                    $columnname = report_col_name($column);
                    $query_distinct->orderby($columnname);
                    $select_columns[] = $columnname;
                }
            }
            $query_distinct = $query_distinct->distinct();
            $columns = $query_distinct->get($select_columns);
            $columns = json_decode(json_encode($columns), true);

            //$response['column'] = self::generateColumnNames($columns);
            $response['distinct_row'] = $columns;
            $response['distinct_row'] = array_map('custom_implode', $response['distinct_row']);
        }

        $final_query = self::commonQuery($CompanyID, $data, $filters);
        foreach ($data['column'] as $column) {
            if(isset(self::$database_columns[$column])){
                $final_query->groupby($column);
                $select_columns[] = DB::raw(self::$database_columns[$column].' as '.$column) ;
            }else{
                $columnname = report_col_name($column);
                $final_query->groupby($columnname);
                $select_columns[] = $columnname;
            }
        }

        foreach ($data['row'] as $column) {
            if(isset(self::$database_columns[$column])){
                $final_query->groupby($column);
            }else{
                $columnname = report_col_name($column);
                $final_query->groupby($columnname);
            }
        }

        $data['row'] = array_merge($data['row'], $data['column']);
        foreach ($data['sum'] as $colname) {
            if($colname == 'AccountID'){
                $select_columns[] = DB::Raw("COUNT(tblAccount.AccountID) as " . $colname);
            }else{
                $select_columns[] = DB::Raw("SUM(" . $colname . ") as " . $colname);
            }
        }
        /*if(!empty($select_columns)){
            $data['row'][] = DB::Raw($select_columns);
        }*/
        //print_r($data['row']);exit;
        if (!empty($data['row'])) {
            $response['data'] = $final_query->get($select_columns);
            $response['data'] = json_decode(json_encode($response['data']), true);
        } else {
            $response['data'] = array();
        }


        return $response;
    }

    public static function commonQuery($CompanyID, $data, $filters){
        $query_common = Account::where(['tblAccount.CompanyId' => $CompanyID,'tblAccount.Status'=>'1','AccountType'=> '1']);

        if(array_intersect($data['column'],array_keys(Report::$dimension['account']['Account'])) || array_intersect_key($data['row'],array_keys(Report::$dimension['account']['Account'])) || array_intersect_key($data['filter'],array_keys(Report::$dimension['account']['Account'])) ){
            $query_common->leftJoin('tblAccountBalance', 'tblAccountBalance.AccountID', '=', 'tblAccount.AccountID');
            self::$AccountJoin = true;
        }
        foreach ($filters as $key => $filter) {
            if (!empty($filter[$key]) && is_array($filter[$key])) {
                if(isset(self::$database_columns[$key])) {
                    $query_common->whereRaw(self::$database_columns[$key].' in ('.implode(',',$filter[$key]).')');
                }else{
                    $query_common->whereIn($key, $filter[$key]);
                }
            }
        }
        return $query_common;
    }

}