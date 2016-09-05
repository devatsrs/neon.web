<?php
class AccountBilling extends \Eloquent {
    //
    protected $guarded = array("AccountBillingID");

    protected $table = 'tblAccountBilling';

    protected $primaryKey = "AccountBillingID";

    public $timestamps = false; // no created_at and updated_at

    public static function insertUpdateBilling($AccountID,$data=array()){
        if(AccountBilling::where('AccountID',$AccountID)->count() == 0) {
            $AccountBilling['PaymentDueInDays'] = $data['PaymentDueInDays'];
            $AccountBilling['RoundChargesAmount'] = $data['RoundChargesAmount'];
            $AccountBilling['CDRType'] = $data['CDRType'];
            $AccountBilling['InvoiceTemplateID'] = $data['InvoiceTemplateID'];
            $AccountBilling['BillingType'] = $data['BillingType'];
            $AccountBilling['TaxRateId'] = $data['TaxRateId'];
            $AccountBilling['BillingCycleType'] = $data['BillingCycleType'];
            $AccountBilling['BillingTimezone'] = $data['BillingTimezone'];
            $AccountBilling['SendInvoiceSetting'] = $data['SendInvoiceSetting'];

            if (!empty($data['BillingStartDate'])) {
                $AccountBilling['BillingStartDate'] = $data['BillingStartDate'];
            }
            if (!empty($data['BillingCycleValue'])) {
                $AccountBilling['BillingCycleValue'] = $data['BillingCycleValue'];
            } else {
                $AccountBilling['BillingCycleValue'] = '';
            }
            if (!empty($data['LastInvoiceDate'])) {
                $AccountBilling['LastInvoiceDate'] = $data['LastInvoiceDate'];
            } elseif (!empty($data['BillingStartDate'])) {
                $AccountBilling['LastInvoiceDate'] = $data['BillingStartDate'];
            }
            if (!empty($AccountBilling['LastInvoiceDate'])) {
                $BillingStartDate = strtotime($AccountBilling['LastInvoiceDate']);
            } else if (!empty($AccountBilling['BillingStartDate'])) {
                $BillingStartDate = strtotime($AccountBilling['BillingStartDate']);
            }
            if (!empty($BillingStartDate)) {
                $AccountBilling['NextInvoiceDate'] = next_billing_date($AccountBilling['BillingCycleType'], $AccountBilling['BillingCycleValue'], $BillingStartDate);
            }
            $AccountBilling['AccountID'] = $AccountID;
            AccountBilling::create($AccountBilling);
        }else{
            AccountNextBilling::insertUpdateBilling($AccountID,$data);

            $AccountBilling['PaymentDueInDays'] = $data['PaymentDueInDays'];
            $AccountBilling['RoundChargesAmount'] = $data['RoundChargesAmount'];
            $AccountBilling['CDRType'] = $data['CDRType'];
            $AccountBilling['InvoiceTemplateID'] = $data['InvoiceTemplateID'];
            $AccountBilling['BillingType'] = $data['BillingType'];
            $AccountBilling['TaxRateId'] = $data['TaxRateId'];
            $AccountBilling['BillingTimezone'] = $data['BillingTimezone'];
            $AccountBilling['SendInvoiceSetting'] = $data['SendInvoiceSetting'];
            AccountBilling::where('AccountID', $AccountID)->update($AccountBilling);

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
    public static function storeNextInvoicePeriod($AccountID,$BillingCycleType,$BillingCycleValue,$LastInvoiceDate,$NextInvoiceDate){
        $StartDate = $LastInvoiceDate;
        $EndDate = $NextInvoiceDate;
        $NextBilling =array();
        for($count=0;$count<50;$count++){
            $NextBilling[]  = array(
                'StartDate' => $StartDate,
                'EndDate' =>$EndDate,
                'AccountID' => $AccountID
            );
            $StartDate = $EndDate;
            $EndDate = next_billing_date($BillingCycleType, $BillingCycleValue, strtotime($StartDate));
        }
        DB::table('tblAccountBillingPeriod')->where(array('AccountID'=>$AccountID))->delete();
        DB::table('tblAccountBillingPeriod')->insert($NextBilling);
    }
    public static function getCurrentPeriod($AccountID,$date){
        return DB::table('tblAccountBillingPeriod')->where(array('AccountID'=>$AccountID))->where('StartDate','<=',$date)->where('EndDate','>',$date)->first();
    }

    public static function storeFirstTimeInvoicePeriod($AccountID){
        if(DB::table('tblAccountBillingPeriod')->where(array('AccountID'=>$AccountID))->count() == 0){
            $AccountBilling =  AccountBilling::getBilling($AccountID);
            AccountBilling::storeNextInvoicePeriod($AccountID,$AccountBilling->BillingCycleType,$AccountBilling->BillingCycleValue,$AccountBilling->LastInvoiceDate,$AccountBilling->NextInvoiceDate);
        }
    }
}
