<?php

class CompaniesApiController extends ApiController {

	public function validCompanyName() {
		$data = Input::all();

		$CompanyID = Company::where("CompanyName",$data['companyName'])->pluck('CompanyID');
		if($CompanyID){
			return Response::json(["status"=>"success", "data"=>"Valid Company Name"]);
		}
		return Response::json(["status"=>"failed", "data"=>"Company Name Not Valid"]);
	}
}