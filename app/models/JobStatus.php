<?php

class JobStatus extends \Eloquent {
	protected $fillable = [];

	protected $table = "tblJobStatus";
	protected  $primaryKey = "JobStatusID";

    public static function getJobStatusIDList(){
        $row = JobStatus::lists( 'Title','JobStatusID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;

    }
    public static function getJobStatusPendingFailed(){
        $row = JobStatus::whereIn('Code',array('P','F'))->lists( 'Title','JobStatusID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }
}