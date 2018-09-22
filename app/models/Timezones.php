<?php

class Timezones extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblTimezones';
    protected $primaryKey = "TimezonesID";
    public $timestamps  = false;

    public static $DaysOfWeek = array(
        "1" => "Sunday",
        "2" => "Monday",
        "3" => "Tuesday",
        "4" => "Wednesday",
        "5" => "Thursday",
        "6" => "Friday",
        "7" => "Saturday"
    );

    public static $Months = array(
        "1" => "January",
        "2" => "February",
        "3" => "March",
        "4" => "April",
        "5" => "May",
        "6" => "June",
        "7" => "July",
        "8" => "August",
        "9" => "September",
        "10" => "October",
        "11" => "November",
        "12" => "December"
    );

    public static $ApplyIF = array(
        "start" => "Session starts during this timezone",
        "end" => "Session finished during this timezone",
        "both" => "Session starts and finished during this timezone"
    );

    public static function getTimezonesIDList($nodefault=0,$reverse = 0) {
        $Timezones = Timezones::where(['Status' => 1]);
        if($nodefault==1) {
            $Timezones->where('TimezonesID','!=',1);
        }
        if($reverse == 0) {
            return $Timezones->select(['Title', 'TimezonesID'])->orderBy('Title')->lists('Title', 'TimezonesID');
        } else {
            return $Timezones->select(['Title', 'TimezonesID'])->orderBy('Title')->lists('TimezonesID','Title');
        }
    }

    public static function getTimezonesName($id){
        $Timezone = Timezones::find($id);
        if(!empty($Timezone)){
            return $Timezone->Title;
        }
        return '';
    }

}