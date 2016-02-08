<?php

class InVoiceLog extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('InvoiceLogID');
    protected $table = 'tblInvoiceLog';
    protected  $primaryKey = "InvoiceLogID";

    const CREATED = 1;
    const UPDATED = 2;
    const VIEWED = 3;
    const SENT =4;
    const CANCEL =5;
    const REGENERATED  =6;

    public static $log_status = array(self::CREATED=>'Created',self::VIEWED=>'Viewed',self::CANCEL=>'Cancel',self::SENT=>'Sent',self::UPDATED=>'Updated',self::REGENERATED => 'Regenerated');

}