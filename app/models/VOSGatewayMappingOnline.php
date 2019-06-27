<?php

class VOSGatewayMappingOnline extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrvcdr";
    protected $table = "tblVOSGatewayMappingOnline";
    protected $primaryKey = "VOSGatewayMappingOnlineID";
    protected $guarded = array('VOSGatewayMappingOnlineID');

    public $timestamps = false; // no created_at and updated_at

}