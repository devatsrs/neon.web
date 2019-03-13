<?php

class RateTablePKGRate extends \Eloquent {

    protected $fillable = [];
    protected $guarded= [];
    protected $table = 'tblRateTablePKGRate';
    protected $primaryKey = "RateTablePKGRateID";

    public static $rules = [
        'RateID'        =>      'required',
        'RateTableId'   =>      'required',
        'EffectiveDate' =>      'required',
        'TimezonesID'   =>      'required',
        'MonthlyCost'   =>      'required_without_all:OneOffCost,PackageCostPerMinute,RecordingCostPerMinute',
    ];

    public static $message = [
        'MonthlyCost.required_without_all'  =>      'Any one cost component is required.'
    ];

    public static $Components = array(
        "OneOffCost"                => "One-Off cost",
        "MonthlyCost"               => "Monthly cost",
        "PackageCostPerMinute"      => "Package Cost Per Minute",
        "RecordingCostPerMinute"    => "Recording Cost Per Minute",
    );
}