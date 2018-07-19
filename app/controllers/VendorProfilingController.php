<?php

class VendorProfilingController extends \BaseController {


    public function index() {

        $data['CompanyID']=User::get_companyID();
        $data['Status'] = 1;
        $data['IsVendor'] = 1;
        $active_vendor = Account::where($data)->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
        $data['Status']   = '1';
		$data['IsVendor'] = '0';
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
        $Timezones = Timezones::getTimezonesIDList();
        return View::make('vendorprofiling.index', compact('active_vendor','inactive_vendor','allvendorcodes','trunk_keys','trunks','countries','countriesCode','account_owners','Timezones'));
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
            Account::whereIn('AccountID',$data['AccountID'])->update(array('IsVendor'=>'0'));
            $active_vendor  = Account::select('AccountID','AccountName')->where(['CompanyID'=>$CompanyID,'IsVendor'=>1])->orderBy('AccountName')->get();
            $inactive_vendor  = Account::select('AccountID','AccountName')->where(['CompanyID'=>$CompanyID,'IsVendor'=>0])->orderBy('AccountName')->get();
            return Response::json(array("status" => "success", "message" => "Vendor Deactivated","active_vendor"=>$active_vendor,"inactive_vendor"=>$inactive_vendor));
        }elseif($data['action'] == 'activate' && !empty($data['AccountID']) && is_array($data['AccountID'])){
            Account::whereIn('AccountID',$data['AccountID'])->update(array('IsVendor'=>1,'Status'=>1));
            $active_vendor  = Account::select('AccountID','AccountName')->where(['CompanyID'=>$CompanyID,'IsVendor'=>1])->orderBy('AccountName')->get();
            $inactive_vendor  = Account::select('AccountID','AccountName')->where(['CompanyID'=>$CompanyID,'IsVendor'=>0])->orderBy('AccountName')->get();
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
        $TimezonesID =$data['Timezones'];
        $CompanyID = User::get_companyID();
        $username = User::get_user_full_name();
        $isall = 0;
        $block = 0;
        $isCountry = 0;
        $CountryIDs=0;
        $criteria = 0;
        $Codes = 0;
        if(!empty($data['AccountID']) && is_array($data['AccountID'])) {

            $AccountIDs = implode(",",array_filter($data['AccountID'],'intval'));

            if(!empty($data['block_by'])){
                //block by code
                if(isset($data['block_by']) && $data['block_by'] == 'code'){
                    // by critearia
                    if(!empty($data['criteria']) && $data['criteria']==1){

                        if(!empty($data['Code']) || !empty($data['Country'])){
                            if(!empty($data['Code'])){
                                $criteria = 1;
                                $Codes = $data['Code'];
                            }else{
                                $criteria = 2;
                                if(!empty($data['Country'])){
                                    $isall = 0;
                                    $CountryIDs='';
                                    if(!empty($data['criteriaCountry'])){
                                        if($data['criteriaCountry'][0]==','){
                                            $CountryIDs = ltrim($data['criteriaCountry'],',');
                                        }else{
                                            $CountryIDs = $data['criteriaCountry'];
                                        }
                                    }

                                }
                            }

                        }else{
                            // all record
                            $criteria = 3;

                        }

                    }else{
                        //select record
                        $Codes = $data['Codes'];
                    }
                }

                //block unblock by country
                if(isset($data['block_by']) && $data['block_by'] == 'country'){

                    $isCountry = 1;
                    if(in_array(0,explode(',',$data['countries']))){
                        $isall = 1;
                    }
                    $CountryIDs = $data['countries'];
                }
            }

            if ($data['action'] == 'block') {
                $block=1;
                $results = DB::statement("call prc_BlockVendorCodes (".$CompanyID.",'" . $AccountIDs . "'," .$TrunkID . "," .$TimezonesID . ",'" . $CountryIDs . "','".$Codes."','".$username."',".$block.",".$isCountry.",".$isall.",".$criteria.")");
                if ($results) {
                    return Response::json(array("status" => "success", "message" => "Country Blocked Successfully."));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem blocking Country."));
                }

            } else { // Unblock
                $results = DB::statement("call prc_BlockVendorCodes (".$CompanyID.",'" . $AccountIDs . "'," .$TrunkID . "," .$TimezonesID . ",'" . $CountryIDs . "','".$Codes."','".$username."',".$block.",".$isCountry.",".$isall.",".$criteria.")");
                if ($results) {
                    return Response::json(array("status" => "success", "message" => "Country Unblocked Successfully."));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Unblocking Country."));
                }
            }


        }
        return Response::json(array("status" => "failed", "message" => "No Vendor Selected."));
    }

}