<?php

class ActivityFeeds extends \Eloquent {
	protected $fillable = [];
    protected $table = "tblUserActivity";
    protected $primaryKey = "UserActivityID";
    protected $guarded = array('UserActivityID');

}