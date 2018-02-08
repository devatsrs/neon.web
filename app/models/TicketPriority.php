<?php

class TicketPriority extends \Eloquent {

    protected $table 		= 	"tblTicketPriority";
    protected $primaryKey 	= 	"PriorityID";
	protected $guarded 		=	 array("PriorityID");
	static $DefaultPriority = 	 'Low';

    public static $enable_cache = false;

    public static $cache = array(
        "ticketPriority_cache"    // all records in obj
    );

    public static function getPriorityIDLIst(){
        if (self::$enable_cache && Cache::has('ticketPriority_cache')) {
            //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('ticketPriority_cache');
            //get the admin defaults
            self::$cache['ticketPriority_cache'] = $admin_defaults['ticketPriority_cache'];
        } else {
            //if the cache doesn't have it yet
            $companyID = User::get_companyID();
            self::$cache['ticketPriority_cache'] = TicketPriority::orderBy('PriorityID')->lists('PriorityValue', 'PriorityID');

            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('ticketPriority_cache', array('ticketPriority_cache' => self::$cache['ticketPriority_cache']));
        }
        return self::$cache['ticketPriority_cache'];
    }
	
	static function getTicketPriority(){
		//TicketfieldsValues::WHERE
		 $row =  TicketPriority::orderBy('PriorityID')->lists('PriorityValue', 'PriorityID');

        $return=array();
        foreach($row as $key=>$value){
            $return[$key]=cus_lang("CUST_PANEL_PAGE_TICKET_FIELDS_PRIORITY_VAL_".$value);
        }
        $return = array("0"=> cus_lang("DROPDOWN_OPTION_SELECT"))+$return;
		 return $return;
	}
	
	
	static function getDefaultPriorityStatus(){
			return TicketPriority::where(["PriorityValue"=>TicketPriority::$DefaultPriority])->pluck('PriorityID');
	}
	
	static function getPriorityStatusByID($id){
			return TicketPriority::where(["PriorityID"=>$id])->pluck('PriorityValue');
	}
	
	static function getPriorityIDByStatus($id){ 
			return TicketPriority::where(["PriorityValue"=>$id])->pluck('PriorityID');
	}
}