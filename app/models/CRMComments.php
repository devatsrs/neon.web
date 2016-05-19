<?php

class CRMComments extends \Eloquent {

    protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('CommentID');
    protected $table = 'tblCRMComments';
    public  $primaryKey = "CommentID";

}