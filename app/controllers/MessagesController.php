<?php

class MessagesController extends \BaseController {

    public function ajex_result() {
		
	    $data 						= 	Input::all();
		$data['MsgLoggedUserID'] 	= 	0;
		
		if(User::is('AccountManager')){
            $where['MsgLoggedUserID'] = User::get_userID();
        }
		if($data['clicktype']=='next'){
			$data['iDisplayStart']  	= 	($data['currentpage']+1)*$data['per_page'];
			$data['currentpage']++;
		}
		if($data['clicktype']=='back'){
			$data['iDisplayStart']  	= 	($data['currentpage']-1)*$data['per_page'];
			$data['currentpage']--;
		}
		
		$data['iDisplayLength'] 	= 	 $data['per_page'];
		$companyID					= 	 User::get_companyID();
		$boxtype					=	 isset($data['boxtype'])?$data['boxtype']:'inbox';
		$array						=  	 $this->GetResult($data);
		$resultdata   				=  	 $array['resultdata'];	
		$resultpage  				=  	 $array['resultpage'];			
		$result 					=    $resultpage['aaData'];
		$totalResults 				=    $resultdata->data['TotalResults'][0]->totalcount; 
		$iTotalDisplayRecords 		= 	 $resultpage['iTotalDisplayRecords'];
		$iDisplayLength 			= 	 $data['iDisplayLength'];
		//echo "<pre>";		print_r($resultpage);			exit;
		if(count($result)<1)
		{
			if(isset($data['SearchStr']) && $data['SearchStr']!='' && $data['currentpage']==0){
				
				return json_encode(array("result"=>"No Result found for ".$data['SearchStr']));
			}else{			
				return '';
			}
		}
		
      return View::make('emailmessages.ajaxresults', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','boxtype'));        
    
	}

    public function index() {        //inbox       
		$data['EmailCall'] 			= 	 Messages::Received;
		$data['iDisplayStart']  	= 	 0;
		$data['iDisplayLength'] 	= 	 Config::get('app.pageSize');
		$companyID 					= 	 User::get_companyID();
		$array						= 	 $this->GetResult($data);
		$resultdata   				= 	 $array['resultdata'];	
		$resultpage  				= 	 $array['resultpage'];		 
		$result 					= 	 $resultpage['aaData'];
		$totalResults 				= 	 $resultdata->data['TotalResults'][0]->totalcount; 
		$iTotalDisplayRecords 		= 	 $resultpage['iTotalDisplayRecords'];
		$iDisplayLength 			= 	 $data['iDisplayLength'];
		$TotalUnreads				=	 $resultdata->data['TotalUnreads'][0]->totalcount;
		$data['currentpage'] 		= 	 0;
		$data['BoxType']			=	 'inbox';
		//echo "<pre>";		print_r($resultpage);			exit;
        return View::make('emailmessages.index', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','TotalUnreads'));    }
		
	function SentBox(){
		$data['EmailCall'] 			= 	 Messages::Sent;
		$data['iDisplayStart']  	= 	 0;
		$data['iDisplayLength'] 	= 	 Config::get('app.pageSize');
		$companyID 					= 	 User::get_companyID();
		$array						= 	 $this->GetResult($data);
		$resultdata   				= 	 $array['resultdata'];	
		$resultpage  				= 	 $array['resultpage'];		 
		$result 					= 	 $resultpage['aaData'];
		$totalResults 				= 	 $resultdata->data['TotalResults'][0]->totalcount; 
		$iTotalDisplayRecords 		= 	 $resultpage['iTotalDisplayRecords'];
		$iDisplayLength 			= 	 $data['iDisplayLength'];
		$TotalUnreads				=	 $resultdata->data['TotalUnreads'][0]->totalcount;
		$data['currentpage'] 		= 	 0;
		$data['BoxType']			=	 'sentbox';
		//echo "<pre>";		print_r($resultpage);			exit;
        return View::make('emailmessages.sentbox', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','TotalUnreads'));	
	}
	
	function GetResult($data){
		
		$companyID 					= 	User::get_companyID();
		$isAdmin 					= 	(User::is_admin() || User::is('RateManager'))?1:0;		
        $data['MsgLoggedUserID'] 	= 	User::get_userID();
		$data['SearchStr']  		=	isset($data['SearchStr'])?$data['SearchStr']:'';
		
	    $query = "call prc_GetAllEmailMessages (".$companyID.",".$data['MsgLoggedUserID'].",".$isAdmin .",".$data['EmailCall'].",'".trim($data['SearchStr'])."',".$data['iDisplayStart']." ,".$data['iDisplayLength'].")";  
		
		$resultdata   	=  DataTableSql::of($query)->getProcResult(array('ResultCurrentPage','TotalResults','TotalUnreads'));	
		$resultpage  	=  DataTableSql::of($query)->make(false);
		return array("resultdata"=>$resultdata,"resultpage"=>$resultpage);
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
	
	function detail($id){
		 $Emaildata   				= 	AccountEmailLog::find($id);
	     Messages::where(['EmailID'=>$id])->update(["HasRead"=>1]);	 //update read status	
		 $attachments 				= 	unserialize($Emaildata->AttachmentPaths);		
		 $data['EmailCall'] 		= 	Messages::Received;
		 $data['iDisplayStart']  	= 	0;
		 $data['iDisplayLength'] 	=	Config::get('app.pageSize');
		 $array						=   $this->GetResult($data);
		 $resultdata   				=   $array['resultdata'];	
		 $resultpage  				=   $array['resultpage'];		
		 $TotalUnreads				=	$resultdata->data['TotalUnreads'][0]->totalcount;		 
		 
		 return View::make('emailmessages.detail', compact('Emaildata','attachments',"TotalUnreads"));
	}


   
	public function loadDashboardMsgsDropDown(){ 
        $reset 		  =  Input::get('reset');
        $dropdownData =  Messages::getMsgDropDown($reset);
        return View::make('emailmessages.dashboard_top_msgs', compact('dropdownData'));
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