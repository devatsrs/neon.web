<?php

class CompanyConfiguration extends \Eloquent {

    protected $fillable = [];
    protected $guarded = array('CompanyConfigurationID');
    protected $table = 'tblCompanyConfiguration';
    public  $primaryKey = "CompanyConfigurationID";
    static protected  $enable_cache = true;
    public static $cache = ["CompanyConfiguration"];

    public static function getConfiguration($CompanyID=0){
        $data = Input::all();
        $LicenceKey = getenv('LICENCE_KEY');
        $CompanyName = getenv('COMPANY_NAME');
        $CompanyConfiguration = 'CompanyConfiguration' . $LicenceKey.$CompanyName;

        self::$cache['CompanyConfiguration'] = array();

        if (self::$enable_cache && Cache::has($CompanyConfiguration)) {
            $cache = Cache::get($CompanyConfiguration);
            self::$cache['CompanyConfiguration'] = $cache['CompanyConfiguration'];
        } else {
            if($CompanyID==0){
                $CompanyID = \User::get_companyID();
            }
            if($CompanyID > 0) {
                self::$cache['CompanyConfiguration'] = CompanyConfiguration::where(['CompanyID' => $CompanyID])->lists('Value', 'Key');
                $CACHE_EXPIRE = self::$cache['CompanyConfiguration']['CACHE_EXPIRE'];
                $time = empty($CACHE_EXPIRE) ? 60 : $CACHE_EXPIRE;
                $minutes = \Carbon\Carbon::now()->addMinutes($time);
                //Cache::forever($CompanyConfiguration, array('CompanyConfiguration' => self::$cache['CompanyConfiguration']));
                Cache::add($CompanyConfiguration, array('CompanyConfiguration' => self::$cache['CompanyConfiguration']), $minutes);
            }
        }
        return self::$cache['CompanyConfiguration'];
    }

    public static function get($key = ""){

        $cache = CompanyConfiguration::getConfiguration();
        if(!empty($key) ){
            if(isset($cache[$key])){
                return $cache[$key];
            }
        }
        return "";

    }

    public static function getJsonKey($key = "",$index = ""){

        $cache = CompanyConfiguration::getConfiguration();

        if(!empty($key) ){

            if(isset($cache[$key])){

                $json = json_decode($cache[$key],true);
                if(isset($json[$index])){
                    return $json[$index];
                }
            }
        }
        return "";

    }
}