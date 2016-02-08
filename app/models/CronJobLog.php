<?php

class CronJobLog extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('CronJobLogID');

    protected $table = 'tblCronJobLog';

    protected  $primaryKey = "CronJobLogID";
}