<?php

class AccountNotificationController extends \BaseController {



    public function ajax_datagrid(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $id=$data['accountID'];
        $select = ["NotificationType", "EmailAddresses", "created_at" ,"CreatedBy","AccountNotificationID"];
        $accountNotification = AccountNotification::where(['CompanyID'=>$companyID]);
        if(!empty($data['NotificationType'])){
            $accountNotification->where('NotificationType','=',$data['NotificationType']);
        }
        $accountNotification->select($select);

        return Datatables::of($accountNotification)->make();
    }

	/**
	 * Store a newly created resource in storage.
	 * POST /AccountOneOffCharge
	 *
	 * @return Response
	 */
	public function store($id)
	{
		$data = Input::all();
        $data["AccountID"] = $id;
        $data["CreatedBy"] = User::get_user_full_name();
        $data['COmpanyID'] = User::get_companyID();
        $rules = array(
            'NotificationType'         =>      'required',
            'EmailAddresses'               =>'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['AccountNotificationID']);
        if (AccountNotification::create($data)) {
            return Response::json(array("status" => "success", "message" => "Account Notification Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Account Notification."));
        }
	}

	public function update($AccountID,$AccountNotificationID)
	{
        if( $AccountID  > 0  && $AccountNotificationID > 0 ) {
            $data = Input::all();
            $AccountNotificationID = $data['AccountNotificationID'];
            $AccountNotification = AccountNotification::find($AccountNotificationID);
            $data["AccountID"] = $AccountID;
            $data["ModifiedBy"] = User::get_user_full_name();

            $rules = array(
                'NotificationType'         =>      'required',
                'EmailAddresses'               =>'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['AccountNotificationID']);
            if ($AccountNotification->update($data)) {
                return Response::json(array("status" => "success", "message" => "Account Notification Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Account Notification."));
            }
        }
	}


	public function delete($AccountID,$AccountNotificationID)
	{
        if( intval($AccountNotificationID) > 0){
            try{
                $AccountNotification = AccountNotification::find($AccountNotificationID);
                $result = $AccountNotification->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Account Notification Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Account Notification."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }
        }
	}
}