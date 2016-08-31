<?php

class JobType extends \Eloquent {
	protected $fillable = [];

	protected $table = "tblJobType";
	protected  $primaryKey = "JobTypeID";


    public static function getJobTypeIDList(){
        $row = JobType::orderBy('Title', 'ASC')->lists( 'Title','JobTypeID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;

    }
    public static function checkJobType($code){
        return JobType::where(["Code" => $code])->count();
    }
}
