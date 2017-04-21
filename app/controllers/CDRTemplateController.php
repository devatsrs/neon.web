<?php

class CDRTemplateController extends BaseController {

    
    public function __construct() {

    }


    /** CDR Upload
     * @return mixed
     */
    public function index() {
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_CDR);
        $trunks = Trunk::getTrunkDropdownIDList();
        $trunks = $trunks+array(0=>'Find From CustomerPrefix');
        return View::make('cdrtemplate.upload',compact('dashboardData','account','gateway','UploadTemplate','trunks'));
    }

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
    public function storeTemplate() {
        $data = Input::all();
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
        $rules['Account'] = 'required';
        $rules['Authentication'] = 'required';
        $rules['connect_datetime'] = 'required';
        $rules['billed_duration'] = 'required';
        $rules['cld'] = 'required';
        if($data['RateFormat'] == Company::CHARGECODE) {
            $rules['ChargeCode'] = 'required';
        }
        if(!empty($data['selection']['ChargeCode'])){
            $data['ChargeCode'] = $data['selection']['ChargeCode'];
        }
        if(!empty($data['selection']['Account'])){
            $data['Account'] = $data['selection']['Account'];
        }
        if(!empty($data['selection']['Authentication'])){
            $data['Authentication'] = $data['selection']['Authentication'];
        }
        if(!empty($data['selection']['connect_datetime'])){
            $data['connect_datetime'] = $data['selection']['connect_datetime'];
        }
        if(!empty($data['selection']['billed_duration'])){
            $data['billed_duration'] = $data['selection']['billed_duration'];
        }
        if(!empty($data['selection']['cld'])){
            $data['cld'] = $data['selection']['cld'];
        }
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = CompanyConfiguration::get('TEMP_PATH').'/';
        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TEMPLATE_FILE']);
        $amazonCDRPath = AmazonS3::generate_upload_path(AmazonS3::$dir['CDR_UPLOAD']);
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        $destinationCDRPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonCDRPath;
        copy($temp_path.$file_name,$destinationPath.$file_name);
        copy($temp_path.$file_name,$destinationCDRPath.$file_name);
        if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
            return Response::json(array("status" => "failed", "message" => "Failed to upload template sample file."));
        }
        $save = ['CompanyID'=>$CompanyID,'Title'=>$data['TemplateName'],'TemplateFile'=>$amazonPath.$file_name];
        $save['created_by'] = User::get_user_full_name();
        $option["option"]= $data['option'];//['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
        $option["selection"] = $data['selection'];//['connect_time'=>$data['connect_time'],'disconnect_time'=>$data['disconnect_time'],'billed_duration'=>$data['billed_duration'],'duration'=>$data['duration'],'cld'=>$data['cld'],'cli'=>$data['cli'],'Account'=>$data['Account'],'cost'=>$data['cost']];
        $save['Options'] = json_encode($option);
        $save['Type'] = FileUploadTemplate::TEMPLATE_CDR;
        if(isset($data['FileUploadTemplateID']) && $data['FileUploadTemplateID']>0) {
            $template = FileUploadTemplate::find($data['FileUploadTemplateID']);
            $template->update($save);
            return Response::json(array("status" => "success", "message" => "CDR Template successfully Updated"));
        }else {/**/
            $template = FileUploadTemplate::create($save);
            if ($template) {
                return Response::json(array("status" => "success", "message" => "CDR Template successfully added"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Template."));
            }
        }
    }

    public function check_upload()
    {
        try {
            $data = Input::all();
            $rules = array(
                'CompanyGatewayID' => 'required',
            );
            if($data['RateCDR']){
                $rules['TrunkID'] = 'required';
                $rules['RateFormat'] = 'required';
            }
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array($ext, array("csv", "xls", "xlsx"))) {
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
}
