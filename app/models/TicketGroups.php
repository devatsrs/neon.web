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
}