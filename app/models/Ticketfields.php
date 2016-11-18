<?php

class Ticketfields extends \Eloquent {

    protected $table 		= 	"tblTicketfields";
    protected $primaryKey 	= 	"TicketFieldsID";
	protected $guarded 		=	 array("TicketFieldsID");
   // public    $timestamps 	= 	false; // no created_at and updated_at	
  // protected $fillable = ['GroupName','GroupDescription','GroupEmailAddress','GroupAssignTime','GroupAssignEmail','GroupAuomatedReply'];
	protected $fillable = [];
	
	
	static  $FIELD_TYPE_STATIC  		= 	0;
    static  $FIELD_TYPE_DYNAMIC 		= 	1;
	
	static  $FIELD_HTML_TEXT    		= 	1;
	static  $FIELD_HTML_TEXTAREA    	= 	2;
	static  $FIELD_HTML_CHECKBOX    	= 	3;
	static  $FIELD_HTML_TEXTNUMBER      = 	4;
	static  $FIELD_HTML_DROPDOWN    	= 	5;
	static  $FIELD_HTML_DATE    		= 	6;
	static  $FIELD_HTML_DECIMAL    		= 	7;
	
	public static $field_html_type = array();
	
	
	public static $Checkboxfields = array("AgentReqSubmit","AgentReqClose","CustomerDisplay","CustomerEdit","CustomerReqSubmit","AgentCcDisplay","CustomerCcDisplay");
	
	//0 for unavialable,1 for disable unselecetd,2 for disable seleceted,3 for active unselecetd,4 for active seleceted	
	

}