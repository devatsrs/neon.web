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
            $data['account_owners'] = User::get_userID();
        }
        $data['fetchType'] = 'Board';
        $response = NeonAPI::request('task/'.$id.'/get_tasks',$data,true,true);
        $columns =[];
        $message = '';
        $boardsWithTask = [];
        if(isset($response['status_code'])) {
            if ($response['status_code'] == 200) {
                $columns = $response['data']['columns'];
                $boardsWithTask = $response['data']['boardsWithITask'];
            }else{
                $message=$response['message'];
            }
        }else{
            $message=$response['error'];
        }
        return View::make('taskboards.board', compact('columns','boardsWithTask','message'))->render();
    }

    public function ajax_task_grid($id){
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        if(User::is('AccountManager')){
            $data['AccountOwners'] = User::get_userID();
        }
        $data['fetchType'] = 'Grid';
        $response = NeonAPI::request('task/'.$id.'/get_tasks',$data);
        return json_response_api($response);
    }

    public function ajax_getattachments($id){
        $response = NeonAPI::request('task/'.$id.'/get_attachments',[],false);
        $attachementPaths ='';
        if(isset($response->status_code)) {
            if ($response->status_code == 200) {
                $attachementPaths = $response->data;
            }else{
                return json_response_api($response);
            }
        }else{
            return json_response_api($response);
        }
        return View::make('crmcomments.attachments', compact('attachementPaths'))->render();
    }

    public function saveattachment($id){
        $data = Input::all();
        $taskattachment = Input::file('taskattachment');
        if(!empty($taskattachment)) {
            $data['file'] = NeonAPI::base64byte($taskattachment);
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
        $response_extensions     =  NeonAPI::request('get_allowed_extensions',[],false);
        $response_extensions   =   json_response_api($response_extensions,true,false);

        if(!empty($response_extensions)){
            if(!isJson($response_extensions)){
                $message = $response_extensions['errors'];
            }
        }
        $token    = get_random_number();
        $max_file_env    = getenv('MAX_UPLOAD_FILE_SIZE');
        $max_file_size    = !empty($max_file_env)?getenv('MAX_UPLOAD_FILE_SIZE'):ini_get('post_max_size');
        return View::make('taskboards.manage', compact('Board','priority','account_owners','leadOrAccount','tasktags','taskStatus','response_extensions','token','max_file_size','message'));
    }
	/**
	 * Show the form for creating a new resource.
	 * GET /dealboard/create
	 *
	 * @return Response
	 */
    public function create(){
        $data = Input::all();
        $response = NeonAPI::request('task/add_task',$data);		
		
		
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
		
		if(isset($data['Task_type']) && $data['Task_type']>0)	
		{
			if($data['Task_type']==Task::Note) //note
			{
				$response_note 			= 	 NeonAPI::request('account/get_note',array('NoteID'=>$data['ParentID']),false,true);	
				$response_data 			= 	$response_note['data'];
				$response_data['type']  = 	Task::Note;
			}
			
			if($data['Task_type']==Task::Mail) //email
			{
				$response_email 		= 	NeonAPI::request('account/get_email',array('EmailID'=>$data['ParentID']),false,true);	
				$response_data 			= 	$response_email['data'];
				$response_data['type']  = 	Task::Mail;
			}
			
			$current_user_title = Auth::user()->FirstName.' '.Auth::user()->LastName;
			return View::make('accounts.show_ajax_single_followup', compact('response','current_user_title','key','data','response_data')); 
			exit; 			
		}
		else
		{
			$current_user_title = Auth::user()->FirstName.' '.Auth::user()->LastName;
			return View::make('accounts.show_ajax_single', compact('response','current_user_title','key'));  
		}
        //return json_response_api($response);
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
            $data = Input::all();
            $response = NeonAPI::request('task/'.$id.'/update_task',$data);
            return json_response_api($response);
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Task."));
        }
    }

    function updateColumnOrder($id){
        $data = Input::all();
        $response = NeonAPI::request('task/'.$id.'/update_columnorder',$data);
        return json_response_api($response);
    }

    function updateTaggedUser($id){
        $data = Input::all();
        $response = NeonAPI::request('task/'.$id.'/update_taggeduser',$data);
        return json_response_api($response);
    }

    public function getLead($id){
        $response = NeonAPI::request('account/'.$id.'/get_account',[],false);
        $return=[];
        if(isset($response->status_code)) {
            if ($response->status_code == 200) {
                $lead = $response->data;
                $return['Company'] = $lead->AccountName;
                $return['Phone'] = $lead->Phone;
                $return['Email'] = $lead->Email;
                $return['Title'] = $lead->Title;
                $return['FirstName'] = $lead->FirstName;
                $return['LastName'] = $lead->LastName;
                return $return;
            }else{
                return json_response_api($response);
            }
        }else{
            return json_response_api($response);
        }
    }

    public function getDropdownLeadAccount($accountLeadCheck){
        $data = Input::all();
        $filter = [];
        if(!empty($data['UserID'])){
            $filter['Owner'] = $data['UserID'];
        }
        if($accountLeadCheck==1) {
            return json_encode(['result'=>Lead::getLeadList($filter)]);
        }else {
            return json_encode(['result'=>Account::getAccountList($filter)]);
        }
    }

    //////////////////////
    function upload_file(){
        $data       =  Input::all();
        $data['file']    = array();
        $attachment    =  Input::file('commentattachment');
        //$response_extensions   =   NeonAPI::request('get_allowed_extensions',[],false);
        $response_extensions     =  explode(',',getenv("CRM_ALLOWED_FILE_UPLOAD_EXTENSIONS"));

        if(!empty($attachment)){
            $data['file'] = NeonAPI::base64byte($attachment);
        }
        try {
            $return_str = check_upload_file($data['file'], 'email_attachments', $response_extensions, $data);
            return $return_str;
        }catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }

    }

    function delete_upload_file(){
        $data    =  Input::all();
        delete_file('email_attachments',$data);
    }
}