<?php

class AccountSubscriptionController extends \BaseController {



    public function ajax_datagrid($id){
        $data = Input::all();        
        $id=$data['account_id'];
        $select = ["tblAccountSubscription.AccountSubscriptionID","tblBillingSubscription.Name", "InvoiceDescription", "Qty" ,"tblAccountSubscription.StartDate",DB::raw("IF(tblAccountSubscription.EndDate = '0000-00-00','',tblAccountSubscription.EndDate) as EndDate"),"tblAccountSubscription.ActivationFee","Discount","tblAccountSubscription.DailyFee","tblAccountSubscription.WeeklyFee","tblAccountSubscription.MonthlyFee","tblAccountSubscription.SubscriptionID","tblAccountSubscription.ExemptTax"];
        $subscriptions = AccountSubscription::join('tblBillingSubscription', 'tblAccountSubscription.SubscriptionID', '=', 'tblBillingSubscription.SubscriptionID')->where("tblAccountSubscription.AccountID",$id);        
        if(!empty($data['SubscriptionName'])){
            $subscriptions->where('tblBillingSubscription.Name','Like','%'.trim($data['SubscriptionName']).'%');
        }
        if(!empty($data['SubscriptionInvoiceDescription'])){
            $subscriptions->where('tblAccountSubscription.InvoiceDescription','Like','%'.trim($data['SubscriptionInvoiceDescription']).'%');
        }
        if(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'true'){
            $subscriptions->where(function($query){
                $query->where('tblAccountSubscription.EndDate','>=',date('Y-m-d'));
                $query->orwhere('tblAccountSubscription.EndDate','=','0000-00-00');
            });

        }elseif(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'false'){
            $subscriptions->where('tblAccountSubscription.EndDate','<',date('Y-m-d'));
        }
        $subscriptions->select($select);

        return Datatables::of($subscriptions)->make();
    }

	/**
	 * Store a newly created resource in storage.
	 * POST /accountsubscription
	 *
	 * @return Response
	 */
	public function store($id)
	{
		$data = Input::all();
        $data["AccountID"] = $id;
        $data["CreatedBy"] = User::get_user_full_name();
        $data['ExemptTax'] = isset($data['ExemptTax']) ? 1 : 0;
        AccountSubscription::$rules['SubscriptionID'] = 'required|unique:tblAccountSubscription,AccountSubscriptionID,NULL,SubscriptionID,'.$data['SubscriptionID'].',AccountID,'.$data["AccountID"];

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $rules = array(
            'AccountID'         =>      'required',
            'SubscriptionID'    =>  'required',
            'StartDate'               =>'required',
			'MonthlyFee' => 'required|numeric',
            'WeeklyFee' => 'required|numeric',
            'DailyFee' => 'required|numeric',
			 'ActivationFee' => 'required|numeric',
			 'Qty' => 'required|numeric',
			 
            //'EndDate'               =>'required'
        );
        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['Status_name']);
        if ($AccountSubscription = AccountSubscription::create($data)) {
            return Response::json(array("status" => "success", "message" => "Subscription Successfully Created",'LastID'=>$AccountSubscription->AccountSubscriptionID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Subscription."));
        }
	}

	public function update($AccountID,$AccountSubscriptionID)
	{
        if( $AccountID  > 0  && $AccountSubscriptionID > 0 ) {
            $data = Input::all();
            $AccountSubscriptionID = $data['AccountSubscriptionID'];
            $AccountSubscription = AccountSubscription::find($AccountSubscriptionID);
            $data["AccountID"] = $AccountID;
            $data["ModifiedBy"] = User::get_user_full_name();
            $data['ExemptTax'] = isset($data['ExemptTax']) ? 1 : 0;
            AccountSubscription::$rules['SubscriptionID'] = 'required|unique:tblAccountSubscription,AccountSubscriptionID,NULL,SubscriptionID,' . $data['SubscriptionID'] . ',AccountID,' . $data["AccountID"];

            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $rules = array(
                'AccountID' => 'required',
                'SubscriptionID' => 'required',
                'StartDate' => 'required',
				'MonthlyFee' => 'required|numeric',
            'WeeklyFee' => 'required|numeric',
            'DailyFee' => 'required|numeric',
			 'ActivationFee' => 'required|numeric',
			 'Qty' => 'required|numeric',
			 
                //'EndDate' => 'required'
            );
            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['Status_name']);
            if ($AccountSubscription->update($data)) {
                return Response::json(array("status" => "success", "message" => "Subscription Successfully Created", 'LastID' => $AccountSubscription->AccountSubscriptionID));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Subscription."));
            }
        }
	}


	public function delete($AccountID,$AccountSubscriptionID)
	{
        if( intval($AccountSubscriptionID) > 0){

            if(!AccountSubscription::checkForeignKeyById($AccountSubscriptionID)){
                try{
                    $AccountSubscription = AccountSubscription::find($AccountSubscriptionID);
                    $result = $AccountSubscription->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Subscription Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Subscription."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Subscription is in Use, You cant delete this Subscription."));
            }
        }
	}

}