<?php

class CRMBoardColumn extends \Eloquent {

    protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('BoardColumnID');
    protected $table = 'tblCRMBoardColumn';
    public  $primaryKey = "BoardColumnID";

}