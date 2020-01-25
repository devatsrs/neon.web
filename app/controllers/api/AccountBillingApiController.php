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
		if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
		$data=array();
		$AccountID=0;
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
		}
		
		if(!empty($data['AccountID'])){
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
			}else {
				return Response::json(["ErrorMessage" => "AccountID must be a mumber."],Codes::$Code400[0]);
			}
		}else if(!empty($data['AccountNo'])) {
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo can not be empty."],Codes::$Code400[0]);
			}
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code400[0]);
			}
		} else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo Field is Required."],Codes::$Code400[0]);
		}

		$Account = Account::find($AccountID);
		if(empty($Account)){
			return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code400[0]);
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
		if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
		$AccountID=0;
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
		}

		if(!empty($data['AccountID'])){
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
			}else {
				return Response::json(["ErrorMessage" => "AccountID must be a mumber."],Codes::$Code400[0]);
			}
		}else if(!empty($data['AccountNo'])){
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo can not be empty."],Codes::$Code400[0]);
			}
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo or AccountDynamicField is Required."],Codes::$Code400[0]);
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
			return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code400[0]);
		}
	}

	public function updateAutoDepositSetting($data,$AccountPaymentAutomationObj){
		$rules = array(
			'AutoTopup' 	=> 'required|in:0,1'
		);

		if(isset($data['AutoTopup']) && intval($data['AutoTopup']) === 1){
			$rules['MinThreshold'] = 'required|numeric';
			$rules['TopupAmount'] 	= 'required|numeric';
		}

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
			return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
		}
		unset($data['AccountID']);
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);
		$data['updated_at']=date('Y-m-d H:i:s');

		if ($AccountPaymentAutomationObj->update($data)) {
			return Response::json((object)['status' => "success"],Codes::$Code200[0]);
		} else {
			return Response::json(array("ErrorMessage" => "Problem Updating Auto Deposit Settings."),Codes::$Code500[0]);
		}

	}

	public function createAutoDepositSetting($data,$AccountID){
		$rules = array(
			'AutoTopup' 	=> 'required|in:0,1'
		);

		if(isset($data['AutoTopup']) && intval($data['AutoTopup']) === 1){
			$rules['MinThreshold'] = 'required|numeric';
			$rules['TopupAmount'] 	= 'required|numeric';
		}

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
			return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
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
		if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
		$AccountID=0;
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
		}

		if(!empty($data['AccountID'])){
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
			}else {
				return Response::json(["ErrorMessage" => "AccountID must be a mumber."],Codes::$Code400[0]);
			}

		}else if(!empty($data['AccountNo'])) {
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo can not be empty."],Codes::$Code400[0]);
			}
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code400[0]);
			}
		} else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo AccountDynamicField is Required."],Codes::$Code400[0]);
		}

		$Account = Account::find($AccountID);
		if(empty($Account)){
			return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code400[0]);
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
		if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
		$AccountID=0;
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
		}

		if(!empty($data['AccountID'])){
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
			}else {
				return Response::json(["ErrorMessage" => "AccountID must be a mumber."],Codes::$Code400[0]);
			}
		}else if(!empty($data['AccountNo'])){
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo can not be empty."],Codes::$Code400[0]);
			}
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code400[0]);
			}
		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo Field is Required."],Codes::$Code400[0]);
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
			return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code400[0]);
		}

	}

	public function updateAutoOutPaymentSetting($data,$AccountPaymentAutomation){
		$rules = array(
			'AutoOutpayment' 	=> 'required|in:0,1'
		);

		if(isset($data['AutoOutpayment']) && intval($data['AutoOutpayment']) === 1){
			$rules['OutPaymentThreshold'] = 'required|numeric';
			$rules['OutPaymentAmount'] 	= 'required|numeric';
		}
		
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
			return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
		}
		unset($data['AccountID']);
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);

		$data['updated_at']=date('Y-m-d H:i:s');
		if ($AccountPaymentAutomation->update($data)) {
			return Response::json((object)['status' => "success"],Codes::$Code200[0]);
		} else {
			return Response::json(array("ErrorMessage" => "Problem Updating Auto Out Payment Settings."),Codes::$Code500[0]);
		}

	}

	public function createAutoOutPaymentSetting($data,$AccountID){

		$rules = array(
			'AutoOutpayment' 	=> 'required|in:0,1'
		);

		if(isset($data['AutoOutpayment']) && intval($data['AutoOutpayment']) === 1){
			$rules['OutPaymentThreshold'] = 'required|numeric';
			$rules['OutPaymentAmount'] 	= 'required|numeric';
		}

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
			return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
		}
		$data['AccountID']=$AccountID;
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);

		$data['created_at']=date('Y-m-d H:i:s');
		if (AccountPaymentAutomation::create($data)) {
			return Response::json((object)['status' => "success"],Codes::$Code200[0]);
		} else {
			return Response::json(array("ErrorMessage" => "Problem Creating Auto Out Payment Settings."),Codes::$Code500[0]);
		}

	}


}