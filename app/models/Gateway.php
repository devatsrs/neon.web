<?php

class Gateway extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('GatewayID');

    protected $table = 'tblGateway';

    protected  $primaryKey = "GatewayID";

    public  static  function getGatewayListID(){
        $row = Gateway::where(array('Status'=>1))->lists('Title', 'GatewayID');
        if(!empty($row)){
            $row = array(""=> "Select a Gateway")+$row;
        }
        return $row;
    }
    public static function getGatewayConfig($GatewayID){
        $GatewayConfigs = GatewayConfig::join('tblGateway','tblGatewayConfig.GatewayID','=','tblGateway.GatewayID')->where(array('tblGatewayConfig.GatewayID'=>$GatewayID,'tblGatewayConfig.Status'=>1))->select(array('tblGatewayConfig.Title','tblGatewayConfig.Name'))->get();
        $gatewayconfig = array();
        foreach($GatewayConfigs as $GatewayConfig){
            $gatewayconfig[$GatewayConfig->Name] = $GatewayConfig->Title;
        }
        return $gatewayconfig;
    }
    public static function getGatewayID($gatewayname){
       return Gateway::where(array('Status'=>1,'Name'=>$gatewayname))->pluck('GatewayID');
    }
    public static function getGatewayName($GatewayID){
        return Gateway::where(array('Status'=>1,'GatewayID'=>$GatewayID))->pluck('Name');
    }

    public static function getGatWayIDList(){
        //$data['CompanyID']=User::get_companyID();
        $row = Gateway::select(array('Name', 'GatewayID'))->orderBy('Name')->lists('Name', 'GatewayID');
        if(!empty($row)){
            $row = array(""=> "Select a Gateway")+$row;
        }
        return $row;
    }
}