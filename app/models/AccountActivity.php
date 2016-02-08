<?php

class AccountActivity extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('ActivityID');

    protected $table = 'tblAccountActivity';

    protected  $primaryKey = "ActivityID";
    const EMAIL =1;
    const CALLS = 2;
    const TASKS = 3;
    const EVENTS = 4;

    public  static $activity_type = array(''=>'-- Select --',self::CALLS=>'Calls',self::TASKS=>'Task',self::EVENTS=>'Events');

}