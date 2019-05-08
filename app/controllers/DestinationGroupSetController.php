<?php


class DestinationGroupSetController extends \BaseController {

    public function index() {
    	$Ratetypes = DestinationGroupSet::getRateTypeIDList();
        $CodedeckList = BaseCodeDeck::getCodedeckIDList();
        return View::make('destinationgroup.index', compact('CodedeckList','Ratetypes'));
    }

    public function ajax_datagrid(){
        $getdata = Input::all();
        $response =  DestinationGroupSet::DataGrid($getdata);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
                $excel_data = $response->data;
                $excel_data = json_decode(json_encode($excel_data),true);
                Excel::create('Destination Group', function ($excel) use ($excel_data) {
                    $excel->sheet('Destination Group', function ($sheet) use ($excel_data) {
                        $sheet->fromArray($excel_data);
                    });
                })->download('xls');
        }
        return $response;
    }
    public function export_datagrid(){
        $getdata = Input::all();
        $getdata["Export"] = 1;
        \Illuminate\Support\Facades\Log::info("export_datagrid" .  print_r($getdata,true));
        $response =  DestinationGroupSet::DataGrid($getdata);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Destination Group', function ($excel) use ($excel_data) {
                $excel->sheet('Destination Group', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return $response;
    }
    public function store(){
        $post_data = Input::all();
        $CompanyID = User::get_companyID();

        $rules['Name'] = 'required|unique:tblDestinationGroupSet,Name,NULL,CompanyID,CompanyID,' . $CompanyID;
        $rules['RateTypeID'] = 'required';
        $message['required'] = ":attribute is required";
        $validator = Validator::make($post_data, $rules, $message);
        if ($validator->fails()) {
        	$error = ($validator->errors());
            return Response::json(['status' => 'fail', 'message' => ($error)]);
        }
        try {
            $insertdata = array();
            $insertdata['Name'] = $post_data['Name'];
            if(isset($post_data['CodedeckID'])){
            $insertdata['CodedeckID'] = $post_data['CodedeckID'];
             } else { $insertdata['CodedeckID'] = 0;}
            $insertdata['RateTypeID'] = $post_data['RateTypeID'];
            $insertdata['CompanyID'] = $CompanyID;
            $insertdata['CreatedBy'] = User::get_user_full_name();
            $insertdata['created_at'] = date('Y-m-d H:i:s');
            $DestinationGroupSet = DestinationGroupSet::create($insertdata);
            Log::info(json_encode($insertdata));
            return Response::json(['status' => 'success','message' => 'DestinationGroup Set added successfully']);
        } catch (\Exception $e) {
            Log::info($e);
            return $this->response->errorInternal('Internal Server');
        }
    }
        public function delete($id)
    { 
        try {
            if (intval($id) > 0) {
                if (!DestinationGroupSet::checkForeignKeyById($id)) {
                    try {
                        DB::beginTransaction();
                        DestinationGroupCode::join('tblDestinationGroup','tblDestinationGroup.DestinationGroupID','=','tblDestinationGroupCode.DestinationGroupID')->where('DestinationGroupSetID',$id)->delete();
                        DestinationGroup::where("DestinationGroupSetID",$id)->delete();
                        $result = DestinationGroupSet::find($id)->delete();
                        DB::commit();
                        Log::info($result);
                        if ($result) {
                            Log::info('deleted');
                            return Response::json(['status' => 'success','message' => 'Destination Group Set Successfully Deleted']);
                        } else {
                            Log::info('probelm');
                            return Response::json(['status'=>'success','message' => 'Problem Deleting Destination Group Set.']);
                        }
                    } catch (\Exception $ex) {
                        Log::info($ex);
                        try {
                            DB::rollback();
                        } catch (\Exception $err) {
                            Log::error($err);
                        }
                        Log::info('probelm 1');
                        return Response::json(['status'=>'fail','message' => 'Destination Group Set is in Use, You cant delete this Destination Group Set.']);
                    }
                } else {Log::info('probelm 2');
                    return Response::json(['status' =>'fail','message' => 'Destination Group Set is in Use, You cant delete this Destination Group Set.']);
                }
            } else { Log::info('probelm 3');
                return Response::json(['status' => 'fail','message'=>'Provide Valid Integer Value.']);
            }
        } catch (\Exception $e) {
            Log::info($e);
            return $this->response->errorInternal('Internal Server');
        }
    }
    public function update($id)
    {
         if ($id > 0) {
            $post_data = Input::all();
            $CompanyID = User::get_companyID();

            $rules['Name'] = 'required|unique:tblDestinationGroupSet,Name,' . $id . ',DestinationGroupSetID,CompanyID,' . $CompanyID;
           // $rules['CodedeckID'] = 'required';
            $validator = Validator::make($post_data, $rules);
            if ($validator->fails()) {
                return Response::json($validator->errors(),true);
            }
            try {
                try {
                    $DestinationGroupSet = DestinationGroupSet::findOrFail($id);
                } catch (\Exception $e) {
                    $reponse_data = ['status' => 'failed', 'message' => 'Destination Group not found', 'status_code' => 200];
                    return API::response()->array($reponse_data)->statusCode(200);
                }
                $updatedata = array();
                if (isset($post_data['Name'])) {
                    $updatedata['Name'] = $post_data['Name'];
                }
                if (isset($post_data['CodedeckID'])) {
                    $updatedata['CodedeckID'] = $post_data['CodedeckID'];
                }
                $DestinationGroupSet->update($updatedata);
                return Response::json(['status' => 'success','message' => 'Destination Group Set updated successfully']);
            } catch (\Exception $e) {
                Log::info($e);
                return $this->response->errorInternal('Internal Server');
            }
        } else {
            return Response::json(['status'=>'fail','message'=>'Provide Valid Integer Value.']);
        }
    }
    public function show($id) {
    	$countries  = DestinationGroupSet::getCountriesNames();
        $DestinationGroupSetID = $id;
        $CompanyID = User::get_companyID();
        $name = DestinationGroupSet::getName($id);
        $terminationtype = DestinationGroupSet::getTerminationTypes();
        $typename  = DestinationGroupSet::getTypeNameByID($id);
        $AccessTypes = DestinationGroupSet::getAccessTypes();
        $City               = ServiceTemplate::getCityDD($CompanyID);
        $Tariff             =ServiceTemplate::getTariffDD($CompanyID);
        $Packages = DestinationGroupSet::getPackages();
        $Prefix = DestinationGroupSet::getAccessPrefixNames();
        $CityTariffFilter  = ServiceTemplate::getTariffDD($CompanyID);

        $City = array('' => 'All') + $City;
        $Tariff = array('' => 'All') + $Tariff;
        $CityTariffFilter = array('' => 'All') + $CityTariffFilter;    
        //$codes = DB::select("call prc_getDestinationCode(6,0,'0','','0','','1','50')");
        $discountplanapplied = DiscountPlan::isDiscountPlanApplied('DestinationGroupSet',$id,0);
        if($typename == 'Access'){
        	return View::make('destinationgroup.access', compact('Tariff','DestinationGroupSetID','countries','name','discountplanapplied','typename','terminationtype', 'AccessTypes','City','Packages','Prefix','CityTariffFilter'));

        }  elseif($typename == 'Package'){
        return View::make('destinationgroup.package', compact('DestinationGroupSetID','countries','name','discountplanapplied','typename','terminationtype', 'AccessTypes','CityTariffs','Packages','Prefix','CityTariffFilter'));
    } else{
        return View::make('destinationgroup.show', compact('DestinationGroupSetID','countries','name','discountplanapplied','typename','terminationtype', 'AccessTypes','CityTariffs','Packages','Prefix','CityTariffFilter'));

    }
    }

    public function group_show($id) {
        $countries  = Country::getCountryDropdownIDList();
        $DestinationGroupID = $id;
        $DestinationGroupSetID = DestinationGroup::where("DestinationGroupID",$DestinationGroupID)->pluck('DestinationGroupSetID');
        $groupname = DestinationGroupSet::getName($DestinationGroupSetID);
        $name = DestinationGroup::getName($id);
        $discountplanapplied = DiscountPlan::isDiscountPlanApplied('DestinationGroupSet',$DestinationGroupSetID,0);
        return View::make('destinationgroup.groupshow', compact('DestinationGroupSetID','DestinationGroupID','countries','name','groupname','discountplanapplied'));
    }

    public function group_ajax_datagrid(){
        $getdata = Input::all();
        $response = DestinationGroup::DataGrid($getdata);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Destination Group Set', function ($excel) use ($excel_data) {
                $excel->sheet('Destination Group Set', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return $response;
    }
    public function code_ajax_datagrid(){
        $getdata = Input::all();
        $response =  NeonAPI::request('destinationgroupsetcode/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Destination Group', function ($excel) use ($excel_data) {
                $excel->sheet('Destination Group', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return json_response_api($response,true,true,true);
    }
    public function group_store(){
        $postdata = Input::all();
        if(isset($postdata['RateID'])) {
            $postdata['RateID'] = implode(',', $postdata['RateID']);
        }
        if(isset($postdata['FilterCode'])) {
            $postdata['Code'] = $postdata['FilterCode'];
        }
        if(isset($postdata['FilterDescription'])) {
            $postdata['Description'] = $postdata['FilterDescription'];
        }
        $response =  NeonAPI::request('destinationgroup/store',$postdata,true,false,false);
        return json_response_api($response);
    }
    public function group_delete($id){
        $response =  NeonAPI::request('destinationgroup/delete/'.$id,array(),'delete',false,false);
        return json_response_api($response);
    }
    public function group_update($id){
        $postdata = Input::all();
        if(isset($postdata['RateID'])) {
            $postdata['RateID'] = implode(',', $postdata['RateID']);
        }
        if(isset($postdata['FilterCode'])) {
            $postdata['Code'] = $postdata['FilterCode'];
        }
        if(isset($postdata['FilterDescription'])) {
            $postdata['Description'] = $postdata['FilterDescription'];
        }
        $response =  NeonAPI::request('destinationgroup/update/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
    public function update_name($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('destinationgroup/update_name/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
}