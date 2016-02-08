<?php

class RateRuleMargin extends \Eloquent {
	protected $fillable = [];
	protected $guarded = array();
    protected $table = 'tblRateRuleMargin';
    protected $primaryKey = "RateRuleMarginId";

//    public function raterule()
//    {
//        return $this->belongsTo('RateRule');
//    }
}