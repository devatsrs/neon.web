<?php
class CurrencyConversionLog extends \Eloquent {

    protected $table = 'tblCurrencyConversionLog';
    protected $primaryKey = "ConversionLogID";
    protected $fillable = [];
    protected $guarded = ['ConversionLogID'];

    public $timestamps = false; // no created_at and updated_at

}