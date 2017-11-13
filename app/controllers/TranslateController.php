<?php

class TranslateController extends \BaseController {

    public function index()
    {
        $all_langs =Translation::getLanguageDropdownList();

        return View::make('translate.index',compact("all_langs"));
    }

    public function changeLanguage($language)
    {
        App::setLocale($language);
        NeonCookie::setCookie('customer_language',$language,365);

        echo json_encode(array( "status"=>"success", "language"=>NeonCookie::getCookie('customer_language') ));
    }

    public function search_ajax_datagrid() {

        $data = Input::all();
        $all_langs = DB::table('tblLanguage')
            ->select("tblLanguage.LanguageID", "tblTranslation.Language", "Translation", "tblLanguage.ISOCode")
            ->join('tblTranslation', 'tblLanguage.LanguageID', '=', 'tblTranslation.LanguageID')
            ->where(["tblLanguage.ISOCode"=>$data["Language"]])
            ->orWhere(["tblLanguage.ISOCode"=>"en"])
            ->get();

        foreach($all_langs as $val){
            if($val->ISOCode=="en"){
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
            if($data["Language"]=="en"){
                $translation=$val;
            }else if(isset($arr_translation[$key])){
                $translation=$arr_translation[$key];
            }

            $html_translation='<label data-languages="'.$data["Language"].'" class="label_language hidden" data-system-name="'.$key.'" >'.$translation.'</label>
                                <input text="text" value="'.$translation.'" data-languages="'.$data["Language"].'" class="text_language form-control"  data-system-name="'.$key.'" />';



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
        $data_langs = DB::table('tblLanguage')
            ->select("TranslationID", "tblTranslation.Language", "Translation", "tblLanguage.ISOCode")
            ->join('tblTranslation', 'tblLanguage.LanguageID', '=', 'tblTranslation.LanguageID')
            ->where(["tblLanguage.ISOCode"=>$request["language"]])
            ->first();
//        dd(DB::getQueryLog());

        $json_file = json_decode($data_langs->Translation, true);

        $json_file[$request["system_name"]]=$request["value"];

        DB::table('tblTranslation')
            ->where(['TranslationID'=>$data_langs->TranslationID])
            ->update(['Translation' => json_encode($json_file)]);

        $this->create_language_file($data_langs->ISOCode,$json_file);


        return json_encode(["status" => "success", "message" => ""]);

    }

    function create_language_file($lang_folder, $data_array){

        $arr_valid="\nreturn array(";
        foreach($data_array as $key=>$value){
            $arr_valid.="\n\t'".$key."'=>'".$value."',";
        }
        $arr_valid.="\n);";

        $JSON_File = app_path("lang/".$lang_folder);
        if(!File::exists($JSON_File)){
            File::makeDirectory($JSON_File);
        }

        file_put_contents($JSON_File."/routes.php", "<?php ".$arr_valid );
    }

    public function exports($languageCode,$type) {

        $data_langs = DB::table('tblLanguage')
            ->select("TranslationID", "tblTranslation.Language", "Translation", "tblLanguage.ISOCode")
            ->join('tblTranslation', 'tblLanguage.LanguageID', '=', 'tblTranslation.LanguageID')
            ->where(["tblLanguage.ISOCode"=>$languageCode])
            ->first();

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
}