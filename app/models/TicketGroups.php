<?php

class TicketGroups extends \Eloquent {

    protected $table 		= 	"tblTicketGroups";
    protected $primaryKey 	= 	"GroupID";
   // public    $timestamps 	= 	false; // no created_at and updated_at	
   
   public static $EscalationTimes = array(
   "1800"=>"30 Minutes",
   "3600"=>"1 Hour",
   "7200"=>"2 Hours",
   "14400"=>"4 Hours",
   "28800"=>"8 Hours",   
   "43200"=>"12 Hours",
   "86400"=>"1 Day",
   "172800"=>"2 Days",
   "259200"=>"3 Days",
   );
   
   //<option value="1800">30 Minutes</option>
   //<option value="3600">1 Hour</option> 
   //<option value="7200">2 Hours</option>
   //<option value="14400">4 Hours</option>
   //<option value="28800">8 Hours</option>
   //<option value="43200">12 Hours</option>
   //<option value="86400">1 Day</option>
   //<option value="172800">2 Days</option>
   //<option value="259200">3 Days</option>
}