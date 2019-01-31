<?php

class Package extends \Eloquent
{
    protected $guarded = array("PackageId");

    protected $table = 'tblPackage';

    protected $primaryKey = "PackageId";

    public static $rules = array(
        'Name' =>      'required|unique:tblPackage',
        'CurrencyId' =>  'required',
        'RateTableId' => 'required',
    );


    public static function getDropdownIDList(){
        $DropdownIDList = Package::lists('Name', 'PackageID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

    public static function getAllServices(){
        $Packages = Package::get();
        return $Packages;
    }

    public static function getServiceNameByID($PackageID){
        return Package::where('PackageId',$PackageID)->pluck('Name');
    }

}