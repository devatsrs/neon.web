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

        $Columns = Report::$dimension['summary']+Report::$measures['summary'];
        $report_settings =array();

        return View::make('report.create', compact('dimensions','measures','Columns','report_settings'));
    }
    public function edit($id){
        $report = Report::find($id);
        $report_settings = json_decode($report->Settings,true);

        $dimensions = Report::$dimension;
        $measures = Report::$measures;

        $Columns = Report::$dimension['summary']+Report::$measures['summary'];
        return View::make('report.create', compact('report','dimensions','measures','Columns','report_settings','report'));
    }

    public function report_store(){
        $postdata = Input::all();
        $response =  NeonAPI::request('report/store',$postdata,true,false,false);
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
        $cube = $data['Cube'];
        $filter_settings = json_decode($data['filter_settings'],true);
        $filters = array();
        if(!empty($filter_settings) && is_array($filter_settings)) {
            foreach ($filter_settings as $key => $filter_setting) {
                parse_str($filter_setting, $filter);
                $filters[$key] = $filter;
            }
        }

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

        $CompanyID = User::get_companyID();
        if(count($data['sum'])) {
            $response = Report::generateDynamicTable($CompanyID, $cube, $data,$filters);
        }
        return json_encode(generateReportTable2($data,$response));
    }

    public function getdatalist(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $ColName = $data['filter_col_name'];
        $query = "CALL prc_getDistinctList('".$CompanyID."','".$ColName."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].")";
        return DataTableSql::of($query,'neon_report')->make();
    }
}