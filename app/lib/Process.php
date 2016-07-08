<?php

/**
 * Created by PhpStorm.
 * User: deven
 * Date: 06/07/2016
 * Time: 6:55 PM
 */

/* An easy way to keep in track of external processes.
* Ever wanted to execute a process in php, but you still wanted to have somewhat controll of the process ? Well.. This is a way of doing it.
* @compability: Linux only. (Windows does not work).
* @author: Peec
*/

class Process
{
    private $pid;
    private $command;

    public function __construct($cl=false){
        if ($cl != false){
            $this->command = $cl;
            $this->runCom();
        }
    }
    private function runCom(){
        $command = 'nohup '.$this->command.' > /dev/null 2>&1 & echo $!';
        exec($command ,$op);
        $this->pid = (int)$op[0];
    }

    public function setPid($pid){
        $this->pid = $pid;
    }

    public function getPid(){
        return $this->pid;
    }

    public function status(){
        $command = 'ps -p '.$this->pid;
        exec($command,$op);
        if (!isset($op[1])){
            return false;
        }
        else {
            return true;
        }
    }

    public function start(){
        if ($this->command != '')$this->runCom();
        else return true;
    }

    public function isrunning($pid) {

        //@TODO: checkout for windows system
        //http://lifehacker.com/362316/use-unix-commands-in-windows-built-in-command-prompt
        $pids_ = explode(PHP_EOL, `ps -e | grep php | awk '{print $1}'`);

        if( !empty($pid) && $pid > 0 && in_array($pid, $pids_)) {
            Log::info(" Running pids " . print_r($pids_,true));
            return TRUE;
        }
        return FALSE;
    }

    public function stop(){

        if(getenv("APP_OS") == "Linux"){
            $command = 'kill -9 '.$this->pid;
        }else{
            $command = 'Taskkill /PID '.$this->pid.' /F';
        }


        if($this->isrunning($this->pid)){

            $DetailOutput=$return_var="";

            exec($command,$DetailOutput,$return_var);

            \Illuminate\Support\Facades\Log::info("Kill Command " . $command);
            \Illuminate\Support\Facades\Log::info("DetailOutput " . print_r($DetailOutput,true));
            \Illuminate\Support\Facades\Log::info("return_var " . $return_var);

        }else{
            return false;
        }


        if ($this->status() == false) {
            return true;
        }
        else {
            return false;
        }
    }

}