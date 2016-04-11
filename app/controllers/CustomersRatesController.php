<?php

class CustomersRatesController extends \BaseController {

    private $trunks, $trunks_cache, $countries, $rate_sheet_formates;

    public function __construct() {

        $this->countries = Country::getCountryDropdownIDList();
        $this->rate_sheet_formates = RateSheetFormate::getCustomerRateSheetFormatesDropdownList('customer');
    }

    public function search_ajax_datagrid($id,$type) {

        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['Effected_Rates_on_off'] = $data['Effected_Rates_on_off']!= 'true'?0:1;
        $data['Country']=$data['Country']!= ''?$data['Country']:'null';
        $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';

        $columns = array('RateID','Code','Description','Interval1','IntervalN','ConnectionFee','RoutinePlan','Rate','EffectiveDate','LastModifiedDate','LastModifiedBy','CustomerRateId');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetCustomerRate (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['Effected_Rates_on_off'].",'".intval($data['RoutinePlanFilter'])."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";


        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Customer Rates.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Customer Rates.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Customer Rates', function ($excel) use ($excel_data) {
                $excel->sheet('Customer Rates', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';
        //echo $query;exit;
        return DataTableSql::of($query)->make();
    }
    public  function search_customer_grid($id){
        $companyID = User::get_companyID();
        $data = Input::all();
        $opt = $data;
        $opt["CompanyID"] = $companyID;
        $opt["AccountID"] = $id;
        return Account::getCustomersGridPopup($opt);

    }

    public function index($id) {

            $Account = Account::find($id);
            $countries = $this->countries;
            $trunks = CustomerTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            $routine = CustomerTrunk::getRoutineDropdownIDList($id);
            $account_owners = User::getOwnerUsersbyRole();
            $trunks_routing =$trunks;
            $trunks_routing[""] = 'Select a Routing plan';
            if(count($trunks) == 0){
                return  Redirect::to('customers_rates/settings/'.$id)->with('info_message', 'Please enable trunks against customer to setup rates');
            }
        $CurrencySymbol = Currency::getCurrencySymbol($Account->CurrencyId);
            return View::make('customersrates.index', compact('id', 'trunks', 'countries','Account','routine','trunks_routing','account_owners','trunk_keys','CurrencySymbol'));


    }

    public function settings($id) {

            $Account = Account::find($id);
            $company_id = User::get_companyID();
            $data = Input::all();
            $trunks = Trunk::getTrunkCacheObj();

            if (!empty($data)) {



                // $results = DB::select(' prc_GetCustomerRate ?,?,?,?,?,?',array($id,$company_id,$data['Code'],$data['Description'],$data['Trunk'],$data['Country']  ));
                // return View::make('customersrates.index',compact ( 'results' ,'id','trunks','countries'));
            }
            $customer_trunks = CustomerTrunk::getCustomerTrunksByTrunkAsKey($id);
            $codedecklist = BaseCodeDeck::getCodedeckIDList();
            $rate_tables =array();
            $rate_table = RateTable::where(["Status" => 1, "CompanyID" => $company_id,'CurrencyID'=>$Account->CurrencyId])->get();
            foreach($rate_table as $row){
                $rate_tables[$row->TrunkID][$row->CodeDeckId][] = ['text'=>$row->RateTableName,'value'=>$row->RateTableId];
            }
            $companygateway = CompanyGateway::getCompanyGatewayIdList();
            unset($companygateway['']);
            //echo '<pre>';print_r($rate_tables);exit;

            // Debugbar::addMessage($customer_trunks);
            //print_r($customer_trunks);

            return View::make('customersrates.trunks', compact('id', 'trunks', 'customer_trunks','codedecklist','Account','rate_tables','Account','companygateway'));
    }

    public function update_trunks($id) {

        /* ,[RateTableID]
          ,[AccountID]
          ,[Trunk]
          ,[Prefix]
          ,[IncludePrefix]
          ,[Status]
          ,[created_at]
          ,[CreatedBy]
          ,[updated_at]
          ,[ModifiedBy]
         */
        $post_data = Input::all();
        if (!empty($post_data)) {

            $companyID = User::get_companyID();
            foreach ($post_data['CustomerTrunk'] as $trunk => $data) {

                if (isset($data['Status']) && $data['Status'] == 1) {

                    $CustomerTrunk = new CustomerTrunk();

                    $data['AccountID'] = $id;
                    $data['CompanyID'] = $companyID;
                    $data['TrunkID'] = $trunk;

                    //$data['Prefix'] = $Prefix;
                    $data['IncludePrefix'] = isset($data['IncludePrefix']) ? 1 : 0;
                    $data['RoutinePlanStatus'] = isset($data['RoutinePlanStatus']) ? 1 : 0;
                    $data['UseInBilling'] = isset($data['UseInBilling']) ? 1 : 0;

                    //$data['Status'] = $data['Status'];
                    $data['CreatedBy'] = User::get_user_full_name();
                    $data['ModifiedBy'] = !empty($data['CustomerTrunkID']) ? User::get_user_full_name() : '';

                    if (!empty($data['CustomerTrunkID']) && trim($data['Prefix']) == '') {
                        // On Update Validate Prefix
                        return Redirect::back()->with('error_message', "Please Add Prefix for " . $trunk . " Trunk");
                        //return Response::json(array("status" => "failed", "message" => "Please Add Prefix for " . $trunk . " Trunk"));
                    } else if (empty($data['CustomerTrunkID']) && $data['Prefix'] == '') {
                        $data['Prefix'] = $LastPrefixNo = LastPrefixNo::getLastPrefix();
                    }

                    // when no prefix after all above conditions
                    /*if ((int) $data['Prefix'] == 0) {

                        return Redirect::back()->with('error_message', "Please Add Prefix for " . $trunk . " Trunk");
                        //return  Response::json(array("status" => "failed", "message" => "Please Add Prefix for " . $trunk ." Trunk" ));
                    }*/

                    //check if duplicate
                    if (CustomerTrunk::isPrefixExists($data['Prefix'], !empty($data['CustomerTrunkID']) ? $data['CustomerTrunkID'] : '')) {

                        return Redirect::back()->with('error_message', "duplicate Prefix " . $data['Prefix'] . " for " . $trunk . " Trunk");
                        //return  Response::json(array("status" => "failed", "message" => "duplicate Prefix ".$data['Prefix']." for " . $trunk ." Trunk" ));
                    }

                    $rules = array("CodeDeckId"=>"required","AccountID" => "required", "CompanyID" => "required", "TrunkID" => "required", "IncludePrefix" => "required", "Status" => "required",);

                    if (!empty($data['CustomerTrunkID'])) {
                        $rules = array_merge($rules, array("CustomerTrunkID" => "required"));
                    }

                    $validator = Validator::make($data, $rules);

                    if ($validator->fails()) {
                        return Redirect::back()->withInput(Input::all())->withErrors($validator);
                        //return json_validator_response($validator);
                    }

                    if( isset($data['CompanyGatewayID']) && is_array($data['CompanyGatewayID'])){
                        $data['CompanyGatewayIDs'] = implode(',', $data['CompanyGatewayID']);
                        unset($data['CompanyGatewayID']);
                    }else{
                        $data['CompanyGatewayIDs'] = '';
                    }

                    if (isset($data['CustomerTrunkID']) && $data['CustomerTrunkID'] > 0) {
                        $CustomerTrunkID = $data['CustomerTrunkID'];
                        unset($data['CustomerTrunkID']);
                        if((int)CustomerTrunk::find($CustomerTrunkID)->RateTableID != (int)$data['RateTableID'] && $data['RateTableID']>0){
                            CustomerTrunk::find($CustomerTrunkID)->update(array('RateTableAssignDate'=>date('Y-m-d')));
                        }
                        $CustomerTrunk = CustomerTrunk::find($CustomerTrunkID)->update($data);
                    } else {
                        unset($data['CustomerTrunkID']);
                        if ($CustomerTrunk->insert($data)) {
                            if(isset($LastPrefixNo)){
 								//Update last prefix no.
                                LastPrefixNo::updateLastPrefixNo($LastPrefixNo);
                            }
                        } else {
                            return Redirect::back()->with('error_message', "Problem Creating Customer Trunk for " . $trunk . " Trunk");
                            ///return  Response::json(array("status" => "failed", "message" => "Problem Creating Customer Trunk for " . $trunk ." Trunk" )); // For Ajax
                        }
                    }
                } else {

                    // if Unselect Status = 0
                    if (isset($data['CustomerTrunkID']) && $data['CustomerTrunkID'] > 0) {
                        $CustomerTrunkID = $data['CustomerTrunkID'];
                        CustomerTrunk::find($CustomerTrunkID)->update(['Status' => 0]);
                    }
                }
            }
            //forloop
            //Success
            return Redirect::back()->with('success_message', "Customer Trunk Saved");

            //return  Response::json(array("status" => "success", "message" => "Customer Trunk Saved")); for AJAX
        }
    }

    public function download($id) {

            $Account = Account::find($id);
            $trunks = CustomerTrunk::getCustomerTrunk($id); //$this->trunks;
            $rate_sheet_formates = $this->rate_sheet_formates;
            $account_owners = User::getOwnerUsersbyRole();
            $emailTemplates = EmailTemplate::getTemplateArray(array('Type'=>EmailTemplate::RATESHEET_TEMPLATE));
            $accounts = Account::getAccountIDList();
            $templateoption = [''=>'Select',1=>'New Create',2=>'Update'];
            $downloadtype = [''=>'Select','xlsx'=>'EXCEL','csv'=>'CSV'];
            $privacy = EmailTemplate::$privacy;
            $type = EmailTemplate::$Type;
            if(count($trunks) == 0){
                return  Redirect::to('customers_rates/settings/'.$id)->with('info_message', 'Please enable trunks against customer to setup rates');
            }

            return View::make('customersrates.download', compact('id', 'trunks', 'rate_sheet_formates','Account','account_owners','emailTemplates','templateoption','privacy','type','accounts','downloadtype'));
    }

    public function process_download($id) {

        if (Request::ajax()) {
            $data = Input::all();
            $test = 0;
            $rules = array('isMerge' => 'required', 'Trunks' => 'required', 'Format' => 'required','filetype'=> 'required');

            if (!isset($data['isMerge'])) {
                $data['isMerge'] = 0;
            }

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if(!empty($data['filetype'])){
                $data['downloadtype'] = $data['filetype'];
                unset($data['filetype']);
            }

            if($data['sendMail'] == 0){
                $data['customer'][] = $id;
                foreach($data['customer'] as $customerID){
                    if((int)$customerID) {
                        //Inserting Job Log
                        try {
                            DB::beginTransaction();
                            $data['AccountID'] = $customerID;
                            $result = Job::logJob("CD", $data);
                            if ($result['status'] != "success") {
                                DB::rollback();
                                $json_result = json_encode(["status" => "failed", "message" => $result['message']]);
                            }
                            DB::commit();
                            $json_result = json_encode(["status" => "success", "message" => "File is added to queue for processing. You will be notified once file creation is completed. "]);
                        } catch (Exception $ex) {
                            DB::rollback();
                            $json_result = json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
                        }
                    }
                }
                return $json_result;
            }else {
                if (isset($data['test']) && $data['test'] == 1) {
                    $test = 1;
                }
                if ($test == 0) {
                    $data['customer'][] = $id;
                    $data['SelectedIDs'] = implode(',', $data['customer']);
                }
                unset($data['customer']);
                unset($data['account_owners']);
                unset($data['Type']);

                $type = $data['type'];
                return bulk_mail($type, $data);
            }
        } else {
            echo json_encode(array("status" => "failed", "message" => "Access not allowed"));
        }
    }

    /**
     * Update the specified resource in storage.
     * PUT /customersrates/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        $data = Input::all();

        $rules = array('EffectiveDate' => 'required','Rate' => 'required|numeric', 'RateID' => 'required', 'Trunk' => 'required|numeric','Interval1' => 'required|numeric','IntervalN' => 'required|numeric');

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $username = User::get_user_full_name();
        $company_id = User::get_companyID();

        /* @AccountId
          ,@RateId
          ,@Trunk
          ,@Rate
          ,@EffectiveDate
          ,@username */

        $data['EffectiveDate'] = date("Y-m-d", strtotime($data['EffectiveDate']));

        $results = DB::statement("call prc_CustomerRateUpdateBySelectedRateId (".$company_id.",'".$id."','".$data['RateID']."',".$data['Trunk'].",".$data['Rate'].",".floatval($data['ConnectionFee']).",'".$data['EffectiveDate']."'," .intval($data['Interval1']).",".intval($data['IntervalN']).",'".intval($data['RoutinePlan'])."','".$username."')");

        if ($results) {
            //return Redirect::back()->with('success_message', ' Customer Rate Successfully Updated');
            return Response::json(array("status" => "success", "message" => "Customer Rate Successfully Updated"));
        } else {
            //return Redirect::back()->with('success_message', 'Problem Updating Customer Rate');
            return Response::json(array("status" => "failed", "message" => "Problem Updating Customer Rate."));
        }
    }

    public function clear_rate($id) {

        if($id > 0) {
            $username = User::get_user_full_name();

            $results = DB::statement('call prc_CustomerRateClear (?) ', array($id));
            if ($results) {
                return Response::json(array("status" => "success", "message" => "Customer Rate Successfully Cleared."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Clearing Customer Rate."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Invalid Record #ID"));
        }
    }

    public function bulk_clear_rate($id) {
        $data = Input::all();
        $username = User::get_user_full_name();
        $CustomerRateIDs = explode(",", $data['CustomerRateIDs']);
        $success = false;
        if (count($CustomerRateIDs)) {
            foreach ($CustomerRateIDs as $CustomerRateID) {
                if((int)$CustomerRateID>0) {
                    $results = DB::statement('call prc_CustomerRateClear (?)', array((int)$CustomerRateID));
                    if ($results) {
                        $success = true;
                    }
                }
            }
        }

        if ($success) {
            return Response::json(array("status" => "success", "message" => "Customer Rates Successfully Cleared."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Clearing Customer Rates."));
        }
    }

    /**
     * BulkUpdate the specified resource in storage.
     * PUT /customersrates/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function bulk_update($id) {

        $data = Input::all();
        $company_id = User::get_companyID();
        $data['customer'][] = $id;
        $rules = array( 'customer'=> 'required', 'RateID'=> 'required', 'EffectiveDate' => 'required','Rate' => 'required', 'Trunk' => 'required','Interval1' => 'required|numeric','IntervalN' => 'required|numeric');
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['customer'] = array_filter($data['customer'],'intval');
        $data['RateID'] = array_filter(explode(",",$data['RateID']),'intval');
        $username = User::get_user_full_name();

        $results = DB::statement("call prc_CustomerRateUpdateBySelectedRateId (".$company_id.",'".implode(',',$data['customer'])."','".implode(',',$data['RateID'])."',".$data['Trunk'].",".$data['Rate'].",".floatval($data['ConnectionFee']).",'".$data['EffectiveDate']."'," .intval($data['Interval1']).",".intval($data['IntervalN']).",'".intval($data['RoutinePlan'])."','".$username."')");
        if ($results) {
            return Response::json(array("status" => "success", "message" => "Customers Rate Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Customer Rate."));
        }
    }

    public function history($id) {

            $Account = Account::find($id);
            $trunks = CustomerTrunk::getTrunkDropdownIDList($id);
            if(count($trunks) == 0){
                return  Redirect::to('customers_rates/settings/'.$id)->with('info_message', 'Please enable trunks against customer to setup rates');
            }
            return View::make('customersrates.history', compact('id','Account'));
    }

    public function history_ajax_datagrid($id) {
        $companyID = User::get_companyID();
        $RateSheetHistory = RateSheetHistory::join('tblJob','tblJob.JobID','=','tblRateSheetHistory.JobID')
            ->leftjoin('tblJobFile','tblJob.JobID','=','tblJobFile.JobID')
            ->where(["tblJob.CompanyID" => $companyID, "tblJob.AccountID" => $id ,"tblRateSheetHistory.Type"=>"CD"])
            ->select(array('tblJob.Title', 'tblRateSheetHistory.created_at as created_date', 'tblRateSheetHistory.CreatedBy','tblRateSheetHistory.RateSheetHistoryID', 'tblJob.JobID as file_job_id','tblJob.OutputFilePath' ));

        return Datatables::of($RateSheetHistory)->make();
    }
    public function show_history($id,$RateSheetHistoryID) {

            $history = RateSheetHistory::join('tblJob', 'tblJob.JobID', '=', 'tblRateSheetHistory.JobID')
                ->where(["tblRateSheetHistory.RateSheetHistoryID" => $RateSheetHistoryID])
                ->select(
                    'tblRateSheetHistory.Title',
                    'tblRateSheetHistory.Description',
                    'tblJob.AccountID',
                    'tblJob.Options',
                    'tblJob.JobStatusMessage',
                    'tblJob.OutputFilePath',
                    'tblRateSheetHistory.created_at as created',
                    'tblRateSheetHistory.JobID'
                )
                ->first();
            $job_file = '';
            if (isset($history->JobID)) {
                $job_file = DB::table('tblJobFile')
                    ->where("tblJobFile.JobID", $history->JobID)
                    ->first();
            }

            return View::make('customersrates.show_history', compact('id', 'history', 'job_file'));

    }

    // not in use
    public function exports($id) {
            $data = Input::all();
            $data['iDisplayStart'] +=1;
            $data['Country']=$data['Country']!= ''?$data['Country']:'null';
            $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
            $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';

            $columns = array('RateID','Code','Description','Rate','EffectiveDate','LastModifiedDate','LastModifiedBy','CustomerRateId');
            $sort_column = $columns[$data['iSortCol_0']];
            $companyID = User::get_companyID();

            $query = "call prc_GetCustomerRate (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',1)";

            DB::setFetchMode( PDO::FETCH_ASSOC );
            $rate_table_rates  = DB::select($query);
            DB::setFetchMode( Config::get('database.fetch'));


            Excel::create('Customer Rates', function ($excel) use ($rate_table_rates) {
                $excel->sheet('Customer Rates', function ($sheet) use ($rate_table_rates) {
                    $sheet->fromArray($rate_table_rates);
                });
            })->download('xls');
    }
    public function history_exports($id,$type) {
            $companyID = User::get_companyID();

            $RateSheetHistory = RateSheetHistory::join('tblJob', 'tblJob.JobID', '=', 'tblRateSheetHistory.JobID')
                ->where(["tblJob.CompanyID" => $companyID, "tblJob.AccountID" => $id, "tblRateSheetHistory.Type" => "CD"])
                ->orderBy("tblRateSheetHistory.RateSheetHistoryID", "desc")
                ->get(array('tblJob.Title', 'tblRateSheetHistory.created_at as Created'));

            $excel_data = json_decode(json_encode($RateSheetHistory),true);
            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Customer Rates History.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Customer Rates History.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Customer Rates History', function ($excel) use ($RateSheetHistory) {
                $excel->sheet('Customer Rates History', function ($sheet) use ($RateSheetHistory) {
                    $sheet->fromArray($RateSheetHistory);
                });
            })->download('xls');*/
    }

    public function download_excel_file($id,$JobID){
            $filePath = JobFile::where(["JobID" => $JobID])->pluck("FilePath");
            Excel::load($filePath, function ($writer) {
                $writer->setFileName(basename($writer->getFileName()));
            })->download();

    }
    public function process_bulk_rate_update($id) {
        $data = Input::all();
        $company_id = User::get_companyID();
        $data['customer'][] = $id;
        $codedeckid = CustomerTrunk::where(['AccountID'=>$id,'TrunkID'=>$data['Trunk']])->pluck('CodeDeckId');
        $rules = array('EffectiveDate' => 'required','Rate' => 'required', 'Trunk' => 'required',);
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['Country'] = $data['Country'] == ''?'NULL':$data['Country'];
        $data['Code'] =  $data['Code'] == ''?'NULL':"'".$data['Code']."'";
        $data['Description'] = $data['Description'] == ''?'NULL':"'".$data['Description']."'";
        $data['customer'] = array_filter($data['customer'],'intval');
        $username = User::get_user_full_name();
        //Inserting Job Log
        //$results = DB::statement('prc_CustomerBulkRateUpdate ?,?,?,?,?,?,?,?,? ', array(implode(',',$data['customer']),$data['Trunk'],$data['Code'],$data['Description'],$data['Country'],$company_id,$data['Rate'],$data['EffectiveDate'],$username));
        $results = DB::statement("call prc_CustomerBulkRateUpdate ('".implode(',',$data['customer'])."',".$data['Trunk'].",".$codedeckid.",".$data['Code'].",".$data['Description'].",".$data['Country'].",".$company_id.",".$data['Rate'].",".floatval($data['ConnectionFee']).",'".$data['EffectiveDate']."',".intval($data['Interval1']).",".intval($data['IntervalN']).",'".intval($data['RoutinePlan'])."','".$username."')");
        if ($results) {
            return Response::json(array("status" => "success", "message" => "Customers Rate Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Customer Rate."));
        }

    }


    public function process_bulk_rate_clear($id) {
        $data = Input::all();
        $company_id = User::get_companyID();
        $data['customer'][] = $id;
        $codedeckid = CustomerTrunk::where(['AccountID'=>$id,'TrunkID'=>$data['Trunk']])->pluck('CodeDeckId');
        $data['Country'] = $data['Country'] == ''?'NULL':$data['Country'];
        $data['Code'] =  $data['Code'] == ''?'NULL':"'".$data['Code']."'";
        $data['Description'] = $data['Description'] == ''?'NULL':"'".$data['Description']."'";
        $data['customer'] = array_filter($data['customer'],'intval');
        $username = User::get_user_full_name();
        //Inserting Job Log
        //$results = DB::statement('prc_CustomerBulkRateUpdate ?,?,?,?,?,?,?,?,? ', array(implode(',',$data['customer']),$data['Trunk'],$data['Code'],$data['Description'],$data['Country'],$company_id,$data['Rate'],$data['EffectiveDate'],$username));
        $results = DB::statement("call prc_CustomerBulkRateClear ('".implode(',',$data['customer'])."',".$data['Trunk'].",".$codedeckid.",".$data['Code'].",".$data['Description'].",".$data['Country'].",".$company_id.")");
        if ($results) {
            return Response::json(array("status" => "success", "message" => "Customers Rate Successfully Deleted"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Customer Rate."));
        }

    }


    public function  delete_customerrates($id){
            $data = Input::all();
            $rules = array('Trunkid' => 'required');
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if(isset($data['action']) && $data['action']=='check_count'){
                return CustomerRate::where(["CustomerID" =>$id ,'TrunkID'=>$data['Trunkid']])->count();
            }

            CustomerTrunk::where(["AccountID" =>$id ,'TrunkID'=>$data['Trunkid']])->update(['RateTableID'=>'0']);
            if (CustomerRate::where(["CustomerID" =>$id ,'TrunkID'=>$data['Trunkid']])->count()  == 0 || CustomerRate::where(["CustomerID" =>$id ,'TrunkID'=>$data['Trunkid']])->delete()) {
                return Response::json(array("status" => "success", "message" => "Customer Rates Deleted Successfully"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Customer Rates."));
            }
    }
    public function vendor_merge(){

        DB::statement("update vendor_merge  set vendor_merge.is_vendor_customer = 1 from vendor_merge inner join tblAccount a on a.AccountName = vendor_merge.customer      and a.IsCustomer = 1 and a.IsVendor = 1");
        $vendors_to_merge = DB::table('vendor_merge')->where(["is_vendor_customer"=>0,"status"=>0])->get();

        Log::info("vendors_to_merge : ". count($vendors_to_merge));
        Log::info("vendors_to_merge : ". print_r($vendors_to_merge,true));

         foreach($vendors_to_merge as $key => $row){
            $vendor = $row->vendor;
            $customer = $row->customer;

            Log::info("Vendor : ". $vendor . " - Customer :" . $customer);
            $results = DB::statement("prc_merge_vendor_into_customer '".$vendor."','".$customer."'");
            DB::table('vendor_merge')->where(["vendor"=>$vendor,"customer"=>$customer])->update(["status"=>1]);
        }
    }

    public function customerdownloadtype($id,$type){
        if($type=='Vos 3.2'){
            $downloadtype = '<option value="">Select a Type</option><option value="txt">TXT</option>';
        }else{
            $downloadtype = '<option value="">Select a Type</option><option value="xlsx">EXCEL</option><option value="csv">CSV</option>';
        }
        return $downloadtype;
    }
}
