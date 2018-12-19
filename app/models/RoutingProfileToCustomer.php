<?php
class RoutingProfileToCustomer extends \Eloquent {
    protected $guarded = array("RoutingProfileToCustomerID");
    protected $table = "tblRoutingProfileToCustomer";
    protected $primaryKey = "RoutingProfileToCustomerID";
    protected $connection = 'sqlsrvrouting';
    protected $fillable = array(
        'CompanyID','RoutingProfileID','AccountID','TrunkID','ServiceID','created_at'
    );

    public static $rules = array(
        'Name'=>'required',
    );
    static public function checkForeignKeyById($id) {
        /*
         * Tables To Check Foreign Key before Delete.
         * */
        $hasInAccount = RoutingProfileCategory::where("RoutingCategoryID",$id)->count();
        if( intval($hasInAccount) > 0 ){
            return true;
        }else{
            return false;
        }

    }
	
    public static function getCategoryDropdownIDList($CompanyID){
        
    }

    public static function clearCache(){
    }
}