<?php 
class SiteIntegration{ 

 protected $support;
 protected $TrackingEmail;
 protected $companyID;
 static    $SupportSlug			=	'support';
 static    $PaymentSlug			=	'payment';
 static    $EmailSlug			=	'email';
 static    $StorageSlug			=	'storage';
 static    $AccountingSlug		=	'accounting';
 static    $AmazoneSlug			=	'amazons3';
 static    $AuthorizeSlug		=	'authorizenet';
 static    $GatewaySlug			=	'billinggateway';
 static    $freshdeskSlug		=	'freshdesk';
 static    $mandrillSlug		=	'mandrill';
 static    $emailtrackingSlug   =   'emailtracking';
 static    $imapSlug      		=   'imap';
 static    $paypalSlug			=	'paypal';
 static    $outlookcalenarSlug	=	'outlook';
 static    $QuickBookSlug		=	'quickbook';
 static    $QuickBookDesktopSlug=	'quickbookdesktop';
 static    $StripeSlug			=	'stripe';
 static    $StripeACHSlug		=	'stripeach';
 static    $SagePaySlug			=	'sagepay';
 static    $SagePayDirectDebitSlug=	'sagepaydirectdebit';
 static    $FideliPaySlug =	'fidelipay';
 static    $XeroSlug =	'xero';
 static    $PeleCardSlug		=	'pelecard';
 static    $MerchantWarriorSlug		=	'merchantwarrior';

 	public function __construct(){
	
		//$this->companyID = 	User::get_companyID();
		$companyID = SiteIntegration::GetComapnyIdByKey();
		$this->companyID = !empty($companyID)?$companyID:User::get_companyID();
	 } 
	 
	 /*
	 * Get support settings return current active support
	 */

	public function SetSupportSettings($type,$data,$companyID=0){
		
		if(self::CheckIntegrationConfiguration(false,SiteIntegration::$freshdeskSlug,$companyID)){
			$this->support = new Freshdesk($data);
		}		
	}
	
	/*
	 * Get support contacts from active support
	 */
	
	public function GetSupportContacts($options = array()){
        if($this->support){
            return $this->support->GetContacts($options);
        }
        return false;
    }
	
	/*
	 * Get support tickets from active support
	 */
	
	public function GetSupportTickets($options = array()){
        if($this->support){
            return $this->support->GetTickets($options);
        }
        return false;
    }
	
	/*
	 * Get support tickets conversation from active support
	 */
	 
	public function GetSupportTicketConversations($id){
        if($this->support){
            return $this->support->GetTicketConversations($id);
        }
        return false;

    }
	 
	/*
	 * send mail . check active mail settings 
	 */
	
	public static function SendMail($view,$data,$companyID,$body){
		$config = self::CheckCategoryConfiguration(true,SiteIntegration::$EmailSlug,$companyID);
		
		switch ($config->Slug){
			case  SiteIntegration::$mandrillSlug:
       		return MandrilIntegration::SendMail($view,$data,$config,$companyID,$body);
      	  break;
		}	
	}
	
	/*
	 * check the connection of tracking mail . return true false
	 */
	
	public function ConnectActiveEmail($view,$data,$companyID,$body){
		$config = self::CheckCategoryConfiguration(true,SiteIntegration::$emailtrackingSlug,$companyID);
		
		switch ($config->Slug){
			case  SiteIntegration::$imapSlug:
			$config = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$imapSlug,$companyID);
       		 if(Imap::CheckConnection($config->EmailTrackingEmail,$config->EmailTrackingServer,$config->EmailTrackingPassword)){
			 	$this->TrackingEmail = Imap;
			 }
      	  break;
		}	
	}
	
	public function ReadEmails($view,$data,$companyID,$body){
		$config = self::CheckCategoryConfiguration(true,SiteIntegration::$emailtrackingSlug,$companyID);
		
		switch ($config->Slug){
			case  SiteIntegration::$imapSlug:
			$config = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$imapSlug,$companyID);
       		return Imap::CheckConnection($config->EmailTrackingEmail,$config->EmailTrackingServer,$config->EmailTrackingPassword);
      	  break;
		}	
	}
	
	
	/*
	 * get company id using license key from company configuration
	 */	 
	
	public static function GetComapnyIdByKey(){
		$key 		= 	getenv('LICENCE_KEY');
		$CompanyId  =  	CompanyConfiguration::where(['Key'=>'LICENCE_KEY',"Value"=>$key])->pluck('CompanyID');	
		return $CompanyId;
	}
	
	/*
	 * check settings addded or not . return true,data or false
	 */ 	
	public static function  CheckIntegrationConfiguration($data=false,$slug,$companyID = 0){
		if (!Auth::guest()){
			$companyID = !empty($companyID)?$companyID:User::get_companyID();
		}
		if(!$companyID){
			$companyID = SiteIntegration::GetComapnyIdByKey();
		}
		$Integration	 =	Integration::where(["Slug"=>$slug])->first();
		//$Integration	 =	Integration::where(["CompanyID" => $companyID,"Slug"=>$slug])->first();

		if(count($Integration)>0)
		{						
			$IntegrationSubcategory = Integration::select("*");
			$IntegrationSubcategory->join('tblIntegrationConfiguration', function($join) use($companyID)
			{
				$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
				$join->where('tblIntegrationConfiguration.CompanyID', '=', $companyID);

			})->where(["tblIntegration.IntegrationID"=>$Integration->IntegrationID])->where(["tblIntegrationConfiguration.Status"=>1]);
			 $result = $IntegrationSubcategory->first();
			 if(count($result)>0)
			 {	
				 $IntegrationData =  isset($result->Settings)?json_decode($result->Settings):array();
				 if(count($IntegrationData)>0){
					 if($data ==true){
						return $IntegrationData;
					 }else{
						return true;
					 }
				 }
			 }
		}
		return false;		
	}
	
	/*
	check main category have data or not
	*/
	public static function  CheckCategoryConfiguration($data=false,$slug,$companyID=0){

		if (!Auth::guest()){
			$companyID = !empty($companyID)?$companyID:User::get_companyID();
		}
		if(!$companyID){
			$companyID = SiteIntegration::GetComapnyIdByKey();
		}

		//$companyID = SiteIntegration::GetComapnyIdByKey();
		//$companyID = !empty($companyID)?$companyID:User::get_companyID();
		//$Integration	 =	Integration::where(["CompanyId" => $companyID,"Slug"=>$slug])->first();
		$Integration	 =	Integration::where(["Slug"=>$slug])->first();
	
		if(count($Integration)>0)
		{						
			$IntegrationSubcategory = Integration::select("*");
			$IntegrationSubcategory->join('tblIntegrationConfiguration', function($join) use ($companyID)
			{
				$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
				$join->where('tblIntegrationConfiguration.CompanyID', '=', $companyID);

			})->where(["tblIntegrationConfiguration.ParentIntegrationID"=>$Integration->IntegrationID])->where(["tblIntegrationConfiguration.Status"=>1]);
			 $result = $IntegrationSubcategory->first();
			 if(count($result)>0)
			 {	
				 $IntegrationData =  isset($result->Settings)?json_decode($result->Settings):array();
				 if(count($IntegrationData)>0){
					 if($data ==true){
						return $result;
					 }else{
						return true;
					 }
				 }
			 }
		}
		return false;		
	}

	
}
?>