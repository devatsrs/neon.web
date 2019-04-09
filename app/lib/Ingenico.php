<?php
/**
 * Created by PhpStorm.
 * User: Vasim
 * Date: 05/04/2019
 * Time: 12:28 PM
 */

class Ingenico {

    var $status;
    var $MerchantID;
    var $APIKeyID;
    var $APISecret;
    var $Integrator;
    var $SandboxUrl;
    var $LiveUrl;

    function __Construct($CompanyID=0){
        $Ingenicoobj = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$IngenicoSlug,$CompanyID);
        if($Ingenicoobj){
            $this->SandboxUrl       = "https://gateway20.Ingenico.biz/services/";
            $this->LiveUrl          = "https://gateway20.Ingenico.biz/services/";

            $this->MerchantID 	= 	$Ingenicoobj->MerchantID;
            $this->APIKeyID 	= 	$Ingenicoobj->APIKeyID;
            $this->APISecret 	= 	$Ingenicoobj->APISecret;
            $this->Integrator 	= 	$Ingenicoobj->Integrator;
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
        $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
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
            'CardToken'         => $data['CardToken'],
            'CardHolderName'    => $data['CardHolderName'],
            'ExpirationMonth'   => $data['ExpirationMonth'],
            'ExpirationYear'    => $data['ExpirationYear'],
            'LastDigit'         => $data['LastDigit'],
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
            'CardToken'         => $data['CardToken'],
            'CardHolderName'    => $data['CardHolderName'],
            'ExpirationMonth'   => $data['ExpirationMonth'],
            'ExpirationYear'    => $data['ExpirationYear'],
            'LastDigit'         => $data['LastDigit'],
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

}