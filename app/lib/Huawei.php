<?php
/**
 * Created by PhpStorm.
 * User: BADAL
 * Date: 26-09-2019
 * Time: 12:34 PM
 */

class Huawei {
    private static $config = array();

    public function __construct($CompanyGatewayID){
        $setting = GatewayAPI::getSetting($CompanyGatewayID,'Huawei');
        foreach((array)$setting as $configkey => $configval){
            if($configkey == 'password' || $configkey == 'api_password' || $configkey == 'dbpassword'){
                self::$config[$configkey] = !empty($configval) ? Crypt::decrypt($configval) : '';
            }else{
                self::$config[$configkey] = $configval;
            }
        }
        if(count(self::$config) && isset(self::$config['host']) && isset(self::$config['username']) && isset(self::$config['password'])){
            Config::set('remote.connections.huawei',self::$config);
        }
    }
    public static function testConnection(){
        if(count(self::$config) && isset(self::$config['host']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try {
                $response = ['result'=>'OK'];
                SSH::into('huawei')->run('ls -l', function ($line) {
                    //echo $line;
                });
                return $response;
            } catch (Exception $ex) {
                $response['result'] = 'false';
                $response['faultString'] = $ex->getMessage();
                $response['faultCode']  = $ex->getCode();
                return $response;
            }
        } else {
            $response['result'] = 'false';
            $response['faultString'] = 'No Credentials Set';
            $response['faultCode']  = '00';
            return $response;
        }
    }
}