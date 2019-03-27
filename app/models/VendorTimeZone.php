<?php
Class VendorTimeZone extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblVendorTimezone';
    protected $primaryKey = "VendorTimezoneID";
    public $timestamps  = false;
}    