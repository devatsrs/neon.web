<?php

class DynamicFields extends \Eloquent {

    protected $guarded = array('DynamicFieldsID');
    protected $table = 'tblDynamicFields';
    public  $primaryKey = "DynamicFieldsID"; //Used in BasedController
    public $timestamps = false;
    static protected  $enable_cache = false;
    public static $cache = ["itemtype_dropdown1_cache"];

    public function fieldOptions() {

        return $this->hasMany('DynamicFieldsDetail', 'DynamicFieldsID', 'DynamicFieldsID');
    }

    public function fieldUniqueOption() {

        return $this->hasOne('DynamicFieldsDetail', 'DynamicFieldsID', 'DynamicFieldsID')->where('FieldType','is_unique');
    }

    public static function getDomTypeDropdownList($CompanyID=0){

        //Items
        if (self::$enable_cache && Cache::has('domtype_dropdown1_cache')) {
            $admin_defaults = Cache::get('domtype_dropdown1_cache');
            self::$cache['domtype_dropdown1_cache'] = $admin_defaults['domtype_dropdown1_cache'];
        } else {
            $CompanyID = $CompanyID>0 ? $CompanyID : User::get_companyID();
            $Where = ["CompanyId"=>$CompanyID];
            self::$cache['domtype_dropdown1_cache'] = DynamicFields::where($Where)->where("Status",1)->lists('FieldDomType','FieldDomType');
            Cache::forever('domtype_dropdown1_cache', array('domtype_dropdown1_cache' => self::$cache['domtype_dropdown1_cache']));
        }
        $list = array();
        $list = self::$cache['domtype_dropdown1_cache'];
        $list[""] = "Select";

        return  array('' => "Select")+ self::$cache['domtype_dropdown1_cache'];

    }

    static public function checkForeignKeyById($id) {
        $hasDynamicFields = DB::table('tblDynamicFieldsValue')->where("DynamicFieldsID",$id)->count();
        if( intval($hasDynamicFields) > 0){
            return true;
        }else{
            return false;
        }
    }

}