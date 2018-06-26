<?php

class AccountSubscriptionController extends \BaseController {

public function main() {	
		$data 				=  Input::all();        
		$id					=  $data['id'];
		$companyID 			=  User::get_companyID();
		$SelectedAccount    =  Account::find($id);	 
		$accounts	 		=  Account::getAccountIDList();
		$services 			=  Service::getDropdownIDList($companyID);
        $DiscountPlan = DiscountPlan::getDiscountPlanIDList($companyID);
	    return View::make('accountsubscription.main', compact('accounts','services','SelectedAccount','services','DiscountPlan'));

    }

    /** Used in Account Service Edit Page */
    public function ajax_datagrid($id){
        $data = Input::all();        
        $id=$data['account_id'];
        $select = ["tblAccountSubscription.AccountSubscriptionID as AID","tblAccountSubscription.SequenceNo","tblBillingSubscription.Name", "InvoiceDescription", "Qty" ,"tblAccountSubscription.StartDate",DB::raw("IF(tblAccountSubscription.EndDate = '0000-00-00','',tblAccountSubscription.EndDate) as EndDate"),"tblAccountSubscription.ActivationFee","tblAccountSubscription.DailyFee","tblAccountSubscription.WeeklyFee","tblAccountSubscription.MonthlyFee","tblAccountSubscription.QuarterlyFee","tblAccountSubscription.AnnuallyFee","tblAccountSubscription.AccountSubscriptionID","tblAccountSubscription.SubscriptionID","tblAccountSubscription.ExemptTax","tblAccountSubscription.Status"];
        $subscriptions = AccountSubscription::join('tblBillingSubscription', 'tblAccountSubscription.SubscriptionID', '=', 'tblBillingSubscription.SubscriptionID')->where("tblAccountSubscription.AccountID",$id);        
        if(!empty($data['SubscriptionName'])){
            $subscriptions->where('tblBillingSubscription.Name','Like','%'.trim($data['SubscriptionName']).'%');
        }
        if(!empty($data['SubscriptionInvoiceDescription'])){
            $subscriptions->where('tblAccountSubscription.InvoiceDescription','Like','%'.trim($data['SubscriptionInvoiceDescription']).'%');
        }
        if(!empty($data['ServiceID'])){
            $subscriptions->where('tblAccountSubscription.ServiceID','=',$data['ServiceID']);
        }else{
            $subscriptions->where('tblAccountSubscription.ServiceID','=',0);
        }
        if(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'true'){
            $subscriptions->where('tblAccountSubscription.Status','=',1);

        }elseif(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'false'){
            $subscriptions->where('tblAccountSubscription.Status','=',0);
        }
        $subscriptions->select($select);

        return Datatables::of($subscriptions)->make();
    }

    /** Used in Main Account Subscription Page */
	public function ajax_datagrid_page($type=''){
        $data 						 = 	Input::all(); //Log::info(print_r($data,true));
        $data['iDisplayStart'] 		+=	1;
        $companyID 					 =  User::get_companyID(); 
        $columns 					 =  ['SequenceNo','AccountName','ServiceName','Name','Qty','StartDate','EndDate','ActivationFee','DailyFee','WeeklyFee','MonthlyFee','QuarterlyFee','AnnuallyFee'];   
        $sort_column 				 =  $columns[$data['iSortCol_0']];
        $data['AccountID'] 			 =  empty($data['AccountID'])?'0':$data['AccountID'];
		if($data['Active'] == 'true'){
			$data['Active']	=	1;
		}else{
			$data['Active'] =   0;
		}
		$data['ServiceID'] 			 =  empty($data['ServiceID'])?'null':$data['ServiceID'];
        $query = "call prc_GetAccountSubscriptions (".$companyID.",".intval($data['AccountID']).",".intval($data['ServiceID']).",'".$data['Name']."','".$data['Active']."','".date('Y-m-d')."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".strtoupper($data['sSortDir_0'])."'";
		
        if(isset($data['Export']) && $data['Export'] == 1)
		{
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
			
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/accountsubscription.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/subscription.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }         
        }
		

        $query .=',0)'; Log::info($query);
       // echo $query;exit;
        $result =  DataTableSql::of($query,'sqlsrv2')->make();
		return $result;
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
        if(!empty($data['EndDate'])) {
            $rules['StartDate'] = 'required|date|before:EndDate';
            $rules['EndDate'] = 'required|date';
        }
        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['Status_name']);
        if(empty($data['SequenceNo'])){
            $SequenceNo = AccountSubscription::where(['AccountID'=>$data["AccountID"]])->max('SequenceNo');
            $SequenceNo = $SequenceNo +1;
            $data['SequenceNo'] = $SequenceNo;
        }
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
            $data['Status'] = isset($data['Status']) ? 1 : 0;
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
            if(!empty($data['EndDate'])) {
                $rules['StartDate'] = 'required|date|before:EndDate';
                $rules['EndDate'] = 'required|date';
            }
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
                    $SubscriptionDiscountPlanCount = SubscriptionDiscountPlan::where("AccountSubscriptionID",$AccountSubscriptionID)->count();
                    if($SubscriptionDiscountPlanCount > 0)
                    {
                        return Response::json(array("status" => "failed", "message" => "Subscription is in Use, Please Delete Discount Plan."));
                        //$SubscriptionDiscountPlanCount = SubscriptionDiscountPlan::where("AccountSubscriptionID",$AccountSubscriptionID)->delete();
                    }
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
                return Response::json(array("status" => "failed", "message" => "Subscription is in Use, You can not delete this Subscription."));
            }
        }
	}

    public function store_discountplan($id)
    {
        $data = Input::all();
        $data["AccountID"] = $id;
        $data["AccountSubscriptionID"] = $data["AccountSubscriptionID_dp"];
        unset($data["AccountSubscriptionID_dp"]);
       // $data["CreatedBy"] = User::get_user_full_name();
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv');

        $rules = array(
            'AccountName'           =>  'required|unique:tblAccountDiscountPlan,AccountName',
            'AccountCLI'            =>  'unique:tblAccountDiscountPlan,AccountCLI',
            //'AccountCLI'            =>  'required|unique:tblSubscriptionDiscountPlan,AccountCLI',
        );

        $message = [
            'AccountCLI.unique'=>'Account CLI field is already taken.'
        ];

        $validator = Validator::make($data, $rules, $message);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($data['AccountCLI'] == "")
        {
            $data['AccountCLI'] = NULL;
        }

        $AccountID = $data['AccountID'];
        $ServiceID = $data['ServiceID'];
        $OutboundDiscountPlan = empty($data['OutboundDiscountPlans']) ? '' : $data['OutboundDiscountPlans'];
        $InboundDiscountPlan = empty($data['InboundDiscountPlans']) ? '' : $data['InboundDiscountPlans'];
        $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), 0);
        try {
            DB::beginTransaction();
            $SubscriptionDiscountPlan = SubscriptionDiscountPlan::create($data);
            if (!empty($SubscriptionDiscountPlan->SubscriptionDiscountPlanID) && !empty($AccountPeriod)) {

                log::info('SubscriptionDiscountPlanID ' . $SubscriptionDiscountPlan->SubscriptionDiscountPlanID);
                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                $AccountSubscriptionID = $data['AccountSubscriptionID'];
                $AccountName = empty($data['AccountName']) ? '' : $data['AccountName'];
                $AccountCLI = empty($data['AccountCLI']) ? '' : $data['AccountCLI'];
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $ServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlan->SubscriptionDiscountPlanID);
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $ServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlan->SubscriptionDiscountPlanID);
                DB::commit();
                return Response::json(array("status" => "success", "message" => "Subscription Account Added", 'LastID' => $SubscriptionDiscountPlan->SubscriptionDiscountPlanID));

            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Adding Subscription Account."));
            }
        }catch( Exception $e){
            try {
                DB::rollback();
            } catch (\Exception $err) {
                Log::error($err);
            }
            Log::error($e);
            return Response::json(array("status" => "failed", "message" => "Problem Adding Subscription Account."));
        }

        /*
        if ($SubscriptionDiscountPlan = SubscriptionDiscountPlan::create($data)) {
            return Response::json(array("status" => "success", "message" => "Subscription Account Added",'LastID'=>$SubscriptionDiscountPlan->SubscriptionDiscountPlanID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Adding Subscription Account."));
        }*/
    }

    function edit_discountplan(){
        $data = Input::all();
        $SubscriptionDiscountPlan =  SubscriptionDiscountPlan::getSubscriptionDiscountPlanById($data['SubscriptionDiscountPlanID']);
        return $SubscriptionDiscountPlan;
    }

    public function update_discountplan()
    {
        $data = Input::all();
        $data["AccountSubscriptionID"] = $data["AccountSubscriptionID_dp"];
        unset($data["AccountSubscriptionID_dp"]);
        //unset($data["AccountSubscriptionID"]);
        $SubscriptionDiscountPlan = SubscriptionDiscountPlan::find($data['SubscriptionDiscountPlanID']);
        // $data["CreatedBy"] = User::get_user_full_name();
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv');

        $rules = array(
            'AccountName'           =>  'required|unique:tblSubscriptionDiscountPlan,AccountName,' . $data['SubscriptionDiscountPlanID'] . ',SubscriptionDiscountPlanID',
            'AccountCLI'            =>  'unique:tblSubscriptionDiscountPlan,AccountCLI,' . $data['SubscriptionDiscountPlanID'] . ',SubscriptionDiscountPlanID',
            //'AccountCLI'            =>  'required|unique:tblSubscriptionDiscountPlan,AccountCLI,' . $data['SubscriptionDiscountPlanID'] . ',SubscriptionDiscountPlanID',
        );
        $message = [
            'AccountCLI.unique'=>'Account CLI field is already taken.'
        ];

        $validator = Validator::make($data, $rules,$message);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if($data['AccountCLI'] == "")
        {
            $data['AccountCLI'] = NULL;
        }

        $AccountID = $SubscriptionDiscountPlan->AccountID;
        $ServiceID = $SubscriptionDiscountPlan->ServiceID;
        $OutboundDiscountPlan = empty($data['OutboundDiscountPlans']) ? '' : $data['OutboundDiscountPlans'];
        $InboundDiscountPlan = empty($data['InboundDiscountPlans']) ? '' : $data['InboundDiscountPlans'];
        $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), 0);
        try {
            DB::beginTransaction();
            $SubscriptionDiscountPlan->update($data);
            $SubscriptionDiscountPlanID = $data['SubscriptionDiscountPlanID'];
            if (!empty($SubscriptionDiscountPlanID) && !empty($AccountPeriod)) {
                log::info('SubscriptionDiscountPlanID ' . $SubscriptionDiscountPlanID);
                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                $AccountSubscriptionID = $data['AccountSubscriptionID'];
                $AccountName = empty($data['AccountName']) ? '' : $data['AccountName'];
                $AccountCLI = empty($data['AccountCLI']) ? '' : $data['AccountCLI'];

                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $ServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $ServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                DB::commit();
                return Response::json(array("status" => "success", "message" => "Subscription Account Updated", 'LastID' => $SubscriptionDiscountPlanID));

            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Subscription Account."));
            }
        }catch( Exception $e){
            try {
                DB::rollback();
            } catch (\Exception $err) {
                Log::error($err);
            }
            Log::error($e);
            return Response::json(array("status" => "failed", "message" => "Problem Updating Subscription Account."));
        }
    }

    // not in use
    public function bulkupdate_discountplan()
    {
        $data = Input::all();
        $AllSubscriptionDiscountPlanID  = $data["AllSubscriptionDiscountPlanID"];
        if(!isset($data['InboundCheckbox']) && !isset($data['OutboundCheckbox']))
        {
            return Response::json(array("status" => "error", "message" => "Please select at least one field."));
            return false;
        }

        if(isset($data["InboundCheckbox"]))
        {
            if($data['BulkInboundDiscountPlans'] == '')
            {
                return Response::json(array("status" => "error", "message" => "Please select Value of Inbound Discount Plans"));
            }
            unset($data['InboundCheckbox']);
            $data['InboundDiscountPlans'] = $data['BulkInboundDiscountPlans'];
        }

        if(isset($data["OutboundCheckbox"]))
        {
            if($data['BulkOutboundDiscountPlans'] == '')
            {
                return Response::json(array("status" => "error", "message" => "Please select Value of Outbound Discount Plans"));
            }
            unset($data['OutboundCheckbox']);
            $data['OutboundDiscountPlans'] = $data['BulkOutboundDiscountPlans'];
        }

        unset($data['BulkInboundDiscountPlans']);
        unset($data['BulkOutboundDiscountPlans']);
        unset($data['AccountSubscriptionID_bulk']);
        unset($data['ServiceID']);
        unset($data['AllSubscriptionDiscountPlanID']);
        //SubscriptionDiscountPlan::whereIn('SubscriptionDiscountPlanID',$AllSubscriptionDiscountPlanID)->update($data);
        $AllSubscriptionDiscountPlanID = explode(",",$AllSubscriptionDiscountPlanID);
        if (SubscriptionDiscountPlan::whereIn('SubscriptionDiscountPlanID',$AllSubscriptionDiscountPlanID)->update($data)) {
            return Response::json(array("status" => "success", "message" => "Subscription Bulk Account Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Subscription Account."));
        }
    }

    // not in use
    public function bulkdelete_discountplan()
    {
        $data = Input::all();
        $SubscriptionDiscountPlanID  = $data["SubscriptionDiscountPlanID"];
        $SubscriptionDiscountPlanID = explode(",",$SubscriptionDiscountPlanID);
        if (SubscriptionDiscountPlan::whereIn('SubscriptionDiscountPlanID',$SubscriptionDiscountPlanID)->delete()) {
            return Response::json(array("status" => "success", "message" => "Subscription Bulk Accounts Deleted"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Subscription Accounts."));
        }
    }

    function get_discountplan($AccountID){
        $data = Input::all();
        $SubscriptionDiscountPlan =  SubscriptionDiscountPlan::getSubscriptionDiscountPlanArray($AccountID,$data['AccountSubscriptionID'],$data['ServiceID']);
        return $SubscriptionDiscountPlan;
    }

    public function delete_discountplan()
    {
        $data = Input::all();
        $SubscriptionDiscountPlanID = $data['SubscriptionDiscountPlanID'];
        if( intval($SubscriptionDiscountPlanID) > 0){
            $SubscriptionDiscountPlan = SubscriptionDiscountPlan::find($SubscriptionDiscountPlanID);
            $AccountID = $SubscriptionDiscountPlan->AccountID;
            $ServiceID = $SubscriptionDiscountPlan->ServiceID;
            $OutboundDiscountPlan = '';
            $InboundDiscountPlan = '';
            $AccountSubscriptionID = $SubscriptionDiscountPlan->AccountSubscriptionID;
            $AccountName = empty($SubscriptionDiscountPlan->AccountName) ? '' : $SubscriptionDiscountPlan->AccountName;
            $AccountCLI = empty($SubscriptionDiscountPlan->AccountCLI) ? '' : $SubscriptionDiscountPlan->AccountCLI;
            $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), 0);
            try{
                DB::beginTransaction();
                $result = $SubscriptionDiscountPlan->delete();
                if (!empty($result) && !empty($AccountPeriod)) {
                    $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                    $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                    $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                    AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $ServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                    AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $ServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Subscription Account Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Subscription Account."));
                }
            }catch (Exception $ex){
                try {
                    DB::rollback();
                } catch (\Exception $err) {
                    Log::error($err);
                }
                Log::error($ex);
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
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
		
		//
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

    public function getDiscountPlanByAccount(){
        $data = Input::all();
        $AccountID = $data['AccountID'];
        $Response = DiscountPlan::getDropdownIDListByAccount($AccountID);
        return Response::json(array("status" => "success", "data" => $Response));
    }

}