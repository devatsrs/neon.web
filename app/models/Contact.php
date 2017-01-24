<?php

class Contact extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblContact';

    protected  $primaryKey = "ContactID";
    public static $rules = array(
       // 'AccountID' =>      'required',
        'CompanyID' =>  'required',
        'FirstName' => 'required',
        'LastName' => 'required',
    );
	
	public static function checkContactByEmail($email){
		 return Contact::where(["Email"=>$email])->first();	
	}
	
	public static function getContacts(){
        $compantID = User::get_companyID();
        $where = ['CompanyId'=>$compantID];      
		           
        $Contacts = Contact::select([DB::raw("concat(IFNULL(tblContact.FirstName,''),' ' ,IFNULL(tblContact.LastName,''))  AS FullName "),"tblContact.ContactID"])->where($where)->orderBy('FirstName', 'asc')->lists('FullName','ContactID');
        if(!empty($Contacts)){
            $Contacts = [''=>'Select'] + $Contacts;
        }
        return $Contacts;
    }
}