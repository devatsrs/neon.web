<?php

class TempVendorRate extends \Eloquent {
	protected $fillable = [];
    public $timestamps = false; // no created_at and updated_at

    protected $guarded = array('TempVendorRateID');

    protected $table = 'tblTempVendorRate';

    protected  $primaryKey = "TempVendorRateID";

}