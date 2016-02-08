<?php

class AccountSubscription extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv2";
    protected $table = "tblAccountSubscription";
    protected $primaryKey = "AccountSubscriptionID";
    protected $guarded = array('AccountSubscriptionID');

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