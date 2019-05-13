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
        if(!empty($post['counttr'])){
            $thList = $post['counttr'];
            for ($k = 0; $k <= $thList; $k++) {
                if(!empty($post['BalanceThresholdnew-'.$k])){
                    $data = [
                        'AccountID'         => $accountid,
                        'BalanceThreshold'  => $post['BalanceThresholdnew-'.$k],
                        'created_at'        => $date,
                        'updated_at'        => $date,
                        "BalanceThresholdEmail" =>$post['email-'.$k]
                    ];
                    AccountBalanceThreshold::insert($data);
                }
            }
        }
    }
}