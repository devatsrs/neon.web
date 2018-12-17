<?php

class ServiceTemapleSubscription extends \Eloquent
{
    protected $guarded = array("ServiceTemplateSubscriptionId");

    protected $table = 'tblServiceTemapleSubscription';

    protected $primaryKey = "ServiceTemplateSubscriptionId";

    public static $rules = array(
    );

    public static $ServiceType = array(""=>"Select", "voice"=>"Voice");


}