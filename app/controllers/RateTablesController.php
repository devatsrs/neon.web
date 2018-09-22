<?php

class RateTablesController extends \BaseController {

    public function ajax_datagrid() {
        $CompanyID = User::get_companyID();
        $rate_tables = RateTable::
        Join('tblCurrency','tblCurrency.CurrencyId','=','tblRateTable.CurrencyId')
            ->join('tblCodeDeck','tblCodeDeck.CodeDeckId','=','tblRateTable.CodeDeckId')
            ->leftjoin('tblTrunk','tblTrunk.TrunkID','=','tblRateTable.TrunkID')
            ->select(['tblRateTable.RateTableName','tblCurrency.Code', 'tblTrunk.Trunk as trunkName','tblCodeDeck.CodeDeckName','tblRateTable.updated_at','tblRateTable.RateTableId', 'tblRateTable.TrunkID', 'tblRateTable.CurrencyID', 'tblRateTable.RoundChargedAmount'])
            ->where("tblRateTable.CompanyId",$CompanyID);
        //$rate_tables = RateTable::join('tblCurrency', 'tblCurrency.CurrencyId', '=', 'tblRateTable.CurrencyId')->where(["tblRateTable.CompanyId" => $CompanyID])->select(["tblRateTable.RateTableName","Code","tblRateTable.updated_at", "tblRateTable.RateTableId"]);
        $data = Input::all();
        if($data['TrunkID']){
            $rate_tables->where('tblRateTable.TrunkID',$data['TrunkID']);
        }
		if($data['Search']!=''){
            $rate_tables->WhereRaw('tblRateTable.RateTableName like "%'.$data['Search'].'%"'); 
        }		

        return Datatables::of($rate_tables)->make();
    }

    public function search_ajax_datagrid($id) {
        $companyID = User::get_companyID();

        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['Country']        = $data['Country'] != '' && $data['Country'] != 'All'?$data['Country']:'null';
        $data['Code']           = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description']    = $data['Description'] != ''?"'".$data['Description']."'":'null';

        $columns = array('RateTableRateID','Code','Description','Interval1','IntervalN','ConnectionFee','PreviousRate','Rate','RateN','EffectiveDate','EndDate','updated_at','ModifiedBy','RateTableRateID');
        $sort_column = $columns[$data['iSortCol_0']];

        $view = isset($data['view']) && $data['view'] == 2 ? $data['view'] : 1;

        if(!empty($data['DiscontinuedRates'])) {
            $query = "call prc_getDiscontinuedRateTableRateGrid (" . $companyID . "," . $id . ",".$data['Timezones']."," . $data['Country'] . "," . $data['Code'] . "," . $data['Description'] . ",".$view."," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "',0)";
        } else {
            $query = "call prc_GetRateTableRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$view.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
        }
        Log::info($query);

        return DataTableSql::of($query)->make();
    }

    /*
     * Datagrid for Edit Mode
     */

    public function edit_ajax_datagrid($id) {
//        $CompanyID = User::get_companyID();
//        $rate_table_rates = RateTableRate::join('tblRate', 'tblRateTableRate.RateID', '=', 'tblRate.RateID')
//                        ->where(["tblRateTableRate.RateTableId" => $id])->select([
//            "tblRateTableRate.RateTableRateID as ID",
//            "tblRate.Code",
//            "tblRateTableRate.Rate",
//            "tblRate.Description",
//            "tblRateTableRate.EffectiveDate",
//            "tblRateTableRate.updated_at",
//            "tblRateTableRate.ModifiedBy",
//            "tblRateTableRate.RateTableRateID"]);
//
//        return Datatables::of($rate_table_rates)->make();
    }

    /**
     * Display a listing of the resource.
     * GET /ratetables
     *
     * @return Response
     */
    public function index() {
            $companyID = User::get_companyID();
            $trunks = Trunk::getTrunkDropdownIDList();
            $trunk_keys = getDefaultTrunk($trunks);
            $RateGenerators = RateGenerator::where(["Status" => 1, "CompanyID" => $companyID])->lists("RateGeneratorName", "RateGeneratorId");
            $codedecks = BaseCodeDeck::where(["CompanyID" => $companyID])->lists("CodeDeckName", "CodeDeckId");
            $codedecks = array(""=>"Select Codedeck")+$codedecks;
            $RateGenerators = array(""=>"Select rate generator")+$RateGenerators;
            $currencylist = Currency::getCurrencyDropdownIDList();
            return View::make('ratetables.index', compact('trunks','RateGenerators','codedecks','trunk_keys','currencylist'));
    }


    /**
     * Store a newly created resource in storage.
     * POST /ratetables
     *
     * @return Response
     */
    public function store() {

        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['CreatedBy'] = User::get_user_full_name();
        $data['RateTableName'] = trim($data['RateTableName']);

        /*$data['RateGeneratorId'] = isset($data['RateGeneratorId'])?$data['RateGeneratorId']:0;
        if($data['RateGeneratorId'] > 0) {
            $rateGenerator = RateGenerator::where(["RateGeneratorId" => $data['RateGeneratorId']])->get();
            $data['TrunkID'] = $rateGenerator[0]->TrunkID;
            $data['CodeDeckId'] = $rateGenerator[0]->CodeDeckId;
        }
            else if(empty($data['TrunkID'])){
            $data['TrunkID'] = Trunk::where(["CompanyID" => $companyID ])->min('TrunkID');
        }*/
        $rules = array(
            'CompanyID' => 'required',
            'RateTableName' => 'required|unique:tblRateTable,RateTableName,NULL,CompanyID,CompanyID,'.$data['CompanyID'],
            //'RateGeneratorId'=>'required',
            'CodedeckId'=>'required',
            //'TrunkID'=>'required',
            'CurrencyID'=>'required',

        );
        $message = ['CurrencyID.required'=>'Currency field is required',
                    //'TrunkID.required'=>'Trunk field is required',
                    'CodedeckId.required'=>'Codedeck field is required',
                    //'RateGeneratorId.required'=>'RateGenerator'
                    ];

        $validator = Validator::make($data, $rules, $message);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if (RateTable::insert($data)) {
            return Response::json(array("status" => "success", "message" => "RateTable Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating RateTable."));
        }

    }

    /**
     * Show the form for editing the specified resource.
     * GET /ratetables/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function view($id) {
        $rateTable = RateTable::find($id);
        $trunkID = RateTable::where(["RateTableId" => $id])->pluck('TrunkID');
        $countries = Country::getCountryDropdownIDList();
        $CodeDeckId = RateTable::getCodeDeckId($id);
        $CompanyID = User::get_companyID();
        $codes = CodeDeck::getCodeDropdownList($CodeDeckId,$CompanyID);
        $isBandTable = RateTable::checkRateTableBand($id);
        $code = RateTable::getCurrencyCode($id);
        $Timezones = Timezones::getTimezonesIDList();

        return View::make('ratetables.edit', compact('id', 'countries','trunkID','codes','isBandTable','code','rateTable','Timezones'));
    }



    public function delete($id) {
        if ($id > 0) {
            $is_id_assigned = RateTable::join('tblCustomerTrunk', 'tblCustomerTrunk.RateTableId', '=', 'tblRateTable.RateTableId')
                            ->where("tblRateTable.RateTableId", $id)->count();
            //Is RateTable assigne to RateTableRate table then dont delete
            if ($is_id_assigned == 0) {
                if(RateTable::checkRateTableInCronjob($id)){
                    if(RateTableRate::where(["RateTableId" => $id])->count()>0){
                        if (RateTableRate::where(["RateTableId" => $id])->delete() && RateTable::where(["RateTableId" => $id])->delete()) {
                            return Response::json(array("status" => "success", "message" => "RateTable Successfully Deleted"));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Deleting RateTable."));
                        }
                    }else{
                        if (RateTable::where(["RateTableId" => $id])->delete()) {
                            return Response::json(array("status" => "success", "message" => "RateTable Successfully Deleted"));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem Deleting RateTable."));
                        }
                    }

                }else{
                    return Response::json(array("status" => "failed", "message" => "RateTable can not be deleted, Its assigned to CronJob."));
                }

            } else {
                if(RateTable::checkRateTableInCronjob($id)){
                    return Response::json(array("status" => "failed", "message" => "RateTable can not be deleted, Its assigned to Customer Rate."));
                }else{
                    return Response::json(array("status" => "failed", "message" => "RateTable can not be deleted, Its assigned to Customer Rate and CronJob."));
                }
            }
        }
    }

    //delete rate table rates
    public function clear_rate($id) {
        if ($id > 0) {
            $data           = Input::all();//echo "<pre>";print_r($data);exit();
            $username       = User::get_user_full_name();
            $EffectiveDate  = $EndDate = $Rate = $RateN = $Interval1 = $IntervalN = $ConnectionFee = 'null';
            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 2; //delete action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['Code']           = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'null';
                $criteria['Description']    = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'null';
                $criteria['Country']        = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'null';
                $criteria['Effective']      = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'null';
                $criteria['TimezonesID']    = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones'] . "'" : 'NULL';

                $RateTableID                = $id;
                $RateTableRateID            = $data['RateTableRateID'];

                if(empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTableRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTableRateUpdateDelete (" . $RateTableID . ",'" . $RateTableRateID . "'," . $EffectiveDate . "," . $EndDate . "," . $Rate . "," . $RateN . "," . $Interval1 . "," . $IntervalN . "," . $ConnectionFee . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Rate Table Rates."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        }
    }

    // update rate table rate
    public function update_rate_table_rate($id) {
        if ($id > 0) {
            $data = Input::all();//echo "<pre>";print_r($data);exit();
            $error = 0;

            $EffectiveDate = $EndDate = $Rate = $RateN = $Interval1 = $IntervalN = $ConnectionFee = 'null';

            if(!empty($data['updateEffectiveDate']) || !empty($data['updateRate']) || !empty($data['updateRateN']) || !empty($data['updateInterval1']) || !empty($data['updateIntervalN']) || !empty($data['updateConnectionFee'])) {// || !empty($data['EndDate'])
                if(!empty($data['updateEffectiveDate'])) {
                    if(!empty($data['EffectiveDate'])) {
                        $EffectiveDate = "'".$data['EffectiveDate']."'";
                    } else {
                        $error=1;
                    }
                }
                /*if(!empty($data['updateEndDate'])) {
                    if(!empty($data['EndDate'])) {
                        $EndDate = "'".$data['EndDate']."'";
                    } else if (empty($data['updateType'])) {
                        $error=1;
                    }
                }*/
                if(!empty($data['updateRate'])) {
                    if(!empty($data['Rate'])) {
                        $Rate = "'".floatval($data['Rate'])."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateRateN'])) {
                    if(!empty($data['RateN'])) {
                        $RateN = "'".floatval($data['RateN'])."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateInterval1'])) {
                    if(!empty($data['Interval1'])) {
                        $Interval1 = "'".$data['Interval1']."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateIntervalN'])) {
                    if(!empty($data['IntervalN'])) {
                        $IntervalN = "'".$data['IntervalN']."'";
                    } else {
                        $error=1;
                    }
                }
                if(!empty($data['updateConnectionFee'])) {
                    if(!empty($data['ConnectionFee'])) {
                        $ConnectionFee = "'".$data['ConnectionFee']."'";
                    } else if (empty($data['updateType'])) {
                        $error=1;
                    }
                }
                if(isset($error) && $error==1) {
                    return Response::json(array("status" => "failed", "message" => "Please Select Checked Field Data"));
                }

            } else {
                return Response::json(array("status" => "failed", "message" => "No Rate selected to Update."));
            }

            $username = User::get_user_full_name();

            try {
                DB::beginTransaction();
                $p_criteria = 0;
                $action     = 1; //update action
                $criteria   = json_decode($data['criteria'], true);

                $criteria['Code']           = !empty($criteria['Code']) && $criteria['Code'] != '' ? "'" . $criteria['Code'] . "'" : 'NULL';
                $criteria['Description']    = !empty($criteria['Description']) && $criteria['Description'] != '' ? "'" . $criteria['Description'] . "'" : 'NULL';
                $criteria['Country']        = !empty($criteria['Country']) && $criteria['Country'] != '' ? "'" . $criteria['Country'] . "'" : 'NULL';
                $criteria['Effective']      = !empty($criteria['Effective']) && $criteria['Effective'] != '' ? "'" . $criteria['Effective'] . "'" : 'NULL';
                $criteria['TimezonesID']    = !empty($criteria['Timezones']) && $criteria['Timezones'] != '' ? "'" . $criteria['Timezones'] . "'" : 'NULL';

                $RateTableID                = $id;
                $RateTableRateID            = $data['RateTableRateID'];

                if(empty($criteria['TimezonesID']) || $criteria['TimezonesID'] == 'NULL') {
                    $criteria['TimezonesID'] = $data['TimezonesID'];
                }

                if (empty($data['RateTableRateID']) && !empty($data['criteria'])) {
                    $p_criteria = 1;
                }

                $query = "call prc_RateTableRateUpdateDelete (" . $RateTableID . ",'" . $RateTableRateID . "'," . $EffectiveDate . "," . $EndDate . "," . $Rate . "," . $RateN . "," . $Interval1 . "," . $IntervalN . "," . $ConnectionFee . "," . $criteria['Country'] . "," . $criteria['Code'] . "," . $criteria['Description'] . "," . $criteria['Effective'] . "," . $criteria['TimezonesID'] . ",'" . $username . "',".$p_criteria.",".$action.")";
                Log::info($query);
                $results = DB::statement($query);

                if ($results) {
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Rates Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating Rate Table Rate."));
                }
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }

        } else {
            return Response::json(array("status" => "failed", "message" => "No RateTable Found."));
        }
    }

    public function change_status($id, $status) {
        if ($id > 0 && ( $status == 0 || $status == 1)) {
            if (RateTable::find($id)->update(["Status" => $status, "ModifiedBy" => User::get_user_full_name()])) {
                return Response::json(array("status" => "success", "message" => "Status Successfully Changed"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Changing Status."));
            }
        }
    }
    
    public function exports($type) {
            $CompanyID = User::get_companyID();
            /*$rate_tables = RateTable::where(["CompanyId" => $CompanyID])->orderBy("RateTableId", "desc");
            $data = Input::all();
            if($data['TrunkID']){
                $rate_tables->where('TrunkID',$data['TrunkID']);
            }
            $rate_tables = $rate_tables->get(["RateTableName"]);*/
            $rate_tables = RateTable::
            join('tblCurrency','tblCurrency.CurrencyId','=','tblRateTable.CurrencyId')
                ->join('tblCodeDeck','tblCodeDeck.CodeDeckId','=','tblRateTable.CodeDeckId')
                ->select(['tblRateTable.RateTableName','tblCurrency.Code as Currency Code','tblCodeDeck.CodeDeckName'])
                ->where("tblRateTable.CompanyId",$CompanyID);
            //$rate_tables = RateTable::join('tblCurrency', 'tblCurrency.CurrencyId', '=', 'tblRateTable.CurrencyId')->where(["tblRateTable.CompanyId" => $CompanyID])->select(["tblRateTable.RateTableName","Code","tblRateTable.updated_at", "tblRateTable.RateTableId"]);
            $data = Input::all();
            if($data['TrunkID']){
                $rate_tables = $rate_tables->where('tblRateTable.TrunkID',$data['TrunkID']);
            }
            $rate_tables = $rate_tables->get();
            $excel_data = json_decode(json_encode($rate_tables),true);


            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Rates Table.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Rates Table.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*
            Excel::create('Rates Table', function ($excel) use ($rate_tables) {
                $excel->sheet('Rates Table', function ($sheet) use ($rate_tables) {
                    $sheet->fromArray($rate_tables);
                });
            })->download('xls');*/
    }
    
    public function rate_exports($id,$type) {
        $companyID = User::get_companyID();
        $data = Input::all();
        $RateTableName = RateTable::find($id)->RateTableName;

        $view = isset($data['view']) && $data['view'] == 2 ? $data['view'] : 1;
        $data['Country']        = $data['Country'] != '' && $data['Country'] != 'All'?$data['Country']:'null';
        $data['Code']           = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description']    = $data['Description'] != ''?"'".$data['Description']."'":'null';

        $query = " call prc_GetRateTableRate (".$companyID.",".$id.",".$data['TrunkID'].",".$data['Timezones'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$view.",null,null,null,null,1)";

        DB::setFetchMode( PDO::FETCH_ASSOC );
        $rate_table_rates  = DB::select($query);
        DB::setFetchMode( Config::get('database.fetch'));

        $RateTableName = str_replace( '\/','-',$RateTableName);

        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/'.$RateTableName . ' - Rates Table Customer Rates.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($rate_table_rates);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/'.$RateTableName . ' - Rates Table Customer Rates.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($rate_table_rates);
        }
        /*Excel::create($RateTableName . ' - Rates Table', function ($excel) use ($rate_table_rates) {
            $excel->sheet('Rates Table', function ($sheet) use ($rate_table_rates) {
                $sheet->fromArray($rate_table_rates);
            });
        })->download('xls');*/
    }
    public static function add_newrate($id){
        $data = Input::all();

        $data['RateTableId'] = $id;
        $rules               = RateTableRate::$rules;
        $rules['RateID']     = 'required|unique:tblRateTableRate,RateID,NULL,RateTableId,RateTableId,'.$id.',EffectiveDate,'.$data['EffectiveDate'];
        $validator           = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $RateTableRate = array();
        $RateTableRate['RateTableId']   = $id;
        $RateTableRate['RateID']        = $data['RateID'];
        $RateTableRate['EffectiveDate'] = $data['EffectiveDate'];
        $RateTableRate['EndDate']       = !empty($data['EndDate']) ? $data['EndDate'] : null;
        $RateTableRate['Rate']          = $data['Rate'];
        $RateTableRate['RateN']         = !empty($data['RateN']) ? $data['RateN'] : $data['Rate'];
        $RateTableRate['Interval1']     = $data['Interval1'];
        $RateTableRate['IntervalN']     = $data['IntervalN'];
        $RateTableRate['ConnectionFee'] = $data['ConnectionFee'];
        $RateTableRate['TimezonesID']   = $data['TimezonesID'];

        /*$PreviousRate = RateTableRate::where('EffectiveDate','<',$data['EffectiveDate'])
                                     ->where('RateID',$data['RateID'])
                                     ->where('RateTableId',$id)
                                     ->orderBy('RateTableRateID','DESC')
                                     ->limit(1)
                                     ->get();
        if(!empty($PreviousRate)) {
            $RateTableRate['PreviousRate'] = $PreviousRate[0]->Rate;
        } else {
            $PreviousRate = RateTableRateArchive::where('EffectiveDate','<',$data['EffectiveDate'])
                                                ->where('RateID',$data['RateID'])
                                                ->where('RateTableId',$id)
                                                ->orderBy('RateTableRateID','DESC')
                                                ->limit(1)
                                                ->get();

            if(!empty($PreviousRate)) {
                $RateTableRate['PreviousRate'] = $PreviousRate[0]->Rate;
            } else {
                $RateTableRate['PreviousRate'] = null;
            }
        }*/

        if (RateTableRate::insert($RateTableRate)) {
            return Response::json(array("status" => "success", "message" => "Rate Successfully Inserted "));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Inserting  Rate."));
        }

    }

    public function upload($id) {
        $uploadtemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_VENDOR_RATE));
        $rateTable = RateTable::where(["RateTableId" => $id])->get(array('TrunkID','CodeDeckId'));
        $rate_sheet_formates = RateSheetFormate::getVendorRateSheetFormatesDropdownList();
        return View::make('ratetables.upload', compact('id','rateTable','rate_sheet_formates','uploadtemplate'));
    }

    public function check_upload() {
        try {
            ini_set('max_execution_time', 0);
            $data = Input::all();
            if (!isset($data['Trunk']) || empty($data['Trunk'])) {
                return json_encode(["status" => "failed", "message" => 'Please Select a Trunk']);
            } else if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                    $file_name_without_ext = GUID::generate();
                    $file_name = $file_name_without_ext . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else if (isset($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if (!empty($file_name)) {

                if ($data['uploadtemplate'] > 0) {
                    $RateTableFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($RateTableFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($RateTableFileUploadTemplate)) {
                    $grid['RateTableFileUploadTemplate'] = json_decode(json_encode($RateTableFileUploadTemplate), true);
                    $grid['RateTableFileUploadTemplate']['Options'] = json_decode($RateTableFileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        }catch(Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function download_sample_excel_file(){
        $filePath =  public_path() .'/uploads/sample_upload/RateTableUploadSample.csv';
        download_file($filePath);

    }

    function ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TempFileName'];
            $grid = getFileContent($file_name, $data);
            $grid['filename'] = $data['TemplateFile'];
            $grid['tempfilename'] = $data['TempFileName'];
            if ($data['uploadtemplate'] > 0) {
                $RateTableFileUploadTemplate = FileUploadTemplate::find($data['uploadtemplate']);
                $grid['RateTableFileUploadTemplate'] = json_decode(json_encode($RateTableFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['RateTableFileUploadTemplate']['Options'] = array();
            $grid['RateTableFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['RateTableFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function storeTemplate($id) {
        $data = Input::all();
        $CompanyID = User::get_companyID();

        $rules['selection.Code'] = 'required';
        $rules['selection.Description'] = 'required';
        $rules['selection.Rate'] = 'required';
        //$rules['selection.EffectiveDate'] = 'required';
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = CompanyConfiguration::get('TEMP_PATH') . '/';

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['RATETABLE_UPLOAD']);
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload vendor rates file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = filterArrayRemoveNewLines($data['selection']);//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            if (isset($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                $template = FileUploadTemplate::find($data['uploadtemplate']);
                $template->update($save);
            } else {
                $template = FileUploadTemplate::create($save);
            }
            $data['uploadtemplate'] = $template->FileUploadTemplateID;
        }
        $save = array();
        $option["option"]=  $data['option'];
        $option["selection"] = filterArrayRemoveNewLines($data['selection']);
        $save['Options'] = json_encode($option);
        $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path'] = $fullPath;
        $save["RateTableID"] = $id;
        $save['codedeckid'] = $data['CodeDeckID'];
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        $save['Trunk'] = $data['Trunk'];
        $save['checkbox_replace_all'] = $data['checkbox_replace_all'];
        $save['checkbox_rates_with_effected_from'] = $data['checkbox_rates_with_effected_from'];
        $save['checkbox_add_new_codes_to_code_decks'] = $data['checkbox_add_new_codes_to_code_decks'];
        $save['ratetablename'] = RateTable::where(["RateTableId" => $id])->pluck('RateTableName');

        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("RTU", $save);
            if ($result['status'] != "success") {
                DB::rollback();
                return json_encode(["status" => "failed", "message" => $result['message']]);
            }
            DB::commit();
            @unlink($temp_path . $file_name);
            return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
        } catch (Exception $ex) {
            DB::rollback();
            return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }
	
	//get ajax code for add new rate
    public function getCodeByAjax(){
        $CompanyID = User::get_companyID();
        $list = array();
        $data = Input::all();
        $rate = $data['q'].'%';
        $ratetableid = $data['page'];
        $CodeDeckId = RateTable::getCodeDeckId($ratetableid);
        $codes = CodeDeck::where(["CompanyID" => $CompanyID,'CodeDeckId'=>$CodeDeckId])
            ->where('Code','like',$rate)->take(100)->lists('Code', 'RateID');

        if(count($codes) > 0){
            foreach($codes as $key => $value){
                $list2 = array();
                $list2['id'] = $key;
                $list2['text'] = $value;
                $list[]= json_encode($list2);
            }
            $rateids = '['.implode(',',$list).']';
        }else{
            $rateids = '[]';
        }


        return $rateids;
    }

    public function edit($id){
        $data = Input::all();
        $rateTableId = RateTable::findOrFail($id);
        $data['CompanyID'] = User::get_companyID();
        $data['RateTableName'] = trim($data['RateTableName']);


        $rules = array(
            'RateTableName' => 'required|unique:tblRateTable,RateTableName,'.$id.',RateTableId,CompanyID,'.$data['CompanyID'],
            'CurrencyID' => 'required',
            'CompanyID' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $data['ModifiedBy'] = User::get_user_full_name();
        if ($rateTableId->update($data)) {
            return Response::json(array("status" => "success", "message" => "Rate Table Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Rate Table."));
        }
    }

    public function search_ajax_datagrid_archive_rates($RateTableID) {

        $data       = Input::all();
        $companyID  = User::get_companyID();
        $view       = isset($data['view']) && $data['view'] == 2 ? $data['view'] : 1;

        if(!empty($data['Codes'])) {
            $Codes       = $data['Codes'];
            $TimezonesID = $data['TimezonesID'];
            $query = 'call prc_GetRateTableRatesArchiveGrid ('.$companyID.','.$RateTableID.','.$TimezonesID.',"'.$Codes.'",'.$view.')';
            //Log::info($query);
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

}