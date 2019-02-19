<?php

class AccountAdditionalCharge extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv2";
    protected $table = "tblAccountAdditionalCharge";
    protected $primaryKey = "AccountAdditionalChargeID";
    protected $guarded = array('AccountAdditionalChargeID');

    public static $rules = array(
        'AccountID'         =>      'required',
    );

    public static function  checkForeignKeyById($id){
        if($id>0){
            return false;
        }
    }
}