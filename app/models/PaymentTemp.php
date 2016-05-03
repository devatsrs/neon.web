<?php

class PaymentTemp extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrv2';
    protected $guarded = array('PaymentID');
    protected $table = 'tblTempPayment';
    protected  $primaryKey = "PaymentID";

}