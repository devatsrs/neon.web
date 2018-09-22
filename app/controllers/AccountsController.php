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

        $BillingClass = BillingClass::getDropdownIDList(User::get_companyID());
        $timezones = TimeZone::getTimeZoneDropdownList();
        $reseller_owners = Reseller::getDropdownIDList(User::get_companyID());
        return View::make('accounts.index', compact('account_owners', 'emailTemplates', 'templateoption', 'accounts', 'accountTags', 'privacy', 'type', 'trunks', 'rate_sheet_formates','boards','opportunityTags','accounts','leadOrAccount','leadOrAccountCheck','opportunitytags','leadOrAccountID','bulk_type','Currencies','BillingClass','timezones','reseller_owners'));

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

            $currencies = Currency::getCurrencyDropdownIDList();
            $timezones = TimeZone::getTimeZoneDropdownList();
            $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();
            $BillingClass = BillingClass::getDropdownIDList($company_id);
            $BillingStartDate=date('Y-m-d');
            $LastAccountNo =  '';
            $doc_status = Account::$doc_status;
            if(!User::is_admin()){
                unset($doc_status[Account::VERIFIED]);
            }
            $dynamicfields = Account::getDynamicfields('account',0);
            $reseller_owners = Reseller::getDropdownIDList($company_id);
            return View::make('accounts.create', compact('account_owners', 'countries','LastAccountNo','doc_status','currencies','timezones','InvoiceTemplates','BillingStartDate','BillingClass','dynamicfields','company','reseller_owners'));
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
                $ResellerUser =User::where('CompanyID',$ResellerCompanyID)->first();
                $ResellerUserID = $ResellerUser->UserID;
                $companyID=$ResellerCompanyID;
                $data['Owner'] = $ResellerUserID;
            }
            $data['CompanyID'] = $companyID;
            $data['AccountType'] = 1;
            $data['IsVendor'] = isset($data['IsVendor']) ? 1 : 0;
            $data['IsCustomer'] = isset($data['IsCustomer']) ? 1 : 0;
            $data['IsReseller'] = isset($data['IsReseller']) ? 1 : 0;
            $data['Billing'] = isset($data['Billing']) ? 1 : 0;
            $data['created_by'] = User::get_user_full_name();
            $data['AccountType'] = 1;
            $data['AccountName'] = trim($data['AccountName']);

            if (isset($data['accountgateway'])) {
                $AccountGateway = implode(',', array_filter(array_unique($data['accountgateway'])));
                unset($data['accountgateway']);
            }else{
                $AccountGateway = '';
            }
            /**
             * If Reseller on backend customer is on
            */
            if($data['IsReseller']==1){
                $data['IsCustomer']=1;
                $data['IsVendor']=0;
             }

            unset($data['ResellerOwner']);

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
            Account::$rules['BillingTimezone'] = 'required';
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

            if(DynamicFields::where(['CompanyID' => $companyID, 'Type' => 'account', 'FieldSlug' => 'vendorname', 'Status' => 1])->count() > 0 && $data['IsVendor'] == 1) {
                Account::$rules['vendorname'] = 'required';
                Account::$messages['vendorname.required'] = 'The Vendor Name field is required.';
            }

            $validator = Validator::make($data, Account::$rules, Account::$messages);

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

            if (isset($data['autoblock'])) {
                $autoblock = $data['autoblock'];
                unset($data['autoblock']);
            }else{
                $autoblock = 0;
            }
            if ($account = Account::create($data)) {

                $DynamicData = array();
                $DynamicData['CompanyID']= $companyID;
                $DynamicData['AccountID']= $account->AccountID;

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
        //$companyID = User::get_companyID();
        $account_owners = User::getOwnerUsersbyRole();
        $countries = $this->countries;
        $tags = json_encode(Tags::getTagsArray());
        $products = Product::getProductDropdownList($companyID);
        $taxes = TaxRate::getTaxRateDropdownIDListForInvoice(0,$companyID);
        $currencies = Currency::getCurrencyDropdownIDList();
        $timezones = TimeZone::getTimeZoneDropdownList();
        $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();
        $BillingClass = BillingClass::getDropdownIDList($companyID);

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
        $DiscountPlan = DiscountPlan::getDropdownIDList($companyID,(int)$account->CurrencyId);
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
        $accountdetails = AccountDetails::where(['AccountID'=>$id])->first();
        $reseller_owners = Reseller::getDropdownIDList(User::get_companyID());
        $accountreseller = Reseller::where('ChildCompanyID',$companyID)->pluck('ResellerID');
        $DiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'Type'=>AccountDiscountPlan::OUTBOUND,'ServiceID'=>0,'AccountSubscriptionID'=>0,'SubscriptionDiscountPlanID'=>0))->pluck('DiscountPlanID');
        $InboundDiscountPlanID = AccountDiscountPlan::where(array('AccountID'=>$id,'Type'=>AccountDiscountPlan::INBOUND,'ServiceID'=>0,'AccountSubscriptionID'=>0,'SubscriptionDiscountPlanID'=>0))->pluck('DiscountPlanID');
        return View::make('accounts.edit', compact('account', 'account_owners', 'countries','AccountApproval','doc_status','currencies','timezones','taxrates','verificationflag','InvoiceTemplates','invoice_count','all_invoice_count','tags','products','taxes','opportunityTags','boards','accounts','leadOrAccountID','leadOrAccount','leadOrAccountCheck','opportunitytags','DiscountPlan','DiscountPlanID','InboundDiscountPlanID','AccountBilling','AccountNextBilling','BillingClass','decimal_places','rate_table','services','ServiceID','billing_disable','hiden_class','dynamicfields','ResellerCount','accountdetails','reseller_owners','accountreseller'));
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
        $companyID = User::get_companyID();
        $ResellerOwner = empty($data['ResellerOwner']) ? 0 : $data['ResellerOwner'];
        if($ResellerOwner>0){
            $Reseller = Reseller::getResellerDetails($ResellerOwner);
            $ResellerCompanyID = $Reseller->ChildCompanyID;
            $ResellerUser =User::where('CompanyID',$ResellerCompanyID)->first();
            $ResellerUserID = $ResellerUser->UserID;
            $companyID=$ResellerCompanyID;
            $data['Owner'] = $ResellerUserID;
        }
        $account = Account::find($id);
        if(isset($data['tags'])){
            Tags::insertNewTags(['tags'=>$data['tags'],'TagType'=>Tags::Account_tag]);
        }
        //$DiscountPlanID = $data['DiscountPlanID'];
        //$InboundDiscountPlanID = $data['InboundDiscountPlanID'];

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
        $data['Billing'] = isset($data['Billing']) ? 1 : 0;
        $data['updated_by'] = User::get_user_full_name();
		$data['AccountName'] = trim($data['AccountName']);
		$data['ShowAllPaymentMethod'] = isset($data['ShowAllPaymentMethod']) ? 1 : 0;
		$data['DisplayRates'] = isset($data['DisplayRates']) ? 1 : 0;

        if($data['IsReseller']==1){
            $data['IsCustomer']=1;
            $data['IsVendor']=0;
        }

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
            Account::$rules['BillingTimezone'] = 'required';
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

        if(DynamicFields::where(['CompanyID' => $companyID, 'Type' => 'account', 'FieldSlug' => 'vendorname', 'Status' => 1])->count() > 0 && $data['IsVendor'] == 1) {
            Account::$rules['vendorname'] = 'required';
            Account::$messages['vendorname.required'] = 'The Vendor Name field is required.';
        }
        $validator = Validator::make($data, Account::$rules,Account::$messages);

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

        if ($account->update($data)) {

            $DynamicData = array();
            $DynamicData['CompanyID']= $companyID;
            $DynamicData['AccountID']= $id;

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
                if(!empty($AccountPeriod)) {
                    $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                    $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                    $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                    $ServiceID=0;
                    $AccountSubscriptionID = 0;
                    $AccountName='';
                    $AccountCLI='';
                    $SubscriptionDiscountPlanID=0;
                    AccountDiscountPlan::addUpdateDiscountPlan($id, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff,$ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                    AccountDiscountPlan::addUpdateDiscountPlan($id, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff,$ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
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
        $getdata['AccountID'] = $id;
        $response = AccountBalance::where('AccountID', $id)->first(['AccountID', 'PermanentCredit', 'UnbilledAmount', 'EmailToCustomer', 'TemporaryCredit', 'TemporaryCreditDateTime', 'BalanceThreshold', 'BalanceAmount', 'VendorUnbilledAmount']);
        $PermanentCredit = $BalanceAmount = $TemporaryCredit = $BalanceThreshold = $UnbilledAmount = $VendorUnbilledAmount = $EmailToCustomer = $SOA_Amount = 0;
        if (!empty($response)) {
            if (!empty($response->PermanentCredit)) {
                $PermanentCredit = $response->PermanentCredit;
            }
            if (!empty($response->TemporaryCredit)) {
                $TemporaryCredit = $response->TemporaryCredit;
            }
            if (!empty($response->BalanceThreshold)) {
                $BalanceThreshold = $response->BalanceThreshold;
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
        return View::make('accounts.credit', compact('account','AccountAuthenticate','PermanentCredit','TemporaryCredit','BalanceThreshold','BalanceAmount','UnbilledAmount','EmailToCustomer','VendorUnbilledAmount','SOA_Amount'));
    }

    public function update_credit(){
        $data = Input::all();
        $postdata= $data;
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
        return View::make('accounts.expense',compact('id','CurrencySymbol'));
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
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $rate_tables = CLIRateTable::
        leftJoin('tblRateTable','tblRateTable.RateTableId','=','tblCLIRateTable.RateTableID')
        ->leftJoin('tblService','tblService.ServiceID','=','tblCLIRateTable.ServiceID')
            ->select(['CLIRateTableID','CLI','tblRateTable.RateTableName','CLIRateTableID','tblService.ServiceName'])
            ->where("tblCLIRateTable.CompanyID",$CompanyID)
            ->where("tblCLIRateTable.AccountID",$id);
        if(!empty($data['CLIName'])){
            $rate_tables->WhereRaw('CLI like "%'.$data['CLIName'].'%"');
        }
        if(!empty($data['ServiceID'])){
            $rate_tables->where('tblCLIRateTable.ServiceID','=',$data['ServiceID']);
        }
        /*
        else{
            $rate_tables->where('tblCLIRateTable.ServiceID','=',0);
        }*/
        return Datatables::of($rate_tables)->make();
    }
    public function clitable_store(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $message = '';

        $rules['CLI'] = 'required';

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $clis = array_filter(preg_split("/\\r\\n|\\r|\\n/", $data['CLI']),function($var){return trim($var)!='';});

        AccountAuthenticate::add_cli_rule($CompanyID,$data);

        foreach($clis as $cli){

            if(CLIRateTable::where(array('CompanyID'=>$CompanyID, 'CLI'=>$cli))->count()){
                $AccountID = CLIRateTable::where(array('CompanyID'=>$CompanyID,'CLI'=>$cli))->pluck('AccountID');
                $message .= $cli.' already exist against '.Account::getCompanyNameByID($AccountID).'.<br>';
            }else{
                $rate_tables['CLI'] = $cli;
                $rate_tables['RateTableID'] = $data['RateTableID'];
                $rate_tables['AccountID'] = $data['AccountID'];
                $rate_tables['CompanyID'] = $CompanyID;
                if(!empty($data['ServiceID'])) {
                    $rate_tables['ServiceID'] = $data['ServiceID'];
                }
                CLIRateTable::insert($rate_tables);
            }
        }

        if(!empty($message)){
            $message = 'Following CLI already exists.<br>'.$message;
            return Response::json(array("status" => "error", "message" => $message));
        }else{
            return Response::json(array("status" => "success", "message" => "CLI Successfully Added"));
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
        AccountAuthenticate::add_cli_rule($CompanyID,$data);
        if ($CLIRateTableID > 0) {
            $CLIs = CLIRateTable::where(array('CLIRateTableID' => $CLIRateTableID))->pluck('CLI');
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
        $query = "call prc_unsetCDRUsageAccount ('" . $CompanyID . "','" . $CLIs . "','".$Date."',".$Confirm.",".$ServiceID.")";
        $recordFound = DB::Connection('sqlsrvcdr')->select($query);
        if($recordFound[0]->Status>0){
            return Response::json(array("status" => "check","check"=>1));
        }
        if ($CLIRateTableID > 0) {
            //CLIRateTable::where(array('CLIRateTableID' => $CLIRateTableID))->where(array('ServiceID' => $ServiceID))->delete();
            CLIRateTable::where(array('CLIRateTableID' => $CLIRateTableID))->delete();
        } else if (!empty($data['criteria'])) {
            $criteria = json_decode($data['criteria'], true);
            CLIRateTable::WhereRaw('CLI like "%' . $criteria['CLIName'] . '%"')
                //->where(array('ServiceID' => $ServiceID))
                ->where(array('AccountID' => $data['AccountID']))->delete();
        } else if (!empty($data['CLIRateTableIDs'])) {
            $CLIRateTableIDs = explode(',', $data['CLIRateTableIDs']);
            //CLIRateTable::whereIn('CLIRateTableID', $CLIRateTableIDs)->where(array('ServiceID' => $ServiceID))->delete();
            CLIRateTable::whereIn('CLIRateTableID', $CLIRateTableIDs)->delete();
        }

        return Response::json(array("status" => "success", "message" => "CLI Deleted Successfully"));
    }

    public function clitable_update(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        AccountAuthenticate::add_cli_rule($CompanyID,$data);
        if (!empty($data['criteria'])) {
            $criteria = json_decode($data['criteria'], true);
            $query = CLIRateTable::WhereRaw('CLI like "%' . $criteria['CLIName'] . '%"');
                //$query->where(array('ServiceID' => $ServiceID));
                $query->where(array('AccountID' => $data['AccountID']));
                $query->update(array('RateTableID' => $data['RateTableID']));
        } else if (!empty($data['CLIRateTableIDs'])) {
            $CLIRateTableIDs = explode(',', $data['CLIRateTableIDs']);
            $query = CLIRateTable::whereIn('CLIRateTableID', $CLIRateTableIDs);
           // $query->where(array('ServiceID' => $ServiceID));
            $query->update(array('RateTableID' => $data['RateTableID']));
        }
        return Response::json(array("status" => "success", "message" => "CLI Updated Successfully"));
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
            \Illuminate\Support\Facades\Log::info('billing -- '.$billing_on_off);
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
            \Illuminate\Support\Facades\Log::info('--criteria-- '.$BulkselectedIDs);
        }else{
            \Illuminate\Support\Facades\Log::info('--ids-- '.$data['BulkselectedIDs']);
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
                \Illuminate\Support\Facades\Log::info("reseller companyID".$CompanyID);
                /*if current companyid and account companyid is differnt that means it reseller account*/
                if($ResellerCompanyID!=$CompanyID){
                    \Illuminate\Support\Facades\Log::info("reseller account");
                    unset($update['IsReseller']);
                    unset($update['Billing']);
                    $billing_on_off=0;
                    $update_billing=0;
                }else{
                    if($ResellerAccountOwnerUpdate==1 && $ResellerCount==0) {
                        log::info('IsReseller is on');
                        log::info('ResellerOwner '.$ResellerOwner);
                        $Reseller = Reseller::getResellerDetails($ResellerOwner);
                        $NewResellerCompanyID = $Reseller->ChildCompanyID;
                        $ResellerUser = User::where('CompanyID', $NewResellerCompanyID)->first();
                        $ResellerUserID = $ResellerUser->UserID;
                        $update['Owner'] = $ResellerUserID;
                        $update['CompanyID'] = $NewResellerCompanyID;
                        unset($update['IsReseller']);
                    }
                }

                \Illuminate\Support\Facades\Log::info('Account id -- '.$id);
                \Illuminate\Support\Facades\Log::info(print_r($update,true));
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
                    \Illuminate\Support\Facades\Log::info('--update billing--');
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
                        \Illuminate\Support\Facades\Log::info('-- AllReady Billing set. No Billing Change--');
                    }
                    \Illuminate\Support\Facades\Log::info('--update billing over--');
                }

                if(!empty($update_billing) && $update_billing==1){
                    $count = AccountBilling::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->count();
                    $billing_on_off = isset($data['billing_on_off'])?1:0;
                    $AccBilling = Account::where(['AccountID'=>$id])->pluck('Billing');
                    //if billing than update account
                    log::info('Update Billing '.$count.' - '.$update_billing.' - '.$billing_on_off.' - '.$AccBilling);
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
                                \Illuminate\Support\Facades\Log::info('-- Allready Billing set. No Billing Change.count 0 --');
                            }

                        }else{
                            AccountBilling::where(['AccountID'=>$id,'ServiceID'=>$ServiceID])->update($billingupdate);
                        }
                    }else{
                        \Illuminate\Support\Facades\Log::info('-- Allready Billing set. No Billing Change--');
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

        \Illuminate\Support\Facades\Log::info(print_r($excel_data,true));

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
}
