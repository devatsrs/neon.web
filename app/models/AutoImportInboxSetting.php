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
        return AutoImportInboxSetting::where(["CompanyID" => $CompanyID])->first();
    }

    public static function updateInboxImportSetting($AutoImportInboxSettingID,$data){

        return AutoImportInboxSetting::where('AutoImportInboxSettingID','=',$AutoImportInboxSettingID)->update($data);

    }
}