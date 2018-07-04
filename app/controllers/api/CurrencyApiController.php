<?php

class CurrencyApiController extends ApiController {


	public function getList()
	{
		$CurrencyList = Currency::select('CurrencyId', 'Symbol', 'Code', 'Description')->get();
		return Response::json(["status"=>"success", "data"=>$CurrencyList]);
	}

}
