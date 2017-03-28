<?php

class TicketfieldsValues extends \Eloquent {

    protected $table 		= 	"tblTicketfieldsValues";
    protected $primaryKey 	= 	"ValuesID";
	protected $guarded 		=	 array("ValuesID");
   // public    $timestamps 	= 	false; // no created_at and updated_at	
  // protected $fillable = ['GroupName','GroupDescription','GroupEmailAddress','GroupAssignTime','GroupAssignEmail','GroupAuomatedReply'];
	protected $fillable = [];
	
	static $Status_Closed = 'Closed';
	static $Status_Resolved = 'Resolved';

    public static $enable_cache = false;

    public static $cache = array(
        "ticketfieldsvalues_cache"    // all records in obj
    );

    public static function getFieldValueIDLIst(){

        if (self::$enable_cache && Cache::has('ticketfieldsvalues_cache')) {
            //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('ticketfieldsvalues_cache');
            //get the admin defaults
            self::$cache['ticketfieldsvalues_cache'] = $admin_defaults['ticketfieldsvalues_cache'];
        } else {
            //if the cache doesn't have it yet
            $companyID = User::get_companyID();
            self::$cache['ticketfieldsvalues_cache'] = TicketfieldsValues::select(['FieldValueAgent','ValuesID'])->lists('FieldValueAgent','ValuesID');

            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('ticketfieldsvalues_cache', array('ticketfieldsvalues_cache' => self::$cache['ticketfieldsvalues_cache']));
        }
        return self::$cache['ticketfieldsvalues_cache'];
    }
}