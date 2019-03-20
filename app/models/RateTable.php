<?php

class RateTable extends \Eloquent
{

    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'tblRateTable';
    protected $primaryKey = "RateTableId";
    protected static $rate_table_cache = array();
    public static $enable_cache = false;

    //const TYPE_VOICECALL = 1;
    //const TYPE_DID = 2;
    const APPLIED_TO_CUSTOMER = 1;
    const APPLIED_TO_VENDOR = 2;
    const APPLIED_TO_RESELLER = 3;
    //public static $types = array( self::TYPE_VOICECALL => 'Voice Call',self::TYPE_DID=>'DID');
    public static $AppliedTo = array( self::APPLIED_TO_CUSTOMER => 'Customer',self::APPLIED_TO_VENDOR=>'Vendor',self::APPLIED_TO_RESELLER=>'Partner');


    const RATE_STATUS_AWAITING  = 0;
    const RATE_STATUS_APPROVED  = 1;
    const RATE_STATUS_REJECTED  = 2;

    public static $RateStatus = array(
        self::RATE_STATUS_APPROVED  => 'Approved',
        self::RATE_STATUS_AWAITING  => 'Awaiting Approval',
   //     self::RATE_STATUS_REJECTED=>'Rejected'
    );

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
                $rateTable = RateTable::where(["Status" => 1, "CompanyID" => $company_id]);

                if(isset($options['NotVendor'])){
                    $rateTable->where("AppliedTo", "!=", self::APPLIED_TO_VENDOR);
                    unset($options['NotVendor']);
                }

                self::$rate_table_cache = $rateTable->where($options)->lists("RateTableName", "RateTableId");
            }else{
                self::$rate_table_cache = RateTable::where(["Status" => 1, "CompanyID" => $company_id])->lists("RateTableName", "RateTableId");
            }
            self::$rate_table_cache = array('' => "Select")+ self::$rate_table_cache;

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

        $types = [];
        $appliedTos = [];
        if(isset($data['types']) && !empty($data['types'])) {
            $types = $data['types'];
            unset($data['types']);
        }

        if(isset($data['applied_tos']) && !empty($data['applied_tos'])) {
            $appliedTos = $data['applied_tos'];
            unset($data['applied_tos']);
        }

        $notVendor = false;
        if(isset($data['NotVendor'])){
            $notVendor = true;
            unset($data['NotVendor']);
        }

        $row = RateTable::where($data);

        if(!empty($types))
            $row->whereIn("Type", $types);

        if(!empty($appliedTos))
            $row->whereIn("AppliedTo", $appliedTos);

        if($notVendor == true)
            $row->where("AppliedTo", "!=", self::APPLIED_TO_VENDOR);

        $row = $row->lists("RateTableName", "RateTableId");
        $row = array(""=> "Select")+$row;
        return $row;
    }

    public static function getRateTables($data=array()){
        $compantID = User::get_companyID();
        $where = ['CompanyID'=>$compantID];
        $RateTables = RateTable::select(['RateTableName','RateTableId'])->where($where)->orderBy('RateTableName', 'asc')->lists('RateTableName','RateTableId');
        if(!empty($RateTables)){
            $RateTables = [''=>'Select'] + $RateTables;
        }
        return $RateTables;
    }
    public static function getCurrencyCode($RateTableId){
        $CurrencyID = RateTable::where(["RateTableId" => $RateTableId])->pluck('CurrencyID');
        return Currency::getCurrencySymbol($CurrencyID);
    }


    public static function checkRateTableInCronjob($RateTableId){
        $CompanyID = User::get_companyID();
        $CronJobCommandID = CronJobCommand::where(['Command'=>'rategenerator','CompanyID'=>$CompanyID])->pluck('CronJobCommandID');
        $cronjobs = CronJob::where(['CronJobCommandID'=>$CronJobCommandID,'CompanyID'=>$CompanyID])->get();
        if(count($cronjobs)>0){
            foreach($cronjobs as $cronjob){
                if(!empty($cronjob['Settings'])){
                    $option = json_decode($cronjob['Settings']);
                    if(!empty($option->rateTableID)){
                        if($option->rateTableID == $RateTableId){
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    public static function getDIDTariffDropDownList($CompanyID,$Type,$CurrencyID,$AppiedTo){
        $row=array();

        $row = RateTable::where(array('CompanyID'=>$CompanyID,'Type'=>$Type,'AppliedTo'=>$AppiedTo,'CurrencyID'=>$CurrencyID))->lists('RateTableName', 'RateTableId');

        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;

    }

    public static function getPackageTariffDropDownList($CompanyID,$Type,$AppiedTo){
        $row=array();

        $row = RateTable::where(array('CompanyID'=>$CompanyID,'Type'=>$Type,'AppliedTo'=>$AppiedTo))->lists('RateTableName', 'RateTableId');

        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;

    }

}