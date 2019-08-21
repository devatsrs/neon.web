<?php

class AccountBalanceLog extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountBalanceLog";
    protected $primaryKey = "AccountBalanceLogID";
    protected $guarded = array('AccountBalanceLogID');

    public $timestamps = false; // no created_at and updated_at

    public static function getPrepaidAccountBalance($AccountID){
        $BalanceAmount = AccountBalanceLog::where(['AccountID'=>$AccountID])->pluck('BalanceAmount');
        return $BalanceAmount;
    }
}