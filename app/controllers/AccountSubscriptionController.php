<?php

class AccountSubscriptionController extends \BaseController {

public function main() {	
		$data 			 = Input::all();
		$id				 = $data['id'];
		$companyID 		 = User::get_companyID();
		$SelectedAccount = Account::find($id);
		$accounts	 	 = Account::getAccountIDList();
		$services 		 = Service::getDropdownIDList($companyID);
        $DiscountPlan    = DiscountPlan::getDiscountPlanIDList($companyID);
        $Subscritions    =  BillingSubscription::getSubscriptionsList();


        return View::make('accountsubscription.main', compact('accounts','services','SelectedAccount','services','DiscountPlan', 'Subscritions', 'id'));

    }

    /** Used in Account Service Edit Page */
    public function ajax_datagrid($id){
        $data = Input::all();        
        $id = $data['account_id'];
        
        $select = [
            "tblAccountSubscription.AccountSubscriptionID as AID",
            "tblBillingSubscription.Name",
            "InvoiceDescription", "Qty" ,"tblAccountSubscription.StartDate",
            DB::raw("IF(tblAccountSubscription.EndDate = '0000-00-00','',tblAccountSubscription.EndDate) as EndDate"),            
            "tblAccountSubscription.ActivationFee",
            
            "tblAccountSubscription.MonthlyFee",
            
            "tblAccountSubscription.AccountSubscriptionID","tblAccountSubscription.SubscriptionID",
            "tblAccountSubscription.SequenceNo",
            "CurrencyTbl1.Code as OneOffCurrency",
            
            "tblAccountSubscription.DailyFee", "tblAccountSubscription.WeeklyFee",
            "CurrencyTbl2.Code as RecurringCurrency",
            
            "tblAccountSubscription.QuarterlyFee","tblAccountSubscription.AnnuallyFee",
            "tblAccountSubscription.ExemptTax","tblAccountSubscription.Status",
            "tblAccountSubscription.DiscountAmount","tblAccountSubscription.DiscountType",
            "tblAccountSubscription.OneOffCurrencyID","tblAccountSubscription.RecurringCurrencyID",
            "CurrencyTbl1.Symbol as OneOffCurrencySymbol","CurrencyTbl2.Symbol as RecurringCurrencySymbol",
        ];
        
        
        $subscriptions = AccountSubscription::join('tblBillingSubscription', 'tblAccountSubscription.SubscriptionID', '=', 'tblBillingSubscription.SubscriptionID')->where("tblAccountSubscription.AccountID",$id);

        $subscriptions->leftJoin('speakintelligentRM.tblCurrency as CurrencyTbl1', 'tblAccountSubscription.OneOffCurrencyID', '=', 'CurrencyTbl1.CurrencyID');
        $subscriptions->leftJoin('speakintelligentRM.tblCurrency as CurrencyTbl2', 'tblAccountSubscription.RecurringCurrencyID', '=', 'CurrencyTbl2.CurrencyID');
        if(!empty($data['SubscriptionName'])){
            $subscriptions->where('tblBillingSubscription.Name','Like','%'.trim($data['SubscriptionName']).'%');
        }
        if(!empty($data['SubscriptionInvoiceDescription'])){
            $subscriptions->where('tblAccountSubscription.InvoiceDescription','Like','%'.trim($data['SubscriptionInvoiceDescription']).'%');
        }
        if(!empty($data['ServiceID'])){
            $subscriptions->where('tblAccountSubscription.ServiceID','=',$data['ServiceID']);
        }else{
            $subscriptions->where('tblAccountSubscription.ServiceID','=',0);
        }
        if(!empty($data['AccountServiceID'])){
            $subscriptions->where('tblAccountSubscription.AccountServiceID','=',$data['AccountServiceID']);
        }else{
            $subscriptions->where('tblAccountSubscription.AccountServiceID','=',0);
        }
        if(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'true'){
            $subscriptions->where('tblAccountSubscription.Status','=',1);

        }elseif(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'false'){
            $subscriptions->where('tblAccountSubscription.Status','=',0);
        }
        $subscriptions->select($select);

        return Datatables::of($subscriptions)->make();
    }

    /** Used in Main Account Subscription Page */
	public function ajax_datagrid_page($type=''){

        $data 						 = 	Input::all(); //Log::info(print_r($data,true));
        $data['iDisplayStart'] 		+=	1;
        $companyID 					 =  User::get_companyID(); 
        $columns 					 =  ['SequenceNo','AccountName','ServiceName','Name','Qty','StartDate','EndDate','ActivationFee','DailyFee','WeeklyFee','MonthlyFee','QuarterlyFee','AnnuallyFee','DiscountAmount','DiscountType'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];
        $data['AccountID'] 			 =  empty($data['AccountID'])?'0':$data['AccountID'];
		if($data['Active'] == 'true'){
			$data['Active']	=	1;
		}else{
			$data['Active'] =   0;
		}
		$data['ServiceID'] 			 =  empty($data['ServiceID'])?'null':$data['ServiceID'];
       $query = "call prc_GetAccountSubscriptions (".$companyID.",".intval($data['AccountID']).",".intval($data['ServiceID']).",'".$data['Name']."','".$data['Active']."','".date('Y-m-d')."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".strtoupper($data['sSortDir_0'])."'";


        if(isset($data['Export']) && $data['Export'] == 1)
		{
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
			
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/accountsubscription.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/subscription.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }         
        }
		

        $query .=',0)'; Log::info($query);
       // echo $query;exit;
        $result =  DataTableSql::of($query,'sqlsrv2')->make();
		return $result;
    }

	/**
	 * Store a newly created resource in storage.
	 * POST /accountsubscription
	 *
	 * @return Response
	 */
	public function store($id)
	{
		$data = Input::all();
                $data["AccountID"] = $id;
                $data["CreatedBy"] = User::get_user_full_name();
                $data['ExemptTax'] = isset($data['ExemptTax']) ? 1 : 0;
                $data['Status'] = isset($data['Status']) ? 1 : 0;
                
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');
        
        $rules = array(
           // 'AccountID'         =>      'required',
            'SubscriptionID'    =>  'required',
            'ActivationFee' => 'required|numeric',
            'DailyFee' => 'required|numeric',
            'WeeklyFee' => 'required|numeric',
            'MonthlyFee' => 'required|numeric',
            'QuarterlyFee' => 'required|numeric',
            'AnnuallyFee' => 'required|numeric',
            'OneOffCurrencyID' => 'required',
            'RecurringCurrencyID' => 'required',
            'StartDate'               =>'required',
            'EndDate'               =>'required',
        );
//        if(!empty($data['EndDate'])) {
//            $rules['StartDate'] = 'required|date|before:EndDate';
//            $rules['EndDate'] = 'required|date';
//        }
        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        
        $dynamiceFields = array();
        AccountSubscription::$rules['SubscriptionID'] = 'required|unique:tblAccountSubscription,AccountSubscriptionID,NULL,SubscriptionID,'.$data['SubscriptionID'].',AccountID,'.$data["AccountID"];

        

        
        unset($data['Status_name']);
        if(empty($data['SequenceNo'])){
            $SequenceNo = AccountSubscription::where(['AccountID'=>$data["AccountID"]])->max('SequenceNo');
            $SequenceNo = $SequenceNo +1;
            $data['SequenceNo'] = $SequenceNo;
        }


        if(isset($data['dynamicFileds']))
        {
            Log::info('Trach Line...1' . count($data['dynamicFileds']));
            $dynamicData['dynamicFileds'] = $data['dynamicFileds'];
            unset($data['dynamicFileds']);
        }

    /*    if(isset($data['dynamicFileds']))
        {
            $dynamiceFields = $data['dynamicFileds'];

            if(isset($data['dynamicSelect']) && !empty($data['dynamicSelect']))
            {
                $selectBox = $data['dynamicSelect'];
                $dynamiceFields[] = implode(",",$selectBox);
                unset( $data['dynamicSelect']);

            }
            unset($data['dynamicFileds']);
            unset($data['AccountSubscriptionID']);
        }

        $companyID        = User::get_companyID();
        if (isset($data['dynamicImage']) && !empty($data['dynamicImage'])) {
            $GetDynamicImg['dynamicImage'] = $data['dynamicImage'];
        }
        unset($data['dynamicImage']);
    */

        if(isset($data['ImageID']) && !empty($data['ImageID']))
        {
            $DaynamicImageID     = $data['ImageID'];
            $GetDynamicImageInfo = $data['dynamicImage'];
            unset($data['ImageID']);
        }
        unset($data['dynamicImage']);
        $data['Status'] = '1';
        $DynamicFields = [];

        if ($AccountSubscription = AccountSubscription::create($data)) {
            //$data['DynamicFields'] = $dynamicData['dynamicFileds'];
            //Log::info('Trach Line...2' . count($data['DynamicFields']));
            $dynamiceFields['AccountID']  = $data['AccountID'];

            if(isset($data['DynamicFields'])) {
                $j=0;
                Log::info('Trach Line...21' . count($data['DynamicFields']));
                $companyID = User::get_companyID();

                foreach($data['DynamicFields'] as $key => $value) {

                    $key = (int) $key;
                    $DynamicFields[$j]['DynamicFieldsID'] = $key;
                    $DynamicFields[$j]['CompanyID'] = $companyID;
                    $DynamicFields[$j]['FieldValue'] = $value;
                    $DynamicFields[$j]['created_at'] = date('Y-m-d H:i:s.000');
                    $DynamicFields[$j]['created_by'] = User::get_user_full_name();
                    $j++;
                }
            }

            if(isset($DynamicFields) && count($DynamicFields)>0) {
                for($k=0; $k<count($DynamicFields); $k++) {

                        AccountSubsDynamicFields::create([
                            'AccountSubscriptionID' => $AccountSubscription->AccountSubscriptionID,
                            'AccountID' => $dynamiceFields['AccountID'],
                            'DynamicFieldsID' => $DynamicFields[$k]['DynamicFieldsID'],
                            'FieldValue' => $DynamicFields[$k]['FieldValue'],
                            'FieldOrder' => $k
                        ]);
                }
            }


            if(isset($GetDynamicImageInfo) && !empty($GetDynamicImageInfo))
            {
                if(isset($_FILES["dynamicImage"]["name"])){
                    $dynamicImage = $_FILES["dynamicImage"]["name"];
                    if($dynamicImage){
                        $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID)."/";
                        $fileUrl=$companyID."/dynamicfields/";
                        if (!file_exists($upload_path.$fileUrl)) {
                            mkdir($upload_path.$fileUrl, 0777, true);
                        }
                        $dynamicImage = time().$companyID. $dynamicImage;

                        $success=move_uploaded_file($_FILES["dynamicImage"]["tmp_name"],$upload_path.$fileUrl.$dynamicImage);

                        if($success){
                            AccountSubsDynamicFields::create([
                                'AccountSubscriptionID' => $AccountSubscription->AccountSubscriptionID,
                                'AccountID' => $dynamiceFields['AccountID'],
                                'DynamicFieldsID' => $DaynamicImageID,
                                'FieldValue' => $dynamicImage,
                                'FieldOrder' => count($DynamicFields)
                            ]);

                            unset($_FILES);
                            unset($GetDynamicImageInfo);

                        }
                    }

                }
            }


            return Response::json(array("status" => "success", "message" => "Subscription Successfully Created",'LastID'=>$AccountSubscription->AccountSubscriptionID));

        } else {

            return Response::json(array("status" => "failed", "message" => "Problem Creating Subscription."));
        }
	}

	public function update($AccountID,$AccountSubscriptionID)
	{
        if( $AccountID  > 0  && $AccountSubscriptionID > 0 ) {
            $data = Input::all();

            $data['Status'] = isset($data['Status']) ? 1 : 0;
            $AccountSubscriptionID = $data['AccountSubscriptionID'];
            $AccountSubscription = AccountSubscription::find($AccountSubscriptionID);
            $data["AccountID"] = $AccountID;
            $data["ModifiedBy"] = User::get_user_full_name();
            $data['ExemptTax'] = isset($data['ExemptTax']) ? 1 : 0;
            AccountSubscription::$rules['SubscriptionID'] = 'required|unique:tblAccountSubscription,AccountSubscriptionID,NULL,SubscriptionID,' . $data['SubscriptionID'] . ',AccountID,' . $data["AccountID"];

            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $rules = array(
                // 'AccountID'         =>      'required',
                 'SubscriptionID'    =>  'required',
                 'ActivationFee' => 'required|numeric',
                 'DailyFee' => 'required|numeric',
                 'WeeklyFee' => 'required|numeric',
                 'MonthlyFee' => 'required|numeric',
                 'QuarterlyFee' => 'required|numeric',
                 'AnnuallyFee' => 'required|numeric',
                 'OneOffCurrencyID' => 'required',
                 'RecurringCurrencyID' => 'required',
                 'StartDate'               =>'required',
                 'EndDate'               =>'required',
             );
            if (!empty($data['EndDate'])) {
                $rules['StartDate'] = 'required|date|before:EndDate';
                $rules['EndDate'] = 'required|date';
            }
            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['Status_name']);
            if(isset($data['dynamicFileds']))
            {
                $dynamiceFields = $data['dynamicFileds'];

                unset($data['dynamicFileds']);
                unset($data['AccountSubscriptionID']);
            }

            if(isset($data['dynamicSelect']) && !empty($data['dynamicSelect']))
            {
                $selectBox = $data['dynamicSelect'];
                $dynamiceFields[] = implode(",",$selectBox);
                unset( $data['dynamicSelect']);

            }
            $companyID = User::get_companyID();

            if (isset($data['dynamicImage']) && !empty($data['dynamicImage'])) {
                $GetDynamicImg['dynamicImage'] = $data['dynamicImage'];
            }
            unset($data['dynamicImage']);


            try {
                $AccountSubscription->update($data);

                $GetDynamiceAll = DynamicFields::join('speakintelligentBilling.tblAccountSubsDynamicFields as db', function ($join) {
                    $join->on('db.DynamicFieldsID', '=', 'tblDynamicFields.DynamicFieldsID');
                })->select('tblDynamicFields.DynamicFieldsID', 'tblDynamicFields.FieldName', 'tblDynamicFields.FieldDomType')
                    ->where('db.AccountID', '=', $AccountID)
                    ->where('db.AccountSubscriptionID', '=', $AccountSubscriptionID)
                    ->get();

                foreach ($GetDynamiceAll as $DynamicFieldsID) {
                    $ids [] = $DynamicFieldsID->DynamicFieldsID;
                    $name[] = $DynamicFieldsID->FieldName;
                    $type[] = $DynamicFieldsID->FieldDomType;
                }
                if(isset($ids) && isset($name) && isset($type) ){
                        for ($i = 0; $i < sizeof($ids); $i++) {
                            if (isset($_FILES["dynamicImage"]["name"]) && $type[$i] == "file") {
                                $dynamicImage = $_FILES["dynamicImage"]["name"];
                                if ($dynamicImage) {
                                    $upload_path = CompanyConfiguration::get('UPLOAD_PATH', $companyID) . "/";
                                    $fileUrl = $companyID . "/dynamicfields/";

                                    if (!file_exists($upload_path . $fileUrl)) {
                                        mkdir($upload_path . $fileUrl, 0777, true);
                                    }

                                    //rename with time img
                                    $dynamicImage = time() . $dynamicImage;
                                    //uploading theimage below directory path
                                    $success = move_uploaded_file($_FILES["dynamicImage"]["tmp_name"], $upload_path . $fileUrl . $dynamicImage);

                                    if ($success) {

                                        AccountSubsDynamicFields::where('AccountID', $AccountID)
                                            ->where('AccountSubscriptionID', $AccountSubscriptionID)
                                            ->where('FieldOrder', $i)
                                            ->where('DynamicFieldsID', $ids[$i])
                                            ->update([
                                                'FieldValue' => $dynamicImage
                                            ]);

                                        unset($_FILES);

                                        if ($name[$i] == "file") {
                                            unset($ids[$i]);
                                            unset($name[$i]);
                                        }

                                    } else {

                                        return Response::json(array("status" => "failed", "message" => "Error: There was a problem uploading your file. Please try again."));
                                    }
                                }


                            } else {

                               if(!empty($dynamiceFields[$i]))
                               {

                                      AccountSubsDynamicFields::where('AccountID', $AccountID)
                                       ->where('AccountSubscriptionID', $AccountSubscriptionID)
                                       ->where('FieldOrder', $i)
                                       ->where('DynamicFieldsID', $ids[$i])
                                       ->update([
                                           'FieldValue' =>(isset($dynamiceFields[$i]) ? $dynamiceFields[$i] : null),
                                       ]);
                               }

                           }

                        }
                }

                return Response::json(array("status" => "success", "message" => "Subscription Successfully Updated", 'LastID' => $AccountSubscription->AccountSubscriptionID));
            }catch(Exception $ex){
                Log::info('Trach Line...' . $ex->getTraceAsString());
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:" . $ex->getMessage()));
            }
        }
	}


	public function delete($AccountID,$AccountSubscriptionID)
	{
        if( intval($AccountSubscriptionID) > 0){

            if(!AccountSubscription::checkForeignKeyById($AccountSubscriptionID)){
                try{
                    $AccountSubscription = AccountSubscription::find($AccountSubscriptionID);
                    $SubscriptionDiscountPlanCount = SubscriptionDiscountPlan::where("AccountSubscriptionID",$AccountSubscriptionID)->count();

                    if($SubscriptionDiscountPlanCount > 0)
                    {
                        return Response::json(array("status" => "failed", "message" => "Subscription is in Use, Please Delete Discount Plan."));
                        //$SubscriptionDiscountPlanCount = SubscriptionDiscountPlan::where("AccountSubscriptionID",$AccountSubscriptionID)->delete();
                    }

                     AccountSubsDynamicFields::where("AccountSubscriptionID",$AccountSubscriptionID)->where("AccountID",$AccountID )->delete();

                    $result = $AccountSubscription->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Subscription Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Subscription."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Subscription is in Use, You can not delete this Subscription."));
            }
        }
	}

    public function store_discountplan($id)
    {
        $data = Input::all();
        $data["AccountID"] = $id;
        $data["AccountSubscriptionID"] = $data["AccountSubscriptionID_dp"];
        unset($data["AccountSubscriptionID_dp"]);
       // $data["CreatedBy"] = User::get_user_full_name();
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv');

        $rules = array(
            'AccountName'           =>  'required|unique:tblAccountDiscountPlan,AccountName',
            'AccountCLI'            =>  'unique:tblAccountDiscountPlan,AccountCLI',
            //'AccountCLI'            =>  'required|unique:tblSubscriptionDiscountPlan,AccountCLI',
        );

        $message = [
            'AccountCLI.unique'=>'Account CLI field is already taken.'
        ];

        $validator = Validator::make($data, $rules, $message);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($data['AccountCLI'] == "")
        {
            $data['AccountCLI'] = NULL;
        }

        $AccountID = $data['AccountID'];
        $ServiceID = $data['ServiceID'];
        $AccountServiceID = AccountSubscription::where(['AccountSubscriptionID'=>$data["AccountSubscriptionID"]])->pluck('AccountServiceID');
        $OutboundDiscountPlan = empty($data['OutboundDiscountPlans']) ? '' : $data['OutboundDiscountPlans'];
        $InboundDiscountPlan = empty($data['InboundDiscountPlans']) ? '' : $data['InboundDiscountPlans'];
        $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), 0);
        try {
            DB::beginTransaction();
            $SubscriptionDiscountPlan = SubscriptionDiscountPlan::create($data);
            if (!empty($SubscriptionDiscountPlan->SubscriptionDiscountPlanID) && !empty($AccountPeriod)) {

                log::info('SubscriptionDiscountPlanID ' . $SubscriptionDiscountPlan->SubscriptionDiscountPlanID);
                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                $AccountSubscriptionID = $data['AccountSubscriptionID'];
                $AccountName = empty($data['AccountName']) ? '' : $data['AccountName'];
                $AccountCLI = empty($data['AccountCLI']) ? '' : $data['AccountCLI'];
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $ServiceID,$AccountServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlan->SubscriptionDiscountPlanID);
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $ServiceID,$AccountServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlan->SubscriptionDiscountPlanID);
                DB::commit();
                return Response::json(array("status" => "success", "message" => "Subscription Account Added", 'LastID' => $SubscriptionDiscountPlan->SubscriptionDiscountPlanID));

            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Adding Subscription Account."));
            }
        }catch( Exception $e){
            try {
                DB::rollback();
            } catch (\Exception $err) {
                Log::error($err);
            }
            Log::error($e);
            return Response::json(array("status" => "failed", "message" => "Problem Adding Subscription Account."));
        }

        /*
        if ($SubscriptionDiscountPlan = SubscriptionDiscountPlan::create($data)) {
            return Response::json(array("status" => "success", "message" => "Subscription Account Added",'LastID'=>$SubscriptionDiscountPlan->SubscriptionDiscountPlanID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Adding Subscription Account."));
        }*/
    }

    function edit_discountplan(){
        $data = Input::all();
        $SubscriptionDiscountPlan =  SubscriptionDiscountPlan::getSubscriptionDiscountPlanById($data['SubscriptionDiscountPlanID']);
        return $SubscriptionDiscountPlan;
    }

    public function update_discountplan()
    {
        $data = Input::all();
        $data["AccountSubscriptionID"] = $data["AccountSubscriptionID_dp"];
        unset($data["AccountSubscriptionID_dp"]);
        //unset($data["AccountSubscriptionID"]);
        $SubscriptionDiscountPlan = SubscriptionDiscountPlan::find($data['SubscriptionDiscountPlanID']);
        // $data["CreatedBy"] = User::get_user_full_name();
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv');

        $rules = array(
            'AccountName'           =>  'required|unique:tblSubscriptionDiscountPlan,AccountName,' . $data['SubscriptionDiscountPlanID'] . ',SubscriptionDiscountPlanID',
            'AccountCLI'            =>  'unique:tblSubscriptionDiscountPlan,AccountCLI,' . $data['SubscriptionDiscountPlanID'] . ',SubscriptionDiscountPlanID',
            //'AccountCLI'            =>  'required|unique:tblSubscriptionDiscountPlan,AccountCLI,' . $data['SubscriptionDiscountPlanID'] . ',SubscriptionDiscountPlanID',
        );
        $message = [
            'AccountCLI.unique'=>'Account CLI field is already taken.'
        ];

        $validator = Validator::make($data, $rules,$message);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if($data['AccountCLI'] == "")
        {
            $data['AccountCLI'] = NULL;
        }

        $AccountID = $SubscriptionDiscountPlan->AccountID;
        $ServiceID = $SubscriptionDiscountPlan->ServiceID;
        $AccountServiceID = $SubscriptionDiscountPlan->AccountServiceID;
        $OutboundDiscountPlan = empty($data['OutboundDiscountPlans']) ? '' : $data['OutboundDiscountPlans'];
        $InboundDiscountPlan = empty($data['InboundDiscountPlans']) ? '' : $data['InboundDiscountPlans'];
        $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), 0);
        try {
            DB::beginTransaction();
            $SubscriptionDiscountPlan->update($data);
            $SubscriptionDiscountPlanID = $data['SubscriptionDiscountPlanID'];
            if (!empty($SubscriptionDiscountPlanID) && !empty($AccountPeriod)) {
                log::info('SubscriptionDiscountPlanID ' . $SubscriptionDiscountPlanID);
                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                $AccountSubscriptionID = $data['AccountSubscriptionID'];
                $AccountName = empty($data['AccountName']) ? '' : $data['AccountName'];
                $AccountCLI = empty($data['AccountCLI']) ? '' : $data['AccountCLI'];
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $ServiceID,$AccountServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $ServiceID,$AccountServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                DB::commit();
                return Response::json(array("status" => "success", "message" => "Subscription Account Updated", 'LastID' => $SubscriptionDiscountPlanID));

            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Subscription Account."));
            }
        }catch( Exception $e){
            try {
                DB::rollback();
            } catch (\Exception $err) {
                Log::error($err);
            }
            Log::error($e);
            return Response::json(array("status" => "failed", "message" => "Problem Updating Subscription Account."));
        }
    }

    // not in use
    public function bulkupdate_discountplan()
    {
        $data = Input::all();
        $AllSubscriptionDiscountPlanID  = $data["AllSubscriptionDiscountPlanID"];
        if(!isset($data['InboundCheckbox']) && !isset($data['OutboundCheckbox']))
        {
            return Response::json(array("status" => "error", "message" => "Please select at least one field."));
            return false;
        }

        if(isset($data["InboundCheckbox"]))
        {
            if($data['BulkInboundDiscountPlans'] == '')
            {
                return Response::json(array("status" => "error", "message" => "Please select Value of Inbound Discount Plans"));
            }
            unset($data['InboundCheckbox']);
            $data['InboundDiscountPlans'] = $data['BulkInboundDiscountPlans'];
        }

        if(isset($data["OutboundCheckbox"]))
        {
            if($data['BulkOutboundDiscountPlans'] == '')
            {
                return Response::json(array("status" => "error", "message" => "Please select Value of Outbound Discount Plans"));
            }
            unset($data['OutboundCheckbox']);
            $data['OutboundDiscountPlans'] = $data['BulkOutboundDiscountPlans'];
        }

        unset($data['BulkInboundDiscountPlans']);
        unset($data['BulkOutboundDiscountPlans']);
        unset($data['AccountSubscriptionID_bulk']);
        unset($data['ServiceID']);
        unset($data['AllSubscriptionDiscountPlanID']);
        //SubscriptionDiscountPlan::whereIn('SubscriptionDiscountPlanID',$AllSubscriptionDiscountPlanID)->update($data);
        $AllSubscriptionDiscountPlanID = explode(",",$AllSubscriptionDiscountPlanID);
        if (SubscriptionDiscountPlan::whereIn('SubscriptionDiscountPlanID',$AllSubscriptionDiscountPlanID)->update($data)) {
            return Response::json(array("status" => "success", "message" => "Subscription Bulk Account Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Subscription Account."));
        }
    }

    // not in use
    public function bulkdelete_discountplan()
    {
        $data = Input::all();
        $SubscriptionDiscountPlanID  = $data["SubscriptionDiscountPlanID"];
        $SubscriptionDiscountPlanID = explode(",",$SubscriptionDiscountPlanID);
        if (SubscriptionDiscountPlan::whereIn('SubscriptionDiscountPlanID',$SubscriptionDiscountPlanID)->delete()) {
            return Response::json(array("status" => "success", "message" => "Subscription Bulk Accounts Deleted"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Subscription Accounts."));
        }
    }

    function get_discountplan($AccountID){
        $data = Input::all();
        $SubscriptionDiscountPlan =  SubscriptionDiscountPlan::getSubscriptionDiscountPlanArray($AccountID,$data['AccountSubscriptionID'],$data['ServiceID']);
        return $SubscriptionDiscountPlan;
    }

    public function delete_discountplan()
    {
        $data = Input::all();
        $SubscriptionDiscountPlanID = $data['SubscriptionDiscountPlanID'];
        if( intval($SubscriptionDiscountPlanID) > 0){
            $SubscriptionDiscountPlan = SubscriptionDiscountPlan::find($SubscriptionDiscountPlanID);
            $AccountID = $SubscriptionDiscountPlan->AccountID;
            $ServiceID = $SubscriptionDiscountPlan->ServiceID;
            $AccountServiceID = $SubscriptionDiscountPlan->AccountServiceID;
            $OutboundDiscountPlan = '';
            $InboundDiscountPlan = '';
            $AccountSubscriptionID = $SubscriptionDiscountPlan->AccountSubscriptionID;
            $AccountName = empty($SubscriptionDiscountPlan->AccountName) ? '' : $SubscriptionDiscountPlan->AccountName;
            $AccountCLI = empty($SubscriptionDiscountPlan->AccountCLI) ? '' : $SubscriptionDiscountPlan->AccountCLI;
            $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), 0);
            try{
                DB::beginTransaction();
                $result = $SubscriptionDiscountPlan->delete();
                if (!empty($result) && !empty($AccountPeriod)) {
                    $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                    $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                    $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                    AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $ServiceID,$AccountServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                    AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $ServiceID,$AccountServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Subscription Account Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Subscription Account."));
                }
            }catch (Exception $ex){
                try {
                    DB::rollback();
                } catch (\Exception $err) {
                    Log::error($err);
                }
                Log::error($ex);
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }

        }
    }
	
	function GetAccountServices($id){
	    $data = Input::all();
        $select = ["tblService.ServiceID","tblService.ServiceName"];
        $services = AccountService::join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')->where("tblAccountService.AccountID",$id);
        $services->where(function($query){ $query->where('tblAccountService.Status','=','1'); });
        $services->select($select);
		$ServicesDataDb =  $services->get();
		$servicesArray = array();
		
		//
		foreach($ServicesDataDb as $ServicesData){				
			$servicesArray[$ServicesData->ServiceName] =	$ServicesData->ServiceID; 						
		} 
		return $servicesArray;
	}
	
	function GetAccountSubscriptions($id){
		$account = Account::find($id);
		$subscriptions =  BillingSubscription::getSubscriptionsArray($account->CompanyId,$account->CurrencyId);

		return $subscriptions;
	}

    public function getDiscountPlanByAccount(){
        $data = Input::all();
        $AccountID = $data['AccountID'];
        $Response = DiscountPlan::getDropdownIDListByAccount($AccountID);
        return Response::json(array("status" => "success", "data" => $Response));
    }


    public function FindAccountServicesField()
    {

        try{

            $GetDynamiceAll = DynamicFields::select('FieldName' , 'FieldDomType', 'DynamicFieldsID')
                ->where('tblDynamicFields.Type','=', 'subscription')
                ->groupBy('tblDynamicFields.DynamicFieldsID')
                ->get();
            return $GetDynamiceAll;

        }catch (Exception $ex){
            return $ex;
        }
    }

    public function FindDynamicFields(){
       try{

           $GetDynamiceAll = DynamicFields::join('tblDynamicFieldsValue', function($join) {
                $join->on('tblDynamicFieldsValue.DynamicFieldsID','=','tblDynamicFields.DynamicFieldsID');
            })->select('tblDynamicFields.FieldName' , 'tblDynamicFields.FieldDomType', 'tblDynamicFieldsValue.FieldValue','tblDynamicFieldsValue.DynamicFieldsID')
                ->where('tblDynamicFields.Type','=', 'subscription')
                ->groupBy('tblDynamicFields.DynamicFieldsID')
                ->get();

           return $GetDynamiceAll;


        }catch (Exception $ex){
            return $ex;
        }

    }


    public function FindDynamicFieldsSubscription()
    {
        $data           = Input::all();
        $SubscriptionID         = $data['SubscriptionID'];
        $AccountSubscriptionID  = $data['AccountSubscriptionID'];

        $AccountSubscription = AccountSubscription::select('AccountID')
                                                        ->where('SubscriptionID',$SubscriptionID)
                                                        ->where('AccountID', $AccountSubscriptionID)
                                                        ->first();

            if( isset($AccountSubscription->AccountID) && !empty($AccountSubscription->AccountID))
            {
                $GetDynamiceAll = DynamicFields::join('tblDynamicFieldsValue', function($join) {
                    $join->on('tblDynamicFieldsValue.DynamicFieldsID','=','tblDynamicFields.DynamicFieldsID');
                })->select('tblDynamicFields.FieldName' , 'tblDynamicFields.FieldDomType', 'tblDynamicFieldsValue.FieldValue','tblDynamicFieldsValue.DynamicFieldsID')
                    ->where('tblDynamicFieldsValue.ParentID','=', $AccountSubscription->AccountID )
                    ->where('tblDynamicFields.Type','=', 'subscription')
                    ->groupBy('tblDynamicFields.DynamicFieldsID')
                    ->get();

            }else {
                $GetDynamiceAll = DynamicFields::join('tblDynamicFieldsValue', function ($join) {
                    $join->on('tblDynamicFieldsValue.DynamicFieldsID', '=', 'tblDynamicFields.DynamicFieldsID');
                })->select('tblDynamicFields.FieldName', 'tblDynamicFields.FieldDomType', 'tblDynamicFieldsValue.FieldValue', 'tblDynamicFieldsValue.DynamicFieldsID')
                    ->where('tblDynamicFields.Type', '=', 'subscription')
                    ->groupBy('tblDynamicFields.DynamicFieldsID')
                    ->get();
            }

        return $GetDynamiceAll;

    }


    public function FindEditDynamicFields(){
       $data = Input::all();
       $AccountSubscriptionID  = (string)$data['AccountSubscriptionID'];
       $AccountID              = (string)$data['AccountID'];

      try{
            $AccountSubsDynamicFields = AccountSubsDynamicFields::join('speakintelligentRM.tblDynamicFields as db2','tblAccountSubsDynamicFields.DynamicFieldsID','=','db2.DynamicFieldsID')
            ->select('tblAccountSubsDynamicFields.AccountSubscriptionID', 'tblAccountSubsDynamicFields.AccountID', 'tblAccountSubsDynamicFields.DynamicFieldsID', 'tblAccountSubsDynamicFields.FieldValue', 'db2.FieldName', 'db2.FieldDomType')
            ->where('tblAccountSubsDynamicFields.AccountSubscriptionID','=',$AccountSubscriptionID)
            ->where('tblAccountSubsDynamicFields.AccountID','=',$AccountID)
            ->where('db2.Type','=', 'subscription')
            ->groupBy('db2.DynamicFieldsID')
            ->orderBy('tblAccountSubsDynamicFields.FieldOrder','ASC')
            ->get();

//          echo '<pre>';
//            print_r($AccountSubsDynamicFields);
//          echo '</pre>';
//          exit;
          return $AccountSubsDynamicFields;

        }catch (Exception $ex){
            return $ex;
        }

    }
}