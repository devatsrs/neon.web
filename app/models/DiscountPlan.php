<?php

class DiscountPlan extends \Eloquent
{
    protected $guarded = array("DiscountPlanID");

    protected $table = 'tblDiscountPlan';

    protected $primaryKey = "DiscountPlanID";

    const VOLUME_MINUTES = 1;

    public static  $discount_service = array(''=>'Select','1'=>'Volume','3'=>'Fixed');

    public static $Component = array(
        "OneOffCost"=>"One-Off cost",
        "MonthlyCost"=>"Monthly cost",
        "CostPerCall"=>"Cost per call",
        "CostPerMinute"=>"Cost per minute",
        "SurchargePerCall"=>"Surcharge per call",
        "SurchargePerMinute"=>"Surcharge per minute",
        "OutpaymentPerMinute"=>"Out payment per minute",
        "Surcharges"=>"Surcharges",
        "Chargeback"=>"Charge back",
        "CollectionCostAmount"=>"Collection cost - amount",
        "CollectionCostPercentage"=>"Collection cost - percentage",
        "RegistrationCostPerNumber"=>"Registration cost - per number",
        //"OneOffCostPerCountry"=>"One-Off cost - Per country",
        //"MonthlyCostPerCountry"=>"Monthly cost - Per country",

    );

    public static $RateTableDIDRate_Components = array(
        "OneOffCost"                => "One-Off cost",
        "MonthlyCost"               => "Monthly cost",
        "CostPerCall"               => "Cost Per Call",
        "CostPerMinute"             => "Cost Per Minute",
        "SurchargePerCall"          => "Surcharge Per Call",
        "SurchargePerMinute"        => "Surcharge Per Minute",
        "OutpaymentPerCall"         => "Outpayment Per Call",
        "OutpaymentPerMinute"       => "Outpayment Per Minute",
        "Surcharges"                => "Surcharges",
        "Chargeback"                => "Chargeback",
        "CollectionCostAmount"      => "Collection Cost Amount",
        "CollectionCostPercentage"  => "Collection Cost (%)",
        "RegistrationCostPerNumber" => "Registration Cost Per Number",
    );

    public static $RateTablePKGRate_Components = array(
        "OneOffCost"                => "One-Off cost",
        "MonthlyCost"               => "Monthly cost",
        "PackageCostPerMinute"      => "Package Cost Per Minute",
        "RecordingCostPerMinute"    => "Recording Cost Per Minute",
    );

    public static $RateTableRate_Components = array(
        "CostPerCall"               => "Cost Per Call",
        "CostPerMinute"             => "Cost Per Minute",
    );

    public static $Unlimited = array(''=>'Select',"1"=>"YES", "0" => "No");

    public static function exludedCompnents($DestinationGroupSetID) {
        $company_id = 1;
        $company = Company::find($company_id);
        $ExcludedComponent = array();
        $DiscountPlanComponents = [];
        if ($DestinationGroupSetID == 1) {
            $DiscountPlanComponents = DiscountPlan::$RateTableRate_Components;
            $AllComponents = $company->Components;
        } else if ($DestinationGroupSetID == 2) {
            $DiscountPlanComponents = DiscountPlan::$RateTableDIDRate_Components;
            $AllComponents = $company->AccessComponents;
        } else if ($DestinationGroupSetID == 3) {
            $DiscountPlanComponents = DiscountPlan::$RateTablePKGRate_Components;
            $AllComponents = $company->PackageComponents;
        }
        if (!empty($AllComponents)) {
            $ExcludedComponent1 = explode(",",$AllComponents);
            foreach ($DiscountPlanComponents as $index => $data) {
                if (!in_array($index, $ExcludedComponent1)) {
                    $ExcludedComponent[$index] = $data;
                }
            }
            return $ExcludedComponent;
        }
        return $DiscountPlanComponents;
    }

    public static function checkForeignKeyById($id) {

        $hasInAccountDiscountScheme = AccountDiscountPlan::where("DiscountPlanID",$id)->count();
        if( intval($hasInAccountDiscountScheme) > 0){
            return true;
        }else{
            return false;
        }
        /** todo implement this function   */
        return false;
    }
    public static function getDropdownIDList($CompanyID,$CurrencyID){
        $DropdownIDList = DiscountPlan::where(array("CompanyID"=>$CompanyID,'CurrencyID'=>$CurrencyID))->lists('Name', 'DiscountPlanID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }
    public static function getDropdownIDListForType($CompanyID,$CurrencyID,$RateType){
        $DropdownIDListQry = DiscountPlan::Join('tblDestinationGroupSet as dgs','dgs.DestinationGroupSetID','=','tblDiscountPlan.DestinationGroupSetID')->
        where(array("tblDiscountPlan.CompanyID"=>$CompanyID,
            'dgs.RateTypeID'=>$RateType))->
        select(['tblDiscountPlan.Name','tblDiscountPlan.DiscountPlanID']);
        $DropdownIDResult = $DropdownIDListQry->get();
        $DropdownIDList= array();
        foreach ($DropdownIDResult as $item) {
            $DropdownIDList[$item->DiscountPlanID] = $item->Name;
        }
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

    public static function getDropdownIDListForRateType($RateType){
        $DropdownIDListQry = DiscountPlan::Join('tblDestinationGroupSet as dgs','dgs.DestinationGroupSetID','=','tblDiscountPlan.DestinationGroupSetID')->
        where(array('dgs.RateTypeID'=>$RateType))
            ->select(['tblDiscountPlan.Name','tblDiscountPlan.DiscountPlanID']);
        $DropdownIDResult = $DropdownIDListQry->get();
        $DropdownIDList= array();
        foreach ($DropdownIDResult as $item) {
            $DropdownIDList[$item->DiscountPlanID] = $item->Name;
        }
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

    public static function verifyDiscountPlanID($CompanyID,$CurrencyID,$RateType,$DiscountPlanID){
        $DropdownIDListQry = DiscountPlan::Join('tblDestinationGroupSet as dgs','dgs.DestinationGroupSetID','=','tblDiscountPlan.DestinationGroupSetID')->
        where(array("tblDiscountPlan.CompanyID"=>$CompanyID,
            'dgs.RateTypeID'=>$RateType))
            ->where('tblDiscountPlan.DiscountPlanID', '=', $DiscountPlanID);

        return $DropdownIDListQry->count();
    }
    public static function getName($DiscountPlanID){
        return DiscountPlan::where("DiscountPlanID",$DiscountPlanID)->pluck('Name');
    }
    public static function isDiscountPlanApplied($Action,$DestinationGroupSetID,$DiscountPlanID){
        $DiscountPlan  = DB::select('call prc_isDiscountPlanApplied(?,?,?)',array($Action,$DestinationGroupSetID,$DiscountPlanID));
        if(count($DiscountPlan)){
            return 1;
        }
        return 0;
    }

    public static function  getDiscountPlanIDList($data){
        $company_id = User::get_companyID();
        $row = DiscountPlan::where(['CompanyID'=>$company_id])->lists('Name','DiscountPlanID');
        $row = array(""=> "Select") + $row;
        return $row;

    }
    public static function getDropdownIDListByAccount($AccountID){
        $Account = Account::find($AccountID);
        $DropdownIDList = DiscountPlan::where(array("CompanyID"=>$Account->CompanyId,'CurrencyID'=>$Account->CurrencyId))->lists('Name', 'DiscountPlanID');
        //$DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

}