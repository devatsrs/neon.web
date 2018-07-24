<?php

class DiscountPlanApiController extends ApiController {


	public function getList()
	{
		$companyID 					 =  User::get_companyID();
		$discountPlan = DiscountPlan::where("CompanyID", $companyID)
			->select("DiscountPlanID", "Name","CurrencyID")
			->get();
		return Response::json(["status"=>"success", "data"=>$discountPlan]);
	}
}