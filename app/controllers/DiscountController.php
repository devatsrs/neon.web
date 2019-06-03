<?php


class DiscountController extends \BaseController
{


    public function index()
    {
        //git pull test
        $currencies = Currency::getCurrencyDropdownIDList();
        $DestinationGroupSets = DestinationGroupSet::getDropdownIDList();
        return View::make('discountplan.index', compact('currencies', 'DestinationGroupSets'));
    }

    public function DataGrid($post_data)
    {

        try {
            $CompanyID = User::get_companyID();
            $rules['iDisplayStart'] = 'required|Min:1';
            $rules['iDisplayLength'] = 'required';
            $rules['iDisplayLength'] = 'required';
            $rules['sSortDir_0'] = 'required';
            $validator = Validator::make($post_data, $rules);
            if ($validator->fails()) {
                return generateResponse($validator->errors(), true);
            }
            $post_data['iDisplayStart'] += 1;
            $columns = ['Name', 'CreatedBy', 'created_at'];
            $Name = $CodedeckID = '';
            if (isset($post_data['Name'])) {
                $Name = $post_data['Name'];
            }
            $DestinationGroupSetID = '';
            if (isset($post_data['DestinationGroupSetID'])) {
                $DestinationGroupSetID = $post_data['DestinationGroupSetID'];
            }
            $sort_column = $columns[$post_data['iSortCol_0']];
            $query = "call prc_getDiscountPlan(" . $CompanyID . ",'" . $Name . "'," . (ceil($post_data['iDisplayStart'] / $post_data['iDisplayLength'])) . " ," . $post_data['iDisplayLength'] . ",'" . $sort_column . "','" . $post_data['sSortDir_0'] . "'" . ",'" . $DestinationGroupSetID . "'";
            if (isset($post_data['Export']) && $post_data['Export'] == 1) {
                Log::info("DataGrid Query" . ($query . ',1)'));
                $result = DB::select($query . ',1)');
            } else {
                $query .= ',0)';
                Log::info("DataGrid Query" . ($query));
                $result = DataTableSql::of($query)->make();
            }
            return $result;
        } catch (\Exception $e) {
            Log::info($e);
            return [];
        }
    }

    public function generateResponse($message, $isError = false, $isCustomError = false, $data = [])
    {
        $status = 'success';
        if ($isError) {
            if ($isCustomError) {
                $message = ["error" => [$message]];
            }
            $status = 'failed';
        }
        $reponse_data = ['status' => $status, 'message' => $message];
        if (count($data) > 0) {
            $reponse_data['data'] = $data;
        }
        return $reponse_data;
        //return \Dingo\Api\Facade\API::response()->array($reponse_data)->statusCode(200);
    }

    public function ajax_datagrid()
    {
        $getdata = Input::all();
        $response = $this->DataGrid($getdata);
        if (isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Discount Plan', function ($excel) use ($excel_data) {
                $excel->sheet('Discount Plan', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return $response;
    }

    public function discountExports($type){
        $post_data = Input::all();
        $CompanyID = User::get_companyID();
        $result = [];
        try {

            $post_data['iDisplayStart'] += 1;
            $columns = ['Name', 'CreatedBy', 'created_at'];
            $Name = $CodedeckID = '';
            if (isset($post_data['Name'])) {
                $Name = $post_data['Name'];
            }
            if (isset($post_data['CodedeckID'])) {
                $CodedeckID = $post_data['CodedeckID'];
            }
            $sort_column = $columns[$post_data['iSortCol_0']];
            $query = "call prc_getDiscount(" . $CompanyID . ",'" . intval($post_data['DiscountPlanID']) . "','" . $Name . "'," . (ceil($post_data['iDisplayStart'] / $post_data['iDisplayLength'])) . " ," . $post_data['iDisplayLength'] . ",'" . $sort_column . "','" . $post_data['sSortDir_0'] . "'";

                $result = DB::select($query . ',1)');

        } catch (\Exception $e) {
            Log::info($e);
            return [];
        }


        $response = json_decode(json_encode($result),true);
        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Discount.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($response);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Discount.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($response);
        }

    }
    public function exports($type){
        $post_data = Input::all();

            $CompanyID = User::get_companyID();

            $Name = $CodedeckID = '';
            if (isset($post_data['Name'])) {
                $Name = $post_data['Name'];
            }
            $DestinationGroupSetID = '';
            if (isset($post_data['DestinationGroupSetID'])) {
                $DestinationGroupSetID = $post_data['DestinationGroupSetID'];
            }
            $sort_column = $post_data['iSortCol_0'];

        $exportSelectedTemplate = DiscountPlan::
        Join('tblDestinationGroupSet','tblDestinationGroupSet.DestinationGroupSetID','=','tblDiscountPlan.DestinationGroupSetID')
            //->Join('tblCurrency','tblServiceTemplate.CurrencyId','=','tblCurrency.CurrencyId')
            ->select(['tblDiscountPlan.Name','tblDestinationGroupSet.Name as DestinationGroupSet',
            'tblDiscountPlan.UpdatedBy','tblDiscountPlan.updated_at'])
            ->orderBy("tblDiscountPlan.Name", "ASC");

        if($Name != ''){
            $exportSelectedTemplate->where('tblDiscountPlan.Name','like','%'.$Name.'%');
        }
        if($DestinationGroupSetID != ''){
            $exportSelectedTemplate->where(["tblDestinationGroupSet.DestinationGroupSetID"=>$DestinationGroupSetID]);
        }

        $exportSelectedTemplate->where(["tblDiscountPlan.CompanyID"=>$CompanyID]);
        Log::info('$exportSelectedTemplate query.' . $exportSelectedTemplate->toSql());
        $exportSelectedTemplate = $exportSelectedTemplate->get();


        $response = json_decode(json_encode($exportSelectedTemplate),true);
        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DiscountPlan.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($response);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DiscountPlan.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($response);
        }

    }

    public function store()
    {

        $post_data = Input::all();
        $CompanyID = User::get_companyID();
        $date = date('Y-m-d H:i:s');

        $rules['Name'] = 'required|unique:tblDiscountPlan,Name,NULL,CompanyID,CompanyID,' . $CompanyID;
        $rules['DestinationGroupSetID'] = 'required|numeric';


        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        try {
            $insertdata = array();
            foreach ($rules as $columnname => $column) {
                $insertdata[$columnname] = $post_data[$columnname];
            }
            if (isset($post_data['Description'])) {
                $insertdata['Description'] = $post_data['Description'];
            }
            $insertdata['CompanyID'] = $CompanyID;
            $insertdata['CreatedBy'] = User::get_user_full_name();
            $insertdata['created_at'] = $date;
            $DiscountPlan = DiscountPlan::create($insertdata);
            return Response::json(array("status" => "success", "message" => "Discount Plan added successfully"));
        } catch (\Exception $e) {
            Log::info($e);
            return Response::json(array("status" => "failed", "message" => "Problem Creating Service"));
        }
    }

    public function PackageDiscountPlan() {

        $data       = Input::all();
        $companyID  = User::get_companyID();
        $PackageId = isset($data['PackageId']) ? $data['PackageId'] : "0";
        $AccountID = isset($data['AccountID']) ? $data['AccountID'] : "0";
        $accountInfo = Account::where(["AccountID" => $AccountID])->first();
        $companyID = $accountInfo->CompanyId;
        $AccountServiceID = isset($data['AccountServiceID']) ? $data['AccountServiceID'] : "0";
        $AccountServicePackageID = isset($data['AccountServicePackageID']) ? $data['AccountServicePackageID'] : "0";

        Log::info("PackageDiscountPlan Data" . print_r($data,true));

        if(!empty($PackageId)) {
            $query = 'call prc_getPackageDiscountPlan (' . $companyID .",'" .
                $AccountID . "','" . $AccountServiceID. "','" . $PackageId. "'," . $AccountServicePackageID . ')';
            Log::info("PackageDiscountPlan Query:" . $query);
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";

            $response['data']       = DB::select($query);

        } else {
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";
            $response['data']       = [];
        }

        return json_encode($response);
    }
    public function TerminationDiscountPlan() {

        $data       = Input::all();
        $companyID  = User::get_companyID();
        $TerminationDicountPlan = isset($data['TerminationDicountPlan']) ? $data['TerminationDicountPlan'] : "";
        $AccountID = isset($data['AccountID']) ? $data['AccountID'] : "0";
        $accountInfo = Account::where(["AccountID" => $AccountID])->first();
        $companyID = $accountInfo->CompanyId;
        $AccountServiceID = isset($data['AccountServiceID']) ? $data['AccountServiceID'] : "0";
        $CLIRateTableID = isset($data['CLIRateTableID']) ? $data['CLIRateTableID'] : '0';

        Log::info("TerminationDiscountPlan Data" . print_r($data,true));

        if(!empty($TerminationDicountPlan)) {
            $query = 'call prc_getTerminationDiscountPlan (' . $companyID .",'" .
                $AccountID . "','" . $AccountServiceID. "','" . $TerminationDicountPlan. "'," . $CLIRateTableID . ')';
            Log::info("TerminationDiscountPlan Query:" . $query);
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";

            $response['data']       = DB::select($query);

        } else {
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";
            $response['data']       = [];
        }

        return json_encode($response);
    }
    public function AccessDiscountPlan() {

        $data       = Input::all();
        $companyID  = User::get_companyID();
        $AccessDicountPlan = isset($data['AccessDicountPlan']) ? $data['AccessDicountPlan'] : "";
        $AccountID = isset($data['AccountID']) ? $data['AccountID'] : "0";
        $accountInfo = Account::where(["AccountID" => $AccountID])->first();
        $companyID = $accountInfo->CompanyId;
        $AccountServiceID = isset($data['AccountServiceID']) ? $data['AccountServiceID'] : "0";
        $CLIRateTableID = isset($data['CLIRateTableID']) ? $data['CLIRateTableID'] : "0";

        Log::info("AccessDiscountPlan Data" . print_r($data,true));

        if(!empty($AccessDicountPlan)) {
            $query = 'call prc_getAccessDiscountPlan (' . $companyID .",'" .
                $AccountID . "','" . $AccountServiceID. "','" . $AccessDicountPlan. "'," . $CLIRateTableID . ')';
            Log::info("AccessDiscountPlan Query:" . $query);
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";

            $response['data']       = DB::select($query);

        } else {
            $response['status']     = "success";
            $response['message']    = "Data fetched successfully!";
            $response['data']       = [];
        }

        return json_encode($response);
    }

    public function delete($id)
    {
        try {
            if (intval($id) > 0) {
                if (!DiscountPlan::checkForeignKeyById($id)) {
                    try {
                        DB::beginTransaction();
                        DiscountScheme::join('tblDiscount','tblDiscountScheme.DiscountID','=','tblDiscount.DiscountID')->where('DiscountPlanID',$id)->delete();
                        Discount::where("DiscountPlanID",$id)->delete();
                        $result = DiscountPlan::find($id)->delete();
                        DB::commit();
                        if ($result) {
                            return Response::json(array("status" => "success", "message" => "Discount Plan deleted successfully"));
                        } else {

                            return Response::json(array("status" => "failed", "message" => "Problem deleting discount plan"));
                        }
                    } catch (\Exception $ex) {
                        Log::info($ex);
                        try {
                            DB::rollback();
                        } catch (\Exception $err) {
                            Log::error($err);
                        }
                        return Response::json(array("status" => "failed", "message" => "Problem deleting Discount plan"));
                    }
                } else {

                    return Response::json(array("status" => "failed", "message" => "Discount Plan is in use"));
                }
            } else {

                return Response::json(array("status" => "failed", "message" => "Problem deleting Discount plan"));
            }
        } catch (\Exception $e) {

            Log::info($e);
            return Response::json(array("status" => "failed", "message" => "Problem deleting discount plan"));
        }
    }

    public function update($id)
    {
        if ($id > 0) {
            $post_data = Input::all();
            $CompanyID = User::get_companyID();

            $rules['Name'] = 'required|unique:tblDiscountPlan,Name,' . $id . ',DiscountPlanID,CompanyID,' . $CompanyID;
            $rules['DestinationGroupSetID'] = 'required|numeric';

            $validator = Validator::make($post_data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            try {
                try {
                    $DiscountPlan = DiscountPlan::findOrFail($id);
                } catch (\Exception $e) {
                    return Response::json(array("status" => "failed", "message" => "Discount Plan not found"));

                }
                $updatedata = array();

                $updatedata['Name'] = $post_data['Name'];

                if (isset($post_data['Description'])) {
                    $updatedata['Description'] = $post_data['Description'];
                }
                $updatedata['UpdatedBy'] = User::get_user_full_name();
                $DiscountPlan->update($updatedata);
                return Response::json(array("status" => "success", "message" => "Discount Plan updated successfully"));
            } catch (\Exception $e) {
                Log::info($e);
                return Response::json(array("status" => "failed", "message" => "Discount Plan not updated"));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Discount Plan not updated"));
        }

    }

    public function show($id)
    {
        $currencies = Currency::getCurrencyDropdownIDList();
        $DestinationGroupSetID = DiscountPlan::where(array('DiscountPlanID' => $id))->pluck('DestinationGroupSetID');
        $DestinationGroupSetRateType = DestinationGroupSet::where(array('DestinationGroupSetID' => $DestinationGroupSetID))->pluck('RateTypeID');
        $DestinationGroup = DestinationGroup::getDropdownIDList($DestinationGroupSetID);
        $DiscountPlanComponents = DiscountPlan::exludedCompnents($DestinationGroupSetRateType);
        $name = DiscountPlan::getName($id);
        $discountplanapplied = DiscountPlan::isDiscountPlanApplied('DiscountPlan', 0, $id);
        return View::make('discountplan.show', compact('currencies', 'DestinationGroup', 'id', 'name', 'discountplanapplied','DiscountPlanComponents'));
    }

    public function discount_ajax_datagrid()
    {
        $post_data = Input::all();
        $CompanyID = User::get_companyID();
        $result = [];
        try {

            $post_data['iDisplayStart'] += 1;
            $columns = ['Name', 'CreatedBy', 'created_at'];
            $Name = $CodedeckID = '';
            if (isset($post_data['Name'])) {
                $Name = $post_data['Name'];
            }
            if (isset($post_data['CodedeckID'])) {
                $CodedeckID = $post_data['CodedeckID'];
            }
            $sort_column = $columns[$post_data['iSortCol_0']];
            $query = "call prc_getDiscount(" . $CompanyID . ",'" . intval($post_data['DiscountPlanID']) . "','" . $Name . "'," . (ceil($post_data['iDisplayStart'] / $post_data['iDisplayLength'])) . " ," . $post_data['iDisplayLength'] . ",'" . $sort_column . "','" . $post_data['sSortDir_0'] . "'";
            if (isset($post_data['Export']) && $post_data['Export'] == 1) {
                $result = DB::select($query . ',1)');
            } else {
                $query .= ',0)';
                $result = DataTableSql::of($query)->make();
            }
        } catch (\Exception $e) {
            Log::info($e);
            return [];
        }


        if (isset($post_data['Export']) && $post_data['Export'] == 1) {
            $excel_data = $result;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Discount Plan', function ($excel) use ($excel_data) {
                $excel->sheet('Discount Plan', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return $result;
    }

    public function TerminationDiscountPlanCodes()
    {
        $post_data = Input::all();
        $post_data['iDisplayStart'] += 1;
        $CompanyID = User::get_companyID();
        Log::info("TerminationDiscountPlanCodes " . print_r($post_data,true));
        $AccountID = isset($post_data['AccountID']) ? $post_data['AccountID'] : "0";
        $accountInfo = Account::where(["AccountID" => $AccountID])->first();
        $companyID = $accountInfo->CompanyId;
        $result = [];
        $RateCodeID = isset($post_data['RateCodeID']) ? $post_data['RateCodeID'] : '';



            $query = "call prc_getTerminationCodes(" . $CompanyID . ",'" . $post_data['AccountID'] . "','" . $post_data['AccountServiceID'] . "','" . $post_data['TerminationDiscountPlanName'] . "' ,'" . $RateCodeID . "',"
                . (ceil($post_data['iDisplayStart'] / $post_data['iDisplayLength'])) . " ," . $post_data['iDisplayLength'].
                ")";
        Log::info("TerminationDiscountPlanCodes " . $query);
                return DataTableSql::of($query)->make();


    }

    public function discount_store()
    {
        $post_data = Input::all();
        $date = date('Y-m-d H:i:s');
        $createdBy = User::get_user_full_name();
        Log::info('discount_store ' . print_r($post_data, true));
        $arrayId = '';
        try {
            $minutesComponents = isset($post_data['getIDs']) ? $post_data['getIDs'] : '';
            $volumeComponents = isset($post_data['getVolumeIDs']) ? $post_data['getVolumeIDs'] : '';
            $fixedComponents = isset($post_data['getFixedIDs']) ? $post_data['getFixedIDs'] : '';
            $DiscountValueError = array();

                $rules['Service'] = 'required';
                $rules['DestinationGroupID'] = 'required';
                $messages = array(
                    'DestinationGroupID.required' =>'Product Group ID is required',
                    'Service.required' =>'Discount Type is required',
                );
                $validator = Validator::make($post_data, $rules,$messages);
                if ($validator->fails()) {
                    return json_validator_response($validator);
                }


            if ($minutesComponents != '' && $post_data['Service'] == 2) {
                $rules['Service'] = 'required';
                $rules['DestinationGroupID'] = 'required';
                $messages = array(
                    'DestinationGroupID.required' =>'Product Group ID is required',
                    'Service.required' =>'Discount Type is required',
                );

                $minutesComponentsList = explode(",", $minutesComponents);
                Log::info('discount_store count' . count($minutesComponentsList));
                for ($k = 0; $k < count($minutesComponentsList) - 1; $k++) {
                    $rules['MinutesComponent-' . ($k + 1)] = 'required';
                    $messages['MinutesComponent-' . ($k + 1).'.required'] = "Component value for the Row " . ($k + 1) . " required";
                    $rules['MinutesDiscount-' . ($k + 1)] = 'required';
                    $messages['MinutesDiscount-' . ($k + 1).'.required'] = "Discount value for the Row " . ($k + 1) . " required";

                }


                $validator = Validator::make($post_data, $rules,$messages);
                if ($validator->fails()) {
                    return json_validator_response($validator,$DiscountValueError);
                }
            }



            if ($volumeComponents != '' && $post_data['Service'] == 1) {
                $rules['Service'] = 'required';
                $rules['DestinationGroupID'] = 'required';
                $messages = array(
                    'DestinationGroupID.required' =>'Product Group ID is required',
                    'Service.required' =>'Discount Type is required',
                );

                //VolumeDiscount- VolumeFromMin- VolumeToMin- VolumeComponent-
                $volumeComponentsList = explode(",", $volumeComponents);
                Log::info('discount_store count' . count($volumeComponentsList));
                for ($k = 0; $k < count($volumeComponentsList) - 1; $k++) {
                    $rules['VolumeComponent-' . ($k + 1)] = 'required';
                    $messages['VolumeComponent-' . ($k + 1).'.required'] = "Component for the Row " . ($k + 1) . " required";
                    $rules['VolumeDiscount-' . ($k + 1)] = 'required';
                    $messages['VolumeDiscount-' . ($k + 1).'.required'] = "Discount for the Row " . ($k + 1) . " required";
                    $rules['VolumeFromMin-' . ($k + 1)] = 'required';
                    $messages['VolumeFromMin-' . ($k + 1).'.required'] = "FromMin for the Row " . ($k + 1) . " required";
                    $rules['VolumeToMin-' . ($k + 1)] = 'required';
                    $messages['VolumeToMin-' . ($k + 1).'.required'] = "ToMin for the Row " . ($k + 1) . " required";
                    if (!empty($post_data['VolumeFromMin-' . ($k + 1)])) {
                        if (!($post_data['VolumeFromMin-' . ($k + 1)] == '>' ||
                            $post_data['VolumeFromMin-' . ($k + 1)] == '<' || is_numeric($post_data['VolumeFromMin-' . ($k + 1)])))
                            array_push($DiscountValueError, "From value for the Row " . ($k + 1) . " must be either number or < or <");
                    }
                }

                $validator = Validator::make($post_data, $rules,$messages);
                if ($validator->fails() || count($DiscountValueError) > 0) {
                    return json_validator_responseWithCustom($validator,$DiscountValueError);
                }

            }

            if ($fixedComponents != ''&& $post_data['Service'] == 3) {
                $rules['Service'] = 'required';
                $rules['DestinationGroupID'] = 'required';
                $messages = array(
                    'DestinationGroupID.required' => 'Product Group ID is required',
                    'Service.required' =>'Discount Type is required',
                );
                $fixedComponentsList = explode(",", $fixedComponents);
                Log::info('discount_store ' . count($fixedComponentsList));
                for ($k = 0; $k < count($fixedComponentsList) - 1; $k++) {
                    $rules['FixedComponent-' . ($k + 1)] = 'required';
                    $messages['FixedComponent-' . ($k + 1).'.required'] = "Component for the Row " . ($k + 1) . " required";
                    $rules['FixedDiscount-' . ($k + 1)] = 'required';
                    $messages['FixedDiscount-' . ($k + 1).'.required'] = "Discount for the Row " . ($k + 1) . " required";
                }
                $validator = Validator::make($post_data, $rules,$messages);
                if ($validator->fails()) {
                    return json_validator_response($validator);
                }
            }
            DB::beginTransaction();
            if ($minutesComponents != ''&& $post_data['Service'] == 2) {
                $minutesComponentsList = explode(",", $minutesComponents);
                Log::info('discount_store count' . count($minutesComponentsList));
                $discountdata = array();
                $discountdata['DestinationGroupID'] = $post_data['DestinationGroupID'];
                $discountdata['DiscountPlanID'] = $post_data['DiscountPlanID'];

                $discountdata['Service'] = '2';
                $discountdata['CreatedBy'] = $createdBy;
                $discountdata['created_at'] = $date;
                $Discount = Discount::create($discountdata);
                for ($k = 0; $k < count($minutesComponentsList) - 1; $k++) {
                    $arrayId = $minutesComponentsList[$k];
                    $discountschemedata = array();
                    $discountschemedata['Discount'] = $post_data['MinutesDiscount-' . $arrayId];
                    $discountschemedata['Threshold'] = $post_data['MinutesTreshhold-' . $arrayId];
                    $discountschemedata['DiscountID'] = $Discount->DiscountID;
                    $discountschemedata['Unlimited'] = $post_data['MinutesUnlimited-' . $arrayId];
                    $discountschemedata['Components'] = implode(",", $post_data['MinutesComponent-' . $arrayId]);
                    $discountschemedata['CreatedBy'] = $createdBy;
                    $discountschemedata['created_at'] = $date;
                    DiscountScheme::create($discountschemedata);
                }
            }
            if ($volumeComponents != ''&& $post_data['Service'] == 1) {
                $volumeComponentsList = explode(",", $volumeComponents);
                Log::info('discount_store ' . count($volumeComponentsList));
                $discountdata = array();
                $discountdata['DestinationGroupID'] = $post_data['DestinationGroupID'];
                $discountdata['DiscountPlanID'] = $post_data['DiscountPlanID'];

                $discountdata['Service'] = '1';
                $discountdata['CreatedBy'] = $createdBy;
                $discountdata['created_at'] = $date;
                Log::info('discount_store ' . print_r($discountdata, true));
                $Discount = Discount::create($discountdata);
                for ($k = 0; $k < count($volumeComponentsList) - 1; $k++) {
                    $arrayId = $volumeComponentsList[$k];
                    $discountschemedata = array();
                    $discountschemedata['Discount'] = $post_data['VolumeDiscount-' . $arrayId];
                    $discountschemedata['FromMin'] = $post_data['VolumeFromMin-' . $arrayId];
                    $discountschemedata['DiscountID'] = $Discount->DiscountID;
                    $discountschemedata['ToMin'] = $post_data['VolumeToMin-' . $arrayId];
                    $discountschemedata['Components'] = implode(",", $post_data['VolumeComponent-' . $arrayId]);
                    $discountschemedata['CreatedBy'] = $createdBy;
                    $discountschemedata['created_at'] = $date;
                    DiscountScheme::create($discountschemedata);
                }
            }

            if ($fixedComponents != ''&& $post_data['Service'] == 3) {
                $fixedComponentsList = explode(",", $fixedComponents);
                Log::info('discount_store ' . count($fixedComponentsList));
                $discountdata = array();
                $discountdata['DestinationGroupID'] = $post_data['DestinationGroupID'];
                $discountdata['DiscountPlanID'] = $post_data['DiscountPlanID'];

                $discountdata['Service'] = '3';
                $discountdata['CreatedBy'] = $createdBy;
                $discountdata['created_at'] = $date;
                $Discount = Discount::create($discountdata);
                for ($k = 0; $k < count($fixedComponentsList) - 1; $k++) {
                    $arrayId = $fixedComponentsList[$k];
                    $discountschemedata = array();
                    $discountschemedata['Discount'] = $post_data['FixedDiscount-' . $arrayId];
                    $discountschemedata['DiscountID'] = $Discount->DiscountID;
                    $discountschemedata['Components'] = implode(",", $post_data['FixedComponent-' . $arrayId]);
                    $discountschemedata['CreatedBy'] = $createdBy;
                    $discountschemedata['created_at'] = $date;
                    DiscountScheme::create($discountschemedata);
                }
            }
            DB::commit();
            return Response::json(array("status" => "success", "message" => "Discount Plan updated successfully"));
        } catch
        (\Exception $e) {
            Log::info($e);
            DB::rollback();
            return Response::json(array("status" => "failed", "message" => "Discount Plan not updated"));
        }

    }

    public function discount_delete($id)
    {
        try {

            if (!Discount::checkForeignKeyById($id)) {
                try {
                    DB::beginTransaction();
                    $result = DiscountScheme::where('DiscountID', $id)->delete();
                    $result = Discount::find($id)->delete();
                    DB::commit();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Discount Plan Deleted successfully"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Discount"));
                    }
                } catch (\Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Discount"));
                }
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Discount"));
            }

        } catch (\Exception $e) {
            Log::info($e);
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Discount"));
        }
    }

    public function getDiscountScheme()
    {
        try {
            $post_data = Input::all();
            Log::info('getDiscountScheme ' . print_r($post_data, true));
            $DiscountScheme = DiscountScheme::where('DiscountID', $post_data['DiscountID'])->get();
            return Response::json(array("status" => "success", "message" => $DiscountScheme));
        } catch (\Exception $e) {
            Log::info($e);
            return Response::json(array("status" => "failed", "message" => "Internal Server Error"));
        }
    }

    public function discount_update($id)
    {
        {
            $post_data = Input::all();
            $date = date('Y-m-d H:i:s');
            $createdBy = User::get_user_full_name();
            Log::info('discount_store ' . print_r($post_data, true));
            $arrayId = '';
            $DiscountValueError = array();
            try {
                $minutesComponents = isset($post_data['getIDs']) ? $post_data['getIDs'] : '';
                $volumeComponents = isset($post_data['getVolumeIDs']) ? $post_data['getVolumeIDs'] : '';
                $fixedComponents = isset($post_data['getFixedIDs']) ? $post_data['getFixedIDs'] : '';

                if ($minutesComponents != '') {

                    $rules['DestinationGroupID'] = 'required';
                    $messages = array(
                        'DestinationGroupID.required' =>'Product Group ID is required',

                    );

                    $minutesComponentsList = explode(",", $minutesComponents);
                    Log::info('discount_store count' . count($minutesComponentsList));
                    for ($k = 0; $k < count($minutesComponentsList) - 1; $k++) {
                        $rules['MinutesComponent-' . ($k + 1)] = 'required';
                        $messages['MinutesComponent-' . ($k + 1).'.required'] = "Component value for the Row " . ($k + 1) . " required";
                        $rules['MinutesDiscount-' . ($k + 1)] = 'required';
                        $messages['MinutesDiscount-' . ($k + 1).'.required'] = "Discount value for the Row " . ($k + 1) . " required";
                    }

                    $validator = Validator::make($post_data, $rules,$messages);
                    if ($validator->fails()) {
                        return json_validator_response($validator);
                    }
                }



                if ($volumeComponents != '') {

                    $rules['DestinationGroupID'] = 'required';
                    $messages = array(
                        'DestinationGroupID.required' =>'Product Group ID is required',
                    );

                    //VolumeDiscount- VolumeFromMin- VolumeToMin- VolumeComponent-
                    $volumeComponentsList = explode(",", $volumeComponents);
                    Log::info('discount_store count' . count($volumeComponentsList));
                    for ($k = 0; $k < count($volumeComponentsList) - 1; $k++) {
                        $rules['VolumeComponent-' . ($k + 1)] = 'required';
                        $messages['VolumeComponent-' . ($k + 1).'.required'] = "Component for the Row " . ($k + 1) . " required";
                        $rules['VolumeDiscount-' . ($k + 1)] = 'required';
                        $messages['VolumeDiscount-' . ($k + 1).'.required'] = "Discount for the Row " . ($k + 1) . " required";
                        $rules['VolumeFromMin-' . ($k + 1)] = 'required';
                        $messages['VolumeFromMin-' . ($k + 1).'.required'] = "FromMin for the Row " . ($k + 1) . " required";
                        $rules['VolumeToMin-' . ($k + 1)] = 'required';
                        $messages['VolumeToMin-' . ($k + 1).'.required'] = "ToMin for the Row " . ($k + 1) . " required";

                        if (!empty($post_data['VolumeFromMin-' . ($k + 1)])) {
                            if (!($post_data['VolumeFromMin-' . ($k + 1)] == '>' ||
                                $post_data['VolumeFromMin-' . ($k + 1)] == '<' || is_numeric($post_data['VolumeFromMin-' . ($k + 1)])))
                                array_push($DiscountValueError, "From value for the Row " . ($k + 1) . " must be either number or < or <");
                        }
                    }

                    $validator = Validator::make($post_data, $rules,$messages);
                    if ($validator->fails()|| count($DiscountValueError) > 0) {
                        return json_validator_responseWithCustom($validator,$DiscountValueError);
                    }
                }

                if ($fixedComponents != '') {

                    $rules['DestinationGroupID'] = 'required';
                    $messages = array(
                        'DestinationGroupID.required' => 'Product Group ID is required',
                    );
                    $fixedComponentsList = explode(",", $fixedComponents);
                    Log::info('discount_store ' . count($fixedComponentsList));
                    for ($k = 0; $k < count($fixedComponentsList) - 1; $k++) {
                        $rules['FixedComponent-' . ($k + 1)] = 'required';
                        $messages['FixedComponent-' . ($k + 1).'.required'] = "Component for the Row " . ($k + 1) . " required";
                        $rules['FixedDiscount-' . ($k + 1)] = 'required';
                        $messages['FixedDiscount-' . ($k + 1).'.required'] = "Discount for the Row " . ($k + 1) . " required";
                    }
                    $validator = Validator::make($post_data, $rules,$messages);
                    if ($validator->fails()) {
                        return json_validator_response($validator);
                    }
                }

                DB::beginTransaction();

                if ($minutesComponents != '') {
                    $minutesComponentsList = explode(",", $minutesComponents);
                    Log::info('discount_store count' . count($minutesComponentsList));
                    $discountdata = array();
                    $discountdata['DestinationGroupID'] = $post_data['DestinationGroupID'];
                    $discountdata['DiscountPlanID'] = $post_data['DiscountPlanID'];

                    $discountdata['Service'] = '2';
                    $discountdata['UpdatedBy'] = $createdBy;
                    $discountdata['updated_at'] = $date;
                    $Discount = Discount::find($post_data['DiscountID']);
                    $Discount->update($discountdata);
                    DiscountScheme::where(array('DiscountID'=>$Discount->DiscountID))->delete();
                    for ($k = 0; $k < count($minutesComponentsList) - 1; $k++) {
                        $arrayId = $minutesComponentsList[$k];
                        $discountschemedata = array();
                        $discountschemedata['Discount'] = $post_data['MinutesDiscount-' . $arrayId];
                        $discountschemedata['Threshold'] = $post_data['MinutesTreshhold-' . $arrayId];
                        $discountschemedata['DiscountID'] = $Discount->DiscountID;
                        $discountschemedata['Unlimited'] = $post_data['MinutesUnlimited-' . $arrayId];
                        $discountschemedata['Components'] = implode(",", $post_data['MinutesComponent-' . $arrayId]);
                        $discountschemedata['CreatedBy'] = $createdBy;
                        $discountschemedata['created_at'] = $date;
                        DiscountScheme::create($discountschemedata);
                    }
                }
                if ($volumeComponents != '') {
                    $volumeComponentsList = explode(",", $volumeComponents);
                    Log::info('discount_store ' . count($volumeComponentsList));
                    $discountdata = array();
                    $discountdata['DestinationGroupID'] = $post_data['DestinationGroupID'];
                    $discountdata['DiscountPlanID'] = $post_data['DiscountPlanID'];

                    $discountdata['Service'] = '1';
                    $discountdata['UpdatedBy'] = $createdBy;
                    $discountdata['updated_at'] = $date;
                    $Discount = Discount::find($post_data['DiscountID']);
                    $Discount->update($discountdata);
                    DiscountScheme::where(array('DiscountID'=>$Discount->DiscountID))->delete();
                    for ($k = 0; $k < count($volumeComponentsList) - 1; $k++) {
                        $arrayId = $volumeComponentsList[$k];
                        $discountschemedata = array();
                        $discountschemedata['Discount'] = $post_data['VolumeDiscount-' . $arrayId];
                        $discountschemedata['FromMin'] = $post_data['VolumeFromMin-' . $arrayId];
                        $discountschemedata['DiscountID'] = $Discount->DiscountID;
                        $discountschemedata['ToMin'] = $post_data['VolumeToMin-' . $arrayId];
                        $discountschemedata['Components'] = implode(",", $post_data['VolumeComponent-' . $arrayId]);
                        $discountschemedata['CreatedBy'] = $createdBy;
                        $discountschemedata['created_at'] = $date;
                        DiscountScheme::create($discountschemedata);
                    }
                }

                if ($fixedComponents != '') {
                    $fixedComponentsList = explode(",", $fixedComponents);
                    Log::info('discount_store ' . count($fixedComponentsList));
                    $discountdata = array();
                    $discountdata['DestinationGroupID'] = $post_data['DestinationGroupID'];
                    $discountdata['DiscountPlanID'] = $post_data['DiscountPlanID'];

                    $discountdata['Service'] = '3';
                    $discountdata['UpdatedBy'] = $createdBy;
                    $discountdata['updated_at'] = $date;
                    $Discount = Discount::find($post_data['DiscountID']);
                    $Discount->update($discountdata);
                    DiscountScheme::where(array('DiscountID'=>$Discount->DiscountID))->delete();
                    for ($k = 0; $k < count($fixedComponentsList) - 1; $k++) {
                        $arrayId = $fixedComponentsList[$k];
                        $discountschemedata = array();
                        $discountschemedata['Discount'] = $post_data['FixedDiscount-' . $arrayId];
                        $discountschemedata['DiscountID'] = $Discount->DiscountID;
                        $discountschemedata['Components'] = implode(",", $post_data['FixedComponent-' . $arrayId]);
                        $discountschemedata['CreatedBy'] = $createdBy;
                        $discountschemedata['created_at'] = $date;
                        DiscountScheme::create($discountschemedata);
                    }
                }

                DB::commit();
                return Response::json(array("status" => "success", "message" => "Discount Plan updated successfully"));
            } catch
            (\Exception $e) {
                Log::info($e);
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => "Discount Plan not updated"));
            }

        }
    }
}