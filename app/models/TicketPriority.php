<?php

class TicketPriority extends \Eloquent {

    protected $table 		= 	"tblTicketPriority";
    protected $primaryKey 	= 	"PriorityID";
	protected $guarded 		=	 array("PriorityID");
	static $DefaultPriority = 	 'Low';
	
	static function getTicketPriority(){
		//TicketfieldsValues::WHERE
		 $row =  TicketPriority::orderBy('PriorityID')->lists('PriorityValue', 'PriorityID');
		 $row = array("0"=> "Select")+$row;
		 return $row;
	}
	
	
	static function getDefaultPriorityStatus(){
			return TicketPriority::where(["PriorityValue"=>TicketPriority::$DefaultPriority])->pluck('PriorityID');
	}
	
	static function getPriorityStatusByID($id){
			return TicketPriority::where(["PriorityID"=>$id])->pluck('PriorityValue');
	}
}