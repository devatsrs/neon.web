<?php

class AccountServiceContract extends \Eloquent {
    protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountServiceContract";
    protected $primaryKey = "AccountServiceContractID";
    protected $guarded = array('AccountServiceContractID');

    public static $rules = array(

    );



}