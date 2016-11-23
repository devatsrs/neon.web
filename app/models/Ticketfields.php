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
	
	const  FIELD_HTML_TEXT    		= 	1;
	const  FIELD_HTML_TEXTAREA    	= 	2;
	const  FIELD_HTML_CHECKBOX    	= 	3;
	const  FIELD_HTML_TEXTNUMBER    = 	4;
	const  FIELD_HTML_DROPDOWN    	= 	5;
	const  FIELD_HTML_DATE    		= 	6;
	const  FIELD_HTML_DECIMAL    	= 	7;
	
	public static $field_html_type = array();
	
	
	public static $Checkboxfields = array("AgentReqSubmit","AgentReqClose","CustomerDisplay","CustomerEdit","CustomerReqSubmit","AgentCcDisplay","CustomerCcDisplay");
	
			public static $type = array(
				self::FIELD_HTML_TEXT => 'text',
				self::FIELD_HTML_TEXTAREA => 'paragraph',
				self::FIELD_HTML_CHECKBOX => 'checkbox',
				self::FIELD_HTML_TEXTNUMBER => 'number',
				self::FIELD_HTML_DROPDOWN => 'dropdown',
				self::FIELD_HTML_DATE => 'date',
				self::FIELD_HTML_DECIMAL => 'decimal',
			);
			
			public static $TypeSave = array(
				'text' => self::FIELD_HTML_TEXT,
				'paragraph' => self::FIELD_HTML_TEXTAREA,
				'checkbox' => self::FIELD_HTML_CHECKBOX,
				 'number'=> self::FIELD_HTML_TEXTNUMBER,
				'dropdown' => self::FIELD_HTML_DROPDOWN,
				'date' => self::FIELD_HTML_DATE,
				'decimal' => self::FIELD_HTML_DECIMAL,
			);
		
	
	
	
	
	//0 for unavialable,1 for disable unselecetd,2 for disable seleceted,3 for active unselecetd,4 for active seleceted	
	

}