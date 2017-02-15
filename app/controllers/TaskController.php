<?php

class TaskController extends \BaseController {

    var $model = 'Opportunity';
	/**
	 * Display a listing of the resource.
	 * GET /Deal board
	 *
	 * @return Response

	  */

    public function ajax_task_board($id){
        $data = Input::all();
        if(User::is('AccountManager')){
            $data['AccountOwner'] = User::get_userID();
        }
        $data['fetchType'] = 'Board';
        $response = NeonAPI::request('task/'.$id.'/get_tasks',$data,true,true);

        $columns =[];
        $message = '';
        $columnsWithITask = [];
        if($response['status']=='success') {
            $columns = $response['data']['columns'];
            $columnsWithITask = $response['data']['columnsWithITask'];
        }else{
            $message = json_response_api($response,false,false);
        }

        return View::make('taskboards.board', compact('columns','columnsWithITask','message'))->render();
    }

    public function ajax_task_grid($id){
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        if(User::is('AccountManager')){
            $data['AccountOwner'] = User::get_userID();
        }
        $response = NeonAPI::request('task/'.$id.'/get_tasks',$data,true);
        return json_response_api($response,true,true,true);
    }

    public function ajax_getattachments($id){
        $attachementPaths ='';
        $message = '';
        $response = NeonAPI::request('task/'.$id.'/get_attachments',[],false);
        if($response->status!='failed') {
            $attachementPaths = json_response_api($response,true,false,false);
        }else{
            $message = json_response_api($response,false,false);
        }
        $type = 'task';
        return View::make('crmcomments.attachments', compact('attachementPaths','message','type','id'))->render();
    }

    public function saveattachment($id){
        $data = Input::all();
        $taskattachment = Input::file('taskattachment');
        if(!empty($taskattachment)) {
            $FilesArray = array();
            $allowed = Get_Api_file_extentsions(true);
			if(isset($allowed->headers)){ return	Redirect::to('/logout'); 	}
			if(!isset($allowed['allowed_extensions'])){
				return json_response_api($allowed,false,true);
			}
			$allowedextensions = $allowed['allowed_extensions'];  
            foreach ($taskattachment as $attachment) {
                $ext = $attachment->getClientOriginalExtension();
                if (!in_array(strtolower($ext), $allowedextensions)) {
					return  array("status"=>"failed","message"=>$ext." file type is not allowed. Allowed file types are ".implode(",",$allowedextensions));
                }
            }
            foreach($taskattachment as $file){
                $ext = $file->getClientOriginalExtension();
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TASK_ATTACHMENT']);
                $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file_name = "TaskAttachment_". GUID::generate() . '.' . $ext;
                $file->move($destinationPath, $file_name);

                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload file."));
                }
                $FilesArray[] = array("filename" => $file->getClientOriginalName(), "filepath" => $amazonPath . $file_name);
            }
            $data['file']		=	json_encode($FilesArray);
            $response = NeonAPI::request('task/'.$id.'/save_attachment',$data,true,false,true);
            return json_response_api($response);
        }else{
            return Response::json(array("status" => "failed", "message" => "No attachment found."));
        }
    }



    public function deleteAttachment($taskID,$attachmentID){
        $response = NeonAPI::request('task/'.$taskID.'/delete_attachment/'.$attachmentID,[],false);
        return json_response_api($response);
    }

    public function manage(){
        $message = '';
        $Board = CRMBoard::getTaskBoard();
        $account_owners = User::getUserIDList();
        $taskStatus = CRMBoardColumn::getTaskStatusList($Board[0]->BoardID);
		
        $where['Status']=1;
        if(User::is('AccountManager')){
            $where['Owner'] = User::get_userID();
        }
        $leadOrAccount = Account::where($where)->select(['AccountName', 'AccountID'])->orderBy('AccountName')->lists('AccountName', 'AccountID');

        if(!empty($leadOrAccount)){
            $leadOrAccount = array(""=> "Select a Company")+$leadOrAccount;
        }
        $tasktags = json_encode(Tags::getTagsArray(Tags::Task_tag));
        $response     =  NeonAPI::request('get_allowed_extensions',[],false);

        $response_extensions = [];

        if($response->status=='failed'){
            $message = json_response_api($response,false,false);
        }else{
            $response_extensions = json_response_api($response,true,true);
        }
        $token    = get_random_number();
        $max_file_size = get_max_file_size();
        return View::make('taskboards.manage', compact('Board','priority','account_owners','leadOrAccount','tasktags','taskStatus','response_extensions','token','max_file_size','message'));
    }
	/**
	 * Show the form for creating a new resource.
	 * GET /dealboard/create
	 *
	 * @return Response
	 */
    public function create(){
        $data 					= 	Input::all();
		$data['TaskBoardUrl']	=	URL::to('/task');
        $response 				= 	NeonAPI::request('task/add_task',$data);

        if($response->status!='failed'){
            if(isset($data['Task_view'])){
                return  json_response_api($response);
            }
            $response = $response->data;
            $response = $response[0];
            $response->type = Task::Tasks;

        }else{
            return json_response_api($response,false,true);
        }

        $key = isset($data['scrol'])?$data['scrol']:0;
        $current_user_title = User::get_user_full_name();

        if(isset($data['Task_type']) && $data['Task_type']>0) {
            if($data['Task_type']==Task::Note){//note
                $response_note    =   NeonAPI::request('account/get_note',array('NoteID'=>$data['ParentID']),false,true);
                $response_data    =  $response_note['data'];
                $response_data['type']  =  Task::Note;
            }

            if($data['Task_type']==Task::Mail){//email
                $response_email   =  NeonAPI::request('account/get_email',array('EmailID'=>$data['ParentID']),false,true);
                $response_data    =  $response_email['data'];
                $response_data['type']  =  Task::Mail;
            }

            return View::make('accounts.show_ajax_single_followup', compact('response','current_user_title','key','data','response_data'));
        } else {
            return View::make('accounts.show_ajax_single', compact('response','current_user_title','key'));
        }
    }
	/**
	 * Update the specified resource in storage.
	 * PUT /dealboard/{id}/update
	 *
	 * @param  int  $id
	 * @return Response
	 */
    //@clarification:will not update attribute against leads
    public function update($id)
    {
        if( $id > 0 ) {
            $data 					= 	Input::all();
			$required_data  		= 	0;
			$key					= 	0;
			$data['TaskBoardUrl']	=	URL::to('/task');	
			if(isset($data['KeyID']) && $data['KeyID']!=''){
				$key = $data['KeyID'];
			}
			unset($data['KeyID']);
            $response = NeonAPI::request('task/'.$id.'/update_task',$data); 
			if(isset($data['required_data']) && $data['required_data']!=''){
					$required_data = 1;
			}
			if($required_data==1 && $response->status=='success'){ 			
				$response = $response->data;
				$response = $response[0];
				$response->type = Task::Tasks;
				$current_user_title = User::get_user_full_name();				
				return View::make('accounts.show_ajax_single_update', compact('response','key','current_user_title'));  
			}else{
            return json_response_api($response);
			}
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Task."));
        }
    }
	
	function GetTask(){
		$response				=	array();
		$data 					= 	Input::all();
		$response_note    		=   NeonAPI::request('task/GetTask',array('TaskID'=>$data['TaskID']),false,true);
		
		if($response_note['status']=='failed'){
			return json_response_api($response_note,false,true);
		}else{ 
			return json_encode($response_note['data']);
		}
	}
	
	 /**
     * Delete a Note
     */
    public function delete_task($id) {        
		$data['TaskID']			=	$id;		 
		$response 				= 	NeonAPI::request('task/deletetask',$data);
		
		if($response->status=='failed'){
			return json_response_api($response,false,true);
		}else{ 
			return Response::json(array("status" => "success", "message" => "Task Successfully Deleted", "TaskID" => $id));
		}     
    }

    function updateColumnOrder($id){
        $data = Input::all();
        $response = NeonAPI::request('task/'.$id.'/update_columnorder',$data);
        return json_response_api($response);
    }

    public function getAttachment($taskID,$attachmentID){
        $response = NeonAPI::request('task/'.$taskID.'/getattachment/'.$attachmentID,[],true,true,true);

        if($response['status']=='failed'){
            return json_response_api($response,false);
        }else{
            $attachment = json_response_api($response,true,false,false);
            $FilePath =  AmazonS3::preSignedUrl($attachment['filepath']);
            if(file_exists($FilePath)){
                download_file($FilePath);
            }else{
                header('Location: '.$FilePath);
            }
            exit;
        }
    }
}