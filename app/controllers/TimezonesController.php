<?php


class TimezonesController extends BaseController {

    public function __construct() {

    }

    public function search_ajax_datagrid($type){
        $data = Input::all();

        $data['iDisplayStart'] +=1;
        $data['Title'] = $data['Title'] != '' ? "'".$data['Title']."'" : 'null';

        $columns     = array('Title','FromTime','ToTime','DaysOfWeek','DaysOfMonth','Months','ApplyIF','created_at','created_by','TimezonesID','Status');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_GetTimezones (" . $data['Title'] . "," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Timezones.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Timezones.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query)->make();
    }

    public function index() {
        return View::make('timezones.index');
    }

    public function store() {
        $data = Input::all();
        if(!empty($data)){
            $rules = array(
                "Title"         => "required|unique:tblTimezones",
                "FromTime"      => "required_without_all:DaysOfWeek,DaysOfMonth,Months|date_format:H:i",
                "ToTime"        => "required_with:FromTime|date_format:H:i",
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

            $save['Title']          = trim($data['Title']);
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

            if($Timezones = Timezones::create($save)){
                return  Response::json(array("status" => "success", "message" => "Timezone Successfully Created"));
            } else {
                return  Response::json(array("status" => "failed", "message" => "Problem Creating Timezone."));
            }

        } else {
            return  Response::json(array("status" => "failed", "message" => "Invalid Request."));
        }
    }

    public function update($id) {
        $data = Input::all();
        if(!empty($data) && $id > 0){
            if($id != 1) {//Can't Edit Default Timezone. Default Timezone ID is 1
                $Timezone = Timezones::find($id);
                if (!empty($Timezone)) {
                    $rules = array(
                        "Title" => "required|unique:tblTimezones,Title," . $id . ",TimezonesID",
                        "FromTime" => "required_without_all:DaysOfWeek,DaysOfMonth,Months|date_format:H:i",
                        "ToTime" => "required_with:FromTime|date_format:H:i",
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

                    $save['Title'] = trim($data['Title']);
                    $save['FromTime'] = $data['FromTime'];
                    $save['ToTime'] = $data['ToTime'];
                    $save['DaysOfWeek'] = !empty($data['DaysOfWeek']) ? implode(',', $data['DaysOfWeek']) : '';
                    $save['DaysOfMonth'] = !empty($data['DaysOfMonth']) ? implode(',', $data['DaysOfMonth']) : '';
                    $save['Months'] = !empty($data['Months']) ? implode(',', $data['Months']) : '';
                    $save['ApplyIF'] = $data['ApplyIF'];
                    $save['Status'] = !empty($data['Status']) ? 1 : 0;
                    $save['updated_at'] = date('Y-m-d H:i:s');
                    $save['updated_by'] = User::get_user_full_name();

                    if ($Timezones = $Timezone->update($save)) {
                        return Response::json(array("status" => "success", "message" => "Timezone Successfully Updated"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Updating Timezone."));
                    }
                } else {
                    return Response::json(array("status" => "failed", "message" => "Requested Timezone not exist."));
                }
            } else {
                return Response::json(array("status" => "failed", "message" => "Can't Edit Default Timezone."));
            }
        } else {
            return  Response::json(array("status" => "failed", "message" => "Invalid Request."));
        }
    }

    public function getTimezonesVariables() {
        return Response::json(array("status" => "success", "message" => "Timezones Variables Successfully Fetched", "ApplyIF" => Timezones::$ApplyIF, "DaysOfWeek" => Timezones::$DaysOfWeek, "Months" => Timezones::$Months));
    }

    /*public function exports($type){
            $companyID = User::get_companyID();
            $data = Input::all();
            if (isset($data['sSearch_0']) && ($data['sSearch_0'] == '' || $data['sSearch_0'] == '1')) {
                $trunks = Trunk::where(["CompanyID" => $companyID, "Status" => 1])->orderBy("TrunkID", "desc")->get(["Trunk", "RatePrefix", "AreaPrefix", "Prefix"]);
            } else {
                $trunks = Trunk::where(["CompanyID" => $companyID, "Status" => 0])->orderBy("TrunkID", "desc")->get(["Trunk", "RatePrefix", "AreaPrefix", "Prefix"]);
            }
            $trunks = json_decode(json_encode($trunks),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Trunks.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($trunks);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Trunks.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($trunks);
            }

    }*/
}
