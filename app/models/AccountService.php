<?php

class AccountService extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountService";
    protected $primaryKey = "AccountServiceID";
    protected $guarded = array('AccountServiceID');

    public static $rules = array(

    );

    public static $messages = array(
        'BillingType.required' =>'Billing TYpe is required',
        'BillingTimezone.required' =>'Billing Timezone is required',
        'BillingCycleType.required' =>'Billing Cycle Type is required',
        'BillingStartDate.required' =>'Billing Cycle Date is required',
        'BillingCycleValue.required' =>'Billing Cycle Value field is required',
        'BillingClassID.required' =>'Billing Class is required',
    );

    public static function  checkForeignKeyById($id){

        if($id>0){
            return false;
        }
    }
}