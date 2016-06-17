<?php
/**
 * Created by PhpStorm.
 * User: srs2
 * Date: 19/09/2015
 * Time: 02:27 
 */

class FileUploadTemplate extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblFileUploadTemplate';
    protected $primaryKey = "FileUploadTemplateID";
    const TEMPLATE_CDR = 1;
    const TEMPLATE_VENDORCDR = 2;
    const TEMPLATE_Account = 3;
    const TEMPLATE_Leads = 4;
    const TEMPLATE_DIALPLAN = 5;

    public static function getTemplateIDList($Type){
        $row = FileUploadTemplate::where(['CompanyID'=>User::get_companyID(),'Type'=>$Type])->orderBy('Title')->lists('Title', 'FileUploadTemplateID');
        $row = array(""=> "Select an upload Template")+$row;
        return $row;
    }

}