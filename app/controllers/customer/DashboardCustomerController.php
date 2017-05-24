<?php
use Jenssegers\Agent\Agent;
class DashboardCustomerController extends BaseController {

    
    public function __construct() {

    }
    public function home() {
        $CustomerID = Customer::get_accountID();
        $account = Account::find($CustomerID);
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $invoice_status_json = json_encode(Invoice::get_invoice_status());
        $monthfilter = 'Weekly';
        if(Cache::has('billing_Chart_cache_'.User::get_companyID().'_'.User::get_userID())){
            $monthfilter = Cache::get('billing_Chart_cache_'.User::get_companyID().'_'.User::get_userID());
        }
        $BillingDashboardWidgets 	= 	CompanyConfiguration::get('BILLING_DASHBOARD_CUSTOMER');
        if(!empty($BillingDashboardWidgets)) {
            $BillingDashboardWidgets			=	explode(",",$BillingDashboardWidgets);
        }
        return View::make('customer.index',compact('account','original_startdate','original_enddate','invoice_status_json','monthfilter','BillingDashboardWidgets'));
    }
    public function invoice_expense_chart(){
        $data = Input::all();
        $CurrencyID = "";
        $CustomerID = Customer::get_accountID();
        $CurrencySymbol = '';
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
        }
        $companyID = User::get_companyID();
        $query = "call prc_getDashboardinvoiceExpense ('". $companyID  . "',  '". $CurrencyID  . "','".$CustomerID."','".$data['Startdate']."','".$data['Enddate']."','".$data['ListType']."')";
        $InvoiceExpenseResult = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('InvoiceExpense'));
        $InvoiceExpense = $InvoiceExpenseResult['data']['InvoiceExpense'];
        return View::make('customer.billingdashboard.invoice_expense_chart', compact('InvoiceExpense','CurrencySymbol'));

    }

    public function invoice_expense_total(){

        $data = Input::all();
        $CustomerID = Customer::get_accountID();
        $CurrencyID = "";
        $CurrencySymbol = $CurrencyCode = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencyCode = Currency::getCurrency($CurrencyID);
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
        }
        $companyID = User::get_companyID();

        $query = "call prc_getDashboardinvoiceExpenseTotalOutstanding ('". $companyID  . "',  '". $CurrencyID  . "','".$CustomerID."','".$data['Startdate']."','".$data['Enddate']."')";
        $InvoiceExpenseResult = DB::connection('sqlsrv2')->select($query);
        if(!empty($InvoiceExpenseResult) && isset($InvoiceExpenseResult[0])) {
            $UnbilledAmount = $VendorUnbilledAmount = $BalanceAmount = 0;
            $getdata['AccountID'] = $CustomerID;
            $response =  NeonAPI::request('account/get_creditinfo',$getdata,false,false,false);
            if(!empty($response) && $response->status == 'success' ) {
                $SOA_Amount = AccountBalance::getAccountSOA($companyID, $CustomerID);
                if(!empty($response->data->UnbilledAmount)){
                    $UnbilledAmount = $response->data->UnbilledAmount;
                }
                if(!empty($response->data->VendorUnbilledAmount)){
                    $VendorUnbilledAmount = $response->data->VendorUnbilledAmount;
                }
                $BalanceAmount = $SOA_Amount+($UnbilledAmount-$VendorUnbilledAmount);
                $InvoiceExpenseResult[0]->TotalUnbillidAmount = $BalanceAmount>=0?$BalanceAmount:0;
                return Response::json(array("data" => $InvoiceExpenseResult[0], 'CurrencyCode' => $CurrencyCode, 'CurrencySymbol' => $CurrencySymbol));
            }else {
                return view_response_api($response);
            }
        }

        //return View::make('customer.billingdashboard.invoice_expense_total', compact( 'CurrencyCode', 'CurrencySymbol','TotalOutstanding'));

    }

    public function invoice_expense_total_widget(){

        $data = Input::all();
        $CurrencyID = "";
        $CurrencySymbol = $CurrencyCode = "";
        $CustomerID = Customer::get_accountID();
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencyCode = Currency::getCurrency($CurrencyID);
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
        }
        $companyID = User::get_companyID();
        $query = "call prc_getDashboardTotalOutStanding ('". $companyID  . "',  '". $CurrencyID  . "',".$CustomerID.")";
        $InvoiceExpenseResult = DB::connection('sqlsrv2')->select($query);
        $account_number = Account::where('AccountID',$CustomerID)->pluck('Number');
        $response = MOR::getAccountsBalace(array('username'=>$account_number));
        if(!empty($InvoiceExpenseResult) && isset($InvoiceExpenseResult[0])) {
            $InvoiceExpenseResult[0]['MOR_Balance'] = $response['balance'];
            return Response::json(array("data" =>$InvoiceExpenseResult[0],'CurrencyCode'=>$CurrencyCode,'CurrencySymbol'=>$CurrencySymbol));
        }
    }

    public function monitor_dashboard(){

        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = 1;
        $agent = new Agent();
        $isDesktop = $agent->isDesktop();
        $User = User::find(Customer::get_currentUser()->Owner);
        $AccountManager = $User->FirstName.' '.$User->LastName;
        $AccountManagerEmail = $User->EmailAddress;
        $MonitorDashboardSetting 	= 	array_filter(explode(',',CompanyConfiguration::get('CUSTOMER_MONITOR_DASHBOARD')));

        return View::make('customer.dashboard',compact('DefaultCurrencyID','original_startdate','original_enddate','isAdmin','newAccountCount','isDesktop','AccountManager','AccountManagerEmail','MonitorDashboardSetting'));

    }

    public function ajax_datagrid_Invoice_Expense($exportType){
        $data 							 = 		Input::all();
        $CompanyID 						 = 		Customer::get_companyID();
        $CustomerID                      =      Customer::get_accountID();
        $data['iDisplayStart'] 			+=		1;
        $typeText=[1=>'Payments',2=>'Invoices'];
        if($data['Type']==1) { //1 for Payment received.
            $columns = array('AccountName', 'InvoiceNo', 'Amount', 'PaymentType', 'PaymentDate', 'Status', 'CreatedBy', 'Notes');
            $sort_column = $columns[$data['iSortCol_0']];
        }elseif($data['Type']==2 || $data['Type']==3){ //2 for Total Invoices
            $columns = ['AccountName','InvoiceNumber','IssueDate','InvoicePeriod','GrandTotal','PendingAmount','InvoiceStatus','InvoiceID'];
            $sort_column = $columns[$data['iSortCol_0']];
        }
        $query = "call prc_getDashboardinvoiceExpenseDrilDown(" . $CompanyID . "," . $data['CurrencyID'] . ",'" . $data['PaymentDate_StartDate'] . "','" . $data['PaymentDate_EndDate'] . "',".$data['Type']."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "',".$CustomerID;
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

}
