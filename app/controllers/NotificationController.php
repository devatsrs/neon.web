<?php

class NotificationController extends \BaseController {
    public function ajax_datagrid($type){
        $data = Input::all();
        $companyID = User::get_companyID();
        $select = ["NotificationType", "EmailAddresses","Status", "created_at" ,"CreatedBy","NotificationID"];
        $Notification = Notification::where(['CompanyID'=>$companyID]);
        if(!empty($data['NotificationType'])){
            $Notification->where('NotificationType','=',$data['NotificationType']);
        }
        $Notification->select($select);

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = $Notification->get();
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Notifications.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Notifications.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }

        return Datatables::of($Notification)->make();
    }

    public function index(){
        asort(Notification::$type);
        $notificationType = array(""=> "Select") + Notification::$type;
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $Country = Country::getCountryDropdownIDList();
        $account = Account::getAccountIDList();
        $trunks = Trunk::getTrunkDropdownIDList();
        $qos_alert_type  = Alert::$qos_alert_type;
        $call_monitor_alert_type  = Alert::$call_monitor_alert_type;
        $MultiCountry = $Country;
        if(isset($MultiCountry[""])){unset($MultiCountry[""]);}
        return View::make('notification.index', compact('notificationType','gateway','Country','account','trunks','qos_alert_type','call_monitor_alert_type','MultiCountry'));
    }


    /**
	 * Store a newly created resource in storage.
	 * POST /AccountOneOffCharge
	 *
	 * @return Response
	 */
	public function store()
	{
		$data = Input::all();
        $data["CreatedBy"] = User::get_user_full_name();
        $data['CompanyID'] = User::get_companyID();
        $data['Status'] = isset($data['Status'])?1:0;
        $rules = array(
            'NotificationType'         =>      'required|unique:tblNotification,NotificationType,NULL,CompanyID,CompanyID,' . $data['CompanyID'],
            'EmailAddresses'               =>'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['NotificationID']);
        if ($Notification = Notification::create($data)) {
            return Response::json(array("status" => "success", "message" => "Notification Successfully Created",'redirect'=>URL::to('/notification/edit/' . $Notification->NotificationID)));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Notification."));
        }
	}

	public function update($NotificationID)
	{
        if($NotificationID > 0 ) {
            $data = Input::all();
            $Notification = Notification::find($NotificationID);
            $data["ModifiedBy"] = User::get_user_full_name();
            $data['Status'] = isset($data['Status'])?1:0;

            $rules = array(
                'EmailAddresses'               =>'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['NotificationID']);
            unset($data['NotificationType']);
            if ($Notification->update($data)) {
                return Response::json(array("status" => "success", "message" => "Notification Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Notification."));
            }
        }
	}


	public function delete($NotificationID)
	{
        if( intval($NotificationID) > 0){
            try{
                $Notification = Notification::find($NotificationID);
                $result = $Notification->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Notification Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Notification."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }
        }
	}
    public function qos_store(){
        $postdata = Input::all();
        $response =  NeonAPI::request('qos_alert/store',$postdata,true,false,false);
        return json_response_api($response);
    }
    public function qos_delete($id){
        $response =  NeonAPI::request('qos_alert/delete/'.$id,array(),'delete',false,false);
        return json_response_api($response);
    }

    public function qos_update($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('qos_alert/update/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
    public function qos_ajax_datagrid(){
        $getdata = Input::all();
        $response =  NeonAPI::request('qos_alert/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Alert', function ($excel) use ($excel_data) {
                $excel->sheet('Alert', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return json_response_api($response,true,true,true);
    }
}