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
        "start" => "Session starts during this time of day",
        "end" => "Session finished during this time of day",
        "both" => "Session starts and finished during this time of day"
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

    public static function getTimeZoneDropDownList(){
        $row=array();
        $row = Timezones::where('Status',1)->orderby('TimezonesID','asc')->lists('Title', 'TimezonesID');
        return $row;
    }

    public static function getTimeZoneByConnectTime($ConnectTime){
        $TimeZones = Timezones::where(['Status'=>1,'ApplyIF'=>'start'])->orderBY('TimezonesID')->get();
        $TimeZoneCount = count(($TimeZones));
        if($TimeZoneCount>1){
            foreach($TimeZones as $TimeZone){
                $count=0;
                if(!empty($TimeZone->FromTime) && !empty($TimeZone->ToTime)){
                    // check on time
                    $date = date('Y-m-d',strtotime($ConnectTime));
                    $FromTime = $date.' '.$TimeZone->FromTime.':00';
                    $ToTime = $date.' '.$TimeZone->ToTime.':00';
                    //echo $ConnectTime.' '.$FromTime.' '.$ToTime;
                    if( strtotime($ConnectTime) >= strtotime($FromTime) && strtotime($ConnectTime) <= strtotime($ToTime)){
                        //return $TimeZone->TimezonesID;
                        $count++;
                    }
                }else{
                    $count++;
                }
                if(!empty($TimeZone->Months)){
                    $Months = explode(',',$TimeZone->Months);
                    $m = date('m',strtotime($ConnectTime));
                    if(in_array($m,$Months)){
                        //return $TimeZone->TimezonesID;
                        $count++;
                    }
                }else{
                    $count++;
                }
                if(!empty($TimeZone->DaysOfMonth)){
                    $DaysOfMonth = explode(',',$TimeZone->DaysOfMonth);
                    $d = date('d',strtotime($ConnectTime));
                    if(in_array($d,$DaysOfMonth)){
                        //return $TimeZone->TimezonesID;
                        $count++;
                    }
                }else{
                    $count++;
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
                        //return $TimeZone->TimezonesID;
                        $count++;
                    }
                }else{
                    $count++;
                }
                if($count==4){
                    return $TimeZone->TimezonesID;
                }
            }
        }else{
            $TimezonesID = Timezones::where(['Status'=>1,'ApplyIF'=>'start'])->pluck('TimezonesID');
            return $TimezonesID;
        }

        $TimezonesID = Timezones::where(['Status'=>1,'Title'=>'Default'])->pluck('TimezonesID');
        return $TimezonesID;
    }

    public static function getTimeZoneByConnectAndDisconnectTime($ConnectTime,$DisconnectTime){
        $TimeZones = Timezones::where(['Status'=>1])->orderBY('TimezonesID')->get();
        $TimeZoneCount = count(($TimeZones));
        if($TimeZoneCount>1){
            foreach($TimeZones as $TimeZone){
                $count=0;
                if(!empty($TimeZone->FromTime) && !empty($TimeZone->ToTime)){
                    // check on time
                    $cdate      = date('Y-m-d',strtotime($ConnectTime));
                    $dcdate     = date('Y-m-d',strtotime($DisconnectTime));
                    $cFromTime  = $cdate.' '.$TimeZone->FromTime.':00';
                    $cToTime    = $cdate.' '.$TimeZone->ToTime.':00';
                    $dcFromTime = $dcdate.' '.$TimeZone->FromTime.':00';
                    $dcToTime   = $dcdate.' '.$TimeZone->ToTime.':00';

                    if($TimeZone->ApplyIF == 'start' && strtotime($ConnectTime) >= strtotime($cFromTime) && strtotime($ConnectTime) <= strtotime($cToTime)) {
                        $count++;
                    } else if($TimeZone->ApplyIF == 'end' && strtotime($DisconnectTime) >= strtotime($dcFromTime) && strtotime($DisconnectTime) <= strtotime($dcToTime)) {
                        $count++;
                    } else if($TimeZone->ApplyIF == 'both' && (strtotime($ConnectTime) >= strtotime($cFromTime) && strtotime($ConnectTime) <= strtotime($cToTime)) && (strtotime($DisconnectTime) >= strtotime($dcFromTime) && strtotime($DisconnectTime) <= strtotime($dcToTime))) {
                        $count++;
                    }
                } else {
                    $count++;
                }
                if(!empty($TimeZone->Months)){
                    $Months = explode(',',$TimeZone->Months);
                    $cm  = date('m', strtotime($ConnectTime));
                    $dcm = date('m', strtotime($DisconnectTime));

                    if($TimeZone->ApplyIF == 'start' && in_array($cm,$Months)) {
                        $count++;
                    } else if($TimeZone->ApplyIF == 'end' && in_array($dcm,$Months)) {
                        $count++;
                    } else if($TimeZone->ApplyIF == 'both' && in_array($cm,$Months) && in_array($dcm,$Months)) {
                        $count++;
                    }
                } else {
                    $count++;
                }
                if(!empty($TimeZone->DaysOfMonth)){
                    $DaysOfMonth = explode(',',$TimeZone->DaysOfMonth);
                    $cd  = date('d',strtotime($ConnectTime));
                    $dcd = date('d',strtotime($DisconnectTime));

                    if($TimeZone->ApplyIF == 'start' && in_array($cd,$DaysOfMonth)) {
                        $count++;
                    } else if($TimeZone->ApplyIF == 'end' && in_array($dcd,$DaysOfMonth)) {
                        $count++;
                    } else if($TimeZone->ApplyIF == 'both' && in_array($cd,$DaysOfMonth) && in_array($dcd,$DaysOfMonth)) {
                        $count++;
                    }
                } else {
                    $count++;
                }
                if(!empty($TimeZone->DaysOfWeek)){
                    $DaysOfWeek = explode(',',$TimeZone->DaysOfWeek);
                    $cday  = date("l",strtotime($ConnectTime));
                    $dcday = date("l",strtotime($DisconnectTime));

                    $cweekdays  = $cday=='Sunday' ? 1 : date("N",strtotime($ConnectTime)) + 1;
                    $dcweekdays = $dcday=='Sunday' ? 1 : date("N",strtotime($DisconnectTime)) + 1;

                    if($TimeZone->ApplyIF == 'start' && in_array($cweekdays,$DaysOfWeek)) {
                        $count++;
                    } else if($TimeZone->ApplyIF == 'end' && in_array($dcweekdays,$DaysOfWeek)) {
                        $count++;
                    } else if($TimeZone->ApplyIF == 'both' && in_array($cweekdays,$DaysOfWeek) && in_array($dcweekdays,$DaysOfWeek)) {
                        $count++;
                    }
                } else {
                    $count++;
                }
                if($count==4){
                    return $TimeZone->TimezonesID;
                }
            }
        }

        $TimezonesID = Timezones::where(['Status'=>1,'Title'=>'Default'])->pluck('TimezonesID');
        return $TimezonesID;
    }

}