<?php
use Curl\Curl;
class Porta{
    private static $config = array();
    private static $cli;
    private static $timeout=0; /* 60 seconds timeout */

   public function __construct($CompanyGatewayID){
       $setting = GatewayAPI::getSetting($CompanyGatewayID,'Porta');
       foreach((array)$setting as $configkey => $configval){
           if($configkey == 'password'){
               self::$config[$configkey] = Crypt::decrypt($configval);
           }else{
               self::$config[$configkey] = $configval;
           }
       }
       if(count(self::$config) && isset(self::$config['api_url']) && isset(self::$config['password'])){
           self::$cli =  new Curl();
       }
    }
   public static function testConnection(){
       $response = array();
       if(count(self::$config) && isset(self::$config['api_url']) && isset(self::$config['password'])){
           $api_url = self::$config['api_url'].'/GetCustomersShortInfo/'.self::$config['password'].'/?format=json';
           self::$cli->get($api_url);
           if(isset(self::$cli->response) && self::$cli->response != '') {
                   $ResponseArray = json_decode(self::$cli->response, true);
                   if(!empty($ResponseArray) && isset($ResponseArray['CustomerRes'])) {
                       $response['result'] = 'OK';
                   }
           }else if(isset(self::$cli->error_message) && isset(self::$cli->error_code)){
               $response['faultString'] =  self::$cli->error_message;
               $response['faultCode'] =  self::$cli->error_code;
           }
           self::$cli->close();
       }
       return $response;
   }
    public static function listAccounts($addparams=array()){

    }
    public static function getAccountCDRs($addparams=array()){

    }
    public static function listVendors($addparams=array()){

    }

    //get data from gateway and insert in temp table
    public static function getAccountsDetail($addparams=array()){
        $response = array();
        if(count(self::$config) && isset(self::$config['api_url']) && isset(self::$config['password'])){
            $api_url = self::$config['api_url'].'/getcustomersshortinfowithnolimit/'.self::$config['password'].'/true?format=json';
            self::$cli->get($api_url);
            if(isset(self::$cli->response) && self::$cli->response != '') {
                $ResponseArray = json_decode(self::$cli->response, true); //get list of customer
                if(!empty($ResponseArray) && isset($ResponseArray['CustomerRes'])) {

                    if(count($ResponseArray['CustomersShortInfo'])>0 && count($addparams)>0){
                        $tempItemData = array();
                        $batch_insert_array = array();
                        $CompanyGatewayID = $addparams['CompanyGatewayID'];
                        $CompanyID = $addparams['CompanyID'];
                        $ProcessID = $addparams['ProcessID'];
                        $FirstName = $LastName = $NamePrefix = '';
                        $Address1 = $Address2 = $Address3 = $City = $Postcode = $Country = '';
                        $Phone1 = $Phone2 = $Fax = '';
                        foreach ((array)$ResponseArray['CustomersShortInfo'] as $row_account) {
                            $customer_api_url = self::$config['api_url'].'/GetCustomer/'.self::$config['password'].'/'.$row_account['ICustomer'].'/?format=json';
                            self::$cli->get($customer_api_url);
                            if(isset(self::$cli->response) && self::$cli->response != '') {
                                $ResponseDetailArray = json_decode(self::$cli->response, true); //get customer detail
                                if(count($ResponseDetailArray)>0){
                                    if(isset($ResponseDetailArray) && count($ResponseDetailArray['Customer'])>0){
                                        //person detail from gateway
                                        $PersonalInformation = $ResponseDetailArray['Customer']['PersonalInformation'];
                                        if(!empty($PersonalInformation)){
                                            $FirstName = $PersonalInformation['FirstName'];
                                            $LastName = $PersonalInformation['LastName'];
                                            $NamePrefix = $PersonalInformation['Salutation'];
                                        }

                                        //addrress detail from gateway
                                        $CustomerAddress = $ResponseDetailArray['Customer']['CustomerAddress'];
                                        if(!empty($CustomerAddress)){
                                            $Address1 = $CustomerAddress['Address1'];
                                            $Address2 = $CustomerAddress['Address2'];
                                            $Address3 = $CustomerAddress['Address3'];
                                            $City = $CustomerAddress['City'];
                                            $Postcode = $CustomerAddress['Postcode'];
                                            $Country = $CustomerAddress['Country'];
                                        }
                                        //Contact detail from gateway
                                        $CustomerContact = $ResponseDetailArray['Customer']['CustomerContact'];
                                        if(!empty($CustomerContact)){
                                            $Phone1 = $CustomerContact['Phone1'];
                                            $Phone2 = $CustomerContact['Phone2'];
                                            $Fax = $CustomerContact['Fax'];
                                        }
                                    }
                                }

                            }
                            $tempItemData['AccountName'] = $row_account['Name'];
                            if(!empty($row_account['email'])){
                                $tempItemData['Email'] = $row_account['email'];
                            }else{
                                $tempItemData['Email'] ='';
                            }
                            if(!empty($FirstName)){
                                $tempItemData['FirstName'] = $FirstName;
                            }else{
                                $tempItemData['FirstName'] = '';
                            }
                            if(!empty($LastName)){
                                $tempItemData['LastName'] = $LastName;
                            }else{
                                $tempItemData['LastName'] = '';
                            }
                            if(!empty($NamePrefix)){
                                $tempItemData['NamePrefix'] = $NamePrefix;
                            }else{
                                $tempItemData['NamePrefix'] = '';
                            }
                            if(!empty($Address1)){
                                $tempItemData['Address1'] = $Address1;
                            }else{
                                $tempItemData['Address1'] = '';
                            }
                            if(!empty($Address2)){
                                $tempItemData['Address2'] = $Address2;
                            }else{
                                $tempItemData['Address2'] = '';
                            }
                            if(!empty($Address3)){
                                $tempItemData['Address3'] = $Address3;
                            }else{
                                $tempItemData['Address3'] = '';
                            }
                            if(!empty($City)){
                                $tempItemData['City'] = $City;
                            }else{
                                $tempItemData['City'] ='';
                            }
                            if(!empty($Postcode)){
                                $tempItemData['PostCode'] = $Postcode;
                            }else{
                                $tempItemData['PostCode'] = '';
                            }
                            if(!empty($Country)){
                                $checkCountry=strtoupper($Country);
                                if($checkCountry=='UK'){
                                    $checkCountry = 'UNITED KINGDOM';
                                }
                                $count = DB::table('tblCountry')->where(["Country" => $checkCountry])->count();
                                if($count>0){
                                    $tempItemData['Country'] = $checkCountry;
                                }else{
                                    $tempItemData['Country'] = '';
                                }
                            }else{
                                $tempItemData['Country'] = '';
                            }
                            if(!empty($Phone1)){
                                $tempItemData['Phone'] = $Phone1;
                            }else{
                                $tempItemData['Phone'] = '';
                            }
                            if(!empty($Phone2)){
                                $tempItemData['Mobile'] = $Phone2;
                            }else{
                                $tempItemData['Mobile'] = '';
                            }
                            if(!empty($Fax)){
                                $tempItemData['Fax'] = $Fax;
                            }else{
                                $tempItemData['Fax'] ='';
                            }
                            $tempItemData['AccountType'] = 1;
                            $tempItemData['CompanyId'] = $CompanyID;
                            $tempItemData['Status'] = 1;
                            $tempItemData['LeadSource'] = 'Gateway import';
                            $tempItemData['CompanyGatewayID'] = $CompanyGatewayID;
                            $tempItemData['ProcessID'] = $ProcessID;
                            $tempItemData['created_at'] = date('Y-m-d H:i:s.000');
                            $tempItemData['created_by'] = 'Imported';

                            if(!empty($tempItemData['AccountName'])){
                                $count = DB::table('tblAccount')->where(["AccountName" => $tempItemData['AccountName'], "AccountType" => 1])->count();
                                if($count==0){
                                    $batch_insert_array[] = $tempItemData;
                                }
                            }
                        } // get data from gateway

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
                    } // insert into temp account

                }else{
                    $response['result'] = 'OK';
                }
            }else if(isset(self::$cli->error_message) && isset(self::$cli->error_code)){
                $response['faultString'] =  self::$cli->error_message;
                $response['faultCode'] =  self::$cli->error_code;
                Log::error("Class Name:".__CLASS__.",Method: ". __METHOD__.", Fault. Code: " . self::$cli->error_code. ", Reason: " . self::$cli->error_message);
                //throw new Exception(self::$cli->error_message);
            }
        }
        return $response;

    }

}