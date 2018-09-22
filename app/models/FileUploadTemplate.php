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
    const TEMPLATE_CDR              = 'CDR';
    const TEMPLATE_VENDORCDR        = 'VendorCDR';
    const TEMPLATE_Account          = 'Account';
    const TEMPLATE_Leads            = 'Leads';
    const TEMPLATE_DIALSTRING       = 'DialString';
    const TEMPLATE_IPS              = 'IPs';
    const TEMPLATE_ITEM             = 'Item';
    const TEMPLATE_VENDOR_RATE      = 'VendorRate';
    const TEMPLATE_PAYMENT          = 'Payment';
    const TEMPLATE_RATETABLE_RATE   = 'RatetableRate';
    const TEMPLATE_CUSTOMER_RATE    = 'CustomerRate';

    public static function getTemplateIDList($Type){
        if(!empty($Type)) {
            $where = ['CompanyID'=>User::get_companyID(), 'FileUploadTemplateTypeID'=>$Type];
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

            $validations = FileUploadTemplate::prepareTemplateValidations($data);
            $validator = Validator::make($validations['data'], $validations['rules_for_type'], $validations['message_for_type']);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if(!empty($validations['option'])) {
                $option = $validations['option'];
            }

            $file_name = $data['TemplateFile'];
            $UploadDir = FileUploadTemplateType::getTemplateUploadDir($data['TemplateType']);
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir[$UploadDir]);
            $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
            copy($file_name, $destinationPath . basename($file_name));
            if (!AmazonS3::upload($destinationPath . basename($file_name), $amazonPath)) {
                return Response::json(array("status" => "failed", "message" => "Failed to upload."));
            }

            $save                       = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . basename($file_name)];
            $save['created_by']         = User::get_user_full_name();
            //$option["Sheet"]          = !empty($data['Sheet']) ? $data['Sheet'] : '';
            //$option["importratesheet"]  = !empty($data['importratesheet']) ? $data['importratesheet'] : '';
            $option["option"]           = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"]        = filterArrayRemoveNewLines($data['selection']);//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];

            if(isset($data['RateUploadType']) && ($data['RateUploadType'] == 'ratetable' || $data['RateUploadType'] == 'vendor')) {

                $option["skipRows"]         = array( "start_row"=>!empty($data["start_row"]) ? $data["start_row"] : 0, "end_row"=>!empty($data["end_row"]) ? $data["end_row"] : 0 );
                $option["importratesheet"]  = !empty($data['importratesheet']) ? $data['importratesheet'] : '';
                if (!empty($data['importdialcodessheet'])) {
                    $option["skipRows_sheet2"] = array("start_row" => !empty($data["start_row_sheet2"]) ? $data["start_row_sheet2"] : 0, "end_row" => !empty($data["end_row_sheet2"]) ? $data["end_row_sheet2"] : 0);
                    $option["importdialcodessheet"] = !empty($data['importdialcodessheet']) ? $data['importdialcodessheet'] : '';
                    $option["selection2"] = filterArrayRemoveNewLines($data['selection2']);
                }
                $option['Settings']['checkbox_replace_all'] = $data['checkbox_replace_all'];
                $option['Settings']['checkbox_rates_with_effected_from'] = $data['checkbox_rates_with_effected_from'];
                $option['Settings']['checkbox_add_new_codes_to_code_decks'] = $data['checkbox_add_new_codes_to_code_decks'];
                $option['Settings']['checkbox_review_rates'] = $data['checkbox_review_rates'];
                $option['Settings']['radio_list_option'] = $data['radio_list_option'];
                if($data['RateUploadType'] == RateUpload::vendor || $data['RateUploadType'] == RateUpload::customer) {
                    $option['Trunk'] = $data['Trunk'];
                } else if($data['RateUploadType'] == RateUpload::ratetable) {
                    $RateTable       = RateTable::find($data['Ratetable']);
                    $option['Trunk'] = $RateTable->TrunkID;
                }
            }
            $save['Options']          = str_replace('Skip loading','',json_encode($option));
            $save['FileUploadTemplateTypeID']               = $data['TemplateType'];

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

                $validations = FileUploadTemplate::prepareTemplateValidations($data);
                $validator = Validator::make($validations['data'], $validations['rules_for_type'], $validations['message_for_type']);

                if ($validator->fails()) {
                    return json_validator_response($validator);
                }

                if(!empty($validations['option'])) {
                    $option = $validations['option'];
                }

                if(isset($data['TemplateFile']) && !empty($data['TemplateFile'])) {
                    $file_name = $data['TemplateFile'];
                    $UploadDir = FileUploadTemplateType::getTemplateUploadDir($data['TemplateType']);
                    $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir[$UploadDir]);
                    $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
                    copy($file_name, $destinationPath . basename($file_name));
                    if (!AmazonS3::upload($destinationPath . basename($file_name), $amazonPath)) {
                        return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                    }

                    $save                   = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . basename($file_name)];
                } else {
                    $save                   = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName']];
                }

                $save['updated_by']         = User::get_user_full_name();
                //$option["Sheet"]          = !empty($data['Sheet']) ? $data['Sheet'] : '';
                $option["option"]           = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
                $option["selection"]        = filterArrayRemoveNewLines($data['selection']);//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
                if(isset($data['RateUploadType']) && ($data['RateUploadType'] == RateUpload::ratetable || $data['RateUploadType'] == RateUpload::vendor || $data['RateUploadType'] == RateUpload::customer)) {
                    $option["skipRows"]         = array( "start_row"=>!empty($data["start_row"]) ? $data["start_row"] : 0, "end_row"=>!empty($data["end_row"]) ? $data["end_row"] : 0 );
                    $option["importratesheet"] = !empty($data['importratesheet']) ? $data['importratesheet'] : '';
                    if (!empty($data['importdialcodessheet'])) {
                        $option["skipRows_sheet2"] = array("start_row" => !empty($data["start_row_sheet2"]) ? $data["start_row_sheet2"] : 0, "end_row" => !empty($data["end_row_sheet2"]) ? $data["end_row_sheet2"] : 0);
                        $option["importdialcodessheet"] = !empty($data['importdialcodessheet']) ? $data['importdialcodessheet'] : '';
                        $option["selection2"] = filterArrayRemoveNewLines($data['selection2']);
                    }
                    $option['Settings']['checkbox_replace_all'] = $data['checkbox_replace_all'];
                    $option['Settings']['checkbox_rates_with_effected_from'] = $data['checkbox_rates_with_effected_from'];
                    $option['Settings']['checkbox_add_new_codes_to_code_decks'] = $data['checkbox_add_new_codes_to_code_decks'];
                    $option['Settings']['checkbox_review_rates'] = $data['checkbox_review_rates'];
                    $option['Settings']['radio_list_option'] = $data['radio_list_option'];

                    if($data['RateUploadType'] == RateUpload::vendor || $data['RateUploadType'] == RateUpload::customer) {
                        $option['Trunk'] = $data['Trunk'];
                    } else if($data['RateUploadType'] == RateUpload::ratetable) {
                        $RateTable       = RateTable::find($data['Ratetable']);
                        $option['Trunk'] = $RateTable->TrunkID;
                    }
                }
                $save['Options']            = str_replace('Skip loading','',json_encode($option));
                $save['FileUploadTemplateTypeID']               = $data['TemplateType'];

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
        $data = json_decode(str_replace('Skip loading','',json_encode($data,true)),true);
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
        }else if($data['TemplateType'] == 8 || $data['TemplateType'] == 10 || $data['TemplateType'] == 11) { //vendor rate / RateTable Rate / Customer Rate Respectively

            if(!empty($data['importdialcodessheet'])) {
                $rules_for_type['selection.Join1'] = 'required';
                $rules_for_type['selection2.Join2'] = 'required';
                $rules_for_type['selection.Code'] = 'required_without:selection2.Code';
                $rules_for_type['selection2.Code'] = 'required_without:selection.Code';
                $rules_for_type['selection.Description'] = 'required_without:selection2.Description';
                $rules_for_type['selection2.Description'] = 'required_without:selection.Description';

                $message_for_type['selection.Join1.required'] = "Please Select Match Codes with DialCode On For Ratesheet";
                $message_for_type['selection2.Join2.required'] = "Please Select Match Codes with Rates On For DialCodeSheet";
                $message_for_type['selection.Code.required_without'] = "Code field is required of sheet1 when Code is not present of sheet2";
                $message_for_type['selection2.Code.required_without'] = "Code field is required of sheet2 when Code is not present of sheet1";
                $message_for_type['selection.Description.required_without'] = "Description field is required of sheet1 when Description is not present of sheet2";
                $message_for_type['selection2.Description.required_without'] = "Description field is required of sheet2 when Description is not present of sheet1";
                $option["skipRows_sheet2"] = array("start_row" => $data["start_row_sheet2"], "end_row" => $data["end_row_sheet2"]);
            }else{
                $rules_for_type['selection.Code']        = 'required';
                $rules_for_type['selection.Description'] = 'required';
                $message_for_type['selection.Code.required'] = "Code Field is required";
                $message_for_type['selection.Description.required'] = "Description Field is required";
            }
            
            $Timezones = Timezones::getTimezonesIDList(1);//no default timezones, only user defined timezones
            if(count($Timezones) > 0) { // if there are any timezones available
                $TimezonesIDsArray = array();
                foreach ($Timezones as $ID => $Title) {
                    $TimezonesIDsArray[] = 'selection.Rate'.$ID;
                }
                $TimezonesIDsString = implode(',',$TimezonesIDsArray);

                $rules_for_type['selection.Rate']                        = 'required_without_all:'.$TimezonesIDsString;
                $message_for_type['selection.Rate.required_without_all'] = "Please select Rate against at least any one timezone.";
                $TimezonesIDsArray[] = 'selection.Rate';
                foreach ($Timezones as $ID => $Title) {
                    $TimezonesIDsString = implode(',',array_diff($TimezonesIDsArray, array('selection.Rate'.$ID)));
                    $rules_for_type['selection.Rate'.$ID]                           = 'required_without_all:'.$TimezonesIDsString;
                    $message_for_type['selection.Rate'.$ID.'.required_without_all'] = "Please select Rate against at least any one timezone.";
                }
            } else { // if there is only 1 timezone, default timezone
                $rules_for_type['selection.Rate']            = 'required';
                $message_for_type['selection.Rate.required'] = "Rate Field is required";
            }
            
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
        $result['data'] = $data;
        if(isset($option)) {
            $result['option'] = $option;
        }

        return $result;
    }

}