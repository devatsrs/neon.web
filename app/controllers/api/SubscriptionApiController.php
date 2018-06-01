<?php

class SubscriptionApiController extends ApiController {


	public function getList()
	{
		$subscription = BillingSubscription::select(["SubscriptionID","Name", "CurrencyID", "AnnuallyFee", "QuarterlyFee", "MonthlyFee", "WeeklyFee", "DailyFee", "Advance", "ActivationFee", "InvoiceLineDescription", "Description", "AppliedTo"])
			->where(["CompanyID" => Session::get("apiRegistrationCompanyID")])->get();
		return Response::json(["status"=>"success", "data"=>$subscription]);
	}
}