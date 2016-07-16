<?php

class AccountAuthenticate extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblAccountAuthenticate';
    public $timestamps = false; // no created_at and updated_at
    protected  $primaryKey = "AccountAuthenticateID";
    public static $rules = array(
        'AccountID' =>      'required',
    );

    public static function validate_ips($data){
        $accountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();

        if (isset($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'IP') {
            $dbIPs = explode(',', $accountAuthenticate->CustomerAuthValue);
            $postIPs = $data['CustomerAuthValue'];
        } elseif (isset($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'IP') {
            $dbIPs = explode(',', $accountAuthenticate->VendorAuthValue);
            $postIPs = $data['VendorAuthValue'];
        }

        $iPsExist = array_intersect($dbIPs, $postIPs);
        $toBeInsert =array_unique(array_merge($dbIPs,$postIPs));

        $status['iPsExist'] = $iPsExist;
        $status['toBeInsert'] = $toBeInsert;
        return $status;
    }

    public static function validate_clis($data){
        $accountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();

        if (isset($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'CLI') {
            $dbCLIs = explode(',', $accountAuthenticate->CustomerAuthValue);
            $postCLIs = $data['CustomerAuthValue'];
        } elseif (isset($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'CLI') {
            $dbCLIs = explode(',', $accountAuthenticate->VendorAuthValue);
            $postCLIs = $data['VendorAuthValue'];
        }

        $CLIsExist = array_intersect($dbCLIs, $postCLIs);
        $toBeInsert =array_unique(array_merge($dbCLIs,$postCLIs));

        $status['CLIExist'] = $CLIsExist;
        $status['toBeInsert'] = $toBeInsert;
        return $status;
    }
}