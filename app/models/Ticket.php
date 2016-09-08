<?php
class Ticket extends \Eloquent {

    protected $guarded = array("ID");

    protected $table = 'tblAccountTickets';

    protected $primaryKey = "ID";
	
	const Low 				= 		1;
	const Medium 			= 		2;
	const High 				= 		3;
	const Urgent 			= 		4;	
	
	const Open 				= 		2;
	const Pending 			= 		3;
	const Resolved 			= 		4;	
	const Closed 			= 		5;
	const Customer 			= 		6;
	const Third_Party 		= 		7;
		
	
 	public static $Priority = [Ticket::Low=>'Low',Ticket::Medium=>'Medium',Ticket::High=>'High',Ticket::Urgent=>'Urgent']; 
	public static $Status 	= [Ticket::Open=>'Open',Ticket::Pending=>'Pending',Ticket::Resolved=>'Resolved',Ticket::Closed=>'Closed',Ticket::Customer=>'Waiting on Customer',Ticket::Third_Party=>'Waiting on Third Party']; 
	
}