<?php

class RateRuleSource extends \Eloquent {
	protected $fillable = [];
	protected $guarded = array();
    protected $table = 'tblRateRuleSource';
    protected $primaryKey = "RateRuleSourceId";

//    public function raterule()
//    {
//        return $this->belongsTo('RateRule');
//    }
}