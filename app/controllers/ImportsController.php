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
            $templateoption = ['' => 'Select', 1 => 'Create new', 2 => 'Update existing'];
            $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_Account);
            return View::make('imports.index', compact('UploadTemplate'));
    }

    public function check_upload() {
        try {
            ini_set('max_execution_time', 0);
            $data = Input::all();
            if (Input::hasFile('excel')) {
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

        $rules['selection.AccountName'] = 'required';
        //$rules['selection.Email'] = 'required';
        $rules['selection.Country'] = 'required';
        $rules['selection.FirstName'] = 'required';
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);
        $temp_path = getenv('TEMP_PATH') . '/';


        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ACCOUNT_DOCUMENT']);
        $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
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
}
