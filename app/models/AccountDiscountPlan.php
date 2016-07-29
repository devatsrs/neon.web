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

    public static function addUpdateDiscountPlan($AccountID,$DiscountPlanID,$Type,$billdays){
        if( AccountDiscountPlan::where(["AccountID"=> $AccountID,'Type'=>$Type])->pluck('DiscountPlanID') != $DiscountPlanID){
            DB::select('call prc_setAccountDiscountPlan(?,?,?,?,?)',array($AccountID,intval($DiscountPlanID),intval($Type),$billdays,User::get_user_full_name()));
        }
    }
    public static function getDiscountPlan($AccountID,$Type){

        return DB::select('call prc_getAccountDiscountPlan(?,?)',array($AccountID,intval($Type)));

    }

}