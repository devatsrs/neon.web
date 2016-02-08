<?php
class InvoiceTaxRate extends \Eloquent {


    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('InvoiceTaxRateID');
    protected $table = 'tblInvoiceTaxRate';
    protected  $primaryKey = "InvoiceTaxRateID";


}