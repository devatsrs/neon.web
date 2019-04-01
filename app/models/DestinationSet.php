<?php

class DestinationSet extends \Eloquent {

    protected $fillable = [];
    protected $guarded = array('SippyVendorDestiMapID');
    protected $table = 'tblDestinationSet';
    public  $primaryKey = "SippyVendorDestiMapID"; //Used in BasedController
    static protected  $enable_cache = false;
   // public static $cache = ["itemtype_dropdown1_cache"];

    public static function getDestinationSet(){
        $data=array();
        $data['CompanyID']=User::get_companyID();
        $row = DestinationSet::where($data)->select(array('Name', 'DestinationSetID'))->orderBy('Name')->lists('Name', 'DestinationSetID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }

}