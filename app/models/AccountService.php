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

    }
    /**
     * $SourceAccountID is Source Account
     * $AccountIDs is Destination accounts
     * $ServiceIds is Services for clone
     * */
    public static function CloneServices($data=array()){

        $SourceAccountID = $data['SourceAccountID'];
        $AccountIDs = $data['AccountIDs'];
        $ServiceIDs = $data['ServiceIDs'];


        $AccountServices = array();
        $AllAccountSubscriptions = array();
        $AllAccountOneoffCharges = array();
        $AllAccountTariffs = array();

        $error = '';
        $Result = array();

        try {

            DB::connection('sqlsrv')->beginTransaction();
            DB::connection('sqlsrv2')->beginTransaction();

            foreach ($AccountIDs as $AccountID) {

                foreach ($ServiceIDs as $ServiceID) {
                    $check_service = AccountService::where(['AccountID' => $AccountID, 'ServiceID' => $ServiceID])->count();
                    if ($check_service == '0') {
                        /* Account Service start */
                        $AccountService = AccountService::where(['AccountID' => $SourceAccountID, 'ServiceID' => $ServiceID])->first();
                        if (!empty($AccountService)) {
                            $accountser = array();
                            $accountser['AccountID'] = $AccountID;
                            $accountser['ServiceID'] = $ServiceID;
                            $accountser['CompanyID'] = $AccountService->CompanyID;
                            $accountser['Status'] = $AccountService->Status;
                            if (!empty($data['ServiceTitle'])) {
                                $accountser['ServiceTitle'] = $AccountService->ServiceTitle;
                            }
                            if (!empty($data['ServiceDescription'])) {
                                $accountser['ServiceDescription'] = $AccountService->ServiceDescription;
                            }
                            $accountser['ServiceTitleShow'] = $AccountService->ServiceTitleShow;
                            $AccountServices[] = $accountser;
                        }

                        /* Account Service end */

                        /* Account Subscription start */

                        if (!empty($data['Subscription'])) {
                            $AccountSubscriptions = AccountSubscription::where(['AccountID' => $SourceAccountID, 'ServiceID' => $ServiceID])->get();
                            if (!empty($AccountSubscriptions)) {
                                foreach ($AccountSubscriptions as $AccountSubscription) {
                                    $accountsub = array();
                                    $AccountSubscription = json_decode(json_encode($AccountSubscription), true);
                                    $accountsub = $AccountSubscription;

                                    $accountsub['AccountID'] = $AccountID;
                                    $accountsub['ServiceID'] = $ServiceID;
                                    unset($accountsub['AccountSubscriptionID']);
                                    unset($accountsub['created_at']);
                                    unset($accountsub['updated_at']);
                                    $accountsub["created_at"] = date('Y-m-d H:i:s');
                                    $accountsub["updated_at"] = date('Y-m-d H:i:s');
                                    $AllAccountSubscriptions[] = $accountsub;
                                }
                            }
                        }

                        /* Account Subscription end */

                        /* Account Addtional Charges start */
                        if (!empty($data['Additional'])) {
                            $AccountOneoffcharges = AccountOneOffCharge::where(['AccountID' => $SourceAccountID, 'ServiceID' => $ServiceID])->get();
                            if (!empty($AccountOneoffcharges)) {
                                foreach ($AccountOneoffcharges as $AccountOneoffcharge) {
                                    $accountadditional = array();
                                    $AccountOneoffcharge = json_decode(json_encode($AccountOneoffcharge), true);
                                    $accountadditional = $AccountOneoffcharge;

                                    $accountadditional['AccountID'] = $AccountID;
                                    $accountadditional['ServiceID'] = $ServiceID;
                                    unset($accountadditional['AccountOneOffChargeID']);
                                    unset($accountadditional['created_at']);
                                    unset($accountadditional['updated_at']);
                                    $accountadditional["created_at"] = date('Y-m-d H:i:s');
                                    $accountadditional["updated_at"] = date('Y-m-d H:i:s');
                                    $AllAccountOneoffCharges[] = $accountadditional;
                                }
                            }
                        }

                        /* Account Addtional Charges end */

                        /* Account tarif start */
                        if (!empty($data['Tariff'])) {
                            $AccountTariffs = AccountTariff::where(['AccountID' => $SourceAccountID, 'ServiceID' => $ServiceID])->get();
                            if (!empty($AccountTariffs)) {
                                foreach ($AccountTariffs as $AccountTariff) {
                                    $accounttar = array();
                                    $AccountTariff = json_decode(json_encode($AccountTariff), true);
                                    $accounttar = $AccountTariff;

                                    $accounttar['AccountID'] = $AccountID;
                                    $accounttar['ServiceID'] = $ServiceID;
                                    unset($accounttar['AccountTariffID']);
                                    unset($accounttar['created_at']);
                                    unset($accounttar['updated_at']);
                                    $accounttar["created_at"] = date('Y-m-d H:i:s');
                                    $accounttar["updated_at"] = date('Y-m-d H:i:s');
                                    $AllAccountTariffs[] = $accounttar;
                                }
                            }
                        }

                        /* Account tarif end */

                        /* Account Billing start */

                        $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), 0);

                        if (!empty($data['Billing'])) {
                            $AccountBillings = AccountBilling::where(['AccountID' => $SourceAccountID, 'ServiceID' => $ServiceID])->first();
                            if (!empty($AccountBillings)) {
                                $AccountBillings = json_decode(json_encode($AccountBillings), true);

                                if (!empty($AccountBillings['BillingStartDate']) || !empty($AccountBillings['BillingCycleType']) || !empty($AccountBillings['BillingCycleValue']) || !empty($AccountBillings['BillingClassID'])) {
                                    AccountBilling::insertUpdateBilling($AccountID, $AccountBillings, $ServiceID);
                                    AccountBilling::storeFirstTimeInvoicePeriod($AccountID, $ServiceID);
                                    $AccountPeriod = AccountBilling::getCurrentPeriod($AccountID, date('Y-m-d'), $ServiceID);
                                }

                            }
                        }

                        /* Account Billing End */


                        /* discount plan start */
                        if (!empty($data['DiscountPlan'])) {
                            if (!empty($AccountPeriod)) {
                                $billdays = getdaysdiff($AccountPeriod->EndDate, $AccountPeriod->StartDate);
                                $getdaysdiff = getdaysdiff($AccountPeriod->EndDate, date('Y-m-d'));
                                $DayDiff = $getdaysdiff > 0 ? intval($getdaysdiff) : 0;
                                $AccountSubscriptionID = 0;
                                $AccountName='';
                                $AccountCLI='';
                                $SubscriptionDiscountPlanID = 0;

                                $DiscountPlanID = AccountDiscountPlan::where(array('AccountID' => $SourceAccountID, 'Type' => AccountDiscountPlan::OUTBOUND, 'ServiceID' => $ServiceID,'AccountSubscriptionID'=>$AccountSubscriptionID,'AccountName'=>$AccountName,'AccountCLI'=>$AccountCLI,'SubscriptionDiscountPlanID'=>$SubscriptionDiscountPlanID))->pluck('DiscountPlanID');
                                $InboundDiscountPlanID = AccountDiscountPlan::where(array('AccountID' => $SourceAccountID, 'Type' => AccountDiscountPlan::INBOUND, 'ServiceID' => $ServiceID,'AccountSubscriptionID'=>$AccountSubscriptionID,'AccountName'=>$AccountName,'AccountCLI'=>$AccountCLI,'SubscriptionDiscountPlanID'=>$SubscriptionDiscountPlanID))->pluck('DiscountPlanID');

                                $OutboundDiscountPlan = empty($DiscountPlanID) ? '' : $DiscountPlanID;
                                $InboundDiscountPlan = empty($InboundDiscountPlanID) ? '' : $InboundDiscountPlanID;

                                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $OutboundDiscountPlan, AccountDiscountPlan::OUTBOUND, $billdays, $DayDiff, $ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                                AccountDiscountPlan::addUpdateDiscountPlan($AccountID, $InboundDiscountPlan, AccountDiscountPlan::INBOUND, $billdays, $DayDiff, $ServiceID,$AccountSubscriptionID,$AccountName,$AccountCLI,$SubscriptionDiscountPlanID);
                            }
                        }

                        /* discount plan end */

                    }else{
                        $AccountName= Account::getCompanyNameByID($AccountID);
                        $ServiceName= Service::getServiceNameByID($ServiceID);
                        $error.= '<br>skip clone service'.$AccountName.' - '.$ServiceName;
                    }

                } // over service loop

            }// over accounts loop
            

            AccountService::insert($AccountServices);
            if (!empty($data['Subscription'])) {
                AccountSubscription::insert($AllAccountSubscriptions);
            }
            if (!empty($data['Additional'])) {
                AccountOneOffCharge::insert($AllAccountOneoffCharges);
            }
            if (!empty($data['Tariff'])) {
                AccountTariff::insert($AllAccountTariffs);
            }

            DB::connection('sqlsrv')->commit();
            DB::connection('sqlsrv2')->commit();

            $Result['Success'] = 'Service clone successfully.'.$error;
        }catch (Exception $e) {
            try {
                DB::connection('sqlsrv')->rollback();
                DB::connection('sqlsrv2')->rollback();
            } catch (Exception $err) {
                Log::error($err);
            }

            Log::error($e);

            $Result['Error'] = 'Error:' . $e->getMessage();
        }

        return $Result;

    }

    public static function getServiceIDsByCriteria($AccountID,$data=array()){
        $select = ["tblAccountService.ServiceID"];
        $services = AccountService::join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')->where("tblAccountService.AccountID",$AccountID);
        if(!empty($data['ServiceName'])){
            $services->where('tblService.ServiceName','Like','%'.trim($data['ServiceName']).'%');
        }
        if(!empty($data['ServiceActive']) && $data['ServiceActive'] == 'true'){
            $services->where(function($query){
                $query->where('tblAccountService.Status','=','1');
            });

        }elseif(!empty($data['ServiceActive']) && $data['ServiceActive'] == 'false'){
            $services->where(function($query){
                $query->where('tblAccountService.Status','=','0');
            });
        }
        $services->select($select);
        $results = $services->get();
        $ServiceID = array();
        foreach($results as $result){
            $ServiceID[]=$result->ServiceID;
        }
        return $ServiceID;
    }

}