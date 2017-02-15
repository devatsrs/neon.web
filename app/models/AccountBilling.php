<?php
class AccountBilling extends \Eloquent {
    //
    protected $guarded = array("AccountBillingID");

    protected $table = 'tblAccountBilling';

    protected $primaryKey = "AccountBillingID";

    public $timestamps = false; // no created_at and updated_at

    public static function insertUpdateBilling($AccountID,$data=array(),$ServiceID){
        if(empty($ServiceID)){
            $ServiceID=0;
        }
        if(AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->count() == 0) {

            $AccountBilling['BillingClassID'] = $data['BillingClassID'];
            $AccountBilling['BillingType'] = $data['BillingType'];
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
            $AccountBilling['ServiceID'] = $ServiceID;
            AccountBilling::create($AccountBilling);
        }else{
            AccountNextBilling::insertUpdateBilling($AccountID,$data,$ServiceID);
            $AccountBilling['BillingClassID'] = $data['BillingClassID'];
            $AccountBilling['BillingType'] = $data['BillingType'];
            $AccountBilling['BillingTimezone'] = $data['BillingTimezone'];
            $AccountBilling['SendInvoiceSetting'] = $data['SendInvoiceSetting'];
            AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->update($AccountBilling);

        }

    }
    public static function getBilling($AccountID,$ServiceID){
        if(empty($ServiceID)){
            $ServiceID = 0;
        }
        return AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->first();
    }
    public static function getBillingKey($AccountBilling,$key){
        return !empty($AccountBilling)?$AccountBilling->$key:'';
    }

    //not using
    public static function getBillingDay($AccountID){
        $days = 0;
        $AccountBilling =  AccountBilling::getBilling($AccountID);
        if(!empty($AccountBilling)) {
            $days = getBillingDay(strtotime($AccountBilling->LastInvoiceDate), $AccountBilling->BillingCycleType, $AccountBilling->BillingCycleValue);
        }
        return $days;
    }
    public static function getInvoiceTemplateID($AccountID){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getInvoiceTemplateID($BillingClassID);
    }
	
	public static function getTaxRate($AccountID){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getTaxRate($BillingClassID);
    }
	
	public static function getTaxRateType($AccountID,$type){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getTaxRateType($BillingClassID,$type);
    }
	
	
    public static function storeNextInvoicePeriod($AccountID,$BillingCycleType,$BillingCycleValue,$LastInvoiceDate,$NextInvoiceDate,$ServiceID){
        if(empty($ServiceID)){
            $ServiceID=0;
        }
        $StartDate = $LastInvoiceDate;
        $EndDate = $NextInvoiceDate;
        $NextBilling =array();
        for($count=0;$count<50;$count++){
            $NextBilling[]  = array(
                'StartDate' => $StartDate,
                'EndDate' =>$EndDate,
                'AccountID' => $AccountID,
                'ServiceID' => $ServiceID
            );
            $StartDate = $EndDate;
            $EndDate = next_billing_date($BillingCycleType, $BillingCycleValue, strtotime($StartDate));
        }
        DB::table('tblAccountBillingPeriod')->where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->delete();
        DB::table('tblAccountBillingPeriod')->insert($NextBilling);
    }
    public static function getCurrentPeriod($AccountID,$date,$ServiceID){
        if(empty($ServiceID)){
            $ServiceID = 0;
        }
        return DB::table('tblAccountBillingPeriod')->where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->where('StartDate','<=',$date)->where('EndDate','>',$date)->first();
    }

    public static function storeFirstTimeInvoicePeriod($AccountID,$ServiceID){
        if(DB::table('tblAccountBillingPeriod')->where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->where('StartDate','>=',date('Y-m-d'))->count() == 0){
            $AccountBilling =  AccountBilling::getBilling($AccountID,$ServiceID);
            AccountBilling::storeNextInvoicePeriod($AccountID,$AccountBilling->BillingCycleType,$AccountBilling->BillingCycleValue,$AccountBilling->LastInvoiceDate,$AccountBilling->NextInvoiceDate,$ServiceID);
        }
    }

    public static function getBillingClassID($AccountID){
        return AccountBilling::where('AccountID',$AccountID)->pluck('BillingClassID');
    }
    public static function getPaymentDueInDays($AccountID){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getPaymentDueInDays($BillingClassID);
    }
    public static function getCDRType($AccountID){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getCDRType($BillingClassID);
    }
    public static function getRoundChargesAmount($AccountID){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getRoundChargesAmount($BillingClassID);
    }
}
