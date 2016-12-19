<?php

class TicketsGroupController extends \BaseController {

private $validlicense;	
	public function __construct(){
			$this->validlicense = Tickets::CheckTicketLicense();
		 } 
	 
	 protected function IsValidLicense(){
	 	return $this->validlicense;		
	 }
	
	
    public function index() {          
		$this->IsValidLicense();
		$data 			 		= 	array();	
		$EscalationTimes_json 	= 	json_encode(TicketGroups::$EscalationTimes);
        return View::make('ticketgroups.groups', compact('data','EscalationTimes_json'));   
	  }		
	  
	  function add(){	  
		$this->IsValidLicense();		
		$Agents			= 	User::getUserIDListAll(0);
		$AllUsers		= 	User::getUserIDListAll(0); 
		$AllUsers[0] 	= 	'None';	
		ksort($AllUsers);			
		$data 			= 	array();		
        return View::make('ticketgroups.group_create', compact('data','AllUsers','Agents'));  
	  }	
	  
	  function Edit($id){	   
		$this->IsValidLicense();
		$ticketdata		=	TicketGroups::find($id);		 
		$Groupagents	=	array();
		$Groupemails	=	array();
		
		$Groupagentsdb	=	TicketGroupAgents::where(["GroupID"=>$id])->get(); 
		foreach($Groupagentsdb as $Groupagentsdata){
			$Groupagents[] = $Groupagentsdata->UserID;
		} 		
		$Groupemails	=	implode(',',$Groupemails);
		$Agents			= 	User::getUserIDListAll(0);
		$AllUsers		= 	User::getUserIDListAll(0); 
		$AllUsers[0] 	= 	'None';	
		ksort($AllUsers);			
		$data 			= 	array(); 
        return View::make('ticketgroups.group_edit', compact('data','AllUsers','Agents','ticketdata','Groupagents'));  
	  }	
	  
	  public function ajax_datagrid($type){
		
       $CompanyID 				= 	User::get_companyID();       
	   $data 					= 	Input::all();
	   $data['iDisplayStart'] 	+=	1;
	   $userID					=	(isset($data['UsersID']) && !empty($data['UsersID']))?$data['UsersID']:0;
	   $search		 			=	$data['Search'];	   
       $columns 	 			= 	array('GroupID','GroupName','GroupEmailAddress','TotalAgents','GroupAssignTime','AssignUser');
       $sort_column 			= 	$columns[$data['iSortCol_0']];
		
        $query 	= 	"call prc_GetTicketGroups (".$CompanyID.",'".$search."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";  

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/TicketGroups.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/TicketGroups.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }          
        }
        $query .=',0)';  

        return DataTableSql::of($query)->make();
	 }
	  
	  function Store(){
	    $this->IsValidLicense();
		$data 			= 	Input::all();  
        
        $rules = array(
            'GroupName' => 'required|min:2',
            'GroupAgent' => 'required',
            'GroupAssignEmail' => 'required',
			'GroupEmailServer' => 'required',
			'GroupEmailPassword' => 'required',
			'GroupReplyAddress' => 'email|required',		
			'GroupEmailAddress'	=> 'email|required|unique:tblTicketGroups,GroupEmailAddress',
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
			$GroupData = array(
				"CompanyID"=>User::get_companyID(),
				"GroupName"=>$data['GroupName'],
				"GroupDescription"=>$data['GroupDescription'],
				"GroupAssignTime"=>$data['GroupAssignTime'],
				"GroupAssignEmail"=>$data['GroupAssignEmail'],
				"GroupReplyAddress"=>$data['GroupReplyAddress'],				
				"EmailTrackingServer"=>$data['GroupEmailServer'],
				"EmailTrackingPassword"=>$data['GroupEmailPassword'],	
				"GroupEmailStatus" => 0,
				"created_at"=>date("Y-m-d H:i:s"),
				"created_by"=>User::get_user_full_name()
			);
			
			try{
 			    DB::beginTransaction();
				$GroupID = TicketGroups::insertGetId($GroupData);		
				if(is_array($data['GroupAgent'])){
					foreach($data['GroupAgent'] as $GroupAgents){
						$TicketGroupAgents =	array("GroupID"=>$GroupID,'UserID'=>$GroupAgents,"created_at"=>date("Y-m-d H:i:s"),"created_by"=>User::get_user_full_name());   
						TicketGroupAgents::Insert($TicketGroupAgents);						
					}
				}	
					
				$this->SendEmailActivationEmail($data['GroupEmailAddress'],$GroupID);
				 DB::commit();	
            	return Response::json(array("status" => "success", "message" => "Group Successfully Created",'LastID'=>$GroupID));
      		 }catch (Exception $ex){ 	
			      DB::rollback();
				 return Response::json(array("status" => "failed", "message" =>$ex->getMessage()));
       		 }    
	  }
	  
	  function Update($id){
	    $this->IsValidLicense();
		$data 			= 	Input::all();  
		$TicketGroup	= 	TicketGroups::find($id);
        
	    $rules = array(
            'GroupName' => 'required|min:2',
            'GroupAgent' => 'required',
            'GroupEmailAddress'	=> 'email|required|unique:tblTicketGroups,GroupEmailAddress,'.$id.',GroupID,CompanyID,'.User::get_companyID(),
            'GroupAssignEmail' => 'required',
			'GroupEmailServer' => 'required',
			'GroupEmailPassword' => 'required',
			'GroupReplyAddress' => 'email|required',	
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
			/*try{*/
				 DB::beginTransaction();
				if(isset($TicketGroup->GroupID)){
					
					$grpagents 			= $data['GroupAgent'];
					$GroupEmailAddress  = $data['GroupEmailAddress'];					
					unset($data['GroupAgent']);
					unset($data['_wysihtml5_mode']);
					unset($data['GroupEmailAddress']);
					//unset($data['GroupEmailServer']);
					//unset($data['GroupEmailPassword']);
					//unset($data['GroupReplyAddress']);
					
					$TicketGroup->update($data);  	 //update groups
					TicketGroupAgents::where(["GroupID" => $TicketGroup->GroupID])->delete(); //delete old group agents
					
					if(is_array($grpagents)){
						foreach($grpagents as $GroupAgents){	 //insert new agents						  
							$TicketGroupAgents =	array("GroupID"=>$TicketGroup->GroupID,'UserID'=>$GroupAgents,"updated_at"=>date("Y-m-d H:i:s"),"updated_by"=>User::get_user_full_name());   
							TicketGroupAgents::Insert($TicketGroupAgents);
						}
					}
					
					if($TicketGroup->GroupEmailAddress!=$GroupEmailAddress){						 		 		
						$this->SendEmailActivationEmailUpdate($GroupEmailAddress,$id);
					}
					 DB::commit();	
					return Response::json(array("status" => "success", "message" => "Group Successfully Updated",'LastID'=>$TicketGroup->GroupID));
				}
      		 /*}catch (Exception $ex){ 	
				 DB::rollback();
				 return Response::json(array("status" => "failed", "message" =>$ex->getMessage()));
       		 } */
	  }
	  
	  function SendEmailActivationEmail($email,$groupID){
		  
		  if(!empty($email))
		  { 
				$remember_token				 = 		str_random(32);
				$user_reset_link 			 = 		URL::to('/activate_support_email')."?remember_token=".$remember_token;
				$data 						 = 		array();
				$data['companyID'] 			 = 		User::get_companyID();
				$CompanyName 				 =  	Company::getName($data['companyID']);
				$data['EmailTo'] 			 = 		trim($email);
				$data['CompanyName'] 		 = 		$CompanyName;
				$data['Subject'] 			 = 		'Activate support email address';
				$data['user_reset_link'] 	 = 		$user_reset_link;
				$result 					 = 		sendMail('emails.auth.email_verify',$data);
				
				if ($result['status'] == 1) {
					$GroupEmaildata = array(
						"GroupEmailAddress"=>$email,
						"remember_token"=>$remember_token
						);
						
					 TicketGroups::where(['GroupID'=>$groupID])->update($GroupEmaildata);								 					
				}				
		  }
	  	
	  }
	  
	  function SendEmailActivationEmailUpdate($email,$groupID){
			
		  if(!empty($email))
	 	  {   
			$remember_token				 = 		str_random(32); //add new
			$user_reset_link 			 = 		URL::to('/activate_support_email')."?remember_token=".$remember_token;
			$data 						 = 		array();
			$data['companyID'] 			 = 		User::get_companyID();
			$CompanyName 				 =  	Company::getName($data['companyID']);
			$data['EmailTo'] 			 = 		trim($email);
			$data['CompanyName'] 		 = 		$CompanyName;
			$data['Subject'] 			 = 		'Activate support email address';
			$data['user_reset_link'] 	 = 		$user_reset_link;
			$result 					 = 		sendMail('emails.auth.email_verify',$data);
				
			if ($result['status'] == 1)
			{
				$GroupEmaildata = array(
					"GroupEmailAddress"=>$email,
					"GroupEmailStatus"=>0,
					"remember_token"=>$remember_token,
					"updated_at"=>date("Y-m-d H:i:s"),
					"updated_by"=>User::get_user_full_name()
					);
					
				 TicketGroups::where(['GroupID'=>$groupID])->update($GroupEmaildata);	
			 }				
	  	  }
	  }
	  
	  function Activate_support_email(){
	 	 $data = Input::all();
        //if any open reset password page direct he will redirect login page
			if(isset($data['remember_token']) && $data['remember_token'] != '')
			{
				$remember_token  = 	$data['remember_token'];
				$user 			 = 	TicketGroups::get_support_email_by_remember_token($remember_token);
				
				if (empty($user)) {
					$data['message']  = "Invalid Token";
					$data['status']  =  "failed";
				} else {
					TicketGroups::where(["GroupID"=>$user->GroupID])->update(array("remember_token"=>'NUll',"GroupEmailStatus"=>1));				
					$data['message']  		=  "Email successfully activated";
					$data['status'] 		=  "success";				
				}  
				return View::make('ticketgroups.activate_status',compact('data'));     					
			}else{
				return Redirect::to('/');
			}
	  }
	  
	 public function delete($id)
     {
        if( intval($id) > 0)
		{
               try{
				   TicketGroups::find($id)->delete();
				   TicketGroupAgents::where(['GroupID'=>$id])->delete();
			       return Response::json(array("status" => "success", "message" => "Subscription Successfully Deleted","GroupID"=>$id));
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
                }
            
        }
    }
	
	
	function send_activation_single($id)
	{
	    try
		{
			if($id)
			{
			   $email_data = 	TicketGroups::find($id);
			  
			  if(count($email_data)>0 && $email_data->GroupEmailStatus==0)
			  {
					$remember_token				 = 		str_random(32); //add new
					$user_reset_link 			 = 		URL::to('/activate_support_email')."?remember_token=".$remember_token;
					$data 						 = 		array();
					$data['companyID'] 			 = 		User::get_companyID();
					$CompanyName 				 =  	Company::getName($data['companyID']);
					$data['EmailTo'] 			 = 		trim($email_data->GroupEmailAddress);
					$data['CompanyName'] 		 = 		$CompanyName;
					$data['Subject'] 			 = 		'Activate support email address';
					$data['user_reset_link'] 	 = 		$user_reset_link;
					$result 					 = 		sendMail('emails.auth.email_verify',$data);
					
					if ($result['status'] == 1)
					{
							$GroupEmaildata = array(
								"remember_token"=>$remember_token,
								"updated_at"=>date("Y-m-d H:i:s"),
								"updated_by"=>User::get_user_full_name()
							);

						 $email_data->update($GroupEmaildata);
						 return Response::json(array("status" => "success", "message" => "Activation email successfully sent."));
					}
			  }else{
			 	return Response::json(array("status" => "failed", "message" => "No email found or already activated"));
			  }			  
			}
		 }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Problem occurred. Exception:". $ex->getMessage()));
         }
	}
	
	function get_group_agents($id){
		try
		{
			$Groupagents    =   array();
			if($id)
			{
				$Groupagentsdb	=	TicketGroupAgents::where(["GroupID"=>$id])->get(); 
			}
			else
			{
				$Groupagentsdb	=	TicketGroupAgents::get(); 
			}
			
			foreach($Groupagentsdb as $Groupagentsdata){
				$userdata = 	User::find($Groupagentsdata->UserID);
				if($userdata){	
					$Groupagents[$userdata->FirstName." ".$userdata->LastName] =$userdata->UserID; 
				}
				
			}
			//echo "<pre>"; print_r($Groupagents);	echo "</pre>";
			return Response::json(array("status" => "success", "data"=>$Groupagents));
		
		  }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Problem occurred. Exception:". $ex->getMessage()));
         }
	}
}