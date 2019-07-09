<?php

class RateTablesController extends \BaseController {

    public function ajax_datagrid() {
        $CompanyID = User::get_companyID();
        $rate_tables = RateTable::
        Join('tblCurrency','tblCurrency.CurrencyId','=','tblRateTable.CurrencyId')
            ->join('tblCodeDeck','tblCodeDeck.CodeDeckId','=','tblRateTable.CodeDeckId')
            ->leftjoin('tblTrunk','tblTrunk.TrunkID','=','tblRateTable.TrunkID')
            ->leftjoin('tblDIDCategory','tblDIDCategory.DIDCategoryID','=','tblRateTable.DIDCategoryID')
            ->leftjoin('tblCustomerTrunk','tblCustomerTrunk.CustomerTrunkID','=',DB::RAW('(SELECT CustomerTrunkID FROM tblCustomerTrunk WHERE RateTableID = tblRateTable.RateTableId LIMIT 1)'))
            ->leftjoin('tblVendorConnection','tblVendorConnection.VendorConnectionID','=',DB::RAW('(SELECT VendorConnectionID FROM tblVendorConnection WHERE RateTableID = tblRateTable.RateTableId LIMIT 1)'))
            ->leftjoin('tblReseller','tblReseller.ResellerID','=','tblRateTable.Reseller')
            ->select([DB::RAW('IF(tblRateTable.Reseller=0,"",IF(tblRateTable.Reseller=-1,"All",tblReseller.ResellerName)) AS ResellerName'),'tblRateTable.Type','tblRateTable.AppliedTo','tblRateTable.RateTableName','tblCurrency.Code', 'tblTrunk.Trunk as trunkName', 'tblDIDCategory.CategoryName as CategoryName','tblCodeDeck.CodeDeckName','tblRateTable.updated_at','tblRateTable.RateTableId', 'tblRateTable.TrunkID', 'tblRateTable.CurrencyID', 'tblRateTable.RoundChargedAmount', 'tblRateTable.MinimumCallCharge', 'tblRateTable.DIDCategoryID', 'tblCustomerTrunk.CustomerTrunkID', 'tblVendorConnection.VendorConnectionID','tblRateTable.Reseller']);
        //$rate_tables = RateTable::join('tblCurrency', 'tblCurrency.CurrencyId', '=', 'tblRateTable.CurrencyId')->where(["tblRateTable.CompanyId" => $CompanyID])->select(["tblRateTable.RateTableName","Code","tblRateTable.updated_at", "tblRateTable.RateTableId"]);

        $data = Input::all();

        if(!empty($data['ResellerPage'])) {
            $ResellerID = Reseller::getResellerID();
            $rate_tables->where("tblRateTable.CompanyId",1);
            $rate_tables->where("tblRateTable.Reseller",-1)->orWhere("tblRateTable.Reseller",$ResellerID);
        } else {
            $rate_tables->where("tblRateTable.CompanyId",$CompanyID);
            if(isset($data['Reseller']) && $data['Reseller'] != 0 && !empty($data['AppliedTo']) && $data['AppliedTo']==RateTable::APPLIED_TO_RESELLER){
                $rate_tables->where('tblRateTable.Reseller',$data['Reseller']);
            }
        }

        if($data['TrunkID']){
            $rate_tables->where('tblRateTable.TrunkID',$data['TrunkID']);
        }
        if(!empty($data['Type'])){
            $rate_tables->where('tblRateTable.Type',$data['Type']);
        }
        if(!empty($data['DIDCategoryID'])){
            $rate_tables->where('tblRateTable.DIDCategoryID',$data['DIDCategoryID']);
        }
        if(!empty($data['AppliedTo'])){
            $rate_tables->where('tblRateTable.AppliedTo',$data['AppliedTo']);
        }
		if($data['Search']!=''){
            $rate_tables->WhereRaw('tblRateTable.RateTableName like "%'.$data['Search'].'%"'); 
        }

        return Datatables::of($rate_tables)->make();
    }

    public function search_ajax_datagrid($id) {
        $companyID = User::get_companyID();

        $data = Input::all();

        $ApprovedStatus = $data['ApprovedStatus'];

        $data['iDisplayStart'] +=1;
        $data['Country']                = !empty($data['Country']) && $data['Country'] != 'All' ? $data['Country'] : 'NULL';
        $data['Code']                   = $data['Code'] != '' ? "'".$data['Code']."'" : 'NULL';
        $data['Description']            = !empty($data['Description']) ? "'".$data['Description']."'" : 'NULL';
        $data['OriginationCode']        = !empty($data['OriginationCode']) ? "'".$data['OriginationCode']."'" : 'NULL';
        $data['OriginationDescription'] = !empty($data['OriginationDescription']) ? "'".$data['OriginationDescription']."'" : 'NULL';
        $data['RoutingCategoryID']      = !empty($data['RoutingCategoryID']) ? "'".$data['RoutingCategoryID']."'" : 'NULL';
        $data['Preference']             = !empty($data['Preference']) ? "'".$data['Preference']."'" : 'NULL';
        $data['Blocked']                = isset($data['Blocked']) && $data['Blocked'] != '' ? "'".$data['Blocked']."'" : 'NULL';
        $data['ApprovedStatus']         = isset($data['ApprovedStatus']) && $data['ApprovedStatus'] != '' ? "'".$data['ApprovedStatus']."'" : 'NULL';
        $data['Timezones']              = isset($data['Timezones']) && $data['Timezones'] != '' ? "'".$data['Timezones']."'" : 'NULL';
        $data['City']                   = !empty($data['City']) ? "'".$data['City']."'" : 'NULL';
        $data['Tariff']                 = !empty($data['Tariff']) ? "'".$data['Tariff']."'" : 'NULL';
        $data['AccessType']             = !empty($data['AccessType']) ? "'".$data['AccessType']."'" : 'NULL';

        if(!empty($data['ResellerPage'])) {
            $companyID = 1;
        }

        $view = isset($data['view']) && $data['view'] == 2 ? $data['view'] : 1;
        $TypeVoiceCall  = RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
        $TypeDID        = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);

        $columns_voicecall  = array('RateTableRateID', 'DestinationType', 'TimezoneTitle', 'OriginationCode', 'OriginationDescription', 'Code', 'Description', 'MinimumDuration', 'Interval1', 'IntervalN', 'ConnectionFee', 'PreviousRate', 'Rate', 'RateN', 'EffectiveDate', 'EndDate', 'ModifiedBy', 'ApprovedBy', 'RoutingCategoryName', 'Preference');
        $columns_did        = array('RateTableRateID', 'AccessType', 'Country', 'OriginationCode', 'Code', 'City', 'Tariff', 'TimezoneTitle', 'OneOffCost', 'MonthlyCost', 'CostPerCall', 'CostPerMinute', 'SurchargePerCall', 'SurchargePerMinute', 'OutpaymentPerCall', 'OutpaymentPerMinute', 'Surcharges', 'Chargeback', 'CollectionCostAmount', 'CollectionCostPercentage', 'RegistrationCostPerNumber', 'EffectiveDate', 'EndDate', 'ModifiedBy', 'ApprovedBy');
        $columns_pkg        = array('RateTableRateID', 'TimezoneTitle', 'Code', 'OneOffCost', 'MonthlyCost', 'PackageCostPerMinute', 'RecordingCostPerMinute', 'EffectiveDate', 'EndDate', 'ModifiedBy', 'ApprovedBy');

        $sort_column_voicecall  = @$columns_voicecall[$data['iSortCol_0']];
        $sort_column_did        = @$columns_did[$data['iSortCol_0']];
        $sort_column_pkg        = @$columns_pkg[$data['iSortCol_0']];

        $rateTable = RateTable::find($id);

        if($ApprovedStatus == RateTable::RATE_STATUS_APPROVED) { //approved rates
            if($rateTable->Type == $TypeVoiceCall) { // voice call
                if(!empty($data['DiscontinuedRates'])) {
                    $query = "call prc_getDiscontinuedRateTableRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Country'] . ",".$data['OriginationCode'].",".$data['OriginationDescription']."," . $data['Code'] . "," . $data['Description'] . ",".$data['RoutingCategoryID'].",".$data['Preference'].",".$data['Blocked'].",".$data['ApprovedStatus'].",".$view."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column_voicecall . "','" . $data['sSortDir_0'] . "',0)";
                } else {
                    $query = "call prc_GetRateTableRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['RoutingCategoryID'].",".$data['Preference'].",".$data['Blocked'].",".$data['ApprovedStatus'].",".$view.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column_voicecall."','".$data['sSortDir_0']."',0)";
                }
            } else if($rateTable->Type == $TypeDID) { // did
                if(!empty($data['DiscontinuedRates'])) {
                    $query = "call prc_getDiscontinuedRateTableDIDRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Country'] . ",".$data['OriginationCode']."," . $data['Code'] . "," . $data['City'] . "," . $data['Tariff'] . "," . $data['AccessType'] . ",".$data['ApprovedStatus']."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column_did . "','" . $data['sSortDir_0'] . "',0)";
                } else {
                    $query = "call prc_GetRateTableDIDRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['Code'].",".$data['City'].",".$data['Tariff'].",".$data['AccessType'].",'".$data['Effective']."',".$data['ApprovedStatus'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column_did."','".$data['sSortDir_0']."',0)";
                }
            } else { // package
                if(!empty($data['DiscontinuedRates'])) {
                    $query = "call prc_getDiscontinuedRateTablePKGRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Code'] . ",".$data['ApprovedStatus']."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column_pkg . "','" . $data['sSortDir_0'] . "',0)";
                } else {
                    $query = "call prc_GetRateTablePKGRate (".$companyID.",".$id.",".$data['Timezones'].",".$data['Code'].",'".$data['Effective']."',".$data['ApprovedStatus'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column_pkg."','".$data['sSortDir_0']."',0)";
                }
            }
        } else { //awaiting approval/rejected
            if($rateTable->Type == $TypeVoiceCall) { // voice call
                $query = "call prc_GetRateTableRateAA (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['ApprovedStatus'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column_voicecall."','".$data['sSortDir_0']."',0)";
            } else if($rateTable->Type == $TypeDID) { // did
                $query = "call prc_GetRateTableDIDRateAA (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['Code'].",".$data['City'].",".$data['Tariff'].",".$data['AccessType'].",'".$data['Effective']."',".$data['ApprovedStatus'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column_did."','".$data['sSortDir_0']."',0)";
            } else { // package
                $query = "call prc_GetRateTablePKGRateAA (".$companyID.",".$id.",".$data['Timezones'].",".$data['Code'].",'".$data['Effective']."',".$data['ApprovedStatus'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column_pkg."','".$data['sSortDir_0']."',0)";
            }
        }

        //Log::info($query);

        return DataTableSql::of($query)->make();
    }

    /*
     * Datagrid for Edit Mode
     */

    public function edit_ajax_datagrid($id) {
//        $CompanyID = User::get_companyID();
//        $rate_table_rates = RateTableRate::join('tblRate', 'tblRateTableRate.RateID', '=', 'tblRate.RateID')
//                        ->where(["tblRateTableRate.RateTableId" => $id])->select([
//            "tblRateTableRate.RateTableRateID as ID",
//            "tblRate.Code",
//            "tblRateTableRate.Rate",
//            "tblRate.Description",
//            "tblRateTableRate.EffectiveDate",
//            "tblRateTableRate.updated_at",
//            "tblRateTableRate.ModifiedBy",
//            "tblRateTableRate.RateTableRateID"]);
//
//        return Datatables::of($rate_table_rates)->make();
    }

    /**
     * Display a listing of the resource.
     * GET /ratetables
     *
     * @return Response
     */
    public function index() {
        $companyID = User::get_companyID();
        $trunks = Trunk::getTrunkDropdownIDList();
        $trunk_keys = getDefaultTrunk($trunks);
        $RateGenerators = RateGenerator::where(["Status" => 1, "CompanyID" => $companyID])->lists("RateGeneratorName", "RateGeneratorId");
        $codedecks = BaseCodeDeck::lists("CodeDeckName", "CodeDeckId");
        $codedecks = array(""=>"Select Codedeck")+$codedecks;
        $RateGenerators = array(""=>"Select rate generator")+$RateGenerators;
        $currencylist = Currency::getCurrencyDropdownIDList();
        $DIDCategory = DIDCategory::getCategoryDropdownIDList($companyID);
        $RateTypes   = RateType::getRateTypeDropDownList();
        $ResellerDD  = RateTable::getResellerDropdownIDList();
        $CompanyCurrency = Company::getCompanyField($companyID,'CurrencyId');

        $Page = Route::getCurrentRoute()->getPath();
        $ResellerPage = 0;
        if($Page == 'rate_tables/commercial') {
            $ResellerPage = 1;
        }

        return View::make('ratetables.index', compact('trunks','RateGenerators','codedecks','trunk_keys','currencylist','DIDCategory','RateTypes','ResellerDD','ResellerPage','CompanyCurrency'));
    }


    /**
     * Store a newly created resource in storage.
     * POST /ratetables
     *
     * @return Response
     */
    public function store() {

        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['CreatedBy'] = User::get_user_full_name();
        $data['RateTableName'] = trim($data['RateTableName']);

        /*$data['RateGeneratorId'] = isset($data['RateGeneratorId'])?$data['RateGeneratorId']:0;
        if($data['RateGeneratorId'] > 0) {
            $rateGenerator = RateGenerator::where(["RateGeneratorId" => $data['RateGeneratorId']])->get();
            $data['TrunkID'] = $rateGenerator[0]->TrunkID;
            $data['CodeDeckId'] = $rateGenerator[0]->CodeDeckId;
        }
            else if(empty($data['TrunkID'])){
            $data['TrunkID'] = Trunk::where(["CompanyID" => $companyID ])->min('TrunkID');
        }*/
        $rules = array(
            'CompanyID' => 'required',
            'RateTableName' => 'required|unique:tblRateTable,RateTableName,NULL,CompanyID,CompanyID,'.$data['CompanyID'],
            //'RateGeneratorId'=>'required',
            'CodedeckId'=>'required',
            //'TrunkID'=>'required',
            'CurrencyID'=>'required',
            'Type'=>'required',
            'AppliedTo'=>'required',
        );
        if(isset($data['AppliedTo']) && $data['AppliedTo'] == RateTable::APPLIED_TO_RESELLER){
            $rules['Reseller'] = 'required';
        }
        $message = ['CurrencyID.required'=>'Currency field is required',
                    //'TrunkID.required'=>'Trunk field is required',
                    'CodedeckId.required'=>'Codedeck field is required',
                    'Reseller.required' => 'The partner field is required'
                    //'RateGeneratorId.required'=>'RateGenerator'
                    ];

        $validator = Validator::make($data, $rules, $message);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if (RateTable::insert($data)) {
            return Response::json(array("status" => "success", "message" => "RateTable Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating RateTable."));
        }

    }

    /**
     * Show the form for editing the specified resource.
     * GET /ratetables/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function view($id) {
        $rateTable = RateTable::find($id);
        $trunkID = RateTable::where(["RateTableId" => $id])->pluck('TrunkID');
        $countries = Country::getCountryDropdownIDList();
        $CodeDeckId = RateTable::getCodeDeckId($id);
        $CompanyID = User::get_companyID();
        $codes = CodeDeck::getCodeDropdownList($CodeDeckId,$CompanyID);
        $isBandTable = RateTable::checkRateTableBand($id);
        $code = RateTable::getCurrencyCode($id);
        $Timezones = Timezones::getTimezonesIDList();
        $Timezones = array('' => "All") + $Timezones;
        $Timezone = Timezones::getTimezonesIDList();
        $RoutingCategories = RoutingCategory::getCategoryDropdownIDList($CompanyID);
        $RateApprovalProcess = CompanySetting::getKeyVal('RateApprovalProcess');
        $TypeVoiceCall = RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
        $TypeDID = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);
        $ROUTING_PROFILE = CompanyConfiguration::get('ROUTING_PROFILE', $CompanyID);
        $CurrencyDropDown = Currency::getCurrencyDropdownIDList();
        $AccessType         = ServiceTemplate::getAccessTypeDD($CompanyID);
        $City               = ServiceTemplate::getCityDD($CompanyID);
        $Tariff             = ServiceTemplate::getTariffDD($CompanyID);

        $Page = Route::getCurrentRoute()->getPath();
        $ResellerPage = 0;
        if($Page == 'rate_tables/{id}/view/commercial') {
            $ResellerPage = 1;
        }

        if($rateTable->Type == $TypeVoiceCall) {
            return View::make('ratetables.edit', compact('id', 'countries','trunkID','codes','isBandTable','code','rateTable','Timezones','RoutingCategories','RateApprovalProcess','TypeVoiceCall','ROUTING_PROFILE','CurrencyDropDown','Timezone','ResellerPage'));
        } else if($rateTable->Type == $TypeDID) {
            return View::make('ratetables.edit_did', compact('id', 'countries','trunkID','codes','isBandTable','code','rateTable','Timezones','RateApprovalProcess','TypeVoiceCall','CurrencyDropDown','Timezone','AccessType','City','Tariff','ResellerPage'));
        } else {
            return View::make('ratetables.edit_pkg', compact('id', 'countries','trunkID','codes','isBandTable','code','rateTable','Timezones','RateApprovalProcess','TypeVoiceCall','CurrencyDropDown','Timezone','ResellerPage'));
        }
    }



    public function delete($id) {
        if ($id > 0) {
            $cronjob            = 'Cronjob';
            $customer_trunk     = 'Customer Trunk';
            $customer_service   = 'Customer Service';
            $package            = 'Package';
            $service_package    = 'Customer Service Package';
            $customer_cli       = 'Customer CLI';
            $vendor_connection  = 'Vendor Connection';
            $service_template   = 'Service Template';
            $is_id_assigned_customer_trunk   = RateTable::join('tblCustomerTrunk', 'tblCustomerTrunk.RateTableId', '=', 'tblRateTable.RateTableId')
                            ->where("tblRateTable.RateTableId", $id)->count();
            $is_id_assigned_customer_service = RateTable::join('tblAccountTariff', 'tblAccountTariff.RateTableID', '=', 'tblRateTable.RateTableId')
                            ->where("tblRateTable.RateTableId", $id)->count();
            $is_id_assigned_customer_cli     = RateTable::join('tblCLIRateTable', 'tblCLIRateTable.RateTableID', '=', 'tblRateTable.RateTableId')
                            ->where("tblRateTable.RateTableId", $id)->count();
            $is_id_assigned_vendor           = RateTable::join('tblVendorConnection', 'tblVendorConnection.RateTableID', '=', 'tblRateTable.RateTableId')
                            ->where("tblRateTable.RateTableId", $id)->count();
            $is_id_assigned_service_template = RateTable::join('tblServiceTemplate', 'tblServiceTemplate.OutboundRateTableId', '=', 'tblRateTable.RateTableId')
                            ->where("tblRateTable.RateTableId", $id)->count();
            $is_id_assigned_package = RateTable::join('tblPackage', 'tblPackage.RateTableId', '=', 'tblRateTable.RateTableId')
                            ->where("tblRateTable.RateTableId", $id)->count();
            $is_id_assigned_service_package = RateTable::join('tblAccountServicePackage', 'tblAccountServicePackage.RateTableID', '=', 'tblRateTable.RateTableId')
                            ->where("tblRateTable.RateTableId", $id)->count();

            //Is RateTable is not being used anywhere then and then only delete
            if ($is_id_assigned_customer_trunk == 0 && $is_id_assigned_customer_service == 0 && $is_id_assigned_customer_cli == 0 && $is_id_assigned_vendor == 0 && $is_id_assigned_service_template == 0 && $is_id_assigned_package == 0 && $is_id_assigned_service_package == 0) {
                if(RateTable::checkRateTableInCronjob($id)){

                    $RateTable      = RateTable::find($id);
                    $TypeDID        = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);
                    $TypePKG        = RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE);

                    $RATE_MODEL = 'RateTableRate';
                    if($RateTable->Type == $TypePKG) {
                        $RATE_MODEL = 'RateTablePKGRate';
                    } else if($RateTable->Type == $TypeDID) {
                        $RATE_MODEL = 'RateTableDIDRate';
                    } else {
                        $RATE_MODEL = 'RateTableRate';
                    }

                    if($RATE_MODEL::where(["RateTableId" => $id])->count()>0){
                        if ($RATE_MODEL::where(["RateTableId" => $id])->delete() && RateTable::where(["RateTableId" => $id])->delete()) {
                            return Response::json(array("status" => "success", "message" => "RateTable Successfully Deleted"));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Deleting RateTable."));
                        }
                    }else{
                        if (RateTable::where(["RateTableId" => $id])->delete()) {
                            return Response::json(array("status" => "success", "message" => "RateTable Successfully Deleted"));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Deleting RateTable."));
                        }
                    }

                }else{
                    return Response::json(array("status" => "failed", "message" => "RateTable can not be deleted, Its assigned to CronJob."));
                }

            } else {
                $error = '';
                $error .= RateTable::checkRateTableInCronjob($id) == false ? $cronjob.',' : '';
                $error .= $is_id_assigned_customer_trunk > 0 ? $customer_trunk.',' : '';
                $error .= $is_id_assigned_customer_service > 0 ? $customer_service.',' : '';
                $error .= $is_id_assigned_package > 0 ? $package.',' : '';
                $error .= $is_id_assigned_service_package > 0 ? $service_package.',' : '';
                $error .= $is_id_assigned_customer_cli > 0 ? $customer_cli.',' : '';
                $error .= $is_id_assigned_vendor > 0 ? $vendor_connection.',' : '';
                $error .= $is_id_assigned_service_template > 0 ? $service_template.',' : '';

                $response = 'RateTable can not be deleted, Its assigned to '.trim($error,',');

                return Response::json(array("status" => "failed", "message" => $response));
            }
        }
    }

    //delete rate table rates
    public function clear_rate($id) {
        if ($id > 0) {
            $data           = Input::all();//echo "<pre>";print_r($data);exit();

            $username       = User::get_user_full_name();
            $EffectiveDate  = $EndDate = $Rate = $RateN = $MinimumDuration = $Interval1 = $IntervalN = $ConnectionFee = $OriginationRateID = $RoutingCategoryID = $Preference = $Blocked = $RateCurrency = $ConnectionFeeCurrency = 'NULL';


            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 2; //delete action
                $criteria   = json_decode($data['criteria'], true);


                $criteria['OriginationCode']        = !empty($criteria['OriginationCode']) && $criteria['OriginationCode'] != '' ? "'" . $criteria['OriginationCode'] . "'" : 'NULL';
                $criteria['OriginationDescription'] = !empty($criteria['OriginationDescription']) && $criteria['OriginationDescription'] != '' ? "'" . $criteria['OriginationDescription'] . "'" : 'NULL';
                $criteria['Code']                   = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Description']            = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'NULL';
                $criteria['Country']                = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'NULL';
                $criteria['Effective']              = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['TimezonesID']            = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones']."'"  : 'NULL';
                $criteria['RoutingCategoryID']      = !empty($criteria['RoutingCategoryID']) && $criteria['RoutingCategoryID'] != '' ? "'" . $criteria['RoutingCategoryID'] . "'" : 'NULL';
                $criteria['Preference']             = !empty($criteria['Preference']) ? "'".$criteria['Preference']."'" : 'NULL';
                $criteria['Blocked']                = isset($criteria['Blocked']) && $criteria['Blocked'] != '' ? "'".$criteria['Blocked']."'" : 'NULL';
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'NULL';

                $RateTableID                = $id;
                $RateTableRateID            = $data['RateTableRateID'];

                if((empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') && !empty($data['TimezonesID'])) {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTableRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                if($data['ApprovedStatus'] == RateTable::RATE_STATUS_APPROVED) {
                    $success_message = 'Rates Successfully added for approval to Delete';
                    $query = "call prc_RateTableRateUpdateDelete (" . $RateTableID . ",'" . $RateTableRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $Rate . "," . $RateN . "," . $MinimumDuration . "," . $Interval1 . "," . $IntervalN . "," . $ConnectionFee . "," . $RoutingCategoryID . "," . $Preference . "," . $Blocked . "," . $RateCurrency . "," . $ConnectionFeeCurrency . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['RoutingCategoryID'] . "," . $criteria['Preference'] . "," . $criteria['Blocked'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                } else {
                    $success_message = 'Rates Successfully Deleted';
                    $query = "call prc_RateTableRateAAUpdateDelete (" . $RateTableID . ",'" . $RateTableRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $Rate . "," . $RateN . "," . $MinimumDuration . "," . $Interval1 . "," . $IntervalN . "," . $ConnectionFee . "," . $RateCurrency . "," . $ConnectionFeeCurrency . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "'," . $p_criteria . "," . $action . ")";
                }

                //Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => $success_message));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Rate Table Rates."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        }
    }

    // update rate table rate
    public function update_rate_table_rate($id) {
        if ($id > 0) {
            $data = Input::all();//echo "<pre>";print_r($data);exit();
            $error = 0;

            $EffectiveDate = $EndDate = $Rate = $RateN = $MinimumDuration = $Interval1 = $IntervalN = $ConnectionFee = $OriginationRateID = $RoutingCategoryID = $Preference = $Blocked = $RateCurrency = $ConnectionFeeCurrency = 'NULL';

            if(!empty($data['updateEffectiveDate']) || !empty($data['updateRate']) || !empty($data['updateRateN']) || !empty($data['updateMinimumDuration']) || !empty($data['updateInterval1']) || !empty($data['updateIntervalN']) || !empty($data['updateConnectionFee']) || !empty($data['updateOriginationRateID']) || !empty($data['updateRoutingCategoryID']) || !empty($data['updatePreference']) || !empty($data['updateBlocked']) || !empty($data['RateCurrency']) || !empty($data['ConnectionFeeCurrency'])) {// || !empty($data['EndDate'])
                if(!empty($data['updateEffectiveDate'])) {
                    if(!empty($data['EffectiveDate'])) {
                        $EffectiveDate = "'".$data['EffectiveDate']."'";
                    } else {
                        $error=1;
                    }
                }
                /*if(!empty($data['updateEndDate'])) {
                    if(!empty($data['EndDate'])) {
                        $EndDate = "'".$data['EndDate']."'";
                    } else if (empty($data['updateType'])) {
                        $error=1;
                    }
                }*/
                if(!empty($data['updateRate'])) {
                    if(isset($data['Rate'])) {
                        $Rate = "'".floatval($data['Rate'])."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateRateN'])) {
                    if(isset($data['RateN'])) {
                        $RateN = $data['RateN'] != '' ? "'".floatval($data['RateN'])."'" : "'NULL'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateMinimumDuration'])) {
                    if(isset($data['MinimumDuration'])) {
                        $MinimumDuration = "'".trim($data['MinimumDuration'])."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateInterval1'])) {
                    if(isset($data['Interval1'])) {
                        $Interval1 = "'".trim($data['Interval1'])."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateIntervalN'])) {
                    if(isset($data['IntervalN'])) {
                        $IntervalN = "'".trim($data['IntervalN'])."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateConnectionFee'])) {
                    if(isset($data['ConnectionFee'])) {
                        $ConnectionFee = $data['ConnectionFee'] != '' ? "'".floatval($data['ConnectionFee'])."'" : "'NULL'";
                    } else if (empty($data['updateType'])) {
                        $error=1;
                    }
                }
                if(!empty($data['updateRateCurrency'])) {
                    if(!empty($data['RateCurrency'])) {
                        $RateCurrency = "'".$data['RateCurrency']."'";
                    }
                }
                if(!empty($data['updateConnectionFeeCurrency'])) {
                    if(!empty($data['ConnectionFeeCurrency'])) {
                        $ConnectionFeeCurrency = "'".$data['ConnectionFeeCurrency']."'";
                    }
                }
                if(!empty($data['updateOriginationRateID'])) {
                    if(!empty($data['OriginationRateID'])) {
                        $OriginationRateID = "'".$data['OriginationRateID']."'";
                    }
                }
                if(!empty($data['updateRoutingCategoryID'])) {
                    if(isset($data['RoutingCategoryID'])) {
                        $RoutingCategoryID = "'".$data['RoutingCategoryID']."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updatePreference'])) {
                    if(isset($data['Preference'])) {
                        $Preference = "'".$data['Preference']."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateBlocked'])) {
                    if(isset($data['Blocked'])) {
                        $Blocked = "'1'";
                    } else {
                        $Blocked = "'0'";
                    }
                }
                if(isset($error) && $error==1) {
                    return Response::json(array("status" => "failed", "message" => "Please Select Checked Field Data"));
                }

            } else {
                return Response::json(array("status" => "failed", "message" => "No Rate selected to Update."));
            }

            $username = User::get_user_full_name();

            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 1; //update action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['OriginationCode']        = !empty($criteria['OriginationCode']) && $criteria['OriginationCode'] != '' ? "'" . $criteria['OriginationCode'] . "'" : 'NULL';
                $criteria['OriginationDescription'] = !empty($criteria['OriginationDescription']) && $criteria['OriginationDescription'] != '' ? "'" . $criteria['OriginationDescription'] . "'" : 'NULL';
                $criteria['Code']                   = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Description']            = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'NULL';
                $criteria['Country']                = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'NULL';
                $criteria['Effective']              = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['TimezonesID']            = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones']."'"  : 'NULL';
                $criteria['RoutingCategoryID']      = !empty($criteria['RoutingCategoryID']) && $criteria['RoutingCategoryID'] != '' ? "'" . $criteria['RoutingCategoryID'] . "'" : 'NULL';
                $criteria['Preference']             = !empty($criteria['Preference']) ? "'".$criteria['Preference']."'" : 'NULL';
                $criteria['Blocked']                = isset($criteria['Blocked']) && $criteria['Blocked'] != '' ? "'".$criteria['Blocked']."'" : 'NULL';
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'NULL';

                $RateTableID                = $id;
                $RateTableRateID            = $data['RateTableRateID'];
                $OriginationRateID          = !empty($OriginationRateID) ? $OriginationRateID : 'NULL';
                $RoutingCategoryID          = !empty($RoutingCategoryID) ? $RoutingCategoryID : 'NULL';

                if((empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') && !empty($data['TimezonesID'])) {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTableRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                if($data['ApprovedStatus'] == RateTable::RATE_STATUS_APPROVED) {
                    $query = "call prc_RateTableRateUpdateDelete (" . $RateTableID . ",'" . $RateTableRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $Rate . "," . $RateN . "," . $MinimumDuration . "," . $Interval1 . "," . $IntervalN . "," . $ConnectionFee . "," . $RoutingCategoryID . "," . $Preference . "," . $Blocked . "," . $RateCurrency . "," . $ConnectionFeeCurrency . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['RoutingCategoryID'] . "," . $criteria['Preference'] . "," . $criteria['Blocked'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "'," . $p_criteria . "," . $action . ")";
                } else {
                    $query = "call prc_RateTableRateAAUpdateDelete (" . $RateTableID . ",'" . $RateTableRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $Rate . "," . $RateN . "," . $MinimumDuration . "," . $Interval1 . "," . $IntervalN . "," . $ConnectionFee . "," . $RateCurrency . "," . $ConnectionFeeCurrency . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "'," . $p_criteria . "," . $action . ")";
                }
                //Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating Rate Table Rate."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        } else {
            return Response::json(array("status" => "failed", "message" => "No RateTable Found."));
        }
    }

    public function approve_rate_table_rate($id) {
        if ($id > 0) {
            $data = Input::all();//echo "<pre>";print_r($data);exit();
            $username = User::get_user_full_name();

            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 1; //update action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['OriginationCode']        = !empty($criteria['OriginationCode']) && $criteria['OriginationCode'] != '' ? "'" . $criteria['OriginationCode'] . "'" : 'NULL';
                $criteria['OriginationDescription'] = !empty($criteria['OriginationDescription']) && $criteria['OriginationDescription'] != '' ? "'" . $criteria['OriginationDescription'] . "'" : 'NULL';
                $criteria['Code']                   = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Description']            = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'NULL';
                $criteria['Country']                = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'NULL';
                $criteria['Effective']              = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['TimezonesID']            = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones']."'"  : 'NULL';
                $criteria['RoutingCategoryID']      = !empty($criteria['RoutingCategoryID']) && $criteria['RoutingCategoryID'] != '' ? "'" . $criteria['RoutingCategoryID'] . "'" : 'NULL';
                $criteria['Preference']             = !empty($criteria['Preference']) ? "'".$criteria['Preference']."'" : 'NULL';
                $criteria['Blocked']                = isset($criteria['Blocked']) && $criteria['Blocked'] != '' ? "'".$criteria['Blocked']."'" : 'NULL';
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'NULL';

                $RateTableID                = $id;
                $RateTableRateID            = $data['RateTableRateID'];

                if((empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') && !empty($data['TimezonesID'])) {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTableRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTableRateApprove (" . $RateTableID . ",'" . $RateTableRateID . "','" . $data['ApprovedStatus'] . "'," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['RoutingCategoryID'] . "," . $criteria['Preference'] . "," . $criteria['Blocked'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                //Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating Rate Table Rate."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        } else {
            return Response::json(array("status" => "failed", "message" => "No RateTable Found."));
        }
    }

    public function approve_rate_table_did_rate($id) {
        if ($id > 0) {
            $data = Input::all();//echo "<pre>";print_r($data);exit();
            $username = User::get_user_full_name();

            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 1; //update action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['OriginationCode']        = !empty($criteria['OriginationCode']) && $criteria['OriginationCode'] != '' ? "'" . $criteria['OriginationCode'] . "'" : 'NULL';
                $criteria['OriginationDescription'] = !empty($criteria['OriginationDescription']) && $criteria['OriginationDescription'] != '' ? "'" . $criteria['OriginationDescription'] . "'" : 'NULL';
                $criteria['Code']                   = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Description']            = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'NULL';
                $criteria['Country']                = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'NULL';
                $criteria['Effective']              = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['TimezonesID']            = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones']."'"  : 'NULL';
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'NULL';
                $criteria['City']                   = !empty($criteria['City']) && $criteria['City'] != '' ? "'" . $criteria['City'] . "'" : 'NULL';
                $criteria['Tariff']                 = !empty($criteria['Tariff']) && $criteria['Tariff'] != '' ? "'" . $criteria['Tariff'] . "'" : 'NULL';

                $RateTableID                = $id;
                $RateTableDIDRateID         = $data['RateTableDIDRateID'];


                if (empty($data['RateTableDIDRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                if((empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') && !empty($data['TimezonesID'])) {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }
                $query = "call prc_RateTableDIDRateApprove (" . $RateTableID . ",'" . $RateTableDIDRateID . "','" . $data['ApprovedStatus'] . "'," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['OriginationCode'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . "," . $criteria['City'] . "," . $criteria['Tariff'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                //Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating Rate Table Rate."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        } else {
            return Response::json(array("status" => "failed", "message" => "No RateTable Found."));
        }
    }

    public function approve_rate_table_pkg_rate($id) {
        if ($id > 0) {
            $data = Input::all();
            $username = User::get_user_full_name();
            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 1; //update action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['Code']                   = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Effective']              = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'NULL';
                $criteria['TimezonesID']            = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones']."'"  : 'NULL';
                $RateTableID                = $id;
                $RateTablePKGRateID         = $data['RateTablePKGRateID'];
                if (empty($data['RateTablePKGRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }
                if((empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') && !empty($data['TimezonesID'])) {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                $query = "call prc_RateTablePKGRateApprove (" . $RateTableID . ",'" . $RateTablePKGRateID . "','" . $data['ApprovedStatus'] . "'," . $criteria['Code'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID']  . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                //Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating Rate Table Rate."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        } else {
            return Response::json(array("status" => "failed", "message" => "No RateTable Found."));
        }
    }

    public function change_status($id, $status) {
        if ($id > 0 && ( $status == 0 || $status == 1)) {
            if (RateTable::find($id)->update(["Status" => $status, "ModifiedBy" => User::get_user_full_name()])) {
                return Response::json(array("status" => "success", "message" => "Status Successfully Changed"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Changing Status."));
            }
        }
    }
    
    public function exports($type) {
            $CompanyID = User::get_companyID();
            /*$rate_tables = RateTable::where(["CompanyId" => $CompanyID])->orderBy("RateTableId", "desc");
            $data = Input::all();
            if($data['TrunkID']){
                $rate_tables->where('TrunkID',$data['TrunkID']);
            }
            $rate_tables = $rate_tables->get(["RateTableName"]);*/

            $Partner    = 'IF(tblRateTable.Reseller=0,"",IF(tblRateTable.Reseller=-1,"All",tblReseller.ResellerName)) AS Partner';
            $Type       = 'IF(tblRateTable.Type='.RateTable::RATE_TABLE_TYPE_ACCESS.', "Access",IF(tblRateTable.Type='.RateTable::RATE_TABLE_TYPE_PACKAGE.', "Package","Termination")) AS Type';
            $AppliedTo  = 'IF(tblRateTable.AppliedTo='.RateTable::APPLIED_TO_VENDOR.', "Vendor",IF(tblRateTable.AppliedTo='.RateTable::APPLIED_TO_RESELLER.', "Partner","Customer")) AS AppliedTo';

            $rate_tables = RateTable::
            Join('tblCurrency','tblCurrency.CurrencyId','=','tblRateTable.CurrencyId')
                ->join('tblCodeDeck','tblCodeDeck.CodeDeckId','=','tblRateTable.CodeDeckId')
                ->leftjoin('tblTrunk','tblTrunk.TrunkID','=','tblRateTable.TrunkID')
                ->leftjoin('tblDIDCategory','tblDIDCategory.DIDCategoryID','=','tblRateTable.DIDCategoryID')
                ->leftjoin('tblCustomerTrunk','tblCustomerTrunk.CustomerTrunkID','=',DB::RAW('(SELECT CustomerTrunkID FROM tblCustomerTrunk WHERE RateTableID = tblRateTable.RateTableId LIMIT 1)'))
                ->leftjoin('tblVendorConnection','tblVendorConnection.VendorConnectionID','=',DB::RAW('(SELECT VendorConnectionID FROM tblVendorConnection WHERE RateTableID = tblRateTable.RateTableId LIMIT 1)'))
                ->leftjoin('tblReseller','tblReseller.ResellerID','=','tblRateTable.Reseller');


            $data = Input::all();

            if(!empty($data['ResellerPage'])) {
                $rate_tables->select([DB::RAW($Type),'tblRateTable.RateTableName','tblCurrency.Code AS Currency', 'tblTrunk.Trunk as Trunk', 'tblDIDCategory.CategoryName as AccessCategory','tblCodeDeck.CodeDeckName AS Codedeck','tblRateTable.updated_at AS LastUpdated']);
            } else {
                $rate_tables->select([DB::RAW($Partner),DB::RAW($Type),DB::RAW($AppliedTo),'tblRateTable.RateTableName','tblCurrency.Code AS Currency', 'tblTrunk.Trunk as Trunk', 'tblDIDCategory.CategoryName as AccessCategory','tblCodeDeck.CodeDeckName AS Codedeck','tblRateTable.updated_at AS LastUpdated']);
            }

            //$rate_tables = RateTable::join('tblCurrency', 'tblCurrency.CurrencyId', '=', 'tblRateTable.CurrencyId')->where(["tblRateTable.CompanyId" => $CompanyID])->select(["tblRateTable.RateTableName","Code","tblRateTable.updated_at", "tblRateTable.RateTableId"]);

            if(!empty($data['ResellerPage'])) {
                $ResellerID = Reseller::getResellerID();
                $rate_tables->where("tblRateTable.CompanyId",1);
                $rate_tables->where("tblRateTable.Reseller",-1)->orWhere("tblRateTable.Reseller",$ResellerID);
            } else {
                $rate_tables->where("tblRateTable.CompanyId",$CompanyID);
                if(isset($data['Reseller']) && $data['Reseller'] != 0){
                    $rate_tables->where('tblRateTable.Reseller',$data['Reseller']);
                }
            }

            if($data['TrunkID']){
                $rate_tables->where('tblRateTable.TrunkID',$data['TrunkID']);
            }
            if(!empty($data['Type'])){
                $rate_tables->where('tblRateTable.Type',$data['Type']);
            }
            if(!empty($data['DIDCategoryID'])){
                $rate_tables->where('tblRateTable.DIDCategoryID',$data['DIDCategoryID']);
            }
            if(!empty($data['AppliedTo'])){
                $rate_tables->where('tblRateTable.AppliedTo',$data['AppliedTo']);
            }
            if($data['Search']!=''){
                $rate_tables->WhereRaw('tblRateTable.RateTableName like "%'.$data['Search'].'%"');
            }
            $rate_tables = $rate_tables->get();
            $excel_data = json_decode(json_encode($rate_tables),true);


            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Rates Table.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Rates Table.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*
            Excel::create('Rates Table', function ($excel) use ($rate_tables) {
                $excel->sheet('Rates Table', function ($sheet) use ($rate_tables) {
                    $sheet->fromArray($rate_tables);
                });
            })->download('xls');*/
    }
    
    public function rate_exports($id,$type) {
        $companyID = User::get_companyID();
        $data = Input::all();

        $ApprovedStatus = $data['ApprovedStatus'];

        $RateTableName = RateTable::find($id)->RateTableName;

        $view = isset($data['view']) && $data['view'] == 2 ? $data['view'] : 1;
        $data['Country']                = !empty($data['Country']) && $data['Country'] != 'All' ? $data['Country'] : 'NULL';
        $data['Code']                   = $data['Code'] != '' ? "'".$data['Code']."'" : 'NULL';
        $data['Description']            = !empty($data['Description']) ? "'".$data['Description']."'" : 'NULL';
        $data['OriginationCode']        = !empty($data['OriginationCode']) ? "'".$data['OriginationCode']."'" : 'NULL';
        $data['OriginationDescription'] = !empty($data['OriginationDescription']) ? "'".$data['OriginationDescription']."'" : 'NULL';
        $data['RoutingCategoryID']      = !empty($data['RoutingCategoryID']) ? "'".$data['RoutingCategoryID']."'" : 'NULL';
        $data['Preference']             = !empty($data['Preference']) ? "'".$data['Preference']."'" : 'NULL';
        $data['Blocked']                = isset($data['Blocked']) && $data['Blocked'] != '' ? "'".$data['Blocked']."'" : 'NULL';
        $data['ApprovedStatus']         = isset($data['ApprovedStatus']) && $data['ApprovedStatus'] != '' ? "'".$data['ApprovedStatus']."'" : 'NULL';
        $data['Timezones']              = !empty($data['Timezones']) && $data['Timezones'] != '' ?  $data['Timezones']  : 'NULL';
        $data['AccessType']             = !empty($data['AccessType']) ? "'".$data['AccessType']."'" : 'NULL';

        $data['City']                   = !empty($data['City']) ? "'".$data['City']."'" : 'NULL';
        $data['Tariff']                 = !empty($data['Tariff']) ? "'".$data['Tariff']."'" : 'NULL';
        $data['ratetablepageview']      = !empty($data['ratetablepageview']) && $data['ratetablepageview']=='AdvanceView' ? 1 : 0;
        $data['isExport']               = '1'.$data['ratetablepageview'];

        $rateTable = RateTable::find($id);
        $TypeVoiceCall  = RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
        $TypeDID        = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);

        if(!empty($data['ResellerPage'])) {
            $companyID = 1;
        }

        if($ApprovedStatus == RateTable::RATE_STATUS_APPROVED) { //approved rates
            if($rateTable->Type == $TypeVoiceCall) { // voice call
                if(!empty($data['DiscontinuedRates'])) {
                    $query = " call prc_getDiscontinuedRateTableRateGrid (".$companyID.",".$id.",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description'].",".$data['RoutingCategoryID'].",".$data['Preference'].",".$data['Blocked'].",".$data['ApprovedStatus'].",".$view.",NULL,NULL,NULL,NULL,".$data['isExport'].")";
                } else {
                    $query = " call prc_GetRateTableRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['RoutingCategoryID'].",".$data['Preference'].",".$data['Blocked'].",".$data['ApprovedStatus'].",".$view.",NULL,NULL,NULL,NULL,".$data['isExport'].")";
                }
            } else if($rateTable->Type == $TypeDID) { // did
                if(!empty($data['DiscontinuedRates'])) {
                    $query = "call prc_getDiscontinuedRateTableDIDRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Country'] . ",".$data['OriginationCode']."," . $data['Code'] . "," . $data['City'] . "," . $data['Tariff'] . "," . $data['AccessType'] . "," . $data['ApprovedStatus'] . ",NULL,NULL,NULL,NULL,".$data['isExport'].")";
                } else {
                    $query = "call prc_GetRateTableDIDRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['Code']."," . $data['City'] . "," . $data['Tariff'] . ",".$data['AccessType'].",'".$data['Effective']."',".$data['ApprovedStatus'].",NULL,NULL,NULL,NULL,".$data['isExport'].")";
                }
            } else { // package
                if(!empty($data['DiscontinuedRates'])) {
                    $query = "call prc_getDiscontinuedRateTablePKGRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Code'] . ",".$data['ApprovedStatus'].",NULL,NULL,NULL,NULL,1)";
                } else {
                    $query = "call prc_GetRateTablePKGRate (".$companyID.",".$id.",".$data['Timezones'].",".$data['Code'].",'".$data['Effective']."',".$data['ApprovedStatus'].",NULL,NULL,NULL,NULL,1)";
                }
            }
        } else { //awaiting approval/rejected
            if($rateTable->Type == $TypeVoiceCall) { // voice call
                $query = "call prc_GetRateTableRateAA (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['ApprovedStatus'].",NULL,NULL,NULL,NULL,".$data['isExport'].")";
            } else if($rateTable->Type == $TypeDID) { // did
                $query = "call prc_GetRateTableDIDRateAA (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['Code'].",".$data['City'].",".$data['Tariff'].",".$data['AccessType'].",'".$data['Effective']."',".$data['ApprovedStatus'].",NULL,NULL,NULL,NULL,".$data['isExport'].")";
            } else { // package
                $query = "call prc_GetRateTablePKGRateAA (".$companyID.",".$id.",".$data['Timezones'].",".$data['Code'].",'".$data['Effective']."',".$data['ApprovedStatus'].",NULL,NULL,NULL,NULL,1)";
            }
        }

        //Log::info($query);
        DB::setFetchMode( PDO::FETCH_ASSOC );
        $rate_table_rates  = DB::select($query);
        DB::setFetchMode( Config::get('database.fetch'));

        if(!empty($data['ResellerPage'])) {
            foreach ($rate_table_rates as $key => $value) {
                if (isset($value['Approved By/Date'])) {
                    unset($value['Approved By/Date']);
                    $rate_table_rates[$key] = $value;
                }
                if (isset($value['ApprovedStatus'])) {
                    unset($value['ApprovedStatus']);
                    $rate_table_rates[$key] = $value;
                }
            }
        }

        $RateTableName = str_replace( '\/','-',$RateTableName);
        $RateTableName = str_replace( '/','-',$RateTableName);

        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/'.$RateTableName . ' - Rate Table Rates.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($rate_table_rates);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/'.$RateTableName . ' - Rate Table Rates.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($rate_table_rates);
        }
        /*Excel::create($RateTableName . ' - Rates Table', function ($excel) use ($rate_table_rates) {
            $excel->sheet('Rates Table', function ($sheet) use ($rate_table_rates) {
                $sheet->fromArray($rate_table_rates);
            });
        })->download('xls');*/
    }
    public static function add_newrate($id){
        $data = Input::all();
        $rateTable = RateTable::find($id);
        $username = User::get_user_full_name();
        $message = array();

        $data['RateTableId'] = $id;

        $RateTableRate = array();
        $RateTableRate['RateTableId']       = $id;
        $RateTableRate['RateID']            = $data['RateID'];
        $RateTableRate['OriginationRateID'] = !empty($data['OriginationRateID']) ? $data['OriginationRateID'] : 0;
        $RateTableRate['EffectiveDate']     = $data['EffectiveDate'];
        $RateTableRate['EndDate']           = !empty($data['EndDate']) ? $data['EndDate'] : NULL;
        $RateTableRate['TimezonesID']       = $data['TimezonesID'];
        $RateApprovalProcess = CompanySetting::getKeyVal('RateApprovalProcess');
        $TypeVoiceCall  = RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
        $TypeDID        = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);

        if($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR) {
            $RateTableRateModel     = 'RateTableRateAA';
            $RateTableDIDRateModel  = 'RateTableDIDRateAA';
            $RateTablePKGRateModel  = 'RateTablePKGRateAA';
            $RateTableRate['ApprovedStatus']    = 0;
        } else {
            $RateTableRateModel     = 'RateTableRate';
            $RateTableDIDRateModel  = 'RateTableDIDRate';
            $RateTablePKGRateModel  = 'RateTablePKGRate';
            $RateTableRate['ApprovedStatus']    = 1;
        }

        if($rateTable->Type == $TypeVoiceCall) {
            $rules                          = $RateTableRateModel::$rules;

            if(!($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)) {
                $rules['RateID'] = 'required|unique:tblRateTableRate,RateID,NULL,RateTableRateId,RateTableId,' . $id . ',TimezonesID,' . $RateTableRate['TimezonesID'] . ',EffectiveDate,' . $RateTableRate['EffectiveDate'] . ',OriginationRateID,' . $RateTableRate['OriginationRateID'];
                //$rules['OriginationRateID']   = 'unique:'.$table.',OriginationRateID,NULL,'.$col_id.',RateTableId,'.$id.',EffectiveDate,'.$data['EffectiveDate'].',RateID,'.$data['RateID'];
                $message['RateID.unique'] = 'This combination of Origination Rate and Destination Rate on given Effective Date is already exist!';
            }
        } else if($rateTable->Type == $TypeDID) {
            $rules                          = $RateTableDIDRateModel::$rules;
            $message                        = $RateTableDIDRateModel::$message;
            $RateTableRate['City']          = !empty($data['City']) ? $data['City'] : "";
            $RateTableRate['Tariff']        = !empty($data['Tariff']) ? $data['Tariff'] : "";
            $RateTableRate['AccessType']    = !empty($data['AccessType']) ? $data['AccessType'] : '';

            if(!($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)) {
                $rules['RateID'] = 'required|unique:tblRateTableDIDRate,RateID,NULL,RateTableDIDRateID,RateTableId,' . $id . ',TimezonesID,' . $RateTableRate['TimezonesID'] . ',EffectiveDate,' . $RateTableRate['EffectiveDate'] . ',OriginationRateID,' . $RateTableRate['OriginationRateID'] . ',City,' . $RateTableRate['City']. ',Tariff,' . $RateTableRate['Tariff'];
                $message['RateID.unique'] = 'This combination of Origination Rate and Destination Rate on given Effective Date is already exist!';
            }
        } else {
            $rules                          = $RateTablePKGRateModel::$rules;
            $message                        = $RateTablePKGRateModel::$message;

            if(!($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)) {
                $rules['RateID'] = 'required|unique:tblRateTablePKGRate,RateID,NULL,RateTablePKGRateID,RateTableId,' . $id . ',TimezonesID,' . $RateTableRate['TimezonesID'] . ',EffectiveDate,' . $RateTableRate['EffectiveDate'];
                $message['RateID.unique'] = 'This Package Name on given Effective Date is already exist!';
            }
        }
        $validator                          = Validator::make($data, $rules, $message);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if($rateTable->Type == $TypeVoiceCall) {
            $RateTableRate['Rate']                  = $data['Rate'];
            $RateTableRate['RateN']                 = !empty($data['RateN']) ? $data['RateN'] : $data['Rate'];
            $RateTableRate['MinimumDuration']       = $data['MinimumDuration'];
            $RateTableRate['Interval1']             = $data['Interval1'];
            $RateTableRate['IntervalN']             = $data['IntervalN'];
            $RateTableRate['ConnectionFee']         = $data['ConnectionFee'];
            $RateTableRate['RateCurrency']          = $data['RateCurrency'] == '' ? NULL : $data['RateCurrency'];
            $RateTableRate['ConnectionFeeCurrency'] = $data['ConnectionFeeCurrency' ] == '' ? NULL : $data['ConnectionFeeCurrency'];

            if ($rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR) {
                $ROUTING_PROFILE = CompanyConfiguration::get('ROUTING_PROFILE');
                if ($ROUTING_PROFILE == 1) {
                    $RateTableRate['RoutingCategoryID'] = $data['RoutingCategoryID'];
                }
                $RateTableRate['Preference'] = $data['Preference'] != '' ? $data['Preference'] : NULL;
                $RateTableRate['Blocked'] = !empty($data['Blocked']) ? $data['Blocked'] : 0;
            }

            $Rate = $RateTableRateModel::insert($RateTableRate);
            $archive_query = "CALL prc_ArchiveOldRateTableRate('".$RateTableRate['RateTableId']."','".$RateTableRate['TimezonesID']."','".$username."');";
        } else if($rateTable->Type == $TypeDID) {
            $RateTableRate['OneOffCost']                = $data['OneOffCost'] == '' ? NULL : $data['OneOffCost'];
            $RateTableRate['MonthlyCost']               = $data['MonthlyCost' ] == '' ? NULL : $data['MonthlyCost'];
            $RateTableRate['CostPerCall']               = $data['CostPerCall' ] == '' ? NULL : $data['CostPerCall'];
            $RateTableRate['CostPerMinute']             = $data['CostPerMinute' ] == '' ? NULL : $data['CostPerMinute'];
            $RateTableRate['SurchargePerCall']          = $data['SurchargePerCall' ] == '' ? NULL : $data['SurchargePerCall'];
            $RateTableRate['SurchargePerMinute']        = $data['SurchargePerMinute' ] == '' ? NULL : $data['SurchargePerMinute'];
            $RateTableRate['OutpaymentPerCall']         = $data['OutpaymentPerCall' ] == '' ? NULL : $data['OutpaymentPerCall'];
            $RateTableRate['OutpaymentPerMinute']       = $data['OutpaymentPerMinute' ] == '' ? NULL : $data['OutpaymentPerMinute'];
            $RateTableRate['Surcharges']                = $data['Surcharges' ] == '' ? NULL : $data['Surcharges'];
            $RateTableRate['Chargeback']                = $data['Chargeback' ] == '' ? NULL : $data['Chargeback'];
            $RateTableRate['CollectionCostAmount']      = $data['CollectionCostAmount' ] == '' ? NULL : $data['CollectionCostAmount'];
            $RateTableRate['CollectionCostPercentage']  = $data['CollectionCostPercentage' ] == '' ? NULL : $data['CollectionCostPercentage'];
            $RateTableRate['RegistrationCostPerNumber'] = $data['RegistrationCostPerNumber' ] == '' ? NULL : $data['RegistrationCostPerNumber'];

            $RateTableRate['OneOffCostCurrency']                = $data['OneOffCostCurrency'] == '' ? NULL : $data['OneOffCostCurrency'];
            $RateTableRate['MonthlyCostCurrency']               = $data['MonthlyCostCurrency' ] == '' ? NULL : $data['MonthlyCostCurrency'];
            $RateTableRate['CostPerCallCurrency']               = $data['CostPerCallCurrency' ] == '' ? NULL : $data['CostPerCallCurrency'];
            $RateTableRate['CostPerMinuteCurrency']             = $data['CostPerMinuteCurrency' ] == '' ? NULL : $data['CostPerMinuteCurrency'];
            $RateTableRate['SurchargePerCallCurrency']          = $data['SurchargePerCallCurrency' ] == '' ? NULL : $data['SurchargePerCallCurrency'];
            $RateTableRate['SurchargePerMinuteCurrency']        = $data['SurchargePerMinuteCurrency' ] == '' ? NULL : $data['SurchargePerMinuteCurrency'];
            $RateTableRate['OutpaymentPerCallCurrency']         = $data['OutpaymentPerCallCurrency' ] == '' ? NULL : $data['OutpaymentPerCallCurrency'];
            $RateTableRate['OutpaymentPerMinuteCurrency']       = $data['OutpaymentPerMinuteCurrency' ] == '' ? NULL : $data['OutpaymentPerMinuteCurrency'];
            $RateTableRate['SurchargesCurrency']                = $data['SurchargesCurrency' ] == '' ? NULL : $data['SurchargesCurrency'];
            $RateTableRate['ChargebackCurrency']                = $data['ChargebackCurrency' ] == '' ? NULL : $data['ChargebackCurrency'];
            $RateTableRate['CollectionCostAmountCurrency']      = $data['CollectionCostAmountCurrency' ] == '' ? NULL : $data['CollectionCostAmountCurrency'];
            $RateTableRate['RegistrationCostPerNumberCurrency'] = $data['RegistrationCostPerNumberCurrency' ] == '' ? NULL : $data['RegistrationCostPerNumberCurrency'];

            $Rate = $RateTableDIDRateModel::insert($RateTableRate);
            $archive_query = "CALL prc_ArchiveOldRateTableDIDRate('".$RateTableRate['RateTableId']."','".$RateTableRate['TimezonesID']."','".$username."');";
        } else {
            unset($RateTableRate['OriginationRateID']);
            $RateTableRate['OneOffCost']                        = $data['OneOffCost'] == '' ? NULL : $data['OneOffCost'];
            $RateTableRate['MonthlyCost']                       = $data['MonthlyCost' ] == '' ? NULL : $data['MonthlyCost'];
            $RateTableRate['PackageCostPerMinute']              = $data['PackageCostPerMinute' ] == '' ? NULL : $data['PackageCostPerMinute'];
            $RateTableRate['RecordingCostPerMinute']            = $data['RecordingCostPerMinute' ] == '' ? NULL : $data['RecordingCostPerMinute'];

            $RateTableRate['OneOffCostCurrency']                = $data['OneOffCostCurrency'] == '' ? NULL : $data['OneOffCostCurrency'];
            $RateTableRate['MonthlyCostCurrency']               = $data['MonthlyCostCurrency' ] == '' ? NULL : $data['MonthlyCostCurrency'];
            $RateTableRate['PackageCostPerMinuteCurrency']      = $data['PackageCostPerMinuteCurrency' ] == '' ? NULL : $data['PackageCostPerMinuteCurrency'];
            $RateTableRate['RecordingCostPerMinuteCurrency']    = $data['RecordingCostPerMinuteCurrency' ] == '' ? NULL : $data['RecordingCostPerMinuteCurrency'];

            $Rate = $RateTablePKGRateModel::insert($RateTableRate);
            $archive_query = "CALL prc_ArchiveOldRateTablePKGRate('".$RateTableRate['RateTableId']."','".$RateTableRate['TimezonesID']."','".$username."');";
        }

        if ($Rate) {
            DB::statement($archive_query);
            return Response::json(array("status" => "success", "message" => "Rate Successfully Inserted "));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Inserting  Rate."));
        }

    }

    public function upload($id) {
        $uploadtemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_VENDOR_RATE));
        $rateTable = RateTable::where(["RateTableId" => $id])->get(array('TrunkID','CodeDeckId'));
        $rate_sheet_formates = RateSheetFormate::getVendorRateSheetFormatesDropdownList();
        return View::make('ratetables.upload', compact('id','rateTable','rate_sheet_formates','uploadtemplate'));
    }

    public function check_upload() {
        try {
            ini_set('max_execution_time', 0);
            $data = Input::all();
            if (!isset($data['Trunk']) || empty($data['Trunk'])) {
                return json_encode(["status" => "failed", "message" => 'Please Select a Trunk']);
            } else if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                    $file_name_without_ext = GUID::generate();
                    $file_name = $file_name_without_ext . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else if (isset($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if (!empty($file_name)) {

                if ($data['uploadtemplate'] > 0) {
                    $RateTableFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($RateTableFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($RateTableFileUploadTemplate)) {
                    $grid['RateTableFileUploadTemplate'] = json_decode(json_encode($RateTableFileUploadTemplate), true);
                    $grid['RateTableFileUploadTemplate']['Options'] = json_decode($RateTableFileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        }catch(Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function download_sample_excel_file(){
        $filePath =  public_path() .'/uploads/sample_upload/RateTableUploadSample.csv';
        download_file($filePath);

    }

    function ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TempFileName'];
            $grid = getFileContent($file_name, $data);
            $grid['filename'] = $data['TemplateFile'];
            $grid['tempfilename'] = $data['TempFileName'];
            if ($data['uploadtemplate'] > 0) {
                $RateTableFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                $grid['RateTableFileUploadTemplate'] = json_decode(json_encode($RateTableFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['RateTableFileUploadTemplate']['Options'] = array();
            $grid['RateTableFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['RateTableFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function storeTemplate($id) {
        $data = Input::all();
        $CompanyID = User::get_companyID();

        $rules['selection.Code'] = 'required';
        $rules['selection.Description'] = 'required';
        $rules['selection.Rate'] = 'required';
        //$rules['selection.EffectiveDate'] = 'required';
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = CompanyConfiguration::get('TEMP_PATH') . '/';

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['RATETABLE_UPLOAD']);
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload vendor rates file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = filterArrayRemoveNewLines($data['selection']);//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            if (isset($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                $template = FileUploadTemplate::find($data['uploadtemplate']);
                $template->update($save);
            } else {
                $template = FileUploadTemplate::create($save);
            }
            $data['uploadtemplate'] = $template->FileUploadTemplateID;
        }
        $save = array();
        $option["option"]=  $data['option'];
        $option["selection"] = filterArrayRemoveNewLines($data['selection']);
        $save['Options'] = json_encode($option);
        $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path'] = $fullPath;
        $save["RateTableID"] = $id;
        $save['codedeckid'] = $data['CodeDeckID'];
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        $save['Trunk'] = $data['Trunk'];
        $save['checkbox_replace_all'] = $data['checkbox_replace_all'];
        $save['checkbox_rates_with_effected_from'] = $data['checkbox_rates_with_effected_from'];
        $save['checkbox_add_new_codes_to_code_decks'] = $data['checkbox_add_new_codes_to_code_decks'];
        $save['ratetablename'] = RateTable::where(["RateTableId" => $id])->pluck('RateTableName');

        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("RTU", $save);
            if ($result['status'] != "success") {
                DB::rollback();
                return json_encode(["status" => "failed", "message" => $result['message']]);
            }
            DB::commit();
            @unlink($temp_path . $file_name);
            return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
        } catch (Exception $ex) {
            DB::rollback();
            return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }
	
	//get ajax code for add new rate
    public function getCodeByAjax(){
        $CompanyID = User::get_companyID();
        $list = array();
        $data = Input::all();
        $rate = $data['q'].'%';
        $ratetableid = $data['page'];
        $CodeDeckId = RateTable::getCodeDeckId($ratetableid);
        $codes = CodeDeck::where(["CompanyID" => $CompanyID,'CodeDeckId'=>$CodeDeckId])
            ->where('Code','like',$rate)->take(100)->lists('Code', 'RateID');

        if(count($codes) > 0){
            foreach($codes as $key => $value){
                $list2 = array();
                $list2['id'] = $key;
                $list2['text'] = $value;
                $list[]= json_encode($list2);
            }
            $rateids = '['.implode(',',$list).']';
        }else{
            $rateids = '[]';
        }


        return $rateids;
    }

    public function edit($id){
        $data = Input::all();
        $rateTableId = RateTable::findOrFail($id);
        $data['CompanyID'] = User::get_companyID();
        $data['RateTableName'] = trim($data['RateTableName']);

        $CustomerTrunk = CustomerTrunk::where('RateTableID',$id)->count();
        $VendorConnection = VendorConnection::where('RateTableID',$id)->count();

        $rules = array(
            'RateTableName' => 'required|unique:tblRateTable,RateTableName,'.$id.',RateTableId,CompanyID,'.$data['CompanyID'],
            'CompanyID' => 'required',
        );

        if($CustomerTrunk == 0 && $VendorConnection == 0) {
            $rules['CurrencyID'] = 'required';
        } else {
            unset($data['CurrencyID']);
            unset($data['TrunkID']);
            unset($data['DIDCategoryID']);
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if($rateTableId->AppliedTo != RateTable::APPLIED_TO_RESELLER) {
            unset($data['Reseller']);
        }

        $data['ModifiedBy'] = User::get_user_full_name();
        if ($rateTableId->update($data)) {
            return Response::json(array("status" => "success", "message" => "Rate Table Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Rate Table."));
        }
    }

    public function search_ajax_datagrid_archive_rates($RateTableID) {

        $data       = Input::all();
        $companyID  = User::get_companyID();
        $view       = isset($data['view']) && $data['view'] == 2 ? $data['view'] : 1;

        if(!empty($data['RateID'])) {
            $RateID             = $data['RateID'];
            $OriginationRateID  = !empty($data['OriginationRateID']) ? $data['OriginationRateID'] : 0;
            $TimezonesID        = $data['TimezonesID'];
            $City               = !empty($data['City']) ? '"'.$data['City'].'"' : '""';
            $Tariff             = !empty($data['Tariff']) ? '"'.$data['Tariff'].'"' : '""';

            $rateTable = RateTable::find($RateTableID);
            $TypeVoiceCall  = RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
            $TypeDID        = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);
            if($rateTable->Type == $TypeVoiceCall) {
                $query = 'call prc_GetRateTableRatesArchiveGrid (' . $companyID . ',' . $RateTableID . ',' . $TimezonesID . ',"' . $RateID . '","' . $OriginationRateID . '",' . $view . ')';
            } else if($rateTable->Type == $TypeDID) {
                $query = 'call prc_GetRateTableDIDRatesArchiveGrid (' . $companyID . ',' . $RateTableID . ',' . $TimezonesID . ',"' . $RateID . '","' . $OriginationRateID . '",'.$City.','.$Tariff.',' . $view . ')';
            } else {
                $query = 'call prc_GetRateTablePKGRatesArchiveGrid (' . $companyID . ',' . $RateTableID . ',' . $TimezonesID . ',"' . $RateID . '",' . $view . ')';
            }
            //Log::info($query);
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";
            $response['data']       = DB::select($query);
        } else {
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";
            $response['data']       = [];
        }

        return json_encode($response);
    }

    public function search_ajax_datagrid_rates_account_service() {

        $data       = Input::all();
        $companyID  = User::get_companyID();
        $Type = isset($data['Type']) ? $data['Type'] : "";
        $Country = isset($data['Country']) ? $data['Country'] : "0";
        $City = isset($data['City']) ? $data['City'] : "";
        $Tariff = isset($data['Tariff']) ? $data['Tariff'] : "";
        $Prefix = isset($data['Prefix']) ? $data['Prefix'] : "";

        $PackageID = isset($data['PackageID']) ? $data['PackageID'] : "0";
        //Log::info("search_ajax_datagrid_rates_account_service " . print_r($data,true));

        if(!empty($data['AccessRateTable'])) {
                $query = 'call prc_getRateTablesRateForAccountService (' . $data['AccessRateTable'] .",'" .
                    $Type . "','" . $City. "','" . $Tariff. "','" . $Country . "','" . $PackageID .
                    "','" . $Prefix . "'" . ')';
            Log::info("search_ajax_datagrid_rates_account_service " . $query);
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";
            $response['data']       = DB::select($query);
            //Log::info("search_ajax_datagrid_rates_account_service " . count($response['data']));
        } else {
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";
            $response['data']       = [];
        }

        return json_encode($response);
    }

    // update rate table did rate
    public function update_rate_table_did_rate($id) {
        if ($id > 0) {
            $data = Input::all();
            $error = 0;

            $EffectiveDate = $EndDate = $OriginationRateID = $City = $Tariff = $AccessType = $OneOffCost = $MonthlyCost = $CostPerCall = $CostPerMinute = $SurchargePerCall = $SurchargePerMinute = $OutpaymentPerCall = $OutpaymentPerMinute = $Surcharges = $Chargeback = $CollectionCostAmount = $CollectionCostPercentage = $RegistrationCostPerNumber = $OneOffCostCurrency = $MonthlyCostCurrency = $CostPerCallCurrency = $CostPerMinuteCurrency = $SurchargePerCallCurrency = $SurchargePerMinuteCurrency = $OutpaymentPerCallCurrency = $OutpaymentPerMinuteCurrency = $SurchargesCurrency = $ChargebackCurrency = $CollectionCostAmountCurrency = $RegistrationCostPerNumberCurrency = 'NULL';

            if(!empty($data['updateEffectiveDate']) || !empty($data['updateCity']) || !empty($data['updateTariff']) || !empty($data['updateAccessType']) || !empty($data['updateOneOffCost']) || !empty($data['updateMonthlyCost']) || !empty($data['updateCostPerCall']) || !empty($data['updateCostPerMinute']) || !empty($data['updateSurchargePerCall']) || !empty($data['updateSurchargePerMinute']) || !empty($data['updateOutpaymentPerCall']) || !empty($data['updateOutpaymentPerMinute']) || !empty($data['updateSurcharges']) || !empty($data['updateChargeback']) || !empty($data['updateCollectionCostAmount']) || !empty($data['updateCollectionCostPercentage']) || !empty($data['updateRegistrationCostPerNumber']) || !empty($data['updateOneOffCostCurrency']) || !empty($data['updateMonthlyCostCurrency']) || !empty($data['updateCostPerCallCurrency']) || !empty($data['updateCostPerMinuteCurrency']) || !empty($data['updateSurchargePerCallCurrency']) || !empty($data['updateSurchargePerMinuteCurrency']) || !empty($data['updateOutpaymentPerCallCurrency']) || !empty($data['updateOutpaymentPerMinuteCurrency']) || !empty($data['updateSurchargesCurrency']) || !empty($data['updateChargebackCurrency']) || !empty($data['updateCollectionCostAmountCurrency']) || !empty($data['updateRegistrationCostPerNumberCurrency']) || !empty($data['updateOriginationRateID'])) {// || !empty($data['EndDate'])
                if(!empty($data['updateEffectiveDate'])) {
                    if(!empty($data['EffectiveDate'])) {
                        $EffectiveDate = "'".$data['EffectiveDate']."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateCity'])) {
                    if(!empty($data['City'])) {
                        $City = "'".$data['City']."'";
                    } else {
                        $City = "''";
                    }
                }
                if(!empty($data['updateTariff'])) {
                    if(!empty($data['Tariff'])) {
                        $Tariff = "'".$data['Tariff']."'";
                    } else {
                        $Tariff = "''";
                    }
                }

                if(!empty($data['updateAccessType'])) {
                    if(!empty($data['AccessType'])) {
                        $AccessType = "'".$data['AccessType']."'";
                    } else {
                        $AccessType = "''";
                    }
                }
                if(!empty($data['updateOneOffCost'])) {
                    if(isset($data['OneOffCost'])) {
                        $OneOffCost = $data['OneOffCost'] != '' ? "'".floatval($data['OneOffCost'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateMonthlyCost'])) {
                    if(isset($data['MonthlyCost'])) {
                        $MonthlyCost = $data['MonthlyCost'] != '' ? "'".floatval($data['MonthlyCost'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateCostPerCall'])) {
                    if(isset($data['CostPerCall'])) {
                        $CostPerCall = $data['CostPerCall'] != '' ? "'".floatval($data['CostPerCall'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateCostPerMinute'])) {
                    if(isset($data['CostPerMinute'])) {
                        $CostPerMinute = $data['CostPerMinute'] != '' ? "'".floatval($data['CostPerMinute'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateSurchargePerCall'])) {
                    if(isset($data['SurchargePerCall'])) {
                        $SurchargePerCall = $data['SurchargePerCall'] != '' ? "'".floatval($data['SurchargePerCall'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateSurchargePerMinute'])) {
                    if(isset($data['SurchargePerMinute'])) {
                        $SurchargePerMinute = $data['SurchargePerMinute'] != '' ? "'".floatval($data['SurchargePerMinute'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateOutpaymentPerCall'])) {
                    if(isset($data['OutpaymentPerCall'])) {
                        $OutpaymentPerCall = $data['OutpaymentPerCall'] != '' ? "'".floatval($data['OutpaymentPerCall'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateOutpaymentPerMinute'])) {
                    if(isset($data['OutpaymentPerMinute'])) {
                        $OutpaymentPerMinute = $data['OutpaymentPerMinute'] != '' ? "'".floatval($data['OutpaymentPerMinute'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateSurcharges'])) {
                    if(isset($data['Surcharges'])) {
                        $Surcharges = $data['Surcharges'] != '' ? "'".floatval($data['Surcharges'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateChargeback'])) {
                    if(isset($data['Chargeback'])) {
                        $Chargeback = $data['Chargeback'] != '' ? "'".floatval($data['Chargeback'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateCollectionCostAmount'])) {
                    if(isset($data['CollectionCostAmount'])) {
                        $CollectionCostAmount = $data['CollectionCostAmount'] != '' ? "'".floatval($data['CollectionCostAmount'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateCollectionCostPercentage'])) {
                    if(isset($data['CollectionCostPercentage'])) {
                        $CollectionCostPercentage = $data['CollectionCostPercentage'] != '' ? "'".floatval($data['CollectionCostPercentage'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateRegistrationCostPerNumber'])) {
                    if(isset($data['RegistrationCostPerNumber'])) {
                        $RegistrationCostPerNumber = $data['RegistrationCostPerNumber'] != '' ? "'".floatval($data['RegistrationCostPerNumber'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateOneOffCostCurrency'])) {
                    if(!empty($data['OneOffCostCurrency'])) {
                        $OneOffCostCurrency = "'".$data['OneOffCostCurrency']."'";
                    }
                }
                if(!empty($data['updateMonthlyCostCurrency'])) {
                    if(!empty($data['MonthlyCostCurrency'])) {
                        $MonthlyCostCurrency = "'".$data['MonthlyCostCurrency']."'";
                    }
                }
                if(!empty($data['updateCostPerCallCurrency'])) {
                    if(!empty($data['CostPerCallCurrency'])) {
                        $CostPerCallCurrency = "'".$data['CostPerCallCurrency']."'";
                    }
                }
                if(!empty($data['updateCostPerMinuteCurrency'])) {
                    if(!empty($data['CostPerMinuteCurrency'])) {
                        $CostPerMinuteCurrency = "'".$data['CostPerMinuteCurrency']."'";
                    }
                }
                if(!empty($data['updateSurchargePerCallCurrency'])) {
                    if(!empty($data['SurchargePerCallCurrency'])) {
                        $SurchargePerCallCurrency = "'".$data['SurchargePerCallCurrency']."'";
                    }
                }
                if(!empty($data['updateSurchargePerMinuteCurrency'])) {
                    if(!empty($data['SurchargePerMinuteCurrency'])) {
                        $SurchargePerMinuteCurrency = "'".$data['SurchargePerMinuteCurrency']."'";
                    }
                }
                if(!empty($data['updateOutpaymentPerCallCurrency'])) {
                    if(!empty($data['OutpaymentPerCallCurrency'])) {
                        $OutpaymentPerCallCurrency = "'".$data['OutpaymentPerCallCurrency']."'";
                    }
                }
                if(!empty($data['updateOutpaymentPerMinuteCurrency'])) {
                    if(!empty($data['OutpaymentPerMinuteCurrency'])) {
                        $OutpaymentPerMinuteCurrency = "'".$data['OutpaymentPerMinuteCurrency']."'";
                    }
                }
                if(!empty($data['updateSurchargesCurrency'])) {
                    if(!empty($data['SurchargesCurrency'])) {
                        $SurchargesCurrency = "'".$data['SurchargesCurrency']."'";
                    }
                }
                if(!empty($data['updateChargebackCurrency'])) {
                    if(!empty($data['ChargebackCurrency'])) {
                        $ChargebackCurrency = "'".$data['ChargebackCurrency']."'";
                    }
                }
                if(!empty($data['updateCollectionCostAmountCurrency'])) {
                    if(!empty($data['CollectionCostAmountCurrency'])) {
                        $CollectionCostAmountCurrency = "'".$data['CollectionCostAmountCurrency']."'";
                    }
                }
                if(!empty($data['updateRegistrationCostPerNumberCurrency'])) {
                    if(!empty($data['RegistrationCostPerNumberCurrency'])) {
                        $RegistrationCostPerNumberCurrency = "'".$data['RegistrationCostPerNumberCurrency']."'";
                    }
                }
                if(!empty($data['updateOriginationRateID'])) {
                    if(!empty($data['OriginationRateID'])) {
                        $OriginationRateID = "'".$data['OriginationRateID']."'";
                    }
                }
                if(isset($error) && $error==1) {
                    return Response::json(array("status" => "failed", "message" => "Please Select Checked Field Data"));
                }

            } else {
                return Response::json(array("status" => "failed", "message" => "No Rate selected to Update."));
            }

            $username = User::get_user_full_name();

            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 1; //update action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['OriginationCode']           = !empty($criteria['OriginationCode']) && $criteria['OriginationCode'] != '' ? "'" . $criteria['OriginationCode'] . "'" : 'NULL';
                $criteria['OriginationDescription']    = !empty($criteria['OriginationDescription']) && $criteria['OriginationDescription'] != '' ? "'" . $criteria['OriginationDescription'] . "'" : 'NULL';
                $criteria['Code']           = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Description']    = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'NULL';
                $criteria['City']           = !empty($criteria['City']) && $criteria['City'] != '' ? "'" . $criteria['City'] . "'" : 'NULL';
                $criteria['Tariff']         = !empty($criteria['Tariff']) && $criteria['Tariff'] != '' ? "'" . $criteria['Tariff'] . "'" : 'NULL';
                $criteria['AccessType']     = !empty($criteria['AccessType']) && $criteria['AccessType'] != '' ? "'" . $criteria['AccessType'] . "'" : 'NULL';
                $criteria['Country']        = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'NULL';
                $criteria['TimezonesID']    = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones']."'"  : 'NULL';
                $criteria['Effective']      = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['ApprovedStatus'] = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'NULL';

                $RateTableID                = $id;
                $RateTableDIDRateID         = $data['RateTableDIDRateID'];
                $OriginationRateID          = !empty($OriginationRateID) ? $OriginationRateID : 'NULL';


                if (empty($data['RateTableDIDRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                if((empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') && !empty($data['TimezonesID'])) {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if($data['ApprovedStatus'] == RateTable::RATE_STATUS_APPROVED) {
                    $query = "call prc_RateTableDIDRateUpdateDelete (" . $RateTableID . ",'" . $RateTableDIDRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $City . "," . $Tariff . "," . $AccessType . "," . $OneOffCost . "," . $MonthlyCost . "," . $CostPerCall . "," . $CostPerMinute . "," . $SurchargePerCall . "," . $SurchargePerMinute . "," . $OutpaymentPerCall . "," . $OutpaymentPerMinute . "," . $Surcharges . "," . $Chargeback . "," . $CollectionCostAmount . "," . $CollectionCostPercentage . "," . $RegistrationCostPerNumber . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $CostPerCallCurrency . "," . $CostPerMinuteCurrency . "," . $SurchargePerCallCurrency . "," . $SurchargePerMinuteCurrency . "," . $OutpaymentPerCallCurrency . "," . $OutpaymentPerMinuteCurrency . "," . $SurchargesCurrency . "," . $ChargebackCurrency . "," . $CollectionCostAmountCurrency . "," . $RegistrationCostPerNumberCurrency . "," . $criteria['Country'] . "," . $criteria['OriginationCode'] . "," . $criteria['Code'] . "," . $criteria['City'] . "," . $criteria['Tariff'] . "," . $criteria['AccessType'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                } else {
                    $query = "call prc_RateTableDIDRateAAUpdateDelete (" . $RateTableID . ",'" . $RateTableDIDRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $City . "," . $Tariff . "," . $AccessType . "," . $OneOffCost . "," . $MonthlyCost . "," . $CostPerCall . "," . $CostPerMinute . "," . $SurchargePerCall . "," . $SurchargePerMinute . "," . $OutpaymentPerCall . "," . $OutpaymentPerMinute . "," . $Surcharges . "," . $Chargeback . "," . $CollectionCostAmount . "," . $CollectionCostPercentage . "," . $RegistrationCostPerNumber . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $CostPerCallCurrency . "," . $CostPerMinuteCurrency . "," . $SurchargePerCallCurrency . "," . $SurchargePerMinuteCurrency . "," . $OutpaymentPerCallCurrency . "," . $OutpaymentPerMinuteCurrency . "," . $SurchargesCurrency . "," . $ChargebackCurrency . "," . $CollectionCostAmountCurrency . "," . $RegistrationCostPerNumberCurrency . "," . $criteria['Country'] . "," . $criteria['OriginationCode'] . "," . $criteria['Code'] . "," . $criteria['City'] . "," . $criteria['Tariff'] . "," . $criteria['AccessType'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                }

                //info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating Rate Table Rate."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        } else {
            return Response::json(array("status" => "failed", "message" => "No RateTable Found."));
        }
    }

    //delete rate table rates
    public function clear_did_rate($id) {
        if ($id > 0) {
            $data           = Input::all();//echo "<pre>";print_r($data);exit();

            $username       = User::get_user_full_name();
            $EffectiveDate = $EndDate = $OriginationRateID = $City = $Tariff = $AccessType = $OneOffCost = $MonthlyCost = $CostPerCall = $CostPerMinute = $SurchargePerCall = $SurchargePerMinute = $OutpaymentPerCall = $OutpaymentPerMinute = $Surcharges = $Chargeback = $CollectionCostAmount = $CollectionCostPercentage = $RegistrationCostPerNumber = $OneOffCostCurrency = $MonthlyCostCurrency = $CostPerCallCurrency = $CostPerMinuteCurrency = $SurchargePerCallCurrency = $SurchargePerMinuteCurrency = $OutpaymentPerCallCurrency = $OutpaymentPerMinuteCurrency = $SurchargesCurrency = $ChargebackCurrency = $CollectionCostAmountCurrency = $RegistrationCostPerNumberCurrency = 'NULL';
            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 2; //delete action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['OriginationCode']           = !empty($criteria['OriginationCode']) && $criteria['OriginationCode'] != '' ? "'" . $criteria['OriginationCode'] . "'" : 'NULL';
                $criteria['OriginationDescription']    = !empty($criteria['OriginationDescription']) && $criteria['OriginationDescription'] != '' ? "'" . $criteria['OriginationDescription'] . "'" : 'NULL';
                $criteria['Code']           = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Description']    = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'NULL';
                $criteria['City']           = !empty($criteria['City']) && $criteria['City'] != '' ? "'" . $criteria['City'] . "'" : 'NULL';
                $criteria['Tariff']         = !empty($criteria['Tariff']) && $criteria['Tariff'] != '' ? "'" . $criteria['Tariff'] . "'" : 'NULL';
                $criteria['AccessType']     = !empty($criteria['AccessType']) && $criteria['AccessType'] != '' ? "'" . $criteria['AccessType'] . "'" : 'NULL';
                $criteria['Country']        = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'NULL';
                $criteria['Effective']      = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['TimezonesID']    = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones']."'"  : 'NULL';
                $criteria['ApprovedStatus'] = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'NULL';

                $RateTableID                = $id;
                $RateTableDIDRateID         = $data['RateTableDIDRateID'];

                if (empty($data['RateTableDIDRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                if((empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') && !empty($data['TimezonesID'])) {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if($data['ApprovedStatus'] == RateTable::RATE_STATUS_APPROVED) {
                    $success_message = 'Rates Successfully added for approval to Delete';
                    $query = "call prc_RateTableDIDRateUpdateDelete (" . $RateTableID . ",'" . $RateTableDIDRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $City . "," . $Tariff . "," . $AccessType . "," . $OneOffCost . "," . $MonthlyCost . "," . $CostPerCall . "," . $CostPerMinute . "," . $SurchargePerCall . "," . $SurchargePerMinute . "," . $OutpaymentPerCall . "," . $OutpaymentPerMinute . "," . $Surcharges . "," . $Chargeback . "," . $CollectionCostAmount . "," . $CollectionCostPercentage . "," . $RegistrationCostPerNumber . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $CostPerCallCurrency . "," . $CostPerMinuteCurrency . "," . $SurchargePerCallCurrency . "," . $SurchargePerMinuteCurrency . "," . $OutpaymentPerCallCurrency . "," . $OutpaymentPerMinuteCurrency . "," . $SurchargesCurrency . "," . $ChargebackCurrency . "," . $CollectionCostAmountCurrency . "," . $RegistrationCostPerNumberCurrency . "," . $criteria['Country'] . "," . $criteria['OriginationCode'] . "," . $criteria['Code'] . "," . $criteria['City'] . "," . $criteria['Tariff'] . "," . $criteria['AccessType'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                } else {
                    $success_message = 'Rates Successfully Deleted';
                    $query = "call prc_RateTableDIDRateAAUpdateDelete (" . $RateTableID . ",'" . $RateTableDIDRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $City . "," . $Tariff . "," . $AccessType . "," . $OneOffCost . "," . $MonthlyCost . "," . $CostPerCall . "," . $CostPerMinute . "," . $SurchargePerCall . "," . $SurchargePerMinute . "," . $OutpaymentPerCall . "," . $OutpaymentPerMinute . "," . $Surcharges . "," . $Chargeback . "," . $CollectionCostAmount . "," . $CollectionCostPercentage . "," . $RegistrationCostPerNumber . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $CostPerCallCurrency . "," . $CostPerMinuteCurrency . "," . $SurchargePerCallCurrency . "," . $SurchargePerMinuteCurrency . "," . $OutpaymentPerCallCurrency . "," . $OutpaymentPerMinuteCurrency . "," . $SurchargesCurrency . "," . $ChargebackCurrency . "," . $CollectionCostAmountCurrency . "," . $RegistrationCostPerNumberCurrency . "," . $criteria['Country'] . "," . $criteria['OriginationCode'] . "," . $criteria['Code'] . "," . $criteria['City'] . "," . $criteria['Tariff'] . "," . $criteria['AccessType'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                }

                //Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => $success_message));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Rates."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        }
    }

    // update rate table package rate
    public function update_rate_table_pkg_rate($id) {
        if ($id > 0) {
            $data = Input::all();
            $error = 0;

            $EffectiveDate = $EndDate = $OneOffCost = $MonthlyCost = $PackageCostPerMinute = $RecordingCostPerMinute = $OneOffCostCurrency = $MonthlyCostCurrency = $PackageCostPerMinuteCurrency = $RecordingCostPerMinuteCurrency = 'NULL';

            if(!empty($data['updateEffectiveDate']) || !empty($data['updateOneOffCost']) || !empty($data['updateMonthlyCost']) || !empty($data['updatePackageCostPerMinute']) || !empty($data['updateRecordingCostPerMinute']) || !empty($data['updateOneOffCostCurrency']) || !empty($data['updateMonthlyCostCurrency']) || !empty($data['updatePackageCostPerMinuteCurrency']) || !empty($data['updateRecordingCostPerMinuteCurrency'])) {// || !empty($data['EndDate'])
                if(!empty($data['updateEffectiveDate'])) {
                    if(!empty($data['EffectiveDate'])) {
                        $EffectiveDate = "'".$data['EffectiveDate']."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateOneOffCost'])) {
                    if(isset($data['OneOffCost'])) {
                        $OneOffCost = $data['OneOffCost'] != '' ? "'".floatval($data['OneOffCost'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateMonthlyCost'])) {
                    if(isset($data['MonthlyCost'])) {
                        $MonthlyCost = $data['MonthlyCost'] != '' ? "'".floatval($data['MonthlyCost'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updatePackageCostPerMinute'])) {
                    if(isset($data['PackageCostPerMinute'])) {
                        $PackageCostPerMinute = $data['PackageCostPerMinute'] != '' ? "'".floatval($data['PackageCostPerMinute'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateRecordingCostPerMinute'])) {
                    if(isset($data['RecordingCostPerMinute'])) {
                        $RecordingCostPerMinute = $data['RecordingCostPerMinute'] != '' ? "'".floatval($data['RecordingCostPerMinute'])."'" : "'NULL'";
                    }
                }
                if(!empty($data['updateOneOffCostCurrency'])) {
                    if(!empty($data['OneOffCostCurrency'])) {
                        $OneOffCostCurrency = "'".$data['OneOffCostCurrency']."'";
                    }
                }
                if(!empty($data['updateMonthlyCostCurrency'])) {
                    if(!empty($data['MonthlyCostCurrency'])) {
                        $MonthlyCostCurrency = "'".$data['MonthlyCostCurrency']."'";
                    }
                }
                if(!empty($data['updatePackageCostPerMinuteCurrency'])) {
                    if(!empty($data['PackageCostPerMinuteCurrency'])) {
                        $PackageCostPerMinuteCurrency = "'".$data['PackageCostPerMinuteCurrency']."'";
                    }
                }
                if(!empty($data['updateRecordingCostPerMinuteCurrency'])) {
                    if(!empty($data['RecordingCostPerMinuteCurrency'])) {
                        $RecordingCostPerMinuteCurrency = "'".$data['RecordingCostPerMinuteCurrency']."'";
                    }
                }
                if(isset($error) && $error==1) {
                    return Response::json(array("status" => "failed", "message" => "Please Select Checked Field Data"));
                }

            } else {
                return Response::json(array("status" => "failed", "message" => "No Rate selected to Update."));
            }

            $username = User::get_user_full_name();

            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 1; //update action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['Code']           = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Description']    = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'NULL';
                $criteria['Effective']      = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['TimezonesID']    = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones']."'"  : 'NULL';
                $criteria['ApprovedStatus'] = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'NULL';

                $RateTableID                = $id;
                $RateTablePKGRateID         = $data['RateTablePKGRateID'];

                if((empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') && !empty($data['TimezonesID'])) {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTablePKGRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                if($data['ApprovedStatus'] == RateTable::RATE_STATUS_APPROVED) {
                    $query = "call prc_RateTablePKGRateUpdateDelete (" . $RateTableID . ",'" . $RateTablePKGRateID . "'," . $EffectiveDate . "," . $EndDate . "," . $OneOffCost . "," . $MonthlyCost . "," . $PackageCostPerMinute . "," . $RecordingCostPerMinute . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $PackageCostPerMinuteCurrency . "," . $RecordingCostPerMinuteCurrency . "," . $criteria['Code'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                } else {
                    $query = "call prc_RateTablePKGRateAAUpdateDelete (" . $RateTableID . ",'" . $RateTablePKGRateID . "'," . $EffectiveDate . "," . $EndDate . "," . $OneOffCost . "," . $MonthlyCost . "," . $PackageCostPerMinute . "," . $RecordingCostPerMinute . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $PackageCostPerMinuteCurrency . "," . $RecordingCostPerMinuteCurrency . "," . $criteria['Code'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                }

                //Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating Rate Table Rate."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        } else {
            return Response::json(array("status" => "failed", "message" => "No RateTable Found."));
        }
    }

    //delete rate table rates
    public function clear_pkg_rate($id) {
        if ($id > 0) {
            $data       = Input::all();


            $username       = User::get_user_full_name();
            $EffectiveDate = $EndDate = $OneOffCost = $MonthlyCost = $PackageCostPerMinute = $RecordingCostPerMinute = $OneOffCostCurrency = $MonthlyCostCurrency = $PackageCostPerMinuteCurrency = $RecordingCostPerMinuteCurrency = 'NULL';


            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 2; //delete action

                $criteria   = json_decode($data['criteria'], true);


                $criteria['Code']           = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Effective']      = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['TimezonesID']            = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones']."'"  : 'NULL';
                $criteria['ApprovedStatus'] = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'NULL';

                $RateTableID                = $id;
                $RateTablePKGRateID         = $data['RateTablePKGRateID'];

                if (empty($data['RateTablePKGRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                if((empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') && !empty($data['TimezonesID'])) {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if($data['ApprovedStatus'] == RateTable::RATE_STATUS_APPROVED) {
                    $success_message = 'Rates Successfully added for approval to Delete';
                    $query = "call prc_RateTablePKGRateUpdateDelete (" . $RateTableID . ",'" . $RateTablePKGRateID . "'," . $EffectiveDate . "," . $EndDate . "," . $OneOffCost . "," . $MonthlyCost . "," . $PackageCostPerMinute . "," . $RecordingCostPerMinute . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $PackageCostPerMinuteCurrency . "," . $RecordingCostPerMinuteCurrency . "," . $criteria['Code'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                } else {
                    $success_message = 'Rates Successfully Deleted';
                    $query = "call prc_RateTablePKGRateAAUpdateDelete (" . $RateTableID . ",'" . $RateTablePKGRateID . "'," . $EffectiveDate . "," . $EndDate . "," . $OneOffCost . "," . $MonthlyCost . "," . $PackageCostPerMinute . "," . $RecordingCostPerMinute . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $PackageCostPerMinuteCurrency . "," . $RecordingCostPerMinuteCurrency . "," . $criteria['Code'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                }

                //Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => $success_message));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Rates."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        }
    }

}