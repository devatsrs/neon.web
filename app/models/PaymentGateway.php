<?php
class PaymentGateway extends \Eloquent {
    protected $fillable = [];
    protected $table = "tblPaymentGateway";
    protected $primaryKey = "PaymentGatewayID";
    protected $guarded = array('PaymentGatewayID');
    public static $gateways = array('Authorize'=>'AuthorizeNet');
    const  Authorize 	= 	1;
    const  Stripe		=	2;
    public static $paymentgateway_name = array(''=>'' ,self::Authorize => 'AuthorizeNet',self::Stripe=>'Stripe');

    public static function getName($PaymentGatewayID)
    {
        return PaymentGateway::$paymentgateway_name[$PaymentGatewayID];

        //return PaymentGateway::where(array('PaymentGatewayID' => $PaymentGatewayID))->pluck('Title');
    }

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
                $transactiondata['Reposnse'] = json_encode($transaction);
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
                if($transaction['response_code'] == 1) {
                    $Notes = 'Stripe transaction_id ' . $transaction['id'];
                    $Status = TransactionLog::SUCCESS;
                }else{
                    $Status = TransactionLog::FAILED;
                    $Notes = empty($transaction['error']) ? '' : $transaction['error'];
                    AccountPaymentProfile::setProfileBlock($AccountPaymentProfileID);
                }
                $transactionResponse['transaction_notes'] =$Notes;
                $transactionResponse['response_code'] = $transaction['response_code'];
                $transactionResponse['transaction_payment_method'] = 'CREDIT CARD';
                $transactionResponse['failed_reason'] = $Notes;
                $transactionResponse['transaction_id'] = $transaction['id'];
                $transactiondata = array();
                $transactiondata['CompanyID'] = $account->CompanyId;
                $transactiondata['AccountID'] = $account->AccountID;
                $transactiondata['Transaction'] = $transaction['id'];
                $transactiondata['Notes'] = $Notes;
                $transactiondata['Amount'] = floatval($transaction['amount']);
                $transactiondata['Status'] = $Status;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = $CreatedBy;
                $transactiondata['ModifyBy'] = $CreatedBy;
                $transactiondata['Reposnse'] = json_encode($transaction);
                TransactionLog::insert($transactiondata);
                return $transactionResponse;

            case '':
                return '';

        }

    }

    public static function getPaymentGatewayID(){
        $PaymentGatewayID = 0;
        if(is_authorize()){
            $PaymentGatewayID = PaymentGateway::Authorize;
        }
        if(is_Stripe()){
            $PaymentGatewayID = PaymentGateway::Stripe;
        }
        return $PaymentGatewayID;
    }

}