<?php
class RoutingProfiles extends \Eloquent {
    protected $guarded = array("RoutingProfileID");
    protected $table = "tblRoutingProfile";
    protected $primaryKey = "RoutingProfileID";
    protected $connection = 'sqlsrvrouting';
    protected $fillable = array(
        'CompanyID','Name','Description','RoutingPolicy','Status','created_at'
    );

    public static $rules = array(
        'Name'=>'required',
    );

    static public function checkForeignKeyById($id) {
        /*
         * Tables To Check Foreign Key before Delete.
         * */
        $hasInAccount = Account::where("RoutingProfileID",$id)->count();
        if( intval($hasInAccount) > 0 ){
            return true;
        }else{
            return false;
        }
    }
    public static function getRoutingCategory($CompanyID){
        $RoutingCategory = RoutingCategory::where('CompanyID', $CompanyID)->select('Name', 'RoutingCategoryID')->orderBy('Name','Asc')->lists('Name', 'RoutingCategoryID');
        return $RoutingCategory;
    }
    public static function getRoutingProfile($CompanyID){
        $RoutingProfile = RoutingProfiles::where('CompanyID', $CompanyID)->select('Name', 'RoutingProfileID')->orderBy('Name','Asc')->lists('Name', 'RoutingProfileID');
        return $RoutingProfile;
    }
    public static function getVendorConnection($CompanyID){
        $VendorConnection = VendorConnection::where(["CompanyID"=> $CompanyID,"Active"=>1])->select('Name', 'VendorConnectionID')->orderBy('Name','Asc')->lists('Name', 'VendorConnectionID');
        return $VendorConnection;
    }
    public static function clearCache(){
    }
    
}