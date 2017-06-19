<?php
class AccountNextBilling extends \Eloquent {
    //
    protected $guarded = array("AccountNextBillingID");

    protected $table = 'tblAccountNextBilling';

    protected $primaryKey = "AccountNextBillingID";

    public $timestamps = false; // no created_at and updated_at

    public static function insertUpdateBilling($AccountID,$data=array(),$ServiceID){
        if(empty($ServiceID)){
            $ServiceID=0;
        }
        $AccountBilling =  AccountBilling::getBilling($AccountID,$ServiceID);
        if($AccountBilling->BillingCycleType != $data['BillingCycleType'] || (!empty($data['BillingCycleValue']) && $AccountBilling->BillingCycleValue != $data['BillingCycleValue']) ) {
            $AccountNextBilling['BillingCycleType'] = $data['BillingCycleType'];
            if (!empty($data['BillingCycleValue'])) {
                $AccountNextBilling['BillingCycleValue'] = $data['BillingCycleValue'];
            } else {
                $AccountNextBilling['BillingCycleValue'] = '';
            }
            if($AccountBilling->BillingCycleType == 'manual'){
                $AccountNextBilling['LastInvoiceDate'] = $AccountBilling->LastInvoiceDate;
            }else{
                $AccountNextBilling['LastInvoiceDate'] = $AccountBilling->NextInvoiceDate;
            }
            $BillingStartDate = strtotime($AccountNextBilling['LastInvoiceDate']);
            if (!empty($BillingStartDate) && $data['BillingCycleType'] != 'manual') {
                $AccountNextBilling['NextInvoiceDate'] = next_billing_date($AccountNextBilling['BillingCycleType'], $AccountNextBilling['BillingCycleValue'], $BillingStartDate);
            }else if($data['BillingCycleType'] == 'manual'){
                $AccountNextBilling['NextInvoiceDate'] = null;
            }
            if (AccountNextBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->count()) {
                AccountNextBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->update($AccountNextBilling);
            } else {
                $AccountNextBilling['AccountID'] = $AccountID;
                $AccountNextBilling['ServiceID'] = $ServiceID;
                AccountNextBilling::create($AccountNextBilling);
            }
            if($data['BillingCycleType'] != 'manual') {
                AccountBilling::storeNextInvoicePeriod($AccountID, $AccountNextBilling['BillingCycleType'], $AccountNextBilling['BillingCycleValue'], $AccountNextBilling['LastInvoiceDate'], $AccountNextBilling['NextInvoiceDate'], $ServiceID);
            }
        }else{
            AccountNextBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->delete();
        }

    }
    public static function getBilling($AccountID,$ServiceID=0){
        return AccountNextBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->first();
    }


}
