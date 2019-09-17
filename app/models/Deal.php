<?php

class Deal extends \Eloquent {

    protected $guarded      = array("DealID");
    protected $table        = 'tblDeal';
    protected $primaryKey   = "DealID";

    const StatusActive = "Active";
    const StatusClosed = "Closed";
    const TypeRevenue = "Revenue";
    const TypePayment = "Payment";

    public static $StatusDropDown = array(
        self::StatusActive => 'Active',
        self::StatusClosed => 'Closed'
    );
    public static $TypeDropDown = array(
        self::TypeRevenue => 'Revenue',
        self::TypePayment => 'Payment'
    );

    public static $rules = array(
        'Title'         => 'required',
        'DealType'      => 'required',
        'AccountID'     => 'required',
        'CodedeckID'    => 'required',
        'Status'        => 'required',
        'AlertEmail'    => 'email',
        'StartDate'     => 'required|date|date_format:Y-m-d',
        'EndDate'       => 'required|date|date_format:Y-m-d|after:StartDate',
        'TotalPL'       => 'required',
    );


    public static function dealDetailArray($DealID, $data){
        $dealDetail = [];
        $process = [];

    }
}