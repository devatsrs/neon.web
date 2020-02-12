<?php
/**
 * Created by PhpStorm.
 * User: Muhammad Imran
 * Date: 24/01/2018
 * Time: 03:00 PM
 */

class MASAV {

	var $status ;

	function __Construct($CompanyID=0)
	{
		$is_masav = SiteIntegration::CheckIntegrationConfigurationFastPay(true, SiteIntegration::$MASAVSlug,$CompanyID);
		if(!empty($is_masav)){
			$this->status = true;
		}else{
			$this->status = false;
		}
	}

	public function doValidation($data){
		$response 					= [];
		$rules 						= [
			'BranchNo' 		=> 'required',
			'BankCode' 		=> 'required',
			'BankAccount' 	=> 'required'
			];

		$validator 					= Validator::make($data, $rules);
		if ($validator->fails()) {
			$errors 				= "";
			foreach ($validator->messages()->all() as $error){
				$errors .= $error."<br>";
			}

			$response['status'] 	= 'failed';
			$response['message'] 	= $errors;
			return $response;
		}
		$CustomerID 				= $data['AccountID'];
		$account 					= Account::find($CustomerID);
		$CurrencyCode 				= Currency::getCurrency($account->CurrencyId);
		if(empty($CurrencyCode)){
			$response['status'] 	= 'failed';
			$response['message'] 	= cus_lang("PAYMENT_MSG_NO_ACCOUNT_CURRENCY_AVAILABLE");
			return $response;
		}
		
		$data['currency'] 			= strtolower($CurrencyCode);
		$Country 					= $account->Country;
		if(!empty($Country)){
			$CountryCode 			= Country::where(['Country'=>$Country])->pluck('ISO2');
		}else{
			$CountryCode 			= '';
		}
		if(empty($CountryCode)){
			$response['status'] 	= 'failed';
			$response['message'] 	= cus_lang("PAYMENT_MSG_NO_ACCOUNT_COUNTRY_AVAILABLE");
			return $response;
		}
		$response['status'] 		= 'success';
		return $response;
	}

	public function createProfile($data){
		$option 					= [];
		$CustomerID 				= $data['AccountID'];
		$CompanyID 					= $data['CompanyID'];
		$PaymentGatewayID 			= $data['PaymentGatewayID'];
		$isDefault 					= 1;
		$account 					= Account::where(['AccountID' => $CustomerID])->first();
		
		$count 						= AccountPaymentProfile::where(['AccountID' => $CustomerID])
									->where(['CompanyID' => $CompanyID])
									->where(['PaymentGatewayID' => $PaymentGatewayID])
									->where(['isDefault' => 1])
									->count();

		if($count>0){
			$isDefault = 0;
		}
		
        $option['BranchNo'] 		= $data['BranchNo'];
        $option['BankCode'] 		= $data['BankCode'];
        $option['BankAccount'] 		= $data['BankAccount'];

		$CardDetail 				= [
			'Options' => json_encode($option),
			'Status' => 1,
			'isDefault' => $isDefault,
			'created_by' => Customer::get_accountName(),
			'CompanyID' => $CompanyID,
			'AccountID' => $CustomerID,
			'PaymentGatewayID' => $PaymentGatewayID
			];
			$result = AccountPaymentProfile::create($CardDetail);
		if ($result) {
			return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_SUCCESSFULLY_CREATED")));
		} else {
			return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_SAVING_PAYMENT_METHOD_PROFILE")));
		}

    }
    public function updateProfile($data){
        $option						= [];
        $AccountPaymentProfileID    = $data['AccountPaymentProfileID'];
        
        $option['BranchNo']         = $data['BranchNo'];
        $option['BankCode']         = $data['BankCode'];
        $option['BankAccount']      = $data['BankAccount'];

		$CardDetail = [
			'Options'           => json_encode($option),
			'updated_at'		=> date('Y-m-d H:i:s'),
            'updated_by'        => Customer::get_accountName()
        ];

		if (AccountPaymentProfile::where(['AccountPaymentProfileID'=>$AccountPaymentProfileID])->update($CardDetail)) {
			return true;
		} else {
			return false;
		}

	}
    
	public function deleteProfile($data){
		$AccountID 					= $data['AccountID'];
		$CompanyID 					= $data['CompanyID'];
		$AccountPaymentProfileID 	= $data['AccountPaymentProfileID'];

		$count 						= AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
		$PaymentProfile 			= AccountPaymentProfile::find($AccountPaymentProfileID);
		if(!empty($PaymentProfile)){
			$options 				= json_decode($PaymentProfile->Options);
			$isDefault 				= $PaymentProfile->isDefault;
		}else{
			return Response::json(array("status" => "failed", "message" => cus_lang("MESSAGE_RECORD_NOT_FOUND")));
		}
		if($isDefault==1 && $count!=1){
			return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_NOT_DELETE_DEFAULT_PROFILE")));
		}

		if($PaymentProfile->delete()) {
			return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_DELETED")));
		} else {
			return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_DELETING_PAYMENT_METHOD_PROFILE")));
		}
	}
}