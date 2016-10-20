<?php

namespace App\Lib;
class QuickBookLog extends \Eloquent {    
    protected $fillable = [];
    protected $guarded = array('QuickBookLogID');
    protected $table = 'tblQuickBookLog';
    protected  $primaryKey = "QuickBookLogID";

    const ACCOUNT = 1;
    const INVOICE = 2;
    const PRODUCT = 3;

    public static $log_status = array(self::ACCOUNT=>'Account',self::INVOICE=>'Invoice',self::PRODUCT=>'Product');

} 