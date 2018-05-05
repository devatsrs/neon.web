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

    public static function DeleteautoimportSetting($AutoImportSettingID){
        AutoImportSetting::where('AutoImportSettingID', '=', $AutoImportSettingID)->delete();
    }

    public static function validate($data){
        $arrWhere=[];

        $AutoImportSettingID=$data["AutoImportSettingID"];
        $arrWhere["FileName"]=trim($data["FileName"]);
        $arrWhere["SendorEmail"]=trim($data["SendorEmail"]);
        $arrWhere["Subject"]=trim($data["Subject"]);
        $arrWhere["TypePKID"]=$data["TypePKID"];
//        $arrWhere["ImportFileTempleteID"]=$data["ImportFileTempleteID"];

        if($data["Type"]==1){
            $arrWhere["TrunkID"]=$data["TrunkID"];
        }

        if(!empty($AutoImportSettingID)){
            $result = AutoImportSetting::where($arrWhere)->where("AutoImportSettingID",'!=', $AutoImportSettingID)->count();
        }else{
            $result = AutoImportSetting::where($arrWhere)->count();
        }
        return $result;
    }

}