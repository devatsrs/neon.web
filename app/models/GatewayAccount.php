<?php

class GatewayAccount extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrv2';
    protected $table = 'tblGatewayAccount';
    public $timestamps = false; // no created_at and updated_at

    public static function getAccountIDList($gatewayid=0){
        $row = GatewayAccount::where(array('CompanyID'=>User::get_companyID()))->select(array('AccountName', 'GatewayAccountID'))->orderBy('AccountName')->lists('AccountName', 'GatewayAccountID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }
    /* not in use */
    public static function getActiveAccountIDList($CompanyID,$GatewayIDs=array()){
        $accountdata = array();
        $userID = User::get_userID();
        //$isAdmin = (User::is_admin() || User::is('RateManager'))?1:0;
        $isAdmin 					= 	(User::is_admin())?1:0;
        $gatewayids = "'".implode(',',array_filter($GatewayIDs,'intval'))."'";
        $account = DB::connection('sqlsrv2')->select("call prc_getActiveGatewayAccount ($CompanyID,$gatewayids,$userID,$isAdmin,'')");
        foreach($account as $dr){
            $accountdata[] = $dr->GatewayAccountID;
        }
        $row = array();
        if(count($accountdata)) {
            $row = GatewayAccount::where(array('CompanyID' => User::get_companyID()))->wherein('GatewayAccountID', $accountdata)->select(array('AccountName', 'GatewayAccountID'))->orderBy('AccountName')->lists('AccountName', 'GatewayAccountID');
            if (!empty($row)) {
                $row = array("" => "Select") + $row;
            }
        }
        return $row;
    }
    public static function getAccountID($GatewayAccountID){
        return GatewayAccount::where(array('CompanyID' => User::get_companyID()))->where('GatewayAccountID',$GatewayAccountID)->pluck('AccountID');
    }

    public static function getAccountNameByGatway($GatewayID=0)
    {
        $row = array();
        $CompanyID=User::get_companyID();
        $accounts = DB::connection('sqlsrv2')->select("call  prc_getAccountNameByGatway ($CompanyID,$GatewayID)");
        if(count($accounts)) {
            foreach($accounts as $account){
                    $row[$account->AccountID]= $account->AccountName;
            }
        }
        if (!empty($row)) {
            $row = array("" => "Select") + $row;
        }
        return $row;
    }

    public static function getAccountIPList($CompanyID){
        return GatewayAccount::where(array('CompanyID' => $CompanyID))->lists('AccountIP', 'GatewayAccountPKID');
    }

    public static function getAccountCLIList($CompanyID){
        return GatewayAccount::where(array('CompanyID' => $CompanyID))->lists('AccountCLI', 'GatewayAccountPKID');
    }
}