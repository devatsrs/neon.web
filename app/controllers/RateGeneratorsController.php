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
                'tblRateGenerator.SelectType',
                'tblRateGenerator.CreatedBy',
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
        $companyID = User::get_companyID();
        $currencylist = Currency::getCurrencyDropdownIDList();
        $Timezones = Timezones::getTimezonesIDList();
        $AllTypes =  RateType::getRateTypeDropDownList();
        
        // $country = ServiceTemplate::Join('tblCountry', function($join) {
        //     $join->on('tblServiceTemplate.country','=','tblCountry.country');
        //   })->select('tblServiceTemplate.country AS country','tblCountry.countryID As CountryID')->where("tblServiceTemplate.CompanyID",User::get_companyID())
        //     ->orderBy('tblServiceTemplate.country')->lists("country", "CountryID");

        $country            = ServiceTemplate::getCountryDD($companyID);
        $AccessType         = ServiceTemplate::getAccessTypeDD($companyID);
        $City               = ServiceTemplate::getCityDD($companyID);
        $Tariff             = ServiceTemplate::getTariffDD($companyID);
        $Prefix             = ServiceTemplate::getPrefixDD($companyID);          
        
        // $CityTariffFilter = [];
        // foreach($CityTariff as $key => $City){
        //     if(strpos($City, " per ")){
        //         $CityTariffFilter[$City] = $City;
        //         unset($CityTariff[$key]);
        //     }
        // }
        //$CityTariff = array_merge($CityTariff, $CityTariffFilter);
        $country = array('' => "All") + $country;
        $AccessType = array('' => "All") + $AccessType;
        $Prefix = array('' => "All") + $Prefix;
        $City = array('' => "All") + $City;
        $Tariff = array('' => "All") +  $Tariff;

        $Package = array('' => "All") + Package::where([
                "status" => 1,
                "CompanyID" => User::get_companyID()
            ])->lists("Name", "PackageId");
        $Categories = DidCategory::getCategoryDropdownIDList();
        
        $Products = ServiceTemplate::where([
                "CompanyID" => User::get_companyID()
            ])->lists("Name", "ServiceTemplateId");

        $ResellerDD  = RateTable::getResellerDropdownIDList();

        return View::make('rategenerators.create', compact('trunks','AllTypes','Products','Package','Categories','codedecklist','currencylist','trunk_keys','Timezones','country','AccessType','Prefix','City','Tariff','ResellerDD'));
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
        
        if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL)) {
            $rules = array(
                'CompanyID' => 'required',
                'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,NULL,CompanyID,CompanyID,' . $data['CompanyID'],
                'Timezones' => 'required',
                'CurrencyID' => 'required',
                'Policy' => 'required',
                'LessThenRate' => 'numeric',
                'ChargeRate' => 'numeric',
                'percentageRate' => 'numeric',
               
            );
            if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL)){
                $rules['codedeckid']='required';
              
            }
            
        } else {
            $rules = array(
                'CompanyID'         => 'required',
                'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,NULL,CompanyID,CompanyID,'.$data['CompanyID'],
                'CurrencyID'        => 'required',
                'DateFrom'          => 'required|date|date_format:Y-m-d',
                'DateTo'            => 'required|date|date_format:Y-m-d',
                'Calls'             => 'numeric',
                'Minutes'           => 'numeric',
                'TimezonesPercentage'   => 'numeric',
                'OriginationPercentage' => 'numeric',
                'percentageRate'    => 'numeric',
            );
        }
        if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE)){
            $rules['RatePosition']='required|numeric';
        }

        if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL)) {
            $rules['TrunkID']='required';
            $rules['RatePosition']='required|numeric';
            $rules['UseAverage']='required';
        }
        if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID)){
            $rules['RatePosition']='required|numeric';
            $rules['Category']='required';
            $rules['NoOfServicesContracted']='numeric';

        }

        if(isset($data['AppliedTo']) && $data['AppliedTo'] == RateTable::APPLIED_TO_RESELLER){
            $rules['Reseller'] = 'required';
        }

        $message = array(
            'Timezones.required' => 'Please select at least 1 Timezone',
            'ProductID.required' => 'Please select product.',
            'TimezonesPercentage.numeric' => 'Please enter valid numeric value of Time Of Day Percentage.',
            'Reseller.required' => 'The partner field is required',
            'NoOfServicesContracted.numeric' => 'The number of services contracted must be a number'
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

            if ($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID) ||$SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE)) {

                $numberArray = array_unique(explode(",", $getNumberString));
                $GetComponent = array();
                $addComponents = array();

                $i = 0;

                for ($i; $i < sizeof($numberArray) - 1; $i++) {
                     if(empty($data['Component-'. $numberArray[$i]]) ||
                        empty($data['TimeOfDay-'. $numberArray[$i]]) ||
                        empty($data['Action-'. $numberArray[$i]]) ||
                        empty($data['MergeTo-'. $numberArray[$i]]) ||
                        empty($data['ToTimeOfDay-'. $numberArray[$i]])){
                            return Response::json(array(
                                "status" => "failed",
                                "message" => "Merge components Value is missing."
                            ));
                    }
                    $GetAllcomponts[] = 'Component-' . $numberArray[$i];

                    if (!isset($data[$GetAllcomponts[$i]])) {
                        unset($data['Component-' . $numberArray[$i]]);
                        unset($data['Origination-' . $numberArray[$i]]);
                        unset($data['TimeOfDay-' . $numberArray[$i]]);
                        unset($data['Action-' . $numberArray[$i]]);
                        unset($data['MergeTo-' . $numberArray[$i]]);
                        unset($data['ToOrigination-' . $numberArray[$i]]);
                        unset($data['ToTimeOfDay-' . $numberArray[$i]]);
                        unset($data['FCountry-' . $numberArray[$i]]);
                        unset($data['TCountry-' . $numberArray[$i]]);
                        unset($data['FAccessType-' . $numberArray[$i]]);
                        unset($data['TAccessType-' . $numberArray[$i]]);
                        unset($data['FPrefix-' . $numberArray[$i]]);
                        unset($data['TPrefix-' . $numberArray[$i]]);
                        unset($data['FCity-' . $numberArray[$i]]);
                        unset($data['TCity-' . $numberArray[$i]]);
                        unset($data['FTariff-' . $numberArray[$i]]);
                        unset($data['TTariff-' . $numberArray[$i]]);
                        unset($data['Package-' . $numberArray[$i]]);

                        
                    } else {
                        
                        if(isset($data['FCountry-' . $numberArray[$i]]) && $data['FCountry-' . $numberArray[$i]] == ''){
                            $data['FCountry-' . $numberArray[$i]] = null;
                        }
                        if(isset($data['TCountry-' . $numberArray[$i]]) && $data['TCountry-' . $numberArray[$i]] == ''){
                            $data['TCountry-' . $numberArray[$i]] = null;
                        }

                        $componts[]       = $data['Component-' . $numberArray[$i]];
                        $origination[]    = @$data['Origination-' . $numberArray[$i]];
                        $timeofday[]      = $data['TimeOfDay-' . $numberArray[$i]];
                        $action[]         = $data['Action-' . $numberArray[$i]];
                        $mergeTo[]        = $data['MergeTo-' . $numberArray[$i]];
                        $originationTo[]  = @$data['ToOrigination-' . $numberArray[$i]];
                        $timeofdayTo[]    = $data['ToTimeOfDay-' . $numberArray[$i]];
                        $fcountry[]       = $data['FCountry-' . $numberArray[$i]];
                        $tcountry[]       = $data['TCountry-' . $numberArray[$i]];
                        $faccesstype[]    = $data['FAccessType-' . $numberArray[$i]];
                        $taccesstype[]    = $data['TAccessType-' . $numberArray[$i]];
                        $fprefix[]        = $data['FPrefix-' . $numberArray[$i]];
                        $tprefix[]        = $data['TPrefix-' . $numberArray[$i]];
                        $fcity[]          = $data['FCity-' . $numberArray[$i]];
                        $tcity[]          = $data['TCity-' . $numberArray[$i]];
                        $ftariif[]        = $data['FTariff-' . $numberArray[$i]];
                        $ttariif[]        = $data['TTariff-' . $numberArray[$i]];
                        $PackageComponent[]  = $data['Package-' . $numberArray[$i]];

                        
                    }

                    unset($data['Component-' . $numberArray[$i]]);
                    unset($data['Origination-' . $numberArray[$i]]);
                    unset($data['TimeOfDay-' . $numberArray[$i]]);
                    unset($data['Action-' . $numberArray[$i]]);
                    unset($data['MergeTo-' . $numberArray[$i]]);
                    unset($data['ToOrigination-' . $numberArray[$i]]);
                    unset($data['ToTimeOfDay-' . $numberArray[$i]]);
                    unset($data['FCountry-' . $numberArray[$i]]);
                    unset($data['TCountry-' . $numberArray[$i]]);
                    unset($data['FAccessType-' . $numberArray[$i]]);
                    unset($data['TAccessType-' . $numberArray[$i]]);
                    unset($data['FPrefix-' . $numberArray[$i]]);
                    unset($data['TPrefix-' . $numberArray[$i]]);
                    unset($data['FCity-' . $numberArray[$i]]);
                    unset($data['TCity-' . $numberArray[$i]]);
                    unset($data['FTariff-' . $numberArray[$i]]);
                    unset($data['TTariff-' . $numberArray[$i]]);
                    unset($data['Package-' . $numberArray[$i]]);

                }

                unset($data['getIDs']);
                unset($data['Category']);
                if (!empty($data['AllComponent'])) {
                    $data['SelectedComponents'] = implode(",", $data['AllComponent']);

                }

                $calculatedRates = array_unique(explode(",", $getRateNumberString));

                for ($i = 0; $i < sizeof($calculatedRates) - 1; $i++) {
                    if(isset($data['RateComponent-' . $calculatedRates[$i]])) {
                        $GetRateComponents[] = 'RateComponent-' . $calculatedRates[$i];

                        if (!isset($data[$GetRateComponents[$i]])) {
                            unset($data['RateComponent-' . $calculatedRates[$i]]);
                            unset($data['RateOrigination-' . $calculatedRates[$i]]);
                            unset($data['RateTimeOfDay-' . $calculatedRates[$i]]);
                            unset($data['RateLessThen-' . $calculatedRates[$i]]);
                            unset($data['ChangeRateTo-' . $calculatedRates[$i]]);
                            unset($data['Country1-' . $calculatedRates[$i]]);
                            unset($data['AccessType1-' . $calculatedRates[$i]]);
                            unset($data['Prefix1-' . $calculatedRates[$i]]);
                            unset($data['City1-' . $calculatedRates[$i]]);
                            unset($data['Tariff1-' . $calculatedRates[$i]]);
                            unset($data['Package1-' . $calculatedRates[$i]]);
                        } else {
                            if (!isset($data['RateComponent-' . $calculatedRates[$i]]) ||
                                empty($data['RateTimeOfDay-' . $calculatedRates[$i]]) ||
                                !isset($data['RateLessThen-' . $calculatedRates[$i]]) ||
                                !is_numeric($data['RateLessThen-' . $calculatedRates[$i]]) ||
                                !isset($data['ChangeRateTo-' . $calculatedRates[$i]]) ||
                                !is_numeric($data['ChangeRateTo-' . $calculatedRates[$i]])
                            ) {
                                return Response::json(array(
                                    "status" => "failed",
                                    "message" => "Calculated Rate Value is missing."
                                ));
                            }
                            if(isset($data['Country1-' . $calculatedRates[$i]]) && $data['Country1-' . $calculatedRates[$i]] == ''){
                                $data['Country1-' . $calculatedRates[$i]] = null;
                            } 

                            $rComponent[]    = $data['RateComponent-' . $calculatedRates[$i]];
                            $rOrigination[]  = @$data['RateOrigination-' . $calculatedRates[$i]];
                            $rTimeOfDay[]    = $data['RateTimeOfDay-' . $calculatedRates[$i]];
                            $rRateLessThen[] = $data['RateLessThen-' . $calculatedRates[$i]];
                            $rChangeRateTo[] = $data['ChangeRateTo-' . $calculatedRates[$i]];
                            $rCountry[]      = $data['Country1-' . $calculatedRates[$i]];
                            $rAccessType[]   = $data['AccessType1-' . $calculatedRates[$i]];
                            $rPrefix[]       = $data['Prefix1-' . $calculatedRates[$i]];
                            $rCity[]         = $data['City1-' . $calculatedRates[$i]];
                            $rTarrif[]       = $data['Tariff1-' . $calculatedRates[$i]];
                            $rPackage[]      = $data['Package1-' . $calculatedRates[$i]];
                        }

                        unset($data['RateComponent-' . $calculatedRates[$i]]);
                        unset($data['RateOrigination-' . $calculatedRates[$i]]);
                        unset($data['RateTimeOfDay-' . $calculatedRates[$i]]);
                        unset($data['RateLessThen-' . $calculatedRates[$i]]);
                        unset($data['ChangeRateTo-' . $calculatedRates[$i]]);
                        unset($data['Country1-' . $calculatedRates[$i]]);
                        unset($data['AccessType1-' . $calculatedRates[$i]]);
                        unset($data['Prefix1-' . $calculatedRates[$i]]);
                        unset($data['City1-' . $calculatedRates[$i]]);
                        unset($data['Tariff1-' . $calculatedRates[$i]]);
                        unset($data['Package1-' . $calculatedRates[$i]]);
                    }
                }
                unset($data['RateComponent-1']);
                unset($data['RateOrigination-1']);
                unset($data['RateTimeOfDay-1']);
                unset($data['RateLessThen-1']);
                unset($data['ChangeRateTo-1']);
                unset($data['Country1-1']);
                unset($data['AccessType1-1']);
                unset($data['Prefix1-1' ]);
                unset($data['City1-1']);
                unset($data['Tariff1-1']);
                unset($data['getRateIDs']);
                unset($data['Package1-1']);
               
            } else {

                unset($data['getIDs']);
                unset($data['Component-1']);
                unset($data['Origination-1']);
                unset($data['TimeOfDay-1']);
                unset($data['Action-1']);
                unset($data['MergeTo-1']);
                unset($data['ToOrigination-1']);
                unset($data['ToTimeOfDay-1']);
                unset($data['Category']);
                unset($data['RateComponent-1']);
                unset($data['RateOrigination-1']);
                unset($data['RateTimeOfDay-1']);
                unset($data['RateLessThen-1']);
                unset($data['ChangeRateTo-1']);
                unset($data['getRateIDs']);
                unset($data['FCountry-1']);
                unset($data['TCountry-1']);
                unset($data['FAccessType-1']);
                unset($data['TAccessType-1']);
                unset($data['FPrefix-1']);
                unset($data['TPrefix-1']);
                unset($data['FCity-1']);
                unset($data['TCity-1']);
                unset($data['FTariff-1']);
                unset($data['TTariff-1']);
                unset($data['Country1-1']);
                unset($data['AccessType1-1']);
                unset($data['Prefix1-1' ]);
                unset($data['City1-1']);
                unset($data['Tariff1-1']);
                unset($data['Package1-1']);
                unset($data['Package-1']);
            }
            if ($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID) || $SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL)) {
                 unset($data['PackageID']);
            }
            if ($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE) || $SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL)) {
                unset($data['NoOfServicesContracted']);
            }

            if(isset($data['CountryID']) && $data['CountryID'] == ''){
                $data['CountryID'] = null;
            }

            unset($data['AllComponent']);
            unset($data['getIDs']);
            unset($data['Category']);
            $rateg = RateGenerator::create($data);
            if (isset($rateg->RateGeneratorId) && !empty($rateg->RateGeneratorId)) {
                $CostComponentSaved = "Created";
    
                if ($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID) ||$SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE)) {

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
                        $GetToOrigination  = $originationTo[$i];
                        $GetToTimeOfDay    = $timeofdayTo[$i];
                        $GetFCountry    = $fcountry[$i];
                        $GetFAccessType    = $faccesstype[$i];
                        $GetFPrefix    = $fprefix[$i];
                        $GetFCity    = $fcity[$i];
                        $GetFTarif    = $ftariif[$i];
                        $GetTCountry    = $tcountry[$i];
                        $GetTAccessType   = $taccesstype[$i];
                        $GetTPrefix    = $tprefix[$i];
                        $GetTCity    = $tcity[$i];
                        $GetTTarif    = $ttariif[$i];
                        $GetPackage    =  $PackageComponent[$i];


                        $addComponents['Component'] = implode(",", $GetComponent);
                        $addComponents['Origination'] = $GetOrigination;
                        $addComponents['TimezonesID'] = $GetTimeOfDay;
                        $addComponents['Action'] = $GetAction;
                        $addComponents['MergeTo'] = $GetMergeTo;
                        $addComponents['ToTimezonesID'] = $GetToTimeOfDay;
                        $addComponents['ToOrigination'] = $GetToOrigination;
                        $addComponents['FromCountryID'] = $GetFCountry;
                        $addComponents['FromAccessType'] = $GetFAccessType;
                        $addComponents['FromPrefix'] = $GetFPrefix;
                        $addComponents['FromCity'] = $GetFCity;
                        $addComponents['FromTariff'] = $GetFTarif;
                        $addComponents['RateGeneratorId'] = $rateg->RateGeneratorId;
                        $addComponents['ToCountryID'] = $GetTCountry;
                        $addComponents['ToAccessType'] = $GetTAccessType;
                        $addComponents['ToPrefix'] = $GetTPrefix;
                        $addComponents['ToCity'] = $GetTCity;
                        $addComponents['ToTariff'] = $GetTTarif;
                        $addComponents['Package'] = $GetPackage;


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

                        $addCalRate['Component']       = implode(",", $rComponent[$i]);
                        $addCalRate['Origination']     = $rOrigination[$i];
                        $addCalRate['TimezonesID']     = $rTimeOfDay[$i];
                        $addCalRate['RateLessThen']    = $rRateLessThen[$i];
                        $addCalRate['ChangeRateTo']    = $rChangeRateTo[$i];
                        $addCalRate['RateGeneratorId'] = $rateg->RateGeneratorId;
                        $addCalRate['CountryID']       = $rCountry[$i];
                        $addCalRate['AccessType']      = $rAccessType[$i];
                        $addCalRate['Prefix']          = $rPrefix[$i];
                        $addCalRate['City']            = $rCity[$i];
                        $addCalRate['Tariff']          = $rTarrif[$i];
                        $addCalRate['Package']         =  $rPackage[$i];




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
            $Products = ServiceTemplate::where([
                "CompanyID" => User::get_companyID()
            ])->lists("Name", "ServiceTemplateId");

            $rategenerators = RateGenerator::where([
                "RateGeneratorId" => $id,
                "CompanyID" => $companyID
            ])->first();


            $rategenerator_rules = RateRule::with('RateRuleMargin', 'RateRuleSource', 'Country')->where([
                "RateGeneratorId" => $id
            ]) ->orderBy("Order", "asc")->get();

            //dd($rategenerator_rules);
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

            // $country = ServiceTemplate::Join('tblCountry', function($join) {
            //     $join->on('tblServiceTemplate.country','=','tblCountry.country');
            //   })->select('tblServiceTemplate.country AS country','tblCountry.countryID As CountryID')->where("tblServiceTemplate.CompanyID",User::get_companyID())
            //     ->orderBy('tblServiceTemplate.country')->lists("country", "CountryID");

            $country            = ServiceTemplate::getCountryDD($companyID);
            $AccessType         = ServiceTemplate::getAccessTypeDD($companyID);
            $City               = ServiceTemplate::getCityDD($companyID);
            $Tariff             = ServiceTemplate::getTariffDD($companyID);
            $Prefix             = ServiceTemplate::getPrefixDD($companyID);  
            // $CityTariffFilter = [];
            // foreach($CityTariff as $key => $City){
            //     if(strpos($City, " per ")){
            //         $CityTariffFilter[$City] = $City;
            //         unset($CityTariff[$key]);
            //     }
            // }
            //$CityTariff = array_merge($CityTariff, $CityTariffFilter);

            $country = array('' => "All") + $country;
            $AccessType = array('' => "All") + $AccessType;
            $Prefix = array('' => "All") + $Prefix;
            $City = array('' => "All") + $City;
            $Tariff = array('' => "All") +  $Tariff;
           
            //unset($AllTypes[3]);
            $Package = array('' => "All") + Package::where([
                "status" => 1,
                "CompanyID" => User::get_companyID()
            ])->lists("Name", "PackageId");

            $ResellerDD  = RateTable::getResellerDropdownIDList();

            // Debugbar::info($rategenerator_rules);
            return View::make('rategenerators.edit', compact('id', 'Products','Package', 'rategenerators', 'rategeneratorComponents' ,'AllTypes' ,'Categories' ,'rategenerator', 'rateGeneratorCalculatedRate', 'rategenerator_rules','codedecklist', 'trunks','array_op','currencylist','Timezones','country','AccessType','Prefix','City','country_rule','Tariff','ResellerDD'));
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
        $getNumberString = @$data['getIDs'];
        $getRateNumberString = @$data['getRateIDs'];

        unset($data['SelectType']);


        $SelectTypes = RateGenerator::select([
            "SelectType"
        ])->where(['RateGeneratorId' => $id ])->get();

        foreach($SelectTypes as $Type){
            $SelectType = $Type->SelectType;
        }

        

        if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL)) {
            $rules = array(
                'CompanyID' => 'required',
                'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,' . $RateGenerator->RateGeneratorId . ',RateGeneratorID,CompanyID,' . $data['CompanyID'],
                'Timezones' => 'required',
                'CurrencyID' => 'required',
                'Policy' => 'required',
                'LessThenRate' => 'numeric',
                'ChargeRate' => 'numeric',
                'percentageRate' => 'numeric',
            );

            if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL)){
                $rules['codedeckid']='required';
            }

        } else {
            $rules = array(
                'CompanyID'         => 'required',
                'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,' . $RateGenerator->RateGeneratorId . ',RateGeneratorID,CompanyID,' . $data['CompanyID'],
                'CurrencyID'        => 'required',
                'DateFrom'          => 'required|date|date_format:Y-m-d',
                'DateTo'            => 'required|date|date_format:Y-m-d',
                'Calls'             => 'numeric',
                'Minutes'           => 'numeric',
                'TimezonesPercentage'   => 'numeric',
                'OriginationPercentage' => 'numeric',
                'percentageRate'    => 'numeric',
            );
        }

        if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL)) {
            $rules['TrunkID']='required';
            $rules['RatePosition']='required|numeric';
            $rules['UseAverage']='required';
        }
        if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID)){
            $rules['RatePosition']='required|numeric';
            $rules['Category']='required';
            $rules['NoOfServicesContracted']='numeric';
        }
        if($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE)){
            $rules['RatePosition']='required|numeric';
        }
        if ($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID) || $SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_VOICECALL)) {
                unset($data['PackageID']);
        }
        if(isset($data['AppliedTo']) && $data['AppliedTo'] == RateTable::APPLIED_TO_RESELLER){
            $rules['Reseller'] = 'required';
        }

        $message = array(
            'Timezones.required' => 'Please select at least 1 Timezone',
            'ProductID.required' => 'Please select product.',
            'TimezonesPercentage.numeric' => 'Please enter valid numeric value of Time Of Day Percentage.',
            'Reseller.required' => 'The partner field is required',
            'NoOfServicesContracted.numeric' => 'The number of services contracted must be a number'
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

            if ($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID) ||$SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE)) {

                $numberArray = array_unique(explode(",", $getNumberString));
                $GetComponent = array();
                $addComponents = array();

                $i = 0;

                for ($i; $i < sizeof($numberArray) - 1; $i++) {
                    if(empty($data['Component-'. $numberArray[$i]]) ||
                        empty($data['TimeOfDay-'. $numberArray[$i]]) ||
                        empty($data['Action-'. $numberArray[$i]]) ||
                        empty($data['MergeTo-'. $numberArray[$i]]) ||
                        empty($data['ToTimeOfDay-'. $numberArray[$i]])){
                        return Response::json(array(
                            "status" => "failed",
                            "message" => "Merge components Value is missing."
                        ));
                    }
                    $GetAllcomponts[$i] = 'Component-' . $numberArray[$i];

                    if (!isset($data[$GetAllcomponts[$i]])) {
                        unset($data['Component-' . $numberArray[$i]]);
                        unset($data['Origination-' . $numberArray[$i]]);
                        unset($data['TimeOfDay-' . $numberArray[$i]]);
                        unset($data['Action-' . $numberArray[$i]]);
                        unset($data['MergeTo-' . $numberArray[$i]]);
                        unset($data['ToOrigination-' . $numberArray[$i]]);
                        unset($data['ToTimeOfDay-' . $numberArray[$i]]);
                        unset($data['FCountry-' . $numberArray[$i]]);
                        unset($data['TCountry-' . $numberArray[$i]]);
                        unset($data['FAccessType-' . $numberArray[$i]]);
                        unset($data['TAccessType-' . $numberArray[$i]]);
                        unset($data['FPrefix-' . $numberArray[$i]]);
                        unset($data['TPrefix-' . $numberArray[$i]]);
                        unset($data['FCity-' . $numberArray[$i]]);
                        unset($data['TCity-' . $numberArray[$i]]);
                        unset($data['FTariff-' . $numberArray[$i]]);
                        unset($data['TTariff-' . $numberArray[$i]]);
                        unset($data['Package-' . $numberArray[$i]]);
                    } else {                       
                        if(isset($data['FCountry-' . $numberArray[$i]]) && $data['FCountry-' . $numberArray[$i]] == ''){
                            $data['FCountry-' . $numberArray[$i]] = null;
                        }
                        if(isset($data['TCountry-' . $numberArray[$i]]) && $data['TCountry-' . $numberArray[$i]] == ''){
                            $data['TCountry-' . $numberArray[$i]] = null;
                        }
                        

                        $componts[] = $data['Component-' . $numberArray[$i]];
                        $origination[] = @$data['Origination-' . $numberArray[$i]];
                        $timeofday[] = $data['TimeOfDay-' . $numberArray[$i]];
                        $action[] = $data['Action-' . $numberArray[$i]];
                        $mergeTo[] = $data['MergeTo-' . $numberArray[$i]];
                        $originationTo[] = @$data['ToOrigination-' . $numberArray[$i]];
                        $timeofdayTo[] = $data['ToTimeOfDay-' . $numberArray[$i]];
                        $fcountry[]       = $data['FCountry-' . $numberArray[$i]];
                        $tcountry[]       = $data['TCountry-' . $numberArray[$i]];
                        $faccesstype[]    = $data['FAccessType-' . $numberArray[$i]];
                        $taccesstype[]    = $data['TAccessType-' . $numberArray[$i]];
                        $fprefix[]        = $data['FPrefix-' . $numberArray[$i]];
                        $tprefix[]        = $data['TPrefix-' . $numberArray[$i]];
                        $fcity[]          = $data['FCity-' . $numberArray[$i]];
                        $tcity[]          = $data['TCity-' . $numberArray[$i]];
                        $ftariif[]        = $data['FTariff-' . $numberArray[$i]];
                        $ttariif[]        = $data['TTariff-' . $numberArray[$i]];
                        $PackageComponent[]  = $data['Package-' . $numberArray[$i]];

                       
                    }

                    unset($data['Component-' . $numberArray[$i]]);
                    unset($data['Origination-' . $numberArray[$i]]);
                    unset($data['TimeOfDay-' . $numberArray[$i]]);
                    unset($data['Action-' . $numberArray[$i]]);
                    unset($data['MergeTo-' . $numberArray[$i]]);
                    unset($data['ToOrigination-' . $numberArray[$i]]);
                    unset($data['ToTimeOfDay-' . $numberArray[$i]]);
                    unset($data['FCountry-' . $numberArray[$i]]);
                    unset($data['TCountry-' . $numberArray[$i]]);
                    unset($data['FAccessType-' . $numberArray[$i]]);
                    unset($data['TAccessType-' . $numberArray[$i]]);
                    unset($data['FPrefix-' . $numberArray[$i]]);
                    unset($data['TPrefix-' . $numberArray[$i]]);
                    unset($data['FCity-' . $numberArray[$i]]);
                    unset($data['TCity-' . $numberArray[$i]]);
                    unset($data['FTariff-' . $numberArray[$i]]);
                    unset($data['TTariff-' . $numberArray[$i]]);
                    unset($data['Package-' . $numberArray[$i]]);
                }

                unset($data['getIDs']);
                unset($data['Category']);
                if (!empty($data['AllComponent'])) {
                    $data['SelectedComponents'] = implode(",", $data['AllComponent']);
                }

                $calculatedRates = array_unique(explode(",", $getRateNumberString));

                for ($i = 0; $i < sizeof($calculatedRates) - 1; $i++) {
                    $GetRateComponents[$i] = 'RateComponent-' . $calculatedRates[$i];

                    if (!isset($data[$GetRateComponents[$i]])) {
                        unset($data['RateComponent-' . $calculatedRates[$i]]);
                        unset($data['RateOrigination-' . $calculatedRates[$i]]);
                        unset($data['RateTimeOfDay-' . $calculatedRates[$i]]);
                        unset($data['RateLessThen-' . $calculatedRates[$i]]);
                        unset($data['ChangeRateTo-' . $calculatedRates[$i]]);
                        unset($data['Country1-' . $calculatedRates[$i]]);
                        unset($data['AccessType1-' . $calculatedRates[$i]]);
                        unset($data['Prefix1-' . $calculatedRates[$i]]);
                        unset($data['City1-' . $calculatedRates[$i]]);
                        unset($data['Tariff1-' . $calculatedRates[$i]]);
                        unset($data['Package1-' . $calculatedRates[$i]]);
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

                        if(isset($data['Country1-' . $calculatedRates[$i]]) && $data['Country1-' . $calculatedRates[$i]] == ''){
                            $data['Country1-' . $calculatedRates[$i]] = null;
                        }
                        

                        $rComponent[]    = $data['RateComponent-' . $calculatedRates[$i]];
                        $rOrigination[]  = @$data['RateOrigination-' . $calculatedRates[$i]];
                        $rTimeOfDay[]    = $data['RateTimeOfDay-' . $calculatedRates[$i]];
                        $rRateLessThen[] = $data['RateLessThen-' . $calculatedRates[$i]];
                        $rChangeRateTo[] = $data['ChangeRateTo-' . $calculatedRates[$i]];
                        $rPackage[]      = $data['Package1-' . $calculatedRates[$i]];


                        $rCountry[] = $data['Country1-' . $calculatedRates[$i]];
                        $rAccessType[] = $data['AccessType1-' . $calculatedRates[$i]];
                        $rPrefix[] = $data['Prefix1-' . $calculatedRates[$i]];
                        $rCity[] = $data['City1-' . $calculatedRates[$i]];
                        $rTarrif[] = $data['Tariff1-' . $calculatedRates[$i]];
                    }

                    unset($data['RateComponent-' . $calculatedRates[$i]]);
                    unset($data['RateOrigination-' . $calculatedRates[$i]]);
                    unset($data['RateTimeOfDay-' . $calculatedRates[$i]]);
                    unset($data['RateLessThen-' . $calculatedRates[$i]]);
                    unset($data['ChangeRateTo-' . $calculatedRates[$i]]);
                    unset($data['Country1-' . $calculatedRates[$i]]);
                    unset($data['AccessType1-' . $calculatedRates[$i]]);
                    unset($data['Prefix1-' . $calculatedRates[$i]]);
                    unset($data['City1-' . $calculatedRates[$i]]);
                    unset($data['Tariff1-' . $calculatedRates[$i]]);
                    unset($data['Package1-' . $calculatedRates[$i]]);
                }

                unset($data['getRateIDs']);
                unset($data['RateComponent-1']);
                unset($data['RateOrigination-1']);
                unset($data['RateTimeOfDay-1']);
                unset($data['RateLessThen-1']);
                unset($data['ChangeRateTo-1']);
                unset($data['Country1-1']);
                unset($data['AccessType1-1']);
                unset($data['Prefix1-1' ]);
                unset($data['City1-1']);
                unset($data['Tariff1-1']);
                unset($data['Package1-1']);
            } else {
                unset($data['getIDs']);
                unset($data['Component-1']);
                unset($data['Action-1']);
                unset($data['Origination-1']);
                unset($data['TimeOfDay-1']);
                unset($data['MergeTo-1']);
                unset($data['ToOrigination-1']);
                unset($data['ToTimeOfDay-1']);
                unset($data['Category']);
                unset($data['RateComponent-1']);
                unset($data['RateOrigination-1']);
                unset($data['RateTimeOfDay-1']);
                unset($data['RateLessThen-1']);
                unset($data['ChangeRateTo-1']);
                unset($data['getRateIDs']);
                unset($data['FCountry-1']);
                unset($data['TCountry-1']);
                unset($data['FAccessType-1']);
                unset($data['TAccessType-1']);
                unset($data['FPrefix-1']);
                unset($data['TPrefix-1']);
                unset($data['FCity-1']);
                unset($data['TCity-1']);
                unset($data['FTariff-1']);
                unset($data['TTariff-1']);
                unset($data['Country1-1']);
                unset($data['AccessType1-1']);
                unset($data['Prefix1-1' ]);
                unset($data['City1-1']);
                unset($data['Tariff1-1']);
                unset($data['Package1-1']);
                unset($data['Package-1']);
                
            }

            unset($data['AllComponent']);

            if(isset($data['CountryID']) && $data['CountryID'] == ''){
                $data['CountryID'] = null;
            }
           

            if ($RateGenerator->update($data)) {
                $CostComponentSaved = "Updated";

                RateGeneratorComponent::where("RateGeneratorId", $id)->delete();
                RateGeneratorCalculatedRate::where("RateGeneratorId", $id)->delete();

                if ($SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID) ||$SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE)) {

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
                        $GetToOrigination = $originationTo[$i];
                        $GetToTimeOfDay   = $timeofdayTo[$i];
                        $GetFCountry    = $fcountry[$i];
                        $GetFAccessType    = $faccesstype[$i];
                        $GetFPrefix    = $fprefix[$i];
                        $GetFCity    = $fcity[$i];
                        $GetFTarif    = $ftariif[$i];
                        $GetTCountry    = $tcountry[$i];
                        $GetTAccessType   = $taccesstype[$i];
                        $GetTPrefix    = $tprefix[$i];
                        $GetTCity    = $tcity[$i];
                        $GetTTarif    = $ttariif[$i];
                        $GetPackage    =  $PackageComponent[$i];

                        $addComponents['Component'] = implode(",", $GetComponent);
                        $addComponents['Origination'] = $GetOrigination;
                        $addComponents['TimezonesID'] = $GetTimeOfDay;
                        $addComponents['Action'] = $GetAction;
                        $addComponents['MergeTo'] = $GetMergeTo;
                        $addComponents['ToTimezonesID'] = $GetToTimeOfDay;
                        $addComponents['ToOrigination'] = $GetToOrigination;
                        $addComponents['RateGeneratorId'] = $RateGeneratorID;
                        $addComponents['FromCountryID'] = $GetFCountry;
                        $addComponents['FromAccessType'] = $GetFAccessType;
                        $addComponents['FromPrefix'] = $GetFPrefix;
                        $addComponents['FromCity'] = $GetFCity;
                        $addComponents['FromTariff'] = $GetFTarif;
                        $addComponents['ToCountryID'] = $GetTCountry;
                        $addComponents['ToAccessType'] = $GetTAccessType;
                        $addComponents['ToPrefix'] = $GetTPrefix;
                        $addComponents['ToCity'] = $GetTCity;
                        $addComponents['ToTariff'] = $GetTTarif;
                        $addComponents['Package'] = $GetPackage;

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

                        $addCalRate['Component']       = implode(",", $rComponent[$i]);
                        $addCalRate['Origination']     = $rOrigination[$i];
                        $addCalRate['TimezonesID']     = $rTimeOfDay[$i];
                        $addCalRate['RateLessThen']    = $rRateLessThen[$i];
                        $addCalRate['ChangeRateTo']    = $rChangeRateTo[$i];
                        $addCalRate['RateGeneratorId'] = $RateGeneratorID;
                        $addCalRate['CountryID']       = $rCountry[$i];
                        $addCalRate['AccessType']      = $rAccessType[$i];
                        $addCalRate['Prefix']          = $rPrefix[$i];
                        $addCalRate['City']            = $rCity[$i];
                        $addCalRate['Tariff']          = $rTarrif[$i];
                        $addCalRate['Package']         =  $rPackage[$i];

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
        // If type is Voice Call
        if(@$data['Type'] == RateGenerator::VoiceCall && @$data['TrunkID'] > 0) {
            $filterdata['Type']       = intval($data['Type']);
            $filterdata['TrunkID']    = intval($data['TrunkID']);
            $filterdata['CodeDeckId'] = intval($data['CodeDeckId']);
            $filterdata['AppliedTo']  = intval($data['AppliedTo']);
            //$filterdata['NotVendor']  = true;
            $rate_table = RateTable::getRateTableCache($filterdata);
        } elseif(@$data['Type'] == RateGenerator::DID) {
            $filterdata['Type']       = intval($data['Type']);
            $filterdata['CodeDeckId'] = intval($data['CodeDeckId']);
            $filterdata['AppliedTo']  = intval($data['AppliedTo']);
            //$filterdata['NotVendor']  = true;
            $rate_table = RateTable::getRateTableCache($filterdata);
        } elseif(@$data['Type'] == RateGenerator::Package) {
            $filterdata['Type']       = intval($data['Type']);
            $filterdata['AppliedTo']  = intval($data['AppliedTo']);
            //$filterdata['NotVendor']  = true;

            $rate_table = RateTable::getRateTableCache($filterdata);
        } else {
            $rate_table = ['' => "Select"];
        }

        return View::make('rategenerators.ajax_rate_table_dropdown', compact('rate_table'));
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