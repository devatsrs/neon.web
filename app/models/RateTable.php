<?php

class RateTable extends \Eloquent
{

    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'tblRateTable';
    protected $primaryKey = "RateTableId";
    protected static $rate_table_cache = array();
    public static $enable_cache = false;

    /*
     * Option = ["TrunkID" = int ,... ]
     * */
    public static function getRateTableCache($options= array())
    {


        if (self::$enable_cache && Cache::has('rate_table_cache')) {
            $rate_table_cache = Cache::get('rate_table_cache');  //get the admin defaults
            self::$rate_table_cache = $rate_table_cache['rate_table_cache'];
        } else {
            self::clearCache();
            $company_id = User::get_companyID();
            if(!empty($options)){
                self::$rate_table_cache = RateTable::where($options)->where(["Status" => 1, "CompanyID" => $company_id])->lists("RateTableName", "RateTableId");
            }else{
                self::$rate_table_cache = RateTable::where(["Status" => 1, "CompanyID" => $company_id])->lists("RateTableName", "RateTableId");
            }
            self::$rate_table_cache = array('' => "Select a Rate Table")+ self::$rate_table_cache;

        }

        return self::$rate_table_cache;
    }

    public static function clearCache()
    {
        Cache::flush("rate_table_cache");


    }
    public static function getCodeDeckId($RateTableId){
        return RateTable::where(["RateTableId" => $RateTableId])->pluck('CodeDeckId');
    }
    public static function checkRateTableBand($RateTableId){
        return RateTable::where(["RateTableId" => $RateTableId,'RateGeneratorID'=>0])->count();
	}
    public static function getRateTableList($data=array()){
        $data['CompanyID']=User::get_companyID();
        $data['Status'] = 1;
        $row = RateTable::where($data)->lists("RateTableName", "RateTableId");
        $row = array(""=> "Select a Rate Table")+$row;
        return $row;
    }
    public static function getCurrencyCode($RateTableId){
        $CurrencyID = RateTable::where(["RateTableId" => $RateTableId])->pluck('CurrencyID');
        return Currency::getCurrencyCode($CurrencyID);
    }
}