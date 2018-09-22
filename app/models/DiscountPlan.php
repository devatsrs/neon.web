<?php

class DiscountPlan extends \Eloquent
{
    protected $guarded = array("DiscountPlanID");

    protected $table = 'tblDiscountPlan';

    protected $primaryKey = "DiscountPlanID";

    const VOLUME_MINUTES = 1;

    public static  $discount_service = array(''=>'Select',self::VOLUME_MINUTES=>'Volume,Minutes');

    public static function checkForeignKeyById($id) {


        /** todo implement this function   */
        return true;
    }
    public static function getDropdownIDList($CompanyID,$CurrencyID){
        $DropdownIDList = DiscountPlan::where(array("CompanyID"=>$CompanyID,'CurrencyID'=>$CurrencyID))->lists('Name', 'DiscountPlanID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }
    public static function getName($DiscountPlanID){
        return DiscountPlan::where("DiscountPlanID",$DiscountPlanID)->pluck('Name');
    }
    public static function isDiscountPlanApplied($Action,$DestinationGroupSetID,$DiscountPlanID){
        $DiscountPlan  = DB::select('call prc_isDiscountPlanApplied(?,?,?)',array($Action,$DestinationGroupSetID,$DiscountPlanID));
        if(count($DiscountPlan)){
            return 1;
        }
        return 0;
    }

    public static function  getDiscountPlanIDList($data){
        $company_id = User::get_companyID();
        $row = DiscountPlan::where(['CompanyID'=>$company_id])->lists('Name','DiscountPlanID');
        $row = array(""=> "Select") + $row;
        return $row;

    }
    public static function getDropdownIDListByAccount($AccountID){
        $Account = Account::find($AccountID);
        $DropdownIDList = DiscountPlan::where(array("CompanyID"=>$Account->CompanyId,'CurrencyID'=>$Account->CurrencyId))->lists('Name', 'DiscountPlanID');
        //$DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

}