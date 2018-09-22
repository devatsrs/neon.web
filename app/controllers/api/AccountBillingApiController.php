<?php

class AccountBillingApiController extends ApiController {

	public function getAccountBilling($AccountID)
	{
		$fields=["AccountBillingID", "AccountID", "BillingType", "BillingCycleType", "BillingCycleValue", "BillingClassID"];
		$AccountBilling =  AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>0))->select($fields)->first();
		return Response::json(["status"=>"success", "data"=>$AccountBilling]);
	}
}