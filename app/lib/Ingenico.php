<?php
/**
 * Created by PhpStorm.
 * User: Vasim
 * Date: 05/04/2019
 * Time: 12:28 PM
 */

class Ingenico {

    var $status;
    var $PSPID;
    var $UserID;
    var $UserPassword;
    var $SHASIGN;
    var $SandboxUrl;
    var $LiveUrl;

    function __Construct($CompanyID=0){
        $Ingenicoobj = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$IngenicoSlug,$CompanyID);
        if($Ingenicoobj){
            $this->SandboxUrl   = "https://secure.ogone.com/ncol/test/orderdirect.asp";
            $this->LiveUrl      = "https://secure.ogone.com/ncol/prod/orderdirect.asp";

            $this->PSPID 	    = 	$Ingenicoobj->PSPID;
            $this->UserID 	    = 	$Ingenicoobj->UserID;
            $this->UserPassword = 	$Ingenicoobj->Password;
            $this->SHASIGN      = 	$Ingenicoobj->SHASIGN;
            $this->IngenicoLive = 	$Ingenicoobj->IngenicoLive;

            if(intval($this->IngenicoLive) == 1) {
                $this->IngenicoUrl	= 	$this->LiveUrl;
                $this->SaveCardUrl	= 	$this->LiveUrl;
            } else {
                $this->IngenicoUrl	= 	$this->SandboxUrl;
                $this->SaveCardUrl	= 	$this->SandboxUrl;
            }
            $this->status           =   true;
        }else{
            $this->status           =   false;
        }
    }

    public function doValidation($data){
        $ValidationResponse = array();
        $rules = array(
            'CardToken'         => 'required',
            'CardHolderName'    => 'required',
            'ExpirationMonth'   => 'required',
            'ExpirationYear'    => 'required',
            'LastDigit'         => 'required|digits:4',
            'CVC'               => 'required',
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
        $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
    }

    public function sendCurlRequest($url,$data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Content-Length: ' . strlen($data)));
        $result = curl_exec($ch);
        $res    = $result != false ? @self::xml2array(simplexml_load_string($result))["@attributes"] : false;
        return $res;
    }

    public function createProfile($data){
        $AccountID          = $data['AccountID'];
        $CompanyID          = $data['CompanyID'];
        $PaymentGatewayID   = $data['PaymentGatewayID'];

        //$account = Account::where(array('AccountID' => $AccountID))->first();

        $isDefault = 1;

        $count = AccountPaymentProfile::where(['AccountID' => $AccountID])
            ->where(['CompanyID' => $CompanyID])
            ->where(['PaymentGatewayID' => $PaymentGatewayID])
            ->where(['isDefault' => 1])
            ->count();

        if($count>0){
            $isDefault = 0;
        }

        $option = array(
            'CardToken'       => $data['CardToken'],
            'CVC'             => $data['CVC'],
            'CardHolderName'  => $data['CardHolderName'],
            'ExpirationMonth' => $data['ExpirationMonth'],
            'ExpirationYear'  => $data['ExpirationYear'],
            'LastDigit'       => $data['LastDigit'],
        );

        $CardDetail = array('Title' => $data['Title'],
            'Options' => json_encode($option),
            'Status' => 1,
            'isDefault' => $isDefault,
            'created_by' => Customer::get_accountName(),
            'CompanyID' => $CompanyID,
            'AccountID' => $AccountID,
            'PaymentGatewayID' => $PaymentGatewayID);
        if (AccountPaymentProfile::create($CardDetail)) {
            return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_SUCCESSFULLY_CREATED")));
        } else {
            return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_SAVING_PAYMENT_METHOD_PROFILE")));
        }

    }

    public function updateProfile($data){
        $AccountPaymentProfileID = $data['AccountPaymentProfileID'];

        //$account = Account::where(array('AccountID' => $AccountID))->first();

        $option = array(
            'CardToken'       => $data['CardToken'],
            'CVC'             => $data['CVC'],
            'CardHolderName'  => $data['CardHolderName'],
            'ExpirationMonth' => $data['ExpirationMonth'],
            'ExpirationYear'  => $data['ExpirationYear'],
            'LastDigit'       => $data['LastDigit'],
        );
        $CardDetail = array(
            'Title' => $data['Title'],
            'Options' => json_encode($option),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => Customer::get_accountName()
        );

        if (AccountPaymentProfile::where(['AccountPaymentProfileID'=>$AccountPaymentProfileID])->update($CardDetail)) {
            return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_SUCCESSFULLY_UPDATED")));
        } else {
            return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_SAVING_PAYMENT_METHOD_PROFILE")));
        }

    }

    public function deleteProfile($data){
        $AccountID                  = $data['AccountID'];
        $CompanyID                  = $data['CompanyID'];
        $AccountPaymentProfileID    = $data['AccountPaymentProfileID'];

        $count                      = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
        $PaymentProfile             = AccountPaymentProfile::find($AccountPaymentProfileID);
        if(!empty($PaymentProfile)){
            $isDefault              = $PaymentProfile->isDefault;
        }else{
            return Response::json(array("status" => "failed", "message" => "Record Not Found"));
        }
        if($isDefault==1){
            if($count!=1){
                return Response::json(array("status" => "failed", "message" => "You can not delete default profile. Please set as default other profile first."));
            }
        }

        if($PaymentProfile->delete()) {
            return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted. Profile deleted too."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem deleting Payment Method Profile."));
        }
    }

    public function paymentValidateWithProfile($data){
        $Response = array();
        $Response['status']='success';
        $account = Account::find($data['AccountID']);
        $CurrencyCode = Currency::getCurrency($account->CurrencyId);
        if(empty($CurrencyCode)){
            $Response['status']='failed';
            $Response['message']=cus_lang("PAYMENT_MSG_NO_ACCOUNT_CURRENCY_AVAILABLE");
        }
        return $Response;
    }

    public function paymentWithApiProfile($data){

        $Account = Account::find($data['AccountID']);
        $CustomerProfile = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
        $IngenicoObj = json_decode($CustomerProfile->Options);

        $CurrencyCode = Currency::getCurrency($Account->CurrencyId);
        $OrderID = date("ymdhis") . rand(10, 99);

        $request = [];
        $request['ORDERID']  = $OrderID;
        $request['ALIAS']    = $IngenicoObj->CardToken;
        $request['CVC']      = $IngenicoObj->CVC;
        $request['PSPID']    = $this->PSPID;
        $request['USERID']   = $this->UserID;
        $request['PSWD']     = $this->UserPassword;
        $request['AMOUNT']   = $data['outstanginamount'];
        $request['CURRENCY'] = $CurrencyCode;
        $request['SHASIGN']  = $this->SHASIGN;
        $request['OPERATION'] = 'SAL';

        $query = "";
        foreach($request as $key => $q)
            $query .= $key . "=" . $q . "&";
        $query = rtrim($query, "&");

        $response = ['status' => 'failed', 'msg' => "Invalid Request.", "response_code" => ''];

        try {
            $resp = $this->sendCurlRequest($this->IngenicoUrl, $query);

            if($resp != false){
                if(@$resp['PAYID'] != 0 && (@$resp['STATUS'] == "5" || @$resp['STATUS'] == "9")){
                    $Notes      = 'Stripe transaction_id ' . $resp['PAYID'];
                    $Status     = TransactionLog::SUCCESS;
                    $response['transaction_id'] = $resp['PAYID'];
                    $response['status']         = 'success';
                    $response['response_code']  = 1;
                    $response['msg']            = $Notes;
                    $transactiondata['Transaction'] = $resp['PAYID'];
                    $transactiondata['Amount']  = floatval($resp['amount']);
                } else {
                    $Notes  = "Error: " . @$resp['NCERRORPLUS'];
                    $Status = TransactionLog::FAILED;
                    $response['failed_reason']     = $Notes;
                }

                $response['transaction_notes'] = $Notes;
                $response['PaymentMethod']     = 'CREDIT CARD';
                $response['Response']          = $resp;
                $transactiondata['CompanyID']  = $Account->CompanyId;
                $transactiondata['AccountID']  = $Account->AccountID;
                $transactiondata['Notes']      = $Notes;
                $transactiondata['Status']     = $Status;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy']  = "API";
                $transactiondata['ModifyBy']   = "API";
                $transactiondata['Response']   = json_encode($resp);
                TransactionLog::insert($transactiondata);
            }

        } catch(Exception $e){
            $response['msg'] = "Error: ". $e->getMessage();
        }

        return $response;
    }

    public static function xml2array ( $xmlObject, $out = array () )
    {
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ) ? self::xml2array ( $node ) : $node;

        return $out;
    }
}