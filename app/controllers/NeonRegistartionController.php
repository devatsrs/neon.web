<?php

class NeonRegistartionController extends \BaseController {
    /**
     * Display a listing of the resource.
     * GET /accounts
     *
     * @return Response
     */
    public function index() {				
		$data = Input::all();
        $Result_Json = $data['apidata']; //json format
        log::info('Json Data');
        log::info(print_r($Result_Json,true));

        $API_Request = json_decode($Result_Json,true);
        $Personal_data = $API_Request['data_user']['personal_data'];
        $CompanyID=1;
        $UserID = 1;
        $AccountName = $Personal_data['company'];
        $CurrencyID= $Personal_data['currencyId'];
        $CurrencyCode = Currency::getCurrency($CurrencyID);
        $Payment_data = $API_Request['data_user']['payment_data'][0];
        $Payment_type = $Payment_data['payment_type'];
        $Amount = $Payment_data['payment_amount'];

        if($Payment_type=='Paypal' || $Payment_type=='SagePay'){
            $PaymentGatewayID= '';
            $PaymentGateway = 'Paypal';
        }else{
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($Payment_type);
            $PaymentGateway = '';
        }
        $SessionName = 'APIEncodeData';
        Session::put($SessionName, $Result_Json);

        /**PayPal**/

        $paypal = new PaypalIpn($CompanyID);
        if(!empty($paypal->status)){
            $paypal->item_title =  Company::getName($CompanyID).' '.$AccountName. ' API Invoice ';
            $paypal->item_number =  '';
            $paypal->curreny_code =  $CurrencyCode;

            $paypal->amount = $Amount;

            $paypal_button = $paypal->get_api_paynow_button($CompanyID);
        }

		return View::make('neonregistartion.api_invoice_payment', compact('data','Amount','PaymentGatewayID','PaymentGateway','paypal_button','CustomData','CompanyID'));

    }

    public function createaccount(){
        $data=Input::All();
        log::info('invoice account create start');
        log::info(print_r($data,true));
        $CompanyID=$data['CompanyID'];

        if(!empty($data['CreditCard']) && $data['CreditCard']==1){
            log::info('Account Creation with Credit card');
            $testdata=$data['customdata'];
            $NewData= json_decode($testdata,true);
            $paymentdata = json_decode($NewData['PaymentResponse'],true);
            $apidata = json_decode($NewData['APIData'],true);

            //log::info(count($NewData));
            //log::info(print_r($paymentdata,true));
            //log::info(print_r($apidata,true));
            //$paymentdata = $NewData['PaymentResponse'];
            //log::info(print_r($testdata,true));
            //log::info(print_r($NewData,true));
            return Response::json(array("status" => "success", "message" => "Create Account Successfully"));
            //$Reseponse = $this->insertApiAccount($CompanyID,$paymentdata,$apidata);
            //return $Reseponse;

        }

        return Response::json(array("status" => "success", "message" => "Create Account Successfully"));
    }

    public function getJson(){
     /*   $json='{
   "data_widget":{
      "widget":[
         {
            "id":"124",
            "title":"Test 2",
            "permalink":"Testing",
            "currency":"3",
            "customer_email":"9",
            "backoffice_email":"",
            "lang":"gb",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:15",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:15"
         }
      ],
      "setting":[
         {
            "id":"58",
            "widget_id":"124",
            "billing_type":"2",
            "billing_cycle":"monthly",
            "billing_cycle_options":"",
            "billing_class":"6",
            "default_tenant_template":"51",
            "inbound_discount_plan":"",
            "outbound_discount_plan":"",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:15",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:15"
         }
      ],
      "verification":[
         {
            "id":"57",
            "widget_id":"124",
            "alpha_char":"4324525423gyrhtebvfbrt",
            "digits":"15",
            "limit_ver_email":"10",
            "supported_countries":"ARG,BEL,CAN",
            "verification_email":"6",
            "verification_type":"2",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:15",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:15"
         }
      ],
      "hosted_centrex":[
         {
            "id":"71",
            "widget_id":"124",
            "service":"2",
            "inbound_discount_plan":"7",
            "outbound_discount_plan":"14",
            "inbound_tariff":"168",
            "out_bound_tariff":"167",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:15",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:15"
         }
      ],
      "hosted_centrex_ext":[
         {
            "id":"80",
            "widget_id":"124",
            "min_range":"20",
            "max_range":"45",
            "extension_default":"97,107,173",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:16",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:16"
         }
      ],
      "hosted_centrex_ext_subscription":[
         {
            "id":"75",
            "hosted_centrex_ext_id":"80",
            "min_qty":"1",
            "max_qty":"23",
            "setup_fee":"0",
            "subscription":"11",
            "yearly":"2400",
            "monthly":"200",
            "weekly":"50",
            "daily":"7.14",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:16",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:16"
         }
      ],
      "did":[
         {
            "id":"85",
            "widget_id":"124",
            "allow_buy_did":"Yes",
            "allow_buy_magrathea":"No",
            "allow_buy_voxbone":"No",
            "allowed_countries":"ARG,AUT,BRA,CAN",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:16",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:16"
         }
      ],
      "siptrunk":[
         {
            "id":"86",
            "widget_id":"124",
            "service":"2",
            "inbound_disc_plan":"",
            "outbound_disc_plan":"",
            "inbound_tariff":"",
            "outbound_tariff":"19",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:16",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:16"
         }
      ],
      "siptrunk_subscription":[
         {
            "id":"94",
            "siptrunk_id":"86",
            "min_qty":"1",
            "max_qty":"20",
            "setup_fee":"1",
            "subscription":"18",
            "yearly":"1200",
            "monthly":"100",
            "weekly":"23.33",
            "daily":"3.33",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:16",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:16"
         }
      ],
      "payment":[
         {
            "id":"30",
            "widget_id":"124",
            "topup_enable":"Yes",
            "mintopup_amount":"20",
            "created_by":"1",
            "created_on":"2018-07-25 14:59:17",
            "updated_by":"1",
            "updated_on":"2018-07-25 14:59:17"
         }
      ]
   },
   "data_user":{
      "personal_data":{
         "user_id":"subodhkant89@gmail.com",
         "password":"12345678",
         "confirm_password":"12345678",
         "first_name":"Subodh",
         "last_name":"Kant",
         "contact":"08586023115",
         "alt_contact":"08586023115",
         "vat":"44444444444",
         "company":"Subodh Kant",
         "house":"H. No. 61, Gali No. - 3, East Laxmi Market",
         "street":"H. No. 61, Gali No. - 3, East Laxmi Market",
         "postal_code":"110092",
         "city":"New Delhi",
         "country":"BEL",
         "currencyId":"3",
         "languageId":"gb"
      },
      "ext_data":{
         "hosted_centrex_data":[
            {
               "ext_number":"34",
               "ext_name":"Name 1",
               "ext_email":"abc@gmail.com"
            },
            {
               "ext_number":"35",
               "ext_name":"Name 2",
               "ext_email":"abc2@gmail.com"
            }
         ],
         "quantity":2,
         "subscriptionId":"11"
      },
      "did_data":[
         {
            "did_no":"9",
            "did_qty":"4",
            "address1":"Address 1",
            "address2":"Address 2",
            "zip":"123456",
            "countryCodeA3":"CAN",
            "serviceId":"2",
            "subscriptionId":"5",
            "quantity":"4"
         },
         {
            "did_no":"10",
            "did_qty":"7",
            "address1":"Address 3",
            "address2":"Address 4",
            "zip":"654321",
            "countryCodeA3":"CAN",
            "serviceId":"2",
            "subscriptionId":"3",
            "quantity":"7"
         },
         {
            "did_no":"67",
            "did_qty":"6",
            "address1":"Address 5",
            "address2":"Address 6",
            "zip":"232323",
            "countryCodeA3":"CAN",
            "serviceId":"5",
            "subscriptionId":"4",
            "quantity":"6"
         }
      ],
      "siptrunk_data":{
         "quantity":"5",
         "siptrunk_data":"5",
         "subscriptionId":"18"
      },
      "topup_data":{
         "amount":"200"
      },
      "payment_data":[
         {
            "payment_type":"AuthorizeNet",
            "payment_amount":"120.00",
            "card-number":"4111111111111111",
            "card-name":"Subodh Kant",
            "card-type":"Visa",
            "card-cvv-number":"432",
            "ExpirationMonth":"12",
            "ExpirationYear":"2019",
            "card-id":""
         }
      ],
      "summary":{
         "amount":"1323.50"
      }
   }
}'; */
        $json='{"data_widget":{"widget":[{"id":"124","title":"Test 2","permalink":"Testing","currency":"3","customer_email":"9","backoffice_email":"","lang":"gb","created_by":"1","created_on":"2018-07-25 14:59:15","updated_by":"1","updated_on":"2018-07-25 14:59:15"}],"setting":[{"id":"58","widget_id":"124","billing_type":"2","billing_cycle":"monthly","billing_cycle_options":"","billing_class":"6","default_tenant_template":"51","inbound_discount_plan":"","outbound_discount_plan":"","created_by":"1","created_on":"2018-07-25 14:59:15","updated_by":"1","updated_on":"2018-07-25 14:59:15"}],"verification":[{"id":"57","widget_id":"124","alpha_char":"4324525423gyrhtebvfbrt","digits":"15","limit_ver_email":"10","supported_countries":"ARG,BEL,CAN","verification_email":"6","verification_type":"2","created_by":"1","created_on":"2018-07-25 14:59:15","updated_by":"1","updated_on":"2018-07-25 14:59:15"}],"hosted_centrex":[{"id":"71","widget_id":"124","service":"2","inbound_discount_plan":"7","outbound_discount_plan":"14","inbound_tariff":"168","out_bound_tariff":"167","created_by":"1","created_on":"2018-07-25 14:59:15","updated_by":"1","updated_on":"2018-07-25 14:59:15"}],"hosted_centrex_ext":[{"id":"80","widget_id":"124","min_range":"20","max_range":"45","extension_default":"97,107,173","created_by":"1","created_on":"2018-07-25 14:59:16","updated_by":"1","updated_on":"2018-07-25 14:59:16"}],"hosted_centrex_ext_subscription":[{"id":"75","hosted_centrex_ext_id":"80","min_qty":"1","max_qty":"23","setup_fee":"0","subscription":"11","yearly":"2400","monthly":"200","weekly":"50","daily":"7.14","created_by":"1","created_on":"2018-07-25 14:59:16","updated_by":"1","updated_on":"2018-07-25 14:59:16"}],"did":[{"id":"85","widget_id":"124","allow_buy_did":"Yes","allow_buy_magrathea":"No","allow_buy_voxbone":"No","allowed_countries":"ARG,AUT,BRA,CAN","created_by":"1","created_on":"2018-07-25 14:59:16","updated_by":"1","updated_on":"2018-07-25 14:59:16"}],"siptrunk":[{"id":"86","widget_id":"124","service":"2","inbound_disc_plan":"","outbound_disc_plan":"","inbound_tariff":"","outbound_tariff":"19","created_by":"1","created_on":"2018-07-25 14:59:16","updated_by":"1","updated_on":"2018-07-25 14:59:16"}],"siptrunk_subscription":[{"id":"94","siptrunk_id":"86","min_qty":"1","max_qty":"20","setup_fee":"1","subscription":"18","yearly":"1200","monthly":"100","weekly":"23.33","daily":"3.33","created_by":"1","created_on":"2018-07-25 14:59:16","updated_by":"1","updated_on":"2018-07-25 14:59:16"}],"payment":[{"id":"30","widget_id":"124","topup_enable":"Yes","mintopup_amount":"20","created_by":"1","created_on":"2018-07-25 14:59:17","updated_by":"1","updated_on":"2018-07-25 14:59:17"}]},"data_user":{"personal_data":{"user_id":"subodhkant89@gmail.com","password":"12345678","confirm_password":"12345678","first_name":"Subodh","last_name":"Kant","contact":"08586023115","alt_contact":"08586023115","vat":"44444444444","company":"Subodh Kant","house":"H. No. 61, Gali No. - 3, East Laxmi Market","street":"H. No. 61, Gali No. - 3, East Laxmi Market","postal_code":"110092","city":"New Delhi","country":"BEL","currencyId":"3","languageId":"gb"},"ext_data":{"hosted_centrex_data":[{"ext_number":"34","ext_name":"Name 1","ext_email":"abc@gmail.com"},{"ext_number":"35","ext_name":"Name 2","ext_email":"abc2@gmail.com"}],"quantity":2,"subscriptionId":"11"},"did_data":[{"did_no":"9","did_qty":"4","address1":"Address 1","address2":"Address 2","zip":"123456","countryCodeA3":"CAN","serviceId":"2","subscriptionId":"5","quantity":"4"},{"did_no":"10","did_qty":"7","address1":"Address 3","address2":"Address 4","zip":"654321","countryCodeA3":"CAN","serviceId":"2","subscriptionId":"3","quantity":"7"},{"did_no":"67","did_qty":"6","address1":"Address 5","address2":"Address 6","zip":"232323","countryCodeA3":"CAN","serviceId":"5","subscriptionId":"4","quantity":"6"}],"siptrunk_data":{"quantity":"5","siptrunk_data":"5","subscriptionId":"18"},"topup_data":{"amount":"200"},"payment_data":[{"payment_type":"AuthorizeNet","payment_amount":"120.00","card-number":"4111111111111111","card-name":"Subodh Kant","card-type":"Visa","card-cvv-number":"432","ExpirationMonth":"12","ExpirationYear":"2019","card-id":""}],"summary":{"amount":"1323.50"}}}';
        $json = json_decode($json, true);
        return $json;
    }

    public function createpayment(){
        $data = Input::All();
        log::info('Payment Start');
        $CompanyID = $data['CompanyID'];
        $CustomData = json_decode($data['CustomData'],true);
        //log::info(print_r($data,true));

        $PaymentAllData = array();

        $PaymentAllData['CardNumber'] = $data['CardNumber'];
        $PaymentAllData['NameOnCard'] = $data['NameOnCard'];
        $PaymentAllData['CardType'] = $data['CardType'];
        $PaymentAllData['CVVNumber'] = $data['CVVNumber'];
        $PaymentAllData['ExpirationMonth'] = $data['ExpirationMonth']; // Need to Add
        $PaymentAllData['ExpirationYear'] = $data['ExpirationYear']; // Need to Add
        $PaymentAllData['PeleCardID'] = empty($data['PeleCardID']) ? '' : $data['PeleCardID'];
        $PaymentAllData['GrandTotal'] = $data['Amount'];
        $PaymentAllData['InvoiceNumber'] = '';
		$PersonalData = $CustomData['data_user']['personal_data'];

        $PaymentAllData['CurrencyId'] = $PersonalData['currencyId'];
        $PaymentAllData['AccountName'] = $PersonalData['company']; // merchantwarrior
        if(!empty($PersonalData['Country'])) {
            $Country = Country::where(['ISO3' => $PersonalData['Country']])->pluck('Country');
        }else{
            $Country='';
        }
        $PaymentAllData['Country'] = $Country; // merchantwarrior // check
        $PaymentAllData['City'] = $PersonalData['city']; //  merchantwarrior
        $PaymentAllData['Address1'] = $PersonalData['house']; // merchantwarrior
        $PaymentAllData['PostCode'] = $PersonalData['postal_code']; // merchantwarrior
        $PaymentAllData['Phone'] = empty($PersonalData['contact']) ? '' : $PersonalData['contact'];
        $PaymentAllData['Email'] = empty($PersonalData['user_id']) ? '' : $PersonalData['user_id'];

        log::info(print_r($PaymentAllData,true));

        $PaymentGatewayID = $data['PaymentGatewayID'];
        $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);

        $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $CompanyID);
        $PaymentResponse = $PaymentIntegration->paymentWithApiCreditCard($PaymentAllData);
        /**
         *Manual Response cheack
         *
         $PaymentResponse=array();
         $PaymentResponse['PaymentMethod'] = 'CreditCard';
         $PaymentResponse['transaction_notes'] = 'AuthorizeNet transaction_id 60100337434 ';
         $PaymentResponse['Amount'] = floatval($data['Amount']);
         $PaymentResponse['Transaction'] = '60100337434';
         $PaymentResponse['Response'] = '{"approved":true,"declined":false,"error":false,"held":false,"response_code":"1","response_subcode":"1","response_reason_code":"1","response_reason_text":"This transaction has been approved.","authorization_code":"GX8VV4","avs_response":"Y","transaction_id":"60100337434","invoice_number":"","description":"","amount":"210.00","method":"CC","transaction_type":"auth_capture","customer_id":"","first_name":"","last_name":"","company":"","address":"","city":"","state":"","zip_code":"","country":"","phone":"","fax":"","email_address":"","ship_to_first_name":"","ship_to_last_name":"","ship_to_company":"","ship_to_address":"","ship_to_city":"","ship_to_state":"","ship_to_zip_code":"","ship_to_country":"","tax":"","duty":"","freight":"","tax_exempt":"","purchase_order_number":"","md5_hash":"134771E9C87050C775DC8208F04CAE60","card_code_response":"P","cavv_response":"2","account_number":"XXXX6266","card_type":"Visa","split_tender_id":"","requested_amount":"","balance_on_card":"","response":"|1|,|1|,|1|,|This transaction has been approved.|,|GX8VV4|,|Y|,|60100337434|,||,||,|210.00|,|CC|,|auth_capture|,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,|134771E9C87050C775DC8208F04CAE60|,|P|,|2|,||,||,||,||,||,||,||,||,||,||,|XXXX6266|,|Visa|,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||"}';
         $PaymentResponse['status'] = 'success';
         //$PaymentResponse['CustomData'] = $data['CustomData'];
         * */
        log::info('payment response');
        log::info(print_r($PaymentResponse,true));
        $Alldata = array();
        $Alldata['PaymentResponse'] = json_encode($PaymentResponse);
        $Alldata['APIData'] =  $data['CustomData'];
        //log::info(print_r($Alldata,true));

        if($PaymentResponse['status']=='failed'){
            if(!empty($PaymentResponse['transaction_notes'])){
                $message = $PaymentResponse['transaction_notes'];
            }else{
                $message = empty($PaymentResponse['message']) ? '' :$PaymentResponse['message'];
            }
            return Response::json(["status"=>"failed","message" => $message, "data"=>$PaymentResponse]);
        }else{
            return Response::json(["status"=>"success","message" => "Create Payment Successfully", "data"=>json_encode($Alldata)]);
        }
    }

    public function insertApiAccount($CompanyID,$ApiData,$PaymentResponse){
        //$CompanyID = User::get_companyID();
        $User = User::where(['AdminUser'=>1,'Status'=>1,'CompanyID'=>$CompanyID])->first();
        $UserID = $User->UserID;
        //$UserID = User::get_userID();
        $UserName = $User->FirstName.' '.$User->LastName;
		$Result = $ApiData;
		//log::info(print_r($Result,true));
		$PersonalData = $Result['data_user']['personal_data'];
		try{

            DB::beginTransaction();
            DB::connection('sqlsrv2')->beginTransaction();

            /**Create Account Start */

            log::info('Create Account Start');

            $dataAccount=array();
            $dataAccount['Owner'] = $UserID;
            $dataAccount['AccountType'] = 1;
            $dataAccount['CompanyID'] = $CompanyID;
            $dataAccount['CurrencyId'] = $PersonalData['currencyId'];
            $dataAccount['LanguageID'] = 43;
            if (empty($dataAccount['Number'])) {
                $dataAccount['Number'] = Account::getLastAccountNo();
            }
            $dataAccount['Number'] = trim($dataAccount['Number']);
            $dataAccount['AccountName'] = $PersonalData['company'];
            $dataAccount['FirstName'] = $PersonalData['first_name'];
            $dataAccount['LastName'] = $PersonalData['last_name'];
            $dataAccount['Email'] = $PersonalData['user_id'];
            $dataAccount['IsCustomer'] = 1;
            $dataAccount['BillingEmail']= $PersonalData['user_id'];
            $dataAccount['password'] = Hash::make($PersonalData['password']);
            $dataAccount['Billing'] = 1;
            $dataAccount['created_by'] = $UserName;
            $dataAccount['VerificationStatus'] = Account::VERIFIED;
            $dataAccount['Address1'] = $PersonalData['house'];
            $dataAccount['Address2'] = $PersonalData['street'];
            $dataAccount['City']     = $PersonalData['city'];
            $dataAccount['PostCode'] = $PersonalData['postal_code'];
            $dataAccount['Country']  = $PersonalData['country']; // change iso3 to title
            $dataAccount['Mobile']   = $PersonalData['contact'];
            //$dataAccount['Phone']    = $PersonalData['alt_contact'];
            $dataAccount['VatNumber']= $PersonalData['vat'];
            Log::info(print_r($dataAccount,true));
            $account = Account::create($dataAccount);

            /**Create Account End */
            $AccountID = $account->AccountID;

            log::info('Create Account end');

            /**Create Account Billing */

            log::info('Create Account Billing Start');

            $BillingSetting = $Result['data_widget']['setting'][0];
            //Log::info(print_r($BillingSetting,true));
            $dataAccountBilling=array();
            $dataAccountBilling['AccountID'] = $AccountID;
            $dataAccountBilling['ServiceID'] = 0;
            $dataAccountBilling['BillingType'] = $BillingSetting['billing_type'];
            //get from billing class id

            $BillingClass = BillingClass::find($BillingSetting['billing_class']);

            $dataAccountBilling['BillingClassID'] = $BillingSetting['billing_class'];
            $dataAccountBilling['BillingTimezone'] = $BillingClass->BillingTimezone;
            $dataAccountBilling['SendInvoiceSetting'] = empty($BillingClass->SendInvoiceSetting) ? 'after_admin_review' : $BillingClass->SendInvoiceSetting;
            $dataAccountBilling['AutoPaymentSetting'] = empty($BillingClass->AutoPaymentSetting) ? 'never' : $BillingClass->AutoPaymentSetting;
            $dataAccountBilling['AutoPayMethod'] = empty($BillingClass->AutoPayMethod) ? 0 : $BillingClass->AutoPayMethod;
            //get from billing class id over
            $dataAccountBilling['BillingCycleType'] = $BillingSetting['billing_cycle'];
            $dataAccountBilling['BillingCycleValue'] = $BillingSetting['billing_cycle_options'];
            // set as first invoice generate
            $BillingCycleType = $BillingSetting['billing_cycle'];
            $BillingCycleValue = $BillingSetting['billing_cycle_options'];
            $BillingStartDate = date('Y-m-d');
            /**
             *  if not first invoice generation
            Log::info($BillingCycleType.' '.$BillingCycleValue.' '.$BillingStartDate);
            $NextBillingDate = next_billing_date($BillingCycleType, $BillingCycleValue, strtotime($BillingStartDate));
            $NextChargedDate = date('Y-m-d', strtotime('-1 day', strtotime($NextBillingDate)));

            $dataAccountBilling['BillingStartDate'] = $BillingStartDate;
            $dataAccountBilling['LastInvoiceDate'] = $BillingStartDate;
            $dataAccountBilling['LastChargeDate'] = $BillingStartDate;
            $dataAccountBilling['NextInvoiceDate'] = $NextBillingDate;
            $dataAccountBilling['NextChargeDate'] = $NextChargedDate;
             */

            $dataAccountBilling['BillingStartDate'] = $BillingStartDate;
            $dataAccountBilling['LastInvoiceDate']  = $BillingStartDate;
            $dataAccountBilling['LastChargeDate']   = $BillingStartDate;
            $dataAccountBilling['NextInvoiceDate']  = $BillingStartDate;
            $dataAccountBilling['NextChargeDate']   = $BillingStartDate;

            Log::info(print_r($dataAccountBilling,true));

            AccountBilling::insertUpdateBilling($AccountID, $dataAccountBilling,0);
            AccountBilling::storeFirstTimeInvoicePeriod($AccountID, 0);
            CompanySetting::setKeyVal('LastAccountNo', $account->Number);

            //Account level billing period
            $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'),0);

            log::info('Create Account Billing End');

            /**Create Account Billing End*/

            /** Create hosted_centrex Start */

            log::info('Create hosted_centrex Start');

            $CentrexService = $Result['data_widget']['hosted_centrex'][0];

            $CentrexServiceID = $CentrexService['service'];
            $inbound_discount_plan = empty($CentrexService['inbound_discount_plan']) ? 0 : $CentrexService['inbound_discount_plan'];
            $outbound_discount_plan = empty($CentrexService['outbound_discount_plan']) ? 0 : $CentrexService['outbound_discount_plan'];
            $inbound_tariff = empty($CentrexService['inbound_tariff']) ? 0 : $CentrexService['inbound_tariff'];
            $out_bound_tariff = empty($CentrexService['out_bound_tariff']) ? 0 : $CentrexService['out_bound_tariff'];

            Log::info(print_r($CentrexService,true));

            $dataAccountService=array();
            $dataAccountService['AccountID'] = $AccountID;
            $dataAccountService['ServiceID'] = $CentrexServiceID;
            $dataAccountService['CompanyID'] = $CompanyID;

            Log::info(print_r($dataAccountService,true));

            AccountService::insert($dataAccountService);

            if(!empty($AccountPeriod)) {
                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                $AccountSubscriptionID = 0;
                $AccountName='';
                $AccountCLI='';
                $SubscriptionDiscountPlanID=0;
                if($inbound_discount_plan >0){
                    AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $inbound_discount_plan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff,$CentrexServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                }
                if($outbound_discount_plan > 0){
                    AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $outbound_discount_plan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff,$CentrexServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                }

            }

            $date = date('Y-m-d H:i:s');
            if($inbound_tariff>0) {
                $inbounddata = array();
                $inbounddata['CompanyID'] = $CompanyID;
                $inbounddata['AccountID'] = $AccountID;
                $inbounddata['ServiceID'] = $CentrexServiceID;
                $inbounddata['RateTableID'] = $inbound_tariff;
                $inbounddata['Type'] = AccountTariff::INBOUND;
                $inbounddata['created_at'] = $date;
                AccountTariff::create($inbounddata);
            }

            if($out_bound_tariff > 0) {
                $outbounddata = array();
                $outbounddata['CompanyID'] = $CompanyID;
                $outbounddata['AccountID'] = $AccountID;
                $outbounddata['ServiceID'] = $CentrexServiceID;
                $outbounddata['RateTableID'] = $out_bound_tariff;
                $outbounddata['Type'] = AccountTariff::OUTBOUND;
                $outbounddata['created_at'] = $date;
                AccountTariff::create($outbounddata);
            }

            $ext_data = $Result['data_user']['ext_data'];
            if(!empty($ext_data) && !empty($ext_data['subscriptionId'])){
                $SubscriptionID = $ext_data['subscriptionId'];
                $quantity = $ext_data['quantity'];
                $SubscriptionData = array();
                $SubscriptionData['AccountID'] = $AccountID;
                $SubscriptionData['ServiceID'] = $CentrexServiceID;
                $SubscriptionData['SubscriptionID'] = $SubscriptionID;
                $SubscriptionData['Qty'] = $quantity;
                $SubscriptionData['StartDate'] = date('Y-m-d');
                $SubscriptionData['CreatedBy'] = $UserName;
                log::info('Subscription ID '.$ext_data['subscriptionId']);
                log::info('Quantity '.$quantity);
                $this->insertAccountSubscription($SubscriptionData);
            }

            log::info('Create hosted_centrex End');
            /** Create hosted_centrex End */

            /** Create DID Start */

            log::info('Create DID Start');

            $did_datas = $Result['data_user']['did_data'];
            if(!empty($did_datas) && count($did_datas)>0){
                foreach($did_datas as $did_data){
                    $Count = AccountService::where(['AccountID'=>$AccountID,'ServiceID'=>$did_data['serviceId']])->count();
                    if($Count==0){
                        $dataAccountService=array();
                        $dataAccountService['AccountID'] = $AccountID;
                        $dataAccountService['ServiceID'] = $did_data['serviceId'];
                        $dataAccountService['CompanyID'] = $CompanyID;
                        Log::info('New Service ID - DID '.$did_data['serviceId']);
                        AccountService::insert($dataAccountService);
                    }
                    $SubscriptionID = $did_data['subscriptionId'];
                    $quantity = $did_data['quantity'];
                    $SubscriptionData = array();
                    $SubscriptionData['AccountID'] = $AccountID;
                    $SubscriptionData['ServiceID'] = $did_data['serviceId'];
                    $SubscriptionData['SubscriptionID'] = $SubscriptionID;
                    $SubscriptionData['Qty'] = $quantity;
                    $SubscriptionData['StartDate'] = date('Y-m-d');
                    $SubscriptionData['CreatedBy'] = $UserName;
                    log::info('DID Subscription ID '.$did_data['subscriptionId']);
                    log::info('DID Quantity '.$quantity);
                    $this->insertAccountSubscription($SubscriptionData);
                }
            }

            log::info('Create DID End');

            /** Create DID End */

            /** Create SipTrunk */

            log::info('Create SipTrunk Start');

            $SipTrunk = $Result['data_widget']['siptrunk'][0];
            $SipTrunkServiceID=0;
            if(!empty($SipTrunk) && !empty($SipTrunk['service'])){
                $SipTrunkServiceID = $SipTrunk['service'];
                $Count = AccountService::where(['AccountID'=>$AccountID,'ServiceID'=>$SipTrunkServiceID])->count();
                if($Count==0){
                    $dataAccountService=array();
                    $dataAccountService['AccountID'] = $AccountID;
                    $dataAccountService['ServiceID'] = $SipTrunkServiceID;
                    $dataAccountService['CompanyID'] = $CompanyID;
                    Log::info('New Service ID - SipTrunk '.$SipTrunkServiceID);
                    AccountService::insert($dataAccountService);

                    $inbound_discount_plan = empty($SipTrunk['inbound_disc_plan']) ? 0 : $SipTrunk['inbound_disc_plan'];
                    $outbound_discount_plan = empty($SipTrunk['outbound_disc_plan']) ? 0 : $SipTrunk['outbound_disc_plan'];
                    $inbound_tariff = empty($SipTrunk['inbound_tariff']) ? 0 : $SipTrunk['inbound_tariff'];
                    $out_bound_tariff = empty($SipTrunk['out_bound_tariff']) ? 0 : $SipTrunk['out_bound_tariff'];

                    if(!empty($AccountPeriod)) {
                        $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                        $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                        $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                        $AccountSubscriptionID = 0;
                        $AccountName='';
                        $AccountCLI='';
                        $SubscriptionDiscountPlanID=0;
                        if($inbound_discount_plan >0){
                            AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $inbound_discount_plan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff,$SipTrunkServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                        }
                        if($outbound_discount_plan > 0){
                            AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $outbound_discount_plan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff,$SipTrunkServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                        }

                    }

                    $date = date('Y-m-d H:i:s');
                    if($inbound_tariff>0) {
                        $inbounddata = array();
                        $inbounddata['CompanyID'] = $CompanyID;
                        $inbounddata['AccountID'] = $AccountID;
                        $inbounddata['ServiceID'] = $SipTrunkServiceID;
                        $inbounddata['RateTableID'] = $inbound_tariff;
                        $inbounddata['Type'] = AccountTariff::INBOUND;
                        $inbounddata['created_at'] = $date;
                        AccountTariff::create($inbounddata);
                    }

                    if($out_bound_tariff > 0) {
                        $outbounddata = array();
                        $outbounddata['CompanyID'] = $CompanyID;
                        $outbounddata['AccountID'] = $AccountID;
                        $outbounddata['ServiceID'] = $SipTrunkServiceID;
                        $outbounddata['RateTableID'] = $out_bound_tariff;
                        $outbounddata['Type'] = AccountTariff::OUTBOUND;
                        $outbounddata['created_at'] = $date;
                        AccountTariff::create($outbounddata);
                    }
                }
            }
            $siptrunk_data = $Result['data_user']['siptrunk_data'];
            if(!empty($siptrunk_data) && !empty($siptrunk_data['subscriptionId'])){
                if($SipTrunkServiceID>0){
                    $SubscriptionID = $siptrunk_data['subscriptionId'];
                    $quantity = $siptrunk_data['quantity'];
                    $SubscriptionData = array();
                    $SubscriptionData['AccountID'] = $AccountID;
                    $SubscriptionData['ServiceID'] = $SipTrunkServiceID;
                    $SubscriptionData['SubscriptionID'] = $SubscriptionID;
                    $SubscriptionData['Qty'] = $quantity;
                    $SubscriptionData['StartDate'] = date('Y-m-d');
                    $SubscriptionData['CreatedBy'] = $UserName;
                    log::info('SipTrunk Subscription ID '.$siptrunk_data['subscriptionId']);
                    log::info('SipTrunk Quantity '.$quantity);
                    $this->insertAccountSubscription($SubscriptionData);
                }

            }

            log::info('Create SipTrunk End');
            /** Create SipTrunk End */

            /** Create Topup */
            log::info('Create TopUp Start');

            $topup = empty($apidata['data_user']['topup_data']['amount']) ? 0 : $apidata['data_user']['topup_data']['amount'];
            log::info('topup amount '.$topup);
            if($topup>0){
                $paymentdata = array();
                $paymentdata['CompanyID'] = $CompanyID;
                $paymentdata['AccountID'] = $AccountID;
                $paymentdata['InvoiceNo'] = '';
                $paymentdata['PaymentDate'] = date('Y-m-d H:i:s');
                $paymentdata['PaymentMethod'] = $PaymentResponse['PaymentMethod'];
                $paymentdata['CurrencyID'] = $account->CurrencyId;
                $paymentdata['PaymentType'] = 'Payment In';
                $paymentdata['Notes'] = 'API TopUp';
                $paymentdata['Amount'] = floatval($topup);
                $paymentdata['Status'] = 'Approved';
                $paymentdata['CreatedBy'] = $UserName.'(API)';
                $paymentdata['ModifyBy'] = $UserName;
                $paymentdata['created_at'] = date('Y-m-d H:i:s');
                $paymentdata['updated_at'] = date('Y-m-d H:i:s');
                Payment::insert($paymentdata);
            }

            log::info('Create TopUp End');

            /** End Topup */

            /** Invoice Generation Start */

            log::info('Invoice Generation Start');

            //Log::info(CompanyConfiguration::get(1,"PHPExePath") . " " . CompanyConfiguration::get(1,"RMArtisanFileLocation") . "  invoicegenerator " . $CompanyID . " $CronJobID $UserID ". " &");
            $PHPExePath = CompanyConfiguration::getValueConfigurationByKey("PHP_EXE_PATH",$CompanyID);
            $RMArtisanFileLocation = CompanyConfiguration::getValueConfigurationByKey("RM_ARTISAN_FILE_LOCATION",$CompanyID);
            $Command = $PHPExePath.' '.$RMArtisanFileLocation.' '.'singleinvoicegeneration '.$CompanyID.' '.$AccountID;
            exec($Command);

            log::info('Invoice Paid And Payment Start');

            $AccountInvoice = Invoice::where(['CompanyID'=>$CompanyID,'AccountID'=>$AccountID])->first();
            log::info('New Account Invoice');
            log::info(print_r($AccountInvoice,true));
            if(!empty($AccountInvoice)){
                $GrandTotal = $AccountInvoice->GrandTotal;
                $InvoiceID = $AccountInvoice->InvoiceID;
                $FullInvoiceNumber='';
                if($GrandTotal>0){
                    $Invoice=Invoice::find($InvoiceID);
                    $FullInvoiceNumber=$Invoice->FullInvoiceNumber;
                    $Invoice->update(array('InvoiceStatus' => Invoice::PAID));
                }
                /** Payment Add Start */
                $paymentdata = array();
                $paymentdata['CompanyID'] = $CompanyID;
                $paymentdata['AccountID'] = $AccountID;
                $paymentdata['InvoiceNo'] = $FullInvoiceNumber;
                $paymentdata['InvoiceID'] = (int)$InvoiceID;
                $paymentdata['PaymentDate'] = date('Y-m-d H:i:s');
                $paymentdata['PaymentMethod'] = $PaymentResponse['PaymentMethod'];
                $paymentdata['CurrencyID'] = $account->CurrencyId;
                $paymentdata['PaymentType'] = 'Payment In';
                $paymentdata['Notes'] = $PaymentResponse['transaction_notes'];
                if($topup>0){
                    $paymentdata['Amount'] = floatval($PaymentResponse['Amount'] - $topup);
                }else{
                    $paymentdata['Amount'] = floatval($PaymentResponse['Amount']);
                }

                $paymentdata['Status'] = 'Approved';
                $paymentdata['CreatedBy'] = $UserName.'(API)';
                $paymentdata['ModifyBy'] = $UserName;
                $paymentdata['created_at'] = date('Y-m-d H:i:s');
                $paymentdata['updated_at'] = date('Y-m-d H:i:s');
                Payment::insert($paymentdata);
                /** Payment Add End */
            }else{
                Log::info($AccountID.' Invoice was not generated');
            }
            log::info('Invoice Paid And Payment End');

            log::info('Invoice Generation End');

            /** Invoice Generation End */

            DB::commit();
            DB::connection('sqlsrv2')->commit();

            $Response = array();
            $Response['AccountID'] = $AccountID;
            $Response['Status'] = 'success';

            return $Response;

        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            DB::connection('sqlsrv2')->rollback();
            return Response::json(["status"=>"failed", "data"=>"","PaymentTransaction"=>$PaymentResponse['transaction_notes'],"error"=>'something gone wrong please contact your system administrator']);
        }
	}
    public function insertAccountSubscription($data=array()){
        log::info('AccountSubscription Start '.$data['SubscriptionID']);
        $dataAccountSubscription=array();
        $dataAccountSubscription['AccountID'] = $data['AccountID'];
        $dataAccountSubscription['ServiceID'] = $data['ServiceID'];
        $dataAccountSubscription['Status'] = 1;
        $Subscription = BillingSubscription::where(['SubscriptionID'=>$data['SubscriptionID']])->first();
        $dataAccountSubscription['SubscriptionID'] = $data['SubscriptionID'];
        $dataAccountSubscription['InvoiceDescription'] = $Subscription->InvoiceLineDescription;
        $dataAccountSubscription['Qty'] = $data['Qty'];
        $dataAccountSubscription['StartDate'] = $data['StartDate'];
        //$dataAccountSubscription['EndDate'];
        $dataAccountSubscription['ExemptTax'] = 0;
        $dataAccountSubscription['ActivationFee'] = $Subscription->ActivationFee;
        $dataAccountSubscription['AnnuallyFee'] = $Subscription->AnnuallyFee;
        $dataAccountSubscription['QuarterlyFee'] = $Subscription->QuarterlyFee;
        $dataAccountSubscription['MonthlyFee'] = $Subscription->MonthlyFee;
        $dataAccountSubscription['WeeklyFee'] = $Subscription->WeeklyFee;
        $dataAccountSubscription['DailyFee'] = $Subscription->DailyFee;
        $SequenceNo = AccountSubscription::where(['AccountID'=>$data["AccountID"]])->max('SequenceNo');
        $SequenceNo = $SequenceNo +1;
        $dataAccountSubscription['SequenceNo'] = $SequenceNo;
        $dataAccountSubscription["CreatedBy"] = $data['CreatedBy'];
        AccountSubscription::create($dataAccountSubscription);
        log::info('AccountSubscription End');
        return '';
    }
}
