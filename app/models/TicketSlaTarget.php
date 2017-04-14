<?php

class TicketSlaTarget extends \Eloquent {

    protected $table 		= 	"tblTicketSlaTarget";
    protected $primaryKey 	= 	"SlaTargetID";
	protected $guarded 		=	 array("SlaTargetID");	
	
	
	static function ProcessTargets($id){
		
			$targets 		= 	TicketSlaTarget::where(['SlaPolicyID'=>$id])->get();
			$targets_array	= 	array();
			
			foreach($targets as $targetsData)	
			{
				$targets_array[TicketPriority::getPriorityStatusByID($targetsData['PritiryID'])]	 = 
				array(
					"RespondTime"=>$targetsData['RespondWithinTimeValue'],
					"RespondType"=>$targetsData['RespondWithinTimeType'],
					"ResolveTime"=>$targetsData['ResolveWithinTimeValue'],
					"ResolveType"=>$targetsData['ResolveWithinTimeType'],
					"SlaOperationalHours"=>$targetsData['OperationalHrs'],
					"Escalationemail"=>$targetsData['EscalationEmail'],
				);
			}
			
			return $targets_array;
	}
}


