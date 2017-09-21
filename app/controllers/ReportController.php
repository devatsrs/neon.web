<?php

class ReportController extends \BaseController {

    public function index(){
        return View::make('report.index', compact(''));
    }

    public function create(){
       /* $data['column'] = array('AccountID');
        $data['row'] = array('Trunk','CompanyGatewayID');
        $data['sum'] = array('NoOfCalls','TotalCharges');
        $cube = 'summary';
        $CompanyID = User::get_companyID();
        $response = Report::generateDynamicTable($CompanyID,$cube,$data);
        //print_r($response);exit;
        //echo generateReportTable($data,$response);
        echo generateReportTable2($data,$response);
        //exit;*/

        $dimensions = Report::$dimension;
        $measures = Report::$measures;
        $disable= '';

        $Columns = $dimensions['summary']+Report::$measures['summary'];
        $report_settings =array();
        $report_settings['Cube'] = 'summary';
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $report_settings['filter_settings'] = '{"date":{"wildcard_match_val":"","start_date":"'.$original_startdate.'","end_date":"'.$original_enddate.'","condition":"none","top":"none"}}';

        return View::make('report.create', compact('dimensions','measures','Columns','report_settings','disable'));
    }
    public function edit($id){
        $report = Report::find($id);
        $report_settings = json_decode($report->Settings,true);

        $dimensions = Report::$dimension;
        $measures = Report::$measures;

        $disable= 'disabled';
        $Columns = $dimensions['summary']+Report::$measures['summary'];
        return View::make('report.create', compact('report','dimensions','measures','Columns','report_settings','report','disable'));
    }

    public function report_store(){
        $postdata = Input::all();
        $response =  NeonAPI::request('report/store',$postdata,true,false,false);
        if(!empty($response->data)) {
            return Response::json(array("status" => $response->status, "message" => $response->message, 'LastID' => $response->data->ReportID, 'redirect' => URL::to('/report/edit/' . $response->data->ReportID)));
        }
        return json_response_api($response);
    }
    public function report_delete($id){
        $response =  NeonAPI::request('report/delete/'.$id,array(),'delete',false,false);
        return json_response_api($response);
    }

    public function report_update($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('report/update/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
    public function ajax_datagrid() {

        $CompanyID = User::get_companyID();
        $currencies = Report::
        select('Name','ReportID')
            ->where("CompanyID", $CompanyID);

        return Datatables::of($currencies)->make();
    }

    public function getdatagrid(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $cube = $data['Cube'];
        $filters = json_decode($data['filter_settings'],true);

        $data['column'] = array_filter(explode(",",$data['column']));
        //$data['sum'] = array_filter(explode(",",$data['Cube']));
        $data['row'] = array_filter(explode(",",$data['row']));
        $data['sum'] = $response = array();

        $measures = array_keys(Report::$measures[$cube]);
        foreach ($measures as $measure){
            if(in_array($measure,$data['column'])){
                $data['sum'][] = $measure;
            }
            if(in_array($measure,$data['row'])){
                $data['sum'][] = $measure;
            }
            if (($key = array_search($measure, $data['column'])) !== false) {
                unset($data['column'][$key]);
            }
            if (($key = array_search($measure, $data['row'])) !== false) {
                unset($data['row'][$key]);
            }
        }
        $data['column'] = array_values($data['column']);
        $data['row'] = array_values($data['row']);
        $all_data_list['CompanyGateway'] = CompanyGateway::getCompanyGatewayIdList();
        $all_data_list['Country'] = Country::getCountryDropdownIDList();
        $all_data_list['Currency'] = Currency::getCurrencyDropdownIDList();
        $all_data_list['Tax'] = TaxRate::getTaxRateDropdownIDList();
        $all_data_list['Product'] = Product::getProductDropdownList();
        $all_data_list['Account'] = Account::getAccountIDList();
        $all_data_list['AccountIP'] = GatewayAccount::getAccountIPList($CompanyID);
        $all_data_list['AccountCLI'] = GatewayAccount::getAccountCLIList($CompanyID);

        $CompanyID = User::get_companyID();
        if(count($data['sum'])) {
            $response = Report::generateDynamicTable($CompanyID, $cube, $data,$filters);
        }
        return json_encode(generateReportTable2($data,$response,$all_data_list));
    }

    public function getdatalist(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $ColName = $data['filter_col_name'];
        $search = $data['sSearch'];
        if(in_array($ColName,array('InvoiceType','InvoiceStatus','ProductType','PaymentMethod','PaymentType'))){
            return generate_manual_datatable_response($ColName);
        }
        $Accountschema = Report::$dimension['summary']['Customer'];
        if(in_array($ColName,$Accountschema) && $ColName != 'AccountID'){
            $accounts = Account::where(["AccountType" => 1, "CompanyID" => $CompanyID, "Status" => 1])
                ->select(array($ColName.' as 2',$ColName))
                ->distinct()
                ->orderBy($ColName);
            if(!empty($search)){
                $accounts->where($ColName,'like','%'.$search.'%');
            }
            return Datatables::of($accounts)->make();
        }
        $query = "CALL prc_getDistinctList('".$CompanyID."','".$ColName."','".$search."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].")";
        return DataTableSql::of($query,'neon_report')->make();
    }
}