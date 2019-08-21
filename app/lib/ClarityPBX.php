<?php
class ClarityPBX {
    private static $config = array();
    private static $dbname = 'clarity';

    public function __construct($CompanyGatewayID){
        $setting = GatewayAPI::getSetting($CompanyGatewayID,'ClarityPBX');
        foreach((array)$setting as $configkey => $configval){
            if($configkey == 'password'){
                self::$config[$configkey] = Crypt::decrypt($configval);
            }else{
                self::$config[$configkey] = $configval;
            }
        }
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            //extract(self::$config);
            Config::set('database.connections.pgsql.host',self::$config['dbserver']);
            Config::set('database.connections.pgsql.database',self::$dbname);
            Config::set('database.connections.pgsql.username',self::$config['username']);
            Config::set('database.connections.pgsql.password',self::$config['password']);
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
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "select * from customer ";
                $results = DB::connection('pgsql')->select($query);
                if(count($results)>0){
                    $tempItemData = array();
                    $batch_insert_array = array();
                    if(count($addparams)>0){
                        $CompanyGatewayID = $addparams['CompanyGatewayID'];
                        $CompanyID = $addparams['CompanyID'];
                        $ProcessID = $addparams['ProcessID'];
                        foreach ($results as $temp_row) {
                            $count = DB::table('tblAccount')->where(["AccountName" => $temp_row->descr, "AccountType" => 1])->count();
                            if($count==0){
                                $tempItemData['AccountName'] = $temp_row->descr;
                                $tempItemData['AccountType'] = 1;
                                $tempItemData['CompanyId'] = $CompanyID;
                                $tempItemData['Status'] = 1;
                                $tempItemData['IsCustomer'] = 1;
                                $tempItemData['LeadSource'] = 'Gateway import';
                                $tempItemData['CompanyGatewayID'] = $CompanyGatewayID;
                                $tempItemData['ProcessID'] = $ProcessID;
                                $tempItemData['created_at'] = date('Y-m-d H:i:s.000');
                                $tempItemData['created_by'] = 'Imported';
                                $batch_insert_array[] = $tempItemData;
                            }
                        }
                        if (!empty($batch_insert_array)) {
                            try{
                                if(DB::table('tblTempAccount')->insert($batch_insert_array)){
                                    $response['result'] = 'OK';
                                    $response['Gateway'] = 'ClarityPBX';
                                }
                            }catch(Exception $err){
                                $response['faultString'] =  $err->getMessage();
                                $response['faultCode'] =  $err->getCode();
                                Log::error("Class Name:".__CLASS__.",Method: ". __METHOD__.", Fault. Code: " . $err->getCode(). ", Reason: " . $err->getMessage());
                                //throw new Exception($err->getMessage());
                            }
                        }else{
                            $response['result'] = 'OK';
                            $response['Gateway'] = 'ClarityPBX';
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

    //get data from gateway and insert in temp table
    public static function getVendorsDetail($addparams=array()){
        $response = array();
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "select * from vendor ";
                $results = DB::connection('pgsql')->select($query);
                if(count($results)>0){
                    $tempItemData = array();
                    $batch_insert_array = array();
                    if(count($addparams)>0){
                        $CompanyGatewayID = $addparams['CompanyGatewayID'];
                        $CompanyID = $addparams['CompanyID'];
                        $ProcessID = $addparams['ProcessID'];
                        foreach ($results as $temp_row) {
                            $count = DB::table('tblAccount')->where(["AccountName" => $temp_row->descr, "AccountType" => 1])->count();
                            if($count==0){
                                $tempItemData['AccountName'] = $temp_row->descr;
                                $tempItemData['AccountType'] = 1;
                                $tempItemData['CompanyId'] = $CompanyID;
                                $tempItemData['Status'] = 1;
                                $tempItemData['IsVendor'] = 1;
                                $tempItemData['LeadSource'] = 'Gateway import';
                                $tempItemData['CompanyGatewayID'] = $CompanyGatewayID;
                                $tempItemData['ProcessID'] = $ProcessID;
                                $tempItemData['created_at'] = date('Y-m-d H:i:s.000');
                                $tempItemData['created_by'] = 'Imported';
                                $batch_insert_array[] = $tempItemData;
                            }
                        }
                        if (!empty($batch_insert_array)) {
                            try{
                                if(DB::table('tblTempAccount')->insert($batch_insert_array)){
                                    $response['result'] = 'OK';
                                    $response['Gateway'] = 'ClarityPBX';
                                }
                            }catch(Exception $err){
                                $response['faultString'] =  $err->getMessage();
                                $response['faultCode'] =  $err->getCode();
                                Log::error("Class Name:".__CLASS__.",Method: ". __METHOD__.", Fault. Code: " . $err->getCode(). ", Reason: " . $err->getMessage());
                                //throw new Exception($err->getMessage());
                            }
                        }else{
                            $response['result'] = 'OK';
                            $response['Gateway'] = 'ClarityPBX';
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