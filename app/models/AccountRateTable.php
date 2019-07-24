<?php

class AccountRateTable extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('AccountRateTableID');

    protected $table = 'tblAccountRateTable';

    protected  $primaryKey = "AccountRateTableID";

}