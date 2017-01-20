<?php

class ContactsController extends \BaseController {

    var $countries;

    public function __construct() {
        $this->countries = Country::getCountryDropdownList();
    }

    public function ajax_datagrid() {

        $companyID = User::get_companyID();
        if (User::is('AccountManager')) {
            $userID = User::get_userID();
            $contacts = Contact::leftjoin('tblAccount', 'tblAccount.AccountID', '=', 'tblContact.Owner')
            ->select([DB::raw("  concat(tblContact.FirstName,' ' ,tblContact.LastName)  AS FullName "), "tblAccount.AccountName","tblContact.Phone", "tblContact.Email", "tblContact.ContactID"])->where(["tblContact.CompanyID" => $companyID])->WhereRaw("( tblAccount.Owner = ".    $userID. " OR tblContact.Owner is NULL   OR tblAccount.AccountType = 0 ) ");
        }else{
            $contacts = Contact::leftjoin('tblAccount', 'tblAccount.AccountID', '=', 'tblContact.Owner')
                ->select([DB::raw("  concat(tblContact.FirstName,' ' ,tblContact.LastName)  AS FullName "), "tblAccount.AccountName","tblContact.Phone", "tblContact.Email", "tblContact.ContactID"])->where(["tblContact.CompanyID" => $companyID]);
        }

        return Datatables::of($contacts)->make();
    }

    /**
     * Display a listing of the resource.
     * GET /contacts
     *
     * @return Response
     */
    public function index() {
            return View::make('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /contacts/create
     *
     * @return Response
     */
    public function create() {

            $companyID = User::get_companyID();
            $lead_owners = Lead::getLeadOwnersByRole();
            $account_owners = Account::getAccountsOwnersByRole();
            $countries = $this->countries;
            return View::make('contacts.create', compact('lead_owners', 'account_owners', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /contacts
     *
     * @return Response
     */
    public function store() {
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['created_by'] = User::get_user_full_name();
        $data['updated_by'] = User::get_user_full_name();

        $messages = array('Owner.required' => 'The Contact Owner is required');

        $validator = Validator::make($data, Contact::$rules, $messages);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if ($contact = Contact::create($data)) {
            return Response::json(array("status" => "success", "message" => "Contact Successfully Created",'LastID'=>$contact->ContactID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Contact."));
        }
    }

    /**
     * Display the specified resource.
     * GET /contacts/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
            $contact = Contact::find($id);
            $companyID = User::get_companyID();
            $contact_owner = Account::find($contact->AccountID);
            $notes = ContactNote::where(["CompanyID" => $companyID, "ContactID" => $id])->orderBy('NoteID', 'desc')->get();
            return View::make('contacts.show', compact('contact', 'contact_owner', 'notes'));
    }

    /**
     * Show the form for editing the specified resource.
     * GET /contacts/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
            $contact = Contact::find($id);
            $companyID = User::get_companyID();
            $lead_owners = Lead::getLeadOwnersByRole();
            $account_owners = Account::getAccountsOwnersByRole();
            $countries = $this->countries;
            return View::make('contacts.edit', compact('contact', 'lead_owners', 'account_owners', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     * PUT /contacts/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        $data = Input::all();
        $lead = Contact::find($id);

        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['updated_by'] = User::get_user_full_name();
        $messages = array('AccountID.required' => 'The Contact Owner is required');

        $validator = Validator::make($data, Contact::$rules, $messages);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if ($lead->update($data)) {
            return Response::json(array("status" => "success", "message" => "Contact Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Contact."));
        }
    }

    /**
     * Add notes to account
     * */
    public function store_note($id) {
        $data = Input::all();
        //$contact = Contact::find($id);

        $companyID = User::get_companyID();
        $user_name = User::get_user_full_name();

        $data['CompanyID'] = $companyID;
        $data['ContactID'] = $id;
        $data['created_by'] = $user_name;
        $data["Note"] = nl2br($data["Note"]);

        $rules = array(
            'CompanyID' => 'required',
            'ContactID' => 'required',
            'Note' => 'required',
        );

        $validator = Validator::make($data, $rules);


        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if (empty($data["NoteID"])) {
            unset($data["NoteID"]);

            $result = ContactNote::create($data);
            $NoteID = DB::getPdo()->lastInsertId();
        } else {
            unset($data['created_by']);
            $data['updated_by'] = $user_name;
            $result = ContactNote::find($data["NoteID"]);
            $result->update($data);
            $NoteID = $data["NoteID"];
        }

        if ($result) {
            if (empty($data["NoteID"])) {
                return Response::json(array("status" => "success", "message" => "Note Successfully Updated", "NoteID" => $NoteID, "Note" => $result));
            }
            return Response::json(array("status" => "success", "message" => "Note Successfully Updated", "update" => true, "NoteID" => $NoteID, "Note" => $result));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Note."));
        }
    }

    /**
     * Delete a Note
     */
    public function delete_note($id) {

        $result = ContactNote::find($id)->delete();
        if ($result) {
            return Response::json(array("status" => "success", "message" => "Note Successfully Deleted", "NoteID" => $id));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Note."));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /contacts/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //$contact = Contact::find($id);
        if (Contact::destroy($id)) {
            return Response::json(array("status" => "success", "message" => "Contact Successfully Deleted"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Contact."));
        }
    }
    public function exports($type) {
            $companyID = User::get_companyID();
            // if CRM or Account Manager show ony their Contacts.
            if (User::is('AccountManager') || User::is('CRM')) {
                $userID = User::get_userID();
                $contacts = Contact::leftjoin('tblAccount', 'tblAccount.AccountID', '=', 'tblContact.Owner')
                    ->where(["tblContact.CompanyID" => $companyID])->WhereRaw("( tblContact.Owner = ".    $userID. " OR tblContact.Owner is NULL)")
                    ->orderBy("ContactID", 'desc')
                    ->get([DB::raw("  concat(tblContact.FirstName,' ',tblContact.LastName)  AS FullName "), "tblAccount.AccountName","tblContact.Phone", "tblContact.Email"]);
            }else{
                $contacts = Contact::leftjoin('tblAccount', 'tblAccount.AccountID', '=', 'tblContact.Owner')
                    ->where(["tblContact.CompanyID" => $companyID])
                    ->orderBy("ContactID", 'desc')
                    ->get([DB::raw("  concat(tblContact.FirstName,' ',tblContact.LastName)  AS FullName "), "tblAccount.AccountName","tblContact.Phone", "tblContact.Email"]);
            }

            $excel_data = json_decode(json_encode($contacts),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Contacts.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Contacts.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Contacts', function ($excel) use ($contacts) {
                $excel->sheet('Contacts', function ($sheet) use ($contacts) {
                    $sheet->fromArray($contacts);
                });
            })->download('xls');*/
    }
	
	function UpdateContactOwner($id){

        $postdata 				= 	Input::all();
        $lead 					= 	Contact::find($id);
        $data['updated_by'] 	= 	User::get_user_full_name();
		$data['Owner'] 			= 	$postdata['Owner'];
        $messages 				= 	array('Owner.required' => 'The Contact Owner is required');
		
		 $rules = array(
            'Owner' =>      'required',          
        );
        $validator = Validator::make($postdata, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if ($lead->update($data)) {
            return Response::json(array("status" => "success", "message" => "Contact Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Contact."));
        }
	}
	
	public function ShowTimeLine($id) {
            $companyID 					= 	 User::get_companyID();
			//get contacts data
		    $contacts 					= 	 Contact::find($id);			 
			//echo "<pre>"; 			print_r($contacts); 			echo "</pre>";			exit;
			//get contacts time line data
            $data['iDisplayStart'] 	    =	 0;
            $data['iDisplayLength']     =    10;
            $data['AccountID']          =    $id;
			$data['GUID']               =    GUID::generate();
            $PageNumber                 =    ceil($data['iDisplayStart']/$data['iDisplayLength']);
            $RowsPerPage                =    $data['iDisplayLength'];			
			$message 					= 	 '';			
            $response_timeline 			= 	 NeonAPI::request('contact/GetTimeLine',$data,false,true);
		/*		echo "<pre>";
				print_r($response_timeline);		
				exit;*/
	
			if($response_timeline['status']!='failed'){
				if(isset($response_timeline['data']))
				{
					$response_timeline =  $response_timeline['data'];
				}else{
					$response_timeline = array();
				}
			}else{ 	
				if(isset($response_timeline['Code']) && ($response_timeline['Code']==400 || $response_timeline['Code']==401)){
					return	Redirect::to('/logout'); 	
				}		
				if(isset($response_timeline->error) && $response_timeline->error=='token_expired'){ Redirect::to('/login');}	
				$message = json_response_api($response_timeline,false,false);
			}
			
		
			$emailTemplates 			= 	 $this->ajax_getEmailTemplate(EmailTemplate::PRIVACY_OFF,EmailTemplate::ACCOUNT_TEMPLATE);
			$random_token				=	 get_random_number();
            
			//Backup code for getting extensions from api
		   $response_api_extensions 	=   Get_Api_file_extentsions();
		   if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}	
		   $response_extensions			=	json_encode($response_api_extensions['allowed_extensions']);
		   
           //all users email address
			$users						=	 USer::select('EmailAddress')->lists('EmailAddress');
	 		$users						=	 json_encode(array_merge(array(""),$users));
			$max_file_size				=	get_max_file_size();			
			$per_scroll 				=   $data['iDisplayLength'];
			$current_user_title 		= 	Auth::user()->FirstName.' '.Auth::user()->LastName;
			$ShowTickets				=   SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$freshdeskSlug); //freshdesk

	        return View::make('contacts.timeline.view', compact('response_timeline','account', 'contacts', 'verificationflag', 'outstanding','response','message','current_user_title','per_scroll','Account_card','account_owners','Board','emailTemplates','response_extensions','random_token','users','max_file_size','leadOrAccount','leadOrAccountCheck','opportunitytags','leadOrAccountID','accounts','boards','data','ShowTickets')); 	
		}
	
	  public function ajax_getEmailTemplate($privacy, $type){
        $filter = array();
        if($type == EmailTemplate::ACCOUNT_TEMPLATE){
            $filter =array('Type'=>EmailTemplate::ACCOUNT_TEMPLATE);
        }elseif($type== EmailTemplate::RATESHEET_TEMPLATE){
            $filter =array('Type'=>EmailTemplate::RATESHEET_TEMPLATE);
        }
        if($privacy == 1){
            $filter ['UserID'] =  User::get_userID();
        }
        return EmailTemplate::getTemplateArray($filter);
    }

}