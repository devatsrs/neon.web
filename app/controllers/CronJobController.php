<?php

class CronJobController extends \BaseController {
    public function ajax_datagrid($type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = User::get_companyID();
        $columns = array('JobTitle','Title','Status');
        $sort_column = $columns[$data['iSortCol_0']];
        $data['Active'] = $data['Active']==''?2:$data['Active'];
        $query = "call prc_GetCronJob (".$companyID.",".$data['Active'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
	
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Cron Job.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Cron Job.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query)->make();
    }

	/**
	 * Display a listing of the resource.
	 * GET /cronjob
	 *
	 * @return Response
	 */
	public function index()
	{
		//
        $commands = CronJobCommand::getCommands();
        $cron_settings = array();
        return View::make('cronjob.index',compact('commands','cron_settings'));
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /cronjob/create
	 *
	 * @return Response
	 */
	public function create()
	{
        $isvalid = CronJob::validate();
        if($isvalid['valid']==1){
            if ($CronJobID = CronJob::insertGetId($isvalid['data'])) {
                CronJob::upadteNextTimeRun($CronJobID);
                return Response::json(array("status" => "success", "message" => "Cron Job Successfully Created"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Cron Job."));
            }
        }else{
            return $isvalid['message'];
        }
	}



	/**
	 * Update the specified resource in storage.
	 * PUT /cronjob/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        if( $id > 0 ) {
            $CronJob = CronJob::findOrFail($id);
            $isvalid = CronJob::validate($id);
            if($isvalid['valid']==1){
                //If user inactivate the cron job , cron job needs to terminate.
                if(isset($isvalid['data']["Status"]) && $CronJob->Status == 1 && $isvalid['data']["Status"] == 0){
                    $this->terminate($id);
                }
                if ($CronJob->update($isvalid['data'])) {
                    CronJob::upadteNextTimeRun($id);
                    return Response::json(array("status" => "success", "message" => "Cron Job Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Cron Job."));
                }
            }else{
                return $isvalid['message'];
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Cron Job."));
        }
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /cronjob/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function delete($id)
    {
        if( intval($id) > 0){
           /* if(!CronJob::checkForeignKeyById($id)) {*/
                try {
                    $result = CronJob::find($id)->delete();
					CronJobLog::where("CronJobID",$id)->delete();
                   	 if ($result) {
                        return Response::json(array("status" => "success", "message" => "Cron Job Successfully Deleted"));
                   	 } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Cron Job."));
                    	}
                	} catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Cron Job is in Use, You cant delete this Cron Job."));
                	}
           /* }else{
                return Response::json(array("status" => "failed", "message" => "Cron Job is in Use, You cant delete this Cron Job."));
            }*/
        }else{
            return Response::json(array("status" => "failed", "message" => "Cron Job is in Use, You cant delete this Cron Job."));
        }
    }
    public function ajax_load_cron_dropdown(){
        $companyID = User::get_companyID();
        $data = Input::all();
        $rateGenerators = "";
        $rateTable = "";
        if(isset($data['CronJobCommandID']) && intval($data['CronJobCommandID']) > 0) {
            $commandconfig = CronJobCommand::getConfig($data['CronJobCommandID']);
            $CronJobCommand = CronJobCommand::find($data['CronJobCommandID']);
            if(isset($data['CronJobID']) && intval($data['CronJobID']) > 0) {
                $query = "call prc_GetCronJobSetting (".$data['CronJobID'].")";
                $cron = DataTableSql::of($query)->getProcResult(array('cron'));
                if($cron['data']['cron']>0){
                    $commandconfigval = json_decode($cron['data']['cron'][0]->Settings);
                }
            }
            $hour_limit = 24;
            $day_limit = 32;
            if($CronJobCommand->Command == 'customerratefileexport' || $CronJobCommand->Command == 'vendorratefileexport' || $CronJobCommand->Command == 'sippyratefilestatus'){
                $CompanyGateway = CompanyGateway::getCompanyGatewayIdList();
            } else if($CronJobCommand->GatewayID > 0){
                $CompanyGateway = CompanyGateway::getGatewayIDList($CronJobCommand->GatewayID);
            }
            if($CronJobCommand->Command == 'sippyaccountusage'){
                $hour_limit = 3;
            }else if($CronJobCommand->Command == 'portaaccountusage'){
                $day_limit= 2;
            }else if($CronJobCommand->Command == 'rategenerator'){
                $day_limit= 2;
                $rateGenerators = RateGenerator::rateGeneratorList($companyID);
                if(!empty($rateGenerators)){
                    $rateGenerators = array(""=> "Select")+$rateGenerators;
                }
                $rateTables = RateTable::where(["CompanyId" => $companyID])->lists('RateTableName', 'RateTableId');
                if(!empty($rateTables)){
                    $rateTables = array(""=> "Select")+$rateTables;
                }
            }else if($CronJobCommand->Command == 'autoinvoicereminder'){
                //$emailTemplates = EmailTemplate::getTemplateArray(array('Type'=>EmailTemplate::INVOICE_TEMPLATE));
				$emailTemplates = EmailTemplate::getTemplateArray(array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE));
                $accounts = Account::getAccountIDList();
            }else if($CronJobCommand->Command == 'accountbalanceprocess'){
                //$emailTemplates = EmailTemplate::getTemplateArray(array('Type'=>EmailTemplate::ACCOUNT_TEMPLATE));
				$emailTemplates = EmailTemplate::getTemplateArray(array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE));
            }else if($CronJobCommand->Command == 'customerratefileexport' || $CronJobCommand->Command == 'customerratefilegeneration' || $CronJobCommand->Command == 'morcustomerrateimport' || $CronJobCommand->Command == 'callshopcustomerrateimport'){
                $customers = Account::getCustomerIDList();
                $customers = array_diff($customers, array('Select'));
            }else if($CronJobCommand->Command == 'vendorratefileexport' || $CronJobCommand->Command == 'vendorratefilegeneration'){
                $vendors = Account::getVendorIDList();
                $vendors = array_diff($vendors, array('Select'));
            }else if($CronJobCommand->Command == 'createsummary'){
                $StartDateMessage = 'in order to create previous days summary please select start date and end date. Otherwise leave it bank. By default system creates last 4 days summary.'; // popup message
            }else if($CronJobCommand->Command == 'resellerpbxaccountusage'){
                $StartDateMessage = "In order to process Reseller's CDRs for previous days please select start and end date. If blank system will process today's CDRs."; // popup message
            }

            $commandconfig = json_decode($commandconfig,true);

            return View::make('cronjob.ajax_config_html', compact('commandconfig','commandconfigval','hour_limit','rateGenerators','rateTables','CompanyGateway','day_limit','emailTemplates','accounts','customers','vendors','StartDateMessage'));
        }
        return '';
    }

    public function history($id){
        $JobTitle = CronJob::where("CronJobID",$id)->pluck("JobTitle");
        $data['StartDateDefault'] 	  	= 	date("Y-m-d",strtotime(''.date('Y-m-d').' -1 months'));
        $data['EndDateDefault']  	= 	date('Y-m-d');
        return View::make('cronjob.history', compact('id','JobTitle','data'));
    }
    public function history_ajax_datagrid($id,$type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = User::get_companyID();
        $data['StartDate'] = !empty($data['StartTime'])?$data['StartDate'].' '.$data['StartTime']:$data['StartDate'];
        $data['EndDate'] = !empty($data['EndTime'])?$data['EndDate'].' '.$data['EndTime']:$data['EndDate'];
        $columns = array('Title','CronJobStatus','Message','created_at');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_GetCronJobHistory (".$id.",'".$data['StartDate']."','".$data['EndDate']."','".$data['Search']."','".$data['Status']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Cron Job History.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Cron Job History.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

        }
        $query .=',0)';

        return DataTableSql::of($query)->make();
    }

    public function activecronjob(){
        return View::make('cronjob.activecronjob');
    }

    public function activecronjob_ajax_datagrid(){
        $data = Input::all();
        $data['iDisplayStart'] +=1;

        $data['Active'] = -1; // all cronjobs running or not running
        if(isset($data['Status']) ){

            if($data['Status']=="running"){
                $data['Status'] = -1;
                $data['Active'] = 1;
            } else if($data['Status']==""){
                $data['Status'] = -1;
            }
        }
        $data['Type']  = empty($data['Type']) ? 0 : $data['Type'];
        $data['Title'] = empty($data['Title']) ? '': $data['Title'];

        $companyID = User::get_companyID();
        $columns = array('Active','PID','JobTitle','RunningTime','LastRunTime','NextRunTime');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_GetActiveCronJob (".$companyID.",'".$data['Title']."',".$data['Status'].",".$data['Active'].",".$data['Type'].",'". date('Y-m-d H:i:s') ."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
        return DataTableSql::of($query)->make();
    }

    public function activeprocessdelete(){

        $data = Input::all();
        $output = CronJob::killactivejobs($data);

        if(isset($output) && $output == !''){
            return Response::json(array("status" => "success", "message" => ".$output."));
        }else{
            return Response::json(array("status" => "failed", "message" => "Cron Job Process is not terminated"));
        }
    }

    /**
     * Cron Job monitor to show all running cron jobs
     * @return mixed
     */
    public function cronjob_monitor(){

        $commands = CronJobCommand::getCommands();
        $Process = new Process();
        $crontab_status = $Process->check_crontab_status();
        return View::make('cronjob.cronjob_monitor', compact('commands','crontab_status'));

    }

    /**
     * Start any cron job
     * @param $CronJobID
     */
    public function trigger($CronJobID){

        //@TODO: what if cron job is running on another server.

        $CompanyID = User::get_companyID();
        $pr_name = 'call prc_getActiveCronJobCommand (';
        $query = $pr_name . $CompanyID . "," . $CronJobID . ")";
        $CronJob = DB::connection('sqlsrv')->select($query);
        $CronJob = json_decode(json_encode($CronJob), true);
        $success = false;
        $CronJob = array_pop($CronJob);
        if(isset($CronJob["Command"]) && !empty($CronJob["Command"]) ) {
            $command = CompanyConfiguration::get("PHP_EXE_PATH"). " " .CompanyConfiguration::get("RM_ARTISAN_FILE_LOCATION"). " " . $CronJob["Command"] . " " . $CompanyID . " " . $CronJobID ;
            $success = run_process($command);
        }
        if($success){
            CronJobLog::createLog($CronJobID,["CronJobStatus"=>CronJob::CRON_SUCCESS, "Message" => "Triggered by " . User::get_user_full_name()]);
            return Response::json(array("status" => "success", "message" => "Cron Job is triggered." ));
        }else{
            return Response::json(array("status" => "failed", "message" => "Failed to trigger Cron Job"));
        }

    }

    /**
     * Terminate the running cronjob
     * @param $CronJobID
     * @return mixed
     */
    public function terminate($CronJobID) {

        $status = CronJob::ActiveCronJobEmailSend($CronJobID);

        if(is_null($status)) {
            return Response::json(array("status" => "failed", "message" => "Invalid CronJobID." ));
        } else if($status == FALSE) {
            return Response::json(array("status" => "failed", "message" => "Cron Job Terminated but Unable to send email." ));
        } else {
            return Response::json(array("status" => "success", "message" => "Cron Job Terminated successfully and email sent." ));
        }

    }

    /** Disable the cronjob
     * @param $CronJobID
     * @return mixed
     */
    public function change_status($CronJobID,$Status=0){

        if($Status == 0 ){
            $Status_to = "Cron Job Disabled";
        }else {
            $Status_to = "Cron Job Enabled";
        }
        if(empty($CronJobID)){
            return Response::json(array("status" => "failed", "message" => "Invalid CronJobID." ));
        } else if(CronJob::find($CronJobID)->update(["Status"=>$Status])){
            CronJobLog::createLog($CronJobID,["CronJobStatus"=>CronJob::CRON_SUCCESS, "Message" => $Status_to . " by " . User::get_user_full_name()]);
            return Response::json(array("status" => "success", "message" => $Status_to ));
        }else {
            return Response::json(array("status" => "failed", "message" => "Failed to Stop the Cron Job." ));
        }
    }

    /** Change Crontab Status
     * @param $CronJobID
     * @return mixed
     */
    public function change_crontab_status($Status=1){

        if($Status == 0 ){
            $Status_to = "Cron Tab Stopped";
        }else {
            $Status_to = "Cron Tab Started";
        }
        $Process = new Process();
        $response = $Process->change_crontab_status($Status);

        if($response){
            return Response::json(array("status" => "success", "message" => $Status_to ));
        }else {
            return Response::json(array("status" => "failed", "message" => "Fail to change status of Cron Tab." ));
        }
    }

    /** check if cron job is failing or not to show in top notification bar
     * @return bool
     */
    public function check_failing(){

        $CompanyID = User::get_companyID();
        $is_cronjob_failing = CronJob::is_cronjob_failing($CompanyID);
        if($is_cronjob_failing){
            return Response::json(array("status" => "success", "message" => "Cron Job is Failing." ));
        }else {
            return Response::json(array("status" => "success", "message" => "" ));
        }

    }
}