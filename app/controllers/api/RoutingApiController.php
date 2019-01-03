<?php

/**
 * Created by PhpStorm.
 * User: Aamar Nazir
 * Date: 28/12/2018
 * Time: 12:47 AM
 */
class RoutingApiController extends ApiController {
    public function routingList()
    {
        Log::info('routingList:Get the routing list.');
        $routingData = Input::all();
        $lcrDetails = '';
        $CompanyID = User::get_companyID();
        $rules = array(
            'OriginationNo' => 'required',
            'DestinationNo' => 'required',
            'ConnectTime' => 'required',
            'Number' => 'required_without_all:CustomerID',
            'CustomerID' => 'required_without_all:Number',
        );
        $validator = Validator::make($routingData, $rules);

        if ($validator->fails()) {
            $errors = "";
            foreach ($validator->messages()->all() as $error) {
                $errors .= $error . "<br>";
            }
            return Response::json(["status" => "failed", "message" => $errors]);
        }

        $profiles = '';
        $RoutingProfileId = array();
        $CustomerProfileAccountID = '';
        if (isset($routingData["Number"]) && $routingData["Number"] != '') {
            $CustomerProfileAccountID = AccountRoutingProfile::where(["AccountNumber" => $routingData["Number"]])->pluck("AccountID");
        }else {
            $CustomerProfileAccountID = AccountRoutingProfile::where(["AccountID" => $routingData["CustomerID"]])->pluck("AccountID");
        }
        Log::info('routingList:Get the routing list count.' . $CustomerProfileAccountID);

        $profiles = '';


        $RoutingProfileId = '';
        $RoutingProfileIds = '';

        if (empty($CustomerProfileAccountID) ) {
            return Response::json(["status" => "failed", "message" => "No Profile found against the Number/CustomerID"]);
        }
        Log::info('routingList:Get the routing list count.' . $CustomerProfileAccountID);



        $removePlusSign = '';
        $Prefix = '';
        //trim and replace CodeComment#1
        $routingData["OriginationNo"] = trim(str_replace("+","",$routingData["OriginationNo"]));


        //trim and replace CodeComment#2
        $routingData["DestinationNo"] = trim(str_replace("+","",$routingData["DestinationNo"]));


                $lcrDetails = RoutingProfileRate::select(['RoutingProfileId','selectionCode'])->
                    whereRaw($routingData["DestinationNo"] . ' like  CONCAT(tblRoutingProfileRate.selectionCode,"%")')
                                ->orderByRaw('CONCAT(tblRoutingProfileRate.selectionCode,"%") desc')
                                ->take(1);
        Log::info('routingList profiles case 1 query with RoutingProfileRate Query' . $lcrDetails->toSql());
                $lcrDetails= $lcrDetails->get();

                Log::info('routingList profiles case 1 query with RoutingProfileRate ' . count($lcrDetails));
                if (count($lcrDetails) > 0) {
                    foreach ($lcrDetails as $lcrDetail) {

                    }
                    $RoutingProfileIds = $lcrDetail->RoutingProfileId;
                    $Prefix = $lcrDetail->selectionCode;
                    Log::info('routingList profiles case 1 query with RoutingProfileRate ' . $RoutingProfileIds);
                }else {
                    $CustomerTrunks = CustomerTrunk::select(['AccountID','TrunkID','Prefix'])
                    ->where('UseInBilling','=',1)
                    ->whereRaw('\'' . $routingData["DestinationNo"] . '\'' . ' like  CONCAT(Prefix,"%")')
                    ->orderByRaw('CONCAT(Prefix,"%") desc')
                    ->take(1);
                    Log::info('routingList profiles case 2 query with RoutingProfileRate ' . $CustomerTrunks->toSql());
                    $CustomerTrunks = $CustomerTrunks->get();
                    if (count($CustomerTrunks) > 0) {
                        foreach ($CustomerTrunks as $CustomerTrunk) {

                        }
                        $TrunkAccountProfiles = AccountRoutingProfile::
                            where(["AccountID" => $CustomerTrunk->AccountID])
                            ->where(["TrunkID" => $CustomerTrunk->TrunkID])
                            ->pluck("RoutingProfileId");
                        Log::info('routingList profiles case 2 query with RoutingProfileRate ' . $TrunkAccountProfiles);
                        if (!empty($TrunkAccountProfiles)) {
                            $RoutingProfileIds = $TrunkAccountProfiles;
                            $Prefix = $CustomerTrunk->Prefix;
                        }
                    }else {
                        $CLIRateTables = CLIRateTable::select(['AccountID', 'ServiceID','CLI'])
                        ->where('CLI', '=', $routingData["OriginationNo"]);
                        Log::info('routingList profiles case 3 query with RoutingProfileRate ' . $CLIRateTables->toSql());
                        $CLIRateTables = $CLIRateTables->get();


                        if (count($CLIRateTables) > 0) {
                           // $CLIRateTable = array_shift($CLIRateTables);
                            foreach ($CLIRateTables as $CLIRateTable) {

                            }
                            $VendorDetails = '';
                            Log::info('routingList profiles case 3 query with RoutingProfileRate ' . $CLIRateTable->AccountID . ' ' . $CLIRateTable->ServiceID);
                            $AccountProfiles = AccountRoutingProfile::
                                            where(["AccountID" => $CLIRateTable->AccountID])
                                            ->where('ServiceID', '=', $CLIRateTable->ServiceID)->pluck("RoutingProfileId");
                            Log::info('routingList profiles case 3 query with RoutingProfileRate ' . $AccountProfiles);

                            if (empty($AccountProfiles)) {
                                $AccountProfiles = AccountRoutingProfile::
                                where(["AccountID" => $CLIRateTable->AccountID])
                                    ->pluck("RoutingProfileId");
                                Log::info('routingList profiles case 3 query with RoutingProfileRate ' . $AccountProfiles);

                            }


                            if (!empty($AccountProfiles)) {

                                $RoutingProfileIds = $AccountProfiles;
                                $Prefix = $CLIRateTable->CLI;
                            }
                        }
                    }
                }

                Log::info('Filter Routing Profile List procedure $RoutingProfileIds' . $RoutingProfileIds);
                $connectTime = strtotime($routingData["ConnectTime"]);
                $dataTimeZone['CompanyID'] = $CompanyID;
                $dataTimeZone['connect_time'] = $routingData["ConnectTime"];
                $dataTimeZone['disconnect_time'] = $routingData["ConnectTime"];
               // $dataTimeZone['TimezonesID'] = '';
        Log::info('Filter Routing Profile List procedure $queryTimeZone' .
            print_r($dataTimeZone,true));
                $GetTimeZone = GetTimeZone::create($dataTimeZone);
                $query="CALL `prc_updateTempCDRTimeZones`('tblgetTimezone')";
                $queryResults = DB::connection('sqlsrv2')->select($query);
                $queryTimeZone = GetTimeZone::
                 where(["connect_time" => $routingData["ConnectTime"]])
                 ->where('disconnect_time', '=', $routingData["ConnectTime"])->pluck("TimezonesID");

        if (empty($queryTimeZone)) {
            $queryTimeZone = 1;
        }
        Log::info('Filter Routing Profile List procedure $queryTimeZone' . $queryTimeZone);
        Log::info('Filter Routing Profile List procedure $GetTimeZone' .
            print_r($GetTimeZone,true));
        GetTimeZone::where(array('getTimezoneID'=>$GetTimeZone->getTimezoneID))->delete();

                    Log::info('Filter Routing Profile List procedure' . $CustomerProfileAccountID);
                    $query = "CALL prc_getRotingRecords(" . $CustomerProfileAccountID . "," . "'" . $routingData['OriginationNo'] . "'" . "," . "'" . $routingData['DestinationNo'] . "'" .
                        "," . "'" . $queryTimeZone . "'" . "," . "'" . $RoutingProfileIds. "'" . ",'" . $Prefix. "'" .")";
                    Log::info('Filter Routing Profile List procedure' . $query);
                    $lcrDetails = DB::connection('speakIntelligentRoutingEngine')->select($query);
                    Log::info('Filter Routing Profile List procedure' . count($lcrDetails));






        $routingDetails = array();
        $positionDetails = 0;
        $locationDetails = 0;
        $routingInfo = array();
        $lastVendorID = '';
        $locationDetail = '';

        /*foreach($lcrDetails as $lcrDetail) {

                $routingDetails['AccountCurrency'] =$lcrDetail->accountCurrency;
                $routingInfo['vendor ID'] = $lcrDetail->VendorID;
                $routingInfo['vendor Name'] = $lcrDetail->VendorName;
                $vendorID = $lcrDetail->VendorID;
                Log::info('ConnectTime TimeZone $lcrDetail' . $vendorID);
                if ($lastVendorID != $vendorID) {
                    $lastVendorID = $lcrDetail->VendorID;
                    $positionDetails++;
                    $locationDetails = 1;
                    $routingDetails['Position' . $positionDetails] = $routingInfo;
                    $routingInfo['Locations'] = array();
                    $locationDetail['SipHeader'] = $lcrDetail->SipHeader;
                    $locationDetail['IP'] = $lcrDetail->IP;
                    $locationDetail['Port'] = $lcrDetail->Port;
                    $locationDetail['Username'] = $lcrDetail->Username;
                    $locationDetail['Password'] = $lcrDetail->Password;
                    $locationDetail['AuthenticationMode'] = $lcrDetail->AuthenticationMode;
                    $locationDetail['Rate'] = $lcrDetail->Rate;
                    $routingInfo['Locations']['Location' . $locationDetails] = $locationDetail;
                    $routingDetails['Position' . $positionDetails] = $routingInfo;
                } else {
                    $locationDetails++;
                    $locationDetail['IP'] = $lcrDetail->IP;
                    $locationDetail['Port'] = $lcrDetail->Port;
                    $locationDetail['Username'] = $lcrDetail->Username;
                    $locationDetail['Password'] = $lcrDetail->Password;
                    $locationDetail['AuthenticationMode'] = $lcrDetail->AuthenticationMode;
                    $locationDetail['Rate'] = $lcrDetail->Rate;
                    $routingInfo['Locations']['Location' . $locationDetails] = $locationDetail;
                    $routingDetails['Position' . $positionDetails] = $routingInfo;
                }




            //return Response::json(["status" => "failed", "Positions" => $routingDetails]);
        }*/
       // return Response::json(["status" => "Success", "Positions" => $routingDetails]);


        $lcrDetails = json_decode(json_encode($lcrDetails),true);
        return Response::json(["status" => "Success", "Positions" => $lcrDetails]);
    }

    public function checkTimeZone($lcrDetail,$TimeZones,$connectTime) {


        $firstOfMonth = strtotime(date("Y-m-01", $connectTime));
        //Apply above formula.
        $weekNum =  intval(date("W", $connectTime)) - intval(date("W", $firstOfMonth)) + 1;
        $Month = date("n",$connectTime) . ",";
        $DayOfMonth = date("d",$connectTime) . ",";
        $Hour = date("H",$connectTime);
        $Minute = date("i",$connectTime);
        $Seconds = date("s",$connectTime);
        $timeZoneMatch = false;
        Log::info('ConnectTime TimeZone' . $connectTime .
            ' '.$Month.
            ' '.$DayOfMonth.
            ' '.$weekNum.
            ' '.date("H",$connectTime).
            ' '.date("i",$connectTime).
            ' '.date("s",$connectTime)
        );

        /*
        isset($TimeZone->DaysOfMonth) &&
        isset($TimeZone->DaysOfWeek) &&
        isset($TimeZone->ToTime) &&
        isset($TimeZone->FromTime */
        $monthZoneMatch = '';
        $monthZoneSet = '';
        $DaysOfMonthZoneMatch = '';
        $DaysOfMonthZoneSet = '';
        $TimeZoneMatch = '';
        $DaysOfWeekSet  = '';
        $ToTimeSet  = '';
        $FromTimeSet  = '';

        foreach($TimeZones as $TimeZone) {
            Log::info('$lcrDetail->TimezoneId ' . $lcrDetail->TimezoneId);
            Log::info('$TimeZone->TimezoneId ' . $TimeZone->TimezonesID);
            if ($lcrDetail->TimezoneId == $TimeZone->TimezonesID) {
                if (!empty($TimeZone->Months)) {
                    Log::info('$TimeZone->TimezoneId months');
                    $monthZoneSet = 0;
                    if (strpos($TimeZone->Months,$Month) > 0 || strpos($TimeZone->Months,',' . date("n",$connectTime)) > 0){
                        $TimeZoneMatch = 1;
                    }
                }

                if (!empty($TimeZone->DaysOfMonth)) {
                    $DaysOfMonthZoneSet = 0;
                    Log::info('$TimeZone->TimezoneId DaysOfMonth');
                    if ($monthZoneSet == 0 && $TimeZoneMatch == 0) {
                        $TimeZoneMatch = 0;
                    } else if (($monthZoneSet == 0 && $TimeZoneMatch == 1) && strpos($TimeZone->DaysOfMonth,$DayOfMonth) > 0 || strpos($TimeZone->DaysOfMonth,',' . date("d",$connectTime)) > 0){
                        $TimeZoneMatch = 1;
                    }else if (strpos($TimeZone->DaysOfMonth,$DayOfMonth) > 0 || strpos($TimeZone->DaysOfMonth,',' . date("d",$connectTime)) > 0){
                        $TimeZoneMatch = 1;
                    }else {
                        $TimeZoneMatch = 0;
                    }
                }

                if (!empty($TimeZone->DaysOfWeek)) {
                    $DaysOfWeekSet  = 0;
                    Log::info('$TimeZone->TimezoneId DaysOfWeek');
                    if (($monthZoneSet == 0 || $DaysOfMonthZoneSet == 0)
                        && $TimeZoneMatch = 0){
                        $TimeZoneMatch = 0;
                    }else if (strpos($TimeZone->DaysOfWeek,$weekNum . ',') > 0 || strpos($TimeZone->DaysOfWeek,',' . $weekNum) > 0){
                        $TimeZoneMatch = 1;
                    }else {
                        $TimeZoneMatch = 0;
                    }
                }

                if (!empty($TimeZone->ToTime) && !empty($TimeZone->FromTime)) {
                    Log::info('$TimeZone->TimezoneId from and to');
                    $ToTimeSet = 0;
                    $FromTimeSet = 0;
                    $setTime = intval(str_replace(":","",$TimeZone->ToTime));
                    $setFromTime = intval(str_replace(":","",$TimeZone->FromTime));
                    $callTime = intval(str_replace(":","",$Hour . ':' . $Minute));

                    if (($monthZoneSet == 0 || $DaysOfMonthZoneSet == 0 || $DaysOfWeekSet = 0)
                        && $TimeZoneMatch = 0){
                        $TimeZoneMatch = 0;
                    }else if ($setTime >= $callTime && $setFromTime <= $callTime){
                        $TimeZoneMatch = 1;
                    }else {
                        $TimeZoneMatch = 0;
                    }
                }
                if ($ToTimeSet != 0 && !empty($TimeZone->ToTime)) {
                    Log::info('$TimeZone->TimezoneId to');
                    $ToTimeSet = 0;
                    $setTime = intval(str_replace(":","",$TimeZone->ToTime));
                    $callTime = intval(str_replace(":","",$Hour . ':' . $Minute));
                    if (($monthZoneSet == 0 || $DaysOfMonthZoneSet == 0 || $DaysOfWeekSet = 0)
                        && $TimeZoneMatch = 0){
                        $TimeZoneMatch = 0;
                    }else if ($setTime >= $callTime){
                        $TimeZoneMatch = 1;
                    }else {
                        $TimeZoneMatch = 0;
                    }
                }

                if ($FromTimeSet != 0 && !empty($TimeZone->FromTime)) {
                    $FromTimeSet = 0;
                    $setTime = intval(str_replace(":","",$TimeZone->FromTime));
                    $callTime = intval(str_replace(":","",$Hour . ':' . $Minute));
                    if (($monthZoneSet == 0 || $DaysOfMonthZoneSet == 0 || $DaysOfWeekSet == 0 || $ToTimeSet == 0)
                        && $TimeZoneMatch = 0){
                        $TimeZoneMatch = 0;
                    }else if ($setTime <= $callTime){
                        $TimeZoneMatch = 1;
                    }else {
                        $TimeZoneMatch = 0;
                    }
                }

                if ($TimeZoneMatch == 1) {
                    return $lcrDetail;
                }else {
                    $lcrDetail = '';
                    return $lcrDetail;
                }
            }
        }

        if ($lcrDetail->TimezoneId == 1) {
            return $lcrDetail;
        }else {
            $lcrDetail = '';
            return $lcrDetail;
        }
    }
}