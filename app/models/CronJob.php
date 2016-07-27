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
                $CronJob = CronJob::findOrFail($id);
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

        }elseif($CronJobCommand->Command == 'pendingduesheets'){
            if(DB::table('tblCronJob')->where('CronJobCommandID','=',$data['CronJobCommandID'])->where('CronJobID','<>',$id)->count() > 0){
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Command already taken."));
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
        }

        $today = date('Y-m-d');
        $data['created_by'] = User::get_user_full_name();
        $data['created_at'] =  $today;
        if(isset($data['Setting'])) {
            if (isset($data['rateGenerators'])) {
                $data['Setting']['rateGeneratorID'] = $data['rateGenerators'];
                $data['Setting']['rateTableID'] = $data['rateTables'];
                unset($data['rateGenerators']);
                unset($data['rateTables']);

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

        $minute = CronJob::calcTimeDiff($LastRunTime);

        $cronsetting = json_decode($CronJob->Settings,true);
        $ActiveCronJobEmailTo = isset($cronsetting['ErrorEmail']) ? $cronsetting['ErrorEmail'] : '';

        $ReturnStatus = terminate_process($PID);

        //Kill the process.
        $CronJob->update([ "PID"=>"", "Active"=>0,"LastRunTime" => date('Y-m-d H:i:00')]);

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

        $emailstatus = Helper::sendMail('emails.cronjob.ActiveCronJobEmailSend', $emaildata);
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

}