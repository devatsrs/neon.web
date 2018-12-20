<?php

class AccountsApiController extends ApiController {

	public function validEmail() {
		$data = Input::all();
		$CompanyID = User::get_companyID();
		
		$AccountID = Account::where('CompanyId',$CompanyID)
							->where('Email',$data['email'])
							->orWhere('BillingEmail', $data['email'])->pluck('AccountID');
		if($AccountID){
			return Response::json(["status"=>"failed", "data"=>"Account already Exists"]);
		}
		return Response::json(["status"=>"success", "data"=>"Account Not Found"]);
	}

	public function checkBalance(){
		$data=Input::all();
		$Result=array();
		$AccountBalance=0;
		if(!empty($data['CustomerID'])) {
			$CompanyID = Account::where(["AccountID" => $data['CustomerID']])->pluck('CompanyId');

			if(intval($CompanyID) > 0){
				$AccountBalance = AccountBalance::getNewAccountExposure($CompanyID, $data['CustomerID']);
			}else{
				return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
			}

		}else if(!empty($data['AccountNo'])) {
			$Account = Account::where(["Number" => $data['AccountNo']])->select('CompanyId','AccountID')->first();

			if(!empty($Account)) {
				$CompanyID = $Account->CompanyId;
				$AccountID = $Account->AccountID;
				$AccountBalance = AccountBalance::getNewAccountExposure($CompanyID, $AccountID);
			}else{
				return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
			}

		}else {
			return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
		}

		if($AccountBalance > 0){
			$Result['has_balance']=1;
			$Result['amount']=$AccountBalance;
		}else{
			$Result['has_balance']=0;
			$Result['amount']=$AccountBalance;
		}

		return Response::json(["status"=>"success", "data"=>$Result]);
	}

}