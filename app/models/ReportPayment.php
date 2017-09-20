<?php
class ReportPayment extends \Eloquent{

    public static $database_columns = array(
        'year' => 'YEAR(PaymentDate)',
        'quarter_of_year' => 'QUARTER(PaymentDate)',
        'month' => 'MONTH(PaymentDate)',
        'week_of_year' => 'WEEK(PaymentDate)',
        'date' => 'DATE(PaymentDate)',
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
            $select_columns[] = DB::Raw("SUM(" . $colname . ") as " . $colname);
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
        $query_common = Payment::where(['tblPayment.CompanyID' => $CompanyID,'tblPayment.Status'=>'Approved','Recall'=>'0']);

        $RMDB = Config::get('database.connections.sqlsrv.database');
        if(report_join($data)){
            $query_common->join($RMDB.'.tblAccount', 'tblPayment.AccountID', '=', 'tblAccount.AccountID');
            self::$AccountJoin = true;
        }

        foreach ($filters as $key => $filter) {
            if (!empty($filter[$key]) && is_array($filter[$key])) {
                if(isset(self::$database_columns[$key])) {
                    $query_common->whereRaw(self::$database_columns[$key].' in ('.implode(',',$filter[$key]).')');
                }else{
                    $query_common->whereIn($key, $filter[$key]);
                }
            } else if (!empty($filter['wildcard_match_val']) && in_array($key, array('PaymentType', 'PaymentMethod'))) {
                $query_common->where($key, 'like', str_replace('*', '%', $filter['wildcard_match_val']));
            } else if (!empty($filter['wildcard_match_val']) && !in_array($key, array('year', 'quarter_of_year','month','week_of_year')) ) {
                $data_in_array = Report::getDataInArray($CompanyID, $key, $filter['wildcard_match_val']);
                if (!empty($data_in_array)) {
                    $query_common->whereIn($key, $data_in_array);
                }
            } else if ($key == 'date') {
                if (!empty($filter['start_date'])) {
                    $query_common->where('PaymentDate', '>=', str_replace('*', '%', $filter['start_date']));
                }
                if (!empty($filter['end_date'])) {
                    $query_common->where('PaymentDate', '<=', str_replace('*', '%', $filter['end_date']));
                }
            }else if (!empty($filter['wildcard_match_val']) && in_array($key, array('year', 'quarter_of_year','month','week_of_year'))) {
                $query_common->whereRaw(self::$database_columns[$key].' like "'.str_replace('*', '%', $filter['wildcard_match_val']).'"');
            }
        }
        return $query_common;
    }

}