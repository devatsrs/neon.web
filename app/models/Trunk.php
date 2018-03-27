<?php

use models\observers;

class Trunk extends \Eloquent  {

    public static $cache = array(
        "trunk_dropdown1_cache",   // Trunk => Trunk
        "trunk_dropdown2_cache",    // TrunkID => Trunk
        "trunk_cache",    // all records in obj
    ); 
 
    protected static $enable_cache = false;

    protected $guarded = array();

    public static $rules = array(
        'Trunk' =>      'required|unique:tblTrunk',
        'CompanyID' =>  'required',
       // 'RatePrefix' => 'required',
       // 'AreaPrefix' => 'required',
       // 'Prefix' =>     'required',
        'Status' =>     'between:0,1',
    );

    protected $table = 'tblTrunk';

    protected  $primaryKey = "TrunkID";


    public static function boot()
    {
        parent::boot();
        Trunk::observe(new TrunkObserver);
/*        Trunk::getTrunkDropdownList();
        Trunk::getTrunkDropdownIDList();
*/    }


    public static function getTrunkDropdownList($CompanyID=0){

        if (self::$enable_cache && Cache::has('trunk_dropdown1_cache')) {
             //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('trunk_dropdown1_cache');
             //get the admin defaults
            self::$cache['trunk_dropdown1_cache'] = $admin_defaults['trunk_dropdown1_cache'];
        } else {
             //if the cache doesn't have it yet
            $company_id = $CompanyID>0?$CompanyID : User::get_companyID();
            //self::$cache['trunk_dropdown1_cache'] = Trunk::where([ "Status" => 1 , "CompanyID" => $company_id])->lists('Trunk', 'Trunk');
            self::$cache['trunk_dropdown1_cache'] = Trunk::where([ "Status" => 1 ])->lists('Trunk', 'Trunk');
            self::$cache['trunk_dropdown1_cache'] = array(""=>cus_lang("DROPDOWN_OPTION_SELECT")) + self::$cache['trunk_dropdown1_cache'];
            
            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('trunk_dropdown1_cache', array('trunk_dropdown1_cache' => self::$cache['trunk_dropdown1_cache']));


        }

        return self::$cache['trunk_dropdown1_cache'];
    }
    
    public static function getTrunkDropdownIDList($CompanyID=0){

        if (self::$enable_cache && Cache::has('trunk_dropdown2_cache')) {
             //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('trunk_dropdown2_cache');
             //get the admin defaults
            self::$cache['trunk_dropdown2_cache'] = $admin_defaults['trunk_dropdown2_cache'];
        } else {
             //if the cache doesn't have it yet
            $company_id = $CompanyID>0?$CompanyID : User::get_companyID();
            //self::$cache['trunk_dropdown2_cache'] = Trunk::where(["Status" => 1 , "CompanyID" => $company_id])->lists( 'Trunk','TrunkID');
            self::$cache['trunk_dropdown2_cache'] = Trunk::where(["Status" => 1 ])->lists( 'Trunk','TrunkID');

            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('trunk_dropdown2_cache', array('trunk_dropdown2_cache' => self::$cache['trunk_dropdown2_cache']));
        }
        self::$cache['trunk_dropdown2_cache'] =  array(""=> cus_lang("DROPDOWN_OPTION_SELECT")) + self::$cache['trunk_dropdown2_cache'] ;

        return self::$cache['trunk_dropdown2_cache'];
    }

    public static function getTrunkCacheObj($CompanyID=0){

         if (self::$enable_cache && Cache::has('trunk_cache')) {
             //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('trunk_cache');
             //get the admin defaults
            self::$cache['trunk_cache'] = $admin_defaults['trunk_cache'];
        } else {
             //if the cache doesn't have it yet
            $company_id = $CompanyID>0?$CompanyID : User::get_companyID();
            //self::$cache['trunk_cache'] = Trunk::where(["Status" => 1 , "CompanyID" => $company_id])->get();
            self::$cache['trunk_cache'] = Trunk::where(["Status" => 1 ])->get();
            Cache::forever('trunk_cache', array('trunk_cache' => self::$cache['trunk_cache']));
        }

        return self::$cache['trunk_cache'];
    }

    public static function clearCache(){

            Cache::flush("trunk_dropdown1_cache");
            Cache::flush("trunk_dropdown2_cache");
            Cache::flush("trunk_cache");

    }
    public static function getTrunkName($trunkid){
        $trunkdata = Trunk::where(["TrunkID" => (int)$trunkid ])->first();
        if(!empty($trunkdata)){
            return $trunkdata->Trunk;
        }
        return '';
    }

}
