<?php

class RateTableDIDRateAA extends \Eloquent {

    protected $fillable = [];
    protected $guarded= [];
    protected $table = 'tblRateTableDIDRateAA';
    protected $primaryKey = "RateTableDIDRateAAID";

    public static $rules = [
        'RateID'        =>      'required',
        'RateTableId'   =>      'required',
        'EffectiveDate' =>      'required',
        'TimezonesID'   =>      'required',
        'MonthlyCost'   =>      'required_without_all:OneOffCost,CostPerCall,CostPerMinute,SurchargePerCall,SurchargePerMinute,OutpaymentPerCall,OutpaymentPerMinute,Surcharges,Chargeback,CollectionCostAmount,CollectionCostPercentage,RegistrationCostPerNumber',
    ];

    public static $message = [
        'MonthlyCost.required_without_all'  =>      'Any one cost component is required.'
    ];

}