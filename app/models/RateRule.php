<?php

class RateRule extends \Eloquent {
	protected $fillable = [];
	protected $guarded = array();
    protected $table = 'tblRateRule';
    protected $primaryKey = "RateRuleId";

    public function raterulesource()
    {
        return $this->hasMany('RateRuleSource','RateRuleId');
    }
    public function raterulemargin()
    {
        return $this->hasMany('RateRuleMargin','RateRuleId');
    }
    public function country()
    {
        return $this->belongsTo('Country','CountryID');
    }
}