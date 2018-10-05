<?php

class ProductApiController extends ApiController {

	public function getListByType()
	{
		$data = Input::all();
		$rules = array(
			'PageNumber' => 'required',
			'RowsPage' => 'required',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->fails()) {
			return Response::json(["status"=>"failed", "message"=>"Please Enter Required Fields."]);
		}
		$CompanyID=User::get_companyID();
		$data['CompanyID']=$CompanyID;
		$data['Name']=empty($data['Name'])?'':$data['Name'];
		$data['Description']=empty($data['Description'])?'':$data['Description'];

		$result = Product::getProductByItemType($data);

		return Response::json(["status"=>"success", "data"=>$result]);
	}

	public function UpdateStockCalculation(){
		$StockData=array();
		$returnData=array();
		$message="";
		$data = Input::all();
		$CompanyID=User::get_companyID();
		$CreatedBy=User::get_user_full_name();
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
		$getProduct = Product::where(['CompanyID'=>$CompanyID,'ProductID'=>$data['ProductID'],'EnableStock'=>1])->first();
		if(!empty($getProduct)){
			$pname=$getProduct['Name'];
			$getPrevProductHistory = StockHistory::where(['CompanyID'=>$CompanyID,'ProductID'=>$data['ProductID']])->orderby('StockHistoryID', 'desc')->first();
			if (!empty($getPrevProductHistory)) {
				$pstock = intval($getPrevProductHistory['Stock']);
				$remainStock = $pstock - $data['Qty'];
				$low_stock_level=intval($getProduct['LowStockLevel']);
				if ($remainStock < 0 || $remainStock <= $low_stock_level) {
					$message = $pname . " is below the Lowlevel Stock.Available Stock is " . $remainStock;
				}
				$invoicemsg="";
				if($InvoiceNo!=''){
					$invoicemsg='{'.$InvoiceNo.'}';
				}
				$reason="Invoice Generated ".$invoicemsg." - qty ".$data['Qty'];

				$StockData['CompanyID']=$CompanyID;
				$StockData['ProductID']=$data['ProductID'];
				$StockData['InvoiceID']=$data['InvoiceID'];
				$StockData['InvoiceNumber']=$InvoiceNo;
				$StockData['Stock']=$remainStock;
				$StockData['Quantity']=$data['Qty'];
				$StockData['Reason']=$reason;
				$StockData['created_at']=date('Y-m-d H:i:s');
				$StockData['created_by']=$CreatedBy;

				$insertStockHistory=StockHistory::insert($StockData);
				if(!empty($insertStockHistory)){
					$getProduct->update(['Quantity'=>$StockData['Stock']]);
					$returnData=["status"=>"success","message"=>"Stock History Created Successfully.","warning"=>$message];
				}else{
					$returnData=["status"=>"failed","message"=>"Something Went Wrong Please Try Again Later."];
				}
			}else{
				$returnData=["status"=>"failed","message"=>"Product History Not Available."];
			}
		}else{
			$returnData=["status"=>"failed","message"=>"Product Not Available At This Time."];
		}
		return Response::json($returnData);
	}
}
