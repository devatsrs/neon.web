<?php

class Dynamiclink extends \Eloquent {

    protected $guarded = array('DynamicLinkID');
    protected $table = 'tblDynamiclink';
    public  $primaryKey = "DynamicLinkID"; //Used in BasedController
    public $timestamps = false;
    static protected  $enable_cache = false;

    public static function getDynamicLinks(){
        $ReturnData=array();
        $CompanyID = Customer::get_companyID();
        $AccountID = Customer::get_accountID();
        $getDynamicLinks=Dynamiclink::where('CompanyID',$CompanyID)->get();
        foreach($getDynamicLinks as $linkdata){
            $CM_data=array();
            $reg=0;
            $CM_data['CompanyID']=$CompanyID;
            $CM_data['AccountID']=$AccountID;
            $name=$linkdata->Title;
            $Link=$linkdata->Link;
            $currencyID=$linkdata->CurrencyID;
            $Account=  Customer::select('Number','CurrencyId','BillingEmail','password')->where('AccountID',$CM_data['AccountID'])->first();

            if(!empty($Account) && ($currencyID==0 || $currencyID==$Account->CurrencyId)) {
                $CM_data['lang'] = NeonCookie::getCookie('customer_language');
                $CM_data['AccountNo'] = $Account->Number;

                $name = getLanguageValue($name);
                $Link = str_replace("{ACCOUNTID}", $CM_data['AccountID'], $Link);
                if (strpos($Link, "{COMPANYID}")  !== FALSE ) {
                    $reg = 1;
                }
                $Link = str_replace("{COMPANYID}", $CM_data['CompanyID'], $Link);
                $Link = str_replace("{LANGUAGE}", $CM_data['lang'], $Link);
                $Link = str_replace("{ACCOUNTNUMBER}", $CM_data['AccountNo'], $Link);

                $Link = str_replace("{billingemail1}", $Account->BillingEmail, $Link);
                if(!empty($Account->password)){
                    try{
                        $Link = str_replace("{CustomerPanelPassword}", Crypt::decrypt($Account->password), $Link);
                    }catch(Exception $e){
                        $Link = str_replace("{CustomerPanelPassword}", '', $Link);
                    }
                }

                $rand_no = getRandomNumber(5);
                $hash = $rand_no . base64_encode(serialize($CM_data)) . $rand_no;
                if ($reg == 1) {
                    $Link = $Link . "&hash=" . $hash;
                }
                $ReturnData[] = array('link' => $Link, 'name' => $name);
            }
        }
        return $ReturnData;
    }

}