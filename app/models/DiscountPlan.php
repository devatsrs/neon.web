<?php

class DiscountPlan extends \Eloquent
{
    protected $guarded = array("DiscountPlanID");

    protected $table = 'tblDiscountPlan';

    protected $primaryKey = "DiscountPlanID";

    const VOLUME_MINUTES = 1;

    public static  $discount_service = array(''=>'Select a Discount Type',self::VOLUME_MINUTES=>'Volume,Minutes');

    public static function checkForeignKeyById($id) {


        /** todo implement this function   */
        return true;
    }
    public static function getDropdownIDList($CompanyID,$CurrencyID){
        $DropdownIDList = DiscountPlan::where(array("CompanyID"=>$CompanyID,'CurrencyID'=>$CurrencyID))->lists('Name', 'DiscountPlanID');
        $DropdownIDList = array('' => "Select a Discount Plan") + $DropdownIDList;
        return $DropdownIDList;
    }

}