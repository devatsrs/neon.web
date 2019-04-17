<?php

class EmailTemplateController extends \BaseController {

    public function ajax_datagrid($exporttype) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['is_reseller']=0;
        $data['is_IsGlobal'] = 0;
        $Count = Reseller::IsResellerByCompanyID($CompanyID);
        if($Count>0){
            $data['is_reseller']=1;
            $data['CompanyID'] = $CompanyID;
        }else{
            $data['CompanyID'] = 0;
            if(isset($data['ResellerOwner']) && $data['ResellerOwner'] > 0){
                $data['CompanyID'] = $data['ResellerOwner'];
            }elseif(isset($data['ResellerOwner']) && $data['ResellerOwner']=='-1'){
                $data['is_IsGlobal'] = 1;
            }
        }

        $Name = empty($data['search']) ? '' : $data['search'];
        $Type = 0;
        $userID = 0;
        $Status = 0;
        $StaticType = 0;
        if(isset($data['type'])&& $data['type']>0){
            $Type=$data['type'];
        }
        if($data['template_privacy']==1){
            $userID =User::get_userID();
        }
        if($data['Status']!='false'){
            $Status=1;
        }
        if(isset($data['system_templates']) && $data['system_templates']!='false'){
            $StaticType=1;
        }

        $columns = ["TemplateName","ResellerName","Subject","CreatedBy","updated_at","Status","TemplateID","StaticType"];
        $data['iDisplayStart'] +=1;
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getEmailTemplate(" . $data['CompanyID']  . ",'" . $Name . "',".$data['is_reseller']."," . $data['is_IsGlobal'] . "," . $data["templateLanguage"] . "," . $Type . "," . $userID . "," . $Status . "," . $StaticType . "," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($exporttype=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID) .'/Templates.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($exporttype=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID) .'/Templates.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query)->make();
    }
    /**
     * Display a listing of the resource.
     * GET /accounts
     *
     * @return Response
     */
    public function index() {
        $CompanyID = User::get_companyID();
        $privacy 		= 	EmailTemplate::$privacy;
        $type 			= 	EmailTemplate::$Type;
		$TemplateType 	=   json_encode(EmailTemplate::$TemplateType);
		$emailfrom	 	=	TicketGroups::GetGroupsFrom($CompanyID);
		$email_from		=	array_merge(array(""=>"Select"),$emailfrom);
        $reseller_owners = Reseller::getDropdownIDListAll();
        return View::make('emailtemplate.index',compact('privacy','type',"TemplateType","email_from","reseller_owners","CompanyID"));
    }


    /**
     * Store a newly created resource in storage.
     * POST /accounts
     *
     * @return Response
     */
    public function store() {

        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['CreatedBy'] = User::get_user_full_name();

        $data['IsGlobal'] =0;
        if(!empty($data['ResellerOwner'])){
            if($data['ResellerOwner']=='-1'){
                $data['IsGlobal'] =1;
            }else{
                $data['CompanyID'] = $data['ResellerOwner'];
            }
        }
        unset($data['ResellerOwner']);



        $rules = [
            "TemplateName" => "required|unique:tblEmailTemplate,TemplateName,NULL,TemplateID,CompanyID,".$data['CompanyID'].",IsGlobal,".$data['IsGlobal'],
            "Subject" => "required",
            "TemplateBody"=>"required",
            "LanguageID"=>"required"
        ];
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(isset($data['Email_template_privacy']) && $data['Email_template_privacy']>0){
            $data['userID'] = User::get_userID();
        }else
		{
			$data['userID'] = NULL;
		}
		$data['Status'] = isset($data['Status'])?1:0;
        unset($data['Email_template_privacy']);
         $data['EmailFrom'] =  isset($data['email_from'])?$data['email_from']:"";
         unset($data['email_from']);

        if(!empty($data['SystemType'])){
            if(EmailTemplate::where([ "LanguageID"=>$data['LanguageID'], "SystemType"=>$data['SystemType'], "CompanyID"=>$data['CompanyID'],"IsGlobal"=>$data['IsGlobal']])->count()){
                return Response::json(array("status" => "failed", "message" => "Template already exists."));
            }
            $emailTemplate = EmailTemplate::where(['SystemType'=>$data['SystemType'],'LanguageID'=>Translation::$default_lang_id])->first();
            if(!empty($emailTemplate)){
                $data['StatusDisabled'] = $emailTemplate->StatusDisabled;
                $data['TicketTemplate'] = $emailTemplate->TicketTemplate;
                $data['StaticType'] = $emailTemplate->StaticType;
                $data['Type'] = $emailTemplate->Type;
            }
        }

        if ($obj = EmailTemplate::create($data)) {
            Log::info(json_encode($data));
            return Response::json(array("status" => "success", "message" => "Template Successfully Created","newcreated"=>$obj));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Template."));
        }


        //return Redirect::route('accounts.index')->with('success_message', 'Accounts Successfully Created');
    }



    /**
     * Show the form for editing the specified resource.
     * GET /accounts/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        $instance = array('Privacy'=>'');
        $template = EmailTemplate::find($id);
        $instance['TemplateID'] = $template->TemplateID;
        $instance['TemplateName'] = $template->TemplateName;
        $instance['Subject'] = $template->Subject;
        $instance['TemplateBody'] = $template->TemplateBody;
        $instance['Type'] = $template->Type;
		$instance['StaticType'] = $template->StaticType;
		$instance['Status'] = $template->Status;
		$instance['email_from'] = $template->EmailFrom;
		$instance['StatusDisabled'] = $template->StatusDisabled;
		if($template->userID==User::get_userID()){
            $instance['Privacy'] = 1;
        } 
		$instance['TicketTemplate'] = $template->TicketTemplate;
		$instance['LanguageID'] = (!empty($template->LanguageID))?$template->LanguageID:Translation::$default_lang_id;
        $instance['SystemType'] = $template->SystemType;
        $PartnerID = '';
        $CompanyID = $template->CompanyID;
        $Count = Reseller::IsResellerByCompanyID($CompanyID);
        if($Count>0){
            $PartnerID = $CompanyID;
        }else{
            if($template->IsGlobal==1){
                $PartnerID = -1;
            }
        }
        $instance['ResellerOwner'] = $PartnerID;
        return $instance;
    }

    /**
     * Update the specified resource in storage.
     * PUT /accounts/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        $data = Input::all();   
        $crmteplate = EmailTemplate::findOrfail($id);
        $companyID = $crmteplate->CompanyID;
        $data['CompanyID'] = $companyID;
        $data['ModifiedBy'] = User::get_user_full_name();
       if($crmteplate->StaticType ==1 && $crmteplate->TicketTemplate ==0) {
		$rules = [
            "TemplateName" => "required|unique:tblEmailTemplate,TemplateName,$id,TemplateID,CompanyID,".$companyID,
            "Subject" => "required",
            "TemplateBody"=>"required",
			//"email_from"=>"required",
            "LanguageID"=>"required"
        ];
		}else{
	    $rules = [
            "TemplateName" => "required|unique:tblEmailTemplate,TemplateName,$id,TemplateID,CompanyID,".$companyID,
            "Subject" => "required",
            "TemplateBody"=>"required",
            "LanguageID"=>"required"
        ];
	   }
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
            exit;
        }

        if(isset($data['Email_template_privacy']) && $data['Email_template_privacy']>0){
            $data['userID'] = User::get_userID();
        }
		else
		{
			$data['userID'] = NULL;
		} 
    	 $data['EmailFrom'] =  isset($data['email_from'])?$data['email_from']:"";
		 unset($data['email_from']);
		 unset($data['Email_template_privacy']); 
		$data['Status'] = isset($data['Status'])?1:0;
        if ($crmteplate->update($data)) {
            return Response::json(array("status" => "success", "message" => "Template Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating template."));
        }
    }

    public function delete($id)
    {
        if( intval($id) > 0){
            if(!EmailTemplate::checkForeignKeyById($id)) {
                try {
                    $result = EmailTemplate::find($id)->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Template Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting template."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "template is in Use, You cant delete this template."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "template is in Use, You cant delete this template."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Template is in Use, You cant delete this template."));
        }
    }

    public function exports($type)
    {
        $companyID = User::get_companyID();
        $data = Input::all();
        $select = ["TemplateName","Subject","CreatedBy","updated_at"];
        $template = EmailTemplate::select($select)->where(["CompanyID" => $companyID])->get();
        $excel_data = json_decode(json_encode($template),true);
        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Templates.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($excel_data);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Templates.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($excel_data);
        }
    }
	
	function ChangeStatus($id){
        $data 		= Input::all();
		try
		{
			$status 	= $data['status'];
			$statusdb 	= 0;
			if($status=='true'){
				$statusdb = 1;
			}
			 
			EmailTemplate::find($id)->update(array("Status"=>$statusdb));
			return Response::json(array("status" => "success", "message" => "Template status successfully updated"));
		}catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => "template is in Use, You cant delete this template."));
        }
	}

    public function ajax_templateList($id){
        $data = EmailTemplate::GetUserDefinedTemplates(0, $id);
        return Response::json(array("status" => "success", "data" => $data));
    }
}