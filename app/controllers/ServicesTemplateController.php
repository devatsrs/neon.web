<?php


class ServicesTemplateController extends BaseController {

    private $users;

    public function __construct() {

    }


    public function ajax_datagrid(){

       $data = Input::all();
        Log::info('servicesTemplate ajax_datagrid AJAX data.' . print_r($data,true));
       $companyID = User::get_companyID();


        $iSortCol_0 = isset($data['iSortCol_0']) ? $data['iSortCol_0']:1;
        $data['ServiceName'] = isset($data['ServiceName']) ? $data['ServiceName']:'';
        $data['ServiceId'] = isset($data['ServiceId']) && $data['ServiceId'] != '' ? $data['ServiceId']:0;
        $data['CountryID'] = isset($data['CountryID']) ? $data['CountryID']:'';
        $data['AccessType'] = isset($data['AccessType']) ? $data['AccessType']:'';
        $data['Prefix'] = isset($data['Prefix']) ? $data['Prefix']:'';
        $data['City'] = isset($data['City']) ? $data['City']:'';
        $data['Tariff'] = isset($data['Tariff']) ? $data['Tariff']:'';


        $sSortDir_0 = '';
        if (isset($data['sSortDir_0'])) {
            $sSortDir_0 = $data['sSortDir_0'];
        }else {
            $sSortDir_0 = "ASC";
        }

        if ($iSortCol_0 == 1 || $iSortCol_0 == 0) {
            $iSortCol_0 = "Name";
        }else if ($iSortCol_0 == 2) {
            $iSortCol_0 = "Name";
        }else if ($iSortCol_0 == 3) {
            $iSortCol_0 = "ServiceName";
        }else if ($iSortCol_0 == 4) {
            $iSortCol_0 = "country";
        }else if ($iSortCol_0 == 5) {
            $iSortCol_0 = "prefixName";
        }else if ($iSortCol_0 == 6) {
            $iSortCol_0 = "accessType";
        }else if ($iSortCol_0 == 7) {
            $iSortCol_0 = "City";
        }else if ($iSortCol_0 == 8) {
            $iSortCol_0 = "Tariff";
        }

        $data['iDisplayStart'] += 1;
        $query = "call prc_getServiceTemplate(" . $companyID . ","
            . "'" .$data['ServiceName']."',"
            . "'" .$data['ServiceId']."',"
            . "'" .$data['CountryID']."',"
            . "'" .$data['AccessType']."',"
            . "'" .$data['Prefix']."',"
            . "'" .$data['City']."',"
            . "'" .$data['Tariff']."',"
            . "'" .(ceil($data['iDisplayStart'] / $data['iDisplayLength']))."',"
            . "'" .$data['iDisplayLength']."',"
            . "'" .$iSortCol_0."',"
            . "'" .$sSortDir_0."',"
            . "'" .'0'."'"
            . ")";

        Log::info('servicesTemplate ajax_datagrid AJAX data.' . $query);

       return DataTableSql::of($query)->make();
    }
    public function selectDataOnCurrency()
    {
        $data = Input::all();


        $selecteddata = $data['selectedData'];
        $companyID = User::get_companyID();
        // $data['ServiceStatus'] = $data['ServiceStatus']== 'true'?1:0;

        $servicesTemplate = Service::select(['tblService.ServiceId','tblService.ServiceName'])
            ;

        //Log::info('$servicesTemplate AJAX.' . $servicesTemplate->toSql());
        //Log::info('selectedCurrency' . $data['selectedCurrency']);


        $currenciesservices =  $servicesTemplate->get();

        $outboundDiscountPlan = DiscountPlan::
            select(['tblDiscountPlan.DiscountPlanID','tblDiscountPlan.Name'])
        ;

        //Log::info('$outboundDiscountPlan query.' . $outboundDiscountPlan->toSql());


       /* if($data['selectedCurrency'] != ''){
            $outboundDiscountPlan->where('tblDiscountPlan.CurrencyID','=', $data['selectedCurrency']);
        }*/
        $outbounddiscountplan =  $outboundDiscountPlan->get();

        /*
        $inboundDiscountPlan = DiscountPlan::
            select(['tblDiscountPlan.DiscountPlanID','tblDiscountPlan.Name'])
        ;
        Log::info('$outboundDiscountPlan query.' . $inboundDiscountPlan->toSql());
        if($data['selectedCurrency'] != ''){
            $inboundDiscountPlan->where('tblDiscountPlan.CurrencyID','=', $data['selectedCurrency']);
        }
        */
        $inbounddiscountplan =  $outbounddiscountplan;

        $rateTable = RateTable::select(["RateTableName", "RateTableId"]);
        $rateTable->where('Type','=', '1');
        $rateTable->where('AppliedTo','!=',2 );

        //Log::info('$rate table query.' . $rateTable->toSql());
        $outboundtarifflist =  $rateTable->get();


        $BillingSubscription = BillingSubscription::select(['Name','SubscriptionID']);
        //Log::info('$billing subscription query.' . $BillingSubscription->toSql());
        $billingsubscriptionlist = $BillingSubscription->get();

        $categoryTariff = RateTable::leftjoin('tblDIDCategory', 'tblDIDCategory.DIDCategoryID', '=', 'tblRateTable.DIDCategoryID');
        $categoryTariff->select(['tblRateTable.RateTableName as RateTableName','tblRateTable.RateTableID as RateTableID']);
                $categoryTariff->where('tblRateTable.Type', '=', '2');
                $categoryTariff->where('tblRateTable.AppliedTo', '!=', 2);

            if (isset($data['selected_didCategory']) && $data['selected_didCategory'] != '') {
                $categoryTariff->where('tblRateTable.DIDCategoryID', '=', $data['selected_didCategory']);
                Log::info('data[selected_didCategory].' . $data['selected_didCategory']);
            }

        //Log::info('$rate table query.' . $categoryTariff->toSql());
        $categorytarifflist = $categoryTariff->get();
        //Log::info('$rate table query.' . count($categorytarifflist));
        $billingsubsforsrvtemplate = array();
        $selecteddidcategorytariflist= array();
        if(isset($data['editServiceTemplateID'])){
            $selectedSubcriptionID = '';
            $selectedTemplateSubscription = ServiceTemplate::join('tblServiceTemapleSubscription', 'tblServiceTemapleSubscription.ServiceTemplateID', '=', 'tblServiceTemplate.ServiceTemplateId');
            $selectedTemplateSubscription->select(['tblServiceTemapleSubscription.SubscriptionId as SubscriptionId']);
            if($data['selectedCurrency'] != ''){
                $selectedTemplateSubscription->where('tblServiceTemapleSubscription.ServiceTemplateID','=', $data['editServiceTemplateID']);
                Log::info('$Selected Template Subscription.' . $selectedTemplateSubscription->toSql());
                $selectedTemplateSubscriptionlist = $selectedTemplateSubscription->get();
                foreach ($selectedTemplateSubscriptionlist as $selectedTemplateSubscriptionsingle) {
                    $selectedSubcriptionID = $selectedSubcriptionID .$selectedTemplateSubscriptionsingle["SubscriptionId"] . ",";
                }
                Log::info('update $selectedSubcriptionID.' . $selectedSubcriptionID);
                $selectedSubcriptionID = explode(',', $selectedSubcriptionID);
                $BillingSubsForSrvTemplate = BillingSubscription::select(['Name','SubscriptionID']);
                $BillingSubsForSrvTemplate->whereIn('SubscriptionID',$selectedSubcriptionID);
                Log::info('$selectedSubcriptionID query.' . count($selectedSubcriptionID));
                Log::info('$billing subscription query.' . $BillingSubsForSrvTemplate->toSql());
                $billingsubsforsrvtemplate = $BillingSubsForSrvTemplate->get();
                Log::info('$billing subscription count.' . count($billingsubsforsrvtemplate));
            }

            $selectedDIDCategoryTariffQuery = 'select tariff.RateTableId as RateTableID,tariff.DIDCategoryId as DIDCategoryID,(select didCat.CategoryName from tblDIDCategory didCat where didCat.DIDCategoryID = tariff.DIDCategoryId) as CategoryName,(select rate.RateTableName from tblRateTable rate where rate.RateTableId = tariff.RateTableId) as RateTableName from tblServiceTemapleInboundTariff tariff where tariff.ServiceTemplateID ='.$data['editServiceTemplateID'];
            //Log::info('$selectedDIDCategoryTariffQuery query.' . $selectedDIDCategoryTariffQuery);
            $selecteddidcategorytariflist = DB::select($selectedDIDCategoryTariffQuery);
            //Log::info('$selecteddidcategorytariflist count.' . count($selecteddidcategorytariflist));
        }

        return View::make('servicetemplate.populatedataoncurrency', compact('currenciesservices','selecteddata','outbounddiscountplan','inbounddiscountplan','outboundtarifflist','billingsubscriptionlist','categorytarifflist','billingsubsforsrvtemplate','selecteddidcategorytariflist'));

    }

    public function index() {

            $CompanyID  = User::get_companyID();
            $CategoryDropdownIDList = DIDCategory::getCategoryDropdownIDList($CompanyID);
            $servicesTemplate = Service::lists('ServiceName','ServiceID');
            $rateTable = RateTable::where('Type','=', '1')->where('AppliedTo','!=',2 )->lists('RateTableName','RateTableId');
//
            $outboundDiscountPlan = DiscountPlan::lists('Name','DiscountPlanID');
            $inbounddiscountplan =   DiscountPlan::lists('Name','DiscountPlanID');
            $BillingSubsForSrvTemplate = BillingSubscription::lists('Name','SubscriptionID');
            $RateType = RateType::select('RateTypeID','Title')->lists('Title','RateTypeID');
            $country            = ServiceTemplate::getCountryDDForProduct($CompanyID);
            $AccessType         = ServiceTemplate::getAccessTypeDD($CompanyID);
            $City               = ServiceTemplate::getCityDD($CompanyID);
            $Tariff             = ServiceTemplate::getTariffDD($CompanyID);
            $Prefix             = ServiceTemplate::getPrefixDD($CompanyID);
                // $CityTariffFilter = [];
            // foreach($CityTariff as $key => $City){
            //     if(strpos($City, " per ")){
            //         $CityTariffFilter[$City] = $City;
            //         unset($CityTariff[$key]);
            //     }
            // }
            //$CityTariff = array_merge($CityTariff, $CityTariffFilter);
            $DiscountPlanVOICECALL = DiscountPlan::getDropdownIDListForRateType(RateType::VOICECALL_ID);
            $DiscountPlanPACKAGE = DiscountPlan::getDropdownIDListForRateType(RateType::PACKAGE_ID);
            $DiscountPlanDID = DiscountPlan::getDropdownIDListForRateType(RateType::DID_ID);


            $country = array('' => "All") + $country;
            $AccessType = array('' => "All") + $AccessType;
            $Prefix = array('' => "All") + $Prefix;
            $City = array('' => "All") + $City;
            $RateType = array('' => "All") + $RateType;
            $rateTable  = array('' => "Select") + $rateTable;
            $Tariff  = array('' => "All") + $Tariff;

            

            return View::make('servicetemplate.index', compact('CategoryDropdownIDList','servicesTemplate','rateTable','outboundDiscountPlan','inbounddiscountplan','BillingSubsForSrvTemplate','country','AccessType','Prefix','City','Tariff','RateType','DiscountPlanVOICECALL','DiscountPlanPACKAGE','DiscountPlanDID','City','Tariff'));

    }

    public function store() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['CompanyID'] = $CompanyID;
        $subsriptionList = isset($data['selectedSubscription'])?$data['selectedSubscription']:'';
        $CategoryTariffList = isset($data['selectedcategotyTariff'])?$data['selectedcategotyTariff']:'';
        $subsriptionList = trim($subsriptionList);
        $CategoryTariffList = trim($CategoryTariffList);
        if (ends_with($subsriptionList,',') ) {
            $subsriptionList = substr($subsriptionList,0,strlen($subsriptionList) - 1);
        }
        if (ends_with($CategoryTariffList,',') ) {
            $CategoryTariffList = substr($CategoryTariffList,0,strlen($CategoryTariffList) - 1);
        }


        $subsriptionList = explode(",",$subsriptionList);
        $CategoryTariffList = explode(",",$CategoryTariffList);
        $OutboundDiscountPlanId = isset($data['OutboundDiscountPlanID123'])?$data['OutboundDiscountPlanID123']:'';
        $ContractDuration = isset($data['ContractDuration'])&&$data['ContractDuration']!=""?$data['ContractDuration']:null;
        $CancellationFee = isset($data['CancellationFee'])&&$data['CancellationFee']!=""?$data['CancellationFee']:null;
        $InboundDiscountPlanId = isset($data['InboundDiscountPlanID123'])?$data['InboundDiscountPlanID123']:'';
        $PackageDiscountPlanId = isset($data['PackageDiscountPlanId'])?$data['PackageDiscountPlanId']:'';
        $CurrencyId = isset($data['CurrencyId'])?$data['CurrencyId']:'';


        /*
        foreach ($subsriptionList as $index2 => $subsription) {
            Log::info('Subscription List.' . $subsription);
        }



        foreach ($CategoryTariffList as $index1 => $CategoryTariffValue) {
            Log::info('Category Tarif Value.' . $CategoryTariffValue);
            $DIDRateTableList = explode(":",$CategoryTariffValue);
            Log::info('Category Tarif Value.' . $DIDRateTableList[0]);
            Log::info('Category Tarif Value.' . $DIDRateTableList[1]);
        }*/

                if(!empty($data)){
                    $user_id = User::get_userID();
                    $data['CompanyID'] = User::get_companyID();
                    $data['Status'] = isset($data['Status']) ? 1 : 0;
                    $data['AutomaticRenewal'] = isset($data['AutomaticRenewal']) ? 1 : 0;

                   // ServiceTemplate::$rules['ServiceId'] = 'required';
                   // ServiceTemplate::$rules['Name'] = 'required';
                  //  ServiceTemplate::$rules['SubscriptionID'] = 'required';
                   // ServiceTemplate::$rules['CurrencyId'] = 'required';

                    ServiceTemplate::$rules['Name'] = 'required|unique:tblServiceTemplate';
                    ServiceTemplate::$rules['ContractDuration'] = 'numeric';
                    ServiceTemplate::$rules['CancellationCharges'] = 'required|numeric';

                    $niceNames = ['CancellationFee' => 'Cancellation Fee'];
                    if(isset($data['CancellationCharges']) && $data['CancellationCharges'] != 2 && $data['CancellationCharges'] != 5) {
                        ServiceTemplate::$rules['CancellationFee'] = 'required|numeric';
                        if($data['CancellationCharges'] == 3){
                            $niceNames = ['CancellationFee' => "Cancellation Fee Percentage"];
                        }
                    }
                    $validator = Validator::make($data, ServiceTemplate::$rules);
                    $validator->setAttributeNames($niceNames);

                    if ($validator->fails()) {
                        return json_validator_response($validator);
                    }

                    if(isset($data['DynamicFields'])) {
                        $j=0;
                        $companyID = User::get_companyID();
                        foreach($data['DynamicFields'] as $key => $value) {
                            $key = (int) $key;
                            if(isset($_FILES["DynamicFields"]["name"][$key])){
                                $dynamicImage = $_FILES["DynamicFields"]["name"][$key];
                                if($dynamicImage){
                                    $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID)."/";
                                    $fileUrl=$companyID."/dynamicfields/";
                                    if (!file_exists($upload_path.$fileUrl)) {
                                        mkdir($upload_path.$fileUrl, 0777, true);
                                    }
                                    $dynamicImage=time().$dynamicImage;
                                    $success=move_uploaded_file($_FILES["DynamicFields"]["tmp_name"][$key],$upload_path.$fileUrl.$dynamicImage);
                                    if($success){
                                        $DynamicFields[$j]['FieldValue']=$fileUrl.$dynamicImage;
                                    }else{
                                        $DynamicFields[$j]['FieldValue']="";
                                        return Response::json(array("status" => "failed", "message" => "Error: There was a problem uploading your file. Please try again."));
                                    }
                                }
                            }else{
                                if(!empty($data['DynamicFields'][$key])){
                                    $DynamicFields[$j]['FieldValue'] = trim($data['DynamicFields'][$key]);
                                } else {
                                    $DynamicFields[$j]['FieldValue'] = "";
                                }
                            }

                            $DynamicFields[$j]['DynamicFieldsID'] = $key;
                            $DynamicFields[$j]['CompanyID'] = $companyID;
                            $DynamicFields[$j]['created_at'] = date('Y-m-d H:i:s.000');
                            $DynamicFields[$j]['created_by'] = User::get_user_full_name();
                            $j++;
                        }
                        unset($data['DynamicFields']);
                    }

                    if(isset($data['hDynamicFields'])){
                        unset($data['hDynamicFields']);
                    }

                    if(isset($DynamicFields)) {
                        if ($error = DynamicFieldsValue::validate($DynamicFields)) {
                            return $error;
                        }
                    }

                    if (isset($data['ServiceId']) && $data['ServiceId'] != '') {
                        $ServiceTemplateData['ServiceId'] = $data['ServiceId'];
                    }
                    $ServiceTemplateData['Name'] = $data['Name'];
                    $ServiceTemplateData['CompanyID'] = $CompanyID;
                    if ($OutboundDiscountPlanId != '') {
                        $ServiceTemplateData['OutboundDiscountPlanId'] = $OutboundDiscountPlanId;
                    }
                    if ($InboundDiscountPlanId != '') {
                        $ServiceTemplateData['InboundDiscountPlanID'] = $InboundDiscountPlanId;
                    }
                    if ($PackageDiscountPlanId != '') {
                        $ServiceTemplateData['PackageDiscountPlanId'] = $PackageDiscountPlanId;
                    }
                    if (isset($data['OutboundRateTableId']) && $data['OutboundRateTableId'] != '') {
                        $ServiceTemplateData['OutboundRateTableId'] = $data['OutboundRateTableId'];
                    }



                    $ServiceTemplateData['AutomaticRenewal']    = $data['AutomaticRenewal'];
                    $ServiceTemplateData['CancellationCharges'] = $data['CancellationCharges'];
                    $ServiceTemplateData['CancellationFee']     = $CancellationFee;
                    $ServiceTemplateData['ContractDuration']    = $ContractDuration;

                    if($ServiceTemplate = ServiceTemplate::create($ServiceTemplateData)){

                        $ServiceTemapleSubscription['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                        //Log::info('ServiceTemplateID.' . $ServiceTemplate->ServiceTemplateId);
                       // $subsriptionList = $data['SubscriptionID'];
                        foreach ($subsriptionList as $subsription) {
                            $ServiceTemapleSubscription['SubscriptionId'] = $subsription;
                            //Log::info('Service Template Controller.' . $subsription);
                            if (!empty($subsription)) {
                                ServiceTemapleSubscription::create($ServiceTemapleSubscription);
                            }
                        }

                        foreach ($CategoryTariffList as $index1 => $CategoryTariffValue) {
                            try {
                                $ServiceTemapleInboundTariff['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                                $ServiceTemapleInboundTariff['CompanyID'] = User::get_companyID();
                                //Log::info('$CategoryTariffValue1.' . $CategoryTariffValue);
                                $DIDRateTableList = explode("-", $CategoryTariffValue);
                                //Log::info('$CategoryTariffValue1.' . $DIDRateTableList);
                                //Log::info('$CategoryTariffValue1.' . count($DIDRateTableList));
                                if ($DIDRateTableList[0] != 0) {
                                    $ServiceTemapleInboundTariff['DIDCategoryId'] = $DIDRateTableList[0];
                                }
                                $ServiceTemapleInboundTariff['RateTableId'] = $DIDRateTableList[1];
                                ServiceTemapleInboundTariff::create($ServiceTemapleInboundTariff);
                                $DIDRateTableList[0] = '';
                                $DIDRateTableList[1] = '';
                                $DIDRateTableList = '';
                                $ServiceTemapleInboundTariff = '';
                            }catch (Exception $ex){
                                
                            }
                        }

                        if(isset($DynamicFields)) {
                            //Log::info('Create the dynamic field.' . count($DynamicFields));
                        }
                        if(isset($DynamicFields) && count($DynamicFields)>0) {
                            for($k=0; $k<count($DynamicFields); $k++) {
                                if(trim($DynamicFields[$k]['FieldValue'])!='') {
                                    $DynamicFields[$k]['ParentID'] = $ServiceTemplate->ServiceTemplateId;
                                    DB::table('tblDynamicFieldsValue')->insert($DynamicFields[$k]);
                                }
                            }
                        }

                        return  Response::json(array("status" => "success", "message" => "Product Successfully Created",'LastID'=>$ServiceTemplate->ServiceTemplateId,'newcreated'=>$ServiceTemplate));
                    } else {
                        return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
                    }

                }


     //  return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
    }

    public function update($ServiceTemplateId) {
        //Log::info('update ServiceTemplateID.' . $ServiceTemplateId);
        $data = Input::all();
        $subsriptionList = isset($data['selectedSubscription'])?$data['selectedSubscription']:'';
        $CategoryTariffList = isset($data['selectedcategotyTariff'])?$data['selectedcategotyTariff']:'';
        $subsriptionList = trim($subsriptionList);
        $CategoryTariffList = trim($CategoryTariffList);
        if (ends_with($subsriptionList,',') ) {
            $subsriptionList = substr($subsriptionList,0,strlen($subsriptionList) - 1);
        }
        if (ends_with($CategoryTariffList,',') ) {
            $CategoryTariffList = substr($CategoryTariffList,0,strlen($CategoryTariffList) - 1);
        }

        //Log::info('update Subscription List.' . $subsriptionList);
        //Log::info('update Category Tariff List.' . $CategoryTariffList);
        $subsriptionList = explode(",",$subsriptionList);
        $CategoryTariffList = explode(",",$CategoryTariffList);
        $OutboundDiscountPlanId = isset($data['OutboundDiscountPlanID123'])?$data['OutboundDiscountPlanID123']:'';
        $InboundDiscountPlanId = isset($data['InboundDiscountPlanID123'])?$data['InboundDiscountPlanID123']:'';
        $PackageDiscountPlanId = isset($data['PackageDiscountPlanId'])?$data['PackageDiscountPlanId']:'';
        $ContractDuration = isset($data['ContractDuration'])&&$data['ContractDuration']!=""?$data['ContractDuration'] : null;
        $CancellationFee = isset($data['CancellationFee'])&&$data['CancellationFee']!=""?$data['CancellationFee'] : null;
        $data['ModifiedBy'] = User::get_user_full_name();
        $user =  $data['ModifiedBy'];

        if(isset($data['DynamicFields']) && count($data['DynamicFields']) > 0) {
            $CompanyID = User::get_companyID();
            foreach ($data['DynamicFields'] as $key => $value) {
                $key = (int) $key;

                if(isset($_FILES["DynamicFields"]["name"][$key])){
                    $dynamicImage = $_FILES["DynamicFields"]["name"][$key];
                    if($dynamicImage){
                        $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID)."/";
                        $fileUrl=$CompanyID."/dynamicfields/";
                        if (!file_exists($upload_path.$fileUrl)) {
                            mkdir($upload_path.$fileUrl, 0777, true);
                        }
                        $dynamicImage=time().$dynamicImage;
                        $success=move_uploaded_file($_FILES["DynamicFields"]["tmp_name"][$key],$upload_path.$fileUrl.$dynamicImage);
                        if($success){
                            $DynamicFields['FieldValue']=$fileUrl.$dynamicImage;
                            $value=$fileUrl.$dynamicImage;
                        }else{
                            $DynamicFields['FieldValue']="";
                            return Response::json(array("status" => "failed", "message" => "Error: There was a problem uploading your file. Please try again."));
                        }
                    }
                }else{
                    if(!empty($data['DynamicFields'][$key])){
                        $DynamicFields['FieldValue'] = $value;
                    } else {
                        $DynamicFields['FieldValue'] = "";
                    }
                }

                $isDynamicFields = DB::table('tblDynamicFieldsValue')
                    ->where('CompanyID',$CompanyID)
                    ->where('ParentID',$ServiceTemplateId)
                    ->where('DynamicFieldsID',$key);

                if($isDynamicFields->count() > 0){
                    $isDynamicFields = $isDynamicFields->first();

                    $DynamicFields['DynamicFieldsID'] = $key;
                    //$DynamicFields['FieldValue'] = $value;
                    $DynamicFields['DynamicFieldsValueID'] = $isDynamicFields->DynamicFieldsValueID;

                    if($error = DynamicFieldsValue::validateOnUpdate($DynamicFields)){
                        return $error;
                    }

                    $getdynamicField=DynamicFields::where('DynamicFieldsID',$key)->get();
                    if($getdynamicField[0]->FieldDomType=='file' && $value==''){

                    }else {
                        DynamicFieldsValue::where('CompanyID', $CompanyID)
                            ->where('ParentID', $ServiceTemplateId)
                            ->where('DynamicFieldsID', $key)
                            ->update(['FieldValue' => $value, 'updated_at' => date('Y-m-d H:i:s.000'), 'updated_by' => $user]);
                    }
                } else {
                    $companyID = User::get_companyID();
                    if(trim($value)!='') {
                        $DynamicFields['CompanyID'] = $companyID;
                        $DynamicFields['ParentID'] = $ServiceTemplateId;
                        $DynamicFields['DynamicFieldsID'] = $key;
                        $DynamicFields['FieldValue'] = $value;
                        $DynamicFields['DynamicFieldsValueID'] = 'NULL';
                        $DynamicFields['created_at'] = date('Y-m-d H:i:s.000');
                        $DynamicFields['created_by'] = $user;
                        $DynamicFields['updated_at'] = date('Y-m-d H:i:s.000');
                        $DynamicFields['updated_by'] = $user;

                        if ($error = DynamicFieldsValue::validateOnUpdate($DynamicFields)) {
                            return $error;
                        }

                        DynamicFieldsValue::insert($DynamicFields);
                    }
                }
            }
            unset($data['DynamicFields']);
        }



        if(!empty($data)){
            $user_id = User::get_userID();
            $data['CompanyID'] = User::get_companyID();
            $data['Status'] = isset($data['Status']) ? 1 : 0;
            $data['AutomaticRenewal'] = isset($data['AutomaticRenewal']) ? 1 : 0;



            ServiceTemplate::$updateRules['Name'] = "required|unique:tblServiceTemplate,Name,".$ServiceTemplateId.",ServiceTemplateId,CompanyID,".User::get_companyID()."";

            ServiceTemplate::$updateRules['ContractDuration'] = 'numeric';
            ServiceTemplate::$updateRules['CancellationCharges'] = 'required|numeric';

            $niceNames = ['CancellationFee' => 'Cancellation Fee'];
            if(isset($data['CancellationCharges']) && $data['CancellationCharges'] != 2 && $data['CancellationCharges'] != 5) {
                ServiceTemplate::$updateRules['CancellationFee'] = 'required|numeric';
                if($data['CancellationCharges'] == 3){
                    $niceNames = ['CancellationFee' => "Cancellation Fee Percentage"];
                }
            }
            $validator = Validator::make($data, ServiceTemplate::$updateRules);
            $validator->setAttributeNames($niceNames);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if (isset($data['ServiceId']) && $data['ServiceId'] != '') {
                $ServiceTemplateData['ServiceId'] = $data['ServiceId'];
            }
            $ServiceTemplateData['Name'] = $data['Name'];
            $ServiceTemplateData['CompanyID'] = User::get_companyID();
            if ($OutboundDiscountPlanId != '') {
                $ServiceTemplateData['OutboundDiscountPlanId'] = $OutboundDiscountPlanId;
            }
            if ($InboundDiscountPlanId != '') {
                $ServiceTemplateData['InboundDiscountPlanId'] = $InboundDiscountPlanId;
            }
            if ($PackageDiscountPlanId != '') {
                $ServiceTemplateData['PackageDiscountPlanId'] = $PackageDiscountPlanId;
            }

            if (isset($data['OutboundRateTableId']) && $data['OutboundRateTableId'] != '') {
                $ServiceTemplateData['OutboundRateTableId'] = $data['OutboundRateTableId'];
            }

            $ServiceTemplateData['AutomaticRenewal']    = $data['AutomaticRenewal'];
            $ServiceTemplateData['CancellationCharges'] = $data['CancellationCharges'];
            $ServiceTemplateData['CancellationFee']     = $CancellationFee;
            $ServiceTemplateData['ContractDuration']    = $ContractDuration;


            $updDelStatus = false;
            $result = ServiceTemapleSubscription::where(array('ServiceTemplateID'=>$ServiceTemplateId))->delete();
            $result = ServiceTemapleInboundTariff::where(array('ServiceTemplateID' => $ServiceTemplateId))->delete();
            $updDelStatus = true;


            if( $updDelStatus && $ServiceTemplate = ServiceTemplate::find($ServiceTemplateId)){

                $ServiceTemapleSubscription['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                //Log::info('ServiceTemplateID.' . $ServiceTemplate->ServiceTemplateId);
                // $subsriptionList = $data['SubscriptionID'];
                foreach ($subsriptionList as $subsription) {
                    $ServiceTemapleSubscription['SubscriptionId'] = $subsription;
                    //Log::info('Service Template Controller.' . $subsription);
                    if (!empty($subsription)) {
                        ServiceTemapleSubscription::create($ServiceTemapleSubscription);
                    }
                }

                foreach ($CategoryTariffList as $index1 => $CategoryTariffValue) {
                    try {
                        $ServiceTemapleInboundTariff['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                       // Log::info('$CategoryTariffValue1.' . $CategoryTariffValue);
                        $DIDRateTableList = explode("-", $CategoryTariffValue);
                        //Log::info('$CategoryTariffValue1.' . $DIDRateTableList);
                        //Log::info('$CategoryTariffValue1.' . count($DIDRateTableList));
                        if ($DIDRateTableList[0] != 0) {
                            $ServiceTemapleInboundTariff['DIDCategoryId'] = $DIDRateTableList[0];
                        }
                        $ServiceTemapleInboundTariff['RateTableId'] = $DIDRateTableList[1];
                        $ServiceTemapleInboundTariff['CompanyID'] = User::get_companyID();
                       // Log::info('$DIDRateTableList[0].' . $DIDRateTableList[0]);
                        //Log::info('$DIDRateTableList[1].' . $DIDRateTableList[1]);

                        ServiceTemapleInboundTariff::create($ServiceTemapleInboundTariff);
                        $DIDRateTableList[0] = '';
                        $DIDRateTableList[1] = '';
                        $DIDRateTableList = '';
                        $ServiceTemapleInboundTariff = '';

                    }catch(Exception $ex){
                        Log::info('Error while inserting..' . $ex->getMessage());
                    }
                }
                if($ServiceTemplate->update($ServiceTemplateData)) {
                    return Response::json(array("status" => "success", "message" => "Service Template Successfully Updated", 'LastID' => $ServiceTemplate->ServiceTemplateId, 'newcreated' => $ServiceTemplate));
                }else {
                    return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
                }
            } else {
                return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
            }

        }  else {
            return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
        }



    }

    public function delete($id){
           try{
               //Log::info('service template delete.' . $id);
               $result = ServiceTemapleSubscription::where(array('ServiceTemplateID'=>$id))->delete();
              // Log::info('ServiceTemapleSubscription delete.' . $result);
               $result = ServiceTemapleInboundTariff::where(array('ServiceTemplateID'=>$id))->delete();
              // Log::info('ServiceTemapleInboundTariff delete.' . $result);
               $result = ServiceTemplate::where(array('ServiceTemplateId'=>$id))->delete();
              // Log::info('ServiceTemaple delete.' . $result);
               if ($result) {
                            $Type =  ServiceTemplateTypes::DYNAMIC_TYPE;
                            $companyID = User::get_companyID();
                            $action = "delete";
                            $DynamicFields = $this->getDynamicFields($companyID,$Type,$action);
                            if($DynamicFields['totalfields'] > 0){
                                $DynamicFieldsIDs = array();
                                foreach ($DynamicFields['fields'] as $field) {
                                    $DynamicFieldsIDs[] = $field->DynamicFieldsID;
                                }
                                //Image Delete
                               // Log::info("delete DynamicFieldValue ProductID3=".$id);
                                $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID)."/";
                                $getDynamicValues=DynamicFieldsValue::where('ParentID',$id)->get();
                                if($getDynamicValues){
                                    foreach($getDynamicValues as $key =>$val){
                                        if (file_exists($upload_path.$val->FieldValue) && $val->FieldValue!='') {
                                            unlink($upload_path.$val->FieldValue);
                                        }
                                    }
                                }
                                // DynamicFieldsValue::deleteDynamicValuesByProductID($companyID,$id,$DynamicFieldsIDs);
                                //Log::info("delete DynamicFieldValue ProductID=".$id);
                                DynamicFieldsValue::deleteDynamicValuesByProductID($companyID,$id);
                            }
                            return Response::json(array("status" => "success", "message" => "Service Successfully Deleted"));
                        }else {
                            return Response::json(array("status" => "failed", "message" => "Problem Deleting Service."));
                        }



            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }
    }
    public static function updateDelete($id){
        try{
            //Log::info('updateDelete service template delete.' . $id);
            $result = ServiceTemapleSubscription::where(array('ServiceTemplateID'=>$id))->delete();
            //Log::info('updateDelete ServiceTemapleSubscription delete.' . $result);
            if ($result) {
                $result = ServiceTemapleInboundTariff::where(array('ServiceTemplateID'=>$id))->delete();
                //Log::info('updateDelete ServiceTemapleInboundTariff delete.' . $result);
                if ($result) {
                    return true;
                }else {
                    return false;
                }
                }else {
                 return false;
                }
        }catch (Exception $ex){
            return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
        }
    }


    public function exports($type){
        try{
        $data = Input::all();
        Log::info('servicesTemplate ajax_datagrid AJAX data.' . print_r($data,true));
        $companyID = User::get_companyID();


        $iSortCol_0 = isset($data['iSortCol_0']) ? $data['iSortCol_0']:1;
        $data['ServiceName'] = isset($data['ServiceName']) ? $data['ServiceName']:'';
        $data['ServiceId'] = isset($data['ServiceId']) && $data['ServiceId'] != '' ? $data['ServiceId']:0;
        $data['CountryID'] = isset($data['CountryID']) ? $data['CountryID']:'';
        $data['AccessType'] = isset($data['AccessType']) ? $data['AccessType']:'';
        $data['Prefix'] = isset($data['Prefix']) ? $data['Prefix']:'';
        $data['City'] = isset($data['City']) ? $data['City']:'';
        $data['Tariff'] = isset($data['Tariff']) ? $data['Tariff']:'';


        $sSortDir_0 = '';
        if (isset($data['sSortDir_0'])) {
            $sSortDir_0 = $data['sSortDir_0'];
        }else {
            $sSortDir_0 = "ASC";
        }

        if ($iSortCol_0 == 1 || $iSortCol_0 == 0) {
            $iSortCol_0 = "Name";
        }else if ($iSortCol_0 == 2) {
            $iSortCol_0 = "Name";
        }else if ($iSortCol_0 == 3) {
            $iSortCol_0 = "ServiceName";
        }else if ($iSortCol_0 == 4) {
            $iSortCol_0 = "country";
        }else if ($iSortCol_0 == 5) {
            $iSortCol_0 = "prefixName";
        }else if ($iSortCol_0 == 6) {
            $iSortCol_0 = "accessType";
        }else if ($iSortCol_0 == 7) {
            $iSortCol_0 = "City";
        }else if ($iSortCol_0 == 8) {
            $iSortCol_0 = "Tariff";
        }

        $data['iDisplayStart'] += 1;
        $query = "call prc_getServiceTemplate(" . $companyID . ","
            . "'" .$data['ServiceName']."',"
            . "'" .$data['ServiceId']."',"
            . "'" .$data['CountryID']."',"
            . "'" .$data['AccessType']."',"
            . "'" .$data['Prefix']."',"
            . "'" .$data['City']."',"
            . "'" .$data['Tariff']."',"
            . "'" .(ceil($data['iDisplayStart'] / $data['iDisplayLength']))."',"
            . "'" .$data['iDisplayLength']."',"
            . "'" .$iSortCol_0."',"
            . "'" .$sSortDir_0."',"
            . "'" .'1'."'"
            . ")";

        Log::info('servicesTemplate ajax_datagrid AJAX data.' . $query);

        $services =  DB::select($query);

        $services = json_decode(json_encode($services),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/ServicesTemplate.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($services);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/ServicesTemplate.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($services);
            }
        }catch(Exception $ex){
            log::info($ex);
        }


    }

    public function storeServiceTempalteData($data='') {
        //Log::info('storeServiceTempalteData:Service Template Controller.');
        try {
            $post_vars = json_decode(file_get_contents("php://input"),true);
            //Log::info('storeServiceTempalteData:storeServiceTempalteData.' . $post_vars);
            $data['Name'] = $post_vars->Name;
            //Log::info('storeServiceTempalteData:$data[\'Name\'].' . $data['Name']);
            $data['ServiceId'] = $post_vars->ServiceId;
            $data['CurrencyId'] = $post_vars->CurrencyId;
            $data['OutboundDiscountPlanId'] = $post_vars->OutboundDiscountPlanId;
            $data['InboundDiscountPlanId'] = $post_vars->InboundDiscountPlanId;
            $data['OutboundRateTableId'] = $post_vars->OutboundRateTableId;
            $data['selectedSubscription'] = $post_vars->selectedSubscription;
            $data['selectedcategotyTariff'] = $post_vars->selectedcategotyTariff;
//            Log::info('storeServiceTempalteData:storeServiceTempalteData.' .
//            'Name:'. $data['Name'].'ServiceId' . $data['ServiceId'] . 'CurrencyId' . $data['CurrencyId'].
//            'OutboundDiscountPlanId' . $data['OutboundDiscountPlanId'] . 'InboundDiscountPlanId' . $data['InboundDiscountPlanId'].
//            'OutboundRateTableId' . $data['OutboundRateTableId'] . 'selectedSubscription' . $data['selectedSubscription'].
//            'selectedcategotyTariff' . $data['selectedcategotyTariff']);


        // Log::info('Subscription List.' . $_REQUEST['selectedSubscription']);
        // Log::info('Subscription List.' . $data['selectedSubscription']);
        $subsriptionList = isset($data['selectedSubscription'])?$data['selectedSubscription']:'';
        $CategoryTariffList = isset($data['selectedcategotyTariff'])?$data['selectedcategotyTariff']:'';
        $subsriptionList = trim($subsriptionList);
        $CategoryTariffList = trim($CategoryTariffList);
        if (ends_with($subsriptionList,',') ) {
            $subsriptionList = substr($subsriptionList,0,strlen($subsriptionList) - 1);
        }
        if (ends_with($CategoryTariffList,',') ) {
            $CategoryTariffList = substr($CategoryTariffList,0,strlen($CategoryTariffList) - 1);
        }

        //Log::info('storeServiceTempalteData:read Subscription List.' . $subsriptionList);
        //Log::info('storeServiceTempalteData:read Category Tariff List.' . $CategoryTariffList);
        $subsriptionList = explode(",",$subsriptionList);
        $CategoryTariffList = explode(",",$CategoryTariffList);
        $OutboundDiscountPlanId = isset($data['OutboundDiscountPlanId'])?$data['OutboundDiscountPlanId']:'';
        $InboundDiscountPlanId = isset($data['InboundDiscountPlanId'])?$data['InboundDiscountPlanId']:'';
        $CurrencyId = isset($data['CurrencyId'])?$data['CurrencyId']:'';


        /*
        foreach ($subsriptionList as $index2 => $subsription) {
            Log::info('Subscription List.' . $subsription);
        }



        foreach ($CategoryTariffList as $index1 => $CategoryTariffValue) {
            Log::info('Category Tarif Value.' . $CategoryTariffValue);
            $DIDRateTableList = explode(":",$CategoryTariffValue);
            Log::info('Category Tarif Value.' . $DIDRateTableList[0]);
            Log::info('Category Tarif Value.' . $DIDRateTableList[1]);
        }*/

        if(!empty($data)){
            $user_id = User::get_userID();
            $data['CompanyID'] = User::get_companyID();
            $data['Status'] = isset($data['Status']) ? 1 : 0;

            // ServiceTemplate::$rules['ServiceId'] = 'required';
            // ServiceTemplate::$rules['Name'] = 'required';
            //  ServiceTemplate::$rules['SubscriptionID'] = 'required';
            // ServiceTemplate::$rules['CurrencyId'] = 'required';

            ServiceTemplate::$rules['Name'] = 'required|unique:tblServiceTemplate';
            $validator = Validator::make($data, ServiceTemplate::$rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if (isset($data['ServiceId']) && $data['ServiceId'] != '') {
                $ServiceTemplateData['ServiceId'] = $data['ServiceId'];
            }
            $ServiceTemplateData['Name'] = $data['Name'];
            if ($OutboundDiscountPlanId != '') {
                $ServiceTemplateData['OutboundDiscountPlanId'] = $OutboundDiscountPlanId;
            }
            if ($InboundDiscountPlanId != '') {
                $ServiceTemplateData['InboundDiscountPlanId'] = $InboundDiscountPlanId;
            }
            if (isset($data['OutboundRateTableId']) && $data['OutboundRateTableId'] != '') {
                $ServiceTemplateData['OutboundRateTableId'] = $data['OutboundRateTableId'];
            }

            $ServiceTemplateData['CurrencyId'] = $data['CurrencyId'];


            if($ServiceTemplate = ServiceTemplate::create($ServiceTemplateData)){

                $ServiceTemapleSubscription['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                //Log::info('storeServiceTempalteData:ServiceTemplateID.' . $ServiceTemplate->ServiceTemplateId);
                // $subsriptionList = $data['SubscriptionID'];
                foreach ($subsriptionList as $subsription) {
                    $ServiceTemapleSubscription['SubscriptionId'] = $subsription;
                    //Log::info('storeServiceTempalteData:Service Template Controller.' . $subsription);
                    ServiceTemapleSubscription::create($ServiceTemapleSubscription);
                }

                foreach ($CategoryTariffList as $index1 => $CategoryTariffValue) {
                    try {
                        $ServiceTemapleInboundTariff['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                        //Log::info('storeServiceTempalteData:$CategoryTariffValue1.' . $CategoryTariffValue);
                        $DIDRateTableList = explode("-", $CategoryTariffValue);
                        //Log::info('$CategoryTariffValue1.' . $DIDRateTableList);
                        //Log::info('storeServiceTempalteData:$CategoryTariffValue1.' . count($DIDRateTableList));
                        if ($DIDRateTableList[0] != 0) {
                            $ServiceTemapleInboundTariff['DIDCategoryId'] = $DIDRateTableList[0];
                        }
                        $ServiceTemapleInboundTariff['RateTableId'] = $DIDRateTableList[1];
                        ServiceTemapleInboundTariff::create($ServiceTemapleInboundTariff);
                        $DIDRateTableList[0] = '';
                        $DIDRateTableList[1] = '';
                        $DIDRateTableList = '';
                        $ServiceTemapleInboundTariff = '';
                    }catch (Exception $ex){
                        return  Response::json(array("status" => "failed", "message" => $ex->getMessage(),'LastID'=>$ServiceTemplate->ServiceTemplateId,'newcreated'=>$ServiceTemplate));
                    }
                }

                return  Response::json(array("status" => "success", "message" => "Service Template Successfully Created",'LastID'=>$ServiceTemplate->ServiceTemplateId,'newcreated'=>$ServiceTemplate));
            } else {
                return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
            }

        }
        }catch (Exception $ex){
            return  Response::json(array("status" => "failed", "message" => $ex->getMessage(),'LastID'=>'','newcreated'=>''));
        }


        //  return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
    }

    function viewSubscriptionDynamicFields(){

        return  View::make('servicetemplate.servicetemplatetype.index');
    }

    public function ajax_GetServiceTemplateType($type)
    {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = ['DynamicFieldsID','FieldName','FieldDomType','created_at','Status','Active'];
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getDynamicFields (".$CompanyID.", '".$data['FieldName']."','".$data['FieldDomType']."','".$data['Active']."',"."'".ServiceTemplateTypes::DYNAMIC_TYPE."'".",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";


        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/subscrition.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){

                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/subscrition.xls';
                $NeonExcel = new NeonExcelIO($file_path);

                $NeonExcel->download_excel($excel_data);

            }
            /*Excel::create('Item', function ($excel) use ($excel_data) {
                $excel->sheet('Item', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';
        //Log::info('ajax_GetServiceTemplateType $query.' . $query);
        $data = DataTableSql::of($query,'sqlsrv')->make(false);
        return Response::json($data);
    }

    public function addDynamicFields(){

        $data = Input::all();
        $companyID = User::get_companyID();
        $data ["CompanyID"] = $companyID;
        $data['Active'] = isset($data['Active']) ? 1 : 0;
        $data["created_by"] = User::get_user_full_name();
        $slug= str_replace(' ', '', $data['FieldName']);
        $data ["FieldSlug"] = "Product".$slug;
        $data ["Status"]=$data['Active'];
        $data["created_at"]=date('Y-m-d H:i:s');
        $data ["Type"] = ServiceTemplateTypes::DYNAMIC_TYPE;
        unset($data['DynamicFieldsID']);
        unset($data['ProductClone']);
        unset($data['Active']);


        $rules = array(
            'CompanyID' => 'required',
            'FieldDomType' => 'required',
            'FieldName' => 'required',
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        //Check FieldName duplicate
        $cnt_duplidate = DynamicFields::where('FieldName',$data['FieldName'])->where('Type',ServiceTemplateTypes::DYNAMIC_TYPE)->get()->count();
        if($cnt_duplidate > 0){
            return Response::json(array("status" => "failed", "message" => "Dynamic Field With This Name Already Exists."));
        }

        if ($dynamicfield = DynamicFields::create($data)) {
            return Response::json(array("status" => "success", "message" => "Dynamic Field Successfully Created",'newcreated'=>$dynamicfield));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Dynamic Field."));
        }

    }

    public function deleteDynamicFields($id){

        if( intval($id) > 0) {

            if (!BillingSubscription::checkForeignKeyById($id)) {

                try {
                    //delete its DynamicFields
                    $DynamicField =DynamicFields::where('DynamicFieldsID',$id)->delete();

                    if ($DynamicField) {
                        return Response::json(array("status" => "success", "message" => "Dynamic Field Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Dynamic Field."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Dynamic Field is in Use, You cant delete this Dynamic Field."));
                }

            }else{
                return Response::json(array("status" => "failed", "message" => "Dynamic Field is in Use, You cant delete this Dynamic Field."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Dynamic Field is in Use, You cant delete this Dynamic Field."));
        }
    }

    public function updateDynamicField($id){

        if( $id > 0 ) {
            $data = Input::all();
            $slug= str_replace(' ', '', $data['FieldName']);
            $data ["FieldSlug"] = "Product".$slug;
            $dynamicfield = DynamicFields::findOrFail($id);
            $user = User::get_user_full_name();

            $companyID = User::get_companyID();
            $data["CompanyID"] = $companyID;
            $data['Status'] = isset($data['Active']) ? 1 : 0;
            $data["updated_at"]=date('Y-m-d H:i:s');
            $data["updated_by"] = $user;
            unset($data['ProductClone']);
            unset($data['Active']);

            $rules = array(
                'CompanyID' => 'required',
                'FieldDomType' => 'required',
                'FieldName' => 'required',
            );

            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            //Check FieldName duplicate
            $cnt_duplidate = DynamicFields::where('FieldName',$data['FieldName'])->where('Type',ServiceTemplateTypes::DYNAMIC_TYPE)->where('DynamicFieldsID','!=',$dynamicfield->DynamicFieldsID)->get()->count();
            if($cnt_duplidate > 0){
                return Response::json(array("status" => "failed", "message" => "Dynamic Field With This Name Already Exists."));
            }

            if ($dynamicfield->update($data)) {
                return Response::json(array("status" => "success", "message" => "Dynamic Field Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Dynamic Field."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Dynamic Field."));
        }

    }

    public function UpdateBulkItemTypeStatus()
    {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $UserName = User::get_user_full_name();
        if (isset($data['type_active_deactive']) && $data['type_active_deactive'] != '') {
            if ($data['type_active_deactive'] == 'active') {
                $data['status_set'] = 1;
            } else if ($data['type_active_deactive'] == 'deactive') {
                $data['status_set'] = 0;
            } else {
                return Response::json(array("status" => "failed", "message" => "No Dynamic Field status selected"));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "No Dynamic Field status selected"));
        }

        if ($data['criteria_ac'] == 'criteria') { //all item checkbox checked
            //Log::info('UpdateBulkItemTypeStatus 1.' );
            $userID = User::get_userID();

            if (!isset($data['Active']) || $data['Active'] == '') {
                $data['Active'] = 9;
            } else {
                $data['Active'] = (int)$data['Active'];
            }

            $query = "call prc_UpdateDynamicFieldStatus (" . $CompanyID . ",'" . $UserName . "','product','" . $data['FieldName'] . "','" . $data['FieldDomType'] . "','" . $data['ItemTypeID'] . "'," . $data['Active'] . "," . $data['status_set'] . ")";
            //Log::info('UpdateBulkItemTypeStatus 1.' . $query );
            $result = DB::connection('sqlsrv')->select($query);
            return Response::json(array("status" => "success", "message" => "Dynamic Field Status Updated"));
        }

        if ($data['criteria_ac'] == 'selected') { //selceted ids from current page
            if (isset($data['SelectedIDs']) && count($data['SelectedIDs']) > 0) {
                foreach($data['SelectedIDs'] as $SelectedID){
                //Log::info('UpdateBulkItemTypeStatus 2.' . $SelectedID);
            }
                DynamicFields::whereIn('DynamicFieldsID', $data['SelectedIDs'])->where('Status', '!=', $data['status_set'])->update(["Status" => intval($data['status_set'])]);
//                    Product::find($SelectedID)->where('Active','!=',$data['status_set'])->update(["Active"=>intval($data['status_set']),'ModifiedBy'=>$UserName,'updated_at'=>date('Y-m-d H:i:s')]);
//                }
                return Response::json(array("status" => "success", "message" => "Dynamic Field Status Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "No Dynamic Field selected"));
            }

        }

    }

    public function getSubscritionsField(){
        $Type =  ServiceTemplateTypes::DYNAMIC_TYPE;
        $CompanyID = User::get_companyID();
        $DynamicFieldsSql = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->where('Status','1')->orderByRaw('case FieldOrder when 0 then 2 else 1 end, FieldOrder');
        //Log::info('getSubscritionsDynamicField $DynamicFieldsSql.' . $DynamicFieldsSql->toSql());
        $DynamicFields['fields'] = $DynamicFieldsSql->get();
        //Log::info('getSubscritionsDynamicField.' . count($DynamicFields) );
        $DynamicFields['totalfields'] = count($DynamicFields['fields']);
        //Log::info('getSubscritionsDynamicField.' . count($DynamicFields) );
        if(count($DynamicFields) > 0 ){
            return View::make('servicetemplate.ajax_dynamicFields',compact('DynamicFields','data'));
        }
    }

    public function getSubscritionsType($data){

        $Type =  ServiceTemplateTypes::DYNAMIC_TYPE;
        $CompanyID = User::get_companyID();
        $DynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->where('Status','1')->orderByRaw('case FieldOrder when 0 then 2 else 1 end, FieldOrder')->get();

        $DynamicFields['totalfields'] = count($DynamicFields['fields']);

        if(count($DynamicFields) > 0 ){
            return View::make('servicetemplate.ajax_dynamicFields',compact('DynamicFields','data'));
        }

    }

    public function getDynamicFields($CompanyID, $Type=Subscription::DYNAMIC_TYPE, $action=''){

        if($action && $action == 'delete') {
            $dynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->get();
        } else {
            $dynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->where('Status',1)->get();
        }

        $dynamicFields['totalfields'] = count($dynamicFields['fields']);

        return $dynamicFields;
    }

    public function addBulkAction(){ // Add Bulk action if input empty then this will add already existing values...
        $data = Input::all();
        //dd($data);
        try{

        if(isset($data['Service']))
        {
            if(!($data['ServiceIdBulkAction']) || $data['ServiceIdBulkAction'] == "")
                return Response::json(array("status" => "failed", "message" => "Service select box required"));
        }



        //$data['CurrencyId']             = (isset($data['CurrencyIdBulkAction']) ? $data['CurrencyIdBulkAction'] : " ");
        $data['ServiceId']              = (isset($data['ServiceIdBulkAction']) ? $data['ServiceIdBulkAction'] : " ");
        $data['OutboundRateTableId']    = (isset($data['OutboundRateTableIdBulkAction']) ? $data['OutboundRateTableIdBulkAction'] : " ");
        $data['OutboundDiscountPlanId'] = (isset($data['OutboundDiscountPlanIdBulkAction']) ? $data['OutboundDiscountPlanIdBulkAction'] : " ");
        $data['InboundDiscountPlanId']  = (isset($data['InboundDiscountPlanIdBulkAction']) ? $data['InboundDiscountPlanIdBulkAction'] : " ");
        $data['selectedcategotyTariff'] = (isset($data['selectedcategotyTariffBulkAction'])? $data['selectedcategotyTariffBulkAction'] : " ");
        $data['DidCategoryTariffID']    = (isset($data['DidCategoryTariffIDBulkAction']) ? $data['DidCategoryTariffIDBulkAction'] : " ");
        $data['InboundDiscountPlanId']  = (isset($data['InboundDiscountPlanIdBulkAction']) ? $data['InboundDiscountPlanIdBulkAction'] : " ");
        $data['PackageDiscountPlanId']  = (isset($data['PackageDiscountPlanIdBulkAction']) ? $data['PackageDiscountPlanIdBulkAction'] : " ");





        unset($data['CurrencyIdBulkAction']);
        unset($data['ServiceIdBulkAction']);
        unset($data['OutboundRateTableIdBulkAction']);
        unset($data['OutboundDiscountPlanIdBulkAction']);
        unset($data['InboundDiscountPlanIdBulkAction']);
        unset($data['DidCategoryTariffIDBulkAction']);
        unset($data['DidCategoryIDBulkAction']);
        unset($data['InboundDiscountPlanIdBulkAction']);
        unset($data['PackageDiscountPlanIdBulkAction']);


        if(isset($data['ServiceTemplateIdBulkAction'])) {

            $ServiceTemplateIdString = ((string)$data['ServiceTemplateIdBulkAction']);
            $ServiceTemplateIdArray = explode(',', $ServiceTemplateIdString);

            for ($i = 0; $i < sizeof($ServiceTemplateIdArray); $i++) {
                $ExistingValues = ServiceTemplate::select('CurrencyId', 'ServiceId', 'OutboundRateTableId', 'OutboundDiscountPlanId', 'InboundDiscountPlanId')
                    ->where('ServiceTemplateId', $ServiceTemplateIdArray[$i])->first();

                $updateFields = [];

                if (isset($data['Service']) && $data['Service'] == 1) {
                    $updateFields['ServiceId'] = (isset($data['ServiceId']) ? $data['ServiceId'] : $ExistingValues['ServiceId']);
                }
                if (isset($data['OutboundTraiff']) && $data['OutboundTraiff'] == 1) {
                    $updateFields['OutboundRateTableId'] = (isset($data['OutboundRateTableId']) ? $data['OutboundRateTableId'] : $ExistingValues['OutboundRateTableId']);
                }
                if (isset($data['OutboundDiscountPlan']) && $data['OutboundDiscountPlan'] == 1 ) {
                    $updateFields['OutboundDiscountPlanId'] = (isset($data['OutboundDiscountPlanId']) ? $data['OutboundDiscountPlanId'] : $ExistingValues['OutboundDiscountPlanId']);
                }
                if (isset($data['InboundDiscountPlan']) && $data['InboundDiscountPlan'] == 1 ) {
                    $updateFields['InboundDiscountPlanId'] = (isset($data['InboundDiscountPlanId']) ? $data['InboundDiscountPlanId'] : $ExistingValues['InboundDiscountPlanId']);
                }
                if (isset($data['PackageDiscountPlan']) && $data['PackageDiscountPlan'] == 1 ) {
                    $updateFields['PackageDiscountPlanId'] = (isset($data['PackageDiscountPlanId']) ? $data['PackageDiscountPlanId'] : $ExistingValues['PackageDiscountPlanId']);
                }



                ServiceTemplate::where('ServiceTemplateId', $ServiceTemplateIdArray[$i])->update($updateFields);
            }

            unset($data['OutboundDiscountPlan']);
            unset($data['OutboundTraiff']);
            unset($data['OutboundDiscountPlan']);
            unset($data['InboundDiscountPlan']);
            unset($data['PackageDiscountPlan']);


            $data['ServiceTemplateId'] = $data['ServiceTemplateIdBulkAction'];
            $data['RateTableId'] = (isset($data['OutboundRateTableId']) ? $data['OutboundRateTableId'] : null);


            $CategoryIdRateTableIdArray = ((string)$data['selectedcategotyTariffBulkAction']);
            $CategoryIdRateTableIdString = explode(',', $CategoryIdRateTableIdArray);

            $collectionArray = array();
            $getCollectionArray = [];
            $CategoryId = [];
            $RateTableId = [];

            for ($i = 0; $i < sizeof($CategoryIdRateTableIdString) - 1; $i++) {
                $ArrayCollection = $CategoryIdRateTableIdString[$i];
                $getCollectionArray[] = explode("-", $ArrayCollection);
            }
           ;
           // unset($data['CurrencyId']);
            unset($data['ServiceId']);
            unset($data['OutboundRateTableId']);
            unset($data['OutboundDiscountPlanId']);
            unset($data['InboundDiscountPlanId']);
            unset($data['PackageDiscountPlanId']);
            unset($data['ServiceTemplateIdBulkAction']);
            unset($data['Service']);
            unset($data['selectedcategotyTariff']);
            unset($data['DidCategoryTariffID']);
            unset($data['selectedcategotyTariffBulkAction']);

            $arrayTemplateID = explode(",", $data['ServiceTemplateId']);



            if (isset($data['InboundTariff'])) {
                unset($data['InboundTariff']);

                for ($i = 0; $i < sizeof($arrayTemplateID); $i++) {
                    unset($data['ServiceTemplateId']);
                    $data['ServiceTemplateId'] = $arrayTemplateID[$i];

                    for ($j = 0; $j < sizeof($getCollectionArray); $j++) {
                        $data['DIDCategoryId'] = $getCollectionArray[$j][0];
                        $data['RateTableId'] = $getCollectionArray[$j][1];


                        if(isset($data['DIDCategoryId']) && !empty($data['DIDCategoryId']))
                        {


                            //Log::info('$alreadyExistServices.' . $data['ServiceTemplateId'] . ' ' . $data['DIDCategoryId'] . ' ' . $data['RateTableId']);

                            $alreadyExistServices = ServiceTemapleInboundTariff::where('ServiceTemplateID', $data['ServiceTemplateId'])
                                ->where('DIDCategoryId', $data['DIDCategoryId'])
                                ->first();

                            //Log::info('$alreadyExistServices InboundTariffId.' . $alreadyExistServices->ServiceTemapleInboundTariffId);


                            if (!isset($alreadyExistServices)){
                                $updateFields1 = [];
                                $updateFields1['ServiceTemplateID'] = $data['ServiceTemplateId'];
                                $updateFields1['RateTableId'] = $data['RateTableId'];
                                $updateFields1['DIDCategoryId'] = $data['DIDCategoryId'];
                                ServiceTemapleInboundTariff::create($updateFields1);


                            }else {
                                $updateFields1 = [];
                                $updateFields1['ServiceTemplateID'] = $data['ServiceTemplateId'];
                                $updateFields1['RateTableId'] = $data['RateTableId'];
                                ServiceTemapleInboundTariff::where('ServiceTemapleInboundTariffId', $alreadyExistServices->ServiceTemapleInboundTariffId)
                                    ->update($updateFields1);
                            }
                        }else{
                            //Log::info('$alreadyExistServices else.' . $data['ServiceTemplateId'] . ' ' . $data['DIDCategoryId'] . ' ' . $data['RateTableId']);

                            $alreadyExistServices = ServiceTemapleInboundTariff::where('ServiceTemplateID', $data['ServiceTemplateId'])
                                ->WhereRaw('DIDCategoryId is null')->first();
//                            Log::info('$alreadyExistServices else case Query.' . ServiceTemapleInboundTariff::where('ServiceTemplateID', $data['ServiceTemplateId'])
//                                    ->WhereRaw('DIDCategoryId is null')->toSql());

                            //Log::info('$alreadyExistServices else case Query result.' . count($alreadyExistServices));

                            unset($data['DIDCategoryId']);
                            if (!isset($alreadyExistServices))
                            {
                                $updateFields1 = [];
                                $updateFields1['ServiceTemplateID'] = $data['ServiceTemplateId'];
                                $updateFields1['RateTableId'] = $data['RateTableId'];
                                ServiceTemapleInboundTariff::create($updateFields1);

                            }else{
                                $updateFields1 = [];
                                $updateFields1['ServiceTemplateID'] = $data['ServiceTemplateId'];
                                $updateFields1['RateTableId'] = $data['RateTableId'];
                                ServiceTemapleInboundTariff::where('ServiceTemapleInboundTariffId', $alreadyExistServices->ServiceTemapleInboundTariffId)
                                    ->update($updateFields1);
                            }
                        }





                    }
                }
            }
        }

            return Response::json(array("status" => "success", "message" => "Bulk Actions updated"));
        }catch (Exception $ex){
            Log::info($ex . count($ex));

            return Response::json(array("status" => "failed", "message" => "Failed to update Bulk Actions"));
        }




    }

}
