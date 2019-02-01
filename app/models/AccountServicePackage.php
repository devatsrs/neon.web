<?php

class AccountServicePackage extends \Eloquent {

    protected $fillable = [];
    protected $guarded = array('AccountServicePackageID');
    protected $table = 'tblAccountServicePackage';
    public  $primaryKey = "AccountServicePackageID"; //Used in BasedController



}