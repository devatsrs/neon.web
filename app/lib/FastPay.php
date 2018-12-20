<?php
/**
 * Created by PhpStorm.
 * User: Badal
 * Date: 16/11/2018
 * Time: 03:00 PM
 */

class FastPay {

	var $status ;

	function __Construct($CompanyID=0)
	{
		$is_FastPay = SiteIntegration::CheckIntegrationConfigurationFastPay(true, SiteIntegration::$FastPaySlug,$CompanyID);
		if(!empty($is_FastPay)){
			$this->status = true;
		}else{
			$this->status = false;
		}
	}

	public function doValidation($data){
		$ValidationResponse = array();
		$rules = array(
			'AccountNumber' => 'required|digits_between:6,19',
			'DDReference' => 'required',
			'SortCode' => 'required'
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
		$CustomerID = $data['AccountID'];
		$account = Account::find($CustomerID);
		$CurrencyCode = Currency::getCurrency($account->CurrencyId);
		if(empty($CurrencyCode)){
			$ValidationResponse['status'] = 'failed';
			$ValidationResponse['message'] = cus_lang("PAYMENT_MSG_NO_ACCOUNT_CURRENCY_AVAILABLE");
			return $ValidationResponse;
		}
		$data['currency'] = strtolower($CurrencyCode);
		$Country = $account->Country;
		if(!empty($Country)){
			$CountryCode = Country::where(['Country'=>$Country])->pluck('ISO2');
		}else{
			$CountryCode = '';
		}
		if(empty($CountryCode)){
			$ValidationResponse['status'] = 'failed';
			$ValidationResponse['message'] = cus_lang("PAYMENT_MSG_NO_ACCOUNT_COUNTRY_AVAILABLE");
			return $ValidationResponse;
		}
		$ValidationResponse['status'] = 'success';
		return $ValidationResponse;
	}

	public function createProfile($data){
		$CustomerID = $data['AccountID'];
		$CompanyID = $data['CompanyID'];
		$PaymentGatewayID=$data['PaymentGatewayID'];

		$account = Account::where(array('AccountID' => $CustomerID))->first();
		$CurrencyCode = Currency::getCurrency($account->CurrencyId);
		$data['currency'] = strtolower($CurrencyCode);
		$Country = $account->Country;
		$CountryCode = Country::where(['Country'=>$Country])->pluck('ISO2');

		$data['currency'] = strtolower($CurrencyCode);
		$data['country'] = strtolower($CountryCode);

		$isDefault = 1;

		$count = AccountPaymentProfile::where(['AccountID' => $CustomerID])
			->where(['CompanyID' => $CompanyID])
			->where(['PaymentGatewayID' => $PaymentGatewayID])
			->where(['isDefault' => 1])
			->count();

		if($count>0){
			$isDefault = 0;
		}

		$email = empty($account->BillingEmail)?'':$account->BillingEmail;
		$accountname = empty($account->AccountName)?'':$account->AccountName;

		$option=array();
		$option['account_number'] = $data['AccountNumber'];
		$option['dd_reference'] = $data['DDReference'];
		$option['sortcode'] = $data['SortCode'];
		$option['country'] = $data['country'];
		$option['currency'] =  $data['currency'];
		$option['email'] = $email;
		$option['account'] = $accountname;

		$CardDetail = array(
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

	}

	public function deleteProfile($data){
		$AccountID = $data['AccountID'];
		$CompanyID = $data['CompanyID'];
		$AccountPaymentProfileID=$data['AccountPaymentProfileID'];

		$count = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
		$PaymentProfile = AccountPaymentProfile::find($AccountPaymentProfileID);
		if(!empty($PaymentProfile)){
			$options = json_decode($PaymentProfile->Options);
			$isDefault = $PaymentProfile->isDefault;
		}else{
			return Response::json(array("status" => "failed", "message" => cus_lang("MESSAGE_RECORD_NOT_FOUND")));
		}
		if($isDefault==1){
			if($count!=1){
				return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_NOT_DELETE_DEFAULT_PROFILE")));
			}
		}

		if($PaymentProfile->delete()) {
			return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_DELETED")));
		} else {
			return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_DELETING_PAYMENT_METHOD_PROFILE")));
		}
	}
}