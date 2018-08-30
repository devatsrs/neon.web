<?php
class TicketImportRule extends \Eloquent {
	
    protected $table 		= 	"tblTicketImportRule";
    protected $primaryKey 	= 	"TicketImportRuleID";
	protected $guarded 		=	 array("TicketImportRuleID");	
	
	const IMPORTRULE_STATUS_ENABLE  = 0;
    const IMPORTRULE_STATUS_DISABLE = 1;
	
	
	const MATCH_ANY = 1;
    const MATCH_ALL = 2;
	
	const IS = 'is';
    const IS_NOT = 'is_not';	
    const CONTAINS = 'contains';
    const DOES_NOT_CONTAIN = 'does_not_contain';
    const START_WITH = 'start_with';
    const END_WITH = 'end_with';
	
	const NOT_IN  = 'not_in';
	const SP_IS   = 'in';
	
	static $OperandDropDownitemsAll = array(
		self::IS=>"Is",		
		self::IS_NOT=>"Is not",
		self::CONTAINS=>"Contains",
		self::DOES_NOT_CONTAIN=>"Does not contain",	
		self::START_WITH=>"Starts with",
		self::END_WITH=>"Ends with",
	);
	
	static $OperandDropDownitemsSpecific = array(
		self::IS=>"Is",
		self::IS_NOT=>"Is not",
	);
	
	static $RuleStatusDropdown = array(
		self::IMPORTRULE_STATUS_ENABLE=>"Active", 
		self::IMPORTRULE_STATUS_DISABLE=>"Active"		
	);
	
	public static function getImportRules(){
        $compantID   = 	User::get_companyID();
        $where 		 =	['CompanyID'=>$compantID];      
        $ImportRules =  TicketImportRule::select(['TicketImportRuleID','Title'])->where($where)->orderBy('TicketImportRuleID', 'asc')->lists('Title','TicketImportRuleID');
        if(!empty($ImportRules)){
            $ImportRules = [''=>'Select'] + $ImportRules;
        }
        return $ImportRules;
    }
	
}

?>