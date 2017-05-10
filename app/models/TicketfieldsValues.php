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
	static $Status_UnResolved = 'All UnResolved';
	static $Status_Open = 'Open';

    public static $enable_cache = true;

    public static $cache = array(
        "ticketfieldsvalues_cache"    // all records in obj
    );

    public static function getFieldValueIDLIst(){
        $LicenceKey = getenv('LICENCE_KEY');
        $CompanyName = getenv('COMPANY_NAME');
        $ticketfieldsvalues_cache = 'ticketfieldsvalues_cache' . $LicenceKey.$CompanyName;
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
            $CACHE_EXPIRE = CompanyConfiguration::get('CACHE_EXPIRE');
            $time = empty($CACHE_EXPIRE)?60:$CACHE_EXPIRE;
            $minutes = \Carbon\Carbon::now()->addMinutes($time);
            Cache::add($ticketfieldsvalues_cache, array('ticketfieldsvalues_cache' => self::$cache['ticketfieldsvalues_cache']), $minutes);
        }
        return self::$cache['ticketfieldsvalues_cache'];
    }

    public static function isClosed($statusID){
        $statues = self::getFieldValueIDLIst();
        if(isset($statues[$statusID]) && $statues[$statusID] == self::$Status_Closed ) {
            return true;
        }
        return false;
    }
    public static function isResolved($statusID){
        $statues = self::getFieldValueIDLIst();
        if(isset($statues[$statusID]) && $statues[$statusID] == self::$Status_Resolved) {
            return true;
        }
        return false;
    }
}