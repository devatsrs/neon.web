<?php

class RateGeneratorsController extends \BaseController {

    public function ajax_datagrid() {
        $companyID = User::get_companyID();
        $data = Input::all(); 
        $where = ["tblRateGenerator.CompanyID" => $companyID];
        if($data['Active']!=''){
            $where['tblRateGenerator.Status'] = $data['Active'];
        }
		
		
		
        $RateGenerators = RateGenerator::
        join("tblTrunk","tblTrunk.TrunkID","=","tblRateGenerator.TrunkID")
        ->leftjoin("tblCurrency","tblCurrency.CurrencyId","=","tblRateGenerator.CurrencyId")
        ->where($where)->select(array(
            'tblRateGenerator.RateGeneratorName',
            'tblTrunk.Trunk',
            'tblCurrency.Code',
            'tblRateGenerator.Status',
            'tblRateGenerator.RateGeneratorId',
            'tblRateGenerator.TrunkID',
            'tblRateGenerator.CodeDeckId',
            'tblRateGenerator.CurrencyID',
                )); // by Default Status 1

		if(isset($data['Search']) && !empty($data['Search'])){
            $RateGenerators->WhereRaw('tblRateGenerator.RateGeneratorName like "%'.$data['Search'].'%"'); 
        }	
		if(isset($data['Trunk']) && !empty($data['Trunk'])){
            $RateGenerators->WhereRaw('tblRateGenerator.TrunkID = '.$data['Trunk'].''); 
        }	
			
        return Datatables::of($RateGenerators)->make();
    }

    public function index() {
		$Trunks =  Trunk::getTrunkDropdownIDList();
		return View::make('rategenerators.index', compact('Trunks'));
    }

    
    public function create() {
            $trunks = Trunk::getTrunkDropdownIDList();
            $trunk_keys = getDefaultTrunk($trunks);
            $codedecklist = BaseCodeDeck::getCodedeckIDList();
            $currencylist = Currency::getCurrencyDropdownIDList();
            $Timezones = Timezones::getTimezonesIDList();
            return View::make('rategenerators.create', compact('trunks','codedecklist','currencylist','trunk_keys','Timezones'));
    }

    public function store() {
        $data = Input::all();

        $companyID = User::get_companyID();
        $data ['CompanyID'] = $companyID;
        $data ['UseAverage'] = isset($data ['UseAverage']) ? 1 : 0;
        $data ['UsePreference'] = isset($data ['UsePreference']) ? 1 : 0;
        $data ['Timezones'] = isset($data ['Timezones']) ? implode(',', $data['Timezones']) : '';
        $rules = array(
            'CompanyID' => 'required',
            'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,NULL,CompanyID,CompanyID,'.$data['CompanyID'],
            'TrunkID' => 'required',
            'Timezones' => 'required',
            'RatePosition' => 'required|numeric',
            'UseAverage' => 'required',
            'codedeckid' => 'required',
            'CurrencyID' => 'required',
            'Policy' => 'required',
            'GroupBy' => 'required',
        );

        $message = array(
            'Timezones.required' => 'Please select at least 1 Timezone'
        );

        if(!empty($data['IsMerge'])) {
            $rules['TakePrice'] = "required";
            $rules['MergeInto'] = "required";
        }

        $validator = Validator::make($data, $rules, $message);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if ($rateg = RateGenerator::create($data)) {
            return Response::json(array(
                        "status" => "success",
                        "message" => "RateGenerator Successfully Created",
                        'LastID'=>$rateg->RateGeneratorId,
                        'redirect' => URL::to('/rategenerators/'.$rateg->RateGeneratorId.'/edit')
                    ));
        } else {
            return Response::json(array(
                        "status" => "failed",
                        "message" => "Problem Creating RateGenerator."
                    ));
        }
    }
    
    /**
     * Show the form for editing the specified resource.
     * GET /rategenerators/{id}/edit
     *
     * @param int $id        	
     * @return Response
     */
    public function edit($id) {
            if ($id) {
                $trunks = Trunk::getTrunkDropdownIDList();
                $companyID = User::get_companyID();
                $rategenerators = RateGenerator::where([
                    "RateGeneratorId" => $id,
                    "CompanyID" => $companyID
                ])->first();
                $rategenerator_rules = RateRule::with('RateRuleMargin', 'RateRuleSource')->where([
                    "RateGeneratorId" => $id
                ]) ->orderBy("Order", "asc")->get();
                $array_op= array();
                $codedecklist = BaseCodeDeck::getCodedeckIDList();
                $currencylist = Currency::getCurrencyDropdownIDList();
                if(count($rategenerator_rules)){
                    $array_op['disabled'] = "disabled";
                }
                    $rategenerator = RateGenerator::find($id);
                $Timezones = Timezones::getTimezonesIDList();

                // Debugbar::info($rategenerator_rules);
                return View::make('rategenerators.edit', compact('id', 'rategenerators','rategenerator', 'rategenerator_rules','codedecklist', 'trunks','array_op','currencylist','Timezones'));
            }
    }

    /**
     * Update the specified resource in storage.
     * PUT /rategenerators/{id}
     *
     * @param int $id        	
     * @return Response
     */
    public function update($id) {
        $data = Input::all();
        $RateGenerator = RateGenerator::find($id);

        $companyID = User::get_companyID();
        $data ['CompanyID'] = $companyID;
        $data ['UseAverage'] = isset($data ['UseAverage']) ? 1 : 0;
        $data ['UsePreference'] = isset($data ['UsePreference']) ? 1 : 0;
        $data ['Timezones'] = isset($data ['Timezones']) ? implode(',', $data['Timezones']) : '';
        $rules = array(
            'CompanyID' => 'required',
            'RateGeneratorName' => 'required|unique:tblRateGenerator,RateGeneratorName,'.$RateGenerator->RateGeneratorId.',RateGeneratorID,CompanyID,'.$data['CompanyID'],
            'TrunkID' => 'required',
            'Timezones' => 'required',
            'RatePosition' => 'required|numeric',
            'UseAverage' => 'required',
            'codedeckid' => 'required',
            'CurrencyID' => 'required',
            'Policy' => 'required',
        );
        
        $message = array(
            'Timezones.required' => 'Please select at least 1 Timezone'
        );

        if(!empty($data['IsMerge'])) {
            $rules['TakePrice'] = "required";
            $rules['MergeInto'] = "required";
        } else {
            $data['IsMerge'] = 0;
        }

        $validator = Validator::make($data, $rules, $message);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data ['ModifiedBy'] = User::get_user_full_name();
        if ($RateGenerator->update($data)) {
            return Response::json(array(
                        "status" => "success",
                        "message" => "RateGenerator Successfully Updated"
                    ));
        } else {
            return Response::json(array(
                        "status" => "failed",
                        "message" => "Problem Updating RateGenerator."
                    ));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /rategenerators/{id}
     *
     * @param int $id        	
     * @return Response
     */
    public function rules($id) {
            if ($id) {
                // $companyID = User::get_companyID();
                $rategenerator_rules = RateRule::with('RateRuleMargin', 'RateRuleSource')->where([
                    "RateGeneratorId" => $id
                ])->get();
                return View::make('rategenerators.rule', compact('id', 'rategenerator_rules'));
            }
    }


    public function delete($id) {
        if ($id) {
            if (RateGenerator::find($id)->delete()) {
                return Response::json(array("status" => "success", "message" => "Rate Generator Successfully deleted"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Rate Generator"));
            }
        }
    }

    public function generate_rate_table($id, $action) {
        if ($id && $action) {
            try {
                DB::beginTransaction();
                $RateGeneratorId = $id;


                $data = compact("RateGeneratorId");
                $data["EffectiveDate"] = Input::get('EffectiveDate');
                $checkbox_replace_all = Input::get('checkbox_replace_all');
                $data['EffectiveRate'] = Input::get('EffectiveRate');

                $IncreaseEffectiveDate = Input::get('IncreaseEffectiveDate');

                if(!empty($IncreaseEffectiveDate)) {
                    $data['IncreaseEffectiveDate']  =   $IncreaseEffectiveDate;
                }

                $DecreaseEffectiveDate = Input::get('DecreaseEffectiveDate');
                if(!empty($DecreaseEffectiveDate)) {
                    $data['DecreaseEffectiveDate']  =   $DecreaseEffectiveDate;
                }

                if(empty($data['EffectiveRate'])){
                    $data['EffectiveRate']='now';
                }
                if(!empty($checkbox_replace_all) && $checkbox_replace_all == 1){
                    $data['replace_rate'] = 1;
                }else{
                    $data['replace_rate'] = 0;
                }
                $data ['CompanyID'] = User::get_companyID();

                if($action == 'create'){
                    $RateTableName = Input::get('RateTableName');
                    $data["rate_table_name"] = $RateTableName;
                    $data['ratetablename'] = $RateTableName;
                    $rules = array(
                        'rate_table_name' => 'required|unique:tblRateTable,RateTableName,NULL,CompanyID,CompanyID,'.$data['CompanyID'].',RateGeneratorID,'.$id,
                        'EffectiveDate'=>'required'
                    );
                }else if($action == 'update'){
                    $RateTableID = Input::get('RateTableID');
                    $data["RateTableId"] = $RateTableID;
                    $data['ratetablename'] = RateTable::where(["RateTableId" => $RateTableID])->pluck('RateTableName');
                    $rules = array(
                        'RateTableId' => 'required',
                        'EffectiveDate'=>'required'
                    );
                }
                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return json_validator_response ( $validator );
                }
                $ExchangeRateStatus = RateGenerator::checkExchangeRate($RateGeneratorId);
                if($ExchangeRateStatus['status'] == 1){
                    return Response::json(array("status" => "failed", "message" => $ExchangeRateStatus['message']));
                }
                /* Old way to get RateTableID
                 *
                 * $RateGenerator = RateGenerator::find($RateGeneratorId);
                 if(!empty($RateGenerator) && is_object($RateGenerator) ){
                    $RateTableID = $RateGenerator->RateTableId;
                    if(is_numeric($RateTableID)  ){
                        $data["RateTableID"] = $RateTableID;
                    }
                }*/

                $result = Job::logJob("GRT", $data);
                if ($result ['status'] != "success") {
                    DB::rollback();
                    return json_encode([
                        "status" => "failed",
                        "message" => $result ['message']
                            ]);
                }
                DB::commit();
                return json_encode([
                    "status" => "success",
                    "message" => "Rate Generator Job Added in queue to process. You will be informed once Job Done. "
                        ]);
            } catch (Exception $ex) {
                DB::rollback();
                return json_encode([
                    "status" => "failed",
                    "message" => " Exception: " . $ex->getMessage()
                        ]);
            }
        }
    }

    public function change_status($id, $status) {
        if ($id > 0 && ( $status == 0 || $status == 1)) {
            if (RateGenerator::find($id)->update(["Status" => $status, "ModifiedBy" => User::get_user_full_name()])) {
                return Response::json(array("status" => "success", "message" => "Status Successfully Changed"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Changing Status."));
            }
        }
    }
    public function exports($type) {
            $companyID = User::get_companyID();
            $RateGenerators = RateGenerator::join("tblTrunk","tblTrunk.TrunkID","=","tblRateGenerator.TrunkID")->where(["tblRateGenerator.CompanyID" => $companyID])
                ->orderBy("RateGeneratorID", "desc")
                ->get(array(
                    'RateGeneratorName',
                    'tblTrunk.Trunk',
                    'tblRateGenerator.Status',
                ));
            $excel_data = json_decode(json_encode($RateGenerators),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Rate Generator.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Rate Generator.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
    }

    public function ajax_load_rate_table_dropdown(){
        $data = Input::all();
        if(isset($data['TrunkID']) && intval($data['TrunkID']) > 0) {
            $filterdata['TrunkID'] = intval($data['TrunkID']);
            $filterdata['CurrencyID'] = intval($data['CurrencyID']);
            $filterdata['CodeDeckId'] = intval($data['CodeDeckId']);
            $rate_table = RateTable::getRateTableCache($filterdata);
            return View::make('rategenerators.ajax_rate_table_dropdown', compact('rate_table'));
        }
        return '';
    }

    public function ajax_existing_rategenerator_cronjob($id){
        $companyID = User::get_companyID();
        $tag = '"rateGeneratorID":"'.$id.'"';
        $cronJobs = CronJob::where('Settings','LIKE', '%'.$tag.'%')->where(['CompanyID'=>$companyID])->select(['JobTitle','Status','created_by','CronJobID'])->get()->toArray();
        return View::make('rategenerators.ajax_rategenerator_cronjobs', compact('cronJobs'));
    }

    public function deleteCronJob($id){
        $data = Input::all();
        try{
            $cronjobs = explode(',',$data['cronjobs']);
            foreach($cronjobs as $cronjobID){
                $cronjob = CronJob::find($cronjobID);
                if($cronjob->Active){
                    $Process = new Process();
                    $Process->change_crontab_status(0);
                }
                $cronjob->delete();
                CronJobLog::where("CronJobID",$cronjobID)->delete();
            }
            return Response::json(array("status" => "success", "message" => "Cron Job Successfully Deleted"));
        }catch (Exception $ex){
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    function Update_Fields_Sorting(){
        $postdata    =  Input::all();
        if(isset($postdata['main_fields_sort']) && !empty($postdata['main_fields_sort']))
        {
           // print_R($postdata);exit;
            try
            {
                DB::beginTransaction();
                $main_fields_sort = json_decode($postdata['main_fields_sort']);
                foreach($main_fields_sort as $main_fields_sort_Data){
                    RateRule::find($main_fields_sort_Data->data_id)->update(array("Order"=>$main_fields_sort_Data->Order));
                }
                DB::commit();
                return Response::json(["status" => "success", "message" => "Order Successfully updated."]);
            } catch (Exception $ex) {
                DB::rollback();
                return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
            }
        }
    }

}