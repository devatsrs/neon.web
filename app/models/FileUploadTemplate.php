<?php
/**
 * Created by PhpStorm.
 * User: srs2
 * Date: 19/09/2015
 * Time: 02:27 
 */

class FileUploadTemplate extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblFileUploadTemplate';
    protected $primaryKey = "FileUploadTemplateID";
    const TEMPLATE_CDR = 1;
    const TEMPLATE_VENDORCDR = 2;
    const TEMPLATE_Account = 3;
    const TEMPLATE_Leads = 4;
    const TEMPLATE_DIALSTRING = 5;
    const TEMPLATE_IPS = 6;
    const TEMPLATE_ITEM = 7;
    const TEMPLATE_VENDOR_RATE = 8;
    const TEMPLATE_PAYMENT = 9;

    public static $template_type = array(
                                        1=>'CDR',
                                        2=>'Vendor CDR',
                                        3=>'Account',
                                        4=>'Leads',
                                        5=>'DialString',
                                        6=>'IPs',
                                        7=>'Item',
                                        8=>'Vendor Rate',
                                        9=>'Payment',
                                    );

    public static $upload_dir = array(
                                        1=>'CDR_UPLOAD',
                                        2=>'CDR_UPLOAD',
                                        3=>'ACCOUNT_DOCUMENT',
                                        4=>'ACCOUNT_DOCUMENT',
                                        5=>'DIALSTRING_UPLOAD',
                                        6=>'IP_UPLOAD',
                                        7=>'ITEM_UPLOAD',
                                        8=>'VENDOR_UPLOAD',
                                        9=>'PAYMENT_UPLOAD',
                                    );

    public static function getTemplateIDList($Type){
        if(!empty($Type)) {
            $where = ['CompanyID'=>User::get_companyID(), 'Type'=>$Type];
        } else {
            $where = ['CompanyID'=>User::get_companyID()];
        }
        $row = FileUploadTemplate::where($where)->orderBy('Title')->lists('Title', 'FileUploadTemplateID');
        $row = array(""=> "Select")+$row;
        return $row;
    }

    public static function createOrUpdateFileUploadTemplate($data){
        $response = array();
        $CompanyID = User::get_companyID();

        if(empty($data['FileUploadTemplateID'])) { //create template
            $rules['TemplateName']          = 'required|unique:tblFileUploadTemplate,Title,NULL,FileUploadTemplateID';
            $rules['TemplateFile']          = 'required';
            $rules['TemplateType']          = 'required';

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            $validations = SELF::prepareTemplateValidations($data);
            $validator = Validator::make($data, $validations['rules_for_type'], $validations['message_for_type']);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if(!empty($validations['option'])) {
                $option = $validations['option'];
            }

            $file_name = $data['TemplateFile'];
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir[SELF::$upload_dir[$data['TemplateType']]]);
            $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
            copy($file_name, $destinationPath . basename($file_name));
            if (!AmazonS3::upload($destinationPath . basename($file_name), $amazonPath)) {
                return Response::json(array("status" => "failed", "message" => "Failed to upload."));
            }

            $save                   = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . basename($file_name)];
            $save['created_by']     = User::get_user_full_name();
            $option["option"]       = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"]    = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options']        = json_encode($option);
            $save['Type']           = $data['TemplateType'];

            try {
                if ($result = FileUploadTemplate::create($save)) {
                    $response['status']     = "success";
                    $response['message']    = "Template Successfully Created.";
                    $response['Template']   = $result;
                    $response['file_name']  = basename($file_name);
                } else {
                    $response['status'] = "failed";
                    $response['message'] = "Error while creating template.";
                }
            } catch (Exception $e) {
                $response['status'] = "failed";
                $response['message'] = "Error while creating template. Exception:".$e->getMessage();
            }
        } else { //update template
            $template = FileUploadTemplate::find($data['FileUploadTemplateID']);

            if($template) {
                $rules["TemplateName"]  = 'required|unique:tblFileUploadTemplate,Title,' . $data['FileUploadTemplateID'] . ',FileUploadTemplateID';
                $rules['TemplateType']  = 'required';

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return json_validator_response($validator);
                }

                $validations = SELF::prepareTemplateValidations($data);
                $validator = Validator::make($data, $validations['rules_for_type'], $validations['message_for_type']);

                if ($validator->fails()) {
                    return json_validator_response($validator);
                }

                if(!empty($validations['option'])) {
                    $option = $validations['option'];
                }

                if(isset($data['TemplateFile']) && !empty($data['TemplateFile'])) {
                    $file_name = $data['TemplateFile'];
                    $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir[SELF::$upload_dir[$data['TemplateType']]]);
                    $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
                    copy($file_name, $destinationPath . basename($file_name));
                    if (!AmazonS3::upload($destinationPath . basename($file_name), $amazonPath)) {
                        return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                    }

                    $save                   = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . basename($file_name)];
                } else {
                    $save                   = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName']];
                }

                $save['updated_by']     = User::get_user_full_name();
                $option["option"]       = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
                $option["selection"]    = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
                $save['Options']        = json_encode($option);
                $save['Type']           = $data['TemplateType'];

                try {
                    if ($template->update($save)) {
                        $response['status']     = "success";
                        $response['message']    = "Template Successfully Updated.";
                        $response['Template']   = $template;
                        $response['file_name']  = basename($file_name);
                    } else {
                        $response['status'] = "failed";
                        $response['message'] = "Error while updating template.";
                    }
                } catch (Exception $e) {
                    $response['status'] = "failed";
                    $response['message'] = "Error while updating template. Exception:".$e->getMessage();
                }
            } else {
                $response["status"]  = "failed";
                $response["message"] = "Template not found.";
            }
        }

        return $response;
    }

    public static function prepareTemplateValidations($data) {
        $rules_for_type = $message_for_type = [];

        if($data['TemplateType'] == 1) { //customer cdr
            $rules_for_type['selection.Account']                            = 'required';
            $rules_for_type['selection.connect_datetime']                   = 'required';
            $rules_for_type['selection.billed_duration']                    = 'required';
            $rules_for_type['selection.cld']                                = 'required';
            $message_for_type['selection.Account.required']                 = 'The account field is required';
            $message_for_type['selection.connect_datetime.required']        = 'The connect datetime field is required';
            $message_for_type['selection.billed_duration.required']         = 'The billed duration field is required';
            $message_for_type['selection.cld.required']                     = 'The cld field is required';
        }else if($data['TemplateType'] == 2) { //vendor cdr
            //No validation
        }else if($data['TemplateType'] == 3) { //account
            Account::$importrules['selection.AccountName']                  = 'required';
            $rules_for_type   = Account::$importrules;
            $message_for_type = Account::$importmessages;
        }else if($data['TemplateType'] == 4) { //leads
            Account::$importleadrules['selection.AccountName']              = 'required';
            Account::$importleadrules['selection.FirstName']                = 'required';
            Account::$importleadrules['selection.LastName']                 = 'required';
            $rules_for_type   = Account::$importleadrules;
            $message_for_type = Account::$importleadmessages;
        }else if($data['TemplateType'] == 5) { //dialstrings
            DialStringCode::$DialStringUploadrules['selection.DialString']  = 'required';
            DialStringCode::$DialStringUploadrules['selection.ChargeCode']  = 'required';
            $rules_for_type   = DialStringCode::$DialStringUploadrules;
            $message_for_type = DialStringCode::$DialStringUploadMessages;
        }else if($data['TemplateType'] == 6) { //ips
            $rules_for_type['selection.AccountName']                        = 'required';
            $rules_for_type['selection.IP']                                 = 'required';
            $rules_for_type['selection.Type']                               = 'required';
            $message_for_type['selection.AccountName.required']             = 'Account Name Field is required';
            $message_for_type['selection.IP.required']                      = 'IP Field is required';
            $message_for_type['selection.Type.required']                    = 'Type Field is required';
        }else if($data['TemplateType'] == 7) { //item
            $rules_for_type['selection.Name']                               = 'required';
            $rules_for_type['selection.Code']                               = 'required';
            $rules_for_type['selection.Description']                        = 'required';
            $rules_for_type['selection.Amount']                             = 'required';
        }else if($data['TemplateType'] == 8) { //vendor rate
            $rules_for_type['selection.Code']                               = 'required';
            $rules_for_type['selection.Description']                        = 'required';
            $rules_for_type['selection.Rate']                               = 'required';
            $option["skipRows"] = array("start_row" => $data["start_row"], "end_row" => $data["end_row"]);
        }else if($data['TemplateType'] == 9) { //payment
            Payment::$importpaymentrules['selection.AccountName']           = 'required';
            Payment::$importpaymentrules['selection.PaymentDate']           = 'required';
            Payment::$importpaymentrules['selection.PaymentMethod']         = 'required';
            Payment::$importpaymentrules['selection.PaymentType']           = 'required';
            Payment::$importpaymentrules['selection.Amount']                = 'required';
            $rules_for_type   = Payment::$importpaymentrules;
            $message_for_type = Payment::$importpaymentmessages;
        }

        $result['rules_for_type']   = $rules_for_type;
        $result['message_for_type'] = $message_for_type;
        if(isset($option)) {
            $result['option'] = $option;
        }

        return $result;
    }

}