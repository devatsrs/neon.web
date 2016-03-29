<?php

class EstimateDetail extends \Eloquent {
    protected $connection 	= 'sqlsrv2';
    protected $fillable 	= [];
    protected $guarded 		= array('EstimateDetailID');
    protected $table 		= 'tblEstimateDetail';
    protected $primaryKey 	= "EstimateDetailID";

}