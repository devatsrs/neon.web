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

        $hasInAccount = RoutingProfiles::where("RoutingCategoryID",$id)->count();
        if( intval($hasInAccount) > 0 ){
            return true;
        }else{
            return false;
        }

    }
	
    public static function getCategoryDropdownIDList($CompanyID){
        $result = self::where(["CompanyID"=>$CompanyID])->select(array('Name', 'RoutingCategoryID'))->orderBy('Name')->lists('Name', 'RoutingCategoryID');
        $row = array(""=> "Select");
        if(!empty($result)){
            $row = array(""=> "Select")+$result;
        }
        return $row;
    }

    public static function clearCache(){
    }
}