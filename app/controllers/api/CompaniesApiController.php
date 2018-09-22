<?php

class CompaniesApiController extends ApiController {

	public function validCompanyName() {
		$data = Input::all();

		$AccountID = Account::getAccountIDByName($data['companyName']);
		if( $AccountID ){
			return Response::json(["status"=>"failed", "data"=>"Company Name already added"]);
		}
		return Response::json(["status"=>"success", "data"=>"Valid Company Name"]);
	}
}