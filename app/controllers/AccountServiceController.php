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
        $products = Product::getProductDropdownList();
        $taxes = TaxRate::getTaxRateDropdownIDListForInvoice(0);
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

        $DiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'Type'=>AccountDiscountPlan::OUTBOUND,'ServiceID'=>$ServiceID))->pluck('DiscountPlanID');
        $InboundDiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'Type'=>AccountDiscountPlan::INBOUND,'ServiceID'=>$ServiceID))->pluck('DiscountPlanID');

        $ServiceTitle = AccountService::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->pluck('ServiceTitle');


		return View::make('accountservices.edit', compact('AccountID','ServiceID','ServiceName','account','decimal_places','products','taxes','rate_table','DiscountPlan','InboundTariffID','OutboundTariffID','invoice_count','BillingClass','timezones','AccountBilling','AccountNextBilling','DiscountPlanID','InboundDiscountPlanID','ServiceTitle'));
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
        $select = ["tblService.ServiceName","tblAccountService.Status","tblAccountService.ServiceID","tblAccountService.AccountServiceID"];
        $services = AccountService::join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')->where("tblAccountService.AccountID",$id);
        if(!empty($data['SubscriptionName'])){
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
            $CompanyID = User::get_companyID();

            if(!empty($data['BillingStartDate']) || !empty($data['BillingCycleType']) || !empty($data['BillingCycleValue']) || !empty($data['BillingClassID'])){
                AccountService::$rules['BillingCycleType'] = 'required';
                AccountService::$rules['BillingStartDate'] = 'required';
                AccountService::$rules['BillingClassID'] = 'required';
                if(isset($data['BillingCycleValue'])){
                    AccountService::$rules['BillingCycleValue'] = 'required';
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
            if($invoice_count == 0){
                $data['LastInvoiceDate'] = $data['BillingStartDate'];
            }

            if(!empty($data['BillingStartDate']) || !empty($data['BillingCycleType']) || !empty($data['BillingCycleValue'])  || !empty($data['BillingClassID'])){
                AccountBilling::insertUpdateBilling($AccountID, $data,$ServiceID);
                AccountBilling::storeFirstTimeInvoicePeriod($AccountID,$ServiceID);
                $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'),$ServiceID);
            }else{
                $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'),0);
            }
            if(!empty($AccountPeriod)) {
                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff,$ServiceID);
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff,$ServiceID);
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

}