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


        public static function getDIDCategory(){

                $compantID = User::get_companyID();
                $where = ['CompanyID'=>$compantID];
                $DidCategoryTables = DIDCategory::select(['CategoryName','DIDCategoryID'])->where($where)->get();
                return $DidCategoryTables;

        }


    public static function getCategoryDropdownIDList($CompanyID=0)
    {
        $CompanyID = $CompanyID>0?$CompanyID : User::get_companyID();
        $result = self::where(["CompanyID" => $CompanyID])->select(array('CategoryName', 'DIDCategoryID'))->orderBy('CategoryName')->lists('CategoryName', 'DIDCategoryID');
        $row = array("" => "Select");
        if (!empty($result)) {
            $row = array("" => "Select") + $result;
        }
        return $row;
    }

    public static function getCategoryDropdownIDListWithoutCompanyID()
    {
        $result = self::select(array('CategoryName', 'DIDCategoryID'))->orderBy('CategoryName')->lists('CategoryName', 'DIDCategoryID');
        $row = array("" => "Select");
        if (!empty($result)) {
            $row = array("" => "Select") + $result;
        }
        return $row;
    }

    static public function checkForeignKeyById($id) {
        $hasAccountApprovalList = RateTable::where("DIDCategoryID",$id)->count();
        if(!intval($hasAccountApprovalList) > 0){
            return true;
        }else{
            return false;
        }
    }

}