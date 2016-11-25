<?php

class TicketsController extends \BaseController {

private $validlicense;	
	public function __construct(){
			$this->validlicense = Tickets::CheckTicketLicense();
		 } 
	 
	 protected function IsValidLicense(){
	 	return $this->validlicense;		
	 }
	
	  public function index(){
			$this->IsValidLicense();
			$data 			 		= 	array();	
			$EscalationTimes_json 	= 	json_encode(TicketGroups::$EscalationTimes);
			$users			 		= 	User::getUserIDListAll(1);
			return View::make('tickets.index', compact('data','EscalationTimes_json','users'));   
	  }	  
}