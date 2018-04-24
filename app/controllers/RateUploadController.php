<?php

class RateUploadController extends \BaseController {

    public function index($id=0,$RateUploadType='') {
        $VendorID = $CustomerID = $RatetableID = 0;

        if($RateUploadType == RateUpload::vendor) {
            $VendorID       = $id;
        } else if($RateUploadType == RateUpload::customer) {
            $CustomerID     = $id;
        } else if($RateUploadType == RateUpload::ratetable) {
            $RatetableID    = $id;
        }

        if($RateUploadType == '') { //default upload type
            $RateUploadType = RateUpload::vendor;
        }

        $Vendors            = Account::getOnlyVendorIDList();
        $Ratetables         = RateTable::getRateTableList();    unset($Ratetables[array_search('Select',$Ratetables)]);
        $Customers          = Account::getOnlyCustomerIDList();
        $dialstring         = DialString::getDialStringIDList();
        $currencies         = Currency::getCurrencyDropdownIDList();
        $uploadtypes        = RateUpload::$uploadtypes;

        return View::make('rateupload.index', compact('Vendors','Customers','Ratetables','VendorID','CustomerID','RatetableID','dialstring','currencies','uploadtypes','RateUploadType','id'));
    }

    public function getUploadTemplates($RateUploadType) {
        $response = array();

        if($RateUploadType == RateUpload::vendor) {
            $TemplateType = FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_VENDOR_RATE);
        } else if($RateUploadType == RateUpload::customer) {
            $TemplateType = FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_CUSTOMER_RATE);
        } else if($RateUploadType == RateUpload::ratetable) {
            $TemplateType = FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_RATETABLE_RATE);
        }

        $arrData = FileUploadTemplate::where(['CompanyID'=>User::get_companyID(),'FileUploadTemplateTypeID'=>$TemplateType])->orderBy('Title')->get(['Title', 'FileUploadTemplateID', 'Options'])->toArray();

        $uploadtemplate=[];
        $uploadtemplate[]=[
            "Title" => "Select",
            "FileUploadTemplateID" => "",
            "start_row" => "",
            "end_row" => ""
        ];

        foreach($arrData as $val)
        {
            $arrUploadTmp=[];
            $arrUploadTmp["Title"]=$val["Title"];
            $arrUploadTmp["FileUploadTemplateID"]=$val["FileUploadTemplateID"];

            $options=json_decode($val["Options"], true);

            if(array_key_exists("skipRows", $options)) {
                $arrUploadTmp["start_row"]=$options["skipRows"]["start_row"];
                $arrUploadTmp["end_row"]=$options["skipRows"]["end_row"];
            } else {
                $arrUploadTmp["start_row"]="0";
                $arrUploadTmp["end_row"]="0";
            }
            if(array_key_exists("Sheet", $options)) {
                $arrUploadTmp["Sheet"]=$options["Sheet"];
            } else {
                $arrUploadTmp["Sheet"]="";
            }
            $uploadtemplate[]=$arrUploadTmp;
        }

        $response['status']              = 'success';
        $response['FileUploadTemplates'] = $uploadtemplate;

        return json_encode($response);
    }

    public function getTrunk($Type) {
        $response = array();

        $data       = Input::all();
        $trunks     = array();

        if(!empty($data['id'])) {
            $id = $data['id'];

            if ($Type == RateUpload::vendor) {
                $trunks     = VendorTrunk::getTrunkDropdownIDList($id);
            } else if ($Type == RateUpload::ratetable) {
                /*$rateTable  = RateTable::where(["RateTableId" => $id])->get(array('TrunkID', 'CodeDeckId'));
                $TrunkID    = !empty($rateTable[0]->TrunkID) ? $rateTable[0]->TrunkID : 0;
                $CodeDeckID = $rateTable[0]->CodeDeckId;*/
            } else if ($Type == RateUpload::customer) {
                $trunks     = CustomerTrunk::getTrunkDropdownIDList($id);
            }
        }

        $response['status']             = 'success';
        $response['Type']               = $Type;
        $response['trunks']             = $trunks;

        return json_encode($response);
    }

    public function checkUpload() {
        try {
            $Sheet  = '';
            $data   = Input::all();
            if(!empty($data['Sheet'])) {
                $Sheet = $data['Sheet'];
            }
            if ($data['RateUploadType'] == RateUpload::vendor && (!isset($data['Trunk']) || empty($data['Trunk']))) {
                return json_encode(["status" => "failed", "message" => 'Please Select a Trunk']);
            } else if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                    $file_name_without_ext = GUID::generate();
                    $file_name = $file_name_without_ext . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;

                    if (!empty($data['checkbox_review_rates']) && $data['checkbox_review_rates'] == 1) {
                        $NeonExcel = new NeonExcelIO($file_name, $data, $Sheet);
                        $file_name = $NeonExcel->convertExcelToCSV($data);
                    }
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else if (isset($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if (!empty($file_name)) {
                if ($data['uploadtemplate'] > 0) {
                    $FileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($FileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data, $Sheet);
                $grid['tempfilename'] = $file_name;//$upload_path.'\\'.'temp.'.$ext;
                $grid['filename'] = $file_name;
                $grid['start_row'] = $data["start_row"];
                $grid['end_row'] = $data["end_row"];

                $TemplateType = '';
                if ($data['RateUploadType'] == RateUpload::vendor) {
                    $TemplateType = FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_VENDOR_RATE);
                } else if ($data['RateUploadType'] == RateUpload::customer) {
                    $TemplateType = FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_CUSTOMER_RATE);
                } else if ($data['RateUploadType'] == RateUpload::ratetable) {
                    $TemplateType = FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_RATETABLE_RATE);
                }

                $grid['RateUploadType'] = $data['RateUploadType'];
                $grid['TemplateType']   = $TemplateType;

                if (!empty($FileUploadTemplate)) {
                    $grid['FileUploadTemplate'] = json_decode(json_encode($FileUploadTemplate), true);
                    $grid['FileUploadTemplate']['Options'] = json_decode($FileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        } catch (Exception $ex) {
            Log::info($ex);
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function ajaxfilegrid(){
        try {
            $data = Input::all();
            $data['Delimiter']      = $data['option']['Delimiter'];
            $data['Enclosure']      = $data['option']['Enclosure'];
            $data['Escape']         = $data['option']['Escape'];
            $data['Firstrow']       = $data['option']['Firstrow'];
            $file_name              = $data['TempFileName'];
            $grid                   = getFileContent($file_name, $data);
            $grid['filename']       = $data['TemplateFile'];
            $grid['tempfilename']   = $data['TempFileName'];

            if ($data['uploadtemplate'] > 0) {
                $FileUploadTemplate         = FileUploadTemplate::find($data['uploadtemplate']);
                $grid['FileUploadTemplate'] = json_decode(json_encode($FileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }

            $grid['FileUploadTemplate']['Options']              = array();
            $grid['FileUploadTemplate']['Options']['option']    = $data['option'];
            $grid['FileUploadTemplate']['Options']['selection'] = $data['selection'];

            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function storeTemplate() {
        $data = Input::all();//echo "<pre>";print_r($data);exit();
        $CompanyID = User::get_companyID();

        $id = '';
        if($data['RateUploadType'] == RateUpload::vendor) {
            $id = $data['Vendor'];
        } else if($data['RateUploadType'] == RateUpload::customer) {
            $id = $data['Customer'];
        } else if($data['RateUploadType'] == RateUpload::ratetable) {
            $id = $data['Ratetable'];
        }

        if(isset($data['selection']['FromCurrency']) && !empty($data['selection']['FromCurrency'])) {
            $CompanyCurrency = Company::find($CompanyID)->CurrencyId;

            $error = array();
            if(!($CompanyCurrency && !empty($CompanyCurrency))) {
                $error['status']    = "failed";
                $error['message']   = "You have not setup your base currency, please select it under company page if you want to convert rates.<br/>";
            } else {
                $CompanyConversionRate  = CurrencyConversion::where(['CurrencyID' => $CompanyCurrency, 'CompanyID' => $CompanyID])->count();
                $FileConversionRate     = CurrencyConversion::where(['CurrencyID' => $data['selection']['FromCurrency'], 'CompanyID' => $CompanyID])->count();

                if($data['RateUploadType'] == RateUpload::vendor) {
                    $TypeCID        = Account::find($id)->CurrencyId;
                    $ConversionRate = CurrencyConversion::where(['CurrencyID' => $TypeCID, 'CompanyID' => $CompanyID])->count();
                } else if($data['RateUploadType'] == RateUpload::customer) {
                    $TypeCID        = Account::find($id)->CurrencyId;
                    $ConversionRate = CurrencyConversion::where(['CurrencyID' => $TypeCID, 'CompanyID' => $CompanyID])->count();
                } else if($data['RateUploadType'] == RateUpload::ratetable) {
                    $TypeCID        = RateTable::find($id)->CurrencyID;
                    $ConversionRate = CurrencyConversion::where(['CurrencyID' => $TypeCID, 'CompanyID' => $CompanyID])->count();
                }

                $error['message'] = "";
                $CurrencyCode = array();
                if(empty($CompanyConversionRate)) {
                    $CurrencyCode[] = Currency::find($CompanyCurrency)->Code;
                }
                if(empty($FileConversionRate)) {
                    $CurrencyCode[] = Currency::find($data['selection']['FromCurrency'])->Code;
                }
                if(empty($ConversionRate)) {
                    $CurrencyCode[] = Currency::find($TypeCID)->Code;
                }

                if(count($CurrencyCode) > 0) {
                    $CurrencyCode    = array_unique($CurrencyCode);
                    $error['status'] = "failed";

                    foreach ($CurrencyCode as $Code) {
                        $error['message'] .= "You have not setup your currency (".$Code.") conversion rate, please set it up under setting -> exchange rate.<br/>";
                    }
                }
            }

            if(isset($error['status']) && $error['status'] == 'failed') {
                return json_encode($error);
            }
        }

        $dir = $jobtype = '';
        if($data['RateUploadType'] == RateUpload::vendor) {
            $VendorTrunk        = VendorTrunk::where(["AccountID" => $id, 'TrunkID' => $data['Trunk']])->first();
            $data['codedeckid'] = $VendorTrunk->CodeDeckId;
            $dir                = 'VENDOR_UPLOAD';
        } else if($data['RateUploadType'] == RateUpload::customer) {
            $CustomerTrunk      = CustomerTrunk::where(["AccountID" => $id, 'TrunkID' => $data['Trunk']])->first();
            $data['codedeckid'] = $CustomerTrunk->CodeDeckId;
            $dir                = 'CUSTOMER_UPLOAD';
        } else if($data['RateUploadType'] == RateUpload::ratetable) {
            $RateTable          = RateTable::find($id);
            $data['codedeckid'] = $RateTable->CodeDeckId;
            $dir                = 'RATETABLE_UPLOAD';
        }

        if ($data['RateUploadType'] == RateUpload::vendor && (!isset($data['codedeckid']) || empty($data['codedeckid']))) {
            return json_encode(["status" => "failed", "message" => 'Please Update a Codedeck in Setting']);
        }

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir[$dir]);

        if(!empty($data['TemplateName'])){
            if(!empty($data['uploadtemplate'])) {
                $data['FileUploadTemplateID'] = $data['uploadtemplate'];
            }
            $uploadresult = FileUploadTemplate::createOrUpdateFileUploadTemplate($data);

            if(is_object($uploadresult)) {
                return $uploadresult;
            } else if (!empty($uploadresult['status']) && $uploadresult['status'] == "failed") {
                return Response::json($uploadresult);
            } else if (!empty($uploadresult['status']) && $uploadresult['status'] == "success") {
                $template               = $uploadresult['Template'];
                $data['uploadtemplate'] = $template->FileUploadTemplateID;
                $file_name              = $uploadresult['file_name'];
            }
        } else {
            $rules['selection.Code']        = 'required';
            $rules['selection.Description'] = 'required';
            $rules['selection.Rate']        = 'required';
            //$rules['selection.EffectiveDate'] = 'required';
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $file_name          = basename($data['TemplateFile']);
            $temp_path          = CompanyConfiguration::get('TEMP_PATH').'/' ;
            $destinationPath    = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
            copy($temp_path . $file_name, $destinationPath . $file_name);
            if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                return Response::json(array("status" => "failed", "message" => "Failed to upload rate file."));
            }
        }
        $option["skipRows"] = array( "start_row"=>$data["start_row"], "end_row"=>$data["end_row"] );
        $option["Sheet"]    = !empty($data['Sheet']) ? $data['Sheet'] : '';

        $save = array();
        $option["option"]       = $data['option'];
        $option["selection"]    = $data['selection'];
        $save['Options']        = str_replace('Skip loading','',json_encode($option));//json_encode($option);
        $fullPath               = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path']      = $fullPath;

        if($data['RateUploadType'] == RateUpload::vendor) {
            $save["AccountID"]      = $id;
            $save['Trunk']          = $data['Trunk'];
            $jobtype                = 'VU';
        } else if($data['RateUploadType'] == RateUpload::customer) {
            $save["AccountID"]      = $id;
            $save['Trunk']          = $data['Trunk'];
            $jobtype                = 'CU';
        } else if($data['RateUploadType'] == RateUpload::ratetable) {
            $RateTable              = RateTable::find($id);
            $save["RateTableID"]    = $id;
            $save['Trunk']          = $RateTable->TrunkID;
            $save['ratetablename']  = $RateTable->RateTableName;
            $jobtype                = 'RTU';
        }

        $save['codedeckid']     = $data['codedeckid'];
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }

        $save['checkbox_replace_all']                   = $data['checkbox_replace_all'];
        $save['checkbox_rates_with_effected_from']      = $data['checkbox_rates_with_effected_from'];
        $save['checkbox_add_new_codes_to_code_decks']   = $data['checkbox_add_new_codes_to_code_decks'];
        $save['checkbox_review_rates']                  = $data['checkbox_review_rates'];
        $save['radio_list_option']                      = $data['radio_list_option'];
        if(!empty($data['ProcessID'])) {
            $save['ProcessID'] = $data['ProcessID'];
        }

        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob($jobtype, $save);
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

    //if you change anything in this method then you need to change also in VendorRateUpload.php and RateTableRateUpload.php in service
    public function reviewRates() {
        $data               = Input::all();
        $CompanyID          = User::get_companyID();
        $ProcessID          = (string) GUID::generate();
        $bacth_insert_limit = 250;
        $counter            = 0;
        $p_forbidden        = 0;
        $p_preference       = 0;
        $DialStringId       = 0;
        $dialcode_separator = 'null';

        $id = '';
        if($data['RateUploadType'] == RateUpload::vendor) {
            $id = $data['Vendor'];
        } else if($data['RateUploadType'] == RateUpload::customer) {
            $id = $data['Customer'];
        } else if($data['RateUploadType'] == RateUpload::ratetable) {
            $id = $data['Ratetable'];
        }

        if(isset($data['selection']['FromCurrency']) && !empty($data['selection']['FromCurrency'])) {
            $CompanyCurrency = Company::find($CompanyID)->CurrencyId;

            $error = array();
            if(!($CompanyCurrency && !empty($CompanyCurrency))) {
                $error['status']    = "failed";
                $error['message']   = "You have not setup your base currency, please select it under company page if you want to convert rates.<br/>";
            } else {
                $CompanyConversionRate  = CurrencyConversion::where(['CurrencyID' => $CompanyCurrency, 'CompanyID' => $CompanyID])->count();
                $FileConversionRate     = CurrencyConversion::where(['CurrencyID' => $data['selection']['FromCurrency'], 'CompanyID' => $CompanyID])->count();

                if($data['RateUploadType'] == RateUpload::vendor) {
                    $TypeCID        = Account::find($id)->CurrencyId;
                    $ConversionRate = CurrencyConversion::where(['CurrencyID' => $TypeCID, 'CompanyID' => $CompanyID])->count();
                } else if($data['RateUploadType'] == RateUpload::customer) {
                    $TypeCID        = Account::find($id)->CurrencyId;
                    $ConversionRate = CurrencyConversion::where(['CurrencyID' => $TypeCID, 'CompanyID' => $CompanyID])->count();
                } else if($data['RateUploadType'] == RateUpload::ratetable) {
                    $TypeCID        = RateTable::find($id)->CurrencyID;
                    $ConversionRate = CurrencyConversion::where(['CurrencyID' => $TypeCID, 'CompanyID' => $CompanyID])->count();
                }

                $error['message'] = "";
                $CurrencyCode = array();
                if(empty($CompanyConversionRate)) {
                    $CurrencyCode[] = Currency::find($CompanyCurrency)->Code;
                }
                if(empty($FileConversionRate)) {
                    $CurrencyCode[] = Currency::find($data['selection']['FromCurrency'])->Code;
                }
                if(empty($ConversionRate)) {
                    $CurrencyCode[] = Currency::find($TypeCID)->Code;
                }

                if(count($CurrencyCode) > 0) {
                    $CurrencyCode    = array_unique($CurrencyCode);
                    $error['status'] = "failed";

                    foreach ($CurrencyCode as $Code) {
                        $error['message'] .= "You have not setup your currency (".$Code.") conversion rate, please set it up under setting -> exchange rate.<br/>";
                    }
                }
            }

            if(isset($error['status']) && $error['status'] == 'failed') {
                return json_encode($error);
            }
        }

        $dir = $jobtype = '';
        if($data['RateUploadType'] == RateUpload::vendor) {
            $VendorTrunk        = VendorTrunk::where(["AccountID" => $id, 'TrunkID' => $data['Trunk']])->first();
            $data['codedeckid'] = $VendorTrunk->CodeDeckId;
            $dir                = 'VENDOR_UPLOAD';
        } else if($data['RateUploadType'] == RateUpload::customer) {
            $CustomerTrunk      = CustomerTrunk::where(["AccountID" => $id, 'TrunkID' => $data['Trunk']])->first();
            $data['codedeckid'] = $CustomerTrunk->CodeDeckId;
            $dir                = 'CUSTOMER_UPLOAD';
        } else if($data['RateUploadType'] == RateUpload::ratetable) {
            $RateTable          = RateTable::find($id);
            $data['codedeckid'] = $RateTable->CodeDeckId;
            $dir                = 'RATETABLE_UPLOAD';
        }

        if ($data['RateUploadType'] == RateUpload::vendor && (!isset($data['codedeckid']) || empty($data['codedeckid']))) {
            return json_encode(["status" => "failed", "message" => 'Please Update a Codedeck in Setting']);
        }

        $amazonPath             = AmazonS3::generate_upload_path(AmazonS3::$dir[$dir]);
        $FileUploadTemplateID   = "";
        $temp_path              = CompanyConfiguration::get('TEMP_PATH').'/' ;

        if(!empty($data['TemplateName'])){
            if(!empty($data['uploadtemplate'])) {
                $data['FileUploadTemplateID'] = $data['uploadtemplate'];
            }
            $uploadresult = FileUploadTemplate::createOrUpdateFileUploadTemplate($data);

            if(is_object($uploadresult)) {
                return $uploadresult;
            } else if (!empty($uploadresult['status']) && $uploadresult['status'] == "failed") {
                return Response::json($uploadresult);
            } else if (!empty($uploadresult['status']) && $uploadresult['status'] == "success") {
                $template               = $uploadresult['Template'];
                $data['uploadtemplate'] = $FileUploadTemplateID = $template->FileUploadTemplateID;
                $file_name              = $uploadresult['file_name'];
            }
        } else {
            $rules['selection.Code']        = 'required';
            $rules['selection.Description'] = 'required';
            $rules['selection.Rate']        = 'required';

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $file_name          = basename($data['TemplateFile']);
            $destinationPath    = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
            copy($temp_path . $file_name, $destinationPath . $file_name);
            if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                return Response::json(array("status" => "failed", "message" => "Failed to upload rate file."));
            }
        }
        $option["skipRows"] = array( "start_row"=>$data["start_row"], "end_row"=>$data["end_row"] );
        $option["Sheet"]    = !empty($data['Sheet']) ? $data['Sheet'] : '';

        $save = array();
        $option["option"]       =  $data['option'];
        $option["selection"]    = $data['selection'];
        $save['Options']        = str_replace('Skip loading','',json_encode($option));//json_encode($option);
        $fullPath               = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path']      = $fullPath;

        if($data['RateUploadType'] == RateUpload::vendor) {
            $save["AccountID"]      = $id;
            $save['Trunk']          = $data['Trunk'];
            $jobtype                = 'VU';
        } else if($data['RateUploadType'] == RateUpload::customer) {
            $save["AccountID"]      = $id;
            $save['Trunk']          = $data['Trunk'];
            $jobtype                = 'CU';
        } else if($data['RateUploadType'] == RateUpload::ratetable) {
            $RateTable              = RateTable::find($id);
            $save["RateTableID"]    = $id;
            $save['Trunk']          = $RateTable->TrunkID;
            $save['ratetablename']  = $RateTable->RateTableName;
            $jobtype                = 'RTU';
        }

        $save['codedeckid']     = $data['codedeckid'];
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }

        $save['checkbox_replace_all']                   = $data['checkbox_replace_all'];
        $save['checkbox_rates_with_effected_from']      = $data['checkbox_rates_with_effected_from'];
        $save['checkbox_add_new_codes_to_code_decks']   = $data['checkbox_add_new_codes_to_code_decks'];
        $save['checkbox_review_rates']                  = $data['checkbox_review_rates'];
        $save['radio_list_option']                      = $data['radio_list_option'];

        $jobdata = array();
        $joboptions = json_decode(json_encode($save));
        if (count($joboptions) > 0) {

            if($data['RateUploadType'] == RateUpload::vendor) {
                $MODEL = "TempVendorRate";
            } else if($data['RateUploadType'] == RateUpload::customer) {
                $MODEL = "TempCustomerRate";
            } else if($data['RateUploadType'] == RateUpload::ratetable) {
                $MODEL = "TempRateTableRate";
            }

            if(isset($joboptions->uploadtemplate) && !empty($joboptions->uploadtemplate)){
                $uploadtemplate     = FileUploadTemplate::find($joboptions->uploadtemplate);
                $templateoptions    = json_decode($uploadtemplate->Options);
            }else{
                $templateoptions    = json_decode($joboptions->Options);
            }
            $csvoption      = $templateoptions->option;
            $attrselection  = $templateoptions->selection;

            // check dialstring mapping or not
            if(isset($attrselection->DialString) && !empty($attrselection->DialString)) {
                $DialStringId = $attrselection->DialString;
            }else{
                $DialStringId = 0;
            }
            if(isset($attrselection->Forbidden) && !empty($attrselection->Forbidden)){
                $p_forbidden = 1;
            }
            if(isset($attrselection->Preference) && !empty($attrselection->Preference)){
                $p_preference = 1;
            }

            if(isset($attrselection->DialCodeSeparator)){
                if($attrselection->DialCodeSeparator == ''){
                    $dialcode_separator = 'null';
                }else{
                    $dialcode_separator = $attrselection->DialCodeSeparator;
                }
            }else{
                $dialcode_separator = 'null';
            }

            if (isset($attrselection->FromCurrency) && !empty($attrselection->FromCurrency)) {
                $CurrencyConversion = 1;
                $CurrencyID = $attrselection->FromCurrency;
            }else{
                $CurrencyConversion = 0;
                $CurrencyID = 0;
            }

            if ($fullPath) {
                $path = AmazonS3::unSignedUrl($fullPath,$CompanyID);
                if (strpos($path, "https://") !== false) {
                    $file = $temp_path . basename($path);
                    file_put_contents($file, file_get_contents($path));
                    $FilePath = $file;
                } else {
                    $FilePath = $path;
                }
            };

            if(isset($templateoptions->skipRows) && $csvoption->Firstrow == 'columnname') {
                $skiptRows              = $templateoptions->skipRows;
                NeonExcelIO::$start_row = intval($skiptRows->start_row);
                NeonExcelIO::$end_row   = intval($skiptRows->end_row);
                $lineno                 = intval($skiptRows->start_row) + 2;
            } else if (isset($templateoptions->skipRows) && $csvoption->Firstrow == 'data') {
                $skiptRows              = $templateoptions->skipRows;
                NeonExcelIO::$start_row = intval($skiptRows->start_row);
                NeonExcelIO::$end_row   = intval($skiptRows->end_row);
                $lineno                 = intval($skiptRows->start_row) + 1;
            } else if ($csvoption->Firstrow == 'data') {
                $lineno = 1;
            } else {
                $lineno = 2;
            }

            $NeonExcel = new NeonExcelIO($FilePath, (array) $csvoption);
            $results = $NeonExcel->read();


            $error = array();
            // if EndDate is mapped and not empty than data will store in and insert from $batch_insert_array
            // if EndDate is mapped and     empty than data will store in and insert from $batch_insert_array2
            $batch_insert_array = $batch_insert_array2 = [];

            foreach ($attrselection as $key => $value) {
                $attrselection->$key = str_replace("\r",'',$value);
                $attrselection->$key = str_replace("\n",'',$attrselection->$key);
            }

            foreach ($results as $index=>$temp_row) {

                if ($csvoption->Firstrow == 'data') {
                    array_unshift($temp_row, null);
                    unset($temp_row[0]);
                }

                foreach ($temp_row as $key => $value) {
                    $key = str_replace("\r",'',$key);
                    $key = str_replace("\n",'',$key);
                    $temp_row[$key] = $value;
                }

                $tempdata = array();
                $tempdata['codedeckid'] = $joboptions->codedeckid;
                $tempdata['ProcessId']  = $ProcessID;

                //check empty row
                $checkemptyrow = array_filter(array_values($temp_row));
                if(!empty($checkemptyrow)){
                    if (isset($attrselection->CountryCode) && !empty($attrselection->CountryCode) && !empty($temp_row[$attrselection->CountryCode])) {
                        $tempdata['CountryCode'] = trim($temp_row[$attrselection->CountryCode]);
                    }else{
                        $tempdata['CountryCode'] = '';
                    }

                    if (isset($attrselection->Code) && !empty($attrselection->Code) && trim($temp_row[$attrselection->Code]) != '') {
                        $tempdata['Code'] = trim($temp_row[$attrselection->Code]);
                    }else if (isset($attrselection->CountryCode) && !empty($attrselection->CountryCode) && !empty($temp_row[$attrselection->CountryCode])) {
                        $tempdata['Code'] = "";  // if code is blank but country code is not blank than mark code as blank., it will be merged with countr code later ie 91 - 1 -> 911
                    } else {
                        $error[] = 'Code is blank at line no:'.$lineno;
                    }

                    if (isset($attrselection->Description) && !empty($attrselection->Description) && !empty($temp_row[$attrselection->Description])) {
                        $tempdata['Description'] = $temp_row[$attrselection->Description];
                    }else{
                        $error[] = 'Description is blank at line no:'.$lineno;
                    }
                    if (isset($attrselection->Action) && !empty($attrselection->Action)) {
                        if(empty($temp_row[$attrselection->Action])){
                            $tempdata['Change'] = 'I';
                        }else{
                            $action_value = $temp_row[$attrselection->Action];
                            if (isset($attrselection->ActionDelete) && !empty($attrselection->ActionDelete) && trim(strtolower($action_value)) == trim(strtolower($attrselection->ActionDelete)) ) {
                                $tempdata['Change'] = 'D';
                            }else if (isset($attrselection->ActionUpdate) && !empty($attrselection->ActionUpdate) && trim(strtolower($action_value)) == trim(strtolower($attrselection->ActionUpdate))) {
                                $tempdata['Change'] = 'U';
                            }else if (isset($attrselection->ActionInsert) && !empty($attrselection->ActionInsert) && trim(strtolower($action_value)) == trim(strtolower($attrselection->ActionInsert))) {
                                $tempdata['Change'] = 'I';
                            }else{
                                $tempdata['Change'] = 'I';
                            }
                        }

                    }else{
                        $tempdata['Change'] = 'I';
                    }

                    if (isset($attrselection->Rate) && !empty($attrselection->Rate)) {
                        $temp_row[$attrselection->Rate] = preg_replace('/[^.0-9\-]/', '', $temp_row[$attrselection->Rate]); //remove anything but numbers and 0 (only allow numbers,-dash,.dot)
                        if (is_numeric(trim($temp_row[$attrselection->Rate]))) {
                            $tempdata['Rate'] = trim($temp_row[$attrselection->Rate]);
                        } else {
                            $error[] = 'Rate is not numeric at line no:' . $lineno;
                        }
                    }elseif($tempdata['Change'] == 'D') {
                        $tempdata['Rate'] = 0;
                    }elseif($tempdata['Change'] != 'D') {
                        $error[] = 'Rate is blank at line no:'.$lineno;
                    }
                    if (isset($attrselection->EffectiveDate) && !empty($attrselection->EffectiveDate) && !empty($temp_row[$attrselection->EffectiveDate])) {
                        try {
                            $tempdata['EffectiveDate'] = formatSmallDate(str_replace( '/','-',$temp_row[$attrselection->EffectiveDate]), $attrselection->DateFormat);
                        }catch (\Exception $e){
                            $error[] = 'Date format is Wrong  at line no:'.$lineno;
                        }
                    }elseif(empty($attrselection->EffectiveDate)){
                        $tempdata['EffectiveDate'] = date('Y-m-d');
                    }elseif($tempdata['Change'] == 'D') {
                        $tempdata['EffectiveDate'] = date('Y-m-d');
                    }elseif($tempdata['Change'] != 'D') {
                        $error[] = 'EffectiveDate is blank at line no:'.$lineno;
                    }
                    if (isset($attrselection->EndDate) && !empty($attrselection->EndDate) && !empty($temp_row[$attrselection->EndDate])) {
                        try {
                            $tempdata['EndDate'] = formatSmallDate(str_replace( '/','-',$temp_row[$attrselection->EndDate]), $attrselection->DateFormat);
                        }catch (\Exception $e){
                            $error[] = 'Date format is Wrong  at line no:'.$lineno;
                        }
                    }
                    if (isset($attrselection->ConnectionFee) && !empty($attrselection->ConnectionFee)) {
                        $tempdata['ConnectionFee'] = trim($temp_row[$attrselection->ConnectionFee]);
                    }
                    if (isset($attrselection->Interval1) && !empty($attrselection->Interval1)) {
                        $tempdata['Interval1'] = intval(trim($temp_row[$attrselection->Interval1]));
                    }
                    if (isset($attrselection->IntervalN) && !empty($attrselection->IntervalN)) {
                        $tempdata['IntervalN'] = intval(trim($temp_row[$attrselection->IntervalN]));
                    }
                    if(!empty($DialStringId)){
                        if (isset($attrselection->DialStringPrefix) && !empty($attrselection->DialStringPrefix)) {
                            $tempdata['DialStringPrefix'] = trim($temp_row[$attrselection->DialStringPrefix]);
                        } else {
                            $tempdata['DialStringPrefix'] = '';
                        }
                    }
                    if(isset($tempdata['Code']) && isset($tempdata['Description']) && ( isset($tempdata['Rate'])  || $tempdata['Change'] == 'D') && ( isset($tempdata['EffectiveDate']) || $tempdata['Change'] == 'D') ){
                        if(isset($tempdata['EndDate'])) {
                            $batch_insert_array[]   = $tempdata;
                        } else {
                            $batch_insert_array2[]  = $tempdata;
                        }
                        $counter++;
                    }
                }

                if($counter==$bacth_insert_limit){
                    Log::info('Batch insert start');
                    Log::info('global counter'.$lineno);
                    Log::info('insertion start');
                    $MODEL::insert($batch_insert_array);
                    $MODEL::insert($batch_insert_array2);
                    Log::info('insertion end');
                    $batch_insert_array = [];
                    $batch_insert_array2 = [];
                    $counter = 0;
                }
                $lineno++;
            } // loop over

            if(!empty($batch_insert_array) || !empty($batch_insert_array2)) {
                Log::info('Batch insert start');
                Log::info('global counter'.$lineno);
                Log::info('insertion start');
                Log::info('last batch insert ' . count($batch_insert_array));
                Log::info('last batch insert 2 ' . count($batch_insert_array2));
                $MODEL::insert($batch_insert_array);
                $MODEL::insert($batch_insert_array2);
                Log::info('insertion end');
            }

            $JobStatusMessage = array();
            $duplicatecode=0;

            if($data['RateUploadType'] == RateUpload::vendor) {
                $query = "CALL  prc_WSReviewVendorRate ('" . $save['AccountID'] . "','" . $save['Trunk'] . "'," . $save['checkbox_replace_all'] . ",'" . $save['checkbox_rates_with_effected_from'] . "','" . $ProcessID . "','" . $save['checkbox_add_new_codes_to_code_decks'] . "','" . $CompanyID . "','".$p_forbidden."','".$p_preference."','".$DialStringId."','".$dialcode_separator."',".$CurrencyID.",".$save['radio_list_option'].")";
            } else if($data['RateUploadType'] == RateUpload::customer) {
                $query = "CALL  prc_WSReviewCustomerRate ('" . $save['AccountID'] . "','" . $save['Trunk'] . "'," . $save['checkbox_replace_all'] . ",'" . $save['checkbox_rates_with_effected_from'] . "','" . $ProcessID . "','" . $save['checkbox_add_new_codes_to_code_decks'] . "','" . $CompanyID . "','".$p_forbidden."','".$p_preference."','".$DialStringId."','".$dialcode_separator."',".$CurrencyID.",".$save['radio_list_option'].")";
            } else if($data['RateUploadType'] == RateUpload::ratetable) {
                $query = "CALL  prc_WSReviewRateTableRate ('" . $save['RateTableID'] . "'," . $save['checkbox_replace_all'] . ",'" . $save['checkbox_rates_with_effected_from'] . "','" . $ProcessID . "','" . $save['checkbox_add_new_codes_to_code_decks'] . "','" . $CompanyID . "','".$p_forbidden."','".$p_preference."','".$DialStringId."','".$dialcode_separator."',".$CurrencyID.",".$save['radio_list_option'].")";
            }

            Log::info('Start '.$query);

            try{
                DB::beginTransaction();
                $JobStatusMessage = DB::select($query);
                Log::info('End '.$query);
                DB::commit();

                $JobStatusMessage = array_reverse(json_decode(json_encode($JobStatusMessage),true));
                Log::info($JobStatusMessage);
                Log::info(count($JobStatusMessage));

                if(!empty($error) || count($JobStatusMessage) >= 1){
                    $prc_error = array();
                    foreach ($JobStatusMessage as $JobStatusMessage1) {
                        $prc_error[] = $JobStatusMessage1['Message'];
                        if(strpos($JobStatusMessage1['Message'], 'DUPLICATE CODE') !==false || strpos($JobStatusMessage1['Message'], 'No PREFIX FOUND') !==false){
                            $duplicatecode = 1;
                        }
                    }

                    // if duplicate code exit job will fail
                    if($duplicatecode == 1){
                        $error = array_merge($prc_error,$error);
                        //unset($error[0]);
                        $jobdata['message'] = implode('<br>',fix_jobstatus_meassage($error));
                        $jobdata['JobStatusID'] = DB::table('tblJobStatus')->where('Code','F')->pluck('JobStatusID');
                    }else{
                        $error = array_merge($prc_error,$error);
                        $jobdata['message'] = implode('<br>',fix_jobstatus_meassage($error));
                        $jobdata['JobStatusID'] = DB::table('tblJobStatus')->where('Code','PF')->pluck('JobStatusID');
                    }
                    $jobdata['status'] = "failed";

                }elseif(empty($JobStatusMessage)){
                    $jobdata['status'] = "success";
                    $jobdata['ProcessID'] = $ProcessID;
                    $jobdata['message'] = "Review Rates Successfully!";
                    $jobdata['FileUploadTemplateID'] = $FileUploadTemplateID;
                    $jobdata['JobStatusID'] = DB::table('tblJobStatus')->where('Code','S')->pluck('JobStatusID');
                }

            }catch ( Exception $err ){
                DB::rollback();
                $jobdata['JobStatusID'] = DB::table('tblJobStatus')->where('Code', 'F')->pluck('JobStatusID');
                $jobdata['message'] = 'Exception: ' . $err->getMessage();
                $jobdata['status'] = "failed";
                Log::error($err);
            }
        }

        return json_encode($jobdata);
    }

    public function getReviewRates() {
        $data                   = Input::all();
        $data['iDisplayStart'] +=1;

        $columns                = array('Code','Description','Rate','EffectiveDate','EndDate','ConnectionFee','Interval1','IntervalN');
        $sort_column            = $columns[$data['iSortCol_0']];
        $data['Code']           = !empty($data['Code']) ? $data['Code'] : NULL;
        $data['Description']    = !empty($data['Description']) ? $data['Description'] : NULL;

        if($data['RateUploadType'] == RateUpload::vendor) {
            $query = "call prc_getReviewVendorRates ('".$data['ProcessID']."','".$data['Action']."','".$data['Code']."','".$data['Description']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
        } else if($data['RateUploadType'] == RateUpload::customer) {
            $query = "call prc_getReviewCustomerRates ('".$data['ProcessID']."','".$data['Action']."','".$data['Code']."','".$data['Description']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
        } else if($data['RateUploadType'] == RateUpload::ratetable) {
            $query = "call prc_getReviewRateTableRates ('".$data['ProcessID']."','".$data['Action']."','".$data['Code']."','".$data['Description']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
        }

        Log::info($query);

        return DataTableSql::of($query)->make();
    }

    public function reviewRatesExports($type) {
        $data = Input::all();

        $data['Code']           = !empty($data['Code']) ? $data['Code'] : NULL;
        $data['Description']    = !empty($data['Description']) ? $data['Description'] : NULL;

        if($data['RateUploadType'] == RateUpload::vendor) {
            $query = "call prc_getReviewVendorRates ('".$data['ProcessID']."','".$data['Action']."','".$data['Code']."','".$data['Description']."',0 ,0,'','',1)";
        } else if($data['RateUploadType'] == RateUpload::customer) {
            $query = "call prc_getReviewCustomerRates ('".$data['ProcessID']."','".$data['Action']."','".$data['Code']."','".$data['Description']."',0 ,0,'','',1)";
        } else if($data['RateUploadType'] == RateUpload::ratetable) {
            $query = "call prc_getReviewRateTableRates ('".$data['ProcessID']."','".$data['Action']."','".$data['Code']."','".$data['Description']."',0 ,0,'','',1)";
        }

        Log::info($query);

        DB::setFetchMode( PDO::FETCH_ASSOC );
        $review_vendor_rates = DB::select($query);
        DB::setFetchMode( Config::get('database.fetch'));

        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Review '.$data['RateUploadType'].' Rates.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($review_vendor_rates);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Review '.$data['RateUploadType'].' Rates.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($review_vendor_rates);
        }
    }

    public function updateTempReviewRates() {
        $data = Input::all();

        $ProcessID      = $data['ProcessID'];
        $Code           = $data['Code'];
        $Description    = $data['Description'];
        $VendorID       = $data['VendorID'];
        $CustomerID     = $data['CustomerID'];
        $RateTableID    = $data['RateTableID'];
        $RateUploadType = $data['RateUploadType'];
        $TrunkID        = 0;

        if($data['Action'] == 'New') {
            $TempRateIDs = array_filter(explode(',',$data['TempRateIDs']),'intval');
        } else if($data['Action'] == 'Deleted') {
            $TempRateIDs = array_filter(explode(',',$data['VendorRateIDs']),'intval');
            $TrunkID     = $data['TrunkID'];
        }

        if (is_array($TempRateIDs) && count($TempRateIDs) || !empty($data['criteria'])) {
            $criteria = !empty($data['criteria']) && (int) $data['criteria'] == 1 ? 1 : 0;
            $Action = '';
            $Interval1 = $IntervalN = 0;
            $EndDate = date('Y-m-d H:i:s');

            if($data['Action'] == 'New') {
                if (!empty($data['updateInterval1']) || !empty($data['updateIntervalN'])) {
                    if (!empty($data['updateInterval1']) && empty($data['Interval1'])) {
                        return json_encode(array("status" => "Error", "message" => "Please enter Interval1 value."));
                    } else if (!empty($data['updateInterval1']) && !empty($data['Interval1'])) {
                        $Interval1 = (int)$data['Interval1'] > 0 ? (int)$data['Interval1'] : 0;
                    }
                    if (!empty($data['updateIntervalN']) && empty($data['IntervalN'])) {
                        return json_encode(array("status" => "Error", "message" => "Please enter IntervalN value."));
                    } else if (!empty($data['updateIntervalN']) && !empty($data['IntervalN'])) {
                        $IntervalN = (int)$data['IntervalN'] > 0 ? (int)$data['IntervalN'] : 0;
                    }
                    $Action = $data['Action'];
                } else {
                    return json_encode(array("status" => "Error", "message" => "Please select atlease 1 checkbox."));
                }
            } else if($data['Action'] == 'Deleted') {
                if (!empty($data['EndDate'])) {
                    $EndDate = $data['EndDate'];
                } else {
                    return json_encode(array("status" => "Error", "message" => "Please Enter End Date."));
                }
                $Action = $data['Action'];
            }

            $TempRateIDs = implode(',',$TempRateIDs);

            try {
                if($RateUploadType == RateUpload::vendor) {
                    $query = "call prc_WSReviewVendorRateUpdate ('".$VendorID."','".$TrunkID."','".$TempRateIDs."','".$ProcessID."','".$criteria."','".$Action."','".$Interval1."','".$IntervalN."','".$EndDate."','".$Code."','".$Description."')";
                } else if($RateUploadType == RateUpload::customer) {
                    $query = "call prc_WSReviewCustomerRateUpdate ('".$CustomerID."','".$TrunkID."','".$TempRateIDs."','".$ProcessID."','".$criteria."','".$Action."','".$Interval1."','".$IntervalN."','".$EndDate."','".$Code."','".$Description."')";
                } else if($RateUploadType == RateUpload::ratetable) {
                    $query = "call prc_WSReviewRateTableRateUpdate ('".$RateTableID."','".$TempRateIDs."','".$ProcessID."','".$criteria."','".$Action."','".$Interval1."','".$IntervalN."','".$EndDate."','".$Code."','".$Description."')";
                }

                Log::info($query);
                DB::statement($query);
                return json_encode(["status" => "success", "message" => "Rates successfully updated."]);
            } catch (Exception $e) {
                return json_encode(array("status" => "failed", "message" => $e->getMessage()));
            }
        }else{
            return json_encode(array("status" => "failed", "message" => "Please select vendor rates."));
        }
    }

    public function getSheetNamesFromExcel() {
        try {
            $data = Input::all();
            if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                    $file_name_without_ext = GUID::generate();
                    $file_name = $file_name_without_ext . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;

                    /*if (!empty($data['checkbox_review_rates']) && $data['checkbox_review_rates'] == 1) {
                        $file_name = NeonExcelIO::convertExcelToCSV($file_name, $data);
                    }*/
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if (!empty($file_name)) {
                $SheetNames = NeonExcelIO::getSheetNamesFromExcel($file_name);
                return Response::json(array("status" => "success", "SheetNames" => $SheetNames));
            }
        } catch (Exception $ex) {
            Log::info($ex);
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

}