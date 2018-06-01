<?php

class InboundOutboundApiController extends ApiController {
	public function getList($CurrencyID)
	{
		$rate_table = RateTable::where(["Status"=>1, "CompanyID"=>Session::get("apiRegistrationCompanyID"),"CurrencyID"=>$CurrencyID])->select("RateTableId", "RateTableName")->get();
		return Response::json(["status"=>"success", "data"=>$rate_table]);
	}
}