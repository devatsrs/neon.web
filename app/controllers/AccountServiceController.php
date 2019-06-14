<?php

class AccountServiceController extends \BaseController {
    // view account edit page
    public function edit($id,$AccountServiceID){
        Artisan::call('cache:clear');

        //Account::getAccountIDList(); exit;
        //AccountService::getAccountServiceIDList($id); exit;
        $account = Account::find($id);
        $AccountCompanyId = $account->CompanyId;
        $ServiceID = AccountService::getServiceIDByAccountServiceID($AccountServiceID);
        $CompanyID = Account::getCompanyIDByAccountID($id);
        $AccountID = $id;
        $ServiceName = Service::getServiceNameByID($ServiceID);
        $decimal_places = get_round_decimal_places($id);
        $products = Product::getProductDropdownList($CompanyID);
        $taxes = TaxRate::getTaxRateDropdownIDListForInvoice(0, getParentCompanyIdIfReseller($CompanyID));
        $rate_table = RateTable::getRateTableList([
            'types' => [RateGenerator::DID],
            'NotVendor' => true,
        ]);
        $termination_rate_table = RateTable::getRateTableList([
            'types' => [RateGenerator::VoiceCall],
            'NotVendor' => true,
        ]);
        $package_rate_table = RateTable::getRateTableList([
            'types' => [RateGenerator::Package],
            'NotVendor' => true,
        ]);
        //$DiscountPlan = DiscountPlan::getDropdownIDList($CompanyID,(int)$account->CurrencyId);
        $DiscountPlanVOICECALL = DiscountPlan::getDropdownIDListForType($CompanyID,(int)$account->CurrencyId,RateType::VOICECALL_ID);
        $DiscountPlan = $DiscountPlanVOICECALL;
        $DiscountPlanDID = DiscountPlan::getDropdownIDListForType($CompanyID,(int)$account->CurrencyId,RateType::DID_ID);
        $DiscountPlanPACKAGE = DiscountPlan::getDropdownIDListForType($CompanyID,(int)$account->CurrencyId,RateType::PACKAGE_ID);
        $AccountServiceContract = AccountServiceContract::where('AccountServiceID',$AccountServiceID)->first();
        $AccountServiceCancelContract = AccountServiceCancelContract::where('AccountServiceID',$AccountServiceID)->first();



        $InboundTariffID = '';
        $OutboundTariffID = '';

        $InboundTariff = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID,'AccountServiceID'=>$AccountServiceID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::INBOUND))->first();
        if(!empty($InboundTariff) && count($InboundTariff) > 0 ){
            $InboundTariffID = empty($InboundTariff->RateTableID) ? '' : $InboundTariff->RateTableID;
        }

        $OutboundTariff = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID,'AccountServiceID'=>$AccountServiceID, 'ServiceID' => $ServiceID, 'Type' => AccountTariff::OUTBOUND))->first();
        if(!empty($OutboundTariff) && count($OutboundTariff) > 0 ){
            $OutboundTariffID = empty($OutboundTariff->RateTableID) ? '' : $OutboundTariff->RateTableID;
        }
        //Billing
        $invoice_count = Account::getInvoiceCount($id);
        $BillingClass = BillingClass::getDropdownIDList($CompanyID);
        $timezones = TimeZone::getTimeZoneDropdownList();
        $AccountBilling =  AccountBilling::getBillingByAccountService($id,$AccountServiceID);
        $AccountNextBilling =  AccountNextBilling::getBillingByAccountService($id,$AccountServiceID);

        $DiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'AccountServiceID'=>$AccountServiceID,'Type'=>AccountDiscountPlan::OUTBOUND,'ServiceID'=>$ServiceID,'AccountSubscriptionID'=>0))->pluck('DiscountPlanID');
        $InboundDiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'AccountServiceID'=>$AccountServiceID,'Type'=>AccountDiscountPlan::INBOUND,'ServiceID'=>$ServiceID,'AccountSubscriptionID'=>0))->pluck('DiscountPlanID');
        $PackageDiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'AccountServiceID'=>$AccountServiceID,'Type'=>AccountDiscountPlan::PACKAGE,'ServiceID'=>$ServiceID,'AccountSubscriptionID'=>0))->pluck('DiscountPlanID');

        $ServiceTitle = AccountService::where(['AccountID'=>$id,'AccountServiceID'=>$AccountServiceID])->pluck('ServiceTitle');
        $ServiceDescription = AccountService::where(['AccountID'=>$id,'AccountServiceID'=>$AccountServiceID])->pluck('ServiceDescription');
        $ServiceTitleShow = AccountService::where(['AccountID'=>$id,'AccountServiceID'=>$AccountServiceID])->pluck('ServiceTitleShow');
        $AccountService = AccountService::where(['AccountID'=>$id,'AccountServiceID'=>$AccountServiceID])->first();
        //ServiceOrderID = $AccountService->ServiceOrderID;

        //As per new question call the routing profile model for fetch the routing profile list.
        $routingprofile = RoutingProfiles::getRoutingProfile(getParentCompanyIdIfReseller($CompanyID));

        $RoutingProfileToCustomer	 	 =	RoutingProfileToCustomer::where('AccountID',$id)->where('AccountServiceID',$AccountServiceID)->first();
        //----------------------------------------------------------------------
        $ROUTING_PROFILE = CompanyConfiguration::get('ROUTING_PROFILE',$CompanyID);

        $AccountSubscriptionID = $id;

        $Packages = Package::getDropdownIDListByCompany($AccountCompanyId);
        $CurrencyID=Account::getCurrencyIDByAccount($id);
        $PackageType=RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE);
        $AffiliateAccount = Account::getAffiliateAccount();
        $AppiedTo=Account::getAccountTypeByAccountID($id);
        $RateTable=RateTable::getPackageTariffDropDownList($CompanyID,$PackageType,$AppiedTo);

        $PackageId="";
        $RateTableID="";
        $allservices = Service::where('Status', 1)->get();
        $countries = Country::getCountryDropdownIDList();
        $AccountServicePackage = AccountServicePackage::where(['AccountID' => $AccountID, 'AccountServiceID' => $AccountServiceID])->first();
        if(!empty($AccountServicePackage) && count($AccountServicePackage) > 0){
            $PackageId=$AccountServicePackage->PackageId;
            $RateTableID=$AccountServicePackage->RateTableID;
        }

        $ParentCompanyID    = getParentCompanyIdIfReseller($AccountCompanyId);
        $AccessType         = ServiceTemplate::getAccessTypeDD($ParentCompanyID);
        $City               = ServiceTemplate::getCityDD($ParentCompanyID);
        $Tariff             = ServiceTemplate::getTariffDD($ParentCompanyID);
        $Prefix             = ServiceTemplate::getPrefixDD($ParentCompanyID);

        $AccessType = array('' => "Select") + $AccessType;
        $Prefix = array('' => "Select") + $Prefix;
        $City = array('' => "Select") + $City;
        $Tariff = array('' => "Select") + $Tariff;

        return View::make('accountservices.edit', compact('AffiliateAccount','CompanyID','AccountID','ServiceID','ServiceName','account','decimal_places','products','taxes','rate_table', 'termination_rate_table',
            'AccessType','Prefix','City','Tariff','package_rate_table','countries','DiscountPlan','DiscountPlanVOICECALL','DiscountPlanDID','DiscountPlanPACKAGE','InboundTariffID','OutboundTariffID','invoice_count','BillingClass','timezones','AccountBilling','AccountNextBilling','DiscountPlanID','InboundDiscountPlanID', 'PackageDiscountPlanID','ServiceTitle','ServiceDescription','ServiceTitleShow','routingprofile','RoutingProfileToCustomer','ROUTING_PROFILE','AccountService','AccountServiceID','AccountServiceContract','AccountServiceCancelContract', 'AccountSubscriptionID','Packages','RateTable','PackageId','RateTableID','allservices'));

    }

    public function CreateNew($id = '')
    {
        $account = Account::find($id);
        $CompanyID = Account::getCompanyIDByAccountID($id);
        $AccountID = $id;
        $decimal_places = get_round_decimal_places($id);
        $products = Product::getProductDropdownList($CompanyID);
        $taxes = TaxRate::getTaxRateDropdownIDListForInvoice(0,getParentCompanyIdIfReseller($CompanyID));
        $rate_table = RateTable::getRateTableList(array('CurrencyID'=>$account->CurrencyId));
        $DiscountPlan = DiscountPlan::getDropdownIDList($CompanyID,(int)$account->CurrencyId);
        $allservices = Service::where('Status', 1)->get();
        $InboundTariffID = '';
        $OutboundTariffID = '';

        //Billing
        $invoice_count = Account::getInvoiceCount($id);
        $BillingClass = BillingClass::getDropdownIDList($CompanyID);
        $timezones = TimeZone::getTimeZoneDropdownList();


        //As per new question call the routing profile model for fetch the routing profile list.
        $routingprofile = RoutingProfiles::getRoutingProfile($CompanyID);

        //----------------------------------------------------------------------
        $ROUTING_PROFILE = CompanyConfiguration::get('ROUTING_PROFILE',$CompanyID);

        $AccountSubscriptionID = $id;

        $Packages = Package::getDropdownIDList();
        $CurrencyID=Account::getCurrencyIDByAccount($id);
        $PackageType=RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE);

        $AppiedTo=Account::getAccountTypeByAccountID($id);
        $RateTable=RateTable::getPackageTariffDropDownList($CompanyID,$PackageType,$AppiedTo);

        $PackageId="";
        $RateTableID="";


        return View::make('accountservices.create', compact('CompanyID','AccountID','account','decimal_places','products','taxes','rate_table','DiscountPlan','InboundTariffID','OutboundTariffID','invoice_count','BillingClass','timezones','AccountBilling','AccountNextBilling','DiscountPlanID','InboundDiscountPlanID','ServiceDescription','routingprofile','RoutingProfileToCustomer','ROUTING_PROFILE','AccountServiceCancelContract', 'AccountSubscriptionID','Packages','RateTable','PackageId','RateTableID','allservices'));
    }

    public function InsertService()
    {
        $serviceid = Input::get('serviceid');
        $companyid = Input::get('companyid');
        $accountid = Input::get('accountid');
        $serviceTitle = empty(Input::get('ServiceTitle')) ? '' : Input::get('ServiceTitle');
        $serviceDescription = empty(Input::get('ServiceDescription')) ? '' : Input::get('ServiceDescription');
        $serviceShowTitle = Input::get('ServiceTitleShow') == false ? 0 : 1;
        $add = new AccountService;
        $add->AccountID = $accountid;
        $add->ServiceID = $serviceid;
        $add->CompanyID = $companyid;
        $add->Status  = 1;
        $add->ServiceTitle = $serviceTitle;
        $add->ServiceDescription = $serviceDescription;
        $add->ServiceTitleShow = $serviceShowTitle;
        if($add->save()){
            $lastid = $add->AccountServiceID;
            return \Redirect::to('accountservices/'.$accountid.'/edit/'.$lastid);
        }


    }

    // add account services
    public function addservices($id){
        $data = Input::all();
        $services = $data['ServiceID'];
        $accountid = $data['AccountID'];
        $servicedata = array();
        if(!empty($services) && count($services)>0 && !empty($accountid)){
            $CompanyID = Account::getCompanyIDByAccountID($accountid);
            $message = '';
            foreach($services as $service){
                $servicedata['ServiceID'] = $service;
                $servicedata['AccountID'] = $data['AccountID'];
                $servicedata['CompanyID'] = $CompanyID;
                AccountService::insert($servicedata);
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
        //Log::info("Account ajax_datagrid " .  print_r($data,true));
        $id=$data['account_id'];
        $select = ["tblAccountService.AccountServiceID","tblService.ServiceName","tblAccountService.ServiceTitle","tblAccountService.Status","tblAccountService.ServiceID","tblAccountService.AccountServiceID"];
        $ServiceActive = 0;
        $ServiceNumber = 0;
        $ServicePackageId = 0;
        $AccountServiceOrderID = 0;
        $iDisplayLength = 0;
        $Affiliate = 0;
        if(!empty($data['ServiceActive']) && $data['ServiceActive'] == 'true'){
            $ServiceActive = 1;
        }
        if(!empty($data['Number'])){
            $ServiceNumber = $data['Number'];
        }

        if(!empty($data['PackageName'])){
            $ServicePackageId = $data['PackageName'];
        }
        if(!empty($data['Affiliate'])){
            $Affiliate = $data['Affiliate'];
        }

        if(!empty($data['AccountServiceOrderID'])){
            $AccountServiceOrderID = $data['AccountServiceOrderID'];
        }

        $iDisplayLength = $data['iDisplayLength'];
        $p_PageNumber = ceil($data['iDisplayStart'] / $data['iDisplayLength']);

        $query = "call prcGetAccountServiceData('" . $id . "','" . $ServiceNumber . "','" .
            $ServicePackageId . "'," . $AccountServiceOrderID . "," . $Affiliate . "," . $iDisplayLength . " ,"
            . $p_PageNumber . ",0" . ")";

        //$result = DB::select($query);
        //$services->select($select);
        Log::info("Account Service SQL " .  $query);

        return DataTableSql::of($query)->make();
    }

    // account service edit page data store and update
    public function update($AccountID,$AccountServiceID)
    {


        $data = Input::all();
        if ($AccountID > 0 && $AccountServiceID > 0 && !empty($data['ServiceID'])) {

            $date = date('Y-m-d H:i:s');
            $data['Billing'] = isset($data['Billing']) ? 1 : 0;
            $data['ServiceBilling'] = isset($data['ServiceBilling']) ? 1 : 0;
            $ServiceID = $data['ServiceID'];
            $CompanyID = Account::getCompanyIDByAccountID($AccountID);
            if (empty($data['ServiceTitleShow'])) {
                if (empty($data['ServiceDescription'])) {
                    return Response::json(array("status" => "failed", "message" => "Please fill Service Description."));
                }
            }

            $RoutingProfileID = '';
            if (isset($data['routingprofile'])) {
                $RoutingProfileID = $data['routingprofile'];
            }
            if ($RoutingProfileID != '') {
                $RoutingProfileToCustomer = RoutingProfileToCustomer::where(["AccountID" => $AccountID, "AccountServiceID" => $AccountServiceID])->first();

                if (!empty($RoutingProfileToCustomer) && count($RoutingProfileToCustomer) > 0) {
                    $routingprofile_table = array();
                    $routingprofile_table['RoutingProfileID'] = $RoutingProfileID;
                    $routingprofile_table['AccountID'] = $AccountID;
                    $routingprofile_table['ServiceID'] = $ServiceID;
                    $routingprofile_table['AccountServiceID'] = $AccountServiceID;
                    $routingprofile_table['updated_at'] = date('Y-m-d H:i:s');
                    RoutingProfileToCustomer::where(["AccountID" => $AccountID, "AccountServiceID" => $AccountServiceID])->update($routingprofile_table);
                } else {
                    $routingprofile_table = array();
                    $routingprofile_table['RoutingProfileID'] = $RoutingProfileID;
                    $routingprofile_table['AccountID'] = $AccountID;
                    $routingprofile_table['ServiceID'] = $ServiceID;
                    $routingprofile_table['AccountServiceID'] = $AccountServiceID;
                    $routingprofile_table['created_at'] = date('Y-m-d H:i:s');
                    $routingprofile_table['updated_at'] = date('Y-m-d H:i:s');
                    RoutingProfileToCustomer::insert($routingprofile_table);
                }
                unset($data['routingprofile']);
            }

            /**start contract Section*/
            $AccountServiceId = AccountService::where('AccountServiceID', $AccountServiceID)->first();
            $AccountServiceContract = AccountServiceContract::where('AccountServiceID', $AccountServiceId->AccountServiceID)->get();

            $Contract = array();

            $Contract['ContractStartDate'] = Input::get('StartDate');
            $Contract['ContractEndDate'] = Input::get('EndDate');
            $Contract['AccountServiceID'] = $AccountServiceId->AccountServiceID;
            $Contract['AutoRenewal'] = Input::has('AutoRenewal') ? 1 : 0;
            $Contract['ContractTerm'] = Input::get('ContractTerm');
            $Contract['Duration'] = Input::get('Duration');
            /**validation*/
            /*if($Contract['ContractStartDate'] != "" || $Contract['ContractEndDate'] != "" || $Contract['AutoRenewal'] != 0 || $Contract['Duration'] != "" || $Contract['ContractTerm'] != ""|| count($AccountServiceContract) > 0)
            {
                if ($Contract['ContractTerm'] == 3) {
                    AccountServiceContract::$rules['FixedFee'] = 'required|numeric';
                } else if ($Contract['ContractTerm'] == 4) {
                    AccountServiceContract::$rules['Percentage'] = 'required|numeric';
                } else if ($Contract['ContractTerm'] == 5) {
                    AccountServiceContract::$rules['FixedFeeContract'] = 'required|numeric';
                }
                AccountServiceContract::$rules['StartDate'] = 'required|date|date_format:Y-m-d';
                AccountServiceContract::$rules['EndDate'] = 'required|date|date_format:Y-m-d';
                AccountServiceContract::$rules['ContractTerm'] = 'required';

                $validator = \Validator::make(Input::all(), AccountServiceContract::$rules);
                $validator->setAttributeNames(['Percentage' => 'Percentage','FixedFee' => 'Fixed Fee','FixedFeeContract' => 'Fixed Fee','StartDate' => 'Contract Start Date','EndDate' => 'Contract End Date','ContractTerm' => 'Contract Term']);
                if ($validator->fails()) {
                    return Response::json(array("status" => "failed", "message" => $validator->errors()->all()));
                }
                //perform actions/
                if ($Contract['ContractTerm'] == 3) {
                    $Contract['ContractReason'] = Input::get('FixedFee');
                } else if ($Contract['ContractTerm'] == 4) {
                    $Contract['ContractReason'] = Input::get('Percentage');
                } else if ($Contract['ContractTerm'] == 5) {
                    $Contract['ContractReason'] = Input::get('FixedFeeContract');
                } else {
                    $Contract['ContractReason'] = NULL;
                }
                if (count($AccountServiceContract) > 0) {
                    AccountServiceContract::where('AccountServiceID', $AccountServiceId->AccountServiceID)->update($Contract);
                } else {
                    AccountServiceContract::create($Contract);
                }
            }*/
            /** end contract section */

            /**Service Billing Section*/
            if ($data['ServiceBilling'] == 1) {
                if (!empty($data['BillingStartDate']) || !empty($data['BillingCycleType']) || !empty($data['BillingCycleValue']) || !empty($data['BillingClassID'])) {
                    AccountService::$rules['BillingCycleType'] = 'required';
                    AccountService::$rules['BillingStartDate'] = 'required';
                    AccountService::$rules['BillingClassID'] = 'required';
                    if (isset($data['BillingCycleValue'])) {
                        AccountService::$rules['BillingCycleValue'] = 'required';
                    }
                }
            }

            /* Package Section Validation */
            if(!empty($data['PackageId']) || !empty($data['RateTableID'])){
                /*AccountService::$rules['PackageId'] = 'required';
                AccountService::$rules['RateTableID'] = 'required';*/

            }

            $validator = Validator::make($data, AccountService::$rules, AccountService::$messages);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            $OutboundDiscountPlan = empty($data['DiscountPlanID']) ? '' : $data['DiscountPlanID'];
            $InboundDiscountPlan = empty($data['InboundDiscountPlanID']) ? '' : $data['InboundDiscountPlanID'];
            $PackageDiscountPlan = empty($data['PackageDiscountPlanID']) ? '' : $data['PackageDiscountPlanID'];

            //billing
            //$invoice_count = Account::getInvoiceCount($AccountID);
            $invoice_count = 0;
            if ($invoice_count == 0 && $data['ServiceBilling'] == 1) {
                $data['LastInvoiceDate'] = $data['BillingStartDate'];
                $data['LastChargeDate'] = $data['BillingStartDate'];
                if ($data['BillingStartDate'] == $data['NextInvoiceDate']) {
                    $data['NextChargeDate'] = $data['BillingStartDate'];
                } else {
                    $data['NextChargeDate'] = date('Y-m-d', strtotime('-1 day', strtotime($data['NextInvoiceDate'])));;
                }
            }

            $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), 0);

            /** @TODO
             * Billing is off now when we change need to do as accountservice wise
             */
            if ($data['ServiceBilling'] == 1) {
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
            if (!empty($AccountPeriod)) {
                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                $AccountSubscriptionID = 0;
                $AccountName = '';
                $AccountCLI = '';
                $SubscriptionDiscountPlanID = 0;
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $ServiceID, $AccountServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $ServiceID, $AccountServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $PackageDiscountPlan, AccountDiscountPlan::PACKAGE, $billdays, $DayDiff, $ServiceID, $AccountServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
            }


            //AccountTariff
            $InboundTariff = empty($data['InboundTariffID']) ? '' : $data['InboundTariffID'];
            $OutboundTariff = empty($data['OutboundTariffID']) ? '' : $data['OutboundTariffID'];

            $inbounddata = array();
            $inbounddata['CompanyID'] = $CompanyID;
            $inbounddata['AccountID'] = $AccountID;
            $inbounddata['ServiceID'] = $ServiceID;
            $inbounddata['AccountServiceID'] = $AccountServiceID;
            $inbounddata['RateTableID'] = $InboundTariff;
            $inbounddata['Type'] = AccountTariff::INBOUND;

            $outbounddata = array();
            $outbounddata['CompanyID'] = $CompanyID;
            $outbounddata['AccountID'] = $AccountID;
            $outbounddata['ServiceID'] = $ServiceID;
            $outbounddata['AccountServiceID'] = $AccountServiceID;
            $outbounddata['RateTableID'] = $OutboundTariff;
            $outbounddata['Type'] = AccountTariff::OUTBOUND;

            if (!empty($InboundTariff)) {
                $count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'AccountServiceID' => $AccountServiceID, 'Type' => AccountTariff::INBOUND))->count();
                if (!empty($count) && $count > 0) {
                    AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'AccountServiceID' => $AccountServiceID, 'Type' => AccountTariff::INBOUND))
                        ->update(array('RateTableID' => $InboundTariff, 'updated_at' => $date));
                } else {
                    $inbounddata['created_at'] = $date;
                    AccountTariff::create($inbounddata);
                }
            } else {
                $count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'AccountServiceID' => $AccountServiceID, 'Type' => AccountTariff::INBOUND))->count();
                if (!empty($count) && $count > 0) {
                    AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'AccountServiceID' => $AccountServiceID, 'Type' => AccountTariff::INBOUND))->delete();
                }
            }

            if (!empty($OutboundTariff)) {
                $count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'AccountServiceID' => $AccountServiceID, 'Type' => AccountTariff::OUTBOUND))->count();
                if (!empty($count) && $count > 0) {
                    AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'AccountServiceID' => $AccountServiceID, 'Type' => AccountTariff::OUTBOUND))
                        ->update(array('RateTableID' => $OutboundTariff, 'updated_at' => $date));
                } else {
                    $outbounddata['created_at'] = $date;
                    AccountTariff::create($outbounddata);
                }
            } else {
                $count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'AccountServiceID' => $AccountServiceID, 'Type' => AccountTariff::OUTBOUND))->count();
                if (!empty($count) && $count > 0) {
                    AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $AccountID, 'ServiceID' => $ServiceID, 'AccountServiceID' => $AccountServiceID, 'Type' => AccountTariff::OUTBOUND))->delete();
                }
            }


            //Package Section
            if(!empty($data['PackageId']) && !empty($data['RateTableID'])) {
                $AccountServicePackage = AccountServicePackage::where(['AccountID' => $AccountID, 'AccountServiceID' => $AccountServiceID]);
                if ($AccountServicePackage->count() > 0) {
                    //Update
                    $AccountServicePackage->update(['PackageId' => $data['PackageId'], 'RateTableID' => $data['RateTableID']]);

                } else {
                    //Create
                    $packagedata = array();
                    $packagedata['AccountID'] = $AccountID;
                    $packagedata['AccountServiceID'] = $AccountServiceID;
                    $packagedata['CompanyID'] = $CompanyID;
                    $packagedata['PackageId'] = $data['PackageId'];
                    $packagedata['RateTableID'] = $data['RateTableID'];
                    $packagedata['created_at'] = date('Y-m-d H:i:s');

                    AccountServicePackage::create($packagedata);

                }
            }

            $accdata = array();
            $accdata['ServiceOrderID'] = empty($data['ServiceOrderID']) ? '' : $data['ServiceOrderID'];
            $accdata['ServiceTitle'] = empty($data['ServiceTitle']) ? '' : $data['ServiceTitle'];
            $accdata['ServiceDescription'] = empty($data['ServiceDescription']) ? '' : $data['ServiceDescription'];
            $accdata['ServiceTitleShow'] = isset($data['ServiceTitleShow']) ? 1 : 0;
            $accdata['SubscriptionBillingCycleType'] = empty($data['SubscriptionBillingCycleType']) ? '' : $data['SubscriptionBillingCycleType'];
            $accdata['SubscriptionBillingCycleValue'] = empty($data['SubscriptionBillingCycleValue']) ? '' : $data['SubscriptionBillingCycleValue'];
            $accdata['AffiliateAccount'] = empty($data['AffiliateAccount']) ? null : $data['AffiliateAccount'];

            AccountService::where(['AccountID' => $AccountID, 'AccountServiceID' => $AccountServiceID])->update($accdata);

            return Response::json(array("status" => "success", "message" => "Account Service Successfully updated."));
        }
        return Response::json(array("status" => "failed", "message" => "Problem Creating Account Service."));
    }

    public function changestatus($AccountServiceID,$Status){
        $data = Input::all();
        $AccountID = $data['accountid'];

        if ($AccountServiceID && $Status) {
            $Action = AccountService::where(array('AccountID'=>$AccountID,'AccountServiceID'=>$AccountServiceID))->first();
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

    public function delete($AccountID,$AccountServiceID)
    {
        if( intval($AccountID) > 0 && intval($AccountServiceID) > 0){

            /*if(AccountService::checkForeignKeyById($AccountID,$AccountServiceID)){*/
            try{
                $result = AccountService::where(array('AccountID'=>$AccountID,'AccountServiceID'=>$AccountServiceID))->delete();
                if ($result) {
                    AccountServiceContract::where('AccountServiceID', $AccountServiceID)->delete();
                    return Response::json(array("status" => "success", "message" => "Service Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Service."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }
            /*}else{
                return Response::json(array("status" => "failed", "message" => "Service is in Use, You can not delete this Service."));
            }*/
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
        $data['RoutingProfile'] = empty($data['RoutingProfile']) ? '' : $data['RoutingProfile'];
        $data['Contract'] = empty($data['Contract']) ? '' : $data['Contract'];

        /**New logic CloneID is AccountServicID**/
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
                    $CloneIDs.= $result['AccountServiceID'].',';
                }
                $CloneIDs = rtrim($CloneIDs,',');


            }
            if(!empty($CloneIDs)){
                $CloneIDs=explode(',',$CloneIDs);
                $data['SourceAccountID'] = $AccountID;
                $data['AccountIDs'] = $AccountIDs;
                $data['AccountServiceIDs'] = $CloneIDs;

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
        $AccountServiceIds = array();
        $save = array();
        if(!empty($data['action']) && !empty($AccountID)){
            if(!empty($data['Criteria'])){
                $criteria = $data['Criteria'];
                $criteria = json_decode($criteria,true);
                $AccountServiceIds = AccountService::getServiceIDsByCriteria($AccountID,$criteria);
            }
            /**ServiceID is AccountServiceID */
            if($data['ServiceID']){
                $AccountServiceIds = $data['ServiceID'];
                $AccountServiceIds=explode(',',$AccountServiceIds);
            }

            if ($data['action'] == 'active') {
                $save['Status'] = 1;
            } else if ($data['action'] == 'deactive') {
                $save['Status'] = 0;
            }
            if(AccountService::whereIn('AccountServiceID',$AccountServiceIds)->where(array('AccountID'=>$AccountID))->update($save)){
                return Response::json(array("status" => "success", "message" => "Service Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Service."));
            }

        }

        return Response::json(array("status" => "failed", "message" => "Problem Updating Service."));
    }

    public function bulk_delete($AccountID){
        $data = Input::all();
        $AccountServiceIds = array();
        $save = array();
        $errormsg = '';
        if(!empty($data['action']) && !empty($AccountID)){
            if(!empty($data['Criteria'])){
                $criteria = $data['Criteria'];
                $criteria = json_decode($criteria,true);
                $AccountServiceIds = AccountService::getServiceIDsByCriteria($AccountID,$criteria);
            }
            /**ServiceID is AccountServiceID */
            if($data['ServiceID']){
                $AccountServiceIds = $data['ServiceID'];
                $AccountServiceIds=explode(',',$AccountServiceIds);
            }
            $error = '';
            try {
                foreach ($AccountServiceIds as $Service => $key) {
                    /*if (AccountService::checkForeignKeyById($AccountID, $key)) {*/
                    AccountService::where(array('AccountID' => $AccountID, 'AccountServiceID' => $key))->delete();
                    /*} else {
                        $ServiceName = Service::getServiceNameByID($key);
                        $error .= '<br>' . $ServiceName;

                    }*/
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
        //Log::info("Account ajax_datagrid " .  print_r($data,true));
        $id=$data['account_id'];
        $select = ["tblAccountService.AccountServiceID","tblService.ServiceName","tblAccountService.ServiceTitle","tblAccountService.Status","tblAccountService.ServiceID","tblAccountService.AccountServiceID"];
        $ServiceActive = 0;
        $ServiceNumber = 0;
        $ServicePackageId = 0;
        $AccountServiceOrderID = 0;
        $iDisplayLength = 0;
        if(!empty($data['ServiceActive']) && $data['ServiceActive'] == 'true'){
            $ServiceActive = 1;
        }
        if(!empty($data['Number'])){
            $ServiceNumber = $data['Number'];
        }

        if(!empty($data['PackageName'])){
            $ServicePackageId = $data['PackageName'];
        }

        if(!empty($data['AccountServiceOrderID'])){
            $AccountServiceOrderID = $data['AccountServiceOrderID'];
        }

        $iDisplayLength = $data['iDisplayLength'];
        $p_PageNumber = (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) == 0 ? 1 : (ceil($data['iDisplayStart'] / $data['iDisplayLength']));

        $query = "call prcGetAccountServiceData('" . $id . "','" . $ServiceNumber . "','" . $ServicePackageId . "'," . $AccountServiceOrderID . "," . $iDisplayLength . " ," . $p_PageNumber . ",1" . ")";

        $result = DB::select($query);
        //$services->select($select);
        Log::info("Account Service SQL " .  $query);




        //$services->select($select);
        Log::info("Account Service SQL " .  $query);
        // $services->select($select);




        $servicedata = json_decode(json_encode($result),true);
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
    public function cancelContract(){

        $data = Input::all();
        $Contract = array();

        /** data get from inputs */
        $Contract['AccountServiceID'] = $data['AccountServiceID'];
        $Contract['CancelationDate'] = $data['CancelDate'];
        $Contract['UserName'] = User::get_user_full_name();



        /** set the values of variables*/
        if(isset($data['IncTerminationFees'])){
            $Contract['IncludeTerminationFees'] = $data['IncTerminationFees'];
        }else{
            $Contract['IncludeTerminationFees'] = 0;
        }

        if(isset($data['DiscountOffered'])){
            $Contract['IncludeDiscountsOffered'] = $data['DiscountOffered'];
        }else{
            $Contract['IncludeDiscountsOffered'] = 0;
        }

        if(isset($data['GenerateInvoice'])){
            $Contract['GenerateInvoice'] = $data['GenerateInvoice'];
        }else{
            $Contract['GenerateInvoice'] = 0;
        }

        /** update or create with validation */
        $validator = \Validator::make($data, [
            'CancelDate' => 'required|date|date_format:Y-m-d'
        ]);
        $validator->setAttributeNames(['CancelDate' => 'Cancellation Date']);
        if ($validator->fails())
        {
            return Response::json(array("status" => "failed", "message" => $validator->errors()->all()));
        }

        $AccountServiceCancelContract = AccountServiceCancelContract::where('AccountServiceID', $data['AccountServiceID'])->first();
        if (count($AccountServiceCancelContract) > 0) {
            AccountServiceCancelContract::where('AccountServiceID', $data['AccountServiceID'])->update($Contract);
        } else {
            AccountServiceCancelContract::create($Contract);
        }
        return Response::json(array("status" => "success", "message" => "Cancel Contract Successful!."));
    }
    public function contract_status($AccountServiceID){
        //return $AccountServiceID;
        $AccountService = AccountService::where('AccountServiceID', $AccountServiceID)->first();
        $CancelContractStatus = array();
        $CancelContractStatus['CancelContractStatus'] = 0;
        $InsertRenewalHistory = [
            'Date' => date('Y-m-d H:i:s'),
            'Action' => 'Contract Renew',
            'ActionBy' => User::get_user_full_name(),
            'AccountServiceID' => $AccountServiceID

        ];

        AccountService::where('AccountServiceID', $AccountServiceID)->update($CancelContractStatus);
        AccountServiceHistory::insert($InsertRenewalHistory);


        return Response::json(array("status" => "success", "message" => "Contract Is Renewed!"));

    }

    public function contract_history($ServiceID){
        $AccountServiceHistory = AccountServiceHistory::select('Date','Action','ActionBy')->where('AccountServiceID',$ServiceID)->orderBy('Date','desc');

        return Datatables::of($AccountServiceHistory)->make();
    }

}