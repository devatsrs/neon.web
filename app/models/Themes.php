<?php

class Themes extends \Eloquent {
	
    protected $connection 	= 	'sqlsrv';
    protected $fillable 	= 	[];
    protected $guarded 		= 	array('ThemeID');
    protected $table 		= 	'tblCompanyThemes';
    protected $primaryKey 	= 	"ThemeID";
    const DRAFT 			= 	'draft';
    const ACTIVE 			= 	'active';
	


   public static function get_theme_status()
	{
       $Company 		= 	Company::find(User::get_companyID());		
       $themeStatus 	= 	explode(',','');
       $themearray 		= 	array(
	   										''=>'Select Theme Status',
	   										self::DRAFT=>'Draft',
											self::ACTIVE=>'Active'											
								);
	   
        foreach($themeStatus as $status)
		{
            $themearray[$status] = $status;
        }
		
        return $themearray;
    }

}