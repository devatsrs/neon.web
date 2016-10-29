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

	function __Construct()
	{
		$is_stripe = SiteIntegration::CheckIntegrationConfiguration(true, SiteIntegration::$StripeSlug);
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
				'card'=>$token['id']]);

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
}