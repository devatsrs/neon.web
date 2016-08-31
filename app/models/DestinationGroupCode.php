<?php
class DestinationGroupCode extends \Eloquent
{
    protected $guarded = array("DestinationGroupCodeID");

    protected $table = 'tblDestinationGroupCode';

    protected $primaryKey = "DestinationGroupCodeID";

    public $timestamps = false; // no created_at and updated_at


}