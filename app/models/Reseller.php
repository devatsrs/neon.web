<?php

class Reseller extends \Eloquent
{
    protected $guarded = array("ResellerID");

    protected $table = 'tblReseller';

    protected $primaryKey = "ResellerID";

    public static $rules = array(
        'CompanyID' =>  'required',
        //'AccountID' =>  'required|AccountID|unique:tblReseller,AccountID',
		'Email' => 'required|email|min:5|unique:tblUser,EmailAddress',
        'Status' =>     'between:0,1',
    );
	
	public static $messages = array(
        'AccountID.required' =>'Reseller Account is required',
        'AccountID.unique' =>'Already Reseller created',
        'Email.required' =>'Email Value field is required',
        'Password.required' =>'Password Value field is required'
    );

    public static function getDropdownIDList($CompanyID=0){
        if($CompanyID==0){
            $CompanyID = User::get_companyID();
        }
        $DropdownIDList = Reseller::where(array("CompanyID"=>$CompanyID,"Status"=>1))->lists('ResellerName', 'ResellerID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

    public static function getAllReseller($CompanyID){
        $Services = Reseller::where(array("CompanyID"=>$CompanyID,"Status"=>1))->get();
        return $Services;
    }

    public static function getResellerNameByID($ResellerID){
        return Reseller::where('ResellerID',$ResellerID)->pluck('ResellerName');
    }

    public static function getResellerAccountID($ChildCompanyID){
        return Reseller::where('ChildCompanyID',$ChildCompanyID)->pluck('AccountID');
    }

    // main admin company id
    public static function get_companyID(){
        return  Reseller::where('ChildCompanyID',Auth::user()->CompanyID)->pluck('CompanyID');
    }

    public static function get_accountID(){
        return  Reseller::where('ChildCompanyID',Auth::user()->CompanyID)->pluck('AccountID');
    }

    public static function get_user_full_name(){
        $AccountID = Reseller::getResellerAccountID(Auth::user()->CompanyID);
        $Account = Account::find($AccountID);
        return $Account->FirstName.' '. $Account->LastName;
    }

    public static function get_user_full_name_with_email(){
        $AccountID = Reseller::getResellerAccountID(Auth::user()->CompanyID);
        $Account = Account::find($AccountID);
        return $Account->FirstName.' '. $Account->LastName.' <'.$Account->BillingEmail.'>';
    }

    public static function get_user_full_name_with_email2(){
        $AccountID = Reseller::getResellerAccountID(Auth::user()->CompanyID);
        $Account = Account::find($AccountID);
        return $Account->FirstName.' '. $Account->LastName.' <'.Auth::user()->Email.'>';
    }

    public static function get_accountName(){
        $AccountID = Reseller::getResellerAccountID(Auth::user()->CompanyID);
        $Account = Account::find($AccountID);
        return $Account->AccountName;
    }

    public static function get_AuthorizeID(){
        $AccountID = Reseller::getResellerAccountID(Auth::user()->CompanyID);
        $Account = Account::find($AccountID);
        return $Account->AutorizeProfileID;
    }

    public static function get_Email(){
        $AccountID = Reseller::getResellerAccountID(Auth::user()->CompanyID);
        $Account = Account::find($AccountID);
        return $Account->Email;
    }

    public static function get_Billing_Email(){
        $AccountID = Reseller::getResellerAccountID(Auth::user()->CompanyID);
        $Account = Account::find($AccountID);
        return $Account->BillingEmail;
    }

    public static function get_currentUser(){
        $AccountID = Reseller::getResellerAccountID(Auth::user()->CompanyID);
        $Account = Account::find($AccountID);
        return $Account;
    }

}