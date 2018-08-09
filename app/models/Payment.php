<?php

class Payment extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrv2';
    protected $guarded = array('PaymentID');
    protected $table = 'tblPayment';
    protected  $primaryKey = "PaymentID";

    public static $method = array(''=>'Select ','CASH'=>'CASH','CHEQUE'=>'CHEQUE','CREDIT CARD'=>'CREDIT CARD','BANK TRANSFER'=>'BANK TRANSFER', 'DIRECT DEBIT'=>'DIRECT DEBIT','PAYPAL_IPN'=>"PAYPAL");
    public static $action = array(''=>'Select ','Payment In'=>'Payment In','Payment Out'=>'Payment Out');
    public static $status = array(''=>'Select ','Pending Approval'=>'Pending Approval','Approved'=>'Approved','Rejected'=>'Rejected');
    //public $timestamps = false; // no created_at and updated_at

    public static $credit_card_type = array(
        'American Express'=>'American Express',
        'Australian BankCard'=>'Australian BankCard',
        'Diners Club'=>"Diners Club",
        'Discover'=>'Discover',
        'MasterCard'=>'MasterCard',
        'Visa'=>'Visa',
        "JCB"=>"JCB",
        'IsraCard'=>'IsraCard',
    );
    public static $account_holder_type = array(
        'individual'=>'individual',
        'company'=>'company',
    );
    public static $account_holder_sagepay_type = array(
        '1'=>'Current / Checking',
        '2'=>'Savings',
        '3'=>'Transmission',
        '4'=>'Bond',
    );

    public static $importpaymentrules = array(
        'selection.AccountName' => 'required',
        'selection.PaymentDate'=>'required',
        'selection.PaymentMethod'=>'required',
        'selection.PaymentType'=>'required',
        'selection.Amount'=>'required'
    );

    public static $importpaymentmessages = array(
        'selection.AccountName.required' =>'The Account Name field is required',
        'selection.PaymentDate.required' =>'The Payment Date field is required',
        'selection.PaymentMethod.required' =>'The Payment Method  field is required',
        'selection.PaymentType.required' =>'The Action field is required',
        'selection.Amount.required' =>'The Amount field is required'
    );

    public static function multiLang_init(){
        Payment::$credit_card_type = array(
            'American Express'=>cus_lang("PAGE_PAYMENT_FIELD_CREDIT_CARD_TYPE_DDL_AMERICAN_EXPRESS"),
            'Australian BankCard'=>cus_lang("PAGE_PAYMENT_FIELD_CREDIT_CARD_TYPE_DDL_AUSTRALIAN_BANKCARD"),
            'Diners Club'=>cus_lang("PAGE_PAYMENT_FIELD_CREDIT_CARD_TYPE_DDL_DINERS_CLUB"),
            'Discover'=>cus_lang("PAGE_PAYMENT_FIELD_CREDIT_CARD_TYPE_DDL_DISCOVER"),
            'MasterCard'=>cus_lang("PAGE_PAYMENT_FIELD_CREDIT_CARD_TYPE_DDL_MASTERCARD"),
            'Visa'=>cus_lang("PAGE_PAYMENT_FIELD_CREDIT_CARD_TYPE_DDL_VISA"),
            "JCB"=>cus_lang("PAGE_PAYMENT_FIELD_CREDIT_CARD_TYPE_DDL_JCB"),
            "IsraCard"=>cus_lang("PAGE_PAYMENT_FIELD_CREDIT_CARD_TYPE_DDL_ISRACARD"),
        );
    }

    public static function validate($id=0){
        $valid = array('valid'=>0,'message'=>'Some thing wrong with payment validation','data'=>'');
        $data = Input::all();
        if(isset($data['CustomerPaymentType'])){
            $data['PaymentType'] = $data['CustomerPaymentType'];
            unset($data['CustomerPaymentType']);
        }
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        unset($data['customers']);
        /*if(isset($data['InvoiceNo']) && trim($data['InvoiceNo']) == '' ) {
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please enter invoice number"));
            return $valid;
        }
        $result = Invoice::select('InvoiceNumber')->where('InvoiceNumber','=',$data['InvoiceNo'])->where('CompanyID','=',$companyID)->first();
        if(empty($result)){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Invoice number is not exist in invoices."));
            return $valid;
        }
        if($id>0){
            $result = payment::select('InvoiceNo')->where('InvoiceNo','=',$data['InvoiceNo'])->where('CompanyID','=',$companyID)->where('PaymentID','<>',$id)->first();
            if (!empty($result)) {
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Invoice number already exist in Payments."));
                return $valid;
            }
            $Payment = Payment::findOrFail($id);
        }else{
            $result = payment::select('InvoiceNo')->where('InvoiceNo', '=', $data['InvoiceNo'])->where('CompanyID', '=', $companyID)->first();
            if (!empty($result)) {
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Invoice number already exist in Payments."));
                return $valid;
            }
        }*/
        $data['CurrencyID'] = '';
        $Account = Account::find($data['AccountID']);
        if(!empty($Account)){
            $data['CurrencyID'] = $Account->CurrencyId;

        }
        if(isset($data['AccountID']) && trim($data['AccountID']) == '' ) {
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Account Name from dropdown"));
            return $valid;
        }elseif(isset($data['PaymentDate'])&& trim($data['PaymentDate']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Payment Date"));
            return $valid;
        }elseif(isset($data['PaymentMethod'])&& trim($data['PaymentMethod']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Payment Method from dropdown"));
            return $valid;
        }elseif(isset($data['PaymentType'])&& trim($data['PaymentType']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Payment Type from dropdown"));
            return $valid;
        }elseif(isset($data['CurrencyID'])&& trim($data['CurrencyID']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please set Currency in setting"));
            return $valid;
        }elseif(isset($data['Amount'])&& trim($data['Amount']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please enter Amount"));
            return $valid;
        }elseif(isset($data['Status'])&& trim($data['Status']) == ''){
            if(User::is_admin()){
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Status from dropdown"));
                return $valid;
            }
        }elseif(date('Y-m-d',strtotime($data['PaymentDate'])) >  date('Y-m-d')){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Future payments not allowed"));
            return $valid;
        }
        if (Input::hasFile('PaymentProof')){

            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['PAYMENT_PROOF']);
            $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;

            $proof = Input::file('PaymentProof');
            $ext = $proof->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("pdf",'png','jpg','gif'))) {
                $filename = rename_upload_file($destinationPath,$proof->getClientOriginalName());
                $proof->move($destinationPath,$filename);
                if(!AmazonS3::upload($destinationPath.$filename,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $data['PaymentProof'] = $amazonPath . $filename;
            }else{
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Please Upload file with given extensions."));
                return $valid;
            }

            /*
            $upload_path = CompanyConfiguration::get('PAYMENT_PROOF_PATH');
            $destinationPath = $upload_path.'/SampleUpload/'.Company::getName().'/';
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['PAYMENT_PROOF']) ;
            $proof = Input::file('PaymentProof');
            // ->move($destinationPath);
            $ext = $proof->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("pdf",'png','jpg','gif'))) {
                $filename = rename_upload_file($destinationPath,$proof->getClientOriginalName());
                $fullPath = $destinationPath .$filename;
                $proof->move($destinationPath,$filename);
                if(!AmazonS3::upload($destinationPath.$filename,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $data['PaymentProof'] = $amazonPath . $filename;
            }else{
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Please Upload file with given extensions."));
                return $valid;
            }*/
        }else{
            unset($data['PaymentProof']);
        }

        if($id==0){
            $today = date('Y-m-d H:i:s');
            $data['CreatedBy'] = User::get_user_full_name();
            $data['created_at'] =  $today;
            $data['ModifyBy'] = '';
            $data['updated_at'] =  '';
        }else{
            $today = date('Y-m-d H:i:s');
            $data['ModifyBy'] = User::get_user_full_name();
            $data['updated_at'] =  $today;
        }

        $valid['valid'] = 1;
        $valid['data'] = $data;
        return $valid;
    }


    /*
     * Validate Payments for Bulk Upload and insert data into temp table.
     * */
    public static function validate_payments($data){

        $selection = $data['selection'];
        $file = $data['TemplateFile'];
        $CompanyID = User::get_companyID();
        $where = ['CompanyId'=>$CompanyID,"AccountType"=>1];
        if(User::is("AccountManager") ){
            $where['Owner']=User::get_userID();
        }
        $Accounts = Account::where($where)->select(['AccountName','AccountID'])->lists('AccountID','AccountName');
        $Accounts = array_change_key_case($Accounts);
        if (!empty($file)) {

            $NeonExcel = new NeonExcelIO($file);
            $results = $NeonExcel->read();

            /*$results =  Excel::selectSheetsByIndex(0)->load($file, function ($reader) {})->get();

            $results = json_decode(json_encode($results), true);*/


            $ProcessID =  GUID::generate();

            Log::info("ProcessID " . $ProcessID );
            $lineno = 2;$counter = 0;
            $batch_insert = [];


            $has_Error = false;
            $confirm_show  = false;
            $response_message = "";
            $response_status = "";

            foreach($results as $row){
                $checkemptyrow = array_filter(array_values($row));
                if(!empty($checkemptyrow)){
                    if (empty($row[$selection['AccountName']])) {
                        $response_message  .= ' <br>Account Name is empty at line no' . $lineno;
                        $has_Error = true;
                    }elseif (!array_key_exists(strtolower(trim($row[$selection['AccountName']])), $Accounts)) {
                        $response_message .= " <br>Invalid Account Name '" . $row[$selection['AccountName']] . "' at line no " . $lineno;
                        $has_Error = true;
                    }
                    if (empty($row[$selection['PaymentDate']])) {
                        $response_message .= ' <br>Payment Date is empty at line no ' . $lineno;
                        $has_Error = true;
                    }else{
                        $date = formatSmallDate(str_replace( '/','-',trim($row[$selection['PaymentDate']])),$selection['DateFormat']);
                        if (empty($date)) {
                            $response_message .= '<br>Invalid Payment Date at line no ' . $lineno;
                            $has_Error = true;
                        }else{
                            $row[$selection['PaymentDate']] = $date;
                        }
                    }
                    if (empty($row[$selection['PaymentMethod']])) {
                        $response_message .= ' <br>Payment Method is empty at line no ' . $lineno;
                        $has_Error = true;
                    }elseif (!in_array(strtolower(trim($row[$selection['PaymentMethod']])), array_map('strtolower', Payment::$method))) {
                        $response_message  .= " <br>Invalid Payment Method : '" . $row[$selection['PaymentMethod']] . "' at line no " . $lineno;
                        $has_Error = true;
                    }
                    if (empty($row[$selection['PaymentType']])) {
                        $response_message .= ' <br>Action is empty at line no ' . $lineno;
                        $has_Error = true;
                    }elseif(!in_array(strtolower(trim($row[$selection['PaymentType']])), array_map('strtolower', Payment::$action) )){
                        $response_message  .= " <br>Invalid Action : '".$row[$selection['PaymentType']]."' at line no ".$lineno;
                        $has_Error = true;
                    }

                    if (empty($row[$selection['Amount']])) {
                        $response_message .= ' <br>Amount is empty at line no ' . $lineno;
                        $has_Error = true;
                    }elseif(!is_numeric($row[$selection['Amount']])){
                        $response_message .= ' <br>Invalid Amount at line no ' . $lineno;
                        $has_Error = true;
                    }
                    if (!$has_Error) {
                        $PaymentStatus = 'Pending Approval';
                        if(User::is('BillingAdmin') || User::is_admin()){
                            $PaymentStatus = 'Approved';
                        }
                        $temp = array('CompanyID' => $CompanyID,
                            'ProcessID' => $ProcessID,
                            'AccountID' => $Accounts[strtolower(trim($row[$selection['AccountName']]))],
                            'PaymentDate' => trim(str_replace( '/','-',$row[$selection['PaymentDate']])),
                            'PaymentMethod' => trim(strtoupper($row[$selection['PaymentMethod']])),
                            'PaymentType' => trim(ucfirst($row[$selection['PaymentType']])),
                            'Status' => $PaymentStatus,
                            'Amount' => trim($row[$selection['Amount']])
                        );

                        if(isset($selection['InvoiceNo']) && !empty($selection['InvoiceNo']) ) {
                            if(!empty($row[$selection['InvoiceNo']])){
                                $invnumber = trim($row[$selection['InvoiceNo']]);
                                $temp['InvoiceNo'] = empty($invnumber) ? '' : $invnumber;
                                if(!empty($temp['InvoiceNo'])){
                                    $temp['InvoiceID'] = (int)Invoice::where('FullInvoiceNumber',$invnumber)->pluck('InvoiceID');
                                }else{
                                    $temp['InvoiceID'] = '';
                                }


                            }else{
                                $temp['InvoiceNo'] = '';
                                $temp['InvoiceID'] = '';
                            }
                        }

                        if(isset($selection['Notes']) && !empty($selection['Notes']) ) {
                            if(!empty($row[$selection['Notes']])){
                                $note = trim($row[$selection['Notes']]);
                                $temp['Notes'] = empty($note) ? '' : $note;
                            }else{
                                $temp['Notes'] = '';
                            }
                        }
                        $batch_insert[] = $temp;
                    }
                    $counter++;
                }
                $lineno++;

            } // loop over


            if ( $has_Error ) {

                Log::info("Opps error  " . $response_message );
                $response_status = 'Error';

            } else {

                Log::info("Inserted into PaymentTemp with  Processid = " . $ProcessID );
                $insert_response =  DB::connection('sqlsrv2')->table("tblTempPayment")->insert($batch_insert);
                if (!$insert_response) {
                    $response_message  = 'Some thing wrong with database';
                    $response_status = 'Error';
                }


                if(empty($response_status)) { // when no error
                    //Validate Payment entries and return error

                    $validation_Errors = DB::connection('sqlsrv2')->select("CALL  prc_validatePayments ('" . $CompanyID . "','" . $ProcessID . "')");

                    if (!empty($validation_Errors)) { // if any error.
                        $response_message = $validation_Errors[0]->ErrorMessage;
                        $response_status = 'Error';
                        $confirm_show = true;

                    }else{
                        $response_message = "";
                        $response_status = 'Success';
                    }
                }
            }

            return ["ProcessID" => $ProcessID, "message" => $response_message, "status" => $response_status,'confirmshow'=>$confirm_show];

        } 
    }

    public static function getPaymentByInvoice($InvoiceID){

        $Invoice = Invoice::find($InvoiceID);

        $query 				= 	"CALL `prc_getInvoicePayments`('".$InvoiceID."','".$Invoice->CompanyID."');";
        $result   			=	DataTableSql::of($query,'sqlsrv2')->getProcResult(array('result'));

        $payment_log = array("total"=>$result['data']['result'][0]->total_grand,"paid_amount"=>$result['data']['result'][0]->paid_amount,"due_amount"=>$result['data']['result'][0]->due_amount);

        if($Invoice->InvoiceStatus==Invoice::PAID){
            // full payment done.
            $paymentamount = 0;
        }elseif($Invoice->InvoiceStatus!=Invoice::PAID && $payment_log['paid_amount']>0){
            //partial payment.
            $paymentamount = number_format($payment_log['due_amount'],get_round_decimal_places($Invoice->AccountID),'.','');
        }else {
            $paymentamount = number_format($payment_log['total'],get_round_decimal_places($Invoice->AccountID),'.','');
        }

        $final_log = array("total"=>$result['data']['result'][0]->total_grand,"paid_amount"=>$result['data']['result'][0]->paid_amount,"due_amount"=>$result['data']['result'][0]->due_amount,"final_payment"=>$paymentamount);

        return $final_log;
    }

    public static function paymentSuccess($data){
        $Invoice = Invoice::find($data['InvoiceID']);
        $account = Account::find($data['AccountID']);
        $isInvoicePay=true;
        if(isset($data['isInvoicePay'])){
            $isInvoicePay=$data['isInvoicePay'];
        }
        $paymentdata = array();
        if(!empty($Invoice) && $isInvoicePay){
            $paymentdata['CompanyID'] = $Invoice->CompanyID;
            $paymentdata['AccountID'] = $Invoice->AccountID;
            $paymentdata['InvoiceNo'] = $Invoice->FullInvoiceNumber;
            $paymentdata['InvoiceID'] = (int)$Invoice->InvoiceID;
        }else{
            $paymentdata['CompanyID'] = $account->CompanyId;
            $paymentdata['AccountID'] = $account->AccountID;
            $paymentdata['InvoiceNo'] = '';
            if(!empty($Invoice)){
                $paymentdata['InvoiceID'] = (int)$Invoice->InvoiceID;
            }else{
                $paymentdata['InvoiceID'] = 0;
            }
            if(isset($data['custome_notes'])){
                $data['transaction_notes'] .= " ". $data['custome_notes'];
            }
        }

        $paymentdata['PaymentDate'] = date('Y-m-d H:i:s');
        $paymentdata['PaymentMethod'] = $data['PaymentMethod'];
        $paymentdata['CurrencyID'] = $account->CurrencyId;
        $paymentdata['PaymentType'] = 'Payment In';
        $paymentdata['Notes'] = $data['transaction_notes'];
        $paymentdata['Amount'] = floatval($data['Amount']);
        $paymentdata['Status'] = 'Approved';
        $paymentdata['CreatedBy'] = $data['CreatedBy'];
        $paymentdata['ModifyBy'] = $data['CreatedBy'];
        $paymentdata['created_at'] = date('Y-m-d H:i:s');
        $paymentdata['updated_at'] = date('Y-m-d H:i:s');
        Payment::insert($paymentdata);
        $transactiondata = array();
        if(!empty($Invoice) && $isInvoicePay){
            $transactiondata['CompanyID'] = $Invoice->CompanyID;
            $transactiondata['AccountID'] = $Invoice->AccountID;
            $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
        }else{
            $transactiondata['CompanyID'] = $account->CompanyId;
            $transactiondata['AccountID'] = $account->AccountID;
            if(!empty($Invoice)){
                $transactiondata['InvoiceID'] = (int)$Invoice->InvoiceID;
            }else{
                $transactiondata['InvoiceID'] = 0;
            }
        }

        $transactiondata['Transaction'] = $data['Transaction'];
        $transactiondata['Notes'] = $data['transaction_notes'];
        $transactiondata['Amount'] = floatval($data['Amount']);
        $transactiondata['Status'] = TransactionLog::SUCCESS;
        $transactiondata['created_at'] = date('Y-m-d H:i:s');
        $transactiondata['updated_at'] = date('Y-m-d H:i:s');
        $transactiondata['CreatedBy'] = $data['CreatedBy'];
        $transactiondata['ModifyBy'] = $data['CreatedBy'];
        $transactiondata['Response'] = json_encode($data['Response']);
        TransactionLog::insert($transactiondata);
        if(!empty($Invoice) && $isInvoicePay){
            $Invoice->update(array('InvoiceStatus' => Invoice::PAID));

            $EmailTemplate = EmailTemplate::getSystemEmailTemplate($paymentdata['CompanyID'], EmailTemplate::InvoicePaidNotificationTemplate, $account->LanguageID);
            if(!empty($EmailTemplate) && isset($EmailTemplate->Status) && $EmailTemplate->Status == 1 ){
                $paymentdata['EmailTemplate'] = $EmailTemplate;
                $paymentdata['CompanyName'] 		= 	Company::getName($paymentdata['CompanyID']);
                $paymentdata['Invoice'] = $Invoice;
                Notification::sendEmailNotification(Notification::InvoicePaidByCustomer,$paymentdata);
            }
        }else{
            $companyID = $paymentdata['CompanyID'];
            $PendingApprovalPayment = Notification::getNotificationMail(Notification::PendingApprovalPayment,$companyID);

            $PendingApprovalPayment = explode(',', $PendingApprovalPayment);
            $data=array();
            $data['EmailToName'] = Company::getName($companyID);
            $data['AccountName'] = Customer::get_user_full_name();
            $data['Subject']= Customer::get_accountName().' Payment verification';
            $data['data']['Amount'] = $paymentdata['Amount'];
            $data['data']['PaymentType'] = $paymentdata['PaymentType'];
            $data['data']['PaymentDate'] = $paymentdata['PaymentDate'];
            $data['data']['Notes']= $paymentdata['Notes'];
            $data['data']['Currency'] = Currency::getCurrencyCode($paymentdata['CurrencyID']);
            $data['data']['AccountName'] = Customer::get_accountName();
            $data['data']['CreatedBy'] = $paymentdata['CreatedBy'];
            foreach($PendingApprovalPayment as $billingemail){
                $billingemail = trim($billingemail);
                if(filter_var($billingemail, FILTER_VALIDATE_EMAIL)) {
                    $data['EmailTo'] = $billingemail;
                    sendMail('emails.admin.payment', $data);
                }
            }
        }
    }

    public static function paymentFail($data){
        $transactiondata = array();

        $isInvoicePay=true;
        if(isset($data['isInvoicePay'])){
            $isInvoicePay=$data['isInvoicePay'];
        }

        $Invoice = Invoice::find($data['InvoiceID']);
        if(!empty($Invoice) && $isInvoicePay){
            $transactiondata['CompanyID'] = $Invoice->CompanyID;
            $transactiondata['AccountID'] = $Invoice->AccountID;
            $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
        }else{
            $account = Account::find($data['AccountID']);
            $transactiondata['CompanyID'] = $account->CompanyId;
            $transactiondata['AccountID'] = $account->AccountID;
            if(!empty( $Invoice )){
                $transactiondata['InvoiceID'] =  $Invoice->InvoiceID;
            }else{
                $transactiondata['InvoiceID'] = 0;
            }
            if(isset($data['custome_notes'])){
                $data['transaction_notes'] .= " ". $data['custome_notes'];
            }
        }

        $transactiondata['Transaction'] = '';
        $transactiondata['Notes'] = $data['transaction_notes'];
        $transactiondata['Amount'] = floatval(0);
        $transactiondata['Status'] = TransactionLog::FAILED;
        $transactiondata['created_at'] = date('Y-m-d H:i:s');
        $transactiondata['updated_at'] = date('Y-m-d H:i:s');
        $transactiondata['CreatedBy'] = $data['CreatedBy'];
        $transactiondata['ModifyBy'] = $data['CreatedBy'];
        $transactiondata['Response'] = json_encode($data['Response']);
        TransactionLog::insert($transactiondata);
    }

    public static function paymentList(){
        $paymentsType = array();
        $CompanyID = User::get_companyID();
        if(is_authorize($CompanyID)){
            $paymentsType["AuthorizeNet"]="AuthorizeNet";
        }
        if(is_Stripe($CompanyID)){
            $paymentsType["Stripe"]="Stripe";
        }
        if(is_FideliPay($CompanyID)){
            $paymentsType["FideliPay"]="FideliPay";
        }
        if(is_StripeACH($CompanyID)){
           // $paymentsType["StripeACH"]="StripeACH";
        }
        if(is_paypal($CompanyID)){
            $paymentsType["Paypal"]="Paypal";
        }
        if(is_sagepay($CompanyID)){
            $paymentsType["SagePay"]="SagePay";
        }
        if(is_pelecard($CompanyID)){
            $paymentsType["PeleCard"]="PeleCard";
        }
        return $paymentsType;
    }
}