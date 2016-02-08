<?php

class UTC extends \Eloquent {
	protected $fillable = [];
    protected $table = "";
    protected $primaryKey = "";

    public static function get_array(){
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        return $tzlist;
    }
}