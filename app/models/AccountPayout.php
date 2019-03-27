<?php
class AccountPayout extends \Eloquent
{
    protected $fillable = [];
    protected $guarded = array('AccountPayoutID');
    protected $table = 'tblAccountPayout';
    protected $primaryKey = "AccountPayoutID";
    public static $StatusActive = 1;
    public static $StatusDeactive = 0;
    public static  $AccountPayoutBankRules = array(
        'AccountNumber'     => 'required|digits_between:6,19',
        'RoutingNumber'     => 'required',
        'AccountHolderType' => 'required',
        'AccountHolderName' => 'required',
    );

    public static function getActivePayoutAccounts($AccountID,$PaymentGatewayID)
    {
        $AccountPayout = AccountPayout::where(array('AccountID' => $AccountID,'PaymentGatewayID'=>$PaymentGatewayID,'Status' => 1,'isDefault' => 1))
            ->Where(function($query)
            {
                $query->where("Blocked",'<>',1)
                    ->orwhereNull("Blocked");
            })
            ->first();
        return $AccountPayout;
    }

    public static function setPayoutBlock($AccountPayoutID)
    {
        AccountPayout::where(array('AccountPayoutID' => $AccountPayoutID))->update(array('Blocked' => 1));
    }

    public static function getPayoutAccount($AccountPayoutID)
    {
        $AccountPayout = AccountPayout::where(array('AccountPaymentProfileID' => $AccountPayoutID))->first();
        return $AccountPayout;
    }


    public static function createPayoutInvoice($data){

        $InvoiceData["CompanyID"] = $data['CompanyID'];
        $InvoiceData["AccountID"] = $data['AccountID'];
        $CreatedBy = "API";
        $Account = Account::find($data["AccountID"]);
        $BillingClassID = AccountBilling::getBillingClassID($data['AccountID']);
        $InvoiceTemplateID = $BillingClassID != false ? BillingClass::getInvoiceTemplateID($BillingClassID) : false;

        if($InvoiceTemplateID == false)
            return ["status"  => "failed", "message" => "Account don't have billing class."];

        $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);

        $message = $InvoiceTemplate->InvoiceTo;
        $replace_array = Invoice::create_accountdetails($Account);
        $text = Invoice::getInvoiceToByAccount($message, $replace_array);
        $InvoiceToAddress = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);

        $prefix = $InvoiceTemplate->InvoiceNumberPrefix;

        $InvoiceData["InvoiceNumber"] = InvoiceTemplate::getNextInvoiceNumber($InvoiceTemplateID);
        $InvoiceData["FullInvoiceNumber"] = $prefix . $InvoiceData["InvoiceNumber"];
        $InvoiceData["Address"]       = $InvoiceToAddress;
        $InvoiceData["Description"]   = "Out Payment";
        $InvoiceData["IssueDate"]     = date('Y-m-d');
        $InvoiceData["SubTotal"]      = -floatval($data["Amount"]);
        $InvoiceData["TotalDiscount"] = 0;
        $InvoiceData["TotalTax"]      = 0;
        $InvoiceData["ItemInvoice"]   = Invoice::ITEM_INVOICE;
        $InvoiceData["BillingClassID"]= $BillingClassID;
        $InvoiceData["InvoiceStatus"] = Invoice::SEND;
        $InvoiceData["GrandTotal"]    = -floatval($data["Amount"]);
        $InvoiceData['InvoiceTotal']  = -floatval($data["Amount"]);
        $InvoiceData["CurrencyID"]    = $Account->CurrencyId;
        $InvoiceData["InvoiceType"]   = Invoice::INVOICE_OUT;
        $InvoiceData["Note"]          = $CreatedBy;
        $InvoiceData["CreatedBy"]     = $CreatedBy;
        $InvoiceData["Terms"]         = $InvoiceTemplate->Terms;
        $InvoiceData["FooterTerm"]    = $InvoiceTemplate->FooterTerm;

        try{
            DB::connection('sqlsrv2')->beginTransaction();
            $Invoice = Invoice::create($InvoiceData);

            $ProductID = Product::where([
                'CompanyId' => $data['CompanyID'],
                'Code'      => 'outpayment'
            ])->pluck('ProductID');

            if (empty($ProductID)) {
                $ProductData = array();
                $ProductData['CompanyID'] = $data['CompanyID'];
                $ProductData['Name'] = 'OutPayment';
                $ProductData['Amount'] = '0.00';
                $ProductData['Description'] = 'Out Payment';
                $ProductData['Code'] = 'outpayment';
                $product = Product::create($ProductData);
                $ProductID = $product->ProductID;
            }

            $InvoiceID = $Invoice->InvoiceID;

            $InvoiceDetailData = $InvoiceTaxRates = array();
            $InvoiceDetailData['InvoiceID']     = $InvoiceID;
            $InvoiceDetailData['ProductID']     = $ProductID;
            $InvoiceDetailData['Description']   = 'Out Payment';
            $InvoiceDetailData['Price']         = -floatval($data["Amount"]);
            $InvoiceDetailData['Qty']           = 1;
            $InvoiceDetailData['TaxAmount']     = 0;
            $InvoiceDetailData['LineTotal']     = -floatval($data["Amount"]);
            $InvoiceDetailData['StartDate']     = '';
            $InvoiceDetailData['EndDate']       = '';
            $InvoiceDetailData['Discount']      = 0;
            $InvoiceDetailData['TaxRateID']     = 0;
            $InvoiceDetailData['TaxRateID2']    = 0;
            $InvoiceDetailData['TotalMinutes']  = 0;
            $InvoiceDetailData["CreatedBy"]     = $CreatedBy;
            $InvoiceDetailData["ModifiedBy"]    = $CreatedBy;
            $InvoiceDetailData["created_at"]    = date("Y-m-d H:i:s");
            $InvoiceDetailData['ProductType']   = Product::ITEM;
            $InvoiceDetailData['ServiceID']     = 0;
            $InvoiceDetailData['AccountSubscriptionID'] = 0;
            InvoiceDetail::insert($InvoiceDetailData);

            $invoiceloddata = array();
            $invoiceloddata['InvoiceID']        = $InvoiceID;
            $invoiceloddata['Note']             = 'Out Payment. Created By '.$CreatedBy;
            $invoiceloddata['created_at']       = date("Y-m-d H:i:s");
            $invoiceloddata['InvoiceLogStatus'] = InVoiceLog::CREATED;
            InVoiceLog::insert($invoiceloddata);

            $InvoiceTaxRates1=TaxRate::getInvoiceTaxRateByProductDetail($InvoiceID);
            if(!empty($InvoiceTaxRates1)) { //Invoice tax
                InvoiceTaxRate::insert($InvoiceTaxRates1);
            }

            Log::info($InvoiceID);
            $pdf_path = Invoice::generate_pdf($InvoiceID);
            Log::info($pdf_path);
            if (empty($pdf_path)) {
                $error['message'] = 'Failed to generate Invoice PDF File';
                $error['status'] = 'failed';
                return $error;
            } else {
                $Invoice->where('InvoiceID', $InvoiceID)->update(["PDF" => $pdf_path]);
            }

            $ubl_path = Invoice::generate_ubl_invoice($Invoice->InvoiceID);
            Log::info($ubl_path);
            if (empty($ubl_path)) {
                $error['message'] = 'Failed to generate Invoice UBL File.';
                $error['status'] = 'failed';
                return $error;
            } else {
                $Invoice->where('InvoiceID', $InvoiceID)->update(["UblInvoice" => $ubl_path]);
            }

            DB::connection('sqlsrv2')->commit();
            $SuccessMsg="Invoice Successfully Created.";

            return [
                "status"  => "success",
                "message" => $SuccessMsg,
                'LastID'  => $InvoiceID
            ];

        } catch (Exception $e){
            Log::info($e);
            DB::connection('sqlsrv2')->rollback();
            return [
                "status"  => "failed",
                "message" => "Problem Creating Invoice. \n" . $e->getMessage()
            ];
        }

    }


    /**
     * @param $data
     * @return array
     */
    public static function payout($data){

        $Account = Account::where([
            'AccountID' => $data['AccountID'],
            'CompanyID' => $data['CompanyID']
        ])->first();
        $response = ['status' => 'failed', 'message' => "Payout Request Failed."];
        if($Account != false) {
            $PayoutMethod = $Account->PayoutMethod != "" ? $Account->PayoutMethod : "Stripe";
            if (!empty($PayoutMethod) && $PayoutMethod=='Stripe') {
                $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($PayoutMethod);
                $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
                $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $data['CompanyID']);
                $data['account'] = $Account;

                $response = $PaymentIntegration->payoutWithStripeAccount($data);
            }
        }

        return $response;
    }



    public static function successPayoutCustomerEmail($email){

        $status = EmailsTemplates::CheckEmailTemplateStatus(Account::OutPaymentEmailTemplate);
        if($status != false) {
            $Account = Account::find($email['AccountID']);
            $CompanyID = $email['CompanyID'];
            $CompanyName = Company::getName();
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency) ? $Currency->Code : '';
            $emaildata = array(
                'CompanyName' => $CompanyName,
                'Currency' => $CurrencyCode,
                'CompanyID' => $CompanyID,
                'OutPaymentAmount' => $email['Amount'],
            );

            $emaildata['EmailToName'] = $Account->AccountName;
            $body = EmailsTemplates::setOutPaymentPlaceholder($Account, 'body', $CompanyID, $emaildata);
            $emaildata['Subject'] = EmailsTemplates::setOutPaymentPlaceholder($Account, "subject", $CompanyID, $emaildata);
            if (!isset($emaildata['EmailFrom'])) {
                $emaildata['EmailFrom'] = EmailsTemplates::GetEmailTemplateFrom(Account::OutPaymentEmailTemplate);
            }

            $CustomerEmail = $Account->BillingEmail;
            if($CustomerEmail != '') {
                $CustomerEmail = explode(",", $CustomerEmail);
                $customeremail_status['status'] = 0;
                $customeremail_status['message'] = '';
                $customeremail_status['body'] = '';
                foreach ($CustomerEmail as $singleemail) {
                    $singleemail = trim($singleemail);
                    if (filter_var($singleemail, FILTER_VALIDATE_EMAIL)) {
                        $emaildata['EmailTo'][] = $singleemail;
                    }
                }
                Log::info("============ EmailData ===========");
                Log::info($emaildata);
                $customeremail_status = Helper::sendMaiL($body, $emaildata, 0);
            }
        }
    }



    public static function bankValidation($data){
        $ValidationResponse = array();
        $validator = Validator::make($data, AccountPayout::$AccountPayoutBankRules);
        if ($validator->fails()) {
            $errors = "";
            foreach ($validator->messages()->all() as $error){
                $errors .= $error."<br>";
            }

            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = $errors;
            return $ValidationResponse;
        }
        $CustomerID = $data['AccountID'];
        $account = Account::find($CustomerID);
        $CurrencyCode = Currency::getCurrency($account->CurrencyId);
        if(empty($CurrencyCode)){
            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = cus_lang("PAYMENT_MSG_NO_ACCOUNT_CURRENCY_AVAILABLE");
            return $ValidationResponse;
        }
        $data['currency'] = strtolower($CurrencyCode);
        $Country = $account->Country;
        if(!empty($Country)){
            $CountryCode = Country::where(['Country'=>$Country])->pluck('ISO2');
        }else{
            $CountryCode = '';
        }
        if(empty($CountryCode)){
            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = cus_lang("PAYMENT_MSG_NO_ACCOUNT_COUNTRY_AVAILABLE");
            return $ValidationResponse;
        }
        $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
    }


    public static function cardValidation($data){
        $ValidationResponse = array();
        $rules = array(
            'CardNumber' => 'required|digits_between:14,19',
            'ExpirationMonth' => 'required',
            'ExpirationYear' => 'required',
            'NameOnCard' => 'required',
            'CVVNumber' => 'required',
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $errors = "";
            foreach ($validator->messages()->all() as $error){
                $errors .= $error."<br>";
            }

            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = $errors;
            return $ValidationResponse;
        }

        if (date("Y") == $data['ExpirationYear'] && date("m") > $data['ExpirationMonth']) {
            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = cus_lang("PAYMENT_MSG_MONTH_MUST_BE_AFTER") . date("F");
            return $ValidationResponse;
        }

        $card = CreditCard::validCreditCard($data['CardNumber']);
        if ($card['valid'] == 0) {
            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = cus_lang("PAYMENT_MSG_ENTER_VALID_CARD_NUMBER");
            return $ValidationResponse;
        }

        $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
    }
}