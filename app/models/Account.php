<?php

class Account extends \Eloquent {
    protected $guarded = array("AccountID");

    protected $table = 'tblAccount';

    protected  $primaryKey = "AccountID";

    const  NOT_VERIFIED = 0;
    //const  PENDING_VERIFICATION = 1;
    const  VERIFIED =2;
    public static $doc_status = array( self::NOT_VERIFIED => 'Not Verified',self::VERIFIED=>'Verified');

    const  DETAIL_CDR = 1;
    const  SUMMARY_CDR= 2;
    const  NO_CDR = 3;
	
	public static $SupportSlug	=	'support';
    public static $cdr_type = array(''=>'Select' ,self::DETAIL_CDR => 'Detail CDR',self::SUMMARY_CDR=>'Summary CDR');


    public static $rules = array(
        'Owner' =>      'required',
        'CompanyID' =>  'required',
        'Country'=>'required',
        'Number' =>  'required|unique:tblAccount,Number',
        'AccountName' => 'required|unique:tblAccount,AccountName',
        'CurrencyId' => 'required',

    );
    /** add columns here to save in table  */
    protected $fillable = array(
        'AccountType','CompanyID','CurrencyId','Title','Owner',
        'Number', 'AccountName', 'NamePrefix','FirstName','LastName',
        'LeadStatus', 'Rating', 'LeadSource','Skype','EmailOptOut',
        'Twitter', 'SecondaryEmail', 'Email','IsVendor','IsCustomer',
        'IsReseller', 'Ownership', 'Website','Mobile','Phone',
        'Fax', 'Employee', 'Description','Address1','Address2',
        'Address3', 'City', 'State','PostCode','Country', 'LanguageID',
        'RateEmail', 'BillingEmail', 'ResellerEmail','TechnicalEmail','VatNumber',
        'Status', 'PaymentMethod', 'PaymentDetail','Converted','ConvertedDate',
        'ConvertedBy', 'TimeZone', 'VerificationStatus','Subscription','SubscriptionQty',
        'created_at', 'created_by', 'updated_at','updated_by','password',
        'ResellerPassword', 'Picture', 'AutorizeProfileID','tags','Autopay',
        'NominalAnalysisNominalAccountNumber', 'InboudRateTableID', 'Billing','ShowAllPaymentMethod',
        'DisplayRates'
    );

    public static $messages = array(
        'CurrencyId.required' =>'The currency field is required',
        'BillingCycleType.required' =>'Billing Cycle field is required',
        'BillingCycleValue.required' =>'Billing Cycle Value field is required',
    );

    public static $importrules = array(
        'selection.AccountName' => 'required'
    );

    public static $importleadrules = array(
            'selection.AccountName' => 'required',
            'selection.FirstName'=>'required',
            'selection.LastName'=>'required',
        );

    public static $importmessages = array(
        'selection.AccountName.required' =>'The Account Name field is required'
    );

    public static $importleadmessages = array(
        'selection.AccountName.required' =>'The Company Name field is required',
        'selection.FirstName.required' =>'The First Name field is required',
        'selection.LastName.required' =>'The Last Name field is required'
    );

    public static $billingrules = array(

    );
    public static $billingmessages = array(
        'BillingClassID.required' =>'Billing Class field is required',
        'BillingType.required' =>'Billing Type field is required',
        'BillingTimezone.required' =>'Billing Timezone field is required',
        'BillingStartDate.required' =>'Billing Start Date field is required',
        'BillingCycleType.required' =>'Billing Cycle field is required',
        'BillingCycleValue.required' =>'Billing Cycle Value field is required',
    );

    static  $defaultAccountAuditFields = [
        'AccountName'=>'AccountName',
        'Address1'=>'Address1',
        'Address2'=>'Address2',
        'Address3'=>'Address3',
        'City'=>'City',
        'PostCode'=>'PostCode',
        'Country'=>'Country',
        'IsCustomer'=>'IsCustomer',
        'IsVendor'=>'IsVendor'
    ];

    public static function boot(){
        parent::boot();


        static::created(function($obj)
        {
            if(!Auth::guest()) {
                $customer = Session::get('customer');
                /* 0= user, 1=customer */
                $UserType = 0;
                if ($customer == 1) {
                    $UserType = 1;
                }
                $UserID = User::get_userID();
                $CompanyID = User::get_companyID();
                $IP = get_client_ip();
                $header = ["UserID" => $UserID,
                    "CompanyID" => $CompanyID,
                    "ParentColumnName" => 'AccountID',
                    "Type" => 'account',
                    "IP" => $IP,
                    "UserType" => $UserType
                ];
                $detail = array();
                log::info('--create start--');
                foreach ($obj->attributes as $index => $value) {
                    if (array_key_exists($index, Account::$defaultAccountAuditFields)) {
                        $data = ['OldValue' => '',
                            'NewValue' => $obj->attributes[$index],
                            'ColumnName' => $index,
                            'ParentColumnID' => $obj->attributes['AccountID']
                        ];
                        $detail[] = $data;
                    }
                }
                Log::info('start');
                Log::info(print_r($header, true));
                Log::info(print_r($detail, true));
                AuditHeader::add_AuditLog($header, $detail);
                Log::info('end');
                log::info('--create end--');
            }

        });


        static::updated(function($obj) {
            if(!Auth::guest()) {
                $customer = Session::get('customer');
                /* 0= user, 1=customer */
                $UserType = 1;
                if ($customer == 1) {
                    $UserType = 0;
                }
                $UserID = User::get_userID();
                $CompanyID = User::get_companyID();
                $IP = get_client_ip();
                $header = ["UserID" => $UserID,
                    "CompanyID" => $CompanyID,
                    "ParentColumnName" => 'AccountID',
                    "Type" => 'account',
                    "IP" => $IP,
                    "UserType" => $UserType
                ];
                $detail = array();
                log::info('--update start--');
                foreach ($obj->original as $index => $value) {
                    if (array_key_exists($index, Account::$defaultAccountAuditFields)) {
                        if ($obj->attributes[$index] != $value) {
                            $data = ['OldValue' => $obj->original[$index],
                                'NewValue' => $obj->attributes[$index],
                                'ColumnName' => $index,
                                'ParentColumnID' => $obj->original['AccountID']
                            ];
                            $detail[] = $data;
                        }
                    }
                }
                Log::info('start');
                Log::info(print_r($header, true));
                Log::info(print_r($detail, true));
                AuditHeader::add_AuditLog($header, $detail);
                Log::info('end');
                log::info('--update end--');
            }
        });
    }

    public static function getCompanyNameByID($id=0){

        return $AccountName = Account::where(["AccountID"=>$id])->pluck('AccountName');

        //return $AccountName = Account::find($id)->pluck('AccountName');


        //return (isset($Acc[0]->AccountName))?$Acc[0]->AccountName:"";
    	

    }

    public static function getCurrency($id=0){
        $currency =  Account::select('Symbol')->join('tblCurrency','tblAccount.CurrencyId','=','tblCurrency.CurrencyId')->where(['AccountID'=>intval($id)])->first();
        if(!empty($currency)){
            return $currency->Symbol;
        }
        return "";
    }

    public static function getRecentAccounts($limit){
        $companyID  = User::get_companyID();
        $account = Account::Where(["AccountType"=> 1,"CompanyID"=>$companyID,"Status"=>1])
            ->orderBy("tblAccount.AccountID", "desc")
            ->take($limit)
            ->get();

        return $account;

    }

    public static function getAccountsOwnersByRole(){
        $companyID = User::get_companyID();
        if(User::is('AccountManager')){
            $UserID = User::get_userID();
            $account_owners = DB::table('tblAccount')->where(["Owner"=> $UserID,"AccountType" => 1, "CompanyID" => $companyID, "Status" => 1])->orderBy('FirstName', 'asc')->get();
        }
        else{
            $account_owners = DB::table('tblAccount')->where(["AccountType" => 1, "CompanyID" => $companyID, "Status" => 1])->orderBy('FirstName', 'asc')->get();
        }

        return $account_owners;
    }
    public static function getLastAccountNo(){
        $LastAccountNo =  CompanySetting::getKeyVal('LastAccountNo');
        if($LastAccountNo == 'Invalid Key'){
            $LastAccountNo = 1;//Account::where(["CompanyID"=> User::get_companyID()])->max('Number');
            CompanySetting::setKeyVal('LastAccountNo',$LastAccountNo);
        }
        while(Account::where(['Number'=>$LastAccountNo])->count() >=1 ){
            $LastAccountNo++;
        }
        return $LastAccountNo;
    }
    public static function getAccountIDList($data=array()){

        if(User::is('AccountManager')){
            $data['Owner'] = User::get_userID();
        }
        if(User::is_admin() && isset($data['UserID'])){
            $data['Owner'] = $data['UserID'];
        }

        $data['Status'] = 1;
        if(!isset($data['AccountType'])) {
            $data['AccountType'] = 1;
            $data['VerificationStatus'] = Account::VERIFIED;
        }
        $data['CompanyID']=User::get_companyID();
        $row = Account::where($data)->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }

    public static function getAccountList($data=array()){

        if(User::is('AccountManager')){
            $data['Owner'] = User::get_userID();
        }
        if(User::is_admin() && isset($data['UserID'])){
            $data['Owner'] = $data['UserID'];
        }

        $data['Status'] = 1;
        if(!isset($data['AccountType'])) {
            $data['AccountType'] = 1;
        }
        $data['CompanyID']=User::get_companyID();
        $result = Account::where($data)->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
        $row = array(""=> "Select");
        if(!empty($result)){
            $row = array(""=> "Select")+$result;
        }
        return $row;
    }

    public static function getCustomersGridPopup($opt = array()){

        if(isset($opt["CompanyID"]) && $opt["CompanyID"] > 0) {

            $companyID = $opt["CompanyID"];// User::get_companyID();

            $AccountID = isset($opt["AccountID"]) ? $opt["AccountID"] : 0;// Exclude AccountID
            if (isset($opt['Trunk'])) {
                $customer = Account::join('tblCustomerTrunk', 'tblCustomerTrunk.AccountID', '=', 'tblAccount.AccountID')
                    ->where(["tblAccount.CompanyID" => $companyID, 'IsCustomer' => 1, 'AccountType' => 1,
                        'tblAccount.Status' => 1, 'tblCustomerTrunk.Status' => 1]);
            }else{
                $customer = Account::where(["CompanyID" => $companyID, 'IsCustomer' => 1, 'AccountType' => 1,'Status' => 1]);
            }
            /** only show his own accounts to Account Manager **/
            if (User::is('AccountManager')) {
                $UserID = User::get_userID();//  //$data['OwnerFilter'];
                $customer->where('Owner', $UserID);
            }

            /** Owner Dropdown filter for Admin  **/
            if (isset($opt['OwnerFilter']) && $opt['OwnerFilter'] != 0) {
                $UserID = $opt['OwnerFilter'];
                $customer->where('Owner', $UserID);
            }

            /** don't list current Account - used in CustomerRate **/
            if(isset($AccountID) && $AccountID > 0) {
                $customer->where('tblAccount.AccountID', '<>', $AccountID);
            }

            /** show only accounts having same codedeckid like currenct Account **/
            if (isset($opt['Trunk'])) {
                $codedeckid = CustomerTrunk::where(['AccountID' => $AccountID, 'TrunkID' => $opt['Trunk']])->pluck('CodeDeckId');
                $customer->where('tblCustomerTrunk.TrunkID', '=', $opt['Trunk']);
                $customer->where('tblCustomerTrunk.CodeDeckId', '=', $codedeckid);
            }
            /** Search Account Name **/
            if (isset($opt['Customer']) && $opt['Customer'] != '') {
                $customer->where('AccountName', 'LIKE', $opt['Customer'] . '%');
            }

            $customer->select(['tblAccount.AccountID', 'AccountName'])->distinct();

            return Datatables::of($customer)->make();
        }
    }
    public static function getAccountManager($AccountID){
        $managerinfo = Account::join('tblUser', 'tblUser.UserID', '=', 'tblAccount.Owner')->where(array('AccountID'=>$AccountID))->first(['tblUser.FirstName','tblUser.LastName','tblUser.EmailAddress','tblAccount.AccountName']);
        return $managerinfo;

    }
    // ignore item invoice
    public static function getInvoiceCount($AccountID){
        return (int)Invoice::where(array('AccountID'=>$AccountID))
            ->where('InvoiceStatus','!=',Invoice::CANCEL)
            ->Where(function($query)
            {
                $query->whereNull('ItemInvoice')
                    ->orwhere('ItemInvoice', '!=', 1);

            })->count();
    }
    // all invoice with item invoice
    public static function getAllInvoiceCount($AccountID){
        return (int)Invoice::where(array('AccountID'=>$AccountID))
            ->where('InvoiceStatus','!=',Invoice::CANCEL)
            ->count();
    }

    public static function getOutstandingAmount($CompanyID,$AccountID,$decimal_places = 2){

        $query = "call prc_getAccountOutstandingAmount ('". $CompanyID  . "',  '". $AccountID  . "')";
        $AccountOutstandingResult = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('AccountOutstanding'));
        $AccountOutstanding = $AccountOutstandingResult['data']['AccountOutstanding'];
        if(count($AccountOutstanding)>0){
            $AccountOutstanding = array_shift($AccountOutstanding);
            $Outstanding = $AccountOutstanding->Outstanding;
            $Outstanding= number_format($Outstanding,$decimal_places);
            return $Outstanding;
        }
    }
        public static function getOutstandingInvoiceAmount($CompanyID,$AccountID,$Invoiceids,$decimal_places = 2){
        $Outstanding = 0;
        $AutoPay = 0;
        $PaymentDue =0;
        $unPaidInvoices = DB::connection('sqlsrv2')->select('call prc_getPaymentPendingInvoice (' . $CompanyID . ',' . $AccountID.','.$PaymentDue.','.$AutoPay.')');
        foreach ($unPaidInvoices as $Invoiceid) {
            if(in_array($Invoiceid->InvoiceID,explode(',',$Invoiceids))) {
                $Outstanding += $Invoiceid->RemaingAmount;
            }
        }
        $Outstanding= number_format($Outstanding,$decimal_places,'.', '');
        return $Outstanding;
    }
    public static function getFullAddress($Account){
        $Address = "";
        $Address .= !empty($Account->Address1) ? $Account->Address1 . ',' . PHP_EOL : '';
        $Address .= !empty($Account->Address2) ? $Account->Address2 . ',' . PHP_EOL : '';
        $Address .= !empty($Account->Address3) ? $Account->Address3 . ',' . PHP_EOL : '';
        $Address .= !empty($Account->City) ? $Account->City . ',' . PHP_EOL : '';
        $Address .= !empty($Account->PostCode) ? $Account->PostCode . ',' . PHP_EOL : '';
        $Address .= !empty($Account->Country) ? $Account->Country : '';
        return $Address;
    }

    public static function validate_cli($cli=0){
        $status=0;
        $companyID  = User::get_companyID();
        $accountCLI = DB::select('call prc_checkCustomerCli (' . $companyID . ',' . $cli.')');

        if(count($accountCLI)>0){
            return false;
        }else{
            return true;
        }
    }

    public static function validate_ip($ip=0){
        $status=0;
        $companyID  = User::get_companyID();
        $AccountIPs = DB::select("call prc_checkAccountIP (" . $companyID . ",'" . $ip."')");
        if(count($AccountIPs)>0){
            return false;
        }else{
            return true;
        }
    }
    public static function AuthIP($account){
        $reponse_return = false;
        $companyID  = User::get_companyID();
        $ipcount = CompanyGateway::where(array('CompanyID'=>$companyID))->where('Settings','like','%"NameFormat":"IP"%')->count();
        if($ipcount > 0) {
            $AccountAuthenticate = AccountAuthenticate::where(array('AccountID' => $account->AccountID))->first();
            $AccountAuthenticateIP = AccountAuthenticate::where(array('AccountID' => $account->AccountID))->where(

                function ($query) {
                    $query->where('CustomerAuthRule', '=', 'IP')
                        ->orwhere('VendorAuthRule', '=', 'IP');
                }
            )->first();
            if (empty($AccountAuthenticate) || empty($AccountAuthenticateIP)) {
                /** if Authentication Rule Not Set as IP */
                $reponse_return = true;
            } else if (empty($AccountAuthenticateIP->CustomerAuthRule) && empty($AccountAuthenticateIP->VendorAuthRule)) {
                /** if Authentication Rule Set as IP and No IP Saved */
                $reponse_return = true;
            }
        }
        return $reponse_return;
    }
    public static function getActivityChartRepose($companyID,$AccountID){
        $query = "call prc_getAccountExpense ('". $companyID  . "',  '". $AccountID  . "')";
        $ExpenseResult = DataTableSql::of($query, 'neon_report')->getProcResult(array('Expense','CustomerExpense','VendorExpense'));
        $Expense = $ExpenseResult['data']['Expense'];
        $CustomerExpense = $ExpenseResult['data']['CustomerExpense'];
        $VendorExpense = $ExpenseResult['data']['VendorExpense'];
        $ExpenseYear = array();
        $previousyear = '';
        $datacount = 0;
        $customer = $vendor = $cat = array();
        foreach($Expense as $ExpenseRow){
            if($previousyear != $ExpenseRow->Year){
                $previousyear = $ExpenseRow->Year;
                $ExpenseYear[$previousyear]['CustomerTotal'] = $ExpenseRow->CustomerTotal;
                $ExpenseYear[$previousyear]['VendorTotal'] = $ExpenseRow->VendorTotal;
            }else{
                $ExpenseYear[$previousyear]['CustomerTotal'] += $ExpenseRow->CustomerTotal;
                $ExpenseYear[$previousyear]['VendorTotal'] += $ExpenseRow->VendorTotal;
            }
            $customer[$datacount] = $ExpenseRow->CustomerTotal;
            $vendor[$datacount] = $ExpenseRow->VendorTotal;
            $month = $ExpenseRow->Month<10 ? '0'.$ExpenseRow->Month:$ExpenseRow->Month;
            $cat[$datacount] = $ExpenseRow->Year.'-'.$month;
            $datacount++;

        }
        $ExpenseYearHTML = '';
        if(!empty($ExpenseYear)) {
            foreach ($ExpenseYear as $year => $total) {
                $ExpenseYearHTML .= "<tr><td>$year</td><td>".$total['CustomerTotal']."</td><td>".$total['VendorTotal']."</td></tr>";
            }
        }else{
            $ExpenseYearHTML = '<h4>'.cus_lang("MESSAGE_DATA_NOT_AVAILABLE").'</h4>';
        }

        $response['customer'] =  implode(',',$customer);
        $response['vendor'] = implode(',',$vendor);
        $response['categories'] = implode(',',$cat);
        $response['ExpenseYear'] = $ExpenseYearHTML;
        $response['CustomerActivity'] = account_expense_table($CustomerExpense,'Customer');
        $response['VendorActivity'] = account_expense_table($VendorExpense,'Vendor');

        return $response;
    }
	
	public static function GetActiveTicketCategory(){
		$TicketsShow	 =	0;
        $companyID  	 = User::get_companyID();

		$Support	 	 =	Integration::where(["CompanyID" => $companyID,"Slug"=>Account::$SupportSlug])->first();	
		//print_r($Support);
		
		if(count($Support)>0)
		{
						
			$SupportSubcategory = Integration::select("*");
			$SupportSubcategory->join('tblIntegrationConfiguration', function($join)
			{
				$join->on('tblIntegrationConfiguration.IntegrationID', '=', 'tblIntegration.IntegrationID');
	
			})->where(["tblIntegration.CompanyID"=>$companyID])->where(["tblIntegration.ParentID"=>$Support->IntegrationID])->where(["tblIntegrationConfiguration.Status"=>1]);
			 $result = $SupportSubcategory->first();
			 if(count($result)>0)
			 {
				return 1;
			 }
			 else
			 {
				return 0;
			 }
		}
		else
		{
			return 0;	
		}
	}

    public static function getAccountIDByName($Name){
        $companyID  	 = User::get_companyID();
        return  Account::where(["AccountName"=>$Name,"CompanyID" => $companyID])->pluck('AccountID');
    }

    public static function getVendorLastInvoiceDate($AccountBilling,$account){
        $invoiceDetail =   Invoice::join('tblInvoiceDetail','tblInvoiceDetail.InvoiceID','=','tblInvoice.InvoiceID')->where(array('AccountID'=>$account->AccountID,'InvoiceType'=>Invoice::INVOICE_IN))->orderBy('EndDate','DESC')->limit(1)->first(['EndDate']);
        if(!empty($invoiceDetail)){
            $LastInvoiceDate = $invoiceDetail->EndDate;
        }else if(!empty($AccountBilling->BillingStartDate)) {
            $LastInvoiceDate = $AccountBilling->BillingStartDate;
        }else{
            $LastInvoiceDate = date('Y-m-d',strtotime($account->created_at));
        }

        return $LastInvoiceDate;

    }
    public static function getCustomerLastInvoiceDate($AccountBilling,$account){
        if(!empty($AccountBilling->LastInvoiceDate)){
            $LastInvoiceDate = $AccountBilling->LastInvoiceDate;
        }else if(!empty($AccountBilling->BillingStartDate)) {
            $LastInvoiceDate = $AccountBilling->BillingStartDate;
        }else{
            $LastInvoiceDate = date('Y-m-d',strtotime($account->created_at));
        }
        return $LastInvoiceDate;

    }
    public static function getCustomerIDList($data=array()){

        if(User::is('AccountManager')){
            $data['Owner'] = User::get_userID();
        }
        if(User::is_admin() && isset($data['UserID'])){
            $data['Owner'] = $data['UserID'];
        }

        $data['Status'] = 1;
        $data['AccountType'] = 1;
        $data['VerificationStatus'] = Account::VERIFIED;
        $data['CompanyID']=User::get_companyID();
        $row = Account::where($data)
            ->where(function($where){
                $where->Where(['IsCustomer'=>1]);
                $where->orwhereNull('IsCustomer');
                $where->orwhereRaw('(IsCustomer = 0 AND IsVendor = 0)');
            })
            ->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }
    public static function getOnlyCustomerIDList($data=array()){
        if(User::is('AccountManager')){
            $data['Owner'] = User::get_userID();
        }
        if(User::is_admin() && isset($data['UserID'])){
            $data['Owner'] = $data['UserID'];
        }
        $data['Status'] = 1;
        $data['AccountType'] = 1;
        $data['VerificationStatus'] = Account::VERIFIED;
        $data['CompanyID']=User::get_companyID();
        $row = Account::where($data)
            ->where(function($where){
                $where->Where(['IsCustomer'=>1]);
            })
            ->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');

        return $row;
    }
    public static function getVendorIDList($data=array()){

        if(User::is('AccountManager')){
            $data['Owner'] = User::get_userID();
        }
        if(User::is_admin() && isset($data['UserID'])){
            $data['Owner'] = $data['UserID'];
        }

        $data['Status'] = 1;
        $data['AccountType'] = 1;
        $data['VerificationStatus'] = Account::VERIFIED;
        $data['CompanyID']=User::get_companyID();
        $row = Account::where($data)
            ->where(function($where){
                $where->Where(['IsVendor'=>1]);
                $where->orwhereNull('IsVendor');
                $where->orwhereRaw('(IsCustomer = 0 AND IsVendor = 0)');
            })
            ->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }
    public static function getOnlyVendorIDList($data=array()){
        if(User::is('AccountManager')){
            $data['Owner'] = User::get_userID();
        }
        if(User::is_admin() && isset($data['UserID'])){
            $data['Owner'] = $data['UserID'];
        }
        $data['Status'] = 1;
        $data['AccountType'] = 1;
        $data['VerificationStatus'] = Account::VERIFIED;
        $data['CompanyID']=User::get_companyID();
        $vendors = Account::where($data)
            ->where(function($where){
                $where->Where(['IsVendor'=>1]);
            })
            ->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');

        return $vendors;
    }

    public static function GetAccountAllEmails($id,$ArrayReturn=false){
	  $array			 =  array();
	  $accountemails	 = 	Account::where(array("AccountID"=>$id))->select(array('Email', 'BillingEmail'))->get();
	  $acccountcontact 	 =  DB::table('tblContact')->where(array("AccountID"=>$id))->orWhere('Owner', $id)->get(array("Email"));
	  
	  	
		if(count($accountemails)>0){
				foreach($accountemails as $AccountData){
					//if($AccountData->Email!='' && !in_array($AccountData->Email,$array))
					if($AccountData->Email!='')
					{
						if(!is_array($AccountData->Email))
						{				  
						  $email_addresses = explode(",",$AccountData->Email);				
						}
						else
						{
						  $email_addresses = $emails;
						}
						if(count($email_addresses)>0)
						{
							foreach($email_addresses as $email_addresses_data)
							{
								$txt = $email_addresses_data;
								if(!in_array($txt,$array))
								{
									$array[] =  $txt;	
								}
							}
						}
						
					}			
					
					if($AccountData->BillingEmail!='')
					{
						if(!is_array($AccountData->BillingEmail))
						{				  
						  $email_addresses = explode(",",$AccountData->BillingEmail);				
						}
						else
						{
						  $email_addresses = $emails;
						}
						if(count($email_addresses)>0)
						{
							foreach($email_addresses as $email_addresses_data)
							{
								$txt = $email_addresses_data;
								if(!in_array($txt,$array))
								{
									//$array[] =  $email_addresses_data;	
									$array[] =  $txt;	
								}
							}
						}
						
					}
				}
		}
		
		if(count($acccountcontact)>0){
				foreach($acccountcontact as $ContactData){
					$txt = $ContactData->Email;
					if($ContactData->Email!=''  && !in_array($txt,$array))
					{
						$array[] =  $txt;
						//$array[] =  $ContactData->Email;
					}
				}
		}
	if($ArrayReturn){return $array;}	
	  return implode(",",$array);
	}
	
	public static function checkAccountByEmail($email){
		   return Account::whereRaw( "( Email = '".$email."' OR  BillingEmail = '".$email."' )")->pluck('AccountID');
	}

    public static function getAllAccounts($AccountID){
        $data=array();
        //$Accounts = [$AccountID];
        if(User::is('AccountManager')){
            $data['Owner'] = User::get_userID();
        }
        if(User::is_admin() && isset($data['UserID'])){
            $data['Owner'] = $data['UserID'];
        }

        $data['Status'] = 1;
        if(!isset($data['AccountType'])) {
            $data['AccountType'] = 1;
            $data['VerificationStatus'] = Account::VERIFIED;
            $data['Billing']=1;
        }
        $data['CompanyID']=User::get_companyID();
        $row = Account::where($data)->where('AccountID','<>',$AccountID)->select(array('AccountID','AccountName'))->orderBy('AccountName');
        return Datatables::of($row)->make();
        //return $row;
    }

    public static function addUpdateAccountDynamicfield($data=array()){
        $DynamicFields = array();
        $FieldsID = DB::table('tblDynamicFields')->where(['CompanyID'=>$data['CompanyID'],'FieldSlug'=>$data['FieldName']])->pluck('DynamicFieldsID');
        if(!empty($FieldsID)){
            $customer=Session::get('customer');
            $UserType = 'user';
            if($customer==1){
                $UserType = 'customer';
            }

            $header = [
                "UserID"=>User::get_userID(),
                "CompanyID"=>$data['CompanyID'],
                "ParentColumnName"=>'AccountID',
                "Type"=>'account',
                "IP"=>get_client_ip(),
                "UserType"=>$UserType
            ];

            $count = DynamicFieldsValue::where(['ParentID'=>$data['AccountID'],'DynamicFieldsID'=>$FieldsID])->count();
            /*update value */
            if(!empty($count) && $count >0){

                $DynamicValues = DynamicFieldsValue::where(['ParentID'=>$data['AccountID'],'DynamicFieldsID'=>$FieldsID])->first();
                $Old_value = $DynamicValues->FieldValue;
                $New_value = $data['FieldValue'];
                $DynamicFields['FieldValue'] = $data['FieldValue'];
                $DynamicFields["updated_at"] = date('Y-m-d H:i:s');
                $DynamicFields["updated_by"] = User::get_user_full_name();
                if($Old_value!=$New_value){
                    DynamicFieldsValue::where(['ParentID'=>$data['AccountID'],'DynamicFieldsID'=>$FieldsID])->update($DynamicFields);

                    $detail = array();
                    $data = [
                        'OldValue'=>$Old_value,
                        'NewValue'=>$New_value,
                        'ColumnName'=>$data['FieldName'],
                        'ParentColumnID'=>$data['AccountID']
                    ];
                    $detail[]=$data;

                    AuditHeader::add_AuditLog($header,$detail);
                }

            }else{
                /* inssert value */
                $DynamicFields['CompanyID'] = $data['CompanyID'];
                $DynamicFields['ParentID'] = $data['AccountID'];
                $DynamicFields['DynamicFieldsID'] = $FieldsID;
                $DynamicFields['FieldValue'] = $data['FieldValue'];
                $DynamicFields["created_at"] = date('Y-m-d H:i:s');
                $DynamicFields["created_by"] = User::get_user_full_name();
                DynamicFieldsValue::insert($DynamicFields);

                $detail = array();
                $data = [
                    'OldValue'=>'',
                    'NewValue'=>$data['FieldValue'],
                    'ColumnName'=>$data['FieldName'],
                    'ParentColumnID'=>$data['AccountID']
                ];
                $detail[]=$data;

                AuditHeader::add_AuditLog($header,$detail);
            }

        }
        return true;
    }

    public static function getDynamicfieldValue($ParentID,$FieldName){
        $FieldValue = '';

        $FieldsID = DB::table('tblDynamicFields')->where(['CompanyID'=>User::get_companyID(),'FieldSlug'=>$FieldName])->pluck('DynamicFieldsID');
        if(!empty($FieldsID)){
            $FieldValue = DynamicFieldsValue::where(['ParentID'=>$ParentID,'DynamicFieldsID'=>$FieldsID])->pluck('FieldValue');
        }

        return $FieldValue;
    }

    public static function getDynamicfields($Type,$ParentID){
        $results = array();
        $data = array();

        $Fields = DB::table('tblDynamicFields')->where(['CompanyID'=>User::get_companyID(),'Type'=>$Type,'Status'=>1])->get();
        if(!empty($Fields) && count($Fields)>0){
            foreach($Fields as $Field){
                $FieldValue = Account::getDynamicfieldValue($ParentID,$Field->FieldSlug);
                $data['FieldDomType'] = $Field->FieldDomType;
                $data['FieldName'] = $Field->FieldName;
                $data['FieldSlug'] = $Field->FieldSlug;
                $data['DynamicFieldsID'] = $Field->DynamicFieldsID;
                $data['FieldValue'] = $FieldValue;
                $results[] = $data;
            }
        }

        return $results;
    }

    public static function getAccountDropdownWithTrunk($opt = array()){

        if(isset($opt["CompanyID"]) && $opt["CompanyID"] > 0) {

            $companyID = $opt["CompanyID"];// User::get_companyID();


            if (isset($opt['IsCustomer'])) {

                $accounts = Account::join('tblCustomerTrunk', 'tblCustomerTrunk.AccountID', '=', 'tblAccount.AccountID')
                    ->where(["tblAccount.CompanyID" => $companyID, 'IsCustomer' => 1, 'AccountType' => 1,
                        'tblAccount.Status' => 1, 'tblCustomerTrunk.Status' => 1]);

            } else if (isset($opt['IsVendor'])) {

                $accounts = Account::join('tblVendorTrunk', 'tblVendorTrunk.AccountID', '=', 'tblAccount.AccountID')
                    ->where(["tblAccount.CompanyID" => $companyID, 'IsVendor' => 1, 'AccountType' => 1,
                        'tblAccount.Status' => 1, 'tblVendorTrunk.Status' => 1]);
            }

            if (isset($opt['TrunkID'])) {

                $accounts->where('TrunkID', $opt['TrunkID']);

            }

            /** only show his own accounts to Account Manager **/
            if (User::is('AccountManager')) {
                $UserID = User::get_userID();//  //$data['OwnerFilter'];
                $accounts->where('Owner', $UserID);
            }

            //return $accounts->orderBy("AccountName", "ASC")->get(array('tblAccount.AccountID','AccountName'))->toArray();
            return $accounts->select(array('AccountName', 'tblAccount.AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');

        }
    }

    public static function getCodeDeckId($AccountID,$TrunkID){
        return  CustomerTrunk::where(["AccountID"=>$AccountID,"TrunkID" => $TrunkID])->pluck('CodeDeckId');
    }

}