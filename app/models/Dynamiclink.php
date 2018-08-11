<?php

class Dynamiclink extends \Eloquent {

    protected $guarded = array('DynamicLinkID');
    protected $table = 'tblDynamiclink';
    public  $primaryKey = "DynamicLinkID"; //Used in BasedController
    public $timestamps = false;
    static protected  $enable_cache = false;



}