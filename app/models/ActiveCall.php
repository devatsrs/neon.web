<?php

class ActiveCall extends \Eloquent {

    protected $connection = 'sqlsrvroutingengine';
    protected $fillable = [];
    protected $guarded = array('ActiveCallID');
    protected $table = 'tblActiveCall';
    public  $primaryKey = "ActiveCallID"; //Used in BasedController

    public static function getActiveCallCost($ActiveCallID){
        $ActiveCall = ActiveCall::find($ActiveCallID);
        $AccountID = $ActiveCall->AccountID;
        $CompanyID = $ActiveCall->CompanyID;
        $CompanyCurrency = Company::where(['CompanyID'=>$CompanyID])->pluck('CurrencyId');
        $AccountCurrency = Account::where(["AccountID"=>$AccountID])->pluck('CurrencyId');
        $Cost = $ActiveCall->Cost;
        $CallType = $ActiveCall->CallType;
        $Duration = $ActiveCall->Duration;
        $BilledDuration = $Duration;
        $PackageCostPerMinute = 0;
        $RecordingCostPerMinute = 0;
        $CallRecordingDuration = $ActiveCall->CallRecordingDuration;
        $TaxRateIDs = $ActiveCall->TaxRateIDs;

        /**
         * Recording and packege cost from package
         * */
        if ($ActiveCall->CallRecording == 1) {
            $RateTablePKGRateID = $ActiveCall->RateTablePKGRateID;
            if(!empty($RateTablePKGRateID)){
                $RateTablePKGRate = DB::table('tblRateTablePKGRate')->where(['RateTablePKGRateID'=>$RateTablePKGRateID])->first();
                if(!empty($RateTablePKGRate)){
                    $PackageCostPerMinute = isset($RateTablePKGRate->PackageCostPerMinute)?$RateTablePKGRate->PackageCostPerMinute:0;
                    if(!empty($PackageCostPerMinute)){
                        if(!empty($RateTablePKGRate->PackageCostPerMinuteCurrency)) {
                            $PackageCostPerMinuteCurrency = $RateTablePKGRate->PackageCostPerMinuteCurrency;
                            $PackageCostPerMinute = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $PackageCostPerMinuteCurrency, $PackageCostPerMinute);
                        }
                        $PackageCostPerMinute = ($CallRecordingDuration * ($PackageCostPerMinute/60));
                    }

                    $RecordingCostPerMinute = isset($RateTablePKGRate->RecordingCostPerMinute)?$RateTablePKGRate->RecordingCostPerMinute:0;
                    if(!empty($RecordingCostPerMinute)){
                        if(!empty($RateTablePKGRate->RecordingCostPerMinuteCurrency)) {
                            $RecordingCostPerMinuteCurrency = $RateTablePKGRate->RecordingCostPerMinuteCurrency;
                            $RecordingCostPerMinute = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $RecordingCostPerMinuteCurrency, $RecordingCostPerMinute);
                        }
                        $RecordingCostPerMinute = ($CallRecordingDuration * ($RecordingCostPerMinute/60));
                    }
                }
            }
        }

        /** calculation outbound cost */

        if($CallType=='Outbound'){
            $CostPerMinute = 0;
            $CostPerCall = 0;
            $MinimumCallCharge = 0;
            $IsMinimumDuration = 0;
            $RateTableRateID = $ActiveCall->RateTableRateID;
            if($RateTableRateID>0){
                $RateTableRate = RateTableRate::find($RateTableRateID);
                $ConnectionFee = empty($RateTableRate->ConnectionFee)?0:$RateTableRate->ConnectionFee;
                if(!empty($ConnectionFee) && !empty($RateTableRate->ConnectionFeeCurrency)){
                    $ConnectionFee = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $RateTableRate->ConnectionFeeCurrency, $ConnectionFee);
                }
                $Interval1 = $RateTableRate->Interval1;
                $IntervalN = $RateTableRate->IntervalN;
                $Rate = $RateTableRate->Rate;
                $RateN = $RateTableRate->RateN;
                $MinimumDuration = empty($RateTableRate->MinimumDuration) ? 0 : $RateTableRate->MinimumDuration;
                if($MinimumDuration > $Duration){
                    $Duration = $MinimumDuration;
                    $IsMinimumDuration = 1;
                }
                if(!empty($RateTableRate->RateCurrency)){
                    if(!empty($Rate)){
                        $Rate = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $RateTableRate->RateCurrency,$Rate);
                    }
                    if(!empty($RateN)){
                        $RateN = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $RateTableRate->RateCurrency,$RateN);
                    }
                }
                /** cost update */

                if($Duration>=$Interval1){
                    $Cost = ($Rate/60.0)*$Interval1+ceil(($Duration-$Interval1)/$IntervalN)*($RateN/60.0)*$IntervalN+$ConnectionFee;
                    $CostPerMinute = ($Rate/60.0)*$Interval1+ceil(($Duration-$Interval1)/$IntervalN)*($RateN/60.0)*$IntervalN;
                    $CostPerCall = $ConnectionFee;

                }elseif($Duration > 0){
                    $Cost = $Rate+$ConnectionFee;
                    $CostPerMinute = $Rate;
                    $CostPerCall = $ConnectionFee;
                }else{
                    $Cost = 0;
                    $CostPerMinute = 0;
                    $CostPerCall = 0;
                }
                /** Billed Duration */
                if($Duration>=$Interval1){
                    $BilledDuration = $Interval1+ceil(($Duration-$Interval1)/$IntervalN)*$IntervalN;
                }elseif($Duration > 0){
                    $BilledDuration = $Interval1;
                }else{
                    $BilledDuration = 0;
                }

            }
            $Cost = $Cost + $PackageCostPerMinute + $RecordingCostPerMinute;

            /** minimum cost calculation
             * if cost is less than minimum cost , cost update as minimum cost
             */

            if(!empty($ActiveCall->RateTableID)) {
                $MinimumCallCharge = RateTable::where(['RateTableID' => $ActiveCall->RateTableID])->pluck('MinimumCallCharge');
                if (!empty($MinimumCallCharge)) {
                    $RateCurrency = RateTable::where(['RateTableID' => $ActiveCall->RateTableID])->pluck('CurrencyID');
                    if (!empty($RateCurrency)) {
                        $MinimumCallCharge = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $RateCurrency, $MinimumCallCharge);
                    }
                    if ($MinimumCallCharge > $Cost) {
                        $Cost = $MinimumCallCharge;
                        $MinimumCallCharge = 1;
                    }
                }else{
                    $MinimumCallCharge = 0;
                }
            }

            /** update cost and duration */

            $UpdateData = array();
            $UpdateData['billed_duration'] = $BilledDuration;
            $UpdateData['Cost'] = $Cost;
            $UpdateData['CostPerCall'] = $CostPerCall;
            $UpdateData['CostPerMinute'] = $CostPerMinute;
            $UpdateData['MinimumCallCharge'] = $MinimumCallCharge;
            $UpdateData['MinimumDuration'] = $IsMinimumDuration;
            $UpdateData['PackageCostPerMinute'] = $PackageCostPerMinute;
            $UpdateData['RecordingCostPerMinute'] = $RecordingCostPerMinute;
            $UpdateData['updated_at'] = date('Y-m-d H:i:s');
            $ActiveCall->update($UpdateData);

        }
        if($CallType=='Inbound'){
            $CostPerCall = 0;
            $CostPerMinute = 0;
            $SurchargePerCall = 0;
            $SurchargePerMinute = 0;
            $OutpaymentPerCall = 0;
            $OutpaymentPerMinute = 0;
            $Surcharges = 0;
            $CollectionCostAmount = 0;
            $CollectionCostPercentage = 0;
            $RateTableDIDRateID = $ActiveCall->RateTableDIDRateID;

            if($RateTableDIDRateID>0){
                $RateTableDIDRate = RateTableDIDRate::find($RateTableDIDRateID);

                if($Duration>0){

                    /**
                     * PerCall means - add direct cost
                     * PerMinute means - duration * cost
                    */

                    $CostPerCall = isset($RateTableDIDRate->CostPerCall)?$RateTableDIDRate->CostPerCall:0;
                    if(!empty($CostPerCall)){
                        if(!empty($RateTableDIDRate->CostPerCallCurrency)){
                            $CostPerCallCurrency = $RateTableDIDRate->CostPerCallCurrency;
                            $CostPerCall = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $CostPerCallCurrency, $CostPerCall);
                        }
                    }
                    $CostPerMinute = isset($RateTableDIDRate->CostPerMinute)?$RateTableDIDRate->CostPerMinute:0;
                    if(!empty($CostPerMinute)){
                        if(!empty($RateTableDIDRate->CostPerMinuteCurrency)) {
                            $CostPerMinuteCurrency = $RateTableDIDRate->CostPerMinuteCurrency;
                            $CostPerMinute = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $CostPerMinuteCurrency, $CostPerMinute);
                        }
                        $CostPerMinute = ($Duration * ($CostPerMinute/60));
                    }
                    $SurchargePerCall = isset($RateTableDIDRate->SurchargePerCall)?$RateTableDIDRate->SurchargePerCall:0;
                    if(!empty($SurchargePerCall)){
                        if(!empty($RateTableDIDRate->SurchargePerCallCurrency)) {
                            $SurchargePerCallCurrency = $RateTableDIDRate->SurchargePerCallCurrency;
                            $SurchargePerCall = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $SurchargePerCallCurrency, $SurchargePerCall);
                        }
                    }
                    $SurchargePerMinute = isset($RateTableDIDRate->SurchargePerMinute)?$RateTableDIDRate->SurchargePerMinute:0;
                    if(!empty($SurchargePerMinute)){
                        if(!empty($RateTableDIDRate->SurchargePerMinuteCurrency)) {
                            $SurchargePerMinuteCurrency = $RateTableDIDRate->SurchargePerMinuteCurrency;
                            $SurchargePerMinute = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $SurchargePerMinuteCurrency, $SurchargePerMinute);
                        }
                        $SurchargePerMinute = ($Duration * ($SurchargePerMinute/60));
                    }

                    /** Out Payment charge ***/
                    $OutpaymentPerCall = isset($RateTableDIDRate->OutpaymentPerCall)?$RateTableDIDRate->OutpaymentPerCall:0;
                    if(!empty($OutpaymentPerCall)){
                        if(!empty($RateTableDIDRate->OutpaymentPerCallCurrency)) {
                            $OutpaymentPerCallCurrency = $RateTableDIDRate->OutpaymentPerCallCurrency;
                            $OutpaymentPerCall = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $OutpaymentPerCallCurrency, $OutpaymentPerCall);
                        }
                    }
                    $OutpaymentPerMinute = isset($RateTableDIDRate->OutpaymentPerMinute)?$RateTableDIDRate->OutpaymentPerMinute:0;
                    if(!empty($OutpaymentPerMinute)){
                        if(!empty($RateTableDIDRate->OutpaymentPerMinuteCurrency)) {
                            $OutpaymentPerMinuteCurrency = $RateTableDIDRate->OutpaymentPerMinuteCurrency;
                            $OutpaymentPerMinute = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $OutpaymentPerMinuteCurrency, $OutpaymentPerMinute);
                        }
                        $OutpaymentPerMinute = ($Duration * ($OutpaymentPerMinute/60));
                    }

                    $Surcharges = isset($RateTableDIDRate->Surcharges)?$RateTableDIDRate->Surcharges:0;
                    if(!empty($Surcharges)){
                        if(!empty($RateTableDIDRate->SurchargesCurrency)) {
                            $SurchargesCurrency = $RateTableDIDRate->SurchargesCurrency;
                            $Surcharges = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $SurchargesCurrency, $Surcharges);
                        }
                        $Surcharges = ($Duration * ($Surcharges/60));
                    }

                    $CollectionCostAmount = isset($RateTableDIDRate->CollectionCostAmount)?$RateTableDIDRate->CollectionCostAmount:0;
                    if(!empty($CollectionCostAmount)){
                        if(!empty($RateTableDIDRate->CollectionCostAmountCurrency)) {
                            $CollectionCostAmountCurrency = $RateTableDIDRate->CollectionCostAmountCurrency;
                            $CollectionCostAmount = Currency::convertCurrency($CompanyCurrency, $AccountCurrency, $CollectionCostAmountCurrency, $CollectionCostAmount);
                        }
                    }

                    $Cost = $PackageCostPerMinute + $RecordingCostPerMinute + $CostPerCall + $CostPerMinute + $SurchargePerCall + $SurchargePerMinute + $Surcharges +$CollectionCostAmount;

                    $CollectionCostPercentage = isset($RateTableDIDRate->CollectionCostPercentage)?$RateTableDIDRate->CollectionCostPercentage:0;
                    if(!empty($CollectionCostPercentage)){
                        if(!empty($TaxRateIDs)){
                            $Cost = ActiveCall::getCostWithTaxes($Cost,$TaxRateIDs);
                        }
                        $CollectionCostPercentage = $Cost * ($CollectionCostPercentage/100);
                        $Cost = $Cost + $CollectionCostPercentage;
                    }
                }
            }

            $UpdateData = array();
            $UpdateData['billed_duration'] = $Duration;
            $UpdateData['Cost'] = $Cost;
            $UpdateData['CostPerCall'] = $CostPerCall;
            $UpdateData['CostPerMinute'] = $CostPerMinute;
            $UpdateData['SurchargePerCall'] = $SurchargePerCall;
            $UpdateData['SurchargePerMinute'] = $SurchargePerMinute;
            $UpdateData['OutpaymentPerCall'] = $OutpaymentPerCall;
            $UpdateData['OutpaymentPerMinute'] = $OutpaymentPerMinute;
            $UpdateData['Surcharges'] = $Surcharges;
            $UpdateData['CollectionCostAmount'] = $CollectionCostAmount;
            $UpdateData['CollectionCostPercentage'] = $CollectionCostPercentage;
            $UpdateData['PackageCostPerMinute'] = $PackageCostPerMinute;
            $UpdateData['RecordingCostPerMinute'] = $RecordingCostPerMinute;
            $UpdateData['updated_at'] = date('Y-m-d H:i:s');
            $ActiveCall->update($UpdateData);
        }
    }

    public static function updateActiveCall($ActiveCallID){
        //log::info('update active call start '.$ActiveCallID);
        $Response = array();
        $Response['Status'] = 'Success';
        $ActiveCall = ActiveCall::find($ActiveCallID);
        $AccountID = $ActiveCall->AccountID;
        $CompanyID = $ActiveCall->CompanyID;
        $CLIRateTableID = 0;
        $OutBoundRateTableID = 0;
        $OutBoundRateTableRateID = 0;
        $CLIPrefix = 'Other';
        $CLDPrefix = 'Other';
        $CompanyGatewayID = 0;
        $GatewayAccountPKID = 0;
        $Cost = $ActiveCall->Cost;
        $Duration = $ActiveCall->Duration;
        $AccountServiceID = 0;
        $RateTablePKGRateID = 0;
        $CallRecordingDuration = 0;
        $MinimumCallCharge = 0;
        $MinimumDuration = 0;
        //$AccountServiceID = AccountService::getFirstAccountServiceID($AccountID);
        /**
         * update gateway account
        **/

        $GatewayID = Gateway::where(['Name'=>'ManualCDR'])->pluck('GatewayID');
        $CompanyGateway = CompanyGateway::where(['GatewayID'=>$GatewayID,'Status'=>1])->first();
        if(!empty($CompanyGateway)){
            $CompanyGatewayID = $CompanyGateway->CompanyGatewayID;
        }

        $CLI=$ActiveCall->CLI;
        $CLD=$ActiveCall->CLD;
        $City = '';
        $Tariff = '';
        $NoType = '';
        $OutPaymentVendorID = 0;

        /**
         * CLI Authentication compulsory for api
         * if not found than return error
        */

        $AccountServicePackageID = 0;
        $PackageRateTableID = 0;
        //$CLIRateTable = CLIRateTable::where(['AccountID'=>$AccountID,'CLI'=>$CLD,'Status'=>1])->first();
        $ConnectTime = $ActiveCall->ConnectTime;
        $ConnectTime = date('Y-m-d',strtotime($ConnectTime));
        $CLIRateTable = CLIRateTable::where(['AccountID'=>$AccountID,'CLI'=>$CLD,'Status'=>1])->where('NumberStartDate','<=',$ConnectTime)->where('NumberEndDate','>=',$ConnectTime)->first();
        $AreaPrefix = '';
        $SpecialInboundRateTableID = 0;
        $SpecialOutBoundRateTableID = 0;
        if(!empty($CLIRateTable) && count($CLIRateTable)>0){
            $AccountServiceID = empty($CLIRateTable->AccountServiceID)?0:$CLIRateTable->AccountServiceID;
            //Access RateTable = Inbound Rate Table
            $CLIRateTableID = empty($CLIRateTable->RateTableID)?0:$CLIRateTable->RateTableID;
            $City = empty($CLIRateTable->City)?'':$CLIRateTable->City;
            $Tariff = empty($CLIRateTable->Tariff)?'':$CLIRateTable->Tariff;
            //$AccountServicePackageID =  empty($CLIRateTable->PackageID)?0:$CLIRateTable->PackageID;
            //$PackageRateTableID =  empty($CLIRateTable->PackageRateTableID)?0:$CLIRateTable->PackageRateTableID;
            $AreaPrefix = empty($CLIRateTable->Prefix)?'':$CLIRateTable->Prefix;
            $NoType = empty($CLIRateTable->NoType)?'':$CLIRateTable->NoType;
            $OutPaymentVendorID = empty($CLIRateTable->VendorID)?'':$CLIRateTable->VendorID;
            //TerminationRateTableID = outbound rate table id
            $OutBoundRateTableID = empty($CLIRateTable->TerminationRateTableID)?0:$CLIRateTable->TerminationRateTableID;
            $SpecialInboundRateTableID = empty($CLIRateTable->SpecialRateTableID)?0:$CLIRateTable->SpecialRateTableID;
            $SpecialOutBoundRateTableID = empty($CLIRateTable->SpecialTerminationRateTableID)?0:$CLIRateTable->SpecialTerminationRateTableID;
        }

        //log::info('Account Service ID '.$AccountServiceID);

        if(empty($AccountServiceID)){
            $Response['Status'] = 'Failed';
            $Response['Message'] = 'CLI not found';
            return $Response;
        }

        $ServiceID=AccountService::getServiceIDByAccountServiceID($AccountServiceID);
        //log::info('Service ID '.$ServiceID);
        $GatewayAccount = GatewayAccount::where(['CompanyID'=>$CompanyID,'CompanyGatewayID'=>$CompanyGatewayID,'GatewayAccountID'=>$CLI,'AccountID'=>$AccountID,'AccountCLI'=>$CLI,'AccountServiceID'=>$AccountServiceID])->first();
        if(empty($GatewayAccount)){
            $GatewayAccountData = array();
            $GatewayAccountData['CompanyID']=$CompanyID;
            $GatewayAccountData['CompanyGatewayID']=$CompanyGatewayID;
            $GatewayAccountData['GatewayAccountID']=$CLI;
            $GatewayAccountData['AccountID']=$AccountID;
            $GatewayAccountData['AccountCLI']=$CLI;
            $GatewayAccountData['ServiceID']=$ServiceID;
            $GatewayAccountData['AccountServiceID']=$AccountServiceID;
            $GatewayAccount = GatewayAccount::create($GatewayAccountData);
        }
        $GatewayAccountPKID = $GatewayAccount->GatewayAccountPKID;
        //log::info('GatewayAccount PKID '.$GatewayAccountPKID);
        /**
         * TimeZone ID update
        */


        if(!empty($ActiveCall->DisconnectTime)) {
            $TimezonesID = Timezones::getTimeZoneByConnectAndDisconnectTime($ActiveCall->ConnectTime,$ActiveCall->DisconnectTime);
        } else {
            $TimezonesID = Timezones::getTimeZoneByConnectTime($ActiveCall->ConnectTime);
        }
        $SpecialTimezonesID = $TimezonesID;

        $CallType = $ActiveCall->CallType;
        $PackageTimezonesID = 0;
        /* Package Rate Table find start */
        $AccountServicePackage = AccountServicePackage::where(['AccountID'=>$AccountID,'AccountServiceID'=>$AccountServiceID,'Status'=>1])->where('PackageStartDate','<=',$ConnectTime)->where('PackageEndDate','>=',$ConnectTime)->first();
        if(!empty($AccountServicePackage)) {
            $AccountServicePackageID = $AccountServicePackage->PackageId;
            $PackageRateTableID = $AccountServicePackage->RateTableID;
            $SpecialPackageRateTableID = empty($AccountServicePackage->SpecialPackageRateTableID) ? 0 : $AccountServicePackage->SpecialPackageRateTableID;
            $SpecialPackage = 0;
            if ($SpecialPackageRateTableID > 0){
                $RateTablePKGRateID = ActiveCall::getRateTablePKGRateID($CompanyID, $SpecialPackageRateTableID, $TimezonesID, $AccountServicePackageID);
                if (!empty($RateTablePKGRateID)) {
                    $PackageTimezonesID = DB::table('tblRateTablePKGRate')->where(['RateTablePKGRateID' => $RateTablePKGRateID])->pluck('TimezonesID');
                    $SpecialPackage = 1;
                }
            }

            if($SpecialPackage == 0) {
                $RateTablePKGRateID = ActiveCall::getRateTablePKGRateID($CompanyID, $PackageRateTableID, $TimezonesID, $AccountServicePackageID);
                if (!empty($RateTablePKGRateID)) {
                    $PackageTimezonesID = DB::table('tblRateTablePKGRate')->where(['RateTablePKGRateID' => $RateTablePKGRateID])->pluck('TimezonesID');
                }
            }
        }

        /** find and update taxes */
        $TaxRateIDs=ActiveCall::getAccountTaxes($AccountID);
        if(empty($TaxRateIDs)){
            $TaxRateIDs = '';
        }


        /** outbound Field Update */
        if($CallType=='Outbound'){

            //$OutBoundRateTableID =  AccountTariff::where(['AccountID'=>$AccountID,'AccountServiceID'=>$AccountServiceID,'Type'=>AccountTariff::OUTBOUND])->pluck('RateTableID');
            if(empty($OutBoundRateTableID) && empty($SpecialOutBoundRateTableID)){
                $Response['Status'] = 'Failed';
                $Response['Message'] = 'Outbound Rate Table not found';
                return $Response;
            }

            $RateTableRateCount = RateTableRate::where(['RateTableId'=>$OutBoundRateTableID,'TimezonesID'=>$TimezonesID])->count();
            if($RateTableRateCount==0){
                $TimezonesID = 1;
            }

            if($SpecialOutBoundRateTableID > 0){
                $SpecialRateTableRateCount = RateTableRate::where(['RateTableId'=>$SpecialOutBoundRateTableID,'TimezonesID'=>$SpecialTimezonesID])->count();
                if($SpecialRateTableRateCount==0){
                    $SpecialTimezonesID = 1;
                }
            }

            /**
             * find Prefix for cli
            */

            $OutBoundSpecial = 0;
            if($SpecialOutBoundRateTableID > 0 ){
                $Result = DB::connection('sqlsrv')->select('CALL  prc_FindApiOutBoundPrefix( ' . $CompanyID . "," . $SpecialOutBoundRateTableID ."," . $SpecialTimezonesID .",".$CLI.",'".$CLD."')");
                //log::info('CALL  prc_FindApiOutBoundPrefix( ' . $CompanyID . "," . $OutBoundRateTableID ."," . $TimezonesID .",".$CLI.",'".$CLD."')");
                if(count($Result) >0){
                    $OutBoundSpecial = 1;
                    $OutBoundRateTableRateID = $Result[0]->RateTableRateID;
                    $CLIPrefix = $Result[0]->OriginationCode;
                    $CLDPrefix = $Result[0]->DestincationCode;
                    $OutBoundRateTableID = $SpecialOutBoundRateTableID;
                    $TimezonesID = $SpecialTimezonesID;
                }
            }
            if($OutBoundSpecial==0){
                $Result = DB::connection('sqlsrv')->select('CALL  prc_FindApiOutBoundPrefix( ' . $CompanyID . "," . $OutBoundRateTableID ."," . $TimezonesID .",".$CLI.",'".$CLD."')");
                //log::info('CALL  prc_FindApiOutBoundPrefix( ' . $CompanyID . "," . $OutBoundRateTableID ."," . $TimezonesID .",".$CLI.",'".$CLD."')");
                if(count($Result) >0){
                    $OutBoundRateTableRateID = $Result[0]->RateTableRateID;
                    $CLIPrefix = $Result[0]->OriginationCode;
                    $CLDPrefix = $Result[0]->DestincationCode;
                }else{
                    $Response['Status'] = 'Failed';
                    $Response['Message'] = 'Outbound Rate not found';
                    return $Response;
                }
            }

            $UpdateData = array();
            $UpdateData['CompanyGatewayID'] = $CompanyGatewayID;
            $UpdateData['GatewayAccountPKID'] = $GatewayAccountPKID;
            $UpdateData['AccountServiceID'] = $AccountServiceID;
            $UpdateData['ServiceID'] = $ServiceID;
            $UpdateData['CLIPrefix'] = $CLIPrefix;
            $UpdateData['CLDPrefix'] = $CLDPrefix;
            $UpdateData['RateTableID'] = $OutBoundRateTableID;
            $UpdateData['RateTableRateID'] = $OutBoundRateTableRateID;
            $UpdateData['RateTableDIDRateID'] = 0;
            $UpdateData['TimezonesID'] = $TimezonesID;
            $UpdateData['Duration'] = $Duration;
            $UpdateData['billed_duration'] = $Duration;
            $UpdateData['Cost'] = $Cost;
            $UpdateData['RateTablePKGRateID'] = $RateTablePKGRateID;
            $UpdateData['CallRecordingDuration'] = $CallRecordingDuration;
            $UpdateData['AccountServicePackageID'] = $AccountServicePackageID;
            $UpdateData['TaxRateIDs'] = $TaxRateIDs;
            $UpdateData['PackageTimezonesID'] = $PackageTimezonesID;
            $UpdateData['City'] = $City;
            $UpdateData['Tariff'] = $Tariff;
            $UpdateData['NoType'] = $NoType;
            $UpdateData['MinimumCallCharge'] = $MinimumCallCharge;
            $UpdateData['MinimumDuration'] = $MinimumDuration;
            $UpdateData['OutPaymentVendorID'] = $OutPaymentVendorID;
            $UpdateData['updated_at'] = date('Y-m-d H:i:s');
            $ActiveCall->update($UpdateData);
        }

        if($CallType=='Inbound'){
            if(empty($AreaPrefix)){
                $Response['Status'] = 'Failed';
                $Response['Message'] = 'InBound Rate not found';
                return $Response;
            }
            if(empty($CLIRateTableID) && empty($SpecialInboundRateTableID)) {
                $Response['Status'] = 'Failed';
                $Response['Message'] = 'Inbound Rate Table not found';
                return $Response;
            }else{
                $InboundRateTableID = $CLIRateTableID;
            }

            $RateTableRateCount = RateTableDIDRate::where(['RateTableId'=>$InboundRateTableID,'TimezonesID'=>$TimezonesID])->count();
            if($RateTableRateCount==0){
                $TimezonesID = 1;
            }

            if($SpecialInboundRateTableID > 0){
                $SpecialRateTableRateCount = RateTableRate::where(['RateTableId'=>$SpecialInboundRateTableID,'TimezonesID'=>$SpecialTimezonesID])->count();
                if($SpecialRateTableRateCount==0){
                    $SpecialTimezonesID = 1;
                }
            }

            /**
             * find Prefix for cli
             */

            $RateTableDIDRateID = 0;
            $OriginType = empty($ActiveCall->OriginType) ? '' : str_replace('-','',$ActiveCall->OriginType);
            $OriginProvider = empty($ActiveCall->OriginProvider) ? '' : str_replace('-','',$ActiveCall->OriginProvider);
            $InBoundSpecial = 0;
            if($SpecialInboundRateTableID > 0){
                $Result = DB::connection('sqlsrv')->select('CALL  prc_FindApiInBoundPrefix( ' . $CompanyID . "," . $SpecialInboundRateTableID ."," . $SpecialTimezonesID .",'".$CLI."','".$CLD."','".$City."','".$Tariff."','".$OriginType."','".$OriginProvider."','".$AreaPrefix."','".$NoType."')");
                //log::info(" start Call prc_FindApiInBoundPrefix( " . $CompanyID . "," . $SpecialInboundRateTableID ."," . $SpecialTimezonesID .",'".$CLI."','".$CLD."','".$City."','".$Tariff."','".$OriginType."','".$OriginProvider."','".$AreaPrefix."','".$NoType."')");
                if(count($Result) >0){
                    $InBoundSpecial = 1;
                    $RateTableDIDRateID = $Result[0]->RateTableDIDRateID;
                    $CLIPrefix = $Result[0]->OriginationCode;
                    $CLDPrefix = $Result[0]->DestincationCode;
                }
            }
            if($InBoundSpecial==0){
                $Result = DB::connection('sqlsrv')->select('CALL  prc_FindApiInBoundPrefix( ' . $CompanyID . "," . $InboundRateTableID ."," . $TimezonesID .",'".$CLI."','".$CLD."','".$City."','".$Tariff."','".$OriginType."','".$OriginProvider."','".$AreaPrefix."','".$NoType."')");
                //log::info(" start Call prc_FindApiInBoundPrefix( " . $CompanyID . "," . $InboundRateTableID ."," . $TimezonesID .",'".$CLI."','".$CLD."','".$City."','".$Tariff."','".$OriginType."','".$OriginProvider."','".$AreaPrefix."','".$NoType."')");
                if(count($Result) >0){
                    $RateTableDIDRateID = $Result[0]->RateTableDIDRateID;
                    $CLIPrefix = $Result[0]->OriginationCode;
                    $CLDPrefix = $Result[0]->DestincationCode;
                }else{
                    $Response['Status'] = 'Failed';
                    $Response['Message'] = 'InBound Rate not found';
                    return $Response;
                }
            }

            $RateTableDIDRate = RateTableDIDRate::find($RateTableDIDRateID);
            $InboundRateTableID = $RateTableDIDRate->RateTableId;
            $TimezonesID = $RateTableDIDRate->TimezonesID;

            $UpdateData = array();
            $UpdateData['CompanyGatewayID'] = $CompanyGatewayID;
            $UpdateData['GatewayAccountPKID'] = $GatewayAccountPKID;
            $UpdateData['AccountServiceID'] = $AccountServiceID;
            $UpdateData['ServiceID'] = $ServiceID;
            $UpdateData['CLIPrefix'] = empty($CLIPrefix)?'Other':$CLIPrefix;
            $UpdateData['CLDPrefix'] = empty($CLDPrefix)?'Other':$CLDPrefix;
            $UpdateData['RateTableID'] = $InboundRateTableID;
            $UpdateData['RateTableRateID'] = 0;
            $UpdateData['RateTableDIDRateID'] = $RateTableDIDRateID;
            $UpdateData['TimezonesID'] = $TimezonesID;
            $UpdateData['Duration'] = $Duration;
            $UpdateData['billed_duration'] = $Duration;
            $UpdateData['Cost'] = $Cost;
            $UpdateData['RateTablePKGRateID'] = $RateTablePKGRateID;
            $UpdateData['CallRecordingDuration'] = $CallRecordingDuration;
            $UpdateData['AccountServicePackageID'] = $AccountServicePackageID;
            $UpdateData['TaxRateIDs'] = $TaxRateIDs;
            $UpdateData['PackageTimezonesID'] = $PackageTimezonesID;
            $UpdateData['City'] = $City;
            $UpdateData['Tariff'] = $Tariff;
            $UpdateData['NoType'] = $NoType;
            $UpdateData['MinimumCallCharge'] = $MinimumCallCharge;
            $UpdateData['MinimumDuration'] = $MinimumDuration;
            $UpdateData['OutPaymentVendorID'] = $OutPaymentVendorID;
            $UpdateData['updated_at'] = date('Y-m-d H:i:s');
            $ActiveCall->update($UpdateData);

            return $Response;
        }
        //log::info('update active call end '.$ActiveCallID);
        return $Response;
    }

    public static function insertActiveCallCDR($ActiveCallID){
        $ActiveCall = ActiveCall::find($ActiveCallID);
        $AccountID = $ActiveCall->AccountID;
        $StartDate = date('Y-m-d',strtotime($ActiveCall->ConnectTime));
        $CompanyGatewayID = $ActiveCall->CompanyGatewayID;
        $AccountServiceID = $ActiveCall->AccountServiceID;
        $GatewayAccountPKID = $ActiveCall->GatewayAccountPKID;
        $GatewayAccountID = GatewayAccount::where(['GatewayAccountPKID'=>$GatewayAccountPKID])->pluck('GatewayAccountID');
        $CompanyID = $ActiveCall->CompanyID;
        $ServiceID = $ActiveCall->ServiceID;
        $Header = UsageHeader::where(['AccountID'=>$AccountID,'StartDate'=>$StartDate,'CompanyGatewayID'=>$CompanyGatewayID,'GatewayAccountID'=>$GatewayAccountID,'AccountServiceID'=>$AccountServiceID,'GatewayAccountPKID'=>$GatewayAccountPKID])->first();
        if(!empty($Header)){
            $UsageHeaderID = $Header->UsageHeaderID;
        }else{
            $data=array();
            $data['AccountID']=$AccountID;
            $data['CompanyID']=$CompanyID;
            $data['CompanyGatewayID']=$CompanyGatewayID;
            $data['GatewayAccountID']=$GatewayAccountID;
            $data['StartDate']=$StartDate;
            $data['ServiceID']=$ServiceID;
            $data['AccountServiceID']=$AccountServiceID;
            $data['GatewayAccountPKID']=$GatewayAccountPKID;
            $data['created_at']=date('Y-m-d H:i:s');
            $data['updated_at']=date('Y-m-d H:i:s');
            $Header = UsageHeader::create($data);
            $UsageHeaderID = $Header->UsageHeaderID;
        }
        /** Detail data */
        $detaildata = array();
        $detaildata['UsageHeaderID']=$UsageHeaderID;
        $detaildata['connect_time']=$ActiveCall->ConnectTime;
        $detaildata['disconnect_time']=$ActiveCall->DisconnectTime;
        $detaildata['duration']=$ActiveCall->Duration;
        $detaildata['billed_duration']=$ActiveCall->billed_duration;
        $detaildata['billed_second']=$ActiveCall->Duration;
        $detaildata['area_prefix']=$ActiveCall->CLDPrefix; //cldprefix
        $detaildata['CLIPrefix']=$ActiveCall->CLIPrefix; //cldprefix
        $detaildata['cli']=$ActiveCall->CLI;
        $detaildata['cld']=$ActiveCall->CLD;
        $detaildata['cost']=$ActiveCall->Cost;
        $detaildata['ProcessID']='';
        $detaildata['ID']='';
        $detaildata['UUID']=$ActiveCall->UUID;
        $detaildata['OutpaymentPerCall'] = $ActiveCall->OutpaymentPerCall;
        $detaildata['OutpaymentPerMinute'] = $ActiveCall->OutpaymentPerMinute;
        $detaildata['Surcharges'] = $ActiveCall->Surcharges;
        $detaildata['CollectionCostAmount'] = $ActiveCall->CollectionCostAmount;
        $detaildata['CollectionCostPercentage'] = $ActiveCall->CollectionCostPercentage;
        $detaildata['RecordingCostPerMinute'] = $ActiveCall->RecordingCostPerMinute;
        $detaildata['PackageCostPerMinute'] = $ActiveCall->PackageCostPerMinute;
        $detaildata['AccountServicePackageID'] = $ActiveCall->AccountServicePackageID;
        $detaildata['CallRecording'] = $ActiveCall->CallRecording;
        $detaildata['CallRecordingStartTime'] = $ActiveCall->CallRecordingStartTime;
        $detaildata['OriginType'] = $ActiveCall->OriginType;
        $detaildata['OriginProvider'] = $ActiveCall->OriginProvider;
        $detaildata['TimezonesID'] = $ActiveCall->TimezonesID;
        $detaildata['PackageTimezonesID'] = $ActiveCall->PackageTimezonesID;
        $detaildata['City'] = $ActiveCall->City;
        $detaildata['Tariff'] = $ActiveCall->Tariff;
        $detaildata['NoType'] = $ActiveCall->NoType;

        $is_inbound=0;
        if($ActiveCall->CallType=='Inbound'){
            $is_inbound=1;
        }
        $detaildata['is_inbound']=$is_inbound;
        //if block
        if($ActiveCall->IsBlock==1) {
            $detaildata['disposition'] = 'Blocked';
            $detaildata['BlockReason'] = $ActiveCall->BlockReason;
        }
        $detaildata['CostPerCall'] = $ActiveCall->CostPerCall;
        $detaildata['CostPerMinute'] = $ActiveCall->CostPerMinute;
        $detaildata['SurchargePerCall'] = $ActiveCall->SurchargePerCall;
        $detaildata['SurchargePerMinute'] = $ActiveCall->SurchargePerMinute;
        $detaildata['MinimumCallCharge'] = $ActiveCall->MinimumCallCharge;
        $detaildata['MinimumDuration'] = $ActiveCall->MinimumDuration;

        $UsageDetails = UsageDetail::create($detaildata);
        $UsageDetailID = $UsageDetails->UsageDetailID;
        //update id
        $updateid=array();
        $updateid['ID']=$UsageDetailID;
        UsageDetail::where(['UsageDetailID'=>$UsageDetailID])->update($updateid);

        if(!empty($ActiveCall->VendorID) && $ActiveCall->CallType=='Outbound'){
            $Account = Account::where(['AccountID'=>$ActiveCall->VendorID])->first();
            $trunk = 'Other';
            $TrunkID = VendorConnection::where(['AccountId'=>$ActiveCall->VendorID,'Name'=>$ActiveCall->VendorConnectionName])->pluck('TrunkID');
            if(!empty($TrunkID)){
                $trunk = Trunk::where(['TrunkID'=>$TrunkID])->pluck('Trunk');
            }
            $buying_cost = $ActiveCall->billed_duration * ($ActiveCall->VendorRate/60);
            $VendorGatewayAccount = GatewayAccount::where(['CompanyID'=>$CompanyID,'CompanyGatewayID'=>$CompanyGatewayID,'GatewayAccountID'=>$Account->AccountName,'AccountName'=>$Account->AccountName,'AccountID'=>$ActiveCall->VendorID,'IsVendor'=>1,'AccountServiceID'=>0,'ServiceID'=>0])->first();
            if(empty($VendorGatewayAccount)){
                $VendorGatewayAccountData = array();
                $VendorGatewayAccountData['CompanyID']=$CompanyID;
                $VendorGatewayAccountData['CompanyGatewayID']=$CompanyGatewayID;
                $VendorGatewayAccountData['GatewayAccountID']=$Account->AccountName;
                $VendorGatewayAccountData['AccountID']=$AccountID;
                $VendorGatewayAccountData['AccountName']=$Account->AccountName;
                $VendorGatewayAccountData['ServiceID']=0;
                $VendorGatewayAccountData['AccountServiceID']=0;
                $VendorGatewayAccountData['IsVendor']=1;
                $VendorGatewayAccount = GatewayAccount::create($VendorGatewayAccountData);
            }
            $VendorGatewayAccountPKID = $VendorGatewayAccount->GatewayAccountPKID;

            $VendorHeader = VendorCDRHeader::where(['AccountID'=>$AccountID,'StartDate'=>$StartDate,'CompanyGatewayID'=>$CompanyGatewayID,'GatewayAccountID'=>$Account->AccountName,'AccountServiceID'=>0,'GatewayAccountPKID'=>$VendorGatewayAccountPKID])->first();
            if(!empty($VendorHeader)){
                $VendorCDRHeaderID = $VendorHeader->VendorCDRHeaderID;
            }else{
                $vendordata=array();
                $vendordata['AccountID']=$AccountID;
                $vendordata['CompanyID']=$CompanyID;
                $vendordata['CompanyGatewayID']=$CompanyGatewayID;
                $vendordata['GatewayAccountID']=$Account->AccountName;
                $vendordata['StartDate']=$StartDate;
                $vendordata['ServiceID']=0;
                $vendordata['AccountServiceID']=0;
                $vendordata['GatewayAccountPKID']=$VendorGatewayAccountPKID;
                $vendordata['created_at']=date('Y-m-d H:i:s');
                $vendordata['updated_at']=date('Y-m-d H:i:s');
                $VendorHeader = VendorCDRHeader::create($vendordata);
                $VendorCDRHeaderID = $VendorHeader->VendorCDRHeaderID;
            }
            $vendordetaildata = array();
            $vendordetaildata['VendorCDRHeaderID']=$VendorCDRHeaderID;
            $vendordetaildata['connect_time']=$ActiveCall->ConnectTime;
            $vendordetaildata['disconnect_time']=$ActiveCall->DisconnectTime;
            $vendordetaildata['duration']=$ActiveCall->Duration;
            $vendordetaildata['billed_duration']=$ActiveCall->billed_duration;
            $vendordetaildata['billed_second']=$ActiveCall->Duration;
            $vendordetaildata['area_prefix']=$ActiveCall->VendorCLDPrefix; //cldprefix
            $vendordetaildata['CLIPrefix']=$ActiveCall->VendorCLIPrefix; //cldprefix
            $vendordetaildata['cli']=$ActiveCall->CLI;
            $vendordetaildata['cld']=$ActiveCall->CLD;
            $vendordetaildata['selling_cost']=$ActiveCall->Cost;
            $vendordetaildata['buying_cost']=$buying_cost;
            $vendordetaildata['ProcessID']='';
            $vendordetaildata['ID']=$UsageDetailID;
            $vendordetaildata['UUID']=$ActiveCall->UUID;
            $vendordetaildata['trunk']=$trunk;
            VendorCDR::create($vendordetaildata);
        }

        //OutPayment Insert start
        if(!empty($ActiveCall->OutPaymentVendorID) && $ActiveCall->CallType=='Inbound'){
            $OutpaymentPerCall = $ActiveCall->OutpaymentPerCall;
            $OutpaymentPerMinute = $ActiveCall->OutpaymentPerMinute;
            $Amount = $OutpaymentPerCall + $OutpaymentPerMinute;
            if(!empty($Amount)){
                ActiveCall::insertOutPayment($ActiveCall,$Amount);
            }

        }
    }

    public static function getRateTablePKGRateID($CompanyID,$RateTableID,$TimezonesID,$PackageId){
        $RateTablePKGRateID = 0;
        $Code = Package::where('PackageId',$PackageId)->pluck('Name');
        $CodeDeckID = RateTable::where(['RateTableId'=>$RateTableID])->pluck('CodeDeckId');
        $RateID = CodeDeck::where(['CodeDeckId'=>$CodeDeckID,'Code'=>$Code])->pluck('RateID');
        if(!empty($RateID)){
            $RateTableRateCount = DB::table('tblRateTablePKGRate')->where(['RateTableId'=>$RateTableID,'TimezonesID'=>$TimezonesID])->count();
            if($RateTableRateCount==0){
                $TimezonesID = 1;
            }
            $RateTable = DB::table('tblRateTablePKGRate')->where(['RateTableId'=>$RateTableID,'RateID'=>$RateID,'TimezonesID'=>$TimezonesID,'ApprovedStatus'=>1])
                ->where('EffectiveDate','<=',date('Y-m-d'))->first();
            if(!empty($RateTable)){
                $RateTablePKGRateID = $RateTable->RateTablePKGRateID;
            }
        }
        return $RateTablePKGRateID;
    }

    /** Get Usage and all over taxes of account */
    public static function getAccountTaxes($AccountID){
        //$BillingClassID = AccountBilling::getBillingClassID($AccountID);
        $final = '';
        //if(!empty($BillingClassID)) {
            //$result = BillingClass::where('BillingClassID', $BillingClassID)->pluck('TaxRateID');
            $result = Account::where('AccountID', $AccountID)->pluck('TaxRateID');
            $resultarray = explode(",", $result);
            if(!empty($resultarray) && count($resultarray)>0) {
                foreach ($resultarray as $resultdata) {
                    if (TaxRate::where(['TaxRateId' => $resultdata, 'TaxType' => TaxRate::TAX_ALL])->count()) {
                        $final .= $resultdata . ',';
                    }
                    if (TaxRate::where(['TaxRateId' => $resultdata, 'TaxType' => TaxRate::TAX_USAGE])->count()) {
                        $final .= $resultdata . ',';
                    }

                }
                $final = rtrim($final, ',');
            }
        //}
        return $final;
    }
    public static function getCostWithTaxes($Cost,$TaxRateIDs){
        $TaxGrandTotal = 0;
        $TaxRateIDs = explode(",",$TaxRateIDs);
        if(!empty($TaxRateIDs) && count($TaxRateIDs)>0) {
            foreach($TaxRateIDs as $TaxRateID) {
                $TaxRateID = intval($TaxRateID);
                if($TaxRateID>0){
                    $TaxAmount=TaxRate::calculateProductTaxAmount($TaxRateID,$Cost);
                    $TaxGrandTotal += $TaxAmount;
                }
            }
        }
        $Total = $Cost + $TaxGrandTotal;

        return $Total;
    }

    public static function insertOutPayment($ActiveCall,$Amount){
        $AccountID = $ActiveCall->AccountID;
        $CompanyID = $ActiveCall->CompanyID;
        $VendorID = $ActiveCall->OutPaymentVendorID;
        $CLI = $ActiveCall->CLI;
        $Date = date('Y-m-d',strtotime($ActiveCall->ConnectTime));
        $OutPaymentLog = OutPaymentLog::where(['AccountID'=>$AccountID,'VendorID'=>$VendorID,'CLI'=>$CLI,'Date'=>$Date])->first();
        if(!empty($OutPaymentLog)){
            // if already amount than plus
            $OutPaymentLogAmount = $OutPaymentLog->Amount + $Amount;
            $data=array();
            $data['Amount'] = $OutPaymentLogAmount;
            $OutPaymentLog->update($data);
        }else{
            $data=array();
            $data['CompanyID'] = $CompanyID;
            $data['AccountID'] = $AccountID;
            $data['VendorID'] = $VendorID;
            $data['CLI'] = $CLI;
            $data['Date'] = $Date;
            $data['Amount'] = $Amount;
            $data['Status'] = 0;
            $data['created_at'] = date('Y-m-d H:i:s');
            OutPaymentLog::insert($data);
        }
    }
}