<?php
class UsageDetail extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrvcdr';
    public $timestamps = false; // no created_at and updated_at

    protected $guarded = array('UsageDetailID');

    protected $table = 'tblUsageDetail';

    protected  $primaryKey = "UsageDetailID";

}