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
    const ONEOFFCHARGE =4;
    const INVOICE_PERIOD = 5;
    const FIRST_PERIOD = 6;
    public static $ProductTypes = ["item"=>self::ITEM, "usage"=>self::USAGE,"subscription"=>self::SUBSCRIPTION];
    public static $TypetoProducts = [self::ITEM => "item", self::USAGE => "usage", self::SUBSCRIPTION =>"subscription"];
    public static $AllProductTypes = [self::ITEM => "Item", self::USAGE => "Usage", self::SUBSCRIPTION =>"Subscription",self::ONEOFFCHARGE=>'Oneoffcharge',self::INVOICE_PERIOD=>'InvoiceReceived'];

    const DYNAMIC_TYPE = 'product';

    const Customer = 0;
    const Reseller = 1;

    public static $AppliedTo = array(self::Customer=>"Customer",self::Reseller=>"Reseller");
    public static $ALLAppliedTo = array(''=>'Select',self::Customer=>"Customer",self::Reseller=>"Reseller");

    static public function checkForeignKeyById($id) {
        $hasAccountApprovalList = InvoiceDetail::where("ProductID",$id)->count();
        if( intval($hasAccountApprovalList) > 0){
            return true;
        }else{
            return false;
        }
    }

    public static function getProductDropdownList($CompanyID=0,$AppliedTo=0){

        //Items
        if (self::$enable_cache && Cache::has('product_dropdown1_cache')) {
            $admin_defaults = Cache::get('product_dropdown1_cache');
            self::$cache['product_dropdown1_cache'] = $admin_defaults['product_dropdown1_cache'];
        } else {
            $CompanyID = $CompanyID>0 ? $CompanyID : User::get_companyID();
            if($AppliedTo==="All"){
                $Where = ["CompanyId"=>$CompanyID];
            }else{
                $Where = ["CompanyId"=>$CompanyID,"AppliedTo"=>$AppliedTo];
            }
            self::$cache['product_dropdown1_cache'] = Product::where($Where)->where("Active",1)->lists('Name','ProductID');
            Cache::forever('product_dropdown1_cache', array('product_dropdown1_cache' => self::$cache['product_dropdown1_cache']));
        }
        $list = array();
        $list = self::$cache['product_dropdown1_cache'];
        $list[""] = "Select";
        //$list["Usage"] = array("Usage");
        //$list["Subscription"] = BillingSubscription::getSubscriptionsList();

        return  array('' => "Select")+ self::$cache['product_dropdown1_cache'];

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

    public static function getProductByItemType($data=array()){
        $dataarr=array();
        if($data['ItemType']==''){
            $ProductList=Product::where(['CompanyId'=>$data['CompanyID'],'Active'=>1])->where('Quantity','>',0)->get();
        }else{
            $ProductList = DB::connection('sqlsrv2')->table('tblItemType')
                ->join('tblProduct', 'tblItemType.ItemTypeID', '=', 'tblProduct.ItemTypeID')
                ->select('tblProduct.*')
                ->where(['tblItemType.title'=>$data['ItemType'],'tblProduct.CompanyId'=>$data['CompanyID'],'tblProduct.Active'=>1])
                ->where('tblProduct.Quantity','>',0)
                ->get();
        }

        $Type =  Product::DYNAMIC_TYPE;

        foreach($ProductList as $plist){
            $Products=array();
            $ItemTypeID=$plist->ItemTypeID;
            $Products['ProductID']=$plist->ProductID;
            $Products['ItemTypeID']=$ItemTypeID;
            $Products['CompanyId']=$plist->CompanyId;
            $Products['Name']=$plist->Name;
            $Products['Code']=$plist->Code;
            $Products['Description']=$plist->Description;
            $Products['Amount']=$plist->Amount;
            $Products['AppliedTo']=$plist->AppliedTo;
            $Products['Note']=$plist->Note;
            $Products['Buying_price']=$plist->Buying_price;
            $Products['Quantity']=$plist->Quantity;
            $Products['Low_stock_level']=$plist->Low_stock_level;
            $Products['Enable_stock']=$plist->Enable_stock;

            $DynamicFields = DynamicFields::where('Type',$Type)->where('CompanyID',$data['CompanyID'])->where('ItemTypeID',$ItemTypeID)->where('Status','1')->get();
            foreach($DynamicFields as $field){
                $fieldName=$field->FieldName;
               // echo $field->ProductID;die;
                $DynamicFieldsValues = DynamicFieldsValue::getDynamicColumnValuesByProductID($field->DynamicFieldsID,$Products['ProductID']);
                //print_r($DynamicFieldsValues);die;
                if($DynamicFieldsValues->count() > 0){
                    foreach ($DynamicFieldsValues as $DynamicFieldsValue) {
                        $DynamicFieldValue = $DynamicFieldsValue->FieldValue;
                    }
                } else {
                    $DynamicFieldValue = "";
                }
                $Products[$fieldName]=$DynamicFieldValue;
            }
            $dataarr[]=$Products;
        }
        return $dataarr;
    }

}