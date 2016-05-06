<?php

class OpportunityController extends \BaseController {

    var $model = 'Opportunity';
	/**
	 * Display a listing of the resource.
	 * GET /Deal board
	 *
	 * @return Response

	  */

    public function ajax_opportunity($id){
        $data = Input::all();
        if(User::is('AccountManager')){
            $data['account_owners'] = User::get_userID();
        }
        $response = NeonAPI::request('opportunity/'.$id.'/get_opportunities',$data,true,true);
        $columns =[];
        $message = '';
        $boradsWithOpportunities = [];
        if(isset($response['status_code'])) {
            if ($response['status_code'] == 200) {
                $columns = $response['data']['result']['columns'];
                $boradsWithOpportunities = $response['data']['result']['boradsWithOpportunities'];
            }else{
                $message=$response['message'];
            }
        }else{
            $message=$response->message;
        }
        return View::make('opportunityboards.board', compact('columns','boradsWithOpportunities','message'))->render();
    }

    public function ajax_getattachments($id){
        $response = NeonAPI::request('opportunity/'.$id.'/get_attachments',[],false);
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
        return View::make('crmcomments.attachments', compact('attachementPaths'))->render();
    }

    public function saveattachment($id){
        $data = Input::all();
        $opportunityattachment = Input::file('opportunityattachment');
        if(!empty($opportunityattachment)) {
            $data['file'] = NeonAPI::base64byte($opportunityattachment);
            $response = NeonAPI::request('opportunity/'.$id.'/save_attachment',$data,true,false,true);
            return json_response_api($response);
        }else{
            return Response::json(array("status" => "failed", "message" => "No attachment found."));
        }
    }

    public function deleteAttachment($opportunityID,$attachmentID){
        $response = NeonAPI::request('opportunity/'.$opportunityID.'/delete_attachment/'.$attachmentID,[],false);
        return json_response_api($response);
    }
	/**
	 * Show the form for creating a new resource.
	 * GET /dealboard/create
	 *
	 * @return Response
	 */
    public function create(){
        $data = Input::all();
        $response = NeonAPI::request('opportunity/add_opportunity',$data);
        return json_response_api($response);
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
            $response = NeonAPI::request('opportunity/'.$id.'/update_opportunity',$data);
            return json_response_api($response);
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Opportunity."));
        }
    }

    function updateColumnOrder($id){
        $data = Input::all();
        $response = NeonAPI::request('opportunity/'.$id.'/update_columnorder',$data);
        return json_response_api($response);
    }

    function updateTaggedUser($id){
        $data = Input::all();
        $response = NeonAPI::request('opportunity/'.$id.'/update_taggeduser',$data);
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

    //////////////////////
    function upload_file(){
        $data       =  Input::all();
        $data['file']    = array();
        $attachment    =  Input::file('commentattachment');
        //Backup code for getting extensions from api
        /*$response_extensions   =   NeonAPI::request('get_allowed_extensions',[],false);
        $response_extensions = json_response_api($response_extensions,true);
        if(!empty($response_extensions)){
            if(!isJson($response_extensions)){
                $response_extensions = implode(',',$response_extensions);
            }
        }*/
        $response_extensions     =  getenv("CRM_ALLOWED_FILE_UPLOAD_EXTENSIONS");

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