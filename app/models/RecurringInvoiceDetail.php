<?php

class RecurringInvoiceDetail extends \Eloquent {
    protected $connection 	= 'sqlsrv2';
    protected $fillable 	= [];
    protected $guarded 		= array('RecurringInvoiceDetailID');
    protected $table 		= 'tblRecurringInvoiceDetail';
    protected $primaryKey 	= "RecurringInvoiceDetailID";

}