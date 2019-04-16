<?php

class EmailTemplate extends \Eloquent {

    protected $guarded = array("TemplateID");
    protected $table = 'tblEmailTemplate';
    protected  $primaryKey = "TemplateID";
	
    const ACCOUNT_TEMPLATE 		=	1;
    const INVOICE_TEMPLATE 		=	2;
    const RATESHEET_TEMPLATE 	= 	3;
	const TICKET_TEMPLATE 		= 	4;
	const ESTIMATE_TEMPLATE 	=	5;
	const CONTACT_TEMPLATE 		=	6;
	const CRONJOB_TEMPLATE 		=	7;
	const TASK_TEMPLATE 		=	8;
	const OPPORTUNITY_TEMPLATE	=	9;
    const AUTO_PAYMENT_TEMPLATE =	10;
    const OUT_PAYMENT_TEMPLATE  =   11;
    const CONTRACT_MANAGE       =   12;
    const CONTRACT_EXPIRE       =   13;
    const APPROVE_OUT_PAYMENT   =   14;

    const InvoicePaidNotificationTemplate   = 'InvoicePaidNotification';
    const DisputeEmailCustomerTemplate      = 'DisputeEmailCustomer';

	
	const PRIVACY_ON = 1;
    const PRIVACY_OFF = 0;
	
	const DYNAMICTEMPLATE = 0;
	const STATICTEMPLATE  = 1;
    const INVOICETEMPLATE = 'Invoice Payment Reminde';
    /*const  INVOICEPAYMENTREMINDER1= 'InvoicePaymentReminder1';
    const  INVOICEPAYMENTREMINDER2= 'InvoicePaymentReminder2';
    const  INVOICEPAYMENTREMINDER3= 'InvoicePaymentReminder3';
    const  INVOICEPAYMENTREMINDER4= 'InvoicePaymentReminder4';
    const  INVOICEPAYMENTREMINDER5= 'InvoicePaymentRemindeR5';*/

	
	public static $TemplateType = [self::ACCOUNT_TEMPLATE=>'leadoptions',self::INVOICE_TEMPLATE=>'invoiceoptions',self::RATESHEET_TEMPLATE=>'Ratesheet',self::TICKET_TEMPLATE=>'Tickets'];

    public static $privacy = [0=>'All User',1=>'Only Me'];
    public static $Type = [0=>'Select Template Type',self::ACCOUNT_TEMPLATE=>'Account',self::INVOICE_TEMPLATE=>'Billing',self::RATESHEET_TEMPLATE=>'Rate sheet',self::TICKET_TEMPLATE=>'Tickets'];
    
	
    public static function getEmailTemplateDropdownIDList($ID){
        $select =  1;
        $data['CompanyID']=$ID;

        $language_arr = Translation::getLanguageDropdownIdList();
        //print_r($language_arr);
        $result=array();
        foreach($language_arr as $key=>$value){

            $data['LanguageID']=$key;

            $EmailTemplate = EmailTemplate::where('CompanyID',$ID);
            $row = $EmailTemplate->select(array('TemplateID', 'TemplateName'))->orderBy('TemplateName')->lists('TemplateName','TemplateID');

            if(count($row)){
                $result[$value]=$row;
            }
        }

        if(!empty($result) && $select==1){
            $result = array(""=> "Select")+$result;
        }
        return $result;
    }
    public static function checkForeignKeyById($id){
        $companyID = User::get_companyID();
        $JobTypeID = JobType::where(["Code" => 'BLE'])->pluck('JobTypeID');
        $hasInCronLog = Job::where("TemplateID",$id)->where("CompanyID",$companyID)->where('JobTypeID',$JobTypeID)->count();
        if( intval($hasInCronLog) > 0 ){
            return true;
        }else{
            return false;
        }
    }
    public static function getTemplateArray($data=array(),$CompanyID=0){
        $select =  isset($data['select'])?$data['select']:1;
        unset($data['select']);
        
        $CompanyID = $CompanyID>0 ? $CompanyID : User::get_companyID();
        $data['CompanyID']=$CompanyID;

        $language_arr = Translation::getLanguageDropdownIdList();

        if(isset($data['LanguageID']) && !empty($data['LanguageID'])){
            $language_arr=[
                $data['LanguageID'] => $language_arr[$data['LanguageID']]
            ];
        }
        $result=array();
        
        foreach($language_arr as $key=>$value){

            $data['LanguageID']=$key;

            $EmailTemplate = EmailTemplate::where($data);

            if(!isset($data['UserID'])){
                $EmailTemplate->whereNull('UserID');
            }
            $EmailTemplate->where('StaticType',0);
            $row = $EmailTemplate->select(array('TemplateID', 'TemplateName'))->orderBy('TemplateName')->lists('TemplateName','TemplateID');

            if(count($row)){
                $result[$value]=$row;
            }
        }

        if(!empty($result) && $select==1){
            $result = array(""=> "Select")+$result;
        }
        return $result;
    }

    public static function getDefaultSystemTemplate($SystemType){
       return  EmailTemplate::where(array('SystemType'=>$SystemType))->pluck('TemplateID');
    }
	
	public static function GetUserDefinedTemplates($select = 1,$language_id=""){
		$select =  isset($select)?$select:1;

        $result=array();
        $language_arr = Translation::getLanguageDropdownIdList();

        foreach($language_arr as $key=>$value){
            $row=  EmailTemplate::where(array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE,"CompanyID"=>User::get_companyID(), "LanguageID" => $key))->whereNull('UserID')->select(["TemplateID","TemplateName"])->lists('TemplateName','TemplateID');
            if(count($row)){
                $result[$value]=$row;
            }
        }
        if(!empty($result) && $select==1){
            $result = array(""=> "Select")+$result;
        }
        return $result;
    }

    public static function getSystemTypeArray($select = 1){
        $result=EmailTemplate::where('SystemType', "!=", "")->where('Status',1)->select('SystemType')->distinct()->orderBy('SystemType')->lists('SystemType', 'SystemType');

        if(!empty($result) && $select==1){
            $result = array(""=> "Select")+$result;
        }
        return $result;
    }

    public static function getSystemEmailTemplate($companyID, $slug,$languageID=""){
        if(empty($languageID)){
            $languageID=Translation::$default_lang_id;
        }

        $emailtemplate=EmailTemplate::where(["SystemType"=>$slug, "LanguageID"=>$languageID, "CompanyID"=>$companyID, 'Status'=>1])->first();
        if(empty($emailtemplate)){
            $emailtemplate=EmailTemplate::where(["SystemType"=>$slug, "LanguageID"=>Translation::$default_lang_id, "CompanyID"=>User::get_companyID(), 'Status'=>1])->first();
        }
        
        return $emailtemplate;
    }

    public static function getInvoiceTemplateArray(){
        
            $CompanyID = User::get_companyID();
            $EmailTemplate = EmailTemplate::where('StaticType',1)->where('CompanyID', $CompanyID)
            ->where('TemplateName','like', '%'.self::INVOICETEMPLATE.'%');
            $row = $EmailTemplate->select(array('TemplateID', 'SystemType'))->orderBy('TemplateID')->lists('SystemType','TemplateID');
            if(count($row)>=1)
            {
                $row = array(""=> "Select")+$row;
            }
            return $row;
    }
}