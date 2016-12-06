<?php

class TicketPriority extends \Eloquent {

    protected $table 		= 	"tblTicketPriority";
    protected $primaryKey 	= 	"PriorityID";
	protected $guarded 		=	 array("PriorityID");
	
	
	static function getTicketPriority(){
		//TicketfieldsValues::WHERE
		 $row =  TicketPriority::orderBy('PriorityID')->lists('PriorityValue', 'PriorityID');
		 $row = array("0"=> "Select")+$row;
		 return $row;
	}
}