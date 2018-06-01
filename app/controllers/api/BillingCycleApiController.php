<?php

class BillingCycleApiController extends ApiController {


	public function getList()
	{
		$BillingTypeList = SortBillingType(1);
		return Response::json(["status"=>"success", "data"=>$BillingTypeList]);
	}
}
