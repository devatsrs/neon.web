<?php
/**
 * Created by PhpStorm.
 * User: Badal
 * Date: 05/06/2018
 * Time: 04:30 PM
 */

class MerchantWarrior {

    public $request;
    var $status;
    var $merchantUUID;
    var $apiKey;
    var $hash;
    var $SandboxUrl;
    var $LiveUrl;
    var $MerchantWarriorUrl;
    var $SaveCardUrl;


    function __Construct($CompanyID=0){
        $MerchantWarriorobj = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$MerchantWarriorSlug,$CompanyID);
        if($MerchantWarriorobj){
            $this->SandboxUrl           = "https://base.merchantwarrior.com/post/";
            $this->LiveUrl              = "https://api.merchantwarrior.com/post/";
            $this->merchantUUID 	    = 	$MerchantWarriorobj->merchantUUID;
            $this->apiKey		        = 	$MerchantWarriorobj->apiKey;
            $this->hash		            = 	$MerchantWarriorobj->hash;
            $this->SaveCardUrl	        = 	$this->SandboxUrl;
            $this->MerchantWarriorUrl   = 	$this->SandboxUrl;
            $this->status               =   true;
        }else{
            $this->status               =   false;
        }
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
            $ValidationResponse['message'] = "Month must be after " . date("F");
            return $ValidationResponse;
        }
        $card = CreditCard::validCreditCard($data['CardNumber']);
        if ($card['valid'] == 0) {
            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = "Please enter valid card number";
            return $ValidationResponse;
        }

        $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
    }

    public function paymentValidateWithCreditCard($data){
        return $this->doValidation($data);
    }

    public function paymentWithCreditCard($data){
        $MerchantWarriorResponse = $this->pay_invoice($data);
        $Response = array();
        if($MerchantWarriorResponse['status']=='success') {
            $Response['PaymentMethod']      = 'CREDIT CARD';
            $Response['transaction_notes']  = $MerchantWarriorResponse['note'];
            $Response['Amount']             = floatval($MerchantWarriorResponse['amount']);
            $Response['Transaction']        = $MerchantWarriorResponse['transaction_id'];
            $Response['Response']           = $MerchantWarriorResponse['response'];
            $Response['status']             = 'success';
        }else{
            $Response['transaction_notes']  = $MerchantWarriorResponse['error'];
            $Response['status']             = 'failed';
            $Response['Response']           = $MerchantWarriorResponse['response'];
        }
        return $Response;
    }

    public function paymentWithProfile($data){
        $account = Account::find($data['AccountID']);

        $CustomerProfile                = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
        $MerchantWarriorObj                    = json_decode($CustomerProfile->Options);

        $MerchantWarriordata = array();
        /*$InvoiceIDs                     = explode(',', $data['InvoiceIDs']);
        $MerchantWarriordata['InvoiceID']      = $InvoiceIDs[0];*/
        $MerchantWarriordata['InvoiceNumber']  = $data['InvoiceNumber'];
        $MerchantWarriordata['GrandTotal']     = $data['outstanginamount'];
        $MerchantWarriordata['AccountID']      = $data['AccountID'];
        $MerchantWarriordata['Token']          = $MerchantWarriorObj->Token;
        $MerchantWarriordata['CVVNumber']      = $MerchantWarriorObj->CVVNumber;

        $transactionResponse = array();

        $transaction = $this->pay_invoice($MerchantWarriordata);

        if($transaction['status']=='success') {
            $Status = TransactionLog::SUCCESS;
            $Notes  = 'MerchantWarrior transaction_id ' . $transaction['transaction_id'];
            $transactionResponse['response_code']   = 1;
        }else{
            $Status = TransactionLog::FAILED;
            $Notes  = empty($transaction['error']) ? '' : $transaction['error'];
        }

        $transactionResponse['transaction_notes']   = $Notes;
        $transactionResponse['PaymentMethod']       = 'CREDIT CARD';
        $transactionResponse['failed_reason']       = $Notes;
        $transactionResponse['transaction_id']      = $transaction['transaction_id'];
        $transactionResponse['Response']            = $transaction;

        $transactiondata = array();
        $transactiondata['CompanyID']   = $account->CompanyId;
        $transactiondata['AccountID']   = $account->AccountID;
        $transactiondata['Notes']       = $Notes;

        if (!empty($transaction['transaction_id'])) {
            $transactiondata['Transaction'] = $transaction['transaction_id'];
        }
        if (!empty($transaction['amount'])) {
            $transactiondata['Amount'] = floatval($transaction['amount']);
        }

        $transactiondata['Status']      = $Status;
        $transactiondata['created_at']  = date('Y-m-d H:i:s');
        $transactiondata['updated_at']  = date('Y-m-d H:i:s');
        $transactiondata['CreatedBy']   = $data['CreatedBy'];
        $transactiondata['ModifyBy']    = $data['CreatedBy'];
        $transactiondata['Response']    = json_encode($transaction);
        TransactionLog::insert($transactiondata);
        return $transactionResponse;
    }

    public function pay_invoice($data){
        try {
            //test params
            /*$creditCard         = "4111111111111111";
            $creditCardDateMmYy = "1219";
            $cvv2               = "123";
            $id                 = "123456789";
            $paramX             = "test";*/
            //test params

            $Account            = Account::find($data['AccountID']);
            $CurrencyID         = $Account->CurrencyId;
            $InvoiceCurrency    = Currency::getCurrency($CurrencyID);
            //print_R($Account);exit;
            $creditCardDateMmYy = $data['ExpirationMonth'].substr($data['ExpirationYear'], -2);

            $postData = array (
                'method'                => 'processCard',
                'merchantUUID'          => $this->merchantUUID,
                'apiKey'                => $this->apiKey,
                'transactionAmount'     => str_replace(',','',str_replace('.','',$data['GrandTotal'])),
                'transactionCurrency'   => $InvoiceCurrency,
                'transactionProduct'    => 'Test Product',
                'customerName'          => $Account->AccountName,
                'customerCountry'       => $Account->Country,
                'customerState'         => $Account->State,
                'customerCity'          => $Account->City,
                'customerAddress'       => $Account->Address1." ".$Account->Address2,
                'customerPostCode'      => $Account->PostCode,
                'customerPhone'         => $Account->Phone,
                'customerEmail'         => $Account->Email,
                //'customerIP'            => '1.1.1.1',
                'paymentCardName'       => $data['NameOnCard'],
                'paymentCardNumber'     => $data['CardNumber'],
                'paymentCardExpiry'     => $creditCardDateMmYy,
                'paymentCardCSC'        => $data['CVVNumber'],
                'hash'                  => $this->hash
            );

            $jsonData = json_encode($postdata);

            try {
                $res = $this->sendCurlRequest($this->MerchantWarriorUrl,$jsonData);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if(!empty($res['StatusCode']) && $res['StatusCode']=='000'){
                $response['status']         = 'success';
                $response['note']           = 'MerchantWarrior transaction_id '.$res['ResultData']['MerchantWarriorTransactionId'];
                $response['transaction_id'] = $res['ResultData']['MerchantWarriorTransactionId'];
                $response['amount']         = $data['GrandTotal'];
                $response['response']       = $res;
            }else{
                $response['status']         = 'fail';
                $response['transaction_id'] = !empty($res['ResultData']['MerchantWarriorTransactionId']) ? $res['ResultData']['MerchantWarriorTransactionId'] : "";
                $response['error']          = $res['ErrorMessage'];
                $response['response']       = $res;
                Log::info(print_r($res,true));
            }
        } catch (Exception $e) {
            log::info($e->getMessage());
            $response['status']             = 'fail';
            $response['error']              = $e->getMessage();
        }
        return $response;
    }


    public function createProfile($data){
        $CustomerID         = $data['AccountID'];
        $CompanyID          = $data['CompanyID'];
        $PaymentGatewayID   = $data['PaymentGatewayID'];

        $isDefault = 1;
        $count = AccountPaymentProfile::where(['AccountID' => $CustomerID])
            ->where(['CompanyID' => $CompanyID])
            ->where(['PaymentGatewayID' => $PaymentGatewayID])
            ->where(['isDefault' => 1])
            ->count();

        if($count>0){
            $isDefault = 0;
        }

        $MerchantWarriorResponse = $this->createMerchantWarriorProfile($data);
        if ($MerchantWarriorResponse["status"] == "success") {
            $option = array(
                'Token' => $MerchantWarriorResponse['Token'],'VoucherId' => $MerchantWarriorResponse['VoucherId'],'CVVNumber' => $data['CVVNumber']
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
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully Created"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Saving Payment Method Profile."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => $MerchantWarriorResponse['error']));
        }
    }

    public function createMerchantWarriorProfile($data){
        try {
            $creditCardDateMmYy = $data['ExpirationMonth'].substr($data['ExpirationYear'], -2);

            $postdata = array(
                'terminalNumber'        => $this->terminalNumber,
                'user'                  => $this->user,
                'password'              => $this->password,
                'shopNumber'            => "001",
                'creditCard'            => $data['CardNumber'],
                'creditCardDateMmYy'    => $creditCardDateMmYy,
                'addFourDigits'         => "false"
            );
            $jsonData = json_encode($postdata);

            try {
                $res = $this->sendCurlRequest($this->SaveCardUrl,$jsonData);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if(!empty($res['StatusCode']) && $res['StatusCode']=='000'){
                $response['status']         = 'success';
                $response['Token']          = $res['ResultData']['Token'];
                $response['VoucherId']      = $res['ResultData']['VoucherId'];
                $response['response']       = $res;
            }else{
                $response['status']         = 'fail';
                $response['error']          = $res['ErrorMessage'];
                $response['response']       = $res;
            }
        } catch (Exception $e) {
            log::info($e->getMessage());
            $response['status']             = 'fail';
            $response['error']              = $e->getMessage();
        }
        return $response;
    }

    public function deleteProfile($data){
        $AccountID                  = $data['AccountID'];
        $CompanyID                  = $data['CompanyID'];
        $AccountPaymentProfileID    = $data['AccountPaymentProfileID'];

        $count                      = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
        $PaymentProfile             = AccountPaymentProfile::find($AccountPaymentProfileID);
        if(!empty($PaymentProfile)){
            $options                = json_decode($PaymentProfile->Options);
            $Token                  = $options->Token;
            $VoucherId              = $options->VoucherId;
            $isDefault              = $PaymentProfile->isDefault;
        }else{
            return Response::json(array("status" => "failed", "message" => "Record Not Found"));
        }
        if($isDefault==1){
            if($count!=1){
                return Response::json(array("status" => "failed", "message" => "You can not delete default profile. Please set as default other profile first."));
            }
        }

        $result = $this->deleteMerchantWarriorProfile($Token);

        if($result["status"]=="success"){
            if($PaymentProfile->delete()) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted. Profile deleted too."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem deleting Payment Method Profile."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => $result['error']));
        }
    }

    public function deleteMerchantWarriorProfile($Token){
        /*try {
            $postdata = array(
                'terminalNumber'        => $this->terminalNumber,
                'user'                  => $this->user,
                'password'              => $this->password,
                'Token'                 => $Token
            );
            $jsonData = json_encode($postdata);

            try {
                $res = $this->sendCurlRequest($this->DeleteCardUrl,$jsonData);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if(!empty($res['StatusCode']) && $res['StatusCode']=='000'){
                $response['status']         = 'success';
                $response['Token']          = $res['ResultData']['Token'];
                $response['response']       = $res;
            }else{
                $response['status']         = 'fail';
                $response['error']          = $res['ErrorMessage'];
                $response['response']       = $res;
            }
        } catch (Exception $e) {
            log::info($e->getMessage());
            $response['status']             = 'fail';
            $response['error']              = $e->getMessage();
        }*/

        $response['status']         = 'success';

        return $response;
    }

    public function sendCurlRequest($url,$data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8', 'Content-Length: ' . strlen($data)));
        $result = curl_exec($ch);
        $res = json_decode($result, true);
        return $res;
    }

}