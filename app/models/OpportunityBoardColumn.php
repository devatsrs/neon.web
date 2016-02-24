<?php

class OpportunityBoardColumn extends \Eloquent {

    protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('OpportunityBoardColumnID');
    protected $table = 'tblOpportunityBoardColumn';
    public  $primaryKey = "OpportunityBoardColumnID";

}