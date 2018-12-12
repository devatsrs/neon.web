<?php
/**
 * Created by PhpStorm.
 * User: vasim seta
 * Date: 03/12/2018
 * Time: 8:25 PM
 */

class DidCategory extends \Eloquent{

    protected $guarded = array('');
    protected $table = 'tblDIDCategory';
    protected $primaryKey = "DIDCategoryID";

    public static function getCategoryDropdownIDList($CompanyID)
    {
        $result = self::where(["CompanyID" => $CompanyID])->select(array('CategoryName', 'DIDCategoryID'))->orderBy('CategoryName')->lists('CategoryName', 'DIDCategoryID');
        $row = array("" => "Select");
        if (!empty($result)) {
            $row = array("" => "Select") + $result;
        }
        return $row;
    }

    public static function getDIDCategoryDropDownList($CompanyID=0){
        $company_id = $CompanyID>0?$CompanyID : User::get_companyID();
        $row = DIDCategory::where(array('CompanyID'=>$company_id))->lists('CategoryName', 'DIDCategoryID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;

    }

    static public function checkForeignKeyById($id) {
        $hasAccountApprovalList = DIDCategory::where("DIDCategoryID",$id)->count();
        if( intval($hasAccountApprovalList) > 0){
            return true;
        }else{
            return false;
        }
    }

}