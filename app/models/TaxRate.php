<?php

class TaxRate extends \Eloquent {
    protected $table = 'tblTaxRate';
    public $primaryKey = "TaxRateId";
    protected $fillable = [];
    protected $guarded = ['TaxRateId'];
    static protected  $enable_cache = true;
    public static $cache = array(
        "taxrate_dropdown1_cache",   // taxrate => taxrateID
        "taxrate_dropdown2_cache",   // taxrate => taxrateID
    );
    const TAX_ALL =1;
    const TAX_USAGE =2;
    const TAX_RECURRING =3;

    public static $tax_array = array(self::TAX_ALL=>'All Charges overall Invoice',self::TAX_USAGE=>'USAGE only',self::TAX_RECURRING=>'Recurring');

    static public function checkForeignKeyById($id) {
        /*
         * Tables To Check Foreign Key before Delete.
         * */

        $hasInAccount = Account::where("TaxRateID",$id)->count();

        if( intval($hasInAccount) > 0 ){
            return true;
        }else{
            return false;
        }

    }

    public static function getTaxRate($taxRateId){
        $TaxtRateIds = explode(",",$taxRateId);
        $TaxRateTitles  = array();
        if(count($TaxtRateIds)) {
            foreach($TaxtRateIds as $TaxRateID) {
                if ($TaxRateID > 0) {
                    $TaxRateTitles[] = TaxRate::where("TaxRateId", $TaxRateID)->pluck('Title');
                }
            }
        }
        return implode(", ",$TaxRateTitles);
    }
    public static function getTaxRateDropdownIDList(){

        if (self::$enable_cache && Cache::has('taxrate_dropdown1_cache')) {
            $admin_defaults = Cache::get('taxrate_dropdown1_cache');
            self::$cache['taxrate_dropdown1_cache'] = $admin_defaults['taxrate_dropdown1_cache'];
        } else {
            self::$cache['taxrate_dropdown1_cache'] = TaxRate::where(array('CompanyID'=>User::get_companyID()))->lists('Title','TaxRateID');
            self::$cache['taxrate_dropdown1_cache'] = array('' => "Select a Tax Rate")+ self::$cache['taxrate_dropdown1_cache'];

            Cache::forever('taxrate_dropdown1_cache', array('taxrate_dropdown1_cache' => self::$cache['taxrate_dropdown1_cache']));
        }

        return self::$cache['taxrate_dropdown1_cache'];
    }

    public static function getTaxRateDropdownIDListForInvoice($TaxRateID=0){
        if($TaxRateID==0){
            self::$cache['taxrate_dropdown2_cache'] = TaxRate::where(array('CompanyID'=>User::get_companyID(),"TaxType"=>TaxRate::TAX_ALL))->get(['TaxRateID','Title','Amount','FlatStatus'])->toArray();
        }else{
            self::$cache['taxrate_dropdown2_cache'] = TaxRate::where(array('CompanyID'=>User::get_companyID(),'TaxRateID'=>$TaxRateID))->get(['TaxRateID','Title','Amount','FlatStatus'])->toArray();
        }
        self::$cache['taxrate_dropdown2_cache'] = array_merge(array(array('TaxRateID' => 0 , "Title"=> "Select a Tax Rate", "Amount"=> 0,"FlatStatus"=>0)),self::$cache['taxrate_dropdown2_cache']);
        return self::$cache['taxrate_dropdown2_cache'];
    }

    public static function clearCache(){

        Cache::flush("taxrate_dropdown1_cache");

    }

    public static function calculateProductTotalTaxAmount($AccountID,$amount,$qty,$decimal_places) {

        //Get Account TaxIDs
        $TaxRateIDs = Account::where("AccountID",$AccountID)->pluck("TaxRateId");

        $SubTotal = $amount*$qty;
        $TotalTax = 0;
        $GrandTotal = 0;
        if(!empty($TaxRateIDs)){

            $TaxRateIDs = explode(",",$TaxRateIDs);

            foreach($TaxRateIDs as $TaxRateID) {

                $TaxRateID = intval($TaxRateID);

                if($TaxRateID>0){

                    $TaxRate = TaxRate::where("TaxRateID",$TaxRateID)->first();

                    if(isset($TaxRate->TaxType) && isset($TaxRate->Amount) ) {

                        if ($TaxRate->TaxType == TaxRate::TAX_ALL) {

                            if (isset($TaxRate->FlatStatus) && isset($TaxRate->Amount)) {

                                if ($TaxRate->FlatStatus == 1) {

                                    $GrandTotal += ($SubTotal) + $TaxRate->Amount;

                                } else {
                                    $GrandTotal += (($SubTotal * $TaxRate->Amount) / 100);

                                }
                            }
                        }
                    }
                }
            }
        }
        return $GrandTotal;
    }
    public static function getTaxName($TaxRateId){
        return $TaxRate = TaxRate::where(["TaxRateId"=>$TaxRateId])->pluck('Title');
    }
}