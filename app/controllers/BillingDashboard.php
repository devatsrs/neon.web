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
            $data['Startdate'] = trim($Startdate).' 00:00:00';
            $data['Enddate'] = trim($Enddate).' 23:59:59';
        }else{
            $data['Startdate'] = $data['date-span'];
            $data['Enddate']=0;
        }

        Cache::forever('billing_Chart_cache_'.User::get_companyID().'_'.User::get_userID(),$data['ListType']);
        $companyID = User::get_companyID();
        $query = "call prc_getDashboardinvoiceExpense ('". $companyID  . "',  '". $CurrencyID  . "','0','".$data['Startdate']."','".$data['Enddate']."','".$data['ListType']."')";
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
            $data['Startdate'] = trim($Startdate).' 00:00:00';
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

    public function invoice_expense_total_widget(){

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
            $data['Startdate'] = trim($Startdate).' 00:00:00';
            $data['Enddate'] = trim($Enddate).' 23:59:59';
        }else{
            $data['Startdate'] = $data['date-span'];
            $data['Enddate']=0;
        }
        $companyID = User::get_companyID();
        $query = "call prc_getDashboardTotalOutStanding ('". $companyID  . "',  '". $CurrencyID  . "','0')";
        $InvoiceExpenseResult = DB::connection('sqlsrv2')->select($query);
        if(!empty($InvoiceExpenseResult) && isset($InvoiceExpenseResult[0])) {
            return Response::json(array("data" =>$InvoiceExpenseResult[0],'CurrencyCode'=>$CurrencyCode,'CurrencySymbol'=>$CurrencySymbol));
        }
    }

    public function ajax_top_pincode(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $report_label = 'Pin Cost';
        $data['Limit'] = empty($data['Limit'])?5:$data['Limit'];
        $data['Type'] = empty($data['Type'])?1:$data['Type'];
        $data['PinExt'] = empty($data['PinExt'])?'pincode':$data['PinExt'];
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        if(isset($data['Closingdate'])){
            $Closingdate		=	explode(' - ',$data['Closingdate']);
            $Startdate			=   $Closingdate[0];
            $Enddate			=	$Closingdate[1];
        }else{
            $Startdate = empty($data['Startdate'])?date('Y-m-d', strtotime('-1 week')):$data['Startdate'];
            $Enddate = empty($data['Enddate'])?date('Y-m-d'):$data['Enddate'];
        }
        $data['Startdate'] = trim($Startdate).' 00:00:00';
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
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Pincode Detail Report.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Pincode Detail Report.xls';
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
        }elseif($data['Type']==2 || $data['Type']==3 || $data['Type']==4 || $data['Type']==5 || $data['Type']==6 || $data['Type']==7){ //2 for Total Invoices
            $columns = ['AccountName','InvoiceNumber','IssueDate','InvoicePeriod','GrandTotal','PendingAmount','InvoiceStatus','InvoiceID'];
            $sort_column = $columns[$data['iSortCol_0']];
        }
        $query = "call prc_getDashboardinvoiceExpenseDrilDown(" . $CompanyID . "," . (int)$data['CurrencyID'] . ",'" . $data['PaymentDate_StartDate'] . "','" . $data['PaymentDate_EndDate'] . "',".$data['Type']."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "',0";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($exportType=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/'.$typeText[$data['Type']].'.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($exportType=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/'.$typeText[$data['Type']].'.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query = $query.',0)';
        //echo $query;exit();
        return DataTableSql::of($query,'sqlsrv2')->make();
    }
    public function GetDashboardPR(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['ListType'] = empty($data['ListType'])?1:$data['ListType'];
        $data['Type'] = empty($data['Type'])?0:$data['Type'];
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        if(isset($data['Duedate'])){
            $Duedate		=	explode(' - ',$data['Duedate']);
            $Startdate			=   $Duedate[0];
            $Enddate			=	$Duedate[1];
        }else{
            $Startdate = empty($data['Startdate'])?date('Y-m-d', strtotime('-1 week')):$data['Startdate'];
            $Enddate = empty($data['Enddate'])?date('Y-m-d'):$data['Enddate'];
        }
        $data['Startdate'] = trim($Startdate).' 00:00:00';
        $data['Enddate'] = trim($Enddate).' 23:59:59';

        $CurrencySymbol = $CurrencyID = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
        }
        Cache::forever('GetDashboardPR_'.User::get_companyID().'_'.User::get_userID(),$data['ListType']);
        $query = "call prc_getDashboardPayableReceivable ('". $companyID  . "', '".intval($CurrencyID)."','".$data['AccountID']."', '". $data['Startdate']  . "','". $data['Enddate']  . "','". $data['Type']  . "','". $data['ListType']  . "')";
        $PayableReceivable_data = DB::connection('neon_report')->select($query);
        $series = $category1 = $category2 = $category3 = array();
        $cat_index = 0;
        foreach($PayableReceivable_data as $TopReport){
            $category1[$cat_index]['name'] = $TopReport->Date;
            $category1[$cat_index]['y'] = $TopReport->TotalOutstanding;

            $category2[$cat_index]['name'] = $TopReport->Date;
            $category2[$cat_index]['y'] = $TopReport->TotalPayable;

            $category3[$cat_index]['name'] = $TopReport->Date;
            $category3[$cat_index]['y'] = $TopReport->TotalReceivable;

            $cat_index++;
        }
        if(!empty($category1)) {
            $series[] = array('name' => 'Total Outstanding', 'data' => $category1, 'color' => '#3366cc');
            $series[] = array('name' => 'Total Payable', 'data' => $category2, 'color' => '#ff9900');
            $series[] = array('name' => 'Total Receivable', 'data' => $category3, 'color' => '#dc3912');
        }
        $reponse['series'] = $series;
        return json_encode($reponse,JSON_NUMERIC_CHECK);
    }
    public function GetDashboardPL(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['ListType'] = empty($data['ListType'])?1:$data['ListType'];
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        if(isset($data['Duedate'])){
            $Duedate		=	explode(' - ',$data['Duedate']);
            $Startdate			=   $Duedate[0];
            $Enddate			=	$Duedate[1];
        }else{
            $Startdate = empty($data['Startdate'])?date('Y-m-d', strtotime('-1 week')):$data['Startdate'];
            $Enddate = empty($data['Enddate'])?date('Y-m-d'):$data['Enddate'];
        }
        $data['Startdate'] = trim($Startdate).' 00:00:00';
        $data['Enddate'] = trim($Enddate).' 23:59:59';

        $CurrencySymbol = $CurrencyID = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
        }

        Cache::forever('GetDashboardPL_'.User::get_companyID().'_'.User::get_userID(),$data['ListType']);
        $query = "call prc_getDashboardProfitLoss ('". $companyID  . "', '".intval($CurrencyID)."','".$data['AccountID']."', '". $data['Startdate']  . "','". $data['Enddate']  . "','". $data['ListType']  . "')";
        $PayableReceivable_data = DB::connection('neon_report')->select($query);
        $series = $category1 = $category2 = $category3 = array();
        $cat_index = 0;
        foreach($PayableReceivable_data as $TopReport){
            $category1[$cat_index]['name'] = $TopReport->Date;
            $category1[$cat_index]['y'] = $TopReport->PL;
            $cat_index++;
        }
        if(!empty($category1)) {
            $series[] = array('name' => 'Profit Loss', 'data' => $category1, 'color' => '#3366cc','showInLegend'=>false);
        }
        $reponse['series'] = $series;
        return json_encode($reponse,JSON_NUMERIC_CHECK);
    }
}