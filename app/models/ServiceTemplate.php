<?php

class ServiceTemplate extends \Eloquent
{
    protected $guarded = array("ServiceTemplateID");

    protected $table = 'tblServiceTemplate';

    protected $primaryKey = "ServiceTemplateId";

    public static $rules = array(
      //  'ServiceId' =>  'required',
        'Name' => 'required',
        'CurrencyId' => 'required',
       // 'selectedSubscription' => 'required',
       // 'selectedcategotyTariff' => 'required',
    );

    public static $updateRules = array(
      //  'ServiceId' =>  'required',
        'Name' => 'required',
       // 'selectedSubscription' => 'required',
       // 'selectedcategotyTariff' => 'required',
    );

    public static $ServiceType = array(""=>"Select", "voice"=>"Voice");


}