<?php

class CustomerRate extends \Eloquent {
	protected $fillable = [];

    protected $table = 'tblCustomerRate';

    protected  $primaryKey = "CustomerRateID";
    public  $timestamps  = false;

    public static function getRatePrefix($CustomerID=0){
        if(empty($CustomerID)){
            $CustomerRates = CustomerRate::select('RatePrefix')->whereNotNull('RatePrefix')->lists('RatePrefix','RatePrefix');
        }else{
            $CustomerRates = CustomerRate::where('CustomerID',$CustomerID)->whereNotNull('RatePrefix')->select('RatePrefix')->lists('RatePrefix','RatePrefix');
        }
        //print_r($CustomerRates);die;
        return $CustomerRates;
    }

}