<?php

class AccountAdditionalCharge extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv2";
    protected $table = "tblAccountAdditionalCharge";
    protected $primaryKey = "AccountAdditionalChargeID";
    protected $guarded = array('AccountAdditionalChargeID');

}