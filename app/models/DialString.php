<?php

class DialString extends \Eloquent {
	protected $fillable = [];

    public static $rules = [
        'Name' =>      'required',
        'CompanyID' =>  'required',
    ];
    protected $table = 'tblDialString';
    protected  $primaryKey = "DialStringID";
    protected $guarded = ['DialStringID'];

    public static function  getDialStringIDList(){
        $company_id = User::get_companyID();
        $row = DialString::where(['CompanyID'=>$company_id])->lists('Name','DialStringID');
        $row = array(""=> "Skip loading") + $row;
        return $row;

    }
    public static function getDialStringName($id){
        return DialString::find($id)->Name;
    }

}