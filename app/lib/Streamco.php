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
        // same code in service/Streamco.php@importStreamcoAccounts()
        // if you change anything here than you also have to change there
        $response = array();
        if(count(self::$config) && isset(self::$config['host']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])){
            try{
                $query = "SELECT DISTINCT
                              c.name,c.address,c.email,c.invoice_email,o.company_id,cu.name AS currency,IF(o.company_id,1,0) AS IsCustomer,IF(t.company_id,1,0) AS IsVendor
                          FROM
                              companies AS c
                          LEFT JOIN
                              originators AS o ON o.company_id = c.id
                          LEFT JOIN
                              terminators AS t ON t.company_id = c.id
                          LEFT JOIN
                              currencies AS cu ON c.balance_currency_id=cu.id";
                $results = DB::connection('pbxmysql')->select($query);
                if(count($results)>0){
                    $tempItemData = array();
                    $batch_insert_array = array();
                    if(count($addparams)>0){
                        $currency = Currency::getCurrencyDropdownIDList();
                        $countries = Country::lists('ISO3', 'Country');
                        $CompanyGatewayID = $addparams['CompanyGatewayID'];
                        $CompanyID = $addparams['CompanyID'];
                        $ProcessID = $addparams['ProcessID'];
                        foreach ($results as $temp_row) {
                            $count = DB::table('tblAccount')->where(array("AccountName" => $temp_row->name, "AccountType" => 1))->count();
                            if($count==0){
                                if($temp_row->address != '' && $temp_row->address != null) {
                                    $DOM = new DOMDocument;
                                    $DOM->loadHTML($temp_row->address);
                                    $items = $DOM->getElementsByTagName('p');
                                    for ($i = 0; $i < $items->length; $i++) {
                                        if($i==0) {
                                            $tempItemData['Address1'] = $items->item($i)->nodeValue;
                                        }
                                        if($i==1) {
                                            $tempItemData['Address2'] = $items->item($i)->nodeValue;
                                        }
                                        if($i==2) {
                                            $tempItemData['Address3'] = $items->item($i)->nodeValue;
                                        }
                                        if($i==3) {
                                            $tempItemData['City'] = $items->item($i)->nodeValue;
                                        }
                                        /*if($i==4) {
                                            $tempItemData['State'] = $items->item($i)->nodeValue;
                                        } else {
                                            $tempItemData['Address'.$i+1] = "";
                                        }*/
                                        if($i==4) {
                                            $tempItemData['PostCode'] = $items->item($i)->nodeValue;
                                        }
                                        if(array_search(strtoupper(trim($items->item($i)->nodeValue)),$countries)) {
                                            $tempItemData['Country'] = array_search(strtoupper(trim($items->item($i)->nodeValue)),$countries);
                                        }
                                    }

                                    if(!isset($tempItemData['Address1'])) {
                                        $tempItemData['Address1'] = "";
                                    }
                                    if(!isset($tempItemData['Address2'])) {
                                        $tempItemData['Address2'] = "";
                                    }
                                    if(!isset($tempItemData['Address3'])) {
                                        $tempItemData['Address3'] = "";
                                    }
                                    if(!isset($tempItemData['City'])) {
                                        $tempItemData['City'] = "";
                                    }
                                    if(!isset($tempItemData['PostCode'])) {
                                        $tempItemData['PostCode'] = "";
                                    }
                                    if(!isset($tempItemData['Country'])) {
                                        $tempItemData['Country'] = "";
                                    }
                                } else {
                                    $tempItemData['Address1'] = "";
                                    $tempItemData['Address2'] = "";
                                    $tempItemData['Address3'] = "";
                                    $tempItemData['City'] = "";
                                    $tempItemData['PostCode'] = "";
                                    $tempItemData['Country'] = "";
                                }
                                $tempItemData['AccountName'] = $temp_row->name;
                                $tempItemData['FirstName'] = "";
                                $tempItemData['Phone'] = "";
                                $tempItemData['BillingEmail'] = $temp_row->invoice_email;
                                $tempItemData['Email'] = $temp_row->email;
                                $tempItemData['Currency'] = array_search($temp_row->currency,$currency) ? array_search($temp_row->currency,$currency):null;
                                $tempItemData['AccountType'] = 1;
                                $tempItemData['CompanyId'] = $CompanyID;
                                $tempItemData['Status'] = 1;
                                /*$tempItemData['IsCustomer'] = $temp_row->IsCustomer;
                                $tempItemData['IsVendor'] = $temp_row->IsVendor;*/
                                $tempItemData['IsCustomer'] = 1;
                                $tempItemData['IsVendor'] = 1;
                                $tempItemData['LeadSource'] = 'Gateway import';
                                $tempItemData['CompanyGatewayID'] = $CompanyGatewayID;
                                $tempItemData['ProcessID'] = $ProcessID;
                                $tempItemData['created_at'] = $addparams['ImportDate'];
                                $tempItemData['created_by'] = 'Imported';
                                $batch_insert_array[] = $tempItemData;
                            }
                        }
                        if (!empty($batch_insert_array)) {
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