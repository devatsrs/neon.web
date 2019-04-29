<?php

class TranslateController extends \BaseController {

    public function index()
    {
        $global_admin = Session::get("global_admin" , 0);
        return View::make('translate.index', compact("global_admin"));
    }

    public function changeLanguage($language)
    {
        set_cus_language($language);
        echo json_encode(array( "status"=>"success", "language"=>NeonCookie::getCookie('customer_language') ));
    }

    public function search_ajax_datagrid() {

        $data = Input::all();

        $all_langs = DB::table('tblLanguage')
            ->select("tblLanguage.LanguageID", "tblTranslation.Language", "Translation", "tblLanguage.ISOCode")
            ->join('tblTranslation', 'tblLanguage.LanguageID', '=', 'tblTranslation.LanguageID')
            ->where(["tblLanguage.ISOCode"=>$data["Language"]])
            ->orWhere(["tblLanguage.ISOCode"=>Translation::$default_lang_ISOcode])
            ->get();

        foreach($all_langs as $val){
            if($val->ISOCode==Translation::$default_lang_ISOcode){
                $arr_english=json_decode($val->Translation, true);
            }else{
                $arr_translation=json_decode($val->Translation, true);
            }
        }

        $arr_return=array();

        foreach($arr_english as $key=>$val){
            $row=array();
            $row[]=$key;
            $row[]=$val;
            $translation="";
            if($data["Language"]==Translation::$default_lang_ISOcode){
                $translation=$val;
            }else if(isset($arr_translation[$key])){
                $translation=$arr_translation[$key];
            }

            $html_translation='<label data-languages="'.$data["Language"].'" class="label_language hidden" data-system-name="'.$key.'" >'.htmlentities($translation).'</label>
                                <input type="text" value="'.htmlentities($translation).'" data-languages="'.$data["Language"].'" class="text_language form-control"  data-system-name="'.$key.'" />';

			$global_admin=intval(Session::get("global_admin" , 0));
//            delete lable btn
            if(!empty($global_admin)){
                $html_translation.='<input type="button" value="Delete" data-languages="'.$data["Language"].'" class="text_delete form-control btn-danger"  data-system-name="'.$key.'" />';
            }

            $row[]=$html_translation;
            $arr_return[]=$row;
        }

        $return=[
            "sEcho"=>1,
            "iTotalRecords"=>count($arr_return),
            "iTotalDisplayRecords"=> count($arr_return),
            "aaData"=>$arr_return
        ];

        return json_encode($return);

    }

    function process_multipalUpdate(){

        $request = Input::all();
        $language = $request["language"];
        $listLabels = $request["listLabels"];

        Translation::multi_update_labels($language, $listLabels);
        return json_encode(["status" => "success", "message" => ""]);
    }

    function process_singleDelete(){
        $request = Input::all();
        Translation::delete_label($request["language"], $request["system_name"]);

        return json_encode(["status" => "success", "message" => "Deleted - ".$request["system_name"]]);
    }


    public function exports($languageCode,$type) {

        $data_langs = Translation::get_language_labels($languageCode);
        //Log::info($data_langs->Language);
        //return false;
        /*$translation_data = json_decode($data_langs->Translation, true);
        $json_file=array();
        foreach($translation_data as $key=>$value){
            $json_file[]=array("SystemName"=>$key, "Translation"=> $value,"ISOCode" => $data_langs->ISOCode);
        }*/

        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/language_'.$languageCode.'.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($data_langs);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/language_'.$languageCode.'.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($data_langs);
        }
    }

    public function new_system_name(){
        $request = Input::all();

        if(Translation::add_system_name($request["system_name"], $request["en_word"])){
            return json_encode(["status" => "success", "message" => "Add Successfully"]);
        }else{
            return json_encode(["status" => "fail", "message" => "System Name already exist."]);
        }
    }

    public static function refresh_label(){

        try{

            $arr_language=Translation::getLanguageDropdownWithFlagList();

            foreach($arr_language as $lang_iso=>$value){
                $data_langs = Translation::get_language_labels($lang_iso);
                $translation_data = json_decode($data_langs->Translation, true);

                if(Translation::$default_lang_ISOcode==$lang_iso){

                    foreach($translation_data  as $key=>$value){
                        if(strpos($key,"CUST_PANEL_PAGE_TICKET_FIELDS_")===0 || strpos($key,"THEMES_")===0){
                            unset($translation_data[$key]);
                        }
                    }

                    $ticketFields = Ticketfields::all();
                    foreach($ticketFields as $ticke_field){
                        $translation_data["CUST_PANEL_PAGE_TICKET_FIELDS_".$ticke_field->TicketFieldsID]=$ticke_field->CustomerLabel;
                    }

                    $ticketFieldsValues = TicketfieldsValues::all();
                    foreach($ticketFieldsValues as $ticke_field_values){
                        $translation_data["CUST_PANEL_PAGE_TICKET_FIELDS_".$ticke_field_values->FieldsID."_VALUE_".$ticke_field_values->ValuesID]=$ticke_field_values->FieldValueCustomer;
                    }

                    $arr_TicketPriority = TicketPriority::all();
                    foreach($arr_TicketPriority as $TicketPriority){
                        $translation_data[strtoupper("CUST_PANEL_PAGE_TICKET_FIELDS_PRIORITY_VAL_".$TicketPriority->PriorityValue)]=$TicketPriority->PriorityValue;
                    }

                    //$themes_data = Themes::all();
                    $CompanyID 				= 	User::get_companyID();
                    $themes = Themes::where(['CompanyID'=>$CompanyID,'ThemeStatus'=>'active']);
                    $CountTheme=$themes->count();
                    $themes_data=$themes->get();
                    if($CountTheme > 0){
                        foreach($themes_data as $theme){
                            $domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $theme->DomainUrl);
                            $domainUrl_key = strtoupper(preg_replace('/-+/', '_',$domainUrl_key));

                            $translation_data["THEMES_".$domainUrl_key."_FOOTERTEXT"]=$theme->FooterText;
                            $translation_data["THEMES_".$domainUrl_key."_LOGIN_MSG"]=isset($theme->LoginMessage)?$theme->LoginMessage:'';
                            $translation_data["THEMES_".$domainUrl_key."_TITLE"]=$theme->Title;
                        }
                    }else{
                        $domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $_SERVER['HTTP_HOST']);
                        $domainUrl_key = strtoupper(preg_replace('/-+/', '_',$domainUrl_key));

                        $translation_data["THEMES_".$domainUrl_key."_FOOTERTEXT"]='';
                        $translation_data["THEMES_".$domainUrl_key."_LOGIN_MSG"]='Dear user, Please login below!';
                        $translation_data["THEMES_".$domainUrl_key."_TITLE"]='';
                    }


                }

                ksort($translation_data);
                Translation::where('TranslationID', $data_langs->TranslationID)->update( array('Translation' => json_encode($translation_data) ));
                Translation::create_language_file($data_langs->ISOCode,$translation_data);
            }


            return json_encode(["status" => "success", "message" => ""]);
        } catch (Exception $ex) {
            return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }

    public static function datatable_Label(){

        $arr_label=[
            "sLengthMenu"=> cus_lang("TABLE_LBL_RECORDS_PER_PAGE"),
            "sZeroRecords"=> cus_lang("MESSAGE_DATA_NOT_AVAILABLE"),
            "sInfo"=> cus_lang("TABLE_LBL_SHOWING_RECORDS"),
            "sInfoEmpty"=> cus_lang("TABLE_LBL_SHOWING_EMPTY_RECORDS"),
            "sInfoFiltered"=> cus_lang("TABLE_LBL_FILTERED_FROM_MAX_TOTAL_RECORD"),
            "sLoadingRecords"=> cus_lang("BUTTON_LOADING_CAPTION"),
            "sProcessing"=> cus_lang("DATATABLE_PROCESSING")
        ];

        return Response::json($arr_label);
    }

    public function download_sample_excel_file(){
            $filePath = public_path() .'/uploads/sample_upload/TranslateImportSample.csv';
            download_file($filePath);

    }

    public function upload() {
        //   $total_records = $this->import("I:\bk\www\projects\aamir\rm\laravel\rm\public\uploads\fxHv86yN\Snq4Obmf0XlJNFz2.csv");
        //   exit;
        ini_set('max_execution_time', 0);
        $data = Input::all();

        if (Input::hasFile('excel')) {
            Log::info("file got");
            $id = User::get_companyID();
            $company_name = Account::getCompanyNameByID($id);
            $upload_path = CompanyConfiguration::get('UPLOAD_PATH');
            $excel = Input::file('excel'); // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();

            if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                $file_name = "Translat_". GUID::generate() . '.' . $ext;
                Log::info("file name ".$file_name);
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TRANSLATION_IMPORT']) ;
                Log::info('upload path '.$amazonPath);
                $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
                $excel->move($destinationPath, $file_name);
                if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name;
                Log::info("fulll path ".$fullPath);
                $data['full_path'] = $fullPath;
                $data['translationname'] = Translation::$translation_name;

                try {
                    DB::beginTransaction();
                    unset($data['excel']); //remove unnecesarry object.
                    $result = Job::logJob("ILT", $data);
                    Log::info("result ".json_encode($result));
                    if ($result['status'] != "success") {
                        DB::rollback();
                        Log::info("failed");
                        return Response::json(["status" => "failed", "message" => $result['message']]);
                    }
                    DB::commit();
                    return Response::json(["status" => "success", "message" => "File Uploaded, Job Added in queue to process. You will be informed once Job Done. "]);
                } catch (Exception $ex) {
                    DB::rollback();
                    return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
                }

            } else {
                return Response::json(array("status" => "failed", "message" => "Allowed Extension .xls, .xlxs, .csv."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Please upload excel/csv file <5MB."));
        }
    }

}