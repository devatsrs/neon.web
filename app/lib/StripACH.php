<?php
/**
 * Created by PhpStorm.
 * User: Bhavin
 * Date: 19/17/2017
 * Time: 12:57 PM
 */

class StripeACH {

	var $status ;
	var $stripe_secret_key;
	var $stripe_publishable_key;

	function __Construct($CompanyID=0)
	{
		$is_stripe = SiteIntegration::CheckIntegrationConfiguration(true, SiteIntegration::$StripeACHSlug,$CompanyID);
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
	public static function create_charge($data)
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

		/**
		 * Need to Create token with bank detail
		 * with token after that create customer
		 * verify customer with default amount
		 *
		*/
		Log::info('token creation start');
		/**
		 * Country should in ISO2(like us,uk,in)
		 * Currency should in ISO3(like usd,gbp,eur)
		*/
		try{
			$token = Stripe::tokens()->create([
				'bank_account' => [
					'country'    		  => $data['country'],
					'currency' 		      => $data['currency'],
					'routing_number'      => $data['routing_number'],
					'account_number' 	  => $data['account_number'],
					'account_holder_name' => $data['account_holder_name'],
					'account_holder_type' => $data['account_holder_type']
				],
			]);
			Log::info(print_r($token,true));

		} catch (Exception $e) {
			Log::error($e);
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
			return $response;
		}

		if(empty($token) || $token['id'] == ''){
			Log::error(print_r($response,true));
			return $response;
		}
		Log::info('token creation end');

		Log::info('customer creation start');
		try{
			$customer = Stripe::customers()->create([
				'email' => $data['email'],
				'description' => $data['account'],
				'source'=>$token['id']]);

			Log::info(print_r($customer,true));
			if(!empty($customer['id'])){
				$response['status'] = 'success';
				$response['VerifyStatus'] = '';
				$response['CustomerProfileID'] = $customer['id'];
				$response['BankAccountID'] = $customer['default_source'];
			}

		} catch (Exception $e) {
			Log::error($e);
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
			return $response;
		}
		Log::info('customer creation end');

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

			if(empty($charge['failure_message'])){
				$response['response_code'] = 1;
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
	public function verifyBankAccount($data){
		$response = array();
		$customerId = $data['CustomerProfileID'];
		$bankAccountId = $data['BankAccountID'];
		$MicroDeposit1 = $data['MicroDeposit1'];
		$MicroDeposit2 = $data['MicroDeposit2'];
		try{
			/**
			 * Need to add to micro payment
			 * for test purpose just add 32,45
			 */
			//$varify = Stripe::BankAccounts()->verify($customerId,$bankAccountId,array(32, 45));
			$varify = Stripe::BankAccounts()->verify($customerId,$bankAccountId,array($MicroDeposit1, $MicroDeposit2));
			Log::info(print_r($varify,true));
			if(!empty($varify['id'])){
				$response['status'] = 'Success';
				$response['VerifyStatus'] = $varify['status'];
			}
		} catch (Exception $e) {
			Log::error($e);
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
			return $response;
		}

		return $response;
	}

	public function doValidation($data){
		$ValidationResponse = array();
		$rules = array(
			'AccountNumber' => 'required|digits_between:6,19',
			'RoutingNumber' => 'required',
			'AccountHolderType' => 'required',
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


		$StripeResponse = array();
		$stripedata['account_holder_name'] = $data['AccountHolderName'];
		$stripedata['account_number'] = $data['AccountNumber'];
		$stripedata['routing_number'] = $data['RoutingNumber'];
		$stripedata['account_holder_type'] = $data['AccountHolderType'];
		$stripedata['country'] = $data['country'];
		$stripedata['currency'] =  $data['currency'];
		$stripedata['email'] = $email;
		$stripedata['account'] = $accountname;

		$StripeResponse = $this->create_customer($stripedata);

		if ($StripeResponse["status"] == "success") {
			$option = array(
				'CustomerProfileID' => $StripeResponse['CustomerProfileID'],
				'BankAccountID' => $StripeResponse['BankAccountID'],
				'VerifyStatus' => '',
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

	public function doVerify($data){
		if(empty($data['MicroDeposit1']) || empty($data['MicroDeposit2'])){
			return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_BOTH_MICRODEPOSIT_REQUIRED")));
		}
		$cardID = $data['cardID'];
		$AccountPaymentProfile = AccountPaymentProfile::find($cardID);
		$options = json_decode($AccountPaymentProfile->Options,true);
		$CustomerProfileID = $options['CustomerProfileID'];
		$BankAccountID = $options['BankAccountID'];
		$stripedata = array();
		$stripedata['CustomerProfileID'] = $CustomerProfileID;
		$stripedata['BankAccountID'] = $BankAccountID;
		$stripedata['MicroDeposit1'] = $data['MicroDeposit1'];
		$stripedata['MicroDeposit2'] = $data['MicroDeposit2'];

		$StripeResponse = $this->verifyBankAccount($stripedata);
		if($StripeResponse['status']=='Success'){
			if($StripeResponse['VerifyStatus']=='verified'){
				$option = array(
					'CustomerProfileID' => $CustomerProfileID,
					'BankAccountID' => $BankAccountID,
					'VerifyStatus' => $StripeResponse['VerifyStatus']
				);
				$AccountPaymentProfile->update(array('Options' => json_encode($option)));

				return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_STRIPEACH_MSG_VERIFICATION_STATUS_IS").$StripeResponse['VerifyStatus']));
			}else{
				return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_STRIPEACH_MSG_VERIFICATION_STATUS_IS").$StripeResponse['VerifyStatus']));
			}

		}else{
			return Response::json(array("status" => "failed", "message" => $StripeResponse['error']));
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
		$CustomerProfile = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
		$StripeObj = json_decode($CustomerProfile->Options);
		if(empty($StripeObj->VerifyStatus) || $StripeObj->VerifyStatus!=='verified'){
			$Response['status']='failed';
			$Response['message']=cus_lang("PAYMENT_MSG_BANK_ACCOUNT_NOT_VERIFIED");
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
			$Notes = 'Stripe ACH transaction_id ' . $transaction['id'];
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
		$transactionResponse['PaymentMethod'] = 'BANK TRANSFER';
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

	public function paymentValidateWithBankDetail($data){

	}
}