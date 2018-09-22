<?php
/**
 * Created by PhpStorm.
 * User: Bhavin
 * Date: 26/09/2017
 * Time: 04:30 PM
 */

class FideliPay {

    public $request;
	var $status ;
	var $SourceKey ;
	var $Pin ;
	var $FideliPayUrl ;

    function __Construct($CompanyID=0){
		
		$FideliPayObj 						= 	SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$FideliPaySlug,$CompanyID);
		if($FideliPayObj){	
			$this->SourceKey 	            = 	$FideliPayObj->SourceKey;
            $this->Pin						= 	$FideliPayObj->Pin;
            $FideliPayUrl                   = CompanyConfiguration::get('FIDELIPAY_WSDL_URL',$CompanyID);
            $this->FideliPayUrl				= 	$FideliPayUrl;
			$this->status = true;			
		}else{
			$this->status = false;
		}
        
    }

    public function getClient(){
        //$wsdl='https://sandbox.fidelipay.com/soap/gate/SDPJGLS1/fidelipay.wsdl';

        $wsdl = $this->FideliPayUrl;

        return new SoapClient($wsdl,array("trace"=>1,"exceptions"=>1));
    }

    public function getToken(){
        $sourcekey = $this->SourceKey;
        $pin = $this->Pin;
        $ClientIP = get_client_ip();
        // generate random seed value
        $seed=time() . rand();
        // make hash value using sha1 function
        $clear= $sourcekey . $seed . $pin;
        $hash=sha1($clear);
        // assembly ueSecurityToken as an array
        $token=array(
            'SourceKey'=>$sourcekey,
            'PinHash'=>array(
                'Type'=>'sha1',
                'Seed'=>$seed,
                'HashValue'=>$hash
            ),
            'ClientIP'=>$ClientIP,
        );

        return $token;
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
            $ValidationResponse['message'] = cus_lang("PAYMENT_MSG_MONTH_MUST_BE_AFTER"). date("F");
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

        $CustomerID = $data['AccountID'];
        $CompanyID = $data['CompanyID'];
        $PaymentGatewayID=$data['PaymentGatewayID'];

        $isDefault = 1;
        $count = AccountPaymentProfile::where(['AccountID' => $CustomerID])
            ->where(['CompanyID' => $CompanyID])
            ->where(['PaymentGatewayID' => $PaymentGatewayID])
            ->where(['isDefault' => 1])
            ->count();

        if($count>0){
            $isDefault = 0;
        }

        $FideliPayResponse = $this->createFideliPayProfile($data);
        if ($FideliPayResponse["status"] == "success") {
            $option = array(
                'CustomerNumber' => $FideliPayResponse['CustomerNumber']
            );
            $CardDetail = array('Title' => $data['Title'],
                'Options' => json_encode($option),
                'Status' => 1,
                'isDefault' => $isDefault,
                'created_by' => Customer::get_accountName(),
                'CompanyID' => $CompanyID,
                'AccountID' => $CustomerID,
                'PaymentGatewayID' => $PaymentGatewayID);
            if (AccountPaymentProfile::create($CardDetail)) {
                return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_SUCCESSFULLY_CREATED")));
            } else {
                return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_SAVING_PAYMENT_METHOD_PROFILE")));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => $FideliPayResponse['error']));
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
            $CustomerNumber = $options->CustomerNumber;
            $isDefault = $PaymentProfile->isDefault;
        }else{
            return Response::json(array("status" => "failed", "message" => cus_lang("MESSAGE_RECORD_NOT_FOUND")));
        }
        if($isDefault==1){
            if($count!=1){
                return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_NOT_DELETE_DEFAULT_PROFILE")));
            }
        }

        $result=$this->deleteFideliPayProfile($CustomerNumber);
        if($result["status"]=="success"){
            if($PaymentProfile->delete()) {
                return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_DELETED")));
            } else {
                return Response::json(array("status" => "failed", "message" =>  cus_lang("PAYMENT_MSG_PROBLEM_DELETING_PAYMENT_METHOD_PROFILE")));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => $result['error']));
        }
    }

    public function paymentWithProfile($data){

        $account = Account::find($data['AccountID']);

        $CustomerProfile = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
        $FidelipayObj = json_decode($CustomerProfile->Options);


        $fidelipaydata = array();
        $fidelipaydata['Invoice'] = $data['InvoiceNumber'];
        $fidelipaydata['Amount'] = $data['outstanginamount'];
        $fidelipaydata['Description'] = $data['InvoiceNumber'].' (Invoice) Payment';
        $fidelipaydata['CustomerNumber'] = $FidelipayObj->CustomerNumber;

        $transactionResponse = array();

        $transaction = $this->createchargebycustomer($fidelipaydata);

        $Notes = '';
        if(!empty($transaction['response_code']) && $transaction['response_code'] == 1) {
            $Notes = 'Fidelipay transaction_id ' . $transaction['id'];
            $Status = TransactionLog::SUCCESS;
        }else{
            $Status = TransactionLog::FAILED;
            $Notes = empty($transaction['error']) ? '' : $transaction['error'];
        }

        $transactionResponse['transaction_notes'] =$Notes;

        if(!empty($transaction['response_code'])) {
            $transactionResponse['response_code'] = $transaction['response_code'];
        }
        $transactionResponse['PaymentMethod'] = 'CREDIT CARD';
        $transactionResponse['failed_reason'] = $Notes;
        if(!empty($transaction['id'])) {
            $transactionResponse['transaction_id'] = $transaction['id'];
        }
        $transactionResponse['Response'] = $transaction;

        $transactiondata = array();
        $transactiondata['CompanyID'] = $account->CompanyId;
        $transactiondata['AccountID'] = $account->AccountID;
        if(!empty($transaction['id'])) {
            $transactiondata['Transaction'] = $transaction['id'];
        }
        $transactiondata['Notes'] = $Notes;
        if(!empty($transaction['amount'])) {
            $transactiondata['Amount'] = floatval($transaction['amount']);
        }
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
        $FideliPayResponse = $this->pay_invoice($data);
        $Response = array();
        if($FideliPayResponse['status']=='success') {
            $Response['PaymentMethod'] = 'CREDIT CARD';
            $Response['transaction_notes'] = $FideliPayResponse['note'];
            $Response['Amount'] = floatval($FideliPayResponse['amount']);
            $Response['Transaction'] = $FideliPayResponse['transaction_id'];
            $Response['Response'] = $FideliPayResponse['response'];
            $Response['status'] = 'success';
        }else{
            $Response['transaction_notes'] = $FideliPayResponse['error'];
            $Response['status'] = 'failed';
            $Response['Response']='';
        }
        return $Response;
    }
	
	public function pay_invoice($data){

        $client = $this->getClient();
        $token  = $this->getToken();

        $CardExpiration = $data['ExpirationMonth'].substr($data['ExpirationYear'], -2);
        log::info('cardexpiration '.$CardExpiration);
        try {

            $Request=array(
                'AccountHolder' => $data['NameOnCard'],
                'Details' => array(
                    'Description' => $data['InvoiceNumber'].' (Invoice) Payment',
                    'Amount' => $data['GrandTotal'],
                    'Invoice' => $data['InvoiceNumber']
                ),
                'CreditCardData' => array(
                    'CardNumber' => $data['CardNumber'],
                    'CardExpiration' => $CardExpiration,
                    'AvsStreet' => '',
                    'AvsZip' => '',
                    'CardCode' => $data['CVVNumber']
                )
            );

            $res=$client->runTransaction($token, $Request);
            log::info(print_r($res,true));

            if(!empty($res->ResultCode) && $res->ResultCode=='A'){
                $response['status'] = 'success';
                $response['transaction_id'] = $res->RefNum;
                $response['note'] = 'FideliPay transaction_id '.$res->RefNum;
                //$Amount = ($res->AuthAmount);
                $Amount = $data['GrandTotal'];
                $response['amount'] = $Amount;
                $response['response'] = $res;
            }else{
                $response['status'] = 'fail';
                $response['error'] = $res->Error;
            }
            //$IfAuthExpired = 'ReAuth';
            //$res=$client->captureTransaction($token,$res->RefNum,$amount, $IfAuthExpired);

        }
        catch (SoapFault $e){
            log::info($e->getMessage());
            $response['status'] = 'fail';
            $response['error'] = $e->getMessage();

        }

        return $response;
	}

    public function createFideliPayProfile($data){
        $response = array();
        $client = $this->getClient();
        $token  = $this->getToken();

        $Account = Account::find($data['AccountID']);

        $AccountName = empty($Account->AccountName) ? '':$Account->AccountName;
        $FirstName   = empty($Account->FirstName) ? '':$Account->FirstName;
        $LastName    = empty($Account->LastName) ? '':$Account->LastName;

        $CardExpiration = $data['ExpirationMonth'].substr($data['ExpirationYear'], -2);

        $CustomerObject=array(
            'BillingAddress'=>array(
                'FirstName'=>$FirstName,
                'LastName'=>$LastName,
                'Company'=>$AccountName,
                'Street'=>'',
                'Street2'=>'',
                'City'=>'',
                'State'=>'',
                'Zip'=>'',
                'Country'=>'',
                'Email'=>'',
                'Phone'=>'',
                'Fax'=>''
            ),
            'PaymentMethods' =>
                array(
                    array(

                        'CardNumber'=>$data['CardNumber'],
                        'CardExpiration'=>$CardExpiration,
                        'CardType'=>'',
                        'CardCode'=>'',
                        'AvsStreet'=>'',
                        'AvsZip'=>'',
                        'CardPresent'=>'',
                        'MagStripe'=>'',
                        'TermType'=>'',
                        'MagSupport'=>'',
                        'XID'=>'',
                        'CAVV'=>$data['CVVNumber'],
                        'ECI'=>'',
                        'InternalCardAuth'=>'',
                        'Pares'=>'',
                        "Expires"=>"",
                        "MethodName"=>"My ".$data['CardType'],
                        "SecondarySort"=>1
                    )
                ),
            'CustomerID'=>$data['AccountID'] + rand(),
            'Description'=>'',
            'Enabled'=>false,
            'Amount'=>'0.0',
            'Tax'=>'0',
            'Next'=>'',
            'Notes'=>'Neon addCustomer',
            'NumLeft'=>'',
            'OrderID'=>rand(),
            'ReceiptNote'=>'addCustomer test Created Charge',
            'Schedule'=>'',
            'SendReceipt'=>true,
            'Source'=>'',
            'CustNum'=>'C'.rand()
        );
        try {
            $CustomerNumber = $client->addCustomer($token, $CustomerObject);
            log::info('CustomerNumber '.$CustomerNumber);
            if(!empty($CustomerNumber)){
                $response['status'] = 'success';
                $response['CustomerNumber'] = $CustomerNumber;
                $response['response'] = $CustomerNumber;
            }else{
                $response['status'] = 'fail';
                $response['error'] = cus_lang("PAYMENT_MSG_PROBLEM_CREATING_PAYMENT_METHOD_PROFILE");
            }
        }catch (SoapFault $e){
            Log::error($e);
            $response['status'] = 'fail';
            $response['error'] = $e->getMessage();
        }

        return $response;
        //log::info($CustomerNumber);

    }

    public function deleteFideliPayProfile($CustomerNumber){
        $response = array();
        $client = $this->getClient();
        $token  = $this->getToken();

        try {
            $result = $client->deleteCustomer($token, $CustomerNumber);
            log::info('Customerdelete');
            log::info(print_r($result,true));
            if($result){
                $response['status'] = 'success';
            }else{
                $response['status'] = 'fail';
                $response['error'] = cus_lang("PAYMENT_MSG_PROBLEM_DELETING_PAYMENT_METHOD_PROFILE");
            }
        }catch (SoapFault $e){
            Log::error($e);
            $response['status'] = 'fail';
            $response['error'] = $e->getMessage();
        }
        return $response;
    }

    public function createchargebycustomer($data){
        $response = array();
        $client = $this->getClient();
        $token  = $this->getToken();
        try {

            $Parameters=array(
                'Command'=>'Sale',
                'Details'=>array(
                    'Invoice' => $data['Invoice'],
                    'PONum' => '',
                    'OrderID' => '',
                    'Description' => $data['Description'],
                    'Amount'=>$data['Amount']
                )
            );

            $CustomerNumber=$data['CustomerNumber'];
            $PayMethod='0';

            $res=$client->runCustomerTransaction($token, $CustomerNumber, $PayMethod, $Parameters);

            log::info(print_r($res,true));

            if(!empty($res->ResultCode) && $res->ResultCode=='A'){
                $response['response_code'] = 1;
                $response['status'] = 'Success';
                $response['id'] = $res->RefNum;
                $response['note'] = 'Fidelipay transaction_id '.$res->RefNum;
                $Amount = ($res->AuthAmount);
                $response['amount'] = $Amount;
                $response['response'] = $res;
            }else{
                $response['status'] = 'fail';
                $response['error'] = $res->Error;
            }

        }
        catch (SoapFault $e){
            log::info($e->getMessage());
            $response['status'] = 'fail';
            $response['error'] = $e->getMessage();

        }

        return $response;
    }

    public function paymentValidateWithApiCreditCard($data){
        return $this->doValidation($data);
    }
    public function paymentWithApiCreditCard($data){
        $FideliPayResponse = $this->pay_invoice($data);
        $Response = array();
        if($FideliPayResponse['status']=='success') {
            $Response['PaymentMethod'] = 'CREDIT CARD';
            $Response['transaction_notes'] = $FideliPayResponse['note'];
            $Response['Amount'] = floatval($FideliPayResponse['amount']);
            $Response['Transaction'] = $FideliPayResponse['transaction_id'];
            $Response['Response'] = $FideliPayResponse['response'];
            $Response['status'] = 'success';
        }else{
            $Response['transaction_notes'] = $FideliPayResponse['error'];
            $Response['status'] = 'failed';
            $Response['Response']='';
        }
        return $Response;
    }
}