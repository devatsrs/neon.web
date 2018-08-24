<?php

class JobsController extends \BaseController {

    public function ajax_datagrid() {

        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['Status'] = !empty($data['Status'])?$data['Status']:0;
        $data['Type'] = !empty($data['Type'])?$data['Type']:0;
        $data['AccountID'] = !empty($data['AccountID'])?$data['AccountID']:0;
        $data['JobLoggedUserID'] = !empty($data['JobLoggedUserID'])?$data['JobLoggedUserID']:0;

        $columns = array('Title','Type','Status','created_at','CreatedBy','JobID','ShowInCounter','updated_at');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();
        if (User::is_admin()) {
            $isAdmin = 1;
        }else{
            $userID = User::get_userID();
            $data['JobLoggedUserID'] = $userID;
            $isAdmin = 0;
        }


/*@companyid INT,
@Status INT,
@Type INT,
@AccountID INT,
@UserID	 INT,
@isAdmin INT ,
@PageNumber INT,
@RowspPage INT,
@lSortCol NVARCHAR(50),
@SortOrder NVARCHAR(5),
@isExport INT = 0*/

        $query = "call prc_GetAllJobs (".$companyID.",".$data['Status'].",".$data['Type'].",".$data['AccountID'].",".$data['JobLoggedUserID'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        //echo $query;exit;
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Customer Rates', function ($excel) use ($excel_data) {
                $excel->sheet('Customer Rates', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        $query .=",0)";

        return DataTableSql::of($query)->make();

    }

    public function index() {
        //if( User::checkPermission('Job') ) {
            $jobtype = JobType::getJobTypeIDList();
            $jobstatus = JobStatus::getJobStatusIDList();
            $creatdby = User::getUserIDList();
            $account = Account::getAccountIDList();
            $jobstatus_for_terminate = JobStatus::getJobStatusPendingFailed();//@TODO: to show only Pending and Failed Status

        return View::make('jobs.index', compact('jobtype','jobstatus','creatdby','account','jobstatus_for_terminate'));
        //}
    }

    public function show($id) {
        //if( User::checkPermission('Job') ) {
            $job = DB::table('tblJob')
                ->join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                ->join('tblJobType', 'tblJob.JobTypeID', '=', 'tblJobType.JobTypeID')
                ->select(
                    'tblJob.Title', 'tblJob.Description', 'tblJob.AccountID', 'tblJob.Options', 'tblJob.JobStatusMessage', 'tblJob.OutputFilePath', 'tblJobType.Title as Type', 'tblJobStatus.Title as Status', 'tblJob.created_at', 'tblJob.CreatedBy', 'tblJob.updated_at', 'tblJob.ModifiedBy', 'tblJob.JobID','tblJob.EmailSentStatus','tblJob.EmailSentStatusMessage','tblJob.JobStatusID'
                )
                ->where("tblJob.JobID", $id)
                ->first();


            $job_file = DB::table('tblJobFile')
                ->where("tblJobFile.JobID", $id)
                ->first();


            return View::make('jobs.show', compact('id', 'job', 'job_file'));
        //}
    }

    public function exports($type) {
        //if( User::checkPermission('Job') ) {
            //When admin show all jobs by all user.
            if (User::is_admin()) {

                $CompanyID = User::get_companyID();

                $jobs = Job::
                join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                    ->join('tblJobType', 'tblJob.JobTypeID', '=', 'tblJobType.JobTypeID')
                    ->where("tblJob.CompanyID", $CompanyID)
                    ->orderBy("tblJob.JobID", "desc")
                    ->get(['tblJob.Title', 'tblJobType.Title as Type', 'tblJobStatus.Title as Status', 'tblJob.created_at as Created', 'tblJob.CreatedBy as CreatedBy']);
            } else {

                $userID = User::get_userID();
                $CompanyID = User::get_companyID();
                $jobs = Job::
                join('tblJobStatus', 'tblJob.JobStatusID', '=', 'tblJobStatus.JobStatusID')
                    ->join('tblJobType', 'tblJob.JobTypeID', '=', 'tblJobType.JobTypeID')
                    ->where("tblJob.CompanyID", $CompanyID)
                    ->where("tblJob.JobLoggedUserID", $userID)
                    ->orderBy("tblJob.JobID", "desc")
                    ->get(['tblJob.Title', 'tblJobType.Title as Type', 'tblJobStatus.Title as Status', 'tblJob.created_at as Created', 'tblJob.CreatedBy as CreatedBy']);
            }

            $excel_data = json_decode(json_encode($jobs),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Jobs.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Jobs.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
           /* Excel::create('Jobs', function ($excel) use ($jobs) {
                $excel->sheet('Jobs', function ($sheet) use ($jobs) {
                    $sheet->fromArray($jobs);
                });
            })->download('xls');*/
        //}
    }

    public function download_rate_sheet_file($id){
        //if( User::checkPermission('Job') ) {
        $FilePath = JobFile::where(["JobID" => $id])->pluck("FilePath");
        $FilePath =  AmazonS3::preSignedUrl($FilePath);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }else{
            header('Location: '.$FilePath);
        }
        exit;
        //}

    }

    public function loadDashboardJobsDropDown(){
        /*$jobs = Job::getAllHeaderJobs();
        $totalNonVisitedJobs = Job::getAlertCounterNonVisitedJobs();
        $totalPendingJobs = Job::getTotalPendingJobs();*/

        $reset = Input::get('reset');
        $dropdownData = Job::getJobsDropDown($reset);

        return View::make('jobs.dashboard_top_jobs', compact('dropdownData'));
    }

    /*
     * Ajax : When New Job Counter Reset
     * */
    public function resetJobsAlert(){
        Job::resetShowInCounter();
        return;
    }
    /*
     * Ajax: When Job Read
     * */
    public function jobRead($id){
        if(intval($id) > 0 ){
            Job::jobRead($id);
        }
        return;
    }

    /*
     * Download Output File
     * */
    public function downloaOutputFile($id){
        //if( User::checkPermission('Job') && intval($id) > 0 ) {
        $OutputFilePath = Job::where("JobID", $id)->pluck("OutputFilePath");
        $JobTypeID = Job::where("JobID", $id)->pluck("JobTypeID");
        $JobType = JobType::where("JobTypeID",$JobTypeID)->pluck("Code");

        if($JobType == 'SCRP') {
            // when sippy customer rate push file download it will have full path stored in job table
            $FilePath = $OutputFilePath;
        } else {
            $FilePath =  AmazonS3::preSignedUrl($OutputFilePath);
        }
        if(file_exists($FilePath)){
            download_file($FilePath);
        }else{
            header('Location: '.$FilePath);
        }
        exit;
        //}

    }

    //active job termination added by abubakar
    public function activejob(){
        $JobStatus = JobStatus::getJobStatusPendingFailed();//@TODO: to show only Pending and Failed Status
        return View::make('activejob.activejob',compact('JobStatus'));
    }

    public function jobactive_ajax_datagrid(){
        $data = Input::all();

        $select = ['Title','PID',DB::raw("CONCAT(TIMESTAMPDIFF(HOUR,LastRunTime,NOW()),':',TIMESTAMPDIFF(MINUTE,LastRunTime,NOW())%60) AS RunningHour"),'LastRunTime','JobID'];
        $job = Job::select($select)->where(['JobStatusID'=>JobStatus::where(['Code'=>'I'])->pluck('JobStatusID')]);
        if(!User::is_admin()) {
            $job->where(['JobLoggedUserID' => User::get_userID()]);
        }
        return Datatables::of($job)->make();
    }

    // Not in use
    public function activeprocessdelete(){

        $data = Input::all();
        $JobID = $data['JobID'];
        $Job = Job::find($JobID);

        $PID = $data['PID'];
        $JobData = array();
        $JobData['PID'] = 0;
        $JobData['JobStatusID'] = $data['JobStatusID'];
        $JobData['JobStatusMessage'] = $Job->JobStatusMessage.' User message:'.$data['message'];

        if(getenv("APP_OS") == "Linux"){
            $command = 'kill -9 '.$PID;
        }else{
            $command = 'Taskkill /PID '.$PID.' /F';
        }
        $output = exec($command,$op);
        Log::info($command);
        Log::info($output);


        Job::where('JobID',$JobID)->update($JobData);


        if(isset($output) && $output == !''){
            return Response::json(array("status" => "success", "message" => ".$output."));
        }else{
            return Response::json(array("status" => "failed", "message" => "Cron Job Process is not terminated"));
        }
    }

    /**
     * Restart a Job
     * @param $id
     */
    public function restart($JobID){

        if(!empty($JobID)){

            DB::connection('sqlsrv')->select("CALL prc_UpdateFailedJobToPending($JobID)");
            return Response::json(array("status" => "success", "message" => "Job will restart soon."));

        }else{

            return Response::json(array("status" => "failed", "message" => "JobID not found."));
        }

    }

  /**
     * Cancel a Job
     * @param $id
     */
    public function cancel($JobID){

        if(!empty($JobID)){

            $userName = User::get_user_full_name();
            DB::connection('sqlsrv')->select("CALL prc_UpdatePendingJobToCanceled($JobID,'$userName')");
            return Response::json(array("status" => "success", "message" => "Job is cancelled."));

        } else {

            return Response::json(array("status" => "failed", "message" => "JobID not found."));

        }

    }

    /**
     * Terminate a job
     * @param $id
     */
    public function terminate($JobID){

        if(!empty($JobID)) {

            $data = Input::all();
            $Job = Job::find($JobID);

            $PID = $Job->PID;

            $UserName = User::get_user_full_name();
            $JobStatusID = $data['JobStatusID'];

            $JobStatusTitle = JobStatus::where("JobStatusID",$JobStatusID)->pluck("Title");
            $JobStatusMessage = PHP_EOL . $UserName . ' has changed Job Status to ' . $JobStatusTitle  . PHP_EOL .' User says:' . addslashes($data['message']);

            $status = false;
            if($PID > 0){

                $status = terminate_process($PID);
            }

            $is_updated =  \Illuminate\Support\Facades\DB::connection('sqlsrv')->select("CALL prc_UpdateInProgressJobStatusToFail($JobID,$JobStatusID,'$JobStatusMessage', '$UserName')");

            $is_updated = json_decode(json_encode($is_updated),true);
            $is_updated = array_shift($is_updated);

            if(isset($is_updated['result']) && $is_updated['result'] == 1 ){

                if($status && $PID > 0){

                    return Response::json(array("status" => "success", "message" => "Job Terminated Successfully!"));

                }else {

                    return Response::json(array("status" => "success", "message" => "Job Status Updated."));
                }

            } else {
                    return Response::json(array("status" => "success", "message" => "Process might be already Completed"));
            }

        } else {

            return Response::json(array("status" => "failed", "message" => "JobID not found."));
        }

    }
}