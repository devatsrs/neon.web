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

    public static function getTimeZoneByConnectTime($ConnectTime){
        $TimeZones = Timezones::where(['Status'=>1,'ApplyIF'=>'start'])->orderBY('TimezonesID')->get();
        $Count = count(($TimeZones));
        if($Count>1){
            foreach($TimeZones as $TimeZone){
                if(!empty($TimeZone->FromTime) && !empty($TimeZone->ToTime)){
                    // check on time
                    $date = date('Y-m-d',strtotime($ConnectTime));
                    $FromTime = $date.' '.$TimeZone->FromTime.':00';
                    $ToTime = $date.' '.$TimeZone->ToTime.':00';
                    //echo $ConnectTime.' '.$FromTime.' '.$ToTime;
                    if( strtotime($ConnectTime) >= strtotime($FromTime) && strtotime($ConnectTime) <= strtotime($ToTime)){
                        return $TimeZone->TimezonesID;
                    }
                }
                if(!empty($TimeZone->Months)){
                    $Months = explode(',',$TimeZone->Months);
                    $m = date('m',strtotime($ConnectTime));
                    if(in_array($m,$Months)){
                        return $TimeZone->TimezonesID;
                    }
                }
                if(!empty($TimeZone->DaysOfMonth)){
                    $DaysOfMonth = explode(',',$TimeZone->DaysOfMonth);
                    $d = date('d',strtotime($ConnectTime));
                    if(in_array($d,$DaysOfMonth)){
                        return $TimeZone->TimezonesID;
                    }
                }
                if(!empty($TimeZone->DaysOfWeek)){
                    $DaysOfWeek = explode(',',$TimeZone->DaysOfWeek);
                    $day = date("l",strtotime($ConnectTime)) ;
                    if($day=='Sunday'){
                        $weekdays=1;
                    }else{
                        $weekdays = date("N",strtotime($ConnectTime)) +1 ;
                    }
                    if(in_array($weekdays,$DaysOfWeek)){
                        return $TimeZone->TimezonesID;
                    }
                }
            }
        }else{
            $TimezonesID = Timezones::where(['Status'=>1,'ApplyIF'=>'start'])->pluck('TimezonesID');
            return $TimezonesID;
        }

        $TimezonesID = Timezones::where(['Status'=>1,'Title'=>'Default'])->pluck('TimezonesID');
        return $TimezonesID;
    }

}