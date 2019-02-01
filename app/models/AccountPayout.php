<?php
class AccountPayout extends \Eloquent
{
    protected $fillable = [];
    protected $guarded = array('AccountPayoutID');
    protected $table = 'tblAccountPayout';
    protected $primaryKey = "AccountPayoutID";
    public static $StatusActive = 1;
    public static $StatusDeactive = 0;

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
        $rules = array(
            'AccountNumber'     => 'required|digits_between:6,19',
            'RoutingNumber'     => 'required',
            'AccountHolderType' => 'required',
            'AccountHolderName' => 'required',
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