<?php

class BillingDashboard extends \BaseController {

    public function invoice_expense_chart(){
        $data = Input::all();
        $CurrencyID = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
        }
        if($data['date-span']==0){
            $Closingdate		=	explode(' - ',$data['Closingdate']);
            $Startdate			=   $Closingdate[0];
            $Enddate			=	$Closingdate[1];
            $data['Startdate'] = trim($Startdate).' 00:00:01';
            $data['Enddate'] = trim($Enddate).' 23:59:59';
        }else{
            $data['Startdate'] = $data['date-span'];
            $data['Enddate']=0;
        }
        $companyID = User::get_companyID();
        $query = "call prc_getDashboardinvoiceExpense ('". $companyID  . "',  '". $CurrencyID  . "','0','".$data['Startdate']."','".$data['Enddate']."')";
        $InvoiceExpenseResult = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('InvoiceExpense'));
        $InvoiceExpense = $InvoiceExpenseResult['data']['InvoiceExpense'];
        return View::make('billingdashboard.invoice_expense_chart', compact('InvoiceExpense','CurrencySymbol'));

    }

    public function invoice_expense_total(){

        $data = Input::all();
        $CurrencyID = "";
        $CurrencySymbol = $CurrencyCode = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencyCode = Currency::getCurrency($CurrencyID);
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
        }
        if($data['date-span']==0){
            $Closingdate		=	explode(' - ',$data['Closingdate']);
            $Startdate			=   $Closingdate[0];
            $Enddate			=	$Closingdate[1];
            $data['Startdate'] = trim($Startdate).' 00:00:01';
            $data['Enddate'] = trim($Enddate).' 23:59:59';
        }else{
            $data['Startdate'] = $data['date-span'];
            $data['Enddate']=0;
        }
        $companyID = User::get_companyID();

        $query = "call prc_getDashboardinvoiceExpenseTotalOutstanding ('". $companyID  . "',  '". $CurrencyID  . "','0','".$data['Startdate']."','".$data['Enddate']."')";
        $InvoiceExpenseResult = DB::connection('sqlsrv2')->select($query);
        $TotalOutstanding = 0;
        if(!empty($InvoiceExpenseResult) && isset($InvoiceExpenseResult[0])) {
            /*$TotalOutstanding = $InvoiceExpenseResult[0]->TotalOutstanding;*/
            return Response::json(array("data" =>$InvoiceExpenseResult[0],'CurrencyCode'=>$CurrencyCode,'CurrencySymbol'=>$CurrencySymbol));
        }

        /*return View::make('billingdashboard.invoice_expense_total', compact( 'CurrencyCode', 'CurrencySymbol','TotalOutstanding'));*/

    }
    public function ajax_top_pincode(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $report_label = 'Pin Cost';
        $data['Limit'] = empty($data['Limit'])?5:$data['Limit'];
        $data['Type'] = empty($data['Type'])?1:$data['Type'];
        $data['PinExt'] = empty($data['PinExt'])?'pincode':$data['PinExt'];
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $Closingdate		=	explode(' - ',$data['Closingdate']);
        $Startdate			=   $Closingdate[0];
        $Enddate			=	$Closingdate[1];
        //$Startdate = empty($data['Startdate'])?date('Y-m-d', strtotime('-1 week')):$data['Startdate'];
        //$Enddate = empty($data['Enddate'])?date('Y-m-d'):$data['Enddate'];
        $data['Startdate'] = trim($Startdate).' 23:59:59';
        $data['Enddate'] = trim($Enddate).' 23:59:59';
        if($data['Type'] == 2 && $data['PinExt'] == 'pincode'){
            $report_label = 'Pin Duration (in Sec) ';
        }else if($data['Type'] == 2 && $data['PinExt'] == 'extension'){
            $report_label = 'Extension Duration (in Sec) ';
        }else if($data['PinExt'] == 'extension'){
            $report_label = 'Extension Cost';
        }
        $CurrencySymbol = $CurrencyID = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
        }

        $query = "call prc_getDashBoardPinCodes ('". $companyID  . "',  '". $data['Startdate']  . "','". $data['Enddate']  . "','".$data['AccountID']."','". $data['Type']  . "','". $data['Limit']  . "','". $data['PinExt']. "','".intval($CurrencyID)."')";
        $top_pincode_data = DB::connection('sqlsrv2')->select($query);

        return View::make('billingdashboard.pin_expense_chart',compact('top_pincode_data','report_label','report_header','data','CurrencySymbol'));
    }
    public function ajaxgrid_top_pincode($type){
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = User::get_companyID();
        $columns = ['DestinationNumber','TotalCharges','NoOfCalls'];
        $data['Startdate'] = empty($data['Startdate'])?date('Y-m-d', strtotime('-1 week')):$data['Startdate'];
        $data['Enddate'] = empty($data['Enddate'])?date('Y-m-d'):$data['Enddate'];
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $sort_column = $columns[$data['iSortCol_0']];
        $CurrencyID = "0";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
        }
        $query = "call prc_getPincodesGrid (".$companyID.",'".$data['Pincode']."','".$data['PinExt']."','".$data['Startdate']."','".$data['Enddate']."','".$data['AccountID']."','".intval($CurrencyID)."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Pincode Detail Report.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Pincode Detail Report.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

            /*Excel::create('Pincode Detail Report', function ($excel) use ($excel_data) {
                $excel->sheet('Pincode Detail Report', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .= ',0)';
        //echo $query;exit;
        return DataTableSql::of($query,'sqlsrv2')->make();

    }

    public function ajax_datagrid_Invoice_Expense($exportType){
        $data 							 = 		Input::all();
        $CompanyID 						 = 		User::get_companyID();
        $data['iDisplayStart'] 			+=		1;
        $typeText=[1=>'Payments',2=>'Invoices',3=>'OutStanding'];
        if($data['Type']==1) { //1 for Payment received.
            $columns = array('AccountName', 'InvoiceNo', 'Amount', 'PaymentType', 'PaymentDate', 'Status', 'CreatedBy', 'Notes');
            $sort_column = $columns[$data['iSortCol_0']];
        }elseif($data['Type']==2 || $data['Type']==3 || $data['Type']==4 || $data['Type']==5 || $data['Type']==6){ //2 for Total Invoices
            $columns = ['AccountName','InvoiceNumber','IssueDate','InvoicePeriod','GrandTotal','PendingAmount','InvoiceStatus','InvoiceID'];
            $sort_column = $columns[$data['iSortCol_0']];
        }
        $query = "call prc_getDashboardinvoiceExpenseDrilDown(" . $CompanyID . "," . $data['CurrencyID'] . ",'" . $data['PaymentDate_StartDate'] . "','" . $data['PaymentDate_EndDate'] . "',".$data['Type']."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "',0";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($exportType=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/'.$typeText[$data['Type']].'.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($exportType=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/'.$typeText[$data['Type']].'.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query = $query.',0)';
        //echo $query;exit();
        return DataTableSql::of($query,'sqlsrv2')->make();
    }
}