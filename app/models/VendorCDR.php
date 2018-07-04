<?php
class VendorCDR extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrvcdr';
    public $timestamps = false; // no created_at and updated_at

    protected $guarded = array('VendorCDRID');

    protected $table = 'tblVendorCDR';

    protected  $primaryKey = "VendorCDRID";


    const RATE_METHOD_CURRENT_RATE = "CurrentRate";
    const RATE_METHOD_SPECIFYRATE = "SpecifyRate";

    static $RateMethod = array(self::RATE_METHOD_CURRENT_RATE=>'Rate setup against account',self::RATE_METHOD_SPECIFYRATE=>'Specify Rate');
}