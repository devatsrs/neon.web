<?php

class PaymentsCustomerController extends \BaseController {


    public function ajax_datagrid($type) {
        $data = Input::all();
        $CompanyID = Customer::get_companyID();
        $data['iDisplayStart'] +=1;
        $data['AccountID'] = Customer::get_accountID();
        $data['InvoiceNo']=$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        //$data['Status'] = 'NULL';
        $data['Status'] = 'Approved';
        $data['type'] = $data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] = $data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
        $data['p_paymentstartdate'] 	 = 		$data['PaymentDate_StartDate']!=''?"".$data['PaymentDate_StartDate']."":'null';
        $data['p_paymentstartTime'] 	 = 		$data['PaymentDate_StartTime']!=''?"".$data['PaymentDate_StartTime']."":'00:00:00';
        $data['p_paymentenddate'] 	 	 = 		$data['PaymentDate_EndDate']!=''?"".$data['PaymentDate_EndDate']."":'null';
        $data['p_paymentendtime'] 	 	 = 		$data['PaymentDate_EndTime']!=''?"".$data['PaymentDate_EndTime']."":'00:00:00';
        $data['p_paymentstart']			 =		'null';
        $data['p_paymentend']			 =		'null';

        if($data['p_paymentstartdate']!='' && $data['p_paymentstartdate']!='null' && $data['p_paymentstartTime']!='')
        {
            $data['p_paymentstart']		=	"'".$data['p_paymentstartdate'].' '.$data['p_paymentstartTime']."'";
        }

        if($data['p_paymentenddate']!='' && $data['p_paymentenddate']!='null' && $data['p_paymentendtime']!='')
        {
            $data['p_paymentend']			=	"'".$data['p_paymentenddate'].' '.$data['p_paymentendtime']."'";
        }

        if($data['p_paymentstart']!='null' && $data['p_paymentend']=='null')
        {
            $data['p_paymentend'] 			= 	"'".date("Y-m-d H:i:s")."'";
        }

        // $data['recall_on_off'] = isset($data['recall_on_off'])?($data['recall_on_off']== 'true'?1:0):0;
        $data['recall_on_off'] = 0;

        $account                     = Account::find($data['AccountID']);
        $CurrencyId                  = $account->CurrencyId;
        $accountCurrencyID 		     = empty($CurrencyId)?'0':$CurrencyId;

        // AccountManager Condition
        $userID = 0;

        $columns = array('InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",'','".$data['Status']."',".$data['type'].",".$data['paymentmethod'].",".$data['recall_on_off'].",".$accountCurrencyID.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',1,".$data['p_paymentstart'].",".$data['p_paymentend']."";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',2,'.$userID.',"")');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID) .'/Payment.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID) .'/Payment.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Payment', function ($excel) use ($excel_data) {
                $excel->sheet('Payment', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0,'.$userID.',"")';
        return DataTableSql::of($query,'sqlsrv2')->make();
    }
	/**
	 * Display a listing of the resource.
	 * GET /payments
	 *
	 * @return Response
	 */
	public function index()
	{
        $id=0;
        $companyID = Customer::get_companyID();
        $CurrencyId = Company::where("CompanyID", '=', $companyID)->pluck('CurrencyId');
        $currency = Currency::where('CurrencyId',$CurrencyId)->pluck('Code');

        $Account = Customer::get_currentUser();

        $StripeACHCount=0;
        $AccountPaymentProfile = AccountPaymentProfile::where(['PaymentGatewayID'=> PaymentGateway::StripeACH,'AccountID'=>$Account->AccountID,'Status'=>1])->count();
        if($AccountPaymentProfile>0){
            $StripeACHCount=1;
        }

        $method = array(
            ''=>cus_lang("DROPDOWN_OPTION_SELECT")
        );
        if(empty($Account->ShowAllPaymentMethod)){
            if(($Account->PaymentMethod == 'AuthorizeNet') && (is_authorize($Account->CompanyID)  ) ){
                $method["online_AuthorizeNet"]="AuthorizeNet";
            }
            if(($Account->PaymentMethod == 'Stripe') && (is_Stripe($Account->CompanyID)  ) ){
                $method["online_Stripe"]="Stripe";
            }
            if(is_FideliPay($Account->CompanyID)){
                $method["online_FideliPay"]="FideliPay";
            }
            if(($Account->PaymentMethod == 'StripeACH') && (is_StripeACH($Account->CompanyID) && $StripeACHCount==1 ) ){
                $method["online_StripeACH"]="StripeACH";
            }
            if(($Account->PaymentMethod == 'Paypal') && (is_paypal($Account->CompanyID)  ) ){
                $method["online_Paypal"]=cus_lang("CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_PAYMENT_METHOD_DDL_PAYPAL");
            }
            if(($Account->PaymentMethod == 'SagePay') && (is_sagepay($Account->CompanyID)  ) ){
                $method["online_SagePay"]="SagePay";
            }
        }else{
            /*$method = array_merge($method, array(
                'CASH'=>cus_lang("CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_PAYMENT_METHOD_DDL_CASH"),
//                    'PAYPAL'=>cus_lang("CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_PAYMENT_METHOD_DDL_PAYPAL"),
                'CHEQUE'=>cus_lang("CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_PAYMENT_METHOD_DDL_CHEQUE"),
                'CREDIT CARD'=>cus_lang("CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_PAYMENT_METHOD_DDL_CREDIT_CARD"),
                'BANK TRANSFER'=>cus_lang("CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_PAYMENT_METHOD_DDL_BANK_TRANSFER")
            ));*/
            
            if(is_authorize($Account->CompanyId)){
                $method["online_AuthorizeNet"]="AuthorizeNet";
            }
            if(is_Stripe($Account->CompanyID)){
                $method["online_Stripe"]="Stripe";
            }
            if(is_FideliPay($Account->CompanyID)){
                $method["online_FideliPay"]="FideliPay";
            }
            if(is_StripeACH($Account->CompanyID) && $StripeACHCount==1){
                $method["online_StripeACH"]="StripeACH";
            }
            if(is_paypal($Account->CompanyID)){
                $method["online_Paypal"]=cus_lang("CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_PAYMENT_METHOD_DDL_PAYPAL");
            }
            if(is_sagepay($Account->CompanyID)){
                $method["online_SagePay"]="SagePay";
            }

        }

        $CurrencyCode 		= 	!empty($Currency) ? $Currency->Code : '';

        $paypal_button = $sagepay_button = "";
        $paypal = new PaypalIpn($Account->CompanyID);
        if(!empty($paypal->status)){
            $paypal->item_title =  Company::getName($Account->CompanyID);
            $paypal->item_number =  0;
            $paypal->curreny_code =  $CurrencyCode;
            $paypal->amount = 0;
            $paypal_button = $paypal->get_paynow_button($Account->AccountID, 0, url('/customer/payments'), url('/customer/payments'));
        }
        if ( (new SagePay($Account->CompanyID))->status()) {
            $SagePay = new SagePay($Account->CompanyID);
            $SagePay->item_title =  Company::getName($Account->CompanyID);
            $SagePay->item_number =  0;
            $SagePay->curreny_code =  $CurrencyCode;
            $SagePay->amount = 0;
            $sagepay_button = $SagePay->get_paynow_button($Account->AccountID, 0);
        }

        $action = array(''=>cus_lang("DROPDOWN_OPTION_SELECT"),'Payment In'=>cus_lang("CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_ACTION_DDL_PAYMENT_OUT"),'Payment Out'=>cus_lang("CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_ACTION_DDL_PAYMENT_IN"));
        $status = array(''=>cus_lang("DROPDOWN_OPTION_SELECT"),'Pending Approval'=>'Pending Approval','Approved'=>'Approved','Rejected'=>'Rejected');
        $AccountDetailsID = AccountDetails::where('AccountID',$Account->AccountID)->pluck('CustomerPaymentAdd');
        return View::make('customer.payments.index', compact('id','currency','method','type','status','action','Account','AccountDetailsID', 'paypal_button', 'sagepay_button'));
	}

    /**
     * Show the form for creating a new resource.
     * GET /payments/create
     *
     * @return Response
     */
    public function create()
    {
        $isvalid = Payment::validate();
        if($isvalid['valid']==1) {
            $save = $isvalid['data'];
            unset($save['Currency']);
            $save['Status'] = 'Approved';
            if(isset($save['InvoiceNo'])) {
                $save['InvoiceID'] = (int)Invoice::where(array('FullInvoiceNumber'=>$save['InvoiceNo'],'AccountID'=>$save['AccountID']))->pluck('InvoiceID');
            }
            if (Payment::create($save)) {
                $companyID = Customer::get_companyID();
                $PendingApprovalPayment = Notification::getNotificationMail(Notification::PendingApprovalPayment,$companyID);

                $PendingApprovalPayment = explode(',', $PendingApprovalPayment);
                $data['EmailToName'] = Company::getName($companyID);
                $save['AccountName'] = Customer::get_user_full_name();
                $data['Subject']= Customer::get_accountName().' Payment verification';
                $data['data'] = $save;
                $data['data']['Currency'] = Currency::getCurrencyCode($save['CurrencyID']);
                $data['data']['AccountName'] = Customer::get_accountName();
                $data['data']['CreatedBy'] = Customer::get_accountName();
                foreach($PendingApprovalPayment as $billingemail){
                    $billingemail = trim($billingemail);
                    if(filter_var($billingemail, FILTER_VALIDATE_EMAIL)) {
                        $data['EmailTo'] = $billingemail;
                        $status = sendMail('emails.admin.payment', $data);
                    }
                }
                /*
                $resource = DB::table('tblResourceCategories')->select('ResourceCategoryID')->where([ "ResourceCategoryName"=>'BillingAdmin',"CompanyID" => $companyID])->first();
                $userid=[];
                if(!empty($resource->ResourceCategoryID)){
                    $permission = DB::table('tblUserPermission')->where([ "AddRemove"=>'add',"CompanyID" => $companyID, "resourceID" => $resource->ResourceCategoryID])->get();
                    if(count($permission)>0){
                        foreach($permission as $pr){
                            $userid[]=$pr->UserID;
                        }
                    }
                }
                $billingadminemails = User::where(["CompanyID" => $companyID, "Status" => 1])->whereIn('UserID', $userid)->get(['EmailAddress']);
                foreach($billingadminemails as $billingadminemail){
                    $billingadminemail = trim($billingadminemail);
                    if(filter_var($billingadminemail->EmailAddress, FILTER_VALIDATE_EMAIL)) {
                        $data['EmailTo'] = $billingadminemail->EmailAddress;
                        $status = sendMail('emails.admin.payment', $data);
                    }
                }
                */

                //@TODO: swipe required - multiLanguage
                $message = isset($status['message'])?' and '.$status['message']:'';
                return Response::json(array("status" => "success", "message" => cus_lang("CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MSG_PAYMENT_SUCCESSFULLY_CREATED")));
            } else {
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MSG_PROBLEM_CREATING_PAYMENT')));
            }
        }else{
            return $isvalid['message'];
        }
    }

    /** not in use **/
    public function exports() {
        $CompanyID = Customer::get_companyID();

        $data = Input::all();
		$data['recall_on_off'] = 0;
        $data['iDisplayStart'] +=1;
        $data['AccountID'] = Customer::get_accountID();
        $data['InvoiceNo']=$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        $data['Status'] = 'Approved';
        $data['type'] = $data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] = $data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
        $columns = array('AccountName','InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy');
        $sort_column = $columns[$data['iSortCol_0']];
		$data['p_paymentstart']			 =		'null';		
		$data['p_paymentend']			 =		'null';
        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",'',".$data['Status'].",".$data['type'].",".$data['paymentmethod'].",".$data['recall_on_off'].",0,".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".$data['p_paymentstart'].",".$data['p_paymentend'].",1,0,'')";

        $excel_data  = DB::connection('sqlsrv2')->select($query);
        $excel_data = json_decode(json_encode($excel_data),true);
        Excel::create('Payments', function ($excel) use ($excel_data) {
            $excel->sheet('Payments', function ($sheet) use ($excel_data) {
                $sheet->fromArray($excel_data);
            });
        })->download('xls');
    }

    public function ajax_datagrid_total(){
        $data = Input::all();
        $CompanyID = Customer::get_companyID();
        $data['iDisplayStart'] 		 =	0;
        $data['iDisplayStart'] 		+=	1;
        $data['iSortCol_0']			 =  0;
        $data['sSortDir_0']			 =  'desc';
        $data['AccountID'] = Customer::get_accountID();
        $data['InvoiceNo']=$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        //$data['Status'] = 'NULL';
        $data['Status'] = 'Approved';
        $data['type'] = $data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] = $data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
        $data['p_paymentstartdate'] 	 = 		$data['PaymentDate_StartDate']!=''?"".$data['PaymentDate_StartDate']."":'null';
        $data['p_paymentstartTime'] 	 = 		$data['PaymentDate_StartTime']!=''?"".$data['PaymentDate_StartTime']."":'00:00:00';
        $data['p_paymentenddate'] 	 	 = 		$data['PaymentDate_EndDate']!=''?"".$data['PaymentDate_EndDate']."":'null';
        $data['p_paymentendtime'] 	 	 = 		$data['PaymentDate_EndTime']!=''?"".$data['PaymentDate_EndTime']."":'00:00:00';
        $data['p_paymentstart']			 =		'null';
        $data['p_paymentend']			 =		'null';

        if($data['p_paymentstartdate']!='' && $data['p_paymentstartdate']!='null' && $data['p_paymentstartTime']!='')
        {
            $data['p_paymentstart']		=	"'".$data['p_paymentstartdate'].' '.$data['p_paymentstartTime']."'";
        }

        if($data['p_paymentenddate']!='' && $data['p_paymentenddate']!='null' && $data['p_paymentendtime']!='')
        {
            $data['p_paymentend']			=	"'".$data['p_paymentenddate'].' '.$data['p_paymentendtime']."'";
        }

        if($data['p_paymentstart']!='null' && $data['p_paymentend']=='null')
        {
            $data['p_paymentend'] 			= 	"'".date("Y-m-d H:i:s")."'";
        }

        // $data['recall_on_off'] = isset($data['recall_on_off'])?($data['recall_on_off']== 'true'?1:0):0;
        $data['recall_on_off'] = 0;

        $account                     = Account::find($data['AccountID']);
        $CurrencyId                  = $account->CurrencyId;
        $accountCurrencyID 		     = empty($CurrencyId)?'0':$CurrencyId;

        $columns = array('InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy');
        $sort_column = $columns[$data['iSortCol_0']];

        //AccountManager Condition
        $userID = 0;

        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",'','".$data['Status']."',".$data['type'].",".$data['paymentmethod'].",".$data['recall_on_off'].",".$accountCurrencyID.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',1,".$data['p_paymentstart'].",".$data['p_paymentend'].",0,".$userID.",'')";

        $result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('ResultCurrentPage','Total_grand_field'));
        $result2  = $result['data']['Total_grand_field'][0]->total_grand;
        $result4  = array(
            "total_grand"=>$result['data']['Total_grand_field'][0]->total_grand,
           // "os_pp"=>$result['data']['Total_grand_field'][0]->first_amount.' / '.$result['data']['Total_grand_field'][0]->second_amount,
        );

        return json_encode($result4,JSON_NUMERIC_CHECK);

    }

    public function  download_doc($id){
        $FileName = Payment::where(["PaymentID"=>$id])->pluck('PaymentProof');
        $CompanyID = Payment::where(["PaymentID"=>$id])->pluck('CompanyID');
        log::info('FileName '.$FileName);
        $FilePath =  AmazonS3::preSignedUrl($FileName,$CompanyID);
        log::info('filepath '.$FilePath);
        download_file($FilePath);
        exit;
    }

}