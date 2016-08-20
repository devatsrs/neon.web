<?php

class DestinationGroupController extends \BaseController {


    public function index() {
        $CodedeckList = BaseCodeDeck::getCodedeckIDList();
        return View::make('destinationgroup.index', compact('CodedeckList'));
    }

    public function ajax_datagrid(){
        $getdata = Input::all();
        $response =  NeonAPI::request('destinationgroupset/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
                $excel_data = $response->data;
                $excel_data = json_decode(json_encode($excel_data),true);
                Excel::create('Destination Group', function ($excel) use ($excel_data) {
                    $excel->sheet('Destination Group', function ($sheet) use ($excel_data) {
                        $sheet->fromArray($excel_data);
                    });
                })->download('xls');
        }
        return json_response_api($response,true,true,true);
    }
    public function store(){
        $postdata = Input::all();
        $response =  NeonAPI::request('destinationgroupset/store',$postdata,true,false,false);
        return json_response_api($response);
    }
        public function delete($id){
        $response =  NeonAPI::request('destinationgroupset/delete/'.$id,array(),'delete',false,false);
        return json_response_api($response);
    }
    public function update($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('destinationgroupset/update/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
    public function show($id) {
        $DestinationGroupSetID = $id;
        $name = DestinationGroupSet::getName($id);
        $discountplanapplied = DiscountPlan::isDiscountPlanApplied('DestinationGroupSet',$id,0);
        return View::make('destinationgroup.show', compact('DestinationGroupSetID','countries','name','discountplanapplied'));
    }
    public function group_show($id) {
        $countries  = Country::getCountryDropdownIDList();
        $DestinationGroupID = $id;
        $DestinationGroupSetID = DestinationGroup::where("DestinationGroupID",$DestinationGroupID)->pluck('DestinationGroupSetID');
        $groupname = DestinationGroupSet::getName($DestinationGroupSetID);
        $name = DestinationGroup::getName($id);
        $discountplanapplied = DiscountPlan::isDiscountPlanApplied('DestinationGroupSet',$DestinationGroupSetID,0);
        return View::make('destinationgroup.groupshow', compact('DestinationGroupSetID','DestinationGroupID','countries','name','groupname','discountplanapplied'));
    }

    public function group_ajax_datagrid(){
        $getdata = Input::all();
        $response =  NeonAPI::request('destinationgroup/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Destination Group Set', function ($excel) use ($excel_data) {
                $excel->sheet('Destination Group Set', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return json_response_api($response,true,true,true);
    }
    public function code_ajax_datagrid(){
        $getdata = Input::all();
        $response =  NeonAPI::request('destinationgroupsetcode/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Destination Group', function ($excel) use ($excel_data) {
                $excel->sheet('Destination Group', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return json_response_api($response,true,true,true);
    }
    public function group_store(){
        $postdata = Input::all();
        if(isset($postdata['RateID'])) {
            $postdata['RateID'] = implode(',', $postdata['RateID']);
        }
        if(isset($postdata['FilterCode'])) {
            $postdata['Code'] = $postdata['FilterCode'];
        }
        if(isset($postdata['FilterDescription'])) {
            $postdata['Description'] = $postdata['FilterDescription'];
        }
        $response =  NeonAPI::request('destinationgroup/store',$postdata,true,false,false);
        return json_response_api($response);
    }
    public function group_delete($id){
        $response =  NeonAPI::request('destinationgroup/delete/'.$id,array(),'delete',false,false);
        return json_response_api($response);
    }
    public function group_update($id){
        $postdata = Input::all();
        if(isset($postdata['RateID'])) {
            $postdata['RateID'] = implode(',', $postdata['RateID']);
        }
        if(isset($postdata['FilterCode'])) {
            $postdata['Code'] = $postdata['FilterCode'];
        }
        if(isset($postdata['FilterDescription'])) {
            $postdata['Description'] = $postdata['FilterDescription'];
        }
        $response =  NeonAPI::request('destinationgroup/update/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
    public function update_name($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('destinationgroup/update_name/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
}