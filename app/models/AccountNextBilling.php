<?php
class AccountNextBilling extends \Eloquent {
    //
    protected $guarded = array("AccountNextBillingID");

    protected $table = 'tblAccountNextBilling';

    protected $primaryKey = "AccountNextBillingID";

    public $timestamps = false; // no created_at and updated_at

    public static function insertUpdateBilling($AccountID,$data=array()){

        $AccountBilling =  AccountBilling::getBilling($AccountID);
        if($AccountBilling->BillingCycleType != $data['BillingCycleType'] || (!empty($data['BillingCycleValue']) && $AccountBilling->BillingCycleValue != $data['BillingCycleValue']) ) {
            $AccountNextBilling['BillingCycleType'] = $data['BillingCycleType'];
            if (!empty($data['BillingCycleValue'])) {
                $AccountNextBilling['BillingCycleValue'] = $data['BillingCycleValue'];
            } else {
                $AccountNextBilling['BillingCycleValue'] = '';
            }
            $AccountNextBilling['LastInvoiceDate'] = $AccountBilling->NextInvoiceDate;
            $BillingStartDate = strtotime($AccountNextBilling['LastInvoiceDate']);
            if (!empty($BillingStartDate)) {
                $AccountNextBilling['NextInvoiceDate'] = next_billing_date($AccountNextBilling['BillingCycleType'], $AccountNextBilling['BillingCycleValue'], $BillingStartDate);
            }
            if (AccountNextBilling::where('AccountID', $AccountID)->count()) {
                AccountNextBilling::where('AccountID', $AccountID)->update($AccountNextBilling);
            } else {
                $AccountNextBilling['AccountID'] = $AccountID;
                AccountNextBilling::create($AccountNextBilling);
            }
        }else{
            AccountNextBilling::where('AccountID', $AccountID)->delete();
        }

    }
    public static function getBilling($AccountID){
        return AccountNextBilling::where('AccountID',$AccountID)->first();
    }


}
