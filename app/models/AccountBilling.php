<?php
class AccountBilling extends \Eloquent {
    //
    protected $guarded = array("AccountBillingID");

    protected $table = 'tblAccountBilling';

    protected $primaryKey = "AccountBillingID";

    public $timestamps = false; // no created_at and updated_at

    public static function insertUpdateBilling($AccountID,$data=array()){

        $AccountBilling['PaymentDueInDays'] = $data['PaymentDueInDays'];
        $AccountBilling['RoundChargesAmount'] = $data['RoundChargesAmount'];
        $AccountBilling['CDRType'] = $data['CDRType'];
        $AccountBilling['InvoiceTemplateID'] = $data['InvoiceTemplateID'];
        $AccountBilling['BillingType'] = $data['BillingType'];
        $AccountBilling['TaxRateId'] = $data['TaxRateId'];
        $AccountBilling['BillingCycleType'] = $data['BillingCycleType'];
        $AccountBilling['BillingTimezone'] = $data['BillingTimezone'];
        $AccountBilling['SendInvoiceSetting'] = $data['SendInvoiceSetting'];
        if(!empty($data['BillingStartDate'])) {
            $AccountBilling['BillingStartDate'] = $data['BillingStartDate'];
        }
        if(!empty($data['BillingCycleValue'])){
            $AccountBilling['BillingCycleValue'] = $data['BillingCycleValue'];
        }else{
            $AccountBilling['BillingCycleValue'] = '';
        }
        if(!empty($data['LastInvoiceDate'])){
            $AccountBilling['LastInvoiceDate'] = $data['LastInvoiceDate'];
        }elseif(!empty($data['BillingStartDate'])) {
            $AccountBilling['LastInvoiceDate'] = $data['BillingStartDate'];
        }
        if(!empty($AccountBilling['LastInvoiceDate'])) {
            $BillingStartDate = strtotime($AccountBilling['LastInvoiceDate']);
        }else if(!empty($AccountBilling['BillingStartDate'])) {
            $BillingStartDate = strtotime($AccountBilling['BillingStartDate']);
        }
        if(!empty($BillingStartDate)) {
            $AccountBilling['NextInvoiceDate'] = next_billing_date($AccountBilling['BillingCycleType'], $AccountBilling['BillingCycleValue'], $BillingStartDate);
        }

        if(AccountBilling::where('AccountID',$AccountID)->count()){
            AccountBilling::where('AccountID',$AccountID)->update($AccountBilling);
        }else{
            $AccountBilling['AccountID'] = $AccountID;
            AccountBilling::create($AccountBilling);
        }

    }
    public static function getBilling($AccountID){
        return AccountBilling::where('AccountID',$AccountID)->first();
    }
    public static function getBillingKey($AccountBilling,$key){
        return !empty($AccountBilling)?$AccountBilling->$key:'';
    }

    public static function getBillingDay($AccountID){
        $days = 0;
        $AccountBilling =  AccountBilling::getBilling($AccountID);
        if(!empty($AccountBilling)) {
            $days = getBillingDay(strtotime($AccountBilling->LastInvoiceDate), $AccountBilling->BillingCycleType, $AccountBilling->BillingCycleValue);
        }
        return $days;
    }
    public static function getInvoiceTemplateID($AccountID){
        return AccountBilling::where('AccountID',$AccountID)->pluck('InvoiceTemplateID');
    }
}
