<?php

class AccountOneOffChargeController extends \BaseController {



    public function ajax_datagrid($id){
        $data = Input::all();
        $id=$data['account_id'];
        $select = ["tblProduct.Name", "tblAccountOneOffCharge.Description", "tblAccountOneOffCharge.Qty" ,"tblAccountOneOffCharge.Price","tblAccountOneOffCharge.Date","tblAccountOneOffCharge.TaxAmount","tblAccountOneOffCharge.created_at","tblAccountOneOffCharge.CreatedBy","tblAccountOneOffCharge.AccountOneOffChargeID","tblProduct.ProductID","tblAccountOneOffCharge.TaxRateID","tblAccountOneOffCharge.TaxRateID2"];
        $accountOneOffCharge = AccountOneOffCharge::join('tblProduct', 'tblAccountOneOffCharge.ProductID', '=', 'tblProduct.ProductID')->where("tblAccountOneOffCharge.AccountID",$id);
        if(!empty($data['OneOfCharge_ProductID'])){
            $accountOneOffCharge->where('tblAccountOneOffCharge.ProductID','=',$data['OneOfCharge_ProductID']);
        }
        if(!empty($data['ServiceID'])){
            $accountOneOffCharge->where('tblAccountOneOffCharge.ServiceID','=',$data['ServiceID']);
        }else{
            $accountOneOffCharge->where('tblAccountOneOffCharge.ServiceID','=',0);
        }
        if(!empty($data['OneOfCharge_Description']))
        {            
            $accountOneOffCharge->where('tblAccountOneOffCharge.Description','Like','%'.trim($data['OneOfCharge_Description']).'%');
        }        
        if(!empty($data['OneOfCharge_Date']))
        {
            $accountOneOffCharge->where('tblAccountOneOffCharge.Date','=',$data['OneOfCharge_Date']);   
         
        }
        $accountOneOffCharge->select($select);

        return Datatables::of($accountOneOffCharge)->make();
    }

	/**
	 * Store a newly created resource in storage.
	 * POST /AccountOneOffCharge
	 *
	 * @return Response
	 */
	public function store($id)
	{
		$data = Input::all();
        $data["AccountID"] = $id;
        $data["CreatedBy"] = User::get_user_full_name();

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $rules = array(
            'AccountID'         =>      'required',
            'ProductID'    =>  'required',
            'Date'               =>'required',
            'Qty'               =>'required',
            'Price'               =>'required|numeric'
        );
        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['productPrice']);
        unset($data['AccountoneofchargeID']);
        $data['Price'] = str_replace(',','',$data['Price']);

        if ($AccountOneOffCharge = AccountOneOffCharge::create($data)) {
            //stock History Calculation
            $StockHistory=array();
            $temparray=array();

            if(intval($data['ProductID']) > 0 && intval($data['Qty']) > 0){
                $companyID = User::get_companyID();
                $temparray['CompanyID']=$companyID;
                $temparray['ProductID']=intval($data['ProductID']);
                $temparray['InvoiceID']='';
                $temparray['Qty']=intval($data['Qty']);
                $temparray['Reason']='';
                $temparray['InvoiceNumber']='';
                $temparray['created_by']=User::get_user_full_name();

                array_push($StockHistory,$temparray);
                $historyData=StockHistoryCalculations($StockHistory);

            }
            $message='';
            if(!empty($historyData)){
                foreach($historyData as $msg){
                    $message.=$msg;
                    $message.="\n\r";
                }
            }
            return Response::json(array("status" => "success","warning"=>$message,  "message" => "Additional Charge Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Additional Charge."));
        }
	}

	public function update($AccountID,$AccountOneOffChargeID)
	{
        if( $AccountID  > 0  && $AccountOneOffChargeID > 0 ) {
            $data = Input::all();
            $AccountOneOffChargeID = $data['AccountOneOffChargeID'];
            $AccountOneOffCharge = AccountOneOffCharge::find($AccountOneOffChargeID);
            $oldQty=intval($AccountOneOffCharge['Qty']);
            $data["AccountID"] = $AccountID;
            $data["ModifiedBy"] = User::get_user_full_name();

            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $rules = array(
                'AccountID'         =>      'required',
                'ProductID'    =>  'required',
                'Date'               =>'required',
                'Qty'               =>'required',
                'Price'               =>'required|numeric'
            );
            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['productPrice']);
            unset($data['AccountOneOffChargeID']);
            $data['Price'] = str_replace(',','',$data['Price']);

            if ($AccountOneOffCharge->update($data)) {
                //stock History Calculation
                $StockHistory=array();
                $temparray=array();
                if(intval($data['ProductID']) > 0 && intval($data['Qty']) > 0){
                    $companyID = User::get_companyID();
                    $temparray['CompanyID']=$companyID;
                    $temparray['ProductID']=intval($data['ProductID']);
                    $temparray['InvoiceID']='';
                    $temparray['Qty']=intval($data['Qty']);
                    $temparray['Reason']='';
                    $temparray['InvoiceNumber']='';
                    $temparray['oldQty']=$oldQty;
                    $temparray['created_by']=User::get_user_full_name();

                    array_push($StockHistory,$temparray);
                    $historyData=stockHistoryUpdateCalculations($StockHistory);
                }

                $message='';
                if(!empty($historyData)){
                    foreach($historyData as $msg){
                        $message.=$msg;
                        $message.="\n";
                    }
                }
                return Response::json(array("status" => "success","warning"=>$message, "message" => "Additional Charges Successfully Updated"));
            } else {
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Updating Additional Charges."));
            }
        }
	}


	public function delete($AccountID,$AccountOneOffChargeID)
	{
        if( intval($AccountOneOffChargeID) > 0){
            try{
                $AccountOneOffCharge = AccountOneOffCharge::find($AccountOneOffChargeID);
                //StockHistory Calculation
                $StockHistory=array();
                $temparray=array();
                $ProductID=$AccountOneOffCharge->ProductID;
                $Qty=intval($AccountOneOffCharge->Qty);
                if($ProductID > 0 && $Qty > 0){
                    $companyID = User::get_companyID();
                    $reason='delete_prodstock';

                    $temparray['CompanyID']=$companyID;
                    $temparray['ProductID']=intval($ProductID);
                    $temparray['InvoiceID']='';
                    $temparray['Qty']=$Qty;
                    $temparray['Reason']=$reason;
                    $temparray['InvoiceNumber']='';
                    $temparray['oldQty']=$Qty;
                    $temparray['created_by']=User::get_user_full_name();

                    array_push($StockHistory,$temparray);
                    $historyData=stockHistoryUpdateCalculations($StockHistory);

                }
                $result = $AccountOneOffCharge->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Additional charge Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Additional charge."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }
        }
	}

    public function ajax_getProductInfo($accountID,$productid){
        return Product::find($productid);
    }

}