<?php

class TicketsFieldsController extends \BaseController {

private $validlicense;	
	public function __construct(){
			$this->validlicense = Tickets::CheckTicketLicense();
		 } 
	 
	 protected function IsValidLicense(){
	 	return $this->validlicense;		
	 }
	
	function index(){
		$this->IsValidLicense();
		$data 			= 	array();	
		$Ticketfields	=	DB::table('tblTicketfields')->orderBy('FieldOrder', 'asc')->get(); 
		$Checkboxfields =   json_encode(Ticketfields::$Checkboxfields);
		//echo Ticketfields::$FIELD_HTML_DROPDOWN; exit;
		//echo "<pre>"; print_r($Ticketfields); exit;
		return View::make('ticketsfields.index', compact('data','Ticketfields',"Checkboxfields"));   
	}
}