<?php

class Opportunity extends \Eloquent {

    protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('OpportunityID');
    protected $table = 'tblOpportunity';
    public  $primaryKey = "OpportunityID";

}