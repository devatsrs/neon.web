<?php

class VendorPreference extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblVendorPreference';
    protected $primaryKey = "VendorPreferenceID";

    public $timestamps = false; // no created_at and updated_at
}