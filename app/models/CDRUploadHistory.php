<?php

class CDRUploadHistory extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrv2';
    protected $table = 'tblCDRUploadHistory';
    protected  $primaryKey = "CDRUploadHistoryID";
    protected $guarded = array('CDRUploadHistoryID');

}