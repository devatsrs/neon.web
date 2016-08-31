<?php

class VendorTrunk extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('VendorTrunkID');
    protected $table = 'tblVendorTrunk';

    protected  $primaryKey = "VendorTrunkID";

    public static function getTrunksByTrunkAsKey($id=0){

        $vendor_trunks = VendorTrunk::where(["AccountID"=>$id])->get();
        $records = array();
        foreach ($vendor_trunks as $vendor_trunk) {
            $records[$vendor_trunk->TrunkID] = $vendor_trunk;
        }
        return $records;
    }
    public static function getTrunkDropdownIDList($AccountID){
        $CompanyID = User::get_companyID();
        $row = VendorTrunk::join("tblTrunk","tblTrunk.TrunkID", "=    ","tblVendorTrunk.TrunkID")
            ->where(["tblVendorTrunk.Status"=> 1])->where(["tblVendorTrunk.CompanyID"=>$CompanyID])->where(["tblVendorTrunk.AccountID"=>$AccountID])->select(array('tblVendorTrunk.TrunkID','Trunk'))->lists('Trunk', 'TrunkID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }
}