<?php

class UsersApiController extends ApiController {


	public function getList()
	{
		$companyID 	=  User::get_companyID();

		$EmailTemplates = User::select(["UserID","FirstName","LastName", "EmailAddress", "AccountingUser"])
			->where(["CompanyID" => $companyID,"Status"=>1])->get();
		return Response::json(["status"=>"success", "data"=>$EmailTemplates]);
	}
}