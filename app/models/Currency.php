<?php
use Symfony\Component\Intl\Intl;
class Currency extends \Eloquent {

    protected $table = 'tblCurrency';
    protected  $primaryKey = "CurrencyId";
    protected $fillable = [];
    protected $guarded = ['CurrencyId'];
    static protected  $enable_cache = true;
    public static $cache = array(
        "currency_dropdown1_cache",   // currency => currencyID
        "currency_dropdown2_cache",
    );

    static public function checkForeignKeyById($id) {
        /*
         * Tables To Check Foreign Key before Delete.
         * */

        $hasInAccount = Account::where("CurrencyID",$id)->count();

        if( intval($hasInAccount) > 0 ){
            return true;
        }else{
            return false;
        }

    }

    public static function getCurrency($CurrencyId){
        if($CurrencyId>0){
            return Currency::where("CurrencyId",$CurrencyId)->pluck('Code');
        }
    }

    public static function getCurrencyDropdownIDList($CompanyID=0,$includePrefix=0){

        if($includePrefix == 1) {
            if (self::$enable_cache && Cache::has('currency_dropdown11_cache')) {
                $admin_defaults = Cache::get('currency_dropdown11_cache');
                self::$cache['currency_dropdown11_cache'] = $admin_defaults['currency_dropdown11_cache'];
            } else {
                $CompanyId = $CompanyID > 0 ? $CompanyID : User::get_companyID();
                //self::$cache['currency_dropdown11_cache'] = Currency::where("CompanyId",$CompanyId)->lists('Code','CurrencyID');

                self::$cache['currency_dropdown11_cache'] = Currency::select('Code', DB::raw('CONCAT("DBDATA-",CurrencyID) AS CurrencyID'))->lists('Code', 'CurrencyID');
                self::$cache['currency_dropdown11_cache'] = array('' => "Select") + self::$cache['currency_dropdown11_cache'];
                Cache::forever('currency_dropdown11_cache', array('currency_dropdown11_cache' => self::$cache['currency_dropdown11_cache']));
            }
            return self::$cache['currency_dropdown11_cache'];
        } else {
            if (self::$enable_cache && Cache::has('currency_dropdown1_cache')) {
                $admin_defaults = Cache::get('currency_dropdown1_cache');
                self::$cache['currency_dropdown1_cache'] = $admin_defaults['currency_dropdown1_cache'];
            } else {
                $CompanyId = $CompanyID > 0 ? $CompanyID : User::get_companyID();
                //self::$cache['currency_dropdown1_cache'] = Currency::where("CompanyId",$CompanyId)->lists('Code','CurrencyID');

                self::$cache['currency_dropdown1_cache'] = Currency::lists('Code', 'CurrencyID');
                self::$cache['currency_dropdown1_cache'] = array('' => "Select") + self::$cache['currency_dropdown1_cache'];
                Cache::forever('currency_dropdown1_cache', array('currency_dropdown1_cache' => self::$cache['currency_dropdown1_cache']));
            }
            return self::$cache['currency_dropdown1_cache'];
        }
    }

    public static function getCurrencyDropdownList(){

        if (self::$enable_cache && Cache::has('currency_dropdown2_cache')) {
            $admin_defaults = Cache::get('currency_dropdown2_cache');
            self::$cache['currency_dropdown2_cache'] = $admin_defaults['currency_dropdown2_cache'];
        } else {
            $CompanyId = User::get_companyID();
            //self::$cache['currency_dropdown2_cache'] = Currency::where("CompanyId",$CompanyId)->lists('Code','Code');
            self::$cache['currency_dropdown2_cache'] = Currency::lists('Code','Code');
            self::$cache['currency_dropdown2_cache'] = array('' => "Select")+ self::$cache['currency_dropdown2_cache'];
            Cache::forever('currency_dropdown2_cache', array('currency_dropdown2_cache' => self::$cache['currency_dropdown2_cache']));
        }

        return self::$cache['currency_dropdown2_cache'];
    }

    public static function getCurrencySymbol($CurrencyID){
        if($CurrencyID>0){
            return Currency::where("CurrencyId",$CurrencyID)->pluck('Symbol');
        }
    }

    public static function clearCache(){

        Cache::flush("currency_dropdown1_cache");
        Cache::flush("currency_dropdown2_cache");

    }
    public static function getCurrencyCode($CurrencyId){
        if($CurrencyId>0){
            return Currency::where("CurrencyId",$CurrencyId)->pluck('Code');
        }
    }

    public static function convertCurrency($CompanyCurrency=0, $AccountCurrency=0, $FileCurrency=0, $Rate=0) {

        if($FileCurrency == $AccountCurrency) {
            $NewRate = $Rate;
        } else if($FileCurrency == $CompanyCurrency) {
            $ConversionRate = CurrencyConversion::where('CurrencyID',$AccountCurrency)->pluck('Value');
            if($ConversionRate){
                $NewRate = ($Rate * $ConversionRate);
            }else{
                $NewRate = 0;
            }
        } else {
            $ACConversionRate = CurrencyConversion::where('CurrencyID',$AccountCurrency)->pluck('Value');
            $FCConversionRate = CurrencyConversion::where('CurrencyID',$FileCurrency)->pluck('Value');

            if($ACConversionRate && $FCConversionRate) {
                $NewRate = ($ACConversionRate) * ($Rate / $FCConversionRate);
            }else{
                $NewRate = 0;
            }
        }

        return $NewRate;

    }

}