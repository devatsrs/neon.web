<?php
class ReportCustomerCDR extends \Eloquent{

    public static $database_columns = array(
        'AccountID' => 'tblHeader.AccountID',
        'DestinationBreak' => 'tblRate.Description',
    );
    public static  $AccountJoin = false;
    public static  $CodeJoin = false;
    public static  $DetailTable = 'tblUsageSummaryDay';

    public static function generateSummaryQuery($CompanyID, $data, $filters){

        if (count($data['row'])) {
            $query_distinct = self::commonCDRQuery($CompanyID, $data, $filters);
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

        $final_query = self::commonCDRQuery($CompanyID, $data, $filters);
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
            if($colname == 'Margin'){
                $select_columns[] = DB::Raw("COALESCE(SUM(".self::$DetailTable.".TotalCharges),0) - COALESCE(SUM(".self::$DetailTable.".TotalCost),0) as " . $colname);
            }else if($colname == 'MarginPercentage'){
                $select_columns[] = DB::Raw("(COALESCE(SUM(".self::$DetailTable.".TotalCharges),0) - COALESCE(SUM(".self::$DetailTable.".TotalCost),0)) / SUM(".self::$DetailTable.".TotalCharges)*100 as " . $colname);
            }else if($colname == 'ACD'){
                $select_columns[] = DB::Raw("IF(SUM(".self::$DetailTable.".NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(".self::$DetailTable.".TotalBilledDuration),0)/SUM(".self::$DetailTable.".NoOfCalls)),0) as " . $colname);
            }else if($colname == 'ASR'){
                $select_columns[] = DB::Raw("SUM(".self::$DetailTable.".NoOfCalls)/(SUM(".self::$DetailTable.".NoOfCalls)+SUM(".self::$DetailTable.".NoOfFailCalls))*100 as " . $colname);
            }else if($colname == 'BilledDuration'){
                $select_columns[] = DB::Raw("ROUND(COALESCE(SUM(".self::$DetailTable.".TotalBilledDuration),0)/ 60,0) as " . $colname);
            }else{
                $select_columns[] = DB::Raw("SUM(".self::$DetailTable."." . $colname . ") as " . $colname);
            }
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
            ->table('tblHeader')
            ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeader.DateID')
            ->where(['tblHeader.CompanyID' => $CompanyID]);

        if(in_array('hour',$data['column']) || in_array('hour',$data['row']) || in_array('minute',$data['column']) || in_array('minute',$data['row'])) {

            if($data['Live'] == 'true'){
                $query_common->join('tblUsageSummaryHourLive', 'tblHeader.HeaderID', '=', 'tblUsageSummaryHourLive.HeaderID');
                $query_common->join('tblDimTime', 'tblUsageSummaryHourLive.TimeID', '=', 'tblDimTime.TimeID');
                self::$DetailTable = 'tblUsageSummaryHourLive';
            }else{
                $query_common->join('tblUsageSummaryHour', 'tblHeader.HeaderID', '=', 'tblUsageSummaryHour.HeaderID');
                $query_common->join('tblDimTime', 'tblUsageSummaryHour.TimeID', '=', 'tblDimTime.TimeID');
                self::$DetailTable = 'tblUsageSummaryHour';
            }
        }else{
            $query_common->join('tblUsageSummaryDay', 'tblHeader.HeaderID', '=', 'tblUsageSummaryDay.HeaderID');
        }

        $RMDB = Config::get('database.connections.sqlsrv.database');
        if(report_join($data)){
            $query_common->join($RMDB.'.tblAccount', 'tblHeader.AccountID', '=', 'tblAccount.AccountID');
            self::$AccountJoin = true;
        }
        if(in_array('DestinationBreak',$data['column']) || in_array('DestinationBreak',$data['row'])){
            $DefaultCodedeck = BaseCodeDeck::where(["CompanyID"=>$CompanyID,"DefaultCodedeck"=>1])->pluck("CodeDeckId");
            $query_common->join($RMDB.'.tblRate', 'tblRate.Code', '=', self::$DetailTable.'.AreaPrefix');
            $query_common->where('CodeDeckId', intval($DefaultCodedeck));
            self::$CodeJoin = true;
        }

        foreach ($filters as $key => $filter) {
            if (!empty($filter[$key]) && is_array($filter[$key]) && !in_array($key, array('GatewayAccountPKID', 'GatewayVAccountPKID'))) {
                if(isset(self::$database_columns[$key])) {
                    $query_common->whereRaw(self::$database_columns[$key].' in ("'.implode('","',$filter[$key]).'")');
                }else{
                    $query_common->whereIn($key, $filter[$key]);
                }
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