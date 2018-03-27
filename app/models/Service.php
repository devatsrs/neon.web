<?php

class Service extends \Eloquent
{
    protected $guarded = array("ServiceID");

    protected $table = 'tblService';

    protected $primaryKey = "ServiceID";

    public static $rules = array(
        'ServiceName' =>      'required|unique:tblService',
        'CompanyID' =>  'required',
        'ServiceType' => 'required',
        // 'AreaPrefix' => 'required',
        // 'Prefix' =>     'required',
        'Status' =>     'between:0,1',
    );

    public static $ServiceType = array(""=>"Select", "voice"=>"Voice");

    public static function getDropdownIDList($CompanyID=0){
        if($CompanyID==0){
            $CompanyID = User::get_companyID();
        }
        $DropdownIDList = Service::where(array("CompanyID"=>$CompanyID,"Status"=>1))->lists('ServiceName', 'ServiceID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

    public static function getAllServices($CompanyID){
        //$Services = Service::where(array("CompanyID"=>$CompanyID,"Status"=>1))->get();
        $Services = Service::where(array("Status"=>1))->get();
        return $Services;
    }

    public static function getServiceNameByID($ServiceID){
        return Service::where('ServiceID',$ServiceID)->pluck('ServiceName');
    }

    public static function  checkForeignKeyById($ServiceID){
        $AccountService = AccountService::where(array('ServiceID'=>$ServiceID))->count();
        if(!empty($AccountService)){
            return false;
        }
        return true;


    }

}