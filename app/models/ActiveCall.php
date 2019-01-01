<?php

class ActiveCall extends \Eloquent {

    protected $connection = 'neon_routingengine';
    protected $fillable = [];
    protected $guarded = array('ActiveCallID');
    protected $table = 'tblActiveCall';
    public  $primaryKey = "ActiveCallID"; //Used in BasedController



}