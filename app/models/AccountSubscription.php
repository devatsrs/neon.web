<?php

class AccountSubscription extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv2";
    protected $table = "tblAccountSubscription";
    protected $primaryKey = "AccountSubscriptionID";
    protected $guarded = array('AccountSubscriptionID');

    public static function calculateCost($ComponentName,$Cost) {

        $Yearly         = '';
        $quarterly      = '';
        $monthly        = '';
        $weekly         = '';
        $daily          = '';
        //$decimal_places     = 2;
        $Cost               = floatval($Cost);

        if ($ComponentName == 'AnnuallyFee') {
            $Yearly     = $Cost;
            $monthly    = $Yearly / 12;
            $quarterly  = $monthly * 3;
            $weekly     = floatval($monthly / 30 * 7);
            $daily      = floatval($monthly / 30);
        } else if ($ComponentName == 'QuarterlyFee') {
            $quarterly  = $Cost;
            $monthly    = $quarterly / 3;
            $Yearly     = $monthly * 12;
            $weekly     = floatval($monthly / 30 * 7);
            $daily      = floatval($monthly / 30);
        } else if ($ComponentName == 'MonthlyFee') {
            $monthly    = $Cost;
            $Yearly     = $monthly * 12;
            $quarterly  = $monthly * 3;
            $weekly     = floatval($monthly / 30 * 7);
            $daily      = floatval($monthly / 30);
        } else if ($ComponentName == 'WeeklyFee') {
            $weekly     = $Cost;
            $daily      = floatval($weekly / 7);
            $monthly    = $daily * 30;
            $Yearly     = $monthly * 12;
            $quarterly  = $monthly * 3;
        } else if ($ComponentName == 'DailyFee') {
            $daily      = $Cost;
            $weekly     = $daily * 7;
            $monthly    = $daily * 30;
            $quarterly  = $monthly * 3;
            $Yearly     = $monthly * 12;
        }

        $result['DailyFee']     = $daily;
        $result['WeeklyFee']    = $weekly;
        $result['MonthlyFee']   = $monthly;
        $result['QuarterlyFee'] = $quarterly;
        $result['AnnuallyFee']  = $Yearly;

        return $result;
    }

    public static $rules = array(
        'AccountID'         =>      'required',
        'SubscriptionID'    =>  'required',
        'StartDate'               =>'required',
        'EndDate'               =>'required'
    );

    public static $frequency = array(
        'Daily'     =>  'Daily',
        'Weekly'    =>  'Weekly',
        'Monthly'   =>  'Monthly',
        
    );

    public static function  checkForeignKeyById($id){

        if($id>0){
            return false;
        }
    }
}