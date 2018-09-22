<?php

class BillingTypeApiController extends ApiController {


	public function getList()
	{
		return Response::json(["status"=>"success", "data"=>AccountApproval::$billing_type]);
	}
}
