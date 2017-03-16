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
        $data['sum'] = array('NoOfCalls');

        $CompanyID = User::get_companyID();
        $response = Report::generateDynamicTable($CompanyID,$cube,$data);
        return json_encode(generateReportTable($data,$response));
    }
}