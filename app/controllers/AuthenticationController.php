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
        $pos = strpos($id, '-');
        if($pos){
            $pos = explode('-',$id);
            $AccountID = $pos[0];
            $ServiceID = $pos[1];
        }else{
            $AccountID = $id;
            $ServiceID = 0;
        }
        $account = Account::find($AccountID);
        $AccountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$AccountID, 'ServiceID'=>$ServiceID))->first();
        $rate_table = RateTable::getRateTableList(array('CurrencyID'=>$account->CurrencyId));
        $AuthRule = 'CLI';
        return View::make('accounts.authenticate', compact('account','AccountAuthenticate','Clitables','rate_table','AuthRule','ServiceID'));
    }
    public function authenticate_store(){
        $data = Input::all();
        $data['CompanyID'] = $CompanyID = User::get_companyID();
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        $rule = AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
        if(isset($data['VendorAuthRuleText'])) {
            unset($data['VendorAuthRuleText']);
        }
        if(isset($data['CustomerAuthValueText'])) {
            unset($data['CustomerAuthValueText']);
        }

        if(empty($data['CustomerAuthRule']) || ($data['CustomerAuthRule'] != 'IP' && $data['CustomerAuthRule'] != 'CLI' && $data['CustomerAuthRule']!='Other')){
            $data['CustomerAuthValue']='';  //if rule other then ip,cli and other, reset the value.
        }elseif(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'Other' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer Other Value required"));
        }elseif(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'IP' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer IP is required"));
        }elseif(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'CLI' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer CLI is required"));
        }

        if(empty($data['VendorAuthRule']) || ($data['VendorAuthRule'] != 'IP'&& $data['VendorAuthRule'] != 'CLI' && $data['VendorAuthRule']!='Other')){
            $data['VendorAuthValue']=''; //if rule other then ip,cli and other, reset the value.
        }else if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'Other' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor Other Value required"));
        }else if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'IP' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor IP is required"));
        }else if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'CLI' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor CLI is required"));
        }

        if(isset($data['VendorAuthValue'])){
            if(!empty($data['VendorAuthRule'])){ //if rule changes and value not changed, reset the values.
                $data['VendorAuthValue'] = implode(',', array_unique(explode(',', $data['VendorAuthValue'])));
                if(!empty($rule)) {
                    if ($rule->VendorAuthRule != $data['VendorAuthRule'] && $rule->VendorAuthValue == $data['VendorAuthValue']) {
                        $data['VendorAuthValue'] = '';
                    }
                }
            }
        }
        if(isset($data['CustomerAuthValue'])){  //if rule changed and value not changed, reset the values.
            if(!empty($data['CustomerAuthRule'])){
                $data['CustomerAuthValue'] = implode(',', array_unique(explode(',', $data['CustomerAuthValue'])));
                if(!empty($rule)) {
                    if ($rule->CustomerAuthRule != $data['CustomerAuthRule'] && $rule->CustomerAuthValue == $data['CustomerAuthValue']) {
                        $data['CustomerAuthValue'] = '';
                    }
                }
            }
        }
        unset($data['vendoriptable_length']);
        unset($data['vendorclitable_length']);
        unset($data['customeriptable_length']);
        unset($data['customerclitable_length']);
        unset($data['CLIName']);
        unset($data['table-clitable_length']);
        if(AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->count()){
            AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->update($data);
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
        }else{
            AccountAuthenticate::insert($data);
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
        }
        return Response::json(array("status" => "failed", "message" => "Problem Updating Account."));
    }

    public function addipclis($id){
        $data = Input::all();
        $data['AccountID'] = $id;
        $data['CompanyID'] = $CompanyID = User::get_companyID();
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        /*$message = '';
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';*/

        $status = AccountAuthenticate::validate_ipclis($data);
        /*$save = $status['data'];*/
        if($status['status']==0){
            return Response::json(array("status" => "error", "message" => $status['message']));
        }

        $object = AccountAuthenticate::where(['CompanyID'=>$data['CompanyID'],'AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID])->first();
        return Response::json(array("status" => "success","object"=>$object, "message" => $status['message']));

        /*if((isset($save['CustomerAuthValue'])) || (isset($save['VendorAuthValue']))){
            if($isCustomerOrVendor=='Customer' && !empty($save['CustomerAuthValue'])) {
                 $status['toBeInsert']=explode(',',$save['CustomerAuthValue']);
            }elseif($isCustomerOrVendor=='Vendor' && !empty($save['VendorAuthValue'])){
                $status['toBeInsert']=explode(',',$save['VendorAuthValue']);
            }
            if(AccountAuthenticate::where(['CompanyID'=>$save['CompanyID'],'AccountID'=>$save['AccountID']])->count()>0){
                AccountAuthenticate::where(['CompanyID'=>$save['CompanyID'],'AccountID'=>$save['AccountID']])->update($save);
            }else{
                AccountAuthenticate::insert($save);
            }
            $object = AccountAuthenticate::where(['CompanyID'=>$save['CompanyID'],'AccountID'=>$save['AccountID']])->first();
            return Response::json(array("status" => "success","object"=>$object, "message" => $status['message']));
        }*/
    }

    public function deleteips($id){
        $data = Input::all();
        $companyID = User::get_companyID();
        $Date = '';
        $Confirm = 0;
        if(isset($data['dates'])){
            $Date = $data['dates'];
            $Confirm = 1;
        }
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        $data['AccountID'] = $id;
        $accountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
        $query = "call prc_unsetCDRUsageAccount ('" . $companyID . "','" . $data['ipclis'] . "','".$Date."',".$Confirm.",".$ServiceID.")";
        $postIps = explode(',',$data['ipclis']);
        unset($data['ipclis']);
        unset($data['isCustomerOrVendor']);
        unset($data['dates']);
        $ips = [];
        if(!empty($accountAuthenticate)){
            $recordFound = DB::Connection('sqlsrvcdr')->select($query);
            if($recordFound[0]->Status>0){
                return Response::json(array("status" => "check","check"=>1));
            }
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
            AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->update($data);
            $object = AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
            return Response::json(array("status" => "success","ipclis"=> explode(',',$ips),"object"=>$object,"message" => "Account Successfully Updated"));
        }else{
            return Response::json(array("status" => "error","message" => "No Ip exist."));
        }
    }

    public function deleteclis($id){
        $data = Input::all();
        $companyID = User::get_companyID();
        $Date = '';
        $Confirm = 0;
        if(isset($data['dates'])){
            $Date = $data['dates'];
            $Confirm = 1;
        }
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        $data['AccountID'] = $id;
        $accountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
        $query = "call prc_unsetCDRUsageAccount ('" . $companyID . "','" . $data['ipclis'] . "','".$Date."',".$Confirm.",".$ServiceID.")";
        $postClis = explode(',',$data['ipclis']);
        unset($data['ipclis']);
        unset($data['isCustomerOrVendor']);
        unset($data['dates']);
        if(!empty($accountAuthenticate)){
            $recordFound = DB::Connection('sqlsrvcdr')->select($query);
            if($recordFound[0]->Status>0){
                return Response::json(array("status" => "check","check"=>1));
            }
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
            AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->update($data);
            $object = AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
            return Response::json(array("status" => "success","ipclis"=> explode(',',$clis),"object"=>$object,"message" => "Account Successfully Updated"));
        }else{
            return Response::json(array("status" => "error","message" => "No Cli exist."));
        }
    }

    public function recordExist(){

    }

}