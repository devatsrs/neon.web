<?php

class Opportunity extends \Eloquent {

    protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('OpportunityID');
    protected $table = 'tblOpportunity';
    public  $primaryKey = "OpportunityID";

    public static function getOpportunityList($select=1){
        $companyID = User::get_companyID();
        $row = Opportunity::where(['CompanyID'=>$companyID])->select(['OpportunityID','OpportunityName'])->lists('OpportunityName','OpportunityID');
        if(!empty($row) & $select==1){
            $row = array(""=> "Select an Opportunity")+$row;
        }
        return $row;
    }

}