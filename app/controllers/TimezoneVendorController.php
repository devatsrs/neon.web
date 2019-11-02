<?php

class TimezoneVendorController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /timezonevendor
	 *
	 * @return Response
	 */
	public function search_ajax_datagrid($type,$id){
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['TimeZoneID'] = $data['TimeZoneID'] != '' ? "'".$data['TimeZoneID']."'" : 'null';
        $data['Type'] = $data['Type'] != '' ? "'".$data['Type']."'" : 'null';
        $data['Country'] = $data['Country'] != '' ? "'".$data['Country']."'" : 'null';
        $data['Status'] = !empty($data['Status']) ? 1 : 0;

        $columns     = array('TimezonesID','Title','FromTime','ToTime','DaysOfWeek','DaysOfMonth','Months','ApplyIF','updated_at','updated_by','Status');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getVendorTimezone (" . $data['TimeZoneID'] . "," . $data['Type'] . "," . $data['Country'] . "," . $data['Status'] . "," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "',". $id ."" ;

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            foreach($excel_data as $key => $item) {
                $timeofday = ['Time Of Day' => $item['TimeZone']];
                $excel_data[$key] = array_splice($item, 0, 3, true) + $timeofday + array_slice($item, 3, count($item)-3, true);
                unset($excel_data[$key]['TimeZone']);
            }
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Time Of Day.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Time Of Day.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';
    
        return DataTableSql::of($query)->make();
	}
	
	public function index($id)
	{
        $Account = Account::find($id);
        $companyID = Account::where('AccountID',$id)->pluck('CompanyId');
        $AllTypes =  array('' => "Select") + RateType::$getType;
        $TimeZones =  array('' => "Select") + Timezones::getTimeZoneDropDownList();
        $CountryList = array('' => "All") + Country::lists('Country','CountryID');
        $VendorID = $id;
        $TimeZonesFilter =  array('' => "All") + Timezones::getTimeZoneDropDownList();
        $CountryFilter =  array('' => "All") + Country::lists('Country','CountryID');
        $TypeFilter =  array('' => "All") + RateType::$getType;
        return View::make('vendorratestimezone.index',compact('id','companyID','Account','AllTypes','TimeZones','CountryList','VendorID','TimeZonesFilter','TypeFilter','CountryFilter'));

	}

	/**
	 * Show the form for creating a new resource.
	 * GET /timezonevendor/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /timezonevendor
	 *
	 * @return Response
	 */
	public function store()
	{
		$data = Input::all();
        if(!empty($data)){
            $rules = array(
                "Type"          => "required",
                "TimeZoneID"    => "required",
                "FromTime"      => "required_without_all:DaysOfWeek,DaysOfMonth,Months|date_format:H:i:s",
                "ToTime"        => "required_with:FromTime|date_format:H:i:s",
                "DaysOfWeek"    => "required_without_all:FromTime,DaysOfMonth,Months",
                "DaysOfMonth"   => "required_without_all:DaysOfWeek,FromTime,Months",
                "Months"        => "required_without_all:DaysOfWeek,DaysOfMonth,FromTime"
            );

            

            $AtLeast = "At least 1 field is required from below fields<br/>From Time & To Time, Days Of Week, Days Of Month, Months";
            $message = array(
                "FromTime.required_without_all"     => $AtLeast,
                "DaysOfWeek.required_without_all"   => $AtLeast,
                "DaysOfMonth.required_without_all"  => $AtLeast,
                "Months.required_without_all"       => $AtLeast,
                "ToTime.required_with"              => "The To Time field is required when From Time is present."
            );
            $validator = Validator::make($data, $rules, $message);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            /*if(strtotime($data["FromTime"]) >= strtotime($data["ToTime"]) && !empty($data["FromTime"]) && !empty($data["ToTime"])){
                return  Response::json(array("status" => "failed", "message" => "To time always greater than from time"));
            }*/

            if($data['Country'] == ''){
                $data['Country'] = null;
            }

            // $VendorCheck = VendorTimeZone::where(['Type' => $data['Type'],'Country' => $data['Country'],'TimeZoneID'=> $data['TimeZoneID'],'VendorID' => $data['VendorID']])->first();
            // if(Count($VendorCheck) > 0){
            //     return  Response::json(array("status" => "failed", "message" => "Vendor Time Of Day Already Exist!"));
            // }

            if($data['Country'] == ''){
                $data['Country'] = null;
            }

            $save['Type']           = $data['Type'];
            $save['Country']        = $data['Country'];
            $save['TimeZoneID']     = $data['TimeZoneID'];
            $save['VendorID']       = $data['VendorID'];
            $save['FromTime']       = $data['FromTime'];
            $save['ToTime']         = $data['ToTime'];
            $save['DaysOfWeek']     = !empty($data['DaysOfWeek']) ? implode(',',$data['DaysOfWeek']) : '';
            $save['DaysOfMonth']    = !empty($data['DaysOfMonth']) ? implode(',',$data['DaysOfMonth']) : '';
            $save['Months']         = !empty($data['Months']) ? implode(',',$data['Months']) : '';
            $save['ApplyIF']        = $data['ApplyIF'];
            $save['Status']         = !empty($data['Status']) ? 1 : 0;
            $save['created_at']     = date('Y-m-d H:i:s');
            $save['created_by']     = User::get_user_full_name();
            $save['updated_at']     = date('Y-m-d H:i:s');

            if($Timezones = VendorTimeZone::create($save)){
                return  Response::json(array("status" => "success", "message" => "Time Of Day Successfully Created"));
            } else {
                return  Response::json(array("status" => "failed", "message" => "Problem Creating Time Of Day."));
            }

        } else {
            return  Response::json(array("status" => "failed", "message" => "Invalid Request."));
        }
    }

	/**
	 * Display the specified resource.
	 * GET /timezonevendor/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /timezonevendor/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /timezonevendor/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$data = Input::all();
        if(!empty($data) && $id > 0){
            if($id != 1) {//Can't Edit Default Timezone. Default Timezone ID is 1
                $VendorTimeZone = VendorTimeZone::find($id);
                if (!empty($VendorTimeZone)) {
                    $rules = array(
                        "Type"          => "required",
                        "TimeZoneID"    => "required",
                        "FromTime" => "required_without_all:DaysOfWeek,DaysOfMonth,Months|date_format:H:i:s",
                        "ToTime" => "required_with:FromTime|date_format:H:i:s",
                        "DaysOfWeek" => "required_without_all:FromTime,DaysOfMonth,Months",
                        "DaysOfMonth" => "required_without_all:DaysOfWeek,FromTime,Months",
                        "Months" => "required_without_all:DaysOfWeek,DaysOfMonth,FromTime"
                    );
                    $AtLeast = "At least 1 field is required from below fields<br/>From Time & To Time, Days Of Week, Days Of Month, Months";
                    $message = array(
                        "FromTime.required_without_all" => $AtLeast,
                        "DaysOfWeek.required_without_all" => $AtLeast,
                        "DaysOfMonth.required_without_all" => $AtLeast,
                        "Months.required_without_all" => $AtLeast,
                        "ToTime.required_with" => "The To Time field is required when From Time is present."
                    );
                    $validator = Validator::make($data, $rules, $message);

                    if ($validator->fails()) {
                        return json_validator_response($validator);
                    }

                    /*if(strtotime($data["FromTime"]) >= strtotime($data["ToTime"]) && !empty($data["FromTime"]) && !empty($data["ToTime"])){
                        return  Response::json(array("status" => "failed", "message" => "To time always greater than from time"));
                    }*/

                    if($data['Country'] == ''){
                        $data['Country'] = null;
                    }

                    // $VendorCheck = VendorTimeZone::where(['Type' => $data['Type'],'Country' => $data['Country'],'TimeZoneID'=> $data['TimeZoneID'],'VendorID'=>$data['VendorID']])->where('VendorTimezoneID', '!=' , $id)->first();
                    // if(Count($VendorCheck) > 0){
                    //     return  Response::json(array("status" => "failed", "message" => "Vendor Time Of Day Already Exist!"));
                    // }
                    
                    if($data['Country'] == ''){
                        $data['Country'] = null;
                    }
                    
                    $save['Type']           = $data['Type'];
                    $save['Country']        = $data['Country'];
                    $save['TimeZoneID']     = $data['TimeZoneID'];
                    $save['VendorID']       = $data['VendorID'];
                    $save['FromTime'] = $data['FromTime'];
                    $save['ToTime'] = $data['ToTime'];
                    $save['DaysOfWeek'] = !empty($data['DaysOfWeek']) ? implode(',', $data['DaysOfWeek']) : '';
                    $save['DaysOfMonth'] = !empty($data['DaysOfMonth']) ? implode(',', $data['DaysOfMonth']) : '';
                    $save['Months'] = !empty($data['Months']) ? implode(',', $data['Months']) : '';
                    $save['ApplyIF'] = $data['ApplyIF'];
                    $save['Status'] = !empty($data['Status']) ? 1 : 0;
                    $save['updated_at'] = date('Y-m-d H:i:s');
                    $save['updated_by'] = User::get_user_full_name();

                    if ($VendorTimeZone = $VendorTimeZone->update($save)) {
                        return Response::json(array("status" => "success", "message" => "Time Of Day Successfully Updated"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Updating Time Of Day."));
                    }
                } else {
                    return Response::json(array("status" => "failed", "message" => "Requested Time Of Day not exist."));
                }
            } else {
                return Response::json(array("status" => "failed", "message" => "Can't Edit Default Time Of Day."));
            }
        } else {
            return  Response::json(array("status" => "failed", "message" => "Invalid Request."));
        }
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /timezonevendor/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$delete = VendorTimeZone::where('VendorTimezoneID',$id)->delete();
        if($delete){    
            return Response::json(array("status" => "success", "message" => "Vendor Time Of Day Successfully Deleted"));
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Time Of Day."));
        }
	}

	public function vendor_changeSelectedStatus($type){
        $data = Input::all();
        if(!empty($data['TimezonesIDs']) && !empty($type)){
            $ids        = explode(',',$data['TimezonesIDs']);
            $status     = $type == 'Active' ? 1 : 0;
            $username   = User::get_user_full_name();

            $update = VendorTimeZone::whereIn('VendorTimezoneID',$ids)
                    ->where('Status','!=',$status)
                    ->where('VendorTimezoneID','!=',1) //default timezone
                    ->update(['Status'=>$status,'updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>$username]);

            if ($update) {
                return Response::json(array("status" => "success", "message" => "Time Of Day Status Successfully Changed"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Changing Time Of Day Status."));
            }
        } else {
            return  Response::json(array("status" => "failed", "message" => "Invalid Request."));
        }
    }

}