<?php

class DestinationGroupController extends \BaseController {


    public function index() {
        $CodedeckList = BaseCodeDeck::getCodedeckIDList();
        return View::make('destinationgroup.index', compact('CodedeckList'));
    }

    public function ajax_datagrid(){
        $getdata = Input::all();
        $DestinationGroupActilead = UserActivity::UserActivitySaved($getdata,'View','Destination Group Set');
        $response =  NeonAPI::request('destinationgroupset/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $export_type['type'] = 'xls';
            $DestinationGroupActilead = UserActivity::UserActivitySaved($export_type,'Export','Destination Group Set');    
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
        if($response->status == 'success'){
            $DestinationGroupActilead = UserActivity::UserActivitySaved($postdata,'Add','Destination Group Set',$postdata['Name']);
        }
        return json_response_api($response);
    }
        public function delete($id){
        $data['id'] = $id;
        $response =  NeonAPI::request('destinationgroupset/delete/'.$id,array(),'delete',false,false);
        if($response->status == 'success'){
            $DestinationGroupActilead = UserActivity::UserActivitySaved($data,'Delete','Destination Group Set');
        }
        return json_response_api($response);
    }
    public function update($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('destinationgroupset/update/'.$id,$postdata,'put',false,false);
        if($response->status == 'success'){
            $DestinationGroupActilead = UserActivity::UserActivitySaved($postdata,'Edit','Destination Group Set',$postdata['Name']);
        }
        return json_response_api($response);
    }
    public function show($id) {
        $data['id'] = $id;
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
        $DestinationGroupActilead = UserActivity::UserActivitySaved($getdata,'View','Destination Group');
        $response =  NeonAPI::request('destinationgroup/datagrid',$getdata,false,false,false);
        
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $export_type['type'] = 'xls';
            $DestinationGroupActilead = UserActivity::UserActivitySaved($export_type,'Export','Destination Group');
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
        $DestinationGroupActilead = UserActivity::UserActivitySaved($getdata,'View','Destination Group Code');
        $response =  NeonAPI::request('destinationgroupsetcode/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $export_type['type'] = 'xls';
            $DestinationGroupActilead = UserActivity::UserActivitySaved($export_type,'Export','Destination Group Code');
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
        if($response->status == 'success'){
            $DestinationGroupActilead = UserActivity::UserActivitySaved($postdata,'Add','Destination Group',$postdata['Name']);
        }
        return json_response_api($response);
    }
    public function group_delete($id){
        $getdata['id'] = $id; 
        $response =  NeonAPI::request('destinationgroup/delete/'.$id,array(),'delete',false,false);
        if($response->status == 'success'){
            $DestinationGroupActilead = UserActivity::UserActivitySaved($getdata,'Delete','Destination Group');
        }
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
        if($response->status == 'success'){
            $DestinationGroupActilead = UserActivity::UserActivitySaved($postdata,'Edit','Destination Group',$postdata['Name']);
        }
        return json_response_api($response);
    }
    public function update_name($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('destinationgroup/update_name/'.$id,$postdata,'put',false,false);
        if($response->status == 'success'){
            $DestinationGroupActilead = UserActivity::UserActivitySaved($postdata,'Edit','Destination Group',$postdata['Name']);
        }
        return json_response_api($response);
    }
}