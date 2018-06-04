<?php

class BillingClassApiController extends ApiController {


	public function getList()
	{
		$DropdownIDList = BillingClass::lists('Name', 'BillingClassID');
		return Response::json(["status"=>"success", "data"=>$DropdownIDList]);
	}
}
