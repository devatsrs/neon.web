<?php

class AuditDetails extends \Eloquent {
    protected $guarded = array("AuditDetailID");

    protected $table = 'tblAuditDetails';

    protected  $primaryKey = "AuditDetailID";

    public    $timestamps 	= 	false; // no created_at and updated_at


}