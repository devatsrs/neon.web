<?php

class DestinationGroupSet extends \Eloquent
{
    protected $guarded = array("DestinationGroupSetID");

    protected $table = 'tblDestinationGroupSet';

    protected $primaryKey = "DestinationGroupSetID";

    public $timestamps = false; // no created_at and updated_at

    public static function checkForeignKeyById($id) {


        /** todo implement this function   */
        return true;
    }

    public static function getDropdownIDList(){
        $CompanyId = User::get_companyID();
        $DropdownIDList = DestinationGroupSet::where("CompanyID", $CompanyId)->orderBy('Name')->lists('Name', 'DestinationGroupSetID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;


        return $DropdownIDList;
    }
    public static function getName($DestinationGroupSetID){
        return DestinationGroupSet::where("DestinationGroupSetID",$DestinationGroupSetID)->pluck('Name');
    }


}