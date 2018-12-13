<?php

class ServiceTemapleInboundTariff extends \Eloquent
{
    protected $guarded = array("ServiceTemapleInboundTariffId");

    protected $table = 'tblServiceTemapleInboundTariff';

    protected $primaryKey = "ServiceTemapleInboundTariffId";

    public static $rules = array(
    );

    public static $ServiceType = array(""=>"Select", "voice"=>"Voice");


}