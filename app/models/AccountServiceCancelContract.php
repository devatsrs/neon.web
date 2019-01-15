<?php

class AccountServiceCancelContract extends \Eloquent {
    protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountServiceCancelContract";
    protected $primaryKey = "AccountServiceCancelContractID";
    protected $guarded = array('AccountServiceCancelContractID');

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



}