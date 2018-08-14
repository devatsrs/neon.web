<?php
class CreditNotesTaxRate extends \Eloquent {


    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('CreditNotesTaxRateID');
    protected $table = 'tblCreditNotesTaxRate';
    protected  $primaryKey = "CreditNotesTaxRateID";


}