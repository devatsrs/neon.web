<?php
class Porta{
    private static $config = array();
    private static $cli;
    private static $timeout=0; /* 60 seconds timeout */

   public function __construct($CompanyGatewayID){
       $setting = GatewayAPI::getSetting($CompanyGatewayID,'Porta');
       foreach((array)$setting as $configkey => $configval){
           if($configkey == 'password'){
               self::$config[$configkey] = Crypt::decrypt($configval);
           }else{
               self::$config[$configkey] = $configval;
           }
       }
       if(count(self::$config) && isset(self::$config['api_url']) && isset(self::$config['password'])){
           self::$cli =  new Curl();
       }
    }
   public static function testConnection(){
       $response = array();
       if(count(self::$config) && isset(self::$config['api_url']) && isset(self::$config['password'])){
           $api_url = self::$config['api_url'].'/GetCustomersShortInfo/'.self::$config['password'].'/?format=json';
           self::$cli->get($api_url);
           if(isset(self::$cli->response) && self::$cli->response != '') {
                   $ResponseArray = json_decode(self::$cli->response, true);
                   if(!empty($ResponseArray) && isset($ResponseArray['CustomerRes'])) {
                       $response['result'] = 'OK';
                   }
           }else if(isset(self::$cli->error_message) && isset(self::$cli->error_code)){
               $response['faultString'] =  self::$cli->error_message;
               $response['faultCode'] =  self::$cli->error_code;
           }
           self::$cli->close();
       }
       return $response;
   }
    public static function listAccounts($addparams=array()){

    }
    public static function getAccountCDRs($addparams=array()){

    }
    public static function listVendors($addparams=array()){

    }

}