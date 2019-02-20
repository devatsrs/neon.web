<?php

class AccountRecurringController extends \BaseController {

    /** Used in Account Service Edit Page */
    public function ajax_datagrid($id){
        $data = Input::all();        
        $id = $data['account_id'];
        $select = [
            "tblAccountRecurring.SequenceNo","tblBillingSubscription.Name",
            "InvoiceDescription", "Qty" ,"tblAccountRecurring.StartDate",
            DB::raw("IF(tblAccountRecurring.EndDate = '0000-00-00','',tblAccountRecurring.EndDate) as EndDate"),
            "tblAccountRecurring.ActivationFee","CurrencyTbl1.Code as OneOffCurrency",
            "tblAccountRecurring.DailyFee", "tblAccountRecurring.WeeklyFee",
            "tblAccountRecurring.MonthlyFee","CurrencyTbl2.Code as RecurringCurrency",
            "tblAccountRecurring.QuarterlyFee","tblAccountRecurring.AnnuallyFee",
            "tblAccountRecurring.AccountRecurringID","tblAccountRecurring.SubscriptionID",
            "tblAccountRecurring.ExemptTax","tblAccountRecurring.Status",
            "tblAccountRecurring.DiscountAmount","tblAccountRecurring.DiscountType",
            "tblAccountRecurring.OneOffCurrencyID","tblAccountRecurring.RecurringCurrencyID",
            "tblAccountRecurring.AccountRecurringID as AID",
        ];

        $subscriptions = AccountRecurring::join('tblBillingSubscription', 'tblAccountRecurring.SubscriptionID', '=', 'tblBillingSubscription.SubscriptionID')->where("tblAccountRecurring.AccountID",$id);

        $subscriptions->leftJoin('speakintelligentRM.tblCurrency as CurrencyTbl1', 'tblAccountRecurring.OneOffCurrencyID', '=', 'CurrencyTbl1.CurrencyID');
        $subscriptions->leftJoin('speakintelligentRM.tblCurrency as CurrencyTbl2', 'tblAccountRecurring.RecurringCurrencyID', '=', 'CurrencyTbl2.CurrencyID');
        if(!empty($data['SubscriptionName'])){
            $subscriptions->where('tblBillingSubscription.Name','Like','%'.trim($data['SubscriptionName']).'%');
        }
        if(!empty($data['SubscriptionInvoiceDescription'])){
            $subscriptions->where('tblAccountRecurring.InvoiceDescription','Like','%'.trim($data['SubscriptionInvoiceDescription']).'%');
        }
        if(!empty($data['ServiceID'])){
            $subscriptions->where('tblAccountRecurring.ServiceID','=',$data['ServiceID']);
        }else{
            $subscriptions->where('tblAccountRecurring.ServiceID','=',0);
        }
        if(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'true'){
            $subscriptions->where('tblAccountRecurring.Status','=',1);

        }elseif(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'false'){
            $subscriptions->where('tblAccountRecurring.Status','=',0);
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
        $data['Status'] = isset($data['Status']) ? 1 : 0;

        $dynamiceFields = array();
        AccountSubscription::$rules['SubscriptionID'] = 'required|unique:tblAccountRecurring,AccountSubscriptionID,NULL,SubscriptionID,'.$data['SubscriptionID'].',AccountID,'.$data["AccountID"];

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $rules = array(
            'AccountID'       => 'required',
            'SubscriptionID'  => 'required',
            'StartDate'       => 'required',
			'MonthlyFee'      => 'required|numeric',
            'WeeklyFee'       => 'required|numeric',
            'DailyFee'        => 'required|numeric',
            'ActivationFee'   => 'required|numeric',
            'Qty'             => 'required|numeric',

        );

        unset($data['Status_name']);
        if(empty($data['SequenceNo'])){
            $SequenceNo = AccountRecurring::where(['AccountID'=>$data["AccountID"]])->max('SequenceNo');
            $SequenceNo = $SequenceNo +1;
            $data['SequenceNo'] = $SequenceNo;
        }


        if(isset($data['dynamicFileds']))
        {
            Log::info('Trach Line...1' . count($data['dynamicFileds']));
            $dynamicData['dynamicFileds'] = $data['dynamicFileds'];
            unset($data['dynamicFileds']);
        }

        if(isset($data['ImageID']) && !empty($data['ImageID']))
        {
            $DaynamicImageID     = $data['ImageID'];
            $GetDynamicImageInfo = $data['dynamicImage'];
            unset($data['ImageID']);
        }
        unset($data['dynamicImage']);
        $data['Status'] = '1';

        if ($AccountRecurring = AccountRecurring::create($data)) {
            return Response::json(array("status" => "success", "message" => "Subscription Successfully Created",'LastID'=>$AccountRecurring->AccountRecurringID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Subscription."));
        }
	}

	public function update($AccountID,$AccountRecurringID)
	{
        if( $AccountID  > 0  && $AccountRecurringID > 0 ) {
            $data = Input::all();

            $data['Status'] = isset($data['Status']) ? 1 : 0;
            $AccountRecurringID = $data['AccountRecurringID'];
            $AccountRecurring = AccountRecurring::find($AccountRecurringID);
            $data["AccountID"] = $AccountID;
            $data["ModifiedBy"] = User::get_user_full_name();
            $data['ExemptTax'] = isset($data['ExemptTax']) ? 1 : 0;
            AccountRecurring::$rules['SubscriptionID'] = 'required|unique:tblAccountRecurring,AccountSubscriptionID,NULL,SubscriptionID,' . $data['SubscriptionID'] . ',AccountID,' . $data["AccountID"];

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
            if (!empty($data['EndDate'])) {
                $rules['StartDate'] = 'required|date|before:EndDate';
                $rules['EndDate'] = 'required|date';
            }
            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['Status_name']);
            if(isset($data['dynamicFileds']))
            {
                $dynamiceFields = $data['dynamicFileds'];

                unset($data['dynamicFileds']);
                unset($data['AccountSubscriptionID']);
            }

            if(isset($data['dynamicSelect']) && !empty($data['dynamicSelect']))
            {
                $selectBox = $data['dynamicSelect'];
                $dynamiceFields[] = implode(",",$selectBox);
                unset( $data['dynamicSelect']);
            }
            $companyID = User::get_companyID();

            if (isset($data['dynamicImage']) && !empty($data['dynamicImage'])) {
                $GetDynamicImg['dynamicImage'] = $data['dynamicImage'];
            }
            unset($data['dynamicImage']);

            try {
                $AccountRecurring->update($data);

                return Response::json(array("status" => "success", "message" => "Subscription Successfully Updated", 'LastID' => $AccountRecurring->AccountRecurringID));
            }catch(Exception $ex){
                Log::info('Trach Line...' . $ex->getTraceAsString());
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:" . $ex->getMessage()));
            }
        }
	}


	public function delete($AccountID,$AccountRecurringID)
	{
        if( intval($AccountRecurringID) > 0){

            if(!AccountRecurring::checkForeignKeyById($AccountRecurringID)){
                try{
                    $AccountRecurring = AccountRecurring::find($AccountRecurringID);

                    $result = $AccountRecurring->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Subscription Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Subscription."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Subscription is in Use, You can not delete this Subscription."));
            }
        }
	}

	function GetAccountServices($id){
	    $data = Input::all();
        $select = ["tblService.ServiceID","tblService.ServiceName"];
        $services = AccountService::join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')->where("tblAccountService.AccountID",$id);
        $services->where(function($query){ $query->where('tblAccountService.Status','=','1'); });
        $services->select($select);
		$ServicesDataDb =  $services->get();
		$servicesArray = array();

		foreach($ServicesDataDb as $ServicesData){				
			$servicesArray[$ServicesData->ServiceName] =	$ServicesData->ServiceID; 						
		} 
		return $servicesArray;
	}
	
	function GetAccountSubscriptions($id){
		$account = Account::find($id);
		$subscriptions =  BillingSubscription::getSubscriptionsArray($account->CompanyId,$account->CurrencyId);	
		return $subscriptions;
	}

}