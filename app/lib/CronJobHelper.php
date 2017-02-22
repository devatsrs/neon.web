<?php

/**
 * Created by PhpStorm.
 * User: deven
 * Date: 23/06/2016
 * Time: 1:12 PM
 */
class CronJobHelper
{

    /** Execute Cron Job command
     * @param $Command
     * @param $CompanyID
     * @param $CronJobID
     * @return bool
     */
    public function start($Command,$CompanyID,$CronJobID) {

        $success = false;
        if( !empty($Command) ) {
            $command = CompanyConfiguration::get("PHP_EXE_PATH"). " " . CompanyConfiguration::get("RM_ARTISAN_FILE_LOCATION") . " " . $Command . " " . $CompanyID . " " . $CronJobID ;
            if (getenv('APP_OS') == 'Linux') {
                pclose(popen( $command . " &", "r"));
                $success=true;
            } else {
                pclose(popen("start /B " . $command, "r"));
                $success=true;
            }
        }
        return $success;

    }

    /** Terminate cronjob command by PID
     * @param $PID
     * @return bool
     */
    public function terminate($PID){

        if(getenv("APP_OS") == "Linux"){
            $KillCommand = 'kill -9 '.$PID;
        }else{
            $KillCommand = 'Taskkill /PID '.$PID.' /F';
        }

        $ReturnStatus = exec($KillCommand,$DetailOutput);

        return true;

    }
}