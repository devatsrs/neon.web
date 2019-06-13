<?php

class AccountOneOffChargeController extends \BaseController {



    public function ajax_datagrid($id){
        $data = Input::all();
        $id=$data['account_id'];
        $select = [
            "tblProduct.Name", "tblAccountOneOffCharge.Description",
            "tblAccountOneOffCharge.Qty" , "tblAccountOneOffCharge.Price",
            "tblAccountOneOffCharge.Date", "tblAccountOneOffCharge.TaxAmount",
            "CurrencyTbl.Code as Currency", "tblAccountOneOffCharge.created_at",
            "tblAccountOneOffCharge.CreatedBy", "tblAccountOneOffCharge.AccountOneOffChargeID",
            "tblProduct.ProductID", "tblAccountOneOffCharge.TaxRateID",
            "tblAccountOneOffCharge.TaxRateID2", "tblAccountOneOffCharge.DiscountAmount",
            "tblAccountOneOffCharge.DiscountType", "tblAccountOneOffCharge.CurrencyID"];


        $accountOneOffCharge = AccountOneOffCharge::join('tblProduct', 'tblAccountOneOffCharge.ProductID', '=', 'tblProduct.ProductID')
        ->leftJoin('speakintelligentRM.tblCurrency as CurrencyTbl', 'tblAccountOneOffCharge.CurrencyID', '=', 'CurrencyTbl.CurrencyID')
            ->where("tblAccountOneOffCharge.AccountID",$id);
        if(!empty($data['OneOfCharge_ProductID'])){
            $accountOneOffCharge->where('tblAccountOneOffCharge.ProductID','=',$data['OneOfCharge_ProductID']);
        }
        if(!empty($data['ServiceID'])){
            $accountOneOffCharge->where('tblAccountOneOffCharge.ServiceID','=',$data['ServiceID']);
        }else{
            $accountOneOffCharge->where('tblAccountOneOffCharge.ServiceID','=',0);
        }
        if(!empty($data['AccountServiceID'])){
            $accountOneOffCharge->where('tblAccountOneOffCharge.AccountServiceID','=',$data['AccountServiceID']);
        }else{
            $accountOneOffCharge->where('tblAccountOneOffCharge.AccountServiceID','=',0);
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
        Log::info("Account One Off Charge .ajax_datagrid" . $accountOneOffCharge->toSql());
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
        $CompanyID = User::get_companyID();
        $Account=Account::where(["AccountID" => $id]);
        if($Account->count() > 0){
            $Account = $Account->first();
            $CompanyID = $Account->CompanyId;
        }


		$data = Input::all();
        $ChargeCode = strtolower('One-Off');
        $product = Product::whereRaw('lower(Code) = '. "'". $ChargeCode . "'")->where("CompanyId", $CompanyID);
        if ($product->count() == 0) {
            $product = [];
            $product['CompanyId'] = $CompanyID;
            $product['Name'] = 'One-Off';
            $product['Code'] = 'One-Off';
            $product['Description'] = 'One-Off';
            $product['Amount'] = '1';
            $product['Active'] = '1';
            $product['Note'] = '';
            $product['AppliedTo'] = '0';
            $product['ItemTypeID'] = '0';
            $product['BuyingPrice'] = '0';
            $product['Quantity'] = '0';
            $product['LowStockLevel'] = '0';
            $product['EnableStock'] = '0';
            $product = Product::create($product);
            Log::info("Account One Off Charge ." . $product);

        }else {
            $product = $product->first();
            if ($product->Active != 1) {
                $product->Active = 1;
                $product->save();
            }
        }
        Log::info("Account One Off Charge AccountAdditionChangesProductID1." . $product->count());
        $AccountAdditionChangesProductID = $product->ProductID;
        Log::info("Account One Off Charge AccountAdditionChangesProductID." . $AccountAdditionChangesProductID);
        Log::info("Account One Off Charge AccountAdditionChangesProductID1." . print_r($data,true));
        $data["AccountID"] = $id;
        $data["CreatedBy"] = User::get_user_full_name();
        if (!isset($data["ProductID"]) || empty($data["ProductID"])) {
            $data["ProductID"] = $AccountAdditionChangesProductID;
            Log::info("Set Account One Off Charge AccountAdditionChangesProductID." . $data["ProductID"]);
        }

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $rules = array(
            'AccountID'   => 'required',
            'ProductID'   => 'required',
            'Description' => 'required',
            'Date'        => 'required',
            'CurrencyID'  => 'required',
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

        if ($AccountOneOffCharge = AccountOneOffCharge::create($data)) {
            //stock History Calculation
            $StockHistory=array();
            $temparray=array();

            if(intval($data['ProductID']) > 0 && intval($data['Qty']) > 0){
                //$companyID = User::get_companyID();
                $temparray['CompanyID']=$CompanyID;
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
        $CompanyID = '';
        if( $AccountID  > 0  && $AccountOneOffChargeID > 0 ) {
            $data = Input::all();
            $AccountOneOffChargeID = $data['AccountOneOffChargeID'];
            $AccountOneOffCharge = AccountOneOffCharge::find($AccountOneOffChargeID);
            $oldQty=intval($AccountOneOffCharge['Qty']);
            $data["AccountID"] = $AccountID;
            $Account=Account::where(["AccountID" => $AccountID]);
            if($Account->count() > 0){
                $Account = $Account->first();
                $CompanyID = $Account->CompanyId;
            }

            $data["ModifiedBy"] = User::get_user_full_name();

            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $rules = array(
                'AccountID'   => 'required',
                'ProductID'   => 'required',
                'Description' => 'required',
                'Date'        => 'required',
                'CurrencyID'  => 'required',
                'Qty'         => 'required',
                'Price'       => 'required|numeric'
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

                    $temparray['CompanyID']=$CompanyID;
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
        $CompanyID = '';
        $Account=Account::where(["AccountID" => $AccountID]);
        if($Account->count() > 0){
            $Account = $Account->first();
            $CompanyID = $Account->CompanyId;
        }

        if( intval($AccountOneOffChargeID) > 0){
            try{
                $AccountOneOffCharge = AccountOneOffCharge::find($AccountOneOffChargeID);
                //StockHistory Calculation
                $StockHistory=array();
                $temparray=array();
                $ProductID=$AccountOneOffCharge->ProductID;
                $Qty=intval($AccountOneOffCharge->Qty);
                if($ProductID > 0 && $Qty > 0){

                    $reason='delete_prodstock';

                    $temparray['CompanyID']=$CompanyID;
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