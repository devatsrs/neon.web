<?php

class UsageHeader extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrvcdr";
    protected $table = "tblUsageHeader";
    protected $primaryKey = "UsageHeaderID";
    protected $guarded = array('UsageHeaderID');

    public static function  checkForeignKeyById($id){

        if($id>0){
            return false;
        }
    }
}