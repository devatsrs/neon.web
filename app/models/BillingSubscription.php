<?php

class BillingSubscription extends \Eloquent {

    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('SubscriptionID');
    protected $table = 'tblBillingSubscription';
    public  $primaryKey = "SubscriptionID";
    static protected  $enable_cache = false;
    public static $cache = ["subscription_dropdown1_cache"];

    static public function checkForeignKeyById($id) {

        $hasInAccountSubscription = AccountSubscription::where("SubscriptionID",$id)->count();
        if( intval($hasInAccountSubscription) > 0 ){
            return true;
        }else{
            return false;
        }
    }

    public static function getSubscriptionsArray($CompanyID,$CurrencyID){
        $BillingSubscription = BillingSubscription::where(array("CompanyID"=>$CompanyID,'CurrencyID'=>$CurrencyID))->get();
        $subscription = array();
        $subscription[''] = "Select a Subscription";
        foreach($BillingSubscription as $Subscription){
            $subscription[$Subscription->SubscriptionID] =$Subscription->Name;
        }
        return $subscription;
    }

    public static function getSubscriptionsList(){

        if (self::$enable_cache && Cache::has('subscription_dropdown1_cache')) {
            $admin_defaults = Cache::get('subscription_dropdown1_cache');
            self::$cache['subscription_dropdown1_cache'] = $admin_defaults['subscription_dropdown1_cache'];
        } else {
            $CompanyId = User::get_companyID();
            self::$cache['subscription_dropdown1_cache'] = BillingSubscription::where("CompanyId",$CompanyId)->lists('Name','SubscriptionID');
            Cache::forever('subscription_dropdown1_cache', array('subscription_dropdown1_cache' => self::$cache['subscription_dropdown1_cache']));
        }

        return self::$cache['subscription_dropdown1_cache'];
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