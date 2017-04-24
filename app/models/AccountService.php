<?php

class AccountService extends \Eloquent {
	protected $fillable = [];
    protected $connection = "sqlsrv";
    protected $table = "tblAccountService";
    protected $primaryKey = "AccountServiceID";
    protected $guarded = array('AccountServiceID');

    public static $rules = array(

    );

    public static $messages = array(
        'BillingType.required' =>'Billing TYpe is required',
        'BillingTimezone.required' =>'Billing Timezone is required',
        'BillingCycleType.required' =>'Billing Cycle Type is required',
        'BillingStartDate.required' =>'Billing Cycle Date is required',
        'BillingCycleValue.required' =>'Billing Cycle Value field is required',
        'BillingClassID.required' =>'Billing Class is required',
    );

    // check if serviceid used
    public static function  checkForeignKeyById($AccountID,$ServiceID){
        $AccountTariff = AccountTariff::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->count();
        $AccountBilling = AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->count();
        $AccountDiscountPlan = AccountDiscountPlan::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->count();
        $AccountSubscription = AccountSubscription::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->count();
        $AccountOneOffCharge = AccountOneOffCharge::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->count();
        $CLIRateTable = CLIRateTable::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->count();

        if(!empty($AccountTariff) || !empty($AccountBilling) || !empty($AccountDiscountPlan) || !empty($AccountSubscription) || !empty($AccountOneOffCharge) || !empty($CLIRateTable)){
            return false;
        }
        return true;


    }
    public static function  getAccountServiceIDList($AccountID){
        $row = array();
        $select = ["tblService.ServiceName","tblAccountService.ServiceID"];
        $services = DB::table('tblAccountService')
            ->join('tblService', function ($join) use($AccountID) {
                $join->on('tblAccountService.ServiceID', '=', 'tblService.ServiceID')
                    ->where('tblAccountService.AccountID', '=', $AccountID)
                    ->where('tblAccountService.Status', '=', 1)
                    ->where('tblService.Status', '=', 1);
            })
            ->select($select)
            ->get();

        //echo count($services);
        foreach ($services as $ser){
            $row[$ser->ServiceID] = $ser->ServiceName;
        }


        if(count($services) > 0){
            $row = array(""=> "Select")+$row;
        }
        return $row;
       // echo "<pre>";
        //print_r($row);
        //echo "</pre>";
        //exit;

        /*
        $select = ["tblService.ServiceName","tblAccountService.ServiceID"];
        $services = AccountService::leftjoin('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')
            ->where(array("tblAccountService.AccountID"=>$AccountID,"tblAccountService.Status"=>1,"tblService.Status"=>1));
        $services->select($select)->get();
        //print_r($services);
        exit;
        if(!empty($services)){
            $services = array(""=> "Select")+$services;
        }

        return $services;*/
    }
}