<?php

class VendorFileUploadTemplate extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblVendorFileUploadTemplate';
    protected $primaryKey = "VendorFileUploadTemplateID";

    public static function getTemplateIDList(){
        $row = VendorFileUploadTemplate::where(['CompanyID'=>User::get_companyID()])->orderBy('Title')->lists('Title', 'VendorFileUploadTemplateID');
        $row = array(""=> "Select")+$row;
        return $row;
    }

}