<?php

class DIDCategory extends \Eloquent
{
    protected $guarded = array("DIDCategoryID");

    protected $table = 'tblDIDCategory';

    protected $primaryKey = "ServiceTemplateId";

    public static $rules = array(
    );


    public static function getDIDCategory(){
        $compantID = User::get_companyID();
        $where = ['CompanyID'=>$compantID];
        $DidCategoryTables = DIDCategory::select(['CategoryName','DIDCategoryID'])->where($where)->get();
        return $DidCategoryTables;
    }

}