<?php

class RateTableRateAA extends \Eloquent {

    protected $fillable = [];
    protected $guarded= [];
    protected $table = 'tblRateTableRateAA';
    protected $primaryKey = "RateTableRateAAID";

    public static $rules = [
        'RateID'        => 'required',
        'RateTableId'   => 'required',
        'Rate'          => 'required',
        'EffectiveDate' => 'required',
        'Interval1'     => 'required',
        'IntervalN'     => 'required',
        'TimezonesID'   => 'required',
    ];

}