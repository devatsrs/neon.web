<?php
class MOR{
    private static $config = array();
    private static $dbname1 = 'mor';

   public function __construct($CompanyGatewayID){
       $setting = GatewayAPI::getSetting($CompanyGatewayID,'MOR');
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
        $country = Country::getCountryDropdownList();
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "select users.*,addresses.*,currencies.name as currencyname from mor.users
left JOIN mor.addresses on addresses.id = users.address_id
left JOIN mor.currencies on currencies.id = users.currency_id
"; // and userfield like '%outbound%'  removed for inbound calls
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
                            $count = DB::table('tblAccount')->where(["AccountName" => $temp_row->username, "AccountType" => 1])->count();
                            if($count==0){
                                $tempItemData['AccountName'] = $temp_row->username;
                                $tempItemData['Number'] = $temp_row->username;
                                $tempItemData['FirstName'] = $temp_row->first_name;
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
                                $tempItemData['Currency'] = isset($currency[$temp_row->currencyname]) && $temp_row->currencyname != ''?$currency[$temp_row->currencyname]:null;

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
                //$response = DB::connection('pbxmysql')->select($query);
                $results = DB::connection('pbxmysql')->select($query);
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

    public static function getMovementReport($addparams=array()){
        $response = array();
        $response['previous_bal'] = 0;
        $user_id = 0;
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "select * from users where username='".$addparams['username']."' limit 1 "; // and userfield like '%outbound%'  removed for inbound calls
                //$response = DB::connection('pbxmysql')->select($query);
                $results = DB::connection('pbxmysql')->select($query);
                if(count($results)>0){
                    $user_id = $results[0]->id;
                }

                $extra = $pextra = ' 1=1 ';
                if (!empty($addparams['StartDate'])) {
                    $extra .= 'AND DATE(calldate) >="'. $addparams['StartDate'].'"';
                    $pextra .= 'AND DATE(date_added) >="'. $addparams['StartDate'].'"';
                }
                if (!empty($addparams['EndDate'])) {
                    $extra .= 'AND DATE(calldate) <="'. $addparams['EndDate'].'"';
                    $pextra .= 'AND DATE(date_added) <="'. $addparams['EndDate'].'"';
                }

                $query = "select tbl.date,(select COALESCE(SUM(amount),0) from payments  where user_id = $user_id and DATE(date_added) = tbl.date) as Payments,(select COALESCE(SUM(user_price),0) from calls  where user_id = $user_id and DATE(calldate) = tbl.date) as Consumption,0 as Total,(select (select COALESCE(SUM(amount),0) from payments  where user_id = $user_id and DATE(date_added) <= tbl.date) - (select COALESCE(SUM(user_price),0) from calls  where user_id = $user_id and DATE(calldate) <= tbl.date)) as Balance from ( (select DATE(calldate) as date from `calls` where `user_id` = $user_id and $extra)  union  (select DATE(date_added) as date from `payments` where `user_id` = $user_id and $pextra) order by `date` desc ) tbl";
                $calls = DB::connection('pbxmysql')->table(DB::raw("($query) as tbl2"))->select();

                $response['datatable'] = $calls;

            }catch(Exception $e){
                $response['faultString'] =  $e->getMessage();
                $response['faultCode'] =  $e->getCode();
                Log::error("Class Name:".__CLASS__.",Method: ". __METHOD__.", Fault. Code: " . $e->getCode(). ", Reason: " . $e->getMessage());
                //throw new Exception($e->getMessage());
            }
        }
        return $response;

    }

    public static function getMovementReportTotal($addparams=array()){
        $response = array();
        $response['TotalPayment'] = $response['TotalCharge'] = $response['Total'] = $response['Balance'] = 0;
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "select * from users where username='".$addparams['username']."' limit 1 "; // and userfield like '%outbound%'  removed for inbound calls
                //$response = DB::connection('pbxmysql')->select($query);
                $results = DB::connection('pbxmysql')->select($query);
                if(count($results)>0){
                    if (!empty($addparams['EndDate'])) {
                        $extra = 'AND DATE(calldate) <="'. $addparams['EndDate'].'"';
                        $pextra = 'AND DATE(date_added) <="'. $addparams['EndDate'].'"';
                    }else{
                        $extra = 'AND DATE(calldate) <="'. date('Y-m-d').'"';
                        $pextra = 'AND DATE(date_added) <="'. date('Y-m-d').'"';
                    }

                    $bal_query = "select ((select COALESCE(SUM(amount),0) from payments  where user_id = ".$results[0]->id." $pextra) - (select COALESCE(SUM(user_price),0) from calls  where user_id = ".$results[0]->id." $extra)) as balance";
                    $bal_result = DB::connection('pbxmysql')->select($bal_query);

                    $payments = DB::connection('pbxmysql')->table('payments')->where('user_id',$results[0]->id);
                    if (!empty($addparams['StartDate'])) {
                        $payments->whereRaw('DATE(date_added) >="' .$addparams['StartDate'].'"');
                    }
                    if (!empty($addparams['EndDate'])) {
                        $payments->whereRaw('DATE(date_added) <="'. $addparams['EndDate'].'"');
                    }
                    $response['TotalPayment'] = number_format($payments->sum('amount'),get_round_decimal_places(),'.','');


                    $calls = DB::connection('pbxmysql')->table('calls')->where('user_id',$results[0]->id);
                    if (!empty($addparams['StartDate'])) {
                        $calls->whereRaw('DATE(calldate) >="'. $addparams['StartDate'].'"');
                    }
                    if (!empty($addparams['EndDate'])) {
                        $calls->whereRaw('DATE(calldate) <="'. $addparams['EndDate'].'"');
                    }
                    $response['TotalCharge'] = number_format($calls->sum('user_price'),get_round_decimal_places(),'.','');



                    if(!empty($bal_result) && count($bal_result)){
                        $response['Balance'] = number_format($bal_result[0]->balance,get_round_decimal_places(),'.','');
                    }else{
                        $response['Balance'] = 0;
                    }

                    $response['Total'] = number_format($response['TotalPayment'] - $response['TotalCharge'],get_round_decimal_places(),'.','');

                }
            }catch(Exception $e){
                $response['faultString'] =  $e->getMessage();
                $response['faultCode'] =  $e->getCode();
                Log::error($e);
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
                $mor_rates = DB::connection('pbxmysql')->table('users')
                    ->join('tariffs','tariff_id','=','tariffs.id')
                    ->join('rates','rates.tariff_id','=','tariffs.id')
                    ->join('destinations','destination_id','=','destinations.id')
                    ->join('ratedetails','rates.id','=','rate_id')
                    ->select('destinations.name','destinations.prefix','rate','connection_fee','increment_s','start_time','end_time','daytype')
                    ->where("username", $addparams['username']);
                if(trim($addparams['Prefix']) != '') {
                    $mor_rates->where('destinations.prefix', 'like','%' .trim($addparams['Prefix']). '%');
                }
                if(trim($addparams['Description']) != '') {
                    $mor_rates->where('destinations.name', 'like','%' .trim($addparams['Description']). '%');
                }
                $mor_rates = $mor_rates->get();
                $mor_rates = json_decode(json_encode($mor_rates), true);
                $data_count = 0;
                $insertLimit= 1000;
                $InsertData = array();
                foreach($mor_rates as $mor_rate){
                    $GatewayCustomerRate = array();
                    $GatewayCustomerRate['CustomerID'] = $addparams['CustomerID'];
                    $GatewayCustomerRate['Description'] = $mor_rate['name'];
                    $GatewayCustomerRate['Code'] = $mor_rate['prefix'];
                    $GatewayCustomerRate['Rate'] = $mor_rate['rate'];
                    $GatewayCustomerRate['EffectiveDate'] = $mor_rate['connection_fee'];
                    $GatewayCustomerRate['Interval1'] = $mor_rate['increment_s'];
                    $GatewayCustomerRate['IntervalN'] = $mor_rate['increment_s'];
                    $GatewayCustomerRate['ConnectionFee'] = $mor_rate['connection_fee'];
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