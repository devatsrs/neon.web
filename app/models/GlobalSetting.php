<?php

class GlobalSetting extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('');
    protected $table = 'tblGlobalSetting';
    protected  $primaryKey = "GlobalSettingID";


}