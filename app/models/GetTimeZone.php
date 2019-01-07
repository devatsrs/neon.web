<?php

class GetTimeZone extends \Eloquent {
	
    protected $connection 	= 	'sqlsrvcdr';
    protected $table = "tblgetTimezone";
    protected  $primaryKey = "getTimezoneID";
    protected $fillable = ['CompanyID', 'connect_time', 'disconnect_time'];
}