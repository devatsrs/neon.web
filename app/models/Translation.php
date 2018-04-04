<?php

class Translation extends \Eloquent {
	
	protected $guarded = array('TranslationID');

    protected $table = 'tblTranslation';

    protected  $primaryKey = "TranslationID";

	public static $enable_cache = false;
	public static $default_lang_id = 43; // English
	public static $default_lang_ISOcode = "en"; // English

    public static $cache = array(
    "language_dropdown1_cache",   // Country => Country
    "language_cache",    // all records in obj
);

    public static function getLanguageDropdownList(){

        if (self::$enable_cache && Cache::has('language_dropdown1_cache')) {
            //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('language_dropdown1_cache');
            //get the admin defaults
            self::$cache['language_dropdown1_cache'] = $admin_defaults['language_dropdown1_cache'];
        } else {
            //if the cache doesn't have it yet
            $dd = Translation::join('tblLanguage', 'tblLanguage.LanguageID', '=', 'tblTranslation.LanguageID')
                            ->whereRaw('tblLanguage.LanguageID=tblTranslation.LanguageID')
                            ->select("tblLanguage.ISOCode", "tblTranslation.Language")->get();

            $dropdown = array();
            foreach ($dd as $key => $value) {
                $dropdown[$value->ISOCode] = $value->Language;
            }
            self::$cache['language_dropdown1_cache'] = $dropdown;
                //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('language_dropdown1_cache', array('language_dropdown1_cache' => self::$cache['language_dropdown1_cache']));

        }

        return self::$cache['language_dropdown1_cache'];
    }

    public static function getLanguageDropdownWithFlagList(){

        if (self::$enable_cache && Cache::has('languageflag_dropdown1_cache')) {
            //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('languageflag_dropdown1_cache');
            //get the admin defaults
            self::$cache['languageflag_dropdown1_cache'] = $admin_defaults['languageflag_dropdown1_cache'];
        } else {
            //if the cache doesn't have it yet
            $lang_result = Translation::join('tblLanguage', 'tblLanguage.LanguageID', '=', 'tblTranslation.LanguageID')
                ->whereRaw('tblLanguage.LanguageID=tblTranslation.LanguageID')
                ->select("tblLanguage.ISOCode", "tblTranslation.Language", "tblLanguage.flag", "tblLanguage.LanguageID")->get();

            $dropdown = array();
            foreach ($lang_result as $key => $value) {
                $dropdown[$value->ISOCode] = ["languageName"=>$value->Language, "languageFlag"=>$value->flag, "languageId"=>$value->LanguageID];
            }
            self::$cache['languageflag_dropdown1_cache'] = $dropdown;
            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('languageflag_dropdown1_cache', array('languageflag_dropdown1_cache' => self::$cache['languageflag_dropdown1_cache']));

        }

        return self::$cache['languageflag_dropdown1_cache'];
    }

    public static function getLanguageDropdownIdList($select=0){
        $dropdown = Translation::join('tblLanguage', 'tblLanguage.LanguageID', '=', 'tblTranslation.LanguageID')
            ->whereRaw('tblLanguage.LanguageID=tblTranslation.LanguageID')
            ->select("tblLanguage.LanguageID", "tblTranslation.Language")->lists("Language", "LanguageID");
        if($select==1) {
            $dropdown = array("" => "Select") + $dropdown;
        }
        return $dropdown;
    }

    public static function get_language_labels($languageCode="en"){
        $data_langs = DB::table('tblLanguage')
            ->select("TranslationID", "tblTranslation.Language", "Translation", "tblLanguage.ISOCode")
            ->join('tblTranslation', 'tblLanguage.LanguageID', '=', 'tblTranslation.LanguageID')
            ->where(["tblLanguage.ISOCode"=>$languageCode])
            ->first();
        return $data_langs;
    }


    public static function add_system_name($system_name, $en_word){

        /*$data_langs = Translation::get_language_labels();

        $translation_data = json_decode($data_langs->Translation, true);

        if(!array_key_exists($system_name ,$translation_data )){
            $translation_data[$system_name]=$en_word;

            Translation::where('TranslationID', $data_langs->TranslationID)->update( array('Translation' => json_encode($translation_data) ));
            Translation::create_language_file($data_langs->ISOCode,$translation_data);
            return true;
        }*/

        $system_name=trim(strtoupper($system_name));
        Translation::update_label(Translation::$default_lang_ISOcode, $system_name, $en_word);
        return true;
    }

    public static function update_label($language,$system_name, $value){

            $data_langs = Translation::get_language_labels($language);

            $json_file = json_decode($data_langs->Translation, true);
            $system_name=strtoupper($system_name);

            $json_file[$system_name]=$value;
            DB::table('tblTranslation')
                ->where(['TranslationID'=>$data_langs->TranslationID])
                ->update(['Translation' => json_encode($json_file)]);

            Translation::create_language_file($data_langs->ISOCode,$json_file);
            return true;
    }

    public static function multi_update_labels($language, $labelArr){

        $data_langs = Translation::get_language_labels($language);

        $json_file = json_decode($data_langs->Translation, true);

        foreach($labelArr as $label){
            $system_name=strtoupper($label["system_name"]);
            $json_file[$system_name]=$label["value"];
        }
        DB::table('tblTranslation')
            ->where(['TranslationID'=>$data_langs->TranslationID])
            ->update(['Translation' => json_encode($json_file)]);

        Translation::create_language_file($data_langs->ISOCode,$json_file);
        return true;
    }

    public static function delete_label($language,$system_name){

        $data_langs = Translation::get_language_labels($language);

        $json_file = json_decode($data_langs->Translation, true);
        if(array_key_exists($system_name, $json_file)){
            unset($json_file[$system_name]);
        }

        DB::table('tblTranslation')
            ->where(['TranslationID'=>$data_langs->TranslationID])
            ->update(['Translation' => json_encode($json_file)]);

        Translation::create_language_file($data_langs->ISOCode,$json_file);
    }

    public static function create_language_file($lang_folder, $data_array){

        ksort($data_array);
        $arr_valid="\nreturn array(";
        foreach($data_array as $key=>$value){
            $arr_valid.="\n\t'".$key."'=>'".HTML::entities($value)."',";
        }
        $arr_valid.="\n);";

        $JSON_File = app_path("lang/".$lang_folder);
        if(!File::exists($JSON_File)){
            // File::makeDirectory($JSON_File);
            RemoteSSH::run("mkdir -p " . $JSON_File);
            RemoteSSH::run("chmod -R 777 " . $JSON_File);
        }
        RemoteSSH::run("chmod -R 777 " . $JSON_File."/routes.php");
        file_put_contents($JSON_File."/routes.php", "<?php ".$arr_valid );

        $service_path=dirname(CompanyConfiguration::get("RM_ARTISAN_FILE_LOCATION"))."/resources/lang/";
        RemoteSSH::run("yes | cp -rf ".$JSON_File." ".$service_path);
        RemoteSSH::run("chmod -R 777 " . $service_path.$lang_folder);

        $api_path=public_path("neon.api/resources/lang/");
        RemoteSSH::run("yes | cp -rf ".$JSON_File." ".$api_path);
        RemoteSSH::run("chmod -R 777 " . $api_path.$lang_folder);
    }

}