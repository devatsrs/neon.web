<?php


class ServicesTemplateController extends BaseController {

    private $users;

    public function __construct() {

    }


    public function ajax_datagrid(){

       $data = Input::all();

       $companyID = User::get_companyID();
      // $data['ServiceStatus'] = $data['ServiceStatus']== 'true'?1:0;

        $iSortCol_0 = isset($data['iSortCol_0']) ? $data['iSortCol_0']:1;
        $sSortDir_0 = '';
        if (isset($data['sSortDir_0'])) {
            $sSortDir_0 = $data['sSortDir_0'];
        }else {
            $sSortDir_0 = "ASC";
        }

        Log::info('$sSortDir_0..' . $sSortDir_0);
        if ($iSortCol_0 == 1 || $iSortCol_0 == 0) {
            $iSortCol_0 = "tblServiceTemplate.Name";
        } else if ($iSortCol_0 == 2) {
            $iSortCol_0 = "tblService.ServiceName";
        }else if ($iSortCol_0 == 3) {
            $iSortCol_0 = "tblCurrency.Code";
        }
        $servicesTemplate = ServiceTemplate::
        leftJoin('tblService','tblService.ServiceID','=','tblServiceTemplate.ServiceId')
            ->Join('tblCurrency','tblServiceTemplate.CurrencyId','=','tblCurrency.CurrencyId')
            ->select(['tblServiceTemplate.ServiceTemplateId','tblService.ServiceId','tblServiceTemplate.Name','tblService.ServiceName','tblCurrency.Code','tblServiceTemplate.OutboundRateTableId','tblServiceTemplate.CurrencyId','tblServiceTemplate.InboundDiscountPlanId','tblServiceTemplate.OutboundDiscountPlanId'])
            ->orderBy($iSortCol_0, $sSortDir_0);

        Log::info('$servicesTemplate AJAX.$data[\'ServiceId\']' . $data['ServiceId']);
        Log::info('$servicesTemplate AJAX.$data[\'ServiceName\']' . $data['ServiceName']);

        if($data['ServiceName'] != ''){
            Log::info('$servicesTemplate AJAX.$data[\'ServiceName\']' . 'set the value');
                   $servicesTemplate->where('tblServiceTemplate.Name','like','%'.$data['ServiceName'].'%');
        }
        if($data['FilterCurrencyId'] != ''){
                   $servicesTemplate->where(["tblServiceTemplate.CurrencyId" => $data['FilterCurrencyId']]);
        }
        if($data['ServiceId'] != ''){
            $servicesTemplate->where(["tblServiceTemplate.ServiceId"=>$data['ServiceId']]);
        }

        Log::info('$servicesTemplate ajax_datagrid AJAX.' . $servicesTemplate->toSql());


       
       return Datatables::of($servicesTemplate)->make();
    }
    public function selectDataOnCurrency()
    {
        $data = Input::all();
        $selecteddata = $data['selectedData'];
        $companyID = User::get_companyID();
        // $data['ServiceStatus'] = $data['ServiceStatus']== 'true'?1:0;

        $servicesTemplate = Service::select(['tblService.ServiceId','tblService.ServiceName'])
            ;

        Log::info('$servicesTemplate AJAX.' . $servicesTemplate->toSql());
        Log::info('selectedCurrency' . $data['selectedCurrency']);


        $currenciesservices =  $servicesTemplate->get();

        $outboundDiscountPlan = DiscountPlan::
            select(['tblDiscountPlan.DiscountPlanID','tblDiscountPlan.Name'])
        ;

        Log::info('$outboundDiscountPlan query.' . $outboundDiscountPlan->toSql());


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



        if($data['selectedCurrency'] != ''){
            $rateTable->where('CurrencyID','=', $data['selectedCurrency']);
        }
        Log::info('$rate table query.' . $rateTable->toSql());
        $outboundtarifflist =  $rateTable->get();


        $BillingSubscription = BillingSubscription::select(['Name','SubscriptionID']);
        if($data['selectedCurrency'] != ''){
            $BillingSubscription->where('CurrencyID','=', $data['selectedCurrency']);
        }
        Log::info('$billing subscription query.' . $BillingSubscription->toSql());
        $billingsubscriptionlist = $BillingSubscription->get();

        $categoryTariff = RateTable::join('tblDIDCategory', 'tblDIDCategory.DIDCategoryID', '=', 'tblRateTable.DIDCategoryID');
        $categoryTariff->select(['tblRateTable.RateTableName as RateTableName','tblRateTable.RateTableID as RateTableID']);
        if($data['selectedCurrency'] != ''){
            $categoryTariff->where('CurrencyID','=', $data['selectedCurrency']);
            $categoryTariff->where('tblRateTable.Type','=', '1');
            $categoryTariff->where('tblRateTable.AppliedTo','!=',2 );
        }
        if(isset($data['selected_didCategory']) && $data['selected_didCategory'] != ''){
            $categoryTariff->where('tblRateTable.DIDCategoryID','=', $data['selected_didCategory']);
            Log::info('data[selected_didCategory].' . $data['selected_didCategory']);
        }
        Log::info('$rate table query.' . $categoryTariff->toSql());
        $categorytarifflist = $categoryTariff->get();

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
            Log::info('$selectedDIDCategoryTariffQuery query.' . $selectedDIDCategoryTariffQuery);
            $selecteddidcategorytariflist = DB::select($selectedDIDCategoryTariffQuery);
            Log::info('$selecteddidcategorytariflist count.' . count($selecteddidcategorytariflist));
        }

        return View::make('servicetemplate.populatedataoncurrency', compact('currenciesservices','selecteddata','outbounddiscountplan','inbounddiscountplan','outboundtarifflist','billingsubscriptionlist','categorytarifflist','billingsubsforsrvtemplate','selecteddidcategorytariflist'));

    }

    public function index() {

            $CompanyID  = User::get_companyID();
            $CategoryDropdownIDList = DIDCategory::getCategoryDropdownIDList($CompanyID);

            return View::make('servicetemplate.index', compact('CategoryDropdownIDList'));

    }

    public function store() {
        Log::info('Service Template Controller.');
        $data = Input::all();

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

        Log::info('read Subscription List.' . $subsriptionList);
        Log::info('read Category Tariff List.' . $CategoryTariffList);
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
                        Log::info('ServiceTemplateID.' . $ServiceTemplate->ServiceTemplateId);
                       // $subsriptionList = $data['SubscriptionID'];
                        foreach ($subsriptionList as $subsription) {
                            $ServiceTemapleSubscription['SubscriptionId'] = $subsription;
                            Log::info('Service Template Controller.' . $subsription);
                            ServiceTemapleSubscription::create($ServiceTemapleSubscription);
                        }

                        foreach ($CategoryTariffList as $index1 => $CategoryTariffValue) {
                            try {
                                $ServiceTemapleInboundTariff['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                                Log::info('$CategoryTariffValue1.' . $CategoryTariffValue);
                                $DIDRateTableList = explode("-", $CategoryTariffValue);
                                //Log::info('$CategoryTariffValue1.' . $DIDRateTableList);
                                Log::info('$CategoryTariffValue1.' . count($DIDRateTableList));
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

                        Log::info('Create the dynamic field.' . count($DynamicFields));
                        if(isset($DynamicFields) && count($DynamicFields)>0) {
                            for($k=0; $k<count($DynamicFields); $k++) {
                                if(trim($DynamicFields[$k]['FieldValue'])!='') {
                                    $DynamicFields[$k]['ParentID'] = $ServiceTemplate->ServiceTemplateId;
                                    DB::table('tblDynamicFieldsValue')->insert($DynamicFields[$k]);
                                }
                            }
                        }

                        return  Response::json(array("status" => "success", "message" => "Service Template Successfully Created",'LastID'=>$ServiceTemplate->ServiceTemplateId,'newcreated'=>$ServiceTemplate));
                    } else {
                        return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
                    }

                }


     //  return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
    }

    public function update($ServiceTemplateId) {
        Log::info('update ServiceTemplateID.' . $ServiceTemplateId);
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

        Log::info('update Subscription List.' . $subsriptionList);
        Log::info('update Category Tariff List.' . $CategoryTariffList);
        $subsriptionList = explode(",",$subsriptionList);
        $CategoryTariffList = explode(",",$CategoryTariffList);
        $OutboundDiscountPlanId = isset($data['OutboundDiscountPlanId'])?$data['OutboundDiscountPlanId']:'';
        $InboundDiscountPlanId = isset($data['InboundDiscountPlanId'])?$data['InboundDiscountPlanId']:'';
        $data['ModifiedBy'] = User::get_user_full_name();
        $user =  $data['ModifiedBy'];

        if(isset($data['DynamicFields']) && count($data['DynamicFields']) > 0) {
            $CompanyID = User::get_companyID();
            foreach ($data['DynamicFields'] as $key => $value) {
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



            ServiceTemplate::$updateRules['Name'] = 'required|unique:tblServiceTemplate,Name,'.$ServiceTemplateId.',ServiceTemplateId';
            $validator = Validator::make($data, ServiceTemplate::$updateRules);

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



            $updDelStatus = false;
            $result = ServiceTemapleSubscription::where(array('ServiceTemplateID'=>$ServiceTemplateId))->delete();
            $result = ServiceTemapleInboundTariff::where(array('ServiceTemplateID' => $ServiceTemplateId))->delete();
            $updDelStatus = true;


            if( $updDelStatus && $ServiceTemplate = ServiceTemplate::find($ServiceTemplateId)){

                $ServiceTemapleSubscription['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                Log::info('ServiceTemplateID.' . $ServiceTemplate->ServiceTemplateId);
                // $subsriptionList = $data['SubscriptionID'];
                foreach ($subsriptionList as $subsription) {
                    $ServiceTemapleSubscription['SubscriptionId'] = $subsription;
                    Log::info('Service Template Controller.' . $subsription);
                    ServiceTemapleSubscription::create($ServiceTemapleSubscription);
                }

                foreach ($CategoryTariffList as $index1 => $CategoryTariffValue) {
                    try {
                        $ServiceTemapleInboundTariff['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                        Log::info('$CategoryTariffValue1.' . $CategoryTariffValue);
                        $DIDRateTableList = explode("-", $CategoryTariffValue);
                        //Log::info('$CategoryTariffValue1.' . $DIDRateTableList);
                        Log::info('$CategoryTariffValue1.' . count($DIDRateTableList));
                        if ($DIDRateTableList[0] != 0) {
                            $ServiceTemapleInboundTariff['DIDCategoryId'] = $DIDRateTableList[0];
                        }
                        $ServiceTemapleInboundTariff['RateTableId'] = $DIDRateTableList[1];
                        Log::info('$DIDRateTableList[0].' . $DIDRateTableList[0]);
                        Log::info('$DIDRateTableList[1].' . $DIDRateTableList[1]);

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
               Log::info('service template delete.' . $id);
               $result = ServiceTemapleSubscription::where(array('ServiceTemplateID'=>$id))->delete();
               Log::info('ServiceTemapleSubscription delete.' . $result);
               $result = ServiceTemapleInboundTariff::where(array('ServiceTemplateID'=>$id))->delete();
               Log::info('ServiceTemapleInboundTariff delete.' . $result);
               $result = ServiceTemplate::where(array('ServiceTemplateId'=>$id))->delete();
               Log::info('ServiceTemaple delete.' . $result);
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
                                Log::info("delete DynamicFieldValue ProductID3=".$id);
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
                                Log::info("delete DynamicFieldValue ProductID=".$id);
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
            Log::info('updateDelete service template delete.' . $id);
            $result = ServiceTemapleSubscription::where(array('ServiceTemplateID'=>$id))->delete();
            Log::info('updateDelete ServiceTemapleSubscription delete.' . $result);
            if ($result) {
                $result = ServiceTemapleInboundTariff::where(array('ServiceTemplateID'=>$id))->delete();
                Log::info('updateDelete ServiceTemapleInboundTariff delete.' . $result);
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
            $companyID = User::get_companyID();
            $data = Input::all();
            //$data['ServiceStatus']=$data['ServiceStatus']=='true'?1:0;

       /* $exportSelectedTemplate = "select tempalte.ServiceTemplateId,".
                                "(select service1.ServiceName from tblService service1 where service1.ServiceID = tempalte.ServiceId) as serviceName,".
                                "(select accountPlan.Name from tblDiscountPlan accountPlan where accountPlan.DiscountPlanID = tempalte.InboundDiscountPlanId) as InboundDiscountPlanId,".
                                "(select accountPlan.Name from tblDiscountPlan accountPlan where accountPlan.DiscountPlanID = tempalte.OutboundDiscountPlanId) as OutboundDiscountPlanId,".
                                "(select GROUP_CONCAT(billSubscription.Name SEPARATOR ', ' ) as serviceSubscription from speakintelligentBilling.tblBillingSubscription billSubscription where billSubscription.SubscriptionID in (select billSubs.SubscriptionId from tblServiceTemapleSubscription billSubs where billSubs.ServiceTemplateID = tempalte.ServiceTemplateId)) as subscriptionList,".
                                "(select GROUP_CONCAT(rateTable.RateTableName SEPARATOR ', ' ) as categoryTariff from tblRateTable rateTable where rateTable.RateTableId in (select categoryTariff.RateTableId from tblServiceTemapleInboundTariff categoryTariff where categoryTariff.ServiceTemplateID = tempalte.ServiceTemplateId)) as categoryTariff ".
                                "from tblServiceTemplate tempalte";
       DB::select($exportSelectedTemplate)
       */
        $exportSelectedTemplate = ServiceTemplate::
        Join('tblService','tblService.ServiceID','=','tblServiceTemplate.ServiceId')
            ->Join('tblCurrency','tblServiceTemplate.CurrencyId','=','tblCurrency.CurrencyId')
            ->select(['tblServiceTemplate.Name','tblService.ServiceName','tblCurrency.Code as Currency'])
            ->orderBy("tblServiceTemplate.Name", "ASC");

        if($data['ServiceName'] != ''){
            Log::info('$servicesTemplate AJAX.$data[\'ServiceName\']' . 'set the value');
            $exportSelectedTemplate->where('tblServiceTemplate.Name','like','%'.$data['ServiceName'].'%');
        }
        if($data['FilterCurrencyId'] != ''){
            $exportSelectedTemplate->where(["tblServiceTemplate.CurrencyId" => $data['FilterCurrencyId']]);
        }
        if($data['ServiceId'] != ''){
            $exportSelectedTemplate->where(["tblServiceTemplate.ServiceId"=>$data['ServiceId']]);
        }

        Log::info('$exportSelectedTemplate query.' . $exportSelectedTemplate->toSql());
        $exportSelectedTemplate = $exportSelectedTemplate->get();
        Log::info('$exportSelectedTemplate count.' . count($exportSelectedTemplate));


        $services = json_decode(json_encode($exportSelectedTemplate),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Services.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($services);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Services.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($services);
            }

    }

    public function storeServiceTempalteData($data='') {
        Log::info('storeServiceTempalteData:Service Template Controller.');
        try {
            $post_vars = json_decode(file_get_contents("php://input"),true);
            Log::info('storeServiceTempalteData:storeServiceTempalteData.' . $post_vars);
            $data['Name'] = $post_vars->Name;
            Log::info('storeServiceTempalteData:$data[\'Name\'].' . $data['Name']);
            $data['ServiceId'] = $post_vars->ServiceId;
            $data['CurrencyId'] = $post_vars->CurrencyId;
            $data['OutboundDiscountPlanId'] = $post_vars->OutboundDiscountPlanId;
            $data['InboundDiscountPlanId'] = $post_vars->InboundDiscountPlanId;
            $data['OutboundRateTableId'] = $post_vars->OutboundRateTableId;
            $data['selectedSubscription'] = $post_vars->selectedSubscription;
            $data['selectedcategotyTariff'] = $post_vars->selectedcategotyTariff;
            Log::info('storeServiceTempalteData:storeServiceTempalteData.' .
            'Name:'. $data['Name'].'ServiceId' . $data['ServiceId'] . 'CurrencyId' . $data['CurrencyId'].
            'OutboundDiscountPlanId' . $data['OutboundDiscountPlanId'] . 'InboundDiscountPlanId' . $data['InboundDiscountPlanId'].
            'OutboundRateTableId' . $data['OutboundRateTableId'] . 'selectedSubscription' . $data['selectedSubscription'].
            'selectedcategotyTariff' . $data['selectedcategotyTariff']);


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

        Log::info('storeServiceTempalteData:read Subscription List.' . $subsriptionList);
        Log::info('storeServiceTempalteData:read Category Tariff List.' . $CategoryTariffList);
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
                Log::info('storeServiceTempalteData:ServiceTemplateID.' . $ServiceTemplate->ServiceTemplateId);
                // $subsriptionList = $data['SubscriptionID'];
                foreach ($subsriptionList as $subsription) {
                    $ServiceTemapleSubscription['SubscriptionId'] = $subsription;
                    Log::info('storeServiceTempalteData:Service Template Controller.' . $subsription);
                    ServiceTemapleSubscription::create($ServiceTemapleSubscription);
                }

                foreach ($CategoryTariffList as $index1 => $CategoryTariffValue) {
                    try {
                        $ServiceTemapleInboundTariff['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                        Log::info('storeServiceTempalteData:$CategoryTariffValue1.' . $CategoryTariffValue);
                        $DIDRateTableList = explode("-", $CategoryTariffValue);
                        //Log::info('$CategoryTariffValue1.' . $DIDRateTableList);
                        Log::info('storeServiceTempalteData:$CategoryTariffValue1.' . count($DIDRateTableList));
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
        Log::info('ajax_GetServiceTemplateType $query.' . $query);
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
            Log::info('UpdateBulkItemTypeStatus 1.' );
            $userID = User::get_userID();

            if (!isset($data['Active']) || $data['Active'] == '') {
                $data['Active'] = 9;
            } else {
                $data['Active'] = (int)$data['Active'];
            }

            $query = "call prc_UpdateDynamicFieldStatus (" . $CompanyID . ",'" . $UserName . "','product','" . $data['FieldName'] . "','" . $data['FieldDomType'] . "','" . $data['ItemTypeID'] . "'," . $data['Active'] . "," . $data['status_set'] . ")";
            Log::info('UpdateBulkItemTypeStatus 1.' . $query );
            $result = DB::connection('sqlsrv')->select($query);
            return Response::json(array("status" => "success", "message" => "Dynamic Field Status Updated"));
        }

        if ($data['criteria_ac'] == 'selected') { //selceted ids from current page
            if (isset($data['SelectedIDs']) && count($data['SelectedIDs']) > 0) {
                foreach($data['SelectedIDs'] as $SelectedID){
                Log::info('UpdateBulkItemTypeStatus 2.' . $SelectedID);
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
        Log::info('getSubscritionsDynamicField $DynamicFieldsSql.' . $DynamicFieldsSql->toSql());
        $DynamicFields['fields'] = $DynamicFieldsSql->get();
        Log::info('getSubscritionsDynamicField.' . count($DynamicFields) );
        $DynamicFields['totalfields'] = count($DynamicFields['fields']);
        Log::info('getSubscritionsDynamicField.' . count($DynamicFields) );
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
        if(isset($data['ServiceTemplateId']))
        {
            $ServiceTemplateIdString =  ((string)$data['ServiceTemplateId']);
            $ServiceTemplateIdArray  = explode(',',$ServiceTemplateIdString);

            for($i = 0; $i < sizeof($ServiceTemplateIdArray); $i++ )
            {

                $ExistingValues = ServiceTemplate::select('CurrencyId','ServiceId','OutboundRateTableId','OutboundDiscountPlanId','InboundDiscountPlanId')
                                                    ->where('ServiceTemplateId',$ServiceTemplateIdArray[$i])->first();

                Log::info('Existing Log'.' ,'.  $ExistingValues['OutboundRateTableId']);


                $UpdatedValues  = ServiceTemplate::where('ServiceTemplateId',$ServiceTemplateIdArray[$i])
                                                    ->update([
                                                                'CurrencyId'             => (isset($data['CurrencyId']) ? $data['CurrencyId'] : $ExistingValues['CurrencyId']),
                                                                'ServiceId'              => (isset($data['ServiceId']) ? $data['ServiceId'] : $ExistingValues['ServiceId']),
                                                                'OutboundRateTableId'    => (isset($data['OutboundRateTableId']) ? $data['OutboundRateTableId'] : $ExistingValues['OutboundRateTableId']),
                                                                'OutboundDiscountPlanId' => (isset($data['OutboundDiscountPlanId']) ? $data['OutboundDiscountPlanId'] : $ExistingValues['OutboundDiscountPlanId']),
                                                                'InboundDiscountPlanId'  => (isset($data['OutboundDiscountPlanId']) ? $data['OutboundDiscountPlanId'] : $ExistingValues['OutboundDiscountPlanId']),
                                                            ]);
            }

            if($UpdatedValues)
                return Response::json(array("status" => "success", "message" => "Bulk Actions updated"));
            else
                return Response::json(array("status" => "failed", "message" => "Failed to update Bulk Actions"));

        }

    }

}
