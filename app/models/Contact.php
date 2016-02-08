<?php

class Contact extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblContact';

    protected  $primaryKey = "ContactID";
    public static $rules = array(
       // 'AccountID' =>      'required',
        'CompanyID' =>  'required',
        'FirstName' => 'required',
        'LastName' => 'required',
    );
}