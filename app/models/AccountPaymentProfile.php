<?php
class AccountPaymentProfile extends \Eloquent
{
    protected $fillable = [];
    protected $guarded = array('AccountPaymentProfileID');
    protected $table = 'tblAccountPaymentProfile';
    protected $primaryKey = "AccountPaymentProfileID";
    public static $StatusActive = 1;
    public static $StatusDeactive = 0;

    public static function getActiveProfile($AccountID,$PaymentGatewayID)
    {
        $AccountPaymentProfile = AccountPaymentProfile::where(array('AccountID' => $AccountID,'PaymentGatewayID'=>$PaymentGatewayID,'Status' => 1, 'isDefault' => 1))
            ->Where(function($query)
            {
                $query->where("Blocked",'<>',1)
                    ->orwhereNull("Blocked");
            })
            ->first();
        return $AccountPaymentProfile;
    }

    public static function setProfileBlock($AccountPaymentProfileID)
    {
        AccountPaymentProfile::where(array('AccountPaymentProfileID' => $AccountPaymentProfileID))->update(array('Blocked' => 1));
    }

    public static function getProfile($AccountPaymentProfileID)
    {
        $AccountPaymentProfile = AccountPaymentProfile::where(array('AccountPaymentProfileID' => $AccountPaymentProfileID))->first();
        return $AccountPaymentProfile;
    }

    public static function createProfile($CompanyID, $CustomerID,$PaymentGatewayID)
    {
        $data = Input::all();

        if(empty($PaymentGatewayID)){
            return Response::json(array("status" => "failed", "message" => "Please Select Payment Gateway"));
        }
        $rules = array(
            'CardNumber' => 'required|digits_between:14,19',
            'ExpirationMonth' => 'required',
            'ExpirationYear' => 'required',
            'NameOnCard' => 'required',
            'CVVNumber' => 'required',
            //'Title' => 'required|unique:tblAutorizeCardDetail,NULL,CreditCardID,CompanyID,'.$CompanyID
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if (date("Y") == $data['ExpirationYear'] && date("m") > $data['ExpirationMonth']) {
            return Response::json(array("status" => "failed", "message" => "Month must be after " . date("F")));
        }
        $card = CreditCard::validCreditCard($data['CardNumber']);
        if ($card['valid'] == 0) {
            return Response::json(array("status" => "failed", "message" => "Please enter valid card number"));
        }

        $ProfileResponse = array();
        if($PaymentGatewayID==PaymentGateway::AuthorizeNet){
            $ProfileResponse = AccountPaymentProfile::createAuthorizeProfile($CompanyID, $CustomerID,$PaymentGatewayID,$data);
        }
        if($PaymentGatewayID==PaymentGateway::Stripe){
            $ProfileResponse = AccountPaymentProfile::createStripeProfile($CompanyID, $CustomerID,$PaymentGatewayID,$data);
        }

        return $ProfileResponse;

    }

    public static function createBankProfile($CompanyID, $CustomerID,$PaymentGatewayID)
    {
        $data = Input::all();
        //$PaymentGatewayID =$data['PaymentGatewayID'];
        if(empty($PaymentGatewayID)){
            return Response::json(array("status" => "failed", "message" => "Please Select Payment Gateway"));
        }
        $rules = array(
            'AccountNumber' => 'required|digits_between:6,19',
            'RoutingNumber' => 'required',
            'AccountHolderType' => 'required',
            'AccountHolderName' => 'required',
            //'Title' => 'required|unique:tblAutorizeCardDetail,NULL,CreditCardID,CompanyID,'.$CompanyID
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $account = Account::find($CustomerID);
        $CurrencyCode = Currency::getCurrency($account->CurrencyId);
        if(empty($CurrencyCode)){
            return json_encode(array("status" => "failed", "message" => "No account currency available"));
        }
        $data['currency'] = strtolower($CurrencyCode);
        $Country = $account->Country;
        if(!empty($Country)){
            $CountryCode = Country::where(['Country'=>$Country])->pluck('ISO2');
        }else{
            $CountryCode = '';
        }
        if(empty($CountryCode)){
            return json_encode(array("status" => "failed", "message" => "No account country available"));
        }

        $data['currency'] = strtolower($CurrencyCode);
        $data['country'] = strtolower($CountryCode);

        $ProfileResponse = array();
        if($PaymentGatewayID==PaymentGateway::StripeACH){
            $ProfileResponse = AccountPaymentProfile::createStripeACHProfile($CompanyID, $CustomerID,$PaymentGatewayID,$data);
        }

        return $ProfileResponse;

    }

    public static function paynow($CompanyID, $AccountID, $Invoiceids, $CreatedBy, $AccountPaymentProfileID)
    {
        $account = Account::find($AccountID);
        $data = [];
        $data['CompanyName'] 		= 	Company::getName($CompanyID);
        $data['ComapnyID']          =   $CompanyID;
        $data['EmailTemplate'] 		= 	EmailTemplate::getSystemEmailTemplate($CompanyID, EmailTemplate::InvoicePaidNotificationTemplate, $account->LanguageID);
        $Invoices = explode(',', $Invoiceids);
        $fullnumber = '';
        if(count($Invoices)>0){
            foreach($Invoices as $inv){
                $AllInvoice = Invoice::find($inv);
                $fullnumber.= $AllInvoice->FullInvoiceNumber.',';
            }
        }
        if($fullnumber!=''){
            $fullnumber = rtrim($fullnumber,',');
        }


        $AccountBilling = AccountBilling::getBilling($AccountID);
        /* removed account outstandig condition */
        //$outstanginamounttotal = Account::getOutstandingAmount($CompanyID,$account->AccountID,get_round_decimal_places($account->AccountID));
        $outstanginamount = Account::getOutstandingInvoiceAmount($CompanyID,$account->AccountID,$Invoiceids, get_round_decimal_places($account->AccountID));
        if ($outstanginamount > 0 ) {
            $CustomerProfile = AccountPaymentProfile::getProfile($AccountPaymentProfileID);
            if (!empty($CustomerProfile)) {
                $PaymentGateway = PaymentGateway::getName($CustomerProfile->PaymentGatewayID);
                if($PaymentGateway=='Stripe'){
                    $CurrencyCode = Currency::getCurrency($account->CurrencyId);
                    if(empty($CurrencyCode)){
                        return json_encode(array("status" => "failed", "message" => "No account currency available"));
                    }
                    $stripestatus = new StripeBilling($CompanyID);
                    if(empty($stripestatus->status)){
                        return json_encode(array("status" => "failed", "message" => "Stripe Payment not setup correctly"));
                    }
                }

                if($PaymentGateway=='StripeACH'){
                    $CurrencyCode = Currency::getCurrency($account->CurrencyId);
                    if(empty($CurrencyCode)){
                        return json_encode(array("status" => "failed", "message" => "No account currency available"));
                    }
                    $stripeAchstatus = new StripeACH();
                    if(empty($stripeAchstatus->status)){
                        return json_encode(array("status" => "failed", "message" => "Stripe ACH Payment not setup correctly"));
                    }
                    $StripeObj = json_decode($CustomerProfile->Options);
                    if(empty($StripeObj->VerifyStatus) || $StripeObj->VerifyStatus!=='verified'){
                        return json_encode(array("status" => "failed", "message" => "Bank Account not verified."));
                    }
                }

                $AccountPaymentProfileID = $CustomerProfile->AccountPaymentProfileID;
                $options = json_decode($CustomerProfile->Options);
                $options->InvoiceNumber = $fullnumber;
                $transactionResponse = PaymentGateway::addTransaction($PaymentGateway, $outstanginamount, $options, $account, $AccountPaymentProfileID,$CreatedBy);
                /**  Get All UnPaid  Invoice */
                $unPaidInvoices = DB::connection('sqlsrv2')->select('call prc_getPaymentPendingInvoice (' . $CompanyID . ',' . $account->AccountID.',0,0)');
                if (isset($transactionResponse['response_code']) && $transactionResponse['response_code'] == 1) {
                    foreach ($unPaidInvoices as $Invoiceid) {
                        /**  Update Invoice as Paid */
                        if (in_array($Invoiceid->InvoiceID, explode(',', $Invoiceids))) {
                            $Invoice = Invoice::find($Invoiceid->InvoiceID);
                            $paymentdata = array();
                            $paymentdata['CompanyID'] = $Invoice->CompanyID;
                            $paymentdata['AccountID'] = $Invoice->AccountID;
                            $paymentdata['InvoiceNo'] = $Invoice->FullInvoiceNumber;
                            $paymentdata['InvoiceID'] = (int)$Invoice->InvoiceID;
                            $paymentdata['PaymentDate'] = date('Y-m-d');
                            $paymentdata['PaymentMethod'] = $transactionResponse['transaction_payment_method'];
                            $paymentdata['CurrencyID'] = $account->CurrencyId;
                            $paymentdata['PaymentType'] = 'Payment In';
                            $paymentdata['Notes'] = $transactionResponse['transaction_notes'];
                            $paymentdata['Amount'] = floatval($Invoiceid->RemaingAmount);
                            $paymentdata['Status'] = 'Approved';
                            $paymentdata['created_at'] = date('Y-m-d H:i:s');
                            $paymentdata['updated_at'] = date('Y-m-d H:i:s');
                            $paymentdata['CreatedBy'] = $CreatedBy;
                            $paymentdata['ModifyBy'] = $CreatedBy;
                            Payment::insert($paymentdata);
                            $transactiondata = array();
                            $transactiondata['CompanyID'] = $account->CompanyId;
                            $transactiondata['AccountID'] = $account->AccountID;
                            $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
                            $transactiondata['Transaction'] = $transactionResponse['transaction_id'];
                            $transactiondata['Notes'] = $transactionResponse['transaction_notes'];
                            $transactiondata['Amount'] = floatval($Invoiceid->RemaingAmount);
                            $transactiondata['Status'] = TransactionLog::SUCCESS;
                            $transactiondata['created_at'] = date('Y-m-d H:i:s');
                            $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                            $transactiondata['CreatedBy'] = $CreatedBy;
                            $transactiondata['ModifyBy'] = $CreatedBy;
                            TransactionLog::insert($transactiondata);
                            $Invoice->update(array('InvoiceStatus' => Invoice::PAID));
                            $data['Invoice'] = $Invoice;
                            $data['InvoiceID'] = $Invoice->InvoiceID;
                            $data['AccountID'] = $Invoice->AccountID;
                            Notification::sendEmailNotification(Notification::InvoicePaidByCustomer,$data);
                        }
                    }
                    return json_encode(array("status" => "success", "message" => "All Invoice Paid Successfully"));
                } else {
                    foreach ($unPaidInvoices as $Invoiceid) {
                        if (in_array($Invoiceid->InvoiceID, explode(',', $Invoiceids))) {
                            $Invoice = Invoice::find($Invoiceid->InvoiceID);
                            $transactiondata = array();
                            $transactiondata['CompanyID'] = $account->CompanyId;
                            $transactiondata['AccountID'] = $account->AccountID;
                            $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
                            $transactiondata['Transaction'] = $transactionResponse['transaction_id'];
                            $transactiondata['Notes'] = $transactionResponse['transaction_notes'];
                            $transactiondata['Amount'] = floatval($Invoiceid->RemaingAmount);
                            $transactiondata['Status'] = TransactionLog::FAILED;
                            $transactiondata['created_at'] = date('Y-m-d H:i:s');
                            $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                            $transactiondata['CreatedBy'] = $CreatedBy;
                            $transactiondata['ModifyBy'] = $CreatedBy;
                            TransactionLog::insert($transactiondata);
                        }
                    }
                    return json_encode(array("status" => "failed", "message" => "Transaction Failed :" . $transactionResponse['failed_reason']));
                }
            } else {
                return json_encode(array("status" => "failed", "message" => "Account Profile not set"));
            }
        } else {
            return json_encode(array("status" => "failed", "message" => "Total outstanding is less or equal to zero"));
        }
    }

    public static function bulkAuthorizePayment($CompanyID, $AccountID, $Invoiceids, $CreatedBy, $AccountPaymentProfileID){

    }

    public static function bulkStripePayment($CompanyID, $AccountID, $Invoiceids, $CreatedBy, $AccountPaymentProfileID){

    }

    // not using
    public static function createAuthorizeProfile($CompanyID, $CustomerID,$PaymentGatewayID,$data){

        $ProfileID = "";
        $ShippingProfileID = "";
        $first = 0;

        $isAuthorizedNet  = 	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$AuthorizeSlug);
        if(!$isAuthorizedNet){
            return Response::json(array("status" => "failed", "message" => "Payment Method Not Integrated"));
        }

        $AuthorizeNet = new AuthorizeNet();

        $PaymentProfile = AccountPaymentProfile::where(['AccountID' => $CustomerID])
            ->where(['CompanyID' => $CompanyID])
            ->where(['PaymentGatewayID' => $PaymentGatewayID])
            ->first();
        if (!empty($PaymentProfile)) {
            $options = json_decode($PaymentProfile->Options);
            $ProfileID = $options->ProfileID;
            $ShippingProfileID = $options->ShippingProfileID;
        }
        $account = Account::where(array('AccountID' => $CustomerID))->first();

        $response = $AuthorizeNet->getCustomerProfile($ProfileID);
        if(empty($ProfileID)){
            $first = 1;
        }
        if ($response == false || empty($ProfileID)) {
            $profile = array('CustomerId' => $CustomerID, 'email' => $account->BillingEmail, 'description' => $account->AccountName);
            $result = $AuthorizeNet->CreateProfile($profile);
            if ($result["status"] == "success") {
                $ProfileID = $result["ID"];
                //$ProfileID = json_decode(json_encode($ProfileID), true)[0];
                $shipping = array('firstName' => $account->FirstName,
                    'lastName' => $account->LastName,
                    'address' => $account->Address1,
                    'city' => $account->City,
                    'state' => $account->state,
                    'zip' => $account->PostCode,
                    'country' => $account->Country,
                    'phoneNumber' => $account->Mobile);
                $result = $AuthorizeNet->CreatShippingAddress($ProfileID, $shipping);
                $ShippingProfileID = $result["ID"];
            } else {
                return Response::json(array("status" => "failed", "message" => (array)$result["message"]));
            }
        }
        $title = $data['Title'];
        $result = $AuthorizeNet->CreatePaymentProfile($ProfileID, $data);
        if ($result["status"] == "success") {
            $PaymentProfileID = $result["ID"];
            /**  @TODO save this field NameOnCard and CCV */
            $option = array(
                'ProfileID' => $ProfileID,
                'ShippingProfileID' => $ShippingProfileID,
                'PaymentProfileID' => $PaymentProfileID
            );
            $CardDetail = array('Title' => $title,
                'Options' => json_encode($option),
                'Status' => 1,
                'isDefault' => $first,
                'created_by' => Customer::get_accountName(),
                'CompanyID' => $CompanyID,
                'AccountID' => $CustomerID,
                'PaymentGatewayID' => $PaymentGatewayID);
            if (AccountPaymentProfile::create($CardDetail)) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully Created"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Saving Payment Method Profile."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => (array)$result["message"]));
        }

    }

    // not using
    public static function createStripeProfile($CompanyID, $CustomerID,$PaymentGatewayID,$data)
    {
        $stripepayment = new StripeBilling($CompanyID);

        $stripedata = array();

        if (empty($stripepayment->status)) {
            return Response::json(array("status" => "failed", "message" => "Stripe Payment not setup correctly"));
        }

        $account = Account::where(array('AccountID' => $CustomerID))->first();

        $isDefault = 1;

        $count = AccountPaymentProfile::where(['AccountID' => $CustomerID])
            ->where(['CompanyID' => $CompanyID])
            ->where(['PaymentGatewayID' => $PaymentGatewayID])
            ->where(['isDefault' => 1])
            ->count();

        if($count>0){
            $isDefault = 0;
        }

        $email = empty($account->BillingEmail)?'':$account->BillingEmail;
        $accountname = empty($account->AccountName)?'':$account->AccountName;


        $StripeResponse = array();
        $stripedata['number'] = $data['CardNumber'];
        $stripedata['exp_month'] = $data['ExpirationMonth'];
        $stripedata['cvc'] = $data['CVVNumber'];
        $stripedata['exp_year'] = $data['ExpirationYear'];
        $stripedata['name'] = $data['NameOnCard'];
        $stripedata['email'] = $email;
        $stripedata['account'] = $accountname;

        $StripeResponse = $stripepayment->create_customer($stripedata);

        if ($StripeResponse["status"] == "Success") {
            $option = array(
                'CustomerProfileID' => $StripeResponse['CustomerProfileID'],
                'CardID' => $StripeResponse['CardID']
            );
            $CardDetail = array('Title' => $data['Title'],
                'Options' => json_encode($option),
                'Status' => 1,
                'isDefault' => $isDefault,
                'created_by' => Customer::get_accountName(),
                'CompanyID' => $CompanyID,
                'AccountID' => $CustomerID,
                'PaymentGatewayID' => $PaymentGatewayID);
            if (AccountPaymentProfile::create($CardDetail)) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully Created"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Saving Payment Method Profile."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => $StripeResponse['error']));
        }
    }

    // not using
    public static function deleteAuthorizeProfile($CompanyID,$AccountID,$AccountPaymentProfileID){
        //If using Authorize.net
        $isAuthorizedNet  = 	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$AuthorizeSlug);
        if(!$isAuthorizedNet){
            return Response::json(array("status" => "failed", "message" => "Payment Method Not Integrated"));
        }

        $AuthorizeNet = new AuthorizeNet();
        $count = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
        $PaymentProfile = AccountPaymentProfile::find($AccountPaymentProfileID);
        if(!empty($PaymentProfile)){
            $options = json_decode($PaymentProfile->Options);
            $ProfileID = $options->ProfileID;
            $PaymentProfileID = $options->PaymentProfileID;
            $isDefault = $PaymentProfile->isDefault;
        }else{
            return Response::json(array("status" => "failed", "message" => "Record Not Found"));
        }
        if($isDefault==1){
            if($count!=1){
                return Response::json(array("status" => "failed", "message" => "You can not delete default profile. Please set as default an other profile first."));
            }
        }
        $result = $AuthorizeNet->DeletePaymentProfile($ProfileID,$PaymentProfileID);
        if($result["status"]=="success"){
            if ($PaymentProfile->delete()) {
                if($count==1){
                    $result =  $AuthorizeNet->deleteProfile($ProfileID);
                    if($result["status"]=="success"){
                        return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted. Profile deleted too."));
                    }
                }else{
                    return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted"));
                }
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem deleting Payment Method Profile."));
            }
        }elseif($result["code"]=='E00040'){
            if ($PaymentProfile->delete()) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted"));
            }else{
                return Response::json(array("status" => "failed", "message" => "Problem deleting Payment Method Profile."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => (array)$result["message"]));
        }
    }

    // not using
    public static function deleteStripeProfile($CompanyID,$AccountID,$AccountPaymentProfileID){

        $stripepayment = new StripeBilling($CompanyID);

        if (empty($stripepayment->status)) {
            return Response::json(array("status" => "failed", "message" => "Stripe Payment not setup correctly"));
        }

        $count = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
        $PaymentProfile = AccountPaymentProfile::find($AccountPaymentProfileID);
        if(!empty($PaymentProfile)){
            $options = json_decode($PaymentProfile->Options);
            $CustomerProfileID = $options->CustomerProfileID;
            $isDefault = $PaymentProfile->isDefault;
        }else{
            return Response::json(array("status" => "failed", "message" => "Record Not Found"));
        }
        if($isDefault==1){
            if($count!=1){
                return Response::json(array("status" => "failed", "message" => "You can not delete default profile. Please set as default an other profile first."));
            }
        }

        $result = $stripepayment->deleteCustomer($CustomerProfileID);

        if($result["status"]=="Success"){
            if($PaymentProfile->delete()) {
                   return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted. Profile deleted too."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem deleting Payment Method Profile."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => $result['error']));
        }
    }

    // not using
    public static function createStripeACHProfile($CompanyID, $CustomerID,$PaymentGatewayID,$data)
    {
        $stripepayment = new StripeACH();

        $stripedata = array();

        if (empty($stripepayment->status)) {
            return Response::json(array("status" => "failed", "message" => "Stripe ACH Payment not setup correctly"));
        }

        $account = Account::where(array('AccountID' => $CustomerID))->first();

        $isDefault = 1;

        $count = AccountPaymentProfile::where(['AccountID' => $CustomerID])
            ->where(['CompanyID' => $CompanyID])
            ->where(['PaymentGatewayID' => $PaymentGatewayID])
            ->where(['isDefault' => 1])
            ->count();

        if($count>0){
            $isDefault = 0;
        }

        $email = empty($account->BillingEmail)?'':$account->BillingEmail;
        $accountname = empty($account->AccountName)?'':$account->AccountName;


        $StripeResponse = array();
        $stripedata['account_holder_name'] = $data['AccountHolderName'];
        $stripedata['account_number'] = $data['AccountNumber'];
        $stripedata['routing_number'] = $data['RoutingNumber'];
        $stripedata['account_holder_type'] = $data['AccountHolderType'];
        $stripedata['country'] = $data['country'];
        $stripedata['currency'] =  $data['currency'];
        $stripedata['email'] = $email;
        $stripedata['account'] = $accountname;

        $StripeResponse = $stripepayment->create_customer($stripedata);

        if ($StripeResponse["status"] == "Success") {
            $option = array(
                'CustomerProfileID' => $StripeResponse['CustomerProfileID'],
                'BankAccountID' => $StripeResponse['BankAccountID'],
                'VerifyStatus' => '',
            );
            $CardDetail = array('Title' => $data['Title'],
                'Options' => json_encode($option),
                'Status' => 1,
                'isDefault' => $isDefault,
                'created_by' => Customer::get_accountName(),
                'CompanyID' => $CompanyID,
                'AccountID' => $CustomerID,
                'PaymentGatewayID' => $PaymentGatewayID);
            if (AccountPaymentProfile::create($CardDetail)) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully Created"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Saving Payment Method Profile."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => $StripeResponse['error']));
        }
    }

    // not using
    public static function deleteStripeACHProfile($CompanyID,$AccountID,$AccountPaymentProfileID){

        $stripepayment = new StripeACH();

        if (empty($stripepayment->status)) {
            return Response::json(array("status" => "failed", "message" => "Stripe ACH Payment not setup correctly"));
        }

        $count = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
        $PaymentProfile = AccountPaymentProfile::find($AccountPaymentProfileID);
        if(!empty($PaymentProfile)){
            $options = json_decode($PaymentProfile->Options);
            $CustomerProfileID = $options->CustomerProfileID;
            $isDefault = $PaymentProfile->isDefault;
        }else{
            return Response::json(array("status" => "failed", "message" => "Record Not Found"));
        }
        if($isDefault==1){
            if($count!=1){
                return Response::json(array("status" => "failed", "message" => "You can not delete default profile. Please set as default an other profile first."));
            }
        }

        $result = $stripepayment->deleteCustomer($CustomerProfileID);

        if($result["status"]=="Success"){
            if($PaymentProfile->delete()) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted. Profile deleted too."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem deleting Payment Method Profile."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => $result['error']));
        }
    }

    // not using
    public static function createSagePayProfile($CompanyID, $CustomerID,$PaymentGatewayID)
    {
        $data = Input::all();

        if(empty($PaymentGatewayID)){
            return Response::json(array("status" => "failed", "message" => "Please Select Payment Gateway"));
        }
        $rules = array(
            'Title' => 'required',
            'AccountName' => 'required',
            'BankAccountName' => 'required',
            'AccountNumber' => 'required|digits_between:2,11',
            'BranchCode' => 'required|digits:6',
            'AccountHolderType' => 'required',
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $isDefault = 1;

        $count = AccountPaymentProfile::where(['AccountID' => $CustomerID])
            ->where(['CompanyID' => $CompanyID])
            ->where(['PaymentGatewayID' => $PaymentGatewayID])
            ->where(['isDefault' => 1])
            ->count();

        if($count>0){
            $isDefault = 0;
        }

        $varifydata = array(
            'AccountNumber'   => $data['AccountNumber'],
            'BranchCode'        => $data['BranchCode'],
            'AccountType' => $data['AccountHolderType']
        );

        $SagePayDirectDebit = new SagePayDirectDebit($CompanyID);
        $verify_response = $SagePayDirectDebit->verifyBankAccount($varifydata);

        if(!empty($verify_response) && $verify_response['status']=='Success'){

            $option = array(
                'AccountName' => $data['AccountName'],
                'BankAccountName'   => Crypt::encrypt($data['BankAccountName']),
                'AccountNumber'   => Crypt::encrypt($data['AccountNumber']),
                'BranchCode'        => Crypt::encrypt($data['BranchCode']),
                'AccountHolderType' => $data['AccountHolderType'],
                'VerifyStatus'      => 'verified',
            );
            $BankDetail = array('Title' => $data['Title'],
                'Options' => json_encode($option),
                'Status' => 1,
                'isDefault' => $isDefault,
                'created_by' => Customer::get_accountName(),
                'CompanyID' => $CompanyID,
                'AccountID' => $CustomerID,
                'PaymentGatewayID' => $PaymentGatewayID);

            if (AccountPaymentProfile::create($BankDetail)) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully Created"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Saving Payment Method Profile."));
            }

        }elseif(!empty($verify_response) && $verify_response['status']=='fail'){
            return Response::json(array("status" => "failed", "message" => $verify_response['error']));
        }else{
            return Response::json(array("status" => "failed", "message" => "Payment Method Profile Successfully Created"));
        }

    }

    //not using
    public static function deleteSagePayDirectDebitProfile($CompanyID,$AccountID,$AccountPaymentProfileID)
    {
        $count = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
        $PaymentProfile = AccountPaymentProfile::find($AccountPaymentProfileID);
        if(!empty($PaymentProfile)){
            $isDefault = $PaymentProfile->isDefault;
        }else{
            return Response::json(array("status" => "failed", "message" => "Record Not Found"));
        }
        if($isDefault==1){
            if($count!=1){
                return Response::json(array("status" => "failed", "message" => "You can not delete default profile. Please set as default an other profile first."));
            }
        }

        if($PaymentProfile->delete()) {
            return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted. Profile deleted too."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem deleting Payment Method Profile."));
        }

    }
}