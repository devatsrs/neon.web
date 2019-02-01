<?php

class VendorCDRHeader extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrvcdr";
    protected $table = "tblVendorCDRHeader";
    protected $primaryKey = "VendorCDRHeaderID";
    protected $guarded = array('VendorCDRHeaderID');

    public static function  checkForeignKeyById($id){

        if($id>0){
            return false;
        }
    }
}