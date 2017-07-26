<?php
class Streamco{
    private static $config = array();
    private static $dbname1 = 'config';

    public function __construct($CompanyGatewayID){
        $setting = GatewayAPI::getSetting($CompanyGatewayID,'Streamco');
        foreach((array)$setting as $configkey => $configval){
            if($configkey == 'dbpassword'){
                self::$config[$configkey] = Crypt::decrypt($configval);
            }else{
                self::$config[$configkey] = $configval;
            }
        }
        if(count(self::$config) && isset(self::$config['host']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])){
            extract(self::$config);

            Config::set('database.connections.pbxmysql.host',$host);
            Config::set('database.connections.pbxmysql.database',self::$dbname1);
            Config::set('database.connections.pbxmysql.username',$dbusername);
            Config::set('database.connections.pbxmysql.password',$dbpassword);

        }
    }
    public static function testConnection(){
        $response = array();
        if(count(self::$config) && isset(self::$config['host']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])){

            try{
                if(DB::connection('pbxmysql')->getDatabaseName()){
                    $response['result'] = 'OK';
                }
            }catch(Exception $e){
                $response['faultString'] =  $e->getMessage();
                $response['faultCode'] =  $e->getCode();
            }
        }
        return $response;
    }

    //get data from gateway and insert in temp table
    public static function getAccountsDetail($addparams=array()){
        $response = array();
        $currency = Currency::getCurrencyDropdownIDList();
        if(count(self::$config) && isset(self::$config['host']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])){
            try{
//                echo "<pre>";print_r($addparams);exit();
                if(isset($addparams['AccountType']) && $addparams['AccountType'] != '') {
                    if($addparams['AccountType'] == 'customers') {
                        $AccountType = 'originators';
                        $where = ["IsCustomer" => 1];
                    } else if ($addparams['AccountType'] == 'vendors') {
                        $AccountType = 'terminators';
                        $where = ["IsVendor" => 1];
                    } else {
                        $AccountType = 'originators';
                        $where = ["IsCustomer" => 1];
                    }
                } else {
                    $AccountType = 'originators';
                    $where = ["IsCustomer" => 1];
                }
                $query = "select a.name,a.enabled,c.email,c.invoice_email,c.address from ".self::$dbname1.".".$AccountType." a LEFT JOIN ".self::$dbname1.".companies c ON a.company_id=c.id"; // and userfield like '%outbound%'  removed for inbound calls
                //$response = DB::connection('pbxmysql')->select($query);
                $results = DB::connection('pbxmysql')->select($query);
                if(count($results)>0){
                    $tempItemData = array();
                    $batch_insert_array = array();
                    if(count($addparams)>0){
                        $CompanyGatewayID = $addparams['CompanyGatewayID'];
                        $CompanyID = $addparams['CompanyID'];
                        $ProcessID = $addparams['ProcessID'];
                        foreach ($results as $temp_row) {
                            $where1 = array("AccountName" => $temp_row->name, "AccountType" => 1,"CompanyId"=>$CompanyID);
                            $where = $where1+$where;
                            $count = DB::table('tblAccount')->where($where)->count();
                            if($count==0){
                                $tempItemData['AccountName'] = $temp_row->name;
//                                $tempItemData['Number'] = $temp_row->name;
                                $tempItemData['FirstName'] = "";
                                $tempItemData['Address1'] = $temp_row->address;
                                $tempItemData['Phone'] = "";
                                $tempItemData['BillingEmail'] = $temp_row->invoice_email;
                                $tempItemData['Email'] = $temp_row->email;
//                                $tempItemData['Currency'] = isset($currency[$temp_row->name]) && $temp_row->name != ''?$currency[$temp_row->name]:null;
                                $tempItemData['AccountType'] = 1;
                                $tempItemData['CompanyId'] = $CompanyID;
                                $tempItemData['Status'] = $temp_row->enabled == 'yes' ? 1 : 0;
                                $tempItemData['IsCustomer'] = $AccountType == 'originators' ? 1 : 0;
                                $tempItemData['IsVendor'] = $AccountType == 'terminators' ? 1 : 0;
                                $tempItemData['LeadSource'] = 'Gateway import';
                                $tempItemData['CompanyGatewayID'] = $CompanyGatewayID;
                                $tempItemData['ProcessID'] = $ProcessID;
                                $tempItemData['created_at'] = date('Y-m-d H:i:s.000');
                                $tempItemData['created_by'] = 'Imported';
                                $batch_insert_array[] = $tempItemData;
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
            }catch(Exception $e){
                $response['faultString'] =  $e->getMessage();
                $response['faultCode'] =  $e->getCode();
                Log::error("Class Name:".__CLASS__.",Method: ". __METHOD__.", Fault. Code: " . $e->getCode(). ", Reason: " . $e->getMessage());
                //throw new Exception($e->getMessage());
            }
        }
        return $response;
    }

}