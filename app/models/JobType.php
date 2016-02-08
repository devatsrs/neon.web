<?php

class JobType extends \Eloquent {
	protected $fillable = [];

	protected $table = "tblJobType";
	protected  $primaryKey = "JobTypeID";


    public static function getJobTypeIDList(){
        $row = JobType::lists( 'Title','JobTypeID');
        if(!empty($row)){
            $row = array(""=> "Select a Type")+$row;
        }
        return $row;

    }
}
