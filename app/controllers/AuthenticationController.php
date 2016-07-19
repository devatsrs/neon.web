<?php

/**
 * Created by PhpStorm.
 * User: srs2
 * Date: 23/02/2016
 * Time: 12:33
 */
class AuthenticationController extends \BaseController
{
    public function __construct(){

    }
    /** Account Authentication Rule */
    public function authenticate($id){
        $account = Account::find($id);
        $AccountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$id))->first();
        return View::make('accounts.authenticate', compact('account','AccountAuthenticate'));
    }
    public function authenticate_store(){
        $data = Input::all();
        $data['CompanyID'] = $CompanyID = User::get_companyID();
        $rule = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();
        if(isset($data['VendorAuthRuleText'])) {
            unset($data['VendorAuthRuleText']);
        }
        if(isset($data['CustomerAuthValueText'])) {
            unset($data['CustomerAuthValueText']);
        }
        if(isset($data['VendorAuthValue'])){
            if(!empty($data['VendorAuthRule'])){ //if rule changes and value not changed, reset the values.
                if($rule->VendorAuthRule!=$data['VendorAuthRule'] && $rule->VendorAuthValue==$data['VendorAuthValue']){
                    $data['VendorAuthValue'] = '';
                }else{
                    $data['VendorAuthValue'] = implode(',', array_unique(explode(',', $data['VendorAuthValue'])));
                }
            }
        }
        if(isset($data['CustomerAuthValue'])){  //if rule changed and value not changed, reset the values.
            if(!empty($data['CustomerAuthRule'])){
                if($rule->CustomerAuthRule!=$data['CustomerAuthRule'] && $rule->CustomerAuthValue==$data['CustomerAuthValue']){
                    $data['CustomerAuthValue'] = '';
                }else{
                    $data['CustomerAuthValue'] = implode(',', array_unique(explode(',', $data['CustomerAuthValue'])));
                }
            }
        }
        if(empty($data['VendorAuthRule']) || ($data['VendorAuthRule'] != 'IP'&& $data['VendorAuthRule'] != 'CLI' && $data['VendorAuthRule']!='Other')){
            $data['VendorAuthValue']=''; //if rule other then ip,cli and other, reset the value.
        }else if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'Other' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor Other Value required"));
        }
        if(empty($data['CustomerAuthRule']) || ($data['CustomerAuthRule'] != 'IP' && $data['CustomerAuthRule'] != 'CLI' && $data['CustomerAuthRule']!='Other')){
            $data['CustomerAuthValue']='';  //if rule other then ip,cli and other, reset the value.
        }elseif(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'Other' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer Other Value required"));
        }
        unset($data['vendoriptable_length']);
        unset($data['vendorclitable_length']);
        unset($data['customeriptable_length']);
        unset($data['customerclitable_length']);
        if(AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->count()){
            AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->update($data);
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
        }else{
            AccountAuthenticate::insert($data);
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
        }
        return Response::json(array("status" => "failed", "message" => "Problem Updating Account."));
    }

   public function addIps($id){
       $data = Input::all();
       $data['AccountID'] = $id;
       $message = '';
       $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
       $data['CompanyID'] = $CompanyID = User::get_companyID();
       if(empty($data['ipclis'])){
           return Response::json(array("status" => "error", "message" => $isCustomerOrVendor." IP required"));
       }
       $ips = preg_split("/\\r\\n|\\r|\\n/", $data['ipclis']);
       $checkstr = implode(',',$ips);
       $count = AccountAuthenticate::where(['CompanyID'=>$CompanyID])->where(function($query)use($checkstr){
           $query->whereRaw('find_in_set(CustomerAuthValue,"'.$checkstr.'")');
           $query->orwhereRaw('find_in_set(VendorAuthValue,"'.$checkstr.'")');
       })->count();
       if($count>0){
           return Response::json(array("status" => "error", "message" => "IP already taken"));
       }
       $rule = AccountAuthenticate::where(['CompanyID'=>$CompanyID,'AccountID'=>$data['AccountID']])->first();
       unset($data['ipclis']);
       unset($data['isCustomerOrVendor']);
       if($isCustomerOrVendor=='Customer'){
           $data['CustomerAuthRule'] = 'IP';
           $data['CustomerAuthValue'] = $ips;
           if(!empty($rule) && $rule->CustomerAuthRule!=$data['CustomerAuthRule']){ //if saving new rule discard existing CustomerAuthValue.
               AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->update(['CustomerAuthValue'=>'']);
           }
       }else{
           $data['VendorAuthRule'] = 'IP';
           $data['VendorAuthValue'] = $ips;
           if(!empty($rule) && $rule->VendorAuthRule!=$data['VendorAuthRule']){    //if saving new rule discard existing VendorAuthValue.
               AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->update(['VendorAuthValue'=>'']);
           }
       }
       $status = AccountAuthenticate::validate_ips($data);
       if(count($status['iPsExist'])>0){
           $iPsExist = implode('<br>',$status['iPsExist']);
           $message = ' and following IPs already exist. '.$iPsExist;
       }
       if(count($status['toBeInsert'])>0){
           if($isCustomerOrVendor=='Customer') {
               $data['CustomerAuthValue'] = ltrim(implode(',',$status['toBeInsert']),',');
           }else{
               $data['VendorAuthValue'] = ltrim(implode(',',$status['toBeInsert']),',');
           }

           if(AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->count() >0){
               AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->update($data);
           }else{
               AccountAuthenticate::insert($data);
           }
           $object = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();
           return Response::json(array("status" => "success","ipclis"=> $status['toBeInsert'],"object"=>$object, "message" => "Account Successfully Updated".$message));
       }
   }

    public function addipclis($id){
        $data = Input::all();
        $data['AccountID'] = $id;
        $data['CompanyID'] = $CompanyID = User::get_companyID();
        $message = '';
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';

        $status = AccountAuthenticate::validate_ipclis($data);
        $save = $status['data'];
        if($status['status']==0){
            return Response::json(array("status" => "error", "message" => $status['message']));
        }

        if(!empty($save['CustomerAuthValue']) || !empty($save['VendorAuthValue'])){
            if($isCustomerOrVendor=='Customer') {
                 $status['toBeInsert']=explode(',',$save['CustomerAuthValue']);
            }else{
                $status['toBeInsert']=explode(',',$save['VendorAuthValue']);
            }
            if(AccountAuthenticate::where(['CompanyID'=>$save['CompanyID'],'AccountID'=>$save['AccountID']])->count()>0){
                AccountAuthenticate::where(['CompanyID'=>$save['CompanyID'],'AccountID'=>$save['AccountID']])->update($save);
            }else{
                AccountAuthenticate::insert($save);
            }
            $object = AccountAuthenticate::where(['CompanyID'=>$save['CompanyID'],'AccountID'=>$save['AccountID']])->first();
            return Response::json(array("status" => "success","ipclis"=> $status['toBeInsert'],"object"=>$object, "message" => "Account Successfully Updated".$message));
        }
    }

    public function deleteips($id){
        $data = Input::all();
        $data['AccountID'] = $id;
        $accountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
        $postIps = explode(',',$data['ipclis']);
        unset($data['ipclis']);
        unset($data['isCustomerOrVendor']);
        $ips = [];
        if(!empty($accountAuthenticate)){
            if($isCustomerOrVendor=='Customer'){
                $data['CustomerAuthRule'] = 'IP';
                $dbIPs = explode(',', $accountAuthenticate->CustomerAuthValue);
                $ips = implode(',',array_diff($dbIPs, $postIps));
                $data['CustomerAuthValue'] = ltrim($ips,',');
            }else{
                $data['VendorAuthRule'] = 'IP';
                $dbIPs = explode(',', $accountAuthenticate->VendorAuthValue);
                $ips = implode(',',array_diff($dbIPs, $postIps));
                $data['VendorAuthValue'] = ltrim($ips,',');
            }
            AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->update($data);
            $object = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();
            return Response::json(array("status" => "success","ipclis"=> explode(',',$ips),"object"=>$object,"message" => "Account Successfully Updated"));
        }else{
            return Response::json(array("status" => "error","message" => "No Ip exist."));
        }
    }

    public function deleteclis($id){
        $data = Input::all();
        $data['AccountID'] = $id;
        $accountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
        $postClis = explode(',',$data['ipclis']);
        unset($data['ipclis']);
        unset($data['isCustomerOrVendor']);
        if(!empty($accountAuthenticate)){
            if($isCustomerOrVendor=='Customer'){
                $data['CustomerAuthRule'] = 'CLI';
                $dbCLIs = explode(',', $accountAuthenticate->CustomerAuthValue);
                $clis = implode(',',array_diff($dbCLIs, $postClis));
                $data['CustomerAuthValue'] = ltrim($clis,',');
            }else{
                $data['VendorAuthRule'] = 'CLI';
                $dbCLIs = explode(',', $accountAuthenticate->VendorAuthValue);
                $clis = implode(',',array_diff($dbCLIs, $postClis));
                $data['VendorAuthValue'] = ltrim($clis,',');
            }
            AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->update($data);
            $object = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();
            return Response::json(array("status" => "success","ipclis"=> explode(',',$clis),"object"=>$object,"message" => "Account Successfully Updated"));
        }else{
            return Response::json(array("status" => "error","message" => "No Cli exist."));
        }
    }

}