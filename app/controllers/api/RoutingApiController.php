<?php

/**
 * Created by PhpStorm.
 * User: Aamar Nazir
 * Date: 28/12/2018
 * Time: 12:47 AM
 */
use app\controllers\api\Codes;

class RoutingApiController extends ApiController {
    public function routingListOld()
    {
        try {
            Log::info('routingList:Get the routing list.');
            //$routingData = Input::all();
            $post_vars = json_decode(file_get_contents("php://input"));
            $lcrDetails = '';
            $CompanyID = User::get_companyID();
            $rules = array(
                'OriginationNo' => 'required',
                'DestinationNo' => 'required',
                'DataAndTime' => 'required',
                'AccountNo' => 'required_without_all:AccountID,AccountDynamicField',
                'AccountID' => 'required_without_all:AccountNo,AccountDynamicField',
                'AccountDynamicField' => 'required_without_all:AccountNo,AccountID',

            );
            $routingData["OriginationNo"] = $post_vars->OriginationNo;
            $routingData["DestinationNo"] = $post_vars->DestinationNo;
            $routingData["DataAndTime"] = $post_vars->DataAndTime;
            $routingData["AccountNo"] = isset($post_vars->AccountNo) ? $post_vars->AccountNo : '';
            $routingData["AccountID"] = isset($post_vars->AccountID) ? $post_vars->AccountID : '';
            $AccountDynamicField = isset($post_vars->AccountDynamicField) ? $post_vars->AccountDynamicField : '';
            if (count($AccountDynamicField) > 0 && $AccountDynamicField != '') {
                $routingData["AccountDynamicField"] = "[]";
            } else {
                $routingData["AccountDynamicField"] = '';
            }
            //foreach($AccountDynamicField as $key => $value) {
            //  Log::info('routingList:Get the routing list.' . $value->Name . ' ' . $value->Value);
            //}

            $validator = Validator::make($routingData, $rules);


            if ($validator->fails()) {
                $errors = "";
                foreach ($validator->messages()->all() as $error) {
                    $errors .= $error . "<br>";
                }
                return Response::json(["status" => "401", "message" => $errors]);
            }

            if (count($AccountDynamicField) > 0 && $AccountDynamicField != '') {
                $AccountIDRef = '';
                $AccountIDRef = Account::findAccountBySIAccountRefWithJSON($AccountDynamicField);

                if (empty($AccountIDRef)) {
                    return Response::json(["status" => "401", "message" => "Please provide the correct Account ID"]);
                }
                $routingData["AccountID"] = $AccountIDRef;
            }


            Log::info('routingList:Get the routing list user company.' . $CompanyID);
            $profiles = '';
            $RoutingProfileId = array();
            $CustomerProfileAccountID = '';
            if (isset($routingData["AccountNo"]) && $routingData["AccountNo"] != '') {
                $CustomerProfileAccountID = Account::where(["Number" => $routingData["AccountNo"]])->pluck("AccountID");
            } else {
                $CustomerProfileAccountID = Account::where(["AccountID" => $routingData["AccountID"]])->pluck("AccountID");
            }
            Log::info('routingList:Get the routing list count.' . $CustomerProfileAccountID);

            $profiles = '';


            $RoutingProfileId = '';
            $RoutingProfileID = '';

            if (empty($CustomerProfileAccountID)) {
                return Response::json(["status" => "401", "message" => "No Profile found against the Number/CustomerID"]);
            }
            Log::info('routingList:Get the routing list count.' . $CustomerProfileAccountID);


            $removePlusSign = '';
            $Prefix = '';
            //trim and replace CodeComment#1
            $routingData["OriginationNo"] = trim(str_replace("+", "", $routingData["OriginationNo"]));


            //trim and replace CodeComment#2
            $routingData["DestinationNo"] = trim(str_replace("+", "", $routingData["DestinationNo"]));


            $lcrDetails = RoutingProfileRate::select(['RoutingProfileId', 'selectionCode'])->
            whereRaw('\'' . $routingData["DestinationNo"] . '\'' . ' like  CONCAT(tblRoutingProfileRate.selectionCode,"%")')
                ->orderByRaw('CONCAT(tblRoutingProfileRate.selectionCode,"%") desc')
                ->take(1);
            Log::info('routingList profiles case 1 query with RoutingProfileRate Query' . $lcrDetails->toSql());
            $lcrDetails = $lcrDetails->get();

            Log::info('routingList profiles case 1 query with RoutingProfileRate ' . count($lcrDetails));
            if (count($lcrDetails) > 0) {
                foreach ($lcrDetails as $lcrDetail) {

                }
                $RoutingProfileID = $lcrDetail->RoutingProfileId;
                // $Prefix = $lcrDetail->selectionCode;
                Log::info('routingList profiles case 1 query with RoutingProfileRate ' . $RoutingProfileID);
            } else {
                $CustomerTrunks = CustomerTrunk::select(['AccountID', 'TrunkID', 'Prefix'])
                    ->where('UseInBilling', '=', 1)
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
                        $RoutingProfileID = $TrunkAccountProfiles;
                        // $Prefix = $CustomerTrunk->Prefix;
                    }
                } else {
                    $CLIRateTables = CLIRateTable::select(['AccountID', 'ServiceID', 'CLI'])
                        ->where('CLI', '=', $routingData["OriginationNo"])
                        ->where(["CompanyID" => $CompanyID]);;
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

                            $RoutingProfileID = $AccountProfiles;
                            //$Prefix = $CLIRateTable->CLI;
                        }
                    }
                }
            }

            Log::info('Filter Routing Profile List procedure $RoutingProfileIds' . $RoutingProfileID);
            $DataAndTime = strtotime($routingData["DataAndTime"]);
            $dataTimeZone['CompanyID'] = $CompanyID;
            $dataTimeZone['connect_time'] = $routingData["DataAndTime"];
            $dataTimeZone['disconnect_time'] = $routingData["DataAndTime"];
            // $dataTimeZone['TimezonesID'] = '';
            Log::info('Filter Routing Profile List procedure $queryTimeZone' .
                print_r($dataTimeZone, true));
            $GetTimeZone = GetTimeZone::create($dataTimeZone);
            $query = "CALL `prc_updateTempCDRTimeZones`('tblgetTimezone')";
            $queryResults = DB::connection('sqlsrv2')->select($query);
            $queryTimeZone = GetTimeZone::
            where(["connect_time" => $routingData["DataAndTime"]])
                ->where('disconnect_time', '=', $routingData["DataAndTime"])->pluck("TimezonesID");

            if (empty($queryTimeZone)) {
                $queryTimeZone = 1;
            }
            Log::info('Filter Routing Profile List procedure $queryTimeZone' . $queryTimeZone);
            Log::info('Filter Routing Profile List procedure $GetTimeZone' .
                print_r($GetTimeZone, true));
            GetTimeZone::where(array('getTimezoneID' => $GetTimeZone->getTimezoneID))->delete();

            /*
                        Log::info('Filter Routing Profile List procedure' . $CustomerProfileAccountID);
                        $query = "CALL prc_getRoutingRecords(" . $CustomerProfileAccountID . "," . "'" . $routingData['OriginationNo'] . "'" . "," . "'" . $routingData['DestinationNo'] . "'" .
                            "," . "'" . $queryTimeZone . "'" . "," . "'" . $RoutingProfileID. "'"  .")";
                        Log::info('Filter Routing Profile List procedure' . $query);
                        $lcrDetailsProc = DB::connection('speakIntelligentRoutingEngine')->select($query)->fetchAll();;
                    //$lcrDetailsProc = $lcrDetailsProc->cursor();
                        Log::info('Filter Routing Profile List procedure' . count($lcrDetailsProc));
                 */
            $procName = "prc_getRoutingRecords";
            $syntax = '';
            // $routingData['Location'] = $post_vars->Location;
            $Location = isset($post_vars->Location) ? $post_vars->Location : '';
            $routingData['Location'] = $Location;
            $parameters = [$CustomerProfileAccountID, $routingData['OriginationNo'], $routingData['DestinationNo'],
                $queryTimeZone, $RoutingProfileID, $Location];
            for ($i = 0; $i < count($parameters); $i++) {
                $syntax .= (!empty($syntax) ? ',' : '') . '?';
            }
            $syntax = 'CALL ' . $procName . '(' . $syntax . ');';
            Log::info('Filter Routing Profile List procedure $syntax123' . $syntax);

            $pdo = DB::connection('speakIntelligentRoutingEngine')->getPdo();
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            $stmt = $pdo->prepare($syntax, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
            for ($i = 0; $i < count($parameters); $i++) {
                $stmt->bindValue((1 + $i), $parameters[$i]);
            }
            $exec = $stmt->execute();
            if (!$exec) return $pdo->errorInfo();
            $results[] = $stmt->fetchAll(\PDO::FETCH_OBJ);
            do {
                try {
                    $results[] = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    Log::info('Filter Routing Profile List procedure $syntax1232' . count($results));
                    // foreach($results as $result) {
                    //    Log::info('Filter Routing Profile List procedure $syntax' . print_r($result,true));
                    // }
                } catch (\Exception $ex) {

                }
            } while ($stmt->nextRowset());

            //Log::info('Filter Routing Profile List procedure password' . Crypt::decrypt('eyJpdiI6IkRrbGRQTjh5V1JQeVJvTDZCNnh2Snc9PSIsInZhbHVlIjoiTGpVcWVYS1lcL2J1SXNXSFwvbXgwSzBBPT0iLCJtYWMiOiI2Mzc3MzUxNjhjM2MxOTljZDAyMTkyYTY5NWY3NTM2NTNkOWY5NjZiMjlhNWMxM2UyYTcxMzViZjBjMTY5MWI5In0='));
            //Crypt::decrypt('eyJpdiI6IkRrbGRQTjh5V1JQeVJvTDZCNnh2Snc9PSIsInZhbHVlIjoiTGpVcWVYS1lcL2J1SXNXSFwvbXgwSzBBPT0iLCJtYWMiOiI2Mzc3MzUxNjhjM2MxOTljZDAyMTkyYTY5NWY3NTM2NTNkOWY5NjZiMjlhNWMxM2UyYTcxMzViZjBjMTY5MWI5In0=');

            if (count($results) == 2) {
                $lcrDetails = $results[0];
                foreach ($lcrDetails as $lcrDetail) {
                    try {
                        $lcrDetail->Password = Crypt::decrypt($lcrDetail->Password);
                    } catch (Exception $e) {

                    }

                }
            } else if (count($results) == 3) {
                $lcrDetails = $results[2];
                foreach ($lcrDetails as $lcrDetail) {
                    try {
                        $lcrDetail->Password = Crypt::decrypt($lcrDetail->Password);
                    } catch (Exception $e) {

                    }

                }
            } else {
                $lcrDetails = '';
            }
            //$lcrDetails = $results;
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
                    Log::info('DataAndTime TimeZone $lcrDetail' . $vendorID);
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


            $lcrDetails = json_decode(json_encode($lcrDetails), true);
            return Response::json(["status" => "200", "data" => $lcrDetails]);
        }catch(Exception $ex) {
            return Response::json(["status" => "500", "message" => "Exception in Routing API"]);
        }
    }


    public function routingList()
    {
        try {
            // Log::info('routingList:Get the routing list.');
            $accountInfo = [];
            $accountreseller = '';
            $post_vars = '';
            $routingData = [];
            $startDate = microtime(true);//date('Y-m-d H:i:s');

            try {
                $post_vars = json_decode(file_get_contents("php://input"));
                //$post_vars = Input::all();
                $routingData = json_decode(json_encode($post_vars), true);
                $countValues = count($routingData);
                if ($countValues == 0) {
                    // Log::info('Exception in Routing API.Invalid JSON');
                    return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
                }
            }catch(Exception $ex) {
                Log::info('Exception in Routing API.Invalid JSON' . $ex->getTraceAsString());
                return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
            }



            $lcrDetails = '';
            $CompanyID = User::get_companyID();
            $rules = array(
                'OriginationNo' => 'required',
                'DestinationNo' => 'required',
                'DateAndTime' => 'required',
                'AccountNo' => 'required_without_all:AccountID,AccountDynamicField',
                'AccountID' => 'required_without_all:AccountNo,AccountDynamicField',
                'AccountDynamicField' => 'required_without_all:AccountNo,AccountID',

            );
            $validator = Validator::make($routingData, $rules);


            if ($validator->fails()) {
                $errors = "";
                foreach ($validator->messages()->all() as $error) {
                    $errors .= $error . "<br>";
                }
                return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
            }

            if (!empty($routingData['AccountDynamicField'])) {
                $AccountIDRef = '';
                $AccountIDRef = Account::findAccountBySIAccountRef($routingData['AccountDynamicField']);

                if (empty($AccountIDRef)) {
                    return Response::json(["ErrorMessage"=>Codes::$Code1000[1]],Codes::$Code1000[0]);
                }
                $routingData["AccountID"] = $AccountIDRef;
            }


            //  Log::info('routingList:Get the routing list user company.' . $CompanyID);
            $profiles = '';
            $RoutingProfileId = array();
            $CustomerProfileAccountID = '';
            if (isset($routingData["AccountNo"]) && $routingData["AccountNo"] != '') {
                $accountInfo = Account::where(["Number" => $routingData["AccountNo"]])->first();
                if (!empty($accountInfo)) {
                    $CustomerProfileAccountID = $accountInfo["AccountID"];
                }
            } else if (isset($routingData["AccountID"]) && $routingData["AccountID"] != ''){
                $accountInfo = Account::where(["AccountID" => $routingData["AccountID"]])->first();
                if (!empty($accountInfo)) {
                    $CustomerProfileAccountID = $accountInfo["AccountID"];
                }
            }

            if (empty($CustomerProfileAccountID)) {
                return Response::json(["ErrorMessage"=>Codes::$Code1000[1]],Codes::$Code1000[0]);
            }

            if (!empty($accountInfo)) {
                $companyID = $accountInfo["CompanyId"];
                $accountreseller = Reseller::where('ChildCompanyID', $companyID)->pluck('AccountID');
            }
            // Log::info('routingList:Get the routing list count.' . $CustomerProfileAccountID . ' ReSeller Account ' . $accountreseller);

            $profiles = '';


            $RoutingProfileId = '';
            $RoutingProfileID = '';



            $checkDate = strtotime($routingData['DateAndTime']);
            if (empty($checkDate)) {
                return Response::json(["ErrorMessage"=>Codes::$Code1022[1]],Codes::$Code1022[0]);
            }
            // Log::info('routingList:Get the routing list count.' . $CustomerProfileAccountID);


            $removePlusSign = '';
            $Prefix = '';
            //trim and replace CodeComment#1
            $routingData["OriginationNo"] = trim(str_replace("+", "", $routingData["OriginationNo"]));


            //trim and replace CodeComment#2
            $routingData["DestinationNo"] = trim(str_replace("+", "", $routingData["DestinationNo"]));


            $lcrDetails = RoutingProfile::select(['RoutingProfileId', 'selectionCode'])->
            whereRaw('\'' . $routingData["DestinationNo"] . '\'' . ' like  CONCAT(SelectionCode,"%")')
                ->where('SelectionCode', '<>', '')
                ->orderByRaw('CONCAT(SelectionCode,"%") desc')
                ->take(1);
            // Log::info('routingList profiles case 1 query with RoutingProfileRate Query');
            $lcrDetails = $lcrDetails->get();

            // Log::info('routingList profiles case 1 query with RoutingProfileRate ' . count($lcrDetails));
            if (count($lcrDetails) > 0) {
                foreach ($lcrDetails as $lcrDetail) {

                }
                $RoutingProfileID = $lcrDetail->RoutingProfileId;
                // $Prefix = $lcrDetail->selectionCode;
                //   Log::info('routingList profiles case 1 query with RoutingProfileRate ' . $RoutingProfileID);
            } else {
                $CustomerTrunks = CustomerTrunk::select(['AccountID', 'TrunkID', 'Prefix'])
                    ->where('UseInBilling', '=', 1)
                    ->where('Prefix', '<>', '')
                    ->whereRaw('\'' . $routingData["DestinationNo"] . '\'' . ' like  CONCAT(Prefix,"%")')
                    ->orderByRaw('CONCAT(Prefix,"%") desc')
                    ->take(1);
                // Log::info('routingList profiles case 2 query with RoutingProfileRate ' );
                $CustomerTrunks = $CustomerTrunks->get();
                if (count($CustomerTrunks) > 0) {
                    foreach ($CustomerTrunks as $CustomerTrunk) {

                    }
                    $TrunkAccountProfiles = EngineRoutingProfileToCustomer::
                    where(["AccountID" => $CustomerTrunk->AccountID])
                        ->where(["TrunkID" => $CustomerTrunk->TrunkID])
                        ->pluck("RoutingProfileID");
                    //  Log::info('routingList profiles case 2 query with RoutingProfileRate ' . $TrunkAccountProfiles);
                    if (!empty($TrunkAccountProfiles)) {
                        $RoutingProfileID = $TrunkAccountProfiles;
                        // $Prefix = $CustomerTrunk->Prefix;
                    }
                } else {
                    $CLIRateTables = CLIRateTable::select(['AccountID', 'ServiceID', 'CLI'])
                        ->where('CLI', '=', $routingData["OriginationNo"])
                        ->where(["CompanyID" => $CompanyID]);;
                    // Log::info('routingList profiles case 3 query with RoutingProfileRate ' );
                    $CLIRateTables = $CLIRateTables->get();


                    if (count($CLIRateTables) > 0) {
                        // $CLIRateTable = array_shift($CLIRateTables);
                        foreach ($CLIRateTables as $CLIRateTable) {

                        }
                        $VendorDetails = '';
                        //  Log::info('routingList profiles case 3 query with RoutingProfileRate ' . $CLIRateTable->AccountID . ' ' . $CLIRateTable->ServiceID);
                        $AccountProfiles = EngineRoutingProfileToCustomer::
                        where(["AccountID" => $CLIRateTable->AccountID])
                            ->where('ServiceID', '=', $CLIRateTable->ServiceID)->pluck("RoutingProfileID");
                        //  Log::info('routingList profiles case 31 query with RoutingProfileRate ' . $AccountProfiles);

                        if (empty($AccountProfiles)) {
                            $AccountProfiles = EngineRoutingProfileToCustomer::
                            where(["AccountID" => $CLIRateTable->AccountID])
                                ->pluck("RoutingProfileID");
                            //Log::info('routingList profiles case 32 query with RoutingProfileRate ' . $AccountProfiles);

                        }


                        if (!empty($AccountProfiles)) {
                            $RoutingProfileID = $AccountProfiles;
                            //$Prefix = $CLIRateTable->CLI;
                        }
                    } else {
                        // Log::info('routingList profiles case 4 query with RoutingProfileRate ' . $CustomerProfileAccountID);
                        $AccountProfiles = EngineRoutingProfileToCustomer::
                        where(["AccountID" => $CustomerProfileAccountID])
                            ->pluck("RoutingProfileID");
                        //  Log::info('routingList profiles case 41 query with RoutingProfileRate ' . $AccountProfiles);
                        if (!empty($AccountProfiles)) {

                            $RoutingProfileID = $AccountProfiles;
                            //$Prefix = $CLIRateTable->CLI;
                        }else {
                            //  Log::info('routingList profiles case 5 query with RoutingProfileRate ' . $CustomerProfileAccountID);
                            $AccountProfiles = EngineRoutingProfileToCustomer::
                            where(["AccountID" => $accountreseller])
                                ->pluck("RoutingProfileID");
                            //  Log::info('routingList profiles case 51 query with RoutingProfileRate ' . $AccountProfiles);
                            if (!empty($AccountProfiles)) {

                                $RoutingProfileID = $AccountProfiles;
                                //$Prefix = $CLIRateTable->CLI;
                            }
                        }
                    }
                }
            }

            //   $endDate = date('Y-m-d H:i:s');
            //  Log::info('Total Time in Seconds .' . strtotime($endDate) . ' ' . strtotime($startDate));
            //   $diff = abs(strtotime($endDate) - strtotime($startDate));
            //   Log::info('Total Time in Seconds .1' . $diff);

            // Log::info('Filter Routing Profile List procedure $RoutingProfileIds' . $RoutingProfileID);
            $DataAndTime = strtotime($routingData["DateAndTime"]);
            $dataTimeZone['CompanyID'] = $CompanyID;
            $dataTimeZone['connect_time'] = $routingData["DateAndTime"];
            $dataTimeZone['disconnect_time'] = $routingData["DateAndTime"];
            // $dataTimeZone['TimezonesID'] = '';
            //Log::info('Filter Routing Profile List procedure $queryTimeZone' .  print_r($dataTimeZone, true));
            $GetTimeZone = GetTimeZone::create($dataTimeZone);
            $query = "CALL `prc_updateTempCDRTimeZones`('tblgetTimezone')";
            $queryResults = DB::connection('sqlsrv2')->select($query);
            $queryTimeZone = GetTimeZone::
            where(["connect_time" => $routingData["DateAndTime"]])
                ->where('disconnect_time', '=', $routingData["DateAndTime"])->pluck("TimezonesID");

            if (empty($queryTimeZone)) {
                $queryTimeZone = 1;
            }
            //   Log::info('Filter Routing Profile List procedure $queryTimeZone' . $queryTimeZone);
            //  Log::info('Filter Routing Profile List procedure $GetTimeZone' . print_r($GetTimeZone, true));
            GetTimeZone::where(array('getTimezoneID' => $GetTimeZone->getTimezoneID))->delete();

            /*
                        Log::info('Filter Routing Profile List procedure' . $CustomerProfileAccountID);
                        $query = "CALL prc_getRoutingRecords(" . $CustomerProfileAccountID . "," . "'" . $routingData['OriginationNo'] . "'" . "," . "'" . $routingData['DestinationNo'] . "'" .
                            "," . "'" . $queryTimeZone . "'" . "," . "'" . $RoutingProfileID. "'"  .")";
                        Log::info('Filter Routing Profile List procedure' . $query);
                        $lcrDetailsProc = DB::connection('speakIntelligentRoutingEngine')->select($query)->fetchAll();;
                    //$lcrDetailsProc = $lcrDetailsProc->cursor();
                        Log::info('Filter Routing Profile List procedure' . count($lcrDetailsProc));
                 */
            $procName = "prc_getRoutingRecords";
            $syntax = '';
            $Location = isset($routingData['Location']) ? $routingData['Location'] : '';
            $parameters = [$CustomerProfileAccountID, $routingData['OriginationNo'], $routingData['DestinationNo'],
                $queryTimeZone, $RoutingProfileID, $Location];
            for ($i = 0; $i < count($parameters); $i++) {
                $syntax .= (!empty($syntax) ? ',' : '') . '?';
            }
            $syntax = 'CALL ' . $procName . '(' . $syntax . ');';
             //Log::info('Filter Routing Profile List procedure $syntax123' . $syntax);

            $pdo = DB::connection('speakIntelligentRoutingEngine')->getPdo();
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            $syntaxLog = 'CALL ' . $procName . '(';
            $stmt = $pdo->prepare($syntax, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
            for ($i = 0; $i < count($parameters); $i++) {
                $syntaxLog = $syntaxLog . "'" . $parameters[$i] . "'" . ',';
                $stmt->bindValue((1 + $i), $parameters[$i]);
            }
             // Log::info('Filter Routing Profile List procedure bindvalue' . ($syntaxLog . ');'));
            //  $startDate = date('Y-m-d H:i:s');
            $exec = $stmt->execute();
            //  $endDate = date('Y-m-d H:i:s');
            //   Log::info('Total Time in Seconds .' . strtotime($endDate) . ' ' . strtotime($startDate));
            //   $diff = abs(strtotime($endDate) - strtotime($startDate));
            //    Log::info('Total Time in Seconds .1' . $diff);
            if (!$exec) return $pdo->errorInfo();
            $results[] = $stmt->fetchAll(\PDO::FETCH_OBJ);
            do {
                try {
                    $results[] = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    //  Log::info('Filter Routing Profile List procedure Results' . count($results));
                    // foreach($results as $result) {
                    //    Log::info('Filter Routing Profile List procedure $syntax' . print_r($result,true));
                    // }
                } catch (\Exception $ex) {

                }
            } while ($stmt->nextRowset());

            //Log::info('Filter Routing Profile List procedure password' . Crypt::decrypt('eyJpdiI6IkRrbGRQTjh5V1JQeVJvTDZCNnh2Snc9PSIsInZhbHVlIjoiTGpVcWVYS1lcL2J1SXNXSFwvbXgwSzBBPT0iLCJtYWMiOiI2Mzc3MzUxNjhjM2MxOTljZDAyMTkyYTY5NWY3NTM2NTNkOWY5NjZiMjlhNWMxM2UyYTcxMzViZjBjMTY5MWI5In0='));
            //Crypt::decrypt('eyJpdiI6IkRrbGRQTjh5V1JQeVJvTDZCNnh2Snc9PSIsInZhbHVlIjoiTGpVcWVYS1lcL2J1SXNXSFwvbXgwSzBBPT0iLCJtYWMiOiI2Mzc3MzUxNjhjM2MxOTljZDAyMTkyYTY5NWY3NTM2NTNkOWY5NjZiMjlhNWMxM2UyYTcxMzViZjBjMTY5MWI5In0=');

            if (count($results) == 2) {
                $lcrDetails = $results[0];
                foreach ($lcrDetails as $lcrDetail) {
                    try {
                        if (!empty($lcrDetail->Password)) {
                            $lcrDetail->Password = Crypt::decrypt($lcrDetail->Password);
                        }
                    } catch (Exception $e) {

                    }

                }
            } else if (count($results) == 3) {
                //  Log::info('Filter Routing Profile List procedure bindvalues is second select' . count($lcrDetails));
                $lcrDetails = $results[2];
                //  Log::info('Filter Routing Profile List procedure bindvalues is second select' . count($lcrDetails));
                foreach ($lcrDetails as $lcrDetail) {
                    try {
                        if (!empty($lcrDetail->Password)) {
                            $lcrDetail->Password = Crypt::decrypt($lcrDetail->Password);
                        }
                    } catch (Exception $e) {

                        Log::info('Filter Routing Profile List procedure bindvalues is second select' . $e . getTraceAsString());
                    }

                }
            } else {
                $lcrDetails = '';
            }
            // Log::info('Filter Routing Profile List procedure bindvalues is second select' . count($lcrDetails));

            //$lcrDetails = $results;
            $routingDetails = array();
            $positionDetails = 0;
            $locationDetails = 0;
            $routingInfo = array();
            $lastVendorID = '';
            $locationDetail = '';


            // Log::info('Filter Routing Profile List procedure bindvalues is second select' . count($lcrDetails));
            $lcrDetails = json_decode(json_encode($lcrDetails), true);
                $endDate = microtime(true);//date('Y-m-d H:i:s');
            //   Log::info('Total Time in Seconds .' . strtotime($endDate) . ' ' . strtotime($startDate));
               $diff = $endDate - $startDate;
              Log::info('Total Time in Seconds .' . $diff);
            return Response::json($lcrDetails,Codes::$Code200[0]);
        }catch(Exception $ex) {
            Log::info('Exception in Routing API.' . $ex->getTraceAsString());
            return Response::json(["ErrorMessage"=>Codes::$Code500[1]],Codes::$Code500[0]);
        }
    }

    public function checkTimeZone($lcrDetail,$TimeZones,$DataAndTime) {


        $firstOfMonth = strtotime(date("Y-m-01", $DataAndTime));
        //Apply above formula.
        $weekNum =  intval(date("W", $DataAndTime)) - intval(date("W", $firstOfMonth)) + 1;
        $Month = date("n",$DataAndTime) . ",";
        $DayOfMonth = date("d",$DataAndTime) . ",";
        $Hour = date("H",$DataAndTime);
        $Minute = date("i",$DataAndTime);
        $Seconds = date("s",$DataAndTime);
        $timeZoneMatch = false;
        Log::info('DataAndTime TimeZone' . $DataAndTime .
            ' '.$Month.
            ' '.$DayOfMonth.
            ' '.$weekNum.
            ' '.date("H",$DataAndTime).
            ' '.date("i",$DataAndTime).
            ' '.date("s",$DataAndTime)
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
                    if (strpos($TimeZone->Months,$Month) > 0 || strpos($TimeZone->Months,',' . date("n",$DataAndTime)) > 0){
                        $TimeZoneMatch = 1;
                    }
                }

                if (!empty($TimeZone->DaysOfMonth)) {
                    $DaysOfMonthZoneSet = 0;
                    Log::info('$TimeZone->TimezoneId DaysOfMonth');
                    if ($monthZoneSet == 0 && $TimeZoneMatch == 0) {
                        $TimeZoneMatch = 0;
                    } else if (($monthZoneSet == 0 && $TimeZoneMatch == 1) && strpos($TimeZone->DaysOfMonth,$DayOfMonth) > 0 || strpos($TimeZone->DaysOfMonth,',' . date("d",$DataAndTime)) > 0){
                        $TimeZoneMatch = 1;
                    }else if (strpos($TimeZone->DaysOfMonth,$DayOfMonth) > 0 || strpos($TimeZone->DaysOfMonth,',' . date("d",$DataAndTime)) > 0){
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