<?php

class CountryApiController extends ApiController {


	public function getList()
	{

		$Countries = Country::select(["CountryID","Prefix","Country", "ISO2", "ISO3"])
			->get();
		return Response::json(["status"=>"200", "data"=>$Countries]);
	}
}