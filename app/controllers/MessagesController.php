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
		 $isAdmin 					= 	(User::is_admin() || User::is('RateManager'))?1:0;
		 if(!User::is_admin())
		 {
		 	if(!$Emaildata->UserID=$User::get_userID())	
			{
				Redirect::to('/emailmessages');	
			}
		 }
		 
	     Messages::where(['EmailID'=>$id])->update(["HasRead"=>1]);	 //update read status	
		 $attachments 				= 	unserialize($Emaildata->AttachmentPaths);		
		 $data['EmailCall'] 		= 	Messages::Received;
		 $data['iDisplayStart']  	= 	0;
		 $data['iDisplayLength'] 	=	Config::get('app.pageSize');
		 $array						=   $this->GetResult($data);
		 $resultdata   				=   $array['resultdata'];	
		 $resultpage  				=   $array['resultpage'];		
		 $TotalUnreads				=	$resultdata->data['TotalUnreads'][0]->totalcount;	
		 $user 						=   User::find($Emaildata->UserID);  		 
		 $to  						=	isset($Emaildata->EmailTo)?Messages::GetAccountTtitlesFromEmail($Emaildata->EmailTo):$user->FirstName.' '.$user->LastName; 
		 $fromUser 					=	User::where(["EmailAddress" => $Emaildata->Emailfrom])->first(); 
		 $from						=	!empty($Emaildata->EmailfromName)?$Emaildata->EmailfromName:$fromUser->FirstName.' '.$fromUser->LastName;
		 return View::make('emailmessages.detail', compact('Emaildata','attachments',"TotalUnreads","to",'from'));
	}
		
	public function Compose(){
		$data 						= 		Input::all();
		$array						=		$this->GetDefaultCounterData(); //get default data for email side bar
		$random_token				=	 	get_random_number();
		$response_api_extensions 	=   	Get_Api_file_extentsions();
		$response_extensions		=		json_encode($response_api_extensions['allowed_extensions']);
		$max_file_size				=		get_max_file_size();
		list($resultdata,$TotalUnreads,$iDisplayLength,$totalResults)   =  $array;		
		return View::make('emailmessages.compose', compact('data','TotalUnreads','iDisplayLength','totalResults','random_token','response_extensions','max_file_size'));
	}
	
	function SendMail(){

        $data = Input::all(); 
        $rules = array(
            'Subject'=>'required',
            'Message'=>'required'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $attachmentsinfo        =	$data['attachmentsinfo']; 
        if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
            $files_array = json_decode($attachmentsinfo,true);
        }

        if(!empty($files_array) && count($files_array)>0)
		{
            $FilesArray = array();
			
            foreach($files_array as $key=> $array_file_data)
			{
                $file_name  		= 	basename($array_file_data['filepath']); 
                $amazonPath 		= 	AmazonS3::generate_upload_path(AmazonS3::$dir['EMAIL_ATTACHMENT']);
                $destinationPath 	= 	getenv("UPLOAD_PATH") . '/' . $amazonPath;

                if (!file_exists($destinationPath))
				{
                    mkdir($destinationPath, 0777, true);
                }
                
				copy($array_file_data['filepath'], $destinationPath . $file_name);
				
                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath))
				{
                    return Response::json(array("status" => "failed", "message" => "Failed to upload file." ));
                }
				
                $FilesArray[] = array ("filename"=>$array_file_data['filename'],"filepath"=>$amazonPath . $file_name);
                unlink($array_file_data['filepath']);
            }
            $data['file']		=	json_encode($FilesArray);
		} 
		
		 $data['name']			=    Auth::user()->FirstName.' '.Auth::user()->LastName;
		
		 $data['address']		=    Auth::user()->EmailAddress; 
	   
		 $response 				= 	NeonAPI::request('email/sendemail',$data,true,false,true);		
		if($response->status=='failed'){
				return  json_response_api($response);
		}
		else
		{										
				//$response 		 = 	$response->data;
				//$response->type  = 	Task::Mail;			
				//$response->LogID = 	$response->AccountEmailLogID;
				return Response::json(array("status" => "success", "message" =>$response->data->message_sent));
		}
	}
	
	protected function GetDefaultCounterData(){		
		$data['EmailCall']			=	Messages::Received;
		$data['iDisplayStart']  	= 	 0;
		$data['iDisplayLength'] 	= 	 Config::get('app.pageSize');
		$array						= 	 $this->GetResult($data);
		$resultdata   				= 	 $array['resultdata'];
		$TotalUnreads				=	 $resultdata->data['TotalUnreads'][0]->totalcount;
		$iDisplayLength 			= 	 Config::get('app.pageSize');
		$totalResults 				=    $resultdata->data['TotalResults'][0]->totalcount;
		return array("0"=>$resultdata,"1"=>$TotalUnreads,"2"=>$iDisplayLength,"3"=>$totalResults);
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