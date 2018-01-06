<?php

class BillingSubscription extends \Eloquent {

    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('SubscriptionID');
    protected $table = 'tblBillingSubscription';
    public  $primaryKey = "SubscriptionID";
    static protected  $enable_cache = false;
    public static $cache = ["subscription_dropdown1_cache"];
	
	public static $Advance = array(''=>'All',0=>"Off",1=>"On");
    const Customer = 0;
    const Reseller = 1;

    public static $AppliedTo = array(self::Customer=>"Customer",self::Reseller=>"Reseller");
    public static $ALLAppliedTo = array(''=>'Select',self::Customer=>"Customer",self::Reseller=>"Reseller");


    static public function checkForeignKeyById($id) {

        $hasInAccountSubscription = AccountSubscription::where("SubscriptionID",$id)->count();
        if( intval($hasInAccountSubscription) > 0 ){
            return true;
        }else{
            return false;
        }
    }

    public static function getSubscriptionsArray($CompanyID,$CurrencyID,$AppliedTo=0){
        if($AppliedTo==="All"){
            $Where = ["CompanyID"=>$CompanyID,'CurrencyID'=>$CurrencyID];
        }else{
            $Where = ["CompanyID"=>$CompanyID,'CurrencyID'=>$CurrencyID,"AppliedTo"=>$AppliedTo];
        }
        $BillingSubscription = BillingSubscription::where($Where)->get();
        $subscription = array();
        $subscription[''] = "Select";
        foreach($BillingSubscription as $Subscription){
            $subscription[$Subscription->SubscriptionID] =$Subscription->Name;
        }
        return $subscription;
    }

    public static function getSubscriptionsList($CompanyID=0,$AppliedTo=0){

        if (self::$enable_cache && Cache::has('subscription_dropdown1_cache')) {
            $admin_defaults = Cache::get('subscription_dropdown1_cache');
            self::$cache['subscription_dropdown1_cache'] = $admin_defaults['subscription_dropdown1_cache'];
        } else {
            $CompanyID = $CompanyID>0?$CompanyID : User::get_companyID();
            if($AppliedTo==="All"){
                $Where = ["CompanyID"=>$CompanyID];
            }else{
                $Where = ["CompanyID"=>$CompanyID,"AppliedTo"=>$AppliedTo];
            }
            self::$cache['subscription_dropdown1_cache'] = BillingSubscription::where($Where)->lists('Name','SubscriptionID');

            Cache::forever('subscription_dropdown1_cache', array('subscription_dropdown1_cache' => self::$cache['subscription_dropdown1_cache']));
        }

        return self::$cache['subscription_dropdown1_cache'];
    }

    public static function getSubscriptionsListByAppliedTo($CompanyID=0,$AppliedTo){
        $CompanyID = $CompanyID>0?$CompanyID : User::get_companyID();
        $BillingSubscription = BillingSubscription::where(["CompanyID"=>$CompanyID,"AppliedTo"=>$AppliedTo])->lists('Name','SubscriptionID');
        return $BillingSubscription;
    }

    public static function getSubscriptionNameByID($SubscriptionID){
        if($SubscriptionID > 0){
            $Name = BillingSubscription::where("SubscriptionID",$SubscriptionID)->pluck("Name");
            return $Name;
        }
    }
    public static function clearCache(){

        Cache::flush("subscription_dropdown1_cache");

    }

}