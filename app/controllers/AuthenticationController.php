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
        if(isset($data['VendorAuthRuleText'])) {
            unset($data['VendorAuthRuleText']);
        }
        if(isset($data['CustomerAuthValueText'])) {
            unset($data['CustomerAuthValueText']);
        }
        if(isset($data['VendorAuthValue'])){
            $data['VendorAuthValue'] = implode(',', array_unique(explode(',', $data['VendorAuthValue'])));
        }
        if(isset($data['CustomerAuthValue'])){
            $data['CustomerAuthValue'] = implode(',', array_unique(explode(',', $data['CustomerAuthValue'])));
        }
        if(!empty($data['VendorAuthRule']) && ($data['VendorAuthRule'] != 'IP' || $data['VendorAuthRule']!='Other')){
            $data['VendorAuthValue']='';

        }else if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'Other' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor Other Value required"));
        }
        if(!empty($data['CustomerAuthRule']) && ($data['CustomerAuthRule'] != 'IP' || $data['CustomerAuthRule']!='Other')){
            $data['CustomerAuthValue']='';
        }elseif(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'Other' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer Other Value required"));
        }
        unset($data['vendoriptable_length']);
        unset($data['customeriptable_length']);
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
       $message = '';
       $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';

       if(empty($data['ips'])){
           return Response::json(array("status" => "error", "message" => $isCustomerOrVendor." IP required"));
       }

       $ips = preg_split("/\\r\\n|\\r|\\n/", $data['ips']);
       unset($data['ips']);
       unset($data['isCustomerOrVendor']);
       $data['AccountID'] = $id;
       if($isCustomerOrVendor=='Customer'){
           $data['CustomerAuthRule'] = 'IP';
           $data['CustomerAuthValue'] = $ips;
       }else{
           $data['VendorAuthRule'] = 'IP';
           $data['VendorAuthValue'] = $ips;
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

           if(AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->count()){
               AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->update($data);
           }else{
               $AccountAuthenticate = array();
               $AccountAuthenticate=$data;
               AccountAuthenticate::insert($AccountAuthenticate);
           }
           $object = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();
           return Response::json(array("status" => "success","ips"=> $status['toBeInsert'],"totalips"=>$object, "message" => "Account Successfully Updated".$message));
       }
   }

    public function deleteips($id){
        $data = Input::all();
        $data['AccountID'] = $id;
        $accountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->first();
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
        $postIps = explode(',',$data['ips']);
        unset($data['ips']);
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
            return Response::json(array("status" => "success","ips"=> explode(',',$ips),"totalips"=>$object,"message" => "Account Successfully Updated"));
        }else{
            return Response::json(array("status" => "error","message" => "No Ip exist."));
        }
    }

}