<?php

class DIDCategory extends \Eloquent {
    //protected $connection = 'sqlsrv';
    protected $guarded = array("DIDCategoryID");

    protected $table = 'tblDIDCategory';

    protected  $primaryKey = "DIDCategoryID";

    static public function checkForeignKeyById($id) {
        $hasAccountApprovalList = DIDCategory::where("DIDCategoryID",$id)->count();
        if( intval($hasAccountApprovalList) > 0){
            return true;
        }else{
            return false;
        }
    }

}