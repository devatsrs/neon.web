<?php

class GatewayConfig extends \Eloquent {
	protected $fillable = [];

    protected $guarded = array('GatewayConfigID');

    protected $table = 'tblGatewayConfig';

    protected  $primaryKey = "GatewayConfigID";

    public static $NameFormat = array('NAMENUB'=>'Account Name - Account Number','NUBNAME'=>'Account Number - Account Name','NAME'=>'Account Name','NUB'=>'Account Number','IP'=>'IP','CLI'=>'CLI');
    public static $Vos_NameFormat = array('NAMENUB'=>'Account Name - Account Number','NUBNAME'=>'Account Number - Account Name','NAME'=>'Account Name','NUB'=>'Account Number','IP'=>'IP');
    public static $Porta_NameFormat = array('NAMENUB'=>'Account Name - Account Number','NUBNAME'=>'Account Number - Account Name','NAME'=>'Account Name','NUB'=>'Account Number');
    public static $Sippy_NameFormat = array('IP'=>'IP','NAME'=>'Account Name');
    public static $SippySQL_NameFormat = array('IP'=>'IP','NAME'=>'Account Name');
    public static $Mirta_NameFormat = array('NUB'=>'Account Number');
    public static $MOR_NameFormat = array('NUB'=>'Account Number','IP'=>'IP');
    public static $CallShop_NameFormat = array('NUB'=>'Account Number');
    public static $Streamco_NameFormat = array('NAME'=>'Account Name');
    public static $FusionPBX_NameFormat = array('NUB'=>'Account Number');
    public static $VoipNow_NameFormat = array('NAME'=>'Account Name');
    public static $AccountNameFormat = array('NAMENUB'=>'Account Name - Account Number','NUBNAME'=>'Account Number - Account Name','NAME'=>'Account Name','NUB'=>'Account Number','IP'=>'IP','CLI'=>'CLI','Other'=>'Other');
    public static $CallType = array('OUT'=>'Outbound','INOUT'=>'Inbond+Outbound');
    public static $M2_NameFormat = array('NAME'=>'Account Name','IP'=>'IP');
    public static $VoipMS_NameFormat = array('NAMENUB'=>'Account Name - Account Number','NUBNAME'=>'Account Number - Account Name','NAME'=>'Account Name','NUB'=>'Account Number');

    public static function getConfigTitle($GatewayConfigID){
        return GatewayConfig::where(array('GatewayConfigID'=>$GatewayConfigID))->pluck('Title');
    }
    public static function getConfigName($GatewayConfigID){
        return GatewayConfig::where(array('GatewayConfigID'=>$GatewayConfigID))->pluck('Name');
    }
}