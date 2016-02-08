<?php

class VendorProfilingController extends \BaseController {


    public function index() {

        $data['CompanyID']=User::get_companyID();
        $data['Status'] = 1;
        $data['IsVendor'] = 1;
        $active_vendor = Account::where($data)->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
        $data['Status'] = '0';
        $inactive_vendor = Account::where($data)->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
        //$allvendorcodes =  VendorTrunk::getAllVendorCodes();
        $allvendorcodes = array();
        $trunks = Trunk::getTrunkDropdownIDList();
        $trunk_keys = getDefaultTrunk($trunks);
        $countriesCode = Country::getCountryDropdownIDList();
        $countries = unserialize(serialize($countriesCode));
        unset($countries['']);
        $countriesCode[''] = 'Select Countries';
        $countries = array(0=>'Select All')+$countries;
        $account_owners = User::getOwnerUsersbyRole();
        return View::make('vendorprofiling.index', compact('active_vendor','inactive_vendor','allvendorcodes','trunk_keys','trunks','countries','countriesCode','account_owners'));
    }

    public function ajax_vendor($id){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $vendors = Account::where('CompanyID',$CompanyID)->where('Status',1)->where('IsVendor',$id)->select(array('AccountID','AccountName'));
        Datatables::of($vendors)->make();
    }

    public function active_deactivate_vendor(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        if($data['action'] == 'deactivate' && !empty($data['AccountID']) && is_array($data['AccountID'])){
            Account::whereIn('AccountID',$data['AccountID'])->update(array('Status'=>'0'));
            $active_vendor  = Account::select('AccountID','AccountName')->where(['CompanyID'=>$CompanyID,'IsVendor'=>1,'Status'=>1])->orderBy('AccountName')->get();
            $inactive_vendor  = Account::select('AccountID','AccountName')->where(['CompanyID'=>$CompanyID,'IsVendor'=>1,'Status'=>0])->orderBy('AccountName')->get();
            return Response::json(array("status" => "success", "message" => "Vendor Deactivated","active_vendor"=>$active_vendor,"inactive_vendor"=>$inactive_vendor));
        }elseif($data['action'] == 'activate' && !empty($data['AccountID']) && is_array($data['AccountID'])){
            Account::whereIn('AccountID',$data['AccountID'])->update(array('Status'=>1));
            $active_vendor  = Account::select('AccountID','AccountName')->where(['CompanyID'=>$CompanyID,'IsVendor'=>1,'Status'=>1])->orderBy('AccountName')->get();
            $inactive_vendor  = Account::select('AccountID','AccountName')->where(['CompanyID'=>$CompanyID,'IsVendor'=>1,'Status'=>0])->orderBy('AccountName')->get();
            return Response::json(array("status" => "success", "message" => "Vendor Activated.","active_vendor"=>$active_vendor,"inactive_vendor"=>$inactive_vendor));
        }
        return Response::json(array("status" => "failed", "message" => "No Vendor Selected."));
    }

    public function ajax_datagrid(){
        $data = Input::all();
        $data['Country'] = empty($data['Code'])?$data['Country']:'';
        $data['iDisplayStart'] +=1;
        $columns = array('RateID','Code');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();
        $query = "call prc_GetVendorCodes (".$companyID.",'".$data['Trunk']."','".$data['Country']."','".$data['Code']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."' ,0 )";
        return DataTableSql::of($query)->make();
    }

    public function block_unblockcode(){
        $data = Input::all();
        $TrunkID =$data['Trunk'];
        $CompanyID = User::get_companyID();
        $username = User::get_user_full_name();
        $isall = 0;
        $block = 0;
        $isCountry = 0;
        if(!empty($data['AccountID']) && is_array($data['AccountID'])) {

            $AccountIDs = implode(",",array_filter($data['AccountID'],'intval'));

            if(!empty($data['criteria'])){
                /* select all found records */
                if(!empty($data['Code'])){
                    $Codes = $data['Code'];
                    /*
                     * Block by Code
                     * */
                    if ($data['action'] == 'block') {
                        $block=1;
                        $results = DB::statement('call prc_BlockVendorCodes ( ?,?,?,?,?,?,?,?,? ); ', array($CompanyID, $AccountIDs, $TrunkID, '', $Codes, $username, $block,$isCountry,$isall)); //1 for Unblock
                        if ($results) {
                            return Response::json(array("status" => "success", "message" => "Code Blocked Successfully."));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Blocking Code."));
                        }

                    } else { // Unblock
                        $results = DB::statement('call prc_BlockVendorCodes ( ?,?,?,?,?,?,?,?,? ); ', array($CompanyID, $AccountIDs, $TrunkID, '', $Codes, $username, $block,$isCountry,$isall)); //2 for Unblock
                        if ($results) {
                            return Response::json(array("status" => "success", "message" => "Code Unblock Successfully."));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Unblocking Code."));
                        }
                    }
                }else{
                    $isCountry = 1;
                    $CountryIDs='';
                    if(!empty($data['criteriaCountry'])){
                        if($data['criteriaCountry'][0]=','){
                            $CountryIDs = ltrim($data['criteriaCountry'],',');
                        }else{
                            $CountryIDs = $data['criteriaCountry'];
                        }
                    }else{
                        $isall = 1;
                    }

                    /*
                     * Block by Country
                     * */
                    if ($data['action'] == 'block') {
                        $block=1;
                        $results = DB::statement('call prc_BlockVendorCodes ( ?,?,?,?,?,?,?,?,? ); ', array($CompanyID, $AccountIDs, $TrunkID, $CountryIDs, '', $username, $block,$isCountry,$isall)); //1 for Unblock
                        if ($results) {
                            return Response::json(array("status" => "success", "message" => "Country Blocked Successfully."));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem blocking Country."));
                        }

                    } else { // Unblock
                        $results = DB::statement('call prc_BlockVendorCodes ( ?,?,?,?,?,?,?,?,? ); ', array($CompanyID, $AccountIDs, $TrunkID, $CountryIDs, '', $username, $block,$isCountry,$isall)); //2 for Unblock
                        if ($results) {
                            return Response::json(array("status" => "success", "message" => "Country Unblocked Successfully."));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Unblocking Country."));
                        }
                    }
                }

            }elseif(isset($data['Codes']) && !empty($data['Codes'])){


                $Codes = $data['Codes'];

                /*
                 * Block by Code
                 * */
                if ($data['action'] == 'block') {
                    $block=1;
                    $results = DB::statement('call prc_BlockVendorCodes ( ?,?,?,?,?,?,?,?,? ); ', array($CompanyID, $AccountIDs, $TrunkID, '', $Codes, $username, $block,$isCountry,$isall)); //1 for Unblock
                    if ($results) {
                        return Response::json(array("status" => "success", "message" => "Code Blocked Successfully."));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Blocking Code."));
                    }

                } else { // Unblock
                    $results = DB::statement('call prc_BlockVendorCodes ( ?,?,?,?,?,?,?,?,? ); ', array($CompanyID, $AccountIDs, $TrunkID, '', $Codes, $username, $block,$isCountry,$isall)); //2 for Unblock
                    if ($results) {
                        return Response::json(array("status" => "success", "message" => "Code Unblock Successfully."));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Unblocking Code."));
                    }
                }

            }else if(isset($data['countries']) && ($data['countries']==0 || !empty($data['countries']))){
                $isCountry = 1;
                if(in_array(0,explode(',',$data['countries']))){
                    $isall = 1;
                }
                $CountryIDs = $data['countries'];

                /*
                 * Block by Country
                 * */
                if ($data['action'] == 'block') {
                    $block=1;
                    $results = DB::statement(' call prc_BlockVendorCodes ( ?,?,?,?,?,?,?,?,? ); ', array($CompanyID, $AccountIDs, $TrunkID, $CountryIDs, '', $username, $block,$isCountry,$isall)); //1 for Unblock
                    if ($results) {
                        return Response::json(array("status" => "success", "message" => "Country Blocked Successfully."));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem blocking Country."));
                    }

                } else { // Unblock
                    $results = DB::statement('call prc_BlockVendorCodes ( ?,?,?,?,?,?,?,?,? ); ', array($CompanyID, $AccountIDs, $TrunkID, $CountryIDs, '', $username, $block,$isCountry,$isall)); //2 for Unblock
                    if ($results) {
                        return Response::json(array("status" => "success", "message" => "Country Unblocked Successfully."));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Unblocking Country."));
                    }
                }

            }


        }
        return Response::json(array("status" => "failed", "message" => "No Vendor Selected."));
    }

}