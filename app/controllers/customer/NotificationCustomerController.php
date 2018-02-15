<?php

class NotificationCustomerController extends \BaseController {
    public function ajax_datagrid($type){
        $data = Input::all();
        $companyID = User::get_companyID();
        $select = ["Name","AlertType","Status", "created_at" ,"CreatedBy","AlertID","Settings"];
        $tag = '"AccountID":"' . Customer::get_accountID() . '"';
        $Notification = Alert::where(['CompanyID'=>$companyID,'CreatedByCustomer'=>1])->where('Settings', 'LIKE', '%' . $tag . '%');

        $Notification->select($select);

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = $Notification->get();
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Notifications.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Notifications.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }

        return Datatables::of($Notification)->make();
    }

    public function index(){

        Alert::multiLang_init();
        asort(Notification::$type);
        $notificationType = array(""=> "Select") + Notification::$type;
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $Country = Country::getCountryDropdownIDList();
        $account = Account::getAccountIDList();
        $trunks = Trunk::getTrunkDropdownIDList();
        $qos_alert_type  = Alert::$qos_alert_type;
        $call_monitor_alert_type  = Alert::$call_monitor_customer_alert_type;
        $MultiCountry = $Country;
        $Multiaccount = $account;
        $Multitrunks = $trunks;
        $Multigateway = $gateway;
        if(isset($MultiCountry[""])){unset($MultiCountry[""]);}
        if(isset($Multiaccount[""])){unset($Multiaccount[""]);}
        if(isset($Multitrunks[""])){unset($Multitrunks[""]);}
        if(isset($Multigateway[""])){unset($Multigateway[""]);}
        return View::make('customer.notification.index', compact('notificationType','gateway','Country','account','trunks','qos_alert_type','call_monitor_alert_type','MultiCountry','Multiaccount','Multitrunks','Multigateway'));
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
        $data["CreatedByCustomer"] = 1;
        $data['CompanyID'] = User::get_companyID();
        //$data['Name'] = Customer::get_accountName().' - '.Alert::$call_monitor_customer_alert_type[$data['AlertType']];
        $rules = Alert::$rules;
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $error_message = self::data_validate($data);
        if(!empty($error_message)){
            return Response::json(array("status" => "failed", "message" =>$error_message));
        }
        $data = self::convert_data($data)+$data;
        if ($Notification = Alert::create($data)) {
            return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_NOTIFICATION_SUCCESSFULLY_CREATED'),'redirect'=>URL::to('/notification/edit/' . $Notification->NotificationID)));
        } else {
            return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_PROBLEM_CREATING_NOTIFICATION')));
        }
	}

	public function update($AlertID)
	{
        if($AlertID > 0 ) {
            $data = Input::all();
            $Notification = Alert::find($AlertID);
            $data["ModifiedBy"] = User::get_user_full_name();
            $rules = Alert::$rules;
            //unset($rules['Name']);
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $error_message = self::data_validate($data);
            if(!empty($error_message)){
                return Response::json(array("status" => "failed", "message" =>$error_message));
            }
            $data = self::convert_data($data)+$data;
            if ($Notification->update($data)) {
                return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_NOTIFICATION_SUCCESSFULLY_UPDATED')));
            } else {
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_PROBLEM_UPDATING_NOTIFICATION')));
            }
        }
	}


	public function delete($AlertID)
	{
        if( intval($AlertID) > 0){
            try{
                AlertLog::where('AlertID',$AlertID)->delete();
                $Notification = Alert::find($AlertID);
                $result = $Notification->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_NOTIFICATION_SUCCESSFULLY_DELETED')));
                } else {
                    return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_PROBLEM_DELETING_NOTIFICATION')));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.MESSAGE_PROBLEM_DELETING_EXCEPTION'). $ex->getMessage()));
            }
        }
	}

    public function convert_data($post_data,$Alert=array()){
        $class_data = array();
        $class_data['Status'] = isset($post_data['Status'])?1:0;
        if(!empty($Alert)){
            $Settings = json_decode($Alert->Settings);
        }
        if($post_data['AlertGroup'] == Alert::GROUP_QOS){
            $class_data['LowValue'] = floatval($post_data['LowValue']);
            $class_data['HighValue'] = floatval($post_data['HighValue']);
            if(isset($Settings->LastRunTime)){
                $post_data['QosAlert']['LastRunTime'] = $Settings->LastRunTime;
            }
            if(isset($Settings->NextRunTime)){
                $post_data['QosAlert']['NextRunTime'] = $Settings->NextRunTime;
            }
            $class_data['Settings'] = json_encode($post_data['QosAlert']);
        }else if ($post_data['AlertGroup'] == Alert::GROUP_CALL) {
            if(isset($Settings->LastRunTime)){
                $post_data['CallAlert']['LastRunTime'] = $Settings->LastRunTime;
            }
            if(isset($Settings->NextRunTime)){
                $post_data['CallAlert']['NextRunTime'] = $Settings->NextRunTime;
            }

            $class_data['Settings'] = json_encode($post_data['CallAlert']);
        }

        return $class_data;
    }
    public function data_validate($post_data)
    {
        $error_message = '';

        if ($post_data['AlertGroup'] == Alert::GROUP_QOS) {
            if (empty($post_data['QosAlert']['Interval'])) {
                $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_QOS_ALERT_INTERVAL_IS_REQUIRED');
            }
            if (empty($post_data['QosAlert']['Time'])) {
                $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_QOS_ALERT_TIME_IS_REQUIRED');
            }
        } else if ($post_data['AlertGroup'] == Alert::GROUP_CALL) {

            if ($post_data['AlertType'] == 'block_destination') {
                if(empty($post_data['CallAlert']['BlacklistDestination'])) {
                    $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_AT_LEAST_ONE_BLACKLIST_DESTINATION_IS_REQUIRED');
                }
                if(empty($post_data['CallAlert']['ReminderEmail'])){
                    $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_EMAIL_ADDRESS_IS_REQUIRED');
                }
            } else if ($post_data['AlertType'] == 'call_duration' || $post_data['AlertType'] == 'call_cost' || $post_data['AlertType'] == 'call_after_office') {
                if (empty($post_data['CallAlert']['AccountID'])) {
                    $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_ACCOUNT_IS_REQUIRED');
                }else{
                    $tag = '"AccountID":"' . $post_data['CallAlert']['AccountID'] . '"';
                    if (!empty($post_data['AlertID'])) {
                        if (Alert::where('Settings', 'LIKE', '%' . $tag . '%')->where(['AlertType'=>$post_data['AlertType'],'CreatedByCustomer'=>1])->where('AlertID', '<>', $post_data['AlertID'])->count() > 0) {
                            $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_ALERTTYPE_IS_ALREADY_TAKEN');
                        }
                    }else{
                        if (Alert::where('Settings', 'LIKE', '%' . $tag . '%')->where(['AlertType'=>$post_data['AlertType'],'CreatedByCustomer'=>1])->count() > 0) {
                            $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_ALERTTYPE_IS_ALREADY_TAKEN');
                        }
                    }
                }

                if ($post_data['AlertType'] == 'call_duration' && empty($post_data['CallAlert']['Duration'])) {
                    $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_DURATION_IS_REQUIRED');
                } else if ($post_data['AlertType'] == 'call_cost' && empty($post_data['CallAlert']['Cost'])) {
                    $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_COST_IS_REQUIRED');
                } else if ($post_data['AlertType'] == 'call_after_office' && empty($post_data['CallAlert']['OpenTime'])) {
                    $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_OPEN_TIME_IS_REQUIRED');
                } else if ($post_data['AlertType'] == 'call_after_office' && empty($post_data['CallAlert']['CloseTime'])) {
                    $error_message = Lang::get('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_CLOSE_TIME_IS_REQUIRED');
                }

            }

        }
        return $error_message;
    }

}