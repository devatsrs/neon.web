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
        $date = date('Y-m-d H:i:s');
        $CreatedBy = User::get_user_full_name();
        if(!empty($post['BalanceThresholdnew'])){
            foreach ($post['BalanceThresholdnew'] as $key => $value) {
                if(!empty($value)){
                    $data = [
                        'AccountID'         => $accountid,
                        'BalanceThreshold'  => $value,
                        'created_at'        => $date,
                        "BalanceThresholdEmail" =>$post['email'][$key]
                    ];
                    AccountBalanceThreshold::insert($data);
                }
            }
        }
    }
}