<?php

class ServiceApiController extends ApiController {


	public function getList()
	{
		$services = Service::leftJoin('tblCompanyGateway','tblService.CompanyGatewayID','=','tblCompanyGateway.CompanyGatewayID')
			->select(["tblService.ServiceID","tblService.ServiceName","tblService.ServiceType","tblService.CompanyGatewayID", "tblCompanyGateway.Title"])
			->where(["tblService.Status" => 1])->get();
		return Response::json(["status"=>"success", "data"=>$services]);
	}
}