<?php

class TempUsageDownloadLog extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrv2';
    protected $table = 'tblTempUsageDownloadLog';
}