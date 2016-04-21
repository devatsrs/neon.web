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

}