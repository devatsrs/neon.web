<?php

class AccountDiscountPlan extends \Eloquent
{
    protected $guarded = array("AccountDiscountPlanID");

    protected $table = 'tblAccountDiscountPlan';

    protected $primaryKey = "AccountDiscountPlanID";


    public static function checkForeignKeyById($id) {


        /** todo implement this function   */
        return true;
    }

    public static function addUpdateDiscountPlan($AccountID,$DiscountPlanID){
        if( AccountDiscountPlan::where(["AccountID"=> $AccountID])->count() == 0){
            DB::select('call prc_setAccountDiscountPlan(?,?,?)',array($AccountID,$DiscountPlanID,User::get_user_full_name()));
        }
    }
    public static function getDiscountPlan($AccountID){

        return DB::select('call prc_getAccountDiscountPlan(?)',array($AccountID));

    }

}