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
        //print_r($response);exit();
        $columns =[];
        $message = '';
        $boardsWithTask = [];
        if(isset($response['status_code'])) {
            if ($response['status_code'] == 200) {
                $columns = $response['data']['result']['columns'];
                $boardsWithTask = $response['data']['result']['boardsWithITask'];
            }else{
                $message=$response['message'];
            }
        }else{
            $message=$response->error;
        }
        return View::make('taskboards.board', compact('columns','boardsWithTask','message'))->render();
    }

    public function ajax_task_grid($id){
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        if(User::is('AccountManager')){
            $data['account_owners'] = User::get_userID();
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
                $attachementPaths = $response->data->result;
            }else{
                return json_response_api($response);
            }
        }else{
            return json_response_api($response);
        }
        return View::make('taskcomments.attachments', compact('attachementPaths'))->render();
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
        $Board = CRMBoard::getTaskBoard();
        $account_owners = User::getUserIDList(0);
        $priority = Task::$priority;

        $where['Status']=1;
        if(User::is('AccountManager')){
            $where['Owner'] = User::get_userID();
        }
        $leadOrAccount = Account::where($where)->select(['AccountName', 'AccountID'])->orderBy('AccountName')->lists('AccountName', 'AccountID');
        $tasktags = json_encode(Tags::getTagsArray(Tags::Task_tag));
        return View::make('taskboards.manage', compact('Board','priority','account_owners','leadOrAccount','tasktags'));
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

		if(!isset($response->status_code )){
			return  json_response_api($response);
		}
		
		if ($response->status_code == 200) {			
			$response = $response->data->result[0];
			$response->type = 1;			
		}
		else{
		 return  json_response_api($response);
		}
		
		$key = $data['scrol'];	
		
		if(isset($data['Task_type']) && $data['Task_type']>0)	
		{
			if($data['Task_type']==3) //note
			{
				$response_note 			= 	 NeonAPI::request('account/get_note',array('NoteID'=>$data['ParentID']),false,true);	
				$response_data 			= 	$response_note['data']['Note'][0];
				$response_data['type']  = 	3;
			}
			
			if($data['Task_type']==2) //email
			{
				$response_email 		= 	NeonAPI::request('account/get_email',array('EmailID'=>$data['ParentID']),false,true);	
				$response_data 			= 	$response_email['data']['Email'][0];
				$response_data['type']  = 	2;
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
                $lead = $response->data->result;
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

}