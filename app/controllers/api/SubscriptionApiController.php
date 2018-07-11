<?php

class SubscriptionApiController extends ApiController {


	public function getList()
	{
		$companyID 					 =  User::get_companyID();
		$subscription = BillingSubscription::select(["SubscriptionID","Name", "CurrencyID", "AnnuallyFee", "QuarterlyFee", "MonthlyFee", "WeeklyFee", "DailyFee", "Advance", "ActivationFee", "InvoiceLineDescription", "Description", "AppliedTo"])
			->where(["CompanyID" => $companyID])->get();
		return Response::json(["status"=>"success", "data"=>$subscription]);
	}
}