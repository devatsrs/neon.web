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
        $rules = array(
            'OriginationCode' => 'required',
            'DestinationCode' => 'required',
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
        if (isset($routingData["Number"]) && $routingData["Number"] != '') {
            $AccountProfiles = AccountRoutingProfile::select(['AccountID'])->where(["AccountNumber" => $routingData["Number"]])->get();
        }else {
            $AccountProfiles = AccountRoutingProfile::select(['AccountID'])->where(["AccountID" => $routingData["CustomerID"]])->get();
        }
        Log::info('routingList:Get the routing list count.' . count($AccountProfiles));

        $profiles = '';


        $RoutingProfileId = '';
        $RoutingProfileIds = '';
        $CustomerProfileAccountID = '';
        if (!isset($AccountProfiles) ) {
            return Response::json(["status" => "failed", "message" => "No Profile found against the Number/CustomerID"]);
        }
        else {
            $CustomerProfile = $AccountProfiles[0];
            //foreach ($AccountProfiles as $CustomerProfile) {

            //}
            $CustomerProfileAccountID = $CustomerProfile->AccountID;
            Log::info('routingList:Get the routing list count.' . $CustomerProfileAccountID);
        }



        $removePlusSign = '';

        //trim and replace CodeComment#1
        $routingData["OriginationCode"] = str_replace("+","",trim($routingData["OriginationCode"]));


        //trim and replace CodeComment#2
        $routingData["DestinationCode"] = str_replace("+","",trim($routingData["DestinationCode"]));

        Log::info('routingList:Get the routing list.$RoutingProfileId');//->whereIn('RoutingProfileId', $RoutingProfileId)
        print_r($RoutingProfileId);
            if (isset($AccountProfiles) ) {
                $lcrDetails = RoutingProfileRate::select(['RoutingProfileId']);
                $lcrDetails = $lcrDetails->whereRaw($routingData["DestinationCode"] . ' like  CONCAT(tblRoutingProfileRate.selectionCode,"%")');
                $lcrDetails = $lcrDetails->orderByRaw('CONCAT(tblRoutingProfileRate.selectionCode,"%") desc');
                $lcrDetails = $lcrDetails->take(1);

                Log::info('routingList profiles case 1 query with RoutingProfileRate ' . $lcrDetails->toSql());
                $lcrDetails = $lcrDetails->get();
                Log::info('routingList profiles query with RoutingProfileRate count($lcrDetails) ' . count($lcrDetails));
                if (count($lcrDetails) > 0) {
                    foreach ($lcrDetails as $lcrDetail) {
                        $RoutingProfileIds = $RoutingProfileIds . $lcrDetail->RoutingProfileId .  ',';
                    }
                }else {
                    $CustomerTrunks = CustomerTrunk::select(['AccountID']);
                    $CustomerTrunks = $CustomerTrunks->whereRaw('\'' . $routingData["DestinationCode"] . '\'' . ' like  CONCAT(Prefix,"%")');
                    $CustomerTrunks = $CustomerTrunks->orderByRaw('CONCAT(Prefix,"%") desc');
                    $CustomerTrunks = $CustomerTrunks->take(1);
                    Log::info('routingList profiles case 2 query with RoutingProfileRate ' . $CustomerTrunks->toSql());
                    $CustomerTrunks = $CustomerTrunks->get();
                  //  $CustomerTrunk = $CustomerTrunks[0];
                    //array_shift to get the first records CodeComment#3
                    if (count($CustomerTrunks) > 0) {
                        $CustomerTrunk = array_shift($CustomerTrunks);
                        //foreach ($CustomerTrunks as $CustomerTrunk) {

                       // }
                        $TrunkAccountProfiles = AccountRoutingProfile::select(['AccountID','RoutingProfileID'])->where(["AccountID" => $routingData["CustomerID"]])->get();
                        if (count($TrunkAccountProfiles) > 0) {
                            foreach ($TrunkAccountProfiles as $TrunkAccountProfile) {
                                $RoutingProfileIds = $RoutingProfileIds . $TrunkAccountProfile->RoutingProfileId .  ',';
                            }
                        }
                    }else {
                        $CLIRateTables = CLIRateTable::select(['AccountID', 'ServiceID']);
                        $CLIRateTables = $CLIRateTables->where('CLI', '=', $routingData["OriginationCode"]);
                        Log::info('routingList profiles case 3 query with RoutingProfileRate ' . $CLIRateTables->toSql());
                        $CLIRateTables = $CLIRateTables->get();


                        if (count($CLIRateTables) > 0) {
                           // $CLIRateTable = array_shift($CLIRateTables);
                            foreach ($CLIRateTables as $CLIRateTable) {

                            }
                            $VendorDetails = '';
                            Log::info('routingList profiles case 3 query with RoutingProfileRate ' . $CLIRateTable->AccountID . ' ' . $CLIRateTable->ServiceID);
                            $AccountProfiles = AccountRoutingProfile::select(['RoutingProfileID'])->where(["AccountID" => $CLIRateTable->AccountID]);
                            $AccountProfiles = $AccountProfiles->where('ServiceID', '=', $CLIRateTable->ServiceID);
                            Log::info('routingList profiles case 3 query with RoutingProfileRate ' . $AccountProfiles->toSql());
                            $AccountProfiles = $AccountProfiles->get();
                            if (count($AccountProfiles) == 0) {
                                $AccountProfiles = AccountRoutingProfile::select(['RoutingProfileID'])->where(["AccountID" => $CLIRateTable->AccountID]);
                                Log::info('routingList profiles case 3 query with RoutingProfileRate ' . $AccountProfiles->toSql());
                                $AccountProfiles = $AccountProfiles->get();
                            }
                            Log::info('routingList profiles case 3 query with RoutingProfileRate ' . count($AccountProfiles));
                            if (count($AccountProfiles) != 0) {
                                $AccountProfilesIn = '';
                                foreach ($AccountProfiles as $AccountProfile) {
                                    $RoutingProfileIds = $RoutingProfileIds . $AccountProfile->RoutingProfileID .  ',';
                                }

                            }
                        }
                    }
                }

                Log::info('Filter Routing Profile List procedure $RoutingProfileIds' . $RoutingProfileIds);
                if (!empty($RoutingProfileIds)) {
                    $selectDataFromTable = 'tblRoutingProfileRate';
                    Log::info('Filter Routing Profile List procedure' . $CustomerProfileAccountID);
                    $query = "CALL speakIntelligentRoutingEngine.prc_getRotingRecords(" . $CustomerProfileAccountID . "," . "'" . $routingData['OriginationCode'] . "'" . "," . "'" . $routingData['DestinationCode'] . "'" .
                        "," . "'" . $selectDataFromTable . "'" . "," . "'" . $RoutingProfileIds. "'" . ")";
                    Log::info('Filter Routing Profile List procedure' . $query);
                    $lcrDetails = DB::select($query);
                    Log::info('Filter Routing Profile List procedure' . count($lcrDetails));
//
//                foreach($routing_data as $routingdata) {
//                    Log::info('Filter Routing Profile List procedure' . $routingdata->VendorPosition);
//                }
                } else {
                    $selectDataFromTable = 'tblVendorRate';
                    $query = "CALL speakIntelligentRoutingEngine.prc_getRotingRecords(" . $CustomerProfileAccountID ."," . "'" .$routingData['OriginationCode']."'". "," ."'".$routingData['DestinationCode']."'".
                        "," . "'" . $selectDataFromTable."'". ")";
                    Log::info('Filter Routing Profile List procedure' . $query);
                    $lcrDetails  = DB::select($query);
                    Log::info('Filter Routing Profile List procedure' . count($lcrDetails));
                    if (count($lcrDetails) == 0) {
                        return Response::json(["status" => "failed", "message" => "No Rate list found against the criteria"]);
                    }
                }

                /*Log::info('Filter Routing Profile List');
                $lcrDetails = RoutingProfileRate::select(['VendorPosition',
                    'SipHeader', 'IP', 'Port', 'Username', 'Password', 'AuthenticationMode',
                    'VendorID', 'VendorName','TimezoneId']);
                $lcrDetails = $lcrDetails->where('OriginatioCode','=',$routingData["OriginationCode"]);
                $lcrDetails = $lcrDetails->where('DestinationCode', '=', $routingData["DestinationCode"]);
                Log::info('Filter Routing Profile List 1' . $lcrDetails->toSql());
                $lcrDetails = $lcrDetails->get();
                if (count($lcrDetails) == 0) {
                    $lcrDetails = RoutingProfileRate::select(['VendorPosition',
                        'SipHeader', 'IP', 'Port', 'Username', 'Password', 'AuthenticationMode',
                        'VendorID', 'VendorName','TimezoneId']);
                    $lcrDetails = $lcrDetails->where('DestinationCode', 'like', '%'.$routingData["DestinationCode"].'%');
                    Log::info('Filter Routing Profile List 2' . $lcrDetails->toSql());
                    $lcrDetails = $lcrDetails->get();
                    if (count($lcrDetails) == 0) {
                        return Response::json(["status" => "failed", "message" => "No Rate list found against the criteria"]);
                    }
                }*/

            }else {
                /*$lcrDetails = LCRDetail::Join('tblLCRHeader','tblLCRHeader.LCRHeaderID','=','tblLCRDetail.LCRHeaderID')->
                select(['tblLCRDetail',
                    'tblLCRDetail.SipHeader', 'tblLCRDetail.IP', 'Port', 'Username', 'Password', 'AuthenticationMode',
                    'VendorID', 'VendorName']);
               // $lcrDetails = $lcrDetails->where('tblLCRHeader.TrunkName','=',$routingData["DestinationType"]);
                if (isset($routingData["OriginationCode"]) && $routingData["OriginationCode"] != '') {
                    $lcrDetails = $lcrDetails->where('OriginatioCode','like','%'.$routingData["OriginationCode"].'%');
                }
                if (isset($routingData["DestinationCode"]) && $routingData["DestinationCode"] != '') {
                    $lcrDetails = $lcrDetails->where('DestinationCode', 'like', '%'.$routingData["DestinationCode"].'%');
                }
                Log::info('routingList profiles query with LCRDetail' . $lcrDetails->toSql());
                $lcrDetails = $lcrDetails->get();*/
            }

       // $lcrDetails = json_encode($lcrDetails,true);

        $connectTime = strtotime($routingData["ConnectTime"]);
        $firstOfMonth = strtotime(date("Y-m-01", $connectTime));
        //Apply above formula.
        $weekNum =  intval(date("W", $connectTime)) - intval(date("W", $firstOfMonth)) + 1;
        Log::info('ConnectTime TimeZone' . $connectTime .
            ' '.date("n",$connectTime).
            ' '.date("d",$connectTime).
            ' '.$weekNum.
            ' '.date("H",$connectTime).
            ' '.date("i",$connectTime).
            ' '.date("s",$connectTime)
        );

        $TimeZones = Timezones::select(['TimezonesID',
            'Title', 'FromTime', 'ToTime', 'DaysOfWeek', 'DaysOfMonth', 'Months',
            'ApplyIF']);
        $TimeZonesIn = '';
        foreach($lcrDetails as $lcrDetail) {
            $TimeZonesIn = $TimeZonesIn . $lcrDetail->TimezoneId . ",";
        }

        $TimeZonesIn = explode(",", $TimeZonesIn);
        $TimeZones = $TimeZones->where('Status','=', 1);
        $TimeZones = $TimeZones->whereIn('TimezonesID', $TimeZonesIn);
        Log::info('Get the available time zones for the profiles ' . $TimeZones->toSql());
        $TimeZones = $TimeZones->get();

       // Log::info('Get the available time zones for the profiles ' . count($TimeZones));
//        foreach($TimeZones as $TimeZone) {
//            Log::info('Get the available time zones for the profiles ' . $TimeZone->Title);
//        }

        $routingDetails = array();
        $positionDetails = 0;
        $locationDetails = 0;
        $routingInfo = array();
        $lastVendorID = '';
        $locationDetail = '';
        foreach($lcrDetails as $lcrDetail) {
            $lcrDetail = $this->checkTimeZone($lcrDetail,$TimeZones,$connectTime);
            if ($lcrDetail != '') {

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
                    $routingInfo['Locations']['Location' . $locationDetails] = $locationDetail;
                    $routingDetails['Position' . $positionDetails] = $routingInfo;
                } else {
                    $locationDetails++;
                    $locationDetail['IP'] = $lcrDetail->IP;
                    $locationDetail['Port'] = $lcrDetail->Port;
                    $locationDetail['Username'] = $lcrDetail->Username;
                    $locationDetail['Password'] = $lcrDetail->Password;
                    $locationDetail['AuthenticationMode'] = $lcrDetail->AuthenticationMode;
                    $routingInfo['Locations']['Location' . $locationDetails] = $locationDetail;
                    $routingDetails['Position' . $positionDetails] = $routingInfo;
                }
            }



            //return Response::json(["status" => "failed", "Positions" => $routingDetails]);
        }
        return Response::json(["status" => "Success", "Positions" => $routingDetails]);
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