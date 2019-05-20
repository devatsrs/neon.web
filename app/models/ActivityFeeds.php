<?php

class ActivityFeeds extends \Eloquent {
	protected $fillable = [];
    protected $table = "tblUserActivity";
    protected $primaryKey = "UserActivityID";
    protected $guarded = array('UserActivityID');


    public static function getActionsByName(){
        $actions = ActivityFeeds::select('Action')->lists('Action','Action');
        return $actions;
    }

}