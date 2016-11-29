<?php
class TicketsDetails extends \Eloquent 
{
    protected $guarded = array("ID");

    protected $table = 'tblTicketsDetails';

    protected $primaryKey = "TicketsDetailsID";	
   
}