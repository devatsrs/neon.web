<?php

class AccountDiscountPlan extends \Eloquent
{
    protected $guarded = array("AccountDiscountPlanID");

    protected $table = 'tblAccountDiscountPlan';

    protected $primaryKey = "AccountDiscountPlanID";

    const OUTBOUND = 1;
    const INBOUND = 2;


    public static function checkForeignKeyById($id) {


        /** todo implement this function   */
        return true;
    }

    public static function addUpdateDiscountPlan($AccountID,$DiscountPlanID,$Type,$billdays,$DayDiff,$ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID){
        log::info('add update discountPLan');
        $Today = date('Y-m-d H:i:s');
        if(!Auth::guest()) {
            $CreatedBY = User::get_user_full_name();
        }else{
            $CreatedBY = 'Guest';
        }

        log::info('test prc_setAccountDiscountPlan(?,?,?,?,?,?,?,?,?,?,?,?)',array($AccountID,intval($DiscountPlanID),intval($Type),$billdays,$DayDiff,$CreatedBY,$Today,$ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID));
        if( AccountDiscountPlan::where(["AccountID"=> $AccountID,'Type'=>$Type,'ServiceID'=>$ServiceID,'AccountSubscriptionID'=>$AccountSubscriptionID,'AccountName'=>$AccountName,'AccountCLI'=>$AccountCLI,'SubscriptionDiscountPlanID'=>$SubscriptionDiscountPlanID])->pluck('DiscountPlanID') != $DiscountPlanID){
            $Today = date('Y-m-d H:i:s');
            log::info('call prc_setAccountDiscountPlan(?,?,?,?,?,?,?,?,?,?,?,?)',array($AccountID,intval($DiscountPlanID),intval($Type),$billdays,$DayDiff,$CreatedBY,$Today,$ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID));
            DB::select('call prc_setAccountDiscountPlan(?,?,?,?,?,?,?,?,?,?,?,?)',array($AccountID,intval($DiscountPlanID),intval($Type),$billdays,$DayDiff,$CreatedBY,$Today,$ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID));
        }
    }
    public static function getDiscountPlan($AccountID,$Type,$ServiceID,$AccountSubscriptionID,$SubscriptionDiscountPlanID){
        return DB::select('call prc_getAccountDiscountPlan(?,?,?,?,?)',array($AccountID,intval($Type),$ServiceID,$AccountSubscriptionID,$SubscriptionDiscountPlanID));

    }
    public static function checkDiscountPlan($AccountID){
        return (int)AccountDiscountPlan::where(["AccountID"=> $AccountID])->count();

    }

}