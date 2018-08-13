<?php
/**
 * Created by PhpStorm.
 * User: Bhavin
 * Date: 8/22/2015
 * Time: 12:57 PM
 */

class StripeBilling {

	var $status ;
	var $stripe_secret_key;
	var $stripe_publishable_key;

	function __Construct($CompanyID=0)
	{
		$is_stripe = SiteIntegration::CheckIntegrationConfiguration(true, SiteIntegration::$StripeSlug,$CompanyID);
		if(!empty($is_stripe)){
			$this->stripe_secret_key = $is_stripe->SecretKey;
			$this->stripe_publishable_key = $is_stripe->PublishableKey;

			/**
			 * Whenever you need work with stripe first we need to set key and version in services config
			 */

			Config::set('services.stripe.secret', $is_stripe->SecretKey);
			Config::set('services.stripe.version', '2016-07-06');
			$this->status = true;;
		}else{
			$this->status = false;
		}

	}

	/**
	 * Invoice Payment with stripe
	*/
	public function create_charge($data)
	{
		$response = array();
		$token = array();
		$charge = array();
		try{
			$token = Stripe::tokens()->create([
				'card' => [
					'number'    => $data['number'],
					'exp_month' => $data['exp_month'],
					'cvc'       => $data['cvc'],
					'exp_year'  => $data['exp_year'],
					'name' => $data['name']
				],
			]);
			//Log::info(print_r($token,true));

		} catch (Exception $e) {
			Log::error($e);
			//return ["return_var"=>$e->getMessage()];
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
		}

		if(empty($token) || $token['id'] == ''){
			return $response;
		}

		try{
			//$data['amount'] = '1';
			//$data['currency'] = 'jpy';
			$charge = Stripe::charges()->create([
				'amount' => $data['amount'], // $10
				'currency' => $data['currency'],
				'description' => $data['description'],
				'card'=>$token['id'],
				'capture'=>true
			]);

			if(!empty($charge['paid'])){
				$response['status'] = 'Success';
				$response['id'] = $charge['id'];
				$response['note'] = 'Stripe transaction_id '.$charge['id'];
				$Amount = ($charge['amount']/100);
				$response['amount'] = $Amount;
				$response['response'] = $charge;
			}else{
				$response['status'] = 'fail';
				$response['error'] = $charge['failure_message'];
			}


		} catch (Exception $e) {
			Log::error($e);
			//return ["return_var"=>$e->getMessage()];
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
		}

		return $response;

	}

	public function create_customer($data){
		$response = array();
		$token = array();
		$customer = array();
		try{
			$token = Stripe::tokens()->create([
				'card' => [
					'number'    => $data['number'],
					'exp_month' => $data['exp_month'],
					'cvc'       => $data['cvc'],
					'exp_year'  => $data['exp_year'],
					'name' => $data['name']
				],
			]);
			//Log::info(print_r($token,true));

		} catch (Exception $e) {
			Log::error($e);
			//return ["return_var"=>$e->getMessage()];
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
		}

		if(empty($token) || $token['id'] == ''){
			return $response;
		}

		try{
			$customer = Stripe::customers()->create([
				'email' => $data['email'],
				'description' => $data['account'],
				'source'=>$token['id']]);

			//Log::info(print_r($customer,true));

			if(!empty($customer['id'])){
				$response['status'] = 'success';
				$response['CustomerProfileID'] = $customer['id'];
				$response['CardID'] = $customer['default_source'];
				$response['response'] = $customer;
			}else{
				$response['status'] = 'fail';
				$response['error'] = $customer['failure_message'];
			}


		} catch (Exception $e) {
			Log::error($e);
			//return ["return_var"=>$e->getMessage()];
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
		}

		return $response;

	}

	public function deleteCustomer($CustomerProfileID){
		$response = array();
		try {
			$customer = Stripe::customers()->delete($CustomerProfileID);
			if(!empty($customer['deleted'])){
				$response['status'] = 'Success';
			}else{
				$response['status'] = 'fail';
				$response['error'] = cus_lang("PAYMENT_MSG_PROBLEM_DELETING_PAYMENT_METHOD_PROFILE");
			}
			//log::info(print_r($customer, true));
		}catch (Exception $e) {
			Log::error($e);
			//return ["return_var"=>$e->getMessage()];
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
		}
		return $response;
	}

	public function createchargebycustomer($data)
	{
		$response = array();
		$token = array();
		$charge = array();
		try{

			$charge = Stripe::charges()->create([
				'amount' => $data['amount'], // $10
				'currency' => $data['currency'],
				'description' => $data['description'],
				'customer' => $data['customerid'],
				'capture'=>true
			]);

			//log::info(print_r($charge,true));

			if(!empty($charge['paid'])){
				$response['response_code'] = $charge['paid'];
				$response['status'] = 'Success';
				$response['id'] = $charge['id'];
				$response['note'] = 'Stripe transaction_id '.$charge['id'];
				$Amount = ($charge['amount']/100);
				$response['amount'] = $Amount;
				$response['response'] = $charge;
			}else{
				$response['status'] = 'fail';
				$response['error'] = $charge['failure_message'];
			}


		} catch (Exception $e) {
			Log::error($e);
			//return ["return_var"=>$e->getMessage()];
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
		}

		return $response;

	}
	public function doValidation($data){
		$ValidationResponse = array();
		$rules = array(
			'CardNumber' => 'required|digits_between:14,19',
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

		$CustomerID = $data['AccountID'];
		$CompanyID = $data['CompanyID'];
		$PaymentGatewayID=$data['PaymentGatewayID'];

		$stripedata = array();

		$account = Account::where(array('AccountID' => $CustomerID))->first();
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

		$StripeResponse = array();
		$stripedata['number'] = $data['CardNumber'];
		$stripedata['exp_month'] = $data['ExpirationMonth'];
		$stripedata['cvc'] = $data['CVVNumber'];
		$stripedata['exp_year'] = $data['ExpirationYear'];
		$stripedata['name'] = $data['NameOnCard'];
		$stripedata['email'] = $email;
		$stripedata['account'] = $accountname;

		$StripeResponse = $this->create_customer($stripedata);

		if ($StripeResponse["status"] == "success") {
			$option = array(
				'CustomerProfileID' => $StripeResponse['CustomerProfileID'],
				'CardID' => $StripeResponse['CardID']
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
			return Response::json(array("status" => "failed", "message" => $StripeResponse['error']));
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
			$CustomerProfileID = $options->CustomerProfileID;
			$isDefault = $PaymentProfile->isDefault;
		}else{
			return Response::json(array("status" => "failed", "message" => cus_lang("MESSAGE_RECORD_NOT_FOUND")));
		}
		if($isDefault==1){
			if($count!=1){
				return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_NOT_DELETE_DEFAULT_PROFILE")));
			}
		}

		$result = $this->deleteCustomer($CustomerProfileID);

		if($result["status"]=="Success"){
			if($PaymentProfile->delete()) {
				return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_DELETED")));
			} else {
				return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_DELETING_PAYMENT_METHOD_PROFILE")));
			}
		}else{
			return Response::json(array("status" => "failed", "message" => $result['error']));
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

	public function paymentWithProfile($data){

		$account = Account::find($data['AccountID']);

		$CustomerProfile = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
		$StripeObj = json_decode($CustomerProfile->Options);

		$CurrencyCode = Currency::getCurrency($account->CurrencyId);
		$stripedata = array();
		$stripedata['currency'] = strtolower($CurrencyCode);
		$stripedata['amount'] = $data['outstanginamount'];
		$stripedata['description'] = $data['InvoiceNumber'].' (Invoice) Payment';
		$stripedata['customerid'] = $StripeObj->CustomerProfileID;

		$transactionResponse = array();

		$transaction = $this->createchargebycustomer($stripedata);

		$Notes = '';
		if(!empty($transaction['response_code']) && $transaction['response_code'] == 1) {
			$Notes = 'Stripe transaction_id ' . $transaction['id'];
			$Status = TransactionLog::SUCCESS;
		}else{
			$Status = TransactionLog::FAILED;
			$Notes = empty($transaction['error']) ? '' : $transaction['error'];
			//AccountPaymentProfile::setProfileBlock($AccountPaymentProfileID);
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
		$Response = array();
		$Response['status']='success';

		$ValidateResonse = $this->doValidation($data);
		if($ValidateResonse['status']=='failed'){
			return $ValidateResonse;
		}

		$account = Account::find($data['AccountID']);
		$CurrencyCode = Currency::getCurrency($account->CurrencyId);
		if(empty($CurrencyCode)){
			$Response['status']='failed';
			$Response['message']=cus_lang("PAYMENT_MSG_NO_ACCOUNT_CURRENCY_AVAILABLE");
		}
		return $Response;
	}

	public function paymentWithCreditCard($data){
		$account = Account::find($data['AccountID']);
		$CurrencyCode = Currency::getCurrency($account->CurrencyId);

		$stripedata = array();
		$stripedata['number'] = $data['CardNumber'];
		$stripedata['exp_month'] = $data['ExpirationMonth'];
		$stripedata['cvc'] = $data['CVVNumber'];
		$stripedata['exp_year'] = $data['ExpirationYear'];
		$stripedata['name'] = $data['NameOnCard'];

		$stripedata['amount'] = $data['GrandTotal'];
		$stripedata['currency'] = strtolower($CurrencyCode);
		$stripedata['description'] = $data['InvoiceNumber'].' (Invoice) Payment';
		$stripedata['CurrencyCode'] = $CurrencyCode;

		log::info('Payment with card start');
		$StripeResponse = $this->create_charge($stripedata);
		log::info('Payment with card end');
		$Response = array();

		if ($StripeResponse['status'] == 'Success') {
			$Response['PaymentMethod'] = 'CREDIT CARD';
			$Response['transaction_notes'] = $StripeResponse['note'];
			$Response['Amount'] = $StripeResponse['amount'];
			$Response['Transaction'] = $StripeResponse['id'];
			$Response['Response']=$StripeResponse['response'];
			$Response['status'] = 'success';
		}else{
			$Response['transaction_notes'] = $StripeResponse['error'];
			$Response['status'] = 'failed';
			$Response['Response']='';
		}

		return $Response;

	}

	public function paymentValidateWithApiCreditCard($data){
		$Response = array();
		$Response['status']='success';
		$CurrencyCode = '';

		$ValidateResonse = $this->doValidation($data);
		if($ValidateResonse['status']=='failed'){
			return $ValidateResonse;
		}

		//need CurrencyID
		if(!empty($data['CurrencyId'])){
			$CurrencyCode = Currency::getCurrency($data['CurrencyId']);
		}
		if(empty($CurrencyCode)){
			$Response['status']='failed';
			$Response['message']=cus_lang("PAYMENT_MSG_NO_ACCOUNT_CURRENCY_AVAILABLE");
		}
		return $Response;
	}

	public function paymentWithApiCreditCard($data){
		$CurrencyCode = Currency::getCurrency($data['CurrencyId']);

		$stripedata = array();
		$stripedata['number'] = $data['CardNumber'];
		$stripedata['exp_month'] = $data['ExpirationMonth'];
		$stripedata['cvc'] = $data['CVVNumber'];
		$stripedata['exp_year'] = $data['ExpirationYear'];
		$stripedata['name'] = $data['NameOnCard'];

		$stripedata['amount'] = $data['GrandTotal'];
		$stripedata['currency'] = strtolower($CurrencyCode);
		$stripedata['description'] = $data['InvoiceNumber'].' (Invoice) Payment';
		$stripedata['CurrencyCode'] = $CurrencyCode;

		log::info('Payment with card start');
		$StripeResponse = $this->create_charge($stripedata);
		log::info('Payment with card end');
		$Response = array();

		if ($StripeResponse['status'] == 'Success') {
			$Response['PaymentMethod'] = 'CREDIT CARD';
			$Response['transaction_notes'] = $StripeResponse['note'];
			$Response['Amount'] = $StripeResponse['amount'];
			$Response['Transaction'] = $StripeResponse['id'];
			$Response['Response']=$StripeResponse['response'];
			$Response['status'] = 'success';
		}else{
			$Response['transaction_notes'] = $StripeResponse['error'];
			$Response['status'] = 'failed';
			$Response['Response']='';
		}

		return $Response;
	}
}