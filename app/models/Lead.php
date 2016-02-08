<?php

class Lead extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblAccount';

    protected  $primaryKey = "AccountID";


    public static $rules = array(
        'Owner' =>      'required',
        'CompanyID' =>  'required',
        'AccountName' => 'required|unique:tblAccount,AccountName',
        'FirstName' =>  'required',
        'LastName' =>  'required',
        'LastName' =>  'required',
    );
    public static function getRecentLeads($limit){
        $companyID  = User::get_companyID();
        $leads = Account::Where(["AccountType"=> 0,"CompanyID"=>$companyID,"Status"=>1])
            ->orderBy("tblAccount.AccountID", "desc")
            ->take($limit)
            ->get();

        return $leads;

    }

    public static function getLeadOwnersByRole(){
        $companyID = User::get_companyID();
        if(User::is('AccountManager')){
            $UserID = User::get_userID();
            $lead_owners = DB::table('tblAccount')->where([ "AccountType" => 0, "CompanyID" => $companyID, "Status" => 1])->orderBy('FirstName', 'asc')->get();
        }
        else{
            $lead_owners = DB::table('tblAccount')->where(["AccountType" => 0, "CompanyID" => $companyID, "Status" => 1])->orderBy('FirstName', 'asc')->get();
        }

        return $lead_owners;
    }

}