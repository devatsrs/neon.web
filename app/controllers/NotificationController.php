<?php

class NotificationController extends \BaseController {
    public function ajax_datagrid(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $select = ["NotificationType", "EmailAddresses", "created_at" ,"CreatedBy","NotificationID"];
        $Notification = Notification::where(['CompanyID'=>$companyID]);
        if(!empty($data['NotificationType'])){
            $Notification->where('NotificationType','=',$data['NotificationType']);
        }
        $Notification->select($select);

        return Datatables::of($Notification)->make();
    }

    public function index(){
        $notificationType = array(""=> "Select") + Notification::$type;
        return View::make('notification.index', compact('notificationType'));
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
        $rules = array(
            'NotificationType'         =>      'required',
            'EmailAddresses'               =>'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['NotificationID']);
        if (Notification::create($data)) {
            return Response::json(array("status" => "success", "message" => "Notification Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Notification."));
        }
	}

	public function update($NotificationID)
	{
        if($NotificationID > 0 ) {
            $data = Input::all();
            $NotificationID = $data['NotificationID'];
            $Notification = Notification::find($NotificationID);
            $data["ModifiedBy"] = User::get_user_full_name();

            $rules = array(
                'NotificationType'         =>      'required',
                'EmailAddresses'               =>'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['NotificationID']);
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
}