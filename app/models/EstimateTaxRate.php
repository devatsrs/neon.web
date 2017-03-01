<?php

class EstimateTaxRate extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('EstimateTaxRateID');
    protected $table = 'tblEstimateTaxRate';
    protected  $primaryKey = "EstimateTaxRateID";

}