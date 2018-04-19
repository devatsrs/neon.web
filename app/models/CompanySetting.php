<?php

class CompanySetting extends \Eloquent {
	protected $fillable = [];
    protected $table = "tblCompanySetting";
    public $timestamps = false; // no created_at and updated_at

    const ACCOUT_VARIFICATION_ON = 1;
    const ACCOUT_VARIFICATION_OFF = 0;

    public static function getKeyVal($key, $CompanyID=0){
        if(empty($CompanyID)){
            $CompanyID=User::get_companyID();
        }
        $CompanySetting = CompanySetting::where(["CompanyID"=> $CompanyID,'key'=>$key])->first();
        if(count($CompanySetting)>0 && isset($CompanySetting->Value)){
            return $CompanySetting->Value;
        }else{
            return 'Invalid Key';
        }
    }

    public static function  setKeyVal($key,$val,$CompanyID=0){

        if(empty($CompanyID)){
            $CompanyID=User::get_companyID();
        }

        $CompanySetting = CompanySetting::where(["CompanyID"=> $CompanyID,'key'=>$key])->first();
        if(count($CompanySetting)>0){
            CompanySetting::where(["CompanyID"=> $CompanyID,'key'=>$key])->update(array('Value'=>$val));
        }else{
            CompanySetting::insert(array('CompanyID' => $CompanyID, 'key' => $key,'Value'=>$val));
        }
    }
}