<?php
class TicketsTable extends \Eloquent 
{
    protected $guarded = array("ID");

    protected $table = 'tblTickets';

    protected $primaryKey = "ID";
	
    static  $FreshdeskTicket  		    = 	1;
    static  $SystemTicket 				= 	0;
	
	static function GetAgentSubmitRules(){
		 $rules 	 =  array();
		 $messages	 =  array();
		 $fields 	 = 	Ticketfields::where(['AgentReqSubmit'=>1])->get();
		 
		foreach($fields as $fieldsdata)	 
		{
			$rules[$fieldsdata->FieldType] = 'required';
			$messages[$fieldsdata->FieldType.".required"] = "The ".$fieldsdata->AgentLabel." field is required";
		}
		
		return array("rules"=>$rules,"messages"=>$messages);
	}
	
}