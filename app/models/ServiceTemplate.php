<?php

class ServiceTemplate extends \Eloquent
{
    protected $guarded = array("ServiceTemplateID");

    protected $table = 'tblServiceTemplate';

    protected $primaryKey = "ServiceTemplateId";

    public static $rules = array(
        'ServiceId' =>  'required',
        'Name' => 'required',
       // 'selectedSubscription' => 'required',
       // 'selectedcategotyTariff' => 'required',
    );

    public static $updateRules = array(
        'ServiceId' =>  'required',
        'Name' => 'required',
        
       // 'selectedSubscription' => 'required',
       // 'selectedcategotyTariff' => 'required',
    );

    public static $ServiceType = array(""=>"Select", "voice"=>"Voice");


    public static function findServiceTemplateByDynamicField($TemplateRef){

        $AccountReferenceArr=json_decode(json_encode($TemplateRef),true);

        Log::info('findServiceTemplateByDynamicField .' . count($AccountReferenceArr) . ' ' . print_r($TemplateRef,true));

        $Query = "select distinct ParentID from tblDynamicFieldsValue where ";
        for ($i =0; $i <count($AccountReferenceArr);$i++) {
            $AccountReference = $AccountReferenceArr[$i];
            $DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),'Type'=>'serviceTemplate','Status'=>1,'FieldName'=>$AccountReference['Name']])->pluck('DynamicFieldsID');
            if(empty($DynamicFieldsID)){
                return '';
            }
        }

        for ($i =0; $i <count($AccountReferenceArr);$i++) {
            $AccountReference = $AccountReferenceArr[$i];
            $DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),'Type'=>'serviceTemplate','Status'=>1,'FieldName'=>$AccountReference['Name']])->pluck('DynamicFieldsID');
                $Query = $Query .'(DynamicFieldsID = ' . $DynamicFieldsID . " and FieldValue='" . $AccountReference["Value"] . "')";
                if ($i != count($AccountReferenceArr) - 1) {
                    $Query = $Query . " OR ";
                }
            }



        Log::info('Service Template $DynamicFieldsID Query.' . $Query);
        $DynamicFieldsValues = DB::select($Query);


        Log::info('Service Template $AccountReference["Value"].' . count($DynamicFieldsValues));
        if (count($DynamicFieldsValues) > 1 || count($DynamicFieldsValues) == 0) {
            return '';
        }else {
            foreach ($DynamicFieldsValues as $DynamicFieldsValue) {

            }
            return $DynamicFieldsValue->ParentID;
        }


        return '';
    }

    public static function getAccessTypeDD($CompanyID,$includePrefix=0){
        if($includePrefix == 1)
            return ServiceTemplate::select('accessType',DB::raw('CONCAT("DBDATA-",accessType) AS accessTypeValue'))->where("CompanyID",$CompanyID)->where("accessType",'!=','')->orderBy('accessType')->lists("accessType", "accessTypeValue");
        else
            return ServiceTemplate::where("CompanyID",$CompanyID)->where("accessType",'!=','')->orderBy('accessType')->lists("accessType", "accessType");
    }
    public static function verifyAccessTypeDD($CompanyID,$accessType){
        return ServiceTemplate::where("CompanyID",$CompanyID)->where("accessType",'=',$accessType)->count();
    }
    public static function verifyPrefixDD($CompanyID,$prefixName){
        return ServiceTemplate::where("CompanyID",$CompanyID)->where("prefixName",'=',$prefixName)->count();
    }
    public static function verifyCityDD($CompanyID,$City){
        return ServiceTemplate::where("CompanyID",$CompanyID)->where("City",'=',$City)->count();
    }
    public static function verifyTariffDD($CompanyID,$Tariff){
        return ServiceTemplate::where("CompanyID",$CompanyID)->where("Tariff",'=',$Tariff)->count();
    }

    public static function getPrefixDD($CompanyID,$includePrefix=0){
        if($includePrefix == 1)
            return ServiceTemplate::select('prefixName',DB::raw('CONCAT("DBDATA-",prefixName) AS prefixNameValue'))->where("CompanyID",$CompanyID)->where("prefixName",'!=','')->orderBy('prefixName')->lists("prefixName", "prefixNameValue");
        else
            return ServiceTemplate::where("CompanyID",$CompanyID)->where("prefixName",'!=','')->orderBy('prefixName')->lists("prefixName", "prefixName");
    }
    public static function getCityDD($CompanyID,$includePrefix=0){
        if($includePrefix == 1)
            return ServiceTemplate::select('City',DB::raw('CONCAT("DBDATA-",City) AS CityValue'))->where("CompanyID",$CompanyID)->where("City",'!=','')->orderBy('City')->lists("City", "CityValue");
        else
            return ServiceTemplate::where("CompanyID",$CompanyID)->where("City",'!=','')->orderBy('City')->lists("City", "City");
    }
    public static function getTariffDD($CompanyID,$includePrefix=0){
        if($includePrefix == 1)
            return ServiceTemplate::select('Tariff',DB::raw('CONCAT("DBDATA-",Tariff) AS TariffValue'))->where("CompanyID",$CompanyID)->where("Tariff",'!=','')->orderBy('Tariff')->lists("Tariff", "TariffValue");
        else
            return ServiceTemplate::where("CompanyID",$CompanyID)->where("Tariff",'!=','')->orderBy('Tariff')->lists("Tariff", "Tariff");
    }
    public static function getCountryPrefixDD($includePrefix=0){
        if($includePrefix == 1)
            return $country = Country::select('Country AS country',DB::raw('CONCAT("DBDATA-",Prefix) AS Prefix'))->orderBy('country')->lists("country", "Prefix");
        else
            return $country = Country::select('Country AS country','Prefix')->orderBy('country')->lists("country", "Prefix");
    }
    public static function getCountryDD($CompanyID){
        $country = ServiceTemplate::Join('tblCountry', function($join) {
                $join->on('tblServiceTemplate.country','=','tblCountry.country');
                })->select('tblServiceTemplate.country AS country','tblCountry.countryID As CountryID')->where("tblServiceTemplate.CompanyID",$CompanyID)
                ->orderBy('tblServiceTemplate.country')->lists("country", "country");
        return $country;        
    }
}