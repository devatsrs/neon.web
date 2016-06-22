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
                $file_path = getenv('UPLOAD_PATH') .'/Cron Job.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Cron Job.xls';
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
            if (CronJob::create($isvalid['data'])) {
                return Response::json(array("status" => "success", "message" => "Cron Job Successfully Created"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Cron Job."));
            }
        }else{
            return $isvalid['message'];
        }
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /cronjob
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 * GET /cronjob/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /cronjob/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
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
                if ($CronJob->update($isvalid['data'])) {
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
            if($CronJobCommand->GatewayID > 0){
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
                    $rateGenerators = array(""=> "Select a Rate Generator")+$rateGenerators;
                }
                $rateTables = RateTable::where(["CompanyId" => $companyID])->lists('RateTableName', 'RateTableId');
                if(!empty($rateTables)){
                    $rateTables = array(""=> "Select a Rate Table")+$rateTables;
                }
            }else if($CronJobCommand->Command == 'autoinvoicereminder'){
                $emailTemplates = EmailTemplate::getTemplateArray(array('Type'=>EmailTemplate::INVOICE_TEMPLATE));
                $accounts = Account::getAccountIDList();
            }


            $commandconfig = json_decode($commandconfig,true);


            return View::make('cronjob.ajax_config_html', compact('commandconfig','commandconfigval','hour_limit','rateGenerators','rateTables','CompanyGateway','day_limit','emailTemplates','accounts'));
        }
        return '';
    }

    public function history($id){
        return View::make('cronjob.history', compact('id'));
    }
    public function history_ajax_datagrid($id,$type) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = User::get_companyID();
        $columns = array('Title','CronJobStatus','Message','created_at');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_GetCronJobHistory (".$id.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Cron Job History.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Cron Job History.xls';
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
        $companyID = User::get_companyID();
        $columns = array('JobTitle','PID','LastRunTime');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_GetActiveCronJob (".$companyID.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
        return DataTableSql::of($query)->make();
    }

    public function activeprocessdelete(){

        $data = Input::all();
        $CronJobID = $data['JobID'];
        $CronJob = CronJob::find($CronJobID);

        $PID = $data['PID'];
        $CronJobData = array();
        $CronJobData['Active'] = 0;
        $CronJobData['PID'] = '';

        if(getenv("APP_OS") == "Linux"){
            $command = 'kill -9 '.$PID;
        }else{
            $command = 'Taskkill /PID '.$PID.' /F';
        }
        $output = exec($command,$op);
        Log::info($command);
        Log::info($output);
        $CronJob->update($CronJobData);


        if(isset($output) && $output == !''){
            return Response::json(array("status" => "success", "message" => ".$output."));
        }else{
            return Response::json(array("status" => "failed", "message" => "Cron Job Process is not terminated"));
        }
    }

    public function cronjob_monitor(){

        return View::make('cronjob.cronjob_monitor', compact(''));

    }
}