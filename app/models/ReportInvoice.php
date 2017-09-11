<?php
class ReportInvoice extends \Eloquent{

    public static $database_columns = array(
        'TaxRateID' => 'tblInvoiceTaxRate.TaxRateID',
        'ProductID' => 'tblInvoiceDetail.ProductID',
        'ProductType' => 'tblInvoiceDetail.ProductType',
        'year' => 'YEAR(IssueDate)',
        'quarter_of_year' => 'QUARTER(IssueDate)',
        'month' => 'MONTH(IssueDate)',
        'week_of_year' => 'WEEK(IssueDate)',
        'date' => 'DATE(IssueDate)',
    );

    public static function generateQuery($CompanyID, $data, $filters){
        $select_columns = array();

        if (count($data['row'])) {
            $query_distinct = self::commonQuery($CompanyID, $data, $filters);

            foreach ($data['row'] as $column) {
                if(isset(self::$database_columns[$column])){
                    $query_distinct->orderby($column);
                    $select_columns[] = DB::raw(self::$database_columns[$column].' as '.$column) ;
                }else{
                    $query_distinct->orderby($column);
                    $select_columns[] = $column;
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
                $final_query->groupby($column);
                $select_columns[] = $column;
            }
        }
        foreach ($data['row'] as $column) {
            if(isset(self::$database_columns[$column])){
                $final_query->groupby($column);
            }else{
                $final_query->groupby($column);
            }
        }

        //$data['row'] = array_merge($data['row'], $data['column']);
        foreach ($data['sum'] as $colname) {
            if($colname == 'TotalTax' && (in_array('TaxRateID',$data['column']) || in_array('TaxRateID',$data['row']))){
                $select_columns[] = DB::Raw("SUM(tblInvoiceTaxRate.TaxAmount) as " . $colname);
            }else if($colname == 'GrandTotal' && (in_array('ProductID',$data['column']) || in_array('ProductID',$data['row']) || in_array('ProductType',$data['column']) || in_array('ProductType',$data['row']))){
                $select_columns[] = DB::Raw("SUM(tblInvoiceDetail.LineTotal) as " . $colname);
            }else if($colname == 'PaidTotal'){
                $select_columns[] = DB::Raw(" SUM(Amount) as " . $colname);
            }else if($colname == 'OutStanding'){
                $select_columns[] = DB::Raw("(SUM(tblInvoice.GrandTotal) - SUM(Amount) as " . $colname);
            }else if(in_array($colname,array('GrandTotal','TotalTax'))){
                $select_columns[] = DB::Raw("SUM(tblInvoice." . $colname . ") as " . $colname);
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

    public static function commonQuery($CompanyID, $data, $filters){
        $query_common = DB::connection('sqlsrv2')
            ->table('tblInvoice')
            ->where(['tblInvoice.CompanyID' => $CompanyID]);

        if(in_array('TaxRateID',$data['column']) || in_array('TaxRateID',$data['row'])){
            $query_common->join('tblInvoiceTaxRate', 'tblInvoice.InvoiceID', '=', 'tblInvoiceTaxRate.InvoiceID');
        }
        if(in_array('ProductID',$data['column']) || in_array('ProductID',$data['row']) || in_array('ProductType',$data['column']) || in_array('ProductType',$data['row'])){
            $query_common->join('tblInvoiceDetail', 'tblInvoice.InvoiceID', '=', 'tblInvoiceDetail.InvoiceID');
        }

        if(in_array('PaidTotal',$data['sum']) || in_array('OutStanding',$data['sum'])){
            $query_common->leftjoin('tblPayment', function($join)
            {
                $join->on('tblInvoice.InvoiceID', '=', 'tblPayment.InvoiceID');
                $join->on('tblInvoice.AccountID', '=', 'tblPayment.AccountID');
                $join->on('tblInvoice.Status', '=', 'Approved');
                $join->on('tblInvoice.Recall', '=', '0');

            });
        }



        foreach ($filters as $key => $filter) {

            if (!empty($filter[$key]) && is_array($filter[$key])) {
                if(isset(self::$database_columns[$key])) {
                    $query_common->whereRaw(self::$database_columns[$key].' in ('.implode(',',$filter[$key]).')');
                }else{
                    $query_common->whereIn($key, $filter[$key]);
                }
            } else if (!empty($filter['wildcard_match_val']) && in_array($key, array('Trunk', 'AreaPrefix'))) {
                $query_common->where($key, 'like', str_replace('*', '%', $filter['wildcard_match_val']));
            } else if (!empty($filter['wildcard_match_val'])) {
                $data_in_array = Report::getDataInArray($CompanyID, $key, $filter['wildcard_match_val']);
                if (!empty($data_in_array)) {
                    $query_common->whereIn($key, $data_in_array);
                }
            } else if ($key == 'date') {

                if (!empty($filter['start_date'])) {

                    $query_common->where('IssueDate', '>=', str_replace('*', '%', $filter['start_date']));
                }
                if (!empty($filter['end_date'])) {
                    $query_common->where('IssueDate', '<=', str_replace('*', '%', $filter['end_date']));
                }
            }
        }
        return $query_common;
    }

}