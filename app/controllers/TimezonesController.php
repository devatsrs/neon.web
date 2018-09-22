<?php


class TimezonesController extends BaseController {

    public function __construct() {

    }

    public function search_ajax_datagrid($type){
        $data = Input::all();

        $data['iDisplayStart'] +=1;
        $data['Title'] = $data['Title'] != '' ? "'".$data['Title']."'" : 'null';
        $data['Status'] = !empty($data['Status']) ? 1 : 0;

        $columns     = array('TimezonesID','Title','FromTime','ToTime','DaysOfWeek','DaysOfMonth','Months','ApplyIF','updated_at','updated_by','Status');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_GetTimezones (" . $data['Title'] . "," . $data['Status'] . "," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "'";

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

    public function changeSelectedStatus($type) {
        $data = Input::all();
        if(!empty($data['TimezonesIDs']) && !empty($type)){
            $ids        = explode(',',$data['TimezonesIDs']);
            $status     = $type == 'Active' ? 1 : 0;
            $username   = User::get_user_full_name();

            $update = Timezones::whereIn('TimezonesID',$ids)
                    ->where('Status','!=',$status)
                    ->where('TimezonesID','!=',1) //default timezone
                    ->update(['Status'=>$status,'updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>$username]);

            if ($update) {
                return Response::json(array("status" => "success", "message" => "Timezones Status Successfully Changed"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Changing Timezones Status."));
            }
        } else {
            return  Response::json(array("status" => "failed", "message" => "Invalid Request."));
        }
    }

    public function delete($id,$type) {
        $data = Input::all();
        $Timezone = Timezones::find($id);
        if ($id != 1 && !empty($Timezone)) {
            $RateTableRate  = RateTableRate::where(['TimezonesID'=>$id]);
            $VendorRate     = VendorRate::where(['TimezonesID'=>$id]);
            $CustomerRate   = CustomerRate::where(['TimezonesID'=>$id]);

            // if no rates against any ratetable, customer or vendor then delete timezone straight
            if($RateTableRate->count() == 0 && $VendorRate->count() == 0 && $CustomerRate->count() == 0) {
                if($Timezone->delete()) {
                    return Response::json(array("status" => "success", "message" => "Timezone Deleted Successfully"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem while deleting Timezone"));
                }
            } else {
                // if rates found against any ratetable, customer or vendor then first send confirmation to user in below else condition
                // if second request comes with confirmation "deleteall" then first delete all rates against ratetables,customers and vendors then delete timezone
                if($type == 'deleteall') {
                    try {
                        DB::beginTransaction();
                        $RateTableRate->delete();
                        $VendorRate->delete();
                        $CustomerRate->delete();

                        if($Timezone->delete()) {
                            DB::commit();
                            return Response::json(array("status" => "success", "message" => "Timezone Deleted Successfully"));
                        } else {
                            return Response::json(array("status" => "failed", "message" => "Problem while deleting Timezone"));
                        }
                    } catch (Exception $ex) {
                        DB::rollback();
                        return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
                    }
                } else { // send confirmation to user that all rates will be delete against ratetables/customer/vendors
                    $RateTables = $Vendors = $Customers = $message = $msg = '';
                    $TimezoneName = $Timezone->Title;
                    if($RateTableRate->count() > 0) {
                        $RateTableIds   = $RateTableRate->distinct()->get()->lists('RateTableId');
                        $RateTables     = RateTable::whereIn('RateTableId',$RateTableIds)->get()->lists('RateTableName');
                        $RateTables     = implode(',',$RateTables);

                        $message       .= "\"".$RateTables."\" RateTables has rates under ".$TimezoneName." Timezone\n";
                        $msg           .= 'RateTables/';
                    }
                    if($VendorRate->count() > 0) {
                        $VendorIds      = $VendorRate->distinct()->get()->lists('AccountId');
                        $Vendors        = Account::whereIn('AccountId',$VendorIds)->get()->lists('AccountName');
                        $Vendors        = implode(',',$Vendors);

                        $message       .= "\"".$Vendors."\" Vendors has rates under ".$TimezoneName." Timezone\n";
                        $msg           .= 'Vendors/';
                    }
                    if($CustomerRate->count() > 0) {
                        $CustomerIds    = $CustomerRate->distinct()->get()->lists('CustomerID');
                        $Customers      = Account::whereIn('AccountId',$CustomerIds)->get()->lists('AccountName');
                        $Customers      = implode(',',$Customers);

                        $message       .= "\"".$Customers."\" Customers has rates under ".$TimezoneName." Timezone\n";
                        $msg           .= 'Customers';
                    }
                    $msg = trim($msg,'/');
                    $message .= "\n Are you sure you want to delete all the rates against above listed ".$msg." for ".$TimezoneName." Timezone ?";

                    return Response::json(array("status" => "pending", "message" => $message));
                }
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Requested Timezone not exist."));
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
