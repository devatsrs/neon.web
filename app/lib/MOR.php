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
                            $count = DB::table('tblAccount')->where(["AccountName" => $temp_row->username, "AccountType" => 1,"CompanyId"=>$CompanyID])->count();
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

                if(!empty($addparams['StartDate'])){
                    $previous_bal_query =  'select (select COALESCE(SUM(amount),0) from payments  where user_id = '.$user_id.' and date_added<"'.$addparams['StartDate'].'") - (select COALESCE(SUM(user_price),0) from calls  where user_id = '.$user_id.' and calldate<"'.$addparams['StartDate'].'") as previous_bal';
                    $previous_bal_result = DB::connection('pbxmysql')->select($previous_bal_query);
                }
                $payments = DB::connection('pbxmysql')->table('payments')->where('user_id',$user_id);
                if (!empty($addparams['StartDate'])) {
                    $payments->whereRaw('DATE(date_added) >="' .$addparams['StartDate'].'"');
                }
                if (!empty($addparams['EndDate'])) {
                    $payments->whereRaw('DATE(date_added) <="'. $addparams['EndDate'].'"');
                }
                $payments->select(DB::Raw("DATE(date_added) as date"));


                $calls = DB::connection('pbxmysql')->table('calls')->where('user_id',$user_id);
                if (!empty($addparams['StartDate'])) {
                    $calls->where('date', '>=', $addparams['StartDate']);
                }
                if (!empty($addparams['EndDate'])) {
                    $calls->where('date', '<=', $addparams['EndDate']);
                }

                $calls->union($payments);
                $calls->select(DB::Raw("date"));

                $response['datatable'] = $calls;
                if(!empty($previous_bal_result) && count($previous_bal_result)){
                    $response['previous_bal'] = $previous_bal_result[0]->previous_bal;
                }else{
                    $response['previous_bal'] = 0;
                }

                /** get only day wise total */
                $payments_total = DB::connection('pbxmysql')->table('payments')->where('user_id',$user_id);
                if (!empty($addparams['StartDate'])) {
                    $payments_total->whereRaw('DATE(date_added) >="' .$addparams['StartDate'].'"');
                }
                if (!empty($addparams['EndDate'])) {
                    $payments_total->whereRaw('DATE(date_added) <="'. $addparams['EndDate'].'"');
                }
                $payments_total_result = $payments_total->groupby('date')->orderby('date','desc')->select(DB::Raw("DATE(date_added) as date,sum(amount) as payment"))->get();
                foreach($payments_total_result as $payments_total_result_row){
                    $response['payment'][$payments_total_result_row->date] = number_format($payments_total_result_row->payment,get_round_decimal_places(),'.','');
                }

                $calls_total = DB::connection('pbxmysql')->table('calls')->where('user_id',$user_id);
                if (!empty($addparams['StartDate'])) {
                    $calls_total->where('date', '>=', $addparams['StartDate']);
                }
                if (!empty($addparams['EndDate'])) {
                    $calls_total->where('date', '<=', $addparams['EndDate']);
                }
                $calls_total_result = $calls_total->groupby('date')->orderby('date','desc')->select(DB::Raw("date,sum(user_price) as payment"))->get();
                foreach($calls_total_result as $calls_total_result_row){
                    $response['calls'][$calls_total_result_row->date] = number_format($calls_total_result_row->payment,get_round_decimal_places(),'.','');
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

    public static function getMovementReportTotal($addparams=array()){
        $response = array();
        $response['TotalPayment'] = $response['TotalCharge'] = $response['Total'] = $response['Balance'] = 0;
        if(count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['username']) && isset(self::$config['password'])){
            try{
                $query = "select * from users where username='".$addparams['username']."' limit 1 "; // and userfield like '%outbound%'  removed for inbound calls
                //$response = DB::connection('pbxmysql')->select($query);
                $results = DB::connection('pbxmysql')->select($query);
                if(count($results)>0){
                    if(!empty($addparams['StartDate'])){
                        $previous_bal_query =  'select (select COALESCE(SUM(amount),0) from payments  where user_id = '.$results[0]->id.' and date_added<"'.$addparams['StartDate'].'") - (select COALESCE(SUM(user_price),0) from calls  where user_id = '.$results[0]->id.' and calldate<"'.$addparams['StartDate'].'") as previous_bal';
                        $previous_bal_result = DB::connection('pbxmysql')->select($previous_bal_query);
                    }
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
                        $calls->where('date', '>=', $addparams['StartDate']);
                    }
                    if (!empty($addparams['EndDate'])) {
                        $calls->where('date', '<=', $addparams['EndDate']);
                    }
                    $response['TotalCharge'] = number_format($calls->sum('user_price'),get_round_decimal_places(),'.','');



                    if(!empty($previous_bal_result) && count($previous_bal_result)){
                        $response['previous_bal'] = $previous_bal_result[0]->previous_bal;
                    }else{
                        $response['previous_bal'] = 0;
                    }

                    $response['Total'] = number_format($response['TotalPayment'] - $response['TotalCharge'],get_round_decimal_places(),'.','');
                    $response['Balance'] = number_format($response['previous_bal'] + $response['TotalPayment'] - $response['TotalCharge'],get_round_decimal_places(),'.','');

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