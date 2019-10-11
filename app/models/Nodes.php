<?php

class Nodes extends \Eloquent {

	//protected $fillable = ["NoteID","CompanyID","AccountID","Title","Note","created_at","updated_at","created_by","updated_by" ];

    protected $guarded = array();

    protected $table = 'tblNode';

    protected  $primaryKey = "ServerID";

    public static $rules = array(
        'ServerName' =>      'required|unique:tblNode',
        'ServerIP'   =>      'required|unique:tblNode',
        'LocalIP'    =>      'required|unique:tblNode',
        'Username'   =>      'required',
    );

    public static function getActiveNodes(){
        $Nodes = Nodes::where('Status','1')->lists('ServerName','ServerIP');
        return $Nodes;
    }

    public static function getServersFromCronJob($CronJobID,$CompanyID){
        $Cron = CronJob::where(['CronJobID' => $CronJobID , 'CompanyID' => $CompanyID])->first();
		$Nodes = json_decode($Cron->Settings,true);
		$CheckServerStatus = [];
		if(!empty($Nodes['Nodes'])){
			return $CheckServerStatus = $Nodes['Nodes'];
        }else{
            return $CheckServerStatus;
        }	
    }

    public static function getCurrentIpOfServer(){
        $host      = gethostname();
        $CurrentIp = gethostbyname($host);

        return $CurrentIp;
    }

    public static function FindNodesInCronJob($ServerIP){
        $Crons = CronJob::where('CompanyID',1)->get();
		foreach($Crons as $Cron){
			$Settings = json_decode($Cron->Settings,true);
			if(isset($Settings['Nodes'])){
				$Nodes = $Settings['Nodes'];
				if(in_array($ServerIP,$Nodes)){
					return false;
				}
			}
        }    
        return true;
    }
}