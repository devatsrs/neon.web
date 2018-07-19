<?php

class BillingClassApiController extends ApiController {


	public function getList()
	{
		$DropdownIDList = BillingClass::select('Name', 'BillingClassID','TaxRateID');
		return Response::json(["status"=>"success", "data"=>$DropdownIDList]);
	}

	public function getTaxRateList()
	{
		$data = Input::all();
		$AccountTaxRate  = BillingClass::getTaxRateType($data['BillingClassID'],TaxRate::TAX_ALL);
		return Response::json(["status"=>"success", "data"=>$AccountTaxRate]);
	}
}
