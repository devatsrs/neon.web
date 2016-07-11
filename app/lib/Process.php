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
        //@TODO: need to fix for Window
        $command = 'nohup '.$this->command.' > /dev/null 2>&1 & echo $!';

        $op = RemoteSSH::run([$command]);
        //exec($command ,$op);
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
        //exec($command,$op);
        $op = RemoteSSH::run([$command]);

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

        $command ="ps -e | grep php | awk '{print $1}'";
            //$pids_ = explode(PHP_EOL, `ps -e | grep php | awk '{print $1}'`);
        $op = RemoteSSH::run([$command]);
        $pids_ = explode(PHP_EOL,$op);

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

            $op = RemoteSSH::run([$command]);
            //exec($command,$DetailOutput,$return_var);

            \Illuminate\Support\Facades\Log::info("Kill Command " . $command);
            \Illuminate\Support\Facades\Log::info("DetailOutput " . print_r($op,true));
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