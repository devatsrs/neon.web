<?php

class AccountOneOffCharge extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv2";
    protected $table = "tblAccountOneOffCharge";
    protected $primaryKey = "AccountOneOffChargeID";
    protected $guarded = array('AccountOneOffChargeID');

    public static $rules = array(
        'AccountID'         =>      'required',
        'SubscriptionID'    =>  'required',
        'StartDate'               =>'required',
        'EndDate'               =>'required'
    );

    public static function  checkForeignKeyById($id){

        if($id>0){
            return false;
        }
    }
}