<?php

class Gateway extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('GatewayID');

    protected $table = 'tblGateway';

    protected  $primaryKey = "GatewayID";

    const GATEWAY_VOS = 'VOS';
    const GATEWAY_VOS5000 = 'VOS5000';
    const GATEWAY_SippySFTP = 'SippySFTP';
    const GATEWAY_FTP = 'FTP';

    const FTP_CLASSIC = "classic";
    const FTP_OVER_TLS_SSL = "ftps"; // FTP over TLS/SSL
    const SSH_FILE_TRANSFER = "ssh"; // SSH File Transfer Protocol

    public static $protocol_type = [self::FTP_CLASSIC=>"FTP CLASSIC",self::FTP_OVER_TLS_SSL=>"FTP OVER TLS/SSL",self::SSH_FILE_TRANSFER=>"SSH"];

    public static $ftp_array = [self::GATEWAY_VOS,self::GATEWAY_SippySFTP,self::GATEWAY_FTP,self::GATEWAY_VOS5000];

    public  static  function getGatewayListID(){
        $row = Gateway::where(array('Status'=>1))->lists('Title', 'GatewayID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
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
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }
	
	public static function getGatWayList(){
        //$data['CompanyID']=User::get_companyID();
        $row = Gateway::select(array('Name', 'GatewayID','Title','Status'))->where(array('Status'=>1))->orderBy('Name')->get();
        return $row;
    }
}