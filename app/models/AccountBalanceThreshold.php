<?php

class AccountBalanceThreshold extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountBalanceThreshold";
    protected $primaryKey = "AccountBalanceThresholdID";
    protected $guarded = array('AccountBalanceThresholdID');

    public $timestamps = false; // no created_at and updated_at
    public static function saveAccountBalanceThreshold($accountid, $post)
    {
        
        foreach ($post['BalanceThresholdnew'] as $key => $value) {
            $data = [
            'AccountID' => $accountid,
            'BalanceThreshold' => $value,
            "BalanceThresholdEmail" =>$post['email'][$key]
        ];
            AccountBalanceThreshold::insert($data);
        }

    }
}