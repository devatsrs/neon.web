<?php
class VoipNow{
    private static $config = array();
    private static $dbname1 = 'voipnow';

   public function __construct($CompanyGatewayID){
       $setting = GatewayAPI::getSetting($CompanyGatewayID,'VoipNow');
       foreach((array)$setting as $configkey => $configval){
           if($configkey == 'password'){
               self::$config[$configkey] = Crypt::decrypt($configval);
           }else{
               self::$config[$configkey] = $configval;
           }
       }
       if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
           extract(self::$config);
           Config::set('database.connections.pbxmysql.host',$dbserver);
           Config::set('database.connections.pbxmysql.database',self::$dbname1);
           Config::set('database.connections.pbxmysql.username',$username);
           Config::set('database.connections.pbxmysql.password',$password);

       }
    }
   public static function testConnection(){
       $response = array();
       if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){

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
        $country = Country::getCountryDropdownList();
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "select client.*,country.name as country,timezone.name as timezone  from client
                          inner join (select min(id) as id from client group by company ) tbl on tbl.id = client.id
                          left join country on country.id = client.country_id
                          left join timezone on timezone.id = client.timezone_id
                         ";

                $results = DB::connection('pbxmysql')->select($query);
                if(count($results)>0){
                    $tempItemData = array();
                    $batch_insert_array = array();
                    if(count($addparams)>0){
                        $CompanyGatewayID = $addparams['CompanyGatewayID'];
                        $CompanyID = $addparams['CompanyID'];
                        $ProcessID = $addparams['ProcessID'];
                        foreach ($results as $temp_row) {
                            $count = DB::table('tblAccount')->where(["AccountName" => $temp_row->company, "AccountType" => 1])->count();
                            if($count==0){
                                $tempItemData['AccountName'] = $temp_row->company;
                                $tempItemData['FirstName'] = $temp_row->first_name;
                                $tempItemData['LastName'] = $temp_row->name;
                                $tempItemData['Address3'] = $temp_row->state;
                                $tempItemData['Country'] = isset($country[$temp_row->country]) && $temp_row->country != ''?$country[$temp_row->country]:null;
                                $tempItemData['City'] = $temp_row->city;
                                $tempItemData['PostCode'] = $temp_row->pcode;
                                $tempItemData['Address1'] = $temp_row->address;
                                $tempItemData['Phone'] = $temp_row->phone;
                                $tempItemData['BillingEmail'] = $temp_row->email;
                                $tempItemData['Email'] = $temp_row->email;
                                $tempItemData['Fax'] = $temp_row->fax;

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

}