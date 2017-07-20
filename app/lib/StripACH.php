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

	function __Construct()
	{
		$is_stripe = SiteIntegration::CheckIntegrationConfiguration(true, SiteIntegration::$StripeACHSlug);
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

	public static function create_customer($data){
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
				$response['CustomerProfileID'] = $customer['id'];
				$response['BankAccountID'] = $customer['default_source'];
			}

		} catch (Exception $e) {
			Log::error($e);
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
			return $response;
		}
		Log::info('customer creation start');
		$customerId = $customer['id'];
		$bankAccountId = $customer['default_source'];
		try{
			$varify = Stripe::BankAccounts()->verify($customerId,$bankAccountId,array(32, 45));
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

	public static function deleteCustomer($CustomerProfileID){
		$response = array();
		try {
			$customer = Stripe::customers()->delete($CustomerProfileID);
			if(!empty($customer['deleted'])){
				$response['status'] = 'Success';
			}else{
				$response['status'] = 'fail';
				$response['error'] = 'Problem deleting Payment Method Profile';
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

	public static function createchargebycustomer($data)
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

	public static function create_customer_bank(){
		$response = array();
		$token = array();
		$customer = array();
		Log::error('start');

		/*
		$token = Stripe::BankAccounts()->verify('cus_9wMcHZMnNqUQAR','ba_1AgvccCLEhHAk25KqUgkBI1Q',array(32, 45));

		Log::info(print_r($token,true));

		exit;

		$token = Stripe::BankAccounts()->find('cus_9wMcHZMnNqUQAR','ba_1AgvccCLEhHAk25KqUgkBI1Q');

		Log::info(print_r($token,true));

		exit;

		$token = Stripe::BankAccounts()->create('cus_9wMcHZMnNqUQAR',[
			'account_number'  => '000123456789',
			'country'    => 'us',
			'currency' => 'usd',
			'routing_number'       => '110000000',
			'account_holder_name'  => 'Jenny Rosen',
			'account_holder_type' => 'individual'
		]);

		Log::info(print_r($token,true));

		exit;

		try{
			$token = Stripe::tokens()->create([
				'bank_account' => [
					'country'    => 'us',
					'currency' => 'usd',
					'routing_number'       => '110000000',
					'account_number'  => '000123456789',
					'account_holder_name'  => 'Jenny Rosen',
					'account_holder_type' => 'individual'
				],
			]);
			Log::info(print_r($token,true));

		} catch (Exception $e) {
			Log::error($e);
			//return ["return_var"=>$e->getMessage()];
			$response['status'] = 'fail';
			$response['error'] = $e->getMessage();
			Log::error(print_r($response,true));
		}

		if(empty($token) || $token['id'] == ''){
			Log::error(print_r($response,true));
			exit;
			//return $response;
		}
		*/


	}
}