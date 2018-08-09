<?php
class FusionPBX{
    private static $config = array();
    private static $dbname1 = 'fusionpbx';

   public function __construct($CompanyGatewayID){
       $setting = GatewayAPI::getSetting($CompanyGatewayID,'FusionPBX');
       foreach((array)$setting as $configkey => $configval){
           if($configkey == 'password'){
               self::$config[$configkey] = Crypt::decrypt($configval);
           }else{
               self::$config[$configkey] = $configval;
           }
       }
       if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
           extract(self::$config);
           Config::set('database.connections.pgsql.host',$dbserver);
           Config::set('database.connections.pgsql.database',self::$dbname1);
           Config::set('database.connections.pgsql.username',$username);
           Config::set('database.connections.pgsql.password',$password);

       }
    }
   public static function testConnection(){
       $response = array();
       if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){

           try{
               if(DB::connection('pgsql')->getDatabaseName()){
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
        $country = Country::getCountryDropdownList();
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "SELECT * FROM v_domains";
                // and userfield like '%outbound%'  removed for inbound calls
                $results = DB::connection('pgsql')->select($query);
                if(count($results)>0){
                    $tempItemData = array();
                    $batch_insert_array = array();
                    if(count($addparams)>0){
                        $CompanyGatewayID = $addparams['CompanyGatewayID'];
                        $CompanyID = $addparams['CompanyID'];
                        $ProcessID = $addparams['ProcessID'];
                        foreach ($results as $temp_row) {
                            $count = DB::table('tblAccount')->where(["Number" => $temp_row->domain_name, "AccountType" => 1])->count();
                            if($count==0){
                                $tempItemData['AccountName'] = !empty($temp_row->domain_description)?$temp_row->domain_description:$temp_row->domain_name;
                                $tempItemData['Number'] = $temp_row->domain_name;
                                /*$tempItemData['FirstName'] = $temp_row->first_name;
                                $tempItemData['LastName'] = $temp_row->last_name;
                                $tempItemData['VatNumber'] = $temp_row->vat_number;
                                $tempItemData['Address3'] = $temp_row->state;
                                $tempItemData['Country'] = isset($country[$temp_row->county]) && $temp_row->county != ''?$country[$temp_row->county]:null;
                                $tempItemData['City'] = $temp_row->city;
                                $tempItemData['PostCode'] = $temp_row->postcode;
                                $tempItemData['Address1'] = $temp_row->address;
                                $tempItemData['Phone'] = $temp_row->phone;
                                $tempItemData['Mobile'] = $temp_row->mob_phone;
                                $tempItemData['BillingEmail'] = $temp_row->email;
                                $tempItemData['Email'] = $temp_row->email;
                                $tempItemData['Address2'] = $temp_row->address2;
                                $tempItemData['Fax'] = $temp_row->fax;
                                $tempItemData['Skype'] = $temp_row->skype;
                                $tempItemData['Currency'] = isset($currency[$temp_row->currencyname]) && $temp_row->currencyname != ''?$currency[$temp_row->currencyname]:null;*/

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
    public static function getAccountsBalace($addparams=array()){
        $response = array();
        $response['balance'] = 0;
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "select * from mor.users where username='".$addparams['username']."' limit 1 "; // and userfield like '%outbound%'  removed for inbound calls
                //$response = DB::connection('pgsql')->select($query);
                $results = DB::connection('pgsql')->select($query);
                if(count($results)>0){
                    foreach ($results as $temp_row) {
                        $response['result'] = 'OK';
                        $response['balance'] = $temp_row->balance;
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