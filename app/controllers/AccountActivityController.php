<?php
use Carbon\Carbon;
class AccountActivityController extends \BaseController {



    public function ajax_datagrid($AccountID){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $select = ["Title", "Description", "Date" ,"ActivityType","created_at","ActivityID"];
        $activities = AccountActivity::where(array('AccountID'=>$AccountID,'CompanyID'=>$CompanyID));
        $today = Carbon::toDay()->toDateTimeString();
        if($data['activityStatus']==1){
            $activities->where('Date','>=',$today);
        }else{
            $activities->where('Date','<=',$today);
        }
        if(!empty($data['activityType'])){
            $activities->where(array('activityType'=>$data['activityType']));
        }
        if(!empty($data['Title'])){
            $activities->where('Title','like','%'.$data['Title'].'%');
        }
        $activities->select($select);
        return Datatables::of($activities)->make();
    }

	/**
	 * Store a newly created resource in storage.
	 * POST /accountsubscription
	 *
	 * @return Response
	 */
	public function store($AccountID)
	{
		$data = Input::all();
        $data["AccountID"] = $AccountID;
        $data['CompanyID'] = User::get_companyID();
        $data["CreatedBy"] = User::get_user_full_name();

        $rules = array(
            'Title' =>      'required',
            'ActivityType'=>'required',
            'Date'=>'required',
            'Time'=>'required'
        );
        $validator = Validator::make($data, $rules);
        $data['Date'] = $data['Date'].' '.$data['Time'];
        unset($data['activityID']);
        unset($data['Time']);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if (AccountActivity::create($data)) {
            return Response::json(array("status" => "success", "message" => "Activity Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Activity."));
        }
	}

	public function update($AccountID,$ActivityID)
	{
        $data = Input::all();
        $AccountActivity = AccountActivity::find($ActivityID);
        $data["AccountID"] = $AccountID;
        $data['CompanyID'] = User::get_companyID();
        $data["ModifiedBy"] = User::get_user_full_name();
        $rules = array(
            'Title' =>      'required',
            'ActivityType'=>'required',
            'Date'=>'required',
            'Time'=>'required'
        );
        $validator = Validator::make($data, $rules);
        $data['Date'] = $data['Date'].' '.$data['Time'];
        unset($data['activityID']);
        unset($data['Time']);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if ($AccountActivity->update($data)) {
            return Response::json(array("status" => "success", "message" => "Activity Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Activity."));
        }
	}


	public function delete($AccountID,$ActivityID)
	{
        if( intval($ActivityID) > 0){
            try{
                $AccountActivity = AccountActivity::find($ActivityID);
                $result = $AccountActivity->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Activity Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Activity."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }
        }
	}

    public function ajax_datagrid_email_log($AccountID){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $where['Accountid'] = $AccountID;
        $where['CompanyID'] = $CompanyID;
        $select = ["Emailfrom","EmailTo","Subject","created_at","CreatedBy","AccountEmailLogID"];
        $emaillog = AccountEmailLog::where(array('AccountID'=>$AccountID,'CompanyID'=>$CompanyID));
        $emaillog->select($select);
        return Datatables::of($emaillog)->make();
    }

    public function sendMail($AccountID){
        $data = Input::all();
        $rules = array(
            'Subject'=>'required',
            'Message'=>'required'
        );
       $account = Account::find($AccountID);
        $CompanyID = User::get_companyID();
        if(getenv('EmailToCustomer') == 1){
            $data['EmailTo'] = $account->Email;//$account->Email;
        }else{
            $data['EmailTo'] = Company::getEmail($CompanyID);//$account->Email;
        }
        $validator = Validator::make($data,$rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
		
        try{
            $status = sendMail('emails.account.AccountEmailSend',$data);
            if($status['status'] == 1){
                $data['AccountID'] = $account->AccountID;
                email_log($data);
                return Response::json(array("status" => "success", "message" => "Email sent Successfully"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Sending Email."));
            }
        }catch (Exception $ex){
            return Response::json(array("status" => "failed", "message" => "Problem sending. Exception:". $ex->getMessage()));
        }


    }
	
	public function sendMailApi($AccountID)
	{		
	    $data 					= 	Input::all();
		$data['AccountID']		=   $AccountID;
		$emailattachments		=   $data['emailattachment_sent'];		
		$all_files 				=	Session::get("activty_email_attachments");
		$email_files_sent		=	array();
		
		//token_attachment
		$files_array	=	Session::get("activty_email_attachments");
		
		
		/*if($emailattachments!='')       
		{
			$emailattachments_array = explode(",",$emailattachments);
			if(is_array($emailattachments_array))
			{
				foreach($emailattachments_array as $emailattachments_data)
				{
					
					$temp_path				=	getenv('TEMP_PATH').'/email_attachment/'.$AccountID.'/'.$emailattachments_data;
					$email_files_sent[]		= 	$temp_path;
				}
			
			}
		}*/
		
       	//$data['file']			=	NeonAPI::base64byte($email_files_sent);
		$data['file']			=	$files_array[$data['token_attachment']];
		
		$data['name']			=    Auth::user()->FirstName.' '.Auth::user()->LastName;
		
		$data['address']		=    Auth::user()->EmailAddress;
	   
		 $response 				= 	NeonAPI::request('accounts/sendemail',$data,true,false,true);				
		
		if(!isset($response->status_code)){
				return  json_response_api($response);
			}
			
			if ($response->status_code == 200) {	
				$logID 	  		 = 	$response->LogID;					
				$response 		 = 	$response->data->result;
				$response->type  = 	2;				
				$response->LogID = 	$logID;
				unset($files_array[$data['token_attachment']]);
				Session::set("activty_email_attachments", $files_array); 
			}
			else{
			 return  json_response_api($response);
			}
			
			$key 			= $data['scrol']!=""?$data['scrol']:0;	
			$current_user_title = Auth::user()->FirstName.' '.Auth::user()->LastName;
			return View::make('accounts.show_ajax_single', compact('response','current_user_title','key'));  
	}

    public function delete_email_log($AccountID,$logID){
        if( intval($logID) > 0){
            try{
                $accountemaillog = AccountEmailLog::find($logID);
                $result = $accountemaillog->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Email log Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Email log."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }
        }
    }

    public function view_email_log($AccountID,$logID){
       return AccountEmailLog::find($logID);
    }

}