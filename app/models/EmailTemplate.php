<?php

class EmailTemplate extends \Eloquent {

    protected $guarded = array("TemplateID");
    protected $table = 'tblEmailTemplate';
    protected  $primaryKey = "TemplateID";
    const ACCOUNT_TEMPLATE =1;
    const INVOICE_TEMPLATE =2;
    const RATESHEET_TEMPLATE = 3;
	const PRIVACY_ON = 1;
    const PRIVACY_OFF = 0;
	
    public static $privacy = [0=>'All User',1=>'Only Me'];
    public static $Type = [0=>'Select Template Type',self::ACCOUNT_TEMPLATE=>'Account',self::INVOICE_TEMPLATE=>'Billing',self::RATESHEET_TEMPLATE=>'Rate sheet'];

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
    public static function getTemplateArray($data=array()){
        $data['CompanyID']=User::get_companyID();
        $EmailTemplate = EmailTemplate::where($data);
        if(!isset($data['UserID'])){
            $EmailTemplate->whereNull('UserID');
        }
        $row = $EmailTemplate->select(array('TemplateID', 'TemplateName'))->orderBy('TemplateName')->lists('TemplateName','TemplateID');

        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }

    public static function getDefaultSystemTemplate($SystemType){
       return  EmailTemplate::where(array('SystemType'=>$SystemType))->pluck('TemplateID');
    }
}