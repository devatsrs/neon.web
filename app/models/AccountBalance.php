<?php
class AccountBalance extends \Eloquent {
    //
    protected $guarded = array("AccountBalanceID");

    protected $table = 'tblAccountBalance';

    protected $primaryKey = "AccountBalanceID";

    public $timestamps = false; // no created_at and updated_at


    public static function getAccountSOA($CompanyID,$AccountID){
        $query = "call prc_getSOA (" . $CompanyID . "," . $AccountID . ",'','',0)";
        $result = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('InvoiceOutWithPaymentIn','InvoiceInWithPaymentOut','InvoiceOutAmountTotal','InvoiceOutDisputeAmountTotal','PaymentInAmountTotal','InvoiceInAmountTotal','InvoiceInDisputeAmountTotal','PaymentOutAmountTotal'));

        $InvoiceOutAmountTotal = $result['data']['InvoiceOutAmountTotal'];
        $PaymentInAmountTotal = $result['data']['PaymentInAmountTotal'];
        $InvoiceInAmountTotal = $result['data']['InvoiceInAmountTotal'];
        $PaymentOutAmountTotal = $result['data']['PaymentOutAmountTotal'];

        $InvoiceOutAmountTotal = ($InvoiceOutAmountTotal[0]->InvoiceOutAmountTotal <> 0) ? $InvoiceOutAmountTotal[0]->InvoiceOutAmountTotal : 0;
        $PaymentInAmountTotal = ($PaymentInAmountTotal[0]->PaymentInAmountTotal <> 0) ? $PaymentInAmountTotal[0]->PaymentInAmountTotal : 0;
        $InvoiceInAmountTotal = ($InvoiceInAmountTotal[0]->InvoiceInAmountTotal <> 0) ? $InvoiceInAmountTotal[0]->InvoiceInAmountTotal : 0;
        $PaymentOutAmountTotal = ($PaymentOutAmountTotal[0]->PaymentOutAmountTotal <> 0) ? $PaymentOutAmountTotal[0]->PaymentOutAmountTotal : 0;

        $OffsetBalance = ($InvoiceOutAmountTotal - $PaymentInAmountTotal) - ($InvoiceInAmountTotal - $PaymentOutAmountTotal);

        return $OffsetBalance;


    }
	
	  public static function getBalanceAmount($AccountID){
          $BalanceAmount = AccountBalance::where(['AccountID'=>$AccountID])->pluck('BalanceAmount');
          $BillingType=AccountBilling::where(['AccountID'=>$AccountID,'ServiceID'=>0])->pluck('BillingType');
          if($BillingType==AccountApproval::BILLINGTYPE_PREPAID){
              $BalanceAmount = AccountBalanceLog::getPrepaidAccountBalance($AccountID);
          }
          return $BalanceAmount;
    }
	
	 public static function getBalanceThresholdAmount($AccountID){
        return AccountBalance::where(['AccountID'=>$AccountID])->pluck('BalanceThreshold');
    }

	 public static function getBalanceSOAOffsetAmount($AccountID){
        return AccountBalance::where(['AccountID'=>$AccountID])->pluck('SOAOffset');
    }

    /**
     * If Account Balance is negative than
     * Prepaid Account = amount is negative to positive
     * Postpaid Account = amount is 0
    **/
    public static function getAccountBalance($AccountID){
        $AccountBalance = AccountBalance::where('AccountID',$AccountID)->pluck('BalanceAmount');
        $BillingType = AccountBilling::where(['AccountID'=>$AccountID,'ServiceID'=>0])->pluck('BillingType');
        if(isset($BillingType)){
            if($BillingType==AccountApproval::BILLINGTYPE_PREPAID){
                /*if($AccountBalance<0){
                    $AccountBalance=abs($AccountBalance);
                }else{
                    $AccountBalance=($AccountBalance) * -1;
                }*/
                $AccountBalance = AccountBalanceLog::getPrepaidAccountBalance($AccountID);
                $ActiveBalance = ActiveCall::where(['AccountID'=>$AccountID])->sum('Cost');

                $AccountBalance = empty($AccountBalance)?0:$AccountBalance;
                $ActiveBalance = empty($ActiveBalance)?0:$ActiveBalance;

                $AccountBalance = $AccountBalance - $ActiveBalance;
            }else{
                if($AccountBalance<0){
                    $AccountBalance=0;
                }
            }
        }else{
            if($AccountBalance<0){
                $AccountBalance=0;
            }
        }
        return $AccountBalance;
    }

    public static function getAccountOutstandingBalance($AccountID,$AccountOutstandingBalance){
        $BillingType = AccountBilling::where(['AccountID'=>$AccountID,'ServiceID'=>0])->pluck('BillingType');
        if(isset($BillingType)){
            if($BillingType==AccountApproval::BILLINGTYPE_PREPAID){
                if($AccountOutstandingBalance<0){
                    $AccountOutstandingBalance=abs($AccountOutstandingBalance);
                }else{
                    $AccountOutstandingBalance=($AccountOutstandingBalance) * -1;
                }
            }else{
                if($AccountOutstandingBalance<0){
                    $AccountOutstandingBalance=0;
                }
            }
        }else{
            if($AccountOutstandingBalance<0){
                $AccountOutstandingBalance=0;
            }
        }
        return $AccountOutstandingBalance;
    }
	
    public static function getNewAccountBalance($CompanyID,$AccountID){
        $AccountBalance = AccountBalance::getBalanceSOAOffsetAmount($AccountID);
        $AccountBalance = number_format($AccountBalance, get_round_decimal_places($AccountID));
        return $AccountBalance;
    }

    public static function getNewAccountExposure($CompanyID,$AccountID){
        $SOA_Amount = AccountBalance::getBalanceSOAOffsetAmount($AccountID);
        $response = AccountBalance::where('AccountID', $AccountID)->first(['AccountID', 'PermanentCredit', 'UnbilledAmount', 'EmailToCustomer', 'TemporaryCredit', 'TemporaryCreditDateTime', 'BalanceThreshold', 'BalanceAmount', 'VendorUnbilledAmount']);
        $UnbilledAmount = empty($response->UnbilledAmount) ? 0 : $response->UnbilledAmount;
        $VendorUnbilledAmount = empty($response->VendorUnbilledAmount) ? 0 : $response->VendorUnbilledAmount;
        $AccountBalance = $SOA_Amount + ($UnbilledAmount - $VendorUnbilledAmount);
        $AccountBalance = number_format($AccountBalance, get_round_decimal_places($AccountID));
        return $AccountBalance;
    }

    public static function getAccountBalanceWithActiveCallRM($AccountID){

        $AccountBalance = AccountBalanceLog::getPrepaidAccountBalance($AccountID);
        $ActiveBalance = ActiveCall::where(['AccountID'=>$AccountID])->sum('Cost');

        $AccountBalance = empty($AccountBalance)?0:$AccountBalance;
        $ActiveBalance = empty($ActiveBalance)?0:$ActiveBalance;

        $TotalAmount = $AccountBalance - $ActiveBalance;

        return $TotalAmount;
    }
}
