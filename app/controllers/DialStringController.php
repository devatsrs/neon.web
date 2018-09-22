<?php

class DialStringController extends \BaseController {

    /**
     * Display a listing of DialString
     *
     * @return Response
     */
    public function index() {					
		return View::make('dialstring.index');
    }

	public function dialstring_datagrid(){
        $CompanyID = User::get_companyID();
        $dialstrings = DialString::where(["CompanyID" => $CompanyID])->select(["Name","created_at","CreatedBy","DialStringID"]);
        return Datatables::of($dialstrings)->make();
    }


    // dial String export
    public function exports($type) {

        $CompanyID = User::get_companyID();
        $dialstring = DialString::where(["CompanyID" => $CompanyID])->select(["Name","created_at","CreatedBy"])->get()->toArray();

        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Dial String.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($dialstring);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Dial String.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($dialstring);
        }

    }

    public function create_dialstring(){
        $data = Input::all();
        $data['CompanyID'] = User::get_companyID();

        $rules = array(
            'Name' => 'required|unique:tblDialString,Name,NULL,CompanyID,CompanyID,'.$data['CompanyID'],
            'CompanyID' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['CreatedBy'] = User::get_user_full_name();

        if ($dialstring = DialString::create($data)) {
            return Response::json(array("status" => "success", "message" => "Dial String Successfully Created",'LastID'=>$dialstring->DialStringID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Dial String."));
        }

    }

    public function update_dialstring($id){
        $data = Input::all();
        $dialstring = DialString::find($id);
        $data['CompanyID'] = User::get_companyID();

        $rules = array(
            'Name' => 'required|unique:tblDialString,Name,'.$id.',DialStringID,CompanyID,'.$data['CompanyID'],
            'CompanyID' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['ModifiedBy'] = User::get_user_full_name();

        if ($dialstring->update($data)) {
            return Response::json(array("status" => "success", "message" => "Dial String Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Dial String."));
        }

    }

    public function delete_dialstring($id){
        if( intval($id) > 0){
            try{
                if(DialStringCode::where(["DialStringID"=>$id])->count()>0){
                    if (DialStringCode::where(["DialStringID" => $id])->delete() && DialString::where(["DialStringID" => $id])->delete()) {
                        return Response::json(array("status" => "success", "message" => "Dial String Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Dial String."));
                    }
                }else{
                    if (DialString::where(["DialStringID" => $id])->delete()) {
                        return Response::json(array("status" => "success", "message" => "Dial String Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Dial String."));
                    }
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Dial String is in Use, You cant delete this Dial String."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Please Select Dial String."));
        }
    }

    //dial string detail view
    public function dialstringcode($id){
        $DialStringName = DialString::getDialStringName($id);
        return View::make('dialstring.dialstringcode', compact('id','DialStringName'));

    }

    //get datagrid of dial strings
    public function ajax_datagrid($type) {

        $companyID = User::get_companyID();
        $data = Input::all();

        $data['ft_dialstring'] = $data['ft_dialstring'] != ''?"'".$data['ft_dialstring']."'":'null';
        $data['ft_chargecode'] = $data['ft_chargecode'] != ''?"'".$data['ft_chargecode']."'":'null';
        $data['ft_description'] = $data['ft_description'] != ''?"'".$data['ft_description']."'":'null';
        $data['ft_forbidden'] = $data['ft_forbidden']== 'true'?1:0;

        $data['iDisplayStart'] +=1;
        $columns = array('DialStringCodeID','DialString','ChargeCode','Description','Forbidden');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_GetDialStrings (".$data['ft_dialstringid'].",".$data['ft_dialstring'].",".$data['ft_chargecode'].",".$data['ft_description'].",".$data['ft_forbidden'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DialStrings.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DialStrings.xls';
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

        DialStringCode::$DialStringStorerules['DialString'] = 'required|unique:tblDialStringCode,DialString,NULL,DialStringID,DialStringID,'.$data['DialStringID'];
        DialStringCode::$DialStringStorerules['ChargeCode'] = 'required';
        $validator = Validator::make($data, DialStringCode::$DialStringStorerules,DialStringCode::$DialStringStoreMessages);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $data['Forbidden'] = isset($data['Forbidden']) ? 1 : 0;
        $data['created_by'] = User::get_user_full_name();

        if ($DialStringCode = DialStringCode::create($data)) {
            return Response::json(array("status" => "success", "message" => "Dial String Successfully Created",'LastID'=>$DialStringCode->DialStringCodeID));
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
        $DialStringCode = DialStringCode::find($id);

        DialStringCode::$DialStringStorerules['DialString'] = 'required|unique:tblDialStringCode,DialString,'.$id.',DialStringCodeID,DialStringID,'.$data['DialStringID'];
        DialStringCode::$DialStringStorerules['ChargeCode'] = 'required';
        $validator = Validator::make($data, DialStringCode::$DialStringStorerules,DialStringCode::$DialStringStoreMessages);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }


        $data['Forbidden'] = isset($data['Forbidden']) ? 1 : 0;
        $data['updated_by'] = User::get_user_full_name();

        if ($DialStringCode->update($data)) {
            return Response::json(array("status" => "success", "message" => "Dial Strings Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Dial Strings."));
        }
    }


    //delete single Dial String
    public function deletecode($id){
        if( intval($id) > 0){
            try{
                $result = DialStringCode::find($id)->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Dial String Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Dial String."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Dial String is in Use, You cant delete this Dial String Code."));
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
        $data['Forbidden'] = isset($data['Forbidden']) ? 1 : 0;

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
            }

            if(!empty($data['updateForbidden'])){
                $updateForbidden = 1;
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
            $criteria['ft_forbidden'] = $criteria['ft_forbidden']== 'true'?1:0;

            $query = "call prc_DialStringCodekBulkUpdate ('".$data['DialStringID']."','".$updateChageCode."','".$updateDescription."','".$updateForbidden."','1','',".$criteria['ft_dialstring'].",".$criteria['ft_chargecode'].",".$criteria['ft_description'].",".$criteria['ft_forbidden'].",'".$data['ChargeCode']."','".$data['Description']."','".$data['Forbidden']."','0')";

            $result = DB::statement($query);
            if ($result) {
                return Response::json(array("status" => "success", "message" => "Dial Strings Updated Successfully."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Dial Strings."));
            }

        }elseif(!empty($data['Action']) && $data['Action'] == 'code'){
            //update from selected dialstrings

            $Dialcodes = $data['Dialcodes'];
            $query = "call prc_DialStringCodekBulkUpdate ('".$data['DialStringID']."','".$updateChageCode."','".$updateDescription."','".$updateForbidden."','0','".$Dialcodes."',null,null,null,0,'".$data['ChargeCode']."','".$data['Description']."','".$data['Forbidden']."','0')";

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
        $data['Forbidden'] = isset($data['Forbidden']) ? 1 : 0;


        if(!empty($data['Action']) && $data['Action'] == 'criteria'){

            $criteria = json_decode($data['criteria'],true);
            $criteria['ft_dialstring'] = $criteria['ft_dialstring'] != ''?"'".$criteria['ft_dialstring']."'":'null';
            $criteria['ft_chargecode'] = $criteria['ft_chargecode'] != ''?"'".$criteria['ft_chargecode']."'":'null';
            $criteria['ft_description'] = $criteria['ft_description'] != ''?"'".$criteria['ft_description']."'":'null';
            $criteria['ft_forbidden'] = $criteria['ft_forbidden']== 'true'?1:0;

            $query = "call prc_DialStringCodekBulkUpdate ('".$data['DialStringID']."','".$updateChageCode."','".$updateDescription."','".$updateForbidden."','1','',".$criteria['ft_dialstring'].",".$criteria['ft_chargecode'].",".$criteria['ft_description'].",'".$criteria['ft_forbidden']."','','','','1')";

            $result = DB::statement($query);
            if ($result) {
                return Response::json(array("status" => "success", "message" => "Dial Strings Deleted Successfully."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem deleting Dial Strings."));
            }

        }elseif(!empty($data['Action']) && $data['Action'] == 'code'){
            $Dialcodes = $data['Dialcodes'];
            $query = "call prc_DialStringCodekBulkUpdate ('".$data['DialStringID']."','".$updateChageCode."','".$updateDescription."','".$updateForbidden."','0','".$Dialcodes."',null,null,null,0,'','','','1')";

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
        $DialStringName = DialString::getDialStringName($id);
        $uploadtemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_DIALSTRING));
        return View::make('dialstring.upload', compact('id','DialStringName','uploadtemplate'));
    }

    public function check_upload($id) {
        try {
            ini_set('max_execution_time', 0);
            $data = Input::all();
            if (empty($id)) {
                return json_encode(["status" => "failed", "message" => 'No Dial String Available']);
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
                    $DialStringFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($DialStringFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($DialStringFileUploadTemplate)) {
                    $grid['DialStringFileUploadTemplate'] = json_decode(json_encode($DialStringFileUploadTemplate), true);
                    $grid['DialStringFileUploadTemplate']['Options'] = json_decode($DialStringFileUploadTemplate->Options, true);
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
                $DialStringFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                $grid['DialStringFileUploadTemplate'] = json_decode(json_encode($DialStringFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['DialStringFileUploadTemplate']['Options'] = array();
            $grid['DialStringFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['DialStringFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }


    public function storeTemplate($id) {
        $data = json_decode(str_replace('Skip loading','',json_encode(Input::all(),true)),true);//Input::all();
        $CompanyID = User::get_companyID();
        DialStringCode::$DialStringUploadrules['selection.DialString'] = 'required';
        DialStringCode::$DialStringUploadrules['selection.ChargeCode'] = 'required';

        $validator = Validator::make($data, DialStringCode::$DialStringUploadrules,DialStringCode::$DialStringUploadMessages);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = CompanyConfiguration::get('TEMP_PATH') . '/';

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['DIALSTRING_UPLOAD']);
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload Dial String file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = filterArrayRemoveNewLines($data['selection']);//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = str_replace('Skip loading','',json_encode($option));//json_encode($option);
            $save['FileUploadTemplateTypeID'] = FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_DIALSTRING);
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
        $save['Options'] = str_replace('Skip loading','',json_encode($option));//json_encode($option);
        $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path'] = $fullPath;
        $save["DialStringID"] = $id;
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        $save['dialstringname'] = DialString::getDialStringName($id);

        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("DSU", $save);
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