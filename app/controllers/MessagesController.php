<?php

class MessagesController extends \BaseController {

    public function ajax_datagrid() {

        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['AccountID'] = !empty($data['AccountID'])?$data['AccountID']:0;
        $data['MsgLoggedUserID'] = !empty($data['JobLoggedUserID'])?$data['JobLoggedUserID']:0;

        $columns = array('Title','created_at','CreatedBy','JobID','ShowInCounter','updated_at');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();
        if (User::is_admin()) {
            $isAdmin = 1;
        }else{
            $userID = User::get_userID();
            $data['MsgLoggedUserID'] = $userID;
            $isAdmin = 0;
        }

        $query = "call prc_GetAllEmailMessages (".$companyID.",".$data['AccountID'].",".$data['MsgLoggedUserID'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

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
        $creatdby = User::getUserIDList();
        $account = Account::getAccountIDList();
        return View::make('emailmessages.index', compact('creatdby','account'));        
    }

    public function show($id) {
        //if( User::checkPermission('Job') ) {
            $EmailMessage = DB::table('tblMessages')              
                ->select(
                    'tblMessages.Title', 'tblMessages.Description', 'tblMessages.AccountID', 'tblMessages.Options', 'tblMessages.MsgStatusMessage', 'tblMessages.OutputFilePath',  'tblMessages.created_at', 'tblMessages.CreatedBy', 'tblMessages.updated_at', 'tblMessages.ModifiedBy', 'tblMessages.MsgID','tblMessages.EmailSentStatus','tblMessages.EmailSentStatusMessage','tblMessages.EmailID',"tblMessages.MatchID","tblMessages.MatchType"
                )
                ->where("tblMessages.MsgID", $id)
                ->first();
				
				$Emaildata = AccountEmailLog::find($EmailMessage->EmailID);

            return View::make('emailmessages.show', compact('id', 'EmailMessage','Emaildata'));
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
                $file_path = getenv('UPLOAD_PATH') .'/Jobs.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Jobs.xls';
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

   
	public function loadDashboardMsgsDropDown(){ 
        $reset 		  =  Input::get('reset');
        $dropdownData =  Messages::getMsgDropDown($reset);
        return View::make('jobs.dashboard_top_msgs', compact('dropdownData'));
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

    public function jobactive_ajax_datagrid(){
        $data = Input::all();

        $select = ['Title','PID',DB::raw("CONCAT(TIMESTAMPDIFF(HOUR,LastRunTime,NOW()),':',TIMESTAMPDIFF(MINUTE,LastRunTime,NOW())%60) AS RunningHour"),'LastRunTime','JobID'];
        $job = Job::select($select)->where(['JobStatusID'=>JobStatus::where(['Code'=>'I'])->pluck('JobStatusID')]);
        if(!User::is_admin()) {
            $job->where(['JobLoggedUserID' => User::get_userID()]);
        }
        return Datatables::of($job)->make();
    }

}