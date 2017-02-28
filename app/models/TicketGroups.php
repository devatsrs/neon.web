<?php

class TicketGroups extends \Eloquent {

    protected $table 		= 	"tblTicketGroups";
    protected $primaryKey 	= 	"GroupID";
	protected $guarded 		=	 array("GroupID");
   // public    $timestamps 	= 	false; // no created_at and updated_at	
  // protected $fillable = ['GroupName','GroupDescription','GroupEmailAddress','GroupAssignTime','GroupAssignEmail','GroupAuomatedReply'];
	protected $fillable = [];
	
   public static $EscalationTimes = array(
	   "1800"=>"30 Minutes",
	   "3600"=>"1 Hour",
	   "7200"=>"2 Hours",
	   "14400"=>"4 Hours",
	   "28800"=>"8 Hours",   
	   "43200"=>"12 Hours",
	   "86400"=>"1 Day",
	   "172800"=>"2 Days",
	   "259200"=>"3 Days",
   );
   
   
   static function getTicketGroups(){
		//TicketfieldsValues::WHERE
	   $row =  TicketGroups::orderBy('GroupID', 'asc')->lists('GroupName','GroupID'); 
	   $row = array("0"=> "Select")+$row;
	   return $row;
	}
	
	 static function getTicketGroups_dropdown(){
		//TicketfieldsValues::WHERE
	    $compantID 	  = 	User::get_companyID();
        $where 		  = 	['CompanyID'=>$compantID];      
        $TicketGroups = 	TicketGroups::select(['GroupID','GroupName'])->where($where)->orderBy('GroupName', 'asc')->lists('GroupName','GroupID');
        if(!empty($TicketGroups)){
            $TicketGroups = [''=>'Select'] + $TicketGroups;
        }
        return $TicketGroups;
	}
	
	
    static function get_support_email_by_remember_token($remember_token) {
        if (empty($remember_token)) {
            return FALSE;
        }
        $result = TicketGroups::where(["remember_token"=>$remember_token])->first();
        if (!empty($result)) {
            return $result;
        } else {
            return FALSE;
        }
    }
	
	static function GetGroupsFrom(){
		$Tickets					=	Tickets::CheckTicketLicense()?1:0;
		$CompanyID 		 			= 	User::get_companyID(); 
		$is_admin					=	user::is_admin()?1:0;
		$FromEmailsQuery  			= 	"CALL `prc_GetFromEmailAddress`('".$CompanyID."',".User::get_userID()." , ".$Tickets.",".$is_admin.")"; 
		$FromEmailsResults			= 	DB::select($FromEmailsQuery);
		$FromEmails					= 	array();
		foreach($FromEmailsResults as $FromEmailsResultsData){
			$FromEmails[$FromEmailsResultsData->EmailFrom] = $FromEmailsResultsData->EmailFrom;
		}
		if(!$Tickets){
			$EmailTrackingDBData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$imapSlug);
			$EmailTrackingData   = isset($EmailTrackingDBData->Settings)?json_decode($EmailTrackingDBData->Settings):array();
			if(isset($EmailTrackingData->EmailTrackingEmail) && !empty($EmailTrackingData->EmailTrackingEmail) && $EmailTrackingDBData->Status){
				$FromEmails[$EmailTrackingData->EmailTrackingEmail] = $EmailTrackingData->EmailTrackingEmail;			
			}
		}
		return $FromEmails;
	}
}