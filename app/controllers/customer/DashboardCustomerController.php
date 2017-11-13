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
                $account_number = Account::where('AccountID',$CustomerID)->pluck('Number');

                /** mor account balance widget **/
                $GatewayID = Gateway::getGatewayID('MOR');
                $CompanyGatewayID = CompanyGateway::getCompanyGatewayID($GatewayID);
                $mor = new MOR($CompanyGatewayID);
                $response = $mor->getAccountsBalace(array('username'=>$account_number));
                $InvoiceExpenseResult[0]->MOR_Balance = $response['balance'];

                /** call shop account balance widget **/
                $GatewayID = Gateway::getGatewayID('CallShop');
                $CompanyGatewayID = CompanyGateway::getCompanyGatewayID($GatewayID);
                $callshop = new CallShop($CompanyGatewayID);
                $response = $callshop->getAccountsBalace(array('username'=>$account_number));
                $InvoiceExpenseResult[0]->CallShop_Balance = $response['balance'];


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
        if(!empty($InvoiceExpenseResult) && isset($InvoiceExpenseResult[0])) {
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

    public function daily_report($id=0){

        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID", $companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');

        $AccountID = !empty($id)?$id:Customer::get_accountID();
        $extends = !empty($id)?'layout.main':'layout.customer.main';
        return View::make('customer.daily_report', compact('DefaultCurrencyID', 'original_startdate', 'original_enddate','AccountID','extends'));

    }
    /*public function daily_report_ajax_datagrid($type){
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $CustomerID = Customer::get_accountID();
        $data['iDisplayStart'] += 1;
        $query = "call prc_getDailyReport (" . $CompanyID . ",$CustomerID,'" . $data['StartDate'] . "','" . $data['EndDate'] . "'," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'];
        if (isset($data['Export']) && $data['Export'] == 1) {
            $filesname = '';
            if(!empty($data['StartDate'])){
                $filesname .= ' From '.$data['StartDate'];
            }
            if(!empty($data['EndDate'])){
                $filesname .= ' To '.$data['EndDate'];
            }
            $excel_data = DB::connection('neon_report')->select($query . ',1)');
            $excel_data = json_decode(json_encode($excel_data), true);

            if ($type == 'csv') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/Movement Report '.$filesname.'.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            } elseif ($type == 'xlsx') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/Movement Report '.$filesname.'.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .= ',0)';

        return DataTableSql::of($query,'neon_report')->make();

    }*/

    public function daily_report_ajax_datagrid($type){
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $CustomerID = $data['AccountID'];
        $data['iDisplayStart'] += 1;
        $account_number = Account::where('AccountID',$CustomerID)->pluck('Number');
        $GatewayID = Gateway::getGatewayID('MOR');
        $CompanyGatewayID = CompanyGateway::getCompanyGatewayID($GatewayID);
        $mor = new MOR($CompanyGatewayID);
        $response = $mor->getMovementReport(array('username'=>$account_number,'StartDate'=>$data['StartDate'],'EndDate'=>$data['EndDate']));

        $previous_bal = $response['previous_bal'];
		$today_total = 0;

        $row_count = 0;

        return Datatables::of($response['datatable'])
            ->add_column('Payments', function($data)use($response){ return isset($response['payment'][$data->date])?$response['payment'][$data->date]:0;})
            ->add_column('Consumption', function($data)use($response){ return isset($response['calls'][$data->date])?$response['calls'][$data->date]:0;})
            ->add_column('Total', function($data)use($response){
                if (isset($response['calls'][$data->date]) && isset($response['payment'][$data->date])) {
                    return $response['payment'][$data->date] - $response['calls'][$data->date];
                } elseif (isset($response['payment'][$data->date])) {
                    return $response['payment'][$data->date];
                } elseif (isset($response['calls'][$data->date])) {
                    return -$response['calls'][$data->date];
                } else {
                    return 0;
                }
            })
            ->add_column('Balance', function($data)use(&$previous_bal,&$row_count,&$today_total,$response){

                $payment =  isset($response['payment'][$data->date])?$response['payment'][$data->date]:0;
                $consumption =  isset($response['calls'][$data->date])?$response['calls'][$data->date]:0;
                if($row_count > 0){
                    $previous_bal = $previous_bal-$today_total-$payment+$consumption;
					$today_total = 0;
                }else{
					$today_total = $payment-$consumption;
				}
                $row_count++;
                return number_format($previous_bal,get_round_decimal_places(),'.','');
            })
            ->make();

    }

    public function daily_report_ajax_datagrid_total(){
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $CustomerID = $data['AccountID'];
        $account_number = Account::where('AccountID',$CustomerID)->pluck('Number');
        $GatewayID = Gateway::getGatewayID('MOR');
        $CompanyGatewayID = CompanyGateway::getCompanyGatewayID($GatewayID);
        $mor = new MOR($CompanyGatewayID);
        $response = $mor->getMovementReportTotal(array('username'=>$account_number,'StartDate'=>$data['StartDate'],'EndDate'=>$data['EndDate']));

        return json_encode($response,JSON_NUMERIC_CHECK);
    }

    public function customer_rates(){
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $CustomerID = Customer::get_accountID();
        $account_number = Account::where('AccountID',$CustomerID)->pluck('Number');
        $GatewayID = Gateway::getGatewayID('MOR');
        $CompanyGatewayID = CompanyGateway::getCompanyGatewayID($GatewayID);
        $mor = new MOR($CompanyGatewayID);
        return View::make('customer.rates', compact('DefaultCurrencyID', 'original_startdate', 'original_enddate'));
    }

    public function customer_rates_grid($type){
        $data = Input::all();
        $CustomerID = Customer::get_accountID();
        $account_number = Account::where('AccountID',$CustomerID)->pluck('Number');

        DB::table('tblGatewayCustomerRate')->where('CustomerID',$CustomerID)->delete();
        $companygateways = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->get();
        $param['username'] = $account_number;
        $param['CustomerID'] = $CustomerID;
        $param['Prefix'] = $data['Prefix'];
        $param['Description'] = $data['Description'];
        if(count($companygateways)>0){
            foreach($companygateways as $companygateway) {
                try{
                    $GatewayName = Gateway::getGatewayName($companygateway['GatewayID']);
                    if($GatewayName == 'MOR' || $GatewayName == 'CallShop') {
                        GatewayAPI::GatewayMethod($GatewayName, $companygateway['CompanyGatewayID'], 'getRates',$param);
                    }
                }catch(Exception $e){
                    Log::error($e);
                }
            }
        }
        $CustomerRate = DB::table('tblGatewayCustomerRate')
            ->where("CustomerID", $CustomerID)
            ->select('Code','Description',  'Interval1','IntervalN','ConnectionFee', 'Rate','EffectiveDate');
        return Datatables::of($CustomerRate)->make();
    }

}
