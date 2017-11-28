<?php

class VendorRateChangeLog extends \Eloquent {
	protected $fillable = [];
    public $timestamps = false; // no created_at and updated_at

    protected $guarded = array('VendorRateChangeLogID');

    protected $table = 'tblVendorRateChangeLog';

    protected  $primaryKey = "VendorRateChangeLogID";

}