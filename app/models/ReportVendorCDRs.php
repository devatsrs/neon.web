<?php
class ReportVendorCDRs extends \Eloquent{

    public static $database_columns = array(
        'AccountID' => 'tblVendorCDRHeader.AccountID',
        'DestinationBreak' => 'tblRate.Description',
        'year' => 'YEAR(StartDate)',
        'quarter_of_year' => 'QUARTER(StartDate)',
        'month_of_year' => 'MONTH(StartDate)',
        'week_of_year' => 'WEEK(StartDate)',
        'date' => 'DATE(StartDate)',
        'hour' => 'HOUR(connect_time)',
        'minute' => 'MINUTE(connect_time)',
    );
    public static  $AccountJoin = false;
    public static  $CodeJoin = false;
    public static  $DetailTable = 'tblVendorCDR';

    public static function generateSummaryQuery($CompanyID, $data, $filters){

        if (count($data['row'])) {
            $query_distinct = self::commonCDRQuery($CompanyID, $data, $filters,false);
            foreach ($data['row'] as $column) {
                if(isset(self::$database_columns[$column])){
                    $query_distinct->orderby($column);
                    $select_columns[] = DB::raw(self::$database_columns[$column].' as '.$column) ;
                }else {
                    $columnname = report_col_name($column);
                    $query_distinct->orderby($columnname);
                    $select_columns[] = $columnname;
                }
            }
            $query_distinct = $query_distinct->distinct();
            $columns = $query_distinct->get($select_columns);
            $columns = json_decode(json_encode($columns), true);

            $response['distinct_row'] = $columns;
            $response['distinct_row'] = array_map('custom_implode', $response['distinct_row']);
        }
        $final_query = self::commonCDRQuery($CompanyID, $data, $filters,false);
        foreach ($data['column'] as $column) {
            if(isset(self::$database_columns[$column])){
                $final_query->groupby($column);
                $select_columns[] = DB::raw(self::$database_columns[$column].' as '.$column) ;
            }else {
                $columnname = report_col_name($column);
                $final_query->groupby($columnname);
                $select_columns[] = $columnname;
            }
        }
        foreach ($data['row'] as $column) {
            if(isset(self::$database_columns[$column])){
                $final_query->groupby($column);
            }else {
                $columnname = report_col_name($column);
                $final_query->groupby($columnname);
            }
        }

        //$data['row'] = array_merge($data['row'], $data['column']);
        foreach ($data['sum'] as $colname) {
            if($colname == 'VendorCDRID'){
                $select_columns[] = DB::Raw("COUNT(".self::$DetailTable.".VendorCDRID) as " . $colname);
            }else if($colname == 'duration2'){
                $select_columns[] = DB::Raw("ROUND(COALESCE(SUM(".self::$DetailTable.".duration),0)/ 60,0) as " . $colname);
            }else if($colname == 'duration1'){
                $select_columns[] = DB::Raw("ROUND(COALESCE(SUM(".self::$DetailTable.".billed_duration),0)/ 60,0) as " . $colname);
            }else{
                $select_columns[] = DB::Raw("SUM(".self::$DetailTable."." . $colname . ") as " . $colname);
            }
        }

        if (!empty($select_columns)) {
            $response['data'] = $final_query->get($select_columns);
            $response['data'] = json_decode(json_encode($response['data']), true);
        } else {
            $response['data'] = array();
        }


        return $response;
    }

    public static function commonCDRQuery($CompanyID, $data, $filters,$Live){
        $query_common = DB::connection('sqlsrvcdr')
            ->table('tblVendorCDRHeader')
            ->join('tblVendorCDR', 'tblVendorCDR.VendorCDRHeaderID', '=', 'tblVendorCDRHeader.VendorCDRHeaderID')
            ->where(['tblVendorCDRHeader.CompanyID' => $CompanyID]);

        $RMDB = Config::get('database.connections.sqlsrv.database');
        if(report_join($data)){
            $query_common->join($RMDB.'.tblAccount', 'tblVendorCDRHeader.AccountID', '=', 'tblAccount.AccountID');
            self::$AccountJoin = true;
        }
        if(in_array('DestinationBreak',$data['column']) || in_array('DestinationBreak',$data['row']) || in_array('DestinationBreak',$data['filter']) || in_array('CountryID',$data['column']) || in_array('CountryID',$data['row']) || in_array('CountryID',$data['filter'])){
            $DefaultCodedeck = BaseCodeDeck::where(["CompanyID"=>$CompanyID,"DefaultCodedeck"=>1])->pluck("CodeDeckId");
            $query_common->join($RMDB.'.tblRate', 'tblRate.Code', '=', self::$DetailTable.'.area_prefix');
            $query_common->where('CodeDeckId', intval($DefaultCodedeck));
            self::$CodeJoin = true;
        }

        foreach ($filters as $key => $filter) {
            if (!empty($filter[$key]) && is_array($filter[$key]) && !in_array($key, array('GatewayAccountPKID'))) {
                if(isset(self::$database_columns[$key])) {
                    $query_common->whereRaw(self::$database_columns[$key].' in ("'.implode('","',$filter[$key]).'")');
                }else{
                    $query_common->whereIn($key, $filter[$key]);
                }
            } else if (!empty($filter[$key]) && is_array($filter[$key]) && in_array($key, array('GatewayAccountPKID'))) {
                $data_in_array = GatewayAccount::where(array('CompanyID' => $CompanyID))
                    ->where(function ($where) use ($filter, $key) {
                        $where->where('AccountIP', 'like', $filter[$key]);
                        $where->orwhere('AccountCLI', 'like', $filter[$key]);
                    })
                    ->lists('GatewayAccountPKID');
                if (!empty($data_in_array)) {
                    $query_common->whereIn($key, $data_in_array);
                }
            } else if (!empty($filter['wildcard_match_val']) && in_array($key, array('trunk', 'area_prefix','year','quarter_of_year','month_of_year','week_of_year','hour','minute'))) {
                if(isset(self::$database_columns[$key])) {
                    $query_common->whereRaw(self::$database_columns[$key]. ' like "'. str_replace('*', '%', $filter['wildcard_match_val']).'"');
                }else{
                    $query_common->where($key, 'like', str_replace('*', '%', $filter['wildcard_match_val']));
                }
            } else if (!empty($filter['wildcard_match_val'])) {
                $data_in_array = Report::getDataInArray($CompanyID, $key, $filter['wildcard_match_val']);
                if (!empty($data_in_array)) {
                    $query_common->whereIn($key, $data_in_array);
                }
            } else if ($key == 'date') {
                if (!empty($filter['start_date'])) {
                    $query_common->where('StartDate', '>=', str_replace('*', '%', $filter['start_date']));
                }
                if (!empty($filter['end_date'])) {
                    $query_common->where('StartDate', '<=', str_replace('*', '%', $filter['end_date']));
                }
            }
        }
        return $query_common;
    }

}