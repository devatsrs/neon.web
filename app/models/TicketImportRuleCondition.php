<?php
class TicketImportRuleCondition extends \Eloquent {

    protected $table 		= 	"tblTicketImportRuleCondition";
    protected $primaryKey 	= 	"TicketImportRuleConditionID";
	protected $guarded 		=	 array("TicketImportRuleConditionID");	
	
	
	static function GetImportRulesCondition($id){
		return TicketImportRuleCondition::where(["TicketImportRuleID"=>$id])->orderby('Order')->get();		
	}
}