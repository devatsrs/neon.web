<?php

class CronJobCommandApp extends \Eloquent {
    protected $connection = "sqlapprm";
	protected $fillable = [];
    protected $guarded = array('CronJobCommandID');

    protected $table = 'tblCronJobCommand';

    protected  $primaryKey = "CronJobCommandID";

    public static function getCommands(){
        $CompanyID = User::get_companyID();
        $row = CronJobCommandApp::where(["Status"=> 1])->orderBy('Title', 'asc')->lists('Title', 'CronJobCommandID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }
    public static function getConfig($CronJobCommandID){
        return CronJobCommandApp::where(['CronJobCommandID'=>$CronJobCommandID,'Status'=>1])->pluck('Settings');
    }

    public static function getCronJobCommandIDByCommand($name,$CompanyID){
        return CronJobCommandApp::where(['Command'=>$name,'CompanyID'=>$CompanyID,'Status'=>1])->pluck('CronJobCommandID');
    }
}