<?php 

class Tickets{ 

 protected $companyID;
 
 	public function __construct(){
		$companyID = SiteIntegration::GetComapnyIdByKey();
		$this->companyID = !empty($companyID)?$companyID:User::get_companyID();
	 } 
	 
	 
	static function CheckTicketLicense(){
		return CompanyConfiguration::get('TICKETING_SYSTEM');		
	}
}
?>