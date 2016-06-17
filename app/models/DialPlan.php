<?php

class DialPlan extends \Eloquent {
	protected $fillable = [];

    public static $rules = [
        'Name' =>      'required',
        'CompanyID' =>  'required',
    ];
    protected $table = 'tblDialPlan';
    protected  $primaryKey = "DialPlanID";
    protected $guarded = ['DialPlanID'];

    public static function  getDialPlanIDList(){
        $company_id = User::get_companyID();
        $row = DialPlan::where(['CompanyID'=>$company_id])->lists('Name','DialPlanID');
        $row = array(""=> "Skip loading") + $row;
        return $row;

    }
    public static function getDialPlanName($id){
        return DialPlan::find($id)->Name;
    }

}