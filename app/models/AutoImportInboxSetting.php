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

        return AutoImportInboxSetting::where('CompanyID','=',$CompanyID)->update(
            [
              'port' => $data['port'],
              'host' => $data['host'],
              'encryption' => $data['encryption'],
              'validate_cert' => $data['validate_cert'],
              'username' => $data['username'],
              'password' => $data['password'],
              'emailNotificationOnSuccess' => $data['emailNotificationOnSuccess'],
              'emailNotificationOnFail' => $data['emailNotificationOnFail'],
              'SendCopyToAccount' => $data['SendCopyToAccount'],
              'updated_at' => date('Y-m-d H:i:s')
            ]);

    }
}