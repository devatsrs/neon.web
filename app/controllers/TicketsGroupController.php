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
		$users			 		= 	User::getUserIDListAll(1);
        return View::make('ticketgroups.groups', compact('data','EscalationTimes_json','users'));   
	  }		
	  
	  function add(){	  
		$this->IsValidLicense();		
		$Agents			= 	User::getUserIDListAll(0);
		$Users			= 	User::getUserIDListAll(0); 
		$AllUsers		=	array_merge(array("0"=>"None"),$Users);		
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
		
		$Groupemailsdb	=	TicketGroupEmailAddresses::where(["GroupID"=>$id])->get(); 
		foreach($Groupemailsdb as $Groupemailsdbdata){
			$Groupemails[] = $Groupemailsdbdata->EmailAddress;
		} 
		$Groupemails	=	implode(',',$Groupemails);
		$Agents			= 	User::getUserIDListAll(0);
		$Users			= 	User::getUserIDListAll(0); 
		$AllUsers		=	array_merge(array("0"=>"None"),$Users);		
		$data 			= 	array();		
        return View::make('ticketgroups.group_edit', compact('data','AllUsers','Agents','ticketdata','Groupagents','Groupemails'));  
	  }	
	  
	  public function ajax_datagrid($type){
		
       $CompanyID 				= 	User::get_companyID();       
	   $data 					= 	Input::all();
	   $data['iDisplayStart'] 	+=	1;
	   $userID					=	(isset($data['UsersID']) && !empty($data['UsersID']))?$data['UsersID']:0;
	   $search		 			=	$data['Search'];	   
       $columns 	 			= 	array('GroupID','GroupName','GroupEmailAddress','TotalAgents','GroupAssignTime','AssignUser');
       $sort_column 			= 	$columns[$data['iSortCol_0']];
		
        $query 	= 	"call prc_GetTicketGroups (".$CompanyID.",'".$userID."','".$search."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";  

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
        $query .=',0)';  Log::info($query);

        return DataTableSql::of($query)->make();
	 }
	  
	  function Store(){
	    $this->IsValidLicense();
		$data 			= 	Input::all();  
        
        $rules = array(
            'GroupName' => 'required|min:2',
            'GroupAgent' => 'required',
            'GroupEmailAddress' => 'required',
            'GroupAssignEmail' => 'required',
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
			$GroupData = array(
				"CompanyID"=>User::get_companyID(),
				"GroupName"=>$data['GroupName'],
				"GroupDescription"=>$data['GroupDescription'],
				//"GroupEmailAddress"=>$data['GroupEmailAddress'],
				"GroupAssignTime"=>$data['GroupAssignTime'],
				"GroupAssignEmail"=>$data['GroupAssignEmail'],
				//"GroupAuomatedReply"=>$data['GroupAuomatedReply']
				"created_at"=>date("Y-m-d H:i:s"),
				"created_by"=>User::get_user_full_name()
			);
			
			try{
				$GroupID = TicketGroups::insertGetId($GroupData);		
				if(is_array($data['GroupAgent'])){
					foreach($data['GroupAgent'] as $GroupAgents){
						$TicketGroupAgents =	array("GroupID"=>$GroupID,'UserID'=>$GroupAgents,"created_at"=>date("Y-m-d H:i:s"),"created_by"=>User::get_user_full_name());   
						TicketGroupAgents::Insert($TicketGroupAgents);						
					}
				}	
				$this->SendEmailActivationEmail($data['GroupEmailAddress'],$GroupID);
					
            	return Response::json(array("status" => "success", "message" => "Group Successfully Created",'LastID'=>$GroupID));
      		 }catch (Exception $ex){ 	
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
            'GroupEmailAddress' => 'required',
            'GroupAssignEmail' => 'required',
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
			/*try{*/
				if(isset($TicketGroup->GroupID)){
					
					$grpagents 			= $data['GroupAgent'];
					$GroupEmailAddress  = $data['GroupEmailAddress'];
					unset($data['GroupAgent']);
					unset($data['_wysihtml5_mode']);
					unset($data['GroupEmailAddress']);
					
					$TicketGroup->update($data);  	 //update groups
					TicketGroupAgents::where(["GroupID" => $TicketGroup->GroupID])->delete(); //delete old group agents
					
					if(is_array($grpagents)){
						foreach($grpagents as $GroupAgents){	 //insert new agents						  
							$TicketGroupAgents =	array("GroupID"=>$TicketGroup->GroupID,'UserID'=>$GroupAgents,"updated_at"=>date("Y-m-d H:i:s"),"updated_by"=>User::get_user_full_name());   
							TicketGroupAgents::Insert($TicketGroupAgents);
						}
					}		
					$this->SendEmailActivationEmailUpdate($GroupEmailAddress,$id);
					return Response::json(array("status" => "success", "message" => "Group Successfully Updated",'LastID'=>$TicketGroup->GroupID));
				}
      		/* }catch (Exception $ex){ 	
				 return Response::json(array("status" => "failed", "message" =>$ex->getMessage()));
       		 } */ 
	  }
	  
	  function SendEmailActivationEmail($emails,$groupID){
		  	Log::info(print_r($emails,true));
		  if(!empty($emails))
		  { 
			  if(!is_array($emails))
			  {
				  
				$email_addresses = explode(",",$emails);
				}else{
					$email_addresses = $emails;
				}
				Log::info(print_r($emails,true));		
				if(count($email_addresses)>0){
					
					foreach($email_addresses as $email_address){
					Log::info(print_r($email_address,true));	
						$remember_token				 = 		str_random(32);
						$user_reset_link 			 = 		URL::to('/activate_support_email')."?remember_token=".$remember_token;
						$data 						 = 		array();
						$data['companyID'] 			 = 		User::get_companyID();
						$CompanyName 				 =  	Company::getName($data['companyID']);
						$data['EmailTo'] 			 = 		trim($email_address);
						$data['CompanyName'] 		 = 		$CompanyName;
						$data['Subject'] 			 = 		'Activate support email address';
						$data['user_reset_link'] 	 = 		$user_reset_link;
						$result 					 = 		sendMail('emails.auth.email_verify',$data);
						
						if ($result['status'] == 1) {
							$GroupEmaildata = array(
								"GroupID"=>$groupID,
								"EmailAddress"=>$email_address,
								"EmailStatus"=>0,
								"remember_token"=>$remember_token,
								"created_at"=>date("Y-m-d H:i:s"),
								"created_by"=>User::get_user_full_name()
								);
								
							 TicketGroupEmailAddresses::insert($GroupEmaildata);								 					
						}
					}
	   		    }
		  
		  }
	  	
	  }
	  
	  function SendEmailActivationEmailUpdate($emails,$groupID){
		  
		  	$AlreadyaddEmails  =  TicketGroupEmailAddresses::where(['GroupID'=>$groupID])->get();				
			
		  if(!empty($emails))
		  { 
			  if(!is_array($emails))
			  {
				  
				$email_addresses = explode(",",$emails);
				}else{
					$email_addresses = $emails;
				}
					
				foreach($AlreadyaddEmails as $AlreadyaddEmailsData){ //delete the removed emails 					
					if(!in_array($AlreadyaddEmailsData->EmailAddress,$email_addresses)){ 	
						TicketGroupEmailAddresses::where(["GroupEmailID"=>$AlreadyaddEmailsData->GroupEmailID])->delete();		
					}
				}
				
				
				if(count($email_addresses)>0){
					
					
					foreach($email_addresses as $email_address){
					  $already =  TicketGroupEmailAddresses::where(["EmailAddress"=>$email_address,'GroupID'=>$groupID])->get();	 					
					  if(count($already)>0) {continue;}//check email already exists
					  
						$remember_token				 = 		str_random(32); //add new
						$user_reset_link 			 = 		URL::to('/activate_support_email')."?remember_token=".$remember_token;
						$data 						 = 		array();
						$data['companyID'] 			 = 		User::get_companyID();
						$CompanyName 				 =  	Company::getName($data['companyID']);
						$data['EmailTo'] 			 = 		trim($email_address);
						$data['CompanyName'] 		 = 		$CompanyName;
						$data['Subject'] 			 = 		'Activate support email address';
						$data['user_reset_link'] 	 = 		$user_reset_link;
						$result 					 = 		sendMail('emails.auth.email_verify',$data);
						
						if ($result['status'] == 1) {
							$GroupEmaildata = array(
								"GroupID"=>$groupID,
								"EmailAddress"=>$email_address,
								"EmailStatus"=>0,
								"remember_token"=>$remember_token,
								"created_at"=>date("Y-m-d H:i:s"),
								"created_by"=>User::get_user_full_name()
								);
								
							 TicketGroupEmailAddresses::insert($GroupEmaildata);								 					
						}
					}
	   		    }
		  
		  }
	  }
	  
	  function Activate_support_email(){
	 	 $data = Input::all();
        //if any open reset password page direct he will redirect login page
			if(isset($data['remember_token']) && $data['remember_token'] != '')
			{
					/////////////////
					
				$remember_token  = 	$data['remember_token'];
				$user 			 = 	TicketGroupEmailAddresses::get_support_email_by_remember_token($remember_token);
				
				if (empty($user)) {
					$data['message']  = "Invalid Token";
					$data['status']  =  "failed";
				} else {
					TicketGroupEmailAddresses::where(["GroupEmailID"=>$user->GroupEmailID])->update(array("remember_token"=>'NUll',"EmailStatus"=>1));				
					$data['message']  		=  "Email successfully activated";
					$data['status'] 		=  "success";				
				}  
				return View::make('ticketgroups.activate_status',compact('data'));     					
			}else{
				return Redirect::to('/');
			}
	  }
}