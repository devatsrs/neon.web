<?php

class TranslateController extends \BaseController {

    public function index()
    {
        return View::make('translate.index');
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

            $html_translation='<label data-languages="'.$data["Language"].'" class="label_language hidden" data-system-name="'.$key.'" >'.$translation.'</label>
                                <input type="text" value="'.$translation.'" data-languages="'.$data["Language"].'" class="text_language form-control"  data-system-name="'.$key.'" />';


//            delete lable btn
            $html_translation.='<input type="button" value="Delete" data-languages="'.$data["Language"].'" class="text_delete form-control btn-danger"  data-system-name="'.$key.'" />';




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

    function process_singleUpdate(){


        $request = Input::all();
        if($request["value"]==""){
            return json_encode(["status" => "fail", "message" => "Required Translation"]);
        }
        $data_langs = Translation::get_language_labels($request["language"]);
//        dd(DB::getQueryLog());

        $json_file = json_decode($data_langs->Translation, true);

        $json_file[$request["system_name"]]=$request["value"];

        DB::table('tblTranslation')
            ->where(['TranslationID'=>$data_langs->TranslationID])
            ->update(['Translation' => json_encode($json_file)]);

        $this->create_language_file($data_langs->ISOCode,$json_file);


        return json_encode(["status" => "success", "message" => ""]);

    }

    function process_singleDelete(){


        $request = Input::all();

        $data_langs = Translation::get_language_labels($request["language"]);
//        dd(DB::getQueryLog());

        $json_file = json_decode($data_langs->Translation, true);
        if(array_key_exists($request["system_name"], $json_file)){
            unset($json_file[$request["system_name"]]);
        }

        DB::table('tblTranslation')
            ->where(['TranslationID'=>$data_langs->TranslationID])
            ->update(['Translation' => json_encode($json_file)]);

        $this->create_language_file($data_langs->ISOCode,$json_file);


        return json_encode(["status" => "success", "message" => "Deleted - ".$request["system_name"]]);

    }

    public static function create_language_file($lang_folder, $data_array){

        ksort($data_array);
        $arr_valid="\nreturn array(";
        foreach($data_array as $key=>$value){
            $arr_valid.="\n\t'".$key."'=>'".$value."',";
        }
        $arr_valid.="\n);";

        $JSON_File = app_path("lang/".$lang_folder);
        if(!File::exists($JSON_File)){
            File::makeDirectory($JSON_File);
        }
        RemoteSSH::run("chmod -R 777 " . $JSON_File."/routes.php");
        file_put_contents($JSON_File."/routes.php", "<?php ".$arr_valid );
    }

    public function exports($languageCode,$type) {

        $data_langs = Translation::get_language_labels($languageCode);
        $translation_data = json_decode($data_langs->Translation, true);
        $json_file=array();
        foreach($translation_data as $key=>$value){
            $json_file[]=array("System Name"=>$key, "Language"=> $value);
        }

        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/language_'.$data_langs->Language.'.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($json_file);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/language_'.$data_langs->Language.'.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($json_file);
        }
    }

    public function new_system_name(){
        $request = Input::all();
        $request["system_name"]=trim(strtoupper($request["system_name"]));
        $data_langs = Translation::get_language_labels();

        $translation_data = json_decode($data_langs->Translation, true);

        if($request["system_name"]!="" && !array_key_exists($request["system_name"] ,$translation_data )){
            $translation_data[$request["system_name"]]=$request["en_word"];

            ksort($translation_data);

            Translation::where('TranslationID', $data_langs->TranslationID)->update( array('Translation' => json_encode($translation_data) ));
            $this->create_language_file($data_langs->ISOCode,$translation_data);
            return json_encode(["status" => "success", "message" => "Add Successfully"]);
        }else{
            return json_encode(["status" => "fail", "message" => "System Name already exist."]);
        }
    }

    public static function refresh_label(){
        try{

            $arr_language=Translation::getLanguageDropdownWithFlagList();
            $arr_iso=array_keys($arr_language);

            foreach($arr_iso as $lang_iso){
                $data_langs = Translation::get_language_labels($lang_iso);
                $translation_data = json_decode($data_langs->Translation, true);

                if(Translation::$default_lang_ISOcode==$lang_iso){

                    foreach($translation_data  as $key=>$value){
                        if(strpos($key,"CUST_PANEL_PAGE_TICKET_FIELDS_")!==false){
                            unset($translation_data[$key]);
                        }
                    }

                    $ticketFields = Ticketfields::all();
                    foreach($ticketFields as $ticke_field){
                        $translation_data["CUST_PANEL_PAGE_TICKET_FIELDS_".$ticke_field->TicketFieldsID]=$ticke_field->CustomerLabel;
                    }

                }

                ksort($translation_data);
                Translation::where('TranslationID', $data_langs->TranslationID)->update( array('Translation' => json_encode($translation_data) ));
                TranslateController::create_language_file($data_langs->ISOCode,$translation_data);
            }


            return json_encode(["status" => "success", "message" => ""]);
        } catch (Exception $ex) {
            return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }

}