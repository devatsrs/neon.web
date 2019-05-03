<?php

class VendorTrunkCostLog extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblVendorTrunkCostLog';
    protected $primaryKey = "VendorTrunkCostLogID";

    public $timestamps = false;
}