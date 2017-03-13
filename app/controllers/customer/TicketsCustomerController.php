<?php

class TicketsCustomerController extends \BaseController {

private $validlicense;	

	public function __construct(){
		$this->validlicense = Tickets::CheckTicketLicense();
		 if(!$this->validlicense)
		 {
			NeonAPI::logout();
	        Session::flush();
    	    Auth::logout();
			Redirect::to('customer/login')->send();
			//return Redirect::to('customer/login')->with('message', 'Your are now logged out!');
		 }
	 } 
	 
	 protected function IsValidLicense(){		 
	 	//return $this->validlicense;		
	 }
	
	 public function index(){
		 
			$this->IsValidLicense();
			$CompanyID 		 			= 	 User::get_companyID(); 
			$data 			 			= 	 array();	
			$status			 			=    TicketsTable::getTicketStatus();
			$Priority		 			=	 TicketPriority::getTicketPriority();
			//$Groups			 			=	 TicketGroups::getTicketGroups(); 
			$Agents			 			= 	 User::getUserIDListAll(0);
			$Agents			 			= 	 array("0"=> "Select")+$Agents;
			$Type			 			=    TicketsTable::getTicketType();
			/////////
			$Sortcolumns				=	 TicketsTable::$SortcolumnsCustomer;			
			$pagination					=	 TicketsTable::$pagination;
			$data['iDisplayStart']  	= 	 0;
			
			$cache_sorting 				= 	 Session::get("TicketsSorting");
			
			if(count($cache_sorting)>0){
				$per_page  					= 	 $cache_sorting['per_page'];
				$data['iDisplayLength'] 	= 	 $cache_sorting['per_page'];
 				$data['iSortCol_0']			=	 $cache_sorting['sort_fld'];
				$data['sSortDir_0']			=	 $cache_sorting['sort_type'];
				
		 	 }else{
				$per_page					= 	 CompanyConfiguration::get('PAGE_SIZE'); 
				$data['iDisplayLength'] 	= 	 CompanyConfiguration::get('PAGE_SIZE');
				$data['iSortCol_0']			=	 TicketsTable::$defaultSortField;
				$data['sSortDir_0']			=	 TicketsTable::$defaultSortType;
				 
			  }
			///////
			$companyID 					= 	 User::get_companyID();
			$array						= 	 $this->GetResult($data); 
			$resultpage  				= 	 $array->resultpage;		 
			$result 					= 	 $array->ResultCurrentPage;
			$totalResults 				= 	 $array->totalcount; 
			$iTotalDisplayRecords 		= 	 $array->iTotalDisplayRecords;
			$iDisplayLength 			= 	 $data['iDisplayLength'];
			$data['currentpage'] 		= 	 0;
			//echo "<pre>";		print_r($result);			exit;
			$Groups						= 	TicketGroups::getTicketGroupsFromData($array->GroupsData);				
			
		TicketsTable::SetTicketSession($result);
		
        return View::make('customer.tickets.index', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','EscalationTimes_json','status','Priority','Groups','Agents','Type',"Sortcolumns","per_page","pagination"));  
			/////////
	  }	
	  
	  public function ajex_result() {
		
	    $data 						= 	Input::all();
		$data['currentpages']		=	$data['currentpage'];
		if($data['clicktype']=='next'){
			$data['iDisplayStart']  	= 	($data['currentpage']+1)*$data['per_page'];
			$data['currentpage']++;
		}
		elseif($data['clicktype']=='back'){
			$data['iDisplayStart']  	= 	($data['currentpage']-1)*$data['per_page'];
			$data['currentpage']--;
		}else
		{
			$data['iDisplayStart'] = 0;
		}	
		
		$cache_sorting = array("per_page"=>$data['per_page'],"sort_fld"=>$data['sort_fld'],"sort_type"=>$data['sort_type']);
		Session::set("TicketsSorting", $cache_sorting);
		
		$data['Search'] 			= 	 $data['formData']['Search'];
		$data['status'] 			= 	 isset($data['formData']['status'])?$data['formData']['status']:'';		
		$data['priority']	 		= 	 isset($data['formData']['priority'])?$data['formData']['priority']:'';
		$data['group'] 				= 	 isset($data['formData']['group'])?$data['formData']['group']:'';		
		$data['agent']				= 	 isset($data['formData']['agent'])?$data['formData']['agent']:'';
		$data['iSortCol_0']			= 	 $data['sort_fld'];
		$data['sSortDir_0']			= 	 $data['sort_type'];
		$data['iDisplayLength'] 	= 	 $data['per_page'];
		$companyID					= 	 User::get_companyID();
		$array						= 	 $this->GetResult($data);
		$resultpage  				= 	 $array->resultpage;		 
		$result 					= 	 $array->ResultCurrentPage;
		$totalResults 				= 	 $array->totalcount; 
		$iTotalDisplayRecords 		= 	 $array->iTotalDisplayRecords;
		$iDisplayLength 			= 	 $data['iDisplayLength'];
		$Sortcolumns				=	 TicketsTable::$SortcolumnsCustomer;
		$pagination					=	 TicketsTable::$pagination;
		//echo "<pre>";		print_r($resultpage);			exit;
		if(count($result)<1)
		{
			if(isset($data['SearchStr']) && $data['SearchStr']!='' && $data['currentpage']==0){
				
				return json_encode(array("result"=>"No Result found for ".$data['SearchStr']));
			}else{			
				return '';
			}
		} 
		TicketsTable::SetTicketSession($result);
       return   View::make('customer.tickets.ajaxresults', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','boxtype','TotalDraft','TotalUnreads','Sortcolumns','pagination'));     
	   
	   //return array('currentpage'=>$data['currentpage'],"Body"=>$body,"result"=>count($result));
    
	}
	  
	  
	  function GetResult($data){
		  		
		if(User::is_admin())	{		
		   	$data['agent']					=	isset($data['agent'])?is_array($data['agent'])?implode(",",$data['agent']):'':'';
		 }else{
			 $data['agent']					=	user::get_userID();
		 }
		
        $response 				= 	NeonAPI::request('tickets/get_tickets',$data,true,false); 
      
		if($response->status=='success')
		{ 
			return $response->data;
		}else{
			return $response->message;
		}
	}
	  
	function add()
	{	
			$this->IsValidLicense();				
			
			$response 		=   NeonAPI::request('ticketsfields/getfields',array("fields"=>"simple"),true,false,false);   
			$data			=	array();	
			
			if($response->status=='success'){
				$Ticketfields = 	$response->data;
			}else{
				$Ticketfields =		array();
			}
			
			
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
			$AllEmails 					= 	implode(",",(Messages::GetAllSystemEmailsWithName(0))); 
			
		   $agentsAll = DB::table('tblTicketGroupAgents')
            ->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()          
            ->select('tblUser.UserID', 'tblUser.FirstName', 'tblUser.LastName')
            ->get();
		   
			//echo "<pre>";			print_r($agentsAll);			echo "</pre>";					exit;
			return View::make('customer.tickets.create', compact('data','AllUsers','Agents','Ticketfields','CompanyID','agentsAll','htmlgroupID','htmlagentID','random_token','response_extensions','max_file_size','AllEmails'));  
	  }	
	  
	public function edit($id)
	{
		$this->IsValidLicense();
		$accountemailaddresses	=	  Account::GetAccountAllEmails(User::get_userID(),true);
        $response  		    	=  	  NeonAPI::request('tickets/edit/'.$id,array(),true);
	
		if(!empty($response) && $response->status == 'success' )
		{ 	
			$ResponseData				=	 $response->data;
			$TicketID 					=	 $id;
			$ticketdata					=	 $ResponseData->ticketdata;
			

			if(!in_array($ticketdata->Requester,$accountemailaddresses))
			{
					App::abort(403, 'You have not access to' . Request::url());		
			}
			
			
			$ticketdetaildata			=	 $ResponseData->ticketdetaildata;								
			$Ticketfields	   			=	 $ResponseData->Ticketfields; 
			$Agents			   			= 	 $ResponseData->Agents;
			$AllUsers		   			= 	 $ResponseData->AllUsers; 
			$CompanyID 		   			= 	 User::get_companyID();	
			$htmlgroupID 	   			= 	 $ResponseData->htmlgroupID;
			$htmlagentID       			= 	 $ResponseData->htmlagentID;
			$AllEmails 					= 	 $ResponseData->AllEmails; 			
		    $agentsAll 					=	 $ResponseData->agentsAll;			
		    $ticketSavedData			= 	 json_decode(json_encode($ResponseData->ticketSavedData),true);
			$random_token	  			=	 get_random_number();
			
			$response_api_extensions 	=    Get_Api_file_extentsions();
		   if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}	
		    $response_extensions		=	json_encode($response_api_extensions['allowed_extensions']);
			$max_file_size				=	get_max_file_size();	
			$ticketSavedData['AttachmentPaths']	=	UploadFile::DownloadFileLocal($ticketdata->AttachmentPaths);	
			
			return View::make('customer.tickets.edit', compact('data','AllUsers','Agents','Ticketfields','CompanyID','agentsAll','htmlgroupID','htmlagentID','random_token','response_extensions','max_file_size','AllEmails','ticketSavedData','TicketID'));  
		}
		else
		{
            return view_response_api($response);
        }		
	}
	  
	  function Store(){
		  
	    $this->IsValidLicense();
		$postdata 			= 	Input::all();  

		if(!isset($postdata['Ticket'])){
			return Response::json(array("status" => "failed", "message" =>"Please submit required fields."));
		}
		
		 $attachmentsinfo        =	isset($postdata['attachmentsinfo'])?$postdata['attachmentsinfo']:array(); 
        if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
            $files_array = json_decode($attachmentsinfo,true);
        }

        if(!empty($files_array) && count($files_array)>0) {
            $FilesArray = array();
            foreach($files_array as $key=> $array_file_data){
                $file_name  = basename($array_file_data['filepath']); 
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TICKET_ATTACHMENT']);
                $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                copy($array_file_data['filepath'], $destinationPath . $file_name);
                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload file." ));
                }
                $FilesArray[] = array ("filename"=>$array_file_data['filename'],"filepath"=>$amazonPath . $file_name);
               // @unlink($array_file_data['filepath']);
            }
            $postdata['file']		=	json_encode($FilesArray);
		} 
			
        $response 			= 		NeonAPI::request('tickets/store',$postdata,true,false,false);
		return json_response_api($response);     
	  
	  }
	  
	  function Update($id)
	  {	  
		  	  
	    $this->IsValidLicense();
		$postdata 			= 	Input::all(); 		
		
		if(!isset($postdata['Ticket'])){
			return Response::json(array("status" => "failed", "message" =>"Please submit required fields."));
		}
		
		 $attachmentsinfo        =	isset($postdata['attachmentsinfo'])?$postdata['attachmentsinfo']:array(); 
        if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
            $files_array = json_decode($attachmentsinfo,true);
        }

        if(!empty($files_array) && count($files_array)>0) {
            $FilesArray = array();
            foreach($files_array as $key=> $array_file_data){
                $file_name  = basename($array_file_data['filepath']); 
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TICKET_ATTACHMENT']);
                $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                copy($array_file_data['filepath'], $destinationPath . $file_name);
                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload file." ));
                }
                $FilesArray[] = array ("filename"=>$array_file_data['filename'],"filepath"=>$amazonPath . $file_name);
                @unlink($array_file_data['filepath']);
            }
            $postdata['file']		=	json_encode($FilesArray);
		} 
		
        $response 			= 		NeonAPI::request('tickets/update/'.$id,$postdata,true,false,false); 
		return json_response_api($response);  		
	 }
	 
	  function UpdateDetailPage($id){
	    $this->IsValidLicense();
		$postdata 			= 	Input::all(); 		
		
		if(!isset($postdata['Ticket'])){
			return Response::json(array("status" => "failed", "message" =>"Please submit required fields."));
		}
	    $response 			= 		NeonAPI::request('tickets/updatedetailpage/'.$id,$postdata,true,false,false); 
		return json_response_api($response);  		
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
	
	public function delete($id)
    {
		$response  		    =  	  NeonAPI::request('tickets/delete/'.$id,array(),true,true); 
		return json_response_api($response); 
    }
	
	function Detail($id){
		
		$this->IsValidLicense();
		$accountemailaddresses	=	 Account::GetAccountAllEmails(User::get_userID(),true);
		
		 $response 		=   NeonAPI::request('ticketsfields/GetDynamicFields',array(),true,false,false);   
		 $data			=	array();	
		 if($response->status=='success'){
			$ticketsfields = 	$response->data;
		 }
		 else{
			$ticketsfields = 	array();
		 } 
		
		$response 				=    NeonAPI::request('tickets/getticket/'.$id,array());
	//echo "<pre>"; print_r($response); exit;
		if(!empty($response) && $response->status == 'success' )
		{
			  $ticketdata		=	 $response->data;
			  if(!in_array($ticketdata->Requester,$accountemailaddresses))
			  {
					App::abort(403, 'You have not access to' . Request::url());		
			  }
			   
			$response_details 			 =  NeonAPI::request('tickets/getticketdetailsdata',array("admin"=>User::is_admin(),"id"=>$id),true);
		//echo "<pre>"; print_r($response_details); exit;
			if(!empty($response_details) && $response_details->status == 'success' )
			{  
				   $ResponseData				 =   $response_details->data;
				   $status			 			 =   $ResponseData->status;
				   $Priority		 			 =	 $ResponseData->Priority;
				   $Groups			 			 =	 $ResponseData->Groups; 
				   $Agents			 			 = 	 $ResponseData->Agents;
				   $response_api_extensions 	 =   Get_Api_file_extentsions();
				   $max_file_size				 =	 get_max_file_size();	
				   $CloseStatus					 =   $ResponseData->CloseStatus;  //close status id for ticket 
				   if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}	
					$response_extensions		 =	json_encode($response_api_extensions['allowed_extensions']); 
					
					$TicketConversation			 =	$ResponseData->TicketConversation;
					//$NextTicket 				 =	$ResponseData->NextTicket;
					//$PrevTicket 				 =	$ResponseData->PrevTicket;
					$ticketSavedData			 = 	json_decode(json_encode($ResponseData->ticketSavedData),true);
					$CompanyID 		 			 = 	 User::get_companyID(); 
					$agentsAll 					 =	 $ResponseData->agentsAll;			 
					$NextTicket 				 =  TicketsTable::GetNextPageID($id); 
					$PrevTicket 				 =	TicketsTable::GetPrevPageID($id);
					$show_edit					 =	0;
					
					return View::make('customer.tickets.detail', compact('data','ticketdata','status','Priority','Groups','Agents','response_extensions','max_file_size','TicketConversation',"NextTicket","PrevTicket",'CloseStatus','ticketsfields','ticketSavedData','CompanyID','agentsAll','show_edit'));  		  
			}else{
          	  return view_response_api($response_details);
         	}			 
		 }else{
            return view_response_api($response);
         }
	
	}
	
	function TicketAction(){
		
		$data 		   		= 	  Input::all();
		$action_type   		=     $data['action_type'];
		$ticket_number  	=     $data['ticket_number'];
		$ticket_type		=	  $data['ticket_type'];		
		$response  		    =  	  NeonAPI::request('tickets/ticketcction',$data,true,true);
		
		if(!empty($response) && $response['status'] == 'success' )
		{ 
			$ResponseData		 =	  $response['data'];
			$response_data       =    $ResponseData['response_data']; 
			$AccountEmail 		 = 	  $ResponseData['AccountEmail'];	
			$parent_id			 =	  $ResponseData['parent_id'];
			
			if($action_type=='forward'){ //attach current email attachments
				$data['uploadtext']  = 	 UploadFile::DownloadFileLocal($response_data['AttachmentPaths']);
			}
			
			
			return View::make('customer.tickets.ticketaction', compact('data','response_data','action_type','uploadtext','AccountEmail','parent_id'));  
		}else{
            return view_response_api($response);
        }	
	}
	
	function UpdateTicketAttributes($id)
	{
		$this->IsValidLicense();
		$data 				= 		Input::all();  
		$data['admin'] 		= 		User::is_admin();		
		$response 			= 		NeonAPI::request('tickets/updateticketattributes/'.$id,$data,true,false,false);
		return json_response_api($response);  			
	}
	
	function ActionSubmit($id){
		
		$this->IsValidLicense();
		$postdata    =  Input::all();		
		
		 $attachmentsinfo        =	$postdata['attachmentsinfo']; 
        if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
            $files_array = json_decode($attachmentsinfo,true);
        }

        	if(!empty($files_array) && count($files_array)>0)
					{
						foreach($files_array as $key=> $array_file_data)
						{
							$file_name  		= 	basename($array_file_data['filepath']); 
							$amazonPath 		= 	AmazonS3::generate_upload_path(AmazonS3::$dir['TICKET_ATTACHMENT']);
							$destinationPath 	= 	CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
			
							if (!file_exists($destinationPath))
							{
								mkdir($destinationPath, 0777, true);
							}
							
							copy($array_file_data['filepath'], $destinationPath . $file_name);
							
							if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath))
							{
								return Response::json(array("status" => "failed", "message" => "Failed to upload file." ));
							}
							
							$FilesArray[] = array ("filename"=>$array_file_data['filename'],"filepath"=>$amazonPath . $file_name);
							//@unlink($array_file_data['filepath']);
						}
						$postdata['file']		=	json_encode($FilesArray);
					}
		 
		$response 			= 		NeonAPI::request('tickets/actionsubmit/'.$id,$postdata,true,false,false);
		return json_response_api($response);     		   
	}
	
	public function GetTicketAttachment($ticketID,$attachmentID){
		$Ticketdata 	=   TicketsTable::find($ticketID);	
		
		if($Ticketdata)
		{
			$attachments 	=   unserialize($Ticketdata->AttachmentPaths);
			$attachment 	=   $attachments[$attachmentID];  
			$FilePath 		=  	AmazonS3::preSignedUrl($attachment['filepath']);	
			
			if(file_exists($FilePath)){
					download_file($FilePath);
			}else{
					header('Location: '.$FilePath);
			}
		}
         exit;		
	}
	
	public function getConversationAttachment($ticketID,$attachmentID){
		
		$Ticketdata 	=   AccountEmailLog::find($ticketID);	
				
		if($Ticketdata)
		{
			$attachments 	=   unserialize($Ticketdata->AttachmentPaths); 
			$attachment 	=   $attachments[$attachmentID]; 
			$FilePath 		=  	AmazonS3::preSignedUrl($attachment['filepath']);
			
			if(file_exists($FilePath)){
				download_file($FilePath);
			}else{
				header('Location: '.$FilePath);
			}			
		}
      	 exit; 
    }
	
	function CloseTicket($ticketID)
	{
		$response  		    =  	  NeonAPI::request('tickets/closeticket/'.$ticketID,array(),true,true); 
		return   json_response_api($response);
	}
	
	function ComposeEmail(){		 
		$data 						= 		Input::all();
		$random_token				=	 	get_random_number();
		$response_api_extensions 	=   	Get_Api_file_extentsions(); 
		if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); }		
		$response_extensions		=		json_encode($response_api_extensions['allowed_extensions']);
		$max_file_size				=		get_max_file_size();
		$AllEmails 					= 		json_encode(Messages::GetAllSystemEmails()); 	
		$AllEmailsTo 				= 		Messages::GetAllSystemEmails(0,true); 	
		$CompanyID 		 			= 		User::get_companyID(); 
		//echo "<pre>"; print_r($AllEmailsTo); exit;
		$response 		=   NeonAPI::request('ticketsfields/GetDynamicFields',array(),true,false,false);   
		$data			=	array();	
		if($response->status=='success'){
			$ticketsfields = 	$response->data;
		}
		else{
			$ticketsfields = 	array();
		}    	
		$default_status				=	TicketsTable::getDefaultStatus();
		 $agentsAll = DB::table('tblTicketGroupAgents')
            ->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()          
            ->select('tblUser.UserID', 'tblUser.FirstName', 'tblUser.LastName')
            ->get();
			
			$FromEmails	 		= TicketGroups::GetGroupsFrom();			
			//$FromEmails = json_encode($FromEmails);
		return View::make('customer.tickets.compose', compact('data','random_token','response_extensions','max_file_size','AllEmails','ticketsfields','CompanyID','agentsAll','FromEmails','default_status','AllEmailsTo'));	
	}
}