<?php

class OutPaymentLog extends \Eloquent {
    protected $guarded = array("OutPaymentLogID");

    protected $fillable = [];

    protected $table = "tblOutPaymentLog";

    protected $primaryKey = "OutPaymentLogID";

    public $timestamps = false;

}