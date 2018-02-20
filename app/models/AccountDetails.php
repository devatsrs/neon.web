<?php

class AccountDetails extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountDetails";
    protected $primaryKey = "AccountDetailID";
    protected $guarded = array('AccountDetailID');

    public $timestamps = false; // no created_at and updated_at

}