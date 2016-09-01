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

        $data = Input::all();
        $rules = array(
            'Subject'=>'required',
            'Message'=>'required'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
		$data['AccountID']		=   $AccountID;

        $attachmentsinfo            =$data['attachmentsinfo'];
        if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
            $files_array = json_decode($attachmentsinfo,true);
        }

        if(!empty($files_array) && count($files_array)>0) {
            $FilesArray = array();
            foreach($files_array as $key=> $array_file_data){
                $file_name = basename($array_file_data['filepath']);
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['EMAIL_ATTACHMENT']);
                $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                copy($array_file_data['filepath'], $destinationPath . $file_name);

                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload file." ));
                }
                $FilesArray[] = array ("filename"=>$array_file_data['filename'],"filepath"=>$amazonPath . $file_name);
                unlink($array_file_data['filepath']);
            }
            $data['file']		=	json_encode($FilesArray);
		}
		
		$data['name']			=    Auth::user()->FirstName.' '.Auth::user()->LastName;
		
		$data['address']		=    Auth::user()->EmailAddress;
	   
		 $response 				= 	NeonAPI::request('accounts/sendemail',$data,true,false,true);				
	
		if($response->status=='failed'){
				return  json_response_api($response);
		}else{										
				$response 		 = 	$response->data;
				$response->type  = 	Task::Mail;			
				$response->LogID = 	$response->AccountEmailLogID;
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

    public function getAttachment($emailID,$attachmentID){
        $response = NeonAPI::request('emailattachment/'.$emailID.'/getattachment/'.$attachmentID,[],true,true,true);

        if($response['status']=='failed'){
            return json_response_api($response,false);
        }else{
            $Comment  = 	json_response_api($response,true,false,false);
            $FilePath =  	AmazonS3::preSignedUrl($Comment['filepath']);
			Log::info("file path");
			Log::info($FilePath);
            if(file_exists($FilePath)){
                download_file($FilePath);
            }else{
                header('Location: '.$FilePath);
            }
            exit;
        }
    }

}