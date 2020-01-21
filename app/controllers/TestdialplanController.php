<?php

class TestdialplanController extends \BaseController {

    public function ajax_datagrid() {
        $data = Input::all();

        Log::info("Country List" . print_r($data,true));
        $rules = array(
            'DestinationCode' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $profileId="";
        if(isset($data['routingprofile']) && $data['routingprofile']!=''){
            $profileId=$data['routingprofile'];
            if($data['routingprofile']=='DefaultLCR'){
                $profileId="";
            }
        }
        $data['iDisplayStart'] +=1;
        $columns = array('AccountID','AccountName','Name','Trunk','ServiceName','ServiceID');
        $sort_column = $columns[$data['iSortCol_0']];
        $date = $data['StartDate'] . " " . $data['StartHour'];
        Log::info("Country List" . $date);
        $query = "call prc_getTestDialPlan ('".$DefaultCurrencyID."','".$data['OriginationCode']."','".$data['DestinationCode']."','1','".$profileId."','',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0".
            ",'" .$data['countryList'] . "','" . $date . "')";
        
        Log::info('query:.' . $query);
        
        \Illuminate\Support\Facades\Log::info($query);

        return DataTableSql::of($query,'sqlsrv')->make();
      // return DataTableSql::of($query,'neon_routingenginenew')->make();

        //return Datatables::of($RoutingCategory)->make();
    }

	public function index()
	{
            //echo $CompanyTimezone = Config::get('app.timezone');
            $company_id = User::get_companyID();
            $routingprofile = RoutingProfiles::getActiveRoutingProfile($company_id);
            $countryList = Country::getCountryDropdownList();
        $countryList = unserialize(serialize($countryList));


        unset($countryList['']);


            return View::make('testdialplan.index',compact('routingprofile','countryList'));

        }

	/**
	 * Store a newly created resource in storage.
	 * POST /Routing Category
	 *
	 * @return Response
	 */
	public function create()
	{
            $data = Input::all();
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            $data["UpdatedBy"] = User::get_user_full_name();
            unset($data['RoutingCategoryID']);
            $rules = array(
                'Name' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if ($RoutingCategory = RoutingCategory::checkCategoryName($data["Name"])) {
                if ($RoutingCategory = RoutingCategory::create($data)) {
                    RoutingCategory::clearCache();
                    return Response::json(array("status" => "success", "message" => "Routing Category Successfully Created",'LastID'=>$RoutingCategory->RoutingCategoryID,'newcreated'=>$RoutingCategory));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Routing Category."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Routing Category with this name already exist."));
            }
            
        
            
	}

	/**
	 * Display the specified resource.
	 * GET /Routing Category/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /Routing Category/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /Routing Category/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{   
            if( $id > 0 ) {
                $data = Input::all();
                $RoutingCategory = RoutingCategory::findOrFail($id);
                $companyID = User::get_companyID();
                $data['CompanyID'] = $companyID;
                $data["UpdatedBy"] = User::get_user_full_name();
                $rules = array(
                    'Name' => 'required',
                );
                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return json_validator_response($validator);
                }
                unset($data['RoutingCategoryID']);
                if ($RoutingCategoryDup = RoutingCategory::checkCategoryNameAndID($data["Name"],$id)) {
                    if ($RoutingCategory->update($data)) {
                        RoutingCategory::clearCache();
                        return Response::json(array("status" => "success", "message" => "Routing Category Successfully Updated"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Updating Routing Category."));
                    }
                }else{
                    return Response::json(array("status" => "failed", "message" => "Routing Category with this name already exist."));
                }
            }else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Routing Category."));
            }
        
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /Routing Category/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function delete($id)
	{
             if( intval($id) > 0){

                if(!RoutingCategory::checkForeignKeyById($id)){
                //if($id){
                    
                    try{
                        $result = RoutingCategory::find($id)->delete();
                        RoutingCategory::clearCache();
                        if ($result) {
                            return Response::json(array("status" => "success", "message" => "Routing Category Successfully Deleted"));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Deleting Routing Category."));
                        }
                    }catch (Exception $ex){
                        return Response::json(array("status" => "failed", "message" => "Routing Category is in Use, You cant delete this Routing Category."));
                    }
                }else{
                    return Response::json(array("status" => "failed", "message" => "Routing Category is in Use, You cant delete this Routing Category."));
                }
            }
	}
        public function exports($type){
            
            $CompanyID = User::get_companyID();
            $RoutingCategory = RoutingCategory::where(["CompanyID" => $CompanyID])->get(['Name','Description']);
            $RoutingCategory = json_decode(json_encode($RoutingCategory),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RoutingCategory.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($RoutingCategory);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RoutingCategory.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($RoutingCategory);
            }

        }
        
        
        function update_fields_sorting(){
            
//        $postdata    =  Input::all();
//        if(isset($postdata['main_fields_sort']) && !empty($postdata['main_fields_sort']))
//        {
//           //print_R($postdata);exit;
//            try
//            {
//                ///DB::beginTransaction();
//                $main_fields_sort = json_decode($postdata['main_fields_sort']);
//                foreach($main_fields_sort as $main_fields_sort_Data){
//                    $RoutingCategory = RoutingCategory::findOrFail($main_fields_sort_Data->data_id);
//                    $dataArray=array();
//                    $dataArray['Order'] = $main_fields_sort_Data->Order;
//                    if ($RoutingCategory->update($dataArray)) {
//                        RoutingCategory::clearCache();
//                    }
//                    //RoutingCategory::find($main_fields_sort_Data->data_id)->update(array("Order"=>$main_fields_sort_Data->Order));
//                }
//                //DB::commit();
//                return Response::json(["status" => "success", "message" => "Order Successfully updated."]);
//            } catch (Exception $ex) {
//                //DB::rollback();
//                return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
//            }
//        }
    }
}