<?php

class LeadsController extends \BaseController {

    var $countries ;
    public function __construct()
    {
        $this->countries = Country::getCountryDropdownList();

    }

    public function ajax_datagrid(){

       $companyID = User::get_companyID();
       $userID = User::get_userID();
        $data = Input::all();
        $select = ["tblAccount.AccountName" ,DB::raw("concat(tblUser.FirstName,' ',tblUser.LastName) as Ownername"),"tblAccount.Phone","tblAccount.Email","tblAccount.AccountID","IsCustomer","IsVendor",'Address1','Address2','Address3','City','Country','Picture'];
        $leads = Account::leftjoin('tblUser', 'tblAccount.Owner', '=', 'tblUser.UserID')->select($select)->where(["tblAccount.AccountType"=>0,"tblAccount.CompanyID" => $companyID]);

        if (User::is('AccountManager')) { // Account Manager
            $leads->where(["tblAccount.Owner" => $userID ]);
        }

        if($data['account_active'] == 'true' ) {
            $leads->where('tblAccount.Status', 1);
        }else{
            $leads->where('tblAccount.Status', 0);
        }

        if(User::is_admin() && isset($data['account_owners'])  && trim($data['account_owners']) > 0) {
            $leads->where('tblAccount.Owner', (int)$data['account_owners']);
        }
        if(trim($data['account_name']) != '') {
            $leads->where('tblAccount.AccountName', 'like','%'.trim($data['account_name']).'%');
        }
        if(trim($data['account_number']) != '') {
            $leads->where('tblAccount.Number','like', '%'.trim($data['account_number']).'%');
        }
        if(trim($data['contact_name']) != '') {
            $leads->leftjoin('tblContact', 'tblContact.Owner', '=', 'tblAccount.AccountID');
            $leads->whereRaw(  " concat(tblContact.FirstName,' ', tblContact.LastName) like '%".trim($data['contact_name'])."%'");
        }
        if(trim($data['tag']) != '') {
            $leads->where('tblAccount.tags', 'like','%'.trim($data['tag']).'%');
        }
        return Datatables::of($leads)->make();
    }


    public function ajax_template($id){
        $user = User::get_currentUser();
        return array('EmailFooter'=>($user->EmailFooter?$user->EmailFooter:''),'EmailTemplate'=>EmailTemplate::findOrfail($id));
    }

    public function ajax_getEmailTemplate($privacy){
        $filter =array('Type'=>EmailTemplate::ACCOUNT_TEMPLATE);
        if($privacy == 1){
            $filter['UserID'] =  User::get_userID();
        }
        return EmailTemplate::getTemplateArray($filter);
    }

    /**
     * Display a listing of the resource.
     * GET /leads
     *
     * @return Response
     */
    public function index()
    {
            $companyID = User::get_companyID();
            $userID = User::get_userID();
            $tags = json_encode(Tags::getTagsArray());
            $account_owners = User::getOwnerUsersbyRole();
            $emailTemplates = array();
            $templateoption = ['' => 'Select', 1 => 'New Create', 2 => 'Update'];
            $accounts = Account::getAccountIDList(array("AccountType" => 0));
            $privacy = EmailTemplate::$privacy;
            $type = EmailTemplate::$Type;
            //$leads = DB::table('tblAccount')->where([ "AccountType"=>0, "CompanyID" => $companyID])->orderBy('AccountID', 'desc')->get();
            $tags = json_encode(Tags::getTagsArray(Tags::Account_tag));
            if (User::is('AccountManager')) { // Account Manager
                $leads = DB::table('tblAccount')->where(["AccountType" => 0, "CompanyID" => $companyID])->orderBy('AccountID', 'desc')->get();
            } else {
                $leads = DB::table('tblAccount')->where(["AccountType" => 0, "CompanyID" => $companyID])->orderBy('AccountID', 'desc')->get();
            }

            return View::make('leads.index', compact('leads', 'account_owners', 'emailTemplates', 'templateoption', 'tags', 'accounts', 'privacy', 'tags', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /leads/create
     *
     * @return Response
     */
    public function create()
    {
            $companyID = User::get_companyID();
            $account_owners = User::getOwnerUsersbyRole();
            $countries = $this->countries;
            return View::make('leads.create', compact('account_owners', 'update_url', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /leads
     *
     * @return Response
     */
    public function store()
    {

        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['IsVendor'] = isset($data['IsVendor']) ? 1 : 0;
        $data['IsCustomer'] = isset($data['IsCustomer']) ? 1 : 0;
        $data['AccountType'] = 0;
        $data['AccountName'] = trim($data['AccountName']);
        $data['Status'] = isset($data['Status']) ? 1 : 0;
        Lead::$rules['AccountName'] = 'required|unique:tblAccount,AccountName,NULL,CompanyID,CompanyID,'.$data['CompanyID'].'';
        $validator = Validator::make($data, Lead::$rules);
        $data['created_by'] =  User::get_user_full_name();


        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if($lead = Lead::create($data)){
            return  Response::json(array("status" => "success", "message" => "Lead Successfully Created",'LastID'=>$lead->AccountID,'redirect' => URL::to('/leads/'.$lead->AccountID.'/show')));
        } else {
            return  Response::json(array("status" => "failed", "message" => "Problem Creating Lead."));
        }
        //return Redirect::route('leads.index')->with('success_message', 'Leads Successfully Created');

    }

    /**
     * Display the specified resource.
     * GET /leads/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {

                $lead = Lead::find($id);
                $companyID = User::get_companyID();
                $lead_owner = User::find($lead->Owner);
                $notes = Note::where(["CompanyID" => $companyID, "AccountID" => $id])->orderBy('NoteID', 'desc')->get();
                $contacts = Contact::where(["CompanyID" => $companyID, "Owner" => $id])->orderBy('FirstName', 'asc')->get();
                return View::make('leads.show', compact('lead', 'lead_owner', 'notes', 'contacts'));

    }

    /**
     * Show the form for editing the specified resource.
     * GET /leads/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
            $lead = Lead::find($id);
            $tags = json_encode(Tags::getTagsArray(Tags::Account_tag));
            $companyID = User::get_companyID();
            $account_owners = User::getOwnerUsersbyRole();
            $countries = $this->countries;
            $text = 'Edit Lead';
            $url = URL::to('leads/update/' . $lead->AccountID);
            $url2 = 'leads/update/' . $lead->AccountID;
            return View::make('leads.edit', compact('lead', 'account_owners', 'countries', 'tags', 'text', 'url', 'url2'));
    }

    /**
     * Update the specified resource in storage.
     * PUT /leads/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $data = Input::all();
        $lead = Lead::find($id);
        $newTags = array_diff(explode(',',$data['tags']),Tags::getTagsArray());
        if(count($newTags)>0){
            foreach($newTags as $tag){
                Tags::create(array('TagName'=>$tag,'CompanyID'=>User::get_companyID(),'TagType'=>Tags::Account_tag));
            }
        }
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['IsVendor'] = isset($data['IsVendor']) ? 1 : 0;
        $data['IsCustomer'] = isset($data['IsCustomer']) ? 1 : 0;
        $data['updated_by'] =  User::get_user_full_name();
        $data['AccountName'] = trim($data['AccountName']);
        $data['Status'] = isset($data['Status']) ? 1 : 0;
        $rules = array(
            'Owner' =>      'required',
            'CompanyID' =>  'required',
            'AccountName' => 'required|unique:tblAccount,AccountName,'.$lead->AccountID . ',AccountID,CompanyID,'.$data['CompanyID'],
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($lead->update($data)){
            return  Response::json(array("status" => "success", "message" => "Lead Successfully Updated"));
        } else {
            return  Response::json(array("status" => "failed", "message" => "Problem Updating Lead."));
        }

        //return Redirect::route('leads.index')->with('success_message', 'Leads Successfully Updated');;

    }

    /**
     * Add notes to account
     * */
    public function store_note($id)
    {
        $data = Input::all();
        $companyID = User::get_companyID();
        $user_name = User::get_user_full_name();

        $data['CompanyID'] = $companyID;
        $data['AccountID'] = $id;
        $data['created_by'] = $user_name;
        $data["Note"] = nl2br($data["Note"]);

        $rules = array(
            'CompanyID' =>  'required',
            'AccountID' =>  'required',
            'Note'      => 'required',
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(empty($data["NoteID"])){
            unset($data["NoteID"]);
            $result = Note::create($data);
            $NoteID = DB::getPdo()->lastInsertId();

        }else{
            unset($data['created_by']);
            $data['updated_by']  = $user_name;
            $result = Note::find($data["NoteID"]);
            $result->update($data);
            $NoteID  = $data["NoteID"];
        }

        if($result){
            if(empty($data["NoteID"])){
                return  Response::json(array("status" => "success", "message" => "Note Successfully Updated", "NoteID"=>$NoteID, "Note" => $result  ));
            }
            return  Response::json(array("status" => "success", "message" => "Note Successfully Updated", "update" => true, "NoteID"=>$NoteID, "Note" => $result  ));

        } else {
            return  Response::json(array("status" => "failed", "message" => "Problem Updating Note."));
        }

    }

    /**
     * Delete a Note
     */
    public function delete_note($id){

        $result = Note::find($id)->delete();
        if($result){
            return  Response::json(array("status" => "success", "message" => "Note Successfully Deleted",   "NoteID" => $id ));
        }else{
            return  Response::json(array("status" => "failed", "message" => "Problem Deleting Note."));
        }

    }

    /**
     * Convert to account
     * */
    public function convert($id)
    {


            $data = Input::all();
            $account = Account::find($id);

            $companyID = User::get_companyID();
            $user_name = User::get_user_full_name();
            $data['CompanyID'] = $companyID;
            $data['AccountType'] = 1;
            $data['Converted'] = 1;
            $data['ConvertedDate'] = date('m/d/Y h:i:s', time());
            $data['ConvertedBy'] = $user_name;
            $data['VerificationStatus'] = 0; // Status : Unverified
            $data['BillingEmail'] = $account->Email; // BillingEmail : Email - We dont show Email field in Account.

            $rules = array(
                'ConvertedBy' => 'required',
                'CompanyID' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }

            $account->update($data);

            return Redirect::to('accounts/' . $id . '/show')->with('is_converted', 'Lead Successfully Converted to Account');
    }


    public function exports()
    {
            $companyID = User::get_companyID();
            $userID = User::get_userID();
            $data = Input::all();
            if (isset($data['sSearch_0']) && ($data['sSearch_0'] == '' || $data['sSearch_0'] == '1')) {
                if (User::is_admin() || User::is('AccountManager')) { // Account Manager
                    $accounts = Account::where(["Status" => 1, "AccountType" => 0, "CompanyID" => $companyID])->orderBy("AccountID", "desc")->get(["AccountName as LeadName", "Phone", "Email"]);
                }
            } else {
                if (User::is_admin() || User::is('AccountManager')) { // Account Manager
                    $accounts = Account::where(["Status" => 0, "AccountType" => 0, "CompanyID" => $companyID])->orderBy("AccountID", "desc")->get(["AccountName as LeadName", "Phone", "Email"]);
                }
            }
            Excel::create('Leads', function ($excel) use ($accounts) {
                $excel->sheet('Leads', function ($sheet) use ($accounts) {
                    $sheet->fromArray($accounts);
                });
            })->download('xls');
    }

    public function bulk_mail(){
            $data = Input::all();
            return bulk_mail('BLE', $data);
    }

    public function bulk_tags(){
            $data = Input::all();
            $rules = array(
                'tags' => 'required',
                'SelectedIDs' => 'required',
            );

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            $newTags = array_diff(explode(',', $data['tags']), Tags::getTagsArray());
            if (count($newTags) > 0) {
                foreach ($newTags as $tag) {
                    Tags::create(array('TagName' => $tag, 'CompanyID' => User::get_companyID()));
                }
            }
            $SelectedIDs = $data['SelectedIDs'];
            unset($data['SelectedIDs']);
            if (Lead::whereIn('AccountID', explode(',', $SelectedIDs))->update($data)) {
                return Response::json(array("status" => "success", "message" => "Lead Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Lead."));
            }
    }

    public function lead_clone($id){
            $lead = Lead::find($id);
            $tags = json_encode(Tags::getTagsArray());
            $account_owners = User::getOwnerUsersbyRole();
            $countries = $this->countries;
            $text = 'New Lead';
            $url = URL::to('leads/store');
            $url2 = 'leads/store';
            return View::make('leads.edit', compact('lead', 'account_owners', 'countries', 'tags','text','url','url2'));
    }

    //import leads by csv
    public function import_leads(){
        $id=1;
        $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_Leads);
        return View::make('leads.importleads',compact('UploadTemplate','id'));
    }

    public function download_sample_excel_file(){
        $filePath =  public_path() .'/uploads/sample_upload/LeadsImportSample.csv';
        download_file($filePath);

    }

    function ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TempFileName'];
            $grid = getFileContent($file_name, $data);
            $grid['filename'] = $data['TemplateFile'];
            $grid['tempfilename'] = $data['TempFileName'];
            if ($data['uploadtemplate'] > 0) {
                $LeadsFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                $grid['LeadsFileUploadTemplate'] = json_decode(json_encode($LeadsFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['LeadsFileUploadTemplate']['Options'] = array();
            $grid['LeadsFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['LeadsFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function check_upload() {
        try {
            ini_set('max_execution_time', 0);
            $data = Input::all();
            if(Input::hasFile('excel')) {
                $upload_path = getenv('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array($ext, array("csv", "xls", "xlsx"))) {
                    $file_name_without_ext = GUID::generate();
                    $file_name = $file_name_without_ext . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else if (!empty($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if (!empty($file_name)) {

                if (!empty($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                    $LeadsFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($LeadsFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($LeadsFileUploadTemplate)) {
                    $grid['LeadsFileUploadTemplate'] = json_decode(json_encode($LeadsFileUploadTemplate), true);
                    $grid['LeadsFileUploadTemplate']['Options'] = json_decode($LeadsFileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        }catch(Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function storeTemplate() {
        $data = Input::all();
        $CompanyID = User::get_companyID();

        //$rules['selection.Email'] = 'required';
        $rules['selection.Country'] = 'required';
        $rules['selection.FirstName'] = 'required';
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);
        $temp_path = getenv('TEMP_PATH') . '/';


        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ACCOUNT_DOCUMENT']);
        $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload accounts file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            $save['Type'] = FileUploadTemplate::TEMPLATE_Leads;
            if (isset($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                $template = FileUploadTemplate::find($data['uploadtemplate']);
                $template->update($save);
            } else {
                $template = FileUploadTemplate::create($save);
            }
            $data['uploadtemplate'] = $template->FileUploadTemplateID;
        }
        $save = array();
        $option["option"]=  $data['option'];
        $option["selection"] = $data['selection'];
        $save['Options'] = json_encode($option);
        $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path'] = $fullPath;
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        $save['AccountType'] = '0';
        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("MGA", $save);
            if ($result['status'] != "success") {
                DB::rollback();
                return json_encode(["status" => "failed", "message" => $result['message']]);
            }
            DB::commit();
            @unlink($temp_path . $file_name);
            return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
        } catch (Exception $ex) {
            DB::rollback();
            return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }

}