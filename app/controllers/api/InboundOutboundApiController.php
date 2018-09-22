<?php

class InboundOutboundApiController extends ApiController {
	public function getList($CurrencyID)
	{
		$companyID 					 =  User::get_companyID();
		$rate_table = RateTable::where(["Status"=>1, "CompanyID"=>$companyID,"CurrencyID"=>$CurrencyID])->select("RateTableId", "RateTableName")->get();
		return Response::json(["status"=>"success", "data"=>$rate_table]);
	}
}