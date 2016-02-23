<?php

class AccountsController extends \BaseController {

    var $countries;
    var $model = 'Account';
    public function __construct() {
        $this->countries = Country::getCountryDropdownList();
    }

    public function ajax_datagrid() {
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $userID = 0;
        if (User::is('AccountManager')) { // Account Manager
            $userID = $userID = User::get_userID();
        }elseif(User::is_admin() && isset($data['account_owners'])  && trim($data['account_owners']) > 0) {
            $userID = (int)$data['account_owners'];
        }
        $data['vendor_on_off'] = $data['vendor_on_off']== 'true'?1:0;
        $data['customer_on_off'] = $data['customer_on_off']== 'true'?1:0;
        $data['account_active'] = $data['account_active']== 'true'?1:0;
        //$data['account_name'] = $data['account_name']!= ''?$data['account_name']:'';
        //$data['tag'] = $data['tag']!= ''?$data['tag']:'null';
        //$data['account_number'] = $data['account_number']!= ''?$data['account_number']:0;
        //$data['contact_name'] = $data['contact_name']!= ''?$data['contact_name']:'';
        $columns = array('AccountID','Number','AccountName','Ownername','Phone','OutStandingAmount','Email','AccountID');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_GetAccounts (".$CompanyID.",".$userID.",".$data['vendor_on_off'].",".$data['customer_on_off'].",".$data['account_active'].",".$data['verification_status'].",'".$data['account_number']."','".$data['contact_name']."','".$data['account_name']."','".$data['tag']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Accounts', function ($excel) use ($excel_data) {
                $excel->sheet('Accounts', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        $query .=',0)';

        return DataTableSql::of($query)->make();
    }


    public function ajax_datagrid_PaymentProfiles($AccountID) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $carddetail = AccountPaymentProfile::select("tblAccountPaymentProfile.Title","tblAccountPaymentProfile.Status","tblAccountPaymentProfile.isDefault","tblPaymentGateway.Title as gateway","created_at","AccountPaymentProfileID");
        $carddetail->join('tblPaymentGateway', function($join)
        {
            $join->on('tblPaymentGateway.PaymentGatewayID', '=', 'tblAccountPaymentProfile.PaymentGatewayID');

        })->where(["tblAccountPaymentProfile.CompanyID"=>$CompanyID])->where(["tblAccountPaymentProfile.AccountID"=>$AccountID]);

        return Datatables::of($carddetail)->make();
    }

    public function ajax_template($id){
        $user = User::get_currentUser();
        return array('EmailFooter'=>($user->EmailFooter?$user->EmailFooter:''),'EmailTemplate'=>EmailTemplate::findOrfail($id));
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

    /**
     * Display a listing of the resource.
     * GET /accounts
     *
     * @return Response
     */
    public function index() {

            $trunks = CustomerTrunk::getTrunkDropdownIDListAll(); //$this->trunks;
            $tags = json_encode(Tags::getTagsArray());
            $account_owners = User::getOwnerUsersbyRole();
            $accounts = Account::getAccountIDList();
            $emailTemplates = array();
            $privacy = EmailTemplate::$privacy;
            $templateoption = ['' => 'Select', 1 => 'Create new', 2 => 'Update existing'];
            return View::make('accounts.index', compact('account_owners', 'emailTemplates', 'templateoption', 'accounts', 'tags', 'privacy', 'type', 'trunks', 'rate_sheet_formates'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /accounts/create
     *
     * @return Response
     */
    public function create() {
            $account_owners = User::getOwnerUsersbyRole();
            $countries = $this->countries;

            $currencies = Currency::getCurrencyDropdownIDList();
            $taxrates = TaxRate::getTaxRateDropdownIDList();
            if(isset($taxrates[""])){unset($taxrates[""]);}
            $timezones = TimeZone::getTimeZoneDropdownList();
            $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();
            $BillingStartDate=date('Y-m-d');
            $LastAccountNo =  '';
            $doc_status = Account::$doc_status;
            if(!User::is_admin()){
                unset($doc_status[Account::VERIFIED]);
            }
            return View::make('accounts.create', compact('account_owners', 'countries','LastAccountNo','doc_status','currencies','taxrates','timezones','InvoiceTemplates','BillingStartDate'));
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
            $data['AccountType'] = 1;
            $data['IsVendor'] = isset($data['IsVendor']) ? 1 : 0;
            $data['IsCustomer'] = isset($data['IsCustomer']) ? 1 : 0;
            $data['created_by'] = User::get_user_full_name();
            $data['AccountType'] = 1;
            $data['AccountName'] = trim($data['AccountName']);
            if (isset($data['TaxRateId'])) {
                $data['TaxRateId'] = implode(',', array_unique($data['TaxRateId']));
            }
            if (strpbrk($data['AccountName'], '\/?*:|"<>')) {
                return Response::json(array("status" => "failed", "message" => "Account Name contains illegal character."));
            }
            $data['Status'] = isset($data['Status']) ? 1 : 0;

            if (empty($data['Number'])) {
                $data['Number'] = Account::getLastAccountNo();
            }
            $data['Number'] = trim($data['Number']);

        if(Company::isBillingLicence()) {
            Account::$rules['BillingType'] = 'required';
            Account::$rules['BillingTimezone'] = 'required';
            //Account::$rules['InvoiceTemplateID'] = 'required';
        }

            Account::$rules['AccountName'] = 'required|unique:tblAccount,AccountName,NULL,CompanyID,CompanyID,' . $data['CompanyID'];
            Account::$rules['Number'] = 'required|unique:tblAccount,Number,NULL,CompanyID,CompanyID,' . $data['CompanyID'];

            $validator = Validator::make($data, Account::$rules, Account::$messages);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $data['AccountIP'] = implode(',', array_unique(explode(',', $data['AccountIP'])));

            if ($account = Account::create($data)) {
                if (trim(Input::get('Number')) == '') {
                    CompanySetting::setKeyVal('LastAccountNo', $account->Number);
                }
                $data['NextInvoiceDate'] = Invoice::getNextInvoiceDate($account->AccountID);
                $account->update($data);
                return Response::json(array("status" => "success", "message" => "Account Successfully Created", 'LastID' => $account->AccountID, 'redirect' => URL::to('/accounts/' . $account->AccountID . '/edit')));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Account."));
            }


            //return Redirect::route('accounts.index')->with('success_message', 'Accounts Successfully Created');
    }

    /**
     * Display the specified resource.
     * GET /accounts/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {

            $account = Account::find($id);
            $companyID = User::get_companyID();
            $account_owner = User::find($account->Owner);
            $notes = Note::where(["CompanyID" => $companyID, "AccountID" => $id])->orderBy('NoteID', 'desc')->get();
            $contacts = Contact::where(["CompanyID" => $companyID, "Owner" => $id])->orderBy('FirstName', 'asc')->get();
            $verificationflag = AccountApprovalList::isVerfiable($id);
            $outstanding = Account::getOutstandingAmount($companyID, $account->AccountID, $account->RoundChargesAmount);
            $currency = Currency::getCurrency($account->CurrencyId);
            $activity_type = AccountActivity::$activity_type;
            $activity_status = [1 => 'Open', 2 => 'Closed'];
            return View::make('accounts.show', compact('account', 'account_owner', 'notes', 'contacts', 'verificationflag', 'outstanding', 'currency', 'activity_type', 'activity_status'));
    }

    /**
     * Show the form for editing the specified resource.
     * GET /accounts/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
            $account = Account::find($id);
            $companyID = User::get_companyID();
            $account_owners = User::getOwnerUsersbyRole();
            $countries = $this->countries;
            $tags = json_encode(Tags::getTagsArray());
            $products = Product::getProductDropdownList();
            $taxes = TaxRate::getTaxRateDropdownIDListForInvoice(0);
            $currencies = Currency::getCurrencyDropdownIDList();
            $taxrates = TaxRate::getTaxRateDropdownIDList();
            if(isset($taxrates[""])){unset($taxrates[""]);}
            $timezones = TimeZone::getTimeZoneDropdownList();
            $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();

            $AccountApproval = AccountApproval::getList($id);
            $doc_status = Account::$doc_status;
            $verificationflag = AccountApprovalList::isVerfiable($id);
            $invoice_count = Account::getInvoiceCount($id);
            if(!User::is_admin() &&   $verificationflag == false && $account->VerificationStatus != Account::VERIFIED){
                unset($doc_status[Account::VERIFIED]);
            }
            return View::make('accounts.edit', compact('account', 'account_owners', 'countries','AccountApproval','doc_status','currencies','timezones','taxrates','verificationflag','InvoiceTemplates','invoice_count','tags','products','taxes'));
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
        $account = Account::find($id);
        $newTags = array_diff(explode(',',$data['tags']),Tags::getTagsArray());
        if(count($newTags)>0){
            foreach($newTags as $tag){
                Tags::create(array('TagName'=>$tag,'CompanyID'=>User::get_companyID(),'TagType'=>Tags::Account_tag));
            }
        }
        $message = $password = "";
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['IsVendor'] = isset($data['IsVendor']) ? 1 : 0;
        $data['IsCustomer'] = isset($data['IsCustomer']) ? 1 : 0;
        $data['updated_by'] = User::get_user_full_name();
		$data['AccountName'] = trim($data['AccountName']);

        $shipping = array('firstName'=>$account['FirstName'],
            'lastName'=>$account['LastName'],
            'address'=>$data['Address1'],
            'city'=>$data['City'],
            'state'=>$account['state'],
            'zip'=>$data['PostCode'],
            'country'=>$data['Country'],
            'phoneNumber'=>$account['Mobile']);
        unset($data['table-4_length']);
        unset($data['cardID']);

        if(isset($data['TaxRateId'])) {
            $data['TaxRateId'] = implode(',', array_unique($data['TaxRateId']));
        }
        if (strpbrk($data['AccountName'],'\/?*:|"<>')) {
            return Response::json(array("status" => "failed", "message" => "Account Name contains illegal character."));
        }
        $data['Status'] = isset($data['Status']) ? 1 : 0;
        if(trim($data['Number']) == ''){
            $data['Number'] = Account::getLastAccountNo();
        }

        if(empty($data['password'])){ /* if empty, dont update password */
            unset($data['password']);
        }else{
            if($account->VerificationStatus == Account::VERIFIED && $account->Status == 1 ) {
                /* Send mail to Customer */
                $password       = $data['password'];
                $data['password']       = Hash::make($password);
            }
        }
        $data['Number'] = trim($data['Number']);

        if(Company::isBillingLicence()) {
            Account::$rules['BillingType'] = 'required';
            Account::$rules['BillingTimezone'] = 'required';
            $icount = Invoice::where(["AccountID" => $id])->count();
            if($icount>0){
                Account::$rules['InvoiceTemplateID'] = 'required';
            }
        }

        Account::$rules['AccountName'] = 'required|unique:tblAccount,AccountName,' . $account->AccountID . ',AccountID,CompanyID,'.$data['CompanyID'];
        Account::$rules['Number'] = 'required|unique:tblAccount,Number,' . $account->AccountID . ',AccountID,CompanyID,'.$data['CompanyID'];

        $validator = Validator::make($data, Account::$rules,Account::$messages);

        if ($validator->fails()) {
            return json_validator_response($validator);
            exit;
        }
        $data['AccountIP'] = implode(',',array_unique(explode(',',$data['AccountIP'])));
        $data['CustomerCLI'] = implode(',',array_unique(explode(',',$data['CustomerCLI'])));

        if ($account->update($data)) {
            $data['NextInvoiceDate'] = Invoice::getNextInvoiceDate($id);
            $invoice_count = Account::getInvoiceCount($id);
            if($invoice_count == 0){
                $data['LastInvoiceDate'] = $data['BillingStartDate'];
            }
            $account->update($data);
            if(trim(Input::get('Number')) == ''){
                CompanySetting::setKeyVal('LastAccountNo',$account->Number);
            }
            if(isset($data['password'])) {
                $this->sendPasswordEmail($account, $password, $data);
            }
            $PaymentGatewayID = PaymentGateway::where(['Title'=>PaymentGateway::$gateways['Authorize']])
                ->where(['CompanyID'=>$companyID])
                ->pluck('PaymentGatewayID');
            $PaymentProfile = AccountPaymentProfile::where(['AccountID'=>$id])
                ->where(['CompanyID'=>$companyID])
                ->where(['PaymentGatewayID'=>$PaymentGatewayID])
                ->first();
            if(!empty($PaymentProfile)){
                $options = json_decode($PaymentProfile->Options);
                $ProfileID = $options->ProfileID;
                $ShippingProfileID = $options->ShippingProfileID;

                //If using Authorize.net
                $isAuthorizedNet = getenv('AMAZONS3_KEY');
                if(!empty($isAuthorizedNet)) {
                    $AuthorizeNet = new AuthorizeNet();
                    $result = $AuthorizeNet->UpdateShippingAddress($ProfileID, $ShippingProfileID, $shipping);
                }
            }
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated. " . $message));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Account."));
        }
        //return Redirect::route('accounts.index')->with('success_message', 'Accounts Successfully Updated');;
    }

    /**
     * Add notes to account
     * */
    public function store_note($id) {
        $data = Input::all();
        //$account = Account::find($id);

        $companyID = User::get_companyID();
        $user_name = User::get_user_full_name();

        $data['CompanyID'] = $companyID;
        $data['AccountID'] = $id;
        $data['created_by'] = $user_name;
        $data["Note"] = nl2br($data["Note"]);

        $rules = array(
            'CompanyID' => 'required',
            'AccountID' => 'required',
            'Note' => 'required',
        );

        $validator = Validator::make($data, $rules);


        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if (empty($data["NoteID"])) {
            unset($data["NoteID"]);
            $result = Note::create($data);
            $NoteID = DB::getPdo()->lastInsertId();
        } else {
            unset($data['created_by']);
            $data['updated_by'] = $user_name;
            $result = Note::find($data["NoteID"]);
            if(!empty($result)) {
                $result->update($data);
            }
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

        $result = Note::find($id)->delete();
        if ($result) {
            return Response::json(array("status" => "success", "message" => "Note Successfully Deleted", "NoteID" => $id));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Note."));
        }
    }

    public  function  upload($id){
        if (Input::hasFile('excel')) {
            $data = Input::all();
            $today = date('Y-m-d');
            $upload_path = Config::get('app.acc_doc_path');
            $company_name = Account::getCompanyNameByID($id);
            $destinationPath = $upload_path . sprintf("\\%s\\", $company_name);
            $excel = Input::file('excel');
            // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();

            if (in_array($ext, array("doc", "docx", 'xls','xlsx',"pdf",'png','jpg','gif'))) {
                $filename = rename_upload_file($destinationPath,$excel->getClientOriginalName());
                $fullPath = $destinationPath .$filename;
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ACCOUNT_DOCUMENT'],$id) ;
                $excel->move($destinationPath, $filename);
                if(!AmazonS3::upload($destinationPath.$filename,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $data['full_path'] = $amazonPath . $filename;
                $username = User::get_user_full_name();
                $list =  AccountApprovalList::create(array('CompanyID' => User::get_companyID(), 'AccountApprovalID' => $data['AccountApprovalID'],'AccountID'=>$id, 'FileName' => $data['full_path'], 'CreatedBy' => $username, 'created_at' => $today));
                $AccountApprovalListID = $list->AccountApprovalListID;
                $filename = basename($list->FileName);

                $refrsh = 0;
                if(AccountApprovalList::isVerfiable($id)){
                    $refrsh = 1;
                }
                return json_encode(["status" => "success",'refresh'=>$refrsh, "message" => "File Uploaded Successfully",'LastID'=>$AccountApprovalListID,'Filename'=>$filename]);

            } else {
                echo json_encode(array("status" => "failed", "message" => "Please upload doc/pdf/image file only."));
            }

        }else {
            echo json_encode(array("status" => "failed", "message" => "Please upload doc/pdf/image file <5MB."));
        }
    }
    public function  download_doc($id){
        $FileName = AccountApprovalList::where(["AccountApprovalListID"=>$id])->pluck('FileName');
        $FilePath =  AmazonS3::preSignedUrl($FileName);
        if(is_amazon() == true){
            header('Location: '.$FilePath);
        }else if(file_exists($FilePath)){
            download_file($FilePath);
        }
        exit;
    }
    public function  download_doc_file($id){
        $DocumentFile = AccountApproval::where(["AccountApprovalID"=>$id])->pluck('DocumentFile');
        $FilePath =  AmazonS3::preSignedUrl($DocumentFile);
        if(is_amazon() == true){
            header('Location: '.$FilePath);
        }else if(file_exists($FilePath)){
            download_file($FilePath);
        }
        exit;
    }
    public function delete_doc($id){
        $AccountApprovalList = AccountApprovalList::find($id);
        $filename = $AccountApprovalList->FileName;
        if($AccountApprovalList->delete()){
            if(file_exists($filename)){
                @unlink($filename);
            }else{
                AmazonS3::delete($filename);
            }
            echo json_encode(array("status" => "success", "message" => "Document deleted successfully"));
        }else{
            echo json_encode(array("status" => "failed", "message" => "Problem Deleting Document"));
        }
    }

    public function ajax_datagrid_sheet() {
            $data = Input::all();

            $columns = array('AccountName', 'Trunk', 'EffectiveDate');
            $sort_column = $columns[$data['iSortCol_0']];

            $companyID = User::get_companyID();
            $data['iDisplayStart'] += 1;

            $userID = '';
            if (User::is('AccountManager')) {
                $userID = User::get_userID();
            } elseif (User::is_admin()) {
                $userID = 0;
            }

        $query = "call prc_GetRecentDueSheet (".$companyID.",".$userID.",".$data['AccountType'].",'" . $data['DueDate'] . "',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $query = "call prc_GetRecentDueSheet (".$companyID.",".$userID.",".$data['AccountType'] . ",'" . $data['DueDate'] . "'," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "',1)";
                DB::setFetchMode(PDO::FETCH_ASSOC);
                $due_sheets = DB::select($query);
                DB::setFetchMode(Config::get('database.fetch'));

                Excel::create('Recent Due Sheet', function ($excel) use ($due_sheets) {
                    $excel->sheet('Recent Due Sheet', function ($sheet) use ($due_sheets) {
                        $sheet->fromArray($due_sheets);
                    });
                })->download('xls');
            }

            return DataTableSql::of($query)->make();
    }

    public function due_ratesheet()
    {
        return View::make('accounts.dueratesheet', compact(''));
    }
    public function addbillingaccount(){
        $customer = DB::connection('sqlsrv3')->table('tblcustomer')->get();
        $old_db = array();
        $new_db = array();
        $accountno = '';
        foreach($customer as $customerrow){
            $old_db[$customerrow->CustomerID] =  trim($customerrow->CustomerName);
        }
        $customer_new = Account::getAccountIDList(array('IsCustomer'=>1));
        foreach($customer_new as $acontid =>$customerrow){
           $new_db[$acontid] =  trim($customerrow);
        }
        echo '<pre>';
        $missing_account =  array_diff(array_values($new_db),array_values($old_db));
        $missing_accountids = array_intersect($new_db,$missing_account);
        echo count($missing_account);
        echo '<br>';
        echo count($missing_accountids);
        echo '<br>';
        foreach($missing_accountids as $accountid => $account_name){
            echo '<br>';
            echo '<br>';
            echo $accountid.'==>'.$account_name;
            echo '<br>';
            echo '<br>';
            $account = Account::find($accountid);

            if($accountid>0){
               $already_account =  DB::connection('sqlsrv3')->table('tblcustomer')->where(array('CustomerID'=>$account->Number))->get();
                if(empty($already_account)) {
                    echo $query = 'SET IDENTITY_INSERT RateManagement.dbo.tblcustomer ON
insert into RateManagement.dbo.tblcustomer (CustomerID,CompanyID,CustomerName,Active,Postcode,Address1,Address2,Address3,ContactEmail,RateEmail,BillingEmail,TechnicalEmail,VATNo) values
(' . " '$account->Number','$account->CompanyId','$account->AccountName','$account->Status','$account->Postcode','$account->Address1','$account->Address2','$account->Address3','$account->Email','$account->RateEmail','$account->BillingEmail','$account->TechnicalEmail','$account->vatnumber')" .
                      "
SET IDENTITY_INSERT RateManagement.dbo.tblcustomer OFF
insert into tblInvoiceCompany (InvoiceCompany,CompanyID,DubaiCompany,CustomerID,Active) values
('$account->AccountName','$account->CompanyId',0,'$account->Number','$account->Status')
";
                    $accountno .= $account->Number . ',';
                    DB::connection('sqlsrv3')->statement($query);
                }

            }
        }
        echo $accountno;


    }

    public static function change_verifiaction_status($id,$status){
        if($id>0){
            Account::find($id)->update(["VerificationStatus"=>intval($status)]);
            echo json_encode(array("status" => "success", "message" => "Account Verification Status Updated"));
        }
        else {
            echo json_encode(array("status" => "failed", "message" => "Problem Updating Account Verification Status"));
        }
    }
    public function sendPasswordEmail($account, $password , $data){
        if(!empty($password) && $account->VerificationStatus == Account::VERIFIED && $account->Status == 1 ) {
            /* Send mail to Customer */
            $email_data = array();
            $emailtoCustomer = getenv('EmailToCustomer');
            if(intval($emailtoCustomer) == 1){
                $email_data['EmailTo'] = $data['BillingEmail'];
            }else{
                $email_data['EmailTo'] = Company::getEmail($account->CompanyId);
            }
            $email_data['BillingEmail'] = $data['BillingEmail'];
            $email_data['password'] = $password;
            $email_data['AccountName'] = $data['AccountName'];
            $email_data['Subject'] = "Customer Panel - Password Set";
            $status = sendMail('emails.admin.accounts.password_set', $email_data);
            $email_data['AccountID'] = $account->AccountID;
            $email_data['message'] = isset($status['body'])?$status['body']:'';
            $email_data['EmailTo'] = $data['BillingEmail'];
            email_log($email_data);
            $message = isset($status['message'])?' and '.$status['message']:'';

            return $message;
        }

    }

    public function get_outstanding_amount($id) {

            $data = Input::all();
            $account = Account::find($id);
            $companyID = User::get_companyID();
            $Invoiceids = $data['InvoiceIDs'];
            $outstanding = Account::getOutstandingInvoiceAmount($companyID, $account->AccountID, $Invoiceids, $account->RoundChargesAmount);
            $currency = Currency::getCurrency($account->CurrencyId);
            $outstandingtext = $outstanding . ' ' . $currency;
            echo json_encode(array("status" => "success", "message" => "", "outstanding" => $outstanding, "outstadingtext" => $outstandingtext));
    }
    public function paynow($id){
            $data = Input::all();
            $CompanyID = User::get_companyID();
            $CreatedBy = User::get_user_full_name();
            $Invoiceids = $data['InvoiceIDs'];
            $AccountPaymentProfileID = $data['AccountPaymentProfileID'];
            return AccountPaymentProfile::paynow($CompanyID, $id, $Invoiceids, $CreatedBy, $AccountPaymentProfileID);
    }

    public function bulk_mail(){

            $data = Input::all();
            $type = $data['type'];
            if ($type == 'CD') {
                $rules = array('isMerge' => 'required', 'Trunks' => 'required', 'Format' => 'required',);

                if (!isset($data['isMerge'])) {
                    $data['isMerge'] = 0;
                }

                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return json_validator_response($validator);
                }
            } else {
                unset($data['Format']);
                unset($data['isMerge']);
            }
            return bulk_mail($type, $data);
    }

    public function validate_cli(){

        $data = Input::all();
        $cli = $data['cli'];
        $status = $message = "";
        $status = "failed";
        if(isset($cli) && !empty($cli)){

            if(Account::validate_cli(trim($cli))){
                $status = "success";
                $message = "";
            }else{
                $message = "CLI Already exits";
            }
        }else{
            $message = "CLI is blank, Please enter valid cli";
        }

        return Response::json(array("status" => $status, "message" => $message));

    }
    public function validate_ip()
    {

        $data = Input::all();
        $ip = $data['ip'];
        $status = $message = "";
        $status = "failed";
        if (isset($ip) && !empty($ip)) {
            if (Account::validate_ip(trim($ip))) {
                $status = "success";
                $message = "";
            } else {
                $message = "IP Already exits";
            }
        } else {
            $message = "IP is blank, Please enter valid IP";
        }

        return Response::json(array("status" => $status, "message" => $message));
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
                    Tags::create(array('TagName' => $tag, 'CompanyID' => User::get_companyID(), 'TagType' => Tags::Account_tag));
                }
            }
            $SelectedIDs = $data['SelectedIDs'];
            unset($data['SelectedIDs']);
            if (Account::whereIn('AccountID', explode(',', $SelectedIDs))->update($data)) {
                return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Account."));
            }
    }
    /**
     * Update InboutRateTable
     */
    public function update_inbound_rate_table($AccountID){

        $data = Input::all();

        if(isset($data['InboudRateTableID'])) {

            $update = ["InboudRateTableID" => $data['InboudRateTableID']];
            if (empty($AccountID)) {
                return Response::json(array("status" => "failed", "message" => "Invalid Account"));
            }
            if (Account::find($AccountID)->update($update)) {
                return Response::json(array("status" => "success", "message" => "Inbound Rate Table Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Inbound Rate Table."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Found Updating Rate Table."));
        }

    }
}
