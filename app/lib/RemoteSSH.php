<?php

class RemoteSSH{
    private static $config = array();
    public static $uploadPath = '';
    private static $lines = array();


    public static function setConfig(){

        $Configuration = CompanyConfiguration::getConfiguration();
        if(!empty($Configuration)){
            self::$config = json_decode($Configuration['SSH'],true);
            self::$uploadPath = $Configuration['UPLOADPATH'];
        }
        if(count(self::$config) && isset(self::$config['host']) && isset(self::$config['username']) && isset(self::$config['password'])){
            Config::set('remote.connections.production',self::$config);
        }
    }

    public static function run($commands = array()){

        self::setConfig();
        \Illuminate\Support\Facades\Log::info($commands);
        $op = \Illuminate\Support\Facades\SSH::run($commands, function($line) {
            self::$lines = $line.PHP_EOL;
        });

        return self::$lines;


    }
}