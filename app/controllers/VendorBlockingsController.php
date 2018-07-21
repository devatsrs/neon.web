<?php

class VendorBlockingsController extends \BaseController {

    private $trunks, $countries;

    public function __construct() {


        //$this->trunks = Trunk::getTrunkDropdownIDList();
        $this->countries = Country::getCountryDropdownIDList();
    }

    public function ajax_datagrid_blockbycountry($id) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;

        $data['Country']=$data['Country']!= ''?$data['Country']:'null';

        $columns = array('CountryID','Country','Status');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_GetVendorBlockByCountry (".$id.",".$data['Trunk'].",".$data['Timezones'].",".$data['Country'].",'".$data['Status']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";

       return  DataTableSql::of($query)->make();
    }

    public function ajax_datagrid_blockbycode($id) {

        $data = Input::all();
        $data['iDisplayStart'] +=1;

        $data['Country']=$data['Country']!= ''?$data['Country']:'null';
        $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';


        $columns = array('RateID','Code','Status','Description');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetVendorBlockByCode (".$companyID.",".$id.",".$data['Trunk'].",".$data['Timezones'].",".$data['Country'].",'".$data['Status']."',".$data['Code'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";

        return DataTableSql::of($query)->make();
    }

    /**
     * Display a listing of the resource.
     * GET /vendorblockings
     *
     * @return Response
     */
    public function index($id) {

            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            $countries = Country::getCountryDropdownIDList();
            $Timezones = Timezones::getTimezonesIDList();
            return View::make('vendorblockings.blockby_country', compact('id', 'trunks', 'trunk_keys' ,'countries','Account','Timezones'));
    }

    // when 2nd Tabl BlockBy Code Submits.
    public function index_blockby_code($id) {
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            $countries = $this->countries;
            $Timezones = Timezones::getTimezonesIDList();
            return View::make('vendorblockings.blockby_code', compact('id', 'trunks', 'trunk_keys', 'countries','Account','Timezones'));
    }

    /**
     * @return mixed
     */
    public function blockunblockcode()
    {

        $postdata = Input::all();
        $CompanyID = User::get_companyID();
        $preference =  !empty($postdata['preference']) ? $postdata['preference'] : 0;
        $acc_id =  $postdata['acc_id'];
        $trunk =  $postdata['trunk'];
        $Timezones =  $postdata['Timezones'];
        $rowcode = $postdata["rowcode"];
        $CodeDeckId = $postdata["CodeDeckId"];
        $description = $postdata["description"];
        $username = User::get_user_full_name();
        $blockId = $postdata["id"];


        if( $postdata['countryBlockingID'] ==  'codewiseBlocking' ){
            $p_action = '';
            $countryBlockingID = 0;
        }else{
            if( $postdata['countryBlockingID'] > 0 ){
                $p_action = 'country_unblock';
                $countryBlockingID = $postdata['countryBlockingID'];
            }else {
                $p_action = 'country_block';
                $countryBlockingID = 0;
            }
        }
        $query = "call prc_lcrBlockUnblock (".$CompanyID.",'".$postdata["GroupBy"]."',".$blockId.",".$preference.",".$acc_id.",".$trunk.",".$Timezones.",".$rowcode.",".$CodeDeckId.",'".$description."','".$username."','".$p_action."','".$countryBlockingID."')";
        DB::select($query);
        \Illuminate\Support\Facades\Log::info($query);
        //$results = DB::select($query);
        //$preference = isset($results[0]->Preference) ? $results[0]->Preference : '';
        $msgVendor = $blockId > 0 ? 'Unblocked' : 'Blocked';
        try{
            $message =  "Vendor ".$msgVendor." Successfully";
            return json_encode(["status" => "success", "message" => $message]);
        }catch ( Exception $ex ){
            $message =  "Oops Somethings Wrong !";
            return json_encode(["status" => "fail", "message" => $message]);
        }

    }

    public function blockbycountry_exports($id,$type) {
            $data = Input::all();

            $data['Country'] = $data['Country'] != ''?"'".$data['Country']."'":'null';

            $query = "call prc_GetVendorBlockByCountry (".$id.",".$data['Trunk'].",".$data['Country'].",'".$data['Status']."',null ,null,null,null,1)";

            DB::setFetchMode( PDO::FETCH_ASSOC );
            $vendor_blocking_by_country  = DB::select($query);
            DB::setFetchMode( Config::get('database.fetch'));


            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Blocked By Country.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($vendor_blocking_by_country);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Blocked By Country.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($vendor_blocking_by_country);
            }

            /*Excel::create('Vendor Blocked By Country', function ($excel) use ($vendor_blocking_by_country) {
                $excel->sheet('Vendor Blocked By Country', function ($sheet) use ($vendor_blocking_by_country) {
                    $sheet->fromArray($vendor_blocking_by_country);
                });
            })->download('xls');*/
    }

    public function blockbycode_exports($id,$type) {
            $data = Input::all();

            $data['Country']=$data['Country']!= ''?$data['Country']:'null';
            $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';

            $companyID = User::get_companyID();

            $query = "call prc_GetVendorBlockByCode (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",'".$data['Status']."',".$data['Code'].",null,null,null,null,1)";

            DB::setFetchMode( PDO::FETCH_ASSOC );
            $vendor_blocking_by_code  = DB::select($query);
            DB::setFetchMode( Config::get('database.fetch'));


            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Blocked By Code.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($vendor_blocking_by_code);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Blocked By Code.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($vendor_blocking_by_code);
            }
            /*Excel::create('Vendor Blocked By Code', function ($excel) use ($vendor_blocking_by_code) {
                $excel->sheet('Vendor Blocked By Code', function ($sheet) use ($vendor_blocking_by_code) {
                    $sheet->fromArray($vendor_blocking_by_code);
                });
            })->download('xls');*/
    }

    public function blockbycountry($id){
        $AccountID = $id;
        $data = Input::all();
        $rules = array('CountryID' => 'required', 'Trunk' => 'required',);
        if(empty($data['CountryID']) && !empty($data['criteria']))
        {
            $rules = array('Trunk' => 'required',);
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $username = User::get_user_full_name();
        $companyID = User::get_companyID();
        $success = false;
        $results ='';
        $message ='';
        if(!empty($data['action'])){

                $Code  = '';
                $RateID  = '';
                if($data['action']=='block') {
                    $p_action = 'country_block';
                    $message = "Vendor blocked";
                }elseif($data['action']=='unblock'){
                    $p_action = 'country_unblock';
                    $message = "Vendor Unblocked";
                }
                if(empty($data['CountryID']) && !empty($data['criteria']))
                {
                    $criteria = json_decode($data['criteria'],true);
                    $TrunkID = $criteria['Trunk'];
                    $TimezonesID = $criteria['Timezones'];
                    $CountryID = $criteria['Country'];

                }else{
                    $TrunkID = $data['Trunk'];
                    $TimezonesID = $data['Timezones'];
                    $CountryID = $data['CountryID'];
                }

                $proc_args  =
                    " ".$companyID          . ", ".
                    " ".$AccountID          . ", ".
                    "'".$Code               . "',".
                    "'".$RateID             . "',".     //RateIDs
                    "'".$CountryID         . "',".     //CountryIDs
                    " ".$TrunkID            . ", ".
                    " ".$TimezonesID            . ", ".
                    "'".$username           . "',".
                    "'".$p_action           . "' ";

                /*IN `p_CompanyId` int,
                IN `p_AccountId` int,
                IN `p_code` VARCHAR(50),
                `p_RateId` longtext,
                IN `p_CountryId` longtext,
                IN `p_TrunkID` varchar(50) ,
                IN  IN `p_Username` varchar(100),
                IN `p_action` varchar(100)*/

                $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$proc_args.");");

                if ($results) {
                    $success = true;
                }

        }
        if ($success) {
            return json_encode(["status" => "success", "message" => $message]);
        } else {
            return json_encode(["status" => "failed", "message" => "Problem Unblocking"]);
        }

    }

    public function blockbycode($id){
        $AccountID = $id;
        $data = Input::all();
        $rules = array('RateID' => 'required', 'Trunk' => 'required',);
        if(empty($data['RateID']) && !empty($data['criteria']))
        {
            $rules = array('Trunk' => 'required',);
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $username = User::get_user_full_name();
        $companyID = User::get_companyID();
        $success = false;
        $results ='';
        $message ='';
        if(!empty($data['action'])){


            $CountryID = '';
            if($data['action']=='block') {
                $p_action = 'code_block';
                $message = "Vendor blocked";
            }elseif($data['action']=='unblock'){
                $p_action = 'code_unblock';
                $message = "Vendor Unblocked";
            }

            if(empty($data['RateID']) && !empty($data['criteria']))
            {
                $criteria = json_decode($data['criteria'],true);
                $Code  = $criteria['Code'];
                $TrunkID = $criteria['Trunk'];
                $TimezonesID = $criteria['Timezones'];
                $RateID = '';

            }else{
                $Code  = '';
                $TrunkID = $data['Trunk'];
                $TimezonesID = $data['Timezones'];
                $RateID = $data['RateID'];

            }

            $proc_args  =
                " ".$companyID          . ", ".
                " ".$AccountID          . ", ".
                "'".$Code               . "',".
                "'".$RateID             . "',".     //RateIDs
                "'".$CountryID          . "',".     //CountryIDs
                " ".$TrunkID            . ", ".
                " ".$TimezonesID        . ", ".
                "'".$username           . "',".
                "'".$p_action           . "' ";

            /*IN `p_CompanyId` int,
            IN `p_AccountId` int,
            IN `p_code` VARCHAR(50),
            `p_RateId` longtext,
            IN `p_CountryId` longtext,
            IN `p_TrunkID` varchar(50) ,
            IN  IN `p_Username` varchar(100),
            IN `p_action` varchar(100)*/

            $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$proc_args.");");
            if ($results) {
                $success = true;
            }
        }
        if ($success) {
            return json_encode(["status" => "success", "message" => $message]);
        } else {
            return json_encode(["status" => "failed", "message" => $message]);
        }

    }

}
