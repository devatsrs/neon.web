<?php

class RateTableRate extends \Eloquent {

    protected $fillable = [];
    protected $guarded= [];
    protected $table = 'tblRateTableRate';
    protected $primaryKey = "RateTableRateID";

    public static $rules = [
        'RateID' =>      'required',
        'RateTableId' =>      'required',
        'Rate' =>      'required',
        'EffectiveDate' =>      'required',
        'Interval1'=>      'required',
        'IntervalN'=>      'required',
        'TimezonesID'=>      'required',
    ];

}