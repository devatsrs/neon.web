<?php

class TempRateTableDIDRate extends \Eloquent {
    protected $fillable = [];
    public $timestamps = false; // no created_at and updated_at

    protected $guarded = array('TempRateTableDIDRateID');

    protected $table = 'tblTempRateTableDIDRate';

    protected  $primaryKey = "TempRateTableDIDRateID";

}