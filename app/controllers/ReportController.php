<?php

class ReportController extends \BaseController {

    public function index(){
        return View::make('report.index', compact(''));
    }

    public function create(){
        return View::make('report.create', compact(''));
    }
    public function edit(){
        return View::make('report.edit', compact(''));
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

        $data['column'] = array_filter(explode(",",$data['column']));
        //$data['sum'] = array_filter(explode(",",$data['Cube']));
        $data['row'] = array_filter(explode(",",$data['row']));
        $data['sum'] = $response = array();

        $measures = Report::$measures[$cube];
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
            $response = Report::generateDynamicTable($CompanyID, $cube, $data);
        }
        return json_encode(generateReportTable2($data,$response));
    }
}