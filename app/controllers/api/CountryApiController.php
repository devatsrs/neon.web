<?php
use app\controllers\api\Codes;

class CountryApiController extends ApiController {


	public function getList()
	{

		$Countries = Country::select(["CountryID","Prefix","Country", "ISO2", "ISO3"])
			->get();
		return Response::json($Countries,Codes::$Code200[0]);
	}
}