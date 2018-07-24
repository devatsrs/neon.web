<?php

class ProductApiController extends ApiController {

	public function getListByType()
	{
		$data = Input::all();
		$result = Product::getProductByItemType($data);

		return Response::json(["status"=>"success", "data"=>$result]);
	}

	public function UpdateStockCalculation(){
		$data = Input::all();
		$CompanyID=User::get_companyID();
		$rules = array(
			'ProductID' => 'required',
			'Qty' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->fails()) {
			return Response::json(["status"=>"failed", "message"=>"Please Enter Required Fields."]);
		}

		if(!isset($data['InvoiceID'])){
			$data['InvoiceID']='';
		}
		$InvoiceNo=Invoice::where('InvoiceID',$data['InvoiceID'])->pluck('FullInvoiceNumber');

		$returnValidateData=stockHistoryValidateCalculation($CompanyID,$data['ProductID'],'',$data['Qty'],$reason='',$InvoiceNo);
		if ($returnValidateData && $returnValidateData['status'] == 'failed') {
			return Response::json($returnValidateData);
		}

		$returnData = stockHistoryCalculation($CompanyID,$data['ProductID'],$data['InvoiceID'], $data['Qty'], '', $InvoiceNo);
		if ($returnData && $returnData['status'] == 'success') {
			return Response::json($returnValidateData);
		}

	}
}
