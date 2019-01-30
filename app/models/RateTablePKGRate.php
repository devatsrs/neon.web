<?php

class RateTablePKGRate extends \Eloquent {

    protected $fillable = [];
    protected $guarded= [];
    protected $table = 'tblRateTablePKGRate';
    protected $primaryKey = "RateTablePKGRateID";

    public static $rules = [
        'RateID'        =>      'required',
        'RateTableID'   =>      'required',
        'EffectiveDate' =>      'required',
        'TimezonesID'   =>      'required',
    ];

}