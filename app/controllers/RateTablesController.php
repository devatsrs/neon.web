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
            ->select(['tblRateTable.Type','tblRateTable.AppliedTo','tblRateTable.RateTableName','tblCurrency.Code', 'tblTrunk.Trunk as trunkName', 'tblDIDCategory.CategoryName as CategoryName','tblCodeDeck.CodeDeckName','tblRateTable.updated_at','tblRateTable.RateTableId', 'tblRateTable.TrunkID', 'tblRateTable.CurrencyID', 'tblRateTable.RoundChargedAmount', 'tblRateTable.MinimumCallCharge', 'tblRateTable.DIDCategoryID', 'tblCustomerTrunk.CustomerTrunkID', 'tblVendorConnection.VendorConnectionID'])
            ->where("tblRateTable.CompanyId",$CompanyID);
        //$rate_tables = RateTable::join('tblCurrency', 'tblCurrency.CurrencyId', '=', 'tblRateTable.CurrencyId')->where(["tblRateTable.CompanyId" => $CompanyID])->select(["tblRateTable.RateTableName","Code","tblRateTable.updated_at", "tblRateTable.RateTableId"]);

        $data = Input::all();
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

        $data['iDisplayStart'] +=1;
        $data['Country']                = !empty($data['Country']) && $data['Country'] != 'All' ? $data['Country'] : 'null';
        $data['Code']                   = $data['Code'] != '' ? "'".$data['Code']."'" : 'null';
        $data['Description']            = !empty($data['Description']) ? "'".$data['Description']."'" : 'null';
        $data['OriginationCode']        = !empty($data['OriginationCode']) ? "'".$data['OriginationCode']."'" : 'null';
        $data['OriginationDescription'] = !empty($data['OriginationDescription']) ? "'".$data['OriginationDescription']."'" : 'null';
        $data['RoutingCategoryID']      = !empty($data['RoutingCategoryID']) ? "'".$data['RoutingCategoryID']."'" : 'null';
        $data['Preference']             = !empty($data['Preference']) ? "'".$data['Preference']."'" : 'null';
        $data['Blocked']                = isset($data['Blocked']) && $data['Blocked'] != '' ? "'".$data['Blocked']."'" : 'null';
        $data['ApprovedStatus']         = isset($data['ApprovedStatus']) && $data['ApprovedStatus'] != '' ? "'".$data['ApprovedStatus']."'" : 'null';
        $data['Timezones']              = isset($data['Timezones']) && $data['Timezones'] != '' ? "'".$data['Timezones']."'" : "''";
        $data['CityTariff']             = !empty($data['CityTariff']) ? "'".$data['CityTariff']."'" : 'null';

        $view = isset($data['view']) && $data['view'] == 2 ? $data['view'] : 1;
        $TypeVoiceCall  = RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
        $TypeDID        = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);

        $rateTable = RateTable::find($id);
        if($rateTable->Type == $TypeVoiceCall) { // voice call
            $columns = array('RateTableRateID','OriginationCode','OriginationDescription','Code','Description','Interval1','IntervalN','ConnectionFee','PreviousRate','Rate','RateN','EffectiveDate','EndDate','updated_at','ModifiedBy','RateTableRateID','OriginationRateID','RateID','RoutingCategoryID','RoutingCategoryName','Preference','Blocked','ApprovedStatus','ApprovedBy','ApprovedDate');
            $sort_column = $columns[$data['iSortCol_0']];

            if(!empty($data['DiscontinuedRates'])) {

                $query = "call prc_getDiscontinuedRateTableRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Country'] . ",".$data['OriginationCode'].",".$data['OriginationDescription']."," . $data['Code'] . "," . $data['Description'] . ",".$data['RoutingCategoryID'].",".$data['Preference'].",".$data['Blocked'].",".$data['ApprovedStatus'].",".$view."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "',0)";
            } else {
                //dd('hi');
                $query = "call prc_GetRateTableRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['RoutingCategoryID'].",".$data['Preference'].",".$data['Blocked'].",".$data['ApprovedStatus'].",".$view.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
            }
        } else if($rateTable->Type == $TypeDID) { // did
            $columns = array('RateTableRateID','Country','OriginationCode','OriginationDescription','Code','Description','CityTariff','OneOffCost','MonthlyCost','CostPerCall','CostPerMinute','SurchargePerCall','SurchargePerMinute','OutpaymentPerCall','OutpaymentPerMinute','Surcharges','Chargeback','CollectionCostAmount','CollectionCostPercentage','RegistrationCostPerNumber','EffectiveDate','EndDate','updated_at','ModifiedBy','RateTableDIDRateID','OriginationRateID','RateID','ApprovedStatus','ApprovedBy','ApprovedDate');
            $sort_column = $columns[$data['iSortCol_0']];
            if(!empty($data['DiscontinuedRates'])) {
                $query = "call prc_getDiscontinuedRateTableDIDRateGrid (" . $companyID . "," . $id . ",'".$data['Timezones']."'," . $data['Country'] . ",".$data['OriginationCode'].",".$data['OriginationDescription']."," . $data['Code'] . "," . $data['Description'] . "," . $data['CityTariff'] . ",".$data['ApprovedStatus']."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "',0)";
            } else {
                $query = "call prc_GetRateTableDIDRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description'].",".$data['CityTariff'].",'".$data['Effective']."',".$data['ApprovedStatus'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
            }
        } else { // package
            $columns = array('RateTableRateID','Code','Description','OneOffCost','MonthlyCost','PackageCostPerMinute','RecordingCostPerMinute','EffectiveDate','EndDate','updated_at','ModifiedBy','RateTablePKGRateID','RateID','ApprovedStatus','ApprovedBy','ApprovedDate');
            $sort_column = $columns[$data['iSortCol_0']];
            if(!empty($data['DiscontinuedRates'])) {
                $query = "call prc_getDiscontinuedRateTablePKGRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Code'] . ",".$data['ApprovedStatus']."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "',0)";
            } else {
                $query = "call prc_GetRateTablePKGRate (".$companyID.",".$id.",".$data['Timezones'].",".$data['Code'].",'".$data['Effective']."',".$data['ApprovedStatus'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
            }
        }
        Log::info($query);

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
            $codedecks = BaseCodeDeck::where(["CompanyID" => $companyID])->lists("CodeDeckName", "CodeDeckId");
            $codedecks = array(""=>"Select Codedeck")+$codedecks;
            $RateGenerators = array(""=>"Select rate generator")+$RateGenerators;
            $currencylist = Currency::getCurrencyDropdownIDList();
            $DIDCategory = DIDCategory::getCategoryDropdownIDList($companyID);
            $RateTypes   = RateType::getRateTypeDropDownList();
            return View::make('ratetables.index', compact('trunks','RateGenerators','codedecks','trunk_keys','currencylist','DIDCategory','RateTypes'));
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
        $message = ['CurrencyID.required'=>'Currency field is required',
                    //'TrunkID.required'=>'Trunk field is required',
                    'CodedeckId.required'=>'Codedeck field is required',
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

        if($rateTable->Type == $TypeVoiceCall) {
            return View::make('ratetables.edit', compact('id', 'countries','trunkID','codes','isBandTable','code','rateTable','Timezones','RoutingCategories','RateApprovalProcess','TypeVoiceCall','ROUTING_PROFILE','CurrencyDropDown','Timezone'));
        } else if($rateTable->Type == $TypeDID) {
            return View::make('ratetables.edit_did', compact('id', 'countries','trunkID','codes','isBandTable','code','rateTable','Timezones','RateApprovalProcess','TypeVoiceCall','CurrencyDropDown','Timezone'));
        } else {
            return View::make('ratetables.edit_pkg', compact('id', 'countries','trunkID','codes','isBandTable','code','rateTable','Timezones','RateApprovalProcess','TypeVoiceCall','CurrencyDropDown','Timezone'));
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
            $EffectiveDate  = $EndDate = $Rate = $RateN = $Interval1 = $IntervalN = $ConnectionFee = $OriginationRateID = $RoutingCategoryID = $Preference = $Blocked = $RateCurrency = $ConnectionFeeCurrency = 'null';

            $Timezone = DB::table('tblRateTableRate')->select('TimezonesID')->where('RateTableRateID',$data['RateTableRateID'])->first();
            $Timezone = $Timezone->TimezonesID;
            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 2; //delete action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['OriginationCode']        = !empty($criteria['OriginationCode']) && $criteria['OriginationCode'] != '' ? "'" . $criteria['OriginationCode'] . "'" : 'NULL';
                $criteria['OriginationDescription'] = !empty($criteria['OriginationDescription']) && $criteria['OriginationDescription'] != '' ? "'" . $criteria['OriginationDescription'] . "'" : 'NULL';
                $criteria['Code']                   = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'null';
                $criteria['Description']            = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'null';
                $criteria['Country']                = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'null';
                $criteria['Effective']              = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'null';
                $criteria['TimezonesID']            = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones'] . "'" : 'NULL';
                $criteria['RoutingCategoryID']      = !empty($criteria['RoutingCategoryID']) && $criteria['RoutingCategoryID'] != '' ? "'" . $criteria['RoutingCategoryID'] . "'" : 'NULL';
                $criteria['Preference']             = !empty($criteria['Preference']) ? "'".$criteria['Preference']."'" : 'null';
                $criteria['Blocked']                = isset($criteria['Blocked']) && $criteria['Blocked'] != '' ? "'".$criteria['Blocked']."'" : 'null';
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'null';

                $RateTableID                = $id;
                $RateTableRateID            = $data['RateTableRateID'];

                if(empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTableRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTableRateUpdateDelete (" . $RateTableID . ",'" . $RateTableRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $Rate . "," . $RateN . "," . $Interval1 . "," . $IntervalN . "," . $ConnectionFee . "," . $RoutingCategoryID . "," . $Preference . "," . $Blocked . "," . $RateCurrency . "," . $ConnectionFeeCurrency . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $Timezone . "," . $criteria['RoutingCategoryID'] . "," . $criteria['Preference'] . "," . $criteria['Blocked'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Deleted"));
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

            $EffectiveDate = $EndDate = $Rate = $RateN = $Interval1 = $IntervalN = $ConnectionFee = $OriginationRateID = $RoutingCategoryID = $Preference = $Blocked = $RateCurrency = $ConnectionFeeCurrency = 'null';

            if(!empty($data['updateEffectiveDate']) || !empty($data['updateRate']) || !empty($data['updateRateN']) || !empty($data['updateInterval1']) || !empty($data['updateIntervalN']) || !empty($data['updateConnectionFee']) || !empty($data['updateOriginationRateID']) || !empty($data['updateRoutingCategoryID']) || !empty($data['updatePreference']) || !empty($data['updateBlocked']) || !empty($data['RateCurrency']) || !empty($data['ConnectionFeeCurrency'])) {// || !empty($data['EndDate'])
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
                if(!empty($data['updateInterval1'])) {
                    if(!empty($data['Interval1'])) {
                        $Interval1 = "'".$data['Interval1']."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateIntervalN'])) {
                    if(!empty($data['IntervalN'])) {
                        $IntervalN = "'".$data['IntervalN']."'";
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
                $criteria['TimezonesID']            = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones'] . "'" : 'NULL';
                $criteria['RoutingCategoryID']      = !empty($criteria['RoutingCategoryID']) && $criteria['RoutingCategoryID'] != '' ? "'" . $criteria['RoutingCategoryID'] . "'" : 'NULL';
                $criteria['Preference']             = !empty($criteria['Preference']) ? "'".$criteria['Preference']."'" : 'null';
                $criteria['Blocked']                = isset($criteria['Blocked']) && $criteria['Blocked'] != '' ? "'".$criteria['Blocked']."'" : 'null';
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'null';

                $RateTableID                = $id;
                $RateTableRateID            = $data['RateTableRateID'];
                $OriginationRateID          = !empty($OriginationRateID) ? $OriginationRateID : 'NULL';
                $RoutingCategoryID          = !empty($RoutingCategoryID) ? $RoutingCategoryID : 'NULL';

                if(empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTableRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }
                $Timezone = DB::table('tblRateTableRate')->select('TimezonesID')->where('RateTableRateID',$data['RateTableRateID'])->first();
                $Timezone = $Timezone->TimezonesID;

                $query = "call prc_RateTableRateUpdateDelete (" . $RateTableID . ",'" . $RateTableRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $Rate . "," . $RateN . "," . $Interval1 . "," . $IntervalN . "," . $ConnectionFee . "," . $RoutingCategoryID . "," . $Preference . "," . $Blocked . "," . $RateCurrency . "," . $ConnectionFeeCurrency . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $Timezone . "," . $criteria['RoutingCategoryID'] . "," . $criteria['Preference'] . "," . $criteria['Blocked'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
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

            $Timezone = DB::table('tblRateTableRate')->select('TimezonesID')->where('RateTableRateID',$data['RateTableRateID'])->first();
            $Timezone = $Timezone->TimezonesID;

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
                $criteria['TimezonesID']            = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones'] . "'" : 'NULL';
                $criteria['RoutingCategoryID']      = !empty($criteria['RoutingCategoryID']) && $criteria['RoutingCategoryID'] != '' ? "'" . $criteria['RoutingCategoryID'] . "'" : 'NULL';
                $criteria['Preference']             = !empty($criteria['Preference']) ? "'".$criteria['Preference']."'" : 'null';
                $criteria['Blocked']                = isset($criteria['Blocked']) && $criteria['Blocked'] != '' ? "'".$criteria['Blocked']."'" : 'null';
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'null';

                $RateTableID                = $id;
                $RateTableRateID            = $data['RateTableRateID'];

                if(empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTableRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTableRateApprove (" . $RateTableID . ",'" . $RateTableRateID . "','" . $data['ApprovedStatus'] . "'," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $Timezone . "," . $criteria['RoutingCategoryID'] . "," . $criteria['Preference'] . "," . $criteria['Blocked'] . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
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

            $Timezone = DB::table('tblRateTableDIDRate')->select('TimezonesID')->where('RateTableDIDRateID',$data['RateTableDIDRateID'])->first();
            $Timezone = $Timezone->TimezonesID;

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
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'null';

                $RateTableID                = $id;
                $RateTableDIDRateID         = $data['RateTableDIDRateID'];


                if (empty($data['RateTableDIDRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTableDIDRateApprove (" . $RateTableID . ",'" . $RateTableDIDRateID . "','" . $data['ApprovedStatus'] . "'," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $Timezone . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
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
            $Timezone = DB::table('tblRateTablePKGRate')->select('TimezonesID')->where('RateTablePKGRateID',$data['RateTablePKGRateID'])->first();
            $Timezone = $Timezone->TimezonesID;
            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 1; //update action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['Code']                   = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Effective']              = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['ApprovedStatus']         = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'null';
                $RateTableID                = $id;
                $RateTablePKGRateID         = $data['RateTablePKGRateID'];
                if (empty($data['RateTablePKGRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTablePKGRateApprove (" . $RateTableID . ",'" . $RateTablePKGRateID . "','" . $data['ApprovedStatus'] . "'," . $criteria['Code'] . "," . $criteria['Effective'] . "," . $Timezone . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
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
            $rate_tables = RateTable::
            join('tblCurrency','tblCurrency.CurrencyId','=','tblRateTable.CurrencyId')
                ->join('tblCodeDeck','tblCodeDeck.CodeDeckId','=','tblRateTable.CodeDeckId')
                ->select(['tblRateTable.RateTableName','tblCurrency.Code as Currency Code','tblCodeDeck.CodeDeckName'])
                ->where("tblRateTable.CompanyId",$CompanyID);
            //$rate_tables = RateTable::join('tblCurrency', 'tblCurrency.CurrencyId', '=', 'tblRateTable.CurrencyId')->where(["tblRateTable.CompanyId" => $CompanyID])->select(["tblRateTable.RateTableName","Code","tblRateTable.updated_at", "tblRateTable.RateTableId"]);
            $data = Input::all();
            if($data['TrunkID']){
                $rate_tables = $rate_tables->where('tblRateTable.TrunkID',$data['TrunkID']);
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

        $RateTableName = RateTable::find($id)->RateTableName;

        $view = isset($data['view']) && $data['view'] == 2 ? $data['view'] : 1;
        $data['Country']                = !empty($data['Country']) && $data['Country'] != 'All' ? $data['Country'] : 'null';
        $data['Code']                   = $data['Code'] != '' ? "'".$data['Code']."'" : 'null';
        $data['Description']            = !empty($data['Description']) ? "'".$data['Description']."'" : 'null';
        $data['OriginationCode']        = !empty($data['OriginationCode']) ? "'".$data['OriginationCode']."'" : 'null';
        $data['OriginationDescription'] = !empty($data['OriginationDescription']) ? "'".$data['OriginationDescription']."'" : 'null';
        $data['RoutingCategoryID']      = !empty($data['RoutingCategoryID']) ? "'".$data['RoutingCategoryID']."'" : 'null';
        $data['Preference']             = !empty($data['Preference']) ? "'".$data['Preference']."'" : 'null';
        $data['Blocked']                = isset($data['Blocked']) && $data['Blocked'] != '' ? "'".$data['Blocked']."'" : 'null';
        $data['ApprovedStatus']         = isset($data['ApprovedStatus']) && $data['ApprovedStatus'] != '' ? "'".$data['ApprovedStatus']."'" : 'null';
        $data['Timezones']              = isset($data['Timezones']) && $data['Timezones'] != '' ? "'".$data['Timezones']."'" : 0;
        $data['CityTariff']             = !empty($data['CityTariff']) ? "'".$data['CityTariff']."'" : 'null';
        $data['ratetablepageview']      = !empty($data['ratetablepageview']) && $data['ratetablepageview']=='AdvanceView' ? 1 : 0;
        $data['isExport']               = '1'.$data['ratetablepageview'];

        $rateTable = RateTable::find($id);
        $TypeVoiceCall  = RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
        $TypeDID        = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);

        if($rateTable->Type == $TypeVoiceCall) { // voice call
            if(!empty($data['DiscontinuedRates'])) {
                $query = " call prc_getDiscontinuedRateTableRateGrid (".$companyID.",".$id.",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description'].",".$data['RoutingCategoryID'].",".$data['Preference'].",".$data['Blocked'].",".$data['ApprovedStatus'].",".$view.",null,null,null,null,".$data['isExport'].")";
            } else {
                $query = " call prc_GetRateTableRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['RoutingCategoryID'].",".$data['Preference'].",".'1'.",".$data['ApprovedStatus'].",".$view.",null,null,null,null,".$data['isExport'].")";
            }
        } else if($rateTable->Type == $TypeDID) { // did
            if(!empty($data['DiscontinuedRates'])) {
                $query = "call prc_getDiscontinuedRateTableDIDRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Country'] . ",".$data['OriginationCode'].",".$data['OriginationDescription']."," . $data['Code'] . "," . $data['Description'] . "," . $data['CityTariff'] . "," . $data['ApprovedStatus'] . ",null,null,null,null,".$data['isExport'].")";
            } else {
                $query = "call prc_GetRateTableDIDRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['OriginationCode'].",".$data['OriginationDescription'].",".$data['Code'].",".$data['Description']."," . $data['CityTariff'] . ",'".$data['Effective']."',".$data['ApprovedStatus'].",null,null,null,null,".$data['isExport'].")";
            }
        } else { // package
            $columns = array('RateTableRateID','Code','Description','OneOffCost','MonthlyCost','PackageCostPerMinute','RecordingCostPerMinute','EffectiveDate','EndDate','updated_at','ModifiedBy','RateTablePKGRateID','RateID','ApprovedStatus','ApprovedBy','ApprovedDate');
            $sort_column = $columns[$data['iSortCol_0']];
            if(!empty($data['DiscontinuedRates'])) {
                $query = "call prc_getDiscontinuedRateTablePKGRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Code'] . ",".$data['ApprovedStatus'].",null,null,null,null,1)";
            } else {
                $query = "call prc_GetRateTablePKGRate (".$companyID.",".$id.",".$data['Timezones'].",".$data['Code'].",'".$data['Effective']."',".$data['ApprovedStatus'].",null,null,null,null,1)";
            }
        }
        Log::info($query);
        DB::setFetchMode( PDO::FETCH_ASSOC );
        $rate_table_rates  = DB::select($query);
        DB::setFetchMode( Config::get('database.fetch'));

        $RateTableName = str_replace( '\/','-',$RateTableName);

        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/'.$RateTableName . ' - Rates Table Customer Rates.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($rate_table_rates);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/'.$RateTableName . ' - Rates Table Customer Rates.xls';
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

        $data['RateTableId'] = $id;

        $RateTableRate = array();
        $RateTableRate['RateTableId']       = $id;
        $RateTableRate['RateID']            = $data['RateID'];
        $RateTableRate['OriginationRateID'] = !empty($data['OriginationRateID']) ? $data['OriginationRateID'] : 0;
        $RateTableRate['EffectiveDate']     = $data['EffectiveDate'];
        $RateTableRate['EndDate']           = !empty($data['EndDate']) ? $data['EndDate'] : null;
        $RateTableRate['TimezonesID']       = $data['TimezonesID'];
        $RateApprovalProcess = CompanySetting::getKeyVal('RateApprovalProcess');
        $TypeVoiceCall  = RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
        $TypeDID        = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);

        if($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR) {
            $RateTableRate['ApprovedStatus']    = 0;
        } else {
            $RateTableRate['ApprovedStatus']    = 1;
        }

        if($rateTable->Type == $TypeVoiceCall) {
            $rules                          = RateTableRate::$rules;
            $rules['RateID']                = 'required|unique:tblRateTableRate,RateID,NULL,RateTableRateId,RateTableId,'.$id.',TimezonesID,'.$RateTableRate['TimezonesID'].',EffectiveDate,'.$RateTableRate['EffectiveDate'].',OriginationRateID,'.$RateTableRate['OriginationRateID'];
            //$rules['OriginationRateID']   = 'unique:'.$table.',OriginationRateID,NULL,'.$col_id.',RateTableId,'.$id.',EffectiveDate,'.$data['EffectiveDate'].',RateID,'.$data['RateID'];
            $message['RateID.unique']       = 'This combination of Origination Rate and Destination Rate on given Effective Date is already exist!';
        } else if($rateTable->Type == $TypeDID) {
            $rules                          = RateTableDIDRate::$rules;
            $message                        = RateTableDIDRate::$message;
            $RateTableRate['CityTariff']    = !empty($data['CityTariff']) ? $data['CityTariff'] : '';
            $rules['RateID']                = 'required|unique:tblRateTableDIDRate,RateID,NULL,RateTableDIDRateID,RateTableId,'.$id.',TimezonesID,'.$RateTableRate['TimezonesID'].',EffectiveDate,'.$RateTableRate['EffectiveDate'].',OriginationRateID,'.$RateTableRate['OriginationRateID'].',CityTariff,'.$RateTableRate['CityTariff'];
            $message['RateID.unique']       = 'This combination of Origination Rate and Destination Rate on given Effective Date is already exist!';
        } else {
            $rules                          = RateTablePKGRate::$rules;
            $message                        = RateTablePKGRate::$message;
            $rules['RateID']                = 'required|unique:tblRateTablePKGRate,RateID,NULL,RateTablePKGRateID,RateTableId,'.$id.',TimezonesID,'.$RateTableRate['TimezonesID'].',EffectiveDate,'.$RateTableRate['EffectiveDate'];
            $message['RateID.unique']       = 'This Package Name on given Effective Date is already exist!';
        }
        $validator                          = Validator::make($data, $rules, $message);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if($rateTable->Type == $TypeVoiceCall) {
            $RateTableRate['Rate']                  = $data['Rate'];
            $RateTableRate['RateN']                 = !empty($data['RateN']) ? $data['RateN'] : $data['Rate'];
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

            $Rate = RateTableRate::insert($RateTableRate);
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

            $Rate = RateTableDIDRate::insert($RateTableRate);
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

            $Rate = RateTablePKGRate::insert($RateTableRate);
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
            $CityTariff         = !empty($data['CityTariff']) ? '"'.$data['CityTariff'].'"' : '""';

            $rateTable = RateTable::find($RateTableID);
            $TypeVoiceCall  = RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
            $TypeDID        = RateType::getRateTypeIDBySlug(RateType::SLUG_DID);
            if($rateTable->Type == $TypeVoiceCall) {
                $query = 'call prc_GetRateTableRatesArchiveGrid (' . $companyID . ',' . $RateTableID . ',' . $TimezonesID . ',"' . $RateID . '","' . $OriginationRateID . '",' . $view . ')';
            } else if($rateTable->Type == $TypeDID) {
                $query = 'call prc_GetRateTableDIDRatesArchiveGrid (' . $companyID . ',' . $RateTableID . ',' . $TimezonesID . ',"' . $RateID . '","' . $OriginationRateID . '",'.$CityTariff.',' . $view . ')';
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

    // update rate table did rate
    public function update_rate_table_did_rate($id) {
        if ($id > 0) {
            $data = Input::all();
            $error = 0;

            $EffectiveDate = $EndDate = $OriginationRateID = $CityTariff = $OneOffCost = $MonthlyCost = $CostPerCall = $CostPerMinute = $SurchargePerCall = $SurchargePerMinute = $OutpaymentPerCall = $OutpaymentPerMinute = $Surcharges = $Chargeback = $CollectionCostAmount = $CollectionCostPercentage = $RegistrationCostPerNumber = $OneOffCostCurrency = $MonthlyCostCurrency = $CostPerCallCurrency = $CostPerMinuteCurrency = $SurchargePerCallCurrency = $SurchargePerMinuteCurrency = $OutpaymentPerCallCurrency = $OutpaymentPerMinuteCurrency = $SurchargesCurrency = $ChargebackCurrency = $CollectionCostAmountCurrency = $RegistrationCostPerNumberCurrency = 'null';

            if(!empty($data['updateEffectiveDate']) || !empty($data['updateCityTariff']) || !empty($data['updateOneOffCost']) || !empty($data['updateMonthlyCost']) || !empty($data['updateCostPerCall']) || !empty($data['updateCostPerMinute']) || !empty($data['updateSurchargePerCall']) || !empty($data['updateSurchargePerMinute']) || !empty($data['updateOutpaymentPerCall']) || !empty($data['updateOutpaymentPerMinute']) || !empty($data['updateSurcharges']) || !empty($data['updateChargeback']) || !empty($data['updateCollectionCostAmount']) || !empty($data['updateCollectionCostPercentage']) || !empty($data['updateRegistrationCostPerNumber']) || !empty($data['updateOneOffCostCurrency']) || !empty($data['updateMonthlyCostCurrency']) || !empty($data['updateCostPerCallCurrency']) || !empty($data['updateCostPerMinuteCurrency']) || !empty($data['updateSurchargePerCallCurrency']) || !empty($data['updateSurchargePerMinuteCurrency']) || !empty($data['updateOutpaymentPerCallCurrency']) || !empty($data['updateOutpaymentPerMinuteCurrency']) || !empty($data['updateSurchargesCurrency']) || !empty($data['updateChargebackCurrency']) || !empty($data['updateCollectionCostAmountCurrency']) || !empty($data['updateRegistrationCostPerNumberCurrency']) || !empty($data['updateOriginationRateID'])) {// || !empty($data['EndDate'])
                if(!empty($data['updateEffectiveDate'])) {
                    if(!empty($data['EffectiveDate'])) {
                        $EffectiveDate = "'".$data['EffectiveDate']."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateCityTariff'])) {
                    if(!empty($data['CityTariff'])) {
                        $CityTariff = "'".$data['CityTariff']."'";
                    } else {
                        $CityTariff = "''";
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
                $criteria['CityTariff']     = !empty($criteria['CityTariff']) && $criteria['CityTariff'] != '' ? "'" . $criteria['CityTariff'] . "'" : 'NULL';
                $criteria['Country']        = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'NULL';
                $criteria['Effective']      = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['ApprovedStatus'] = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'null';

                $RateTableID                = $id;
                $RateTableDIDRateID         = $data['RateTableDIDRateID'];
                $OriginationRateID          = !empty($OriginationRateID) ? $OriginationRateID : 'NULL';

                $Timezone = DB::table('RateTableDIDRateID')->select('TimezonesID')->where('RateTableDIDRateID',$data['RateTableDIDRateID'])->first();
                $Timezone = $Timezone->TimezonesID;


                if (empty($data['RateTableDIDRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTableDIDRateUpdateDelete (" . $RateTableID . ",'" . $RateTableDIDRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $CityTariff . "," . $OneOffCost . "," . $MonthlyCost . "," . $CostPerCall . "," . $CostPerMinute . "," . $SurchargePerCall . "," . $SurchargePerMinute . "," . $OutpaymentPerCall . "," . $OutpaymentPerMinute . "," . $Surcharges . "," . $Chargeback . "," . $CollectionCostAmount . "," . $CollectionCostPercentage . "," . $RegistrationCostPerNumber . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $CostPerCallCurrency . "," . $CostPerMinuteCurrency . "," . $SurchargePerCallCurrency . "," . $SurchargePerMinuteCurrency . "," . $OutpaymentPerCallCurrency . "," . $OutpaymentPerMinuteCurrency . "," . $SurchargesCurrency . "," . $ChargebackCurrency . "," . $CollectionCostAmountCurrency . "," . $RegistrationCostPerNumberCurrency . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['CityTariff'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $Timezone . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
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
            $EffectiveDate = $EndDate = $OriginationRateID = $CityTariff = $OneOffCost = $MonthlyCost = $CostPerCall = $CostPerMinute = $SurchargePerCall = $SurchargePerMinute = $OutpaymentPerCall = $OutpaymentPerMinute = $Surcharges = $Chargeback = $CollectionCostAmount = $CollectionCostPercentage = $RegistrationCostPerNumber = $OneOffCostCurrency = $MonthlyCostCurrency = $CostPerCallCurrency = $CostPerMinuteCurrency = $SurchargePerCallCurrency = $SurchargePerMinuteCurrency = $OutpaymentPerCallCurrency = $OutpaymentPerMinuteCurrency = $SurchargesCurrency = $ChargebackCurrency = $CollectionCostAmountCurrency = $RegistrationCostPerNumberCurrency = 'null';

            $Timezone = DB::table('tblRateTableDIDRate')->select('TimezonesID')->where('RateTableDIDRateID',$data['RateTableDIDRateID'])->first();
            $Timezone = $Timezone->TimezonesID;

            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 2; //delete action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['OriginationCode']           = !empty($criteria['OriginationCode']) && $criteria['OriginationCode'] != '' ? "'" . $criteria['OriginationCode'] . "'" : 'NULL';
                $criteria['OriginationDescription']    = !empty($criteria['OriginationDescription']) && $criteria['OriginationDescription'] != '' ? "'" . $criteria['OriginationDescription'] . "'" : 'NULL';
                $criteria['Code']           = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'null';
                $criteria['Description']    = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'null';
                $criteria['CityTariff']     = !empty($criteria['CityTariff']) && $criteria['CityTariff'] != '' ? "'" . $criteria['CityTariff'] . "'" : 'NULL';
                $criteria['Country']        = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'null';
                $criteria['Effective']      = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'null';
                $criteria['ApprovedStatus'] = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'null';

                $RateTableID                = $id;
                $RateTableDIDRateID         = $data['RateTableDIDRateID'];


                if (empty($data['RateTableDIDRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTableDIDRateUpdateDelete (" . $RateTableID . ",'" . $RateTableDIDRateID . "'," . $OriginationRateID . "," . $EffectiveDate . "," . $EndDate . "," . $CityTariff . "," . $OneOffCost . "," . $MonthlyCost . "," . $CostPerCall . "," . $CostPerMinute . "," . $SurchargePerCall . "," . $SurchargePerMinute . "," . $OutpaymentPerCall . "," . $OutpaymentPerMinute . "," . $Surcharges . "," . $Chargeback . "," . $CollectionCostAmount . "," . $CollectionCostPercentage . "," . $RegistrationCostPerNumber . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $CostPerCallCurrency . "," . $CostPerMinuteCurrency . "," . $SurchargePerCallCurrency . "," . $SurchargePerMinuteCurrency . "," . $OutpaymentPerCallCurrency . "," . $OutpaymentPerMinuteCurrency . "," . $SurchargesCurrency . "," . $ChargebackCurrency . "," . $CollectionCostAmountCurrency . "," . $RegistrationCostPerNumberCurrency . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['CityTariff'] . "," . $criteria['OriginationCode'] . "," . $criteria['OriginationDescription'] . "," . $criteria['Effective'] . "," . $Timezone . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Deleted"));
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

            $EffectiveDate = $EndDate = $OneOffCost = $MonthlyCost = $PackageCostPerMinute = $RecordingCostPerMinute = $OneOffCostCurrency = $MonthlyCostCurrency = $PackageCostPerMinuteCurrency = $RecordingCostPerMinuteCurrency = 'null';

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
                $criteria['TimezonesID']    = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones'] . "'" : 'NULL';
                $criteria['ApprovedStatus'] = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'null';

                $RateTableID                = $id;
                $RateTablePKGRateID         = $data['RateTablePKGRateID'];

                if(empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTablePKGRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $Timezone = DB::table('tblRateTablePKGRate')->select('TimezonesID')->where('RateTablePKGRateID',$data['RateTablePKGRateID'])->first();
                $Timezone = $Timezone->TimezonesID;

                $query = "call prc_RateTablePKGRateUpdateDelete (" . $RateTableID . ",'" . $RateTablePKGRateID . "'," . $EffectiveDate . "," . $EndDate . "," . $OneOffCost . "," . $MonthlyCost . "," . $PackageCostPerMinute . "," . $RecordingCostPerMinute . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $PackageCostPerMinuteCurrency . "," . $RecordingCostPerMinuteCurrency . "," . $criteria['Code'] . "," . $criteria['Effective'] . "," . $Timezone . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
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
            $data           = Input::all();


            $username       = User::get_user_full_name();
            $EffectiveDate = $EndDate = $OneOffCost = $MonthlyCost = $PackageCostPerMinute = $RecordingCostPerMinute = $OneOffCostCurrency = $MonthlyCostCurrency = $PackageCostPerMinuteCurrency = $RecordingCostPerMinuteCurrency = 'null';
            $Timezone = DB::table('tblRateTablePKGRate')->select('TimezonesID')->where('RateTablePKGRateID',$data['RateTablePKGRateID'])->first();
            $Timezone = $Timezone->TimezonesID;

            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 2; //delete action

                $criteria   = json_decode($data['criteria'], true);


                $criteria['Code']           = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'null';
                $criteria['Effective']      = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'null';
                $criteria['TimezonesID']    = isset($criteria['TimezonesID']) && $criteria['TimezonesID'] != '' ? "'" . $criteria['TimezonesID'] . "'" : "''";
                $criteria['ApprovedStatus'] = isset($criteria['ApprovedStatus']) && $criteria['ApprovedStatus'] != '' ? "'".$criteria['ApprovedStatus']."'" : 'null';

                $RateTableID                = $id;
                $RateTablePKGRateID         = $data['RateTablePKGRateID'];

                if (empty($data['RateTablePKGRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTablePKGRateUpdateDelete (" . $RateTableID . ",'" . $RateTablePKGRateID . "'," . $EffectiveDate . "," . $EndDate . "," . $OneOffCost . "," . $MonthlyCost . "," . $PackageCostPerMinute . "," . $RecordingCostPerMinute . "," . $OneOffCostCurrency . "," . $MonthlyCostCurrency . "," . $PackageCostPerMinuteCurrency . "," . $RecordingCostPerMinuteCurrency . "," . $criteria['Code'] . "," . $criteria['Effective'] . "," . $Timezone . "," . $criteria['ApprovedStatus'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Deleted"));
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