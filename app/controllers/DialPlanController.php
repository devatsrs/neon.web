<?php

class DialPlanController extends \BaseController {

    /**
     * Display a listing of DialpLan
     *
     * @return Response
     */
    public function index() {					
		return View::make('dialplan.index');
    }

	public function dialplan_datagrid(){
        $CompanyID = User::get_companyID();
        $dialplans = DialPlan::where(["CompanyId" => $CompanyID])->select(["Name","created_at","CreatedBy","DialPlanID"]);
        return Datatables::of($dialplans)->make();
    }


    // dial plan export
    public function exports($type) {

        $CompanyID = User::get_companyID();
        $dialplans = DialPlan::where(["CompanyId" => $CompanyID])->select(["Name","created_at","CreatedBy"])->get()->toArray();

        if($type=='csv'){
            $file_path = getenv('UPLOAD_PATH') .'/Dial Plan.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($dialplans);
        }elseif($type=='xlsx'){
            $file_path = getenv('UPLOAD_PATH') .'/Dial Plan.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($dialplans);
        }

    }

    public function create_dialplan(){
        $data = Input::all();
        $data['CompanyID'] = User::get_companyID();

        $rules = array(
            'Name' => 'required|unique:tblDialPlan,Name,NULL,CompanyID,CompanyID,'.$data['CompanyID'],
            'CompanyID' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['CreatedBy'] = User::get_user_full_name();

        if ($dialplan = DialPlan::create($data)) {
            return Response::json(array("status" => "success", "message" => "Dial Plan Successfully Created",'LastID'=>$dialplan->DialPlanID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Dial Plan."));
        }

    }

    public function update_dialplan($id){
        $data = Input::all();
        $dialplan = DialPlan::find($id);
        $data['CompanyID'] = User::get_companyID();

        $rules = array(
            'Name' => 'required|unique:tblDialPlan,Name,'.$id.',DialPlanID,CompanyID,'.$data['CompanyID'],
            'CompanyID' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['ModifiedBy'] = User::get_user_full_name();

        if ($dialplan->update($data)) {
            return Response::json(array("status" => "success", "message" => "Dial Plan Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Dial Plan."));
        }

    }

    public function delete_dialplan($id){
        if( intval($id) > 0){
            try{
                $result = DialPlan::find($id)->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Dial Plan Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Dial Plan."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Dial Plan is in Use, You cant delete this Dial Plan."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Please Select dial Plan."));
        }
    }

    //dial string view
    public function dialplancode($id){
        $DialPlanName = DialPlan::getDialPlanName($id);
        return View::make('dialplan.dialplancode', compact('id','DialPlanName'));

    }

    //get datagrid of dial string
    public function ajax_datagrid($type) {

        $companyID = User::get_companyID();
        $data = Input::all();

        $data['ft_dialstring'] = $data['ft_dialstring'] != ''?"'".$data['ft_dialstring']."'":'null';
        $data['ft_chargecode'] = $data['ft_chargecode'] != ''?"'".$data['ft_chargecode']."'":'null';
        $data['ft_description'] = $data['ft_description'] != ''?"'".$data['ft_description']."'":'null';

        $data['iDisplayStart'] +=1;
        $columns = array('DialPlanCodeID','DialString','ChargeCode','Description','Forbidden');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_GetDialStrings (".$data['ft_dialplanid'].",".$data['ft_dialstring'].",".$data['ft_chargecode'].",".$data['ft_description'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/DialPlanCode.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/DialPlanCode.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query)->make();
    }

    /**
     * Store a newly created Dial String in storage.
     *
     * @return Response
     */
    public function store() {
        $data = Input::all();
        $rules = DialPlanCode::$rules;

        $rules['DialString'] = 'required|unique:tblDialPlanCode,DialString,NULL,DialPlanID,DialPlanID,'.$data['DialPlanID'];
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        // forbidden - 0-unblock,1-block
        if($data['Forbidden']=='0'){
            $data['Forbidden'] = '0';
        }elseif($data['Forbidden']=='1'){
            $data['Forbidden'] = '1';
        }else{
            $data['Forbidden'] = '';
        }

        $data['created_by'] = User::get_user_full_name();

        if ($DialPlanCode = DialPlanCode::create($data)) {
            return Response::json(array("status" => "success", "message" => "Dial String Successfully Created",'LastID'=>$DialPlanCode->tblDialPlanCode));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Dial String."));
        }
    }


    /**
     * Update the specified Dial String in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        $data = Input::all();
        $DialPlanCode = DialPlanCode::find($id);

        $rules = DialPlanCode::$rules;

        $rules['DialString'] = 'required|unique:tblDialPlanCode,DialString,'.$id.',DialPlanCodeID,DialPlanID,'.$data['DialPlanID'];


        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        // forbidden - 0-unblock,1-block
        if($data['Forbidden']=='0'){
            $data['Forbidden'] = '0';
        }elseif($data['Forbidden']=='1'){
            $data['Forbidden'] = '1';
        }else{
            $data['Forbidden'] = '';
        }

        if ($DialPlanCode->update($data)) {
            return Response::json(array("status" => "success", "message" => "Dial Strings Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Dial Strings."));
        }
    }


    //delete single Dial String
    public function deletecode($id){
        if( intval($id) > 0){
            try{
                $result = DialPlanCode::find($id)->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Dial String Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Dial String."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Dial String is in Use, You cant delete this Dial Plan Code."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Please Select Dial String."));
        }
    }

    //update bulk dial string
    public  function update_selected(){
        $data = Input::all();
        $error = array();
        $rules = array();
        $updateChageCode = 0;
        $updateDescription = 0;
        $updateForbidden = 0;
        $Dialcodes = '';

        // check which fileds need to update
        if(!empty($data['updateChageCode']) || !empty($data['updateDescription']) || !empty($data['updateForbidden'])){

            if(!empty($data['updateChageCode'])){
                $updateChageCode = 1;
                if(empty($data['ChargeCode'])){
                    $rules['ChargeCode'] = 'required';
                }
            }

            if(!empty($data['updateDescription'])){
                $updateDescription = 1;
                if(empty($data['Description'])){
                    $rules['Description'] = 'required';
                }
            }

            if(!empty($data['updateForbidden'])){
                $updateForbidden = 1;
                if($data['Forbidden']=='0'){
                    $data['Forbidden'] = '0';
                }elseif($data['Forbidden']=='1'){
                    $data['Forbidden'] = '1';
                }else{
                    $data['Forbidden'] = '';
                }
            }

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }

        }else{
            return Response::json(array("status" => "failed", "message" => "No Dial String selected to Update."));
        }

        if(!empty($data['Action']) && $data['Action'] == 'criteria'){
            //update from critearia
            $criteria = json_decode($data['criteria'],true);
            $criteria['ft_dialstring'] = $criteria['ft_dialstring'] != ''?"'".$criteria['ft_dialstring']."'":'null';
            $criteria['ft_chargecode'] = $criteria['ft_chargecode'] != ''?"'".$criteria['ft_chargecode']."'":'null';
            $criteria['ft_description'] = $criteria['ft_description'] != ''?"'".$criteria['ft_description']."'":'null';

            $query = "call prc_dialplancodekbulkupdate ('".$data['DialPlanID']."','".$updateChageCode."','".$updateDescription."','".$updateForbidden."','1','',".$criteria['ft_dialstring'].",".$criteria['ft_chargecode'].",".$criteria['ft_description'].",'".$data['ChargeCode']."','".$data['Description']."','".$data['Forbidden']."','0')";

            $result = DB::statement($query);
            if ($result) {
                return Response::json(array("status" => "success", "message" => "Dial Strings Updated Successfully."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Dial Strings."));
            }

        }elseif(!empty($data['Action']) && $data['Action'] == 'code'){
            //update from selected dialstrings

            $Dialcodes = $data['Dialcodes'];
            $query = "call prc_dialplancodekbulkupdate ('".$data['DialPlanID']."','".$updateChageCode."','".$updateDescription."','".$updateForbidden."','0','".$Dialcodes."',null,null,null,'".$data['ChargeCode']."','".$data['Description']."','".$data['Forbidden']."','0')";

            $result = DB::statement($query);
            if ($result) {
                return Response::json(array("status" => "success", "message" => "Dial Strings Updated Successfully."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Dial Strings."));
            }

        }else{
            return Response::json(array("status" => "failed", "message" => "No Dial String selected to Update."));
        }

    }

    // bulk dial string delete
    public  function delete_selected(){
        $data = Input::all();
        $updateChageCode = 0;
        $updateDescription = 0;
        $updateForbidden = 0;
        $Dialcodes = '';


        if(!empty($data['Action']) && $data['Action'] == 'criteria'){

            $criteria = json_decode($data['criteria'],true);
            $criteria['ft_dialstring'] = $criteria['ft_dialstring'] != ''?"'".$criteria['ft_dialstring']."'":'null';
            $criteria['ft_chargecode'] = $criteria['ft_chargecode'] != ''?"'".$criteria['ft_chargecode']."'":'null';
            $criteria['ft_description'] = $criteria['ft_description'] != ''?"'".$criteria['ft_description']."'":'null';

            $query = "call prc_dialplancodekbulkupdate ('".$data['DialPlanID']."','".$updateChageCode."','".$updateDescription."','".$updateForbidden."','1','',".$criteria['ft_dialstring'].",".$criteria['ft_chargecode'].",".$criteria['ft_description'].",'','','','1')";

            $result = DB::statement($query);
            if ($result) {
                return Response::json(array("status" => "success", "message" => "Dial Strings Deleted Successfully."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem deleting Dial Strings."));
            }

        }elseif(!empty($data['Action']) && $data['Action'] == 'code'){
            $Dialcodes = $data['Dialcodes'];
            $query = "call prc_dialplancodekbulkupdate ('".$data['DialPlanID']."','".$updateChageCode."','".$updateDescription."','".$updateForbidden."','0','".$Dialcodes."',null,null,null,'','','','1')";

            $result = DB::statement($query);
            if ($result) {
                return Response::json(array("status" => "success", "message" => "Dial Strings Updated Successfully."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Dial Strings."));
            }

        }else{
            return Response::json(array("status" => "failed", "message" => "No Dial String selected to Update."));
        }

    }

    // dial string upload view
    public function upload($id) {
        $DialPlanName = DialPlan::getDialPlanName($id);
        $uploadtemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_DIALPLAN);
        return View::make('dialplan.upload', compact('id','DialPlanName','uploadtemplate'));
    }

    public function check_upload($id) {
        try {
            ini_set('max_execution_time', 0);
            $data = Input::all();
            if (empty($id)) {
                return json_encode(["status" => "failed", "message" => 'No Dial Plan Available']);
            } else if (Input::hasFile('excel')) {
                $upload_path = getenv('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array($ext, array("csv", "xls", "xlsx"))) {
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
                    $DialPlanFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($DialPlanFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($DialPlanFileUploadTemplate)) {
                    $grid['DialPlanFileUploadTemplate'] = json_decode(json_encode($DialPlanFileUploadTemplate), true);
                    $grid['DialPlanFileUploadTemplate']['Options'] = json_decode($DialPlanFileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        }catch(Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    function ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TempFileName'];
            $grid = getFileContent($file_name, $data);
            $grid['filename'] = $data['TemplateFile'];
            $grid['tempfilename'] = $data['TempFileName'];
            if ($data['uploadtemplate'] > 0) {
                $DialPlanFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                $grid['DialPlanFileUploadTemplate'] = json_decode(json_encode($DialPlanFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['DialPlanFileUploadTemplate']['Options'] = array();
            $grid['DialPlanFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['DialPlanFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }


    public function storeTemplate($id) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        DialPlanCode::$DialPlanUploadrules['selection.DialString'] = 'required';
        DialPlanCode::$DialPlanUploadrules['selection.ChargeCode'] = 'required';
        DialPlanCode::$DialPlanUploadrules['selection.Description'] = 'required';

        $validator = Validator::make($data, DialPlanCode::$DialPlanUploadrules,DialPlanCode::$DialPlanUploadMessages);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = getenv('TEMP_PATH') . '/';

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['DIALPLAN_UPLOAD']);
        $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload Dial Plan file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            $save['Type'] = FileUploadTemplate::TEMPLATE_DIALPLAN;
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
        $option["selection"] = $data['selection'];
        $save['Options'] = json_encode($option);
        $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path'] = $fullPath;
        $save["DialPlanID"] = $id;
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        $save['dialplanname'] = DialPlan::getDialPlanName($id);

        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("DPU", $save);
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

    // download sample file of dial string upload
    public function download_sample_excel_file(){
            $filePath = public_path() .'/uploads/sample_upload/DialStringUploadSample.csv';
            download_file($filePath);

    }

}