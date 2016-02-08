<?php

class LastPrefixNo extends \Eloquent {

	protected $fillable = [];

    public $timestamps = false; // no created_at and updated_at

	protected $table = "tblLastPrefixNo";

	protected  $primaryKey = "LastPrefixNoID";

	public static function getLastPrefix(){


       //Get Last Prefix No. if Prefix is null.
        $LastPrefixNo2 = 0;
        $LastPrefixNo = LastPrefixNo::where(["CompanyID"=> User::get_companyID()])->first();
        if(count($LastPrefixNo) == 0){
            $company = Company::find(User::get_companyID());
            if($company->CustomerAccountPrefix == ''){
                $LastPrefixNo = DB::table('tblGlobalSetting')->where(["Key" => 'Default_Customer_Trunk_Prefix'])->first();
                $company->CustomerAccountPrefix = $LastPrefixNo->Value;
                $company->save();
            }else{
                LastPrefixNo::insert(array('CompanyID' => User::get_companyID(), 'LastPrefixNo' => $company->CustomerAccountPrefix));
                return $LastPrefixNo2 = $company->CustomerAccountPrefix;
            }
            if(count($LastPrefixNo)>0){
                LastPrefixNo::insert(array('CompanyID' => User::get_companyID(), 'LastPrefixNo' => $LastPrefixNo->Value));
                return $LastPrefixNo2 =  $LastPrefixNo->Value;
            }
        }
        if(count($LastPrefixNo) > 0 && isset($LastPrefixNo->LastPrefixNo)){
            $LastPrefixNo2 = $LastPrefixNo->LastPrefixNo;
            $LastPrefixNo2++;
        }
        while(CustomerTrunk::where(["CompanyID"=> User::get_companyID(),'Prefix'=>$LastPrefixNo2])->count() >=1){
            $LastPrefixNo2++;
        }
       	return $LastPrefixNo2;

	}

	//Increament Last PRefix No
	public static function incrementLastPrefix(){

       //Get Last Prefix No. if Prefix is null.
       LastPrefixNo::where(["CompanyID"=> User::get_companyID()])->increment('LastPrefixNo');

	}

	//Update Last PRefix No
	public static function updateLastPrefixNo($value){

       LastPrefixNo::where(["CompanyID"=> User::get_companyID()])->update(['LastPrefixNo'=>$value]);

	}
}