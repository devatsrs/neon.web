<?php
class ReportTax extends \Eloquent{

    public static $database_columns = array(
        'TaxRateID' => 'tblInvoiceTaxRate.TaxRateID',
        'ProductID' => 'tblInvoiceDetail.ProductID',
        'ProductType' => 'tblInvoiceDetail.ProductType',
        'year' => 'YEAR(IssueDate)',
        'quarter_of_year' => 'QUARTER(IssueDate)',
        'month' => 'MONTH(IssueDate)',
        'week_of_year' => 'WEEK(IssueDate)',
        'date' => 'DATE(IssueDate)',
        'AccountID' => 'tblInvoice.AccountID',
        'SubscriptionID' => 'tblInvoiceDetail.ProductID',
        'ServiceID' => 'tblInvoice.ServiceID',
        'Code' => 'tblProduct.Code',
        'CurrencyID' => 'tblInvoice.CurrencyID',
        'BillingType' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.BillingType, "")',
        'BillingClassID' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.BillingClassID, "")',
        'BillingStartDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.BillingStartDate, "")',
        'BillingCycleType' => "IF(tblAccountNextBilling.BillingCycleType!='', tblAccountNextBilling.BillingCycleType, IF(tblAccountBilling.BillingCycleType!='', tblAccountBilling.BillingCycleType, ''))",
        'BillingCycleValue' => "IF(tblAccountNextBilling.BillingCycleValue!='', tblAccountNextBilling.BillingCycleValue, IF(tblAccountBilling.BillingCycleValue!='', tblAccountBilling.BillingCycleValue, ''))",
        'LastInvoiceDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.LastInvoiceDate, "")',
        'NextInvoiceDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.NextInvoiceDate, "")',
        'LastChargeDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.LastChargeDate, "")',
        'NextChargeDate' => 'IF(tblAccountBilling.ServiceID = 0, tblAccountBilling.NextChargeDate, "")',
        'IssueDate' => 'DATE_FORMAT(IssueDate,"%Y-%m-%d")',
        'invoiceDueDate' => 'DATE_ADD(DATE_FORMAT(IssueDate,"%Y-%m-%d"), INTERVAL tblBillingClass.PaymentDueInDays DAY)',
    );
    public static $database_payment_columns = array(
        'year' => 'YEAR(PaymentDate)',
        'quarter_of_year' => 'QUARTER(PaymentDate)',
        'month' => 'MONTH(PaymentDate)',
        'week_of_year' => 'WEEK(PaymentDate)',
        'date' => 'DATE(PaymentDate)',
    );

    public static $AccountBillingJoin = false;
    public static $AccountNextBillingJoin = false;
    public static $InvoiceDetailJoin = false;
    public static $InvoiceTaxRateJoin = false;
    public static $AccountJoin = false;
    public static $ProductJoin = false;
    public static $BillingClassJoin = false;
    public static $dateFilterString = array();
    public static $invoiceDataFilterWhere = array();

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

        foreach ($data['sum'] as $colname) {
            $measure_name  = self::get_measure_name($colname,$data,$CompanyID);
            if(!empty($measure_name)) {
                $select_columns[] = DB::Raw($measure_name." as " . $colname);
            }
            $orders_columns[]  = $colname;

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
        log::info($final_query->toSql());

        return $response;
    }

    public static function commonQuery($CompanyID, $data, $filters){

        $RMDB = Config::get('database.connections.sqlsrv.database');

        self::$dateFilterString = self::$invoiceDataFilterWhere = array();
        $query_common = DB::connection('sqlsrv2')
            ->table('tblInvoice')
            ->where(['tblInvoice.CompanyID' => $CompanyID]);


        if(in_array('BillingType',$data['column']) || in_array('BillingType',$data['row']) ||
            in_array('BillingStartDate',$data['column']) || in_array('BillingStartDate',$data['row']) ||
            in_array('BillingCycleType',$data['column']) || in_array('BillingCycleType',$data['row']) ||
            in_array('BillingCycleValue',$data['column']) || in_array('BillingCycleValue',$data['row']) ||
            in_array('BillingClassID',$data['column']) || in_array('BillingClassID',$data['row']) ||
            in_array('LastInvoiceDate',$data['column']) || in_array('LastInvoiceDate',$data['row']) ||
            in_array('NextInvoiceDate',$data['column']) || in_array('NextInvoiceDate',$data['row']) ||
            in_array('LastChargeDate',$data['column']) || in_array('LastChargeDate',$data['row']) ||
            in_array('NextChargeDate',$data['column']) || in_array('NextChargeDate',$data['row']) ||
            in_array('invoiceDueDate',$data['column']) || in_array('invoiceDueDate',$data['row'])
        ){
            $query_common->leftjoin($RMDB.'.tblAccountBilling', function ($join) {
                $join->on('tblAccountBilling.AccountID', '=', 'tblInvoice.AccountID')
                    ->where('tblAccountBilling.ServiceID', '=', 0);
            }
            );
            self::$AccountBillingJoin = true;
        }
        if( in_array('BillingCycleValue',$data['column']) || in_array('BillingCycleValue',$data['row']) ||
            in_array('BillingCycleType',$data['column']) || in_array('BillingCycleType',$data['row']) ){
            $query_common->leftjoin($RMDB.'.tblAccountNextBilling', function ($join) {
                $join->on('tblAccountNextBilling.AccountID', '=', 'tblInvoice.AccountID')
                    ->where('tblAccountNextBilling.ServiceID', '=', 0);
            }
            );
            self::$AccountNextBillingJoin = true;
        }

        if( in_array('invoiceDueDate',$data['column']) || in_array('invoiceDueDate',$data['row']) ){
            $query_common->leftjoin($RMDB.'.tblBillingClass', 'tblBillingClass.BillingClassID', '=', 'tblAccountBilling.BillingClassID');
            self::$BillingClassJoin = true;
        }

        if(in_array('TaxRateID',$data['column']) || in_array('TaxRateID',$data['row']) || in_array('TaxRateID',$data['filter']) || in_array('TotalTax',$data['sum'])){
            $query_common->join('tblInvoiceTaxRate', 'tblInvoice.InvoiceID', '=', 'tblInvoiceTaxRate.InvoiceID');
            $query_common->leftjoin('tblInvoiceDetail', 'tblInvoiceDetail.InvoiceDetailID', '=', 'tblInvoiceTaxRate.InvoiceDetailID');
            self::$InvoiceTaxRateJoin = true;
            // self::$InvoiceDetailJoin = true;

        }
        if(in_array('ProductID',$data['column']) || in_array('ProductID',$data['row']) || in_array('ProductID',$data['filter']) || in_array('ProductType',$data['column']) || in_array('ProductType',$data['row']) || in_array('ProductType',$data['filter']) || in_array('SubscriptionID',$data['column']) || in_array('SubscriptionID',$data['row']) || in_array('SubscriptionID',$data['filter']) || in_array('Code',$data['column']) || in_array('Code',$data['row']) || in_array('Code',$data['filter'])){
            $query_common->join('tblInvoiceDetail', 'tblInvoice.InvoiceID', '=', 'tblInvoiceDetail.InvoiceID');
            if(!self::$InvoiceDetailJoin) {
                self::$InvoiceDetailJoin = true;
                if (in_array('ProductID', $data['column']) || in_array('ProductID', $data['row']) || in_array('ProductID', $data['filter']) || in_array('Code', $data['column']) || in_array('Code', $data['row']) || in_array('Code', $data['filter'])) {
                    $query_common->whereRaw(' ( tblInvoiceDetail.ProductType = ' . Product::ITEM . ' OR tblInvoiceDetail.ProductType =' . Product::ONEOFFCHARGE . ')');
                    $query_common->join('tblProduct', 'tblProduct.ProductID', '=', 'tblInvoiceDetail.ProductID');
                    self::$ProductJoin = true;
                } else if (in_array('SubscriptionID', $data['column']) || in_array('SubscriptionID', $data['row']) || in_array('SubscriptionID', $data['filter'])) {
                    $query_common->whereRaw(' ( tblInvoiceDetail.ProductType = ' . Product::SUBSCRIPTION . ')');
                }
            }

        }

        if(report_join($data)){
            $query_common->join($RMDB.'.tblAccount', 'tblInvoice.AccountID', '=', 'tblAccount.AccountID');
            self::$AccountJoin = true;
        }





        foreach ($filters as $key => $filter) {

            if(self::$InvoiceDetailJoin == false && in_array($key,array('ProductID','ProductType'))){
                $query_common->join('tblInvoiceDetail', 'tblInvoice.InvoiceID', '=', 'tblInvoiceDetail.InvoiceID');
                self::$InvoiceDetailJoin = true;
            }
            if(self::$InvoiceTaxRateJoin == false && in_array($key,array('TaxRateID','TotalTaxSubTotal','ItemLineTotal'))){
                $query_common->join('tblInvoiceTaxRate', 'tblInvoice.InvoiceID', '=', 'tblInvoiceTaxRate.InvoiceID');
                if(!self::$InvoiceDetailJoin) {
                    $query_common->leftjoin('tblInvoiceDetail', 'tblInvoiceDetail.InvoiceDetailID', '=', 'tblInvoiceTaxRate.InvoiceDetailID');
                    self::$InvoiceTaxRateJoin = true;
                }
            }

            if (!empty($filter[$key]) && is_array($filter[$key])) {
                if(isset(self::$database_columns[$key])) {
                    $_words = '"'.implode('","', $filter[$key]).'"';
                    $query_common->whereRaw(self::$database_columns[$key].' in ('.$_words.')');
                    if(in_array($key, array('year', 'quarter_of_year','month','week_of_year'))) {
                        self::$dateFilterString[] = self::$database_payment_columns[$key].' in ('.implode(',',$filter[$key]).')';
                    }
                }else{
                    $query_common->whereIn($key, $filter[$key]);
                }
            } else if (!empty($filter['wildcard_match_val']) && in_array($key, array('InvoiceType', 'InvoiceStatus','ProductType'))) {
                $query_common->where($key, 'like', str_replace('*', '%', $filter['wildcard_match_val']));
            } else if (!empty($filter['wildcard_match_val']) && !in_array($key, array('year', 'quarter_of_year','month','week_of_year')) ) {
                $data_in_array = Report::getDataInArray($CompanyID, $key, $filter['wildcard_match_val']);
                if (!empty($data_in_array)) {
                    $query_common->whereIn($key, $data_in_array);
                }
            } else if ($key == 'date') {
                if (!empty($filter['start_date'])) {
                    $query_common->where('IssueDate', '>=', str_replace('*', '%', $filter['start_date']));
                    self::$dateFilterString[] = 'PaymentDate >= "'. str_replace('*', '%', $filter['start_date']).'"';
                    self::$invoiceDataFilterWhere[] = 'IssueDate'. '>="'. str_replace('*', '%', $filter['start_date']).'"';
                }
                if (!empty($filter['end_date'])) {
                    $query_common->where('IssueDate', '<=', str_replace('*', '%', $filter['end_date']));
                    self::$dateFilterString[] = 'PaymentDate <= "'. str_replace('*', '%', $filter['end_date']).'"';
                    self::$invoiceDataFilterWhere[] = 'IssueDate'. '<="'. str_replace('*', '%', $filter['end_date']).'"';
                }
            }else if (!empty($filter['wildcard_match_val']) && in_array($key, array('year', 'quarter_of_year','month','week_of_year'))) {
                $query_common->whereRaw(self::$database_columns[$key].' like "'.str_replace('*', '%', $filter['wildcard_match_val']).'"');
                self::$dateFilterString[] = self::$database_payment_columns[$key].' like "'.str_replace('*', '%', $filter['wildcard_match_val']).'"';
            } else if (in_array($key,array_keys(Report::$measures[$data['Cube']]))) {
                $measure_name  = $measure_name2 = self::get_measure_name($key,$data,$CompanyID);
                if($filter['number_agg'] ==  'count_distinct' ){
                    $aggregator2 = 'distinct';
                    $aggregator = 'count';
                }else{
                    $aggregator = $filter['number_agg'];
                    $aggregator2 = '';
                }
                if(empty($measure_name)) {
                    $measure_name =  $aggregator."(".$aggregator2." ". $key . ") ";;
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

    public static function get_measure_name($colname,$data,$CompanyID){
        $measure_name = '';
        $extra_query_3 = '';
        if(in_array('AccountID',$data['column']) || in_array('AccountID',$data['row'])){
            $extra_query_2 = 'tblPayment.AccountID = tblInvoice.AccountID';
        }else{
            $extra_query_2 = 'tblPayment.CompanyID = '.$CompanyID;
        }
        if(in_array('CurrencyID',$data['column']) || in_array('CurrencyID',$data['row']) || in_array('CurrencyID',$data['filter'])){
            $extra_query_3 = ' AND tblPayment.CurrencyID = tblInvoice.CurrencyID';
        }
        if($colname == 'TotalTax' && self::$InvoiceTaxRateJoin == true){
            $measure_name = "SUM(tblInvoiceTaxRate.TaxAmount) ";
        }else if($colname == 'GrandTotal' && self::$InvoiceDetailJoin == true){
            $measure_name = "SUM(tblInvoiceDetail.LineTotal) ";
        }else if($colname == 'PaidTotal'){
            $extra_query = !empty(self::$dateFilterString)?implode(' AND ',self::$dateFilterString):' 1=1 ';
            $measure_name = " (SELECT SUM(Amount) FROM tblPayment WHERE (FIND_IN_SET(tblPayment.InvoiceID,group_concat(tblInvoice.InvoiceID)) OR (tblPayment.InvoiceID =0 ".$extra_query_3." AND ".$extra_query.") ) AND $extra_query_2 AND Status='Approved' AND Recall = '0') ";
        }else if($colname == 'OutStanding'){
            $extra_query = !empty(self::$dateFilterString)?implode(' AND ',self::$dateFilterString):' 1=1 ';
            $measure_name = "(SUM(tblInvoice.GrandTotal) - (SELECT SUM(Amount) FROM tblPayment WHERE ( FIND_IN_SET(tblPayment.InvoiceID,group_concat(tblInvoice.InvoiceID)) OR (tblPayment.InvoiceID =0 ".$extra_query_3." AND ".$extra_query.")) AND $extra_query_2 AND Status='Approved' AND Recall = '0'))";
        }else if(self::$InvoiceTaxRateJoin == true && in_array($colname,array('TotalTax'))){
            $measure_name = "SUM(tblInvoiceTaxRate.TaxAmount)";
        }else if(self::$InvoiceDetailJoin == false && in_array($colname,array('GrandTotal'))){
            $measure_name = "SUM(tblInvoice." . $colname . ")";
        }else if(self::$InvoiceDetailJoin == false && in_array($colname,array('SubTotal'))){
            $measure_name = "SUM(tblInvoice." . $colname . ")";
            // $measure_name = "IFNULL(SUM(tblInvoiceDetail.LineTotal),SUM(tblInvoice.".$colname."))";
        }else if(self::$InvoiceDetailJoin == false && in_array($colname,array('ItemLineTotal'))){
            $measure_name = "(CASE WHEN tblInvoiceTaxRate.InvoiceDetailID >0 THEN SUM(tblInvoiceDetail.LineTotal) ELSE SUM(tblInvoice.SubTotal) END)";
        }else if(self::$InvoiceDetailJoin == false && in_array($colname,array('TotalTaxSubTotal'))){
            //$measure_name = "SUM(tblInvoiceTaxRate.TaxAmount) +  SUM(tblInvoice.SubTotal)";
            $measure_name = "(SUM(tblInvoiceTaxRate.TaxAmount) + CASE WHEN tblInvoiceTaxRate.InvoiceDetailID >0 THEN SUM(tblInvoiceDetail.LineTotal) ELSE SUM(tblInvoice.SubTotal) END )";
        }
        return $measure_name ;
    }

}