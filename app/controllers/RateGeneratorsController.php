<?php

class RateGeneratorsController extends \BaseController {

    public function ajax_datagrid() {
        $companyID = User::get_companyID();
        $data = Input::all();
        $where = ["tblRateGenerator.CompanyID" => $companyID];
        if($data['Active']!=''){
            $where['tblRateGenerator.Status'] = $data['Active'];
        }

        $RateGenerators = RateGenerator::
        leftjoin("tblTrunk","tblTrunk.TrunkID","=","tblRateGenerator.TrunkID")
            ->leftjoin("tblCurrency","tblCurrency.CurrencyId","=","tblRateGenerator.CurrencyId")
            ->leftjoin("tblDIDCategory","tblDIDCategory.DIDCategoryID","=","tblRateGenerator.DIDCategoryID")
            ->leftjoin("tblRateType","tblRateType.RateTypeID","=","tblRateGenerator.SelectType")
            ->where($where)->select(array(
                'tblRateType.Title',
                'tblRateGenerator.RateGeneratorName',
                'tblDIDCategory.CategoryName',
                'tblTrunk.Trunk',
                'tblCurrency.Code',
                'tblRateGenerator.Status',
                'tblRateGenerator.created_at',
                'tblRateGenerator.RateGeneratorId',
                'tblRateGenerator.TrunkID',
                'tblRateGenerator.CodeDeckId',
                'tblRateGenerator.CurrencyID',
            )); // by Default Status 1

        if(isset($data['Search']) && !empty($data['Search'])){
            $RateGenerators->WhereRaw('tblRateGenerator.RateGeneratorName like "%'.$data['Search'].'%"');
        }
        if(isset($data['Trunk']) && !empty($data['Trunk'])){
            $RateGenerators->WhereRaw('tblRateGenerator.TrunkID = '.$data['Trunk'].'');
        }
        if(isset($data['SelectType']) && !empty($data['SelectType'])){
            $RateGenerators->WhereRaw('tblRateGenerator.SelectType = '.$data['SelectType'].'');
        }
        if(isset($data['DIDCategoryID']) && !empty($data['DIDCategoryID'])){
            $RateGenerators->WhereRaw('tblRateGenerator.DIDCategoryID = '.$data['DIDCategoryID'].'');
        }

        return Datatables::of($RateGenerators)->make();
    }

    public function index() {
        $Trunks =  Trunk::getTrunkDropdownIDList();
        $RateTypes =  RateType::getRateTypeDropDownList();
        $Categories = DidCategory::getCategoryDropdownIDList();
        $DIDType=RateType::getRateTypeIDBySlug(RateType::SLUG_DID);
        $VoiceCallType=RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL);
        return View::make('rategenerators.index', compact('Trunks','RateTypes','Categories','DIDType','VoiceCallType'));
    }


    public function create() {
        $trunks = Trunk::getTrunkDropdownIDList();
        $trunk_keys = getDefaultTrunk($trunks);
        $codedecklist = BaseCodeDeck::getCodedeckIDList();
        $currencylist = Currency::getCurrencyDropdownIDList();
        $Timezones = Timezones::getTimezonesIDList();
        $AllTypes =  RateType::getRateTypeDropDownList();
        unset($AllTypes[3]);
        $Categories = DidCategory::getCategoryDropdownIDList();
        $Products = ServiceTemplate::lists("Name", "ServiceTemplateId");
        return View::make('rategenerators.create', compact('trunks','AllTypes','Products','Categories','codedecklist','currencylist','trunk_keys','Timezones'));
    }

    public function store() {
        $data = Input::all();
        $companyID = User::get_companyID();
        $data ['CompanyID'] = $companyID;
        $data ['UseAverage'] = isset($data ['UseAverage']) ? 1 : 0;
        $data ['UsePreference'] = isset($data ['UsePreference']) ? 1 : 0;
        $data ['Timezones'] = isset($data ['Timezones']) ? implode(',', $data['Timezones']) : '';
        $data ['DIDCategoryID']= isset($data['Category']) ? $data['Category'] : '';
        $data['VendorPositionPercentage'] = $data['percentageRate'];
        $getNumberString = @$data['getIDs'];
        $getRateNumberString = @$data['getRateIDs'];
        $SelectType = $data['SelectType'];

        if($SelectType != 2) {
            $rules = array(
                'CompanyID' => 'required',
                'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,NULL,CompanyID,CompanyID,' . $data['CompanyID'],
                'Timezones' => 'required',
                'codedeckid' => 'required',
                'CurrencyID' => 'required',
                'Policy' => 'required',
                'LessThenRate' => 'numeric',
                'ChargeRate' => 'numeric',
                'percentageRate' => 'numeric',
            );
        } else {
            $rules = array(
                'CompanyID'         => 'required',
                'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,NULL,CompanyID,CompanyID,'.$data['CompanyID'],
                'CurrencyID'        => 'required',
                'Policy'            => 'required',
                'ProductID'         => 'required',
                'DateFrom'          => 'required|date|date_format:Y-m-d',
                'DateTo'            => 'required|date|date_format:Y-m-d',
                'Calls'             => 'numeric',
                'Minutes'           => 'numeric',
                'TimeOfDayPercentage'   => 'numeric',
                'OriginationPercentage' => 'numeric',
                'LessThenRate'      => 'numeric',
                'ChargeRate'        => 'numeric',
                'percentageRate'    => 'numeric',
            );
        }

        if($SelectType == 1) {
            $rules['TrunkID']='required';
            $rules['RatePosition']='required|numeric';
            $rules['UseAverage']='required';
        }

        $message = array(
            'Timezones.required' => 'Please select at least 1 Timezone',
            'ProductID.required' => 'Please select product.'
        );

        if(!empty($data['IsMerge'])) {
            $rules['TakePrice'] = "required";
            $rules['MergeInto'] = "required";
        } else {
            $data['IsMerge'] = 0;
        }

        $validator = Validator::make($data, $rules, $message);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        //Validation For LessThenRate-ChargeRate
        if(!empty($data['LessThenRate']) || !empty($data['ChargeRate'])){
            if(empty($data['LessThenRate'])){
                return Response::json(array("status" => "failed", "message" => "LessThenRate is required if given ChargeRate."));
            }

            if(empty($data['ChargeRate'])){
                return Response::json(array("status" => "failed", "message" => "ChargeRate is required if given LessThenRate."));
            }
        }
        $data ['CreatedBy'] = User::get_user_full_name();
        try {

            DB::beginTransaction();

            if ($SelectType == 2) {

                $numberArray = array_unique(explode(",", $getNumberString));
                $GetComponent = array();
                $addComponents = array();

                $i = 0;

                for ($i; $i < sizeof($numberArray) - 1; $i++) {
                    $GetAllcomponts[] = 'Component-' . $numberArray[$i];

                    if (!isset($data[$GetAllcomponts[$i]])) {
                        unset($data['Component-' . $numberArray[$i]]);
                        unset($data['Origination-' . $numberArray[$i]]);
                        unset($data['TimeOfDay-' . $numberArray[$i]]);
                        unset($data['Action-' . $numberArray[$i]]);
                        unset($data['MergeTo-' . $numberArray[$i]]);
                        break;
                    } else {
                        if(empty($data['Component-'. $numberArray[$i]]) ||
                            empty($data['TimeOfDay-'. $numberArray[$i]]) ||
                            empty($data['Action-'. $numberArray[$i]]) ||
                            empty($data['MergeTo-'. $numberArray[$i]])){
                            return Response::json(array(
                                "status" => "failed",
                                "message" => "Merge components Value is missing."
                            ));
                        }
                        $componts[] = $data['Component-' . $numberArray[$i]];
                        $origination[] = @$data['Origination-' . $numberArray[$i]];
                        $timeofday[] = $data['TimeOfDay-' . $numberArray[$i]];
                        $action[] = $data['Action-' . $numberArray[$i]];
                        $mergeTo[] = $data['MergeTo-' . $numberArray[$i]];
                    }

                    unset($data['Component-' . $numberArray[$i]]);
                    unset($data['Origination-' . $numberArray[$i]]);
                    unset($data['TimeOfDay-' . $numberArray[$i]]);
                    unset($data['Action-' . $numberArray[$i]]);
                    unset($data['MergeTo-' . $numberArray[$i]]);
                }

                unset($data['getIDs']);
                unset($data['Category']);
                if (!empty($data['AllComponent'])) {
                    $data['SelectedComponents'] = implode(",", $data['AllComponent']);
                }

                $calculatedRates = array_unique(explode(",", $getRateNumberString));

                for ($i = 0; $i < sizeof($calculatedRates) - 1; $i++) {
                    $GetRateComponents[] = 'RateComponent-' . $calculatedRates[$i];

                    if (!isset($data[$GetRateComponents[$i]])) {
                        unset($data['RateComponent-' . $calculatedRates[$i]]);
                        unset($data['RateOrigination-' . $calculatedRates[$i]]);
                        unset($data['RateTimeOfDay-' . $calculatedRates[$i]]);
                        unset($data['RateLessThen-' . $calculatedRates[$i]]);
                        unset($data['ChangeRateTo-' . $calculatedRates[$i]]);
                        break;
                    } else {
                        if(!isset($data['RateComponent-'. $calculatedRates[$i]]) ||
                            empty($data['RateTimeOfDay-'. $calculatedRates[$i]]) ||
                            !isset($data['RateLessThen-'. $calculatedRates[$i]]) ||
                            !is_numeric($data['RateLessThen-'. $calculatedRates[$i]]) ||
                            !isset($data['ChangeRateTo-'. $calculatedRates[$i]]) ||
                            !is_numeric($data['ChangeRateTo-'. $calculatedRates[$i]])){
                            return Response::json(array(
                                "status" => "failed",
                                "message" => "Calculated Rate Value is missing."
                            ));
                        }

                        $rComponent[]    = $data['RateComponent-' . $calculatedRates[$i]];
                        $rOrigination[]  = @$data['RateOrigination-' . $calculatedRates[$i]];
                        $rTimeOfDay[]    = $data['RateTimeOfDay-' . $calculatedRates[$i]];
                        $rRateLessThen[] = $data['RateLessThen-' . $calculatedRates[$i]];
                        $rChangeRateTo[] = $data['ChangeRateTo-' . $calculatedRates[$i]];
                    }

                    unset($data['RateComponent-' . $calculatedRates[$i]]);
                    unset($data['RateOrigination-' . $calculatedRates[$i]]);
                    unset($data['RateTimeOfDay-' . $calculatedRates[$i]]);
                    unset($data['RateLessThen-' . $calculatedRates[$i]]);
                    unset($data['ChangeRateTo-' . $calculatedRates[$i]]);
                }

                unset($data['RateComponent-1']);
                unset($data['RateOrigination-1']);
                unset($data['RateTimeOfDay-1']);
                unset($data['RateLessThen-1']);
                unset($data['ChangeRateTo-1']);
                unset($data['getRateIDs']);
            } else {
                unset($data['getIDs']);
                unset($data['Component-1']);
                unset($data['Origination-1']);
                unset($data['TimeOfDay-1']);
                unset($data['Action-1']);
                unset($data['MergeTo-1']);
                unset($data['Category']);
                unset($data['RateComponent-1']);
                unset($data['RateOrigination-1']);
                unset($data['RateTimeOfDay-1']);
                unset($data['RateLessThen-1']);
                unset($data['ChangeRateTo-1']);
                unset($data['getRateIDs']);
            }

            unset($data['AllComponent']);

            $rateg = RateGenerator::create($data);
            if (isset($rateg->RateGeneratorId) && !empty($rateg->RateGeneratorId)) {
                $CostComponentSaved = "Created";

                if ($SelectType == 2) {

                    $numberArray = explode(",", $getNumberString);
                    $GetComponent = array();
                    $addComponents = array();
                    $i = 0;

                    for ($i = 0; $i < sizeof($numberArray) - 1; $i++) {

                        if (!isset($componts[$i])) {
                            break;
                        }

                        $GetComponent   = $componts[$i];
                        $GetOrigination = $origination[$i];
                        $GetTimeOfDay   = $timeofday[$i];
                        $GetAction      = $action[$i];
                        $GetMergeTo     = $mergeTo[$i];

                        $addComponents['RatePositionID'] = $data['RatePosition'];
                        $addComponents['TrunkID'] = $data['TrunkID'];
                        $addComponents['CurrencyID'] = $data['CurrencyID'];

                        $addComponents['Component'] = implode(",", $GetComponent);
                        $addComponents['Origination'] = $GetOrigination;
                        $addComponents['TimeOfDay'] = $GetTimeOfDay;
                        $addComponents['Action'] = $GetAction;
                        $addComponents['MergeTo'] = $GetMergeTo;
                        $addComponents['RateGeneratorId'] = $rateg->RateGeneratorId;

                        if (RateGeneratorComponent::create($addComponents)) {
                            $CostComponentSaved = "and Cost component Updated";
                        }

                    }

                    $calculatedRates = explode(",", $getRateNumberString);
                    $addCalRate = array();
                    for ($i = 0; $i < sizeof($calculatedRates) - 1; $i++) {
                        if (!isset($rComponent[$i])) {
                            break;
                        }

                        $addCalRate['RatePositionID']  = $data['RatePosition'];
                        $addCalRate['TrunkID']         = $data['TrunkID'];
                        $addCalRate['CurrencyID']      = $data['CurrencyID'];
                        $addCalRate['Component']       = implode(",", $rComponent[$i]);
                        $addCalRate['Origination']     = $rOrigination[$i];
                        $addCalRate['TimeOfDay']       = $rTimeOfDay[$i];
                        $addCalRate['RateLessThen']    = $rRateLessThen[$i];
                        $addCalRate['ChangeRateTo']    = $rChangeRateTo[$i];
                        $addCalRate['RateGeneratorId'] = $rateg->RateGeneratorId;

                        if (RateGeneratorCalculatedRate::create($addCalRate)) {
                            $CostComponentSaved = "and Calculated Rate Updated";
                        }
                    }
                }

                DB::commit();

                return Response::json(array(
                    "status" => "success",
                    "message" => "RateGenerator Successfully Created" . $CostComponentSaved,
                    'LastID' => $rateg->RateGeneratorId,
                    'redirect' => URL::to('/rategenerators/' . $rateg->RateGeneratorId . '/edit')
                ));
            } else {
                return Response::json(array(
                    "status" => "failed",
                    "message" => "Problem Creating RateGenerator."
                ));
            }
        }catch (Exception $e){
            Log::info($e);
            DB::rollback();
            return Response::json(array("status" => "failed", "message" => "Problem Creating RateGenerator. \n" . $e->getMessage()));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * GET /rategenerators/{id}/edit
     *
     * @param int $id
     * @return Response
     */
    public function edit($id) {

        if ($id) {
            $trunks = Trunk::getTrunkDropdownIDList();
            $companyID = User::get_companyID();
            $Categories = DidCategory::getCategoryDropdownIDList();
            $Products = ServiceTemplate::lists("Name", "ServiceTemplateId");

            $rategenerators = RateGenerator::where([
                "RateGeneratorId" => $id,
                "CompanyID" => $companyID
            ])->first();

            $rategenerator_rules = RateRule::with('RateRuleMargin', 'RateRuleSource')->where([
                "RateGeneratorId" => $id
            ]) ->orderBy("Order", "asc")->get();
            $rategeneratorComponents = RateGeneratorComponent::where('RateGeneratorID',$id )->get();
            $rateGeneratorCalculatedRate = RateGeneratorCalculatedRate::where('RateGeneratorID',$id )->get();

            $array_op= array();
            $codedecklist = BaseCodeDeck::getCodedeckIDList();
            $currencylist = Currency::getCurrencyDropdownIDList();
            if(count($rategenerator_rules)){
                $array_op['disabled'] = "disabled";
            }
            $rategenerator = RateGenerator::find($id);
            $Timezones = Timezones::getTimezonesIDList();

            $AllTypes =  RateType::getRateTypeDropDownList();
            unset($AllTypes[3]);

            // Debugbar::info($rategenerator_rules);
            return View::make('rategenerators.edit', compact('id', 'Products', 'rategenerators', 'rategeneratorComponents' ,'AllTypes' ,'Categories' ,'rategenerator', 'rateGeneratorCalculatedRate', 'rategenerator_rules','codedecklist', 'trunks','array_op','currencylist','Timezones'));
        }
    }

    /**
     * Update the specified resource in storage.
     * PUT /rategenerators/{id}
     *
     * @param int $id
     * @return Response
     */
    public function update($id) {

        $data = Input::all();

        $RateGeneratorID = $id;
        $RateGenerator = RateGenerator::find($id);

        $companyID = User::get_companyID();
        $data ['CompanyID'] = $companyID;
        $data ['UseAverage'] = isset($data ['UseAverage']) ? 1 : 0;
        $data ['UsePreference'] = isset($data ['UsePreference']) ? 1 : 0;
        $data ['Timezones'] = isset($data ['Timezones']) ? implode(',', $data['Timezones']) : '';
        $data ['DIDCategoryID']= isset($data['Category']) ? $data['Category'] : '';
        $data['VendorPositionPercentage'] = $data['percentageRate'];
        $getNumberString = $data['getIDs'];
        $getRateNumberString = $data['getRateIDs'];

        unset($data['SelectType']);


        $SelectTypes = RateGenerator::select([
            "SelectType"
        ])->where(['RateGeneratorId' => $id ])->get();

        foreach($SelectTypes as $Type){
            $SelectType = $Type->SelectType;
        }

        if($SelectType != 2) {
            $rules = array(
                'CompanyID' => 'required',
                'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,' . $RateGenerator->RateGeneratorId . ',RateGeneratorID,CompanyID,' . $data['CompanyID'],
                'Timezones' => 'required',
                'codedeckid' => 'required',
                'CurrencyID' => 'required',
                'Policy' => 'required',
                'LessThenRate' => 'numeric',
                'ChargeRate' => 'numeric',
                'percentageRate' => 'numeric',
            );
        } else {
            $rules = array(
                'CompanyID'         => 'required',
                'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,' . $RateGenerator->RateGeneratorId . ',RateGeneratorID,CompanyID,' . $data['CompanyID'],
                'CurrencyID'        => 'required',
                'Policy'            => 'required',
                'ProductID'         => 'required',
                'DateFrom'          => 'required|date|date_format:Y-m-d',
                'DateTo'            => 'required|date|date_format:Y-m-d',
                'Calls'             => 'numeric',
                'Minutes'           => 'numeric',
                'TimeOfDayPercentage'   => 'numeric',
                'OriginationPercentage' => 'numeric',
                'LessThenRate'      => 'numeric',
                'ChargeRate'        => 'numeric',
                'percentageRate'    => 'numeric',
            );
        }

        if($SelectType == 1) {
            $rules['TrunkID']='required';
            $rules['RatePosition']='required|numeric';
            $rules['UseAverage']='required';

        }

        $message = array(
            'Timezones.required' => 'Please select at least 1 Timezone',
            'ProductID.required' => 'Please select product.'
        );

        if(!empty($data['IsMerge'])) {
            $rules['TakePrice'] = "required";
            $rules['MergeInto'] = "required";
        } else {
            $data['IsMerge'] = 0;
        }

        $validator = Validator::make($data, $rules, $message);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        //Validation For LessThenRate-ChargeRate
        if(!empty($data['LessThenRate']) || !empty($data['ChargeRate'])){
            if(empty($data['LessThenRate'])){
                return Response::json(array("status" => "failed", "message" => "LessThenRate is required if given ChargeRate."));
            }

            if(empty($data['ChargeRate'])){
                return Response::json(array("status" => "failed", "message" => "ChargeRate is required if given LessThenRate."));
            }
        }

        $data ['ModifiedBy'] = User::get_user_full_name();

        try {

            DB::beginTransaction();

            if ($SelectType == 2) {

                $numberArray = array_unique(explode(",", $getNumberString));
                $GetComponent = array();
                $addComponents = array();

                $i = 0;

                for ($i; $i < sizeof($numberArray) - 1; $i++) {
                    $GetAllcomponts[] = 'Component-' . $numberArray[$i];

                    if (!isset($data[$GetAllcomponts[$i]])) {
                        unset($data['Component-' . $numberArray[$i]]);
                        unset($data['Origination-' . $numberArray[$i]]);
                        unset($data['TimeOfDay-' . $numberArray[$i]]);
                        unset($data['Action-' . $numberArray[$i]]);
                        unset($data['MergeTo-' . $numberArray[$i]]);
                        break;
                    } else {
                        if(empty($data['Component-'. $numberArray[$i]]) ||
                            empty($data['TimeOfDay-'. $numberArray[$i]]) ||
                            empty($data['Action-'. $numberArray[$i]]) ||
                            empty($data['MergeTo-'. $numberArray[$i]])){
                            return Response::json(array(
                                "status" => "failed",
                                "message" => "Merge components Value is missing."
                            ));
                        }
                        $componts[] = $data['Component-' . $numberArray[$i]];
                        $origination[] = @$data['Origination-' . $numberArray[$i]];
                        $timeofday[] = $data['TimeOfDay-' . $numberArray[$i]];
                        $action[] = $data['Action-' . $numberArray[$i]];
                        $mergeTo[] = $data['MergeTo-' . $numberArray[$i]];
                    }

                    unset($data['Component-' . $numberArray[$i]]);
                    unset($data['Origination-' . $numberArray[$i]]);
                    unset($data['TimeOfDay-' . $numberArray[$i]]);
                    unset($data['Action-' . $numberArray[$i]]);
                    unset($data['MergeTo-' . $numberArray[$i]]);
                }

                unset($data['getIDs']);
                unset($data['Category']);
                if (!empty($data['AllComponent'])) {
                    $data['SelectedComponents'] = implode(",", $data['AllComponent']);
                }

                $calculatedRates = array_unique(explode(",", $getRateNumberString));

                for ($i = 0; $i < sizeof($calculatedRates) - 1; $i++) {
                    $GetRateComponents[] = 'RateComponent-' . $calculatedRates[$i];

                    if (!isset($data[$GetRateComponents[$i]])) {
                        unset($data['RateComponent-' . $calculatedRates[$i]]);
                        unset($data['RateOrigination-' . $calculatedRates[$i]]);
                        unset($data['RateTimeOfDay-' . $calculatedRates[$i]]);
                        unset($data['RateLessThen-' . $calculatedRates[$i]]);
                        unset($data['ChangeRateTo-' . $calculatedRates[$i]]);
                        break;
                    } else {
                        if(!isset($data['RateComponent-'. $calculatedRates[$i]]) ||
                            empty($data['RateTimeOfDay-'. $calculatedRates[$i]]) ||
                            !isset($data['RateLessThen-'. $calculatedRates[$i]]) ||
                            !is_numeric($data['RateLessThen-'. $calculatedRates[$i]]) ||
                            !isset($data['ChangeRateTo-'. $calculatedRates[$i]]) ||
                            !is_numeric($data['ChangeRateTo-'. $calculatedRates[$i]])){
                            return Response::json(array(
                                "status" => "failed",
                                "message" => "Calculated Rate Value is missing."
                            ));
                        }

                        $rComponent[]    = $data['RateComponent-' . $calculatedRates[$i]];
                        $rOrigination[]  = @$data['RateOrigination-' . $calculatedRates[$i]];
                        $rTimeOfDay[]    = $data['RateTimeOfDay-' . $calculatedRates[$i]];
                        $rRateLessThen[] = $data['RateLessThen-' . $calculatedRates[$i]];
                        $rChangeRateTo[] = $data['ChangeRateTo-' . $calculatedRates[$i]];
                    }

                    unset($data['RateComponent-' . $calculatedRates[$i]]);
                    unset($data['RateOrigination-' . $calculatedRates[$i]]);
                    unset($data['RateTimeOfDay-' . $calculatedRates[$i]]);
                    unset($data['RateLessThen-' . $calculatedRates[$i]]);
                    unset($data['ChangeRateTo-' . $calculatedRates[$i]]);
                }

                unset($data['getRateIDs']);
                unset($data['RateComponent-1']);
                unset($data['RateOrigination-1']);
                unset($data['RateTimeOfDay-1']);
                unset($data['RateLessThen-1']);
                unset($data['ChangeRateTo-1']);
            } else {
                unset($data['getIDs']);
                unset($data['Component-1']);
                unset($data['Action-1']);
                unset($data['Origination-1']);
                unset($data['TimeOfDay-1']);
                unset($data['MergeTo-1']);
                unset($data['Category']);
                unset($data['RateComponent-1']);
                unset($data['RateOrigination-1']);
                unset($data['RateTimeOfDay-1']);
                unset($data['RateLessThen-1']);
                unset($data['ChangeRateTo-1']);
                unset($data['getRateIDs']);
            }

            unset($data['AllComponent']);

            if ($RateGenerator->update($data)) {
                $CostComponentSaved = "Updated";

                RateGeneratorComponent::where("RateGeneratorId", $id)->delete();
                RateGeneratorCalculatedRate::where("RateGeneratorId", $id)->delete();

                if ($SelectType == 2) {

                    $numberArray = explode(",", $getNumberString);
                    $GetComponent = array();
                    $addComponents = array();
                    $i = 0;
                    for ($i = 0; $i < sizeof($numberArray) - 1; $i++) {
                        if (!isset($componts[$i])) {
                            break;
                        }
                        $GetComponent   = $componts[$i];
                        $GetOrigination = $origination[$i];
                        $GetTimeOfDay   = $timeofday[$i];
                        $GetAction      = $action[$i];
                        $GetMergeTo     = $mergeTo[$i];

                        $addComponents['RatePositionID'] = $data['RatePosition'];
                        $addComponents['TrunkID'] = $data['TrunkID'];
                        $addComponents['CurrencyID'] = $data['CurrencyID'];

                        $addComponents['Component'] = implode(",", $GetComponent);
                        $addComponents['Origination'] = $GetOrigination;
                        $addComponents['TimeOfDay'] = $GetTimeOfDay;
                        $addComponents['Action'] = $GetAction;
                        $addComponents['MergeTo'] = $GetMergeTo;
                        $addComponents['RateGeneratorId'] = $RateGeneratorID;

                        if (RateGeneratorComponent::create($addComponents)) {
                            $CostComponentSaved = "and Cost component Updated";
                        }
                    }

                    $calculatedRates = explode(",", $getRateNumberString);
                    $addCalRate = array();

                    for ($i = 0; $i < sizeof($calculatedRates) - 1; $i++) {
                        if (!isset($rComponent[$i])) {
                            break;
                        }

                        $addCalRate['RatePositionID']  = $data['RatePosition'];
                        $addCalRate['TrunkID']         = $data['TrunkID'];
                        $addCalRate['CurrencyID']      = $data['CurrencyID'];
                        $addCalRate['Component']       = implode(",", $rComponent[$i]);
                        $addCalRate['Origination']     = $rOrigination[$i];
                        $addCalRate['TimeOfDay']       = $rTimeOfDay[$i];
                        $addCalRate['RateLessThen']    = $rRateLessThen[$i];
                        $addCalRate['ChangeRateTo']    = $rChangeRateTo[$i];
                        $addCalRate['RateGeneratorId'] = $RateGeneratorID;

                        if (RateGeneratorCalculatedRate::create($addCalRate)) {
                            $CostComponentSaved = "and Calculated Rate Updated";
                        }
                    }
                }

                DB::commit();

                return Response::json(array(
                    "status" => "success",
                    "message" => "RateGenerator and RateGenerator Cost component Successfully " . $CostComponentSaved,
                ));

            } else {
                return Response::json(array(
                    "status" => "failed",
                    "message" => "Problem Updating RateGenerator."
                ));
            }
        } catch (Exception $e){
            Log::info($e);
            DB::rollback();
            return Response::json(array("status" => "failed", "message" => "Problem Updating RateGenerator. \n" . $e->getMessage()));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /rategenerators/{id}
     *
     * @param int $id
     * @return Response
     */
    public function rules($id) {
        if ($id) {
            // $companyID = User::get_companyID();
            $rategenerator_rules = RateRule::with('RateRuleMargin', 'RateRuleSource')->where([
                "RateGeneratorId" => $id
            ])->get();
            return View::make('rategenerators.rule', compact('id', 'rategenerator_rules'));
        }
    }


    public function delete($id) {
        if ($id) {
            try{
                DB::beginTransaction();

                RateGeneratorComponent::where('RateGeneratorId',$id)->delete();
                RateGeneratorCalculatedRate::where('RateGeneratorId',$id)->delete();
                RateGenerator::find($id)->delete();
                DB::commit();
                return Response::json(array("status" => "success", "message" => "Rate Generator Successfully deleted"));

            }catch (Exception $e){
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => "Invoice is in Use, You cant delete this Currency. \n" . $e->getMessage() ));
            }

        }
    }

    public function generate_rate_table($id, $action) {
        if ($id && $action) {
            try {
                DB::beginTransaction();
                $RateGeneratorId = $id;

                $data = compact("RateGeneratorId");
                $data["EffectiveDate"] = Input::get('EffectiveDate');
                $checkbox_replace_all = Input::get('checkbox_replace_all');
                $data['EffectiveRate'] = Input::get('EffectiveRate');

                $IncreaseEffectiveDate = Input::get('IncreaseEffectiveDate');

                if(!empty($IncreaseEffectiveDate)) {
                    $data['IncreaseEffectiveDate']  =   $IncreaseEffectiveDate;
                }

                $DecreaseEffectiveDate = Input::get('DecreaseEffectiveDate');
                if(!empty($DecreaseEffectiveDate)) {
                    $data['DecreaseEffectiveDate']  =   $DecreaseEffectiveDate;
                }

                if(empty($data['EffectiveRate'])){
                    $data['EffectiveRate']='now';
                }
                if(!empty($checkbox_replace_all) && $checkbox_replace_all == 1){
                    $data['replace_rate'] = 1;
                }else{
                    $data['replace_rate'] = 0;
                }
                $data ['CompanyID'] = User::get_companyID();

                if($action == 'create'){
                    $RateTableName = Input::get('RateTableName');
                    $data["rate_table_name"] = $RateTableName;
                    $data['ratetablename'] = $RateTableName;
                    $rules = array(
                        'rate_table_name' => 'required|unique:tblRateTable,RateTableName,NULL,CompanyID,CompanyID,'.$data['CompanyID'].',RateGeneratorID,'.$id,
                        'EffectiveDate'=>'required'
                    );
                }else if($action == 'update'){
                    $RateTableID = Input::get('RateTableID');
                    $data["RateTableId"] = $RateTableID;
                    $data['ratetablename'] = RateTable::where(["RateTableId" => $RateTableID])->pluck('RateTableName');
                    $rules = array(
                        'RateTableId' => 'required',
                        'EffectiveDate'=>'required'
                    );
                }
                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return json_validator_response ( $validator );
                }
                $ExchangeRateStatus = RateGenerator::checkExchangeRate($RateGeneratorId);
                if($ExchangeRateStatus['status'] == 1){
                    return Response::json(array("status" => "failed", "message" => $ExchangeRateStatus['message']));
                }
                /* Old way to get RateTableID
                 *
                 * $RateGenerator = RateGenerator::find($RateGeneratorId);
                 if(!empty($RateGenerator) && is_object($RateGenerator) ){
                    $RateTableID = $RateGenerator->RateTableId;
                    if(is_numeric($RateTableID)  ){
                        $data["RateTableID"] = $RateTableID;
                    }
                }*/

                $result = Job::logJob("GRT", $data);
                if ($result ['status'] != "success") {
                    DB::rollback();
                    return json_encode([
                        "status" => "failed",
                        "message" => $result ['message']
                    ]);
                }
                DB::commit();
                return json_encode([
                    "status" => "success",
                    "message" => "Rate Generator Job Added in queue to process. You will be informed once Job Done. "
                ]);
            } catch (Exception $ex) {
                DB::rollback();
                return json_encode([
                    "status" => "failed",
                    "message" => " Exception: " . $ex->getMessage()
                ]);
            }
        }
    }

    public function change_status($id, $status) {
        if ($id > 0 && ( $status == 0 || $status == 1)) {
            if (RateGenerator::find($id)->update(["Status" => $status, "ModifiedBy" => User::get_user_full_name()])) {
                return Response::json(array("status" => "success", "message" => "Status Successfully Changed"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Changing Status."));
            }
        }
    }
    public function exports($type) {
        $companyID = User::get_companyID();

        $data=Input::all();
        $where = ["tblRateGenerator.CompanyID" => $companyID];
        if($data['Active']!=''){
            $where['tblRateGenerator.Status'] = $data['Active'];
        }

        /*$RateGenerators = RateGenerator::join("tblTrunk","tblTrunk.TrunkID","=","tblRateGenerator.TrunkID")->where(["tblRateGenerator.CompanyID" => $companyID])
            ->orderBy("RateGeneratorID", "desc")
            ->get(array(
                'RateGeneratorName',
                'tblTrunk.Trunk',
                'tblRateGenerator.Status',
            ));*/

        $RateGenerators = RateGenerator::
        leftjoin("tblTrunk","tblTrunk.TrunkID","=","tblRateGenerator.TrunkID")
            ->leftjoin("tblCurrency","tblCurrency.CurrencyId","=","tblRateGenerator.CurrencyId")
            ->leftjoin("tblDIDCategory","tblDIDCategory.DIDCategoryID","=","tblRateGenerator.DIDCategoryID")
            ->leftjoin("tblRateType","tblRateType.RateTypeID","=","tblRateGenerator.SelectType")
            ->where($where); // by Default Status 1

        if(isset($data['Search']) && !empty($data['Search'])){
            $RateGenerators->WhereRaw('tblRateGenerator.RateGeneratorName like "%'.$data['Search'].'%"');
        }
        if(isset($data['Trunk']) && !empty($data['Trunk'])){
            $RateGenerators->WhereRaw('tblRateGenerator.TrunkID = '.$data['Trunk'].'');
        }
        if(isset($data['SelectType']) && !empty($data['SelectType'])){
            $RateGenerators->WhereRaw('tblRateGenerator.SelectType = '.$data['SelectType'].'');
        }
        if(isset($data['DIDCategoryID']) && !empty($data['DIDCategoryID'])){
            $RateGenerators->WhereRaw('tblRateGenerator.DIDCategoryID = '.$data['DIDCategoryID'].'');
        }

        $Result = $RateGenerators->orderBy("RateGeneratorID", "desc")
            ->get(array(
                'tblRateType.Title',
                'tblRateGenerator.RateGeneratorName',
                'tblDIDCategory.CategoryName',
                'tblTrunk.Trunk',
                'tblCurrency.Code',
                'tblRateGenerator.Status',
                'tblRateGenerator.created_at',
            ));

        $excel_data = json_decode(json_encode($Result),true);

        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Rate Generator.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($excel_data);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Rate Generator.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($excel_data);
        }
    }

    public function ajax_load_rate_table_dropdown(){
        $data = Input::all();
        if(isset($data['TrunkID']) && intval($data['TrunkID']) > 0) {
            $filterdata['TrunkID'] = intval($data['TrunkID']);
            $filterdata['CurrencyID'] = intval($data['CurrencyID']);
            $filterdata['CodeDeckId'] = intval($data['CodeDeckId']);
            $rate_table = RateTable::getRateTableCache($filterdata);
            return View::make('rategenerators.ajax_rate_table_dropdown', compact('rate_table'));
        }
        return '';
    }

    public function ajax_existing_rategenerator_cronjob($id){
        $companyID = User::get_companyID();
        $tag = '"rateGeneratorID":"'.$id.'"';
        $cronJobs = CronJob::where('Settings','LIKE', '%'.$tag.'%')->where(['CompanyID'=>$companyID])->select(['JobTitle','Status','created_by','CronJobID'])->get()->toArray();
        return View::make('rategenerators.ajax_rategenerator_cronjobs', compact('cronJobs'));
    }

    public function deleteCronJob($id){
        $data = Input::all();
        try{
            $cronjobs = explode(',',$data['cronjobs']);
            foreach($cronjobs as $cronjobID){
                $cronjob = CronJob::find($cronjobID);
                if($cronjob->Active){
                    $Process = new Process();
                    $Process->change_crontab_status(0);
                }
                $cronjob->delete();
                CronJobLog::where("CronJobID",$cronjobID)->delete();
            }
            return Response::json(array("status" => "success", "message" => "Cron Job Successfully Deleted"));
        }catch (Exception $ex){
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    function Update_Fields_Sorting(){
        $postdata    =  Input::all();
        if(isset($postdata['main_fields_sort']) && !empty($postdata['main_fields_sort']))
        {
            try
            {
                DB::beginTransaction();
                $main_fields_sort = json_decode($postdata['main_fields_sort']);
                foreach($main_fields_sort as $main_fields_sort_Data){
                    RateRule::find($main_fields_sort_Data->data_id)->update(array("Order"=>$main_fields_sort_Data->Order));
                }
                DB::commit();
                return Response::json(["status" => "success", "message" => "Order Successfully updated."]);
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
            }
        }
    }


    function ComponentTable(){
        return View::make('rategenerators.ajax.index');
    }
}