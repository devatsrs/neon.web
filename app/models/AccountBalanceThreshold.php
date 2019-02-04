<?php

class AccountBalanceThreshold extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountBalanceThreshold";
    protected $primaryKey = "AccountBalanceThresholdID";
    protected $guarded = array('AccountBalanceThresholdID');

    public $timestamps = false; // no created_at and updated_at

}