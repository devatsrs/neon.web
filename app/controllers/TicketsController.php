<?php

class TicketsController extends \BaseController {

private $validlicense;	
	public function __construct(){
			$this->validlicense = Tickets::CheckTicketLicense();
		 } 
	 
	 protected function IsValidLicense(){
	 	return $this->validlicense;		
	 }
	
	  public function index(){
			$this->IsValidLicense();
			$data 			 		= 	array();	
			$EscalationTimes_json 	= 	json_encode(TicketGroups::$EscalationTimes);
			$users			 		= 	User::getUserIDListAll(1);
			return View::make('tickets.index', compact('data','EscalationTimes_json','users'));   
	  }	
	  
	  function ajax_datagrid($type)
	  {	
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
			$query .=',0)';  
	
			return DataTableSql::of($query)->make();
	 	}  
		
		function add()
		{	
			$this->IsValidLicense();				
			$Ticketfields	   			=	 DB::table('tblTicketfields')->orderBy('FieldOrder', 'asc')->get(); 
			$Agents			   			= 	 User::getUserIDListAll(0);
			$AllUsers		   			= 	 User::getUserIDListAll(0); 
			$AllUsers[0] 	   			= 	 'None';	
			ksort($AllUsers);			
			$CompanyID 		   			= 	 User::get_companyID();	
			$htmlgroupID 	   			= 	 '';
			$htmlagentID       			= 	 '';
			$random_token	  			=	 get_random_number();
			$response_api_extensions 	=    Get_Api_file_extentsions();
		   if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}	
		    $response_extensions		=	json_encode($response_api_extensions['allowed_extensions']);
			$max_file_size				=	get_max_file_size();	
			$AllEmails 					= 	implode(",",(Messages::GetAllSystemEmails(0))); 
			
		   $agentsAll = DB::table('tblTicketGroupAgents')
            ->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()          
            ->select('tblUser.UserID', 'tblUser.FirstName', 'tblUser.LastName')
            ->get();
			
			
		   
			//echo "<pre>";			print_r($agentsAll);			echo "</pre>";					exit;
			return View::make('tickets.create', compact('data','AllUsers','Agents','Ticketfields','CompanyID','agentsAll','htmlgroupID','htmlagentID','random_token','response_extensions','max_file_size','AllEmails'));  
	  }	
	  
	  function Store(){
	    $this->IsValidLicense();
		$data 			= 	Input::all();  
		
		
		Log::info(print_r($data,true));
        exit;
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
 			    DB::beginTransaction();
				$GroupID = TicketGroups::insertGetId($GroupData);		
				if(is_array($data['GroupAgent'])){
					foreach($data['GroupAgent'] as $GroupAgents){
						$TicketGroupAgents =	array("GroupID"=>$GroupID,'UserID'=>$GroupAgents,"created_at"=>date("Y-m-d H:i:s"),"created_by"=>User::get_user_full_name());   
						TicketGroupAgents::Insert($TicketGroupAgents);						
					}
				}	
				
				 $email_addresses = explode(",",$data['GroupEmailAddress']);				
				 foreach($email_addresses as $email_addresses_data)
				 {
				   $already = 	TicketGroupEmailAddresses::where(["EmailAddress"=>trim($email_addresses_data)])->get();	
				   if(count($already)>0)
				   {
					    DB::rollback();
				  		return Response::json(array("status" => "failed", "message" =>$email_addresses_data." email address already exists."));
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
	  
	  function uploadFile(){
        $data       =  Input::all();
        $attachment    =  Input::file('emailattachment');
        if(!empty($attachment)) {
            try { 
                $data['file'] = $attachment;
                $returnArray = UploadFile::UploadFileLocal($data);
                return Response::json(array("status" => "success", "message" => '','data'=>$returnArray));
            } catch (Exception $ex) {
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }
        }

    }

    function deleteUploadFile(){
        $data    =  Input::all();
        try {
            UploadFile::DeleteUploadFileLocal($data);
            return Response::json(array("status" => "success", "message" => 'Attachments delete successfully'));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

}