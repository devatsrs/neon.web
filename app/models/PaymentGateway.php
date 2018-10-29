<?php
class PaymentGateway extends \Eloquent {
    protected $fillable = [];
    protected $table = "tblPaymentGateway";
    protected $primaryKey = "PaymentGatewayID";
    protected $guarded = array('PaymentGatewayID');
    public static $gateways = array('Authorize'=>'AuthorizeNet');
    const  AuthorizeNet	= 	1;
    const  Stripe		=	2;
    const  StripeACH	=	3;
    const  SagePayDirectDebit	=	4;
    const  FideliPay	=	5;
    const  PeleCard	    =	6;
    const  MerchantWarrior	    =	7;
    const  AuthorizeNetEcheck	=	8;
    public static $paymentgateway_name = array(''=>'' ,
        self::AuthorizeNet => 'AuthorizeNet',
        self::Stripe=>'Stripe',
        self::StripeACH=>'StripeACH',
        self::SagePayDirectDebit=>'SagePayDirectDebit',
        self::FideliPay=>'FideliPay',
        self::PeleCard=>'PeleCard',
        self::MerchantWarrior=>'MerchantWarrior',
        self::AuthorizeNetEcheck=>'AuthorizeNetEcheck'
    );

    public static function getName($PaymentGatewayID)
    {
        return PaymentGateway::$paymentgateway_name[$PaymentGatewayID];

        //return PaymentGateway::where(array('PaymentGatewayID' => $PaymentGatewayID))->pluck('Title');
    }

    // not using
    public static function addTransaction($PaymentGateway,$amount,$options,$account,$AccountPaymentProfileID,$CreatedBy)
    {
        switch($PaymentGateway) {
            case 'AuthorizeNet':
                $authorize = new AuthorizeNet();
                $transaction = $authorize->addAuthorizeNetTransaction($amount,$options);
				$Notes = '';
                if($transaction->response_code == 1) {
                    $Notes = 'AuthorizeNet transaction_id ' . $transaction->transaction_id;
                    $Status = TransactionLog::SUCCESS;
                }else{
                    $Status = TransactionLog::FAILED;
                    $Notes = isset($transaction->real_response->xml->messages->message->text) && $transaction->real_response->xml->messages->message->text != '' ? $transaction->real_response->xml->messages->message->text : $transaction->error_message ;
                    AccountPaymentProfile::setProfileBlock($AccountPaymentProfileID);
                }
                $transactionResponse['transaction_notes'] =$Notes;
                $transactionResponse['response_code'] = $transaction->response_code;
                $transactionResponse['transaction_payment_method'] = 'CREDIT CARD';
                $transactionResponse['failed_reason'] =$transaction->response_reason_text!='' ? $transaction->response_reason_text : $Notes;
                $transactionResponse['transaction_id'] = $transaction->transaction_id;
                $transactiondata = array();
                $transactiondata['CompanyID'] = $account->CompanyId;
                $transactiondata['AccountID'] = $account->AccountID;
                $transactiondata['Transaction'] = $transaction->transaction_id;
                $transactiondata['Notes'] = $Notes;
                $transactiondata['Amount'] = floatval($transaction->amount);
                $transactiondata['Status'] = $Status;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = $CreatedBy;
                $transactiondata['ModifyBy'] = $CreatedBy;
                $transactiondata['Response'] = json_encode($transaction);
                TransactionLog::insert($transactiondata);
                return $transactionResponse;
            case 'Stripe':

                $CurrencyCode = Currency::getCurrency($account->CurrencyId);
                $stripedata = array();
                $stripedata['currency'] = strtolower($CurrencyCode);
                $stripedata['amount'] = $amount;
                $stripedata['description'] = $options->InvoiceNumber.' (Invoice) Payment';
                $stripedata['customerid'] = $options->CustomerProfileID;

                $transactionResponse = array();

                $stripepayment = new StripeBilling();
                $transaction = $stripepayment->createchargebycustomer($stripedata);

                $Notes = '';
                if(!empty($transaction['response_code']) && $transaction['response_code'] == 1) {
                    $Notes = 'Stripe transaction_id ' . $transaction['id'];
                    $Status = TransactionLog::SUCCESS;
                }else{
                    $Status = TransactionLog::FAILED;
                    $Notes = empty($transaction['error']) ? '' : $transaction['error'];
                    //AccountPaymentProfile::setProfileBlock($AccountPaymentProfileID);
                }
                $transactionResponse['transaction_notes'] =$Notes;
                if(!empty($transaction['response_code'])) {
                    $transactionResponse['response_code'] = $transaction['response_code'];
                }
                $transactionResponse['transaction_payment_method'] = 'CREDIT CARD';
                $transactionResponse['failed_reason'] = $Notes;
                if(!empty($transaction['id'])) {
                    $transactionResponse['transaction_id'] = $transaction['id'];
                }
                $transactiondata = array();
                $transactiondata['CompanyID'] = $account->CompanyId;
                $transactiondata['AccountID'] = $account->AccountID;
                if(!empty($transaction['id'])) {
                    $transactiondata['Transaction'] = $transaction['id'];
                }
                $transactiondata['Notes'] = $Notes;
                if(!empty($transaction['amount'])) {
                    $transactiondata['Amount'] = floatval($transaction['amount']);
                }
                $transactiondata['Status'] = $Status;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = $CreatedBy;
                $transactiondata['ModifyBy'] = $CreatedBy;
                $transactiondata['Response'] = json_encode($transaction);
                TransactionLog::insert($transactiondata);
                return $transactionResponse;

            case 'StripeACH':

                $CurrencyCode = Currency::getCurrency($account->CurrencyId);
                $stripedata = array();
                $stripedata['currency'] = strtolower($CurrencyCode);
                $stripedata['amount'] = $amount;
                $stripedata['description'] = $options->InvoiceNumber.' (Invoice) Payment';
                $stripedata['customerid'] = $options->CustomerProfileID;

                $transactionResponse = array();

                $stripepayment = new StripeACH();
                $transaction = $stripepayment->createchargebycustomer($stripedata);

                $Notes = '';
                if(!empty($transaction['response_code']) && $transaction['response_code'] == 1) {
                    $Notes = 'Stripe ACH transaction_id ' . $transaction['id'];
                    $Status = TransactionLog::SUCCESS;
                }else{
                    $Status = TransactionLog::FAILED;
                    $Notes = empty($transaction['error']) ? '' : $transaction['error'];
                    //AccountPaymentProfile::setProfileBlock($AccountPaymentProfileID);
                }
                $transactionResponse['transaction_notes'] =$Notes;
                if(!empty($transaction['response_code'])) {
                    $transactionResponse['response_code'] = $transaction['response_code'];
                }
                $transactionResponse['transaction_payment_method'] = 'BANK TRANSFER';
                $transactionResponse['failed_reason'] = $Notes;
                if(!empty($transaction['id'])) {
                    $transactionResponse['transaction_id'] = $transaction['id'];
                }
                $transactiondata = array();
                $transactiondata['CompanyID'] = $account->CompanyId;
                $transactiondata['AccountID'] = $account->AccountID;
                if(!empty($transaction['id'])) {
                    $transactiondata['Transaction'] = $transaction['id'];
                }
                $transactiondata['Notes'] = $Notes;
                if(!empty($transaction['amount'])) {
                    $transactiondata['Amount'] = floatval($transaction['amount']);
                }
                $transactiondata['Status'] = $Status;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = $CreatedBy;
                $transactiondata['ModifyBy'] = $CreatedBy;
                $transactiondata['Response'] = json_encode($transaction);
                TransactionLog::insert($transactiondata);
                return $transactionResponse;

            case '':
                return '';

        }

    }

    public static function getPaymentGatewayIDByName($PaymentMehod){
        $PaymentGateway = PaymentGateway::$paymentgateway_name;
        $PaymentGatewayID = array_search($PaymentMehod, $PaymentGateway);
        return $PaymentGatewayID;
    }

    public static function getPaymentGatewayIDBYAccount($AccountID){
        $Account = Account::find($AccountID);
        $PaymentGatewayID = '';
        if(!empty($Account->PaymentMethod)){
            $PaymentGatewayName = $Account->PaymentMethod;
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($PaymentGatewayName);
        }
        return $PaymentGatewayID;
    }

    public static function getPaymentGatewayNameBYAccount($AccountID){
        $Account = Account::find($AccountID);
        $PaymentGatewayName = '';
        if(!empty($Account->PaymentMethod)){
            $PaymentGatewayName = $Account->PaymentMethod;
        }
        return $PaymentGatewayName;
    }

    public static function getPaymentGatewayClass($PaymentGatewayID){
        if($PaymentGatewayID=='2'){
            return 'StripeBilling';
        }
        return PaymentGateway::$paymentgateway_name[$PaymentGatewayID];
    }
}