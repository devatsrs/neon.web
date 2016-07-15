<?php

class CronJobLog extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('CronJobLogID');

    protected $table = 'tblCronJobLog';

    protected  $primaryKey = "CronJobLogID";

    public static function createLog($CronJobID,$moredata=array()){
        $joblogdata = array();
        $joblogdata['CronJobID'] = $CronJobID;
        $joblogdata['created_at'] = date('Y-m-d H:i:s');
        $joblogdata['created_by'] = User::get_user_full_name();
        $joblogdatafinal = $moredata+$joblogdata;
        if(empty($joblogdatafinal['CronJobStatus'])){
            $joblogdatafinal['CronJobStatus'] = CronJob::CRON_SUCCESS;
        }
        CronJobLog::insert($joblogdatafinal);
    }

}