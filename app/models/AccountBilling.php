<?php
class AccountBilling extends \Eloquent {
    //
    protected $guarded = array("AccountBillingID");

    protected $table = 'tblAccountBilling';

    protected $primaryKey = "AccountBillingID";

    public $timestamps = false; // no created_at and updated_at

    static  $defaultAccountAuditFields = [
        'BillingType'=>'BillingType',
        'BillingTimezone'=>'BillingTimezone',
        'BillingCycleType'=>'BillingCycleType',
        'BillingCycleValue'=>'BillingCycleValue',
        'BillingStartDate'=>'BillingStartDate',
        'LastInvoiceDate'=>'LastInvoiceDate',
        'NextInvoiceDate'=>'NextInvoiceDate',
        'LastChargeDate'=>'LastChargeDate',
        'NextChargeDate'=>'NextChargeDate',
        'BillingClassID'=>'BillingClassID',
        'ServiceID'=>'ServiceID',
        'AutoPayMethod'=>'AutoPayMethod',

    ];

    public static function boot(){
        parent::boot();
        log::info('AccountBilling Boot');

        static::created(function($obj)
        {
            if(!Auth::guest()) {
                log::info('AccountBilling Create Boot');
                $customer = Session::get('customer');
                /* 0= user, 1=customer */
                $UserType = 0;
                if ($customer == 1) {
                    $UserType = 1;
                }
                $UserID = User::get_userID();
                $CompanyID = User::get_companyID();
                $IP = get_client_ip();
                $header = ["UserID" => $UserID,
                    "CompanyID" => $CompanyID,
                    "ParentColumnName" => 'AccountBillingID',
                    "Type" => 'accountbilling',
                    "IP" => $IP,
                    "UserType" => $UserType
                ];
                $detail = array();
                log::info('--Account Billing create start--');
                foreach ($obj->attributes as $index => $value) {
                    if (array_key_exists($index, AccountBilling::$defaultAccountAuditFields)) {
                        $data = ['OldValue' => '',
                            'NewValue' => $obj->attributes[$index],
                            'ColumnName' => $index,
                            'ParentColumnID' => $obj->attributes['AccountBillingID']
                        ];
                        $detail[] = $data;
                    }
                }
                Log::info('start');
                Log::info(print_r($header, true));
                Log::info(print_r($detail, true));
                AuditHeader::add_AuditLog($header, $detail);
                Log::info('end');
                log::info('--Account Billing create end--');
            }

        });


        static::updated(function($obj) {
            if(!Auth::guest()) {
                log::info('AccountBilling Update Boot');
                $customer = Session::get('customer');
                /* 0= user, 1=customer */
                $UserType = 1;
                if ($customer == 1) {
                    $UserType = 0;
                }
                $UserID = User::get_userID();
                $CompanyID = User::get_companyID();
                $IP = get_client_ip();
                $header = ["UserID" => $UserID,
                    "CompanyID" => $CompanyID,
                    "ParentColumnName" => 'AccountBillingID',
                    "Type" => 'accountbilling',
                    "IP" => $IP,
                    "UserType" => $UserType
                ];
                $detail = array();
                log::info('--Account Billing update start--');
                foreach ($obj->original as $index => $value) {
                    if (array_key_exists($index, AccountBilling::$defaultAccountAuditFields)) {
                        if ($obj->attributes[$index] != $value) {
                            $data = ['OldValue' => $obj->original[$index],
                                'NewValue' => $obj->attributes[$index],
                                'ColumnName' => $index,
                                'ParentColumnID' => $obj->original['AccountBillingID']
                            ];
                            $detail[] = $data;
                        }
                    }
                }
                Log::info('start');
                Log::info(print_r($header, true));
                Log::info(print_r($detail, true));
                AuditHeader::add_AuditLog($header, $detail);
                Log::info('end');
                log::info('--Account Billing update end--');
            }
        });
    }

    public static function insertUpdateBilling($AccountID,$data=array(),$ServiceID,$invoice_count=0){
        if(empty($ServiceID)){
            $ServiceID=0;
        }
        if(empty($data['ServiceBilling'])){
            $data['ServiceBilling']=0;
        }
        if(AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->count() == 0) {

            if (!empty($data['BillingClassID'])) {
                $AccountBilling['BillingClassID'] = $data['BillingClassID'];
            }
            if (!empty($data['BillingType'])) {
                $AccountBilling['BillingType'] = $data['BillingType'];
            }
            $AccountBilling['BillingCycleType'] = $data['BillingCycleType'];
            if (!empty($data['BillingTimezone'])) {
                $AccountBilling['BillingTimezone'] = $data['BillingTimezone'];
            }
            if (!empty($data['SendInvoiceSetting'])) {
                $AccountBilling['SendInvoiceSetting'] = $data['SendInvoiceSetting'];
            }

            if (!empty($data['AutoPaymentSetting'])) {
                $AccountBilling['AutoPaymentSetting'] = $data['AutoPaymentSetting'];
            }
            if (!empty($data['AutoPayMethod'])) {
                $AccountBilling['AutoPayMethod'] = $data['AutoPayMethod'];
            }
            else{
                $AccountBilling['AutoPayMethod'] = 0;
            }

            if (!empty($data['BillingStartDate'])) {
                $AccountBilling['BillingStartDate'] = $data['BillingStartDate'];
            }
            if (!empty($data['BillingCycleValue'])) {
                $AccountBilling['BillingCycleValue'] = $data['BillingCycleValue'];
            } else {
                $AccountBilling['BillingCycleValue'] = '';
            }
            if (!empty($data['LastInvoiceDate'])) {
                $AccountBilling['LastInvoiceDate'] = $data['LastInvoiceDate'];
            } elseif (!empty($data['BillingStartDate'])) {
                $AccountBilling['LastInvoiceDate'] = $data['BillingStartDate'];
            }
            if (!empty($AccountBilling['LastInvoiceDate'])) {
                $BillingStartDate = strtotime($AccountBilling['LastInvoiceDate']);
            } else if (!empty($AccountBilling['BillingStartDate'])) {
                $BillingStartDate = strtotime($AccountBilling['BillingStartDate']);
            }
            if (!empty($data['LastChargeDate'])) {
                $AccountBilling['LastChargeDate'] = $data['LastChargeDate'];
            } elseif (!empty($data['BillingStartDate'])) {
                $AccountBilling['LastChargeDate'] = $data['BillingStartDate'];
            }
            if (!empty($BillingStartDate)) {
                //$AccountBilling['NextInvoiceDate'] = next_billing_date($AccountBilling['BillingCycleType'], $AccountBilling['BillingCycleValue'], $BillingStartDate);
                if(!empty($data['NextInvoiceDate'])){
                    $AccountBilling['NextInvoiceDate'] = $data['NextInvoiceDate'];
                }else{
                    $AccountBilling['NextInvoiceDate'] = next_billing_date($AccountBilling['BillingCycleType'], $AccountBilling['BillingCycleValue'], $BillingStartDate);
                }

                if(!empty($data['NextChargeDate'])){
                    $AccountBilling['NextChargeDate'] = $data['NextChargeDate'];
                }else{
                    $AccountBilling['NextChargeDate'] = next_billing_date($AccountBilling['BillingCycleType'], $AccountBilling['BillingCycleValue'], $BillingStartDate);
                }
            }
            $AccountBilling['AccountID'] = $AccountID;
            $AccountBilling['ServiceID'] = $ServiceID;
            $AccountBilling['ServiceBilling'] = $data['ServiceBilling'];
            AccountBilling::create($AccountBilling);
        }else{
            $AccountBillingObj =  AccountBilling::getBilling($AccountID,$ServiceID);
            if($AccountBillingObj->BillingCycleType != 'manual' && $data['BillingCycleType'] != 'manual' && (AccountDiscountPlan::checkDiscountPlan($AccountID) > 0)) {
                AccountNextBilling::insertUpdateBilling($AccountID, $data, $ServiceID);
            }else{
                if($data['BillingCycleType'] == 'manual') {
                    AccountNextBilling::where(array('AccountID' => $AccountID, 'ServiceID' => $ServiceID))->delete();
                }
                $AccountBilling['BillingCycleType'] = $data['BillingCycleType'];
                if (!empty($data['BillingStartDate'])) {
                    $AccountBilling['BillingStartDate'] = $data['BillingStartDate'];
                }
                if (!empty($data['BillingCycleValue'])) {
                    $AccountBilling['BillingCycleValue'] = $data['BillingCycleValue'];
                } else {
                    $AccountBilling['BillingCycleValue'] = '';
                }
                if (!empty($data['LastInvoiceDate'])) {
                    $AccountBilling['LastInvoiceDate'] = $data['LastInvoiceDate'];
                } elseif (!empty($data['BillingStartDate'])) {
                    $AccountBilling['LastInvoiceDate'] = $data['BillingStartDate'];
                }
                if (!empty($data['LastChargeDate'])) {
                    $AccountBilling['LastChargeDate'] = $data['LastChargeDate'];
                } elseif (!empty($data['BillingStartDate'])) {
                    $AccountBilling['LastChargeDate'] = $data['BillingStartDate'];
                }
                if (!empty($AccountBilling['LastInvoiceDate'])) {
                    $BillingStartDate = strtotime($AccountBilling['LastInvoiceDate']);
                } else if (!empty($AccountBilling['BillingStartDate'])) {
                    $BillingStartDate = strtotime($AccountBilling['BillingStartDate']);
                }
                if (!empty($BillingStartDate) && $data['BillingCycleType'] != 'manual') {
                    if(!empty($data['NextInvoiceDate'])){
                        $AccountBilling['NextInvoiceDate'] = $data['NextInvoiceDate'];
                    }else{
                        $AccountBilling['NextInvoiceDate'] = next_billing_date($AccountBilling['BillingCycleType'], $AccountBilling['BillingCycleValue'], $BillingStartDate);
                    }

                    if(!empty($data['NextChargeDate'])){
                        $AccountBilling['NextChargeDate'] = $data['NextChargeDate'];
                    }else{
                        $AccountBilling['NextChargeDate'] = next_billing_date($AccountBilling['BillingCycleType'], $AccountBilling['BillingCycleValue'], $BillingStartDate);
                    }
                }else if($data['BillingCycleType'] == 'manual'){
                    $AccountBilling['NextInvoiceDate'] = null;
                    $AccountBilling['NextChargeDate'] = null;
                }

            }
            if (!empty($data['BillingClassID'])) {
                $AccountBilling['BillingClassID'] = $data['BillingClassID'];
            }

            if (!empty($data['BillingType'])) {
                $AccountBilling['BillingType'] = $data['BillingType'];
            }

            if (!empty($data['BillingTimezone'])) {
                $AccountBilling['BillingTimezone'] = $data['BillingTimezone'];
            }
            if (!empty($data['SendInvoiceSetting'])) {
                $AccountBilling['SendInvoiceSetting'] = $data['SendInvoiceSetting'];
            }
            if (!empty($data['AutoPaymentSetting'])) {
                $AccountBilling['AutoPaymentSetting'] = $data['AutoPaymentSetting'];
            }
            if (!empty($data['AutoPayMethod'])) {
                $AccountBilling['AutoPayMethod'] = $data['AutoPayMethod'];
            }
            else{
                $AccountBilling['AutoPayMethod'] = 0;
            }
            $AccountBilling['ServiceBilling'] = $data['ServiceBilling'];
            if(!empty($AccountBilling)){
                $AccountBillingID=AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->pluck('AccountBillingID');
                $UpdateAccountBilling = AccountBilling::find($AccountBillingID);
                $UpdateAccountBilling->update($AccountBilling);
            }

        }

    }
    public static function getBilling($AccountID,$ServiceID=0){
        return AccountBilling::where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->first();
    }
    public static function getBillingKey($AccountBilling,$key){
        return !empty($AccountBilling)?$AccountBilling->$key:'';
    }

    //not using
    public static function getBillingDay($AccountID){
        $days = 0;
        $AccountBilling =  AccountBilling::getBilling($AccountID);
        if(!empty($AccountBilling)) {
            $days = getBillingDay(strtotime($AccountBilling->LastInvoiceDate), $AccountBilling->BillingCycleType, $AccountBilling->BillingCycleValue);
        }
        return $days;
    }
    public static function getInvoiceTemplateID($AccountID){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getInvoiceTemplateID($BillingClassID);
    }
	
	public static function getTaxRate($AccountID){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getTaxRate($BillingClassID);
    }
	
	public static function getTaxRateType($AccountID,$type){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getTaxRateType($BillingClassID,$type);
    }
	
	
    public static function storeNextInvoicePeriod($AccountID,$BillingCycleType,$BillingCycleValue,$LastInvoiceDate,$NextInvoiceDate,$ServiceID){
        if(empty($ServiceID)){
            $ServiceID=0;
        }
        $StartDate = $LastInvoiceDate;
        $EndDate = $NextInvoiceDate;
        $NextBilling =array();
        for($count=0;$count<50;$count++){
            $NextBilling[]  = array(
                'StartDate' => $StartDate,
                'EndDate' =>$EndDate,
                'AccountID' => $AccountID,
                'ServiceID' => $ServiceID
            );
            $StartDate = $EndDate;
            $EndDate = next_billing_date($BillingCycleType, $BillingCycleValue, strtotime($StartDate));
        }
        DB::table('tblAccountBillingPeriod')->where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->delete();
        DB::table('tblAccountBillingPeriod')->insert($NextBilling);
    }
    public static function getCurrentPeriod($AccountID,$date,$ServiceID){
        if(empty($ServiceID)){
            $ServiceID = 0;
        }
        return DB::table('tblAccountBillingPeriod')->where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->where('StartDate','<=',$date)->where('EndDate','>',$date)->first();
    }

    public static function storeFirstTimeInvoicePeriod($AccountID,$ServiceID){
        if(DB::table('tblAccountBillingPeriod')->where(array('AccountID'=>$AccountID,'ServiceID'=>$ServiceID))->where('StartDate','>=',date('Y-m-d'))->count() == 0){
            $AccountBilling =  AccountBilling::getBilling($AccountID,$ServiceID);
            AccountBilling::storeNextInvoicePeriod($AccountID,$AccountBilling->BillingCycleType,$AccountBilling->BillingCycleValue,$AccountBilling->LastInvoiceDate,$AccountBilling->NextInvoiceDate,$ServiceID);
        }
    }

    public static function getBillingClassID($AccountID){
        return AccountBilling::where('AccountID',$AccountID)->pluck('BillingClassID');
    }
    public static function getPaymentDueInDays($AccountID){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getPaymentDueInDays($BillingClassID);
    }

    public static function getRoundChargesAmount($AccountID){
        $BillingClassID = self::getBillingClassID($AccountID);
        return BillingClass::getRoundChargesAmount($BillingClassID);
    }
}
