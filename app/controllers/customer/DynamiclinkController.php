<?php

class DynamiclinkController extends \BaseController {

    var $model = 'Dynamiclink';
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
        $columns = ['DynamicLinkID','Title','Link','Currency','created_at'];
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getDynamiclinks (".$CompanyID.", '','0', ".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DynamicLinks.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DynamicLinks.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';
        $data = DataTableSql::of($query,'sqlsrv')->make(false);

        return Response::json($data);

    }


    public function index()
    {
        $companyID = User::get_companyID();
        $Currency = Currency::getCurrencyDropdownIDList($companyID);
        return View::make('customer.dynamiclink.index', compact('Currency'));
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
        $data["CreatedBy"] =  User::get_user_full_name();
        $data["created_at"]=date('Y-m-d H:i:s');
        unset($data['DynamicLinkID']);

        $rules = array(
            'CompanyID' => 'required',
            'Title' => 'required',
            'Link' => 'required',
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        //Check FieldName duplicate
        $cnt_duplidate = Dynamiclink::where('Title',$data['Title'])->get()->count();
        if($cnt_duplidate > 0){
            return Response::json(array("status" => "failed", "message" => "Title With This Name Already Exists."));
        }

        if ($dynamicfield = Dynamiclink::create($data)) {
            return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_DYNAMICLINK_MSG_NOTIFICATION_SUCCESSFULLY_CREATED')));
        } else {
            return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_DYNAMICLINK_MSG_PROBLEM_CREATING_NOTIFICATION')));
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
            $dynamiclink = Dynamiclink::findOrFail($id);
            $user = User::get_user_full_name();

            $companyID = User::get_companyID();
            $data["CompanyID"] = $companyID;
            $data["updated_at"]=date('Y-m-d H:i:s');
            $data["ModifiedBy"] = $user;

            $rules = array(
                'CompanyID' => 'required',
                'Title' => 'required',
                'Link' => 'required',
            );

            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if ($dynamiclink->update($data)) {
                return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_DYNAMICLINK_MSG_DYNAMICLINK_SUCCESSFULLY_UPDATED')));
            } else {
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_DYNAMICLINK_MSG_PROBLEM_CREATING_NOTIFICATION')));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_DYNAMICLINK_MSG_PROBLEM_CREATING_NOTIFICATION')));
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
            try{
                $DynamicLink = Dynamiclink::find($id);
                $result = $DynamicLink->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_DYNAMICLINK_MSG_DYNAMICLINK_SUCCESSFULLY_DELETED')));
                } else {
                    return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_DYNAMICLINK_MSG_DYNAMICLINK_DELETING_NOTIFICATION')));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.MESSAGE_PROBLEM_DELETING_EXCEPTION'). $ex->getMessage()));
            }
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

}