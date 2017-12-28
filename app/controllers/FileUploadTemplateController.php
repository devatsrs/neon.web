<?php

class FileUploadTemplateController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $select = ['Title','created_at','FileUploadTemplateID'];
        $fileuploadtemplate = FileUploadTemplate::select($select)->where(['CompanyID'=>$CompanyID]);
        if(isset($data['Export']) && $data['Export'] == 1) {
            $template = FileUploadTemplate::where(["CompanyID" => $CompanyID])->orderBy("Title", "asc")->get(["Title", "created_at as Created at"]);
            $excel_data = json_decode(json_encode($template),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Upload Template.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Upload Template.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        return Datatables::of($fileuploadtemplate)->make();
    }

    /**
     * Display a listing of the resource.
     * GET /uploadtemplate
     *
     * @return Response
     */
    public function index() {
        return View::make('fileuploadtemplates.index', compact('account_owners'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /uploadtemplate/create
     *
     * @return Response
     */
    public function create() {
        $CompanyID = User::get_companyID();
        $columns = [];
        $rows = [];
        $id = '';
        $TemplateName  = '';
        $TemplateType = '';
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
            $dialstring = DialString::getDialStringIDList();
            $currencies = Currency::getCurrencyDropdownIDList();

            $attrskiprows = new stdClass();
            $attrskiprows->start_row = isset($data['start_row']) ? $data['start_row'] : 0;
            $attrskiprows->end_row = isset($data['end_row']) ? $data['end_row'] : 0;

            $TemplateType = $data['TemplateType'];

            // for item template
            $Type =  Product::DYNAMIC_TYPE;
            $productObj = new ProductsController();
            $DynamicFields = $productObj->getDynamicFields($CompanyID,$Type);

            // for customer cdr template
            $trunks = Trunk::getTrunkDropdownIDList();
            $trunks = $trunks+array(0=>'Find From CustomerPrefix');
            $trunks = array('Trunk'=>$trunks);
            $Services = Service::getDropdownIDList(User::get_companyID());
            $Services = array('Service'=>$Services);
            $ratetables = RateTable::getRateTableList();
        }
        $heading = 'New Template';
        $templateID = '';
        $Types = array(""=>"select");
        $Types += FileUploadTemplate::$template_type;

        return View::make('fileuploadtemplates.create',compact('columns','rows','message','csvoption','attrselection','file_name','templateID','heading','TemplateName','dialstring','currencies','attrskiprows','Types','TemplateType','DynamicFields','trunks','Services','ratetables'));
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
        $CompanyID = User::get_companyID();
        $message = '';
        $file_name = '';
        $TemplateName = '';
        $csvoption= '';
        $csvoption = new StdClass;
        $data = Input::all();
        $columns = [];
        $rows = [];

        if( intval($id) > 0){
            $template = FileUploadTemplate::find($id);
            if (!empty($template) && Request::isMethod('post')) {
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
                    $TemplateName = $data['TemplateName'];
                }

                $attrskiprows = new stdClass();
                $attrskiprows->start_row = isset($data['start_row']) ? $data['start_row'] : 0;
                $attrskiprows->end_row = isset($data['end_row']) ? $data['end_row'] : 0;

                $templateoptions=json_decode($template->Options);
                $csvoption = $templateoptions->option;
                $attrselection = $templateoptions->selection;
            }else if (!empty($template)) {
                $templateoptions=json_decode($template->Options);
                $csvoption = $templateoptions->option;
                $attrselection = $templateoptions->selection;
                $attrskiprows = isset($templateoptions->skipRows) ? $templateoptions->skipRows : '';

                if(!empty($csvoption->Delimiter)){
                    Config::set('excel::csv.delimiter', $csvoption->Delimiter);
                }
                if(!empty($csvoption->Enclosure)){
                    Config::set('excel::csv.enclosure', $csvoption->Enclosure);
                }
                if(!empty($csvoption->Escape)){
                    Config::set('excel::csv.line_ending', $csvoption->Escape);
                }

                if (!empty($template->TemplateFile)) {
                    $path = AmazonS3::unSignedUrl($template->TemplateFile);
                    if (strpos($path, "https://") !== false) {
                        $file = CompanyConfiguration::get('TEMP_PATH') . '/' . basename($path);
                        file_put_contents($file, file_get_contents($path));
                        $file_name = $file;
                    } else {
                        $file_name = $path;
                    }
                }
            }
            if(!empty($file_name)){
                $grid = getFileContent($file_name,$data);
                $columns = array(""=> "Skip loading") + $grid['columns'];
                $rows = $grid['rows'];
            }
            $dialstring = DialString::getDialStringIDList();
            $currencies = Currency::getCurrencyDropdownIDList();
        }else{
            $message = 'toastr.error("Not Found", "Error", toastr_opts);';
        }
        $heading = 'Update Template';

        $Types = array(""=>"select");
        $Types += FileUploadTemplate::$template_type;

        // for item template
        $Type =  Product::DYNAMIC_TYPE;
        $productObj = new ProductsController();
        $DynamicFields = $productObj->getDynamicFields($CompanyID,$Type);

        // for customer cdr template
        $trunks = Trunk::getTrunkDropdownIDList();
        $trunks = $trunks+array(0=>'Find From CustomerPrefix');
        $trunks = array('Trunk'=>$trunks);
        $Services = Service::getDropdownIDList(User::get_companyID());
        $Services = array('Service'=>$Services);
        $ratetables = RateTable::getRateTableList();

        return View::make('fileuploadtemplates.create',compact('columns','rows','message','csvoption','attrselection','file_name','template','heading','dialstring','currencies','attrskiprows','Types','DynamicFields','trunks','Services','ratetables','TemplateName'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /uploadtemplate
     *
     * @return Response
     */
    public function store() {
        $data = Input::all();

        if(!empty($data['TemplateType'])) {
            $response = FileUploadTemplate::createOrUpdateFileUploadTemplate($data);

            if(is_object($response)) { //validator error
                return $response;
            } else if ($response['status'] == "success") {
                return Response::json(array("status" => $response['status'], "message" => $response['message'], 'redirect' => URL::to('/uploadtemplate/')));
            } else {
                return Response::json(array("status" => $response['status'], "message" => $response['message']));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Please select Template Type."));
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

        if(!empty($data['TemplateType'])) {
            $response = FileUploadTemplate::createOrUpdateFileUploadTemplate($data);

            if(is_object($response)) { //validator error
                return $response;
            } else if ($response['status'] == "success") {
                return Response::json(array("status" => $response['status'], "message" => $response['message'], 'redirect' => URL::to('/uploadtemplate/')));
            } else {
                return Response::json(array("status" => $response['status'], "message" => $response['message']));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Please select Template Type."));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /file upload Template/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id) {
        if( intval($id) > 0){
            try {
                $result = FileUploadTemplate::find($id)->delete();
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
}