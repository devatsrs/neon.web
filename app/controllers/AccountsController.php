<?php

class AccountsController extends \BaseController {

    var $countries;
    var $model = 'Account';
    public function __construct() {
        $this->countries = Country::getCountryDropdownList();
    }

    public function ajax_datagrid($type) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        if(is_reseller()){
            $data['ResellerOwner'] = Reseller::getResellerID();
        }
        $data['iDisplayStart'] +=1;
        $userID = 0;
        if (User::is('AccountManager')) { // Account Manager
            $userID = $userID = User::get_userID();
        }elseif(User::is_admin() && isset($data['account_owners'])  && trim($data['account_owners']) > 0) {
            $userID = (int)$data['account_owners'];
        }
        $data['vendor_on_off'] = $data['vendor_on_off']== 'true'?1:0;
        $data['customer_on_off'] = $data['customer_on_off']== 'true'?1:0;
        $data['reseller_on_off'] = $data['reseller_on_off']== 'true'?1:0;
        $data['account_active'] = $data['account_active']== 'true'?1:0;
        $data['low_balance'] = $data['low_balance']== 'true'?1:0;
        //$data['account_name'] = $data['account_name']!= ''?$data['account_name']:'';
        //$data['tag'] = $data['tag']!= ''?$data['tag']:'null';
        //$data['account_number'] = $data['account_number']!= ''?$data['account_number']:0;
        //$data['contact_name'] = $data['contact_name']!= ''?$data['contact_name']:'';
        $columns = array('AccountID','Number','AccountName','Ownername','Phone','OutStandingAmount','UnbilledAmount','PermanentCredit','AccountExposure','Email','AccountID');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_GetAccounts (".$CompanyID.",".$userID.",".$data['vendor_on_off'].",".$data['customer_on_off'].",".$data['reseller_on_off'].",".$data['ResellerOwner'].",".$data['account_active'].",".$data['verification_status'].",'".$data['account_number']."','".$data['contact_name']."','".$data['account_name']."','".$data['tag']."','".$data["ipclitext"]."','".$data['low_balance']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            \Illuminate\Support\Facades\Log::info("Account query ".$query.',2)');
            $excel_data = json_decode(json_encode($excel_data),true);

            foreach($excel_data as $key => $item)
                unset($excel_data[$key]['Account Owner']);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Accounts.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Accounts.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Accounts', function ($excel) use ($excel_data) {
                $excel->sheet('Accounts', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';

        log::info($query);

        return DataTableSql::of($query)->make();
    }


    public function ajax_datagrid_PaymentProfiles($AccountID) {
        $data = Input::all();
        //$CompanyID = User::get_companyID();
        $PaymentGatewayName = '';
        $PaymentGatewayID='';
        $account = Account::find($AccountID);
        $CompanyID = $account->CompanyId;
        if(!empty($account->PaymentMethod)){
            $PaymentGatewayName = $account->PaymentMethod;
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($PaymentGatewayName);
        }
        $carddetail = AccountPaymentProfile::select("tblAccountPaymentProfile.Title","tblAccountPaymentProfile.Status","tblAccountPaymentProfile.isDefault",DB::raw("'".$PaymentGatewayName."' as gateway"),"created_at","AccountPaymentProfileID","tblAccountPaymentProfile.Options");
        $carddetail->where(["tblAccountPaymentProfile.CompanyID"=>$CompanyID])
            ->where(["tblAccountPaymentProfile.AccountID"=>$AccountID])
            ->where(["tblAccountPaymentProfile.PaymentGatewayID"=>$PaymentGatewayID]);

        return Datatables::of($carddetail)->make();
    }

    public function ajax_datagrid_PayoutAccounts($AccountID) {
        $PaymentGatewayName = '';
        $PaymentGatewayID='';
        $account = Account::find($AccountID);
        $CompanyID = $account->CompanyId;
        if(!empty($account->PayoutMethod)){
            $PaymentGatewayName = $account->PayoutMethod;
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($PaymentGatewayName);
        } else {
            $PaymentGatewayName = "Stripe";
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($PaymentGatewayName);
        }
        $payouts = AccountPayout::select("tblAccountPayout.Title","tblAccountPayout.Status","tblAccountPayout.isDefault",DB::raw("'".$PaymentGatewayName."' as gateway"),"created_at","tblAccountPayout.AccountPayoutID","tblAccountPayout.Options");
        $payouts->where(["tblAccountPayout.CompanyID"=>$CompanyID])
            ->where(["tblAccountPayout.AccountID"=>$AccountID])
            ->where(["tblAccountPayout.PaymentGatewayID"=>$PaymentGatewayID]);

        return Datatables::of($payouts)->make();
    }

    public function ajax_datagrid_account_logs($AccountID) {
        $account = Account::find($AccountID);
        $CompanyID = $account->CompanyId;
        //$CompanyID = User::get_companyID();
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $userID = 0;
        if (User::is('AccountManager')) { // Account Manager
            $userID = $userID = User::get_userID();
        }elseif(User::is_admin() && isset($data['account_owners'])  && trim($data['account_owners']) > 0) {
            $userID = (int)$data['account_owners'];
        }
        $columns = array('ColumnName','OldValue','NewValue','created_at','created_by');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_GetAccountLogs (".$CompanyID.",".$userID.",".$AccountID.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."')";

        return DataTableSql::of($query)->make();
    }

    public function ajax_template($id){
        $user = User::get_currentUser();
        return array('EmailFooter'=>($user->EmailFooter?$user->EmailFooter:''),'EmailTemplate'=>EmailTemplate::findOrfail($id));
    }

    public function ajax_getEmailTemplate($privacy, $type){
        $filter = array();
        /*if($type == EmailTemplate::ACCOUNT_TEMPLATE){
            $filter =array('Type'=>EmailTemplate::ACCOUNT_TEMPLATE);
        }elseif($type== EmailTemplate::RATESHEET_TEMPLATE){
            $filter =array('Type'=>EmailTemplate::RATESHEET_TEMPLATE);
        }*/
        $filter =array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE);
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
        $CompanyID = User::get_companyID();
        $trunks = CustomerTrunk::getTrunkDropdownIDListAll(); //$this->trunks;
        $accountTags = json_encode(Tags::getTagsArray(Tags::Account_tag));
        $account_owners = User::getOwnerUsersbyRole();
        $emailTemplates = array();
        $privacy = EmailTemplate::$privacy;
        $boards = CRMBoard::getBoards(CRMBoard::OpportunityBoard);
        $opportunityTags = json_encode(Tags::getTagsArray(Tags::Opportunity_tag));
        $accounts = Account::getAccountIDList();
        $templateoption = ['' => 'Select', 1 => 'Create new', 2 => 'Update existing'];
        $leadOrAccountID = '';
        $leadOrAccount = $accounts;
        $leadOrAccountCheck = 'account';
        $opportunitytags = json_encode(Tags::getTagsArray(Tags::Opportunity_tag));
        $bulk_type = 'accounts';
        $Currencies = Currency::getCurrencyDropdownIDList();

        $BillingClass = BillingClass::getBillingClassListByCompanyID($CompanyID);
        $timezones = TimeZone::getTimeZoneDropdownList();
        $rate_timezones = Timezones::getTimezonesIDList();
        $reseller_owners = Reseller::getDropdownIDList($CompanyID);
        $ROUTING_PROFILE = CompanyConfiguration::get('ROUTING_PROFILE',$CompanyID);
        return View::make('accounts.index', compact('account_owners', 'emailTemplates', 'templateoption', 'accounts', 'accountTags', 'privacy', 'type', 'trunks', 'rate_sheet_formates','boards','opportunityTags','accounts','leadOrAccount','leadOrAccountCheck','opportunitytags','leadOrAccountID','bulk_type','Currencies','BillingClass','timezones','reseller_owners','rate_timezones','ROUTING_PROFILE'));

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

        $company_id = User::get_companyID();
        $company = Company::find($company_id);

        $CompanyID = $company_id;
        $currencies = Currency::getCurrencyDropdownIDList();
        $timezones = TimeZone::getTimeZoneDropdownList();
        $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();
        //$BillingClass = BillingClass::getDropdownIDList($company_id);
        $BillingClass = BillingClass::getBillingClassListByCompanyID($company_id);
        $BillingStartDate=date('Y-m-d');
        $LastAccountNo =  '';
        $doc_status = Account::$doc_status;
        if(!User::is_admin()){
            unset($doc_status[Account::VERIFIED]);
        }
        $DiscountPlanVOICECALL = DiscountPlan::getDropdownIDListForRateType(RateType::VOICECALL_ID);
        $DiscountPlan = $DiscountPlanVOICECALL;
        $DiscountPlanDID = DiscountPlan::getDropdownIDListForRateType(RateType::DID_ID);
        $DiscountPlanPACKAGE = DiscountPlan::getDropdownIDListForRateType(RateType::PACKAGE_ID);
        $dynamicfields = Account::getDynamicfields('account',0);
        $reseller_owners = Reseller::getDropdownIDList($company_id);
        //As per new question call the routing profile model for fetch the routing profile list.
        $routingprofile = RoutingProfiles::orderBy('Name','Asc')->lists('Name', 'RoutingProfileID');
        $TaxRates = TaxRate::getTaxRateDropdownIDList($company_id);
        //$RoutingProfileToCustomer	 	 =	RoutingProfileToCustomer::where(["AccountID"=>$id])->first();
        //----------------------------------------------------------------------
        $reseller = is_reseller() ? Reseller::where('ChildCompanyID',$CompanyID)->first():[];

        $ROUTING_PROFILE = CompanyConfiguration::get('ROUTING_PROFILE',$company_id);
        return View::make('accounts.create', compact('account_owners', 'countries','LastAccountNo','doc_status','currencies','timezones','InvoiceTemplates','BillingStartDate','BillingClass','dynamicfields','company','reseller_owners','routingprofile','ROUTING_PROFILE', 'DiscountPlan','DiscountPlanPACKAGE','DiscountPlanDID','DiscountPlanVOICECALL','CompanyID','TaxRates','reseller'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /accounts
     *
     * @return Response
     */
    public function store() {
        $ServiceID = 0;
        $data = Input::all();
        $companyID = User::get_companyID();
        $ResellerOwner = empty($data['ResellerOwner']) ? 0 : $data['ResellerOwner'];



        if($ResellerOwner>0){
            $Reseller = Reseller::getResellerDetails($ResellerOwner);
            $ResellerCompanyID = $Reseller->ChildCompanyID;
            $ResellerUser = User::where('CompanyID', $ResellerCompanyID)->first();


            $ResellerUserID = $ResellerUser->UserID;
            $companyID=$ResellerCompanyID;
            $data['Owner'] = $ResellerUserID;
        }
        $RoutingProfileID='';
        if(isset($data['routingprofile'])){
            $RoutingProfileID=$data['routingprofile'];
        }
        $data['CompanyID']      = $companyID;
        $data['AccountType']    = 1;
        $data['IsVendor']       = isset($data['IsVendor']) ? 1 : 0;
        $data['IsCustomer']     = isset($data['IsCustomer']) ? 1 : 0;
        $data['IsAffiliateAccount'] = isset($data['IsAffiliateAccount']) ? 1 : 0;
        $data['IsReseller']     = isset($data['IsReseller']) ? 1 : 0;
        $data['Billing']        = isset($data['Billing']) ? 1 : 0;
        $data['created_by']     = User::get_user_full_name();
        $data['AccountType']    = 1;
        $data['AccountName']    = trim($data['AccountName']);
        $CustomerID = '';

        if (isset($data['accountgateway'])) {
            $AccountGateway = implode(',', array_filter(array_unique($data['accountgateway'])));
            unset($data['accountgateway']);
        }else{
            $AccountGateway = '';
        }

        if(!is_reseller() && $data['IsVendor'] == 0 && $data['IsCustomer'] == 0 && $data['IsReseller'] == 0)
            return Response::json(array("status" => "failed", "message" => "One of the option should be checked either Customer, Vendor or Partner."));

        if(is_reseller() && $data['IsCustomer'] == 0)
            return Response::json(array("status" => "failed", "message" => "Customer option should be checked."));

        if(!is_reseller() && $data['IsCustomer'] == 1 && $ResellerOwner == 0)
            return Response::json(array("status" => "failed", "message" => "Account Partner is required for customer"));

        /**
         * If Reseller on backend customer is on
         */
        /*if($data['IsReseller']==1){
            $data['IsCustomer']=1;
            $data['IsVendor']=0;
        }*/

        unset($data['ResellerOwner']);
        unset($data['routingprofile']);

        //when account varification is off in company setting then varified the account by default.
        $AccountVerification =  CompanySetting::getKeyVal('AccountVerification');

        if ( $AccountVerification != CompanySetting::ACCOUT_VARIFICATION_ON ) {
            $data['VerificationStatus'] = Account::VERIFIED;
        }


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

        unset($data['DataTables_Table_0_length']);
        $ManualBilling = isset($data['BillingCycleType']) && $data['BillingCycleType'] == 'manual'?1:0;
        if(Company::isBillingLicence() && $data['Billing'] == 1) {
            Account::$rules['BillingType'] = 'required';
            //Account::$rules['BillingTimezone'] = 'required';
            Account::$rules['BillingCycleType'] = 'required';
            Account::$rules['BillingClassID'] = 'required';
            if(isset($data['BillingCycleValue'])){
                Account::$rules['BillingCycleValue'] = 'required';
            }
            if($ManualBilling ==0) {
                Account::$rules['BillingStartDate'] = 'required';
            }

        }

        Account::$rules['AccountName'] = 'required|unique:tblAccount,AccountName,NULL,CompanyID,AccountType,1';
        Account::$rules['Number'] = 'required|unique:tblAccount,Number,NULL,CompanyID';
        if ($data['IsAffiliateAccount'] == 1) {
            Account::$rules['CommissionPercentage'] = 'required';
        }

        if(DynamicFields::where(['CompanyID' => getParentCompanyIdIfReseller($companyID), 'Type' => 'account', 'FieldSlug' => 'vendorname', 'Status' => 1])->count() > 0 && $data['IsVendor'] == 1) {
            Account::$rules['vendorname'] = 'required';
            Account::$messages['vendorname.required'] = 'The Vendor Name field is required.';
        }

        $validator = Validator::make($data, Account::$rules, Account::$messages);
        $validator->setAttributeNames(['AccountName' => 'Account Name']);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if($data['AutoPaymentSetting']!='never'){
            if($data['AutoPayMethod']==0){
                return Response::json(array("status" => "failed", "message" => "Please Select Auto Pay Method."));
            }

        }

        if(isset($data['vendorname'])){
            $VendorName = $data['vendorname'];
            unset($data['vendorname']);
        }else{
            $VendorName = '';
        }


        if (isset($data['pbxaccountstatus'])) {
            $pbxaccountstatus = $data['pbxaccountstatus'];
            unset($data['pbxaccountstatus']);
        }else{
            $pbxaccountstatus = 0;
        }

        if (isset($data['CustomerID'])) {
            $CustomerID = $data['CustomerID'];
            unset($data['CustomerID']);
        }else{
            $CustomerID = '';
        }

        if (isset($data['autoblock'])) {
            $autoblock = $data['autoblock'];
            unset($data['autoblock']);
        }else{
            $autoblock = 0;
        }

        if (isset($data['COCNumber'])) {
            $COCNumber = $data['COCNumber'];
            unset($data['COCNumber']);
        }else{
            $COCNumber = '';
        }

        if (isset($data['PONumber'])) {
            $PONumber = $data['PONumber'];
            unset($data['PONumber']);
        }else{
            $PONumber = '';
        }

        if (isset($data['AccountHolder'])) {
            $AccountHolder = $data['AccountHolder'];
            unset($data['AccountHolder']);
        }else{
            $AccountHolder = '';
        }

        if (isset($data['RegisterDutchFoundation'])) {
            $RegisterDutchFoundation = $data['RegisterDutchFoundation'];
            unset($data['RegisterDutchFoundation']);
        }else{
            $RegisterDutchFoundation = 0;
        }

        if (isset($data['DutchProvider'])) {
            $DutchProvider = $data['DutchProvider'];
            unset($data['DutchProvider']);
        }else{
            $DutchProvider = 0;
        }

        if (isset($data['DirectDebit'])) {
            $DirectDebit = $data['DirectDebit'];
            unset($data['DirectDebit']);
        }else{
            $DirectDebit = 0;
        }

        $rules = array(
            'TopupAmount' => 'numeric|regex:/^\d*(\.\d{2})?$/',
            'AutoOutPayment' => 'numeric',
            'OutPaymentThreshold' => 'numeric',
            'OutPaymentAmount' => 'numeric|regex:/^\d*(\.\d{2})?$/',

        );


        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $ErrorMessage = "";

        if (isset($data['AutoOutPayment']) && $data['AutoOutPayment'] == 1) {

            if (empty($data['OutPaymentAmount']))
                $ErrorMessage .= "The OutPaymentAmount field is required <br> ";

            if (empty($data['OutPaymentThreshold']))
                $ErrorMessage .= "The OutPaymentThreshold field is required <br> ";
        }

        if (isset($data['AutoTopup']) && $data['AutoTopup'] == 1) {

            if (empty($data['MinThreshold']))
                $ErrorMessage .= "The MinThreshold field is required<br> ";

            if (empty($data['TopupAmount']))
                $ErrorMessage .= "The TopupAmount field is required<br> ";

        }


        if ($ErrorMessage != "")
            return Response::json(array("status" => "failed", "message" => $ErrorMessage));

        $AccountPaymentAutomation['AutoTopup'] = (isset($data['AutoTopup']) ? $data['AutoTopup'] : "");
        $AccountPaymentAutomation['MinThreshold'] = $data['MinThreshold'];
        $AccountPaymentAutomation['AutoOutpayment'] = (isset($data['AutoOutPayment']) ? $data['AutoOutPayment'] : "");
        $AccountPaymentAutomation['OutPaymentThreshold'] = $data['OutPaymentThreshold'];
        $AccountPaymentAutomation['OutPaymentAmount'] = $data['OutPaymentAmount'];
        $AccountPaymentAutomation['TopupAmount'] = $data['TopupAmount'];


        unset($data['AutoTopup']);
        unset($data['AutoOutPayment']);
        unset($data['MinThreshold']);
        unset($data['TopupAmount']);
        unset($data['OutPaymentThreshold']);
        unset($data['OutPaymentAmount']);


        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(empty($data['DifferentBillingAddress'])) {
            $data['BillingAddress1'] = $data['Address1'];
            $data['BillingAddress2'] = $data['Address2'];
            $data['BillingAddress3'] = $data['Address3'];
            $data['BillingCity']     = $data['City'];
            $data['BillingPostCode'] = $data['PostCode'];
            $data['BillingCountry']  = $data['Country'];
        }
        $data['TaxRateID'] = implode(',', array_unique($data['TaxRateID']));

        if ($account = Account::create($data)) {

            $DynamicData = array();
            $DynamicData['CompanyID']= $companyID;
            $DynamicData['AccountID']= $account->AccountID;

            $AccountPaymentAutomation['AccountID'] = $DynamicData['AccountID'];
            AccountPaymentAutomation::create($AccountPaymentAutomation);
            //
            if($RoutingProfileID!=''){
                $RoutingProfileToCustomer	 	 =	RoutingProfileToCustomer::where(["AccountID"=>$account->AccountID])->first();

                if(isset($RoutingProfileToCustomer->AccountID)){
                    $routingprofile_table=array();
                    $routingprofile_table['RoutingProfileID'] = $RoutingProfileID;
                    RoutingProfileToCustomer::where(['AccountID'=>$account->AccountID])->update($routingprofile_table);
                }else{
                    if($RoutingProfileID!=''){
                        $routingprofile_table=array();
                        $routingprofile_table['RoutingProfileID'] = $RoutingProfileID;
                        $routingprofile_table['AccountID'] = $account->AccountID;
                        RoutingProfileToCustomer::insert($routingprofile_table);
                    }
                }
                unset($data['routingprofile']);
            }

            if(!empty($AccountGateway)){
                $DynamicData['FieldName'] = 'accountgateway';
                $DynamicData['FieldValue']= $AccountGateway;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(!empty($VendorName)){
                $DynamicData['FieldName'] = 'vendorname';
                $DynamicData['FieldValue']= $VendorName;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(!empty($CustomerID)){
                $DynamicData['FieldName'] = 'CustomerID';
                $DynamicData['FieldValue']= $CustomerID;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }

            if(isset($pbxaccountstatus)){
                $DynamicData['FieldName'] = 'pbxaccountstatus';
                $DynamicData['FieldValue']= $pbxaccountstatus;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($autoblock)){
                $DynamicData['FieldName'] = 'autoblock';
                $DynamicData['FieldValue']= $autoblock;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($COCNumber)){
                $DynamicData['FieldName'] = 'COCNumber';
                $DynamicData['FieldValue']= $COCNumber;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($PONumber)){
                $DynamicData['FieldName'] = 'PONumber';
                $DynamicData['FieldValue']= $PONumber;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($AccountHolder)){
                $DynamicData['FieldName'] = 'AccountHolder';
                $DynamicData['FieldValue']= $AccountHolder;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($RegisterDutchFoundation)){
                $DynamicData['FieldName'] = 'RegisterDutchFoundation';
                $DynamicData['FieldValue']= $RegisterDutchFoundation;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($DutchProvider)){
                $DynamicData['FieldName'] = 'DutchProvider';
                $DynamicData['FieldValue']= $DutchProvider;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($DirectDebit)){
                $DynamicData['FieldName'] = 'DirectDebit';
                $DynamicData['FieldValue']= $DirectDebit;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }


            if($data['Billing'] == 1) {
                if($ManualBilling ==0) {
                    if ($data['BillingStartDate'] == $data['NextInvoiceDate']) {
                        $data['NextChargeDate'] = $data['BillingStartDate'];
                    } else {
                        $BillingStartDate = strtotime($data['BillingStartDate']);
                        $data['BillingCycleValue'] = empty($data['BillingCycleValue']) ? '' : $data['BillingCycleValue'];
                        $NextBillingDate = next_billing_date($data['BillingCycleType'], $data['BillingCycleValue'], $BillingStartDate);
                        $data['NextChargeDate'] = date('Y-m-d', strtotime('-1 day', strtotime($NextBillingDate)));;
                    }
                }

                AccountBilling::insertUpdateBilling($account->AccountID, $data,$ServiceID);
                if($ManualBilling ==0) {
                    AccountBilling::storeFirstTimeInvoicePeriod($account->AccountID, $ServiceID);
                }

                $AccountPeriod = AccountBilling::getCurrentPeriod($account->AccountID, date('Y-m-d'),$ServiceID);
                $OutboundDiscountPlan = empty($data['DiscountPlanID']) ? '' : $data['DiscountPlanID'];
                $InboundDiscountPlan = empty($data['InboundDiscountPlanID']) ? '' : $data['InboundDiscountPlanID'];
                $PackageDiscountPlan = empty($data['PackageDiscountPlanID']) ? '' : $data['PackageDiscountPlanID'];

                if(!empty($AccountPeriod)) {
                    $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                    $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                    $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                    $ServiceID=0;
                    $AccountSubscriptionID = 0;
                    $AccountName='';
                    $AccountCLI='';
                    $SubscriptionDiscountPlanID=0;
                    $AccountServiceID=0;

                    AccountDiscountPlan::addUpdateDiscountPlan($account->AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff,$ServiceID,$AccountServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                    AccountDiscountPlan::addUpdateDiscountPlan($account->AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff,$ServiceID,$AccountServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                    AccountDiscountPlan::addUpdateDiscountPlan($account->AccountID, $PackageDiscountPlan, AccountDiscountPlan::PACKAGE, $billdays, $DayDiff,$ServiceID,$AccountServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                }
            }

            if (trim(Input::get('Number')) == '') {
                CompanySetting::setKeyVal('LastAccountNo', $account->Number);
            }

            $AccountDetails=array();
            //$AccountDetails['ResellerOwner'] = $ResellerOwner;
            $AccountDetails['AccountID'] = $account->AccountID;
            AccountDetails::create($AccountDetails);


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
    public function show_old($id) {

        $account = Account::find($id);
        $AccountBilling = AccountBilling::getBilling($id,0);
        $companyID = User::get_companyID();
        $account_owner = User::find($account->Owner);
        $notes = Note::where(["CompanyID" => $companyID, "AccountID" => $id])->orderBy('NoteID', 'desc')->get();
        $contacts = Contact::where(["CompanyID" => $companyID, "Owner" => $id])->orderBy('FirstName', 'asc')->get();
        $verificationflag = AccountApprovalList::isVerfiable($id);
        $outstanding = Account::getOutstandingAmount($companyID, $account->AccountID, get_round_decimal_places($account->AccountID));
        $currency = Currency::getCurrencySymbol($account->CurrencyId);
        $activity_type = AccountActivity::$activity_type;
        $activity_status = [1 => 'Open', 2 => 'Closed'];
        return View::make('accounts.show', compact('account', 'account_owner', 'notes', 'contacts', 'verificationflag', 'outstanding', 'currency', 'activity_type', 'activity_status','AccountBilling'));
    }


    public function show($id) {
        $account 					= 	 Account::find($id);
        $companyID 					= 	 User::get_companyID();

        //get account contacts
        $contacts 					= 	 Contact::where(["CompanyID" => $companyID, "Owner" => $id])->orderBy('FirstName', 'asc')->get();
        //get account time line data
        $data['iDisplayStart'] 	    =	 0;
        $data['iDisplayLength']     =    10;
        $data['AccountID']          =    $id;
        $data['GUID']               =    GUID::generate();
        $PageNumber                 =    ceil($data['iDisplayStart']/$data['iDisplayLength']);
        $RowsPerPage                =    $data['iDisplayLength'];
        $message 					= 	 '';
        $response_timeline 			= 	 NeonAPI::request('account/GetTimeLine',$data,false,true);
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
                \Illuminate\Support\Facades\Log::info("Account 401 ");
                \Illuminate\Support\Facades\Log::info(print_r($response_timeline,true));
                //return	Redirect::to('/logout');
            }
            if(isset($response_timeline->error) && $response_timeline->error=='token_expired'){
                \Illuminate\Support\Facades\Log::info("Account token_expired ");
                \Illuminate\Support\Facades\Log::info(print_r($response_timeline,true));
                //Redirect::to('/login');
            }
            $message = json_response_api($response_timeline,false,false);
        }

        $vendor   = $account->IsVendor?1:0;
        $Customer = $account->IsCustomer?1:0;
        $Reseller = $account->IsReseller?1:0;
        $ResellerOwner=0;
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        if(is_reseller()){
            $data['ResellerOwner'] = Reseller::getResellerID();
        }

        //get account card data
        $sql 						= 	 "call prc_GetAccounts (".$companyID.",0,'".$vendor."','".$Customer."','".$Reseller."','".$ResellerOwner."','".$account->Status."','".$account->VerificationStatus."','".$account->Number."','','".$account->AccountName."','".$account->tags."','',0,1 ,1,'AccountName','asc',0)";
        Log::info("Show My Sql Query:" . $sql);
        $Account_card  				= 	 DB::select($sql);
        $Account_card  				=	 array_shift($Account_card);

        $outstanding 				= 	 Account::getOutstandingAmount($companyID, $account->AccountID, get_round_decimal_places($account->AccountID));
        $account_owners 			= 	 User::getUserIDList();
        //$Board 						=	 CRMBoard::getTaskBoard();



        //$emailTemplates 			= 	 $this->ajax_getEmailTemplate(EmailTemplate::PRIVACY_OFF,EmailTemplate::ACCOUNT_TEMPLATE);
        $emailTemplates 			= 	EmailTemplate::GetUserDefinedTemplates();
        $random_token				=	 get_random_number();

        //Backup code for getting extensions from api
        $response_api_extensions 	=   Get_Api_file_extentsions();
        //if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}
        $response_extensions			=	json_encode($response_api_extensions['allowed_extensions']);

        //all users email address
        $users						=	 USer::select('EmailAddress')->lists('EmailAddress');
        $users						=	 json_encode(array_merge(array(""),$users));

        //Account oppertunity data
        $boards 					= 	 CRMBoard::getTaskBoard(); //opperturnity variables start
        if(count($boards)<1){

            $message 				= 	 "No Task Board Found. PLease create task board first";
        }else{
            $boards					=	  $boards[0];
        }
        $accounts 					= 	 Account::getAccountIDList();
        $leadOrAccountID 			= 	 '';
        $leadOrAccount 				= 	 $accounts;
        $leadOrAccountCheck 		= 	 'account';
        $opportunitytags 			= 	 json_encode(Tags::getTagsArray(Tags::Opportunity_tag));

        /* if (isset($response->status) && $response->status != 'failed') {
            $response = $response->data;
        }else{
            if(isset($response->Code) && ($response->Code==400 || $response->Code==401)){
                return	Redirect::to('/logout');
            }
            else{
                $message	    =	$response->message['error'][0];
                 Session::set('error_message',$message);
            }
        }			*/
        $FromEmails	 				= 	TicketGroups::GetGroupsFrom();
        $max_file_size				=	get_max_file_size();
        $per_scroll 				=   $data['iDisplayLength'];
        $current_user_title 		= 	Auth::user()->FirstName.' '.Auth::user()->LastName;
        $ShowTickets				=   SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$freshdeskSlug,$companyID); //freshdesk
        $SystemTickets				=   Tickets::CheckTicketLicense();

        return View::make('accounts.view', compact('response_timeline','account', 'contacts', 'verificationflag', 'outstanding','response','message','current_user_title','per_scroll','Account_card','account_owners','Board','emailTemplates','response_extensions','random_token','users','max_file_size','leadOrAccount','leadOrAccountCheck','opportunitytags','leadOrAccountID','accounts','boards','data','ShowTickets','SystemTickets','FromEmails'));
    }


    public function log($id) {
        $account = Account::find($id);
        $accounts = Account::getAccountIDList();
        return View::make('accounts.accounts_audit_logs', compact('account','accounts'));
    }


    /**
     * Show the form for editing the specified resource.
     * GET /accounts/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */

    public function GetTimeLineSrollData($id,$start)
    {
        $data 					   = 	Input::all();
        $data['iDisplayStart'] 	   =	$start;
        $data['iDisplayLength']    =    10;
        $data['AccountID']         =    $id;
        $response 				   = 	NeonAPI::request('account/GetTimeLine',$data,false);

        if($response->status!='failed'){
            if(!isset($response->data))
            {
                return  Response::json(array("status" => "failed", "message" => "No Result Found","scroll"=>"end"));
            }
            else
            {
                $response =  $response->data;
            }
        }
        else{
            return json_response_api($response,false,true);
        }

        $key 					= 	$data['scrol'];
        $current_user_title 	= 	Auth::user()->FirstName.' '.Auth::user()->LastName;
        return View::make('accounts.show_ajax', compact('response','current_user_title','key'));
    }

    function AjaxConversations($id){
        if(empty($id) || !is_numeric($id)){
            return '<div>No conversation found.</div>';
        }
        $data 			= 	Input::all();
        $data['id']	=	$id;
        $response 		= 	 NeonAPI::request('account/GetConversations',$data,true,true);
        if($response['status']=='failed'){
            return json_response_api($response,false,true);
        }else{
            return View::make('accounts.conversations', compact("response","data"));
        }
    }

    public function edit($id) {

        Payment::multiLang_init();
        $ServiceID = 0;
        $account = Account::find($id);
        $companyID = $account->CompanyId;
        if(is_reseller() && $companyID != User::get_companyID())
            return  Response::json(array("status" => "failed", "message" => "Invalid Data."));
        //$companyID = User::get_companyID();
        $account_owners = User::getOwnerUsersbyRole();
        $countries = $this->countries;
        $tags = json_encode(Tags::getTagsArray());
        $products = Product::getProductDropdownList($companyID);
        $taxes = TaxRate::getTaxRateDropdownIDListForInvoice(0,$companyID);
        $currencies = Currency::getCurrencyDropdownIDList();
        $timezones = TimeZone::getTimeZoneDropdownList();
        $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();
        //$BillingClass = BillingClass::getDropdownIDList($companyID);
        $BillingClass = BillingClass::getBillingClassListByCompanyID($companyID);


        $boards = CRMBoard::getBoards(CRMBoard::OpportunityBoard);
        $opportunityTags = json_encode(Tags::getTagsArray(Tags::Opportunity_tag));
        $accounts = Account::getAccountList();

        $AccountApproval = AccountApproval::getList($id);
        $doc_status = Account::$doc_status;
        $verificationflag = AccountApprovalList::isVerfiable($id);
        $invoice_count = Account::getInvoiceCount($id);
        $all_invoice_count = Account::getAllInvoiceCount($id);
        if(!User::is_admin() &&   $verificationflag == false && $account->VerificationStatus != Account::VERIFIED){
            unset($doc_status[Account::VERIFIED]);
        }
        $leadOrAccountID = $id;
        $leadOrAccount = $accounts;
        $leadOrAccountCheck = 'account';
        $opportunitytags = json_encode(Tags::getTagsArray(Tags::Opportunity_tag));
        $DiscountPlanVOICECALL = DiscountPlan::getDropdownIDListForRateType(RateType::VOICECALL_ID);
        $DiscountPlan = $DiscountPlanVOICECALL;
        $DiscountPlanDID = DiscountPlan::getDropdownIDListForRateType(RateType::DID_ID);
        $DiscountPlanPACKAGE = DiscountPlan::getDropdownIDListForRateType(RateType::PACKAGE_ID);
        $AccountBilling =  AccountBilling::getBilling($id,$ServiceID);
        $AccountNextBilling =  AccountNextBilling::getBilling($id,$ServiceID);
        $decimal_places = get_round_decimal_places($id);
        $rate_table = RateTable::getRateTableList(array('CurrencyID'=>$account->CurrencyId));
        $services = Service::getAllServices($companyID);

        $billing_disable = $hiden_class= '';
        if($invoice_count > 0 || AccountDiscountPlan::checkDiscountPlan($id) > 0){
            $billing_disable = 'disabled';
        }
        if(isset($AccountBilling->BillingCycleType)){
            $hiden_class= 'hidden';
            if(empty($AccountBilling->BillingStartDate)){
                $AccountBilling->BillingStartDate = $AccountBilling->LastInvoiceDate;
            }
        }

        $ResellerCount = Reseller::where(['AccountID'=>$id,'Status'=>1])->count();

        $dynamicfields = Account::getDynamicfields('account',$id);
        //Log::info("Count for Dynamic fields for Account ." . $id . ' ' . count($dynamicfields));
        $accountdetails = AccountDetails::where(['AccountID'=>$id])->first();
        $reseller_owners = Reseller::getDropdownIDList(User::get_companyID());
        $accountreseller = Reseller::where('ChildCompanyID',$companyID)->pluck('ResellerID');

        $DiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'Type'=>AccountDiscountPlan::OUTBOUND,'ServiceID'=>0,'AccountSubscriptionID'=>0,'SubscriptionDiscountPlanID'=>0))->pluck('DiscountPlanID');
        $InboundDiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'Type'=>AccountDiscountPlan::INBOUND,'ServiceID'=>0,'AccountSubscriptionID'=>0,'SubscriptionDiscountPlanID'=>0))->pluck('DiscountPlanID');
        $PackageDiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'Type'=>AccountDiscountPlan::PACKAGE,'ServiceID'=>0,'AccountSubscriptionID'=>0,'SubscriptionDiscountPlanID'=>0))->pluck('DiscountPlanID');

        //As per new question call the routing profile model for fetch the routing profile list.
        $RoutingProfileToCustomer	 	 =	RoutingProfileToCustomer::where(["AccountID"=>$id])->first();
        //----------------------------------------------------------------------

        $UserCompanyID = User::get_companyID();
        $routingprofile = RoutingProfiles::orderBy('Name','Asc')->lists('Name', 'RoutingProfileID');
        $ROUTING_PROFILE = CompanyConfiguration::get('ROUTING_PROFILE', $UserCompanyID);
        $AccountPaymentAutomation = AccountPaymentAutomation::where('AccountID',$id)->first();
        $Packages = Package::getDropdownIDListByCompany($companyID);
        $AffiliateAccount = Account::getAffiliateAccount();

        $AccountRateTable = AccountRateTable::where(['AccountID' => $id])->first();

        $AccountAccessRateTableID = isset($AccountRateTable->AccessRateTableID) ? $AccountRateTable->AccessRateTableID : '';
        $AccountPackageRateTableID = isset($AccountRateTable->PackageRateTableID) ? $AccountRateTable->PackageRateTableID : '';
        $AccountTerminationRateTableID = isset($AccountRateTable->TerminationRateTableID) ? $AccountRateTable->TerminationRateTableID : '';
        $rate_table = RateTable::getRateTableList([
            'types' => [RateGenerator::DID],
            'NotVendor' => true,
            'CompanyID' => $companyID
        ]);
        $termination_rate_table = RateTable::getRateTableList([
            'types' => [RateGenerator::VoiceCall],
            'NotVendor' => true,
            'CompanyID' => $companyID
        ]);
        $package_rate_table = RateTable::getRateTableList([
            'types' => [RateGenerator::Package],
            'NotVendor' => true,
            'CompanyID' => $companyID
        ]);
        $reseller = is_reseller() ? Reseller::where('ChildCompanyID',$companyID)->first():[];
        return View::make('accounts.edit', compact('account','AffiliateAccount', 'AccountPaymentAutomation' ,'account_owners', 'countries','AccountApproval','doc_status','currencies','timezones','taxrates','verificationflag','InvoiceTemplates','invoice_count','all_invoice_count','tags','products','taxes','opportunityTags','boards','accounts','leadOrAccountID','leadOrAccount','leadOrAccountCheck','opportunitytags',
            'Packages','DiscountPlanVOICECALL','DiscountPlanDID','DiscountPlanPACKAGE','DiscountPlan','DiscountPlanID','InboundDiscountPlanID','PackageDiscountPlanID','AccountBilling','AccountNextBilling','BillingClass','decimal_places','rate_table','services','ServiceID','billing_disable','hiden_class','dynamicfields','ResellerCount','accountdetails','reseller_owners','accountreseller','routingprofile','RoutingProfileToCustomer','ROUTING_PROFILE','reseller','AccountAccessRateTableID','AccountPackageRateTableID','AccountTerminationRateTableID','termination_rate_table','package_rate_table'));
    }

    /**
     * Update the specified resource in storage.
     * PUT /accounts/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        $ServiceID = 0;
        $data = Input::all();
        $account = Account::find($id);
        //$companyID = User::get_companyID();
        $companyID = $account->CompanyId;
        //$ResellerOwner = empty($data['ResellerOwner']) ? 0 : $data['ResellerOwner'];



        /*if($ResellerOwner>0){
            $Reseller = Reseller::getResellerDetails($ResellerOwner);
            $ResellerCompanyID = $Reseller->ChildCompanyID;
            $ResellerUser =User::where('CompanyID',$ResellerCompanyID)->first();
            $ResellerUserID = $ResellerUser->UserID;
            $companyID=$ResellerCompanyID;
            $data['Owner'] = $ResellerUserID;
        }*/
        if(isset($data['tags'])){
            Tags::insertNewTags(['tags'=>$data['tags'],'TagType'=>Tags::Account_tag]);
        }
        //$DiscountPlanID = $data['DiscountPlanID'];
        //$InboundDiscountPlanID = $data['InboundDiscountPlanID'];

        if(isset($data['routingprofile'])){

            //$RoutingProfileToCustomer = RoutingProfileToCustomer::where('RoutingProfileID',$data['routingprofile'])->first();
            $RoutingProfileToCustomer	 	 =	RoutingProfileToCustomer::where(["AccountID"=>$id])->first();

            if(isset($RoutingProfileToCustomer->AccountID)){
                $routingprofile_table=array();
                $routingprofile_table['RoutingProfileID'] = $data['routingprofile'];
                RoutingProfileToCustomer::where(['AccountID'=>$id])->update($routingprofile_table);
            }else{
                if($data['routingprofile']!=''){
                    $routingprofile_table=array();
                    $routingprofile_table['RoutingProfileID'] = $data['routingprofile'];
                    $routingprofile_table['AccountID'] = $id;

                    RoutingProfileToCustomer::insert($routingprofile_table);
                }
                //
                // print_r($data);echo '-N--';
            }
            unset($data['routingprofile']);
            // die();
        }

        // assign account to routin profile
        //
        $AccountDetails=array();
        $AccountDetails['CustomerPaymentAdd'] = isset($data['CustomerPaymentAdd']) ? 1 : 0;
        //$AccountDetails['ResellerOwner'] = $ResellerOwner;
        $AccountDetails['AccountID'] = $id;
        unset($data['CustomerPaymentAdd']);
        unset($data['ResellerOwner']);

        $message = $password = "";
        $data['CompanyID'] = $companyID;
        $data['IsVendor'] = isset($data['IsVendor']) ? 1 : 0;
        $data['IsCustomer'] = isset($data['IsCustomer']) ? 1 : 0;
        $data['IsReseller'] = isset($data['IsReseller']) ? 1 : 0;
        $data['IsAffiliateAccount'] = isset($data['IsAffiliateAccount']) ? 1 : 0;
        $data['Billing'] = isset($data['Billing']) ? 1 : 0;
        $data['updated_by'] = User::get_user_full_name();
        $data['AccountName'] = trim($data['AccountName']);
        $data['ShowAllPaymentMethod'] = isset($data['ShowAllPaymentMethod']) ? 1 : 0;
        $data['DisplayRates'] = isset($data['DisplayRates']) ? 1 : 0;

        /*if($data['IsReseller']==1){
            $data['IsCustomer']=1;
            $data['IsVendor']=0;
        }*/

        if(!is_reseller() && $data['IsVendor'] == 0 && $data['IsCustomer'] == 0 && $data['IsReseller'] == 0)
            return Response::json(array("status" => "failed", "message" => "One of the option should be checked either Customer, Vendor or Partner."));

        if(is_reseller() && $data['IsCustomer'] == 0)
            return Response::json(array("status" => "failed", "message" => "Customer option should be on."));

        $shipping = array('firstName'=>$account['FirstName'],
            'lastName'=>$account['LastName'],
            'address'=>$data['Address1'],
            'city'=>$data['City'],
            'state'=>$account['state'],
            'zip'=>$data['PostCode'],
            'country'=>$data['Country'],
            'phoneNumber'=>$account['Mobile']);
        unset($data['table-4_length']);
        unset($data['table-subscription_length']);
        unset($data['table-additionalcharge_length']);
        unset($data['table-service_length']);
        unset($data['cardID']);
        //unset($data['DiscountPlanID']);
        //unset($data['InboundDiscountPlanID']);
        unset($data['DataTables_Table_0_length']);

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
                //$data['password']       = Hash::make($password);
                $data['password']       = Crypt::encrypt($password);
            }
        }
        $data['Number'] = trim($data['Number']);
        $ManualBilling = isset($data['BillingCycleType']) && $data['BillingCycleType'] == 'manual'?1:0;

        if(Company::isBillingLicence() && $data['Billing'] == 1) {
            Account::$rules['BillingType'] = 'required';
            //Account::$rules['BillingTimezone'] = 'required';
            Account::$rules['BillingCycleType'] = 'required';
            Account::$rules['BillingClassID'] = 'required';
            if(isset($data['BillingCycleValue'])){
                Account::$rules['BillingCycleValue'] = 'required';
            }
            if($ManualBilling == 0){
                Account::$rules['BillingStartDate'] = 'required';
            }
        }
        Account::$rules['AccountName'] = 'required|unique:tblAccount,AccountName,' . $account->AccountID . ',AccountID,AccountType,1';
        Account::$rules['Number'] = 'required|unique:tblAccount,Number,' . $account->AccountID . ',AccountID';

        if ($data['IsAffiliateAccount'] == 1) {
            Account::$rules['CommissionPercentage'] = 'required';
        }
        if(DynamicFields::where(['CompanyID' => $companyID, 'Type' => 'account', 'FieldSlug' => 'vendorname', 'Status' => 1])->count() > 0 && $data['IsVendor'] == 1) {
            Account::$rules['vendorname'] = 'required';
            Account::$messages['vendorname.required'] = 'The Vendor Name field is required.';
        }
        $validator = Validator::make($data, Account::$rules,Account::$messages);

        $validator->setAttributeNames(['AccountName' => 'Account Name']);
        if ($validator->fails()) {
            return json_validator_response($validator);
            exit;
        }

        $invoice_count = Account::getInvoiceCount($id);
        if($invoice_count == 0 && $ManualBilling == 0){
            $data['LastInvoiceDate'] = $data['BillingStartDate'];
            $data['LastChargeDate'] = $data['BillingStartDate'];
            if($data['BillingStartDate']==$data['NextInvoiceDate']){
                $data['NextChargeDate']=$data['BillingStartDate'];
            }else{
                $BillingStartDate = strtotime($data['BillingStartDate']);
                $data['BillingCycleValue'] = empty($data['BillingCycleValue']) ? '' : $data['BillingCycleValue'];
                $NextBillingDate = next_billing_date($data['BillingCycleType'], $data['BillingCycleValue'], $BillingStartDate);
                $data['NextChargeDate'] = date('Y-m-d', strtotime('-1 day', strtotime($NextBillingDate)));;
            }
        }

        if (isset($data['accountgateway'])) {
            $AccountGateway = implode(',', array_filter(array_unique($data['accountgateway'])));
            unset($data['accountgateway']);
        }else{
            $AccountGateway = '';
        }

        if (isset($data['vendorname'])) {
            $VendorName = $data['vendorname'];
            unset($data['vendorname']);
        }else{
            $VendorName = '';
        }

        if (isset($data['pbxaccountstatus'])) {
            $pbxaccountstatus = $data['pbxaccountstatus'];
            unset($data['pbxaccountstatus']);
        }else{
            $pbxaccountstatus = 0;
        }

        if (isset($data['autoblock'])) {
            $autoblock = $data['autoblock'];
            unset($data['autoblock']);
        }else{
            $autoblock = 0;
        }

        if (isset($data['COCNumber'])) {
            $COCNumber = $data['COCNumber'];
            unset($data['COCNumber']);
        }else{
            $COCNumber = '';
        }

        if (isset($data['CustomerID'])) {
            $CustomerID = $data['CustomerID'];
            unset($data['CustomerID']);
        }else{
            $CustomerID = '';
        }

        if (isset($data['PONumber'])) {
            $PONumber = $data['PONumber'];
            unset($data['PONumber']);
        }else{
            $PONumber = '';
        }

        if (isset($data['AccountHolder'])) {
            $AccountHolder = $data['AccountHolder'];
            unset($data['AccountHolder']);
        }else{
            $AccountHolder = '';
        }

        if (isset($data['RegisterDutchFoundation'])) {
            $RegisterDutchFoundation = $data['RegisterDutchFoundation'];
            unset($data['RegisterDutchFoundation']);
        }else{
            $RegisterDutchFoundation = 0;
        }

        if (isset($data['DutchProvider'])) {
            $DutchProvider = $data['DutchProvider'];
            unset($data['DutchProvider']);
        }else{
            $DutchProvider = 0;
        }

        if (isset($data['DirectDebit'])) {
            $DirectDebit = $data['DirectDebit'];
            unset($data['DirectDebit']);
        }else{
            $DirectDebit = 0;
        }

        /*$test=array();
        $test['BillingStartDate']=$data['BillingStartDate'];
        $test['BillingCycleType']=$data['BillingCycleType'];
        $test['LastInvoiceDate']=$data['LastInvoiceDate'];
        $test['LastChargeDate']=$data['LastChargeDate'];
        $test['NextInvoiceDate']=$data['NextInvoiceDate'];
        $test['NextChargeDate']=$data['NextChargeDate'];
        log::info(print_r($test,true));*/

        if($data['Billing'] == 1) {
            if($ManualBilling == 0){
                if ($data['NextInvoiceDate'] < $data['LastInvoiceDate']) {
                    return Response::json(array("status" => "failed", "message" => "Please Select Appropriate Date."));
                }
                if ($data['NextChargeDate'] < $data['LastChargeDate']) {
                    return Response::json(array("status" => "failed", "message" => "Please Select Appropriate Date."));
                }
            }
        }
        if($data['AutoPaymentSetting']!='never'){
            if($data['AutoPayMethod']==0){
                return Response::json(array("status" => "failed", "message" => "Please Select Auto Pay Method."));
            }

        }

        $rules = array(
            'MinThreshold' => 'numeric',
            'TopupAmount' => 'numeric|regex:/^\d*(\.\d{2})?$/',
            'OutPaymentThreshold' => 'numeric',
            'OutPaymentAmount' => 'numeric|regex:/^\d*(\.\d{2})?$/',

        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }


        $ErrorMessage = "";

        if (isset($data['AutoOutPayment']) && $data['AutoOutPayment'] == 1) {

            if (empty($data['OutPaymentAmount']))
                $ErrorMessage .= "The OutPaymentAmount field is required <br> ";

            if (empty($data['OutPaymentThreshold']))
                $ErrorMessage .= "The OutPaymentThreshold field is required <br> ";
        }

        if (isset($data['AutoTopup']) && $data['AutoTopup'] == 1) {

            if (empty($data['MinThreshold']))
                $ErrorMessage .= "The MinThreshold field is required<br> ";

            if (empty($data['TopupAmount']))
                $ErrorMessage .= "The TopupAmount field is required<br> ";

        }


        if ($ErrorMessage != "")
            return Response::json(array("status" => "failed", "message" => $ErrorMessage));


        $AccountPaymentAutomation['AutoTopup'] = (isset($data['AutoTopup']) ? $data['AutoTopup'] : "");
        $AccountPaymentAutomation['MinThreshold'] = $data['MinThreshold'];
        $AccountPaymentAutomation['AutoOutpayment'] = (isset($data['AutoOutPayment']) ? $data['AutoOutPayment'] : "");
        $AccountPaymentAutomation['OutPaymentThreshold'] = $data['OutPaymentThreshold'];
        $AccountPaymentAutomation['OutPaymentAmount'] = $data['OutPaymentAmount'];
        $AccountPaymentAutomation['TopupAmount'] = $data['TopupAmount'];

        unset($data['AutoTopup']);
        unset($data['AutoOutPayment']);
        unset($data['MinThreshold']);
        unset($data['TopupAmount']);
        unset($data['OutPaymentThreshold']);
        unset($data['OutPaymentAmount']);


        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $automation = AccountPaymentAutomation::where(['AccountID' => $id])->first();

        if ($automation != false)
            $automation->update($AccountPaymentAutomation);
        else{
            $AccountPaymentAutomation['AccountID'] = $id;
            AccountPaymentAutomation::create($AccountPaymentAutomation);
        }

//        else{
//
//            AccountPaymentAutomation::find($id)->delete();
//            AccountPaymentAutomation::where(['AccountID' => $id])->delete();
//
//        }

        if(empty($data['DifferentBillingAddress'])) {
            $data['BillingAddress1'] = $data['Address1'];
            $data['BillingAddress2'] = $data['Address2'];
            $data['BillingAddress3'] = $data['Address3'];
            $data['BillingCity']     = $data['City'];
            $data['BillingPostCode'] = $data['PostCode'];
            $data['BillingCountry']  = $data['Country'];
        }
        if (isset($data['TaxRateID']) && !empty($data['TaxRateID'])) {
            $data['TaxRateID'] = implode(',', array_unique($data['TaxRateID']));
        }

        /* if ($data['IsAffiliateAccount'] == 0) {
             unset($data['CommissionPercentage']);
         }*/
        if ($account->update($data)) {

            $DynamicData = array();
            $DynamicData['CompanyID']= $companyID;
            $DynamicData['AccountID']= $id;

            if(!empty($AccountGateway)){
                $DynamicData['FieldName'] = 'accountgateway';
                $DynamicData['FieldValue']= $AccountGateway;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(!empty($CustomerID)){
                $DynamicData['FieldName'] = 'CustomerID';
                $DynamicData['FieldValue']= $CustomerID;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }

            if(!empty($VendorName)){
                $DynamicData['FieldName'] = 'vendorname';
                $DynamicData['FieldValue']= $VendorName;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($pbxaccountstatus)){
                $DynamicData['FieldName'] = 'pbxaccountstatus';
                $DynamicData['FieldValue']= $pbxaccountstatus;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($autoblock)){
                $DynamicData['FieldName'] = 'autoblock';
                $DynamicData['FieldValue']= $autoblock;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($COCNumber)){
                $DynamicData['FieldName'] = 'COCNumber';
                $DynamicData['FieldValue']= $COCNumber;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($PONumber)){
                $DynamicData['FieldName'] = 'PONumber';
                $DynamicData['FieldValue']= $PONumber;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($AccountHolder)){
                $DynamicData['FieldName'] = 'AccountHolder';
                $DynamicData['FieldValue']= $AccountHolder;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($RegisterDutchFoundation)){
                $DynamicData['FieldName'] = 'RegisterDutchFoundation';
                $DynamicData['FieldValue']= $RegisterDutchFoundation;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($DutchProvider)){
                $DynamicData['FieldName'] = 'DutchProvider';
                $DynamicData['FieldValue']= $DutchProvider;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }
            if(isset($DirectDebit)){
                $DynamicData['FieldName'] = 'DirectDebit';
                $DynamicData['FieldValue']= $DirectDebit;
                Account::addUpdateAccountDynamicfield($DynamicData);
            }

            if($data['Billing'] == 1) {
                if($ManualBilling == 0){
                    if ($data['NextInvoiceDate'] < $data['LastInvoiceDate']) {
                        return Response::json(array("status" => "failed", "message" => "Please Select Appropriate Date."));
                    }
                    if ($data['NextChargeDate'] < $data['LastChargeDate']) {
                        return Response::json(array("status" => "failed", "message" => "Please Select Appropriate Date."));
                    }
                }
                AccountBilling::insertUpdateBilling($id, $data,$ServiceID,$invoice_count);
                if($ManualBilling == 0){
                    AccountBilling::storeFirstTimeInvoicePeriod($id, $ServiceID);
                }

                $AccountPeriod = AccountBilling::getCurrentPeriod($id, date('Y-m-d'),$ServiceID);

                $OutboundDiscountPlan = empty($data['DiscountPlanID']) ? '' : $data['DiscountPlanID'];
                $InboundDiscountPlan = empty($data['InboundDiscountPlanID']) ? '' : $data['InboundDiscountPlanID'];
                $PackageDiscountPlan = empty($data['PackageDiscountPlanID']) ? '' : $data['PackageDiscountPlanID'];

                if(!empty($AccountPeriod)) {
                    $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                    $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                    $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                    $ServiceID=0;
                    $AccountSubscriptionID = 0;
                    $AccountName='';
                    $AccountCLI='';
                    $SubscriptionDiscountPlanID=0;
                    $AccountServiceID=0;

                    AccountDiscountPlan::addUpdateDiscountPlan($id, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff,$ServiceID,$AccountServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                    AccountDiscountPlan::addUpdateDiscountPlan($id, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff,$ServiceID,$AccountServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                    AccountDiscountPlan::addUpdateDiscountPlan($id, $PackageDiscountPlan, AccountDiscountPlan::PACKAGE, $billdays, $DayDiff,$ServiceID,$AccountServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                }
            }

            if(trim(Input::get('Number')) == ''){
                CompanySetting::setKeyVal('LastAccountNo',$account->Number);
            }
            if(isset($data['password'])) {
                // $this->sendPasswordEmail($account, $password, $data);
            }

            $AccountDetailsID=AccountDetails::where('AccountID',$id)->pluck('AccountDetailID');
            if(!empty($AccountDetailsID)){
                AccountDetails::find($AccountDetailsID)->update($AccountDetails);
            }else{
                AccountDetails::create($AccountDetails);
            }

            if(!empty($data['PaymentMethod'])) {
                if (is_authorize($companyID) && $data['PaymentMethod'] == 'AuthorizeNet') {

                    $PaymentGatewayID = PaymentGateway::AuthorizeNet;
                    $PaymentProfile = AccountPaymentProfile::where(['AccountID' => $id])
                        ->where(['CompanyID' => $companyID])
                        ->where(['PaymentGatewayID' => $PaymentGatewayID])
                        ->first();
                    if (!empty($PaymentProfile)) {
                        $options = json_decode($PaymentProfile->Options);
                        $ProfileID = $options->ProfileID;
                        $ShippingProfileID = $options->ShippingProfileID;

                        //If using Authorize.net
                        $isAuthorizedNet = SiteIntegration::CheckIntegrationConfiguration(false, SiteIntegration::$AuthorizeSlug,$companyID);
                        if ($isAuthorizedNet) {
                            $AuthorizeNet = new AuthorizeNet();
                            $result = $AuthorizeNet->UpdateShippingAddress($ProfileID, $ShippingProfileID, $shipping);
                        } else {
                            return Response::json(array("status" => "success", "message" => "Payment Method Not Integrated"));
                        }
                    }
                }
            }

            AccountRateTable::addAccountRateTable($id,$data);

            return Response::json(array("status" => "success", "message" => "Account Successfully Updated. " . $message));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Account."));
        }
        //return Redirect::route('accounts.index')->with('success_message', 'Accounts Successfully Updated');;
    }


    public function getAccountPartnerInfo($id){
        $Reseller = Reseller::getResellerDetails($id);

        $CompanyID = is_numeric($id) && $id < 1 ? User::get_companyID() : $Reseller->ChildCompanyID;
        if($CompanyID == false)
            return Response::json(array("status" => "failed", "message" => "Invalid Request."));

        $data['BillingClass'] = BillingClass::getBillingClassListByCompanyID($CompanyID);
        /*$data['TerminationDiscountPlan'] = DiscountPlan::getDropdownIDListForRateType(RateType::VOICECALL_ID);
        $data['AccessDiscountPlan'] = DiscountPlan::getDropdownIDListForRateType(RateType::DID_ID);
        $data['PackageDiscountPlan'] = DiscountPlan::getDropdownIDListForRateType(RateType::PACKAGE_ID);*/
        $data['TaxRates'] = TaxRate::getTaxRateDropdownIDList($CompanyID);
        //log::info(print_r($data['TaxRates'],true));

        return Response::json(array("status" => "success","CompanyID"=>$CompanyID, "data" => json_decode(json_encode($data), true)));
    }

    /**
     * Add notes to account
     * */
    public function store_note($id) {
        $data 					= 	Input::all();
        $companyID 				= 	User::get_companyID();
        $user_name 				= 	User::get_user_full_name();
        $data['CompanyID'] 		= 	$companyID;
        $data['AccountID'] 		= 	$id;
        $data['created_by'] 	=	$user_name;
        $data["Note"] 			= 	nl2br($data["Note"]);
        $key 					= 	$data['scrol']!=""?$data['scrol']:0;
        unset($data["scrol"]);
        $response 				= 	NeonAPI::request('account/add_note',$data);

        if($response->status=='failed'){
            return json_response_api($response,false,true);
        }else{
            $response = $response->data;
            $response->type = Task::Note;
        }

        $current_user_title = Auth::user()->FirstName.' '.Auth::user()->LastName;
        return View::make('accounts.show_ajax_single', compact('response','current_user_title','key'));
    }
    /**
     * Get a Note
     */
    function get_note(){
        $response				=	array();
        $data 					= 	Input::all();
        $response_note    		=   NeonAPI::request('account/get_note',$data,false,true);
        if($response_note['status']=='failed'){
            return json_response_api($response_note,false,true);
        }else{
            return json_encode($response_note['data']);
        }
    }
    /**
     * Update a Note
     */
    function update_note()
    {
        $data 					= 	Input::all();
        $companyID 				= 	User::get_companyID();
        $user_name 				= 	User::get_user_full_name();
        $data['CompanyID'] 		= 	$companyID;
        $data['updated_by'] 	=	$user_name;
        $data["Note"] 			= 	nl2br($data["Note"]);
        unset($data['KeyID']);
        $response 				= 	NeonAPI::request('account/update_note',$data);

        if($response->status=='failed'){
            return json_response_api($response,false,true);
        }else{
            $response = $response->data;
            $response->type = Task::Note;
        }

        $current_user_title = Auth::user()->FirstName.' '.Auth::user()->LastName;
        return View::make('accounts.show_ajax_single_update', compact('response','current_user_title','key'));
    }

    /**
     * Delete a Note
     */
    public function delete_note($id) {
        ///$result = Note::find($id)->delete();
        $postdata				= 	Input::all();
        $data['NoteID']			=	$id;
        $data['NoteType']		=	$postdata['note_type'];
        $response 				= 	NeonAPI::request('account/delete_note',$data);

        if($response->status=='failed'){
            return json_response_api($response,false,true);
        }else{
            return Response::json(array("status" => "success", "message" => "Note Successfully Deleted", "NoteID" => $id));
        }
    }

    public  function  upload($id){
        if (Input::hasFile('excel')) {
            $data = Input::all();
            $today = date('Y-m-d');
            $upload_path = CompanyConfiguration::get('ACC_DOC_PATH');
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ACCOUNT_DOCUMENT'],$id) ;
            $destinationPath = $upload_path . '/' . $amazonPath;
            $excel = Input::file('excel');
            // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();

            if (in_array(strtolower($ext), array("doc", "docx", 'xls','xlsx',"pdf",'png','jpg','gif'))) {
                $filename = rename_upload_file($destinationPath,$excel->getClientOriginalName());
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
        if(file_exists($FilePath)){
            download_file($FilePath);
        }elseif(is_amazon() == true){
            header('Location: '.$FilePath);
        }
        exit;
    }
    public function  download_doc_file($id){
        $DocumentFile = AccountApproval::where(["AccountApprovalID"=>$id])->pluck('DocumentFile');
        $FilePath =  AmazonS3::preSignedUrl($DocumentFile);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }elseif(is_amazon() == true){
            header('Location: '.$FilePath);
        }
        exit;
    }
    public function delete_doc($id){
        $AccountApprovalList = AccountApprovalList::find($id);
        $filename = $AccountApprovalList->FileName;
        if($AccountApprovalList->delete()){
            AmazonS3::delete($filename);
            echo json_encode(array("status" => "success", "message" => "Document deleted successfully"));
        }else{
            echo json_encode(array("status" => "failed", "message" => "Problem Deleting Document"));
        }
    }

    public function ajax_datagrid_sheet($type) {
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

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Recent Due Sheet.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($due_sheets);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Recent Due Sheet.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($due_sheets);
            }
            /*Excel::create('Recent Due Sheet', function ($excel) use ($due_sheets) {
                $excel->sheet('Recent Due Sheet', function ($sheet) use ($due_sheets) {
                    $sheet->fromArray($due_sheets);
                });
            })->download('xls');*/
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
            $emailtoCustomer = CompanyConfiguration::get('EMAIL_TO_CUSTOMER');
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
            $email_data['message_id'] 	=  isset($status['message_id'])?$status['message_id']:"";
            $email_data['AccountID'] = $account->AccountID;
            $email_data['message'] = isset($status['body'])?$status['body']:'';
            $email_data['EmailTo'] = $data['BillingEmail'];
            email_log($email_data);
            $message = isset($status['message'])?' and '.$status['message']:'';

            return $message;
        }

    }

    // not using
    public function get_outstanding_amount($id) {

        $data = Input::all();
        $account = Account::find($id);
        $companyID = User::get_companyID();
        $Invoiceids = $data['InvoiceIDs'];
        $outstanding = Account::getOutstandingInvoiceAmount($companyID, $account->AccountID, $Invoiceids, get_round_decimal_places($account->AccountID));
        $currency = Currency::getCurrencySymbol($account->CurrencyId);
        $outstandingtext = $currency.$outstanding;
        echo json_encode(array("status" => "success", "message" => "", "outstanding" => $outstanding, "outstadingtext" => $outstandingtext));
    }

    // not using
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
        if (User::is('AccountManager')) { // Account Manager
            $criteria = json_decode($data['criteria'],true);
            $criteria['account_owners'] = $userID = User::get_userID();
            $data['criteria'] = json_encode($criteria);
        }
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
                $message = "CLI Already exists";
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
                $message = "IP Already exists";
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
    public function get_credit($id)
    {
        $data = Input::all();
        //$CompanyID = User::get_companyID();
        $account = Account::find($id);
        $CompanyID = $account->CompanyId;

        if(is_reseller() && $CompanyID != User::get_companyID())
            return  Response::json(array("status" => "failed", "message" => "Invalid Data."));

        $BillingType=AccountBilling::where(['AccountID'=>$id,'ServiceID'=>0])->pluck('BillingType');
        $getdata['AccountID'] = $id;
        $response = AccountBalance::where('AccountID', $id)->first(['AccountID', 'PermanentCredit', 'UnbilledAmount', 'EmailToCustomer', 'TemporaryCredit', 'TemporaryCreditDateTime', 'BalanceThreshold', 'BalanceAmount', 'VendorUnbilledAmount', 'OutPaymentAvailable', 'OutPaymentPaid']);
        $PermanentCredit = $BalanceAmount = $TemporaryCredit = $BalanceThreshold = $UnbilledAmount = $VendorUnbilledAmount = $EmailToCustomer = $SOA_Amount = $OutPaymentAvailable = $OutPaymentPaid = $OutPaymentPaid = 0;

        // Calculating total Out Payment
        $OutPaymentAwaiting = OutPaymentLog::where([
            'AccountID' => $id,
            'Status' => 0,
        ])->sum('Amount');

        if (!empty($response)) {
            if (!empty($response->PermanentCredit)) {
                $PermanentCredit = $response->PermanentCredit;
            }
            if (!empty($response->TemporaryCredit)) {
                $TemporaryCredit = $response->TemporaryCredit;
            }
            if (!empty($response->OutPaymentAvailable)) {
                $OutPaymentAvailable = $response->OutPaymentAvailable;
            }
            if (!empty($response->OutPaymentPaid)) {
                $OutPaymentPaid = $response->OutPaymentPaid;
            }
            if (!empty($response->BalanceThreshold)) {
                $BalanceThreshold = $response->BalanceThreshold;
            }else{
                $BalanceThreshold=0;
            }
            //$SOA_Amount = AccountBalance::getAccountSOA($CompanyID, $id);
            $SOA_Amount = AccountBalance::getNewAccountBalance($CompanyID, $id);
            if (!empty($response->UnbilledAmount)) {
                $UnbilledAmount = $response->UnbilledAmount;
            }
            if (!empty($response->VendorUnbilledAmount)) {
                $VendorUnbilledAmount = $response->VendorUnbilledAmount;
            }
            //$BalanceAmount = $SOA_Amount + ($UnbilledAmount - $VendorUnbilledAmount);
            $BalanceAmount = AccountBalance::getNewAccountExposure($CompanyID, $id);
            if (!empty($response->EmailToCustomer)) {
                $EmailToCustomer = $response->EmailToCustomer;
            }
        }
        if(isset($BillingType) && $BillingType==AccountApproval::BILLINGTYPE_PREPAID){
            $SOA_Amount = AccountBalanceLog::getPrepaidAccountBalance($id);
        }
        if(!empty($id)){
            $AccountBalanceThreshold = AccountBalanceThreshold::where(array('AccountID' => $id))->get();
        }
        return View::make('accounts.credit', compact('account','AccountAuthenticate','PermanentCredit','TemporaryCredit','BalanceThreshold','BalanceAmount','UnbilledAmount','EmailToCustomer','VendorUnbilledAmount','SOA_Amount','BillingType','AccountBalanceThreshold', 'OutPaymentAwaiting', 'OutPaymentAvailable', 'OutPaymentPaid'));
    }

    public function update_credit(){
        $data = Input::all();
        $postdata= $data;

        $rules=array();$messages=array();
        if(!empty($postdata['counttr'])){
            $thList = $postdata['counttr'];
            for ($k = 0; $k < $thList; $k++) {
                $rules['BalanceThresholdnew-' . ($k)] = 'required';
                $messages['BalanceThresholdnew-' . ($k).'.required'] = "Balance Threshold Value for the Row " . ($k+1 ) . " required";

                $rules['email-' . ($k)] = 'required';
                $messages['email-' . ($k).'.required'] = "Balance Threshold Email Value for the Row " . ($k+1 ) . " required";
            }
        }
        $validator = Validator::make($data, $rules,$messages);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
//        
        //Update Account Thread HOld
        try{
            AccountBalanceThreshold::where('AccountID', $postdata['AccountID'])->delete();
            AccountBalanceThreshold::saveAccountBalanceThreshold($postdata['AccountID'],$postdata);
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
        $response =  NeonAPI::request('account/update_creditinfo',$postdata,true,false,false);
        return json_response_api($response);
    }
    public function ajax_datagrid_credit($type){
        $getdata = Input::all();
        $response =  NeonAPI::request('account/get_credithistorygrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = json_decode(json_encode($response->data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/CreditHistory.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/CreditHistory.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        return json_response_api($response,true,true,true);
    }
    //////////////////////
    function uploadFile(){
        $data       =  Input::all();
        $attachment    =  Input::file('emailattachment');
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


    function Delete_task_parent()
    {
        $data 		= 	Input::all();

        if($data['parent_type']==Task::Note)
        {
            $data_send  	=  	array("NoteID" => $data['parent_id']);
            $result 		=  	NeonAPI::request('account/delete_note',$data_send);
        }

        if($data['parent_type']==Task::Mail)
        {
            $data_send  	=  array("AccountEmailLogID" => $data['parent_id']);
            $result 		=  NeonAPI::request('account/delete_email',$data_send);

        }
        return  json_response_api($result);

    }

    function UpdateBulkAccountStatus()
    {
        $data 		 = 	Input::all();
        $CompanyID 	 =  User::get_companyID();

        $type_status =  $data['type_active_deactive'];

        if(isset($data['type_active_deactive']) && $data['type_active_deactive']!='')
        {
            if($data['type_active_deactive']=='active'){
                $data['status_set']  = 1;
            }else if($data['type_active_deactive']=='deactive'){
                $data['status_set']  = 0;
            }else{
                return Response::json(array("status" => "failed", "message" => "No account status selected"));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "No account status selected"));
        }

        if($data['criteria_ac']=='criteria'){ //all account checkbox checked
            $userID = 0;

            if (User::is('AccountManager')) { // Account Manager
                $userID = $userID = User::get_userID();
            }elseif(User::is_admin() && isset($data['account_owners'])  && trim($data['account_owners']) > 0) {
                $userID = (int)$data['account_owners'];
            }
            $data['vendor_on_off'] 	 = $data['vendor_on_off']== 'true'?1:0;
            $data['customer_on_off'] = $data['customer_on_off']== 'true'?1:0;
            $data['reseller_on_off'] = $data['reseller_on_off']== 'true'?1:0;
            $data['low_balance'] = $data['low_balance']== 'true'?1:0;

            $query = "call prc_UpdateAccountsStatus (".$CompanyID.",".$userID.",".$data['vendor_on_off'].",".$data['customer_on_off'].",".$data['reseller_on_off'].",".$data['verification_status'].",'".$data['account_number']."','".$data['contact_name']."','".$data['account_name']."','".$data['tag']."','".$data['low_balance']."','".$data['status_set']."')";

            $result  			= 	DB::select($query);
            return Response::json(array("status" => "success", "message" => "Account Status Updated"));
        }

        if($data['criteria_ac']=='selected'){ //selceted ids from current page
            if(isset($data['SelectedIDs']) && count($data['SelectedIDs'])>0){
                foreach($data['SelectedIDs'] as $SelectedIDs){
                    Account::find($SelectedIDs)->update(["Status"=>intval($data['status_set'])]);
                }
                return Response::json(array("status" => "success", "message" => "Account Status Updated"));
            }else{
                return Response::json(array("status" => "failed", "message" => "No account selected"));
            }

        }


    }

    public function expense($id){
        $CurrencySymbol = Account::getCurrency($id);
        $account = Account::find($id);
        return View::make('accounts.expense',compact('id','CurrencySymbol','account'));
    }
    public function expense_chart(){
        $data = Input::all();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $companyID = User::get_companyID();
        $response = Account::getActivityChartRepose($companyID,$data['AccountID']);
        return $response;
    }
    public function unbilledreport($id){
        $data = Input::all();
        // $companyID = User::get_companyID();
        // @TODO: ServiceID need to fix for show
        $AccountBilling = AccountBilling::getBilling($id,0);
        $account = Account::find($id);
        $companyID = $account->CompanyId;
        $today = date('Y-m-d 23:59:59');
        $CustomerLastInvoiceDate = Account::getCustomerLastInvoiceDate($AccountBilling,$account);
        $VendorLastInvoiceDate = Account::getVendorLastInvoiceDate($AccountBilling,$account);
        $CurrencySymbol = Currency::getCurrencySymbol($account->CurrencyId);
        $query = "call prc_getUnbilledReport (?,?,?,?,?)";
        $UnbilledResult = DB::connection('neon_report')->select($query,array($companyID,$id,$CustomerLastInvoiceDate,$today,1));
        $VendorUnbilledResult  =array();
        if(!empty($VendorLastInvoiceDate)){
            $query = "call prc_getVendorUnbilledReport (?,?,?,?,?)";
            $VendorUnbilledResult = DB::connection('neon_report')->select($query,array($companyID,$id,$VendorLastInvoiceDate,$today,1));
        }

        return View::make('accounts.unbilled_table', compact('UnbilledResult','CurrencySymbol','VendorUnbilledResult','account'));
    }

    public function prepaidunbilledreport($id){
        $data = Input::all();
        // $companyID = User::get_companyID();
        // @TODO: ServiceID need to fix for show
        $AccountBilling = AccountBilling::getBilling($id,0);
        $account = Account::find($id);
        $companyID = $account->CompanyId;
        $today = date('Y-m-d 23:59:59');
        $CustomerLastInvoiceDate = Account::getCustomerLastInvoiceDate($AccountBilling,$account);
        $CurrencySymbol = Currency::getCurrencySymbol($account->CurrencyId);
        $query = "call prc_getPrepaidUnbilledReport (?,?,?,?,?)";
        $UnbilledResult = DB::select($query,array($companyID,$id,$CustomerLastInvoiceDate,$today,1));
        return View::make('accounts.prepaid_unbilled_table', compact('UnbilledResult','CurrencySymbol','account'));
    }

    public function activity_pdf_download($id){

        $CurrencySymbol = Account::getCurrency($id);
        $account = Account::find($id);
        $companyID = User::get_companyID();
        $response = $response = Account::getActivityChartRepose($companyID,$id);

        $body = View::make('accounts.printexpensechart',compact('id','CurrencySymbol','response'))->render();
        $body = htmlspecialchars_decode($body);

        $destination_dir = CompanyConfiguration::get('TEMP_PATH') . '/';
        if (!file_exists($destination_dir)) {
            mkdir($destination_dir, 0777, true);
        }
        RemoteSSH::run("chmod -R 777 " . $destination_dir);
        $file_name = $account->AccountName.' Account Activity Chart '. date('d-m-Y') . '.pdf';
        $htmlfile_name = $account->AccountName. ' Account Activity Chart ' . date('d-m-Y') . '.html';

        $local_file = $destination_dir .  $file_name;
        $local_htmlfile = $destination_dir .  $htmlfile_name;
        file_put_contents($local_htmlfile,$body);

        if(getenv('APP_OS') == 'Linux'){
            exec (base_path(). '/wkhtmltox/bin/wkhtmltopdf --javascript-delay 5000 "'.$local_htmlfile.'" "'.$local_file.'"',$output);
            Log::info(base_path(). '/wkhtmltox/bin/wkhtmltopdf --javascript-delay 5000"'.$local_htmlfile.'" "'.$local_file.'"',$output);

        }else{
            exec (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --javascript-delay 5000 "'.$local_htmlfile.'" "'.$local_file.'"',$output);
            Log::info (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --javascript-delay 5000"'.$local_htmlfile.'" "'.$local_file.'"',$output);
        }
        Log::info($output);
        @unlink($local_htmlfile);
        $save_path = $destination_dir . $file_name;
        return Response::download($save_path);
    }

    public function clitable_ajax_datagrid($id){

        $data = Input::all();
        $account = Account::find($data['AccountID']);
        $CompanyID = $account->CompanyId;
        //Log::info("clitable_ajax_datagrid_query " . print_r($data,true));
        $rate_tables = CLIRateTable::
        leftJoin('tblRateTable as rt','rt.RateTableId','=','tblCLIRateTable.RateTableID')
            ->leftJoin('tblRateTable as termination','termination.RateTableId','=','tblCLIRateTable.TerminationRateTableID')
            ->leftJoin('tblRateTable as specialRT','specialRT.RateTableId','=','tblCLIRateTable.SpecialRateTableID')
            ->leftJoin('tblRateTable as specialTerminationRT','specialTerminationRT.RateTableId','=','tblCLIRateTable.SpecialTerminationRateTableID')
            ->leftJoin('tblService','tblService.ServiceID','=','tblCLIRateTable.ServiceID')
            ->leftJoin('tblCountry','tblCountry.CountryID','=','tblCLIRateTable.CountryID')
            ->select(['CLIRateTableID', 'CLI', 'rt.RateTableName as AccessRateTable', DB::raw("(select name from tblDiscountPlan dplan where dplan.DiscountPlanID = tblCLIRateTable.AccessDiscountPlanID ) as AccessDiscountPlan"), 'termination.RateTableName as TerminationRateTable', DB::raw("(select name from tblDiscountPlan dplan where dplan.DiscountPlanID = tblCLIRateTable.TerminationDiscountPlanID ) as TerminationDiscountPlan"), 'specialRT.RateTableName as SpecialRateTable', 'specialTerminationRT.RateTableName as SpecialTerminationRateTable', 'tblCLIRateTable.ContractID', 'tblCLIRateTable.NoType',
                'tblCountry.Country as Country', 'tblCLIRateTable.PrefixWithoutCountry', 'tblCLIRateTable.City', 'tblCLIRateTable.Tariff', 'tblCLIRateTable.NumberStartDate', 'tblCLIRateTable.NumberEndDate', 'tblCLIRateTable.Status',
                'tblCLIRateTable.RateTableID','tblCLIRateTable.AccessDiscountPlanID','tblCLIRateTable.TerminationRateTableID','tblCLIRateTable.TerminationDiscountPlanID','tblCLIRateTable.CountryID','tblCLIRateTable.SpecialRateTableID','tblCLIRateTable.SpecialTerminationRateTableID','tblCLIRateTable.Prefix'])
            ->where("tblCLIRateTable.CompanyID",$CompanyID)
            ->where("tblCLIRateTable.AccountServiceID",$data['AccountServiceID'])
            ->where("tblCLIRateTable.AccountID",$id);
        if(!empty($data['CLIName'])){
            $rate_tables->WhereRaw('CLI like "%'.$data['CLIName'].'%"');
        }
        if(isset($data['CLIStatus']) && $data['CLIStatus'] != ""){
            $rate_tables->where('tblCLIRateTable.Status',"=",$data['CLIStatus']);
        }
        if(!empty($data['ServiceID'])){
            $rate_tables->where('tblCLIRateTable.ServiceID','=',$data['ServiceID']);
        }
        if(!empty($data['NumberContractID'])){
            $rate_tables->where('tblCLIRateTable.ContractID','=',$data['NumberContractID']);
        }
        if(!empty($data['NumberStartDate'])){
            $rate_tables->where('tblCLIRateTable.NumberStartDate','>=',$data['NumberStartDate']);
        }
        if(!empty($data['NumberEndDate'])){
            $rate_tables->where('tblCLIRateTable.NumberEndDate','<=',$data['NumberEndDate']);
        }
        if(!empty($data['AccountServiceID'])){
            $rate_tables->where('tblCLIRateTable.AccountServiceID','=',$data['AccountServiceID']);
        }
        /*
        else{
            $rate_tables->where('tblCLIRateTable.ServiceID','=',0);
        }*/
        Log::info($rate_tables->toSql());
        return Datatables::of($rate_tables)->make();
    }

    public function packagetable_ajax_datagrid($id){

        $data = Input::all();
        $account = Account::find($data['AccountID']);
        $CompanyID = $account->CompanyId;
        //Log::info("packagetable_ajax_datagrid" . print_r($data,true));
        $rate_tables = AccountServicePackage::
        leftJoin('tblRateTable as rt','rt.RateTableId','=','tblAccountServicePackage.RateTableID')
            ->leftJoin('tblRateTable as specialPackageRT','specialPackageRT.RateTableId','=','tblAccountServicePackage.SpecialPackageRateTableID')
            ->leftJoin('tblPackage as package','package.PackageId','=','tblAccountServicePackage.PackageId')
            ->select(['AccountServicePackageID', 'package.Name','rt.RateTableName',DB::raw("(select name from tblDiscountPlan dplan where dplan.DiscountPlanID = tblAccountServicePackage.PackageDiscountPlanID ) as PackageDiscountPlan"), 'specialPackageRT.RateTableName as SpecialRateTableName','tblAccountServicePackage.ContractID', 'tblAccountServicePackage.PackageStartDate', 'tblAccountServicePackage.PackageEndDate', 'tblAccountServicePackage.Status',
                'tblAccountServicePackage.PackageId','tblAccountServicePackage.RateTableID','tblAccountServicePackage.PackageDiscountPlanID','tblAccountServicePackage.SpecialPackageRateTableID'])
            ->where("tblAccountServicePackage.CompanyID",$CompanyID)
            ->where("package.CompanyID",$CompanyID)
            ->where("tblAccountServicePackage.AccountServiceID",$data['AccountServiceID'])
            ->where("tblAccountServicePackage.AccountID",$id);

        if(isset($data['PackageStatus']) && $data['PackageStatus'] != ""){
            $rate_tables->where('tblAccountServicePackage.Status',"=",$data['PackageStatus']);
        }
        if(!empty($data['PackageName'])){
            $rate_tables->where('tblAccountServicePackage.PackageId','=',$data['PackageName']);
        }
        if(!empty($data['AccountServiceID'])){
            $rate_tables->where('tblAccountServicePackage.AccountServiceID','=',$data['AccountServiceID']);
        }
        if(!empty($data['PackageContractID'])){
            $rate_tables->where('tblAccountServicePackage.ContractID','=',$data['PackageContractID']);
        }
        if(!empty($data['PackageStartDate'])){
            $rate_tables->where('tblAccountServicePackage.PackageStartDate','>=',$data['PackageStartDate']);
        }
        if(!empty($data['PackageEndDate'])){
            $rate_tables->where('tblAccountServicePackage.PackageEndDate','<=',$data['PackageEndDate']);
        }
        /*
        else{
            $rate_tables->where('tblCLIRateTable.ServiceID','=',0);
        }*/
        Log::info("packagetable_ajax_datagrid" . $rate_tables->toSql());
        return Datatables::of($rate_tables)->make();
    }
    public function clitable_store(){
        $data = Input::all();
        $account = Account::find($data['AccountID']);
        $CompanyID = $account->CompanyId;
        $message = '';

        // Log::info("clitable_store " . print_r($data,true));
        $rules['CLI']                    = 'required';
        $rules['NumberStartDate']        = 'required';
        $rules['NumberEndDate']          = 'required';
        $rules['RateTableID']            = 'required'; // Default Access Rate Table
        $rules['TerminationRateTableID'] = 'required'; // Default Termination Rate Table
        $rules['CountryID']              = 'required'; // Country
        $rules['NoType']                 = 'required'; // Type
        $rules['PrefixWithoutCountry']   = 'required'; // Prefix


        $validator = Validator::make($data, $rules, [
            'CLI.required'                    => "The number is required.",
            'RateTableID.required'            => "The default access rate table is required.",
            'TerminationRateTableID.required' => "The default termination rate table is required.",
            'CountryID.required'              => "The country is required.",
            'NoType.required'                 => "The type is required.",
            'PrefixWithoutCountry.required'   => "The prefix is required.",
        ]);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if (strtotime($data['NumberEndDate']) <= strtotime($data['NumberStartDate'])) {
            return  Response::json(array("status" => "failed", "message" => "End Date should be greater then start date"));
        }



        $clis = array_filter(preg_split("/\\r\\n|\\r|\\n/", $data['CLI']),function($var){return trim($var)!='';});

        AccountAuthenticate::add_cli_rule($CompanyID,$data);
        $insertArr = [];
        $cli = $data['CLI'];
        $check = CLIRateTable::where([
            'CompanyID' =>  $CompanyID,
            'AccountID' =>  $data['AccountID'],
            'AccountServiceID' =>  $data['AccountServiceID'],
            'CLI'=>  $data['CLI'],
            'Status'    =>  1
        ])->whereBetween('NumberStartDate', array($data['NumberStartDate'], $data['NumberEndDate']));

        //  whereRaw("'" . $data['NumberStartDate'] . "'" .  " >= NumberStartDate")
        //    ->whereRaw("'" .$data['NumberStartDate']. "'" . " <= NumberEndDate");

        $check1 = CLIRateTable::where([
            'CompanyID' =>  $CompanyID,
            'AccountID' =>  $data['AccountID'],
            'AccountServiceID' =>  $data['AccountServiceID'],
            'CLI'=>  $data['CLI'],
            'Status'    =>  1
        ])->whereBetween('NumberEndDate', array($data['NumberStartDate'], $data['NumberEndDate']));

        //whereRaw("'" . $data['NumberEndDate'] . "'" .  " >= NumberStartDate")
        //  ->whereRaw("'" .$data['NumberEndDate']. "'" . " <= NumberEndDate");

        $check2 = CLIRateTable::where([
            'CompanyID' =>  $CompanyID,
            'AccountID' =>  $data['AccountID'],
            'AccountServiceID' =>  $data['AccountServiceID'],
            'CLI'=>  $data['CLI'],
            'Status'    =>  1
        ])->where('NumberEndDate','>=',$data['NumberStartDate'])->where('NumberStartDate','<=',$data['NumberStartDate']);

        if($check->count() > 0 || $check1->count() > 0 || $check2->count() > 0){
            $message = 'Number '. $data['CLI'] . ' already exist between start date '.
                $data['NumberStartDate'] . ' and End Date ' .$data['NumberEndDate'].' <br>';
            return Response::json(array("status" => "error", "message" => $message));
        } else {
            $rate_tables['CLI'] = $data['CLI'];
            $rate_tables['RateTableID'] = $data['RateTableID'];
            $rate_tables['SpecialRateTableID'] = !empty($data['SpecialRateTableID']) ? $data['SpecialRateTableID'] : 0;
            $rate_tables['AccessDiscountPlanID'] = !empty($data['AccessDiscountPlanID']) ? $data['AccessDiscountPlanID'] : 0;
            $rate_tables['TerminationRateTableID'] = !empty($data['TerminationRateTableID']) ? $data['TerminationRateTableID'] : 0;
            $rate_tables['SpecialTerminationRateTableID'] = !empty($data['SpecialTerminationRateTableID']) ?
                $data['SpecialTerminationRateTableID'] : 0;
            $rate_tables['TerminationDiscountPlanID'] = !empty($data['TerminationDiscountPlanID']) ? $data['TerminationDiscountPlanID'] : 0;
            $rate_tables['CountryID'] = !empty($data['CountryID']) ? $data['CountryID'] : 0;
            $rate_tables['NumberStartDate'] = !empty($data['NumberStartDate']) ? $data['NumberStartDate'] : '';
            $rate_tables['NumberEndDate'] = !empty($data['NumberEndDate']) ? $data['NumberEndDate'] : '';
            $rate_tables['NoType'] = !empty($data['NoType']) ? $data['NoType'] : '';
            $rate_tables['PrefixWithoutCountry'] = !empty($data['PrefixWithoutCountry'])?$data['PrefixWithoutCountry']:'';
            $rate_tables['ContractID'] = !empty($data['ContractID'])?$data['ContractID']:'';
            $rate_tables['City'] = !empty($data['City'])?$data['City']:'';
            $rate_tables['Tariff'] = !empty($data['Tariff'])?$data['Tariff']:'';
            $rate_tables['AccountID'] = $data['AccountID'];
            $rate_tables['CompanyID'] = $CompanyID;


            $rate_tables['Status'] = isset($data['Status']) ? 1 : 0;
            if(!empty($data['ServiceID'])) {
                $rate_tables['ServiceID'] = $data['ServiceID'];
            }
            if(!empty($data['AccountServiceID'])) {
                $rate_tables['AccountServiceID'] = $data['AccountServiceID'];
            }

            $rate_tables['Prefix'] = $rate_tables['PrefixWithoutCountry'];
            if (!empty($rate_tables['CountryID']) && !empty($rate_tables['PrefixWithoutCountry'])) {
                $ProductCountry = Country::where(array('CountryID' => $rate_tables['CountryID']))->first();
                $zeroPrefix = 0;
                $zeroPrefixStop = 0;
                for ($x = 0; $x < strlen($rate_tables['PrefixWithoutCountry']) && $zeroPrefixStop == 0; $x++) {
                    if (substr($rate_tables['PrefixWithoutCountry'], $x, 1) == "0") {
                        $zeroPrefix++;
                    }else {
                        $zeroPrefixStop = 1;
                    }
                }

                if ($zeroPrefix > 0) {
                    $ProductCountryPrefix = $ProductCountry->Prefix . substr($rate_tables['PrefixWithoutCountry'], $zeroPrefix, strlen($rate_tables['PrefixWithoutCountry']));
                } else {
                    $ProductCountryPrefix = $ProductCountry->Prefix . (empty($rate_tables['PrefixWithoutCountry']) ? "" : $rate_tables['PrefixWithoutCountry']);
                }
                $rate_tables['Prefix'] = $ProductCountryPrefix;
            }
            $query = 'call prc_getRateTableVendor (' . $rate_tables['RateTableID'] .",'" .
                $rate_tables['NoType'] . "','" . $rate_tables['City']. "','" . $rate_tables['Tariff']. "','" . $rate_tables['CountryID'] . "','" . '0' .
                "','" . $rate_tables['Prefix'] . "'" . ')';
            $results = DB::select($query);
            Log::info("clitable_store prc" . $query);
            $VendorID = '';
            foreach($results as $result){
                $VendorID = $result->VendorID;
            }
            if (!empty($VendorID)) {
                $rate_tables['VendorID'] = $VendorID;
            }
            Log::info("clitable_store prc" . $VendorID);
            $insertArr[] = $rate_tables;
        }


        //dd($insertArr);
        if(!empty($message)){
            $message = 'Following CLI already exists.<br>'.$message;
            return Response::json(array("status" => "error", "message" => $message));
        }else{

            CLIRateTable::insert($insertArr);
            return Response::json(array("status" => "success", "message" => "Number Successfully Added"));
        }

    }
    public function packagetable_store(){
        $data = Input::all();
        $account = Account::find($data['AccountID']);
        $CompanyID = $account->CompanyId;
        $message = '';
        $date = date('Y-m-d H:i:s');
        $CreatedBy = User::get_user_full_name();

        $rules['PackageID']          = 'required';
        $rules['PackageRateTableID'] = 'required';
        $rules['PackageStartDate']   = 'required';
        $rules['PackageEndDate']     = 'required';


        $validator = Validator::make($data, $rules, [
            'PackageID.required' => "Package is required.",
            'PackageRateTableID.required' => "Default package rate table is required.",
        ]);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if (strtotime($data['PackageEndDate']) <= strtotime($data['PackageStartDate'])) {
            return  Response::json(array("status" => "failed", "message" => "End Date should be greater then start date"));
        }

        $insertArr = [];
        $PackageId = $data['PackageID'];
        $check = AccountServicePackage::where([
            'CompanyID'=>$CompanyID,
            'AccountID'=>$data['AccountID'],
            'AccountServiceID' =>  $data['AccountServiceID'],
            'PackageId'=>  $data['PackageID'],
            'Status'=>1
        ])->whereBetween('PackageStartDate', array($data['PackageStartDate'], $data['PackageEndDate']));
        $check1 = AccountServicePackage::where([
            'CompanyID'=>$CompanyID,
            'AccountID'=>$data['AccountID'],
            'AccountServiceID' =>  $data['AccountServiceID'],
            'PackageId'=>  $data['PackageID'],
            'Status'=>1
        ])->whereBetween('PackageEndDate', array($data['PackageStartDate'], $data['PackageEndDate']));
        $check2 = AccountServicePackage::where([
            'CompanyID'=>$CompanyID,
            'AccountID'=>$data['AccountID'],
            'AccountServiceID' =>  $data['AccountServiceID'],
            'PackageId'=>  $data['PackageID'],
            'Status'=>1
        ])->where('PackageEndDate','>=',$data['PackageStartDate'])->where('PackageStartDate','<=',$data['PackageStartDate']);
        //->whereRaw("'" . $data['PackageStartDate'] . "'" .  " >= PackageStartDate")
        //   ->whereRaw("'" .$data['PackageEndDate']. "'" . " <= PackageEndDate");

        if($check->count() > 0 || $check1->count() > 0 || $check2->count() > 0){
            $message = 'Selected Package already exists between package start date ' . $data['PackageStartDate'] . ' and  package end data ' . '.<br>';
        } else {
            $rate_tables['PackageID'] = $data['PackageID'];
            $rate_tables['RateTableID'] = !empty($data['PackageRateTableID']) ? $data['PackageRateTableID'] : 0;
            $rate_tables['SpecialPackageRateTableID'] = !empty($data['SpecialPackageRateTableID']) ? $data['SpecialPackageRateTableID'] : 0;
            $rate_tables['PackageDiscountPlanID'] = !empty($data['AccountPackageDiscountPlanID']) ? $data['AccountPackageDiscountPlanID'] : 0;
            $rate_tables['PackageStartDate'] = !empty($data['PackageStartDate']) ? $data['PackageStartDate'] : '';
            $rate_tables['PackageEndDate'] = !empty($data['PackageEndDate']) ? $data['PackageEndDate'] : '';
            $rate_tables['ContractID'] = !empty($data['ContractID'])?$data['ContractID']:'';
            $rate_tables['AccountID'] = $data['AccountID'];
            $rate_tables['CompanyID'] = $CompanyID;
            $rate_tables['created_at'] = $date;
            $rate_tables['created_by'] = $CreatedBy;
            $rate_tables['updated_at'] = $date;
            $rate_tables['updated_by'] = $CreatedBy;

            $rate_tables['Status'] = isset($data['Status']) ? 1 : 0;
            if(!empty($data['ServiceID'])) {
                $rate_tables['ServiceID'] = $data['ServiceID'];
            }
            if(!empty($data['AccountServiceID'])) {
                $rate_tables['AccountServiceID'] = $data['AccountServiceID'];
            }
            $query = 'call prc_getRateTableVendor (' . $rate_tables['RateTableID'] .",'" .
                '' . "','" . ''. "','" . ''. "','" . '0' . "','" . $rate_tables['PackageID'] .
                "','" . '' . "'" . ')';
            $results = DB::select($query);
            Log::info("package_store prc" . $query);
            $VendorID = '';
            foreach($results as $result){
                $VendorID = $result->VendorID;
            }
            if (!empty($VendorID)) {
                $rate_tables['VendorID'] = $VendorID;
            }

            $insertArr[] = $rate_tables;
        }


        if(!empty($message)){

            return Response::json(array("status" => "error", "message" => $message));
        }else{
            AccountServicePackage::insert($insertArr);
            return Response::json(array("status" => "success", "message" => "Package Successfully Added"));
        }

    }
    public function clitable_delete($CLIRateTableID){
        $data = Input::all();

        $CompanyID = User::get_companyID();
        $Date = '';
        $Confirm = 0;
        $CLIs = '';
        if(isset($data['dates'])){
            $Date = $data['dates'];
            $Confirm = 1;
        }
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        if(!empty($data['AccountServiceID'])){
            $AccountServiceID = $data['AccountServiceID'];
        }else{
            $AccountServiceID = 0;
        }
        AccountAuthenticate::add_cli_rule($CompanyID,$data);

        if ($CLIRateTableID > 0) {
            $CLIs = CLIRateTable::where(array('CLIRateTableID' => $CLIRateTableID))->pluck('CLI');
            $data['CLIRateTableIDs'] = $CLIRateTableID + ",";
        } else if (!empty($data['criteria'])) {
            $criteria = json_decode($data['criteria'], true);
            $CLIRateTables = CLIRateTable::WhereRaw('CLI like "%' . $criteria['CLIName'] . '%"')
                //->where(array('ServiceID' => $ServiceID))
                ->where(array('AccountID' => $data['AccountID']))
                ->select(DB::raw('group_concat(CLI) as CLIs'))->get();
            if(!empty($CLIRateTables)){
                $CLIs = $CLIRateTables[0]->CLIs;
            }
        } else if (!empty($data['CLIRateTableIDs'])) {
            $CLIRateTableIDs = explode(',', $data['CLIRateTableIDs']);
            //$CLIRateTables = CLIRateTable::whereIn('CLIRateTableID', $CLIRateTableIDs)->where(array('ServiceID' => $ServiceID))->select(DB::raw('group_concat(CLI) as CLIs'))->get();
            $CLIRateTables = CLIRateTable::whereIn('CLIRateTableID', $CLIRateTableIDs)->select(DB::raw('group_concat(CLI) as CLIs'))->get();
            if(!empty($CLIRateTables)){
                $CLIs = $CLIRateTables[0]->CLIs;
            }
        }
        $query = "call prc_unsetCDRUsageAccount ('" . $CompanyID . "','" . $CLIs . "','".$Date."',".$Confirm.",".$ServiceID.",".$AccountServiceID.")";
        $recordFound = DB::Connection('sqlsrvcdr')->select($query);
        if($recordFound[0]->Status>0){
            return Response::json(array("status" => "check","check"=>1));
        }

        // Log::info("clitable_delete " . print_r($data,true) . '' . $CLIRateTableID);

        if (!empty($data['CLIRateTableIDs'])) {
            $CLIRateTableIDs = explode(',', $data['CLIRateTableIDs']);
            //CLIRateTable::whereIn('CLIRateTableID', $CLIRateTableIDs)->where(array('ServiceID' => $ServiceID))->delete();
            CLIRateTable::whereIn('CLIRateTableID', $CLIRateTableIDs)->delete();
        }

        return Response::json(array("status" => "success", "message" => "CLI Deleted Successfully"));
    }

    public function packagetable_delete($AccountServicePackageID){
        $data = Input::all();
        // Log::info("packagetable_delete " . print_r($data,true) . '' . $AccountServicePackageID);
        $CompanyID = User::get_companyID();
        if ($AccountServicePackageID > 0) {
            $data['AccountServicePackageIDs'] = $AccountServicePackageID + ",";
        }
        if (!empty($data['AccountServicePackageIDs'])) {
            $CLIRateTableIDs = explode(',', $data['AccountServicePackageIDs']);
            AccountServicePackage::whereIn('AccountServicePackageID', $CLIRateTableIDs)->delete();
        }

        return Response::json(array("status" => "success", "message" => "Package Deleted Successfully"));
    }

    public function clitable_update(){
        $data = Input::all();
        $account = Account::find($data['AccountID']);
        $CompanyID = $account->CompanyId;
        $rules['CLI'] = 'required';
        $rules['NumberStartDate']        = 'required';
        $rules['NumberEndDate']          = 'required';
        $rules['RateTableID']            = 'required'; // Default Access Rate Table
        $rules['TerminationRateTableID'] = 'required'; // Default Termination Rate Table
        $rules['CountryID']              = 'required'; // Country
        $rules['NoType']                 = 'required'; // Type
        $rules['PrefixWithoutCountry']   = 'required'; // Prefix
        // Log::info("clitable_store " . print_r($data,true));

        $zeroPrefix = 0;




        $validator = Validator::make($data, $rules, [
            'CLI.required'                    => "The number is required.",
            'RateTableID.required'            => "The default access rate table is required.",
            'TerminationRateTableID.required' => "The default termination rate table is required.",
            'CountryID.required'              => "The country is required.",
            'NoType.required'                 => "The type is required.",
            'PrefixWithoutCountry.required'   => "The prefix is required.",
        ]);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if (strtotime($data['NumberEndDate']) <= strtotime($data['NumberStartDate'])) {
            return  Response::json(array("status" => "failed", "message" => "End Date should be greater then start date"));
        }

        $cli = $data['CLI'];

        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        if(!empty($data['AccountServiceID'])){
            $AccountServiceID = $data['AccountServiceID'];
        }else{
            $AccountServiceID = 0;
        }
        AccountAuthenticate::add_cli_rule($CompanyID,$data);
        $rate_tables['CLI'] = $cli;
        $rate_tables['RateTableID'] = $data['RateTableID'];
        $rate_tables['SpecialRateTableID'] = !empty($data['SpecialRateTableID']) ? $data['SpecialRateTableID'] : 0;
        $rate_tables['AccessDiscountPlanID'] = !empty($data['AccessDiscountPlanID']) ? $data['AccessDiscountPlanID'] : 0;
        $rate_tables['TerminationRateTableID'] = !empty($data['TerminationRateTableID']) ? $data['TerminationRateTableID'] : 0;
        $rate_tables['SpecialTerminationRateTableID'] = !empty($data['SpecialTerminationRateTableID']) ? $data['SpecialTerminationRateTableID'] : 0;
        $rate_tables['TerminationDiscountPlanID'] = !empty($data['TerminationDiscountPlanID']) ? $data['TerminationDiscountPlanID'] : 0;
        $rate_tables['CountryID'] = !empty($data['CountryID']) ? $data['CountryID'] : 0;
        $rate_tables['NumberStartDate'] = !empty($data['NumberStartDate']) ? $data['NumberStartDate'] : '';
        $rate_tables['NumberEndDate'] = !empty($data['NumberEndDate']) ? $data['NumberEndDate'] : '';
        $rate_tables['NoType'] = !empty($data['NoType']) ? $data['NoType'] : '';
        $rate_tables['PrefixWithoutCountry'] = !empty($data['PrefixWithoutCountry'])?$data['PrefixWithoutCountry']:'';
        $rate_tables['ContractID'] = !empty($data['ContractID'])?$data['ContractID']:'';
        $rate_tables['City'] = !empty($data['City'])?$data['City']:'';
        $rate_tables['Tariff'] = !empty($data['Tariff'])?$data['Tariff']:'';
        $rate_tables['AccountID'] = $data['AccountID'];
        $rate_tables['CompanyID'] = $CompanyID;



        $rate_tables['Status'] = isset($data['Status']) ? 1 : 0;
        if(!empty($data['ServiceID'])) {
            $rate_tables['ServiceID'] = $data['ServiceID'];
        }
        if(!empty($data['AccountServiceID'])) {
            $rate_tables['AccountServiceID'] = $data['AccountServiceID'];
        }
        // $UpdateData[] = $rate_tables;



        if (!empty($data['CLIRateTableID'])) {
            $oldCLI = CLIRateTable::findOrFail($data['CLIRateTableID']);

            // if this cli already exist in table
            $check = false;
            if($rate_tables['Status'] == 1 || $data['CLI'] != $oldCLI->CLI)
                $check = CLIRateTable::where([
                    'CompanyID' =>  $CompanyID,
                    'AccountID' =>  $data['AccountID'],
                    'AccountServiceID' =>  $data['AccountServiceID'],
                    'CLI'=>  $data['CLI'],
                    'Status'    =>  1
                ])->where("CLIRateTableID", "!=", $data['CLIRateTableID'])->whereBetween('NumberStartDate', array($data['NumberStartDate'], $data['NumberEndDate']));

            //  whereRaw("'" . $data['NumberStartDate'] . "'" .  " >= NumberStartDate")
            //    ->whereRaw("'" .$data['NumberStartDate']. "'" . " <= NumberEndDate");

            $check1 = CLIRateTable::where([
                'CompanyID' =>  $CompanyID,
                'AccountID' =>  $data['AccountID'],
                'AccountServiceID' =>  $data['AccountServiceID'],
                'CLI'=>  $data['CLI'],
                'Status'    =>  1
            ])->where("CLIRateTableID", "!=", $data['CLIRateTableID'])->whereBetween('NumberEndDate', array($data['NumberStartDate'], $data['NumberEndDate']));

            //whereRaw("'" . $data['NumberEndDate'] . "'" .  " >= NumberStartDate")
            //  ->whereRaw("'" .$data['NumberEndDate']. "'" . " <= NumberEndDate");

            $check2 = CLIRateTable::where([
                'CompanyID' =>  $CompanyID,
                'AccountID' =>  $data['AccountID'],
                'AccountServiceID' =>  $data['AccountServiceID'],
                'CLI'=>  $data['CLI'],
                'Status'    =>  1
            ])->where("CLIRateTableID", "!=", $data['CLIRateTableID'])->where('NumberEndDate','>=',$data['NumberStartDate'])->where('NumberStartDate','<=',$data['NumberStartDate']);



            if($check != false  && $check->count() > 0 || $check1 != false && $check1->count() > 0 ||$check2 != false && $check2->count() > 0){
                $message = 'Number '. $data['CLI'] . ' already exist between start date '.
                    $data['NumberStartDate'] . ' and End Date ' .$data['NumberEndDate'].' <br>';
                return Response::json(array("status" => "error", "message" => $message));
            }

            $rate_tables['Prefix'] = $rate_tables['PrefixWithoutCountry'];
            if (!empty($rate_tables['CountryID']) && !empty($rate_tables['PrefixWithoutCountry'])) {
                $ProductCountry = Country::where(array('CountryID' => $rate_tables['CountryID']))->first();
                $zeroPrefixStop = 0;
                for ($x = 0; $x < strlen($rate_tables['PrefixWithoutCountry']) && $zeroPrefixStop == 0; $x++) {
                    if (substr($rate_tables['PrefixWithoutCountry'], $x, 1) == "0") {
                        $zeroPrefix++;
                    }else {
                        $zeroPrefixStop = 1;
                    }
                }

                if ($zeroPrefix > 0) {
                    $ProductCountryPrefix = $ProductCountry->Prefix . substr($rate_tables['PrefixWithoutCountry'], $zeroPrefix, strlen($rate_tables['PrefixWithoutCountry']));
                } else {
                    $ProductCountryPrefix = $ProductCountry->Prefix . (empty($rate_tables['PrefixWithoutCountry']) ? "" : $rate_tables['PrefixWithoutCountry']);
                }
                $rate_tables['Prefix'] = $ProductCountryPrefix;
            }
            $query = 'call prc_getRateTableVendor (' . $rate_tables['RateTableID'] .",'" .
                $rate_tables['NoType'] . "','" . $rate_tables['City']. "','" . $rate_tables['Tariff']. "','" . $rate_tables['CountryID'] . "','" . '0' .
                "','" . $rate_tables['Prefix'] . "'" . ')';
            $results = DB::select($query);
            Log::info("clitable_update " . $query);
            $VendorID = '';
            foreach($results as $result){
                $VendorID = $result->VendorID;
            }
            if (!empty($VendorID)) {
                $rate_tables['VendorID'] = $VendorID;
            }


            $oldCLI->update($rate_tables);
        }
        return Response::json(array("status" => "success", "message" => "Number Updated Successfully"));
    }

    public function packagetable_update(){
        $data = Input::all();
        $account = Account::find($data['AccountID']);
        $CompanyID = $account->CompanyId;

        $rules['PackageID']          = 'required';
        $rules['PackageRateTableID'] = 'required';
        $rules['PackageStartDate']   = 'required';
        $rules['PackageEndDate']     = 'required';

        $date = date('Y-m-d H:i:s');
        $CreatedBy = User::get_user_full_name();

        $validator = Validator::make($data, $rules, [
            'PackageID.required' => "Package is required.",
            'PackageRateTableID.required' => "Default Package Rate Table is required.",
        ]);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if (strtotime($data['PackageEndDate']) <= strtotime($data['PackageStartDate'])) {
            return  Response::json(array("status" => "failed", "message" => "End Date should be greater then start date"));
        }
        $rate_tables['PackageID'] = $data['PackageID'];
        $rate_tables['RateTableID'] = !empty($data['PackageRateTableID']) ? $data['PackageRateTableID'] : 0;
        $rate_tables['SpecialPackageRateTableID'] = !empty($data['SpecialPackageRateTableID']) ? $data['SpecialPackageRateTableID'] : 0;
        $rate_tables['PackageDiscountPlanID'] = !empty($data['AccountPackageDiscountPlanID']) ? $data['AccountPackageDiscountPlanID'] : 0;
        $rate_tables['PackageStartDate'] = !empty($data['PackageStartDate']) ? $data['PackageStartDate'] : '';
        $rate_tables['PackageEndDate'] = !empty($data['PackageEndDate']) ? $data['PackageEndDate'] : '';
        $rate_tables['ContractID'] = !empty($data['ContractID'])?$data['ContractID']:'';
        $rate_tables['AccountID'] = $data['AccountID'];
        $rate_tables['CompanyID'] = $CompanyID;
        $rate_tables['updated_at'] = $date;
        $rate_tables['updated_by'] = $CreatedBy;

        $rate_tables['Status'] = isset($data['Status']) ? 1 : 0;
        if(!empty($data['ServiceID'])) {
            $rate_tables['ServiceID'] = $data['ServiceID'];
        }
        if(!empty($data['AccountServiceID'])) {
            $rate_tables['AccountServiceID'] = $data['AccountServiceID'];
        }
        /* $PackageId = $data['PackageID'];
        $check = AccountServicePackage::where([
            'CompanyID'=>$CompanyID,
            'AccountID'=>$data['AccountID'],
            'PackageId'=>$PackageId,
            'Status'=>1
        ])->count();*/



        if (!empty($data['AccountServicePackageID'])) {
            $oldAccountServicePackage = AccountServicePackage::findOrFail($data['AccountServicePackageID']);

            // if this cli already exist in table
            $check = false;
            if($rate_tables['Status'] == 1 || $data['PackageID'] != $oldAccountServicePackage->PackageId)
                $check = AccountServicePackage::where([
                    'CompanyID'=>$CompanyID,
                    'AccountID'=>$data['AccountID'],
                    'AccountServiceID' =>  $data['AccountServiceID'],
                    'PackageId'=>  $data['PackageID'],
                    'Status'=>1
                ])->where("AccountServicePackageID", "!=", $data['AccountServicePackageID'])->whereBetween('PackageStartDate', array($data['PackageStartDate'], $data['PackageEndDate']));
            $check1 = AccountServicePackage::where([
                'CompanyID'=>$CompanyID,
                'AccountID'=>$data['AccountID'],
                'AccountServiceID' =>  $data['AccountServiceID'],
                'PackageId'=>  $data['PackageID'],
                'Status'=>1
            ])->where("AccountServicePackageID", "!=", $data['AccountServicePackageID'])->whereBetween('PackageEndDate', array($data['PackageStartDate'], $data['PackageEndDate']));
            $check2 = AccountServicePackage::where([
                'CompanyID'=>$CompanyID,
                'AccountID'=>$data['AccountID'],
                'AccountServiceID' =>  $data['AccountServiceID'],
                'PackageId'=>  $data['PackageID'],
                'Status'=>1
            ])->where("AccountServicePackageID", "!=", $data['AccountServicePackageID'])->where('PackageEndDate','>=',$data['PackageStartDate'])->where('PackageStartDate','<=',$data['PackageStartDate']);



            if($check != false && $check->count() > 0 || $check1 != false && $check1->count() > 0 || $check2 != false && $check2->count() > 0){
                $message = 'Selected Package already exists between package start date ' . $data['PackageStartDate'] . ' and  package end data ' . '.<br>';
                return Response::json(array("status" => "error", "message" => $message));
            }

            $query = 'call prc_getRateTableVendor (' . $rate_tables['RateTableID'] .",'" .
                '' . "','" . ''. "','" . ''. "','" . '0' . "','" . $rate_tables['PackageID'] .
                "','" . '' . "'" . ')';
            $results = DB::select($query);
            Log::info("package_update prc" . $query);
            $VendorID = '';
            foreach($results as $result){
                $VendorID = $result->VendorID;
            }
            if (!empty($VendorID)) {
                $rate_tables['VendorID'] = $VendorID;
            }


            $oldAccountServicePackage->update($rate_tables);
        }
        return Response::json(array("status" => "success", "message" => "Package Updated Successfully"));
    }

    public function BulkAction(){
        $data = Input::all();
        $update_billing=0;
        $accountbillngdata=0;
        $ManualBilling = isset($data['BillingCycleType']) && $data['BillingCycleType'] == 'manual'?1:0;
        $ServiceID = 0;
        $ResellerOwner=0;
        $ResellerAccountOwnerUpdate=0;
        if(
            !isset($data['OwnerCheck']) &&
            !isset($data['CurrencyCheck']) &&
            !isset($data['VendorCheck']) &&
            !isset($data['BillingCheck']) &&
            !isset($data['CustomerCheck'])&&
            !isset($data['ResellerCheck'])&&
            !isset($data['CustomerPaymentAddCheck'])&&
            !isset($data['ResellerOwnerAddCheck'])&&
            !isset($data['BulkBillingClassCheck'])&&
            !isset($data['BulkBillingTypeCheck'])&&
            !isset($data['BulkBillingTimezoneCheck'])&&
            !isset($data['BulkBillingStartDateCheck'])&&
            !isset($data['BulkBillingCycleTypeCheck'])&&
            !isset($data['BulkSendInvoiceSettingCheck'])&&
            !isset($data['BulkAutoPaymentSettingCheck']) &&
            !isset($data['BulkAutoPaymentMethodCheck'])
        )
        {
            return Response::json(array("status" => "error", "message" => "Please select at least one option."));
        }
        elseif(!isset($data['BulkselectedIDs']) || empty($data['BulkselectedIDs']))
        {
            return Response::json(array("status" => "error", "message" => "Please select at least one Account."));
        }


        $update = [];
        $billingupdate = array();
        $currencyupdate = array();
        $AccountDetails = array();
        $AccountDetailUpdate=0;
        if(isset($data['account_owners']) && $data['account_owners'] != 0 && isset($data['OwnerCheck'])){
            $update['Owner'] = $data['account_owners'];
        }
        if(isset($data['Currency']) && $data['Currency'] != 0 && isset($data['CurrencyCheck'])){
            $currencyupdate['CurrencyId'] = $data['Currency'];
        }
        if(isset($data['VendorCheck'])){
            $update['IsVendor'] = isset($data['vendor_on_off'])?1:0;
        }
        if(isset($data['CustomerCheck'])){
            $update['IsCustomer'] = isset($data['Customer_on_off'])?1:0;
        }
        if(isset($data['ResellerCheck'])){
            $update['IsReseller'] = isset($data['Reseller_on_off'])?1:0;
        }
        if(isset($data['CustomerPaymentAddCheck'])){
            $AccountDetailUpdate=1;
            $AccountDetails['CustomerPaymentAdd'] = isset($data['customerpayment_on_off'])?1:0;
        }
        if(isset($data['ResellerOwnerAddCheck']) && !empty($data['ResellerOwner'])){
            $ResellerAccountOwnerUpdate=1;
            $ResellerOwner = empty($data['ResellerOwner']) ? 0 : $data['ResellerOwner'];
        }

        if(isset($data['BillingCheck'])){
            $billing_on_off = isset($data['billing_on_off'])?1:0;
            //\Illuminate\Support\Facades\Log::info('billing -- '.$billing_on_off);
            if(!empty($billing_on_off)){
                Account::$billingrules['BillingClassID'] = 'required';
                Account::$billingrules['BillingType'] = 'required';
                Account::$billingrules['BillingTimezone'] = 'required';

                Account::$billingrules['BillingCycleType'] = 'required';
                if(isset($data['BillingCycleValue'])){
                    Account::$billingrules['BillingCycleValue'] = 'required';
                }
                if($ManualBilling ==0) {
                    Account::$billingrules['BillingStartDate'] = 'required';
                }

                $validator = Validator::make($data, Account::$billingrules, Account::$billingmessages);
                if ($validator->fails()) {
                    return json_validator_response($validator);
                }
                $update['Billing'] = 1;
            }else{
                $update['Billing'] = 0;
            }
        }else{
            if(isset($data['BulkBillingClassCheck'])){
                $update_billing=1;
                if(!empty($data['BillingClassID'])){
                    $billingupdate['BillingClassID'] = $data['BillingClassID'];
                }
                Account::$billingrules['BillingClassID'] = 'required';
            }
            if(isset($data['BulkBillingTypeCheck'])){
                $update_billing=1;
                if(!empty($data['BillingType'])){
                    $billingupdate['BillingType'] = $data['BillingType'];
                }
                Account::$billingrules['BillingType'] = 'required';
            }
            if(isset($data['BulkBillingTimezoneCheck'])){
                $update_billing=1;
                if(!empty($data['BillingTimezone'])){
                    $billingupdate['BillingTimezone'] = $data['BillingTimezone'];
                }
                Account::$billingrules['BillingTimezone'] = 'required';
            }
            if(isset($data['BulkBillingStartDateCheck'])){
                $update_billing=1;
                if(!empty($data['BillingStartDate'])){
                    $accountbillngdata = 1;
                    $billingupdate['BillingStartDate'] = $data['BillingStartDate'];
                }
                Account::$billingrules['BillingStartDate'] = 'required';
            }
            if(isset($data['BulkBillingCycleTypeCheck'])){
                $update_billing=1;
                if(!empty($data['BillingCycleType'])){
                    $accountbillngdata = 1;
                    $billingupdate['BillingCycleType'] = $data['BillingCycleType'];
                    if(isset($data['BillingCycleValue'])){
                        Account::$billingrules['BillingCycleValue'] = 'required';
                        $billingupdate['BillingCycleValue'] = $data['BillingCycleValue'];
                    }else{
                        $billingupdate['BillingCycleValue'] = '';
                    }
                }
                Account::$billingrules['BillingCycleType'] = 'required';
            }
            if(isset($data['BulkSendInvoiceSettingCheck'])){
                if(!empty($data['SendInvoiceSetting'])){
                    $update_billing=1;
                    $billingupdate['SendInvoiceSetting'] = $data['SendInvoiceSetting'];
                }
            }
            if(isset($data['BulkAutoPaymentSettingCheck'])){
                if(!empty($data['AutoPaymentSetting'])){
                    $update_billing=1;
                    $billingupdate['AutoPaymentSetting'] = $data['AutoPaymentSetting'];
                }
            }
            if(isset($data['BulkAutoPaymentMethodCheck'])){
                if(!empty($data['AutoPayMethod'])){
                    $update_billing=1;
                    $billingupdate['AutoPayMethod'] = $data['AutoPayMethod'];
                }
            }

            $validator = Validator::make($data, Account::$billingrules, Account::$billingmessages);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
        }

        if(!empty($data['BulkActionCriteria'])){
            $criteria = json_decode($data['BulkActionCriteria'], true);
            $BulkselectedIDs = $this->getAccountsByCriteria($criteria);
            $selectedIDs = array_filter(explode(',',$BulkselectedIDs));
            //\Illuminate\Support\Facades\Log::info('--criteria-- '.$BulkselectedIDs);
        }else{
            //\Illuminate\Support\Facades\Log::info('--ids-- '.$data['BulkselectedIDs']);
            $selectedIDs = array_filter(explode(',',$data['BulkselectedIDs']));
        }

        //$selectedIDs = explode(',',$data['BulkselectedIDs']);
        try{
            //Implement loop because boot is triggering for each updated record to log the changes.
            foreach ($selectedIDs as $id)
            {
                $ResellerCount = Account::where("IsReseller",'=',1)->where("AccountID",$id)->count();

                $ResellerCompanyID = Account::where("AccountID",$id)->pluck('CompanyId');
                $CompanyID = User::get_companyID();
                //\Illuminate\Support\Facades\Log::info("reseller companyID".$CompanyID);
                /*if current companyid and account companyid is differnt that means it reseller account*/
                if($ResellerCompanyID!=$CompanyID){
                    //\Illuminate\Support\Facades\Log::info("reseller account");
                    unset($update['IsReseller']);
                    unset($update['Billing']);
                    $billing_on_off=0;
                    $update_billing=0;
                }else{
                    if($ResellerAccountOwnerUpdate==1 && $ResellerCount==0) {
                        //log::info('IsReseller is on');
                        //log::info('ResellerOwner '.$ResellerOwner);
                        $Reseller = Reseller::getResellerDetails($ResellerOwner);
                        $NewResellerCompanyID = $Reseller->ChildCompanyID;
                        $ResellerUser = User::where('CompanyID', $NewResellerCompanyID)->first();
                        $ResellerUserID = $ResellerUser->UserID;
                        $update['Owner'] = $ResellerUserID;
                        $update['CompanyID'] = $NewResellerCompanyID;
                        unset($update['IsReseller']);
                    }
                }

                //\Illuminate\Support\Facades\Log::info('Account id -- '.$id);
                //\Illuminate\Support\Facades\Log::info(print_r($update,true));
                DB::beginTransaction();
                $upcurrencyaccount = Account::find($id);
                if(empty($upcurrencyaccount->CurrencyId) && isset($currencyupdate['CurrencyId'])){
                    $upcurrencyaccount->update($currencyupdate);
                }
                $upaccount = Account::find($id);
                $upaccount->update($update);
                //Account::where(['AccountID'=>$id])->update($update);
                /** Account Details Update
                 */
                if($AccountDetailUpdate==1) {
                    $AccountDetailsID = AccountDetails::where('AccountID', $id)->pluck('AccountDetailID');
                    $AccountDetails['AccountID']=$id;
                    if (!empty($AccountDetailsID)) {
                        AccountDetails::find($AccountDetailsID)->update($AccountDetails);
                    } else {
                        AccountDetails::create($AccountDetails);
                    }
                }


                $invoice_count = Account::getInvoiceCount($id);
                //new billing
                if(isset($data['BillingCheck']) && !empty($billing_on_off)) {
                    //\Illuminate\Support\Facades\Log::info('--update billing--');
                    $count = AccountBilling::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->count();
                    if($count==0){
                        //billing section start
                        $BillingCycleType= $data['BillingCycleType'];
                        $BillingCycleValue= $data['BillingCycleValue'];
                        if($ManualBilling ==0) {
                            $data['LastInvoiceDate'] = $data['BillingStartDate'];
                            $BillingStartDate = strtotime($data['BillingStartDate']);
                            $data['NextInvoiceDate'] = next_billing_date($BillingCycleType, $BillingCycleValue, $BillingStartDate);
                            $data['NextChargeDate'] = date('Y-m-d', strtotime('-1 day', strtotime($data['NextInvoiceDate'])));
                        }
                        AccountBilling::insertUpdateBilling($id, $data, $ServiceID, $invoice_count);
                        if ($ManualBilling == 0) {
                            AccountBilling::storeFirstTimeInvoicePeriod($id, $ServiceID);
                        }
                    }else{
                        //\Illuminate\Support\Facades\Log::info('-- AllReady Billing set. No Billing Change--');
                    }
                    //\Illuminate\Support\Facades\Log::info('--update billing over--');
                }

                if(!empty($update_billing) && $update_billing==1){
                    $count = AccountBilling::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->count();
                    $billing_on_off = isset($data['billing_on_off'])?1:0;
                    $AccBilling = Account::where(['AccountID'=>$id])->pluck('Billing');
                    //if billing than update account
                    //log::info('Update Billing '.$count.' - '.$update_billing.' - '.$billing_on_off.' - '.$AccBilling);
                    if($count>0 && ($billing_on_off==1 || $AccBilling==1)){
                        //AccountBilling::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->update($billingupdate);
                        if(!empty($accountbillngdata) && $accountbillngdata==1){
                            $abdata = AccountBilling::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->first();

                            if(empty($billingupdate['BillingCycleType'])){
                                $billingupdate['BillingCycleType'] = $abdata->BillingCycleType;
                            }
                            if(empty($billingupdate['BillingCycleValue'])){
                                $billingupdate['BillingCycleValue'] = $abdata->BillingCycleValue;
                            }
                            if(empty($billingupdate['BillingStartDate'])){
                                $billingupdate['BillingStartDate'] = $abdata->BillingStartDate;
                            }
                            $billingupdate['LastInvoiceDate'] = $billingupdate['BillingStartDate'];
                            $billingupdate['LastChargeDate'] = $billingupdate['BillingStartDate'];
                            $BillingCycleType= $billingupdate['BillingCycleType'];
                            $BillingCycleValue= $billingupdate['BillingCycleValue'];
                            if($ManualBilling ==0) {
                                $BillingStartDate = strtotime($billingupdate['BillingStartDate']);
                                $NextBillingDate = next_billing_date($BillingCycleType, $BillingCycleValue, $BillingStartDate);
                                $billingupdate['NextInvoiceDate'] = $NextBillingDate;
                                if ($NextBillingDate != '') {
                                    $NextChargedDate = date('Y-m-d', strtotime('-1 day', strtotime($NextBillingDate)));
                                    $billingupdate['NextChargeDate'] = $NextChargedDate;
                                }
                            }

                            if($invoice_count==0) {
                                AccountBilling::insertUpdateBilling($id, $billingupdate, $ServiceID, $invoice_count);
                                if($ManualBilling ==0) {
                                    AccountBilling::storeFirstTimeInvoicePeriod($id, $ServiceID);
                                }
                            }else{
                                //\Illuminate\Support\Facades\Log::info('-- Allready Billing set. No Billing Change.count 0 --');
                            }

                        }else{
                            AccountBilling::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->update($billingupdate);
                        }
                    }else{
                        //\Illuminate\Support\Facades\Log::info('-- Allready Billing set. No Billing Change--');
                    }

                }
                //billing section end

                DB::commit();
            }
            return Response::json(array("status" => "success", "message" => "Accounts Updated Successfully"));
        }catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            return Response::json(array("status" => "error", "message" => $e->getMessage()));
        }
    }

    public function getAccountsByCriteria($data=array()){

        $CompanyID = User::get_companyID();
        $userID = 0;
        if (User::is('AccountManager')) { // Account Manager
            $userID = $userID = User::get_userID();
        }elseif(User::is_admin() && isset($data['account_owners'])  && trim($data['account_owners']) > 0) {
            $userID = (int)$data['account_owners'];
        }
        $data['vendor_on_off'] = $data['vendor_on_off']== 'true'?1:0;
        $data['customer_on_off'] = $data['customer_on_off']== 'true'?1:0;
        $data['reseller_on_off'] = $data['reseller_on_off']== 'true'?1:0;
        $data['account_active'] = $data['account_active']== 'true'?1:0;
        $data['low_balance'] = $data['low_balance']== 'true'?1:0;
        $data['ResellerOwner'] = empty($data['ResellerOwner']) ? 0 : $data['ResellerOwner'];
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        if(is_reseller()){
            $data['ResellerOwner'] = Reseller::getResellerID();
        }

        $query = "call prc_GetAccounts (".$CompanyID.",".$userID.",".$data['vendor_on_off'].",".$data['customer_on_off'].",".$data['reseller_on_off'].",".$data['ResellerOwner'].",".$data['account_active'].",".$data['verification_status'].",'".$data['account_number']."','".$data['contact_name']."','".$data['account_name']."','".$data['tag']."','".$data["ipclitext"]."','".$data['low_balance']."',1,50,'AccountName','asc',2)";
        $excel_data  = DB::select($query);
        $excel_datas = json_decode(json_encode($excel_data),true);

        //\Illuminate\Support\Facades\Log::info(print_r($excel_data,true));

        $selectedIDs='';
        foreach($excel_datas as $exceldata){
            $selectedIDs.= $exceldata['AccountID'].',';
        }

        return $selectedIDs;

    }

    public function getNextBillingDate(){
        $data = Input::all();
        $BillingStartDate= strtotime($data['BillingStartDate']);
        $BillingCycleType= $data['BillingCycleType'];
        $BillingCycleValue= $data['BillingCycleValue'];
        $NextChargedDate='';
        $NextBillingDate = next_billing_date($BillingCycleType, $BillingCycleValue, $BillingStartDate);
        if($NextBillingDate!=''){
            $NextChargedDate = date('Y-m-d', strtotime('-1 day', strtotime($NextBillingDate)));
        }
        return Response::json(array("status" => "success", "NextBillingDate" => $NextBillingDate,"NextChargedDate" => $NextChargedDate));
    }

    public function getAccountTaxes(){
        $data = Input::all();
        $Taxes = '';
        $CompanyID = $data['CompanyID'];
        $CompanyID = getParentCompanyIdIfReseller($CompanyID);
        $Country = $data['Country'];
        $RegisterDutchFoundation = 0;
        $DutchProvider = 0;
        if(isset($data['RegisterDutchFoundation']) && $data['RegisterDutchFoundation']=='true'){
            $RegisterDutchFoundation=1;
        }
        if(isset($data['DutchProvider']) && $data['DutchProvider']=='true'){
            $DutchProvider=1;
        }
        if($Country=='NETHERLANDS'){
            $EUCountry = 'NL';
        }else{
            $EUCountry = Country::where('Country',$Country)->pluck('EUCountry');
            $EUCountry = empty($EUCountry) ? 'NEU' : 'EU';
        }
        $Results = TaxRate::where(['DutchProvider'=>$DutchProvider,'DutchFoundation'=>$RegisterDutchFoundation,'Country'=>$EUCountry,'CompanyId'=>$CompanyID,'Status'=>1])->get();
        //log::info(print_r($Results,true));
        if(!empty($Results)){
            foreach($Results as $result){
                $Taxes.=$result->TaxRateId.',';
            }
            $Taxes = rtrim($Taxes, ',');
        }
        $Taxes = explode(",", $Taxes);

        return Response::json(array("status" => "success", "Taxes" => $Taxes));
    }
}
