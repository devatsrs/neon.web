<?php

class AccountSubsDynamicFields extends \Eloquent {

    protected $connection = 'sqlsrv2';
    protected $table = 'tblAccountSubsDynamicFields';
    public  $primaryKey = "AccountSubsDynamicFieldsID";
    public $timestamps = false;
    protected $fillable = ['AccountSubscriptionID', 'AccountID', 'DynamicFieldsID', 'FieldValue', 'FieldOrder'];

}