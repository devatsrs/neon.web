<?php
use app\controllers\api\Codes;

class InboundOutboundApiController extends ApiController {
	public function getList($CurrencyID)
	{
		$companyID 					 =  User::get_companyID();
		$rate_table = RateTable::where(["Status"=>1, "CompanyID"=>$companyID,"CurrencyID"=>$CurrencyID])->select("RateTableId", "RateTableName")->get();
		return Response::json(["data"=>$rate_table],Codes::$Code200[0]);
	}
}