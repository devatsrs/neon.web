<?php
class AlertLog extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('AlertLogID');
    protected $table = 'tblAlertLog';
    protected  $primaryKey = "AlertLogID";

    public $timestamps = false; // no created_at and updated_at
}