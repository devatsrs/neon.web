<?php

class CustomerTrunk extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('CustomerTrunkID');
    protected $table = 'tblCustomerTrunk';

    protected  $primaryKey = "CustomerTrunkID";

    // Convert All Customer Trunk Records to [TrunkName] as key to display in table
    public static function getCustomerTrunksByTrunkAsKey($id=0){

        $customer_trunks = CustomerTrunk::where(["AccountID"=>$id])->get();
        $records = array();
        foreach ($customer_trunks as $customer_trunk) {
            $records[$customer_trunk->TrunkID] = $customer_trunk;
        }
        return $records;
    }
    public static function isPrefixExists($Prefix='',$CustomerTrunkID = ''){


       $CompanyID = User::get_companyID();
       if(!empty($CustomerTrunkID) && $CustomerTrunkID > 0 ){
        $row = CustomerTrunk::where("CustomerTrunkID", "!=" ,$CustomerTrunkID)->where(["CompanyID"=>$CompanyID, "Prefix"=>$Prefix])->get();
       }else{
        $row = CustomerTrunk::where([ "CompanyID"=>$CompanyID, "Prefix"=>$Prefix])->get(); 
       }
       

       if( count($row) > 0 &&  isset($row[0]->Prefix) && $row[0]->Prefix > 0){
            return true;
       }else{
            return false;
       }     
    }

    public static function getCustomerTrunk($AccountID=0){

        if($AccountID==0)
            return '';

        $CompanyID = User::get_companyID();
        $row = CustomerTrunk::join("tblTrunk","tblTrunk.TrunkID", "=    ","tblCustomerTrunk.TrunkID")
                        ->where(["tblCustomerTrunk.Status"=> 1])->where(["tblCustomerTrunk.CompanyID"=>$CompanyID])->where(["tblCustomerTrunk.AccountID"=>$AccountID])->get();

        return $row;

    }
    public static function getTrunkDropdownIDList($AccountID){
        $CompanyID = User::get_companyID();
        $row = CustomerTrunk::join("tblTrunk","tblTrunk.TrunkID", "=    ","tblCustomerTrunk.TrunkID")
            ->where(["tblCustomerTrunk.Status"=> 1])->where(["tblCustomerTrunk.CompanyID"=>$CompanyID])->where(["tblCustomerTrunk.AccountID"=>$AccountID])->select(array('tblCustomerTrunk.TrunkID','Trunk'))->lists('Trunk', 'TrunkID');
        if(!empty($row)){
            $row = array(""=> "Select a Trunk")+$row;
        }
        return $row;
    }
    public static function getRoutineDropdownIDList($AccountID){
        $CompanyID = User::get_companyID();
        $row = CustomerTrunk::join("tblTrunk","tblTrunk.TrunkID", "=    ","tblCustomerTrunk.TrunkID")
            ->where(["tblCustomerTrunk.Status"=> 1,'tblCustomerTrunk.RoutinePlanStatus'=>1])->where(["tblCustomerTrunk.CompanyID"=>$CompanyID])->where(["tblCustomerTrunk.AccountID"=>$AccountID])->select(array('tblCustomerTrunk.TrunkID','Trunk'))->lists('Trunk', 'TrunkID');
        if(!empty($row)){
            $row = array(""=> "Select a Routine Plan")+$row;
        }
        return $row;
    }

    public static function getTrunkDropdownIDListAll(){
        $CompanyID = User::get_companyID();
        $row = CustomerTrunk::join("tblTrunk","tblTrunk.TrunkID", "=    ","tblCustomerTrunk.TrunkID")
            ->where(["tblCustomerTrunk.Status"=> 1])->where(["tblCustomerTrunk.CompanyID"=>$CompanyID])->select(array('tblCustomerTrunk.TrunkID','Trunk'))->lists('Trunk', 'TrunkID');
        if(!empty($row)){
            $row = array(""=> "Select a Trunk")+$row;
        }
        return $row;
    }

}