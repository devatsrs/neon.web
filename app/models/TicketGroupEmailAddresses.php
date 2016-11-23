<?php

class TicketGroupEmailAddresses extends \Eloquent {

    protected $table 		= 	"tblTicketGroupEmailAddresses";
    protected $primaryKey 	= 	"GroupEmailID";
    protected $fillable 	= 	['GroupEmailID'];
   // public    $timestamps 	= 	false; // no created_at and updated_at	
   
   
    static function get_support_email_by_remember_token($remember_token) {
        if (empty($remember_token)) {
            return FALSE;
        }
        $result = TicketGroupEmailAddresses::where(["remember_token"=>$remember_token])->first();
        if (!empty($result)) {
            return $result;
        } else {
            return FALSE;
        }
    }
}