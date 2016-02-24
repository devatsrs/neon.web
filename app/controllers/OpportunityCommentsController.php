<?php

class OpportunityCommentsController extends \BaseController {

    var $model = 'OpportunityComments';

    /** Return opportunity comment and its attachments.
     * @param $id
     * @return mixed
     */
    public function ajax_opportunitycomments($id){
        $companyID = User::get_companyID();
        $data = Input::all();
        $select = ['CommentText','AttachmentPaths','created_at','CreatedBy'];
        $result = OpportunityComments::select($select)->where(['OpportunityID'=>$id])->orderby('created_at','desc')->get();
        $opportunityComments=[];
        $commentcount = 0;
        if(!empty($result)) {
            foreach ($result as $comment) {
                $attachments = '';
                $attachmentPaths = json_decode($comment->AttachmentPaths);
                if (!empty($attachmentPaths)) {
                    foreach ($attachmentPaths as $item) {
                        $path = validfilepath($item->filepath);
                        $attachments[] = ['filename'=>$item->filename,'filepath'=>$path];
                    }
                }
                $opportunityComments[] = ['CommentText' => $comment['CommentText'],
                                            'AttachmentPaths' => $attachments,
                                            'created_at' => \Carbon\Carbon::createFromTimeStamp(strtotime($comment['created_at']))->diffForHumans(),
                ];
                $commentcount++;
            }
        }
        return View::make('opportunitycomments.comments', compact('opportunityComments','commentcount'))->render();
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /dealboard/create
	 *
	 * @return Response
	 */
    public function create(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data ["CompanyID"] = $companyID;
        $rules = array(
            'OpportunityID' => 'required',
            'CommentText' => 'required'
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $commentattachments = [];
        $comment_data=[];
        if (Input::hasFile('commentattachment')) {
            $commentattachment = Input::file('commentattachment');
            $allowed = getenv("CRM_ALLOWED_FILE_UPLOAD_EXTENSIONS");
            $allowedextensions = explode(',',$allowed);
            $allowedextensions = array_change_key_case($allowedextensions);
            foreach ($commentattachment as $attachment) {
                $ext = $attachment->getClientOriginalExtension();
                if (!in_array(strtolower($ext), $allowedextensions)) {
                    return Response::json(array("status" => "failed", "message" => $ext." file type is not allowed. Allowed file types are ".$allowed));
                }
            }
            foreach ($commentattachment as $attachment) {
                $ext = $attachment->getClientOriginalExtension();
                $originalfilename = $attachment->getClientOriginalName();
                $file_name = "CommentAttachment_" . GUID::generate() . '.' . $ext;
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['OPPORTUNITY_ATTACHMENT']);
                $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
                $attachment->move($destinationPath, $file_name);
                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name;
                $commentattachments[] = ['filename' => $originalfilename, 'filepath' => $fullPath];
            }
        }
        if(!empty($commentattachments)){
            $comment_data['AttachmentPaths'] = json_encode($commentattachments);
        }
        $comment_data["CommentText"] = $data["CommentText"];
        $comment_data["OpportunityID"] = $data["OpportunityID"];
        $comment_data["CreatedBy"] = User::get_user_full_name();
        if (OpportunityComments::create($comment_data)) {
            return Response::json(array("status" => "success", "message" => "Comment Add successfully"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Saving Comment"));
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
            $data = Input::all();

            $Opportunity = Opportunity::findOrFail($id);

            $companyID = User::get_companyID();
            $data["CompanyID"] = $companyID;
            $rules = array(
                'CompanyID' => 'required',
                'OpportunityName' => 'required',
                'Company'=>'required',
                'Email'=>'required',
                'Phone'=>'required',
                'OpportunityBoardID'=>'required'
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
/*
            if($data['leadcheck']=='Yes'){
                unset($data['Company']);
                unset($data['PhoneNumber']);
                unset($data['Email']);
            }*/
            unset($data['leadcheck']);
            unset($data['OpportunityID']);
            if (Opportunity::where(['OpportunityID'=>$id])->update($data)) {
                return Response::json(array("status" => "success", "message" => "Opportunity Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Opportunity."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Opportunity."));
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    function ajax_updateColumnOrder($id){
        $data = Input::all();
        try {
            $cardorder = explode(',', $data['cardorder']);
            foreach ($cardorder as $index => $key) {
                Opportunity::where(['OpportunityID' => $key])->update(['Order' => $index,'OpportunityBoardColumnID'=>$data['OpportunityBoardColumnID']]);
            }
            return Response::json(array("status" => "success", "message" => "Opportunity Updated"));
        }
        catch(Exception $ex){
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function getLead($id){
        //@todo: add select
        return Lead::where(['AccountID'=>$id])->select(['AccountName','Phone','Email'])->get();
    }

}