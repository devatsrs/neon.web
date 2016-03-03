<?php

class BillingSubscriptionController extends \BaseController {

    var $model = 'BillingSubscription';

    public function ajax_datagrid() {
        $data = Input::all();                
        //$FdilterAdvance = $data['FilterAdvance']== 'true'?1:0;
        $CompanyID = User::get_companyID();
        $billingSubscription = BillingSubscription::select("Name", "MonthlyFee", "WeeklyFee", "DailyFee", "SubscriptionID" , "ActivationFee","CurrencyID","InvoiceLineDescription","Description","Advance")->where("CompanyID", $CompanyID);
        if(isset($data['FilterAdvance']) && $data['FilterAdvance']!=''){
            $billingSubscription->where("Advance",$data['FilterAdvance']);
        }
        if(!empty($data['FilterName'])){
            $billingSubscription->where('Name','Like','%'.trim($data['FilterName']).'%');
        }
        if(!empty($data['FilterCurrencyID'])){
            $billingSubscription->where('CurrencyID','=',$data['FilterCurrencyID']);
        }
        return Datatables::of($billingSubscription)->make();
    }

    public function index() {

        $currencies = Currency::getCurrencyDropdownIDList();
        return View::make('billingsubscription.index', compact('currencies'));

    }

    public function create()
    {
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        unset($data['SubscriptionID']);
        $data['CreatedBy'] = User::get_user_full_name();
        $rules = array(
            'CompanyID' => 'required',
            'Name' => 'required|unique:tblBillingSubscription,Name,NULL,SubscriptionID,CompanyID,'.$data['CompanyID'],
            'MonthlyFee' => 'required|numeric',
            'WeeklyFee' => 'required|numeric',
            'DailyFee' => 'required|numeric',
            'CurrencyID' => 'required',
            'InvoiceLineDescription' => 'required',
            'ActivationFee' => 'required|numeric',
        );
        $data['Advance'] = isset($data['Advance']) ? 1 : 0;
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if ($BillingSubscription = BillingSubscription::create($data)) {
            return Response::json(array("status" => "success", "message" => "Subscription Successfully Created",'LastID'=>$BillingSubscription->SubscriptionID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Subscription."));
        }
    }


    public function update($id)
    {
        if($id >0 ) {
            $BillingSubscription = BillingSubscription::find($id);
            $data = Input::all();
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            unset($data['SubscriptionID']);
            $data['ModifiedBy'] = User::get_user_full_name();
            $rules = array(
                'CompanyID' => 'required',
                'Name' => 'required|unique:tblBillingSubscription,Name,'.$id.',SubscriptionID,CompanyID,'.$data['CompanyID'],
                'MonthlyFee' => 'required|numeric',
                'WeeklyFee' => 'required|numeric',
                'DailyFee' => 'required|numeric',
                'CurrencyID' => 'required',
                'InvoiceLineDescription' => 'required',
                'ActivationFee' => 'required|numeric',
            );
            $data['Advance'] = isset($data['Advance']) ? 1 : 0;
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if ($BillingSubscription->update($data)) {
                return Response::json(array("status" => "success", "message" => "Subscription Successfully Updated",'LastID'=>$id));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Subscription."));
            }
        }
    }

    public function delete($id)
    {
        if( intval($id) > 0){

            if(!BillingSubscription::checkForeignKeyById($id)){
                try{
                    $BillingSubscription = BillingSubscription::find($id);
                    AmazonS3::delete($BillingSubscription->CompanyLogoAS3Key);
                    $result = $BillingSubscription->delete();
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