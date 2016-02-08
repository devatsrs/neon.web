<?php

class RateSheetDetails extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('RateSheetDetailsID');

    protected $table = 'tblRateSheetDetails';

    protected  $primaryKey = "RateSheetDetailsID";
}