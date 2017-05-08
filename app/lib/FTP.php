<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
class FTP{
    private static $config = array();
    private static $sippy_file_location = "";

    public static $customer_cdr_file_name = "cdrs-thrift.bin.";
    public static $vendor_cdr_file_name = "cdrs_connections-thrift.bin.";

    public function __construct($CompanyGatewayID){
        $setting = GatewayAPI::getSetting($CompanyGatewayID,'FTP');
        foreach((array)$setting as $configkey => $configval){
            if($configkey == 'password'){
                self::$config[$configkey] = Crypt::decrypt($configval);
            }else{
                self::$config[$configkey] = $configval;
            }
        }
        if(count(self::$config) && isset(self::$config['host']) && isset(self::$config['username']) && isset(self::$config['password'])){
            Config::set('remote.connections.production',self::$config);
        }
    }

    public function testConnection(){
        try{
            $response = ['result'=>'OK'];
            //SSH::into('production')->getString(self::$config['cdr_folder']);
            SSH::into('production')->run('ls -l', function($line) {
                //echo $line;
            });
            return $response;
        }catch (Exception $e){
            $response['result'] = 'false';
            $response['faultString'] = $e->getMessage();
            $response['faultCode']  = $e->getCode();
            return $response;
        }
    }
}