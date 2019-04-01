<?php

class SippySQL{

    private static $config = array();
    private static $cli;
    private static $timeout = 0; /* 60 seconds timeout */

    public function __construct($CompanyGatewayID){
        $setting = GatewayAPI::getSetting($CompanyGatewayID, 'SippySQL');
        foreach ((array)$setting as $configkey => $configval) {
            if ($configkey == 'dbpassword' && $configval != "") {
                self::$config[$configkey] = !empty($configval) ? Crypt::decrypt($configval) : '';
            } else {
                self::$config[$configkey] = $configval;
            }
        }
        if (count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])) {
            extract(self::$config);
            Config::set('database.connections.pgsql.host', $dbserver);
            Config::set('database.connections.pgsql.database', $dbname);
            Config::set('database.connections.pgsql.username', $dbusername);
            Config::set('database.connections.pgsql.password', $dbpassword);
        }
    }

    public static function testConnection(){
        $response = array();
        if (count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])) {

            try {
                if (DB::connection('pgsql')->getDatabaseName()) {
                    $response['result'] = 'OK';
                }
            } catch (Exception $e) {
                $response['faultString'] = $e->getMessage();
                $response['faultCode'] = $e->getCode();
            }
        } else {
            $response['faultCode'] = "Error";
            $response['faultString'] = "No Database Settings defined.";
        }
        return $response;
    }

    public static function getAccountByIP($addparams = array()){
        $response = array();
        if (count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])) {
            try {
                $qry = "select i_account from authentications where remote_ip in (" . $addparams['remote_ip'] . ") limit 1";
                $response = DB::connection('pgsql')->select($qry);
                Log::info($qry);
            } catch (Exception $e) {
                $response['faultString'] = $e->getMessage();
                $response['faultCode'] = $e->getCode();
                Log::error("Class Name:" . __CLASS__ . ",Method: " . __METHOD__ . ", Fault. Code: " . $e->getCode() . ", Reason: " . $e->getMessage());
                throw new Exception($e->getMessage());
            }
        }
        return $response;
    }

    public static function getVendorByIP($addparams = array()){
        $response = array();
        if (count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])) {
            try {
                $qry = "select i_vendor from connections where destination in (" . $addparams['remote_ip'] . ") limit 1";
                $response = DB::connection('pgsql')->select($qry);
                Log::info($qry);
            } catch (Exception $e) {
                $response['faultString'] = $e->getMessage();
                $response['faultCode'] = $e->getCode();
                Log::error("Class Name:" . __CLASS__ . ",Method: " . __METHOD__ . ", Fault. Code: " . $e->getCode() . ", Reason: " . $e->getMessage());
                throw new Exception($e->getMessage());
            }
        }
        return $response;
    }

    public static function getTariffID($addparams = array()){
        $response = array();
        if (count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])) {
            try {
                $qry = "select i_tariff from billing_plans where i_billing_plan = " . $addparams['i_billing_plan'] . " limit 1";
                $response = DB::connection('pgsql')->select($qry);
                Log::info($qry);
            } catch (Exception $e) {
                $response['faultString'] = $e->getMessage();
                $response['faultCode'] = $e->getCode();
                Log::error("Class Name:" . __CLASS__ . ",Method: " . __METHOD__ . ", Fault. Code: " . $e->getCode() . ", Reason: " . $e->getMessage());
                throw new Exception($e->getMessage());
            }
        }
        return $response;
    }

    public static function getDestinationSetList($addparams = array()){
        $response = array();
        if (count(self::$config) && isset(self::$config['dbserver']) && isset(self::$config['dbusername']) && isset(self::$config['dbpassword'])) {
            try {
                $qry = "select
                            distinct m.i_destination_set,d.name
                        from
                            routing_group_members m
                        join
                            destination_sets d on d.i_destination_set=m.i_destination_set
                        where
                            m.i_connection = " . $addparams['i_connection'];
                $response = DB::connection('pgsql')->select($qry);
                Log::info($qry);
            } catch (Exception $e) {
                $response['faultString'] = $e->getMessage();
                $response['faultCode'] = $e->getCode();
                Log::error("Class Name:" . __CLASS__ . ",Method: " . __METHOD__ . ", Fault. Code: " . $e->getCode() . ", Reason: " . $e->getMessage());
                throw new Exception($e->getMessage());
            }
        }
        return $response;
    }

}