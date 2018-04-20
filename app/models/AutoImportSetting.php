<?php

class AutoImportSetting extends \Eloquent
{

    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'tblAutoImportSetting';
    protected $primaryKey = "AutoImportSettingID";
    protected static $rate_table_cache = array();
    public static $enable_cache = false;


    public static function updateAccountImportSetting($AutoImportSettingID,$data=array()){

        $data["updated_at"] = date('Y-m-d H:i:s');
        $data['updated_by'] =  User::get_user_full_name();
        return AutoImportSetting::where('AutoImportSettingID','=',$AutoImportSettingID)
            ->update([
                       'Type' => $data['Type'],
                       'TypePKID' => $data['TypePKID'],
                       'TrunkID' => $data['TrunkID'],
                       'ImportFileTempleteID' => $data['ImportFileTempleteID'],
                       'Subject' => $data['Subject'],
                       'FileName' => $data['FileName'],
                       'SendorEmail' => $data['SendorEmail']
                    ]);
    }

    public static function updateRateTableImportSetting($AutoImportSettingID,$data=array()){

        $data["updated_at"] = date('Y-m-d H:i:s');
        $data['updated_by'] =  User::get_user_full_name();
        return AutoImportSetting::where('AutoImportSettingID','=',$AutoImportSettingID)
            ->update([
                'Type' => $data['Type'],
                'TypePKID' => $data['TypePKID'],
                'ImportFileTempleteID' => $data['ImportFileTempleteID'],
                'Subject' => $data['Subject'],
                'FileName' => $data['FileName'],
                'SendorEmail' => $data['SendorEmail']
            ]);
    }

    public static function DeleteautiimportSetting($AutoImportSettingID){
        AutoImportSetting::where('AutoImportSettingID', '=', $AutoImportSettingID)->delete();
    }
	

}