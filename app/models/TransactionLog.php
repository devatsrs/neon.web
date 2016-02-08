<?php
class TransactionLog extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $table = 'tblTransactionLog';
    public $primaryKey = "TransactionLogID";
    protected $fillable = [];
    protected $guarded = ['TransactionLogID'];

    const SUCCESS =1;
    const FAILED =2;


} 