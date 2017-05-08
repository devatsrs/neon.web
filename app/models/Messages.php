<?php

class Messages extends \Eloquent {

    protected $fillable 	= 	['PID'];
    protected $table 		= 	"tblMessages";
    protected $primaryKey 	= 	"MsgID";
    public    $timestamps 	= 	false; // no created_at and updated_at
	
	const  Sent 			= 	0;
    const  Received			=   1;
    const  Draft 			= 	2;
	
	
	const  UserTypeAccount	= 	0;
    const  UserTypeContact	=   1;
	
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
        //$isAdmin = (User::is_admin() || User::is('RateManager'))?1:0;
        $isAdmin 					= 	(User::is_admin())?1:0;
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
	
	public static function GetAllSystemEmails($lead=1,$indexEmail =false)
	{
		 $array 		 =  [];
		
		 if($lead==0)
		 {
			$AccountSearch   =  DB::table('tblAccount')->where(['AccountType'=>1,"Status"=>1])->whereRaw('Email !=""')->get(array("Email","BillingEmail"));
		 }
		 else
		 {
			$AccountSearch   =  DB::table('tblAccount')->where(["Status"=>1])->whereRaw('Email !=""')->get(array("Email","BillingEmail"));
		 }
		 
		 $ContactSearch 	 =  DB::table('tblContact')->get(array("Email"));	
		
		if(count($AccountSearch)>0){
				foreach($AccountSearch as $AccountData){
					//if($AccountData->Email!='' && !in_array($AccountData->Email,$array))
					if($AccountData->Email!='')
					{
						if(!is_array($AccountData->Email))
						{				  
						  $email_addresses = explode(",",$AccountData->Email);				
						}
						else
						{
						  $email_addresses = $emails;
						}
						if(count($email_addresses)>0)
						{
							foreach($email_addresses as $email_addresses_data)
							{
								if(!in_array($email_addresses_data,$array))
								{
									if($indexEmail){
										$array[$email_addresses_data] =  $email_addresses_data;	
									}else{
										$array[] =  $email_addresses_data;	
									}
								}
							}
						}
						
					}			
					
					if($AccountData->BillingEmail!='')
					{
						if(!is_array($AccountData->BillingEmail))
						{				  
						  $email_addresses = explode(",",$AccountData->BillingEmail);				
						}
						else
						{
						  $email_addresses = $emails;
						}
						if(count($email_addresses)>0)
						{
							foreach($email_addresses as $email_addresses_data)
							{
								if(!in_array($email_addresses_data,$array))
								{
									//$array[] =  $email_addresses_data;
									if($indexEmail){
										$array[$email_addresses_data] =  $email_addresses_data;	
									}else{
										$array[] =  $email_addresses_data;	
									}	
								}
							}
						}
						
					}
				}
		}
		
		if(count($ContactSearch)>0){
				foreach($ContactSearch as $ContactData){
					if($ContactData->Email!=''  && !in_array($ContactData->Email,$array))
					{
						if($indexEmail){
							$array[$ContactData->Email] =  $ContactData->Email;	
						}else{
							$array[] =  $ContactData->Email;
						}	
						
					}
				}
		}
		
		$UserSearch 	 =  DB::table('tblUser')->where(["Status"=>1])->get(array("EmailAddress"));		
		
		if(count($UserSearch)>0 || count($UserSearch)>0)													
		{
				foreach($UserSearch as $UserSearch){
					if($UserSearch->EmailAddress!=''  && !in_array($UserSearch->EmailAddress,$array))
					{
						if($indexEmail){
							$array[$UserSearch->EmailAddress] =  $UserSearch->EmailAddress;	
						}else{
							$array[] =  $UserSearch->EmailAddress;
						}	
						
					}
				}
		}				
		
		//return  array_filter(array_unique($array));
		return $array;
    }
	
	/////
	public static function GetAllSystemEmailsWithName($lead=1,$accountname = false)
	{
		 $array 		 =  [];
		
		 if($lead==0)
		 {
			 if($accountname){
			 $AccountSearch   =  DB::table('tblAccount')->where(['AccountType'=>1,"Status"=>1])->whereRaw('Email !=""')->get(array("Email","BillingEmail","AccountName"));
			 }else{
			$AccountSearch   =  DB::table('tblAccount')->where(['AccountType'=>1,"Status"=>1])->whereRaw('Email !=""')->get(array("Email","BillingEmail","FirstName","LastName"));
			 }
		 }
		 else
		 {
			  if($accountname){
			    $AccountSearch   =  DB::table('tblAccount')->where(["Status"=>1])->whereRaw('Email !=""')->get(array("Email","AccountName"));
			  }else{
				$AccountSearch   =  DB::table('tblAccount')->where(["Status"=>1])->whereRaw('Email !=""')->get(array("Email","BillingEmail"));
			  }
		 }
		 
		 $ContactSearch 	 =  DB::table('tblContact')->get(array("Email","FirstName","LastName"));	
		
		if(count($AccountSearch)>0){
				foreach($AccountSearch as $AccountData){
					//if($AccountData->Email!='' && !in_array($AccountData->Email,$array))
					if($AccountData->Email!='')
					{
						if(!is_array($AccountData->Email))
						{				  
						  $email_addresses = explode(",",$AccountData->Email);				
						}
						else
						{
						  $email_addresses = $emails;
						}
						if(count($email_addresses)>0)
						{
							foreach($email_addresses as $email_addresses_data)
							{
								if($accountname){
									$txt = $AccountData->AccountName." <".$email_addresses_data.">";
								}else{
									$txt = $AccountData->FirstName.' '.$AccountData->LastName." <".$email_addresses_data.">";
								}
								if(!in_array($txt,$array))
								{
									$array[] =  $txt;	
								}
							}
						}
						
					}			
					
					if($AccountData->BillingEmail!='')
					{
						if(!is_array($AccountData->BillingEmail))
						{				  
						  $email_addresses = explode(",",$AccountData->BillingEmail);				
						}
						else
						{
						  $email_addresses = $emails;
						}
						if(count($email_addresses)>0)
						{
							foreach($email_addresses as $email_addresses_data)
							{
								//$txt = $AccountData->AccountName." <".$email_addresses_data.">";
								if($accountname){
									$txt = $AccountData->AccountName." <".$email_addresses_data.">";
								}else{
									$txt = $AccountData->FirstName.' '.$AccountData->LastName." <".$email_addresses_data.">";
								}
								
								if(!in_array($txt,$array))
								{
									//$array[] =  $email_addresses_data;	
									$array[] =  $txt;	
								}
							}
						}
						
					}
				}
		}
		
		if(count($ContactSearch)>0){
				foreach($ContactSearch as $ContactData){
					$txt =  $ContactData->FirstName.' '.$ContactData->LastName." <".$ContactData->Email.">";
					if($ContactData->Email!=''  && !in_array($txt,$array))
					{
						$array[] =  $txt;
						//$array[] =  $ContactData->Email;
					}
				}
		}
		
		$UserSearch 	 =  DB::table('tblUser')->where(["Status"=>1])->get(array("EmailAddress","FirstName","LastName"));		
		
		if(count($UserSearch)>0 || count($UserSearch)>0)													
		{	 
				foreach($UserSearch as $UserSearch){
					$txt =  $UserSearch->FirstName.' '.$UserSearch->LastName." <".$UserSearch->EmailAddress.">";
					if($UserSearch->EmailAddress!=''  && !in_array($txt,$array))
					{
						$array[] =  $txt;
						
					}
				}					
		}					
		//return  array_filter(array_unique($array));
		return $array;
    }
	
	///
}