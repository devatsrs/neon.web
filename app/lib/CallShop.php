<?php
class CallShop{
    private static $config = array();
    private static $dbname1 = 'svbpanel';

   public function __construct($CompanyGatewayID){
       $setting = GatewayAPI::getSetting($CompanyGatewayID,'CallShop');
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
        $currency = Currency::getCurrencyDropdownIDList();
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "select * from svbpanel.usuarios"; // and userfield like '%outbound%'  removed for inbound calls
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
                            $count = DB::table('tblAccount')->where(["AccountName" => $temp_row->usuario, "AccountType" => 1])->count();
                            if($count==0){
                                $tempItemData['AccountName'] = $temp_row->usuario;
                                $tempItemData['Number'] = $temp_row->usuario;
                                $tempItemData['FirstName'] = $temp_row->nombre;
                                $tempItemData['Address1'] = $temp_row->direccion;
                                $tempItemData['Phone'] = $temp_row->telefono;
                                $tempItemData['BillingEmail'] = $temp_row->email;
                                $tempItemData['Email'] = $temp_row->email;
                                $tempItemData['Currency'] = isset($currency[$temp_row->moneda]) && $temp_row->moneda != ''?$currency[$temp_row->moneda]:null;
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
                $query = "select * from svbpanel.usuarios where usuario='".$addparams['username']."' limit 1 "; // and userfield like '%outbound%'  removed for inbound calls
                //$response = DB::connection('pbxmysql')->select($query);
                $results = DB::connection('pbxmysql')->select($query);
                if(count($results)>0){
                    foreach ($results as $temp_row) {
                        $response['result'] = 'OK';
                        $response['balance'] = $temp_row->saldo;
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


    public static function getRates($addparams=array()){
        $response = array();
        $response['TotalPayment'] = $response['TotalCharge'] = $response['Total'] = $response['Balance'] = 0;
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                DB::purge('pbxmysql');
                $mor_rates = DB::connection('pbxmysql')->table('usuarios')
                    ->join('tarifas','tarifas_id','=','tarifas.id')
                    ->join('importes','importes.tarifas_id','=','usuarios.tarifas_id')
                    ->select('destino','prefijo','importe')
                    ->where("usuario", $addparams['username']);
                if(trim($addparams['Prefix']) != '') {
                    $mor_rates->where('prefijo', 'like',str_replace('*','%',trim($addparams['Prefix'])));
                }
                if(trim($addparams['Description']) != '') {
                    $mor_rates->where('destino', 'like',str_replace('*','%',trim($addparams['Description'])));
                }
                $mor_rates = $mor_rates->get();
                $mor_rates = json_decode(json_encode($mor_rates), true);
                $data_count = 0;
                $insertLimit= 1000;
                $InsertData = array();
                foreach($mor_rates as $mor_rate){
                    $GatewayCustomerRate = array();
                    $GatewayCustomerRate['CustomerID'] = $addparams['CustomerID'];
                    $GatewayCustomerRate['Description'] = $mor_rate['destino'];
                    $GatewayCustomerRate['Code'] = $mor_rate['prefijo'];
                    $GatewayCustomerRate['Rate'] = $mor_rate['importe'];
                    $GatewayCustomerRate['Interval1'] = 1;
                    $GatewayCustomerRate['IntervalN'] = 1;
                    $GatewayCustomerRate['ConnectionFee'] = 0;
                    $data_count++;
                    $InsertData[] = $GatewayCustomerRate;
                    if($data_count > $insertLimit &&  !empty($InsertData)){
                        DB::table('tblGatewayCustomerRate')->insert($InsertData);
                        $InsertData = array();
                        $data_count = 0;
                    }
                }
				if (!empty($InsertData)) {
					DB::table('tblGatewayCustomerRate')->insert($InsertData);
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