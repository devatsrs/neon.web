<?php

class DestinationGroupSet extends \Eloquent
{
    protected $guarded = array("DestinationGroupSetID");

    protected $table = 'tblDestinationGroupSet';

    protected $primaryKey = "DestinationGroupSetID";

    public $timestamps = false; // no created_at and updated_at

    public static function checkForeignKeyById($id) {

    $hasInDiscountPlan = DiscountPlan::where("DestinationGroupSetID",$id)->count();
        if( intval($hasInDiscountPlan) > 0 ){
            return true;
        }else{
            return false;
        }
    }

    public static function  getRateTypeIDList(){
        $row = RateType::where('Active', 1)->orderBy('Title','asc')->lists('Title','RateTypeID');
        $row = array(""=> "Select") + $row;
        return $row;

    }

    public static function getAccessTypes()
    {
        $row = ServiceTemplate::where("CompanyID",User::get_companyID())->where("accessType",'!=','')->orderBy('accessType')->lists("accessType", "accessType");
        $row = array(""=> "All") + $row;
        return $row;
    }
    public static function getAccessPrefixNames()
    {
        $row = ServiceTemplate::where("CompanyID",User::get_companyID())->orderBy('prefixName')->lists("prefixName", "prefixName");
        $row = array(""=> "All") + $row;
        return $row;
    }

    public static function getCountriesNames()
    {
        $row = ServiceTemplate::where("CompanyID",User::get_companyID())->orderBy('country')->lists("country", "country");
        $row = array(""=> "All") + $row;
        return $row;
    }

    public static function getCityTarrifs()
    {
         $row = ServiceTemplate::where("CompanyID",User::get_companyID())->where("city_tariff",'!=','')->orderBy('city_tariff')->lists("city_tariff", "city_tariff");
         $row = array(""=> "All") + $row;
        return $row;

    }

    public static function getPackages()
    {
        $row = Package::where("CompanyID",User::get_companyID())->orderBy('Name')->lists("Name", "PackageId");
         $row = array(""=> "Select") + $row;
        return $row;
    }
    public static function  getTerminationTypes(){
        $row = Rate::distinct()->where('Type', '!=', '')->orderBy('Type','asc')->lists('Type','Type');
        $row = array(""=> "All") + $row;
        return $row;
    }

    public static function getDropdownIDList(){
        $CompanyId = User::get_companyID();
        $DropdownIDList = DestinationGroupSet::orderBy('Name')->lists('Name', 'DestinationGroupSetID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;


        return $DropdownIDList;
    }
    public static function getName($DestinationGroupSetID){
        return DestinationGroupSet::where("DestinationGroupSetID",$DestinationGroupSetID)->pluck('Name');
    }

    public static function DataGrid($postdata)
    {
        $post_data = $postdata;
        try {
            $CompanyID = User::get_companyID();
            $rules['iDisplayStart'] = 'required|Min:1';
            $rules['iDisplayLength'] = 'required';
            $rules['sSortDir_0'] = 'required';
            $validator = Validator::make($post_data, $rules);
            if ($validator->fails()) {
                return Response::json(['status' => 'fail','message' => $validator->errors()]);
            }
            $post_data['iDisplayStart'] += 1;
            $columns = ['Name', 'CreatedBy', 'created_at'];
            $Name = $CodedeckID = '';
            if (isset($post_data['Name'])) {
                $Name = $post_data['Name'];
            }
            if (isset($post_data['CodedeckID'])) {
                $CodedeckID = $post_data['CodedeckID'];
            }
            if (isset($post_data['RateTypeID'])) {
                $RateTypeID = $post_data['RateTypeID'];
            }
            $sort_column = $columns[$post_data['iSortCol_0']];
            $query = "call prc_getDestinationGroupSet(" . $CompanyID . ",'" . $Name . "','" . intval($CodedeckID) . "'," . (ceil($post_data['iDisplayStart'] / $post_data['iDisplayLength'])) . " ," . $post_data['iDisplayLength'] . ",'" . $sort_column . "','" . $post_data['sSortDir_0'] ."','". $RateTypeID ."'";
            if (isset($post_data['Export']) && $post_data['Export'] == 1) {
                $query = $query . ',1)';
                Log::info($query);
                 $result = DB::select($query);
                return $result;

            } else {
                $query .= ',0)';
                Log::info($query);
                
                return $result = DataTableSql::of($query)->make();
            
          }
        } catch (\Exception $e) {
            Log::info($e);
            return $this->response->errorInternal();
        }
    }

    public static function getTypeNameByID($id)
    {  
        $dgstypeid = DestinationGroupSet::find($id);
        $type = RateType::find($dgstypeid->RateTypeID);
        if(!empty($type->Title))
        {
            return $type->Title;
        }
        else { return 'null';}

    }
}