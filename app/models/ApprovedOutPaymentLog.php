<?php

class ApprovedOutPaymentLog extends \Eloquent {
    protected $guarded = array("ApprovedOutPaymentLogID");

    protected $fillable = [];

    protected $table = "tblApprovedOutPaymentLog";

    protected $primaryKey = "ApprovedOutPaymentLogID";


}