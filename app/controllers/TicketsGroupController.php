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
		$response =  NeonAPI::request('ticketgroups/get/'.$id,array());
		
		if(!empty($response) && $response->status == 'success' ){
			$ticketdata		=	$response->data;		 
			$Groupagents	=	array();
			
			$Groupagentsdb	=	NeonAPI::request('ticketgroups/get_group_agents_ids/'.$id,array()); 
			$Groupagents	= 	$Groupagentsdb->data;
			 		
			$Agents			= 	User::getUserIDListAll(0);
			$AllUsers		= 	User::getUserIDListAll(0); 
			$AllUsers[0] 	= 	'None';	
			ksort($AllUsers);			
			$data 			= 	array(); 
			return View::make('ticketgroups.group_edit', compact('data','AllUsers','Agents','ticketdata','Groupagents'));  
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
                $file_path = getenv('UPLOAD_PATH') .'/TicketGroups.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/TicketGroups.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }          
        }	 
        return json_response_api($response,true,true,true);
	 }
	  
	  function Store(){
	    $this->IsValidLicense();
		
		$postdata 				= 		Input::all();  		
		$postdata['activate'] 	= 		URL::to('/activate_support_email');
        $response 				= 		NeonAPI::request('ticketgroups/store',$postdata,true,false,false);
		
        if(!empty($response) && $response->status == 'success'){
            $response->redirect =  URL::to('/ticketgroups/');
        }
        return json_response_api($response);     
	  }
	  
	  function Update($id){
	    $this->IsValidLicense();
		
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
		Log::info(print_r($response,true));
		return json_response_api($response,true);		
	}
}