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
        $dbIPs = [];
        if (isset($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'IP') {
            if(!empty($accountAuthenticate)) {
                $dbIPs = explode(',', $accountAuthenticate->CustomerAuthValue);
            }
            $postIPs = $data['CustomerAuthValue'];
        } elseif (isset($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'IP') {
            if(!empty($accountAuthenticate)) {
                $dbIPs = explode(',', $accountAuthenticate->VendorAuthValue);
            }
            $postIPs = $data['VendorAuthValue'];
        }

        $iPsExist = array_intersect($dbIPs, $postIPs);
        $toBeInsert =array_unique(array_merge($dbIPs,$postIPs));

        $status['iPsExist'] = $iPsExist;
        $status['toBeInsert'] = $toBeInsert;
        return $status;
    }

    public static function validate_ipclis($data){
        $dbValue = [];
        $status = ['status'=>0,'message'=>'Some thing wrong with updating account','data'=>[]];
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
        $type = $data['type']==1?'CLI':'IP';
        if(empty($data['ipclis'])){
            $status['message'] = $isCustomerOrVendor." ".$type." required";
            return $status;
        }

        $ipclis = preg_split("/\\r\\n|\\r|\\n/", $data['ipclis']);
        $checkstr = implode(',',$ipclis);
        $count = AccountAuthenticate::where(['CompanyID'=>$data['CompanyID']])->where(function($query)use($checkstr){
            $query->whereRaw('find_in_set(CustomerAuthValue,"'.$checkstr.'")');
            $query->orwhereRaw('find_in_set(VendorAuthValue,"'.$checkstr.'")');
        })->count();
        if($count>0){
            $status['message'] = $type." already taken";
            return $status;
        }

        $rule = AccountAuthenticate::where(['CompanyID'=>$data['CompanyID'],'AccountID'=>$data['AccountID']])->first();
        if($data['isCustomerOrVendor'] == 1){
            $data['CustomerAuthRule'] = $type;
            $data['CustomerAuthValue'] = $ipclis;
            if(!empty($rule) && $rule->CustomerAuthRule!=$data['CustomerAuthRule']){ //if saving new rule discard existing CustomerAuthValue.
                AccountAuthenticate::where(['CompanyID'=>$data['CompanyID'],'AccountID'=>$data['AccountID']])->update(['CustomerAuthValue'=>'']);
            }
        }else{
            $data['VendorAuthRule'] = $type;
            $data['VendorAuthValue'] = $ipclis;
            if(!empty($rule) && $rule->VendorAuthRule!=$data['VendorAuthRule']){ //if saving new rule discard existing CustomerAuthValue.
                AccountAuthenticate::where(['CompanyID'=>$data['CompanyID'],'AccountID'=>$data['AccountID']])->update(['VendorAuthValue'=>'']);
            }
        }
        if (isset($data['CustomerAuthRule'])) {
            if(!empty($rule)) {
                $dbValue = explode(',', $rule->CustomerAuthValue);
            }
            $postValue = $data['CustomerAuthValue'];
            $valueExist = array_intersect($dbValue, $postValue);
            $toBeInsert =array_unique(array_merge($dbValue,$postValue));
            $data['CustomerAuthValue']= $toBeInsert;
            $data['CustomerAuthValue'] = implode(',',$toBeInsert);
            $data['CustomerAuthValue'] = ltrim($data['CustomerAuthValue'],',');
        } elseif (isset($data['VendorAuthRule'])) {
            if(!empty($rule)) {
                $dbValue = explode(',', $rule->VendorAuthValue);
            }
            $postValue = $data['VendorAuthValue'];
            $valueExist = array_intersect($dbValue, $postValue);
            $toBeInsert =array_unique(array_merge($dbValue,$postValue));
            $data['VendorAuthValue'] = implode(',',$toBeInsert);
            $data['VendorAuthValue'] = ltrim($data['VendorAuthValue'],',');
        }
        $status['status'] = 1;
        $status['message']='Account Successfully Updated';
        if(count($valueExist)>0){
            $valueExist = implode('<br>',$valueExist);
            $status['message'] .= ' and following '.$type.' already exist. '.$valueExist;
        }
        unset($data['ipclis']);
        unset($data['isCustomerOrVendor']);
        unset($data['type']);
        $status['data'] = $data;
        return $status;
    }
}