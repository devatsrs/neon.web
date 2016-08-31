<?php
class DestinationGroup extends \Eloquent
{

    protected $guarded = array("DestinationGroupID");

    protected $table = 'tblDestinationGroup';

    protected $primaryKey = "DestinationGroupID";

    public $timestamps = false; // no created_at and updated_at


    public static function checkForeignKeyById($id) {

        $hasInDestinationSet = DestinationGroupSet::where("DestinationGroupID",$id)->count();
        if( intval($hasInDestinationSet) > 0 ){
            return true;
        }else{
            return false;
        }

    }
    public static function getDropdownIDList($DestinationGroupSetID){
        $CompanyId = User::get_companyID();
        $DropdownIDList = DestinationGroup::where(array("CompanyID"=> $CompanyId,'DestinationGroupSetID'=>$DestinationGroupSetID))->lists('Name', 'DestinationGroupID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;


        return $DropdownIDList;
    }
    public static function getName($DestinationGroupID){
        return DestinationGroup::where("DestinationGroupID",$DestinationGroupID)->pluck('Name');
    }
}