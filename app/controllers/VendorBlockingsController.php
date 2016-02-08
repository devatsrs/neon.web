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

        $query = "call prc_GetVendorBlockByCountry (".$id.",".$data['Trunk'].",".$data['Country'].",'".$data['Status']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";

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

        $query = "call prc_GetVendorBlockByCode (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",'".$data['Status']."',".$data['Code'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";

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
            return View::make('vendorblockings.blockby_country', compact('id', 'trunks', 'trunk_keys' ,'countries','Account'));
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
            return View::make('vendorblockings.blockby_code', compact('id', 'trunks', 'trunk_keys', 'countries','Account'));
    }

    public function blockbycountry_exports($id) {
            $data = Input::all();

            $data['Country']=$data['Country']!= ''?$data['Country']:'null';

            $query = "call prc_GetVendorBlockByCountry (".$id.",".$data['Trunk'].",".(int)$data['Country'].",'".$data['Status']."',null ,null,null,null,1)";

            DB::setFetchMode( PDO::FETCH_ASSOC );
            $vendor_blocking_by_country  = DB::select($query);
            DB::setFetchMode( Config::get('database.fetch'));

            Excel::create('Vendor Blocked By Country', function ($excel) use ($vendor_blocking_by_country) {
                $excel->sheet('Vendor Blocked By Country', function ($sheet) use ($vendor_blocking_by_country) {
                    $sheet->fromArray($vendor_blocking_by_country);
                });
            })->download('xls');
    }

    public function blockbycode_exports($id) {
            $data = Input::all();

            $data['Country']=$data['Country']!= ''?$data['Country']:'null';
            $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';

            $companyID = User::get_companyID();

            $query = "call prc_GetVendorBlockByCode (".$companyID.",".$id.",".$data['Trunk'].",".(int)$data['Country'].",'".$data['Status']."',".$data['Code'].",null,null,null,null,1)";

            DB::setFetchMode( PDO::FETCH_ASSOC );
            $vendor_blocking_by_code  = DB::select($query);
            DB::setFetchMode( Config::get('database.fetch'));

            Excel::create('Vendor Blocked By Code', function ($excel) use ($vendor_blocking_by_code) {
                $excel->sheet('Vendor Blocked By Code', function ($sheet) use ($vendor_blocking_by_code) {
                    $sheet->fromArray($vendor_blocking_by_code);
                });
            })->download('xls');
    }

    public function blockbycountry($id){
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
                if(empty($data['CountryID']) && !empty($data['criteria']))
                {
                    $criteria = json_decode($data['criteria'],true);
                    $criteria['Country'] = $criteria['Country'] != '' ? $criteria['Country'] : null;
                    if($data['action']=='block') {
                        /*@TODO: dev-mysql merge - need to fix*/
                        $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$companyID.", " . $id . "," . $criteria['Trunk'] . ",'" . $criteria['Country'] . "',null,'".$username."','".$criteria['Status']."',null,'country',1);");
                        $message = "Vendor blocked";
                    }elseif($data['action']=='unblock'){
                        /*@TODO: dev-mysql merge - need to fix*/
                        $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$companyID.", " . $id . "," . $criteria['Trunk'] . ",'" . $criteria['Country'] . "',null,'".$username."','".$criteria['Status']."',null,'country',2);");
                        $message = "Vendor Unblocked";
                    }

                }else{
                    if($data['action']=='block') {
                        /*@TODO: dev-mysql merge - need to fix*/
                        $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$companyID."," . $id . "," . $data['Trunk'] . ",'" . $data['CountryID'] . "',null,'".$username."','All',null,'country',1);");
                        $message = "Vendor blocked";
                    }elseif($data['action']=='unblock'){
                        /*@TODO: dev-mysql merge - need to fix*/
                        $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$companyID."," . $id . "," . $data['Trunk'] . ",'" . $data['CountryID'] . "',null,'".$username."','All',null,'country',2);");
                        $message = "Vendor Unblocked";
                    }
                }
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
            if(empty($data['RateID']) && !empty($data['criteria']))
            {
                $criteria = json_decode($data['criteria'],true);
                $criteria['Country'] = $criteria['Country'] != '' ? $criteria['Country'] : null;
                $criteria['Code'] = $criteria['Code'] != ''?"'".$criteria['Code']."'":'null';
                if($data['action']=='block') {
                    $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$companyID.", " . $id . "," . $criteria['Trunk'] . ",'" . $criteria['Country'] . "',null,'".$username."','".$criteria['Status']."',".$criteria['Code'].",'code',1);");
                    $message = "Vendor blocked";
                }elseif($data['action']=='unblock'){
                    $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$companyID.", " . $id . "," . $criteria['Trunk'] . ",'" . $criteria['Country'] . "',null,'".$username."','".$criteria['Status']."',".$criteria['Code'].",'code',2);");
                    $message = "Vendor Unblocked";
                }
            }else{
                if($data['action']=='block') {
                    $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$companyID.", " . $id . "," . $data['Trunk'] . ",'','".$data['RateID']."','".$username."','All',null,'code',1);");
                    $message = "Vendor blocked";
                }elseif($data['action']=='unblock'){
                    $results = DB::statement("call prc_VendorBlockUnblockByAccount (".$companyID."," . $id . "," . $data['Trunk'] . ",'','".$data['RateID']."','".$username."','All',null,'code',2);");
                    $message = "Vendor Unblocked";
                }
            }
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
