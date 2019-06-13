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

    const ALL_TAX_FILTER = '';
    const TAX_ALL =1;
    const TAX_USAGE =2;
    const TAX_RECURRING =3;

    const ALL = '';
    const NL ='NL';
    const EU = 'EU';
    const NEU ='NEU';

    public static $tax_array = array(self::TAX_ALL=>'All Charges overall Invoice',self::TAX_USAGE=>'USAGE only',self::TAX_RECURRING=>'Recurring');

  public static $tax_array_filter = array(self::ALL_TAX_FILTER=>'All',self::TAX_ALL=>'All Charges overall Invoice',self::TAX_USAGE=>'USAGE only',self::TAX_RECURRING=>'Recurring');
  
    public static $tax_countries_array = array(self::NL=>'Netherlands',self::EU=>'EU Country',self::NEU=>'Non EU');
    public static $tax_countries_filter = array(self::ALL=>'All',self::NL=>'Netherlands',self::EU=>'EU Country',self::NEU=>'Non EU');

    static public function checkForeignKeyById($id) {
        /*
         * Tables To Check Foreign Key before Delete.
         * */

        $hasInBillingClass = BillingClass::whereRaw('FIND_IN_SET(?,TaxRateID)', [$id])->count();
        $hasInInvoiceTaxRate = InvoiceTaxRate::where("TaxRateID",$id)->count();
        $hasInAccountTaxRate = Account::whereRaw('FIND_IN_SET(?,TaxRateID)', [$id])->count();

        if( intval($hasInBillingClass) > 0 || intval($hasInInvoiceTaxRate) > 0 || intval($hasInAccountTaxRate) > 0){
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
    public static function getTaxRateDropdownIDList($CompanyID){

        $CompanyID = getParentCompanyIdIfReseller($CompanyID);
        $Taxes = TaxRate::where(array('CompanyID'=>$CompanyID))->lists('Title','TaxRateID');
        $Taxes = array('' => "Select")+ $Taxes;
        return $Taxes;

        /** remove catch logic due to companyid problem
        if (self::$enable_cache && Cache::has('taxrate_dropdown1_cache')) {
            $admin_defaults = Cache::get('taxrate_dropdown1_cache');
            self::$cache['taxrate_dropdown1_cache'] = $admin_defaults['taxrate_dropdown1_cache'];
        } else {
            self::$cache['taxrate_dropdown1_cache'] = TaxRate::where(array('CompanyID'=>$CompanyID))->lists('Title','TaxRateID');
            self::$cache['taxrate_dropdown1_cache'] = array('' => "Select")+ self::$cache['taxrate_dropdown1_cache'];

            Cache::forever('taxrate_dropdown1_cache', array('taxrate_dropdown1_cache' => self::$cache['taxrate_dropdown1_cache']));
        }
        return self::$cache['taxrate_dropdown1_cache']; */
    }

    public static function getTaxRateDropdownIDListForInvoice($TaxRateID=0,$CompanyID){
        if($TaxRateID==0){
            self::$cache['taxrate_dropdown2_cache'] = TaxRate::where(array('CompanyID'=>$CompanyID))->get(['TaxRateID','Title','Amount','FlatStatus'])->toArray();
        }else{
            self::$cache['taxrate_dropdown2_cache'] = TaxRate::where(array('CompanyID'=>$CompanyID,'TaxRateID'=>$TaxRateID))->get(['TaxRateID','Title','Amount','FlatStatus'])->toArray();
        }
        self::$cache['taxrate_dropdown2_cache'] = array_merge(array(array('TaxRateID' => 0 , "Title"=> "Select", "Amount"=> 0,"FlatStatus"=>0)),self::$cache['taxrate_dropdown2_cache']);
        return self::$cache['taxrate_dropdown2_cache'];
    }

    public static function clearCache(){

        Cache::flush("taxrate_dropdown1_cache");

    }

    public static function calculateProductTotalTaxAmount($AccountID,$amount,$qty,$decimal_places) {

        //Get Account TaxIDs
        $TaxRateIDs = AccountBilling::getTaxRate($AccountID);

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

    public static function calculateProductTaxAmount($TaxRateID,$Price) {

        if($TaxRateID>0){

            $TaxRate = TaxRate::where("TaxRateID",$TaxRateID)->first();

            if(isset($TaxRate->TaxType) && isset($TaxRate->Amount) ) {

               // if ($TaxRate->TaxType == TaxRate::TAX_ALL) {

                    if (isset($TaxRate->FlatStatus) && isset($TaxRate->Amount)) {

                        if ($TaxRate->FlatStatus == 1) {

                            return $TaxRate->Amount;

                        } else {
                            return (($Price * $TaxRate->Amount) / 100);

                        }
                    }
               // }
            }
        }
        return 0;

    }

    public static function getInvoiceTaxRateByProductDetail($invoiceID){
        $InvoiceDetail=InvoiceDetail::where('InvoiceID',$invoiceID)->select('InvoiceDetailID','TaxRateID','TaxRateID2','Price')->get();
        $Result = array();
        foreach($InvoiceDetail as $data) {
            if (!empty($data->TaxRateID)) {
                $TaxRate = array();
                $TaxRate['TaxRateID'] = $data->TaxRateID;
                $TaxRate['InvoiceDetailID'] = $data->InvoiceDetailID;
                $TaxRate['Title'] = TaxRate::getTaxName($data->TaxRateID);
                $TaxRate['created_at'] = date("Y-m-d H:i:s");
                $TaxRate['InvoiceID'] = $invoiceID;
                $TaxRate['TaxAmount'] = TaxRate::calculateProductTaxAmount($data->TaxRateID, $data->Price);
                $Result[] = $TaxRate;
            }
            if (!empty($data->TaxRateID2)) {
                $TaxRate = array();
                $TaxRate['TaxRateID'] = $data->TaxRateID2;
                $TaxRate['InvoiceDetailID'] = $data->InvoiceDetailID;
                $TaxRate['Title'] = TaxRate::getTaxName($data->TaxRateID2);
                $TaxRate['created_at'] = date("Y-m-d H:i:s");
                $TaxRate['InvoiceID'] = $invoiceID;
                $TaxRate['TaxAmount'] = TaxRate::calculateProductTaxAmount($data->TaxRateID2, $data->Price);
                $Result[] = $TaxRate;
            }
        }
        return $Result;
    }


    public static function getAccountTaxes($data){

        $Taxes = '';
        $CompanyID = $data['CompanyID'];
        $Country = $data['Country'];
        $RegisterDutchFoundation = 0;
        $DutchProvider = 0;
        if(isset($data['RegisterDutchFoundation']) && $data['RegisterDutchFoundation']=='true'){
            $RegisterDutchFoundation=1;
        }
        if(isset($data['DutchProvider']) && $data['DutchProvider']=='true'){
            $DutchProvider=1;
        }
        if($Country=='NETHERLANDS'){
            $EUCountry = 'NL';
        }else{
            $EUCountry = Country::where('Country',$Country)->pluck('EUCountry');
            $EUCountry = empty($EUCountry) ? 'NEU' : 'EU';
        }
        $Results = TaxRate::where(['DutchProvider'=>$DutchProvider,'DutchFoundation'=>$RegisterDutchFoundation,'Country'=>$EUCountry,'CompanyId'=>$CompanyID,'Status'=>1])->get();
        //log::info(print_r($Results,true));
        if(!empty($Results)){
            foreach($Results as $result){
                $Taxes.=$result->TaxRateId.',';
            }
            $Taxes = rtrim($Taxes, ',');
        }
        //$Taxes = explode(",", $Taxes);

        return $Taxes;
    }
}