<?php

class CronJob extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('CronJobID');

    protected $table = 'tblCronJob';

    protected  $primaryKey = "CronJobID";

    const  MINUTE = 1;
    const  HOUR = 2;
    const  DAILY = 3;
    const  WEEKLY = 4;
    const  MONTHLY = 5;
    const  YEARLY = 6;
    const  CUSTOM = 7;

    const  CRON_SUCCESS = 1;
    const  CRON_FAIL = 2;

    const ACTIVE = 1;
    const INACTIVE = 0;
	const EMAILTEMPLATE = "CronjobActiveEmail";
    public static $cron_type = array(self::MINUTE=>'Minute',self::HOUR=>'Hourly',self::DAILY=>'Daily');

    public static function checkForeignKeyById($id){
        $hasInCronLog = CronJobLog::where("CronJobID",$id)->count();
        if( intval($hasInCronLog) > 0 ){
            return true;
        }else{
            return false;
        }
    }
//@Todo:
    public static function validate($id=0){
        $valid = array('valid'=>0,'message'=>'Some thing wrong with cron model validation','data'=>'');
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;

        if(isset($data['JobTitle']) && trim($data['JobTitle']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Job Title is required"));
            return $valid;
        }else{
            if($id>0){
                $result = CronJob::select('JobTitle')->where('JobTitle','=',$data['JobTitle'])->where('CompanyID','=',$companyID)->where('CronJobID','<>',$id)->first();
                if (!empty($result)) {
                    $valid['message'] = Response::json(array("status" => "failed", "message" => "Job title already exist in Cron Jobs."));
                    return $valid;
                }
            }else{
                $result = CronJob::select('JobTitle')->where('JobTitle','=',$data['JobTitle'])->where('CompanyID','=',$companyID)->first();
                if(!empty($result)){
                    $valid['message'] = Response::json(array("status" => "failed", "message" => "Job title already exist in Cron Jobs."));
                    return $valid;
                }
            }
        }
        $CronJobCommand = CronJobCommand::find($data['CronJobCommandID']);

        if($CronJobCommand->Command == 'rategenerator'){
            $tag = '"rateTableID":"'.$data["rateTables"].'"';

            if(DB::table('tblCronJob')->where('Settings','LIKE', '%'.$tag.'%')->where('CronJobID','<>',$id)->count() > 0){
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Rate table already taken."));
                return $valid;
            }
        }elseif(isset($data["CompanyGatewayID"]) && $data["CompanyGatewayID"] >0 && isset($data["CronJobCommandID"]) && $data['CronJobCommandID'] > 0){
            $tag = '"CompanyGatewayID":"'.$data["CompanyGatewayID"].'"';
            if(DB::table('tblCronJob')->where('Settings','LIKE', '%'.$tag.'%')->where('CronJobCommandID', $data['CronJobCommandID'] )->where('CronJobID','<>',$id)->count() > 0){
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Gateway already taken."));
                return $valid;
            }

        }else{
            if(DB::table('tblCronJob')->where('CronJobCommandID','=',$data['CronJobCommandID'])->where('CronJobID','<>',$id)->count() > 0){
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Cron Job is Already Setup."));
                return $valid;
            }

        }

        if(isset($data['Setting']['JobTime']) && trim($data['Setting']['JobTime']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Job time is required"));
            return $valid;
        }

        if(isset($data['Setting']['MaxInterval']) && trim($data['Setting']['MaxInterval']) == '' ) {
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Max Call Duration is required"));
            return $valid;
        }else if(isset($data['Setting']['JobStartTime']) && trim($data['Setting']['JobStartTime']) == '' ) {
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Job start time is required"));
            return $valid;
        }else if(isset($data['Setting']['MaxInterval']) && isset($CronJobCommand) &&($data['Setting']['MaxInterval'] < 1 || (int)$data['Setting']['MaxInterval'] > 180 ) && $CronJobCommand->Command == 'sippyaccountusage') {
            $valid['message'] = Response::json(array("status" => "failed", "message" => " Interval is between 1 minutes to 180 minutes you can set"));
            return $valid;
        }else if(isset($data['Setting']['MaxInterval']) && isset($CronJobCommand) &&($data['Setting']['MaxInterval'] < 1 || (int)$data['Setting']['MaxInterval'] > 2880 ) && $CronJobCommand->Command == 'portaaccountusage') {
            $valid['message'] = Response::json(array("status" => "failed", "message" => " Interval is between 1 minutes to 2880 minutes you can set"));
            return $valid;
        }elseif(isset($data['rateGenerators'])&& trim($data['rateGenerators']) == '' && $CronJobCommand->Command == 'rategenerator'){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Rate Generator from dropdown"));
            return $valid;
        }elseif(isset($data['rateTables'])&& trim($data['rateTables']) == '' && $CronJobCommand->Command == 'rategenerator'){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Rate Table from dropdown"));
            return $valid;
        }elseif(isset($data['Setting']['EffectiveDay'])&& trim($data['Setting']['EffectiveDay']) == '' && $CronJobCommand->Command == 'rategenerator'){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please enter Effective Day"));
            return $valid;
        }elseif(isset($data['Setting']['EffectiveDay']) && !is_numeric($data['Setting']['EffectiveDay']) && $CronJobCommand->Command == 'rategenerator'){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please enter numeric Effective Day"));
            return $valid;
        }elseif(isset($data['CompanyGatewayID'])&& trim($data['CompanyGatewayID']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Gateway"));
            return $valid;
        }elseif(isset($data['TemplateID'])&& trim($data['TemplateID']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Template"));
            return $valid;
        }else if(isset($data['Setting']['StartDate']) &&  isset($data['Setting']['EndDate']) && trim($data['Setting']['StartDate']) == '' && trim($data['Setting']['EndDate']) != '' ) {
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Start date is required"));
            return $valid;
        }else if(isset($data['Setting']['StartDate']) &&  isset($data['Setting']['EndDate']) && trim($data['Setting']['StartDate']) != '' && trim($data['Setting']['EndDate']) == '' ) {
            $valid['message'] = Response::json(array("status" => "failed", "message" => "End date is required"));
            return $valid;
        }else if(isset($data['Setting']['StartDate']) &&  isset($data['Setting']['EndDate']) && $data['Setting']['StartDate'] > $data['Setting']['EndDate']){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Dates are invalid"));
            return $valid;
        }

        $today = date('Y-m-d');
        $data['created_by'] = User::get_user_full_name();
        $data['created_at'] =  $today;
        if(isset($data['Setting'])) {
            if (isset($data['rateGenerators'])) {
                $data['Setting']['rateGeneratorID'] = $data['rateGenerators'];
                $data['Setting']['rateTableID'] = $data['rateTables'];
                $data['Setting']['EffectiveRate'] = $data['EffectiveRate'];
                if(!empty($data['replace_rate'])&& $data['replace_rate']==1){
                    $data['Setting']['replace_rate'] = 1;
                    unset($data['replace_rate']);
                }else{
                    $data['Setting']['replace_rate'] = 0;
                }
                unset($data['rateGenerators']);
                unset($data['rateTables']);
                unset($data['EffectiveRate']);

            }
            if(isset($data['CompanyGatewayID'])){
                $data['Setting']['CompanyGatewayID'] = $data['CompanyGatewayID'];
                unset($data['CompanyGatewayID']);
            }
            if(isset($data['AccountID'])){
                $data['Setting']['AccountID'] = $data['AccountID'];
                unset($data['AccountID']);
            }
            if(isset($data['TemplateID'])){
                $data['Setting']['TemplateID'] = $data['TemplateID'];
                unset($data['TemplateID']);
            }
            $data['Settings'] = json_encode($data['Setting']);
        }
        unset($data['Status_name']);
        unset($data['CronJobID']);
        unset($data['Setting']);
        $valid['valid'] = 1;
        $valid['data'] = $data;
         return $valid;
    }


    public static function ActiveCronJobEmailSend($CronJobID){
        $emaildata = array();

        if(empty($CronJobID)){
            return NULL;
        }

        $CronJob = CronJob::find($CronJobID);
        $JobTitle = $CronJob->JobTitle;
        $CompanyID = $CronJob->CompanyID;
        $LastRunTime = $CronJob->LastRunTime;
        $ComanyName = Company::getName($CompanyID);
        $PID = $CronJob->PID;
        $MysqlPID=$CronJob->MysqlPID;

        $minute = CronJob::calcTimeDiff($LastRunTime);

        $cronsetting = json_decode($CronJob->Settings,true);
        $ActiveCronJobEmailTo = isset($cronsetting['ErrorEmail']) ? $cronsetting['ErrorEmail'] : '';

        $ReturnStatus = terminate_process($PID);
        if($MysqlPID!=''){
            try{
                $MysqlProcess=DB::select("SELECT * FROM INFORMATION_SCHEMA.PROCESSLIST where ID=".$MysqlPID);
                if(!empty($MysqlProcess)){
                    terminateMysqlProcess($MysqlPID);
                }
            }catch (\Exception $err) {
                Log::error($err);
            }

        }

        //Kill the process.
        $CronJob->update([ "PID"=>"", "Active"=>0,"LastRunTime" => date('Y-m-d H:i:00'),"MysqlPID"=>"","ProcessID"=>""]);

        CronJobLog::createLog($CronJobID,["CronJobStatus"=>CronJob::CRON_FAIL, "Message"=> "Terminated by " . User::get_user_full_name()]);

        $emaildata['KillCommand'] = "";
        $emaildata['ReturnStatus'] = $ReturnStatus;
        $emaildata['DetailOutput'] = array();

        $emaildata['CompanyID'] = $CompanyID;
        $emaildata['Minute'] = $minute;
        $emaildata['JobTitle'] = $CronJob->JobTitle;
        $emaildata['PID'] = $CronJob->PID;
        $emaildata['CompanyName'] = $ComanyName;
        $emaildata['EmailTo'] = $ActiveCronJobEmailTo;
        $emaildata['EmailToName'] = '';
        $emaildata['Subject'] = $JobTitle. ' is terminated, Was running since ' . $minute .' minutes.';
        $emaildata['Url'] = \Illuminate\Support\Facades\URL::to('/cronjob_monitor');

        $emailstatus = sendMail('emails.cronjob.ActiveCronJobEmailSend', $emaildata);
        return $emailstatus;
    }

    public static function calcTimeDiff($LastRunTime)
    {
        $seconds = strtotime(date('Y-m-d H:i:s')) - strtotime($LastRunTime);
        $minutes = floor(($seconds / 60));
        if (isset($minutes) && $minutes != '')
        {
            return $minutes;
        }else{
            return 0;
        }

    }

    // check sippy and vos download cronjob is active or not
    public static function checkCDRDownloadFiles(){
        $CompanyID = User::get_companyID();
        $CronJonCommandsIds = array();
        $rows = CronJobCommand::where(["Status"=> 1,'CompanyID'=>$CompanyID])->whereIn('Command',array('sippydownloadcdr','vosdownloadcdr'))->get()->toArray();
        if(count($rows)>0){
            foreach($rows as $row){
                if(!empty($row['CronJobCommandID'])){
                    $CronJonCommandsIds[]=$row['CronJobCommandID'];
                }
            }

           $count = CronJob::where(["Status"=> 1,'CompanyID'=>$CompanyID])->whereIn('CronJobCommandID',$CronJonCommandsIds)->count();
           if($count>0){
               return true;
           }
        }
        return false;
    }

    public static function killactivejobs($data){
        $CronJobID = $data['JobID'];
        $CronJob = CronJob::find($CronJobID);

        $PID = $data['PID'];
        $CronJobData = array();
        $CronJobData['Active'] = 0;
        $CronJobData['PID'] = '';
        $output = '';
        if(!empty($PID)) {
            if (getenv("APP_OS") == "Linux") {
                $command = 'kill -9 ' . $PID;
            } else {
                $command = 'Taskkill /PID ' . $PID . ' /F';
            }
            $output = exec($command, $op);
            Log::info($command);
            Log::info($output);
        }
        $CronJob->update($CronJobData);
        return $output;
    }

    public static function upadteNextTimeRun($CronJobID,$skipLastRunTime=false){
        $CronJob =  CronJob::find($CronJobID);
        $data['NextRunTime'] = CronJob::calcNextTimeRun($CronJob->CronJobID,$skipLastRunTime);
        $CronJob->update($data);
    }

    public static function calcNextTimeRun($CronJobID,$skipLastRunTime = false){
        $CronJob =  CronJob::find($CronJobID);
        $cronsetting = json_decode($CronJob->Settings);
        if(!empty($CronJob) && isset($cronsetting->JobTime)){
            switch($cronsetting->JobTime) {
                case 'HOUR':
                    if($CronJob->LastRunTime == '' || $skipLastRunTime ){
                        $strtotime = strtotime('+'.$cronsetting->JobInterval.' hour');
                    }else{
                        $strtotime = strtotime($CronJob->LastRunTime)+$cronsetting->JobInterval*60*60;
                    }
                    return date('Y-m-d H:i:00',$strtotime);
                case 'MINUTE':
                    if($CronJob->LastRunTime == ''|| $skipLastRunTime){
                        $strtotime = strtotime('+'.$cronsetting->JobInterval.' minute');
                    }else{
                        $strtotime = strtotime($CronJob->LastRunTime)+$cronsetting->JobInterval*60;
                    }
                    return date('Y-m-d H:i:00',$strtotime);
                case 'DAILY':
                    if($CronJob->LastRunTime == ''|| $skipLastRunTime){
                        $strtotime = strtotime('+'.$cronsetting->JobInterval.' day');
                    }else{
                        $strtotime = strtotime($CronJob->LastRunTime)+$cronsetting->JobInterval*60*60*24;
                    }
                    if(isset($cronsetting->JobStartTime)){
                        return date('Y-m-d',$strtotime).' '.date("H:i:00", strtotime("$cronsetting->JobStartTime"));
                    }
                    return date('Y-m-d H:i:00',$strtotime);
                case 'MONTHLY':
                    if($CronJob->LastRunTime == ''|| $skipLastRunTime){
                        $strtotime = strtotime('+'.$cronsetting->JobInterval.' month');
                    }else{
                        $strtotime = strtotime("+$cronsetting->JobInterval month", strtotime($CronJob->LastRunTime));
                    }
                    if(isset($cronsetting->JobStartTime)){
                        return date('Y-m-d',$strtotime).' '.date("H:i:00", strtotime("$cronsetting->JobStartTime"));
                    }
                    return date('Y-m-d H:i:00',$strtotime);
                case 'SECONDS':
                    if($CronJob->LastRunTime == ''){
                        $strtotime = strtotime('+'.$cronsetting->JobInterval.' seconds');
                    }else{
                        $strtotime = strtotime($CronJob->LastRunTime)+$cronsetting->JobInterval;
                    }
                    return date('Y-m-d H:i:s',$strtotime);
                default:
                    return '';
            }
        }
    }

    /**
     * used when company timezone change we need to update all cron job next run time.
     * @param $CompanyID
     */
    public static function updateAllCronJobNextRunTime($CompanyID) {
        $AllActiveCronJobs = CronJob::where(['CompanyID' => $CompanyID, "Status" => 1])->get()->toArray();
        if (count($AllActiveCronJobs) > 0) {
            foreach ($AllActiveCronJobs as $CronJob) {
                if (!empty($CronJob['CronJobID']) && $CronJob['CronJobID'] > 0) {
                    self::upadteNextTimeRun($CronJob['CronJobID'],true);
                }
            }
        }
    }

    /**
     *  check if cron job is failing or not.
     * @param $companyID
     * @return bool
     */
    public static function is_cronjob_failing($companyID){
        $Status = 1;
        $Type = 0; // All Cron Jobs Types
        $Active = -1; // All Cron Jobs which are running (Active)State
        $sort_column = "JobTitle";
        $sort_type = "ASC";
        $iDisplayLength = 100;
        $p_PageNumber = 1;
        $p_CurrentDateTime = date('Y-m-d H:i:s');
        //call prc_GetActiveCronJob (1,'',1,-1,0,'2017-03-22 10:05:31',1 ,50,'Active','desc',0)
        $query = "call prc_GetActiveCronJob (".$companyID.",'',".$Status.",".$Active.",".$Type.",'". $p_CurrentDateTime ."',".$p_PageNumber." ,".$iDisplayLength.",'".$sort_column."','".$sort_type."',0)";
        $result  =  \Illuminate\Support\Facades\DB::select($query);
        $resultArray = json_decode(json_encode($result), true);
        foreach($resultArray as $row){
            if($row["CronJobStatus"]== CronJob::CRON_FAIL){
                return true;
            }
        }
        return false;

    }

    public static function create_system_report_alert_job($CompanyID,$active){
        $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('neonalerts',$CompanyID);
        $settings = CompanyConfiguration::get('NEON_ALERTS');
        $JobTitle = 'Neon System Alerts';
        $today = date('Y-m-d');
        $cronJobs_count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$CronJobCommandID])->count();
        log::info('count - '.$cronJobs_count.'========='.$active);
        if($cronJobs_count == 0 && !empty($settings)){
            $cronjobdata = array();
            $cronjobdata['CompanyID'] = $CompanyID;
            $cronjobdata['CronJobCommandID'] = $CronJobCommandID;
            $cronjobdata['Settings'] = $settings;
            $cronjobdata['Status'] = 1;
            $cronjobdata['created_by'] = User::get_user_full_name();
            $cronjobdata['created_at'] =  $today;
            $cronjobdata['JobTitle'] = $JobTitle;
            CronJob::create($cronjobdata);
        } else if($cronJobs_count == 1 && $active == 1){
            CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$CronJobCommandID])->update(['Status'=>1]);
        }
    }
}