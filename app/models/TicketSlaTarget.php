<?php

class TicketSlaTarget extends \Eloquent {

    protected $table 		= 	"tblTicketSlaTarget";
    protected $primaryKey 	= 	"SlaTargetID";
	protected $guarded 		=	 array("SlaTargetID");	
	
	
	static function ProcessTargets($id){
		
			$targets 		= 	TicketSlaTarget::where(['TicketSlaID'=>$id])->get();
			$targets_array	= 	array();
			
			foreach($targets as $targetsData)	
			{
				$targets_array[TicketPriority::getPriorityStatusByID($targetsData['PriorityID'])]	 = 
				array(
					"RespondTime"=>$targetsData['RespondValue'],
					"RespondType"=>$targetsData['RespondType'],
					"ResolveTime"=>$targetsData['ResolveValue'],
					"ResolveType"=>$targetsData['ResolveType'],
					"SlaOperationalHours"=>$targetsData['OperationalHrs'],
					"Escalationemail"=>$targetsData['EscalationEmail'],
				);
			}
			
			return $targets_array;
	}
}


