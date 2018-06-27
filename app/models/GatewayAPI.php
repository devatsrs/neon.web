<?php

class GatewayAPI extends \Eloquent {
	protected $fillable = [];

    private static $gateway_class = array('Sippy','Porta','PBX','FTP', 'VOS','MOR','CallShop','Streamco','FusionPBX','M2','SippySFTP','VoipNow','VOS5000','VoipMS');

    public static $required_key = array('api_url','BillingTime','cdr_folder','dbserver','host','NameFormat','password','username');

    public static function GatewayMethod($classname,$CompanyGatewayID,$method,$param=array()){
        if(in_array($classname,self::$gateway_class)){
            if(self::checkSetting($classname,$CompanyGatewayID) == 'true'){
                $class =  new $classname($CompanyGatewayID);
               return $class->$method($param);
            }
        }
    }
    public static function getSetting($CompanyGatewayID,$gatewayname){
        $gatewayid = Gateway::getGatewayID($gatewayname);
        if($CompanyGatewayID >0){
            $companysetting =  CompanyGateway::getCompanyGatewayConfig($CompanyGatewayID);
           return (array)json_decode($companysetting);
        }
    }
    public static function checkSetting($gatewayname,$CompanyGatewayID){
        $gatewayid = Gateway::getGatewayID($gatewayname);
        if($gatewayid >0){
            $requrieddata =  Gateway::getGatewayConfig($gatewayid);
            $rules = array();
            foreach($requrieddata as $key => $rowdata){
                if(in_array($key,self::$required_key)) {
                    $rules[$key] = 'required';
                }
            }
            $companysetting =  CompanyGateway::getCompanyGatewayConfig($CompanyGatewayID);
            $data = (array)json_decode($companysetting);
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            return 'true';
        }
    }

    public static function getRules($gatewayid){
        $rules = array();
        if($gatewayid >0){
            $requrieddata =  Gateway::getGatewayConfig($gatewayid);
            foreach($requrieddata as $key => $rowdata){
                if(in_array($key,self::$required_key)) {
                    $rules[$key] = 'required';
                }
            }
        }
        return $rules;
    }

}