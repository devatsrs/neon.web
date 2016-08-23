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
	  $Subcategory->join('tblIntegrationConfiguration', function($join)
		{
			$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
	
		})->where(["tblIntegration.CompanyID"=>$companyID])->where(["tblIntegration.Slug"=>$slug]);
		 $result = $Subcategory->first();
		 return $result;
   }      
}
