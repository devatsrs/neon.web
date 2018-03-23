<?php
/**
 * Created by PhpStorm.
 * User: Bhavin
 * Date: 26/09/2017
 * Time: 04:30 PM
 */

class RateUpload {

    const vendor        = 'vendor';
    const ratetable     = 'ratetable';
    const customer      = 'customer';
    public static $uploadtypes   = array(
        RateUpload::vendor      => 'Vendor',
        RateUpload::ratetable   => 'Rate Table',
        //$this->customer     => 'Customer Rate'
    );
}