<?php

class DynamicFieldsValue extends \Eloquent {

    protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('DynamicFieldsValueID');
    protected $table = 'tblDynamicFieldsValue';
    public  $primaryKey = "DynamicFieldsValueID"; //Used in BasedController
    static protected  $enable_cache = false;

    const BARCODE_SLUG = 'BarCode';

    public static function getDynamicColumnValuesByProductID($DynamicFieldsID,$ProductID) {
        $CompanyID = User::get_companyID();

        return DynamicFieldsValue::where('CompanyID',$CompanyID)
                                    ->where('ParentID',$ProductID)
                                    ->where('DynamicFieldsID',$DynamicFieldsID)
                                    ->get();
    }

    public static function deleteDynamicColumnValuesByProductID($CompanyID,$ProductID,$DynamicFieldsIDs) {
        return DynamicFieldsValue::where('CompanyID',$CompanyID)
                                    ->where('ParentID',$ProductID)
                                    ->whereIn('DynamicFieldsID',$DynamicFieldsIDs)
                                    ->delete();
    }
}