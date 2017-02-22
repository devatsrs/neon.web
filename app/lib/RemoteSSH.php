<?php

class RemoteSSH{
    private static $config = array();
    public static $uploadPath = '';

    public static function setConfig(){

        $Configuration = CompanyConfiguration::getConfiguration();
        if(!empty($Configuration)){
            self::$config = json_decode($Configuration['SSH'],true);
            self::$uploadPath = $Configuration['UPLOAD_PATH'];
        }
        if(count(self::$config) && isset(self::$config['host']) && isset(self::$config['username']) && isset(self::$config['password'])){
            Config::set('remote.connections.production',self::$config);
        }
    }

    /** Execute command and return PID
     * @param array $commands
     * @return array
     */
    public static function run($commands = array()){

        self::setConfig();

        \Illuminate\Support\Facades\Log::info($commands);

        $output = array();
        \Illuminate\Support\Facades\SSH::run($commands, function($line) use(&$output) {
            $output =  explode(PHP_EOL,$line);
        });

        /*
        [0] => Array
        (
            [0] => 105901
        )
         */

        /*
         *    [0] => Array
        (
            [0] => 6258
            [1] => 102887
            [2] => 104458
            [3] => 104765
            [4] => 104783
            [5] => 105178
            [6] => 105346
            [7] => 105497
            [8] => 105819
            [9] => 105847
            [10] => 105901
            [11] =>
        )

         */
        if( count($output) == 1 && is_numeric($output[0])){
            // PID
            return $output[0];
        }
        else{
            // Other OUTPUT
            return $output;
        }

    }
}