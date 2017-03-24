<?php

class AccountTariff extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountTariff";
    protected $primaryKey = "AccountTariffID";
    protected $guarded = array('AccountTariffID');

    public static $rules = array(
        'AccountID'    =>  'required',
        'ServiceID'    =>  'required',
		'RateTableID'  => 'required'
    );
	
	const OUTBOUND = 1;
    const INBOUND = 2;

    public static function  checkForeignKeyById($id){

        if($id>0){
            return false;
        }
    }
}