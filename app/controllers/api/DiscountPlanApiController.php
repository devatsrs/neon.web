<?php

class DiscountPlanApiController extends ApiController {


	public function getList()
	{
//		$discountPlan = DiscountPlan::join("tblDestinationGroupSet as dgs", "dgs.DestinationGroupSetID", '=', "tblDiscountPlan.DestinationGroupSetID")
//			->join("tblCurrency as c", "c.CurrencyId", '=', "tblDiscountPlan.CurrencyID")
//			->where("tblDiscountPlan.CompanyID",1)
//			->select("tblDiscountPlan.DiscountPlanID", "tblDiscountPlan.Name", "tblDiscountPlan.DestinationGroupSetID", "dgs.Name as DestinationGroupSet", "tblDiscountPlan.CurrencyID", "c.Code as Currency", "tblDiscountPlan.Description")
//			->get();

		$discountPlan = DiscountPlan::where("CompanyID",Session::get("apiRegistrationCompanyID"))
			->select("DiscountPlanID", "Name")
			->get();
		return Response::json(["status"=>"success", "data"=>$discountPlan]);
	}
}