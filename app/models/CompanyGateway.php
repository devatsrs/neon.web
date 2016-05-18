<?php

class CompanyGateway extends \Eloquent {
	protected $fillable = [];

    protected $guarded = array('CompanyGatewayID');

    protected $table = 'tblCompanyGateway';

    protected  $primaryKey = "CompanyGatewayID";



    public static function checkForeignKeyById($id){
        $hasIngatewaycount =  GatewayAccount::where(array('CompanyGatewayID'=>$id))->count();

        if( intval($hasIngatewaycount) > 0 ){
            return true;
        }else{
            return false;
        }
    }
    public static function getCompanyGatewayIdList(){
        $row = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->lists('Title', 'CompanyGatewayID');
        if(!empty($row)){
            $row = array(""=> "Select a Gateway")+$row;
        }
        return $row;

    }
    public static function getCompanyGatewayConfig($CompanyGatewayID){
        return CompanyGateway::where(array('Status'=>1,'CompanyGatewayID'=>$CompanyGatewayID))->pluck('Settings');
    }
    public static function getCompanyGatewayID($gatewayid){
        return CompanyGateway::where(array('GatewayID'=>$gatewayid,'CompanyID'=>User::get_companyID()))->pluck('CompanyGatewayID');
    }
    public static function getGatewayIDList($gatewayid){
        $row = CompanyGateway::where(array('Status'=>1,'GatewayID'=>$gatewayid,'CompanyID'=>User::get_companyID()))->lists('Title', 'CompanyGatewayID');
        if(!empty($row)){
            $row = array(""=> "Select a Gateway")+$row;
        }
        return $row;

    }
    public static function getAccountIDList($CompanyGatewayID){
        $row = array();
        $trunk = CustomerTrunk::join('tblaccount','tblaccount.accountid','=','tblcustomertrunk.accountid')->where(array('tblaccount.CompanyID'=>User::get_companyID(),'tblaccount.Status'=>1,'tblcustomertrunk.Status'=>1))->where('CompanyGatewayIDs','!=','')->whereNull('CompanyGatewayIDs','and','NotNull')->select(array('tblaccount.AccountName','CompanyGatewayIDs','tblaccount.AccountID'))->orderBy('AccountName')->get();
        foreach($trunk as $trunk_row){
            if(in_array($CompanyGatewayID,explode(',',$trunk_row['CompanyGatewayIDs']))){
                $row[$trunk_row['AccountID']]= $trunk_row['AccountName'];
            }
        }
        if(!empty($row)){
            $row = array(""=> "Select a Account")+$row;
        }
        return $row;
    }

    public static function importgatewaylist(){
        $row = array();
        $gatewaylist = array();
        $companygateways = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->get();
        if(count($companygateways)>0){
            foreach($companygateways as $companygateway){
                if(!empty($companygateway['Settings'])){
                    $option = json_decode($companygateway['Settings']);
                    if(!empty($option->AllowAccountImport)){
                        $GatewayName = Gateway::getGatewayName($companygateway['GatewayID']);
                        $row['CompanyGatewayID'] = $companygateway['CompanyGatewayID'];
                        $row['Title'] = $companygateway['Title'];
                        $row['Gateway'] = $GatewayName;
                        $gatewaylist[] = $row;
                    }
                }
            }
        }
        return $gatewaylist;
    }

    public static function getMissingCompanyGatewayIdList(){
        $row = array();
        $companygateways = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->get();
        if(count($companygateways)>0){
            foreach($companygateways as $companygateway){
                if(!empty($companygateway['Settings'])){
                    $option = json_decode($companygateway['Settings']);
                    if(!empty($option->AllowAccountImport)){
                        $row[$companygateway['CompanyGatewayID']] = $companygateway['Title'];
                    }
                }
            }
        }
        print_r($row);exit;
        if(!empty($row)){
            $row = array(""=> "Select a Gateway")+$row;
        }
        return $row;
    }

}