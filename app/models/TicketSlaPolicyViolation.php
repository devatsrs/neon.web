<?php

class TicketSlaPolicyViolation extends \Eloquent {

    protected $table 		= 	"tblTicketSlaPolicyViolation";
    protected $primaryKey 	= 	"ViolationID";
	protected $guarded 		=	 array("ViolationID");	
	
	static $RespondedVoilationType   = 0;
	static $ResolvedVoilationType    = 1;
}

