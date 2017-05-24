<?php
class TicketImportRuleAction extends \Eloquent {

    protected $table 		= 	"tblTicketImportRuleAction";
    protected $primaryKey 	= 	"TicketImportRuleActionID";
	protected $guarded 		=	 array("TicketImportRuleActionID");		
	
	static function GetImportRulesAction($id){
		return TicketImportRuleAction::where(["TicketImportRuleID"=>$id])->orderby('Order')->get();		
	}
	
}