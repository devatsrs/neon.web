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
            $this->SandboxTokenUrl      = "https://base.merchantwarrior.com/token/";
            $this->LiveTokenUrl         = "https://api.merchantwarrior.com/token/";
            $this->merchantUUID 	    = 	$MerchantWarriorobj->merchantUUID;
            $this->apiKey		        = 	$MerchantWarriorobj->apiKey;
            $this->apiPassphrase		= 	$MerchantWarriorobj->apiPassphrase;
            $this->MerchantWarriorLive  =   $MerchantWarriorobj->MerchantWarriorLive;
            if($this->MerchantWarriorLive == 1)
            {
                $this->SaveCardUrl	        = 	$this->LiveUrl;
                $this->MerchantWarriorUrl   = 	$this->LiveUrl;
                $this->TokenUrl             = 	$this->LiveTokenUrl;
            }
            else
            {
                $this->SaveCardUrl	        = 	$this->SandboxUrl;
                $this->MerchantWarriorUrl   = 	$this->SandboxUrl;
                $this->TokenUrl             = 	$this->SandboxTokenUrl;
            }
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
            $Response['Response']           = !empty($MerchantWarriorResponse['response']) ? $MerchantWarriorResponse['response'] : "";
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
        $MerchantWarriordata['cardID']          = $MerchantWarriorObj->cardID;

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

            $Account            = Account::find($data['AccountID']);
            $CurrencyID         = $Account->CurrencyId;
            $InvoiceCurrency    = Currency::getCurrency($CurrencyID);

            //$data['GrandTotal'] = 70;
            if(is_int($data['GrandTotal'])) {
                $Amount = str_replace(',', '', str_replace('.', '', $data['GrandTotal']));
                $Amount = number_format((float)$Amount, 2, '.', '');
            }
            else{
                if($this->MerchantWarriorLive == 1) {
                    $Amount = $data['GrandTotal']; // for live
                }
                else{
                    $Amount = number_format(round($data['GrandTotal']), 2, '.', ''); // for testing
                }
            }

            //generate hash as per reference in https://dox.merchantwarrior.com/?php#transaction-type-hash
            $hash = strtolower($this->apiPassphrase . $this->merchantUUID . $Amount . $InvoiceCurrency) ;
            $hash = md5($hash);
            //echo "<pre>";print_r($data);exit;
            if(isset($data['cardID'])) {
                $postdata = array(
                    'method'                => 'processCard',
                    'merchantUUID'          => $this->merchantUUID,
                    'apiKey'                => $this->apiKey,
                    'transactionAmount'     => $Amount,
                    'transactionCurrency'   => $InvoiceCurrency,
                    'transactionProduct'    => 'Invoice No.' . $data['InvoiceNumber'],
                    'customerName'          => $Account->AccountName,
                    'customerCountry'       => $Account->Country,
                    'customerState'         => $Account->City,
                    'customerCity'          => $Account->City,
                    'customerAddress'       => $Account->Address1,
                    'customerPostCode'      => $Account->PostCode,
                    'customerPhone'         => $Account->Phone,
                    'customerEmail'         => $Account->Email,
                    'cardID'                => $data['cardID'],
                    'hash'                  => $hash
                );
            }
            else{
                $creditCardDateMmYy = $data['ExpirationMonth'].substr($data['ExpirationYear'], -2);
                $postdata = array(
                    'method'                => 'processCard',
                    'merchantUUID'          => $this->merchantUUID,
                    'apiKey'                => $this->apiKey,
                    'transactionAmount'     => $Amount,
                    'transactionCurrency'   => $InvoiceCurrency,
                    'transactionProduct'    => 'Invoice No.' . $data['InvoiceNumber'],
                    'customerName'          => $Account->AccountName,
                    'customerCountry'       => $Account->Country,
                    'customerState'         => $Account->City,
                    'customerCity'          => $Account->City,
                    'customerAddress'       => $Account->Address1,
                    'customerPostCode'      => $Account->PostCode,
                    'customerPhone'         => $Account->Phone,
                    'customerEmail'         => $Account->Email,
                    'paymentCardName'       => $data['NameOnCard'],
                    'paymentCardNumber'     => $data['CardNumber'],
                    'paymentCardExpiry'     => $creditCardDateMmYy,
                    'paymentCardCSC'        => $data['CVVNumber'],
                    'hash'                  => $hash
                );
            }
            //$jsonData = json_encode($postData);
            try {
                if(isset($data['cardID'])) {
                    $res = $this->sendCurlRequest($this->TokenUrl, $postdata);
                }
                else{
                    $res = $this->sendCurlRequest($this->SaveCardUrl, $postdata);
                }
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if(!empty($res['status']) && $res['status']==1 && $res['responseData']['responseCode']==0){
                $response['status']         = 'success';
                $response['note']           = 'MerchantWarrior transaction_id '.$res['transactionID'];
                $response['transaction_id'] = $res['transactionID'];
                $response['amount']         = $res['responseData']['transactionAmount'];
                $response['response']       = $res;
            }else{
                $response['status']         = 'fail';
                $response['transaction_id'] = !empty($res['transactionID']) ? $res['transactionID'] : "";
                $response['error']          = $res['responseData']['responseMessage'];
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

            //$data['GrandTotal'] = 70;
            if(is_int($data['GrandTotal'])) {
                $Amount = str_replace(',', '', str_replace('.', '', $data['GrandTotal']));
                $Amount = number_format((float)$Amount, 2, '.', '');
            }else{
                if($this->MerchantWarriorLive == 1) {
                    $Amount = $data['GrandTotal']; // for live
                }
                else{
                    $Amount = number_format(round($data['GrandTotal']), 2, '.', ''); // for testing
                }
            }

            //generate hash as per reference in https://dox.merchantwarrior.com/?php#transaction-type-hash
            $hash = strtolower($this->apiPassphrase . $this->merchantUUID . $Amount . $InvoiceCurrency) ;
            $hash = md5($hash);
            $creditCardDateMmYy = $data['ExpirationMonth'].substr($data['ExpirationYear'], -2);
            $postdata = array(
                'method'                => 'processCard',
                'merchantUUID'          => $this->merchantUUID,
                'apiKey'                => $this->apiKey,
                'transactionAmount'     => $Amount,
                'transactionCurrency'   => $InvoiceCurrency,
                'transactionProduct'    => 'Invoice No.' . $data['InvoiceNumber'],
                'customerName'          => $data['AccountName'],
                'customerCountry'       => $data['Country'],
                'customerState'         => $data['City'],
                'customerCity'          => $data['City'],
                'customerAddress'       => $data['Address1'],
                'customerPostCode'      => $data['PostCode'],
                'customerPhone'         => $data['Phone'], //not required
                'customerEmail'         => $data['Email'], //not required
                'paymentCardName'       => $data['NameOnCard'],
                'paymentCardNumber'     => $data['CardNumber'],
                'paymentCardExpiry'     => $creditCardDateMmYy,
                'paymentCardCSC'        => $data['CVVNumber'],
                'hash'                  => $hash
            );

            //$jsonData = json_encode($postData);
            try {
                $res = $this->sendCurlRequest($this->SaveCardUrl, $postdata);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if(!empty($res['status']) && $res['status']==1 && $res['responseData']['responseCode']==0){
                $response['status']         = 'success';
                $response['note']           = 'MerchantWarrior transaction_id '.$res['transactionID'];
                $response['transaction_id'] = $res['transactionID'];
                $response['amount']         = $res['responseData']['transactionAmount'];
                $response['response']       = $res;
            }else{
                $response['status']         = 'fail';
                $response['transaction_id'] = !empty($res['transactionID']) ? $res['transactionID'] : "";
                $response['error']          = $res['responseData']['responseMessage'];
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
       // echo "<pre>";print_r($MerchantWarriorResponse);exit;
        if ($MerchantWarriorResponse["status"] == "success") {
            $option = array(
                'cardID' => $MerchantWarriorResponse['cardID'],'cardKey' => $MerchantWarriorResponse['response']['responseData']['cardKey'],'ivrCardID' => $MerchantWarriorResponse['response']['responseData']['ivrCardID']
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
            $ExpirationYear = substr($data['ExpirationYear'], -2);

            $postdata = array(
                'method'            => 'addCard',
                'merchantUUID'      => $this->merchantUUID,
                'apiKey'            => $this->apiKey,
                'cardName'          => $data['NameOnCard'],
                'cardNumber'        => $data['CardNumber'],
                'cardExpiryMonth'   => $data['ExpirationMonth'],
                'cardExpiryYear'    => $ExpirationYear
            );
           // $jsonData = json_encode($postdata);
            try {
                $res = $this->sendCurlRequest($this->TokenUrl,$postdata);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if(!empty($res['status']) && $res['status']==1 && $res['responseData']['responseCode']==0){
                $response['status']         = 'success';
                $response['cardID']         = $res['responseData']['cardID'];
                $response['response']       = $res;
            }else{
                $response['status']         = 'fail';
                $response['error']          = $res['responseData']['responseMessage'];
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
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

        $curl_response = curl_exec($curl);
        $error = curl_error($curl);

        // Check for CURL errors
        if (isset($error) && strlen($error)) {
            throw new Exception("CURL Error: {$error}");
        }

        // Parse the XML
        $xml = simplexml_load_string($curl_response);
        // Convert the result from a SimpleXMLObject into an array
        $xml = (array)$xml;
        // Validate the response - the only successful code is 0
        $status = ((int)$xml['responseCode'] === 0) ? true : false;

        // Make the response a little more useable
        $res = array (
            'status' => $status,
            'transactionID' => (isset($xml['transactionID']) ? $xml['transactionID'] : null),
            'responseData' => $xml
        );
        return $res;
    }

    public function paymentValidateWithApiCreditCard($data){
        return $this->doValidation($data);
    }


    public function paymentWithApiCreditCard($data){
        $MerchantWarriorResponse = $this->payInvoiceWithApi($data);
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
            $Response['Response']           = !empty($MerchantWarriorResponse['response']) ? $MerchantWarriorResponse['response'] : "";
        }
        return $Response;
    }

}