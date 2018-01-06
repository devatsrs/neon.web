<?php
/**
 * Created by PhpStorm.
 * User: CodeDesk
 * Date: 8/22/2015
 * Time: 12:57 PM
 */



class QuickBook {

	protected $token ;
	protected $oauth_consumer_key ;
	protected $oauth_consumer_secret ;
	protected $sandbox ;
	protected $quickbooks_oauth_url ;
	protected $quickbooks_success_url ;
	protected $quickbooks_menu_url ;
	protected $dsn ;
	protected $encryption_key ;
	protected $the_username ;
	protected $the_tenant ;
	protected $quickbooks_is_connected = false;
	protected $realm ;
	protected $Context =array() ;


	function __Construct($CompanyID){
		//require_once dirname(__FILE__) . '/quicibookmaster/QuickBooks.php';
		//require_once 'I:/www/bhavin/neon/web/newbhavin/app/lib/quicibookmaster/QuickBooks.php';
		//$path = getenv('PATH_SDK_ROOT').'QuickBooks.php';
		//require_once($path);
		$this->check_quickbook($CompanyID);
    }

	public function test_connection()
	{
		$quickbooks_CompanyInfo = false;
		if ($this->is_quickbook()){
			if ($this->quickbooks_is_connected) {
				$CompanyInfoService = new QuickBooks_IPP_Service_CompanyInfo();
				$quickbooks_CompanyInfo = $CompanyInfoService->get($this->Context, $this->realm);
			}
		}
		return $quickbooks_CompanyInfo;
	}
	
	public function getAllAcccount()
	{
		$accountresponse = array();

		//https://appcenter.intuit.com/Playground/OAuth/AccessGranted?ia=true&oauth_token=lvprdco5CjnH7fx5z6P9RRHFm9AUrRHhhoH3UdCwjoGRrLEv&oauth_verifier=0hzsvq6&realmId=193514449127769&dataSource=QBO
			//$query = 'query?query='.urlencode('Select * from Customer');
			//$query = 'account/1';


			$url = "https://sandbox-quickbooks.api.intuit.com/v3/company/193514449127769/customer/67";
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_POSTFIELDS => "",
				CURLOPT_HTTPHEADER => array(
					"accept: application/json",
					"authorization: OAuth realm=\"193514449127769\",oauth_consumer_key=\"qyprdGjyW92fBh1eron3vMi2ljRuzv\",oauth_token=\"lvprdE2leSmZ2icBsPrdkJDZs51Y5QjS3jQybC5ej1rHRcym\",oauth_signature_method=\"HMAC-SHA1\",oauth_timestamp=\"1475053838\",oauth_nonce=\"JxDRV4\",oauth_version=\"1.0\",oauth_signature=\"poxS7fi89i4UcUSigp1%2FS4pmf8Y%3D\"",
				),
			));

		$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
				$accountresponse["error"] = $err;
				//echo "cURL Error #:" . $err;
			} else {
				$accountresponse["response"] = $response;
				//echo $response;
			}
			return $accountresponse;

		/*
		$URL = 'https://sandbox-quickbooks.api.intuit.com';

		$companyid = '193514342633202';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $LicenceVerifierURL);
		curl_setopt($ch, CURLOPT_VERBOSE, '1');
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);//TRUE to automatically set the Referer: field in requests where it follows a Location: redirect.
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);//TRUE to force the connection to explicitly close when it has finished processing, and not be pooled for reuse.
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);//TRUE to force the use of a new connection instead of a cached one.


		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		// curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		//NVPRequest for submitting to server
		$nvpreq = "json=" . json_encode($post);

		//$nvpreq = http_build_query($post);

		////setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

		//getting response from server
		$response = curl_exec($ch);

		// echo $response;
		return $response; */

	}


	public function quickbook_disconnect(){

		if (!QuickBooks_Utilities::initialized($this->dsn)) {
			// Initialize creates the neccessary database schema for queueing up requests and logging
			QuickBooks_Utilities::initialize($this->dsn);
		}
		$IntuitAnywhere = new QuickBooks_IPP_IntuitAnywhere($this->dsn, $this->encryption_key, $this->oauth_consumer_key, $this->oauth_consumer_secret, $this->quickbooks_oauth_url, $this->quickbooks_success_url);


		if ($IntuitAnywhere->disconnect($this->the_username, $this->the_tenant))
		{

		}
	}

	public function quickbook_connect(){
		if (!QuickBooks_Utilities::initialized($this->dsn)) {
			// Initialize creates the neccessary database schema for queueing up requests and logging
			QuickBooks_Utilities::initialize($this->dsn);
		}
		$IntuitAnywhere = new QuickBooks_IPP_IntuitAnywhere($this->dsn, $this->encryption_key, $this->oauth_consumer_key, $this->oauth_consumer_secret, $this->quickbooks_oauth_url, $this->quickbooks_success_url);
		// Try to handle the OAuth request
		if ($IntuitAnywhere->handle($this->the_username, $this->the_tenant))
		{
			; // The user has been connected, and will be redirected to $that_url automatically.
		}
		else
		{
			// If this happens, something went wrong with the OAuth handshake
			die('Oh no, something bad happened: ' . $IntuitAnywhere->errorNumber() . ': ' . $IntuitAnywhere->errorMessage());
		}
	}

	public static function addCustomer()
	{

		$token = 'f1f664d3bf391b4318b89aabcfe28b04c93c';
		$oauth_consumer_key = 'qyprdGjyW92fBh1eron3vMi2ljRuzv';
		$oauth_consumer_secret = '36MI6Rzg0PrrPMNy31gPBYnZv8JLvdMXajvTeRm5';

		// If you're using DEVELOPMENT TOKENS, you MUST USE SANDBOX MODE!!!  If you're in PRODUCTION, then DO NOT use sandbox.
		$sandbox = true;     // When you're using development tokens
		//$sandbox = false;    // When you're using production tokens

		$quickbooks_oauth_url = 'http://localhost/bhavin/neon/web/newbhavin/public/quickbook/oauth';
		$quickbooks_success_url = 'http://localhost/bhavin/neon/web/newbhavin/public/quickbook/success';
		$quickbooks_menu_url = 'http://localhost/bhavin/neon/web/newbhavin/public/quickbook';
		$dsn = 'mysqli://root:root@localhost/LocalRatemanagement';
		$encryption_key = 'bcde1234';
		$the_username = 'DO_NOT_CHANGE_ME';
		$the_tenant = 12345;

		if (!QuickBooks_Utilities::initialized($dsn)) {
			// Initialize creates the neccessary database schema for queueing up requests and logging
			QuickBooks_Utilities::initialize($dsn);
		}

		$IntuitAnywhere = new QuickBooks_IPP_IntuitAnywhere($dsn, $encryption_key, $oauth_consumer_key, $oauth_consumer_secret, $quickbooks_oauth_url, $quickbooks_success_url);

		if ($IntuitAnywhere->check($the_username, $the_tenant) and
			$IntuitAnywhere->test($the_username, $the_tenant)
		) {
			// Yes, they are
			$quickbooks_is_connected = true;

			// Set up the IPP instance
			$IPP = new QuickBooks_IPP($dsn);

			// Get our OAuth credentials from the database
			$creds = $IntuitAnywhere->load($the_username, $the_tenant);

			// Tell the framework to load some data from the OAuth store
			$IPP->authMode(
				QuickBooks_IPP::AUTHMODE_OAUTH,
				$the_username,
				$creds);

			if ($sandbox) {
				// Turn on sandbox mode/URLs
				$IPP->sandbox(true);
			}

			// Print the credentials we're using
			//print_r($creds);

			// This is our current realm
			$realm = $creds['qb_realm'];

			// Load the OAuth information from the database
			$Context = $IPP->context();

			// Get some company info
			$CompanyInfoService = new QuickBooks_IPP_Service_CompanyInfo();
			$quickbooks_CompanyInfo = $CompanyInfoService->get($Context, $realm);


		$CustomerService = new QuickBooks_IPP_Service_Customer();

		$Customer = new QuickBooks_IPP_Object_Customer();
		$Customer->setTitle('Ms');
		$Customer->setGivenName('Shannon');
		$Customer->setMiddleName('B');
		$Customer->setFamilyName('Palmer');
		$Customer->setDisplayName('Shannon B Palmer ' . mt_rand(0, 1000));

		// Terms (e.g. Net 30, etc.)
		$Customer->setSalesTermRef(4);

		// Phone #
		$PrimaryPhone = new QuickBooks_IPP_Object_PrimaryPhone();
		$PrimaryPhone->setFreeFormNumber('860-532-0089');
		$Customer->setPrimaryPhone($PrimaryPhone);

		// Mobile #
		$Mobile = new QuickBooks_IPP_Object_Mobile();
		$Mobile->setFreeFormNumber('860-532-0089');
		$Customer->setMobile($Mobile);

		// Fax #
		$Fax = new QuickBooks_IPP_Object_Fax();
		$Fax->setFreeFormNumber('860-532-0089');
		$Customer->setFax($Fax);

		// Bill address
		$BillAddr = new QuickBooks_IPP_Object_BillAddr();
		$BillAddr->setLine1('72 E Blue Grass Road');
		$BillAddr->setLine2('Suite D');
		$BillAddr->setCity('Mt Pleasant');
		$BillAddr->setCountrySubDivisionCode('MI');
		$BillAddr->setPostalCode('48858');
		$Customer->setBillAddr($BillAddr);

		// Email
		$PrimaryEmailAddr = new QuickBooks_IPP_Object_PrimaryEmailAddr();
		$PrimaryEmailAddr->setAddress('support@consolibyte.com');
		$Customer->setPrimaryEmailAddr($PrimaryEmailAddr);

		if ($resp = $CustomerService->add($Context, $realm, $Customer)) {
			print('Our new customer ID is: [' . $resp . '] (name "' . $Customer->getDisplayName() . '")');
		} else {
			print($CustomerService->lastError($Context));
		}

	}

	}

	public function check_quickbook($CompanyID){
		$QuickBookData		=	SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$QuickBookSlug,$CompanyID);

		if(!$QuickBookData){
			$this->quickbooks_is_connected = false;
		}else{
			$OauthConsumerKey = $QuickBookData->OauthConsumerKey;
			$OauthConsumerSecret = $QuickBookData->OauthConsumerSecret;
			$AppToken = $QuickBookData->AppToken;
			$QuickBookSandbox = $QuickBookData->QuickBookSandbox;
			if(!empty($QuickBookSandbox) && $QuickBookSandbox == 1){
				$QuickBookSandbox = true;
			}else{
				$QuickBookSandbox = false;
			}
			if(!empty($OauthConsumerKey) && !empty($OauthConsumerSecret) && !empty($AppToken)){
				$this->oauth_consumer_key = $OauthConsumerKey;
				$this->oauth_consumer_secret = $OauthConsumerSecret;
				$this->token = $AppToken;
				$this->sandbox = $QuickBookSandbox;

				$this->quickbooks_oauth_url = URL::to('/quickbook/oauth');
				$this->quickbooks_success_url = URL::to('/quickbook/success');
				$this->quickbooks_menu_url = URL::to('/quickbook');
				$dbconnection = Config::get('database.connections.sqlsrv');

				$dsn = 'mysqli://'.$dbconnection['username'].':'.$dbconnection['password'].'@'.$dbconnection['host'].'/'.$dbconnection['database'];

				//$this->dsn = 'mysqli://root:root@localhost/LocalRatemanagement';
				if(empty($CompanyID)){
					$CompanyID = SiteIntegration::GetComapnyIdByKey();
					$CompanyID = !empty($CompanyID)?$CompanyID:User::get_companyID();
				}

				$this->dsn = $dsn;
				$this->encryption_key = 'bcde1234';
				$this->the_username = 'DO_NOT_CHANGE_ME'.$CompanyID;
				$this->the_tenant = 12345;
				$this->quickbooks_is_connected = true;
			}else{
				$this->quickbooks_is_connected = false;
			}
		}
	}

	public function is_quickbook(){
		if($this->quickbooks_is_connected){
			if (!QuickBooks_Utilities::initialized($this->dsn)) {
				// Initialize creates the neccessary database schema for queueing up requests and logging
				QuickBooks_Utilities::initialize($this->dsn);
			}
			$IntuitAnywhere = new QuickBooks_IPP_IntuitAnywhere($this->dsn, $this->encryption_key, $this->oauth_consumer_key, $this->oauth_consumer_secret, $this->quickbooks_oauth_url, $this->quickbooks_success_url);

			if ($IntuitAnywhere->check($this->the_username, $this->the_tenant) and
				$IntuitAnywhere->test($this->the_username, $this->the_tenant)
			) {
				// Yes, they are
				$this->quickbooks_is_connected = true;
				$IPP = new QuickBooks_IPP($this->dsn);

				// Get our OAuth credentials from the database
				$creds = $IntuitAnywhere->load($this->the_username, $this->the_tenant);

				// Tell the framework to load some data from the OAuth store
				$IPP->authMode(
					QuickBooks_IPP::AUTHMODE_OAUTH,
					$this->the_username,
					$creds);

				if ($this->sandbox)
				{
					// Turn on sandbox mode/URLs
					$IPP->sandbox(true);
				}

				// Print the credentials we're using
				//echo "<pre>";print_r($creds);exit;

				// This is our current realm
				$realm = $creds['qb_realm'];
				$this->realm = $realm;

				// Load the OAuth information from the database
				$Context = $IPP->context();

				$this->Context = $Context;

				return true;
			}else{
				$this->quickbooks_is_connected = false;
				return false;
			}
		}else{
			return false;
		}
	}

	public function checkAuth(){
		if($this->quickbooks_is_connected){
			if (!QuickBooks_Utilities::initialized($this->dsn)) {
				// Initialize creates the neccessary database schema for queueing up requests and logging
				QuickBooks_Utilities::initialize($this->dsn);
			}
			$IntuitAnywhere = new QuickBooks_IPP_IntuitAnywhere($this->dsn, $this->encryption_key, $this->oauth_consumer_key, $this->oauth_consumer_secret, $this->quickbooks_oauth_url, $this->quickbooks_success_url);
			// Try to handle the OAuth request
			if ($IntuitAnywhere->handle($this->the_username, $this->the_tenant))
			{
				; // The user has been connected, and will be redirected to $that_url automatically.
			}
			else
			{
				// If this happens, something went wrong with the OAuth handshake
				die('Oh no, something bad happened: ' . $IntuitAnywhere->errorNumber() . ': ' . $IntuitAnywhere->errorMessage());
			}
		}else{
			die("Quickbook Not Setup");
		}
	}

	public function getAllCustomer(){
		$customers = array();
		if ($this->is_quickbook()){
			if ($this->quickbooks_is_connected) {

				$Context = $this->Context;

				$realm = $this->realm;

				$allcustomer = array();

				$CustomerService = new QuickBooks_IPP_Service_Customer();

				$customers = $CustomerService->query($Context, $realm, "SELECT * FROM Customer MAXRESULTS 1000");
				//echo "<pre>"; print_r($customers); exit;
				if(!empty($customers) && count($customers)>0){
					foreach ($customers as $Customer){
						//print('Customer Id=' . $Customer->getId() . ' is named: ' . $Customer->getFullyQualifiedName() . '<br>');
						$cid = $Customer->getId();
						if(!empty($cid)){

							$id = $Customer->getId();
							$id = str_replace('{-','',$id);
							$id = str_replace('}','',$id);

							//$allcustomer = $allcustomer[];
							$allcustomer[$id]['Id'] = $id;
							$allcustomer[$id]['AccountName'] = $Customer->getDisplayName();
							$allcustomer[$id]['FirstName'] = $Customer->getGivenName();
							$allcustomer[$id]['LastName'] = $Customer->getFamilyName();
							$Phone = $Customer->getPrimaryPhone();
							if(!empty($Phone) && count($Phone)>0){
								$allcustomer[$id]['Phone'] = $Phone->getFreeFormNumber();
							}
							$Email = $Customer->getPrimaryEmailAddr();
							if(!empty($Email) && count($Email)>0){
								$allcustomer[$id]['Email'] = $Email->getAddress();
							}
							$BillAddr = $Customer->getBillAddr();
							if(!empty($BillAddr) && count($BillAddr)>0){
								$Address1 = $BillAddr->getLine1();
								$Address2 = $BillAddr->getLine2();
								$City = $BillAddr->getCity();
								$PostCode = $BillAddr->getPostalCode();
								$Country = $BillAddr->getCountry();

								if(isset($Address1) && $Address1!=''){
									$allcustomer[$id]['Address1'] = $Address1;
								}
								if(isset($Address2) && $Address2!=''){
									$allcustomer[$id]['Address2'] = $Address2;
								}
								if(isset($City) && $City!=''){
									$allcustomer[$id]['City'] = $City;
								}
								if(isset($PostCode) && $PostCode!=''){
									$allcustomer[$id]['PostCode'] = $PostCode;
								}
								if(isset($Country) && $Country!=''){
									$allcustomer[$id]['Country'] = $Country;
								}
							}

						}
					}
				}
				echo "<pre>";
				print_r($allcustomer);
				//return $customers;
				exit;
				//$error = $CustomerService->lastError();

				/*
				foreach ($customers as $Customer)
				{
					print('Customer Id=' . $Customer->getId() . ' is named: ' . $Customer->getFullyQualifiedName() . '<br>');
				}*/
			}
		}
		return $customers;
	}

	public function getAllItems(){
		$items = array();
		if ($this->is_quickbook()){
			if ($this->quickbooks_is_connected) {

				$Context = $this->Context;

				$realm = $this->realm;

				$allitem = array();

				$ItemService = new QuickBooks_IPP_Service_Term();

				$items = $ItemService->query($Context, $realm, "SELECT * FROM Item WHERE Metadata.LastUpdatedTime > '2013-01-01T14:50:22-08:00' ORDER BY Metadata.LastUpdatedTime ");

			}
		}
		return $items;
	}

	public function createItem(){
		$response = array();
		if ($this->is_quickbook()){
			if ($this->quickbooks_is_connected) {

				$Context = $this->Context;

				$realm = $this->realm;

				$ItemService = new QuickBooks_IPP_Service_Item();

				$Item = new QuickBooks_IPP_Object_Item();

				$Item->setName('Usage');
				$Item->setType('Service');
				$Item->setIncomeAccountRef('1');

				if ($resp = $ItemService->add($Context, $realm, $Item))
				{
					if(!empty($resp)){
						$resp = str_replace('{-','',$resp);
						$resp = str_replace('}','',$resp);
					}
					$response['response'] = $resp;
				}
				else
				{
					$response['error'] = $ItemService->lastError($Context);
				}

			}
		}else{
			$response['error'] = 'quickbook not setup';
		}
		return $response;
	}
	public function CreateInvoice($InvoiceID){
		$response = array();
		$Invoices = Invoice::find($InvoiceID);
		$InvoiceFullNumber = $Invoices->FullInvoiceNumber;
		$count = $this->CheckInvoice($InvoiceFullNumber);
		Log::info('-- count --'.print_r($count,true));
		if(isset($count) && $count==0) {
			Log::info('-- Create Item --'.print_r($InvoiceFullNumber,true));
			$Context = $this->Context;
			$realm = $this->realm;
			/*
			$CustomerService = new \QuickBooks_IPP_Service_Customer();

			$customers = $CustomerService->query($Context, $realm, "SELECT * FROM Customer MAXRESULTS 1000");
			log::info('--customer--'.print_r($customers,true));
			exit; */


			$InvoiceService = new \QuickBooks_IPP_Service_Invoice();

			$Invoice = new \QuickBooks_IPP_Object_Invoice();

			$Invoice->setDocNumber('WEB' . mt_rand(0, 10000));
			$Invoice->setTxnDate('2013-10-11');

			$Line = new \QuickBooks_IPP_Object_Line();
			$Line->setDetailType('SalesItemLineDetail');
			$Line->setAmount(30.0000 * 1.0000 * 0.516129);
			$Line->setDescription('Test description goes here.');

			$SalesItemLineDetail = new \QuickBooks_IPP_Object_SalesItemLineDetail();
			$SalesItemLineDetail->setItemRef('25');
			$SalesItemLineDetail->setUnitPrice(30 * 0.516129);
			$SalesItemLineDetail->setQty(1.00000);

			$Line->addSalesItemLineDetail($SalesItemLineDetail);

			$Invoice->addLine($Line);

			$Line1 = new \QuickBooks_IPP_Object_Line();
			$Line1->setDetailType('SalesItemLineDetail');
			$Line1->setAmount(20.0000 * 1.0000 * 0.516129);
			$Line1->setDescription('Test description goes here.');

			$SalesItemLineDetail1 = new \QuickBooks_IPP_Object_SalesItemLineDetail();
			$SalesItemLineDetail1->setItemRef('25');
			$SalesItemLineDetail1->setUnitPrice(20 * 0.516129);
			$SalesItemLineDetail1->setQty(1.00000);

			$Line1->addSalesItemLineDetail($SalesItemLineDetail1);

			$Invoice->addLine1($Line1);

			$Invoice->setCustomerRef('69');

			if ($resp = $InvoiceService->add($Context, $realm, $Invoice))
			{
				if(!empty($resp)){
					$resp = str_replace('{-','',$resp);
					$resp = str_replace('}','',$resp);
				}
				$response['response'] = $resp;
			}
			else
			{
				$response['error'] = $InvoiceService->lastError($Context);
			}
		}
		Log::info('-- Create Item --');
		Log::info(print_r($response,true));
		exit;
	}

	/* Account Get From QuickBook And Insert Temp Account For Account Import*/

	public function getAccountsDetail($addparams=array()){
		$response = array();
		if ($this->is_quickbook()) {
			if ($this->quickbooks_is_connected) {

				$Context = $this->Context;

				$realm = $this->realm;

				$allcustomer = array();


				$CustomerService = new QuickBooks_IPP_Service_Customer();

				$customers = $CustomerService->query($Context, $realm, "SELECT * FROM Customer MAXRESULTS 1000");
				$count = $CustomerService->query($Context, $realm, "SELECT COUNT(*) FROM Customer  ");
				//echo $count;exit;

				log::info('---count customer---'.$count);

				if(!empty($customers) && $count>0 && count($addparams)>0){

					$tempItemData = array();

					$batch_insert_array = array();
					$CompanyID = $addparams['CompanyID'];
					$ProcessID = $addparams['ProcessID'];
					$FirstName = $LastName = '';
					$Address1 = $Address2 = $Address3 = $City = $Postcode = $Country = '';
					$Email = $Phone1 = $Phone2 = $Fax = '';

					foreach ((array)$customers as $Customer){
						//print('Customer Id=' . $Customer->getId() . ' is named: ' . $Customer->getFullyQualifiedName() . '<br>');
						$cid = $Customer->getId();
						if(!empty($cid)){

							$id = $Customer->getId();
							$id = str_replace('{-','',$id);
							$id = str_replace('}','',$id);

							//$allcustomer = $allcustomer[];
							$FirstName = $Customer->getGivenName();
							$LastName = $Customer->getFamilyName();
							$Phone = $Customer->getPrimaryPhone();
							if(!empty($Phone) && count($Phone)>0){
								$Phone1 = $Phone->getFreeFormNumber();
							}
							$Email = $Customer->getPrimaryEmailAddr();
							if(!empty($Email) && count($Email)>0){
								$Email = $Email->getAddress();
							}
							$BillAddr = $Customer->getBillAddr();
							if(!empty($BillAddr) && count($BillAddr)>0){
								$Address1 = $BillAddr->getLine1();
								$Address2 = $BillAddr->getLine2();
								$City = $BillAddr->getCity();
								$PostCode = $BillAddr->getPostalCode();
								$Country = $BillAddr->getCountry();
							}

							$tempItemData['AccountName'] = $Customer->getDisplayName();
							if(!empty($FirstName)){
								$tempItemData['FirstName'] = $FirstName;
							}else{
								$tempItemData['FirstName'] = '';
							}

							if(!empty($LastName)){
								$tempItemData['LastName'] = $LastName;
							}else{
								$tempItemData['LastName'] = '';
							}

							if(!empty($Email)){
								$tempItemData['Email'] = $Email;
							}else{
								$tempItemData['Email'] ='';
							}

							if(!empty($Address1)){
								$tempItemData['Address1'] = $Address1;
							}else{
								$tempItemData['Address1'] = '';
							}
							if(!empty($Address2)){
								$tempItemData['Address2'] = $Address2;
							}else{
								$tempItemData['Address2'] = '';
							}
							if(!empty($City)){
								$tempItemData['City'] = $City;
							}else{
								$tempItemData['City'] ='';
							}
							if(!empty($Postcode)){
								$tempItemData['PostCode'] = $Postcode;
							}else{
								$tempItemData['PostCode'] = '';
							}
							if(!empty($Country)){
								$checkCountry=strtoupper($Country);
								if($checkCountry=='UK'){
									$checkCountry = 'UNITED KINGDOM';
								}
								$count = DB::table('tblCountry')->where(["Country" => $checkCountry])->count();
								if($count>0){
									$tempItemData['Country'] = $checkCountry;
								}else{
									$tempItemData['Country'] = '';
								}
							}else{
								$tempItemData['Country'] = '';
							}
							if(!empty($Phone1)){
								$tempItemData['Phone'] = $Phone1;
							}else{
								$tempItemData['Phone'] = '';
							}
							$tempItemData['AccountType'] = 1;
							$tempItemData['CompanyId'] = $CompanyID;
							$tempItemData['Status'] = 1;
							$tempItemData['LeadSource'] = 'QuickbookImport';
							$tempItemData['ProcessID'] = $ProcessID;
							$tempItemData['created_at'] = date('Y-m-d H:i:s.000');
							$tempItemData['created_by'] = 'Imported';

							if(!empty($tempItemData['AccountName'])){
								$count = DB::table('tblAccount')->where(["AccountName" => $tempItemData['AccountName'], "AccountType" => 1,"CompanyId"=>$CompanyID])->count();
								if($count==0){
									$batch_insert_array[] = $tempItemData;
								}
							}

						}
					}// get data from quickbook

					if (!empty($batch_insert_array)) {
						//Log::info('insertion start');
						try{
							if(DB::table('tblTempAccount')->insert($batch_insert_array)){
								$response['result'] = 'OK';
							}
						}catch(Exception $err){
							$response['error'] =  'Failed to connect QuickBook.';
							//$response['faultString'] =  $err->getMessage();
							//$response['faultCode'] =  $err->getCode();
							Log::error("Class Name:".__CLASS__.",Method: ". __METHOD__.", Fault. Code: " . $err->getCode(). ", Reason: " . $err->getMessage());
							//throw new Exception($err->getMessage());
						}
						//Log::info('insertion end');
					}else{
						$response['result'] = 'OK';
					}

					//log::info(print_r($batch_insert_array,true));
				}  // insert into temp account
				else{
					$response['result'] = 'OK';
				}

			}else{
				$response['error'] =  'Failed to connect QuickBook.';
			}
		}else{
			$response['error'] =  'Failed to connect QuickBook.';
		}

		return $response;
	}

	public function createJournal(){
		//$this->getAllAccountPrefrences();
		//$this->getAllJournal();
		$response = array();
		if ($this->is_quickbook()){
			if ($this->quickbooks_is_connected) {

				$Context = $this->Context;

				$realm = $this->realm;

				$JournalEntryService = new QuickBooks_IPP_Service_JournalEntry();

				// Main journal entry object
				$JournalEntry = new QuickBooks_IPP_Object_JournalEntry();
				$JournalEntry->setDocNumber('1240');
				$JournalEntry->setTxnDate(date('Y-m-d'));

				// Debit line
				$Line1 = new QuickBooks_IPP_Object_Line();
				$Line1->setDescription('Line 1 description');
				$Line1->setAmount(100);
				$Line1->setDetailType('JournalEntryLineDetail');

				$Detail1 = new QuickBooks_IPP_Object_JournalEntryLineDetail();
				$Detail1->setPostingType('Debit');
				$Detail1->setAccountRef(131);

				$customer = new QuickBooks_IPP_Object_Entity();
				$customer->setEntityRef('73');

				$Detail1->setEntity($customer);


				$Line1->addJournalEntryLineDetail($Detail1);
				$JournalEntry->addLine($Line1);


				// Credit line

				$Line2 = new QuickBooks_IPP_Object_Line();
				$Line2->setDescription('Line 2 description');
				$Line2->setAmount(100);
				$Line2->setDetailType('JournalEntryLineDetail');

				$Detail2 = new QuickBooks_IPP_Object_JournalEntryLineDetail();
				$Detail2->setPostingType('Credit');
				$Detail2->setAccountRef(131);

				$customer1 = new QuickBooks_IPP_Object_Entity();
				$customer1->setEntityRef('73');

				$Detail2->setEntity($customer1);

				$Line2->addJournalEntryLineDetail($Detail2);
				$JournalEntry->addLine($Line2);

				if ($resp = $JournalEntryService->add($Context, $realm, $JournalEntry))
				{
					if(!empty($resp)){
						$resp = str_replace('{-','',$resp);
						$resp = str_replace('}','',$resp);
					}
					$response['response'] = $resp;
				}
				else
				{
					$response['error'] = $JournalEntryService->lastError($Context);
				}

			}
		}else{
			$response['error'] = 'quickbook not setup';
		}
		log::info(print_r($response,true));
		return $response;
	}

	public function getAllJournal(){
		$journal = array();
		if ($this->is_quickbook()){
			if ($this->quickbooks_is_connected) {

				$Context = $this->Context;

				$realm = $this->realm;

				$the_payment_to_delete = '{-207}';

				$JournalEntryService = new QuickBooks_IPP_Service_JournalEntry();

				//$journal = $JournalEntryService->delete($Context, $realm, $the_payment_to_delete);

				$journal = $JournalEntryService->query($Context, $realm, "SELECT * FROM JournalEntry WHERE TxnDate < '2016-10-09'");

			}
		}

		log::info(print_r($journal,true));

		//return $journal;
		exit;
	}

	public function getChartofAccounts(){
		$AccountPrefrences = array();
		$chartofaccounts = array();
		$data = array();
		if ($this->is_quickbook()){
			if ($this->quickbooks_is_connected) {

				$Context = $this->Context;

				$realm = $this->realm;

				$AccountService = new QuickBooks_IPP_Service_Account();

				$AccountPrefrences = $AccountService->query($Context, $realm, "SELECT * FROM Account");
				if(!empty($AccountPrefrences) && count((array)$AccountPrefrences)>0){
					foreach((array)$AccountPrefrences as $AccountPrefrence){
						$name = $AccountPrefrence->getName();
						if(!empty($name)){
							$chartofaccounts[$name] = $name;
							//$chartofaccounts[] = $data;
						}
					}
				}

			}
		}

		log::info(print_r($AccountPrefrences,true));

		return $chartofaccounts;

	}

}