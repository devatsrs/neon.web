<?php

class ImportsController extends \BaseController {

    var $countries;
    var $model = 'Account';
    public function __construct() {
        $this->countries = Country::getCountryDropdownList();
    }

    /**
     * Display a listing of the resource.
     * GET /accounts
     *
     * @return Response
     */
    public function index() {
            $Quickbook = new BillingAPI();
            $check_quickbook = $Quickbook->check_quickbook();
            $gatewaylist = CompanyGateway::importgatewaylist();
            $templateoption = ['' => 'Select', 1 => 'Create new', 2 => 'Update existing'];
            $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_Account);
            return View::make('imports.index', compact('UploadTemplate','gatewaylist','check_quickbook'));
    }

    public function download_sample_excel_file(){
            $filePath =  public_path() .'/uploads/sample_upload/AccountImportSample.csv';
            download_file($filePath);
    }

    public function check_upload() {
        try {
            ini_set('max_execution_time', 0);
            $data = Input::all();
            if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
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
            } else if (!empty($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if (!empty($file_name)) {

                if (!empty($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                    $AccountFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($AccountFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                //$grid['CompanyGatewayID'] = $data['CompanyGatewayID'];
                if (!empty($AccountFileUploadTemplate)) {
                    $grid['AccountFileUploadTemplate'] = json_decode(json_encode($AccountFileUploadTemplate), true);
                    $grid['AccountFileUploadTemplate']['Options'] = json_decode($AccountFileUploadTemplate->Options, true);
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
                $AccountFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                $grid['AccountFileUploadTemplate'] = json_decode(json_encode($AccountFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['AccountFileUploadTemplate']['Options'] = array();
            $grid['AccountFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['AccountFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function storeTemplate() {
        $data = Input::all();
        $CompanyID = User::get_companyID();

        Account::$importrules['selection.AccountName'] = 'required';
        //Account::$importrules['selection.Email'] = 'required';
        //Account::$importrules['selection.Country'] = 'required';
        //Account::$importrules['selection.FirstName'] = 'required';

        $validator = Validator::make($data, Account::$importrules,Account::$importmessages);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);
        $temp_path = CompanyConfiguration::get('TEMP_PATH') . '/';


        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ACCOUNT_DOCUMENT']);
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload accounts file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            $save['Type'] = FileUploadTemplate::TEMPLATE_Account;
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
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        /* if(!empty($data['tempCompanyGatewayID'])){
             $save['CompanyGatewayID'] = $data['tempCompanyGatewayID'];
         }*/
        $save['AccountType'] = '1';
        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("MGA", $save);
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


    //import data from gateway and insert into temp table
    public function getAccountInfoFromGateway($id,$gateway){
        try {
        ini_set('max_execution_time', 0);
        $CompanyGateway =  CompanyGateway::find($id);
        $response = array();
        $response1 = array();
        if(!empty($CompanyGateway)){
            $getGatewayName = Gateway::getGatewayName($CompanyGateway->GatewayID);
            $response =  GatewayAPI::GatewayMethod($getGatewayName,$CompanyGateway->CompanyGatewayID,'testConnection');
        }
        if(isset($response['result']) && $response['result'] =='OK'){
            $ProcessID = (string) GUID::generate();
            $CompanyGatewayID=$id;
            $param['CompanyGatewayID'] = $id; // change
            $CompanyID = User::get_companyID();
            $param['CompanyID'] = $CompanyID;
            $param['ProcessID'] = $ProcessID;
            if($gateway == 'PBX'){
                $pbx = new PBX($CompanyGatewayID);
                $response1 = $pbx->getAccountsDetail($param);
            }elseif($gateway == 'Porta'){
                $porta = new Porta($CompanyGatewayID);
                $response1 = $porta->getAccountsDetail($param);
            }elseif($gateway == 'MOR'){
                $mor = new MOR($CompanyGatewayID);
                $response1 = $mor->getAccountsDetail($param);
            }elseif($gateway == 'CallShop'){
                $mor = new CallShop($CompanyGatewayID);
                $response1 = $mor->getAccountsDetail($param);
            }elseif($gateway == 'Streamco'){
                $param['ImportDate'] = date('Y-m-d H:i:s.000');
                $streamco = new Streamco($CompanyGatewayID);
                $response1 = $streamco->getAccountsDetail($param);
            }elseif($gateway == 'FusionPBX'){
                $param['ImportDate'] = date('Y-m-d H:i:s.000');
                $FusionPBX = new FusionPBX($CompanyGatewayID);
                $response1 = $FusionPBX->getAccountsDetail($param);
            }
            //$pbx = new PBX($CompanyGatewayID);

            if(isset($response1['result']) && $response1['result'] =='OK'){
                return Response::json(array("status" => "success", "message" => "Get Account successfully From Gateway", "processid" => $ProcessID));
            }else if(isset($response1['faultCode']) && isset($response1['faultString'])){
                return Response::json(array("status" => "failed", "message" => "Access Denied."));
            }else{
                return Response::json(array("status" => "failed", "message" => "Import Gateway Account."));
            }
        }else if(isset($response['faultCode']) && isset($response['faultString'])){
            return Response::json(array("status" => "failed", "message" => "Failed to connect Gateway."));
        }else{
            return Response::json(array("status" => "failed", "message" => "Failed to connect Gateway."));
        }

        }catch(Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }


    // get missing getway account
    public function ajax_get_missing_gatewayaccounts(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = ['tblTempAccountID','AccountName','FirstName','LastName','Email'];
        $sort_column = $columns[$data['iSortCol_0']];
        $CompanyGatewayID = $data['CompanyGatewayID'];
        $cprocessid = $data['importprocessid'];
        $query = "call prc_getMissingAccountsByGateway (".$CompanyID.", ".$CompanyGatewayID.",'".$cprocessid."', ".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Missing Gateway Account', function ($excel) use ($excel_data) {
                $excel->sheet('Missing Gateway Account', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        $query .=',0)';

        //$query = "call prc_getMissingAccountsByGateway (".$CompanyID.",".$CompanyGatewayID.")";

        return DataTableSql::of($query)->make();

    }

    //leads import
    public function import_leads() {
        $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_Leads);
        return View::make('imports.leads', compact('UploadTemplate'));
    }



    public function leads_check_upload() {
        try {
            ini_set('max_execution_time', 0);
            $data = Input::all();
            if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
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
            } else if (!empty($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if (!empty($file_name)) {

                if (!empty($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                    $AccountFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($AccountFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                //$grid['CompanyGatewayID'] = $data['CompanyGatewayID'];
                if (!empty($AccountFileUploadTemplate)) {
                    $grid['AccountFileUploadTemplate'] = json_decode(json_encode($AccountFileUploadTemplate), true);
                    $grid['AccountFileUploadTemplate']['Options'] = json_decode($AccountFileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        }catch(Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    function leads_ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TempFileName'];
            $grid = getFileContent($file_name, $data);
            $grid['filename'] = $data['TemplateFile'];
            $grid['tempfilename'] = $data['TempFileName'];
            if ($data['uploadtemplate'] > 0) {
                $AccountFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                $grid['AccountFileUploadTemplate'] = json_decode(json_encode($AccountFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['AccountFileUploadTemplate']['Options'] = array();
            $grid['AccountFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['AccountFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function leads_storeTemplate() {
        $data = Input::all();
        $CompanyID = User::get_companyID();

        Account::$importleadrules['selection.AccountName'] = 'required';
        //Account::$importrules['selection.Email'] = 'required';
        //Account::$importrules['selection.Country'] = 'required';
        Account::$importleadrules['selection.FirstName'] = 'required';
        Account::$importleadrules['selection.LastName'] = 'required';

        $validator = Validator::make($data, Account::$importleadrules,Account::$importleadmessages);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);
        $temp_path = CompanyConfiguration::get('TEMP_PATH') . '/';


        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ACCOUNT_DOCUMENT']);
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload accounts file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            $save['Type'] = FileUploadTemplate::TEMPLATE_Leads;
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
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        /* if(!empty($data['tempCompanyGatewayID'])){
             $save['CompanyGatewayID'] = $data['tempCompanyGatewayID'];
         }*/
        $save['AccountType'] = '0';
        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("MGA", $save);
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

    //sample file of leads
    public function leads_download_sample_excel_file(){
            $filePath =  public_path() .'/uploads/sample_upload/LeadsImportSample.csv';
            download_file($filePath);
    }

    public function add_missing_gatewayaccounts(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        if(empty($data['companygatewayid'])){
            return json_encode(array("status" => "failed", "message" => "Please select gateway."));
        }
        $AccountIDs =array_filter(explode(',',$data['TempAccountIDs']),'intval');
        if (is_array($AccountIDs) && count($AccountIDs) || !empty($data['criteria'])) {
            $jobType = JobType::where(["Code" => 'MGA'])->first(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->first(["JobStatusID"]);
            $jobdata["CompanyID"] = $CompanyID;
            $jobdata["JobTypeID"] = $jobType->JobTypeID ;
            $jobdata["JobStatusID"] =  $jobStatus->JobStatusID;
            $jobdata["JobLoggedUserID"] = User::get_userID();
            $jobdata["Title"] =  $jobType->Title;
            $jobdata["Description"] = $jobType->Title ;
            $jobdata["CreatedBy"] = User::get_user_full_name();
            $jobdata["Options"] = json_encode($data);
            $jobdata["created_at"] = date('Y-m-d H:i:s');
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);
            if($JobID){
                return json_encode(["status" => "success", "message" => "Import Account Job Added in queue to process.You will be notified once job is completed."]);
            }else{
                return json_encode(array("status" => "failed", "message" => "Problem Creating in import Account."));
            }
        }else{
            return json_encode(array("status" => "failed", "message" => "Please select account."));
        }
    }

    /**
     * QuickBook Import
     */

    public function getAccountInfoFromQuickbook(){
        try {
            ini_set('max_execution_time', 0);

            $data = Input::all();
            $QuickBook = new BillingAPI();
            $quickbooks_CompanyInfo = $QuickBook->test_connection();

            if(!empty($quickbooks_CompanyInfo)){
                $ProcessID = (string) GUID::generate();
                log::info('--ProcessID--'.$ProcessID);
                $CompanyID = User::get_companyID();
                $param['CompanyID'] = $CompanyID;
                $param['ProcessID'] = $ProcessID;
                $quickbook = new BillingAPI();
                $response1 = $quickbook->getAccountsDetail($param);
                log::info('Quickbook Response'.print_r($response1,true));
                if(isset($response1['result']) && $response1['result'] =='OK'){
                    return Response::json(array("status" => "success", "message" => "Get Account successfully From QuickBook", "processid" => $ProcessID));
                }else if(isset($response1['error'])){
                    return Response::json(array("status" => "failed", "message" => "Failed to connect QuickBook."));
                }else{
                    return Response::json(array("status" => "failed", "message" => "Import QuickBook Account."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Failed to connect QuickBook."));
            }
        }catch(Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }

    }

    public function ajax_get_missing_quickbookaccounts(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = ['tblTempAccountID','AccountName','FirstName','LastName','Email'];
        $sort_column = $columns[$data['iSortCol_0']];
        //$CompanyGatewayID = $data['CompanyGatewayID'];
        $cprocessid = $data['quickbookimportprocessid'];
        $query = "call prc_getMissingAccountsOfQuickbook (".$CompanyID.",'".$cprocessid."', ".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('QuickBook Account', function ($excel) use ($excel_data) {
                $excel->sheet('QuickBook Account', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        $query .=',0)';

        //$query = "call prc_getMissingAccountsByGateway (".$CompanyID.",".$CompanyGatewayID.")";

        return DataTableSql::of($query)->make();
    }

    public function add_missing_quickbookaccounts(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        if(empty($data['quickbookimportprocessid'])){
            return json_encode(array("status" => "failed", "message" => "Problem Creating in import Account.."));
        }

        $AccountIDs =array_filter(explode(',',$data['TempAccountIDs']),'intval');
        if (is_array($AccountIDs) && count($AccountIDs) || !empty($data['criteria'])) {
            $jobType = JobType::where(["Code" => 'MGA'])->first(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->first(["JobStatusID"]);
            $jobdata["CompanyID"] = $CompanyID;
            $jobdata["JobTypeID"] = $jobType->JobTypeID ;
            $jobdata["JobStatusID"] =  $jobStatus->JobStatusID;
            $jobdata["JobLoggedUserID"] = User::get_userID();
            $jobdata["Title"] =  $jobType->Title;
            $jobdata["Description"] = $jobType->Title ;
            $jobdata["CreatedBy"] = User::get_user_full_name();
            $jobdata["Options"] = json_encode($data);
            $jobdata["created_at"] = date('Y-m-d H:i:s');
            $jobdata["updated_at"] = date('Y-m-d H:i:s');

            $JobID = Job::insertGetId($jobdata);
            if($JobID){
                return json_encode(["status" => "success", "message" => "Import Account Job Added in queue to process.You will be notified once job is completed."]);
            }else{
                return json_encode(array("status" => "failed", "message" => "Problem Creating in import Account."));
            }
        }else{
            return json_encode(array("status" => "failed", "message" => "Please select account."));
        }
    }

    /*import ip section */
    public function import_ips() {
        $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_IPS);
        return View::make('imports.ips', compact('UploadTemplate'));
    }

    // download sample file of ip upload
    public function ips_download_sample_excel_file(){
        $filePath = public_path() .'/uploads/sample_upload/AccountIPUploadSample.csv';
        download_file($filePath);

    }

    public function ips_check_upload() {
        try {
            ini_set('max_execution_time', 0);
            $data = Input::all();
            if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
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

                if ($data['UploadTemplate'] > 0) {
                    $IPFileUploadTemplate = FileUploadTemplate::find($data['UploadTemplate']);
                    $options = json_decode($IPFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($IPFileUploadTemplate)) {
                    $grid['IPFileUploadTemplate'] = json_decode(json_encode($IPFileUploadTemplate), true);
                    $grid['IPFileUploadTemplate']['Options'] = json_decode($IPFileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        }catch(Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    function ips_ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TempFileName'];
            $grid = getFileContent($file_name, $data);
            $grid['filename'] = $data['TemplateFile'];
            $grid['tempfilename'] = $data['TempFileName'];
            if ($data['UploadTemplate'] > 0) {
                $IPFileUploadTemplate = FileUploadTemplate::find($data['UploadTemplate']);
                $grid['IPFileUploadTemplate'] = json_decode(json_encode($IPFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['IPFileUploadTemplate']['Options'] = array();
            $grid['IPFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['IPFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }


    public function ips_storeTemplate() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $rules = array(
            'selection.AccountName'=>'required',
            'selection.IP'=>'required',
            'selection.Type'=>'required',
        );
        $message = array(
            'selection.AccountName.required' =>'Account Name Field is required',
            'selection.IP.required' =>'IP Field is required',
            'selection.Type.required' =>'Type Field is required',
        );

        $validator = Validator::make($data, $rules,$message);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $file_name = basename($data['TemplateFile']);

        $temp_path = CompanyConfiguration::get('TEMP_PATH') . '/';

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['IP_UPLOAD']);
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload ip file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $destinationPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            $save['Type'] = FileUploadTemplate::TEMPLATE_IPS;
            if (isset($data['UploadTemplate']) && $data['UploadTemplate'] > 0) {
                $template = FileUploadTemplate::find($data['UploadTemplate']);
                $template->update($save);
            } else {
                $template = FileUploadTemplate::create($save);
            }
            $data['UploadTemplate'] = $template->FileUploadTemplateID;
        }
        $save = array();
        $option["option"]=  $data['option'];
        $option["selection"] = $data['selection'];
        $save['Options'] = json_encode($option);
        $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path'] = $fullPath;
        if(isset($data['UploadTemplate'])) {
            $save['UploadTemplate'] = $data['UploadTemplate'];
        }
        $save['IPCLI'] = 'IP';
        //$save['dialstringname'] = DialString::getDialStringName($id);

        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("ICU", $save);
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
}
