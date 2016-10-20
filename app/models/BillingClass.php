<?php

class BillingClass extends \Eloquent
{
    protected $guarded = array("BillingClassID");

    protected $table = 'tblBillingClass';

    protected $primaryKey = "BillingClassID";

    public static $SendInvoiceSetting = array(""=>"Please Select an Option", "automatically"=>"Automatically", "after_admin_review"=>"After Admin Review" , "never"=>"Never");

    public static function getDropdownIDList($CompanyID){
        $DropdownIDList = BillingClass::where(array("CompanyID"=>$CompanyID))->lists('Name', 'BillingClassID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

    public static function getInvoiceTemplateID($BillingClassID){
        return BillingClass::where('BillingClassID',$BillingClassID)->pluck('InvoiceTemplateID');
    }
    public static function getPaymentDueInDays($BillingClassID){
        return BillingClass::where('BillingClassID',$BillingClassID)->pluck('PaymentDueInDays');
    }
    public static function getCDRType($BillingClassID){
        return BillingClass::where('BillingClassID',$BillingClassID)->pluck('CDRType');
    }
    public static function getRoundChargesAmount($BillingClassID){
        return BillingClass::where('BillingClassID',$BillingClassID)->pluck('RoundChargesAmount');
    }
    public static function getAccounts($BillingClassID){
        return Account::join('tblAccountBilling','tblAccountBilling.AccountID','=','tblAccount.AccountID')->where('BillingClassID',$BillingClassID)->orderBy('AccountName')->get(['AccountName']);
    }

}