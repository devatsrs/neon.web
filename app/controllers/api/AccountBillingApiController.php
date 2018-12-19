<?php

class AccountBillingApiController extends ApiController {

	public function getAccountBilling($AccountID)
	{
		$fields=["AccountBillingID", "AccountID", "BillingType", "BillingCycleType", "BillingCycleValue", "BillingClassID"];
		$AccountBilling =  AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>0))->select($fields)->first();
		return Response::json(["status"=>"success", "data"=>$AccountBilling]);
	}

	public function getAutoDepositSettings(){
		$data=Input::all();
		$AccountID=0;
		if(!empty($data['CustomerID'])){
			$AccountID=$data['CustomerID'];

		}else if(!empty($data['AccountNo'])) {
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		} else{
			return Response::json(["status"=>"failed", "data"=>"CustomerID or AccountNo Field is Required."]);
		}
		$Result=AccountPaymentAutomation::where('AccountID',$AccountID)->get(['AutoTopup','MinThreshold','TopupAmount']);

		return Response::json(["status"=>"success", "data"=>$Result]);
	}

	public function setAutoDepositSettings(){
		$data=Input::all();
		$AccountID=0;
		if(!empty($data['CustomerID'])){
			$AccountID=$data['CustomerID'];
		}else if(!empty($data['AccountNo'])){
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		}else{
			return Response::json(["status"=>"failed", "data"=>"CustomerID or AccountNo Field is Required."]);
		}

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
		unset($data['CustomerID']);
		unset($data['AccountNo']);
		$data['updated_at']=date('Y-m-d H:i:s');

		$AccountBilling=AccountPaymentAutomation::where('AccountID',$AccountID);
		if(!empty($AccountBilling)){
			if ($AccountBilling->update($data)) {
				return Response::json(array("status" => "success", "message" => "Auto Deposit Settings Updated Successfully."));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Updating Auto Deposit Settings."));
			}
		}else{
			return Response::json(array("status" => "failed", "message" => "Account Not Found."));
		}

		//return Response::json(["status"=>"success", "data"=>$Result]);
	}

	public function getAutoOutPaymentSettings(){
		$data=Input::all();
		$AccountID=0;
		if(!empty($data['CustomerID'])){
			$AccountID=$data['CustomerID'];

		}else if(!empty($data['AccountNo'])) {
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		} else{
			return Response::json(["status"=>"failed", "data"=>"CustomerID or AccountNo Field is Required."]);
		}
		$Result=AccountPaymentAutomation::where('AccountID',$AccountID)->get(['AutoOutpayment','OutPaymentThreshold','OutPaymentAmount']);

		return Response::json(["status"=>"success", "data"=>$Result]);
	}

	public function setAutoOutPaymentSettings(){
		$data=Input::all();
		$AccountID=0;
		if(!empty($data['CustomerID'])){
			$AccountID=$data['CustomerID'];
		}else if(!empty($data['AccountNo'])){
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		}else{
			return Response::json(["status"=>"failed", "data"=>"CustomerID or AccountNo Field is Required."]);
		}

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
		unset($data['CustomerID']);
		unset($data['AccountNo']);

		$data['updated_at']=date('Y-m-d H:i:s');

		$AccountBilling=AccountPaymentAutomation::where('AccountID',$AccountID);
		if(!empty($AccountBilling)){
			//print_r($data);die;
			if ($AccountBilling->update($data)) {
				return Response::json(array("status" => "success", "message" => "Auto Out Deposit Settings Updated Successfully."));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Updating Auto Out Deposit Settings."));
			}
		}else{
			return Response::json(array("status" => "failed", "message" => "Account Not Found."));
		}

	}

}