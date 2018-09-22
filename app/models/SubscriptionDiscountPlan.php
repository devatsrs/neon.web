<?php

class SubscriptionDiscountPlan extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblSubscriptionDiscountPlan";
    protected $primaryKey = "SubscriptionDiscountPlanID";
    protected $guarded = array('SubscriptionDiscountPlanID');

    public static function getSubscriptionDiscountPlanArray($AccountID,$AccountSubscriptionID,$ServiceID){

        $Where = ["AccountID"=>$AccountID,'AccountSubscriptionID'=>$AccountSubscriptionID,"ServiceID"=>$ServiceID];
        //$SubscriptionDiscountPlan = SubscriptionDiscountPlan::where($Where)->get()->toArray();
        $SubscriptionDiscountPlan = SubscriptionDiscountPlan::leftjoin('tblDiscountPlan as tbldp1', 'tbldp1.DiscountPlanID', '=', 'tblSubscriptionDiscountPlan.InboundDiscountPlans')
            ->leftjoin('tblDiscountPlan as tbldp2', 'tbldp2.DiscountPlanID', '=', 'tblSubscriptionDiscountPlan.OutboundDiscountPlans')
            ->where($Where)
            ->select(array('tblSubscriptionDiscountPlan.*','tbldp1.Name as InboundDiscountPlans','tbldp2.Name as OutboundDiscountPlans'))
            ->get()
            ->toArray();
        return $SubscriptionDiscountPlan;
    }
    public static function getSubscriptionDiscountPlanById($SubscriptionDiscountPlanID){

        $Where = ["SubscriptionDiscountPlanID"=>$SubscriptionDiscountPlanID];
        $SubscriptionDiscountPlan = SubscriptionDiscountPlan::where($Where)->get()->toArray();
        return $SubscriptionDiscountPlan;
    }


}