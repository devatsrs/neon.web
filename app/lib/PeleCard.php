<?php
/**
 * Created by PhpStorm.
 * User: Bhavin
 * Date: 26/09/2017
 * Time: 04:30 PM
 */

class PeleCard {

    public $request;
    var $status;
    var $terminalNumber;
    var $user;
    var $password;
    var $SandboxUrl;
    var $LiveUrl;
    var $PeleCardLive;
    var $PeleCardUrl;
    var $SaveCardUrl;
    var $DebitRegularType;
    var $ConvertToToken;

    function __Construct($CompanyID=0){
        $PeleCardobj = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$PeleCardSlug,$CompanyID);
        if($PeleCardobj){
            $this->SandboxUrl       = "https://gateway20.pelecard.biz/services/";
            $this->LiveUrl          = "https://gateway20.pelecard.biz/services/";

            $this->terminalNumber 	= 	$PeleCardobj->terminalNumber;
            $this->user		        = 	$PeleCardobj->user;
            $this->password		    = 	Crypt::decrypt($PeleCardobj->password);
            $this->PeleCardLive     = 	$PeleCardobj->PeleCardLive;
            $this->DebitRegularType = 	"DebitRegularType";
            $this->ConvertToToken   = 	"ConvertToToken";

            if(intval($this->PeleCardLive) == 1) {
                $this->PeleCardUrl	= 	$this->LiveUrl.$this->DebitRegularType;
                $this->SaveCardUrl	= 	$this->LiveUrl.$this->ConvertToToken;
            } else {
                $this->PeleCardUrl	= 	$this->SandboxUrl.$this->DebitRegularType;
                $this->SaveCardUrl	= 	$this->SandboxUrl.$this->ConvertToToken;
            }

            $this->status           =   true;
        }else{
            $this->status           =   false;
        }
    }

    public function doValidation($data){
        $ValidationResponse = array();
        $rules = array(
            'CardNumber' => 'required|digits_between:9,19',
            'ExpirationMonth' => 'required',
            'ExpirationYear' => 'required',
            'NameOnCard' => 'required',
            //'CVVNumber' => 'required',
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
        /*$card = CreditCard::validCreditCard($data['CardNumber']);
        if ($card['valid'] == 0) {
            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = "Please enter valid card number";
            return $ValidationResponse;
        }*/

        $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
    }

    public function paymentValidateWithCreditCard($data){
        return $this->doValidation($data);
    }

    public function paymentWithCreditCard($data){
        $PeleCardResponse = $this->pay_invoice($data);
        $Response = array();
        if($PeleCardResponse['status']=='success') {
            $Response['PaymentMethod']      = 'CREDIT CARD';
            $Response['transaction_notes']  = $PeleCardResponse['note'];
            $Response['Amount']             = floatval($PeleCardResponse['amount']);
            $Response['Transaction']        = $PeleCardResponse['transaction_id'];
            $Response['Response']           = $PeleCardResponse['response'];
            $Response['status']             = 'success';
        }else{
            $Response['transaction_notes']  = $PeleCardResponse['error'];
            $Response['status']             = 'failed';
            $Response['Response']           = $PeleCardResponse['response'];
        }
        return $Response;
    }

    public function paymentWithProfile($data){
        $account = Account::find($data['AccountID']);

        $CustomerProfile                = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
        $PeleCardObj                    = json_decode($CustomerProfile->Options);

        $pelecarddata = array();
        /*$InvoiceIDs                     = explode(',', $data['InvoiceIDs']);
        $pelecarddata['InvoiceID']      = $InvoiceIDs[0];*/
        $pelecarddata['InvoiceNumber']  = $data['InvoiceNumber'];
        $pelecarddata['GrandTotal']     = $data['outstanginamount'];
        $pelecarddata['AccountID']      = $data['AccountID'];
        $pelecarddata['Token']          = $PeleCardObj->Token;
        $pelecarddata['CVVNumber']      = $PeleCardObj->CVVNumber;
        $pelecarddata['PeleCardID']     = !empty($PeleCardObj->PeleCardID) ? $PeleCardObj->PeleCardID : '';

        $transactionResponse = array();

        $transaction = $this->pay_invoice($pelecarddata);

        if($transaction['status']=='success') {
            $Status = TransactionLog::SUCCESS;
            $Notes  = 'PeleCard transaction_id ' . $transaction['transaction_id'];
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
            $currency           = "1";

            if(strtolower($InvoiceCurrency) != "ils") {
                if(strtolower($InvoiceCurrency) == "usd") {
                    $currency   = "2";
                } else if(strtolower($InvoiceCurrency) == "eur") {
                    $currency   = "978";
                } else if(strtolower($InvoiceCurrency) == "gbp") {
                    $currency   = "286";
                } else {
                    $currency   = "0";
                }
            }

            if(!empty($data['Token'])) {
                $token              = $data['Token'];
                $creditCard         = "";
                $creditCardDateMmYy = "";
            } else {
                $token              = "";
                $creditCard         = $data['CardNumber'];
                $creditCardDateMmYy = $data['ExpirationMonth'].substr($data['ExpirationYear'], -2);
            }

            $postdata = array(
                'terminalNumber'        => $this->terminalNumber,
                'user'                  => $this->user,
                'password'              => $this->password,
                'shopNumber'            => "001",
                'creditCard'            => $creditCard,
                'creditCardDateMmYy'    => $creditCardDateMmYy,
                'token'                 => $token,
                'total'                 => str_replace(',','',str_replace('.','',$data['GrandTotal'])),
                'currency'              => $currency,
                'cvv2'                  => $data['CVVNumber'],
                'id'                    => $data['PeleCardID'],//$data['AccountID'],
                'authorizationNumber'   => "",
                'paramX'                => $data['InvoiceNumber']
            );
            $jsonData = json_encode($postdata);

            try {
                $res = $this->sendCurlRequest($this->PeleCardUrl,$jsonData);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if(!empty($res['StatusCode']) && $res['StatusCode']=='000'){
                $response['status']         = 'success';
                $response['note']           = 'PeleCard transaction_id '.$res['ResultData']['PelecardTransactionId'];
                $response['transaction_id'] = $res['ResultData']['PelecardTransactionId'];
                $response['amount']         = $data['GrandTotal'];
                $response['response']       = $res;
            }else{
                $response['status']         = 'fail';
                $response['transaction_id'] = !empty($res['ResultData']['PelecardTransactionId']) ? $res['ResultData']['PelecardTransactionId'] : "";
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

    public function payInvoiceWithApi($data){
        try {
            $InvoiceCurrency    = Currency::getCurrency($data['CurrencyId']);
            $currency           = "1";

            if(strtolower($InvoiceCurrency) != "ils") {
                if(strtolower($InvoiceCurrency) == "usd") {
                    $currency   = "2";
                } else if(strtolower($InvoiceCurrency) == "eur") {
                    $currency   = "978";
                } else if(strtolower($InvoiceCurrency) == "gbp") {
                    $currency   = "286";
                } else {
                    $currency   = "0";
                }
            }

            $token              = "";
            $creditCard         = $data['CardNumber'];
            $creditCardDateMmYy = $data['ExpirationMonth'].substr($data['ExpirationYear'], -2);

            $postdata = array(
                'terminalNumber'        => $this->terminalNumber,
                'user'                  => $this->user,
                'password'              => $this->password,
                'shopNumber'            => "001",
                'creditCard'            => $creditCard,
                'creditCardDateMmYy'    => $creditCardDateMmYy,
                'token'                 => $token,
                'total'                 => str_replace(',','',str_replace('.','',$data['GrandTotal'])),
                'currency'              => $currency,
                'cvv2'                  => $data['CVVNumber'],
                'id'                    => $data['PeleCardID'],//$data['AccountID'],
                'authorizationNumber'   => "",
                'paramX'                => $data['InvoiceNumber']
            );
            $jsonData = json_encode($postdata);

            try {
                $res = $this->sendCurlRequest($this->PeleCardUrl,$jsonData);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if(!empty($res['StatusCode']) && $res['StatusCode']=='000'){
                $response['status']         = 'success';
                $response['note']           = 'PeleCard transaction_id '.$res['ResultData']['PelecardTransactionId'];
                $response['transaction_id'] = $res['ResultData']['PelecardTransactionId'];
                $response['amount']         = $data['GrandTotal'];
                $response['response']       = $res;
            }else{
                $response['status']         = 'fail';
                $response['transaction_id'] = !empty($res['ResultData']['PelecardTransactionId']) ? $res['ResultData']['PelecardTransactionId'] : "";
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

        $PeleCardResponse = $this->createPeleCardProfile($data);
        if ($PeleCardResponse["status"] == "success") {
            $option = array(
                'Token' => $PeleCardResponse['Token'],'VoucherId' => $PeleCardResponse['VoucherId'],'CVVNumber' => $data['CVVNumber'],'PeleCardID' => $data['PeleCardID']
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
            return Response::json(array("status" => "failed", "message" => $PeleCardResponse['error']));
        }
    }

    public function createPeleCardProfile($data){
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

        $result = $this->deletePeleCardProfile($Token);

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

    public function deletePeleCardProfile($Token){
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

    public function paymentValidateWithApiCreditCard($data){
        return $this->doValidation($data);
    }

    public function paymentWithApiCreditCard($data){
        $PeleCardResponse = $this->payInvoiceWithApi($data);
        $Response = array();
        if($PeleCardResponse['status']=='success') {
            $Response['PaymentMethod']      = 'CREDIT CARD';
            $Response['transaction_notes']  = $PeleCardResponse['note'];
            $Response['Amount']             = floatval($PeleCardResponse['amount']);
            $Response['Transaction']        = $PeleCardResponse['transaction_id'];
            $Response['Response']           = $PeleCardResponse['response'];
            $Response['status']             = 'success';
        }else{
            $Response['transaction_notes']  = $PeleCardResponse['error'];
            $Response['status']             = 'failed';
            $Response['Response']           = $PeleCardResponse['response'];
        }
        return $Response;
    }

}