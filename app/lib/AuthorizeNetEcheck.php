<?php
/**
 * Created by PhpStorm.
 * User: CodeDesk
 * Date: 8/22/2015
 * Time: 12:57 PM
 */
/*$isSandbox = getenv('AUTHORIZENET_SANDBOX');
if($isSandbox == 1){
    define("AUTHORIZENET_SANDBOX", true);
}else{
    define("AUTHORIZENET_SANDBOX", false);
}
define("AUTHORIZENET_API_LOGIN_ID", getenv('AUTHORIZENET_API_LOGIN_ID'));
define("AUTHORIZENET_TRANSACTION_KEY", getenv('AUTHORIZENET_TRANSACTION_KEY'));*/

class AuthorizeNetEcheck {

    public $request;

    function __Construct($CompanyID=0){
		
		$AuthorizeData 						= 	SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$AuthorizeSlug,$CompanyID);
		if($AuthorizeData){	
			$AUTHORIZENET_API_LOGIN_ID  	= 	isset($AuthorizeData->AuthorizeLoginID)?$AuthorizeData->AuthorizeLoginID:'';		
			$AUTHORIZENET_TRANSACTION_KEY  	= 	isset($AuthorizeData->AuthorizeTransactionKey)?$AuthorizeData->AuthorizeTransactionKey:'';
			$isSandbox						=	isset($AuthorizeData->AuthorizeTestAccount)?$AuthorizeData->AuthorizeTestAccount:'';

			define("AUTHORIZENET_API_LOGIN_ID", $AUTHORIZENET_API_LOGIN_ID);
			define("AUTHORIZENET_TRANSACTION_KEY", $AUTHORIZENET_TRANSACTION_KEY);
			
			if($isSandbox == 1){
				define("AUTHORIZENET_SANDBOX", true);
			}else{
				define("AUTHORIZENET_SANDBOX", false);
			}		
		}
        $this->request = new AuthorizeNetCIM();
    }

    public function CreateAuthorizeCustomerProfile($data){
        try{
            $customerProfile = new AuthorizeNetCustomer();
            $customerProfile->description = htmlspecialchars($data["description"]);
            $customerProfile->merchantCustomerId = $data["CustomerId"];
            $customerProfile->email = $data["email"];
            $response = $this->request->createCustomerProfile($customerProfile); 
            if (($response != null) && ($response->getResultCode() == "Ok") ) {
                $result["status"] = "success";
                $result["message"] = cus_lang("PAYMENT_MSG_CUSTOMER_PROFILE_CREATED_ON_AUTHORIZE_NET");
                $result["ID"] = (int) $response->getCustomerProfileId();
            }else{

                /**
                 * When duplicate entry...
                 */
                if($response->getMessageCode()== 'E00039'){
                    $result["status"] = "success";
                    $result["ID"] = filter_var($response->getMessageText(), FILTER_SANITIZE_NUMBER_INT);
                }else{

                    $result["status"] = "failed";
                    $result["message"] = $response->getMessageText();
                }
            }
            return $result;
        }catch(Exception $ex){
            $ex->getMessage();
            $result["status"] = "failed";
            $result["message"] = $ex->getMessage();
            return $result;
        }
    }

    function UpdateProfile($ProfileID,$data){
        try{
            $customerProfile = new AuthorizeNetCustomer();
            $customerProfile->description = $data["description"];
            $customerProfile->merchantCustomerId = $data["CustomerId"];
            $customerProfile->email = $data["email"];
            $response = $this->request->createCustomerProfile($customerProfile);
            if (($response != null) && ($response->getResultCode() == "Ok") ) {
                $result["status"] = "success";
                $result["message"] = cus_lang("PAYMENT_MSG_CUSTOMER_PROFILE_CREATED_ON_AUTHORIZE_NET");
                $result["ID"] = $response->xml->customerProfileId;
            }else{
                $result["status"] = "failed";
                $result["message"] = $response->xml->messages->message->text;
            }
            return $result;
        }catch(Exception $ex){
            $ex->getMessage();
            $result["status"] = "failed";
            $result["message"] = $ex->getMessage();
            return $result;
        }
    }

    public function deleteAuthorizeProfile($ProfileID){
        try{
            $response = $this->request->deleteCustomerProfile($ProfileID);
            if (($response != null) && ($response->getResultCode() == "Ok") ) {
                $result["status"] = "success";
                $result["message"] = cus_lang("PAYMENT_MSG_CUSTOMER_PROFILE_DELETED_ON_AUTHORIZE_NET");
                $result["ID"] = $response->xml->customerProfileId;
            }else{
                $result["status"] = "failed";
                $result["message"] = $response->xml->messages->message->text;
            }
            return $result;
        }catch(Exception $ex){
            $ex->getMessage();
            $result["status"] = "failed";
            $result["message"] = $ex->getMessage();
            return $result;
        }
    }

    public function CreatePaymentProfile($customerProfileId,$data){
        try{
            $data["ExpirationDate"] = $data["ExpirationYear"]."-".$data["ExpirationMonth"];
            $paymentProfile = new AuthorizeNetPaymentProfile;
            $paymentProfile->customerType = "individual";
            $paymentProfile->payment->creditCard->cardNumber = $data["CardNumber"];
            $paymentProfile->payment->creditCard->expirationDate = $data["ExpirationDate"]; 
            $response = $this->request->createCustomerPaymentProfile($customerProfileId, $paymentProfile);
            if (($response != null) && ($response->getResultCode() == "Ok") ) {
                $result["status"] = "success";
                $result["message"] = cus_lang("PAYMENT_MSG_PAYMENT_PROFILE_CREATED_ON_AUTHORIZE_NET");
                $result["ID"] = (int) $response->xml->customerPaymentProfileId;
            }
            else {
                $result["status"] = "failed";
                $result["message"] = $response->xml->messages->message->text;
            }
            return $result;
        }catch(Exception $ex){
            $ex->getMessage();
            $result["status"] = "failed";
            $result["message"] = $ex->getMessage();
            return $result;
        }
    }

    function UpdatePaymentProfile($customerProfileId,$paymentProfileId,$data){
        try{
            $data["ExpirationDate"] = $data["ExpirationYear"]."-".$data["ExpirationMonth"];
            $paymentProfile = new AuthorizeNetPaymentProfile;
            $paymentProfile->customerType = "individual";
            $paymentProfile->payment->creditCard->cardNumber = $data["CardNumber"];
            $paymentProfile->payment->creditCard->expirationDate = $data["ExpirationDate"];
            $response = $this->request->updateCustomerPaymentProfile($customerProfileId,$paymentProfileId,$paymentProfile);
            if (($response != null) && ($response->getResultCode() == "Ok") ) {
                $result["status"] = "success";
                $result["message"] = cus_lang("PAYMENT_MSG_PAYMENT_PROFILE_CREATED_ON_AUTHORIZE_NET");
                $result["ID"] = (int) $response->xml->customerPaymentProfileId;
            }
            else {
                $result["status"] = "failed";
                $result["message"] = $response->xml->messages->message->text;
            }
            return $result;
        }catch(Exception $ex){
            $ex->getMessage();
            $result["status"] = "failed";
            $result["message"] = $ex->getMessage();
            return $result;
        }
    }

    public function deletePaymentProfile($customerProfileId,$paymentProfileId){
        try{
            $response = $this->request->deleteCustomerPaymentProfile($customerProfileId,$paymentProfileId);
            if (($response != null) && ($response->getResultCode() == "Ok") ) {
                $result["status"] = "success";
                $result["message"] = cus_lang("PAYMENT_MSG_PAYMENT_PROFILE_DELETED_ON_AUTHORIZE_NET");
                $result["code"] = $response->xml->messages->message->code;
                $result["ID"] = (int) $response->xml->customerPaymentProfileId;
            }
            else {
                $result["status"] = "failed";
                $result["code"] = $response->xml->messages->message->code;
                $result["message"] = $response->xml->messages->message->text;
            }
            return $result;
        }catch(Exception $ex){
            $ex->getMessage();
            $result["status"] = "failed";
            $result["message"] = $ex->getMessage();
            return $result;
        }
    }

    public function CreatShippingAddress($customerProfileId,$data){
        try{
            $address = new AuthorizeNetAddress;
            $address->firstName = $data['firstName'];
            $address->lastName = $data['lastName'];
            $address->address = $data['address'];
            $address->city = $data['city'];
            $address->state = $data['state'];
            $address->zip = $data['zip'];
            $address->country = $data['country'];
            $address->phoneNumber = $data['phoneNumber'];
            $response = $this->request->createCustomerShippingAddress($customerProfileId, $address);
            if (($response != null) && ($response->getResultCode() == "Ok") ) {
                $result["status"] = "success";
                $result["message"] = cus_lang("PAYMENT_MSG_SHIPPING_ADDRESS_CREATED_ON_AUTHORIZE_NET");
                $result["ID"] = (int) $response->getCustomerAddressId();
            }
            else {

                /**
                 * When duplicate entry...
                 */
                if($response->getMessageCode()== 'E00039'){
                    $result["status"] = "success";
                    $result["ID"] = filter_var($response->getMessageText(), FILTER_SANITIZE_NUMBER_INT);
                }else{

                    $result["status"] = "failed";
                    $result["message"] = $response->xml->messages->message->text;
                }
            }
            return $result;
        }catch(Exception $ex){
            $ex->getMessage();
            $result["status"] = "failed";
            $result["message"] = $ex->getMessage();
            return $result;
        }
    }

    function UpdateShippingAddress($customerProfileId,$customerShippingAddressId,$data){
        try{
            $address = new AuthorizeNetAddress;
            $address->firstName = $data['firstName'];
            $address->lastName = $data['lastName'];
            $address->address = $data['address'];
            $address->city = $data['city'];
            $address->state = $data['state'];
            $address->zip = $data['zip'];
            $address->country = $data['country'];
            $address->phoneNumber = $data['phoneNumber'];
            $response = $this->request->updateCustomerShippingAddress($customerProfileId,$customerShippingAddressId, $address);
            if (($response != null) && ($response->getResultCode() == "Ok") ) {
                $result["status"] = "success";
                $result["message"] = cus_lang("PAYMENT_MSG_SHIPPING_ADDRESS_UPDATED_ON_AUTHORIZE_NET");
                $result["ID"] = (int) $response->xml->customerAddressId;
            }
            else {
                $result["status"] = "failed";
                $result["message"] = $response->xml->messages->message->text;
            }
            return $result;
        }catch(Exception $ex){
            $ex->getMessage();
            $result["status"] = "failed";
            $result["message"] = $ex->getMessage();
            return $result;
        }
    }

    function deleteShippingAddress($customerProfileId,$customerShippingAddressId){
        try{
            $response = $this->request->deleteCustomerShippingAddress($customerProfileId,$customerShippingAddressId);
            if (($response != null) && ($response->getResultCode() == "Ok") ) {
                $result["status"] = "success";
                $result["message"] = cus_lang("PAYMENT_MSG_SHIPPING_ADDRESS_DELETED_ON_AUTHORIZE_NET");
                $result["ID"] = (int) $response->xml->customerAddressId;
            }
            else {
                $result["status"] = "failed";
                $result["message"] = $response->xml->messages->message->text;
            }
            return $result;
        }catch(Exception $ex){
            $ex->getMessage();
            $result["status"] = "failed";
            $result["message"] = $ex->getMessage();
            return $result;
        }
    }
	public function addAuthorizeNetTransaction($amount, $options)
    {
        $transaction = new \AuthorizeNetTransaction();
        $request = new \AuthorizeNetCIM();
        $transaction->amount = $amount;
        $transaction->customerProfileId = $options->ProfileID;
        $transaction->order->invoiceNumber = $options->InvoiceNumber;
        $transaction->customerPaymentProfileId = $options->PaymentProfileID;

        $response = $request->createCustomerProfileTransaction("AuthCapture", $transaction);
		$transactionResponse = $response->getTransactionResponse();
		$transactionResponse->real_response = $response;
		
        return $transactionResponse;
    }
    public function pay_invoice($data){
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
                'amount' => $data['GrandTotal'],
                'card_num' => $data['CardNumber'],
                'exp_date' => $data['ExpirationMonth'].'/'.$data['ExpirationYear'],
                'card_code' => $data['CVVNumber'],
                'invoice_num' => $data['InvoiceNumber'],
                //'first_name' => $data['FirstName'],
                //'last_name' => $data['LastName'],
                //'address' => $data['Address'],
                //'city' => $data['City'],
                //'state' => $data['State'],
                //'country' => $data['Country'],
                //'zip' => $data['Zip'],
                //'email' => $data['Email'],

            )
        );
        $response = $sale->authorizeAndCapture();
        //Log::info($response);
        return $response; 
    }

    /**
     * get a customer profile
     * @param $customerProfileId
     */
    public function getCustomerProfile($customerProfileId) {

        $response  = $this->request->getCustomerProfile($customerProfileId);
        if ($response->getResultCode() !='Error'){
            return $response->getCustomerProfileId();
        }
        return false;
    }

    public function doValidation($data){
        $ValidationResponse = array();
        $rules = array(
            'CardNumber' => 'required|digits_between:13,19',
            'ExpirationMonth' => 'required',
            'ExpirationYear' => 'required',
            'NameOnCard' => 'required',
            'CVVNumber' => 'required',
            //'Title' => 'required|unique:tblAutorizeCardDetail,NULL,CreditCardID,CompanyID,'.$CompanyID
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $errors = "";
            foreach ($validator->messages()->all() as $error){
                $errors .= $error."<br>";
            }

            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = $errors;
            return $ValidationResponse;
        }
        if (date("Y") == $data['ExpirationYear'] && date("m") > $data['ExpirationMonth']) {

            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = cus_lang("PAYMENT_MSG_MONTH_MUST_BE_AFTER") . date("F");
            return $ValidationResponse;
        }
        $card = CreditCard::validCreditCard($data['CardNumber']);
        if ($card['valid'] == 0) {
            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = cus_lang("PAYMENT_MSG_ENTER_VALID_CARD_NUMBER");
            return $ValidationResponse;
        }

        $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
    }

    public function createProfile($data){
        $ProfileID = "";
        $ShippingProfileID = "";
        $first = 0;

        $CustomerID = $data['AccountID'];
        $CompanyID = $data['CompanyID'];
        $PaymentGatewayID=$data['PaymentGatewayID'];

        $PaymentProfile = AccountPaymentProfile::where(['AccountID' => $CustomerID])
            ->where(['CompanyID' => $CompanyID])
            ->where(['PaymentGatewayID' => $PaymentGatewayID])
            ->first();
        if (!empty($PaymentProfile)) {
            $options = json_decode($PaymentProfile->Options);
            $ProfileID = $options->ProfileID;
            $ShippingProfileID = $options->ShippingProfileID;
        }
        $account = Account::where(array('AccountID' => $CustomerID))->first();

        $response = $this->getCustomerProfile($ProfileID);
        if(empty($ProfileID)){
            $first = 1;
        }
        if ($response == false || empty($ProfileID)) {
            $profile = array('CustomerId' => $CustomerID, 'email' => $account->BillingEmail, 'description' => $account->AccountName);
            $result = $this->CreateAuthorizeCustomerProfile($profile);
            if ($result["status"] == "success") {
                $ProfileID = $result["ID"];
                //$ProfileID = json_decode(json_encode($ProfileID), true)[0];
                $shipping = array('firstName' => $account->FirstName,
                    'lastName' => $account->LastName,
                    'address' => $account->Address1,
                    'city' => $account->City,
                    'state' => $account->state,
                    'zip' => $account->PostCode,
                    'country' => $account->Country,
                    'phoneNumber' => $account->Mobile);
                $result = $this->CreatShippingAddress($ProfileID, $shipping);
                $ShippingProfileID = $result["ID"];
            } else {
                return Response::json(array("status" => "failed", "message" => (array)$result["message"]));
            }
        }
        $title = $data['Title'];
        $result = $this->CreatePaymentProfile($ProfileID, $data);
        if ($result["status"] == "success") {
            $PaymentProfileID = $result["ID"];
            /**  @TODO save this field NameOnCard and CCV */
            $option = array(
                'ProfileID' => $ProfileID,
                'ShippingProfileID' => $ShippingProfileID,
                'PaymentProfileID' => $PaymentProfileID
            );
            $CardDetail = array('Title' => $title,
                'Options' => json_encode($option),
                'Status' => 1,
                'isDefault' => $first,
                'created_by' => Customer::get_accountName(),
                'CompanyID' => $CompanyID,
                'AccountID' => $CustomerID,
                'PaymentGatewayID' => $PaymentGatewayID);
            if (AccountPaymentProfile::create($CardDetail)) {
                return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_SUCCESSFULLY_CREATED")));
            } else {
                return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_SAVING_PAYMENT_METHOD_PROFILE")));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => (array)$result["message"]));
        }
    }

    public function deleteProfile($data){
        $AccountID = $data['AccountID'];
        $CompanyID = $data['CompanyID'];
        $AccountPaymentProfileID=$data['AccountPaymentProfileID'];

        $count = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
        $PaymentProfile = AccountPaymentProfile::find($AccountPaymentProfileID);
        if(!empty($PaymentProfile)){
            $options = json_decode($PaymentProfile->Options);
            $ProfileID = $options->ProfileID;
            $PaymentProfileID = $options->PaymentProfileID;
            $isDefault = $PaymentProfile->isDefault;
        }else{
            return Response::json(array("status" => "failed", "message" => cus_lang("MESSAGE_RECORD_NOT_FOUND")));
        }
        if($isDefault==1){
            if($count!=1){
                return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_NOT_DELETE_DEFAULT_PROFILE")));
            }
        }
        $result = $this->DeletePaymentProfile($ProfileID,$PaymentProfileID);
        if($result["status"]=="success"){
            if ($PaymentProfile->delete()) {
                if($count==1){
                    $result =  $this->deleteAuthorizeProfile($ProfileID);
                    if($result["status"]=="success"){
                        return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_DELETED")));
                    }
                }else{
                    return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_DELETED_AUTHORIZE_NET")));
                }
            } else {
                return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_DELETING_PAYMENT_METHOD_PROFILE")));
            }
        }elseif($result["code"]=='E00040'){
            if ($PaymentProfile->delete()) {
                return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_DELETED_AUTHORIZE_NET")));
            }else{
                return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_DELETING_PAYMENT_METHOD_PROFILE")));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => (array)$result["message"]));
        }
    }

    public function paymentWithProfile($data){
        $account = Account::find($data['AccountID']);
        $AccountPaymentProfileID = $data['AccountPaymentProfileID'];
        $CustomerProfile = AccountPaymentProfile::find($AccountPaymentProfileID);
        $StripeObj = json_decode($CustomerProfile->Options);
        $StripeObj->InvoiceNumber = $data['InvoiceNumber'];

        $transaction = $this->addAuthorizeNetTransaction($data['outstanginamount'],$StripeObj);
        $Notes = '';
        if($transaction->response_code == 1) {
            $Notes = 'AuthorizeNet transaction_id ' . $transaction->transaction_id;
            $Status = TransactionLog::SUCCESS;
        }else{
            $Status = TransactionLog::FAILED;
            $Notes = isset($transaction->real_response->xml->messages->message->text) && $transaction->real_response->xml->messages->message->text != '' ? $transaction->real_response->xml->messages->message->text : $transaction->error_message ;
            AccountPaymentProfile::setProfileBlock($AccountPaymentProfileID);
        }
        $transactionResponse['transaction_notes'] =$Notes;
        $transactionResponse['response_code'] = $transaction->response_code;
        $transactionResponse['PaymentMethod'] = 'CREDIT CARD';
        $transactionResponse['failed_reason'] =$transaction->response_reason_text!='' ? $transaction->response_reason_text : $Notes;
        $transactionResponse['transaction_id'] = $transaction->transaction_id;
        $transactionResponse['Response'] = $transaction;

        $transactiondata = array();
        $transactiondata['CompanyID'] = $account->CompanyId;
        $transactiondata['AccountID'] = $account->AccountID;
        $transactiondata['Transaction'] = $transaction->transaction_id;
        $transactiondata['Notes'] = $Notes;
        $transactiondata['Amount'] = floatval($transaction->amount);
        $transactiondata['Status'] = $Status;
        $transactiondata['created_at'] = date('Y-m-d H:i:s');
        $transactiondata['updated_at'] = date('Y-m-d H:i:s');
        $transactiondata['CreatedBy'] = $data['CreatedBy'];
        $transactiondata['ModifyBy'] = $data['CreatedBy'];
        $transactiondata['Response'] = json_encode($transaction);
        TransactionLog::insert($transactiondata);
        return $transactionResponse;

    }

    public function paymentValidateWithCreditCard($data){
        return $this->doValidation($data);
    }

    public function paymentWithCreditCard($data){
        $AuthorizeResponse = $this->pay_invoice($data);
        $Notes = '';
        if($AuthorizeResponse->response_code == 1) {
            $Notes = 'AuthorizeNet transaction_id ' . $AuthorizeResponse->transaction_id;
        }else{
            $Notes = isset($AuthorizeResponse->response->xml->messages->message->text) && $AuthorizeResponse->response->xml->messages->message->text != '' ? $AuthorizeResponse->response->xml->messages->message->text : $AuthorizeResponse->response_reason_text ;
        }

        $Response = array();

        if($AuthorizeResponse->approved) {
            $Response['PaymentMethod'] = $AuthorizeResponse->method;;
            $Response['transaction_notes'] = $Notes;
            $Response['Amount'] = floatval($AuthorizeResponse->amount);
            $Response['Transaction'] = $AuthorizeResponse->transaction_id;
            $Response['Response'] = $AuthorizeResponse;
            $Response['status'] = 'success';
        }else{
            $Response['transaction_notes'] = $Notes;
            $Response['status'] = 'failed';
            $Response['Response']=json_encode($AuthorizeResponse);
        }
        return $Response;
    }

    public function paymentValidateWithApiCreditCard($data){
        return $this->doValidation($data);
    }

    public function paymentWithApiCreditCard($data){
        $data['InvoiceNumber']='';
        $AuthorizeResponse = $this->pay_invoice($data);
        $Notes = '';
        if($AuthorizeResponse->response_code == 1) {
            $Notes = 'AuthorizeNet transaction_id ' . $AuthorizeResponse->transaction_id;
        }else{
            $Notes = isset($AuthorizeResponse->response->xml->messages->message->text) && $AuthorizeResponse->response->xml->messages->message->text != '' ? $AuthorizeResponse->response->xml->messages->message->text : $AuthorizeResponse->response_reason_text ;
        }

        $Response = array();

        if($AuthorizeResponse->approved) {
            $Response['PaymentMethod'] = $AuthorizeResponse->method;
            $Response['transaction_notes'] = $Notes;
            $Response['Amount'] = floatval($AuthorizeResponse->amount);
            $Response['Transaction'] = $AuthorizeResponse->transaction_id;
            $Response['Response'] = $AuthorizeResponse;
            $Response['status'] = 'success';
        }else{
            $Response['transaction_notes'] = $Notes;
            $Response['status'] = 'failed';
            $Response['Response']=json_encode($AuthorizeResponse);
        }
        return $Response;
    }

    public function pay_echeckinvoice($data){
        $sale = new AuthorizeNetAIM;
        log::info('pcheck start');
        log::info(print_r($data,true));
        $bank_acct_name=$data['BankAccountName'];
        $bank_name=$data['BankName'];
        $bank_acct_num=$data['AccountNumber'];
        $bank_aba_code=$data['RoutingNumber'];

        $bank_acct_type='CHECKING';
        $echeck_type = 'WEB';
        $sale->setField('amount',$data['GrandTotal']);
        $sale->setField('invoice_num',$data['InvoiceNumber']);
        $sale->setECheck($bank_aba_code, $bank_acct_num, $bank_acct_type, $bank_name, $bank_acct_name, $echeck_type);
        /*
        $sale->setFields(
            array(
                'amount' => rand(1, 1000),
                'method' => 'echeck',
                'bank_aba_code' => '121042882',
                'bank_acct_num' => '123456789123',
                'bank_acct_type' => 'CHECKING',
                'bank_name' => 'Bank of Earth',
                'bank_acct_name' => 'Jane Doe',
                'echeck_type' => 'WEB',
            )
        );*/
        $response = $sale->authorizeAndCapture();
        log::info('pcheck end');
//log::info(print_r($response,true));        log::info(print_r($response,true));
        $test='{"approved":true,"declined":false,"error":false,"held":false,"response_code":"1","response_subcode":"1","response_reason_code":"1","response_reason_text":"This transaction has been approved.","authorization_code":"T45T7F","avs_response":"Y","transaction_id":"60107039735","invoice_number":"GIRISH1248","description":"","amount":"5.20","method":"ECHECK","transaction_type":"auth_capture","customer_id":"","first_name":"","last_name":"","company":"","address":"","city":"","state":"","zip_code":"","country":"","phone":"","fax":"","email_address":"","ship_to_first_name":"","ship_to_last_name":"","ship_to_company":"","ship_to_address":"","ship_to_city":"","ship_to_state":"","ship_to_zip_code":"","ship_to_country":"","tax":"","duty":"","freight":"","tax_exempt":"","purchase_order_number":"","md5_hash":"58D207E574EB6A173A66A72F6CD2C7F4","card_code_response":"P","cavv_response":"2","account_number":"XXXX1111","card_type":"Visa","split_tender_id":"","requested_amount":"","balance_on_card":"","response":"|1|,|1|,|1|,|This transaction has been approved.|,|T45T7F|,|Y|,|60107039735|,|GIRISH1248|,||,|162.75|,|CC|,|auth_capture|,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,|58D207E574EB6A173A66A72F6CD2C7F4|,|P|,|2|,||,||,||,||,||,||,||,||,||,||,|XXXX1111|,|Visa|,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||"}';
        $response = json_decode($test);
        return $response;
    }

    //
    public function paymentWithBankDetail($data){
        log::info('Pyament doing with echeck start');
        $AuthorizeResponse = $this->pay_echeckinvoice($data);
        $Notes = '';
        if($AuthorizeResponse->response_code == 1) {
            $Notes = 'AuthorizeNet Echeck transaction_id ' . $AuthorizeResponse->transaction_id;
        }else{
            $Notes = isset($AuthorizeResponse->response->xml->messages->message->text) && $AuthorizeResponse->response->xml->messages->message->text != '' ? $AuthorizeResponse->response->xml->messages->message->text : $AuthorizeResponse->response_reason_text ;
        }

        $Response = array();
        log::info(print_r($AuthorizeResponse,true));
        if($AuthorizeResponse->approved) {
            $Response['PaymentMethod'] = $AuthorizeResponse->method;;
            $Response['transaction_notes'] = $Notes;
            $Response['Amount'] = floatval($AuthorizeResponse->amount);
            $Response['Transaction'] = $AuthorizeResponse->transaction_id;
            $Response['Response'] = $AuthorizeResponse;
            $Response['status'] = 'success';
        }else{
            $Response['transaction_notes'] = $Notes;
            $Response['status'] = 'failed';
            $Response['Response']=json_encode($AuthorizeResponse);
        }
        return $Response;
    }

    public function paymentValidateWithBankDetail($data){
        $ValidationResponse = array();
        $rules = array(
            'BankAccountName' => 'required',
            'BankName' => 'required',
            'AccountNumber' => 'required|digits_between:6,19',
            'RoutingNumber' => 'required|digits_between:6,12'
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $errors = "";
            foreach ($validator->messages()->all() as $error){
                $errors .= $error."<br>";
            }

            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = $errors;
            return $ValidationResponse;
        }
        $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
    }

}