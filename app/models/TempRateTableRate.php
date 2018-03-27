<?php

class TempRateTableRate extends \Eloquent {
    protected $fillable = [];
    public $timestamps = false; // no created_at and updated_at

    protected $guarded = array('TempRateTableRateID');

    protected $table = 'tblTempRateTableRate';

    protected  $primaryKey = "TempRateTableRateID";

}