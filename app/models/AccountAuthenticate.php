<?php

class AccountAuthenticate extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblAccountAuthenticate';
    public $timestamps = false; // no created_at and updated_at
    protected  $primaryKey = "AccountAuthenticateID";
    public static $rules = array(
        'AccountID' =>      'required',
    );

    public static function validate_ipclis($data){
        $dbValue = [];
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        $status = ['status'=>0,'message'=>'','data'=>[]];
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
        $type = $data['type']==1?'CLI':'IP';
        if(empty($data['ipclis'])){
            $status['message'] = $isCustomerOrVendor." ".$type." required";
            return $status;
        }
        $ipclis = array_filter(preg_split("/\\r\\n|\\r|\\n/", $data['ipclis']),function($var){return trim($var)!='';});
        $ipclis = str_replace('"','',$ipclis);
        $ipclis = str_replace("'","",$ipclis);
        $ipclis = str_replace(',','',$ipclis);
        $ipclis = array_filter(array_map('trim', $ipclis), 'strlen');
        $ipclist = implode(',',$ipclis);
        $query = "CALL prc_AddAccountIPCLI(".$data['CompanyID'].",".$data['AccountID'].",".$data['isCustomerOrVendor'].",'".$ipclist."','".$type."',".$ServiceID.")";
        $found = DB::select($query);
        $validation = '';
        if(!empty($found)) {
            $status['message'] = 'Account Successfully Updated.';

            foreach ($found as $obj) {
                $temp = explode(',',$obj->IPCLI);
                $intersect = array_intersect($ipclis,$temp);
                if (!empty($intersect)) {
                    foreach($intersect as $index=>$value) {
                        $validation .= $value . ' ' . $type . ' already exist against '.$obj->AccountName.'.<br>';
                    }
                }
            }
        }

        if(!empty($validation)){
            $status['message'] .= '<br>Following '.$type.' skipped.<br>'.$validation;
        }

        /*$rule = AccountAuthenticate::where(['CompanyID'=>$data['CompanyID'],'AccountID'=>$data['AccountID']])->first();
        if($data['isCustomerOrVendor'] == 1){
            $data['CustomerAuthRule'] = $type;
            $data['CustomerAuthValue'] = $ipclis;
            if(!empty($rule) && $rule->CustomerAuthRule!=$data['CustomerAuthRule']){ //if saving new rule discard existing CustomerAuthValue.
                AccountAuthenticate::where(['CompanyID'=>$data['CompanyID'],'AccountID'=>$data['AccountID']])->update(['CustomerAuthValue'=>'']);
                $rule->CustomerAuthValue = '';
            }
        }else{
            $data['VendorAuthRule'] = $type;
            $data['VendorAuthValue'] = $ipclis;
            if(!empty($rule) && $rule->VendorAuthRule!=$data['VendorAuthRule']){ //if saving new rule discard existing CustomerAuthValue.
                AccountAuthenticate::where(['CompanyID'=>$data['CompanyID'],'AccountID'=>$data['AccountID']])->update(['VendorAuthValue'=>'']);
                $rule->VendorAuthValue = '';
            }
        }
        if (isset($data['CustomerAuthRule'])) {
            if(!empty($rule)) {
                $dbValue = explode(',', $rule->CustomerAuthValue);
            }
            $postValue = $data['CustomerAuthValue'];
            $toBeInsert =array_unique(array_merge($dbValue,$postValue));
            $data['CustomerAuthValue']= $toBeInsert;
            $data['CustomerAuthValue'] = implode(',',$toBeInsert);
            $data['CustomerAuthValue'] = ltrim($data['CustomerAuthValue'],',');
        } elseif (isset($data['VendorAuthRule'])) {
            if(!empty($rule)) {
                $dbValue = explode(',', $rule->VendorAuthValue);
            }
            $postValue = $data['VendorAuthValue'];
            $toBeInsert =array_unique(array_merge($dbValue,$postValue));
            $data['VendorAuthValue'] = implode(',',$toBeInsert);
            $data['VendorAuthValue'] = ltrim($data['VendorAuthValue'],',');
        }*/
        $status['status'] = 1;
        /*unset($data['ipclis']);
        unset($data['isCustomerOrVendor']);
        unset($data['type']);
        $status['data'] = $data;*/
        return $status;
    }

    public static function add_cli_rule($CompanyID,$data){
        if(!empty($data['AuthRule'])){
            $AccountAuthenticate = array();
            $AccountAuthenticate['CustomerAuthRule'] = 'CLI';
            $AccountAuthenticate['CustomerAuthValue'] = '';

            if(!empty($data['ServiceID'])){

                if(AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$data['ServiceID']))->count()){
                    AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$data['ServiceID']))->update($AccountAuthenticate);
                }else{
                    $AccountAuthenticate['AccountID'] = $data['AccountID'];
                    $AccountAuthenticate['CompanyID'] = $CompanyID;
                    $AccountAuthenticate['ServiceID'] = $data['ServiceID'];
                    AccountAuthenticate::insert($AccountAuthenticate);
                }

            }else{
                if(AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>0))->count()){
                    AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>0))->update($AccountAuthenticate);
                }else{
                    $AccountAuthenticate['AccountID'] = $data['AccountID'];
                    $AccountAuthenticate['CompanyID'] = $CompanyID;
                    AccountAuthenticate::insert($AccountAuthenticate);
                }
            }

        }
    }
}