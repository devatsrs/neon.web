<?php

class AssignRoutingController extends \BaseController {

    public function ajax_datagrid($type) {

        $data = Input::all();
        $SourceCustomers = empty($data['SourceCustomers']) ? '' : $data['SourceCustomers'];
        if ($SourceCustomers == 'null') {
            $SourceCustomers = '';
        }
        $ratetableeid = empty($data['RateTableId']) ? 0 : $data['RateTableId'];
        $TrunkID = empty($data['TrunkID']) ? 0 : $data['TrunkID'];
        $CompanyID = User::get_companyID();
        $services = !empty($data["services"]) ? $data["services"] : 0;
        $data['iDisplayStart'] +=1;
        $columns = array('AccountID','AccountName','InRateTableName','OutRateTableName','ServiceName','ServiceID');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getAssignRoutingProfileByAccount (".$CompanyID.",'".$data["level"]."',".$TrunkID.",'".$SourceCustomers."',".$services.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."' ";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            foreach($excel_data as $rowno => $rows){
                foreach($rows as $colno => $colval){
                    $excel_data[$rowno][$colno] = str_replace( "<br>" , "\n" ,$colval );
                }
            }

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/ApplyAssignRouting.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/ApplyAssignRouting.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';
        
        Log::info('query:.' . $query);
        
        \Illuminate\Support\Facades\Log::info($query);

        return DataTableSql::of($query,'sqlsrvrouting')->make();

    }

	public function index()
	{
            $all_customers = Account::getAccountIDList(['IsCustomer'=>1]);
            $companyID = User::get_companyID();
            $trunks = Trunk::getTrunkDropdownIDList();
            $routingprofile = RoutingProfiles::getRoutingProfile();
            $codedecks = BaseCodeDeck::where(["CompanyID" => $companyID])->lists("CodeDeckName", "CodeDeckId");
            $codedecks = array(""=>"Select Codedeck")+$codedecks;
            $rate_tables = RateTable::getRateTables();
            $allservice = Service::getDropdownIDList($companyID);
            $currencies = Currency::getCurrencyDropdownIDList();
            $CurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
            return View::make('assignrouting.index', compact('all_customers','trunks','codedecks','currencies','CurrencyID','rate_tables','allservice','routingprofile'));
        }
        
        public function routingprofilescategory()
	{
        $PageRefresh=1;
        return View::make('routingprofiles.routingprofilescategory', compact('PageRefresh'));

        }
        
        public function routingprofilesfilter()
	{
        $PageRefresh=1;
        return View::make('routingprofiles.routingprofilesfilter', compact('PageRefresh'));

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
            
            unset($data['RoutingProfileID']);
            $rules = array(
                'Name' => 'required',
                'Description' => 'required',
                'RoutingPolicy' => 'required',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
          
            if ($RoutingProfiles = RoutingProfiles::create($data)) {
                RoutingProfiles::clearCache();
                $orderingCnt=1;
                $dataCat = array_unique($data['RoutingCategory']);
                foreach($dataCat as $key=> $val){
                    $RoutingProfileCategoryData = array();
                    $RoutingProfileCategoryData['RoutingProfileID'] = $RoutingProfiles->RoutingProfileID;
                    $RoutingProfileCategoryData['RoutingCategoryID'] = $val;
                    $RoutingProfileCategoryData['Order'] = $orderingCnt;
                    $RoutingProfileCategory = RoutingProfileCategory::create($RoutingProfileCategoryData);
                    RoutingProfileCategory::clearCache();
                    $orderingCnt++;
                }
                return Response::json(array("status" => "success", "message" => "Routing Profile Successfully Created",'LastID'=>$RoutingProfiles->RoutingProfileID,'newcreated'=>$RoutingProfiles));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Routing Profile."));
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
                $RoutingCategory = RoutingProfiles::findOrFail($id);
                $companyID = User::get_companyID();
                $data['CompanyID'] = $companyID;
                $data["UpdatedBy"] = User::get_user_full_name();
                $rules = array(
                    'Name' => 'required',
                    'Description' => 'required',
                    'RoutingPolicy' => 'required',
                );
                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return json_validator_response($validator);
                }
                unset($data['RoutingProfileID']);
                if ($RoutingCategory->update($data)) {
                    RoutingProfiles::clearCache();
                    
                    //Delete Old Data
                    RoutingProfileCategory::where(array('RoutingProfileID'=>$id))->delete();
                    
                    //Add Routing profile cate
                    $orderingCnt=1;
                    $dataCat = array_unique($data['RoutingCategory']);
                    foreach($dataCat as $key=> $val){
                        $RoutingProfileCategoryData = array();
                        $RoutingProfileCategoryData['RoutingProfileID'] = $id;
                        $RoutingProfileCategoryData['RoutingCategoryID'] = $val;
                        $RoutingProfileCategoryData['Order'] = $orderingCnt;
                        $RoutingProfileCategory = RoutingProfileCategory::create($RoutingProfileCategoryData);
                        RoutingProfileCategory::clearCache();
                        $orderingCnt++;
                    }
                    //-
                    return Response::json(array("status" => "success", "message" => "Routing Profile Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating Routing Profile."));
                }
            }else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Routing Profile."));
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

               // if(!RoutingCategory::checkForeignKeyById($id)){
                if($id){
                    
                    try{
                        $result = RoutingProfiles::find($id)->delete();
                        RoutingProfiles::clearCache();
                        if ($result) {
                            return Response::json(array("status" => "success", "message" => "Routing Profiles Successfully Deleted"));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Deleting Routing Profiles."));
                        }
                    }catch (Exception $ex){
                        return Response::json(array("status" => "failed", "message" => "Routing Profiles is in Use, You cant delete this Routing Profiles."));
                    }
                }else{
                    return Response::json(array("status" => "failed", "message" => "Routing Profiles is in Use, You cant delete this Routing Profiles."));
                }
            }
	}
        public function exports($type){
            $CompanyID = User::get_companyID();
            $RoutingProfiles = RoutingProfiles::where(["CompanyID" => $CompanyID])->get(['Name','Description']);
            $RoutingProfiles = json_decode(json_encode($RoutingProfiles),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RoutingCategory.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($RoutingProfiles);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RoutingCategory.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($RoutingProfiles);
            }

        }
        public function ajaxcall($id)
	{
            
		$RoutingCategory = RoutingProfileCategory::getRoutingProfileCategory($id);
                echo json_encode($RoutingCategory);
                //echo implode(',', $RoutingCategory);;
	}
}