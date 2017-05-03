<?php

class TicketsGroupController extends \BaseController {

	public function __construct(){
		parent::validateTicketLicence();	  
	 } 	 
	
	
   public function index() {          
		$data 			 		= 	array();	
		$EscalationTimes_json 	= 	json_encode(TicketGroups::$EscalationTimes);
        return View::make('ticketgroups.groups', compact('data','EscalationTimes_json'));   
	  }		
	  
	  function add(){	  
		$Agents			= 	User::getUserIDListAll(0);
		$AllUsers		= 	User::getUserIDListAll(0); 
		$businessHours	=	TicketBusinessHours::getBusinesshours(1); 
		$AllUsers[0] 	= 	'None';	
		ksort($AllUsers);			
		$data 			= 	array();		
        return View::make('ticketgroups.group_create', compact('data','AllUsers','Agents','businessHours'));  
	  }	
	  
	  function Edit($id){	   
		
		$response =  NeonAPI::request('ticketgroups/get/'.$id,array());
		
		if(!empty($response) && $response->status == 'success' ){
			$ticketdata		=	$response->data;
			$Groupagents	=	array();
			
			$Groupagentsdb	=	NeonAPI::request('ticketgroups/get_group_agents_ids/'.$id,array()); 
			$Groupagents	= 	$Groupagentsdb->data;
			 		
			$Agents			= 	User::getUserIDListAll(0);
			$AllUsers		= 	User::getUserIDListAll(0); 
			$businessHours	=	TicketBusinessHours::getBusinesshours(1); 
			$AllUsers[0] 	= 	'None';	
			ksort($AllUsers);			
			$data 			= 	array(); 
			return View::make('ticketgroups.group_edit', compact('data','AllUsers','Agents','ticketdata','Groupagents','businessHours'));  
		}else{
            return view_response_api($response);
        }
	  }	
	  
	  public function ajax_datagrid($type){
		  
		$companyID 				= 	User::get_companyID();
        $data 					= 	Input::all();
        $data['iDisplayStart'] +=	1;
        $response 				= 	NeonAPI::request('ticketgroups/get_groups',$data); 
		
		if(isset($data['Export']) && $data['Export'] == 1) {      
		 
		 $excel_data = $response->data;
         $excel_data = json_decode(json_encode($excel_data), true);

            if($type=='csv'){
                $file_path =  CompanyConfiguration::get('UPLOAD_PATH').'/TicketGroups.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path =  CompanyConfiguration::get('UPLOAD_PATH') .'/TicketGroups.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }          
        }	 
        return json_response_api($response,true,true,true);
	 }
	  
	  function Store(){
	    
		
		$postdata 				= 		Input::all();  		
		$postdata['activate'] 	= 		URL::to('/activate_support_email');
        $response 				= 		NeonAPI::request('ticketgroups/store',$postdata,true,false,false);
		
        if(!empty($response) && $response->status == 'success'){
            $response->redirect =  URL::to('/ticketgroups/');
        }
        return json_response_api($response);     
	  }
	  
	  function Update($id){
	    
		
		$postdata 				= 		Input::all();
		$postdata['activate'] 	= 		URL::to('/activate_support_email');
        $response 				= 		NeonAPI::request('ticketgroups/update/'.$id,$postdata,'put',false,false);
		
        return json_response_api($response);
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
		$response 		= 		NeonAPI::request('ticketgroups/delete/'.$id,array(),true,false,false); 
		return json_response_api($response);
    }
	
	
	function get_group_agents($id){
		
		$postdata 				= 		Input::all();
        $response 				= 		NeonAPI::request('ticketgroups/get_group_agents/'.$id,array(),true,true,false); 
		return json_response_api($response);		
	}
	
	function validatesmtp(){
		$data = Input::all();
		$response 				= 		NeonAPI::request('ticketgroups/validatesmtp',$data,true,false,false); 
		return json_response_api($response,true);		
	}
	
	function send_activation_single($id){
		$postdata 				= 		Input::all(); 
        $response 				= 		NeonAPI::request('ticketgroups/send_activation_single/'.$id,$postdata,true,true,false);
		return json_response_api($response,true);		
	}
}