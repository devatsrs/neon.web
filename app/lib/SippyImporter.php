<?php

/**
 * Created by PhpStorm.
 * User: srs4
 * Date: 10/11/2017
 * Time: 11:27 AM
 */
//namespace App\Lib;

use App\Lib\SippySFTP;

class SippyImporter
{
    public static function isAccountExist($username){
        $addparam = array();
        $isAccountExist = 0;
        $addparam['username'] = $username;
        try{
            $sippy = new SippySFTP();
            $response = $sippy->getAccountInfo($addparam);
            if (!empty($response) && !isset($response['faultCode'])) {
                if(!empty($response['authname'])){
                    $isAccountExist = 1;
                }
            }
        }catch (\Exception $e) {
            //Log::error($e);
        }
        return $isAccountExist;
    }

    public static function getAccountsDetail($addparams = array())
    {
        $response = array();
        $currency = Currency::getCurrencyDropdownIDList();
        $country = Country::getCountryDropdownList();

        $TimeZone = 'GMT';
        date_default_timezone_set($TimeZone);

        //Log:info('AddParam '.print_r($addparam,true));
        try {
            $sippy = new SippySFTP($addparams['CompanyGatewayID']);
            $account_list = $sippy->listAccounts();
            if (!isset($account_list['faultCode'])) {
                if (isset($account_list['accounts'])) {
                    Log::info(print_r(count($account_list['accounts']), true));

                    $tempItemData = array();
                    $batch_insert_array = array();
                    if (count((array)$account_list['accounts']) > 0) {
                        $CompanyGatewayID = $addparams['CompanyGatewayID'];
                        $CompanyID = $addparams['CompanyID'];
                        $ProcessID = $addparams['ProcessID'];
                        foreach ((array)$account_list['accounts'] as $row_account) {
                            $count = DB::table('tblAccount')->where(["AccountName" => $row_account['username'], "AccountType" => 1,"CompanyId"=>$CompanyID])->count();
                            if($count==0){
                                $params['i_account'] = $row_account['i_account'];
                                $account_detail = $sippy->getAccountInfo($params);

                                if (!isset($account_detail['faultCode'])) {
                                    if (isset($account_detail['username'])) {
                                        $tempItemData['AccountName'] = $account_detail['username'];
                                        $tempItemData['Number'] = "";
                                        $tempItemData['FirstName'] = $account_detail['first_name'];
                                        $tempItemData['LastName'] = $account_detail['last_name'];
                                        $tempItemData['Address1'] = $account_detail['street_addr'];
                                        $tempItemData['City'] = $account_detail['city'];
                                        $tempItemData['State'] = $account_detail['state'];
                                        $tempItemData['Country'] = isset($country[$account_detail['country']]) && $account_detail['country'] != ''?$country[$account_detail['country']]:null;
                                        $tempItemData['Currency'] = isset($currency[$account_detail['payment_currency']]) && $account_detail['payment_currency'] != ''?$currency[$account_detail['payment_currency']]:null;
                                        $tempItemData['TimeZone'] = $account_detail['i_time_zone'];
                                        $tempItemData['Blocked'] = $account_detail['blocked'];
                                        $tempItemData['PaymentMethod'] = $account_detail['payment_method'];
                                        $tempItemData['Company'] = $account_detail['company_name'];
                                        $tempItemData['PostCode'] = $account_detail['postal_code'];
                                        $tempItemData['Mobile'] = $account_detail['contact'];
                                        $tempItemData['Phone'] = $account_detail['phone'];
                                        $tempItemData['Fax'] = $account_detail['fax'];
                                        $tempItemData['Email'] = $account_detail['email'];
                                        $tempItemData['Description'] = $account_detail['description'];

                                        $tempItemData['AccountType'] = 1;
                                        $tempItemData['CompanyId'] = $CompanyID;
                                        $tempItemData['Status'] = 1;
                                        $tempItemData['LeadSource'] = 'Gateway import';
                                        $tempItemData['CompanyGatewayID'] = $CompanyGatewayID;
                                        $tempItemData['ProcessID'] = $ProcessID;
                                        $tempItemData['created_at'] = date('Y-m-d H:i:s.000');
                                        $tempItemData['created_by'] = 'Imported';

                                        $batch_insert_array[] = $tempItemData;
                                    }
                                }
                            }
                        }
                        if (!empty($batch_insert_array)) {
                            //Log::info('insertion start');
                            try{
                                if(DB::table('tblTempAccount')->insert($batch_insert_array)){
                                    $response['result'] = 'OK';
                                }
                            }catch(Exception $err){
                                $response['faultString'] =  $err->getMessage();
                                $response['faultCode'] =  $err->getCode();
                                Log::error("Class Name:".__CLASS__.",Method: ". __METHOD__.", Fault. Code: " . $err->getCode(). ", Reason: " . $err->getMessage());
                                //throw new Exception($err->getMessage());
                            }
                            //Log::info('insertion end');
                        }else{
                            $response['result'] = 'OK';
                        }
                    }
                }

                return $batch_insert_array;
            }
        } catch (\Exception $e) {
            //Log::error($e);
        }
    }

            /*public static function createAccount($account){
                $addparam = array();

                $TimeZone = 'GMT';
                date_default_timezone_set($TimeZone);

                //Log:info('AddParam '.print_r($addparam,true));
                try{
                    $sippy = new Sippy();

                    $isAccountExist = self::isAccountExist($account->AccountName);
                    $Currency = \Currency::find($account->CurrencyId)->Code;
                    $Company = \Company::find($account->CompanyId)->CompanyName;

                    if($isAccountExist == 0) {
                        $addparam['username']                   = $account->AccountName;
                        $addparam['web_password']               = '';
                        $addparam['authname']                   = '';
                        $addparam['voip_password']              = '';
                        $addparam['max_sessions']               = 2;
                        $addparam['max_credit_time']            = '';
                        $addparam['translation_rule']           = '';
                        $addparam['cli_translation_rule']       = '';
                        $addparam['credit_limit']               = 0.00;
                        $addparam['i_routing_group']            = '';
                        $addparam['i_billing_plan']             = '';
                        $addparam['i_time_zone']                = $account->TimeZone;
                        $addparam['balance']                    = 0.00;
                        $addparam['cpe_number']                 = '';
                        $addparam['vm_enabled']                 = '';
                        $addparam['vm_password']                = '';
                        $addparam['blocked']                    = $account->Blocked;
                        $addparam['i_lang']                     = 'en';
                        $addparam['payment_currency']           = $Currency;
                        $addparam['payment_method']             = $account->PaymentMethod;
                        $addparam['i_export_type']              = '';
                        $addparam['lifetime']                   = '';
                        $addparam['preferred_codec']            = NULL;
                        $addparam['use_preferred_codec_only']   = '';
                        $addparam['reg_allowed']                = '';
                        $addparam['welcome_call_ivr']           = '';
                        $addparam['on_payment_action']          = NULL;
                        $addparam['min_payment_amount']         = '';
                        $addparam['trust_cli']                  = '';
                        $addparam['disallow_loops']             = '';
                        $addparam['vm_notify_emails']           = '';
                        $addparam['vm_forward_emails']          = '';
                        $addparam['vm_del_after_fwd']           = '';
                        $addparam['company_name']               = $Company;
                        $addparam['salutation']                 = '';
                        $addparam['first_name']                 = $account->FirstName;
                        $addparam['last_name']                  = $account->LastName;
                        $addparam['mid_init']                   = '';
                        $addparam['street_addr']                = $account->Address1 . ' ' . $account->Address2 . ' ' . $account->Address3;
                        $addparam['state']                      = $account->State;
                        $addparam['postal_code']                = $account->PostCode;
                        $addparam['city']                       = $account->City;
                        $addparam['country']                    = $account->Country;
                        $addparam['contact']                    = $account->Mobile;
                        $addparam['phone']                      = $account->Phone;
                        $addparam['fax']                        = $account->Fax;
                        $addparam['alt_phone']                  = '';
                        $addparam['alt_contact']                = '';
                        $addparam['email']                      = $account->Email;
                        $addparam['cc']                         = '';
                        $addparam['bcc']                        = '';
                        $addparam['i_password_policy']          = '';
                        $addparam['i_media_relay_type']         = '';
                        $addparam['i_incoming_anonymous_action'] = '';
                        $addparam['description']                = $account->Description;
                        //$addparam['i_tariff']                   = '';

                        $response = $sippy->createAccount($addparam);
                    } else {
                        $addparam['username']                   = $account->AccountName;
                        $addparam['i_time_zone']                = $account->TimeZone;
                        $addparam['blocked']                    = $account->Blocked;
                        $addparam['payment_currency']           = $Currency;
                        $addparam['payment_method']             = $account->PaymentMethod;
                        $addparam['company_name']               = $Company;
                        $addparam['first_name']                 = $account->FirstName;
                        $addparam['last_name']                  = $account->LastName;
                        $addparam['street_addr']                = $account->Address1 . ' ' . $account->Address2 . ' ' . $account->Address3;
                        $addparam['state']                      = $account->State;
                        $addparam['postal_code']                = $account->PostCode;
                        $addparam['city']                       = $account->City;
                        $addparam['country']                    = $account->Country;
                        $addparam['contact']                    = $account->Mobile;
                        $addparam['phone']                      = $account->Phone;
                        $addparam['fax']                        = $account->Fax;
                        $addparam['email']                      = $account->Email;
                        $addparam['description']                = $account->Description;

                        $response = $sippy->updateAccount($account->AccountName);
                    }

                    if (!isset($response['faultCode'])) {
                        if (isset($response['result']) && $response['result'] == 'OK') {
                            Log::info(print_r($response, true));
                            $response = (array)$response;

                            $created_at = $updated_at = date('Y-m-d H:i:s');

                            $accountData['i_account']           = $response['i_account'];
                            $accountData['CompanyGatewayID']    = $response['i_account'];
                            $accountData['updated_at']          = $updated_at;

                            try {
                                if (SippyAccount::where('AccountID', $account->AccountID)->count() > 0) {
                                    SippyAccount::update($accountData)->where('AccountID', $account->AccountID);
                                } else {
                                    $accountData['AccountID']   = $account->AccountID;
                                    $accountData['created_at']  = $created_at;
                                    SippyAccount::create($accountData);
                                }
                            } catch (\Exception $e) {
                                Log::error($e);
                            }
                        }
                    }
                }catch (\Exception $e) {
                    //Log::error($e);
                }
                return $accountData;
            }*/

}