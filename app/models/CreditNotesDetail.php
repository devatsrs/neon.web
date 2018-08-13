<?php

class CreditNotesDetail extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('CreditNotesDetailID');
    protected $table = 'tblCreditNotesDetail';
    protected  $primaryKey = "CreditNotesDetailID";

}