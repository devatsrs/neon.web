<?php

class Country extends \Eloquent {
    protected $guarded = array('CountryID');

    protected $table = 'tblCountry';

    protected  $primaryKey = "CountryID";

	public static $enable_cache = false;

    public static $cache = array(
    "country_dropdown1_cache",   // Country => Country
    "country_dropdown2_cache",    // CountryID => Country
    "country_cache",    // all records in obj
); 


    public static function boot()
    {
        parent::boot();
        Country::observe(new CountryObserver);
    }


    public static function getCountryDropdownList($is_all=''){

        if (self::$enable_cache && Cache::has('country_dropdown1_cache')) {
             //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('country_dropdown1_cache');
             //get the admin defaults
            self::$cache['country_dropdown1_cache'] = $admin_defaults['country_dropdown1_cache'];
        } else {
             //if the cache doesn't have it yet
            $company_id = User::get_companyID();
            self::$cache['country_dropdown1_cache'] = Country::lists('Country', 'Country');
               
            self::$cache['country_dropdown1_cache'] = array("" => "Select")+ self::$cache['country_dropdown1_cache'];
            
            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('country_dropdown1_cache', array('country_dropdown1_cache' => self::$cache['country_dropdown1_cache']));

        }

        if($is_all == 'All'){
                unset(self::$cache['country_dropdown1_cache'][""]);
                self::$cache['country_dropdown1_cache'] = array("All" => "All")+self::$cache['country_dropdown1_cache'];
        }

        return self::$cache['country_dropdown1_cache'];
    }
    
    public static function getCountryDropdownIDList($is_all=''){

        if (self::$enable_cache && Cache::has('country_dropdown2_cache')) {
             //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('country_dropdown2_cache');
             //get the admin defaults
            self::$cache['country_dropdown2_cache'] = $admin_defaults['country_dropdown2_cache'];
        } else {
             //if the cache doesn't have it yet
            $company_id = User::get_companyID();
            self::$cache['country_dropdown2_cache'] = Country::selectRaw("concat(prefix,' ',Country) as IDCountry")->addSelect('CountryID')->lists('IDCountry','CountryID');
            
            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('country_dropdown2_cache', array('country_dropdown2_cache' => self::$cache['country_dropdown2_cache']));
        }
        self::$cache['country_dropdown2_cache'] = array('' => cus_lang("DROPDOWN_OPTION_SELECT"))+ self::$cache['country_dropdown2_cache'];
        if($is_all == 'All'){
            unset(self::$cache['country_dropdown2_cache'][""]);
            self::$cache['country_dropdown2_cache'] = array("All" => cus_lang("DROPDOWN_OPTION_ALL"))+self::$cache['country_dropdown2_cache'];
        }
        return self::$cache['country_dropdown2_cache'];
    }

    public static function getCountryCacheObj(){

        if (self::$enable_cache && Cache::has('country_cache')) {
             //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('country_cache');
             //get the admin defaults
            self::$cache['country_cache'] = $admin_defaults['country_cache'];
        } else {
             //if the cache doesn't have it yet
            $company_id = User::get_companyID();
            self::$cache['country_cache'] = Country::all();
            Cache::forever('country_cache', array('country_cache' => self::$cache['country_cache']));
        }

        return self::$cache['country_cache'];
    }


    public static function clearCache(){

            Cache::flush("country_dropdown1_cache");
            Cache::flush("country_dropdown2_cache");
            Cache::flush("country_cache");

    }
    public static function getCountryPrefix($CountryID){
        return Country::where(array('CountryID'=>$CountryID))->pluck('Prefix');
    }
    public static function getName($CountryID){
        return Country::where(array('CountryID'=>$CountryID))->pluck('Country');
    }



}