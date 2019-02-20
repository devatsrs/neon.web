<?php

class AccountAdditionalChargeController extends \BaseController {


    public function ajax_datagrid($id){
        $data = Input::all();
        $id=$data['account_id'];
        $select = [
            "tblProduct.Name", "tblAccountAdditionalCharge.Description",
            "tblAccountAdditionalCharge.Qty" , "tblAccountAdditionalCharge.Price",
            "tblAccountAdditionalCharge.Date", "tblAccountAdditionalCharge.TaxAmount",
            "CurrencyTbl.Code as Currency", "tblAccountAdditionalCharge.created_at",
            "tblAccountAdditionalCharge.CreatedBy", "tblAccountAdditionalCharge.AccountAdditionalChargeID",
            "tblProduct.ProductID", "tblAccountAdditionalCharge.TaxRateID",
            "tblAccountAdditionalCharge.TaxRateID2", "tblAccountAdditionalCharge.DiscountAmount",
            "tblAccountAdditionalCharge.DiscountType", "tblAccountAdditionalCharge.CurrencyID"];

        $AccountAdditionalCharge = AccountAdditionalCharge::join('tblProduct', 'tblAccountAdditionalCharge.ProductID', '=', 'tblProduct.ProductID')
        ->leftJoin('speakintelligentRM.tblCurrency as CurrencyTbl', 'tblAccountAdditionalCharge.CurrencyID', '=', 'CurrencyTbl.CurrencyID')
            ->where("tblAccountAdditionalCharge.AccountID",$id);
        if(!empty($data['Additional_ProductID'])){
            $AccountAdditionalCharge->where('tblAccountAdditionalCharge.ProductID','=',$data['Additional_ProductID']);
        }
        if(!empty($data['ServiceID'])){
            $AccountAdditionalCharge->where('tblAccountAdditionalCharge.ServiceID','=',$data['ServiceID']);
        }else{
            $AccountAdditionalCharge->where('tblAccountAdditionalCharge.ServiceID','=',0);
        }
        if(!empty($data['Additional_Description']))
        {
            $AccountAdditionalCharge->where('tblAccountAdditionalCharge.Description','Like','%'.trim($data['Additional_Description']).'%');
        }        
        if(!empty($data['Additional_Date']))
        {
            $AccountAdditionalCharge->where('tblAccountAdditionalCharge.Date','=',$data['Additional_Date']);
        }
        $AccountAdditionalCharge->select($select);

        return Datatables::of($AccountAdditionalCharge)->make();
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
            'AccountID'   => 'required',
            'ProductID'   => 'required',
            'Date'        => 'required',
            'Qty'         => 'required',
            'Price'       => 'required|numeric'
        );

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['productPrice']);
        unset($data['AccountoneofchargeID']);
        $data['Price'] = str_replace(',','',$data['Price']);

        if ($AccountOneOffCharge = AccountAdditionalCharge::create($data)) {
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

	public function update($AccountID,$AccountAdditionalChargeID)
	{
        if( $AccountID  > 0  && $AccountAdditionalChargeID > 0 ) {
            $data = Input::all();
            $AccountAdditionalChargeID = $data['AccountAdditionalChargeID'];
            $AccountAdditionalCharge = AccountAdditionalCharge::find($AccountAdditionalChargeID);
            $oldQty=intval($AccountAdditionalCharge['Qty']);
            $data["AccountID"] = $AccountID;
            $data["ModifiedBy"] = User::get_user_full_name();

            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $rules = array(
                'AccountID'   => 'required',
                'ProductID'   => 'required',
                'Date'        =>'required',
                'Qty'         =>'required',
                'Price'       =>'required|numeric'
            );
            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['productPrice']);
            unset($data['AccountAdditionalChargeID']);
            $data['Price'] = str_replace(',','',$data['Price']);

            if ($AccountAdditionalCharge->update($data)) {
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


	public function delete($AccountID,$AccountAdditionalChargeID)
	{
        if( intval($AccountAdditionalChargeID) > 0){
            try{
                $AccountAdditionalCharge = AccountAdditionalCharge::find($AccountAdditionalChargeID);
                //StockHistory Calculation
                $StockHistory=array();
                $temparray=array();
                $ProductID=$AccountAdditionalCharge->ProductID;
                $Qty=intval($AccountAdditionalCharge->Qty);
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
                $result = $AccountAdditionalCharge->delete();
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