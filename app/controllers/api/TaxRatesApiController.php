<?php

class TaxRatesApiController extends ApiController {

	public function getTaxRates()
	{
		$CompanyID = User::get_companyID();
		$taxrates = TaxRate::select('TaxRateId', 'Title', 'Amount','TaxType','FlatStatus')->where(["CompanyId"=>$CompanyID, "Status"=>1 ])->get();
		return Response::json(["status"=>"success", "data"=>$taxrates]);
	}
}