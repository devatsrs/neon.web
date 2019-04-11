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

    public static function getAccessTypeDD($CompanyID){
        return ServiceTemplate::where("CompanyID",$CompanyID)->where("accessType",'!=','')->orderBy('accessType')->lists("accessType", "accessType");
    }
    public static function getPrefixDD($CompanyID){
        return ServiceTemplate::where("CompanyID",$CompanyID)->where("prefixName",'!=','')->orderBy('prefixName')->lists("prefixName", "prefixName");
    }
    public static function getCityDD($CompanyID){
        return ServiceTemplate::where("CompanyID",$CompanyID)->where("City",'!=','')->orderBy('City')->lists("City", "City");
    }
    public static function getTariffDD($CompanyID){
        return ServiceTemplate::where("CompanyID",$CompanyID)->where("Tariff",'!=','')->orderBy('Tariff')->lists("Tariff", "Tariff");
    }
    public static function getCountryPrefixDD(){
        return $country = Country::select('Country AS country','Prefix')->orderBy('country')->lists("country", "Prefix");
    }
}