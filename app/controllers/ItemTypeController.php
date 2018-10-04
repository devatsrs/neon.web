<?php

class ItemTypeController extends \BaseController {

    var $model = 'ItemType';
	/**
	 * Display a listing of the resource.
	 * GET /products
	 *
	 * @return Response

	  */

    public function ajax_datagrid($type) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = ['ItemTypeID','title','updated_at','Active'];
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getItemTypes (".$CompanyID.", '".$data['title']."','".$data['Active']."', ".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Item.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Item.xls';
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
        $data = DataTableSql::of($query,'sqlsrv2')->make(false);

        return Response::json($data);
//        return DataTableSql::of($query,'sqlsrv2')->make();
    }


    public function index()
    {
        $id=0;
        $Type =  Product::DYNAMIC_TYPE;
        $companyID = User::get_companyID();
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $DynamicFields = $this->getDynamicFields($companyID,$Type);
        return View::make('products.itemtypes.index', compact('id','gateway','DynamicFields'));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /products/create
	 *
	 * @return Response
	 */
    public function create(){

        $data = Input::all();
        $companyID = User::get_companyID();
        $roundplaces = $RoundChargesAmount = get_round_decimal_places();
        $data ["CompanyID"] = $companyID;
        $data['Active'] = isset($data['Active']) ? 1 : 0;
        $data["created_by"] = User::get_user_full_name();

        unset($data['ItemTypeID']);
        unset($data['ProductClone']);

        $rules = array(
            'CompanyID' => 'required',
            'Title' => 'required',
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $checkduplicate=ItemType::where('title',$data['Title'])->get()->count();
        if($checkduplicate > 0){
            return Response::json(array("status" => "failed", "message" => "Item Type Already Exists."));
        }

        if ($itemtype = ItemType::create($data)) {
            return Response::json(array("status" => "success", "message" => "Item Type Successfully Created",'newcreated'=>$itemtype));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Item Type."));
        }
    }


	/**
	 * Update the specified resource in storage.
	 * PUT /products/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function update($id)
    {
        if( $id > 0 ) {
            $data = Input::all();
            $itemtype = ItemType::findOrFail($id);
            $user = User::get_user_full_name();

            $companyID = User::get_companyID();
            $data["CompanyID"] = $companyID;
            $data['Active'] = isset($data['Active']) ? 1 : 0;
            $data["updated_by"] = $user;
            unset($data['ProductClone']);

            $rules = array(
                'CompanyID' => 'required',
                'Title' => 'required',
            );
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if ($itemtype->update($data)) {
                return Response::json(array("status" => "success", "message" => "Item Type Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Item Type."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Item Type."));
        }
    }

	/**
	 * Remove the specified resource from storage.
	 * DELETE /products/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function delete($id) {
        if( intval($id) > 0){
            if(!ItemType::checkForeignKeyById($id)) {
                try {
                    //delete its DynamicFields
                    $DynamicField=DynamicFields::where('ItemTypeID',$id)->delete();

                    $result = ItemType::find($id)->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Item Type Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Item Type."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Item Type is in Use, You cant delete this Item Type."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Item Type is in Use, You cant delete this Item Type."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Item Type is in Use, You cant delete this Item Type."));
        }
    }

    /**
     * Get product Field Value
     */
    /*public function get($id,$field){
        if($id>0 && !empty($field)){
            return json_encode(Product::where(["ProductID"=>$id])->pluck($field));
        }
        return json_encode('');
    }*/

    /**
     * Show the form for uploading items.
     * GET /products/upload
     *
     * @return View
     */
    public function upload(){
        $Type =  Product::DYNAMIC_TYPE;
        $CompanyID = User::get_companyID();
        $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_ITEM));
        $DynamicFields = $this->getDynamicFields($CompanyID,$Type);
        return View::make('products.upload',compact('UploadTemplate','DynamicFields'));
    }

    /**
     * @return mixed
     */
    public function check_upload()
    {
        try {
            $data = Input::all();
            $rules = array(
//                'Authentication' => 'required',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                    $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
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
            if ($data['FileUploadTemplateID'] > 0) {
                $FileUploadTemplate = FileUploadTemplate::find($data['FileUploadTemplateID']);
                $options = json_decode($FileUploadTemplate->Options, true);
                $data['Delimiter'] = $options['option']['Delimiter'];
                $data['Enclosure'] = $options['option']['Enclosure'];
                $data['Escape'] = $options['option']['Escape'];
                $data['Firstrow'] = $options['option']['Firstrow'];
            }

            if (!empty($file_name)) {
                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($FileUploadTemplate)) {
                    $grid['FileUploadTemplate'] = json_decode(json_encode($FileUploadTemplate), true);
                    $grid['FileUploadTemplate']['Options'] = json_decode($FileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "message" => "file uploaded", "data" => $grid));
            }
        } catch (Exception $e) {
            return Response::json(array("status" => "failed", "message" => $e->getMessage()));
        }
    }

    /**
     * @return mixed
     */
    public function ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TemplateFile'];
            $grid = getFileContent($file_name, $data);
            if ($data['FileUploadTemplateID'] > 0) {
                $FileUploadTemplate = FileUploadTemplate::find($data['FileUploadTemplateID']);
                $grid['FileUploadTemplate'] = json_decode(json_encode($FileUploadTemplate), true);
                //$grid['FileUploadTemplate']['Options'] = json_decode($FileUploadTemplate->Options,true);
            }
            $grid['FileUploadTemplate']['Options'] = array();
            $grid['FileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['FileUploadTemplate']['Options']['selection'] = $data['selection'];

            return Response::json(array("status" => "success", "message" => "data refreshed", "data" => $grid));
        }catch (Exception $e){
            return Response::json(array("status" => "failed", "message" => $e->getMessage()));
        }
    }

    /**
     * @return mixed
     */
    public function storeTemplate() {
        $data = json_decode(str_replace('Skip loading','',json_encode(Input::all(),true)),true);//Input::all();
        $CompanyID = User::get_companyID();
        if(isset($data['FileUploadTemplateID']) && $data['FileUploadTemplateID']>0) {
            $rules = array('TemplateName' => 'required|unique:tblFileUploadTemplate,Title,'.$data['FileUploadTemplateID'].',FileUploadTemplateID',
                'TemplateFile' => 'required',
            );
        }else{
            $rules = array('TemplateName' => 'required|unique:tblFileUploadTemplate,Title,NULL,FileUploadTemplateID',
                'TemplateFile' => 'required',
            );
        }
        if(!empty($data['selection']['Name'])){
            $data['Name'] = $data['selection']['Name'];
        }else{
            $rules['Name'] = 'required';
        }
        if(!empty($data['selection']['Code'])){
            $data['Code'] = $data['selection']['Code'];
        }else{
            $rules['Code'] = 'required';
        }
        if(!empty($data['selection']['Description'])){
            $data['Description'] = $data['selection']['Description'];
        }else{
            $rules['Description'] = 'required';
        }
        if(!empty($data['selection']['Amount'])){
            $data['Amount'] = $data['selection']['Amount'];
        }else{
            $rules['Amount'] = 'required';
        }
        if(!empty($data['selection']['Note'])){
            $data['Note'] = $data['selection']['Note'];
        }else{
            $data['Note'] = '';
        }
        if(!empty($data['selection']['AppliedTo'])){
            $data['AppliedTo'] = $data['selection']['AppliedTo'];
        }else{
            $data['AppliedTo'] = '';
        }
        /*if(!empty($data['selection']['Active'])){
            $data['Active'] = $data['selection']['Active'];
        }else{
            $rules['Active'] = 'required';
        }*/
        /*if(!empty($data['selection']['BarCode'])){
            $data['BarCode'] = $data['selection']['BarCode'];
        }else{
            $rules['BarCode'] = 'required';
        }*/

        $DynamicFields = $this->getDynamicFields($CompanyID, Product::DYNAMIC_TYPE);
        if($DynamicFields['totalfields'] > 0) {
            foreach ($DynamicFields['fields'] as $dynamicField) {
                if(!empty($data['selection']['DynamicFields-'.$dynamicField->DynamicFieldsID])) {
                    $data['DynamicFields-'.$dynamicField->DynamicFieldsID] = $data['selection']['DynamicFields-'.$dynamicField->DynamicFieldsID];
                } else {
                    $data['DynamicFields-'.$dynamicField->DynamicFieldsID] = "";
                }
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = CompanyConfiguration::get('TEMP_PATH').'/';
        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TEMPLATE_FILE']);
        $amazonItemPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ITEM_UPLOAD']);
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        $destinationItemPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonItemPath;
        copy($temp_path.$file_name,$destinationPath.$file_name);
        copy($temp_path.$file_name,$destinationItemPath.$file_name);
        if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
            return Response::json(array("status" => "failed", "message" => "Failed to upload template sample file."));
        }
        $save = ['CompanyID'=>$CompanyID,'Title'=>$data['TemplateName'],'TemplateFile'=>$amazonPath.$file_name];
        $save['created_by'] = User::get_user_full_name();
        $option["option"]= $data['option'];//['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
        $option["selection"] = filterArrayRemoveNewLines($data['selection']);//['connect_time'=>$data['connect_time'],'disconnect_time'=>$data['disconnect_time'],'billed_duration'=>$data['billed_duration'],'duration'=>$data['duration'],'cld'=>$data['cld'],'cli'=>$data['cli'],'Account'=>$data['Account'],'cost'=>$data['cost']];
        $save['Options'] = str_replace('Skip loading','',json_encode($option));//json_encode($option);
        $save['FileUploadTemplateTypeID'] = FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_ITEM);
        if(isset($data['FileUploadTemplateID']) && $data['FileUploadTemplateID']>0) {
            $template = FileUploadTemplate::find($data['FileUploadTemplateID']);
            $template->update($save);
        }else {/**/
            $template = FileUploadTemplate::create($save);
        }
        if ($template) {
            //Inserting Job Log
            $data['FileUploadTemplateID'] = $template->FileUploadTemplateID;
            $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
            $data['full_path'] = $fullPath;
            $jobType = JobType::where(["Code" => 'IU'])->get(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
            $histdata['CompanyID']= $jobdata["CompanyID"] = $CompanyID;
            $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
            $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
            $jobdata["JobLoggedUserID"] = User::get_userID();
            $jobdata["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
            $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
            $histdata['CreatedBy']= $jobdata["CreatedBy"] = User::get_user_full_name();
            $jobdata["Options"] = json_encode($data);
            $jobdata["created_at"] = date('Y-m-d H:i:s');
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);
            /*$histdata['CompanyGatewayID'] = $data['CompanyGatewayID'];
            $histdata['StartDate'] = $data['StartDate'];
            $histdata['EndDate'] = $data['EndDate'];
            $histdata['created_at'] = date('Y-m-d H:i:s');

            CDRUploadHistory::insert($histdata);*/


            $jobfiledata["JobID"] = $JobID;
            $jobfiledata["FileName"] = basename($fullPath);
            $jobfiledata["FilePath"] = $fullPath;
            $jobfiledata["HttpPath"] = 0;
            $jobfiledata["Options"] = json_encode($data);
            $jobfiledata["CreatedBy"] = User::get_user_full_name();
            $jobfiledata["updated_at"] = date('Y-m-d H:i:s');
            $JobFileID = JobFile::insertGetId($jobfiledata);
            return Response::json(array("status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Template."));
        }
    }

    /**
     * @param $CompanyID
     * @param $Type
     * @return mixed
     */

    public function getDynamicFields($CompanyID, $Type=Product::DYNAMIC_TYPE, $action=''){

        if($action && $action == 'delete') {
            $dynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->get();
        } else {
            $dynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->where('Status',1)->get();
        }

        $dynamicFields['totalfields'] = count($dynamicFields['fields']);

        return $dynamicFields;
    }

    /**
     * @return mixed
     */
    public static function getDynamicFieldsIDBySlug() {
        return DB::table('tblDynamicFields')->where('FieldSlug',DynamicFieldsValue::BARCODE_SLUG)->pluck('DynamicFieldsID');
    }

    /**
     * @param $BarCode
     * @return mixed
     */
    public function getProductByBarCode($BarCode) {
        $ColumnID = $this->getDynamicFieldsIDBySlug();

        if($ColumnID) {
            $product = DB::connection('sqlsrv2')->select("CALL  prc_getProductByBarCode ('" . $BarCode . "','" . $ColumnID . "')");

            if($product) {
                return Response::json(array("status" => "success", "message" => "Product found.", "data" => $product[0]));
            } else {
                return Response::json(array("status" => "failed", "message" => "Product not found."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "BarCode column not found."));
        }
    }

    public function download_sample_excel_file(){
        $filePath =  public_path() .'/uploads/sample_upload/ItemUploadSample.csv';
        download_file($filePath);
    }

    function UpdateBulkItemTypeStatus()
    {
        $data 		= Input::all();
        $CompanyID 	= User::get_companyID();
        $UserName   = User::get_user_full_name();

        if(isset($data['type_active_deactive']) && $data['type_active_deactive']!='')
        {
            if($data['type_active_deactive']=='active'){
                $data['status_set']  = 1;
            }else if($data['type_active_deactive']=='deactive'){
                $data['status_set']  = 0;
            }else{
                return Response::json(array("status" => "failed", "message" => "No item type status selected"));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "No item type status selected"));
        }

        if($data['criteria_ac']=='criteria'){ //all item checkbox checked
            $userID = User::get_userID();

            if(!isset($data['Active']) || $data['Active'] == '') {
                $data['Active'] = 9;
            } else {
                $data['Active'] = (int) $data['Active'];
            }

            $query = "call prc_UpdateItemTypeStatus (".$CompanyID.",'".$UserName."','".$data['title']."',".$data['Active'].",".$data['status_set'].")";

            $result = DB::connection('sqlsrv2')->select($query);
            return Response::json(array("status" => "success", "message" => "Item Types Status Updated"));
        }

        if($data['criteria_ac']=='selected'){ //selceted ids from current page
            if(isset($data['SelectedIDs']) && count($data['SelectedIDs'])>0){
//                foreach($data['SelectedIDs'] as $SelectedID){
                    ItemType::whereIn('ItemTypeID',$data['SelectedIDs'])->where('Active','!=',$data['status_set'])->update(["Active"=>intval($data['status_set'])]);
//                    Product::find($SelectedID)->where('Active','!=',$data['status_set'])->update(["Active"=>intval($data['status_set']),'ModifiedBy'=>$UserName,'updated_at'=>date('Y-m-d H:i:s')]);
//                }
                return Response::json(array("status" => "success", "message" => "Item Types Status Updated"));
            }else{
                return Response::json(array("status" => "failed", "message" => "No Item Types selected"));
            }

        }


    }
}