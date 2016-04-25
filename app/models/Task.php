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

    const All = 0;
    const Overdue = 1;
    const DueSoon = 2;
    const ThisWeeks = 3;
    const CustomDate = 4;


    public static $priority = [Task::High=>'High',Task::Medium=>'Medium',Task::Low=>'Low'];
    public static $tasks = [Task::All=>'All',Task::Overdue=>'Overdue',Task::DueSoon=>'Due Soon',
                            Task::ThisWeeks=>'This Weeks',Task::CustomDate=>'Custom Date'];

}