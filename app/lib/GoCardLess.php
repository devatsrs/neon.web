<?php
/**
 * Created by PhpStorm.
 * User: Bilal
 * Date: 19/17/2017
 * Time: 12:57 PM
 */
use App\GoCardlessPro;

class GoCardLess {

	var $status ;
	var $gocardless_access_key;
	var $isLive;

	public static $BacsScheme = "bacs";
	public static $client;
	function __Construct($CompanyID=0)
	{
		$is_goCardLess = SiteIntegration::CheckIntegrationConfiguration(true, SiteIntegration::$GoCardLessSlug,$CompanyID);
		if(!empty($is_goCardLess)){
			$this->gocardless_access_key = $is_goCardLess->AccessKey;
			$this->isLive = $is_goCardLess->isLive;

			self::$client = new GoCardlessPro\Client([
				'access_token' => $this->gocardless_access_key,
				'environment'  => $this->isLive != 1 ? GoCardlessPro\Environment::SANDBOX : GoCardlessPro\Environment::LIVE
			]);
			/**
			 * Whenever you need work with GoCardLess first we need to set key and version in services config
			 */

			Config::set('services.gocardless.access', $is_goCardLess->AccessKey);
			Config::set('services.gocardless.version', '2016-07-06');
			$this->status = true;;
		}else{
			$this->status = false;
		}

	}

	/**
	 * Invoice Payment with GoCardLess
	 */
	public static function create_charge($data)
	{
		$response = array();
		$payment = array();
		try{
			$payment = self::$client->payments()->create([
				"params" => [
					"amount" 		=> ($data['amount'] * 100),
					"currency" 		=> strtoupper($data['currency']),
					"description" 	=> $data['description'],
					"links" => [
						"mandate" => $data['mandateid']
					]]
			]);

		} catch (Exception $e) {
			Log::error($e);
			//return ["return_var"=>$e->getMessage()];
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
		}

		if(!empty($payment) && isset($payment->status) && $payment->status != 'mandate_is_inactive'){
			$response['response_code'] = 1;
			$response['status'] = 'Success';
			$response['id'] = $payment->id;
			$response['note'] = 'GoCardLess transaction_id '.$payment->id;
			$Amount = ($payment->amount/100);
			$response['amount'] = $Amount;
			$response['response'] = $payment;
		} else {
			$response['status'] = 'fail';
			$response['error'] = $payment->status;
		}

		return $response;

	}

	public function create_customer($data){

		/**
		 * Need to Create token with bank detail
		 * with token after that create customer
		 * verify customer with default amount
		 *
		 */
		Log::info('Customer creation start');
		/**
		 * Country should in ISO2(like us,uk,in)
		 * Currency should in ISO3(like usd,gbp,eur)
		 */

		try{
			$customer = self::$client->customers()->create([
				"params" => [
					"email" => $data['email'],
					"given_name" => $data['firstname'],
					"family_name" => $data['surname'],
					"country_code" => strtoupper($data['country'])
				]
			]);

			//Log::info(print_r($customer, true));

			$bankAccount = [];
			if(isset($customer->id)) {
				$bankAccount = self::$client->customerBankAccounts()->create([
					'params' => [
						'country_code' 	 => strtoupper($data['country']),
						'currency' 		 => strtoupper($data['currency']),
						'branch_code' 	 => $data['routing_number'],
						'account_number' => $data['account_number'],
						'account_holder_name' => $data['account_holder_name'],
						//'account_type' => $data['account_holder_type'],
						'links' 		 => ['customer' => $customer->id]
					],
				]);

			} else
				return ['status' => 'fail', 'error' => 'Something went wrong while creating customer.'];
			//Log::info(print_r($bankAccount, true));

			$mandate = [];
			if(!empty($bankAccount) && isset($bankAccount->id)) {
				$mandate = self::$client->mandates()->create([
					'params' => [
						"scheme"  => self::$BacsScheme,
						'links'   => ['customer_bank_account' => $bankAccount->id]
					],
				]);

			} else
				return ['status' => 'fail', 'error' => 'Something went wrong while adding bank account.'];
			//Log::info(print_r($mandate, true));

			if(!empty($mandate) && isset($mandate->id)){
				return [
					'status' 		=> 'success',
					'CustomerID' 	=> $customer->id,
					'BankAccountID' => $bankAccount->id,
					'MandateID' 	=> $mandate->id,
				];

			} else
				return ['status' => 'fail', 'error' => 'Something went wrong while creating mandate.'];

		} catch (Exception $e) {
			Log::error($e);
			return ['status' => 'fail', 'error' => $e->getMessage()];
		}
	}

	public function cancelMandate($MandateID){
		$response = array();
		try {
			$mandate = self::$client->mandates()->cancel($MandateID);
			if(!empty($mandate)){
				$response['status'] = 'Success';
			}else{
				$response['status'] = 'fail';
				$response['error'] = cus_lang("PAYMENT_MSG_PROBLEM_DELETING_PAYMENT_METHOD_PROFILE");
			}
			//Log::info(print_r($mandate, true));
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
		Log::useFiles(storage_path() . '/logs/gocardless-' . '-' . date('Y-m-d') . '.log');
		$response = array();
		try{

			$payment = self::$client->payments()->create([
				"params" => [
					"amount" 		=> ($data['amount'] * 100),
					"currency" 		=> strtoupper($data['currency']),
					"description" 	=> $data['description'],
					"links" => [
						"mandate" => $data['mandateid']
					]
				]
			]);
			//Log::info(print_r($payment,true));

			if(!empty($payment) && isset($payment->status) && $payment->status != 'mandate_is_inactive'){
				$response['response_code'] = 1;
				$response['status'] = 'Success';
				$response['id'] = $payment->id;
				$response['note'] = 'GoCardLess transaction_id '.$payment->id;
				$Amount = ($payment->amount/100);
				$response['amount'] = $Amount;
				$response['response'] = $payment;
			} else {
				$response['status'] = 'fail';
				$response['error'] = $payment->status;
			}

			//Log::info($payment->id);
			//Log::info(print_r($response, true));


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

		$profileData['account_holder_name'] = $data['AccountHolderName'];
		$profileData['account_number'] 		= $data['AccountNumber'];
		$profileData['routing_number'] 		= $data['RoutingNumber'];
		$profileData['account_holder_type'] = $data['AccountHolderType'];
		$profileData['country'] 			= $data['country'];
		$profileData['currency'] 			= $data['currency'];
		$profileData['email'] 				= $email;
		$profileData['account'] 			= $accountname;
		$profileData['firstname'] 			= empty($account->FirstName)?'':$account->FirstName;
		$profileData['surname'] 			= empty($account->LastName)?'':$account->LastName;

		$GoCardLessResponse = $this->create_customer($profileData);

		if ($GoCardLessResponse["status"] == "success") {
			$option = array(
				'CustomerID' 	=> $GoCardLessResponse['CustomerID'],
				'BankAccountID' => $GoCardLessResponse['BankAccountID'],
				'MandateID' 	=> $GoCardLessResponse['MandateID'],
				'VerifyStatus' 	=> '',
			);

			$CardDetail = array(
				'Title' 	  => $data['Title'],
				'Options' 	  => json_encode($option),
				'Status' 	  => 1,
				'isDefault'   => $isDefault,
				'created_by'  => Customer::get_accountName(),
				'CompanyID'   => $CompanyID,
				'AccountID'   => $CustomerID,
				'PaymentGatewayID' => $PaymentGatewayID);

			if (AccountPaymentProfile::create($CardDetail)) {
				return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_MSG_PAYMENT_METHOD_PROFILE_SUCCESSFULLY_CREATED")));
			} else {
				return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_PROBLEM_SAVING_PAYMENT_METHOD_PROFILE")));
			}
		}else{
			return Response::json(array("status" => "failed", "message" => $GoCardLessResponse['error']));
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
			$MandateID = $options->MandateID;
			$isDefault = $PaymentProfile->isDefault;
		}else{
			return Response::json(array("status" => "failed", "message" => cus_lang("MESSAGE_RECORD_NOT_FOUND")));
		}
		if($isDefault==1){
			if($count!=1){
				return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_NOT_DELETE_DEFAULT_PROFILE")));
			}
		}

		$result = $this->cancelMandate($MandateID);

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
		$CustomerProfile = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
		$ProfileObj = json_decode($CustomerProfile->Options);
		if(empty($ProfileObj->VerifyStatus) || $ProfileObj->VerifyStatus!=='verified'){
			$Response['status']='failed';
			$Response['message']=cus_lang("PAYMENT_MSG_BANK_ACCOUNT_NOT_VERIFIED");
		}

		return $Response;
	}


	public function paymentWithProfile($data){
		$account = Account::find($data['AccountID']);

		$CustomerProfile = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
		$GoCardLessObj = json_decode($CustomerProfile->Options);

		$CurrencyCode = Currency::getCurrency($account->CurrencyId);
		$profileData = array();
		$profileData['currency'] 	= strtolower($CurrencyCode);
		$profileData['amount'] 		= $data['outstanginamount'];
		$profileData['description'] = $data['InvoiceNumber'].' (Invoice) Payment';
		$profileData['customerid'] 	= $GoCardLessObj->CustomerID;
		$profileData['mandateid'] 	= $GoCardLessObj->MandateID;

		$transactionResponse = array();

		$transaction = $this->createchargebycustomer($profileData);

		$Notes = '';
		if(!empty($transaction['response_code']) && $transaction['response_code'] == 1) {
			$Notes = 'GoCardLess transaction_id ' . $transaction['id'];
			$Status = TransactionLog::SUCCESS;
		}else{
			$Status = TransactionLog::FAILED;
			$Notes = empty($transaction['error']) ? '' : $transaction['error'];
		}
		$transactionResponse['transaction_notes'] = $Notes;
		if(!empty($transaction['response_code'])) {
			$transactionResponse['response_code'] = $transaction['response_code'];
		}
		$transactionResponse['PaymentMethod'] = 'BANK TRANSFER';
		$transactionResponse['failed_reason'] = $Notes;
		if(!empty($transaction['id'])) {
			$transactionResponse['transaction_id'] = $transaction['id'];
		}
			$transactionResponse['Response'] = $transaction;
			$transactionResponse['PaymentStatus'] = 'Pending Approval';

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
		$transactiondata['Status'] 		= $Status;
		$transactiondata['created_at'] 	= date('Y-m-d H:i:s');
		$transactiondata['updated_at'] 	= date('Y-m-d H:i:s');
		$transactiondata['CreatedBy'] 	= $data['CreatedBy'];
		$transactiondata['ModifyBy'] 	= $data['CreatedBy'];
		$transactiondata['Response'] 	= json_encode($transaction);
		TransactionLog::insert($transactiondata);
		return $transactionResponse;
	}

}