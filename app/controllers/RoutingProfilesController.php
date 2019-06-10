<?php

class RoutingProfilesController extends \BaseController {

    public function ajax_datagrid() {
        $data = Input::all();
        //$RoutingProfiles = RoutingProfiles::select('Name','Description','Status', 'RoutingProfileID', 'RoutingPolicy');
        $companyID = User::get_companyID();
        $status = $data['Status'] == 'true' ? 1 : 0;
        
//        $exportSelectedTemplate = "select tblRoutingProfile.Name,tblRoutingProfile.Description,tblRoutingProfile.Status,tblRoutingProfile.RoutingProfileID,tblRoutingProfile.RoutingPolicy,".
//                                "(select GROUP_CONCAT(tblRoutingCategory.Name SEPARATOR ', ' ) as Routescategory from tblRoutingCategory where tblRoutingCategory.RoutingCategoryID in (select tblRoutingProfileCategory.RoutingCategoryID from tblRoutingProfileCategory where tblRoutingProfileCategory.RoutingProfileID = tblRoutingProfile.RoutingProfileID)) as Routingcategory ".
//                                "from tblRoutingProfile ";
//        $exportSelectedTemplate =DB::select($exportSelectedTemplate);
//     
//               $exportSelectedTemplate = $exportSelectedTemplate->get();
//               print_r($exportSelectedTemplate);die();
//               $excel_data = json_decode(json_encode($excel_data),true);
               
        $RoutingProfiles = RoutingProfiles::Join('tblRoutingProfileCategory','tblRoutingProfileCategory.RoutingProfileID','=','tblRoutingProfile.RoutingProfileID')
            ->select(['tblRoutingProfile.Name','tblRoutingProfile.Description','tblRoutingProfile.SelectionCode','tblRoutingProfile.Status','tblRoutingProfile.RoutingProfileID',DB::raw("(select GROUP_CONCAT(tblRoutingCategory.Name SEPARATOR ', ' ) as Routescategory from tblRoutingCategory where tblRoutingCategory.RoutingCategoryID in (select tblRoutingProfileCategory.RoutingCategoryID from tblRoutingProfileCategory where tblRoutingProfileCategory.RoutingProfileID = tblRoutingProfile.RoutingProfileID)) as Routingcategory,'tblRoutingProfile.RoutingPolicy'")])
            ->where(["tblRoutingProfile.CompanyID" => $companyID])->groupBy("tblRoutingProfile.RoutingProfileID");
        
        $RoutingProfiles->where(["tblRoutingProfile.Status" => $status]);    

        if(!empty($data['Name'])){
            $RoutingProfiles->where(["tblRoutingProfile.Name" => $data['Name']]);
        }
        
        return Datatables::of($RoutingProfiles)->make();
    }

    public function index() {
        $CompanyID = User::get_companyID();
        $RoutingCategory = RoutingProfiles::getRoutingCategory($CompanyID);
        $VendorConnection = RoutingProfiles::getVendorConnection($CompanyID);
        $RoutingCategories  = RoutingCategory::select('Name','RoutingCategoryID','Description')->get();
        return View::make('routingprofiles.index', compact('RoutingCategory','VendorConnection','RoutingCategories'));
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
            //'RoutingPolicy' => 'required',
            'RoutingCategory' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if(isset($data['Status'])){
            $data['Status']=1;
        }else{
            $data['Status']=0;
        }

        
        if ($RoutingProfiles = RoutingProfiles::create($data)) {

            RoutingProfiles::clearCache();
            $orderingCnt= Input::get('Orders');
            
            $dataCat = array_unique($data['RoutingCategory']);
            $dataCat = array_filter($dataCat);
            
            foreach($dataCat as $key=> $val){
                $RoutingProfileCategoryData = array();
                $RoutingProfileCategoryData['RoutingProfileID'] = $RoutingProfiles->RoutingProfileID;
                $RoutingProfileCategoryData['RoutingCategoryID'] = $val;

                if($RoutingProfileCategoryData['RoutingCategoryID'] > 0){

                    $RoutingProfileCategoryData['Order'] = (int)@$orderingCnt[$key];
                    $RoutingProfileCategory = RoutingProfileCategory::create($RoutingProfileCategoryData);
                    RoutingProfileCategory::clearCache();
                }
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
            $data['Status'] = isset($data['Status']) ? 1 : 0;
            $RoutingProfiles = RoutingProfiles::findOrFail($id);
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            $data["UpdatedBy"] = User::get_user_full_name();
            $rules = array(
                'Name' => 'required',
                //'RoutingPolicy' => 'required',
                'RoutingCategory' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['RoutingProfileID']);
            if ($RoutingProfiles->update($data)) {
                RoutingProfiles::clearCache();

                //Delete Old Data
                RoutingProfileCategory::where(array('RoutingProfileID'=>$id))->delete();

                //Add Routing profile cate
                $orderingCnt= Input::get('Orders');
                $dataCat = array_unique($data['RoutingCategory']);
                foreach($dataCat as $key=> $val){
                    $RoutingProfileCategoryData = array();
                    $RoutingProfileCategoryData['RoutingProfileID'] = $id;
                    $RoutingProfileCategoryData['RoutingCategoryID'] = $val;
                    if($RoutingProfileCategoryData['RoutingCategoryID'] > 0){
                        $RoutingProfileCategoryData['Order'] = $orderingCnt[$key];
                        $RoutingProfileCategory = RoutingProfileCategory::create($RoutingProfileCategoryData);
                        RoutingProfileCategory::clearCache();
                    }
                }
                //-
                return Response::json(array("status" => "success", "message" => "Routing Profile Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Routing Profile."));
            }
        } else {
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
                    try {
                        $result = RoutingProfiles::find($id)->delete();
                        RoutingProfiles::clearCache();
                        if ($result) {
                            $results = RoutingProfileCategory::where(array('RoutingProfileID'=>$id))->delete();
                            RoutingProfileCategory::clearCache();
                            return Response::json(array("status" => "success", "message" => "Routing Profiles Successfully Deleted"));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Deleting Routing Profiles."));
                        }
                    } catch (Exception $ex){
                        return Response::json(array("status" => "failed", "message" => "Routing Profiles is in Use, You cant delete this Routing Profiles."));
                    }
                } else {
                    return Response::json(array("status" => "failed", "message" => "Routing Profiles is in Use, You cant delete this Routing Profiles."));
                }
            }
	}
    public function exports($type)
    {
        $CompanyID = User::get_companyID();
        $RoutingProfiles = RoutingProfiles::Join('tblRoutingProfileCategory','tblRoutingProfileCategory.RoutingProfileID','=','tblRoutingProfile.RoutingProfileID')
            ->select(['tblRoutingProfile.Name','tblRoutingProfile.Description','tblRoutingProfile.SelectionCode as SelectionCode','tblRoutingProfile.Status',DB::raw("(select GROUP_CONCAT(tblRoutingCategory.Name SEPARATOR ', ' ) as RoutesCategory from tblRoutingCategory where tblRoutingCategory.RoutingCategoryID in (select tblRoutingProfileCategory.RoutingCategoryID from tblRoutingProfileCategory where tblRoutingProfileCategory.RoutingProfileID = tblRoutingProfile.RoutingProfileID)) as 'Routing Category'")])
            ->where(["tblRoutingProfile.CompanyID" => $CompanyID])->groupBy("tblRoutingProfile.RoutingProfileID");


        $data = Input::all();
        if(!empty($data['Name'])){
            $RoutingProfiles->where(["tblRoutingProfile.Name" => $data['Name']]);
        }
        if(!empty($data['Status']) || ($data['Status']=='0')){
            $RoutingProfiles->where(["tblRoutingProfile.Status" => $data['Status']]);
        }
        $RoutingProfiles = $RoutingProfiles->orderBy('tblRoutingProfile.Name')->get();
        $RoutingProfiles = json_decode(json_encode($RoutingProfiles),true);
        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RoutingProfile.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($RoutingProfiles);
        } elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RoutingProfile.xls';
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
    
    public function ajaxfetch()
    {
        $id = Input::all();
        $result =  RoutingCategory::select('Name','RoutingCategoryID','Description')->where('RoutingCategoryID',$id )->first();
        return $result;
    }

    public function ajaxedit(){
        $id = Input::all();
        $RoutingProfileCategory = RoutingProfileCategory::select('RoutingCategoryID')->where('RoutingProfileID', $id )->get();
        $result = array();         
        $RoutingCategory = RoutingCategory::Join('tblRoutingProfileCategory', function($join) {
            $join->on('tblRoutingCategory.RoutingCategoryID','=','tblRoutingProfileCategory.RoutingCategoryID');
            })->select('tblRoutingProfileCategory.Order','tblRoutingCategory.Name','tblRoutingCategory.RoutingCategoryID','tblRoutingCategory.Description')->where('tblRoutingProfileCategory.RoutingProfileID',$id)
            ->orderBy('tblRoutingProfileCategory.Order')->get();
        array_push($result, $RoutingCategory);             
        return $result; 
    }

    public function ajax_categories(){
       $Categories = RoutingCategory::select('Name','RoutingCategoryID','Description')->orderBy('Name')->get();
       return $Categories;
    }
}