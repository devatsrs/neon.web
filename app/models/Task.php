<?php
class Task extends \Eloquent {

    //protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('TaskID');
    protected $table = 'tblTask';
    public  $primaryKey = "TaskID";

    const High = 1;
    const Medium = 2;
    const Low = 3;

    public static $priority = [Task::High=>'High',Task::Medium=>'Medium',Task::Low=>'Low'];

}