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

}