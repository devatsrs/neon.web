<?php

class ServiceTemplateTypes extends \Eloquent
{

    protected $connection = 'sqlsrv2';
    protected $fillable = [];

    const DYNAMIC_TYPE = 'serviceTemplate';

}