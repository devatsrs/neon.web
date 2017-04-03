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
            return Response::json(array("status" => "success", "message" => "Additional Charge Successfully Created"));
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
                return Response::json(array("status" => "success", "message" => "Additional Charges Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Additional Charges."));
            }
        }
	}


	public function delete($AccountID,$AccountOneOffChargeID)
	{
        if( intval($AccountOneOffChargeID) > 0){
            try{
                $AccountOneOffCharge = AccountOneOffCharge::find($AccountOneOffChargeID);
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