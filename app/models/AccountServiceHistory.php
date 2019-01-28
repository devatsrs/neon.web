<?php

class AccountServiceHistory extends \Eloquent {
    protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountServiceHistory";
    protected $primaryKey = "AccountServiceContractID";
    protected $guarded = array('AccountServiceContractID');

    public static $rules = array(

    );



}