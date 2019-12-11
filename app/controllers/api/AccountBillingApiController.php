<?php
use app\controllers\api\Codes;

class AccountBillingApiController extends ApiController {

	public function getAccountBilling($AccountID)
	{
		$fields=["AccountBillingID", "AccountID", "BillingType", "BillingCycleType", "BillingCycleValue", "BillingClassID"];
		$AccountBilling =  AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>0))->select($fields)->first();
		return Response::json(["status"=>"success", $AccountBilling]);
	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * @Response
	 * 	AutoTopup,MinThreshold,TopupAmount
	 */
	public function getAutoDepositSettings(){
		$data=array();
		$post_vars = json_decode(file_get_contents("php://input"));
		if(!empty($post_vars)){
			$data=json_decode(json_encode($post_vars),true);
		}

		$AccountID=0;
		if(!empty($data['AccountID'])){
			$AccountID=$data['AccountID'];

		}else if(!empty($data['AccountNo'])) {
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}

		} else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo Field is Required."],Codes::$Code402[0]);
		}

		$Account = Account::find($AccountID);
		if(empty($Account)){
			return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
		}

		$Result=AccountPaymentAutomation::where('AccountID',$AccountID)->get(['AutoTopup','MinThreshold','TopupAmount']);

		$Result = $Result != false ? $Result->first() : $Result;
		return Response::json($Result,Codes::$Code200[0]);
	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * AutoTopup,MinThreshold,TopupAmount
	 */
	public function setAutoDepositSettings(){
		$post_vars = json_decode(file_get_contents("php://input"));
		$data=json_decode(json_encode($post_vars),true);

		$AccountID=0;
		if(!empty($data['AccountID'])){
			$AccountID=$data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}else{
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}

		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo Field is Required."],Codes::$Code402[0]);
		}
		$AccountCount=Account::where('AccountID',$AccountID)->count();
		if($AccountCount > 0) {
			$AccountPaymentAutomation = AccountPaymentAutomation::where('AccountID', $AccountID);
			$CountAccountPaymentAutomation = $AccountPaymentAutomation->count();
			if ($CountAccountPaymentAutomation > 0) {
				//update
				$AccountPaymentAutomationObj = $AccountPaymentAutomation->first();
				return $this->updateAutoDepositSetting($data, $AccountPaymentAutomationObj);

			} else {
				//return Response::json(array("status" => "failed", "message" => "Account Not Found."));
				//Create Record
				return $this->createAutoDepositSetting($data, $AccountID);
			}
		}else{
			return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
		}
	}

	public function updateAutoDepositSetting($data,$AccountPaymentAutomationObj){
		$rules = array(
			'AutoTopup' => 'required',
			'MinThreshold' => 'required',
			'TopupAmount' => 'required',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrv');

		$validator = Validator::make($data, $rules);
		$validator->setPresenceVerifier($verifier);

		if ($validator->fails()) {
			//return json_validator_response($validator);
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
		}
		unset($data['AccountID']);
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);
		$data['updated_at']=date('Y-m-d H:i:s');

		if ($AccountPaymentAutomationObj->update($data)) {
			return Response::json((object)['status' => "success"],Codes::$Code200[0]);
		} else {
			return Response::json(array("ErrorMessage" => "Problem Updating Auto Deposit Settings."),Codes::$Code402[0]);
		}

	}

	public function createAutoDepositSetting($data,$AccountID){
		$rules = array(
			'AutoTopup' => 'required',
			'MinThreshold' => 'required',
			'TopupAmount' => 'required',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrv');

		$validator = Validator::make($data, $rules);
		$validator->setPresenceVerifier($verifier);

		if ($validator->fails()) {
			//return json_validator_response($validator);
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
		}
		$data['AccountID']=$AccountID;
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);

		$data['created_at']=date('Y-m-d H:i:s');
		if (AccountPaymentAutomation::create($data)) {
			return Response::json((object)['status' => "success"],Codes::$Code200[0]);
		} else {
			return Response::json(array("ErrorMessage" => "Problem Creating Auto Deposit Settings."),Codes::$Code500[0]);
		}

	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * @Response
	 * AutoOutpayment,OutPaymentThreshold,OutPaymentAmount
	 */
	public function getAutoOutPaymentSettings(){
		$post_vars = json_decode(file_get_contents("php://input"));
		$data=json_decode(json_encode($post_vars),true);

		$AccountID=0;
		if(!empty($data['AccountID'])){
			$AccountID=$data['AccountID'];

		}else if(!empty($data['AccountNo'])) {
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}else{
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}

		} else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo Field is Required."],Codes::$Code402[0]);
		}

		$Account = Account::find($AccountID);
		if(empty($Account)){
			return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
		}

		$Result=AccountPaymentAutomation::where('AccountID',$AccountID)->get(['AutoOutpayment','OutPaymentThreshold','OutPaymentAmount']);

		$Result = $Result != false ? $Result->first() : $Result;
		return Response::json($Result,Codes::$Code200[0]);
	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 *AutoOutpayment,OutPaymentThreshold,OutPaymentAmount
	 */
	public function setAutoOutPaymentSettings(){
		$post_vars = json_decode(file_get_contents("php://input"));
		$data=json_decode(json_encode($post_vars),true);

		$AccountID=0;
		if(!empty($data['AccountID'])){
			$AccountID=$data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}else{
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}

		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo Field is Required."],Codes::$Code402[0]);
		}
		$AccountCount=Account::where('AccountID',$AccountID)->count();
		if($AccountCount > 0) {
			$AccountPaymentAutomation = AccountPaymentAutomation::where('AccountID', $AccountID);
			$CountAccountPaymentAutomation = $AccountPaymentAutomation->count();
			if ($CountAccountPaymentAutomation > 0) {
				//update
				$AccountPaymentAutomationObj = $AccountPaymentAutomation->first();
				return $this->updateAutoOutPaymentSetting($data, $AccountPaymentAutomationObj);

			} else {
				//return Response::json(array("status" => "failed", "message" => "Account Not Found."));
				//Create Record
				return $this->createAutoOutPaymentSetting($data, $AccountID);
			}
		}else{
			return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
		}

	}

	public function updateAutoOutPaymentSetting($data,$AccountPaymentAutomation){

		$rules = array(
			'AutoOutpayment' => 'required',
			'OutPaymentThreshold' => 'required',
			'OutPaymentAmount' => 'required',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrv');

		$validator = Validator::make($data, $rules);
		$validator->setPresenceVerifier($verifier);

		if ($validator->fails()) {
			//return json_validator_response($validator);
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
		}
		unset($data['AccountID']);
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);

		$data['updated_at']=date('Y-m-d H:i:s');
		if ($AccountPaymentAutomation->update($data)) {
			return Response::json((object)['status' => "success"],Codes::$Code200[0]);
		} else {
			return Response::json(array("ErrorMessage" => "Problem Updating Auto Out Deposit Settings."),Codes::$Code500[0]);
		}

	}

	public function createAutoOutPaymentSetting($data,$AccountID){

		$rules = array(
			'AutoOutpayment' => 'required',
			'OutPaymentThreshold' => 'required',
			'OutPaymentAmount' => 'required',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrv');

		$validator = Validator::make($data, $rules);
		$validator->setPresenceVerifier($verifier);

		if ($validator->fails()) {
			//return json_validator_response($validator);
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
		}
		$data['AccountID']=$AccountID;
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);

		$data['created_at']=date('Y-m-d H:i:s');
		if (AccountPaymentAutomation::create($data)) {
			return Response::json((object)['status' => "success"],Codes::$Code200[0]);
		} else {
			return Response::json(array("ErrorMessage" => "Problem Creating Auto Out Deposit Settings."),Codes::$Code500[0]);
		}

	}


}