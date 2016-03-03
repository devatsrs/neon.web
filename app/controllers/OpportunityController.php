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
        $companyID = User::get_companyID();
        $data = Input::all();
        $data['account_owners'] = empty($data['account_owners'])?0:$data['account_owners'];
        $data['AccountID'] = empty($data['AccountID'])?0:$data['AccountID'];
        $query = "call prc_GetOpportunities (".$companyID.", ".$id.",'".$data['opportunityName']."',".$data['account_owners'].", ".$data['AccountID'].")";
        $result = DB::select($query);
        $boradsWithOpportunities = [];
        foreach($result as $row){
            $columns[$row->OpportunityBoardColumnID] = $row->OpportunityBoardColumnName;
            if(!empty($row->OpportunityName)) {
                $boradsWithOpportunities[$row->OpportunityBoardColumnID][] = $row;
            }else{
                $boradsWithOpportunities[$row->OpportunityBoardColumnID][] = '';
            }
        }
        return View::make('opportunityboards.board', compact('columns','boradsWithOpportunities'))->render();
    }

    public function ajax_getattachments($id){
        $attachementPaths = Opportunity::where(['OpportunityID'=>$id])->pluck('AttachmentPaths');
        if(!empty($attachementPaths)){
            $attachementPaths = json_decode($attachementPaths);
        }
        return View::make('opportunitycomments.attachments', compact('attachementPaths'))->render();
    }

    public function saveattachment($id){
        $data = Input::all();
        $AttachmentPaths = Opportunity::find($id)->AttachmentPaths;
        $opportunityattachments = [];
        $opportunityattachment = Input::file('opportunityattachment');
        $allowed = getenv("CRM_ALLOWED_FILE_UPLOAD_EXTENSIONS");
        $allowedextensions = explode(',',$allowed);
        $allowedextensions = array_change_key_case($allowedextensions);
        foreach ($opportunityattachment as $attachment) {
            $ext = $attachment->getClientOriginalExtension();
            if (!in_array(strtolower($ext), $allowedextensions)) {
                return Response::json(array("status" => "failed", "message" => $ext." file type is not allowed. Allowed file types are ".$allowed));
            }
        }
        foreach ($opportunityattachment as $attachment) {
            $ext = $attachment->getClientOriginalExtension();
            $originalfilename = $attachment->getClientOriginalName();
            $file_name = "OpportunityAttachment_" . GUID::generate() . '.' . $ext;
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['OPPORTUNITY_ATTACHMENT']);
            $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
            $attachment->move($destinationPath, $file_name);
            if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                return Response::json(array("status" => "failed", "message" => "Failed to upload."));
            }
            $fullPath = $amazonPath . $file_name;
            $opportunityattachments[] = ['filename' => $originalfilename, 'filepath' => $fullPath];
        }

        if(count($opportunityattachments)>0){
            $AttachmentPaths = json_decode($AttachmentPaths,true);
            if(count($AttachmentPaths)>0) {
                $opportunityattachments = array_merge($AttachmentPaths , $opportunityattachments);
            }
            $opportunity_data['AttachmentPaths'] = json_encode($opportunityattachments);
            $result = Opportunity::where(['OpportunityID'=>$id])->update($opportunity_data);
            if($result){
                return Response::json(array("status" => "success", "message" =>'Attachment saved successfully'));
            }else{
                return Response::json(array("status" => "failed", "message" => "Problem saving attachment."));
            }
        } else{
            return Response::json(array("status" => "failed", "message" => "No attachment found."));
        }
    }

    public function deleteAttachment($opportunityID,$attachmentID){
        $attachmentPaths = Opportunity::find($opportunityID)->AttachmentPaths;
        if(!empty($attachmentPaths)){
            $attachmentPaths = json_decode($attachmentPaths,true);
            unset($attachmentPaths[$attachmentID]);
            $data = ['AttachmentPaths'=>json_encode($attachmentPaths)];
            $result = Opportunity::where(['opportunityID'=>$opportunityID])->update($data);
            if($result){
                return Response::json(array("status" => "success", "message" =>'Attachment deleted successfully'));
            }else{
                return Response::json(array("status" => "failed", "message" =>'Problem deleting attachment'));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "No attachment found."));
        }
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
        $message = '';
        $data ["CompanyID"] = $companyID;
        $rules = array(
            'CompanyID' => 'required',
            'OpportunityName' => 'required',
            'Company'=>'required',
            'Email'=>'required',
            'Phone'=>'required',
            'OpportunityBoardID'=>'required',
        );
        if($data['leadcheck']=='No') {
            $rules['Company'] = 'required|unique:tblAccount,AccountName,NULL,CompanyID,CompanyID,' . $companyID . '';
        }
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($data['leadcheck']=='No'){
            $AccountType = $data['leadOrAccount']=='Lead'?0:1;
            $tobeinsert = ['CompanyID'=>$companyID,
                            'Owner'=>$data['UserID'],
                            'AccountName'=>$data['Company'],
                            'Email'=>$data['Email'],
                            'Phone'=>$data['Phone'],
                            'AccountType'=>$AccountType,
                            'Status' => 1,
                            'created_by'=>User::get_user_full_name(),
                            'created_at'=>DB::raw('Now()')
                            ];
            if($AccountType==0){
                $AccountID = Lead::insertGetId($tobeinsert);
                $contact = ['CompanyId'=>$companyID,
                            'AccountID'=>$AccountID,
                            'FirstName'=>$data['ContactName']];
                Contact::create($contact);
                $message = 'and lead is created successfully.';
            }else{
                $AccountID = Account::insertGetId($tobeinsert);
                $contact = ['CompanyId'=>$companyID,
                    'AccountID'=>$AccountID,
                    'FirstName'=>$data['ContactName']];
                Contact::create($contact);
                $message = 'and Account is created successfully.';
            }
            $data['AccountID'] = $AccountID;
        }
        //Add new tags to db against opportunity
        Tags::insertNewTags(['tags'=>$data['Tags'],'TagType'=>Tags::Opportunity_tag]);
        // place new opp. in first column of board
        $data["OpportunityBoardColumnID"] = OpportunityBoardColumn::where(['OpportunityBoardID'=>$data['OpportunityBoardID'],'Order'=>0])->pluck('OpportunityBoardColumnID');
        $count = Opportunity::where(['CompanyID'=>$companyID,'OpportunityBoardID'=>$data['OpportunityBoardID'],'OpportunityBoardColumnID'=>$data["OpportunityBoardColumnID"]])->count();
        $data['Order'] = $count;
        $data["CreatedBy"] = User::get_user_full_name();

        unset($data['Company']);
        unset($data['PhoneNumber']);
        unset($data['Email']);

        unset($data['OppertunityID']);
        unset($data['leadcheck']);
        unset($data['leadOrAccount']);
        if (Opportunity::create($data)) {
            return Response::json(array("status" => "success", "message" => "Opportunity Successfully Created ".$message));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Opportunity."));
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

            if($data['leadcheck']=='Yes'){
                unset($data['Company']);
                unset($data['PhoneNumber']);
                unset($data['Email']);
            }
            Tags::insertNewTags(['tags'=>$data['Tags'],'TagType'=>Tags::Opportunity_tag]);
            unset($data['leadcheck']);
            unset($data['OpportunityID']);
            unset($data['leadOrAccount']);
            if (Opportunity::where(['OpportunityID'=>$id])->update($data)) {
                return Response::json(array("status" => "success", "message" => "Opportunity Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Opportunity."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Opportunity."));
        }
    }

    function updateColumnOrder($id){
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
    //@TODO: convert into procedure
    public function getLead($id){
        return Lead::where(['tblAccount.AccountID'=>$id])->select(['AccountName as Company','tblAccount.Phone','tblAccount.Email','tblAccount.AccountType',DB::raw("concat(tblContact.FirstName,' ', tblContact.LastName) as ContactName")])
            ->leftjoin('tblContact', 'tblContact.Owner', '=', 'tblAccount.AccountID')->get();
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