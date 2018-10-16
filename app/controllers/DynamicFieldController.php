<?php

class DynamicFieldController extends \BaseController {

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
        $columns = ['DynamicFieldsID','title','FieldName','FieldDomType','created_at','Status','FieldDescription','FieldOrder','FieldSlug','Type','ItemTypeID','Minimum','Maximum','DefaultValue','SelectVal'];
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getDynamicTypes (".$CompanyID.", '".$data['FieldName']."','".$data['FieldDomType']."','".$data['Active']."','product','".$data['ItemTypeID']."', ".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DynamicField.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DynamicField.xls';
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
//        return DataTableSql::of($query,'sqlsrv2')->make();
    }


    public function index()
    {
        $id=0;
        $Type =  Product::DYNAMIC_TYPE;
        $companyID = User::get_companyID();
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $DynamicFields = $this->getDynamicFields($companyID,$Type);
        $itemtypes 	= 	ItemType::getItemTypeDropdownList($companyID);
        return View::make('products.dynamicfields.index', compact('id','gateway','DynamicFields','itemtypes'));
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
        $data ["CompanyID"] = $companyID;
        $data['Active'] = isset($data['Active']) ? 1 : 0;
        $data["created_by"] = User::get_user_full_name();
        $slug= str_replace(' ', '', $data['FieldName']);
        $data ["FieldSlug"] = "Product".$slug;
        $data ["Status"]=$data['Active'];
        $data["created_at"]=date('Y-m-d H:i:s');
        $data ["Type"] = Product::DYNAMIC_TYPE;
        unset($data['DynamicFieldsID']);
        unset($data['ProductClone']);
        unset($data['Active']);

        $rules = array(
            'CompanyID' => 'required',
            'ItemTypeID' => 'required',
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
        $cnt_duplidate = DynamicFields::where('FieldName',$data['FieldName'])->where('ItemTypeID',$data['ItemTypeID'])->get()->count();
        if($cnt_duplidate > 0){
            return Response::json(array("status" => "failed", "message" => "Dynamic Field With This Name Already Exists."));
        }

        if ($dynamicfield = DynamicFields::create($data)) {
            return Response::json(array("status" => "success", "message" => "Dynamic Field Successfully Created",'newcreated'=>$dynamicfield));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Dynamic Field."));
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
                'ItemTypeID' => 'required',
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
            $cnt_duplidate = DynamicFields::where('FieldName',$data['FieldName'])->where('ItemTypeID',$data['ItemTypeID'])->where('DynamicFieldsID','!=',$dynamicfield->DynamicFieldsID)->get()->count();
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

	/**
	 * Remove the specified resource from storage.
	 * DELETE /products/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function delete($id) {
        if( intval($id) > 0){
            if(!DynamicFields::checkForeignKeyById($id)) {
                try {
                    $result = DynamicFields::find($id)->delete();
                    if ($result) {
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

    public function download_sample_excel_file(){
        $filePath =  public_path() .'/uploads/sample_upload/ItemUploadSample.csv';
        download_file($filePath);
    }

    function UpdateBulkDynamicFieldStatus()
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
                return Response::json(array("status" => "failed", "message" => "No Dynamic Field status selected"));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "No Dynamic Field status selected"));
        }

        if($data['criteria_ac']=='criteria'){ //all item checkbox checked
            $userID = User::get_userID();

            if(!isset($data['Active']) || $data['Active'] == '') {
                $data['Active'] = 9;
            } else {
                $data['Active'] = (int) $data['Active'];
            }

            $query = "call prc_UpdateDynamicFieldStatus (".$CompanyID.",'".$UserName."','product','".$data['FieldName']."','".$data['FieldDomType']."','".$data['ItemTypeID']."',".$data['Active'].",".$data['status_set'].")";
            $result = DB::connection('sqlsrv')->select($query);
            return Response::json(array("status" => "success", "message" => "Dynamic Field Status Updated"));
        }

        if($data['criteria_ac']=='selected'){ //selceted ids from current page
            if(isset($data['SelectedIDs']) && count($data['SelectedIDs'])>0){
//                foreach($data['SelectedIDs'] as $SelectedID){
                    DynamicFields::whereIn('DynamicFieldsID',$data['SelectedIDs'])->where('Status','!=',$data['status_set'])->update(["Status"=>intval($data['status_set'])]);
//                    Product::find($SelectedID)->where('Active','!=',$data['status_set'])->update(["Active"=>intval($data['status_set']),'ModifiedBy'=>$UserName,'updated_at'=>date('Y-m-d H:i:s')]);
//                }
                return Response::json(array("status" => "success", "message" => "Dynamic Field Status Updated"));
            }else{
                return Response::json(array("status" => "failed", "message" => "No Dynamic Field selected"));
            }

        }

    }

    function DeleteBulkDynamicField(){
        $data 		= Input::all();
        $CompanyID 	= User::get_companyID();
        $UserName   = User::get_user_full_name();
        if(isset($data['type_active_deactive']) && $data['type_active_deactive']!='')
        {
            if($data['type_active_deactive']=='delete'){
                if($data['criteria_ac']=='selected'){ //selceted ids from current page
                    if(isset($data['SelectedIDs']) && count($data['SelectedIDs'])>0){
                        DB::select("delete tblDynamicFields from tblDynamicFields left join tblDynamicFieldsValue on tblDynamicFieldsValue.DynamicFieldsID = tblDynamicFields.DynamicFieldsID
                        where tblDynamicFieldsValue.DynamicFieldsID is null
                                AND tblDynamicFields.CompanyID = ".$CompanyID."
                                AND tblDynamicFields.Type= 'product'");
                        //$result = DynamicFields::whereIn('DynamicFieldsID',$data['SelectedIDs'])->delete();
                        return Response::json(array("status" => "success", "message" => "Dynamic Field Deleted Successfully."));

                    }else{
                        return Response::json(array("status" => "failed", "message" => "No Dynamic Field selected"));
                    }

                }

                if($data['criteria_ac']=='criteria'){ //all item checkbox checked
                    $userID = User::get_userID();

                    if(!isset($data['Active']) || $data['Active'] == '') {
                        $data['Active'] = 9;
                    } else {
                        $data['Active'] = (int) $data['Active'];
                    }

                    $query = "call prc_DeleteDynamicFieldStatus (".$CompanyID.",'".$UserName."','product','".$data['FieldName']."','".$data['FieldDomType']."','".$data['ItemTypeID']."',".$data['Active'].")";
                    $result = DB::connection('sqlsrv')->select($query);

                    return Response::json(array("status" => "success", "message" => "Dynamic Fields which Are Not In Use Are Deleted Successfully."));
                }

            }else{
                return Response::json(array("status" => "failed", "message" => "No Dynamic Field status selected"));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "No Dynamic Field status selected"));
        }

    }

    public function getDynamicFieldsByItemType($CompanyID, $Type=Product::DYNAMIC_TYPE,$ItemTypeID){
        $dynamicFields['fields'] = DynamicFields::where(['Type'=>$Type,'CompanyID'=>$CompanyID,'Status'=>1,'ItemTypeID'=>$ItemTypeID])->get();
        $dynamicFields['totalfields'] = count($dynamicFields['fields']);

        return $dynamicFields;
    }

    public function ViewByType($ItemTypeID){
        $Type =  Product::DYNAMIC_TYPE;
        $companyID = User::get_companyID();
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $DynamicFields = $this->getDynamicFieldsByItemType($companyID,$Type,$ItemTypeID);
        $itemtypes 	= 	ItemType::getItemTypeDropdownList($companyID);
        return View::make('products.dynamicfields.index', compact('id','gateway','DynamicFields','itemtypes','ItemTypeID'));
    }

}