<?php

class BillingDashboard extends \BaseController {

    public function invoice_expense_chart(){
        $data = Input::all();
        $CurrencyID = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
        }
        $companyID = User::get_companyID();
        $query = "call prc_getDashboardinvoiceExpense ('". $companyID  . "',  '". $CurrencyID  . "','0')";
        $InvoiceExpenseResult = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('InvoiceExpense'));
        $InvoiceExpense = $InvoiceExpenseResult['data']['InvoiceExpense'];
        return View::make('billingdashboard.invoice_expense_chart', compact('InvoiceExpense'));

    }

    public function invoice_expense_total(){

        $data = Input::all();
        $CurrencyID = "";
        $CurrencySymbol = $CurrencyCode = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencyCode = Currency::getCurrency($CurrencyID);
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyCode);
        }
        $companyID = User::get_companyID();

        $query = "call prc_getDashboardinvoiceExpense ('". $companyID  . "',  '". $CurrencyID  . "','0')";
        $InvoiceExpenseResult = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('InvoiceExpense'));

        $InvoiceExpense = $InvoiceExpenseResult['data']['InvoiceExpense'];
        $TotalOutstanding = 0;
        if(count($InvoiceExpense)) {

            foreach ($InvoiceExpense as $row) {
                if(isset($row->TotalOutstanding)) {
                    $TotalOutstanding += $row->TotalOutstanding;
                }
            }
        }

        return View::make('billingdashboard.invoice_expense_total', compact( 'CurrencyCode', 'CurrencySymbol','TotalOutstanding'));

    }
    public function ajax_top_pincode(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $report_label = 'Pin Cost';
        $data['Limit'] = empty($data['Limit'])?5:$data['Limit'];
        $data['Type'] = empty($data['Type'])?1:$data['Type'];
        $data['PinExt'] = empty($data['PinExt'])?'pincode':$data['PinExt'];

        if($data['Type'] == 2 && $data['PinExt'] == 'pincode'){
            $report_label = 'Pin Duration (in Sec) ';
        }else if($data['Type'] == 2 && $data['PinExt'] == 'extension'){
            $report_label = 'Extension Duration (in Sec) ';
        }else if($data['PinExt'] == 'extension'){
            $report_label = 'Extension Cost';
        }

        $query = "call prc_getDashBoardPinCodes ('". $companyID  . "',  '". $data['Startdate']  . "','". $data['Enddate']  . "','0','". $data['Type']  . "','". $data['Limit']  . "','". $data['PinExt']. "')";
        $top_pincode_data = DB::connection('sqlsrv2')->select($query);

        return View::make('billingdashboard.pin_expense_chart',compact('top_pincode_data','report_label','report_header','data'));
    }
    public function ajaxgrid_top_pincode(){
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = User::get_companyID();
        $columns = ['DestinationNumber','TotalCharges','NoOfCalls'];
        $data['Startdate'] = empty($data['Startdate'])?'0000-00-00 00:00:00':$data['Startdate'];
        $data['Enddate'] = empty($data['Enddate'])?'0000-00-00 00:00:00':$data['Enddate'];
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getPincodesGrid (".$companyID.",'".$data['Pincode']."','".$data['PinExt']."','".$data['Startdate']."','".$data['Enddate']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Pincode Detail Report', function ($excel) use ($excel_data) {
                $excel->sheet('Pincode Detail Report', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        $query .= ',0)';
        //echo $query;exit;
        return DataTableSql::of($query,'sqlsrv2')->make();

    }
}