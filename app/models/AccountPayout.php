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

    public static function createPayoutAccount($CompanyID, $CustomerID, $PaymentGatewayID)
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
        $stripedata['acc'] = $account;

        $StripeResponse = $stripepayment->create_customer($stripedata);

        if ($StripeResponse["status"] == "Success") {
            $option = array(
                'CustomerProfileID' => $StripeResponse['CustomerProfileID'],
                'CardID' => $StripeResponse['CardID']
            );

            if(isset($StripeResponse['StripeAccountID'])){
                $option['StripeAccountID'] = $StripeResponse['StripeAccountID'];
            }

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
    public static function deletePayoutAccount($CompanyID,$AccountID,$AccountPayoutID){

        $stripepayment = new StripeBilling($CompanyID);

        if (empty($stripepayment->status)) {
            return Response::json(array("status" => "failed", "message" => "Stripe Payment not setup correctly"));
        }

        $count = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
        $PaymentProfile = AccountPaymentProfile::find($AccountPayoutID);
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

}