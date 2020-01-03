<?php
class DestinationGroup extends \Eloquent
{

    protected $guarded = array("DestinationGroupID");

    protected $table = 'tblDestinationGroup';

    protected $primaryKey = "DestinationGroupID";

    public $timestamps = false; // no created_at and updated_at


     public static function checkForeignKeyById($id) {
        $hasInDiscountPlan = Discount::where("DestinationGroupID",$id)->count();
        if( intval($hasInDiscountPlan) > 0 ){
            return true;
        }else{
            return false;
        }
    }

    public static function getCountryName($id)
    {
        return $countryname = Country::where('CountryID',$id)->pluck('Country');
        
    }
    public static function getDropdownIDList($DestinationGroupSetID){
        $CompanyId = User::get_companyID();
        $DropdownIDList = DestinationGroup::where(array('DestinationGroupSetID'=>$DestinationGroupSetID))->lists('Name', 'DestinationGroupID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;


        return $DropdownIDList;
    }
    public static function getName($DestinationGroupID){
        return DestinationGroup::where("DestinationGroupID",$DestinationGroupID)->pluck('Name');
    }
    

      public static function DataGrid()
    {
        $post_data = Input::all();
        try {
            $CompanyID = User::get_companyID();
            $rules['iDisplayStart'] = 'required|Min:1';
            $rules['iDisplayLength'] = 'required';
            $rules['sSortDir_0'] = 'required';
            $rules['DestinationGroupSetID'] = 'required';
            $validator = Validator::make($post_data, $rules);
            if ($validator->fails()) {
                return Response::json(['status' => 'fail' ,'message' => $validator->errors()]);
            }
            $post_data['iDisplayStart'] += 1;
            $columns = ['Name', 'CreatedBy', 'created_at'];
            $Name = $DestinationGroupSetID = '';
            $CountryName = '';
            $Type = '';
            $Prefix = '';
            $City = '';
            $Tariff = '';
            $PackageID='';
            if (isset($post_data['Name'])) {
                $Name = $post_data['Name'];
            }
            if (isset($post_data['DestinationGroupSetID'])) {
                $DestinationGroupSetID = $post_data['DestinationGroupSetID'];
                $gettypeid = DestinationGroupSet::where('DestinationGroupSetID',$DestinationGroupSetID)->pluck('RateTypeID');
            } else {$gettypeid = 0;}
            if (isset($post_data['CountryID'])) {
                $CountryName = $post_data['CountryID'];
            }
            if (isset($post_data['Type'])) {
                $Type = $post_data['Type'];
            }
            if (isset($post_data['Prefix'])) {
                $Prefix = $post_data['Prefix'];
            }
            if (isset($post_data['City'])) {
                $City = $post_data['City'];
            }
            if (isset($post_data['Tariff'])) {
                $Tariff = $post_data['Tariff'];
            }
            if (isset($post_data['PackageID'])) {
                $PackageID = $post_data['PackageID'];
            }

            

            $sort_column = $columns[$post_data['iSortCol_0']];
            $query = "call prc_getDestinationGroup(" . $CompanyID . ",'" . intval($DestinationGroupSetID) . "','" . $Name . "'," . (ceil($post_data['iDisplayStart'] / $post_data['iDisplayLength'])) . " ," . $post_data['iDisplayLength'] . ",'" . $sort_column . "','" . $post_data['sSortDir_0'] ."', '". $CountryName. "', '". $Type. "', '". $Prefix. "', '". $City. "','". $Tariff. "', '". $PackageID ."', '".$gettypeid."'";
            if (isset($post_data['Export']) && $post_data['Export'] == 1) {
                $result = DB::select($query . ',1)');
                Log::info(json_encode($query));
            } else {
                $query .= ',0)';
                Log::info(json_encode($query));
                $result = DataTableSql::of($query)->make();
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::info($e);
            return $this->response->errorInternal('Internal Server');
        }
    }

    public static function CodeDataGrid($postdata)
    {
        $post_data = $postdata;
        
        try {
           // Log::info("Post Data" . print_r($post_data,true) );
            $rules['iDisplayStart'] = 'required|Min:1';
            $rules['DestinationGroupSetID'] = 'required';
            $rules['iDisplayLength'] = 'required';
            $validator = Validator::make($post_data, $rules);
            if ($validator->fails()) {
                return Response::json(['status' => 'fail','message' => $validator->errors()]);
            }
            $DestinationGroupID = $CountryID = $Selected = 0;
            $Code = $Description = '';
            $Type = '';
            $post_data['iDisplayStart'] += 1;
            if (isset($post_data['DestinationGroupID'])) {
                $DestinationGroupID = $post_data['DestinationGroupID'];
            }
            if (isset($post_data['Code'])) {
                $Code = $post_data['Code'];
            }
            if (isset($post_data['Description'])) {
                $Description = $post_data['Description'];
            }
            if (isset($post_data['CountryID'])) {
                $CountryID = $post_data['CountryID'];
            }
            if (isset($post_data['Selected'])) {
                $Selected = $post_data['Selected'] == 'true'?1:0;
            }
            if (isset($post_data['Type'])) {
                $Type = $post_data['Type'];
            }
            $query = "call prc_getDestinationCode(" . intval($post_data['DestinationGroupSetID']) . "," . intval($DestinationGroupID) . ",'".$CountryID."','".$Code."','".$Selected."','".$Description."', '".$Type ."','".(ceil($post_data['iDisplayStart'] / $post_data['iDisplayLength']))."','".$post_data['iDisplayLength']."')";
            Log::info(json_encode($query));
            $result = DataTableSql::of($query)->make();

            return $result;
        } catch (\Exception $e) {
            Log::info($e);
            return $this->response->errorInternal('Internal Server');
        }
    }
    
}