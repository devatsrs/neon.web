<?php

class SippyVendorDestiMap extends \Eloquent {

    protected $fillable = [];
    protected $guarded = array('SippyVendorDestiMapID');
    protected $table = 'tblSippyVendorDestiMap';
    public  $primaryKey = "SippyVendorDestiMapID"; //Used in BasedController
    static protected  $enable_cache = false;
   // public static $cache = ["itemtype_dropdown1_cache"];



}