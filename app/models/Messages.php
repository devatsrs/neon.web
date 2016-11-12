<?php

class Messages extends \Eloquent {

    protected $fillable 	= 	['PID'];
    protected $table 		= 	"tblMessages";
    protected $primaryKey 	= 	"MsgID";
    public    $timestamps 	= 	false; // no created_at and updated_at
	
	const  Sent 			= 	0;
    const  Received			=   1;
    const  Draft 			= 	2;
	
	const  inbox			=	'inbox';
	const  sentbox			=	'sentbox';
	const  draftbox			=	'draftbox';

    public static function logMsgRecord($JobType, $options = "") {
		              
		$rules = array(
			'CompanyID' => 'required',                
			'MsgLoggedUserID' => 'required',
			'Title' => 'required',
			'CreatedBy' => 'required',
		);

		$CompanyID 					= 	User::get_companyID();
		$options["CompanyID"] 		= 	$CompanyID;
		$data["CompanyID"] 			= 	$CompanyID;
		$data["AccountID"] 			= 	$options["AccountID"];
		$data["MsgLoggedUserID"] 	= 	User::get_userID();
		$data["Title"] 				= 	Account::getCompanyNameByID($data["AccountID"]) ;
		$data["Description"] 		= 	Account::getCompanyNameByID($data["AccountID"]);
		$data["CreatedBy"] 			= 	User::get_user_full_name();
		$data["created_at"] 		= 	date('Y-m-d H:i:s');
		$data["updated_at"] 		= 	date('Y-m-d H:i:s');

		$validator 					= 	Validator::make($data, $rules);
		if ($validator->fails()) {
			return validator_response($validator);
		}

		if ($JobID = Job::insertGetId($data)) {                   
				return array("status" => "success", "message" => "Job Logged Successfully");
		} else {
			   return array("status" => "failed", "message" => "Problem Inserting Job.");
		}
    }


    public static function getMsgDropDown($reset = 0){
        $companyID = User::get_companyID();
        $userID = User::get_userID();
        $isAdmin = (User::is_admin() || User::is('RateManager'))?1:0;
        $query = "Call prc_getMsgsDropdown (".$companyID.",".$userID.",".$isAdmin .",".$reset.")" ; 
        $dropdownData = DataTableSql::of($query)->getProcResult(array('jobs','totalNonVisitedJobs'));
        return $dropdownData;

    }
	
	public static function GetAccountTtitlesFromEmail($Emails)
	{
		$AccountName = array();	
		if(count($Emails)>0)
		{
			$Imap = new Imap();
			if(!is_array($Emails)){
				$email_addresses = explode(",",$Emails);
			}
			else{
				$email_addresses = $Emails;
			}
	
			if(count($email_addresses)>0){
				foreach($email_addresses as $email_address){
					$EmailData      =  $Imap->findEmailAddress($email_address);
					$AccountName[]  =  !empty($EmailData['AccountTitle'])?$EmailData['AccountTitle']:$email_address; 					  
				}
			}
		}
		return implode(",",$AccountName);
	
    }
	
	public static function GetAllSystemEmails()
	{
		 $array 		 =  [];
		 
		$AccountSearch   =  DB::table('tblAccount')->get(array("Email","BillingEmail"));
		$ContactSearch 	 =  DB::table('tblContact')->get(array("Email"));	
		
		if(count($AccountSearch)>0){
				foreach($AccountSearch as $AccountData){
					if($AccountData->Email!='' && !in_array($AccountData->Email,$array))
					{
						$array[] =  $AccountData->Email;
					}
					if($AccountData->BillingEmail!=''  && !in_array($AccountData->BillingEmail,$array))
					{
						$array[] =  $AccountData->BillingEmail;
					}
				}
		}
		
		if(count($ContactSearch)>0){
				foreach($ContactSearch as $ContactData){
					if($ContactData->Email!=''  && !in_array($ContactData->Email,$array))
					{
						$array[] =  $ContactData->Email;
					}
				}
		}
		//return  array_filter(array_unique($array));
		return $array;
    }
}