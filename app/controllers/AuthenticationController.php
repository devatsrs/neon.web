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
        if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'IP' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor IP required"));
        }else if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'Other' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor Other Value required"));
        }
        if(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'IP' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer IP required"));
        }elseif(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'Other' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer Other Value required"));
        }

        if(AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->count()){
            AccountAuthenticate::where(array('AccountID'=>$data['AccountID']))->update($data);
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
        }else{
            $AccountAuthenticate = array();
            $AccountAuthenticate=$data;
            AccountAuthenticate::insert($AccountAuthenticate);
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
        }
        return Response::json(array("status" => "failed", "message" => "Problem Updating Account."));
    }

}