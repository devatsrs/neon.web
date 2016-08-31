<?php

class Integration extends \Eloquent {
	
    protected $guarded 		= 	array("IntegrationID");
    protected $table 		= 	'tblIntegration';
    protected $primaryKey 	= 	"IntegrationID";
	
    public static $rules = array(
    );
	
	
   
}
