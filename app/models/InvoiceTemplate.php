<?php

class InvoiceTemplate extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('InvoiceTemplateID');
    protected $table = 'tblInvoiceTemplate';
    protected  $primaryKey = "InvoiceTemplateID";
    static protected  $enable_cache = false;
    public static $invoice_date_format = array(''=>'Select a DateFormat','d-m-Y'=>'dd-mm-yyyy','m-d-Y'=>'mm-dd-yyyy');

    static public function checkForeignKeyById($id) {
        $CompanyId = User::get_companyID();
        if(Account::where(["CompanyID"=>$CompanyId, "InvoiceTemplateID"=>$id])->count()>0){
            return true;
        }
        return false;
    }
    public static $cache = array(
        "it_dropdown1_cache",
    );
    public static function getInvoiceTemplateList() {

        if (self::$enable_cache && Cache::has('it_dropdown1_cache')) {
            $admin_defaults = Cache::get('it_dropdown1_cache');
            self::$cache['it_dropdown1_cache'] = $admin_defaults['it_dropdown1_cache'];
        } else {
            $CompanyId = User::get_companyID();
            self::$cache['it_dropdown1_cache'] = InvoiceTemplate::where("CompanyId",$CompanyId)->lists('Name','InvoiceTemplateID');
            self::$cache['it_dropdown1_cache'] = array('' => "Select an Invoice Template")+ self::$cache['it_dropdown1_cache'];
            Cache::forever('it_dropdown1_cache', array('it_dropdown1_cache' => self::$cache['it_dropdown1_cache']));
        }

        return self::$cache['it_dropdown1_cache'];

    }
    public static function getAccountNextInvoiceNumber($AccountID){

        $InvoiceTemplateID = AccountBilling::where(["AccountID"=>$AccountID])->pluck("InvoiceTemplateID");
        if($InvoiceTemplateID > 0){
            return self::getNextInvoiceNumber($InvoiceTemplateID);
        }else{
            return 0;
        }
    }
    public static function getNextInvoiceNumber($InvoiceTemplateid){
        $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateid);
        $NewInvoiceNumber =  (($InvoiceTemplate->LastInvoiceNumber > 0)?($InvoiceTemplate->LastInvoiceNumber + 1):$InvoiceTemplate->InvoiceStartNumber);
        $CompanyID = User::get_companyID();
        while(Invoice::where(["InvoiceNumber"=> $NewInvoiceNumber,'CompanyID'=>$CompanyID])->count()>0){
            $NewInvoiceNumber++;
        }
        return $NewInvoiceNumber;
    }
		/////////////////
	public static function getAccountNextEstimateNumber($AccountID)
	{

        $InvoiceTemplateID = AccountBilling::where(["AccountID"=>$AccountID])->pluck("InvoiceTemplateID");
        
		if($InvoiceTemplateID > 0)
		{
            return self::getNextEstimateNumber($InvoiceTemplateID);
        }
		else
		{
            return 0;
        }
    }
	
    public static function getNextEstimateNumber($InvoiceTemplateid)
	{
        $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateid);
        $NewEstimateNumber =  (($InvoiceTemplate->LastEstimateNumber > 0)?($InvoiceTemplate->LastEstimateNumber + 1):$InvoiceTemplate->EstimateStartNumber);
        $CompanyID = User::get_companyID();
        
		while(Estimate::where(["EstimateNumber"=> $NewEstimateNumber,'CompanyID'=>$CompanyID])->count()>0)
		{
            $NewEstimateNumber++;
        }
		
        return $NewEstimateNumber;
    }
	//////////////////////
	
    public static function clearCache(){

        Cache::flush("it_dropdown1_cache");

    }
}