<?php

class InvoiceTemplate extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('InvoiceTemplateID');
    protected $table = 'tblInvoiceTemplate';
    protected  $primaryKey = "InvoiceTemplateID";
    static protected  $enable_cache = false;
    public static $invoice_date_format = array(''=>'Select','d-m-Y'=>'dd-mm-yyyy','m-d-Y'=>'mm-dd-yyyy');
	public static $HeaderDefault = "Invoice Company Name \n Address \n Country \n Email";
	public static $TermsDefault = 'Edit This dummy Terms and Condition. Please carefully review the following basic rules that govern your use of the Website. Please note that your use of the Website constitutes your unconditional agreement to follow and be bound by these Terms and Conditions of Use. If you (the "User") do not agree to them, do not use the Website, provide any materials to the Website or download any materials from them.';
	public static $FooterDefault = 'Edit This dummy Terms and Condition. Please carefully review the following basic rules that govern your use of the Website. Please note that your use of the Website constitutes your unconditional agreement to follow and be bound by these Terms and Conditions of Use. If you (the "User") do not agree to them, do not use the Website, provide any materials to the Website or download any materials from them';
	
    static public function checkForeignKeyById($id) {
        $CompanyId = User::get_companyID();
        if(BillingClass::where(["CompanyID"=>$CompanyId, "InvoiceTemplateID"=>$id])->count()>0){
            return true;
        }
        return false;
    }
    public static $cache = array(
        "it_dropdown1_cache",
    );
    public static function getInvoiceTemplateList($CompanyID=0) {

        if (self::$enable_cache && Cache::has('it_dropdown1_cache')) {
            $admin_defaults = Cache::get('it_dropdown1_cache');
            self::$cache['it_dropdown1_cache'] = $admin_defaults['it_dropdown1_cache'];
        } else {
            $CompanyId = $CompanyID>0 ? $CompanyID : User::get_companyID();
            self::$cache['it_dropdown1_cache'] = InvoiceTemplate::where("CompanyId",$CompanyId)->lists('Name','InvoiceTemplateID');
            self::$cache['it_dropdown1_cache'] = array('' => "Select")+ self::$cache['it_dropdown1_cache'];
            Cache::forever('it_dropdown1_cache', array('it_dropdown1_cache' => self::$cache['it_dropdown1_cache']));
        }

        return self::$cache['it_dropdown1_cache'];

    }
    public static function getAccountNextInvoiceNumber($AccountID){

        $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($AccountID);
        if($InvoiceTemplateID > 0){
            return self::getNextInvoiceNumber($InvoiceTemplateID);
        }else{
            return 0;
        }
    }
    public static function getNextInvoiceNumber($InvoiceTemplateid){
        $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateid);
        $NewInvoiceNumber =  (($InvoiceTemplate->LastInvoiceNumber > 0)?($InvoiceTemplate->LastInvoiceNumber + 1):$InvoiceTemplate->InvoiceStartNumber);
        $CompanyID = $InvoiceTemplate->CompanyID;
        while(Invoice::where(["InvoiceNumber"=> $NewInvoiceNumber,'CompanyID'=>$CompanyID])->count()>0){
            $NewInvoiceNumber++;
        }
        return $NewInvoiceNumber;
    }
		/////////////////
	public static function getAccountNextEstimateNumber($AccountID)
	{

        $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($AccountID);
        
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
        if(empty($NewEstimateNumber)){
            $NewEstimateNumber=1;
        }
        $CompanyID = User::get_companyID();
        
		while(Estimate::where(["EstimateNumber"=> $NewEstimateNumber,'CompanyID'=>$CompanyID])->count()>0)
		{
            $NewEstimateNumber++;
        }
		
        return $NewEstimateNumber;
    }

    public static function getNextCreditNotesNumber($InvoiceTemplateid)
    {
        $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateid);
        $NewCreditNotesNumber =  (($InvoiceTemplate->LastCreditNotesNumber > 0)?($InvoiceTemplate->LastCreditNotesNumber + 1):$InvoiceTemplate->CreditNotesStartNumber);
        if(empty($NewCreditNotesNumber)){
            $NewCreditNotesNumber=1;
        }
        $CompanyID = User::get_companyID();

        while(CreditNotes::where(["CreditNotesNumber"=> $NewCreditNotesNumber,'CompanyID'=>$CompanyID])->count()>0)
        {
            $NewCreditNotesNumber++;
        }

        return $NewCreditNotesNumber;
    }
	//////////////////////
	
    public static function clearCache(){

        Cache::flush("it_dropdown1_cache");

    }
}