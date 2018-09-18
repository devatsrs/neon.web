<?php
class ReportPayment extends \Eloquent{

    public static $database_columns = array(
        'year' => 'YEAR(PaymentDate)',
        'quarter_of_year' => 'QUARTER(PaymentDate)',
        'month' => 'MONTH(PaymentDate)',
        'week_of_year' => 'WEEK(PaymentDate)',
        'date' => 'DATE(PaymentDate)',
        'PaymentMethod' => 'tblPayment.PaymentMethod',
        'AccountID' => 'tblPayment.AccountID',
        'CurrencyID' => 'tblPayment.CurrencyID',
        'BillingType' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.BillingType, "")',
        'BillingClassID' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.BillingClassID, "")',
        'BillingStartDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.BillingStartDate, "")',
        'BillingCycleType' => "IF(tblAccountNextBilling.BillingCycleType!='', tblAccountNextBilling.BillingCycleType, IF(tblAccountBilling.BillingCycleType!='', tblAccountBilling.BillingCycleType, ''))",
        'BillingCycleValue' => "IF(tblAccountNextBilling.BillingCycleValue!='', tblAccountNextBilling.BillingCycleValue, IF(tblAccountBilling.BillingCycleValue!='', tblAccountBilling.BillingCycleValue, ''))",
        'LastInvoiceDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.LastInvoiceDate, "")',
        'NextInvoiceDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.NextInvoiceDate, "")',
        'LastChargeDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.LastChargeDate, "")',
        'NextChargeDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.NextChargeDate, "")',
    );
    public static $AccountJoin = false;
    public static  $AccountBillingJoin = false;
    public static  $AccountNextBillingJoin = false;

    public static function generateQuery($CompanyID, $data, $filters){
        $select_columns = array();
        $setting_ag = isset($data['setting_ag'])?json_decode($data['setting_ag'],true):array();
        $setting_af_re = check_apply_limit($setting_ag);
        $measure_filter = count(array_intersect($data['filter'],array_keys(Report::$measures[$data['Cube']])));
        $orders_columns = array();

        if (count($data['row'])) {
            $query_distinct = self::commonQuery($CompanyID, $data, $filters);
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
            $select_columns[] = DB::Raw(get_col_full_name($setting_ag,'',$colname));
            $orders_columns[]  = $colname;
        }
        if($setting_af_re['applylimit']) {
            foreach($orders_columns as $order_column) {
                $final_query->orderby(DB::raw($order_column), $setting_af_re['order']);
            }
            $final_query->limit($setting_af_re['limit']);
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

        if(in_array('BillingType',$data['column']) || in_array('BillingType',$data['row']) ||
            in_array('BillingStartDate',$data['column']) || in_array('BillingStartDate',$data['row']) ||
            in_array('BillingCycleType',$data['column']) || in_array('BillingCycleType',$data['row']) ||
            in_array('BillingCycleValue',$data['column']) || in_array('BillingCycleValue',$data['row']) ||
            in_array('BillingClassID',$data['column']) || in_array('BillingClassID',$data['row']) ||
            in_array('LastInvoiceDate',$data['column']) || in_array('LastInvoiceDate',$data['row']) ||
            in_array('NextInvoiceDate',$data['column']) || in_array('NextInvoiceDate',$data['row']) ||
            in_array('LastChargeDate',$data['column']) || in_array('LastChargeDate',$data['row']) ||
            in_array('NextChargeDate',$data['column']) || in_array('NextChargeDate',$data['row'])
        ){
            $query_common->leftjoin($RMDB.'.tblAccountBilling', function ($join) {
                $join->on('tblAccountBilling.AccountID', '=', 'tblAccount.AccountID')
                    ->where('tblAccountBilling.ServiceID', '=', 0);
            }
            );
            self::$AccountBillingJoin = true;
        }

        if( in_array('BillingCycleValue',$data['column']) || in_array('BillingCycleValue',$data['row']) ||
            in_array('BillingCycleType',$data['column']) || in_array('BillingCycleType',$data['row']) ){
            $query_common->leftjoin($RMDB.'.tblAccountNextBilling', function ($join) {
                $join->on('tblAccountNextBilling.AccountID', '=', 'tblAccount.AccountID')
                    ->where('tblAccountNextBilling.ServiceID', '=', 0);
            }
            );
            self::$AccountNextBillingJoin = true;
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
            } else if (in_array($key,array_keys(Report::$measures[$data['Cube']]))) {
                $measure_name  = $measure_name2 = get_measure_name($key,'tblPayment');
                if($filter['number_agg'] ==  'count_distinct' ){
                    $aggregator2 = 'distinct';
                    $aggregator = 'count';
                }else{
                    $aggregator = $filter['number_agg'];
                    $aggregator2 = '';
                }
                if(empty($measure_name)) {
                    $measure_name =  $aggregator."(".$aggregator2." tblPayment.". $key . ") ";;
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
                        $whereRaw_measure = $measure_name." ". $filter['number_sign'] ." ". str_replace('*', '%', (double)$filter['number_agg_val']);
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