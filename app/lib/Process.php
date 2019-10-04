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
        $command = 'nohup '.$this->command.'  >/dev/null 2>/dev/null & printf "%u" $!';
        $op = RemoteSSH::run([$command]);
        //exec($command ,$op);
        $this->pid = (int)$op;
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

        if ($op > 0){
            return true ;
        }
        else {
            return false;
        }
    }

    public function start(){

        if ($this->command != ''){
            $this->runCom();
        }
        else {
            return true;
        }
    }

    public function isrunning($pid) {

        //@TODO: checkout for windows system
        //http://lifehacker.com/362316/use-unix-commands-in-windows-built-in-command-prompt

        $command ="ps -e | grep php | awk '{print $1}'";
            //$pids_ = explode(PHP_EOL, `ps -e | grep php | awk '{print $1}'`);
        $pids_ = RemoteSSH::run([$command]);
        //$pids_ = explode(PHP_EOL,$op);

        if( !empty($pid) && $pid > 0 && in_array($pid, $pids_)) {
            Log::info(" Running pids " . print_r($pids_,true));
            return TRUE;
        }
        return FALSE;
    }

    public function stop(){

        $command = 'kill -9 '.$this->pid;

        if($this->isrunning($this->pid)){

            RemoteSSH::run([$command]);

        }


        if ($this->status() == false) {
            return true;
        }
        else {
            return false;
        }
    }

    public function check_crontab_status(){
        $command = 'sudo service crond status';
        $output = RemoteSSH::run([$command]);
        if(isset($output[0]) && strstr($output[0],"is running...") != FALSE ){
            return true;
        }
        return false;
    }

    public function change_crontab_status($Status = 1){

        if($Status == 0 ){
            $command = 'sudo service crond stop';
        }else {
            $command = 'sudo service crond start';
        }

        $output = RemoteSSH::run([$command]);

        $is_running = $this->check_crontab_status();

        if(!$is_running &&  $Status == 0 ){
            return true;
        }
        else if($is_running &&  $Status == 1 ){
            return true;
        }
        return false;
    }
}