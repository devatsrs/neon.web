<?php
class ReportVendorCDR extends \Eloquent{


    public static $AccountJoin = false;

    public static function generateSummaryQuery($CompanyID, $data, $filters){

        if (count($data['row'])) {
            $query_distinct = self::commonCDRQuery($CompanyID, $data, $filters);
            foreach ($data['row'] as $column) {
                $columnname = report_col_name($column);
                $query_distinct->orderby($columnname);
                $select_columns[] = $columnname;
            }
            $query_distinct = $query_distinct->distinct();
            $columns = $query_distinct->get($select_columns);
            $columns = json_decode(json_encode($columns), true);

            //$response['column'] = self::generateColumnNames($columns);
            $response['distinct_row'] = $columns;
            $response['distinct_row'] = array_map('custom_implode', $response['distinct_row']);
        }

        $final_query = self::commonCDRQuery($CompanyID, $data, $filters);
        foreach ($data['column'] as $column) {
            $columnname = report_col_name($column);
            $final_query->groupby($columnname);
            $select_columns[] = $columnname;
        }
        foreach ($data['row'] as $column) {
            $columnname = report_col_name($column);
            $final_query->groupby($columnname);
        }


        foreach ($data['sum'] as $colname) {
            $select_columns[] = DB::Raw("SUM(tblVendorSummaryDay." . $colname . ") as " . $colname);
        }
        /*if(!empty($select_columns)){
            $data['row'][] = DB::Raw($select_columns);
        }*/
        //print_r($data['row']);exit;
        if (!empty($select_columns)) {
            $response['data'] = $final_query->get($select_columns);
            $response['data'] = json_decode(json_encode($response['data']), true);
        } else {
            $response['data'] = array();
        }


        return $response;
    }

    public static function commonCDRQuery($CompanyID, $data, $filters){
        $query_common = DB::connection('neon_report')
            ->table('tblHeaderV')
            ->join('tblVendorSummaryDay', 'tblHeaderV.HeaderVID', '=', 'tblVendorSummaryDay.HeaderVID')
            ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeaderV.DateID')
            ->where(['tblHeaderV.CompanyID' => $CompanyID]);

        $RMDB = Config::get('database.connections.sqlsrv.database');
        if(report_join($data)){
            $query_common->join($RMDB.'.tblAccount', 'tblHeaderV.AccountID', '=', 'tblAccount.AccountID');
            self::$AccountJoin = true;
        }
        foreach ($filters as $key => $filter) {
            if (!empty($filter[$key]) && is_array($filter[$key]) && !in_array($key, array('GatewayAccountPKID', 'GatewayVAccountPKID'))) {
                $query_common->whereIn($key, $filter[$key]);
            } else if (!empty($filter[$key]) && is_array($filter[$key]) && in_array($key, array('GatewayAccountPKID', 'GatewayVAccountPKID'))) {
                $data_in_array = GatewayAccount::where(array('CompanyID' => $CompanyID))
                    ->where(function ($where) use ($filter, $key) {
                        $where->where('AccountIP', 'like', $filter[$key]);
                        $where->orwhere('AccountCLI', 'like', $filter[$key]);
                    })
                    ->lists('GatewayAccountPKID');
                if (!empty($data_in_array)) {
                    $query_common->whereIn($key, $data_in_array);
                }
            } else if (!empty($filter['wildcard_match_val']) && in_array($key, array('Trunk', 'AreaPrefix','year','quarter_of_year','month_of_year','week_of_year'))) {
                $query_common->where($key, 'like', str_replace('*', '%', $filter['wildcard_match_val']));
            } else if (!empty($filter['wildcard_match_val'])) {
                $data_in_array = Report::getDataInArray($CompanyID, $key, $filter['wildcard_match_val']);
                if (!empty($data_in_array)) {
                    $query_common->whereIn($key, $data_in_array);
                }
            } else if ($key == 'date') {
                if (!empty($filter['start_date'])) {
                    $query_common->where('date', '>=', str_replace('*', '%', $filter['start_date']));
                }
                if (!empty($filter['end_date'])) {
                    $query_common->where('date', '<=', str_replace('*', '%', $filter['end_date']));
                }
            }
        }
        return $query_common;
    }

}