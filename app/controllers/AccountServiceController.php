<?php

class AccountServiceController extends \BaseController {

    // view account edit page
	public function edit($id,$ServiceID){
        //Account::getAccountIDList(); exit;
        //AccountService::getAccountServiceIDList($id); exit;
        $account = Account::find($id);
        $CompanyID = User::get_companyID();
		$AccountID = $id;
        $ServiceName = Service::getServiceNameByID($ServiceID);
        $decimal_places = get_round_decimal_places($id);
        $products = Product::getProductDropdownList($CompanyID);
        $taxes = TaxRate::getTaxRateDropdownIDListForInvoice(0,$CompanyID);
        $rate_table = RateTable::getRateTableList(array('CurrencyID'=>$account->CurrencyId));
        $DiscountPlan = DiscountPlan::getDropdownIDList($CompanyID,(int)$account->CurrencyId);

        $InboundTariffID = '';
        $OutboundTariffID = '';

        $InboundTariff = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::INBOUND))->first();
        if(!empty($InboundTariff) && count($InboundTariff) > 0 ){
            $InboundTariffID = empty($InboundTariff->RateTableID) ? '' : $InboundTariff->RateTableID;
        }

        $OutboundTariff = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::OUTBOUND))->first();
        if(!empty($OutboundTariff) && count($OutboundTariff) > 0 ){
            $OutboundTariffID = empty($OutboundTariff->RateTableID) ? '' : $OutboundTariff->RateTableID;
        }
        //Billing
        $invoice_count = Account::getInvoiceCount($id);
        $BillingClass = BillingClass::getDropdownIDList(User::get_companyID());
        $timezones = TimeZone::getTimeZoneDropdownList();
        $AccountBilling =  AccountBilling::getBilling($id,$ServiceID);
        $AccountNextBilling =  AccountNextBilling::getBilling($id,$ServiceID);

        $DiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'Type'=>AccountDiscountPlan::OUTBOUND,'ServiceID'=>$ServiceID,'AccountSubscriptionID'=>0))->pluck('DiscountPlanID');
        $InboundDiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'Type'=>AccountDiscountPlan::INBOUND,'ServiceID'=>$ServiceID,'AccountSubscriptionID'=>0))->pluck('DiscountPlanID');

        $ServiceTitle = AccountService::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->pluck('ServiceTitle');
        $ServiceDescription = AccountService::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->pluck('ServiceDescription');
        $ServiceTitleShow = AccountService::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->pluck('ServiceTitleShow');

		return View::make('accountservices.edit', compact('AccountID','ServiceID','ServiceName','account','decimal_places','products','taxes','rate_table','DiscountPlan','InboundTariffID','OutboundTariffID','invoice_count','BillingClass','timezones','AccountBilling','AccountNextBilling','DiscountPlanID','InboundDiscountPlanID','ServiceTitle','ServiceDescription','ServiceTitleShow'));
	}

    // add account services
    public function addservices($id){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $services = $data['ServiceID'];
        $accountid = $data['AccountID'];
        $servicedata = array();
        if(!empty($services) && count($services)>0 && !empty($accountid)){
            $message = '';
            foreach($services as $service){
                if(AccountService::where(array('AccountID'=>$accountid,'CompanyID'=>$CompanyID,'ServiceID'=>$service))->count()){
                    $ServiceName = Service::getServiceNameByID($service);
                    $message .= $ServiceName.' already exists <br>';
                }else{
                    $servicedata['ServiceID'] = $service;
                    $servicedata['AccountID'] = $data['AccountID'];
                    $servicedata['CompanyID'] = $CompanyID;
                    AccountService::insert($servicedata);
                }
            }
            if(!empty($message)){
                $message = 'Following service already exists.<br>'.$message;
                return Response::json(array("status" => "success", "message" => $message));
            }else{
                return Response::json(array("status" => "success", "message" => "Services Successfully Added"));
            }
        }else{
            if(empty($accountid)){
                return Response::json(array("status" => "failed", "message" => "No Account selected."));
            }else{
                return Response::json(array("status" => "failed", "message" => "No Services selected."));
            }
        }
    }

    // get all account service
    public function ajax_datagrid($id){
        $data = Input::all();
        $id=$data['account_id'];
        $select = ["tblAccountService.ServiceID","tblService.ServiceName","tblAccountService.ServiceTitle","tblAccountService.Status","tblAccountService.ServiceID","tblAccountService.AccountServiceID"];
        $services = AccountService::join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')->where("tblAccountService.AccountID",$id);
        if(!empty($data['ServiceName'])){
            $services->where('tblService.ServiceName','Like','%'.trim($data['ServiceName']).'%');
        }
        if(!empty($data['ServiceActive']) && $data['ServiceActive'] == 'true'){
            $services->where(function($query){
                $query->where('tblAccountService.Status','=','1');
            });

        }elseif(!empty($data['ServiceActive']) && $data['ServiceActive'] == 'false'){
            $services->where(function($query){
                $query->where('tblAccountService.Status','=','0');
            });
        }
        $services->select($select);

        return Datatables::of($services)->make();
    }

    // account service edit page data store and update
	public function update($AccountID,$ServiceID)
	{
        if( $AccountID  > 0  && $ServiceID > 0 ) {
            $data = Input::all();

            $date = date('Y-m-d H:i:s');
            $data['Billing'] = isset($data['Billing']) ? 1 : 0;
            $data['ServiceBilling'] = isset($data['ServiceBilling']) ? 1 : 0;
            $CompanyID = User::get_companyID();
            if(empty($data['ServiceTitleShow'])){
                if(empty($data['ServiceDescription'])){
                    return Response::json(array("status" => "failed", "message" => "Please fill Service Description."));
                }
            }

            if($data['ServiceBilling'] == 1) {
                if (!empty($data['BillingStartDate']) || !empty($data['BillingCycleType']) || !empty($data['BillingCycleValue']) || !empty($data['BillingClassID'])) {
                    AccountService::$rules['BillingCycleType'] = 'required';
                    AccountService::$rules['BillingStartDate'] = 'required';
                    AccountService::$rules['BillingClassID'] = 'required';
                    if (isset($data['BillingCycleValue'])) {
                        AccountService::$rules['BillingCycleValue'] = 'required';
                    }
                }
            }

            $validator = Validator::make($data, AccountService::$rules, AccountService::$messages);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            $OutboundDiscountPlan = empty($data['DiscountPlanID']) ? '' : $data['DiscountPlanID'];
            $InboundDiscountPlan = empty($data['InboundDiscountPlanID']) ? '' : $data['InboundDiscountPlanID'];

            //billing
            //$invoice_count = Account::getInvoiceCount($AccountID);
            $invoice_count = 0;
            if($invoice_count == 0 && $data['ServiceBilling'] == 1){
                $data['LastInvoiceDate'] = $data['BillingStartDate'];
                $data['LastChargeDate'] = $data['BillingStartDate'];
                if($data['BillingStartDate']==$data['NextInvoiceDate']){
                    $data['NextChargeDate']=$data['BillingStartDate'];
                }else{
                    $data['NextChargeDate'] = date('Y-m-d', strtotime('-1 day', strtotime($data['NextInvoiceDate'])));;
                }
            }

            $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), 0);

            if($data['ServiceBilling'] == 1) {
                if (!empty($data['BillingStartDate']) || !empty($data['BillingCycleType']) || !empty($data['BillingCycleValue']) || !empty($data['BillingClassID'])) {
                    if ($data['NextInvoiceDate'] < $data['LastInvoiceDate']) {
                        return Response::json(array("status" => "failed", "message" => "Please Select Appropriate Date."));
                    }
                    if ($data['NextChargeDate'] < $data['LastChargeDate']) {
                        return Response::json(array("status" => "failed", "message" => "Please Select Appropriate Date."));
                    }
                    AccountBilling::insertUpdateBilling($AccountID, $data, $ServiceID, $invoice_count);
                    AccountBilling::storeFirstTimeInvoicePeriod($AccountID, $ServiceID);
                    $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), $ServiceID);
                }
            }
            if(!empty($AccountPeriod)) {
                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                $AccountSubscriptionID = 0;
                $AccountName='';
                $AccountCLI='';
                $SubscriptionDiscountPlanID=0;
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff,$ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff,$ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
            }


            //AccountTariff
            $InboundTariff = empty($data['InboundTariffID']) ? '' : $data['InboundTariffID'];
            $OutboundTariff = empty($data['OutboundTariffID']) ? '' : $data['OutboundTariffID'];

            $inbounddata = array();
            $inbounddata['CompanyID'] = $CompanyID;
            $inbounddata['AccountID'] = $AccountID;
            $inbounddata['ServiceID'] = $ServiceID;
            $inbounddata['RateTableID'] = $InboundTariff;
            $inbounddata['Type'] = AccountTariff::INBOUND;

            $outbounddata = array();
            $outbounddata['CompanyID'] = $CompanyID;
            $outbounddata['AccountID'] = $AccountID;
            $outbounddata['ServiceID'] = $ServiceID;
            $outbounddata['RateTableID'] = $OutboundTariff;
            $outbounddata['Type'] = AccountTariff::OUTBOUND;

            if(!empty($InboundTariff)){
                $count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::INBOUND))->count();
                if(!empty($count) && $count>0){
                    AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::INBOUND))
                                    ->update(array('RateTableID' => $InboundTariff, 'updated_at' => $date));
                }else{
                    $inbounddata['created_at'] = $date;
                    AccountTariff::create($inbounddata);
                }
            }else{
                $count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::INBOUND))->count();
                if(!empty($count) && $count>0){
                    AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::INBOUND))->delete();
                }
            }

            if(!empty($OutboundTariff)){
                $count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::OUTBOUND))->count();
                if(!empty($count) && $count>0){
                    AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::OUTBOUND))
                        ->update(array('RateTableID' => $OutboundTariff, 'updated_at' => $date));
                }else{
                    $outbounddata['created_at'] = $date;
                    AccountTariff::create($outbounddata);
                }
            }else{
                $count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::OUTBOUND))->count();
                if(!empty($count) && $count>0){
                    AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::OUTBOUND))->delete();
                }
            }

            $accdata=array();
            $accdata['ServiceTitle'] = empty($data['ServiceTitle']) ? '':$data['ServiceTitle'];
            $accdata['ServiceDescription'] = empty($data['ServiceDescription']) ? '':$data['ServiceDescription'];
            $accdata['ServiceTitleShow'] = isset($data['ServiceTitleShow']) ? 1 : 0;
            AccountService::where(['AccountID'=>$AccountID,'ServiceID'=>$ServiceID])->update($accdata);

            return Response::json(array("status" => "success", "message" => "Account Service Successfully updated."));
        }
        return Response::json(array("status" => "failed", "message" => "Problem Creating Account Service."));
	}

    public function changestatus($ServiceID,$Status){
        $data = Input::all();
        $AccountID = $data['accountid'];

        if ($ServiceID && $Status) {
            $Action = AccountService::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->first();
            if ($Status == 'active') {
                $save['Status'] = 1;
            } else if ($Status == 'deactive') {
                $save['Status'] = 0;
            }
            if($Action->update($save)){
                return Response::json(array("status" => "success", "message" => "Service Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Service."));
            }
        }
    }

	public function delete($AccountID,$ServiceID)
	{
        if( intval($AccountID) > 0 && intval($ServiceID) > 0){

            if(AccountService::checkForeignKeyById($AccountID,$ServiceID)){
                try{
                    $result = AccountService::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Service Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Service."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Service is in Use, You can not delete this Service."));
            }
        }
	}

    public function cloneservice($AccountID){
        $data = Input::all();
        if(empty($data['ServiceTitle']) && empty($data['Subscription']) && empty($data['Additional']) && empty($data['Billing']) && empty($data['Tariff']) && empty($data['DiscountPlan'])){
            return Response::json(array("status" => "failed", "message" => "No Service Section selected."));
        }

        $data['ServiceTitle'] = empty($data['ServiceTitle']) ? '' : $data['ServiceTitle'];
        $data['Subscription'] = empty($data['Subscription']) ? '' : $data['Subscription'];
        $data['Additional'] = empty($data['Additional']) ? '' : $data['Additional'];
        $data['Billing'] = empty($data['Billing']) ? '' : $data['Billing'];
        $data['Tariff'] = empty($data['Tariff']) ? '' : $data['Tariff'];
        $data['DiscountPlan'] = empty($data['DiscountPlan']) ? '' : $data['DiscountPlan'];


        $CloneIDs = $data['CloneID'];
        if(!empty($data['AccountID'])){
            $AccountIDs = $data['AccountID'];
            //service id for clone
            if(!empty($data['criteria'])){
                $criteria = $data['criteria'];
                $criteria = json_decode($criteria,true);


                $select = ["tblAccountService.ServiceID"];
                $services = AccountService::join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')->where("tblAccountService.AccountID",$AccountID);
                if(!empty($criteria['ServiceName'])){
                    $services->where('tblService.ServiceName','Like','%'.trim($criteria['ServiceName']).'%');
                }
                if(!empty($criteria['ServiceActive']) && $criteria['ServiceActive'] == 'true'){
                    $services->where(function($query){
                        $query->where('tblAccountService.Status','=','1');
                    });

                }elseif(!empty($criteria['ServiceActive']) && $criteria['ServiceActive'] == 'false'){
                    $services->where(function($query){
                        $query->where('tblAccountService.Status','=','0');
                    });
                }
                $results = $services->get($select);

                $results = json_decode(json_encode($results),true);
                if(empty($results)){
                    return Response::json(array("status" => "failed", "message" => "No Services selected."));
                }
                $CloneIDs='';
                foreach($results as $result){
                    $CloneIDs.= $result['ServiceID'].',';
                }
                $CloneIDs = rtrim($CloneIDs,',');


            }
            if(!empty($CloneIDs)){
                $CloneIDs=explode(',',$CloneIDs);
                $data['SourceAccountID'] = $AccountID;
                $data['AccountIDs'] = $AccountIDs;
                $data['ServiceIDs'] = $CloneIDs;

                $clone_result = AccountService::CloneServices($data);
                if(!empty($clone_result['Success'])){
                    return Response::json(array("status" => "success", "message" => $clone_result['Success']));
                }elseif(!empty($clone_result['Error'])){
                    return Response::json(array("status" => "failed", "message" => "Problem cloning service."));
                }
            }

            return Response::json(array("status" => "failed", "message" => "Problem cloning service."));
        }
        return Response::json(array("status" => "failed", "message" => "No Accounts selected."));

    }

    public function search_accounts_grid($AccountID){
        //$AllAccounts = Account::getAllAccounts();
        //return Account::getCustomersGridPopup($opt);
        return Account::getAllAccounts($AccountID);
    }

    public function bulk_change_status($AccountID){
        $data = Input::all();
        $ServiceIds = array();
        $save = array();
        if(!empty($data['action']) && !empty($AccountID)){
            if(!empty($data['Criteria'])){
                $criteria = $data['Criteria'];
                $criteria = json_decode($criteria,true);
                $ServiceIds = AccountService::getServiceIDsByCriteria($AccountID,$criteria);
            }
            if($data['ServiceID']){
                $ServiceIds = $data['ServiceID'];
                $ServiceIds=explode(',',$ServiceIds);
            }

            if ($data['action'] == 'active') {
                $save['Status'] = 1;
            } else if ($data['action'] == 'deactive') {
                $save['Status'] = 0;
            }
            if(AccountService::whereIn('ServiceID',$ServiceIds)->where(array('AccountID'=>$AccountID))->update($save)){
                return Response::json(array("status" => "success", "message" => "Service Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Service."));
            }

        }

        return Response::json(array("status" => "failed", "message" => "Problem Updating Service."));
    }

    public function bulk_delete($AccountID){
        $data = Input::all();
        $ServiceIds = array();
        $save = array();
        $errormsg = '';
        if(!empty($data['action']) && !empty($AccountID)){
            if(!empty($data['Criteria'])){
                $criteria = $data['Criteria'];
                $criteria = json_decode($criteria,true);
                $ServiceIds = AccountService::getServiceIDsByCriteria($AccountID,$criteria);
            }
            if($data['ServiceID']){
                $ServiceIds = $data['ServiceID'];
                $ServiceIds=explode(',',$ServiceIds);
            }
            $error = '';
            try {
                foreach ($ServiceIds as $Service => $key) {
                    if (AccountService::checkForeignKeyById($AccountID, $key)) {
                        AccountService::where(array('AccountID' => $AccountID, 'ServiceID' => $key))->delete();
                    } else {
                        $ServiceName = Service::getServiceNameByID($key);
                        $error .= '<br>' . $ServiceName;

                    }
                }
                if (!empty($error)) {
                    $errormsg = '<br>Following Service is Use,you can not delete.' . $error;
                }
                $message = 'Service Sucessfully deleted.' . $errormsg;
                return Response::json(array("status" => "success", "message" => $message));
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }

        }

        return Response::json(array("status" => "failed", "message" => "Problem Updating Service."));
    }

    public function exports($id,$type){
        $data = Input::all();

        $select = ["tblAccountService.ServiceID","tblService.ServiceName","tblAccountService.Status"];
        $services = AccountService::join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')->where("tblAccountService.AccountID",$id);
        if(!empty($data['ServiceName'])){
            $services->where('tblService.ServiceName','Like','%'.trim($data['ServiceName']).'%');
        }
        if(!empty($data['ServiceActive']) && $data['ServiceActive'] == 'true'){
            $services->where(function($query){
                $query->where('tblAccountService.Status','=','1');
            });

        }elseif(!empty($data['ServiceActive']) && $data['ServiceActive'] == 'false'){
            $services->where(function($query){
                $query->where('tblAccountService.Status','=','0');
            });
        }
        $services->select($select);

        $servicedata =  $services->get();


        $servicedata = json_decode(json_encode($servicedata),true);
        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AccountServices.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($servicedata);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AccountServices.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($servicedata);
        }

    }
}