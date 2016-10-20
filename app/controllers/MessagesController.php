<?php

class MessagesController extends \BaseController {

    public function ajex_result() {
		
	    $data 						= 	Input::all();
		$data['MsgLoggedUserID'] 	= 	0;
		
		if(User::is('AccountManager')){
            $where['MsgLoggedUserID'] = User::get_userID();
        }
		$data['currentpages']		=	$data['currentpage'];
		if($data['clicktype']=='next'){
			$data['iDisplayStart']  	= 	($data['currentpage']+1)*$data['per_page'];
			$data['currentpage']++;
		}
		elseif($data['clicktype']=='back'){
			$data['iDisplayStart']  	= 	($data['currentpage']-1)*$data['per_page'];
			$data['currentpage']--;
		}else
		{
			$data['iDisplayStart'] = 0;
		}
		
		
		$data['iDisplayLength'] 	= 	 $data['per_page'];
		$companyID					= 	 User::get_companyID();
		$boxtype					=	 isset($data['boxtype'])?$data['boxtype']:'inbox';
		$array						=  	 $this->GetResult($data);
		$resultdata   				=  	 $array['resultdata'];	
		$resultpage  				=  	 $array['resultpage'];			
		$result 					= 	 $resultdata->data['ResultCurrentPage'];
		$totalResults 				=    $resultdata->data['TotalResults'][0]->totalcount; 
		$iTotalDisplayRecords 		= 	 $resultpage['iTotalDisplayRecords'];
		$iDisplayLength 			= 	 $data['iDisplayLength'];
		$TotalDraft					=	$resultdata->data['TotalCountDraft'][0]->TotalCountDraft;
		//echo "<pre>";		print_r($resultpage);			exit;
		if(count($result)<1)
		{
			if(isset($data['SearchStr']) && $data['SearchStr']!='' && $data['currentpage']==0){
				
				return json_encode(array("result"=>"No Result found for ".$data['SearchStr']));
			}else{			
				return '';
			}
		} 
       return   View::make('emailmessages.ajaxresults', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','boxtype','TotalDraft'));     
	   
	   //return array('currentpage'=>$data['currentpage'],"Body"=>$body,"result"=>count($result));
    
	}

    public function index() {        //inbox       
		$data['EmailCall'] 			= 	 Messages::Received;
		$data['iDisplayStart']  	= 	 0;
		$data['iDisplayLength'] 	= 	 Config::get('app.pageSize');
		$companyID 					= 	 User::get_companyID();
		$array						= 	 $this->GetResult($data);
		$resultdata   				= 	 $array['resultdata'];	
		$resultpage  				= 	 $array['resultpage'];		 
		$result 					= 	 $resultdata->data['ResultCurrentPage'];
		$totalResults 				= 	 $resultdata->data['TotalResults'][0]->totalcount; 
		$iTotalDisplayRecords 		= 	 $resultpage['iTotalDisplayRecords'];
		$iDisplayLength 			= 	 $data['iDisplayLength'];
		$TotalUnreads				=	 $resultdata->data['totalcountInbox'][0]->totalcountInbox;
		$TotalDraft					=	 $resultdata->data['TotalCountDraft'][0]->TotalCountDraft;
		$data['currentpage'] 		= 	 0;
		$data['BoxType']			=	 'inbox';
		
		//echo "<pre>";		print_r($result);			exit;
        return View::make('emailmessages.index', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','TotalUnreads','TotalDraft'));  
		  }
		
	function SentBox(){
		$data['EmailCall'] 			= 	 Messages::Sent;
		$data['iDisplayStart']  	= 	 0;
		$data['iDisplayLength'] 	= 	 Config::get('app.pageSize');
		$companyID 					= 	 User::get_companyID();
		$array						= 	 $this->GetResult($data);
		$resultdata   				= 	 $array['resultdata'];	
		$resultpage  				= 	 $array['resultpage'];		 
		//$result 					= 	 $resultpage['aaData'];
		$result						=	 $resultdata->data['ResultCurrentPage'];;
		$totalResults 				= 	 $resultdata->data['TotalResults'][0]->totalcount; 
		$iTotalDisplayRecords 		= 	 $resultpage['iTotalDisplayRecords'];
		$iDisplayLength 			= 	 $data['iDisplayLength'];
		$TotalUnreads				=	 $resultdata->data['totalcountInbox'][0]->totalcountInbox;
		$TotalDraft					=	 $resultdata->data['TotalCountDraft'][0]->TotalCountDraft;
		$data['currentpage'] 		= 	 0;
		$data['BoxType']			=	 'sentbox';
		//echo "<pre>";		print_r($result);			exit;
        return View::make('emailmessages.sentbox', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','TotalUnreads','TotalDraft'));	
	}
	
	function GetResult($data){
		
		$companyID 					= 	User::get_companyID();
		$isAdmin 					= 	(User::is_admin() || User::is('RateManager'))?1:0;		
        $data['MsgLoggedUserID'] 	= 	User::get_userID();
		$data['SearchStr']  		=	isset($data['SearchStr'])?$data['SearchStr']:'';
		
	    $query = "call prc_GetAllEmailMessages (".$companyID.",".$data['MsgLoggedUserID'].",".$isAdmin .",".$data['EmailCall'].",'".trim($data['SearchStr'])."',".$data['iDisplayStart']." ,".$data['iDisplayLength'].")";     
		
		$resultdata   	=  DataTableSql::of($query)->getProcResult(array('ResultCurrentPage','TotalResults','totalcountInbox','TotalCountDraft'));	
		$resultpage  	=  DataTableSql::of($query)->make(false);
		//echo "<pre>";		print_r($resultdata);			exit;
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
		 	if($Emaildata->UserID!=User::get_userID())	
			{
				return Redirect::to('/emailmessages');	 
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
		 $TotalUnreads				=	$resultdata->data['totalcountInbox'][0]->totalcountInbox;	
		 $TotalDraft				=	$resultdata->data['TotalCountDraft'][0]->TotalCountDraft;
		 $user 						=   User::find($Emaildata->UserID);  	
		 if($user)	{
			$ToName					=	$user->FirstName.' '.$user->LastName; 
		 } else{
			 $ToName				=	'';
			}
		 $to  						=	isset($Emaildata->EmailTo)?Messages::GetAccountTtitlesFromEmail($Emaildata->EmailTo):$ToName; 
		 $fromUser 					=	User::where(["EmailAddress" => $Emaildata->Emailfrom])->first(); 
		 $from						=	!empty($Emaildata->EmailfromName)?$Emaildata->EmailfromName:isset($fromUser->FirstName)?$fromUser->FirstName.' '.$fromUser->LastName:$Emaildata->Emailfrom;
		 
		  $response_api_extensions 	=    Get_Api_file_extentsions();
		  $response_extensions		=	 json_encode($response_api_extensions['allowed_extensions']);
		  $random_token				=	 get_random_number();
		  $max_file_size			=	 get_max_file_size();		
		
		 return View::make('emailmessages.detail', compact('Emaildata','attachments',"TotalUnreads","to",'from','TotalDraft','response_extensions','random_token','max_file_size'));
	}
		
	public function Compose($id=0){
		$Emaildata					=		array();
		$data 						= 		Input::all();
		$array						=		$this->GetDefaultCounterData(); //get default data for email side bar
		$random_token				=	 	get_random_number();
		$response_api_extensions 	=   	Get_Api_file_extentsions();
		$response_extensions		=		json_encode($response_api_extensions['allowed_extensions']);
		$max_file_size				=		get_max_file_size();
		$AllEmails 					= 		json_encode(Messages::GetAllSystemEmails()); 
		
		if($id){
			 $Emaildata   				= 	AccountEmailLog::find($id); 
			 if($Emaildata->EmailCall!=Messages::Draft){
			 	return Redirect::to('/emailmessages');
			 }
			 $isAdmin 					= 	(User::is_admin() || User::is('RateManager'))?1:0;
			 if(!User::is_admin()) {
				if($Emaildata->UserID!=User::get_userID()){
					return Redirect::to('/emailmessages');	 
				}
			 }
			 //attachments
			 //echo $Emaildata->AttachmentPaths; exit;
			$data['uploadtext']  = 	 UploadFile::DownloadFileLocal($Emaildata->AttachmentPaths);
			//echo "<pre>"; echo $data['uploadtext']; exit;
			 
		}
		
		list($resultdata,$TotalUnreads,$iDisplayLength,$totalResults,$TotalDraft)   =  $array;		
						
		return View::make('emailmessages.compose', compact('data','TotalUnreads','iDisplayLength','totalResults','random_token','response_extensions','max_file_size','AllEmails','TotalDraft','Emaildata'));
	}
	
	function SendMail(){

        $data = Input::all(); 
       /* $rules = array(
            'Subject'=>'required',
            'Message'=>'required'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }*/
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
                @unlink($array_file_data['filepath']);
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
	
	public function Draft(){		
		$data['EmailCall'] 			= 	 Messages::Draft;
		$data['iDisplayStart']  	= 	 0;
		$data['iDisplayLength'] 	= 	 Config::get('app.pageSize');
		$companyID 					= 	 User::get_companyID();
		$array						= 	 $this->GetResult($data);  
		$resultdata   				= 	 $array['resultdata'];	
		$resultpage  				= 	 $array['resultpage'];		 
		//$result 					= 	 $resultpage['aaData'];
		$result						=	 $resultdata->data['ResultCurrentPage'];;
		$totalResults 				= 	 $resultdata->data['TotalResults'][0]->totalcount; 
		$iTotalDisplayRecords 		= 	 $resultpage['iTotalDisplayRecords'];
		$iDisplayLength 			= 	 $data['iDisplayLength'];
		$TotalUnreads				=	 $resultdata->data['totalcountInbox'][0]->totalcountInbox;
		$TotalDraft					=	 $resultdata->data['TotalCountDraft'][0]->TotalCountDraft;
		$data['currentpage'] 		= 	 0;
		$data['BoxType']			=	 'sentbox';
		//echo "<pre>";		print_r($result);			exit;
        return View::make('emailmessages.draft', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','TotalUnreads','TotalDraft'));	
	
	}
	
	
	protected function GetDefaultCounterData(){		
		$data['EmailCall']			=	Messages::Received;
		$data['iDisplayStart']  	= 	 0;
		$data['iDisplayLength'] 	= 	 Config::get('app.pageSize');
		$array						= 	 $this->GetResult($data);
		$resultdata   				= 	 $array['resultdata'];
		$TotalUnreads				=	 $resultdata->data['totalcountInbox'][0]->totalcountInbox;
		$iDisplayLength 			= 	 Config::get('app.pageSize');
		$totalResults 				=    $resultdata->data['TotalResults'][0]->totalcount;
		$TotalDraft					=	 $resultdata->data['TotalCountDraft'][0]->TotalCountDraft;
		return array("0"=>$resultdata,"1"=>$TotalUnreads,"2"=>$iDisplayLength,"3"=>$totalResults,4=>$TotalDraft);
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
	
	function Ajax_Action(){
		 $data 	=	Input::all(); 
		 
		 $action_type  = $data['action_type'];
		 $action_value = $data['action_value'];
		 
		 if(count($data['allVals'])>0){
			 try{
				 if($action_type=='HasRead'){
						foreach($data['allVals'] as $EmailIDs ){
							$SaveData = array("HasRead"=>$data['action_value'],"ModifiedBy"=> User::get_user_full_name());
							Messages::where(array('EmailID'=>$EmailIDs))->update($SaveData);		
						}
						return Response::json(array("status" => "success", "message" =>"Successfully updated."));
				 }
				 if($action_type=='Delete' && $data['action_value']==1){
					 foreach($data['allVals'] as $EmailIDs ){							
							AccountEmailLog::find($EmailIDs)->delete();
						}
						return Response::json(array("status" => "success", "message" =>"Successfully Deleted."));
				 }
			 }catch (Exception $ex){
				 return Response::json(array("status" => "failed", "message" =>$ex->getMessage()));
       		 }
			 
		 }
		 
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