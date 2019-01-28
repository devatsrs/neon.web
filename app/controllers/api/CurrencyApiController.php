<?php
use app\controllers\api\Codes;

class CurrencyApiController extends ApiController {


	public function getList()
	{
		$CurrencyList = Currency::select('CurrencyId', 'Symbol', 'Code', 'Description')->get();
		return Response::json(["data"=>$CurrencyList],Codes::$Code200[0]);
	}

}
