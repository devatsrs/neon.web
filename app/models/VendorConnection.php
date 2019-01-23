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

    static public function checkForeignKeyById($id) {
        $hasAccountApprovalList = VendorConnection::where("VendorConnectionID",$id)->count();
        if( intval($hasAccountApprovalList) > 0){
            return true;
        }else{
            return false;
        }
    }


    public static function getTrunkDropdownIDList($AccountID,$CompanyID){
        $row = VendorConnection::join("tblTrunk","tblTrunk.TrunkID", "=    ","tblVendorConnection.TrunkID")
            ->where(["tblVendorConnection.Active"=> 1,"tblVendorConnection.CompanyID"=>$CompanyID,"tblVendorConnection.AccountId"=>$AccountID])->select(array('tblVendorConnection.TrunkID','Trunk'))->lists('Trunk', 'TrunkID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }



}