<?php

class ItemType extends \Eloquent {

    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('ItemTypeID');
    protected $table = 'tblItemType';
    public  $primaryKey = "ItemTypeID"; //Used in BasedController
    static protected  $enable_cache = false;
    public static $cache = ["itemtype_dropdown1_cache"];

    static public function checkForeignKeyById($id) {
        $hasAccountApprovalList = Product::where("ItemTypeID",$id)->count();
        if( intval($hasAccountApprovalList) > 0){
            return true;
        }else{
            return false;
        }
    }

    public static function getItemTypeDropdownList($CompanyID=0){

        //Items
        if (self::$enable_cache && Cache::has('itemtype_dropdown1_cache')) {
            $admin_defaults = Cache::get('itemtype_dropdown1_cache');
            self::$cache['itemtype_dropdown1_cache'] = $admin_defaults['itemtype_dropdown1_cache'];
        } else {
            $CompanyID = $CompanyID>0 ? $CompanyID : User::get_companyID();
            $Where = ["CompanyId"=>$CompanyID];
            self::$cache['itemtype_dropdown1_cache'] = ItemType::where($Where)->where("Active",1)->orderby('title')->lists('title','ItemTypeID');
            Cache::forever('itemtype_dropdown1_cache', array('itemtype_dropdown1_cache' => self::$cache['itemtype_dropdown1_cache']));
        }
        $list = array();
        $list = self::$cache['itemtype_dropdown1_cache'];
        $list[""] = "Select";

        return  array('0' => "Select")+ self::$cache['itemtype_dropdown1_cache'];

    }

    //not using
    public static function validate($data){
        $rules = array(
            'CompanyID' => 'required',
            'Name' => 'required',
            'Amount' => 'required|numeric',
            'Description' => 'required',
            'Code' => 'required'
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
    }

    public static function getProductName($id,$ProductType){
        if( $id>0 && $ProductType == self::ITEM ){
            $Product = Product::find($id);
            if(!empty($Product)){
                return $Product->Name;
            }
        }
        if( $id == 0 && $ProductType == self::USAGE ){
            return 'Usage';
        }
        if( $id > 0 && $ProductType == self::SUBSCRIPTION ){
            return BillingSubscription::getSubscriptionNameByID($id);
        }
    }

    public static function clearCache(){

        Cache::flush("product_dropdown1_cache");

    }

}