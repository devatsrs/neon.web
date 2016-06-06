<?php

class RateTablesController extends \BaseController {

    public function ajax_datagrid() {
        $CompanyID = User::get_companyID();
        $rate_tables = RateTable::
        join('tblCurrency','tblCurrency.CurrencyId','=','tblRateTable.CurrencyId')
            ->join('tblCodeDeck','tblCodeDeck.CodeDeckId','=','tblRateTable.CodeDeckId')
            ->select(['tblRateTable.RateTableName','tblCurrency.Code','tblCodeDeck.CodeDeckName','tblRateTable.updated_at','tblRateTable.RateTableId'])
            ->where("tblRateTable.CompanyId",$CompanyID);
        //$rate_tables = RateTable::join('tblCurrency', 'tblCurrency.CurrencyId', '=', 'tblRateTable.CurrencyId')->where(["tblRateTable.CompanyId" => $CompanyID])->select(["tblRateTable.RateTableName","Code","tblRateTable.updated_at", "tblRateTable.RateTableId"]);
        $data = Input::all();
        if($data['TrunkID']){
            $rate_tables->where('tblRateTable.TrunkID',$data['TrunkID']);
        }
        return Datatables::of($rate_tables)->make();
    }

    public function search_ajax_datagrid($id) {
        $companyID = User::get_companyID();

        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $columns = array('RateTableRateID','Code','Description','Interval1','IntervalN','ConnectionFee','Rate','EffectiveDate','updated_at','ModifiedBy','RateTableRateID');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_GetRateTableRate (".$companyID.",".$id.",".$data['TrunkID'].",'".$data['Country']."','".$data['Code']."','".$data['Description']."','".$data['Effective']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";


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
        $data['RateGeneratorId'] = isset($data['RateGeneratorId'])?$data['RateGeneratorId']:0;
        if($data['RateGeneratorId'] > 0) {
            $data['TrunkID'] = RateGenerator::where(["RateGeneratorId" => $data['RateGeneratorId']])->pluck('TrunkID');
        }else if(empty($data['TrunkID'])){
            $data['TrunkID'] = Trunk::where(["CompanyID" => $companyID ])->min('TrunkID');
        }

        $rules = array(
            'CompanyID' => 'required',
            'RateTableName' => 'required|unique:tblRateTable,RateTableName,NULL,CompanyID,CompanyID,'.$data['CompanyID'],
            'RateGeneratorId'=>'required',
            'TrunkID'=>'required'

        );
        $validator = Validator::make($data, $rules);
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
            $trunkID = RateTable::where(["RateTableId" => $id])->pluck('TrunkID');
            $countries = Country::getCountryDropdownIDList();
            $CodeDeckId = RateTable::getCodeDeckId($id);
            $CompanyID = User::get_companyID();
            $codes = CodeDeck::getCodeDropdownList($CodeDeckId,$CompanyID);
            $isBandTable = RateTable::checkRateTableBand($id);
            $code = RateTable::getCurrencyCode($id);
            return View::make('ratetables.edit', compact('id', 'countries','trunkID','codes','isBandTable','code'));
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

    public function clear_rate($id) {
        if ($id > 0) {
            if (RateTableRate::find($id)->delete()) {
                return Response::json(array("status" => "success", "message" => "Rate Successfully Deleted"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Rate."));
            }
        }
    }

    public function bulk_clear_rate_table_rate($id) {

        if ($id > 0) {
            $data = Input::all();

            $data["ModifiedBy"] = User::get_user_full_name();
            $data["Rate"] = 0;

            $rules = array('RateTableRateID' => 'required', 'Rate' => 'required', 'ModifiedBy' => 'required');
            if(!empty($data['criteria'])) {
                $rules = array('Rate' => 'required', 'ModifiedBy' => 'required');
            }
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if(!empty($data['criteria'])) {
                $criteria = json_decode($data['criteria'], true);
                $companyID = User::get_companyID();
                $query = "call prc_RateTableRateInsertUpdate('','" . $id . "',0,'','','','',0,'" . $companyID . "','" . $criteria['TrunkID'] . "','" . intval($criteria['Country']) . "','" . $criteria['Code'] . "','" . $criteria['Description'] . "','" . $criteria['Effective'] . "',2)";
                DB::statement($query);
                unset($data['RateTableRateID']);
                return Response::json(array("status" => "success", "message" => "Rate Successfully Deleted"));
            }else{
                $RateTableRateIDs = explode(",", $data['RateTableRateID']);
                unset($data['RateTableRateID']);
            }




            if (count($RateTableRateIDs)) {
                foreach ($RateTableRateIDs as $RateTableRateID) {

                    if ((int)$RateTableRateID > 0 && !RateTableRate::find($RateTableRateID)->delete()) { //if ((int)$RateTableRateID > 0 && !RateTableRate::find($RateTableRateID)->update($data)) {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Rate."));
                    }
                }
                return Response::json(array("status" => "success", "message" => "Rate Successfully Deleted"));
            }
        }
    }

    public function update_rate_table_rate($id, $RateTableRateID = 0) {
        if ($id > 0 && $RateTableRateID > 0) {

            $data = Input::all();

            $data["ModifiedBy"] = User::get_user_full_name();

            $rules = array('EffectiveDate' => 'required', 'Rate' => 'required', 'ModifiedBy' => 'required','Interval1'=>'required','IntervalN'=>'required');

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $username = User::get_user_full_name();
            $companyID = User::get_companyID();
            $results = DB::statement('call prc_RateTableRateInsertUpdate (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array($RateTableRateID, $id, $data['Rate'], $data['EffectiveDate'],$username,intval($data['Interval1']),intval($data['IntervalN']),floatval($data['ConnectionFee']),$companyID,0,0,'','','',0));
            if ($results) {
                return Response::json(array("status" => "success", "message" => "Rate Table Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating  Rate Table."));
            }
        } else {

            // Create RateTableRate

            $data = Input::all();

            $data["CreatedBy"] = User::get_user_full_name();
            $data["ModifiedBy"] = User::get_user_full_name();
            $data["RateTableId"] = $id;
            $data["PreviousRate"] = 0;
            unset($data['RateTableRateID']);

            $rules = array('RateID' => 'required', 'RateTableId' => 'required', 'Rate' => 'required', 'EffectiveDate' => 'required', 'Interval1'=>'required','IntervalN'=>'required','PreviousRate' => 'required', 'CreatedBy' => 'required', 'ModifiedBy' => 'required');

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if (RateTableRate::insert($data)) {
                RateTableRate::find(DB::getPdo()->lastInsertId())->touch();
                return Response::json(array("status" => "success", "message" => "Rate Table Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating  Rate Table."));
            }
        }
    }

    public function bulk_update_rate_table_rate($id) {
        if ($id > 0) {
            $data = Input::all();
            $username = User::get_user_full_name();
            $rules = array('EffectiveDate' => 'required', 'Rate' => 'required','Interval1'=>'required','IntervalN'=>'required');

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $companyID = User::get_companyID();
            if(empty($data['RateTableRateID']) && !empty($data['criteria'])){
                $criteria = json_decode($data['criteria'],true);
                $results = DB::statement('call prc_RateTableRateInsertUpdate (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array('', $id, $data['Rate'], $data['EffectiveDate'],$username,intval($data['Interval1']),intval($data['IntervalN']),floatval($data['ConnectionFee']),$companyID,intval($criteria['TrunkID']),intval($criteria['Country']),$criteria['Code'],$criteria['Description'],$criteria['Effective'],1));
            }else{
                $results = DB::statement('call prc_RateTableRateInsertUpdate (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array($data['RateTableRateID'], $id, $data['Rate'], $data['EffectiveDate'],$username,intval($data['Interval1']),intval($data['IntervalN']),floatval($data['ConnectionFee']),$companyID,0,0,'','','',0));
            }
            if ($results) {
                return Response::json(array("status" => "success", "message" => "Bulk Rate Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating  Rate Table."));
            }

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
                $file_path = getenv('UPLOAD_PATH') .'/Rates Table.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Rates Table.xls';
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
            $query = " call prc_GetRateTableRate (".$companyID.",".$id.",".$data['TrunkID'].",'".$data['Country']."','".$data['Code']."','".$data['Description']."','".$data['Effective']."',null,null,null,null,1)";

            DB::setFetchMode( PDO::FETCH_ASSOC );
            $rate_table_rates  = DB::select($query);
            DB::setFetchMode( Config::get('database.fetch'));

            $RateTableName = str_replace( '\/','-',$RateTableName);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/'.$RateTableName . ' - Rates Table Customer Rates.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($rate_table_rates);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/'.$RateTableName . ' - Rates Table Customer Rates.xls';
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
        $RateTableRate = array();
        $RateTableRate['RateTableId'] = $id;
        $RateTableRate['RateID'] = $data['RateID'];
        $RateTableRate['EffectiveDate'] = $data['EffectiveDate'];
        $RateTableRate['Rate'] = $data['Rate'];
        $RateTableRate['Interval1'] = $data['Interval1'];
        $RateTableRate['IntervalN'] = $data['IntervalN'];
        $RateTableRate['ConnectionFee'] = $data['ConnectionFee'];
        $rules = RateTableRate::$rules;
        $rules['RateID'] = 'required|unique:tblRateTableRate,RateID,NULL,RateTableId,RateTableId,'.$id.',EffectiveDate,'.$data['EffectiveDate'];
        $validator = Validator::make($RateTableRate, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if (RateTableRate::insert($RateTableRate)) {
            return Response::json(array("status" => "success", "message" => "Rate Successfully Inserted "));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Inserting  Rate."));
        }

    }

    public function upload($id) {
        $uploadtemplate = VendorFileUploadTemplate::getTemplateIDList();
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
                $upload_path = getenv('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array($ext, array("csv", "xls", "xlsx"))) {
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
                    $RateTableFileUploadTemplate = VendorFileUploadTemplate::find($data['uploadtemplate']);
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
                $RateTableFileUploadTemplate = VendorFileUploadTemplate::find($data['uploadtemplate']);
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

        $temp_path = getenv('TEMP_PATH') . '/';

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['RATETABLE_UPLOAD']);
        $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload vendor rates file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            if (isset($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                $template = VendorFileUploadTemplate::find($data['uploadtemplate']);
                $template->update($save);
            } else {
                $template = VendorFileUploadTemplate::create($save);
            }
            $data['uploadtemplate'] = $template->VendorFileUploadTemplateID;
        }
        $save = array();
        $option["option"]=  $data['option'];
        $option["selection"] = $data['selection'];
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
}