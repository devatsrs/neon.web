<?php

/**
 * Created by PhpStorm.
 * User: VASIM
 * Date: 20-11-2018
 * Time: 03:53 PM
 */

use \Illuminate\Support\Facades\DB;

class SippyRatePushController extends \BaseController {

    private static $SippySFTP;
    private static $SippySQL;

    public function index($id) {
        $CompanyID = User::get_companyID();
        $SippyGatewayList = CompanyGateway::where(['Status'=>1,'CompanyID'=>$CompanyID])->whereIN("GatewayID",[6,15])->lists('Title', 'CompanyGatewayID');
        return View::make('sippy_rate_push.destination_set_mapping', compact('SippyGatewayList','id'));
    }

    public function getDestinationSetList($CompanyGatewayID) {
        $formdata = Input::all();
        $CompanyID          = User::get_companyID();
        $response['error']  = array();

        $SippyRatePush = new SippyRatePush($CompanyGatewayID);
        $checkSettings = $SippyRatePush->checkAPIDatabaseSettings();

        $Data["aaData"] = array();
        if($checkSettings['status'] == 1) {
            $account = Account::join('tblVendorTrunk', 'tblAccount.AccountID', '=', 'tblVendorTrunk.AccountID')
                ->join('tblTrunk', 'tblTrunk.TrunkID', '=', 'tblVendorTrunk.TrunkID');

            $account->where([
                "tblAccount.CompanyId" => $CompanyID,
                "tblAccount.AccountType" => 1,
                "tblAccount.AccountID" => 4360,
                "tblAccount.VerificationStatus" => Account::VERIFIED,
                "tblVendorTrunk.Status" => 1,
                "tblTrunk.Status" => 1,
            ]);

            $accounts = $account->distinct()->select('tblAccount.CompanyId', 'tblAccount.AccountID', 'tblAccount.AccountName', 'tblAccount.Number')->get();

            foreach ($accounts as $account) {
                $Trunks = Trunk::join('tblVendorTrunk', 'tblTrunk.TrunkID', '=', 'tblVendorTrunk.TrunkID')
                    ->where(['AccountID' => $account->AccountID, "tblVendorTrunk.Status" => 1, "tblTrunk.Status" => 1])
                    ->select('tblTrunk.TrunkID', 'tblTrunk.Trunk')
                    ->get();

                $result_i_vendor = $SippyRatePush->getSippyVendorID($account->AccountID, $CompanyGatewayID);

                if (!isset($result_i_vendor['error']) && !empty($result_i_vendor['i_vendor'])) {
                    $param['i_vendor'] = $result_i_vendor['i_vendor'];
                    $connections = $SippyRatePush->getVendorConnectionsList($param, $account->AccountName);

                    if (isset($connections['vendor_connections']) && count((array)$connections['vendor_connections']) > 0) {
                        foreach ((array)$connections['vendor_connections'] as $row_connections) {
                            $paramD['i_connection'] = $row_connections['i_connection'];
                            $result_destination_set = $SippyRatePush->getDestinationSetList($paramD, $account->AccountName);

                            if (isset($result_destination_set['destination_set'])) {
                                $i = 0;
                                foreach ($result_destination_set['destination_set'] as $destination_set) {
                                    foreach ($Trunks as $Trunk) {
                                        $arr = array();
                                        $neon_destination_set = DB::table('tblSippyDestinationSet')->where([
                                            "CompanyGatewayID" => $CompanyGatewayID,
                                            "AccountID" => $account->AccountID,
                                            "TrunkID" => $Trunk->TrunkID,
                                            "i_vendor" => $result_i_vendor['i_vendor'],
                                            "i_destination_set" => $destination_set->i_destination_set
                                        ]);

                                        if ($neon_destination_set->count() > 0) {
                                            $neon_destination_set = $neon_destination_set->first();
                                            $arr[4] = $neon_destination_set->SippyDestinationSetID; //SippyDestinationSetID - 4
                                            $arr[1] = $neon_destination_set->code_rule; //code_rule - 1
                                        } else {
                                            $arr[4] = ""; //SippyDestinationSetID - 4
                                            $arr[1] = ""; //code_rule - 1
                                        }
                                        $arr[0] = $account->AccountName; //AccountName - 0
                                        $arr[2] = $Trunk->Trunk; //TrunkName - 2
                                        $arr[3] = $destination_set->name; //destination_set_name - 3
                                        $arr[5] = $CompanyGatewayID; //CompanyGatewayID - 5
                                        $arr[6] = $account->AccountID; //AccountID - 6
                                        $arr[7] = $Trunk->TrunkID; //TrunkID - 7
                                        $arr[8] = $result_i_vendor['i_vendor']; //i_vendor - 8
                                        $arr[9] = $row_connections['i_connection']; //i_connection - 9
                                        $arr[10] = $destination_set->i_destination_set; //i_destination_set - 10

                                        array_push($Data["aaData"], $arr);
                                    }
                                    $i++;
                                }
                            } else {
                                $response['error'][] = $result_destination_set['error'];
                            }
                        }
                    } else {
                        $response['error'][] = $connections['error'];
                    }
                } else {
                    $response['error'][] = $result_i_vendor['error'];
                }
            }

            foreach ($Data["aaData"] as $key => $row) {
                $iSortCol_0[$key] = $row[$formdata['iSortCol_0']];
            }
            $sort = $formdata['sSortDir_0'] == 'asc' ? SORT_ASC : SORT_DESC;
            array_multisort($iSortCol_0, $sort, $Data["aaData"]);

            if (!empty($response['error'])) {
                $Data['error'] = $response['error'];
            }

            $Data['sColumns'] = ["SippyDestinationSetID", "code_rule", "CompanyGatewayID", "AccountID", "AccountName", "TrunkID", "TrunkName", "i_vendor", "i_connection", "i_destination_set", "destination_set_name"];
            if (!empty($Data)) {
                $Data['iTotalRecords'] = count($Data["aaData"]);
                $Data['iTotalDisplayRecords'] = count($Data["aaData"]);
                $Data['Total']['totalcount'] = count($Data["aaData"]);
            } else {
                $Data['iTotalRecords'] = 0;
                $Data['iTotalDisplayRecords'] = 0;
                $Data['Total']['totalcount'] = 0;
            }
        } else {
            $Data['error'] = $checkSettings['message'];
            $Data['iTotalRecords'] = 0;
            $Data['iTotalDisplayRecords'] = 0;
            $Data['Total']['totalcount'] = 0;
        }
        return json_encode($Data);
    }

}