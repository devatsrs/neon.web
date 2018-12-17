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
            ->select(['tblService.ServiceId','tblServiceTemplate.Name','tblService.ServiceName','tblCurrency.Code','tblServiceTemplate.OutboundRateTableId','tblServiceTemplate.ServiceTemplateId','tblServiceTemplate.CurrencyId','tblServiceTemplate.InboundDiscountPlanId','tblServiceTemplate.OutboundDiscountPlanId'])
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


        if($data['selectedCurrency'] != ''){
            $outboundDiscountPlan->where('tblDiscountPlan.CurrencyID','=', $data['selectedCurrency']);
        }
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
        Log::info('$rate table query.' . $rateTable->toSql());


        if($data['selectedCurrency'] != ''){
            $rateTable->where('CurrencyID','=', $data['selectedCurrency']);
        }
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
                if ($result) {
                    $result = ServiceTemapleInboundTariff::where(array('ServiceTemplateID'=>$id))->delete();
                    Log::info('ServiceTemapleInboundTariff delete.' . $result);
                    if ($result) {
                        $result = ServiceTemplate::where(array('ServiceTemplateId'=>$id))->delete();
                        Log::info('ServiceTemaple delete.' . $result);
                        if ($result) {
                            return Response::json(array("status" => "success", "message" => "Service Successfully Deleted"));
                        }else {
                            return Response::json(array("status" => "failed", "message" => "Problem Deleting Service."));
                        }
                    }else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Service."));
                    }

                } else {
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
}
