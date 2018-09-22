<?php
use Curl\Curl;
class VoipMS{
    private static $config = array();
    private static $cli;
    private static $timeout=0; /* 60 seconds timeout */

   public function __construct($CompanyGatewayID){
       $setting = GatewayAPI::getSetting($CompanyGatewayID,'VoipMS');
       foreach((array)$setting as $configkey => $configval){
           if($configkey == 'password'){
               self::$config[$configkey] = Crypt::decrypt($configval);
           }else{
               self::$config[$configkey] = $configval;
           }
       }
       self::$config['method'] = "getLanguages";//test request

       if(count(self::$config) && isset(self::$config['api_url']) &&  isset(self::$config['username']) && isset(self::$config['password'])){
           self::$cli =  new Curl();
       }
    }
   public static function testConnection(){
       $response = array();
       if(count(self::$config) && isset(self::$config['api_url']) &&  isset(self::$config['username'])  && isset(self::$config['password'])){
           $api_url = self::$config['api_url'].'?api_username='.self::$config['username'].'&api_password='.self::$config['password'].'&method='.self::$config['method'];
           self::$cli->get($api_url);
           if(isset(self::$cli->response) && self::$cli->response != '') {
               $ResponseArray = json_decode(self::$cli->response, true);
               if(!empty($ResponseArray) && isset($ResponseArray['status']) && $ResponseArray['status'] == 'success') {
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
        if(count(self::$config) && isset(self::$config['api_url']) &&  isset(self::$config['username'])  && isset(self::$config['password'])){
            $api_url = self::$config['api_url'].'?api_username='.self::$config['username'].'&api_password='.self::$config['password'].'&method=getSubAccounts';
            self::$cli->get($api_url);
            if(isset(self::$cli->response) && self::$cli->response != '') {
                $ResponseArray = json_decode(self::$cli->response, true); //get list of customer
                if(!empty($ResponseArray) && isset($ResponseArray['accounts'])) {

                    if(count($ResponseArray['accounts'])>0 && count($addparams)>0){
                        $tempItemData = array();
                        $batch_insert_array = array();
                        $CompanyGatewayID = $addparams['CompanyGatewayID'];
                        $CompanyID = $addparams['CompanyID'];
                        $ProcessID = $addparams['ProcessID'];
                        foreach ($ResponseArray['accounts'] as $row_account) {
                            $tempItemData['AccountName'] = $row_account['username'];
                            $tempItemData['Number'] = $row_account['account'];
                            $tempItemData['FirstName'] = "";
                            $tempItemData['LastName'] = "";
                            $tempItemData['VatNumber'] = "";
                            $tempItemData['Address3'] = "";
                            $tempItemData['Country'] = null;
                            $tempItemData['City'] = "";
                            $tempItemData['PostCode'] = "";
                            $tempItemData['Address1'] = "";
                            $tempItemData['Phone'] = "";
                            $tempItemData['Mobile'] = "";
                            $tempItemData['BillingEmail'] = "";
                            $tempItemData['Email'] = "";
                            $tempItemData['Address2'] = "";
                            $tempItemData['Fax'] = "";
                            $tempItemData['Skype'] = "";
                            $tempItemData['Currency'] = null;

                            $tempItemData['AccountType'] = 1;
                            $tempItemData['CompanyId'] = $CompanyID;
                            $tempItemData['Status'] = 1;
                            $tempItemData['LeadSource'] = 'Gateway import';
                            $tempItemData['CompanyGatewayID'] = $CompanyGatewayID;
                            $tempItemData['ProcessID'] = $ProcessID;
                            $tempItemData['created_at'] = date('Y-m-d H:i:s.000');
                            $tempItemData['created_by'] = 'Imported';
                            $batch_insert_array[] = $tempItemData;

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