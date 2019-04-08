<?php

class BillingSubscriptionController extends \BaseController {

    var $model = 'BillingSubscription';

    public function ajax_datagrid($type) {
        $data = Input::all();                
        //$FdilterAdvance = $data['FilterAdvance']== 'true'?1:0;
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = array("Name", "AnnuallyFee", "QuarterlyFee", "MonthlyFee", "WeeklyFee", "DailyFee", "Advance", 
            "OneOffCurrencyID");

        $sort_column = $columns[$data['iSortCol_0']];
        if($data['FilterAdvance'] == ''){
            $data['FilterAdvance'] = 'null';
        }
        if($data['FilterAppliedTo'] == ''){
            $data['FilterAppliedTo'] = 'null';
        }
        

        $query = "call prc_getBillingSubscription (".$CompanyID.",".$data['FilterAdvance'].",'".$data['FilterName']."','".intval($data['FilterCurrencyID'])."',".intval($data['FilterOneOffCurrencyID']).",".$data['FilterAppliedTo'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $billexports = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Billing Subscription.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($billexports);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Billing Subscription.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($billexports);
            }
            /*Excel::create('Billing Subscription', function ($excel) use ($billexports) {
                $excel->sheet('Billing Subscription', function ($sheet) use ($billexports) {
                    $sheet->fromArray($billexports);
                });
            })->download('xls');*/
        }
        $query .=',0)';
        return DataTableSql::of($query,'sqlsrv2')->make();
    }

    public function index() {

        $currencies = 	Currency::getCurrencyDropdownIDList();
		$AdvanceSubscription = json_encode(BillingSubscription::$Advance);
        return View::make('billingsubscription.index', compact('currencies','AdvanceSubscription'));

    }

    public function create()
    {
        $data = Input::all();

        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        unset($data['SubscriptionID']);
        unset($data['SubscriptionClone']);
        $data['CreatedBy'] = User::get_user_full_name();
        $data["AppliedTo"] = empty($data['AppliedTo']) ? BillingSubscription::Customer : $data['AppliedTo'];

        if(isset($data['DynamicFields'])) {
            $j=0;
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


        $rules = array(
            'CompanyID' => 'required',
            'Name' => 'required|unique:tblBillingSubscription,Name,NULL,SubscriptionID,CompanyID,'.$data['CompanyID'].',AppliedTo,'.$data['AppliedTo'],
            'AnnuallyFee' => 'required|numeric',
            'QuarterlyFee' => 'required|numeric',
            'MonthlyFee' => 'required|numeric',
            'WeeklyFee' => 'required|numeric',
            'DailyFee' => 'required|numeric',
            'InvoiceLineDescription' => 'required',
            'ActivationFee' => 'required|numeric',
            'RecurringCurrencyID' => 'required',
            'OneOffCurrencyID' => 'required'
        );
        $messages = array(
            'OneOffCurrencyID.required' => "Activation Fee Currency is Required", 
            'RecurringCurrencyID.required' => "Recurring Fee Currency is Required",
            'ActivationFee.required' => 'Activation Fee Required'
        );
        $data['Advance'] = isset($data['Advance']) ? 1 : 0;
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $validator = Validator::make($data, $rules, $messages);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $Attachment = !empty($data['Image']) ? 1 : 0;
        unset($data['Image']);

        if (Input::hasFile('Image') && $Attachment==1){
            $upload_path = CompanyConfiguration::get('UPLOAD_PATH');
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['PRODUCT_ATTACHMENTS'],'',$data['CompanyID']) ;
            $destinationPath = $upload_path . '/' . $amazonPath;
            $proof = Input::file('Image');

            $ext = $proof->getClientOriginalExtension();
            if (in_array(strtolower($ext), array('jpeg','png','jpg','gif'))) {

                $filename = rename_upload_file($destinationPath,$proof->getClientOriginalName());

                $proof->move($destinationPath,$filename);
                if(!AmazonS3::upload($destinationPath.$filename,$amazonPath,$data['CompanyID'])){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $data['Image'] = $amazonPath . $filename;
            }else{
                return Response::json(array("status" => "failed", "message" => "Please Upload file with given extensions."));
            }
        }else{
            unset($data['Image']);
        }
       
        if ($BillingSubscription = BillingSubscription::create($data)) {

            if(isset($DynamicFields) && count($DynamicFields)>0) {
                for($k=0; $k<count($DynamicFields); $k++) {
                    if(trim($DynamicFields[$k]['FieldValue'])!='') {
                        $DynamicFields[$k]['ParentID'] = $BillingSubscription->SubscriptionID;
                        DB::table('tblDynamicFieldsValue')->insert($DynamicFields[$k]);
                    }
                }
            }

            return Response::json(array("status" => "success", "message" => "Subscription Successfully Created",'LastID'=>$BillingSubscription->SubscriptionID, 'newcreated'=>$BillingSubscription));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Subscription."));
        }
    }


    public function update($id)
    {

        if($id >0 ) {
            $BillingSubscription = BillingSubscription::find($id);
            $data = Input::all();
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            unset($data['SubscriptionClone']);
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
                        ->where('ParentID',$data['SubscriptionID'])
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
                                ->where('ParentID', $data['SubscriptionID'])
                                ->where('DynamicFieldsID', $key)
                                ->update(['FieldValue' => $value, 'updated_at' => date('Y-m-d H:i:s.000'), 'updated_by' => $user]);
                        }
                    } else {
                        if(trim($value)!='') {
                            $DynamicFields['CompanyID'] = $companyID;
                            $DynamicFields['ParentID'] = $data['SubscriptionID'];
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

            $rules = array(
                'CompanyID' => 'required',
                'Name' => 'required|unique:tblBillingSubscription,Name,'.$id.',SubscriptionID,CompanyID,'.$data['CompanyID'].',AppliedTo,'.$data['AppliedTo'],
                'AnnuallyFee' => 'required|numeric',
                'QuarterlyFee' => 'required|numeric',
                'MonthlyFee' => 'required|numeric',
                'WeeklyFee' => 'required|numeric',
                'DailyFee' => 'required|numeric',
               // 'CurrencyID' => 'required',
                'InvoiceLineDescription' => 'required',
                'ActivationFee' => 'required|numeric',
                'RecurringCurrencyID' => 'required',
                'OneOffCurrencyID' => 'required'

            );
            $messages = array(
            'OneOffCurrencyID.required' => "Activation Fee Currency is Required", 
            'RecurringCurrencyID.required' => "Recurring Fee Currency is Required",
            'ActivationFee.required' => 'Activation Fee Required'
            );
            $data['Advance'] = isset($data['Advance']) ? 1 : 0;
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($data, $rules, $messages);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if(isset($data['hDynamicFields'])){
                unset($data['hDynamicFields']);
            }


            //Subscription Upload Start
            $Attachment = !empty($data['Image']) ? 1 : 0;
            unset($data['Image']);

            if (Input::hasFile('Image') && $Attachment==1){
                $upload_path = CompanyConfiguration::get('UPLOAD_PATH');
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['PRODUCT_ATTACHMENTS'],'',$data['CompanyID']) ;
                $destinationPath = $upload_path . '/' . $amazonPath;
                $proof = Input::file('Image');

                $ext = $proof->getClientOriginalExtension();
                if (in_array(strtolower($ext), array('jpeg','png','jpg','gif'))) {

                    $filename = rename_upload_file($destinationPath,$proof->getClientOriginalName());

                    $proof->move($destinationPath,$filename);
                    if(!AmazonS3::upload($destinationPath.$filename,$amazonPath,$data['CompanyID'])){
                        return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                    }
                    $data['Image'] = $amazonPath . $filename;

                }else{
                    return Response::json(array("status" => "failed", "message" => "Please Upload file with given extensions."));
                }
            }else{
                unset($data['Image']);
            }

            if ($BillingSubscription->update($data)) {
                return Response::json(array("status" => "success", "message" => "Subscription Successfully Updated",'LastID'=>$id));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Subscription."));
            }
        }
    }

    public function delete($id)
    {
        if( intval($id) > 0){

            if(!BillingSubscription::checkForeignKeyById($id)){
                try{
                    Log::info("delete DynamicFieldValue ProductID1=".$id);
                    $BillingSubscription = BillingSubscription::find($id);
                    AmazonS3::delete($BillingSubscription->CompanyLogoAS3Key);
                    if(!empty($BillingSubscription->Image)){
                        AmazonS3::delete($BillingSubscription->Image);
                    }
                    $result = $BillingSubscription->delete();
                    Log::info("delete DynamicFieldValue ProductID=".$id);
                    if ($result) {
                        $Type =  Subscription::DYNAMIC_TYPE;
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
	
	function getSubscriptionData_ajax($id){		
       $BillingSubscription = BillingSubscription::find($id);
	   Log::info($BillingSubscription);
		if(empty($BillingSubscription)){
			return Response::json(array("status" => "failed", "message" => "Subscription Not found." ));
		}else{
			return Response::json($BillingSubscription);
		}
	
	}


    function viewSubscriptionDynamicFields(){

        return  View::make('billingsubscription.subscriptiontype.index');
    }

    public function ajax_GetSubscriptions($type)
    {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = ['DynamicFieldsID','FieldName','FieldDomType','created_at','Status','Active'];
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getDynamicFields (".$CompanyID.", '".$data['FieldName']."','".$data['FieldDomType']."','".$data['Active']."','subscription',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";


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
        $data ["Type"] = Subscription::DYNAMIC_TYPE;
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
        $cnt_duplidate = DynamicFields::where('FieldName',$data['FieldName'])->where('Type', 'subscription')->get()->count();
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
                        return Response::json(array("status" => "success", "message" => "Subscription Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Subscription."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Subscription is in Use, You cant delete this Item Type."));
                }

            }else{
                return Response::json(array("status" => "failed", "message" => "Subscription is in Use, You cant delete this Item Type."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Subscription is in Use, You cant delete this Item Type."));
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
            $cnt_duplidate = DynamicFields::where('FieldName',$data['FieldName'])->where('DynamicFieldsID','!=',$dynamicfield->DynamicFieldsID)->get()->count();
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
            $userID = User::get_userID();

            if (!isset($data['Active']) || $data['Active'] == '') {
                $data['Active'] = 9;
            } else {
                $data['Active'] = (int)$data['Active'];
            }

            $query = "call prc_UpdateDynamicFieldStatus (" . $CompanyID . ",'" . $UserName . "','product','" . $data['FieldName'] . "','" . $data['FieldDomType'] . "','" . $data['ItemTypeID'] . "'," . $data['Active'] . "," . $data['status_set'] . ")";
            $result = DB::connection('sqlsrv')->select($query);
            return Response::json(array("status" => "success", "message" => "Dynamic Field Status Updated"));
        }

        if ($data['criteria_ac'] == 'selected') { //selceted ids from current page
            if (isset($data['SelectedIDs']) && count($data['SelectedIDs']) > 0) {
//                foreach($data['SelectedIDs'] as $SelectedID){
                DynamicFields::whereIn('DynamicFieldsID', $data['SelectedIDs'])->where('Status', '!=', $data['status_set'])->update(["Status" => intval($data['status_set'])]);
//                    Product::find($SelectedID)->where('Active','!=',$data['status_set'])->update(["Active"=>intval($data['status_set']),'ModifiedBy'=>$UserName,'updated_at'=>date('Y-m-d H:i:s')]);
//                }
                return Response::json(array("status" => "success", "message" => "Dynamic Field Status Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "No Dynamic Field selected"));
            }

        }

      }


    public function getSubscritionsType($data){

            $Type =  Subscription::DYNAMIC_TYPE;
            $CompanyID = User::get_companyID();
            $DynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->where('Status','1')->orderByRaw('case FieldOrder when 0 then 2 else 1 end, FieldOrder')->get();

            $DynamicFields['totalfields'] = count($DynamicFields['fields']);

            if(count($DynamicFields) > 0 ){
                return View::make('billingsubscription.ajax_dynamicFields',compact('DynamicFields','data'));
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
    public function getSubscritionsField(){
        $Type =  Subscription::DYNAMIC_TYPE;
        $CompanyID = User::get_companyID();
        $DynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->where('Status','1')->orderByRaw('case FieldOrder when 0 then 2 else 1 end, FieldOrder')->get();

        $DynamicFields['totalfields'] = count($DynamicFields['fields']);

        if(count($DynamicFields) > 0 ){
            return View::make('billingsubscription.ajax_dynamicFields',compact('DynamicFields','data'));
        }
      }
    }