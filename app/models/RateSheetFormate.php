<?php

class RateSheetFormate extends \Eloquent {
	protected $fillable = [];

	protected $guarded = array();

    protected $table = 'tblRateSheetFormate';

    protected  $primaryKey = "RateSheetFormateID";

    const  RATESHEET_FORMAT_VOS32 = 'Vos 3.2';
    const  RATESHEET_FORMAT_VOS20 = 'Vos 2.0';
    const  RATESHEET_FORMAT_RATESHEET = 'Rate Sheet';
    const  RATESHEET_FORMAT_SIPPY = 'Sippy';
    const  RATESHEET_FORMAT_PORTA = 'Porta';
    const  RATESHEET_FORMAT_MOR = 'Mor';

    public static $cache = array(
        "rsformates_dropdown_customer_cache", //Customer  // Trunk => Trunk 
        "rsformates_dropdown_vendor_cache",  //Customer  
        "rsformates_cache",    // all records in obj
    ); 
 
    protected static $enable_cache = false;


    public static function boot()
    {
        parent::boot();
        RateSheetFormate::observe(new RateSheetFormateObserver);
    }


    public static function getVendorRateSheetFormatesDropdownList(){

        if (self::$enable_cache && Cache::has('rsformates_dropdown_customer_cache')) {
             //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('rsformates_dropdown_customer_cache');
             //get the admin defaults
            self::$cache['rsformates_dropdown_customer_cache'] = $admin_defaults['rsformates_dropdown_customer_cache'];
        } else {
             //if the cache doesn't have it yet
            $company_id = User::get_companyID();
            self::$cache['rsformates_dropdown_customer_cache'] = RateSheetFormate::where(["Status"=>1,"Vendor"=>1])->select(["Title as t1","Title as t2"])->orderBy('t1','asc')->lists('t1', 't2');
            
            self::$cache['rsformates_dropdown_customer_cache'] = array("" => "Select")+ self::$cache['rsformates_dropdown_customer_cache'];
            
            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('rsformates_dropdown_customer_cache', array('rsformates_dropdown_customer_cache' => self::$cache['rsformates_dropdown_customer_cache']));

        }

        return self::$cache['rsformates_dropdown_customer_cache'];
    }
    
    public static function getCustomerRateSheetFormatesDropdownList(){

        if (self::$enable_cache && Cache::has('rsformates_dropdown_customer_cache')) {
             //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('rsformates_dropdown_customer_cache');
             //get the admin defaults
            self::$cache['rsformates_dropdown_customer_cache'] = $admin_defaults['rsformates_dropdown_customer_cache'];
        } else {
             //if the cache doesn't have it yet
            $company_id = User::get_companyID();
            self::$cache['rsformates_dropdown_customer_cache'] = RateSheetFormate::where(["Status"=>1,"Customer"=>1])->select(["Title as t1","Title as t2"])->orderBy('t1','asc')->lists('t1', 't2');
            
            self::$cache['rsformates_dropdown_customer_cache'] = array("" => "Select")+ self::$cache['rsformates_dropdown_customer_cache'];
            
            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('rsformates_dropdown_customer_cache', array('rsformates_dropdown_customer_cache' => self::$cache['rsformates_dropdown_customer_cache']));

        }

        return self::$cache['rsformates_dropdown_customer_cache'];
    }

   

    public static function clearCache(){

            Cache::flush("rsformates_dropdown_customer_cache");
            Cache::flush("rsformates_dropdown_vendor_cache");
           // Cache::flush("rsformates_dropdown2_cache");
           // Cache::flush("rsformates_cache");

    }

}