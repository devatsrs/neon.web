<?php
use Illuminate\Support\Facades\Crypt;
class NeonRegistartionController extends \BaseController {
    /**
     * Display a listing of the resource.
     * GET /accounts
     *
     * @return Response
     */
    public function index() {
        $data = Input::all();
        $error=0;
        $APILog=array();
        log::info('Data');
        log::info(print_r($data,true));
        $Result_Json = $data['data']; //json format
        log::info('Json Data');
        log::info(print_r($Result_Json,true));
        $API_Request = json_decode($Result_Json,true);
        //log::info(print_r($API_Request,true));
        if(!empty($API_Request['HTTP_REFERER'])){
            $APILog['RequestUrl'] = $API_Request['HTTP_REFERER'];
            Session::put('API_BACK_URL',$API_Request['HTTP_REFERER']);
            log::info('API REQUEST URL '.$API_Request['HTTP_REFERER']);

        }else{
            $APILog['RequestUrl'] = $_SERVER['HTTP_REFERER'];
            Session::put('API_BACK_URL',$_SERVER['HTTP_REFERER']);
            log::info('API REQUEST URL '.$_SERVER['HTTP_REFERER']);
        }

        $UserID = $API_Request['UserID'];
        log::info('UserID '.$UserID);
        $CompanyID = User::where(["UserID"=>$UserID])->pluck('CompanyID');
        log::info('CompanyID '.$CompanyID);

        $AccountName='';
        $CurrencyID=0;
        $CurrencyCode='';
        $errormessage='';
        if(!empty($API_Request['AccountID'])){
            $AccountName = Account::where(["AccountID"=>$API_Request['AccountID']])->pluck('AccountName');
            $CurrencyID = Account::where(["AccountID"=>$API_Request['AccountID']])->pluck('CurrencyId');
        }else{
            if(isset($API_Request['data_user']['personal_data'])) {
                $Personal_data = $API_Request['data_user']['personal_data'];
                if(!empty($Personal_data['company'])){
                    $AccountName = $Personal_data['company'];
                }
                if(!empty($Personal_data['company'])) {
                    $CurrencyID = $Personal_data['currencyId'];
                }
            }else{
                $errormessage.='Personal Data not define.';
            }
        }
        if(!empty($CurrencyID)) {
            $CurrencyCode = Currency::getCurrency($CurrencyID);
        }
        $Payment_type='';
        $Amount=0;
        $PaymentGatewayID = '';
        $PaymentGateway = '';
        if(isset($API_Request['data_user']['payment_data'][0])) {
            $Payment_data = $API_Request['data_user']['payment_data'][0];
            if(isset($Payment_data['payment_type']) && isset($Payment_data['payment_amount'])){
                $Payment_type = $Payment_data['payment_type'];
                if($Payment_data['payment_amount'] && is_numeric($Payment_data['payment_amount']) && $Payment_data['payment_amount']>0) {
                    $Amount = $Payment_data['payment_amount'];
                    if ($Payment_type == 'Paypal' || $Payment_type == 'SagePay') {
                        $PaymentGatewayID = '';
                        $PaymentGateway = $Payment_type;
                    } else {
                        $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($Payment_type);
                        $PaymentGateway = '';
                    }
                }else{
                    $errormessage.=' Payment Amount not set correctly.';
                }
            }else{
                $errormessage.=' Payment Type or Payment Amount not define.';
            }

        }else{
            $errormessage.=' Payment Data not define.';
        }
        $SessionName = 'APIEncodeData';
        Session::put($SessionName, $Result_Json);
        if($AccountName==''){
            $errormessage.=' AccountName not set correctly.';
        }
        if(empty($CurrencyID)){
            $errormessage.=' Currency not set correctly.';
        }

        if($errormessage=='') {
            /**PayPal**/
            $paypal_button = $sagepay_button = "";
            $paypal = new PaypalIpn($CompanyID);
            if (!empty($paypal->status)) {
                $paypal->item_title = Company::getName($CompanyID) . ' ' . $AccountName . ' API Invoice ';
                $paypal->item_number = '';
                $paypal->curreny_code = $CurrencyCode;

                $paypal->amount = $Amount;

                $paypal_button = $paypal->get_api_paynow_button($CompanyID);
            }
            if ( (new SagePay($CompanyID))->status()) {

                $SagePay = new SagePay($CompanyID);

                $SagePay->item_title = Company::getName($CompanyID) . ' ' . $AccountName . ' API Invoice ';
                $SagePay->item_number =  '';
                $SagePay->curreny_code =  $CurrencyCode;


                $SagePay->amount = $Amount;

                $sagepay_button = $SagePay->get_api_paynow_button($CompanyID);

            }
        }else{
            $error=1;
            $APILog['NeonAccountStatus'] = 'failed';
            $Response['status'] = 'failed';
            $Response['NeonStatus'] = 'failed';
            $Response['NeonMessage'] = $errormessage;
            $APILog['FinalApiResponse'] = json_encode($Response);
        }
        $CustomData = $Result_Json;

        $APILog['CompanyID']=$CompanyID;
        $APILog['UserID']=$UserID;
        $APILog['AccountName']=$AccountName;
        $APILog['ApiJson']=$Result_Json;
        $APILog['PaymentGateway']=$Payment_type;
        $APILog['created_at']=date('Y-m-d H:i:s');
        if(!empty($API_Request['AccountID'])){
            $APILog['AccountID']=$API_Request['AccountID'];
        }
        $RegistarionApiLogID = DB::table('tblRegistarionApiLog')->insertGetId($APILog);
        log::info('$LastLog ID '.$RegistarionApiLogID);
        Session::put('RegistarionApiLogID', $RegistarionApiLogID);

        $BackRequestUrl = $APILog['RequestUrl'];
        log::info('ErrorMessage : '.$errormessage);

		return View::make('neonregistartion.api_invoice_payment', compact('data','Amount','PaymentGatewayID','PaymentGateway','paypal_button','sagepay_button','CustomData','CompanyID','BackRequestUrl','error','errormessage'));

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

            if(!empty($apidata['AccountID'])){
                log::info('Update API Account');
                $AccountID = $apidata['AccountID'];
                $Reseponse = $this->updateApiAccount($CompanyID,$AccountID,$paymentdata,$apidata);
            }else{
                log::info('Create API Account');
                $Reseponse = $this->insertApiAccount($CompanyID,$paymentdata,$apidata);
            }


            log::info('Last Log ID '.Session::get('RegistarionApiLogID'));
            log::info('Account Creation Reseponse');
            log::info(print_r($Reseponse,true));

            $RegistarionApiLogID = Session::get('RegistarionApiLogID');
            $RegistarionApiLogUpdate = array();
            if(!empty($RegistarionApiLogID)){
                $RegistarionApiLogUpdate['NeonAccountStatus'] = $Reseponse['NeonStatus'];
                $RegistarionApiLogUpdate['AccountID'] = $Reseponse['AccountID'];
                $RegistarionApiLogUpdate['FinalApiResponse'] = json_encode($Reseponse);
                DB::table('tblRegistarionApiLog')->where('RegistarionApiLogID', $RegistarionApiLogID)->update($RegistarionApiLogUpdate);
            }

            return $Reseponse;

        }

        return Response::json(array("status" => "success", "message" => "Create Account Successfully"));
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


        if(!empty($CustomData['AccountID'])){
            $Account = Account::where('AccountID',$CustomData['AccountID'])->first();
            $currencyId = $Account->CurrencyId;
            $AccountName = $Account->AccountName;
            $Country = empty($Account->Country) ? '' : $Account->Country;
            $city = empty($Account->City) ? '' : $Account->City;
            $Address1 = empty($Account->Address1) ? '' : $Account->Address1;
            $PostCode = empty($Account->PostCode) ? '' : $Account->PostCode;
            $Phone = empty($Account->Phone) ? '' : $Account->Phone;
            $Email = empty($Account->BillingEmail) ? '' : $Account->BillingEmail;

        }else{
            $PersonalData = $CustomData['data_user']['personal_data'];
            $currencyId = $PersonalData['currencyId'];
            $AccountName = $PersonalData['company'];
            if(!empty($PersonalData['country'])) {
                $Country = Country::where(['ISO3' => $PersonalData['country']])->pluck('Country');
            }else{
                $Country='';
            }
            $city = empty($PersonalData['city']) ? '' : $PersonalData['city'];
            $Address1 = empty($PersonalData['house']) ? '' : $PersonalData['house']; // merchantwarrior
            $PostCode = empty($PersonalData['postal_code']) ? '' : $PersonalData['postal_code']; // merchantwarrior
            $Phone = empty($PersonalData['contact']) ? '' : $PersonalData['contact'];
            $Email = empty($PersonalData['user_id']) ? '' : $PersonalData['user_id'];
        }


        $PaymentAllData['CurrencyId'] = $currencyId;
        $PaymentAllData['AccountName'] = $AccountName; // merchantwarrior
        $PaymentAllData['Country'] = $Country; // merchantwarrior // check
        $PaymentAllData['City'] = $city; //  merchantwarrior
        $PaymentAllData['Address1'] = $Address1; // merchantwarrior
        $PaymentAllData['PostCode'] = $PostCode; // merchantwarrior
        $PaymentAllData['Phone'] = $Phone;
        $PaymentAllData['Email'] = $Email;

        log::info(print_r($PaymentAllData,true));

        $PaymentGatewayID = $data['PaymentGatewayID'];
        $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);

        $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $CompanyID);
        $PaymentResponse = $PaymentIntegration->paymentWithApiCreditCard($PaymentAllData);
        /**
         *Manual Response cheack
         **
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

        $RegistarionApiLogID = Session::get('RegistarionApiLogID');
        log::info('R LogID '.$RegistarionApiLogID);
        $RegistarionApiLogUpdate = array();
        if(!empty($RegistarionApiLogID)){
            $RegistarionApiLogUpdate['PaymentAmount'] = $data['Amount'];
            $RegistarionApiLogUpdate['PaymentResponse'] = json_encode($PaymentResponse);
            if($PaymentResponse['status']=='failed'){
                $RegistarionApiLogUpdate['PaymentStatus'] = 'failed';
            }else{
                $RegistarionApiLogUpdate['PaymentStatus'] = 'success';
            }
            DB::table('tblRegistarionApiLog')->where('RegistarionApiLogID', $RegistarionApiLogID)->update($RegistarionApiLogUpdate);
        }

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

    public function insertApiAccount($CompanyID,$PaymentResponse,$ApiData){
        $UserID = $ApiData['UserID'];
        //$UserID = User::get_userID();
        $User = User::where(['UserID'=>$UserID])->first();
        $UserName = $User->FirstName.' '.$User->LastName;
        log::info('Insert Api Account');
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
            $dataAccount['Status'] = 1;
            $dataAccount['CompanyID'] = $CompanyID;
            $dataAccount['CurrencyId'] = $PersonalData['currencyId'];
            $dataAccount['LanguageID'] = empty($PersonalData['languageId']) ? Translation::$default_lang_id : $PersonalData['languageId'];
            $dataAccount['Number'] = Illuminate\Support\Str::slug($PersonalData['company']);
            $dataAccount['AccountName'] = $PersonalData['company'];
            $dataAccount['FirstName'] = empty($PersonalData['first_name']) ? '' : $PersonalData['first_name'];
            $dataAccount['LastName'] = empty($PersonalData['last_name']) ? '' : $PersonalData['last_name'];
            $dataAccount['Email'] = $PersonalData['user_id'];
            $dataAccount['IsCustomer'] = 1;
            $dataAccount['BillingEmail']= $PersonalData['user_id'];
            //$dataAccount['password'] = Hash::make($PersonalData['password']);
            $dataAccount['password'] = Crypt::encrypt($PersonalData['password']);
            $dataAccount['Billing'] = 1;
            $dataAccount['created_by'] = $UserName;
            $dataAccount['VerificationStatus'] = Account::VERIFIED;
            $dataAccount['Address1'] = empty($PersonalData['house']) ? '' : $PersonalData['house'];
            $dataAccount['Address2'] = empty($PersonalData['street']) ? '' : $PersonalData['street'];
            $dataAccount['City']     = empty($PersonalData['city']) ? '' : $PersonalData['city'];
            $dataAccount['PostCode'] = empty($PersonalData['postal_code']) ? '' : $PersonalData['postal_code'];
            if(!empty($PersonalData['country'])) {
                $Country = Country::where(['ISO3' => $PersonalData['country']])->pluck('Country');
            }else{
                $Country='';
            }
            $dataAccount['Country']  = $Country; // change iso3 to title
            $dataAccount['Mobile']   = empty($PersonalData['contact']) ? '' : $PersonalData['contact'];
            $dataAccount['Phone']    = empty($PersonalData['contact']) ? '' : $PersonalData['contact'];
            $dataAccount['VatNumber']= empty($PersonalData['vat']) ? '' : $PersonalData['vat'];
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
            $dataAccountBilling['BillingCycleValue'] = empty($BillingSetting['billing_cycle_options']) ? '' : $BillingSetting['billing_cycle_options'];
            // set as first invoice generate
            $BillingCycleType = $BillingSetting['billing_cycle'];
            $BillingCycleValue = $BillingSetting['billing_cycle_options'];
            $BillingStartDate = date('Y-m-d');
            /**
             *  if not first invoice generation*/
            Log::info($BillingCycleType.' '.$BillingCycleValue.' '.$BillingStartDate);
            $NextBillingDate = next_billing_date($BillingCycleType, $BillingCycleValue, strtotime($BillingStartDate));
            $NextChargedDate = date('Y-m-d', strtotime('-1 day', strtotime($NextBillingDate)));

            $dataAccountBilling['BillingStartDate'] = $BillingStartDate;
            $dataAccountBilling['LastInvoiceDate'] = $BillingStartDate;
            $dataAccountBilling['LastChargeDate'] = $BillingStartDate;
            $dataAccountBilling['NextInvoiceDate'] = $NextBillingDate;
            $dataAccountBilling['NextChargeDate'] = $NextChargedDate;
            /**
             *  if not first invoice generation

            $dataAccountBilling['BillingStartDate'] = $BillingStartDate;
            $dataAccountBilling['LastInvoiceDate']  = $BillingStartDate;
            $dataAccountBilling['LastChargeDate']   = $BillingStartDate;
            $dataAccountBilling['NextInvoiceDate']  = $BillingStartDate;
            $dataAccountBilling['NextChargeDate']   = $BillingStartDate;
             */
            Log::info(print_r($dataAccountBilling,true));

            AccountBilling::insertUpdateBilling($AccountID, $dataAccountBilling,0);
            AccountBilling::storeFirstTimeInvoicePeriod($AccountID, 0);
            CompanySetting::setKeyVal('LastAccountNo', $account->Number);

            /** credit limit insert start */
            if(!empty($BillingSetting['credit_limit'])){
                $AccountBalancedata=array();
                $AccountBalancedata['PermanentCredit']=$BillingSetting['credit_limit'];
                if (AccountBalance::where('AccountID', $AccountID)->count()) {
                    AccountBalance::where('AccountID', $AccountID)->update($AccountBalancedata);
                    $AccountBalancedata['AccountID']=$AccountID;
                } elseif (AccountBalance::where('AccountID', $AccountID)->count() == 0) {
                    $AccountBalancedata['AccountID']=$AccountID;
                    AccountBalance::create($AccountBalancedata);
                }

                $AccountBalancedata['created_at'] = date('Y-m-d H:i:s');
                $AccountBalancedata['CreatedBy'] =$UserName;
                DB::table('tblAccountBalanceHistory')->insert($AccountBalancedata);

            }

            /** credit limit insert end */

            //Account level billing period
            $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'),0);

            /**Account Level Discount Plan Start*/

            $main_inbound_discount_plan = empty($BillingSetting['inbound_discount_plan']) ? 0 : $BillingSetting['inbound_discount_plan'];
            $main_outbound_discount_plan = empty($BillingSetting['outbound_discount_plan']) ? 0 : $BillingSetting['outbound_discount_plan'];

            if (!empty($AccountPeriod)) {
                $CentrexServiceID = 0;
                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                $AccountSubscriptionID = 0;
                $AccountName = '';
                $AccountCLI = '';
                $SubscriptionDiscountPlanID = 0;
                if ($main_inbound_discount_plan > 0) {
                    AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $main_inbound_discount_plan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $CentrexServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                }
                if ($main_outbound_discount_plan > 0) {
                    AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $main_outbound_discount_plan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $CentrexServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                }

            }

            /**Account Level Discount Plan End*/

            log::info('Create Account Billing End');

            /**Create Account Billing End*/

            /** Create hosted_centrex Start */

            log::info('Create hosted_centrex Start');

            if(isset($Result['data_widget']['hosted_centrex'][0])){
                $CentrexService = $Result['data_widget']['hosted_centrex'][0];
            }else{
                $CentrexService = array();
            }

            if(!empty($CentrexService['service'])) {

                $CentrexServiceID = $CentrexService['service'];
                $inbound_discount_plan = empty($CentrexService['inbound_discount_plan']) ? 0 : $CentrexService['inbound_discount_plan'];
                $outbound_discount_plan = empty($CentrexService['outbound_discount_plan']) ? 0 : $CentrexService['outbound_discount_plan'];
                $inbound_tariff = empty($CentrexService['inbound_tariff']) ? 0 : $CentrexService['inbound_tariff'];
                $out_bound_tariff = empty($CentrexService['out_bound_tariff']) ? 0 : $CentrexService['out_bound_tariff'];

                Log::info(print_r($CentrexService, true));

                $dataAccountService = array();
                $dataAccountService['AccountID'] = $AccountID;
                $dataAccountService['ServiceID'] = $CentrexServiceID;
                $dataAccountService['CompanyID'] = $CompanyID;

                Log::info(print_r($dataAccountService, true));

                AccountService::insert($dataAccountService);

                if (!empty($AccountPeriod)) {
                    $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                    $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                    $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                    $AccountSubscriptionID = 0;
                    $AccountName = '';
                    $AccountCLI = '';
                    $SubscriptionDiscountPlanID = 0;
                    if ($inbound_discount_plan > 0) {
                        AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $inbound_discount_plan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $CentrexServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                    }
                    if ($outbound_discount_plan > 0) {
                        AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $outbound_discount_plan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $CentrexServiceID, $AccountSubscriptionID, $AccountName, $AccountCLI, $SubscriptionDiscountPlanID);
                    }

                }

                $date = date('Y-m-d H:i:s');
                if ($inbound_tariff > 0) {
                    $inbounddata = array();
                    $inbounddata['CompanyID'] = $CompanyID;
                    $inbounddata['AccountID'] = $AccountID;
                    $inbounddata['ServiceID'] = $CentrexServiceID;
                    $inbounddata['RateTableID'] = $inbound_tariff;
                    $inbounddata['Type'] = AccountTariff::INBOUND;
                    $inbounddata['created_at'] = $date;
                    AccountTariff::create($inbounddata);
                }

                if ($out_bound_tariff > 0) {
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
                if (!empty($ext_data) && !empty($ext_data['subscriptionId'])) {
                    $SubscriptionID = $ext_data['subscriptionId'];
                    $quantity = $ext_data['quantity'];
                    $SubscriptionData = array();
                    $SubscriptionData['AccountID'] = $AccountID;
                    $SubscriptionData['ServiceID'] = $CentrexServiceID;
                    $SubscriptionData['SubscriptionID'] = $SubscriptionID;
                    $SubscriptionData['Qty'] = $quantity;
                    $SubscriptionData['StartDate'] = $NextBillingDate;
                    $SubscriptionData['CreatedBy'] = $UserName;
                    log::info('Subscription ID ' . $ext_data['subscriptionId']);
                    log::info('Quantity ' . $quantity);
                    $this->insertAccountSubscription($SubscriptionData);
                }

            }else{
                log::info('Skip hosted_centrex');
                log::info('Skip ext_data');
            }
            log::info('Create hosted_centrex End');
            /** Create hosted_centrex End */

            /** Create DID Start */

            log::info('Create DID Start');

            if(isset($Result['data_user']['did_data'])){
                $did_datas = $Result['data_user']['did_data'];
            }else{
                $did_datas = array();
            }
            if(!empty($did_datas) && count($did_datas)>0){
                foreach($did_datas as $did_data) {
                    if(!empty($did_data['serviceId'])) {
                        $Count = AccountService::where(['AccountID' => $AccountID, 'ServiceID' => $did_data['serviceId']])->count();
                        if ($Count == 0) {
                            $dataAccountService = array();
                            $dataAccountService['AccountID'] = $AccountID;
                            $dataAccountService['ServiceID'] = $did_data['serviceId'];
                            $dataAccountService['CompanyID'] = $CompanyID;
                            Log::info('New Service ID - DID ' . $did_data['serviceId']);
                            AccountService::insert($dataAccountService);
                        }
                        if (!empty($did_data['subscriptionId'])) {
                            $SubscriptionID = $did_data['subscriptionId'];
                            $quantity = $did_data['quantity'];
                            $SubscriptionData = array();
                            $SubscriptionData['AccountID'] = $AccountID;
                            $SubscriptionData['ServiceID'] = $did_data['serviceId'];
                            $SubscriptionData['SubscriptionID'] = $SubscriptionID;
                            $SubscriptionData['Qty'] = $quantity;
                            //$SubscriptionData['StartDate'] = date('Y-m-d');
                            $SubscriptionData['StartDate'] = $NextBillingDate;
                            $SubscriptionData['CreatedBy'] = $UserName;
                            log::info('DID Subscription ID ' . $did_data['subscriptionId']);
                            log::info('DID Quantity ' . $quantity);
                            $this->insertAccountSubscription($SubscriptionData);
                        }
                    }
                }
            }

            log::info('Create DID End');

            /** Create DID End */

            /** Create SipTrunk */

            log::info('Create SipTrunk Start');
            if(isset($Result['data_widget']['siptrunk'][0])){
                $SipTrunk = $Result['data_widget']['siptrunk'][0];
            }else{
                $SipTrunk = array();
            }

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
            if(isset($Result['data_user']['siptrunk_data'])){
                $siptrunk_data = $Result['data_user']['siptrunk_data'];
            }else{
                $siptrunk_data = array();
            }
            if(!empty($siptrunk_data) && !empty($siptrunk_data['subscriptionId'])){
                if($SipTrunkServiceID>0){
                    $SubscriptionID = $siptrunk_data['subscriptionId'];
                    $quantity = $siptrunk_data['quantity'];
                    $SubscriptionData = array();
                    $SubscriptionData['AccountID'] = $AccountID;
                    $SubscriptionData['ServiceID'] = $SipTrunkServiceID;
                    $SubscriptionData['SubscriptionID'] = $SubscriptionID;
                    $SubscriptionData['Qty'] = $quantity;
                    //$SubscriptionData['StartDate'] = date('Y-m-d');
                    $SubscriptionData['StartDate'] = $NextBillingDate;
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
            /*
            $topup = empty($Result['data_user']['topup_data']['amount']) ? 0 : $Result['data_user']['topup_data']['amount'];
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
                $paymentdata['Notes'] = 'TopUp';
                $paymentdata['Amount'] = floatval($topup);
                $paymentdata['Status'] = 'Approved';
                $paymentdata['CreatedBy'] = $UserName.'(API)';
                $paymentdata['ModifyBy'] = $UserName;
                $paymentdata['created_at'] = date('Y-m-d H:i:s');
                $paymentdata['updated_at'] = date('Y-m-d H:i:s');
                Payment::insert($paymentdata);
            }
            */
            log::info('Create TopUp End');

            /** End Topup */

            /** Invoice Generation Start */

            DB::commit();
            DB::connection('sqlsrv2')->commit();

            log::info('Invoice Generation Start');

            /*
            $PHPExePath = CompanyConfiguration::getValueConfigurationByKey("PHP_EXE_PATH",$CompanyID);
            $RMArtisanFileLocation = CompanyConfiguration::getValueConfigurationByKey("RM_ARTISAN_FILE_LOCATION",$CompanyID);
            $Command = $PHPExePath.' '.$RMArtisanFileLocation.' '.'singleinvoicegeneration '.$CompanyID.' '.$AccountID;
            RemoteSSH::run($Command);
            //exec($Command);
            */

            $InvoiceID=0;
            $FullInvoiceNumber='';
            $RegistarionApiLogUpdate = array();
            $RegistarionApiLogUpdate['InvoiceStatus'] = 'failed';
            $RegistarionApiLogUpdate['InvoiceID'] = 0;

            if(!empty($Result['data_user']['summary'])) {
                $summarydata = $Result['data_user']['summary'];
                $Result = $this->createAPIInvoice($CompanyID,$AccountID,$UserName,$summarydata);
                if(isset($Result['status']) && $Result['status']=='success'){
                    $InvoiceID = $Result['LastID'];
                    $Invoice=Invoice::find($InvoiceID);
                    $FullInvoiceNumber=$Invoice->FullInvoiceNumber;
                    $Invoice->update(array('InvoiceStatus' => Invoice::PAID));

                    $RegistarionApiLogUpdate['InvoiceStatus'] = 'success';
                    $RegistarionApiLogUpdate['InvoiceID'] = $InvoiceID;
                    Log::info($InvoiceID.' Invoice was generated successfully');
                }
            }
            $RegistarionApiLogID = Session::get('RegistarionApiLogID');
            if(!empty($RegistarionApiLogID)){
                DB::table('tblRegistarionApiLog')->where('RegistarionApiLogID', $RegistarionApiLogID)->update($RegistarionApiLogUpdate);
            }

            log::info('Invoice Generation End');

            /** Invoice Generation End */

            log::info('Invoice Payment Start');

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
            /*
            if($topup>0){
                $paymentdata['Amount'] = floatval($PaymentResponse['Amount'] - $topup);
            }else{
                $paymentdata['Amount'] = floatval($PaymentResponse['Amount']);
            }*/
            $paymentdata['Amount'] = floatval($PaymentResponse['Amount']);

            $paymentdata['Status'] = 'Approved';
            $paymentdata['CreatedBy'] = $UserName.'(API)';
            $paymentdata['ModifyBy'] = $UserName;
            $paymentdata['created_at'] = date('Y-m-d H:i:s');
            $paymentdata['updated_at'] = date('Y-m-d H:i:s');
            Payment::insert($paymentdata);
            /** Payment Add End */

            log::info('Invoice Payment End');

            $Response = array();
            $Response['AccountID'] = $AccountID;
            $Response['AccountNumber'] = $dataAccount['Number'];
            $Response['status'] = 'success';
            $Response['message'] = 'Account Create Successfully';
            $Response['PaymentStatus'] = 'success';
            $Response['PaymentMessage'] = 'Payment Create Successfully';
            $Response['NeonStatus'] = 'success';
            $Response['NeonMessage'] = 'Account Create Successfully';
            $ApiRequestUrl = Session::get('API_BACK_URL');
            $Response['ApiRequestUrl'] = $ApiRequestUrl;
            //$response['ApiRequestData'] = json_encode($ApiData);
            return $Response;

        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            DB::connection('sqlsrv2')->rollback();
            $Response = array();
            $Response['status'] = 'failed';
            $Response['message'] = 'something gone wrong please contact your system administrator';
            $Response['PaymentStatus'] = 'success';
            $Response['PaymentMessage'] = 'Payment Create Successfully';
            $Response['NeonStatus'] = 'failed';
            $Response['NeonMessage'] = 'something gone wrong please contact your system administrator';
            $ApiRequestUrl = Session::get('API_BACK_URL');
            $Response['ApiRequestUrl'] = $ApiRequestUrl;
            $Response['AccountID']='';
            $Response['AccountNumber']='';
            //$response['ApiRequestData'] = json_encode($ApiData);
            return $Response;

            //return Response::json(["status"=>"failed", "data"=>"","PaymentTransaction"=>$PaymentResponse['transaction_notes'],"error"=>'something gone wrong please contact your system administrator']);
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
        /**if subscription in advance than start date will next invoice generation day
         * Other wise today
         */
        if(!empty($Subscription->Advance)){
            $dataAccountSubscription['StartDate'] = $data['StartDate'];
        }else{
            $dataAccountSubscription['StartDate'] = date('Y-m-d');
        }
        $dataAccountSubscription['EndDate'] = '';
        $dataAccountSubscription['ExemptTax'] = 0;
        if(!empty($Subscription->Advance)) {
            $dataAccountSubscription['ActivationFee'] = 0; // Allready charge in one off invoice
        }else{
            $dataAccountSubscription['ActivationFee'] = $Subscription->ActivationFee * $data['Qty'];
        }
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

    /**
     * Update api account
     * skip account creation,account billing,invoice generation
     * if no service in neon than widget service,tariff,discount-plan insert
     * if service in neon than only subscription add
     * payment two entry - one top up and other total-top up
    */

    public function updateApiAccount($CompanyID,$AccountID,$PaymentResponse,$ApiData){
        $UserID = $ApiData['UserID'];
        $User = User::where(['UserID'=>$UserID])->first();
        $UserName = $User->FirstName.' '.$User->LastName;
        log::info('Update Api Account Start');
        $account = Account::where('AccountID',$AccountID)->first();
        $Result = $ApiData;
        try{

            DB::beginTransaction();
            DB::connection('sqlsrv2')->beginTransaction();

            //Account level billing period
            $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'),0);

            $NextBillingDate=AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>0))->pluck('NextInvoiceDate');
            if(empty($NextBillingDate)){
                $NextBillingDate=date('Y-m-d');
            }

            /**Create Account Billing End*/

            /** Create hosted_centrex Start */

            log::info('Create hosted_centrex Start');

            if(isset($Result['data_widget']['hosted_centrex'][0])){
                $CentrexService = $Result['data_widget']['hosted_centrex'][0];
            }else{
                $CentrexService = array();
            }

            if(!empty($CentrexService['service'])){
                $CentrexServiceID = $CentrexService['service'];

                log::info('hosted_centrex Service '.$CentrexServiceID);

                $AccountServiceCount = AccountService::where(['AccountID'=>$AccountID,'ServiceID'=>$CentrexServiceID])->count();

                log::info('hosted_centrex Service Count '.$AccountServiceCount);

                if($AccountServiceCount == 0){
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

                }

                if(isset($Result['data_user']['ext_data'])){
                    $ext_data = $Result['data_user']['ext_data'];
                }else{
                    $ext_data = array();
                }
                if(!empty($ext_data) && !empty($ext_data['subscriptionId'])){
                    $SubscriptionID = $ext_data['subscriptionId'];
                    $quantity = $ext_data['quantity'];
                    $SubscriptionData = array();
                    $SubscriptionData['AccountID'] = $AccountID;
                    $SubscriptionData['ServiceID'] = $CentrexServiceID;
                    $SubscriptionData['SubscriptionID'] = $SubscriptionID;
                    $SubscriptionData['Qty'] = $quantity;
                    $SubscriptionData['StartDate'] = $NextBillingDate;
                    $SubscriptionData['CreatedBy'] = $UserName;
                    log::info('Subscription ID '.$ext_data['subscriptionId']);
                    log::info('Quantity '.$quantity);
                    $this->insertAccountSubscription($SubscriptionData);
                }
            }

            log::info('Create hosted_centrex End');
            /** Create hosted_centrex End */

            /** Create DID Start */

            log::info('Create DID Start');

            if(isset($Result['data_user']['did_data'])){
                $did_datas = $Result['data_user']['did_data'];
            }else{
                $did_datas = array();
            }
            if(!empty($did_datas) && count($did_datas)>0){
                foreach($did_datas as $did_data) {
                    if(!empty($did_data['serviceId'])) {
                        $Count = AccountService::where(['AccountID' => $AccountID, 'ServiceID' => $did_data['serviceId']])->count();
                        if ($Count == 0) {
                            $dataAccountService = array();
                            $dataAccountService['AccountID'] = $AccountID;
                            $dataAccountService['ServiceID'] = $did_data['serviceId'];
                            $dataAccountService['CompanyID'] = $CompanyID;
                            Log::info('New Service ID - DID ' . $did_data['serviceId']);
                            AccountService::insert($dataAccountService);
                        }
                        if (!empty($did_data['subscriptionId'])) {
                            $SubscriptionID = $did_data['subscriptionId'];
                            $quantity = $did_data['quantity'];
                            $SubscriptionData = array();
                            $SubscriptionData['AccountID'] = $AccountID;
                            $SubscriptionData['ServiceID'] = $did_data['serviceId'];
                            $SubscriptionData['SubscriptionID'] = $SubscriptionID;
                            $SubscriptionData['Qty'] = $quantity;
                            $SubscriptionData['StartDate'] = $NextBillingDate;
                            $SubscriptionData['CreatedBy'] = $UserName;
                            log::info('DID Subscription ID ' . $did_data['subscriptionId']);
                            log::info('DID Quantity ' . $quantity);
                            $this->insertAccountSubscription($SubscriptionData);
                        }
                    }
                }
            }

            log::info('Create DID End');

            /** Create DID End */

            /** Create SipTrunk */

            log::info('Create SipTrunk Start');

            if(isset($Result['data_widget']['siptrunk'][0])){
                $SipTrunk = $Result['data_widget']['siptrunk'][0];
            }else{
                $SipTrunk = array();
            }
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

            if(isset($Result['data_user']['siptrunk_data'])){
                $siptrunk_data = $Result['data_user']['siptrunk_data'];
            }else{
                $siptrunk_data = array();
            }
            if(!empty($siptrunk_data) && !empty($siptrunk_data['subscriptionId'])){
                if($SipTrunkServiceID>0){
                    $SubscriptionID = $siptrunk_data['subscriptionId'];
                    $quantity = $siptrunk_data['quantity'];
                    $SubscriptionData = array();
                    $SubscriptionData['AccountID'] = $AccountID;
                    $SubscriptionData['ServiceID'] = $SipTrunkServiceID;
                    $SubscriptionData['SubscriptionID'] = $SubscriptionID;
                    $SubscriptionData['Qty'] = $quantity;
                    $SubscriptionData['StartDate'] = $NextBillingDate;
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

            /*
            $topup = empty($Result['data_user']['topup_data']['amount']) ? 0 : $Result['data_user']['topup_data']['amount'];
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
                $paymentdata['Notes'] = 'TopUp';
                $paymentdata['Amount'] = floatval($topup);
                $paymentdata['Status'] = 'Approved';
                $paymentdata['CreatedBy'] = $UserName.'(API)';
                $paymentdata['ModifyBy'] = $UserName;
                $paymentdata['created_at'] = date('Y-m-d H:i:s');
                $paymentdata['updated_at'] = date('Y-m-d H:i:s');
                Payment::insert($paymentdata);
            }

            */
            log::info('Create TopUp End');

            /** End Topup */

            $InvoiceID=0;
            $FullInvoiceNumber='';
            $RegistarionApiLogUpdate = array();
            $RegistarionApiLogUpdate['InvoiceStatus'] = 'failed';
            $RegistarionApiLogUpdate['InvoiceID'] = 0;

            if(!empty($Result['data_user']['summary'])) {
                $summarydata = $Result['data_user']['summary'];
                $Result = $this->createAPIInvoice($CompanyID,$AccountID,$UserName,$summarydata);
                if(isset($Result['status']) && $Result['status']=='success'){
                    $InvoiceID = $Result['LastID'];
                    $Invoice=Invoice::find($InvoiceID);
                    $FullInvoiceNumber=$Invoice->FullInvoiceNumber;
                    $Invoice->update(array('InvoiceStatus' => Invoice::PAID));

                    $RegistarionApiLogUpdate['InvoiceStatus'] = 'success';
                    $RegistarionApiLogUpdate['InvoiceID'] = $InvoiceID;
                    Log::info($InvoiceID.' Invoice was generated successfully');
                }
            }
            $RegistarionApiLogID = Session::get('RegistarionApiLogID');
            if(!empty($RegistarionApiLogID)){
                DB::table('tblRegistarionApiLog')->where('RegistarionApiLogID', $RegistarionApiLogID)->update($RegistarionApiLogUpdate);
            }

            /** Payment Add Start */
            $paymentdata = array();
            $paymentdata['CompanyID'] = $CompanyID;
            $paymentdata['AccountID'] = $AccountID;
            $paymentdata['InvoiceNo'] = $FullInvoiceNumber;
            $paymentdata['InvoiceID'] = $InvoiceID;
            $paymentdata['PaymentDate'] = date('Y-m-d H:i:s');
            $paymentdata['PaymentMethod'] = $PaymentResponse['PaymentMethod'];
            $paymentdata['CurrencyID'] = $account->CurrencyId;
            $paymentdata['PaymentType'] = 'Payment In';
            $paymentdata['Notes'] = $PaymentResponse['transaction_notes'];
            /*
            if($topup>0){
                $paymentdata['Amount'] = floatval($PaymentResponse['Amount'] - $topup);
            }else{
                $paymentdata['Amount'] = floatval($PaymentResponse['Amount']);
            }*/
            $paymentdata['Amount'] = floatval($PaymentResponse['Amount']);
            $paymentdata['Status'] = 'Approved';
            $paymentdata['CreatedBy'] = $UserName.'(API)';
            $paymentdata['ModifyBy'] = $UserName;
            $paymentdata['created_at'] = date('Y-m-d H:i:s');
            $paymentdata['updated_at'] = date('Y-m-d H:i:s');
            Payment::insert($paymentdata);

            /** Payment Add End */

            DB::commit();
            DB::connection('sqlsrv2')->commit();

            log::info(' Payment End');

            log::info('Invoice Generation End');

            /** Invoice Generation End */


            $Response = array();
            $Response['AccountID'] = $AccountID;
            $Response['AccountNumber'] = $account->Number;
            $Response['status'] = 'success';
            $Response['message'] = 'Account Updated Successfully';
            $Response['PaymentStatus'] = 'success';
            $Response['PaymentMessage'] = 'Payment Create Successfully';
            $Response['NeonStatus'] = 'success';
            $Response['NeonMessage'] = 'Account Updated Successfully';
            $ApiRequestUrl = Session::get('API_BACK_URL');
            $Response['ApiRequestUrl'] = $ApiRequestUrl;
            //$response['ApiRequestData'] = json_encode($ApiData);
            return $Response;

        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();
            DB::connection('sqlsrv2')->rollback();
            $Response = array();
            $Response['status'] = 'failed';
            $Response['message'] = 'something gone wrong please contact your system administrator';
            $Response['PaymentStatus'] = 'success';
            $Response['PaymentMessage'] = 'Payment Create Successfully';
            $Response['NeonStatus'] = 'failed';
            $Response['NeonMessage'] = 'something gone wrong please contact your system administrator';
            $ApiRequestUrl = Session::get('API_BACK_URL');
            $Response['ApiRequestUrl'] = $ApiRequestUrl;
            $Response['AccountID']=$AccountID;
            $Response['AccountNumber']=$account->Number;
            //$response['ApiRequestData'] = json_encode($ApiData);
            return $Response;

            //return Response::json(["status"=>"failed", "data"=>"","PaymentTransaction"=>$PaymentResponse['transaction_notes'],"error"=>'something gone wrong please contact your system administrator']);
        }
    }

    public function createAPIInvoice($CompanyID,$AccountID,$CreatedBy,$data){
        $created_at=date('Y-m-d H:i:s');
        $Account = Account::where(["AccountID"=>$AccountID])->first();

        try {
            DB::connection('sqlsrv2')->beginTransaction();

            $InvoiceData = array();
            $InvoiceTemplateID = BillingClass::getInvoiceTemplateID($data['BillingClassID']);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            $message = $InvoiceTemplate->InvoiceTo;
            $replace_array = Invoice::create_accountdetails($Account);
            $text = Invoice::getInvoiceToByAccount($message, $replace_array);
            $InvoiceToAddress = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
            $Terms = $InvoiceTemplate->Terms;
            $FooterTerm = $InvoiceTemplate->FooterTerm;

            $LastInvoiceNumber = InvoiceTemplate::getNextInvoiceNumber($InvoiceTemplateID);
            $FullInvoiceNumber = $InvoiceTemplate->InvoiceNumberPrefix . $LastInvoiceNumber;
            $InvoiceData["InvoiceNumber"] = $LastInvoiceNumber;
            $InvoiceData["CompanyID"] = $CompanyID;
            $InvoiceData["AccountID"] = intval($AccountID);
            $InvoiceData["Address"] = $InvoiceToAddress;        //change
            $InvoiceData["IssueDate"] = date('Y-m-d');  //today
            $InvoiceData["PONumber"] = ''; //blank
            $InvoiceData["SubTotal"] = str_replace(",", "", $data["SubTotal"]);
            //$InvoiceData["TotalDiscount"] = str_replace(",","",$data["TotalDiscount"]);
            $InvoiceData["TotalDiscount"] = 0;
            //$InvoiceData["TotalTax"] = str_replace(",","",$data["TotalTax"]);
            $InvoiceData["TotalTax"] = 0;
            $InvoiceData["GrandTotal"] = floatval(str_replace(",", "", $data["GrandTotal"]));
            $InvoiceData["CurrencyID"] = $Account->CurrencyId;
            $InvoiceData["InvoiceType"] = Invoice::INVOICE_OUT;
            $InvoiceData["InvoiceStatus"] = Invoice::AWAITING;
            $InvoiceData["ItemInvoice"] = Invoice::ITEM_INVOICE;
            $InvoiceData["Note"] = 'API'; //static api generation
            $InvoiceData["Terms"] = $Terms;  //change
            $InvoiceData["FooterTerm"] = $FooterTerm;
            $InvoiceData["CreatedBy"] = $CreatedBy; 
            //$InvoiceData['InvoiceTotal'] = str_replace(",","",$data["GrandTotal"]);
            $InvoiceData['InvoiceTotal'] = 0;
            $InvoiceData['BillingClassID'] = $data["BillingClassID"];
            $InvoiceData["FullInvoiceNumber"] = $FullInvoiceNumber;

            //log::info(print_r($InvoiceData, true));
            $Invoice = Invoice::create($InvoiceData);
            if(empty($Invoice)){
                $reseponse = array("status" => "failed", "message" => "Problem Creating Invoice. ");
                return $reseponse;
            }
            //Store Last Invoice Number.
            InvoiceTemplate::find($InvoiceTemplateID)->update(array("LastInvoiceNumber" => $LastInvoiceNumber));
            $InvoiceID = $Invoice->InvoiceID;
            log::info('InvoiceID ' . $InvoiceID);

            if (!empty($data['SubscriptionData']) && !empty($InvoiceID)) {
                $SubscriptionDatas = $data['SubscriptionData'];

                /** Subscription insert start */
                foreach ($SubscriptionDatas as $SubscriptionData) {
                    $InvoiceDetailData = array();
                    $InvoiceDetailData['InvoiceID'] = $InvoiceID;
                    $InvoiceDetailData['ProductID'] = $SubscriptionData['ProductID'];
                    //$Description = BillingSubscription::where("SubscriptionID", $SubscriptionData['ProductID'])->pluck("Description");
                    $InvoiceDetailData['Description'] = $SubscriptionData['Description'];
                    $InvoiceDetailData['Price'] = $SubscriptionData['Price'];
                    $InvoiceDetailData['Qty'] = $SubscriptionData['Qty'];
                    $InvoiceDetailData['TaxAmount'] = 0;
                    $InvoiceDetailData['LineTotal'] = $SubscriptionData['LineTotal'];
                    $InvoiceDetailData['StartDate'] = '';
                    $InvoiceDetailData['EndDate'] = '';
                    $InvoiceDetailData['Discount'] = 0;
                    $InvoiceDetailData['TaxRateID'] = 0;
                    $InvoiceDetailData['TaxRateID2'] = 0;
                    $InvoiceDetailData['CreatedBy'] = $CreatedBy;
                    $InvoiceDetailData['ModifiedBy'] = $CreatedBy;
                    $InvoiceDetailData['created_at'] = $created_at;
                    $InvoiceDetailData['updated_at'] = $created_at;
                    $InvoiceDetailData['ProductType'] = Product::SUBSCRIPTION;
                    $InvoiceDetailData['ServiceID'] = 0;
                    $InvoiceDetailData['AccountSubscriptionID'] = 0;
                    $InvoiceDetails = InvoiceDetail::create($InvoiceDetailData);

                    if (isset($InvoiceDetails) && !empty($SubscriptionData['TaxRateData'])) {
                        foreach ($SubscriptionData['TaxRateData'] as $TaxRateData) {
                            $InvoiceTaxRates = array();
                            $InvoiceTaxRates['InvoiceID'] = $InvoiceID;
                            $InvoiceTaxRates['InvoiceDetailID'] = $InvoiceDetails->InvoiceDetailID;
                            $InvoiceTaxRates['TaxRateID'] = $TaxRateData['TaxRateID'];
                            $InvoiceTaxRates['TaxAmount'] = $TaxRateData['TaxAmount'];
                            $Title = TaxRate::where("TaxRateId", $TaxRateData['TaxRateID'])->pluck("Title");
                            $InvoiceTaxRates['Title'] = $Title;
                            $InvoiceTaxRates['InvoiceTaxType'] = 0;
                            InvoiceTaxRate::create($InvoiceTaxRates);
                        }
                    }
                }
            }
            /** Subscription insert end */

            /** Product insert start */
            if (!empty($data['ProductData']) && !empty($InvoiceID)) {
                $ProductDatas = $data['ProductData'];

                foreach ($ProductDatas as $ProductData) {
                    $InvoiceDetailData = array();
                    $InvoiceDetailData['InvoiceID'] = $InvoiceID;
                    $InvoiceDetailData['ProductID'] = $ProductData['ProductID'];
                    //$Description = BillingSubscription::where("SubscriptionID", $ProductData['ProductID'])->pluck("Description");
                    $InvoiceDetailData['Description'] = $ProductData['Description'];
                    $InvoiceDetailData['Price'] = $ProductData['Price'];
                    $InvoiceDetailData['Qty'] = $ProductData['Qty'];
                    $InvoiceDetailData['TaxAmount'] = 0;
                    $InvoiceDetailData['LineTotal'] = $ProductData['LineTotal'];
                    $InvoiceDetailData['StartDate'] = '';
                    $InvoiceDetailData['EndDate'] = '';
                    $InvoiceDetailData['Discount'] = 0;
                    $InvoiceDetailData['TaxRateID'] = 0;
                    $InvoiceDetailData['TaxRateID2'] = 0;
                    $InvoiceDetailData['CreatedBy'] = $CreatedBy;
                    $InvoiceDetailData['ModifiedBy'] = $CreatedBy;
                    $InvoiceDetailData['created_at'] = $created_at;
                    $InvoiceDetailData['updated_at'] = $created_at;
                    $InvoiceDetailData['ProductType'] = Product::ITEM;
                    $InvoiceDetailData['ServiceID'] = 0;
                    $InvoiceDetailData['AccountSubscriptionID'] = 0;
                    $InvoiceDetails = InvoiceDetail::create($InvoiceDetailData);

                    if (isset($InvoiceDetails) && !empty($ProductData['TaxRateData'])) {
                        foreach ($ProductData['TaxRateData'] as $TaxRateData) {
                            $InvoiceTaxRates = array();
                            $InvoiceTaxRates['InvoiceID'] = $InvoiceID;
                            $InvoiceTaxRates['InvoiceDetailID'] = $InvoiceDetails->InvoiceDetailID;
                            $InvoiceTaxRates['TaxRateID'] = $TaxRateData['TaxRateID'];
                            $InvoiceTaxRates['TaxAmount'] = $TaxRateData['TaxAmount'];
                            $Title = TaxRate::where("TaxRateId", $TaxRateData['TaxRateID'])->pluck("Title");
                            $InvoiceTaxRates['Title'] = $Title;
                            $InvoiceTaxRates['InvoiceTaxType'] = 0;
                            InvoiceTaxRate::create($InvoiceTaxRates);
                        }
                    }
                }
            }
            /** Product insert end */

            /** TopUp as product insert start */

            if (!empty($data['TopUpAmount']) && !empty($InvoiceID)) {
                $InvoiceDetailData = array();
                $ProductID = Product::where(['CompanyId' => $CompanyID, 'Code' => 'topup'])->pluck('ProductID');
                if(empty($ProductID)){
                    $ProductData=array();
                    $ProductData['CompanyID']=$CompanyID;
                    $ProductData['Name']='TopUp';
                    $ProductData['Amount']='0.00';
                    $ProductData['Description']='TopUp';
                    $ProductData['Code']='topup';
                    $product = Product::create($ProductData);
                    $ProductID = $product->ProductID;
                }
                /** if blank need to add first */
                $InvoiceDetailData['InvoiceID'] = $InvoiceID;
                $InvoiceDetailData['ProductID'] = $ProductID;
                $InvoiceDetailData['Description'] = 'TopUp';
                $InvoiceDetailData['Price'] = $data['TopUpAmount'];
                $InvoiceDetailData['Qty'] = 1;
                $InvoiceDetailData['TaxAmount'] = 0;
                $InvoiceDetailData['LineTotal'] = $data['TopUpAmount'];
                $InvoiceDetailData['StartDate'] = '';
                $InvoiceDetailData['EndDate'] = '';
                $InvoiceDetailData['Discount'] = 0;
                $InvoiceDetailData['TaxRateID'] = 0;
                $InvoiceDetailData['TaxRateID2'] = 0;
                $InvoiceDetailData['CreatedBy'] = $CreatedBy;
                $InvoiceDetailData['ModifiedBy'] = $CreatedBy;
                $InvoiceDetailData['created_at'] = $created_at;
                $InvoiceDetailData['updated_at'] = $created_at;
                $InvoiceDetailData['ProductType'] = Product::ITEM;
                $InvoiceDetailData['ServiceID'] = 0;
                $InvoiceDetailData['AccountSubscriptionID'] = 0;
                InvoiceDetail::create($InvoiceDetailData);
            }

            /** TopUp as product insert end */

            /** All Over Tax insert Start */

            if (!empty($InvoiceID) && $data['AllOverTaxData']) {
                foreach ($data['AllOverTaxData'] as $TaxRateData) {
                    $InvoiceTaxRates = array();
                    $InvoiceTaxRates['InvoiceID'] = $InvoiceID;
                    $InvoiceTaxRates['InvoiceDetailID'] = 0;
                    $InvoiceTaxRates['TaxRateID'] = $TaxRateData['TaxRateID'];
                    $InvoiceTaxRates['TaxAmount'] = $TaxRateData['TaxAmount'];
                    $Title = TaxRate::where("TaxRateId", $TaxRateData['TaxRateID'])->pluck("Title");
                    $InvoiceTaxRates['Title'] = $Title;
                    $InvoiceTaxRates['InvoiceTaxType'] = 1;
                    InvoiceTaxRate::create($InvoiceTaxRates);
                }
            }

            //StockHistory
            $StockHistory=array();
            $temparray=array();
            $InvoiceDetailStockData=InvoiceDetail::where(['InvoiceID'=>$InvoiceID,'ProductType'=>1])->get();
            if(!empty($InvoiceDetailStockData) && count($InvoiceDetailStockData)>0) {
                foreach ($InvoiceDetailStockData as $CheckInvoiceHistory) {
                        $ProductID = intval($CheckInvoiceHistory->ProductID);
                        $Qty = intval($CheckInvoiceHistory['Qty']);
                        $temparray['CompanyID'] = $CompanyID;
                        $temparray['ProductID'] = $ProductID;
                        $temparray['InvoiceID'] = $InvoiceID;
                        $temparray['Qty'] = $Qty;
                        $temparray['Reason'] = '';
                        $temparray['InvoiceNumber'] = $InvoiceData["FullInvoiceNumber"];
                        $temparray['created_by'] = $CreatedBy;
                        array_push($StockHistory, $temparray);
                }
                $historyData=StockHistoryCalculations($StockHistory);
            }

            /** All Over Tax insert End */

            $invoiceloddata = array();
            $invoiceloddata['InvoiceID'] = $InvoiceID;
            $invoiceloddata['Note'] = 'Created By ' . $CreatedBy;
            $invoiceloddata['created_at'] = $created_at;
            $invoiceloddata['InvoiceLogStatus'] = InVoiceLog::CREATED;
            InVoiceLog::insert($invoiceloddata);

            $pdf_path = Invoice::generate_pdf($Invoice->InvoiceID);
            if (empty($pdf_path)) {
                $error['message'] = 'Failed to generate Invoice PDF File';
                $error['status'] = 'failure';
                return $error;
            } else {
                $Invoice->update(["PDF" => $pdf_path]);
            }

            DB::connection('sqlsrv2')->commit();
            $SuccessMsg="Invoice Successfully Created.";
            $message='';
            $reseponse = array("status" => "success","warning"=>$message, "message" => $SuccessMsg,'LastID'=>$Invoice->InvoiceID);
            return $reseponse;

        }catch (Exception $e){
            Log::info($e);
            DB::connection('sqlsrv2')->rollback();
            $reseponse = array("status" => "failed", "message" => "Problem Creating Invoice. \n" . $e->getMessage());
            return $reseponse;
        }

    }
}
