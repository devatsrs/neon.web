<?php

class Product extends \Eloquent {

    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('ProductID');
    protected $table = 'tblProduct';
    public  $primaryKey = "ProductID"; //Used in BasedController
    static protected  $enable_cache = false;
    public static $cache = ["product_dropdown1_cache"];
    const ITEM = 1;
    const USAGE = 2;
    const SUBSCRIPTION = 3;
    public static $ProductTypes = ["item"=>self::ITEM, "usage"=>self::USAGE,"subscription"=>self::SUBSCRIPTION];
    public static $TypetoProducts = [self::ITEM => "item", self::USAGE => "usage", self::SUBSCRIPTION =>"subscription"];

    static public function checkForeignKeyById($id) {
        $hasAccountApprovalList = InvoiceDetail::where("ProductID",$id)->count();
        if( intval($hasAccountApprovalList) > 0){
            return true;
        }else{
            return false;
        }
    }

    public static function getProductDropdownList(){

        //Items
        if (self::$enable_cache && Cache::has('product_dropdown1_cache')) {
            $admin_defaults = Cache::get('product_dropdown1_cache');
            self::$cache['product_dropdown1_cache'] = $admin_defaults['product_dropdown1_cache'];
        } else {
            $CompanyId = User::get_companyID();
            self::$cache['product_dropdown1_cache'] = Product::where("CompanyId",$CompanyId)->where("Active",1)->lists('Name','ProductID');
            self::$cache['product_dropdown1_cache'] = self::$cache['product_dropdown1_cache'];
        }
        $list = array();
        $list = self::$cache['product_dropdown1_cache'];
        $list[""] = "Select a Product";
        //$list["Usage"] = array("Usage");
        //$list["Subscription"] = BillingSubscription::getSubscriptionsList();

        return  array('' => "Select a Product")+ self::$cache['product_dropdown1_cache'];

    }

    public static function validate($data){
        $rules = array(
            'CompanyID' => 'required',
            'Name' => 'required',
            'Amount' => 'required',
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