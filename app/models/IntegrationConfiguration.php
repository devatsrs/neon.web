<?php

class IntegrationConfiguration extends \Eloquent {
	
    protected $guarded 		= 	array("IntegrationConfigurationID");
    protected $table 		= 	'tblIntegrationConfiguration';
    protected $primaryKey 	= 	"IntegrationConfigurationID";
	
    public static $rules = array(
    );	
	
   static function GetIntegrationDataBySlug($slug){
	   
	   $companyID	=  User::get_companyID();
	   
	  $Subcategory = Integration::select("*");
	  $Subcategory->leftJoin('tblIntegrationConfiguration', function($join) use($companyID)
		{
			$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
			$join->where('tblIntegrationConfiguration.CompanyID','=',$companyID);
	
		})
		  //->where(["tblIntegrationConfiguration.CompanyID"=>$companyID])
		  ->where(["tblIntegration.Slug"=>$slug]);
		 $result = $Subcategory->first();
		 return $result;
   } 
   
   static function GetGatewayConfiguration($GatewayID = 0){
 	 	$CompanyID 		= 	User::get_companyID();
		
       	$Gateway =  CompanyGateway::select('*')->where("CompanyID", $CompanyID);
		if($GatewayID>0){
			$Gateway->where("GatewayID", $GatewayID);
		} 
		
		$result = $Gateway->count();
		return $result;
   }     
}
