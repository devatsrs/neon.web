<?php

class RecurringTaxRate extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('RecurringInvoiceTaxRateID');
    protected $table = 'tblRecurringInvoiceTaxRate';
    protected  $primaryKey = "RecurringInvoiceTaxRateID";

}