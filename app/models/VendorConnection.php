<?php

class VendorConnection extends \Eloquent {
    protected $table = 'tblVendorConnection';
    public $primaryKey = "VendorConnectionID";
    protected $fillable = [];
    protected $guarded = ['VendorConnectionID'];
    static protected  $enable_cache = true;
    public static $cache = array(
        "taxrate_dropdown1_cache",   // taxrate => taxrateID
        "taxrate_dropdown2_cache",   // taxrate => taxrateID
    );
    const Type_DID ='DID';
    const Type_VoiceCall ='VoiceCall';

    public static $Type_array = array(self::Type_DID=>'DID',self::Type_VoiceCall=>'Voice Calls');

    static public function checkForeignKeyById($id) {
        $hasAccountApprovalList = VendorConnection::where("VendorConnectionID",$id)->count();
        if( intval($hasAccountApprovalList) > 0){
            return true;
        }else{
            return false;
        }
    }



}