<?php
class PBX{
    private static $config = array();
    private static $dbname1 = 'asteriskcdrdb';

   public function __construct($CompanyGatewayID){
       $setting = GatewayAPI::getSetting($CompanyGatewayID,'PBX');
       foreach((array)$setting as $configkey => $configval){
           if($configkey == 'password'){
               self::$config[$configkey] = Crypt::decrypt($configval);
           }else{
               self::$config[$configkey] = $configval;
           }
       }
       if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
           extract(self::$config);
           Config::set('database.connections.pbxmysql.host',$dbserver);
           Config::set('database.connections.pbxmysql.database',self::$dbname1);
           Config::set('database.connections.pbxmysql.username',$username);
           Config::set('database.connections.pbxmysql.password',$password);

       }
    }
   public static function testConnection(){
       $response = array();
       if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){

           try{
               if(DB::connection('pbxmysql')->getDatabaseName()){
                   $response['result'] = 'OK';
               }
           }catch(Exception $e){
               $response['faultString'] =  $e->getMessage();
               $response['faultCode'] =  $e->getCode();
           }
       }
       return $response;
   }

}