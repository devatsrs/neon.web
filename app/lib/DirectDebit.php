<?php
/**
 * Created by PhpStorm.
 * User: Vasim
 * Date: 05/04/2019
 * Time: 12:28 PM
 */

class DirectDebit {

    var $status;

    function __Construct($CompanyID=0){
        $this->status = true;
    }

    public function doValidation($data){
        $ValidationResponse = array();
        $rules = array(
            'BankAccount'       => 'required',
            // 'BIC'               => 'required',
            'AccountHolderName' => 'required',
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
        $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
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
            'BankAccount'       => $data['BankAccount'],
            'BIC'               => $data['BIC'],
            'AccountHolderName' => $data['AccountHolderName'],
            'MandateCode'       => $data['MandateCode'],
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
            'BankAccount'       => $data['BankAccount'],
            'BIC'               => $data['BIC'],
            'AccountHolderName' => $data['AccountHolderName'],
            'MandateCode'       => $data['MandateCode'],
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