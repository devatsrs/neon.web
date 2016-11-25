<?php
class Tickets extends \Eloquent 
{
    protected $guarded = array("ID");

    protected $table = 'tblTickets';

    protected $primaryKey = "ID";
	
    static  $FreshdeskTicket  		    = 	1;
    static  $SystemTicket 				= 	0;
	
}