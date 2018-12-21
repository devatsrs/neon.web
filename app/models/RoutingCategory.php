<?php
class RoutingCategory extends \Eloquent {
    protected $guarded = array("RoutingCategoryID");
    protected $table = "tblRoutingCategory";
    protected $primaryKey = "RoutingCategoryID";
    protected $connection = 'sqlsrvrouting';
    protected $fillable = array(
        'CompanyID','Name','Description','created_at'
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
	
    static public function checkCategoryName($Name) {
        /*
         * Tables To Check Foreign Key before Delete.
         * */
        $hasInCategory = RoutingCategory::where("Name",$Name)->count();
        if( intval($hasInCategory) > 0 ){
            return false;
        }else{
            return true;
        }
    }

    static public function checkCategoryNameAndID($Name,$id) {
        /*
         * Tables To Check Foreign Key before Delete.
         * */
        $hasInCategory = RoutingCategory::where("Name",$Name)->where("RoutingCategoryID",'!=', $id)->count();
        if( intval($hasInCategory) > 0 ){
            return false;
        }else{
            return true;
        }
    }

    public static function getCategoryDropdownIDList($CompanyID=0,$reverse=0){
        $CompanyID = $CompanyID > 0 ? $CompanyID : User::get_companyID();
        $result = self::where(["CompanyID"=>$CompanyID])->select(array('Name', 'RoutingCategoryID'))->orderBy('Name');
        if($reverse == 1) {
            $result = $result->lists('RoutingCategoryID', 'Name');
        } else {
            $result = $result->lists('Name', 'RoutingCategoryID');
        }
        $row = array(""=> "Select");
        if(!empty($result)){
            $row = array(""=> "Select")+$result;
        }
        return $row;
    }

    public static function clearCache(){
    }
}