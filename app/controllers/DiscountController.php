<?php

class DiscountController extends \BaseController {


    public function index() {
        $currencies = Currency::getCurrencyDropdownIDList();
        $DestinationGroupSets = DestinationGroupSet::getDropdownIDList();
        return View::make('discountplan.index', compact('currencies','DestinationGroupSets'));
    }

    public function ajax_datagrid(){
        $getdata = Input::all();
        $response =  NeonAPI::request('discountplan/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Discount Plan', function ($excel) use ($excel_data) {
                $excel->sheet('Discount Plan', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return json_response_api($response,true,true,true);
    }

    public function store(){
        $postdata = Input::all();
        $response =  NeonAPI::request('discountplan/store',$postdata,true,false,false);
        return json_response_api($response);
    }

    public function delete($id){
        $response =  NeonAPI::request('discountplan/delete/'.$id,array(),'delete',false,false);
        return json_response_api($response);
    }

    public function update($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('discountplan/update/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }

    public function show($id) {
        $currencies = Currency::getCurrencyDropdownIDList();
        $DestinationGroupSetID = DiscountPlan::where(array('DiscountPlanID'=>$id))->pluck('DestinationGroupSetID');
        $DestinationGroup = DestinationGroup::getDropdownIDList($DestinationGroupSetID);
        $name = DiscountPlan::getName($id);
        $discountplanapplied = DiscountPlan::isDiscountPlanApplied('DiscountPlan',0,$id);
        return View::make('discountplan.show', compact('currencies','DestinationGroup','id','name','discountplanapplied'));
    }

    public function discount_ajax_datagrid(){
        $getdata = Input::all();
        $response =  NeonAPI::request('discount/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Discount Plan', function ($excel) use ($excel_data) {
                $excel->sheet('Discount Plan', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return json_response_api($response,true,true,true);
    }

    public function discount_store(){
        $postdata = Input::all();
        $response =  NeonAPI::request('discount/store',$postdata,true,false,false);
        return json_response_api($response);
    }

    public function discount_delete($id){
        $response =  NeonAPI::request('discount/delete/'.$id,array(),'delete',false,false);
        return json_response_api($response);
    }

    public function discount_update($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('discount/update/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
}