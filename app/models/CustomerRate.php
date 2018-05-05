<?php

class CustomerRate extends \Eloquent {
	protected $fillable = [];

    protected $table = 'tblCustomerRate';

    protected  $primaryKey = "CustomerRateID";
    public  $timestamps  = false;

}