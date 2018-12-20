<?php

/**
 * Created by PhpStorm.
 * User: VASIM
 * Date: 20-11-2018
 * Time: 03:53 PM
 */

use \Illuminate\Support\Facades\DB;

class SippyRatePush
{
    private static $SippySFTP;
    private static $SippySQL;

    function __Construct($CompanyGatewayID){
        self::$SippySFTP = new SippySFTP($CompanyGatewayID);
        self::$SippySQL  = new SippySQL($CompanyGatewayID);
    }

    public function checkAPIDatabaseSettings() {
        $APIResult = self::$SippySFTP->testAPIConnection();
        $DBResult  = self::$SippySQL->testConnection();

        $response = array();
        if(!empty($APIResult['result']) && $APIResult['result'] == 'OK') {
            $response['status'] = 1;
            $response['message'] = "OK";
        } else {
            $response['status'] = 0;
            $response['message'] = $APIResult['faultString'] . '\n';
        }
        if(!empty($DBResult['result']) && $DBResult['result'] == 'OK' && $response['status'] == 1) {
            $response['status'] = 1;
            $response['message'] = "OK";
        } else {
            $response['status'] = 0;
            $response['message'] = $DBResult['faultString'];
        }
        return $response;
    }

    public function getSippyVendorID($AccountID, $CompanyGatewayID){
        $Account    = Account::find($AccountID);
        $AuthRule   = DB::table('tblAccountAuthenticate')->where(['AccountID' => $AccountID]);
        $AuthRuleName = $AuthRuleValue = '';
        if($AuthRule->count() > 0) {
            $AuthRule = $AuthRule->first();
            $AuthRuleName   = $AuthRule->VendorAuthRule;
            $AuthRuleValue  = $AuthRule->VendorAuthValue;
        } else {
            $AuthRule = CompanyGateway::find($CompanyGatewayID);
            $AuthRule = json_decode($AuthRule->Settings);
            $AuthRuleName   = $AuthRule->NameFormat;
        }

        if($AuthRuleName != 'IP') {
            $AccountName = '';
            switch ($AuthRuleName) {
                case 'NAME':
                    $AccountName = $Account->AccountName;
                    break;
                case 'NUB':
                    $AccountName = $Account->Number;
                    break;
                case 'NAMENUB':
                    $AccountName = $Account->AccountName . '-' . $Account->Number;
                    break;
                case 'NUBNAME':
                    $AccountName = $Account->Number . '-' . $Account->AccountName;
                    break;
                case 'Other':
                    $AccountName = $AuthRuleValue;
            }

            $param['name'] = $AccountName;
            $result = self::$SippySFTP->getVendorInfo($param);

            if (!empty($result) && !isset($result['faultCode'])) {
                if(!empty($result['i_vendor'])){
                    $response['i_vendor'] = $result['i_vendor'];
                } else {
                    $response['error'] = 'Vendor '.$AccountName.' does not exist in sippy.';
                }
            } else {
                $response['error'] = 'Error while getting Vendor '.$AccountName.' from sippy. faultCode '.$result['faultCode'].' faultString '.$result['faultString'];
            }
        } else {
            $IPs = explode(',',$AuthRuleValue);
            if(!empty($IPs)) {
                $IPs = "'".implode("','",$IPs)."'";
                $param['remote_ip'] = $IPs;
                $result = self::$SippySQL->getVendorByIP($param);

                if (!empty($result) && !isset($result['faultCode'])) {
                    if(!empty($result[0]->i_vendor)){
                        $response['i_vendor'] = $result[0]->i_vendor;
                    } else {
                        $response['error'] = "IP ".$IPs." does not exist in sippy for Vendor :".$Account->AccountName;
                    }
                } else {
                    if(isset($result['faultCode']))
                        $response['error'] = 'Error while getting Vendor '.$Account->AccountName.' from sippy. faultCode '.$result['faultCode'].' faultString '.$result['faultString'];
                    else
                        $response['error'] = "IP ".$IPs." does not exist in sippy for Vendor :".$Account->AccountName;
                }
            } else {
                $response['error'] = 'No IPs setup against Vendor : '.$Account->AccountName;
            }
        }

        return $response;
    }

    public function getVendorConnectionsList($param,$AccountName){
        $result = self::$SippySFTP->getVendorConnectionsList($param);

        if (!empty($result) && !isset($result['faultCode'])) {
            if (isset($result['vendor_connections'])) {
                $response['vendor_connections'] = $result['vendor_connections'];
            } else {
                $response['error'] = 'No Connections Exist for Vendor '.$AccountName.' in sippy.';
            }
        } else {
            $response['error'] = 'Error while getting Connections for Vendor '.$AccountName.' from sippy. faultCode '.$result['faultCode'].' faultString '.$result['faultString'];
        }

        return $response;
    }

    public function getDestinationSetList($param,$AccountName){
        $result = self::$SippySQL->getDestinationSetList($param);

        if (!isset($result['faultCode']) && count($result)>0) {
            $response['destination_set'] = $result;
        } else {
            if(isset($result['faultCode']))
                $response['error'] = 'Error while getting Vendor '.$AccountName.' from sippy. faultCode '.$result['faultCode'].' faultString '.$result['faultString'];
            else
                $response['error'] = "No Destination Set exist in sippy for Vendor :".$AccountName;
        }

        return $response;
    }

}