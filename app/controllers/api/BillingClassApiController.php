<?php

class BillingClassApiController extends ApiController {


	public function getList()
	{
		$DropdownIDList = BillingClass::select('Name', 'BillingClassID','TaxRateID')->get();
		return Response::json(["status"=>"success", "data"=>$DropdownIDList]);
	}

	public function getTaxRateList()
	{
		$data = Input::all();
		$AccountTaxRate=array();
		$result 		=   BillingClass::where('BillingClassID',$data['BillingClassID'])->pluck('TaxRateID');
		$resultarray 	= 	explode(",",$result);

		foreach($resultarray as $resultdata)	{
			if(TaxRate::where(['TaxRateId'=>$resultdata])->count()){
				$AccountTaxRate[]  = $resultdata;
			}
		}
		return Response::json(["status"=>"success", "data"=>$AccountTaxRate]);
	}
}
