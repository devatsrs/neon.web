<?php
class TicketsConversation extends \Eloquent {
    protected $table 		= 	 "tblTicketsConversation";
    protected $primaryKey 	= 	 "TicketConversationID";
	protected $guarded 		=	 array("TicketConversationID");
	protected $fillable		= 	 [];
	
}