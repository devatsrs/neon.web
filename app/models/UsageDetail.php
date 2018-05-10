<?php
class UsageDetail extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrvcdr';
    public $timestamps = false; // no created_at and updated_at

    protected $guarded = array('UsageDetailID');

    protected $table = 'tblUsageDetails';

    protected  $primaryKey = "UsageDetailID";


    const RATE_METHOD_CURRENT_RATE = "CurrentRate";
    const RATE_METHOD_SPECIFYRATE = "SpecifyRate";
    const RATE_METHOD_VALUE_AGAINST_COST = "ValueAgainstCost";

    static $RateMethod = array(self::RATE_METHOD_CURRENT_RATE=>'Rate setup against account',self::RATE_METHOD_SPECIFYRATE=>'Specify Rate',self::RATE_METHOD_VALUE_AGAINST_COST=> "Add Margin on Cost" );
}