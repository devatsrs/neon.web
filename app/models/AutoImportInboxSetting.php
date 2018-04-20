<?php

class AutoImportInboxSetting extends \Eloquent
{

    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'tblAutoImportInboxSetting';
    protected $primaryKey = "AutoImportInboxSettingID";
    protected static $rate_table_cache = array();
    public static $enable_cache = false;

   
    public static function getAutoImportSetting($CompanyID){
        return AutoImportInboxSetting::where(["CompanyID" => $CompanyID])->limit(1)->get();
    }

    public static function updateInboxImportSetting($CompanyID,$data){
        return AutoImportInboxSetting::where('CompanyID','=',$CompanyID)->update([ 'port' => $data['port'],'host' => $data['host'] ]);
    }
}