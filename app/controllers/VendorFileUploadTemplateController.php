<?php

class VendorFileUploadTemplateController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $select = ['Title','created_at','VendorFileUploadTemplateID'];
        $vendorfileuploadtemplate = VendorFileUploadTemplate::select($select)->where(['CompanyID'=>$CompanyID]);
        if(isset($data['Export']) && $data['Export'] == 1) {
            $Vendortemplate = VendorFileUploadTemplate::where(["CompanyID" => $CompanyID])->orderBy("Title", "asc")->get(["Title", "created_at as Created at"]);
            $excel_data = json_decode(json_encode($Vendortemplate),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Template.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Template.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        return Datatables::of($vendorfileuploadtemplate)->make();
    }

    /**
     * Display a listing of the resource.
     * GET /uploadtemplate
     *
     * @return Response
     */
    public function index() {
        return View::make('vendorfileuploadtemplate.index', compact('account_owners'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /uploadtemplate/create
     *
     * @return Response
     */
    public function create() {
        $columns = [];
        $rows = [];
        $id = '';
        $TemplateName  = '';
        $attrselection = new StdClass;
        $csvoption = new StdClass;
        $attrselection->Code = $attrselection->Description= $attrselection->Rate =$attrselection->EffectiveDate=$attrselection->Change=$attrselection->Interval1=$attrselection->IntervalN=$attrselection->ConnectionFee=$attrselection->Action=$attrselection->ActionDelete=$attrselection->ActionUpdate=$attrselection->ActionInsert=$attrselection->DateFormat='';
        $csvoption->Delimiter=$csvoption->Enclosure=$csvoption->Escape='';
        $csvoption->Delimiter = ',';
        $csvoption->Firstrow='columnname';
        $message = $file_name = '';
        if (Request::isMethod('post')) {
            $data = Input::all();
            if(!empty($data['TemplateName'])){
                if (Input::hasFile('excel')) {
                    $upload_path = CompanyConfiguration::get('TEMP_PATH');
                    $excel = Input::file('excel');
                    $ext = $excel->getClientOriginalExtension();
                    if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                        $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                        $excel->move($upload_path, $file_name);
                        $file_name = $upload_path.'/'.$file_name;
                    }
                }else if(isset($data['TemplateFile']) && isset($data['TemplateName'])) {
                    $file_name = $data['TemplateFile'];
                }else{
                    $message = 'toastr.error("Please and select a file", "Error", toastr_opts);';
                }
            }else{
                $message = 'toastr.error("Please insert template name", "Error", toastr_opts);';
            }
            if(!empty($file_name)){
                $grid = getFileContent($file_name,$data);
                $columns = array(""=> "Skip loading") + $grid['columns'];
                $rows = $grid['rows'];
                $TemplateName = $data['TemplateName'];
            }
        }
        $heading = 'New Template';
        $templateID = '';
        return View::make('vendorfileuploadtemplate.create',compact('columns','rows','message','csvoption','attrselection','file_name','templateID','heading','TemplateName'));
    }

    function ajaxfilegrid(){
        $data = Input::all();
        $file_name = $data['TemplateFile'];
        $grid = getFileContent($file_name,$data);
        return json_encode($grid);
    }


    /**
     * Show the form for editing a resource.
     * GET /uploadtemplate/edit
     *
     * @return Response
     */
    public function edit($id) {
        $message = '';
        $file_name = '';
        $TemplateName = $csvoption= '';
        $csvoption = new StdClass;
        $data = Input::all();
        $columns = [];
        $rows = [];
        $templateID = $id;
        if( intval($id) > 0){
            $template = VendorFileUploadTemplate::find($id);
             if(!empty($template) && isset($data['TemplateFile'])) {
                 $templateoptions=json_decode($template->Options);
                 $TemplateName = $template->Title;
                 $attrselection = $templateoptions->selection;
                 $file_name = $data['TemplateFile'];
                 $csvoption->Enclosure = $data['Enclosure'];
                 $csvoption->Delimiter = $data['Delimiter'];
                 $csvoption->Escape = $data['Escape'];
                 $csvoption->Firstrow = $data['Firstrow'];
            }else if (!empty($template)) {
                $TemplateName = $template->Title;
                $templateoptions=json_decode($template->Options);
                $csvoption = $templateoptions->option;
                $attrselection = $templateoptions->selection;
                if(!empty($csvoption->Delimiter)){
                    Config::set('excel::csv.delimiter', $csvoption->Delimiter);
                }
                if(!empty($csvoption->Enclosure)){
                    Config::set('excel::csv.enclosure', $csvoption->Enclosure);
                }
                if(!empty($csvoption->Escape)){
                    Config::set('excel::csv.line_ending', $csvoption->Escape);
                }

                if(!empty($template->TemplateFile)){
                    $path = AmazonS3::unSignedUrl($template->TemplateFile);
                    if(strpos($path, "https://") !== false){
                        $file = CompanyConfiguration::get('TEMP_PATH').'/'.basename($path);
                        file_put_contents($file,file_get_contents($path));
                        $file_name = $file;
                    }else{
                        $file_name = $path;
                    }
                }
            }
            if(!empty($file_name)){
                $grid = getFileContent($file_name,$data);
                $columns = array(""=> "Skip loading") + $grid['columns'];
                $rows = $grid['rows'];
            }
        }else{
            $message = 'toastr.error("Not Found", "Error", toastr_opts);';
        }
        $heading = 'Update Template';
        return View::make('vendorfileuploadtemplate.create',compact('columns','rows','message','csvoption','attrselection','file_name','TemplateName','templateID','heading'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /uploadtemplate
     *
     * @return Response
     */
    public function store() {

        $data = Input::all();
        $CompanyID = User::get_companyID();
        $rules = array('TemplateName'=>'required|unique:tblVendorFileUploadTemplate,Title,NULL,VendorFileUploadTemplateID',
                        'TemplateFile'=>'required');
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = $data['TemplateFile'];
        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_TEMPLATE_FILE']) ;
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        rename ($file_name,$destinationPath.basename($file_name));
        if(!AmazonS3::upload($destinationPath.basename($file_name),$amazonPath)){
            return Response::json(array("status" => "failed", "message" => "Failed to upload."));
        }
        $save = ['CompanyID'=>$CompanyID,'Title'=>$data['TemplateName'],'TemplateFile'=>$amazonPath.basename($file_name)];
        $save['created_by'] = User::get_user_full_name();
        $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
        $option["selection"] = filterArrayRemoveNewLines($data['selection']);//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
        $save['Options'] = json_encode($option);
        if (VendorFileUploadTemplate::create($save)) {
            return Response::json(array("status" => "success", "message" => "Template Successfully Created",'redirect' => URL::to('/uploadtemplate/')));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Account."));
        }
    }

    /**
     * Update a created resource in storage.
     * POST /uploadtemplate
     *
     * @return Response
     */
    public function update() {

        $data = Input::all();
        $template = VendorFileUploadTemplate::find($data['VendorFileUploadTemplateID']);
        $CompanyID = User::get_companyID();
        $rules = array('TemplateName'=>'required|unique:tblVendorFileUploadTemplate,Title,'.$data['VendorFileUploadTemplateID'].',VendorFileUploadTemplateID');
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $save = ['CompanyID'=>$CompanyID,'Title'=>$data['TemplateName'],];
        $save['updated_by'] = User::get_user_full_name();
        $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
        $option["selection"] = filterArrayRemoveNewLines($data['selection']);//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
        $save['Options'] = json_encode($option);
        if ($template->update($save)) {
            return Response::json(array("status" => "success", "message" => "Template Successfully Updated",'redirect' => URL::to('/uploadtemplate/')));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Template."));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /Vendor file upload Template/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id) {
        if( intval($id) > 0){
            try {
                $result = VendorFileUploadTemplate::find($id)->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Template Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Template."));
                }
            } catch (Exception $ex) {
                return Response::json(array("status" => "failed", "message" =>$ex->getMessage()));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Not found."));
        }
    }
    /**
     * Display the specified resource.
     * GET /accounts/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
            $account = Account::find($id);
            $companyID = User::get_companyID();
            $account_owner = User::find($account->Owner);
            $notes = Note::where(["CompanyID" => $companyID, "AccountID" => $id])->orderBy('NoteID', 'desc')->get();
            $contacts = Contact::where(["CompanyID" => $companyID, "Owner" => $id])->orderBy('FirstName', 'asc')->get();
            $verificationflag = AccountApprovalList::isVerfiable($id);
            $outstanding =Account::getOutstandingAmount($companyID,$account->AccountID,get_round_decimal_places($account->AccountID));
            $currency = Currency::getCurrency($account->CurrencyId);
            return View::make('accounts.show', compact('account', 'account_owner', 'notes', 'contacts','verificationflag','outstanding','currency'));
    }
}