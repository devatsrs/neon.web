<?php

class ActiveCall extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrvcdr";
    protected $table = "tblActiveCall";
    protected $primaryKey = "ActiveCallID";
    protected $guarded = array('ActiveCallID');

    public $timestamps = false; // no created_at and updated_at

}