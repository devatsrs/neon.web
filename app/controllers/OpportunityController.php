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
        $data['fetchType'] = 'Board';
        $response = NeonAPI::request('opportunity/'.$id.'/get_opportunities',$data,true,true);
        $message = '';
        $columns = [];
        $columnsWithOpportunities = [];
		$Currency	='';
        $WorthTotal = 0;
        if($response['status']!='failed') {
            $columns = $response['data']['columns'];
            $columnsWithOpportunities = $response['data']['columnsWithOpportunities'];
            $WorthTotal = $response['data']['WorthTotal'];
			$Currency = $response['data']['Currency'];
        }else{
            $message = json_response_api($response,false,false);
        }
        return View::make('opportunityboards.board', compact('columns','columnsWithOpportunities','message','WorthTotal','Currency'))->render();
    }

    public function ajax_grid($id){
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        if(User::is('AccountManager')){
            $data['AccountOwner'] = User::get_userID();
        }
        $response = NeonAPI::request('opportunity/'.$id.'/get_opportunities',$data,true);
        return json_response_api($response,true,true,true);
    }

    public function ajax_getattachments($id){
        $message = '';
        $response = NeonAPI::request('opportunity/'.$id.'/get_attachments',[],false);
        if($response->status!='failed') {
            $attachementPaths = json_response_api($response,true,false,false);
        }else{
            $message = json_response_api($response,false,false);
        }
        $type = 'opportunity';
        return View::make('crmcomments.attachments', compact('attachementPaths','message','type','id'))->render();
    }

    public function saveattachment($id){
        $data = Input::all();
        $opportunityattachment = Input::file('opportunityattachment');
        if(!empty($opportunityattachment)) {
            $FilesArray = array();
            $allowed = Get_Api_file_extentsions(true);
			if(isset($allowed->headers)){ return	Redirect::to('/logout'); 	}
			if(!isset($allowed['allowed_extensions'])){
				return json_response_api($allowed,false,true);
			}
			$allowedextensions = $allowed['allowed_extensions'];  
            foreach ($opportunityattachment as $attachment) {
                $ext = $attachment->getClientOriginalExtension();
                if (!in_array(strtolower($ext), $allowedextensions)) { 
					return  array("status"=>"failed","message"=>$ext." file type is not allowed. Allowed file types are ".implode(",",$allowedextensions));
                }
            }
            foreach($opportunityattachment as $file){
                $ext = $file->getClientOriginalExtension();
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['OPPORTUNITY_ATTACHMENT']);
                $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file_name = "OpportunityAttachment_". GUID::generate() . '.' . $ext;
                $file->move($destinationPath, $file_name);

                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload file."));
                }
                $FilesArray[] = array("filename" => $file->getClientOriginalName(), "filepath" => $amazonPath . $file_name);
            }
            $data['file']		=	json_encode($FilesArray);
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
			$data['TaskBoardUrl']	=	$_SERVER['HTTP_REFERER'];	
            $response = NeonAPI::request('opportunity/'.$id.'/update_opportunity',$data);
            return json_response_api($response,false,true);
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Opportunity."));
        }
    }

    function updateColumnOrder($id){
        $data = Input::all();
        $response = NeonAPI::request('opportunity/'.$id.'/update_columnorder',$data);
        return json_response_api($response);
    }

    public function getLeadorAccount($id){
        $response = NeonAPI::request('account/'.$id.'/get_account',[],false);
        $return=[];
        if($response->status=='failed'){
            return json_response_api($response,false);
        }else{
            $lead = json_response_api($response,true,false,false);
            $return['Company'] = $lead->AccountName;
            $return['Phone'] = $lead->Phone;
            $return['Email'] = $lead->Email;
            $return['Title'] = $lead->Title;
            $return['FirstName'] = $lead->FirstName;
            $return['LastName'] = $lead->LastName;
            return $return;
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
    function uploadFile(){
        $data       =  Input::all();
        $attachment    =  Input::file('commentattachment');
        if(!empty($attachment)) {
            try {
                $data['file'] = $attachment;
                $returnArray = UploadFile::UploadFileLocal($data);
                return Response::json(array("status" => "success", "message" => '','data'=>$returnArray));
            } catch (Exception $ex) {
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }
        }

    }

    function deleteUploadFile(){
        $data    =  Input::all();
        try {
            UploadFile::DeleteUploadFileLocal($data);
            return Response::json(array("status" => "success", "message" => 'Attachments delete successfully'));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function getAttachment($opportunityID,$attachmentID){
        $response = NeonAPI::request('opportunity/'.$opportunityID.'/getattachment/'.$attachmentID,[],true,true,true);

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