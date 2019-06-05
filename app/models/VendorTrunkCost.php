<?php

class VendorTrunkCost extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblVendorTrunkCost';
    protected $primaryKey = "VendorTrunkCostID";

    public $timestamps = false;
}