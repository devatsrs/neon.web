<?php

class UsageHourly extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrv2';
    protected $guarded = array('tblUsageHourlyID');

    protected $table = 'tblUsageHourly';

    protected  $primaryKey = "tblUsageHourlyID";
}