<?php

class AccountBillingApiController extends ApiController {

	public function getAccountBilling($AccountID)
	{
		$fields=["AccountBillingID", "AccountID", "BillingType", "BillingCycleType", "BillingCycleValue", "BillingClassID"];
		$AccountBilling =  AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>0))->select($fields)->first();
		return Response::json(["status"=>"success", "data"=>$AccountBilling]);
	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * @Response
	 * 	AutoTopup,MinThreshold,TopupAmount
	 */
	public function getAutoDepositSettings(){
		$data=Input::all();
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
				return Response::json(["status"=>"failed", "data"=>"Account Not Found."]);
			}

		} else{
			return Response::json(["status"=>"failed", "message"=>"AccountID or AccountNo Field is Required."]);
		}
		$Result=AccountPaymentAutomation::where('AccountID',$AccountID)->get(['AutoTopup','MinThreshold','TopupAmount']);

		return Response::json(["status"=>"success", "data"=>$Result]);
	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * AutoTopup,MinThreshold,TopupAmount
	 */
	public function setAutoDepositSettings(){
		$data=Input::all();
		$AccountID=0;
		if(!empty($data['AccountID'])){
			$AccountID=$data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}else{
				return Response::json(["status"=>"failed", "data"=>"Account Not Found."]);
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["status"=>"failed", "data"=>"Account Not Found."]);
			}

		}else{
			return Response::json(["status"=>"failed", "data"=>"AccountID or AccountNo Field is Required."]);
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
			return Response::json(["status"=>"failed", "message"=>"Account Not Found."]);
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
			return json_validator_response($validator);
		}
		unset($data['AccountID']);
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);
		$data['updated_at']=date('Y-m-d H:i:s');

		if ($AccountPaymentAutomationObj->update($data)) {
			return Response::json(array("status" => "success", "message" => "Auto Deposit Settings Updated Successfully."));
		} else {
			return Response::json(array("status" => "failed", "message" => "Problem Updating Auto Deposit Settings."));
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
			return json_validator_response($validator);
		}
		$data['AccountID']=$AccountID;
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);

		$data['created_at']=date('Y-m-d H:i:s');
		if (AccountPaymentAutomation::create($data)) {
			return Response::json(array("status" => "success", "message" => "Auto Deposit Settings created Successfully."));
		} else {
			return Response::json(array("status" => "failed", "message" => "Problem Creating Auto Deposit Settings."));
		}

	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * @Response
	 * AutoOutpayment,OutPaymentThreshold,OutPaymentAmount
	 */
	public function getAutoOutPaymentSettings(){
		$data=Input::all();
		$AccountID=0;
		if(!empty($data['AccountID'])){
			$AccountID=$data['AccountID'];

		}else if(!empty($data['AccountNo'])) {
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}else{
				return Response::json(["status"=>"failed", "data"=>"Account Not Found."]);
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["status"=>"failed", "data"=>"Account Not Found."]);
			}

		} else{
			return Response::json(["status"=>"failed", "data"=>"AccountID or AccountNo Field is Required."]);
		}
		$Result=AccountPaymentAutomation::where('AccountID',$AccountID)->get(['AutoOutpayment','OutPaymentThreshold','OutPaymentAmount']);

		return Response::json(["status"=>"success", "data"=>$Result]);
	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 *AutoOutpayment,OutPaymentThreshold,OutPaymentAmount
	 */
	public function setAutoOutPaymentSettings(){
		$data=Input::all();
		$AccountID=0;
		if(!empty($data['AccountID'])){
			$AccountID=$data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}else{
				return Response::json(["status"=>"failed", "data"=>"Account Not Found."]);
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["status"=>"failed", "data"=>"Account Not Found."]);
			}

		}else{
			return Response::json(["status"=>"failed", "data"=>"AccountID or AccountNo Field is Required."]);
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
			return Response::json(["status"=>"failed", "message"=>"Account Not Found."]);
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
			return json_validator_response($validator);
		}
		unset($data['AccountID']);
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);

		$data['updated_at']=date('Y-m-d H:i:s');
		if ($AccountPaymentAutomation->update($data)) {
			return Response::json(array("status" => "success", "message" => "Auto Out Deposit Settings Updated Successfully."));
		} else {
			return Response::json(array("status" => "failed", "message" => "Problem Updating Auto Out Deposit Settings."));
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
			return json_validator_response($validator);
		}
		$data['AccountID']=$AccountID;
		unset($data['AccountNo']);
		unset($data['AccountDynamicField']);

		$data['created_at']=date('Y-m-d H:i:s');
		if (AccountPaymentAutomation::create($data)) {
			return Response::json(array("status" => "success", "message" => "Auto Out Deposit Settings created Successfully."));
		} else {
			return Response::json(array("status" => "failed", "message" => "Problem Creating Auto Out Deposit Settings."));
		}

	}


}