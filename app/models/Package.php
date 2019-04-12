<?php

class Package extends \Eloquent
{
    protected $guarded = array("PackageId");

    protected $table = 'tblPackage';

    protected $primaryKey = "PackageId";

    public static $rules = array(
        'Name' =>      'required|unique:tblPackage',
//        'CurrencyId' =>  'required',
        'RateTableId' => 'required',
    );


    public static function getDropdownIDList(){
        $DropdownIDList = Package::lists('Name', 'PackageId');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

    public static function getDropdownIDListByCompany($CompanyID){
        $DropdownIDList = Package::where('CompanyId',$CompanyID)->lists('Name', 'PackageId');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

    public static function getAllServices(){
        $Packages = Package::get();
        return $Packages;
    }

    public static function getServiceNameByID($PackageID){
        return Package::where('PackageId',$PackageID)->pluck('Name');
    }

    public static function findPackageByDynamicField($TemplateRef){

        $AccountReferenceArr=json_decode(json_encode($TemplateRef),true);

        Log::info('findPackageByDynamicField .' . count($AccountReferenceArr) . ' ' . print_r($TemplateRef,true));

        $Query = "select distinct ParentID from tblDynamicFieldsValue where ";
        for ($i =0; $i <count($AccountReferenceArr);$i++) {
            $AccountReference = $AccountReferenceArr[$i];
            $DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),'Type'=>'package','Status'=>1,'FieldName'=>$AccountReference['Name']])->pluck('DynamicFieldsID');
            if(empty($DynamicFieldsID)){
                return '';
            }
        }

        for ($i =0; $i <count($AccountReferenceArr);$i++) {
            $AccountReference = $AccountReferenceArr[$i];
            $DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),'Type'=>'package','Status'=>1,'FieldName'=>$AccountReference['Name']])->pluck('DynamicFieldsID');
            $Query = $Query .'(DynamicFieldsID = ' . $DynamicFieldsID . " and FieldValue='" . $AccountReference["Value"] . "')";
            if ($i != count($AccountReferenceArr) - 1) {
                $Query = $Query . " OR ";
            }
        }



        Log::info('Package Template findPackageByDynamicField Query.' . $Query);
        $DynamicFieldsValues = DB::select($Query);


        Log::info('Package Template findPackageByDynamicField count Result.' . count($DynamicFieldsValues));
        if (count($DynamicFieldsValues) > 1 || count($DynamicFieldsValues) == 0) {
            return '';
        }else {
            foreach ($DynamicFieldsValues as $DynamicFieldsValue) {

            }
            return $DynamicFieldsValue->ParentID;
        }


        return '';
    }

}