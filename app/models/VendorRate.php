<?php

class VendorRate extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblVendorRate';
    protected $primaryKey = "VendorRateID";

    public static function getRecentDueVendorRates(){
        $companyID = User::get_companyID();
        /* Select Rate which effective date is coming in next 2 days  */
        $vendor_rates = VendorRate::join('tblRate' , "tblVendorRate.RateId" , '=',"tblRate.RateId"   )
            ->join('tblCountry' , "tblCountry.CountryId" , '=',"tblRate.CountryId"   )
            ->join('tblAccount' , "tblAccount.AccountID" , '=',"tblVendorRate.AccountID"   )
            ->where("tblAccount.CompanyID",$companyID)
            ->whereRaw("  DATEDIFF(DAY,tblVendorRate.EffectiveDate, GETDATE()) between -2 and 0 ")
            ->select([
                "tblAccount.AccountName",
                "tblAccount.AccountID",
                "tblVendorRate.TrunkID",
                "tblVendorRate.EffectiveDate",
            ])->take(10)->get();
        return $vendor_rates ;
     }
}