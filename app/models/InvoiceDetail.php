<?php

class InvoiceDetail extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('InvoiceDetailID');
    protected $table = 'tblInvoiceDetail';
    protected  $primaryKey = "InvoiceDetailID";

}