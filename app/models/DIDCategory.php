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

}