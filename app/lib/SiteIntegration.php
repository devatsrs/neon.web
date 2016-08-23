<?php 
class SiteIntegration{ 

 protected $support;
 protected $companyID;
 static   $SupportSlug	=	'support';
 protected $PaymentSlug	=	'payment';
 static    $EmailSlug	=	'email';
 static    $StorageSlug	=	'storage';
 static    $AmazoneSlug	=	'amazons3';

 	public function __construct(){
	
		$this->companyID = 	User::get_companyID();
	 } 

	public function SetSupportSettings($type,$data){
		
		if(is_FreshDesk()){		
			$this->support = new Freshdesk($data);
		}		
	}
	
	public function GetSupportContacts($options = array()){
        if($this->support){
            return $this->support->GetContacts($options);
        }
        return false;
    }
	
	public function GetSupportTickets($options = array()){
        if($this->support){
            return $this->support->GetTickets($options);
        }
        return false;

    }

	public function GetSupportTicketConversations($id){
        if($this->support){
            return $this->support->GetTicketConversations($id);
        }
        return false;

    }
	
	 public static function is_FreshDesk(){
		$companyID		 =  User::get_companyID();
		$Support	 	 =	Integration::where(["CompanyID" => $companyID,"Slug"=>SiteIntegration::$SupportSlug])->first();	
	
		if(count($Support)>0)
		{						
			$SupportSubcategory = Integration::select("*");
			$SupportSubcategory->join('tblIntegrationConfiguration', function($join)
			{
				$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
	
			})->where(["tblIntegration.CompanyID"=>$companyID])->where(["tblIntegration.ParentID"=>$Support->IntegrationID])->where(["tblIntegrationConfiguration.Status"=>1]);
			 $result = $SupportSubcategory->first();
			 if(count($result)>0)
			 {
				return 1;
			 }
			 else
			 {
				return 0;
			 }
		}
		else
		{
			return 0;	
		}	
	 }
	 
	 
	public function is_Authorize($data = false){

		$Payment	 	 =	Integration::where(["CompanyID" => $this->companyID,"Slug"=>$this->PaymentSlug])->first();	
	
		if(count($Payment)>0)
		{						
			$PaymentSubcategory = Integration::select("*");
			$PaymentSubcategory->join('tblIntegrationConfiguration', function($join)
			{
				$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
	
			})->where(["tblIntegration.CompanyID"=>$this->companyID])->where(["tblIntegration.ParentID"=>$Payment->IntegrationID])->where(["tblIntegrationConfiguration.Status"=>1]);
			 $result = $PaymentSubcategory->first();
			 if(count($result)>0)
			 {
				 $PaymentData =  isset($result->Settings)?json_decode($result->Settings):array();
				 if(count($PaymentData)>0){
					 if($data ==true){
						return $PaymentData;
					 }else{
						return 1;
					 }
				 }else
				 {
				 	return 0;
				 }
			 }
			 else
			 {
				return 0;
			 }
		}
		else
		{
			return 0;	
		}
	}	
	
	public static function is_EmailIntegration($companyID='',$data = false){
		
	
		if($companyID==''){
			$companyID =  User::get_companyID();
		}
		$Email	 	 =	Integration::where(["CompanyID" => $companyID,"Slug"=>SiteIntegration::$EmailSlug])->first();	
	
		if(count($Email)>0)
		{						
			$EmailSubcategory = Integration::select("*");
			$EmailSubcategory->join('tblIntegrationConfiguration', function($join)
			{
				$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
	
			})->where(["tblIntegration.CompanyID"=>$companyID])->where(["tblIntegration.ParentID"=>$Email->IntegrationID])->where(["tblIntegrationConfiguration.Status"=>1]);
			 $result = $EmailSubcategory->first();
			 if(count($result)>0)
			 {
				 $EmailData =  isset($result->Settings)?json_decode($result->Settings):array();
				 if(count($EmailData)>0){
					 if($data){						
						return $result;
					 }else{
						return 1;
					 }
				 }else
				 {
				 	return 0;
				 }
			 }
			 else
			 {
				return 0;
			 }
		}
		else
		{
			return 0;	
		}
	}
	
	public static function SendMail($view,$data,$companyID){
		$config = SiteIntegration::is_EmailIntegration($companyID,true);
		
		switch ($config->Slug){
			case "mandrill":
       		return MandrilIntegration::SendMail($view,$data,$config,$companyID);
      	  break;
		}	
	}
	
	public static function is_storage_configured(){
		
		$companyID		 =  User::get_companyID();
		$Storage	 	 =	Integration::where(["CompanyID" => $companyID,"Slug"=>SiteIntegration::$StorageSlug])->first();	
	
		if(count($Storage)>0)
		{						
			$StorageSubcategory = Integration::select("*");
			$StorageSubcategory->join('tblIntegrationConfiguration', function($join)
			{
				$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
	
			})->where(["tblIntegration.CompanyID"=>$companyID])->where(["tblIntegration.ParentID"=>$Storage->IntegrationID])->where(["tblIntegrationConfiguration.Status"=>1]);
			 $result = $StorageSubcategory->first();
			 if(count($result)>0)
			 {
				 $StorageData =  isset($result->Settings)?json_decode($result->Settings):array();
				 if(count($StorageData)>0){
					 if($data ==true){
						return $StorageData;
					 }else{
						return 1;
					 }
				 }else
				 {
				 	return 0;
				 }
			 }
			 else
			 {
				return 0;
			 }
		}
		else
		{
			return 0;	
		}
	}	 
	
	
	public static function is_amazon_configured(){
		
		$companyID		 =  User::get_companyID();
		$Storage	 	 =	Integration::where(["CompanyID" => $companyID,"Slug"=>SiteIntegration::$AmazoneSlug])->first();	
	
		if(count($Storage)>0)
		{						
			$StorageSubcategory = Integration::select("*");
			$StorageSubcategory->join('tblIntegrationConfiguration', function($join)
			{
				$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
	
			})->where(["tblIntegration.CompanyID"=>$companyID])->where(["tblIntegration.ParentID"=>$Storage->ParentID])->where(["tblIntegrationConfiguration.Status"=>1]);
			 $result = $StorageSubcategory->first();
			 if(count($result)>0)
			 {
				 $StorageData =  isset($result->Settings)?json_decode($result->Settings):array();
				 if(count($StorageData)>0){
					 if($data ==true){
						return $StorageData;
					 }else{
						return 1;
					 }
				 }else
				 {
				 	return 0;
				 }
			 }
			 else
			 {
				return 0;
			 }
		}
		else
		{
			return 0;	
		}
	}	 
}
?>