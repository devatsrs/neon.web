<?php
class ReportVendorCDR extends \Eloquent{

    public static $database_columns = array(
        'AccountID' => 'tblVendorSummaryDay.AccountID',
        'DestinationBreak' => 'tblRate.Description',
    );
    public static $AccountJoin = false;
    public static  $CodeJoin = false;
    public static  $DetailTable = 'tblVendorSummaryDay';

    public static function generateSummaryQuery($CompanyID, $data, $filters){
        $setting_ag = json_decode($data['setting_ag'],true);
        $setting_af_re = check_apply_limit($setting_ag);
        $measure_filter = count(array_intersect($data['filter'],array_keys(Report::$measures[$data['Cube']])));
        $orders_columns = array();
        if (count($data['row'])) {
            $query_distinct = self::commonCDRQuery($CompanyID, $data, $filters,false);
            if(substr($filters['date']['start_date'],0,10) == date('Y-m-d') || substr($filters['date']['end_date'],0,10) == date('Y-m-d')){
                $query_distinct2 = self::commonCDRQuery($CompanyID, $data, $filters,true);
                foreach ($data['row'] as $column) {
                    if(isset(self::$database_columns[$column])){
                        $select_columns2[] = DB::raw(self::$database_columns[$column].' as '.$column) ;
                    }else {
                        $columnname = report_col_name($column);
                        $select_columns2[] = $columnname;
                    }
                }
                $query_distinct2 = $query_distinct2->distinct();
                $query_distinct2->select($select_columns2);
                $query_distinct->union($query_distinct2);
            }
            foreach ($data['row'] as $column) {
                if(isset(self::$database_columns[$column])){
                    $columnname = $column;
                    $select_columns[] = DB::raw(self::$database_columns[$column].' as '.$column) ;
                }else {
                    $columnname = report_col_name($column);
                    $select_columns[] = $columnname;
                }
                $query_distinct->orderby($columnname);
                if($measure_filter){
                    $query_distinct->groupby($columnname);
                }
            }
            $query_distinct = $query_distinct->distinct();
            $columns = $query_distinct->get($select_columns);
            $columns = json_decode(json_encode($columns), true);

            //$response['column'] = self::generateColumnNames($columns);
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


        foreach ($data['sum'] as $colname) {
            $measure_name  = get_measure_name($colname,self::$DetailTable);
            if(!empty($measure_name)) {
                $select_columns[] = DB::Raw($measure_name." as " . $colname);
            } else {
                $select_columns[] = DB::Raw(get_col_full_name($setting_ag,self::$DetailTable,$colname));
            }
            $orders_columns[]  = $colname;
        }
        if(substr($filters['date']['start_date'],0,10) == date('Y-m-d') || substr($filters['date']['end_date'],0,10) == date('Y-m-d')){
            $final_query2 = self::commonCDRQuery($CompanyID, $data, $filters,true);
            foreach ($data['column'] as $column) {
                if(isset(self::$database_columns[$column])){
                    $final_query2->groupby($column);
                    $select_columns2[] = DB::raw(self::$database_columns[$column].' as '.$column) ;
                }else {
                    $columnname = report_col_name($column);
                    $final_query2->groupby($columnname);
                    $select_columns2[] = $columnname;
                }
            }
            foreach ($data['row'] as $column) {
                if(isset(self::$database_columns[$column])){
                    $final_query2->groupby($column);
                }else {
                    $columnname = report_col_name($column);
                    $final_query2->groupby($columnname);
                }
            }

            foreach ($data['sum'] as $colname) {
                $measure_name  = get_measure_name($colname,self::$DetailTable);
                if(!empty($measure_name)) {
                    $select_columns2[] = DB::Raw($measure_name." as " . $colname);
                } else {
                    $select_columns2[] = DB::Raw(get_col_full_name($setting_ag,self::$DetailTable,$colname));
                }
            }
            $final_query2->select($select_columns2);
            $final_query->union($final_query2);

        }
        if($setting_af_re['applylimit']) {
            foreach($orders_columns as $order_column) {
                $final_query->orderby(DB::raw($order_column), $setting_af_re['order']);
            }
            $final_query->limit($setting_af_re['limit']);
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
        $query_common = DB::connection('neon_report')
            ->table('tblHeaderV')
            ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeaderV.DateID')
            ->where(['tblHeaderV.CompanyID' => $CompanyID]);

        if(in_array('hour',$data['column']) || in_array('hour',$data['row']) || in_array('hour',$data['filter'])) {
            if($Live){
                $query_common->join('tblVendorSummaryHourLive', 'tblHeaderV.HeaderVID', '=', 'tblVendorSummaryHourLive.HeaderVID');
                $query_common->join('tblDimTime', 'tblVendorSummaryHourLive.TimeID', '=', 'tblDimTime.TimeID');
                self::$DetailTable = 'tblVendorSummaryHourLive';
            }else{
                $query_common->join('tblVendorSummaryHour', 'tblHeaderV.HeaderVID', '=', 'tblVendorSummaryHour.HeaderVID');
                $query_common->join('tblDimTime', 'tblVendorSummaryHour.TimeID', '=', 'tblDimTime.TimeID');
                self::$DetailTable = 'tblVendorSummaryHour';
            }
        }else{
            if($Live){
				self::$DetailTable = 'tblVendorSummaryDayLive';
                $query_common->join('tblVendorSummaryDayLive', 'tblHeaderV.HeaderVID', '=', 'tblVendorSummaryDayLive.HeaderVID');
            }else{
				self::$DetailTable = 'tblVendorSummaryDay';
                $query_common->join('tblVendorSummaryDay', 'tblHeaderV.HeaderVID', '=', 'tblVendorSummaryDay.HeaderVID');
            }
        }
        $RMDB = Config::get('database.connections.sqlsrv.database');
        if(report_join($data)){
            $query_common->join($RMDB.'.tblAccount', 'tblHeaderV.AccountID', '=', 'tblAccount.AccountID');
            self::$AccountJoin = true;
        }
        if(in_array('DestinationBreak',$data['column']) || in_array('DestinationBreak',$data['row']) || in_array('DestinationBreak',$data['filter'])){
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
            } else if (in_array($key,array_keys(Report::$measures[$data['Cube']]))) {
                $measure_name  = $measure_name2 = get_measure_name($key,self::$DetailTable);
                if($filter['number_agg'] ==  'count_distinct' ){
                    $aggregator2 = 'distinct';
                    $aggregator = 'count';
                }else{
                    $aggregator = $filter['number_agg'];
                    $aggregator2 = '';
                }
                if(empty($measure_name)) {
                    $measure_name =  $aggregator."(".$aggregator2." ".self::$DetailTable.".". $key . ") ";;
                }
                switch ($filter['number_sign']) {
                    case 'null':
                        $whereRaw_measure = $measure_name.' IS NULL';
                        break;
                    case 'not_null':
                        $whereRaw_measure = $measure_name.' IS NOT NULL';
                        break;
                    case 'range':
                        $whereRaw_measure  = $measure_name ." Between ". (double)$filter['number_agg_range_min']." AND ".(double)$filter['number_agg_range_max'];
                        break;
                    default :
                        $whereRaw_measure = $measure_name." ". $filter['number_sign'] ." ". str_replace('*', '%', $filter['number_agg_val']);
                        break;
                }

                if(empty($filter['number_agg']) && empty($measure_name2)){
                    $query_common->whereRaw($whereRaw_measure);
                }else{
                    $query_common->havingRaw($whereRaw_measure);
                }
            }
        }
        return $query_common;
    }

}