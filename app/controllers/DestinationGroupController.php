<?php


class DestinationGroupController extends \BaseController {

    public function index() {
        $CodedeckList = BaseCodeDeck::getCodedeckIDList();
        return View::make('destinationgroup.index', compact('CodedeckList'));
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
    public function store(){
        $post_data = Input::all();
        $CompanyID = User::get_companyID();

        $rules['Name'] = 'required|unique:tblDestinationGroupSet,Name,NULL,CompanyID,CompanyID,' . $CompanyID;
        $rules['CodedeckID'] = 'required';
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()]);
        }
        try {
            $insertdata = array();
            $insertdata['Name'] = $post_data['Name'];
            $insertdata['CodedeckID'] = $post_data['CodedeckID'];
            $insertdata['CompanyID'] = $CompanyID;
            $insertdata['CreatedBy'] = User::get_user_full_name();
            $insertdata['created_at'] = date('Y-m-d H:i:s');
            $DestinationGroupSet = DestinationGroupSet::create($insertdata);
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
            $rules['CodedeckID'] = 'required';
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
        $DestinationGroupSetID = $id;
        $name = DestinationGroupSet::getName($id);
        $discountplanapplied = DiscountPlan::isDiscountPlanApplied('DestinationGroupSet',$id,0);
        return View::make('destinationgroup.show', compact('DestinationGroupSetID','countries','name','discountplanapplied'));
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

    public function export_datagrid(){
        $getdata = Input::all();
        $response = DestinationGroup::DataGrid($getdata);
        $getdata["Export"] = 1;

            $excel_data = $response;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Destination Group Set', function ($excel) use ($excel_data) {
                $excel->sheet('Destination Group Set', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');

    }

    public function appcodes()
    {
        $inputdata = Input::all();
        if(!empty($postdata["DestinationGroupID"])){
            $dgid = $postdata["DestinationGroupID"];
        }
        if(!empty($postdata["DestinationGroupSetID"])){
            $dgsid = $postdata["DestinationGroupSetID"];
        }
        Log::info("getting codes");

        return $appcodes  = DB::statement("call prc_getDestinationCode(".$dgsid.",".$dgid.",'0','','0','1','1','2000')");


    }
    public function code_ajax_datagrid(){
        $getdata = Input::all();
        $response =  DestinationGroup::CodeDataGrid($getdata);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Destination Group', function ($excel) use ($excel_data) {
                $excel->sheet('Destination Group', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return ($response);
    }

    public function codelist()
    {
        $postdata = Input::all();
        $dgid= '';
        $dgsid = '';
        $countries = '';
        $types = '';
        if($postdata['stype'] == 'Termination'){
            $types = DestinationGroupSet::getTerminationTypes();
        }
        if($postdata['stype'] == 'Access'){
            $types = DestinationGroupSet::getAccessTypes();
        }
        
        if(!empty($postdata["DestinationGroupID"])){
            $dgid = $postdata["DestinationGroupID"];
        }
        if(!empty($postdata["DestinationGroupSetID"])){
            $dgsid = $postdata["DestinationGroupSetID"];
        }
        if(!empty($postdata["countries"])){
            $countries = json_decode($postdata["countries"]);
        }

        return View::make('destinationgroup.include.code2', compact('dgid', 'dgsid','countries','types'));
    }

    public function codelists()
    {
        $postdata = Input::all();
        $dgid= '';
        $dgsid = '';
         $countries  = Country::getCountryDropdownIDList();
        if(!empty($postdata["DestinationGroupID"])){
            $dgid = $postdata["DestinationGroupID"];
        }
        if(!empty($postdata["DestinationGroupSetID"])){
            $dgsid = $postdata["DestinationGroupSetID"];
        }
        

        return View::make('destinationgroup.include.codes', compact('dgid', 'dgsid','countries'));
    }

    public function group_store(){
        $postdata = Input::all();
        $CompanyID = User::get_companyID();

        $rules['Name'] = 'required|unique:tblDestinationGroup,Name,NULL,CompanyID,CompanyID,' . $CompanyID.',DestinationGroupSetID,'.$postdata['DestinationGroupSetID'];
        $rules['DestinationGroupSetID'] = 'required';
        $validator = Validator::make($postdata, $rules);
        if ($validator->fails()) {
           // return json_validator_response($validator);
            return json_validator_response($validator);
        }
        try {
            $insertdata = array();
            $insertdata['Name'] = $postdata['Name'];
            $insertdata['DestinationGroupSetID'] = $postdata['DestinationGroupSetID'];
            $insertdata['CompanyID'] = $CompanyID;
            if(isset($postdata['CountryID'])){$insertdata['CountryName'] = $postdata['CountryID'];}
            if(isset($postdata['Type'])){$insertdata['Type'] = $postdata['Type'];}
            if(isset($postdata['Prefix'])){$insertdata['Prefix'] = $postdata['Prefix'];}
            if(isset($postdata['City'])){$insertdata['City'] = $postdata['City'];}
            if(isset($postdata['Tariff'])){$insertdata['Tariff'] = $postdata['Tariff'];}
            if(isset($postdata['PackageID'])){$insertdata['PackageID'] = $postdata['PackageID'];}
            $insertdata['CreatedBy'] = User::get_user_full_name();
            $insertdata['created_at'] = date("Y-m-d H:i:s");
            $DestinationGroup = DestinationGroup::create($insertdata);
            $dgsid = $DestinationGroup->DestinationGroupID;
            //updating codes

            if(isset($postdata['RateID'])) {
            $postdata['RateID'] = implode(',', $postdata['RateID']);
        
        if(isset($postdata['FilterCode'])) {
            $postdata['Code'] = $postdata['FilterCode'];
        }
        if(isset($postdata['FilterDescription'])) {
            $postdata['Description'] = $postdata['FilterDescription'];
        }

            //$rules['Name'] = 'required|unique:tblDestinationGroup,Name,' . $DestinationGroupID . ',DestinationGroupID,CompanyID,' . $CompanyID;
            
            try {
                try {
                    $DestinationGroup = DestinationGroup::findOrFail($dgsid);
                } catch (\Exception $e) {
                    $reponse_data = ['status' => 'failed', 'message' => 'Destination Group not found', 'status_code' => 200];
                    return API::response()->array($reponse_data)->statusCode(200);
                }
                $updatedata = array();
                if (isset($postdata['Name'])) {
                    $updatedata['Name'] = $postdata['Name'];
                }
                $RateID= $Description =  $Code = $Action ='';
                $CountryID = 0;
                if(isset($postdata['RateID'])) {
                    $RateID = $postdata['RateID'];
                }
                if(isset($postdata['Code'])) {
                    $Code = $postdata['Code'];
                }
                if(isset($postdata['CountryID'])) {
                    $CountryID = intval($postdata['CountryID']);
                }
                if(isset($postdata['Description'])) {
                    $Description = $postdata['Description'];
                }
                if(isset($postdata['Action'])) {
                    $Action = $postdata['Action'];
                }
                $DestinationGroup->update($updatedata);
               
                $insert_query = "call prc_insertUpdateDestinationCode(?,?,?,?,?,?)";
                DB::statement($insert_query,array(intval($dgsid),$RateID,$CountryID,$Code,$Description,'Insert'));
            
                //return Response::json(['status' => 'success','message' => 'Codes updated']);
            } catch (\Exception $e) {
                Log::info($e);
                return $this->response->errorInternal('Internal Server');
            }
}
            // updating codes finish


            return Response::json(['status' => 'success','message' => 'Destination Group added successfully']);
        } catch (\Exception $e) {
            Log::info($e);
            return $this->response->errorInternal('Internal Server');
        }
    }
    public function group_delete($id){
        try {
            if (intval($id) > 0) {
                if (!DestinationGroup::checkForeignKeyById($id)) {
                    try {
                        DB::beginTransaction();
                        $result = DestinationGroupCode::where('DestinationGroupID',$id)->delete();
                        $result = DestinationGroup::find($id)->delete();
                        DB::commit();
                        if ($result) {
                            return Response::json(['status' => 'success','message' =>'Destination Group Successfully Deleted']);
                        } else {
                            return Response::json(['status' => 'fail','message' =>'Problem Deleting Destination Group.']);
                        }
                    } catch (\Exception $ex) {
                        Log::info($ex);
                        try {
                            DB::rollback();
                        } catch (\Exception $err) {
                            Log::error($err);
                        }
                        Log::info('Destination Group is in Use');
                        return Response::json(['status' => 'failed','message' =>'Destination Group is in Use, You cant delete this Destination Group.']);
                    }
                } else {
                    return Response::json(['status' => 'failed','message' =>'Destination Group is in Use, You cant delete this Destination Group.']);
                }
            } else {
                return Response::json(['status' => 'failed','message' =>"Provide Valid Integer Value"]);
            }
        } catch (\Exception $e) {
            Log::info($e);
            return $this->response->errorInternal('Internal Server');
        }
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
        $CompanyID = User::get_companyID();

            //$rules['Name'] = 'required|unique:tblDestinationGroup,Name,' . $DestinationGroupID . ',DestinationGroupID,CompanyID,' . $CompanyID;
            $rules['DestinationGroupID'] = 'required';
            $validator = Validator::make($postdata, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            try {
                try {
                    $DestinationGroup = DestinationGroup::findOrFail($id);
                } catch (\Exception $e) {
                    return $reponse_data = ['status' => 'failed', 'message' => 'Destination Group not found', 'status_code' => 200];
                   // return API::response()->array($reponse_data)->statusCode(200);
                }
                $updatedata = array();
                if (isset($postdata['Name'])) {
                    $updatedata['Name'] = $postdata['Name'];
                }
                $RateID= $Description =  $Code = $Action ='';
                $CountryID = 0;

                if(isset($postdata['RateID'])) {
                    $RateID = $postdata['RateID'];
                }
                if(isset($postdata['Code'])) {
                    $Code = $postdata['Code'];
                }
                if(isset($postdata['CountryID'])) {
                    $CountryID = intval($postdata['CountryID']);
                }
                if(isset($postdata['Description'])) {
                    $Description = $postdata['Description'];
                }
                if(isset($postdata['Action'])) {
                    $Action = $postdata['Action'];
                }
                //dd($postdata);
                $DestinationGroup->update($updatedata);
                $insert_query = "call prc_insertUpdateDestinationCode(?,?,?,?,?,?)";
                DB::statement($insert_query,array(intval($DestinationGroup->DestinationGroupID),$RateID,$CountryID,$Code,$Description,$Action));
                Log::info(json_encode(array(intval($DestinationGroup->DestinationGroupID),$RateID,$CountryID,$Code,$Description,$Action)));
                return Response::json(['status' => 'success','message' => 'Destination Group updated successfully ']);
               // return \Session::flash('successmsg', 'successfully updated');
            } catch (\Exception $e) {
                Log::info($e);
                return $this->response->errorInternal('Internal Server');
            }
    }
    public function update_name($id){

        if ($id > 0) {
            $postdata = Input::all();
            if(!empty($postdata['action']) && $postdata['action'] == 'Delete'){
               $action = 'Delete'; 
            }else {$action = 'Insert';}

            if(isset($postdata['RateID'])) {
            $postdata['RateID'] = implode(',', $postdata['RateID']);
        }
        if(isset($postdata['FilterCode'])) {
            $postdata['Code'] = $postdata['FilterCode'];
        }
        if(isset($postdata['FilterDescription'])) {
            $postdata['Description'] = $postdata['FilterDescription'];
        }
            $CompanyID = User::get_companyID();
            $ii  = $postdata['DestinationGroupSetID'];
            $rules['Name'] = 'required|unique:tblDestinationGroup,Name,' . $id . ',DestinationGroupID,CompanyID,' . $CompanyID.',DestinationGroupSetID,'.$postdata['DestinationGroupSetID'];
            $rules['DestinationGroupID'] = 'required';
            $validator = Validator::make($postdata, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
        
          try {
            try {
                    $DestinationGroup = DestinationGroup::findOrFail($id);
                } catch (\Exception $e) {
                    $reponse_data = ['status' => 'failed', 'message' => 'Destination Group not found', 'status_code' => 200];
                    return API::response()->array($reponse_data)->statusCode(200);
                }
                $updatedata = array();
                
                $RateID= $Description =  $Code = $Action ='';
                $CountryID = 0;
                if (isset($postdata['Name'])) {
                    $updatedata['Name'] = $postdata['Name'];
                }
                
                if(isset($postdata['RateID'])) {
                    $RateID = $postdata['RateID'];
                }
                if(isset($postdata['Code'])) {
                    $Code = $postdata['Code'];
                }
                if(isset($postdata['CountryID'])) {
                    $updatedata['CountryName'] = $postdata['CountryID'];
                    $updatedata['CountryID'] = $postdata['CountryID'];
                    $CountryID = intval($postdata['CountryID']);
                }
                if(isset($postdata['Description'])) {
                    $Description = $postdata['Description'];
                }
                if(isset($postdata['Type'])) {
                    $updatedata['Type'] = $postdata['Type'];
                    $Type= $postdata['Type'];
                }
                if(isset($postdata['Prefix'])) {
                    $updatedata['Prefix'] = $postdata['Prefix'];
                    $Prefix= $postdata['Prefix'];
                }
                if(isset($postdata['City'])) {
                    $updatedata['City'] = $postdata['City'];
                    $City= $postdata['City'];
                }
              if(isset($postdata['Tariff'])) {
                  $updatedata['Tariff'] = $postdata['Tariff'];
                  $Tariff= $postdata['Tariff'];
              }

                if(isset($postdata['PackageID'])) {
                    $updatedata['PackageID'] = $postdata['PackageID'];
                    $PackageID= $postdata['PackageID'];
                }
               
               
                    //$Action = "Insert";

                $DestinationGroup->update($updatedata);
                if(isset($postdata['RateID']) && !empty($postdata['RateID'])) {
                $insert_query = "call prc_insertUpdateDestinationCode(?,?,?,?,?,?)";
                DB::statement($insert_query,array(intval($DestinationGroup->DestinationGroupID),$RateID,$CountryID,$Code,$Description,$action));
            }
                    
                
                return Response::json(['status' => 'success','message' => 'Destination Group updated successfully']);
            } catch (\Exception $e) {
                Log::info($e);
                return $this->response->errorInternal('Internal Server');
            }

        } else {
            return Response::json(['status' => 'fail' ,'message'=> 'Provide Valid Integer Value.']);
        }

    }
}