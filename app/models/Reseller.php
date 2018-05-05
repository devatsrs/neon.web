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
        'AccountID.unique' =>'Reseller exists',
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

    public static function getResellerDetails($ResellerID){
        return Reseller::where('ResellerID',$ResellerID)->first();
    }

    public static function getResellerID(){
        return Reseller::where('ChildCompanyID',Auth::user()->CompanyID)->pluck('ResellerID');
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

    public static function get_AllowWhilteLabel($ResellerID){
        $IsAllowWhiteLabel = Reseller::where('ResellerID',$ResellerID)->pluck('AllowWhiteLabel');
        return $IsAllowWhiteLabel;
    }

    public static function is_AllowWhiteLabel(){
        return Reseller::where('ChildCompanyID',Auth::user()->CompanyID)->pluck('AllowWhiteLabel');
    }

    public static function ResellerDomainUrl($ResellerID){
        $Reseller = Reseller::find($ResellerID);
        $IsAllowWhiteLabel = $Reseller->AllowWhiteLabel;
        $ResellerDomain = CompanyConfiguration::where(['CompanyID'=>$Reseller->ChildCompanyID,'Key'=>'WEB_URL'])->pluck('Value');
        return $ResellerDomain;
    }

    public static function IsAllowDomainUrl($DomainUrl,$ResellerID=''){
        $DomainUrl = rtrim($DomainUrl,"/");
        /**
         * When create/update reseller and white labeling is on than check domain url if already exits or not.
         * Because we can not allow to setup multiple theme in single domain.
        **/

        if(empty($ResellerID)){
            $WebUrlArray = CompanyConfiguration::where(['Key'=>'WEB_URL'])->select('Value')->get()->toArray();
        }else{
            $CompanyID = Reseller::where('ResellerID',$ResellerID)->pluck('ChildCompanyID');
            $WebUrlArray = CompanyConfiguration::where(['Key'=>'WEB_URL'])->where('CompanyID', '<>', $CompanyID)->select('Value')->get()->toArray();
        }
        $wb1 = array();
        foreach($WebUrlArray as $wb){

            $wb1[]=$wb['Value'];
        }
        if (in_array($DomainUrl, $wb1))
        {
            return false;
        }

        return true;
    }

}