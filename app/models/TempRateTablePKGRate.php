<?php

class TempRateTablePKGRate extends \Eloquent {
    protected $fillable = [];
    public $timestamps = false; // no created_at and updated_at

    protected $guarded = array('TempRateTablePKGRateID');

    protected $table = 'tblTempRateTablePKGRate';

    protected  $primaryKey = "TempRateTablePKGRateID";

}