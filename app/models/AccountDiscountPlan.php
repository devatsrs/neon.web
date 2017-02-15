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

    public static function addUpdateDiscountPlan($AccountID,$DiscountPlanID,$Type,$billdays,$DayDiff,$ServiceID){
        if(empty($ServiceID)){
            $ServiceID = 0;
        }
        if( AccountDiscountPlan::where(["AccountID"=> $AccountID,'Type'=>$Type,'ServiceID'=>$ServiceID])->pluck('DiscountPlanID') != $DiscountPlanID){
            $Today = date('Y-m-d H:i:s');
            DB::select('call prc_setAccountDiscountPlan(?,?,?,?,?,?,?,?)',array($AccountID,intval($DiscountPlanID),intval($Type),$billdays,$DayDiff,User::get_user_full_name(),$Today,$ServiceID));
        }
    }
    public static function getDiscountPlan($AccountID,$Type,$ServiceID){
        if(empty($ServiceID)){
            $ServiceID = 0;
        }
        return DB::select('call prc_getAccountDiscountPlan(?,?,?)',array($AccountID,intval($Type),$ServiceID));

    }

}