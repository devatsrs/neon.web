<?php

class RateTableDIDRate extends \Eloquent {

    protected $fillable = [];
    protected $guarded= [];
    protected $table = 'tblRateTableDIDRate';
    protected $primaryKey = "RateTableDIDRateID";

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

    public static $Components = array(
        "OneOffCost"                => "One-Off cost",
        "MonthlyCost"               => "Monthly cost",
        "CostPerCall"               => "Cost Per Call",
        "CostPerMinute"             => "Cost Per Minute",
        "SurchargePerCall"          => "Surcharge Per Call",
        "SurchargePerMinute"        => "Surcharge Per Minute",
        "OutpaymentPerCall"         => "Outpayment Per Call",
        "OutpaymentPerMinute"       => "Outpayment Per Minute",
        "Surcharges"                => "Surcharges",
        "Chargeback"                => "Chargeback",
        "CollectionCostAmount"      => "Collection Cost Amount",
        "CollectionCostPercentage"  => "Collection Cost (%)",
        "RegistrationCostPerNumber" => "Registration Cost Per Number",
    );
}