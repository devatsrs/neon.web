<?php

class RateTablesMultiAccController extends \BaseController {

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
        $query = "call prc_getRateTableByAccount (".$CompanyID.",'".$data["level"]."',".$TrunkID.",".$ratetableeid.",'".$SourceCustomers."',".$services.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."' ";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            foreach($excel_data as $rowno => $rows){
                foreach($rows as $colno => $colval){
                    $excel_data[$rowno][$colno] = str_replace( "<br>" , "\n" ,$colval );
                }
            }

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/LCR.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/LCR.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        \Illuminate\Support\Facades\Log::info($query);

        return DataTableSql::of($query)->make();

    }

    public function index() {

        $all_customers = Account::getAccountIDList(['IsCustomer'=>1]);
        $companyID = User::get_companyID();
        $trunks = Trunk::getTrunkDropdownIDList();
        $codedecks = BaseCodeDeck::where(["CompanyID" => $companyID])->lists("CodeDeckName", "CodeDeckId");
        $codedecks = array(""=>"Select Codedeck")+$codedecks;
        $rate_tables = RateTable::getRateTables();
        $allservice = Service::getDropdownIDList($companyID);

        return View::make('ratetables.rates_multi_account', compact('all_customers','trunks','codedecks','rate_tables','allservice'));
    }

    public function store() {

        $data = Input::all();
        // $selected_customer = explode(",",$data["selected_customer"]);
        $companyID = User::get_companyID();
        $creaedBy = User::get_user_full_name();

        if(!empty($data["selected_customer"])) {

            $RateTable_Id = $data["RateTable_Id"];
            $TrunkID = $data["TrunkID"];

            if ($data["selected_level"] == 'T') {

                $rules = array(
                    'RateTable_Id' => 'required',
                    'TrunkID' => 'required',
                );
                $message = ['RateTable_Id.required'=>'Rate Table field is required',
                    'TrunkID.required'=>'Trunk field is required'
                ];
                $validator = Validator::make($data, $rules, $message);
                if ($validator->fails()) {
                    return json_validator_response($validator);
                }
                $query = "call prc_applyRateTableTomultipleAccByTrunk (".$companyID.",'".$data["selected_customer"]."','".$TrunkID."','".$RateTable_Id."','".$creaedBy."')";
                DataTableSql::of($query)->make();
                \Illuminate\Support\Facades\Log::info($query);
                try{
                    return json_encode(["status" => "success", "message" => "Rate Table Apply successfully"]);
                }catch ( Exception $ex ){
                    $message =  "Oops Somethings Wrong !";
                    return json_encode(["status" => "fail", "message" => $message]);
                }

            } else {

                $rules = array(
                    'ServiceID' => 'required',
                );
                $message = ['InboundRateTable.required'=>'In Bound Rate Table field is required',
                    'OutboundRateTable.required'=>'Out Bound Rate Table field is required',
                    'ServiceID.required'=>'Service is required'
                ];
                $validator = Validator::make($data, $rules, $message);
                if ($validator->fails()) {
                    return json_validator_response($validator);
                }
                $ServiceID = $data["ServiceID"];
                if(!empty($data["InboundRateTable"]) || !empty($data["InboundRateTable"]) ){
                    $InboundRateTable = (!empty($data["InboundRateTable"]) && $data["InboundRateTable"] > 0 ) ? $data["InboundRateTable"] : 0;
                    $OutboundRateTable = (!empty($data["OutboundRateTable"]) && $data["OutboundRateTable"] > 0 ) ? $data["OutboundRateTable"] : 0;
                    $query = "call prc_applyRateTableTomultipleAccByService (".$companyID.",'".$data["selected_customer"]."','".$ServiceID."','".$InboundRateTable."','".$OutboundRateTable."','".$creaedBy."')";
                    DataTableSql::of($query)->make();
                    log::info($query);
                    try{
                        return json_encode(["status" => "success", "message" => "Successfully Apply Inbound & Outbound RateTable to Customer"]);
                    }catch ( Exception $ex ){
                        $message =  "Oops Somethings Wrong !";
                        return json_encode(["status" => "fail", "message" => $message]);
                    }
                }else{
                    return Response::json(array("status" => "fail", "message" => "Select Inbound or Outbound Ratetable"));
                }

            }

        }else{

            return Response::json(array("status" => "failed", "message" => "Select Customers"));

        }

    }


}