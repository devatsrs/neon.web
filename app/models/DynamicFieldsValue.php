<?php

class DynamicFieldsValue extends \Eloquent {
    protected $guarded = array("DynamicFieldsValueID");

    protected $table = 'tblDynamicFieldsValue';

    protected  $primaryKey = "DynamicFieldsValueID";

    public    $timestamps 	= 	false; // no created_at and updated_at

}