<?php
class RoutingProfileCategory extends \Eloquent {
    protected $guarded = array("RoutingProfileCategoryID");
    protected $table = "tblRoutingProfileCategory";
    protected $primaryKey = "RoutingProfileCategoryID";
    protected $connection = 'sqlsrvrouting';
    protected $fillable = array(
        'RoutingProfileID','RoutingCategoryID','Order','RoutingProfileCategoryID'
    );

    public static $rules = array(
        'RoutingProfileID'=>'required',
    );

    public static function getRoutingProfileCategory($id){
       // $result = RoutingProfileCategory::where(["RoutingProfileID"=>$id])->select(array('RoutingCategoryID'))->orderBy('Order')->lists('RoutingCategoryID');
        
        $result = DB::connection('sqlsrvrouting')->select("SELECT a.RoutingCategoryID , NAME FROM tblRoutingCategory a, tblRoutingProfileCategory b WHERE a.RoutingCategoryID=b.RoutingCategoryID AND b.RoutingProfileID='$id' GROUP BY a.RoutingCategoryID ORDER BY 'b.ORDER'");

        
        return $result;
    }
    public static function clearCache(){
    }
}