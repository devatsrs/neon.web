<?php

class VOSVendorActiveCall extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrvcdr";
    protected $table = "tblVOSVendorActiveCall";
    protected $primaryKey = "VOSVendorActiveCallID";
    protected $guarded = array('VOSVendorActiveCallID');

    public $timestamps = false; // no created_at and updated_at

}