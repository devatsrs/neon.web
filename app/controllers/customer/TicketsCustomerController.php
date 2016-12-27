<?php

class TicketsCustomerController extends \BaseController {

private $validlicense;	

	public function __construct(){
			$this->validlicense = Tickets::CheckTicketLicense();
		 } 
	 
	 protected function IsValidLicense(){
	 	return $this->validlicense;		
	 }
	
	 public function index(){
		 
			$this->IsValidLicense();
			$CompanyID 		 			= 	 User::get_companyID(); 
			$data 			 			= 	 array();	
			$status			 			=    TicketsTable::getTicketStatus();
			$Priority		 			=	 TicketPriority::getTicketPriority();
			$Groups			 			=	 TicketGroups::getTicketGroups(); 
			$Agents			 			= 	 User::getUserIDListAll(0);
			$Agents			 			= 	 array("0"=> "Select")+$Agents;
			$Type			 			=    TicketsTable::getTicketType();
			/////////
			$Sortcolumns				=	 TicketsTable::$Sortcolumns;
			$data['iSortCol_0']			=	 TicketsTable::$defaultSortField;
			$data['sSortDir_0']			=	 TicketsTable::$defaultSortType;
			
			$data['iDisplayStart']  	= 	 0;
			$data['iDisplayLength'] 	= 	 Config::get('app.pageSize');
			$companyID 					= 	 User::get_companyID();
			$array						= 	 $this->GetResult($data);
			$resultpage  				= 	 $array->resultpage;		 
			$result 					= 	 $array->ResultCurrentPage;
			$totalResults 				= 	 $array->totalcount; 
			$iTotalDisplayRecords 		= 	 $array->iTotalDisplayRecords;
			$iDisplayLength 			= 	 $data['iDisplayLength'];
			$data['currentpage'] 		= 	 0;
			
		
		//echo "<pre>";		print_r($result);			exit;
        return View::make('customer.tickets.index', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','EscalationTimes_json','status','Priority','Groups','Agents','Type',"Sortcolumns"));  
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
		$Sortcolumns				=	 TicketsTable::$Sortcolumns;
		//echo "<pre>";		print_r($resultpage);			exit;
		if(count($result)<1)
		{
			if(isset($data['SearchStr']) && $data['SearchStr']!='' && $data['currentpage']==0){
				
				return json_encode(array("result"=>"No Result found for ".$data['SearchStr']));
			}else{			
				return '';
			}
		} 
       return   View::make('customer.tickets.ajaxresults', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','boxtype','TotalDraft','TotalUnreads','Sortcolumns'));     
	   
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
		/*$this->IsValidLicense();
		
        if($id > 0)
		{	$TicketID 					=	$id;
			$ticketdata					=	 TicketsTable::find($id);
			$ticketdetaildata			=	 TicketsDetails::where(["TicketID"=>$id])->get();								
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
		    $ticketSavedData = 	TicketsTable::SetUpdateValues($ticketdata,$ticketdetaildata,$Ticketfields);
			//echo "<pre>";			print_r($agentsAll);			echo "</pre>";					exit;
			return View::make('customer.tickets.edit', compact('data','AllUsers','Agents','Ticketfields','CompanyID','agentsAll','htmlgroupID','htmlagentID','random_token','response_extensions','max_file_size','AllEmails','ticketSavedData','TicketID'));  
		}*/
	}
	  
	  function Store(){
		  
	    $this->IsValidLicense();
		$postdata 			= 	Input::all();  

		if(!isset($postdata['Ticket'])){
			return Response::json(array("status" => "failed", "message" =>"Please submit required fields."));
		}
		
		 $attachmentsinfo        =	$postdata['attachmentsinfo']; 
        if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
            $files_array = json_decode($attachmentsinfo,true);
        }

        if(!empty($files_array) && count($files_array)>0) {
            $FilesArray = array();
            foreach($files_array as $key=> $array_file_data){
                $file_name  = basename($array_file_data['filepath']); 
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TICKET_ATTACHMENT']);
                $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;

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
			
        $response 			= 		NeonAPI::request('tickets/store',$postdata,true,false,false);
		return json_response_api($response);     
	  
		  
	  /*  $this->IsValidLicense();
		$data 			= 	Input::all();  

				
		if(!isset($data['Ticket']))
		{
			return Response::json(array("status" => "failed", "message" =>"Please submit required fields."));
		}
		
		Log::info(print_r($data,true));
		Log::info(".....................................");
		$RulesMessages      = 	TicketsTable::GetAgentSubmitRules();       
        $validator 			= 	Validator::make($data['Ticket'], $RulesMessages['rules'], $RulesMessages['messages']);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
		
		
		 $files					=	'';
		 $attachmentsinfo        =	$data['attachmentsinfo']; 
        if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
            $files_array = json_decode($attachmentsinfo,true);
        }

        if(!empty($files_array) && count($files_array)>0)
		{
            $FilesArray = array();
			
            foreach($files_array as $key=> $array_file_data)
			{
                $file_name  		= 	basename($array_file_data['filepath']); 
                $amazonPath 		= 	AmazonS3::generate_upload_path(AmazonS3::$dir['TICKET_ATTACHMENT']);
                $destinationPath 	= 	getenv("UPLOAD_PATH") . '/' . $amazonPath;

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
                @unlink($array_file_data['filepath']);
            }
            $files		=	serialize($FilesArray);
		}
		
			$Ticketfields = $data['Ticket'];
		
			$TicketData = array(
				"CompanyID"=>User::get_companyID(),
				"Requester"=>$Ticketfields['default_requester'],
				"Subject"=>$Ticketfields['default_subject'],
				"Type"=>$Ticketfields['default_ticket_type'],
				"Status"=>$Ticketfields['default_status'],
				"Priority"=>$Ticketfields['default_priority'],
				"Group"=>$Ticketfields['default_group'],
				"Agent"=>$Ticketfields['default_agent'],
				"Description"=>$Ticketfields['default_description'],	
				"AttachmentPaths"=>$files,
				"created_at"=>date("Y-m-d H:i:s"),
				"created_by"=>User::get_user_full_name()
			);
			
			try{
 			    DB::beginTransaction();
				$TicketID = TicketsTable::insertGetId($TicketData);	
				
				foreach($Ticketfields as $key => $TicketfieldsData)
				{
					if(!in_array($key,Ticketfields::$staticfields))
					{
						$TicketFieldsID =  Ticketfields::where(["FieldType"=>$key])->pluck('TicketFieldsID');
						TicketsDetails::insert(array("TicketID"=>$TicketID,"FieldID"=>$TicketFieldsID,"FieldValue"=>$TicketfieldsData));
					}
				}				
				 DB::commit();	
            	return Response::json(array("status" => "success", "message" => "Ticket Successfully Created",'LastID'=>$TicketID));
      		 }catch (Exception $ex){ 	
			      DB::rollback();
				 return Response::json(array("status" => "failed", "message" =>$ex->getMessage()));
       		 }    */
	  }
	  
	  function Update($id)
	  {	  
		  	  
	    $this->IsValidLicense();
		$postdata 			= 	Input::all(); 		
		
		if(!isset($postdata['Ticket'])){
			return Response::json(array("status" => "failed", "message" =>"Please submit required fields."));
		}
		
		 $attachmentsinfo        =	$postdata['attachmentsinfo']; 
        if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
            $files_array = json_decode($attachmentsinfo,true);
        }

        if(!empty($files_array) && count($files_array)>0) {
            $FilesArray = array();
            foreach($files_array as $key=> $array_file_data){
                $file_name  = basename($array_file_data['filepath']); 
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TICKET_ATTACHMENT']);
                $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;

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
		
        $response 			= 		NeonAPI::request('tickets/update/'.$id,$postdata,true,false,false); Log::info(print_r($response,true));
		return json_response_api($response);  		
	  
	  
	   /* $this->IsValidLicense();
		$data 			= 	Input::all();  
		$ticketdata		=	 TicketsTable::find($id);
	    if($ticketdata)
		{
			if(!isset($data['Ticket']))
			{
				return Response::json(array("status" => "failed", "message" =>"Please submit required fields."));
			}
			
			Log::info(print_r($data,true));
			Log::info(".....................................");
			$RulesMessages      = 	TicketsTable::GetAgentSubmitRules();       
			$validator 			= 	Validator::make($data['Ticket'], $RulesMessages['rules'], $RulesMessages['messages']);
			if ($validator->fails()) {
				return json_validator_response($validator);
			}
			
			
			 $files					=	'';
			 $attachmentsinfo        =	$data['attachmentsinfo']; 
			if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
				$files_array = json_decode($attachmentsinfo,true);
			}
	
			if(!empty($files_array) && count($files_array)>0)
			{
				$FilesArray = array();
				
				foreach($files_array as $key=> $array_file_data)
				{
					$file_name  		= 	basename($array_file_data['filepath']); 
					$amazonPath 		= 	AmazonS3::generate_upload_path(AmazonS3::$dir['TICKET_ATTACHMENT']);
					$destinationPath 	= 	getenv("UPLOAD_PATH") . '/' . $amazonPath;
	
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
					@unlink($array_file_data['filepath']);
				}
				$files		=	serialize($FilesArray);
			}
			
				$Ticketfields = $data['Ticket'];
			
				$TicketData = array(
					"Requester"=>$Ticketfields['default_requester'],
					"Subject"=>$Ticketfields['default_subject'],
					"Type"=>$Ticketfields['default_ticket_type'],
					"Status"=>$Ticketfields['default_status'],
					"Priority"=>$Ticketfields['default_priority'],
					"Group"=>$Ticketfields['default_group'],
					"Agent"=>$Ticketfields['default_agent'],
					"Description"=>$Ticketfields['default_description'],	
					"AttachmentPaths"=>$files,
					"updated_at"=>date("Y-m-d H:i:s"),
					"updated_by"=>User::get_user_full_name()
				);
				
				try{
					DB::beginTransaction();
					$ticketdata->update($TicketData);	
					
					TicketsDetails::where(["TicketID"=>$id])->delete();
					foreach($Ticketfields as $key => $TicketfieldsData)
					{
						if(!in_array($key,Ticketfields::$staticfields))
						{
							$TicketFieldsID =  Ticketfields::where(["FieldType"=>$key])->pluck('TicketFieldsID');
							TicketsDetails::insert(array("TicketID"=>$id,"FieldID"=>$TicketFieldsID,"FieldValue"=>$TicketfieldsData));
						}
					}				
					 DB::commit();	
					return Response::json(array("status" => "success", "message" => "Ticket Successfully Updated",'LastID'=>$id));
				 }catch (Exception $ex){ 	
					  DB::rollback();
					 return Response::json(array("status" => "failed", "message" =>$ex->getMessage()));
				 } 
		  }else{
		  	return Response::json(array("status" => "failed", "message" =>"invalid Ticket."));
		  }*/
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
       /* if( $id > 0){
            try{
                DB::beginTransaction();
                TicketsTable::where(["TicketID"=>$id])->delete();
                TicketsDetails::where(["TicketID"=>$id])->delete();
				TicketsConversation::where(array('TicketID'=>$id))->delete();
                DB::commit();
                return Response::json(array("status" => "success", "message" => "Ticket Successfully Deleted"));

            }catch (Exception $e){
                DB::rollback();
                return Response::json(array("status" => "failed", "message" =>$e->getMessage() ));
            }

        }*/
    }
	
	function Detail($id){
		
		$this->IsValidLicense();
		$accountemailaddresses	=	 Account::GetAccountAllEmails(User::get_userID(),true);
		$response 				=    NeonAPI::request('tickets/getticket/'.$id,array());
		
		if(!empty($response) && $response->status == 'success' )
		{
			  $ticketdata		=	 $response->data;
			  if(!in_array($ticketdata->Requester,$accountemailaddresses))
			  {
					App::abort(403, 'You have not access to' . Request::url());		
			  }
			   
			$response_details 			 =  NeonAPI::request('tickets/getticketdetailsdata',array("admin"=>User::is_admin(),"id"=>$id),true);
		
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
					$NextTicket 				 =	$ResponseData->NextTicket;
					$PrevTicket 				 =	$ResponseData->PrevTicket;
					
					return View::make('customer.tickets.detail', compact('data','ticketdata','status','Priority','Groups','Agents','response_extensions','max_file_size','TicketConversation',"NextTicket","PrevTicket",'CloseStatus'));  		  
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
		
		/*$data 		   		= 	  Input::all();
		$action_type   		=     $data['action_type'];
		$ticket_number  	=     $data['ticket_number'];
		$ticket_type		=	  $data['ticket_type'];
		
		
		if($ticket_type=='parent'){
			$response_data      =     TicketsTable::find($ticket_number);
			$AccountEmail 		= 	  $response_data->Requester;	
			$parent_id			=	  0;
		}else{
			$response_data      =     TicketsConversation::find($ticket_number);
			$AccountEmail 		= 	  TicketsConversation::where(array('TicketConversationID'=>$ticket_number))->pluck('TicketTo');
			$parent_id			=	  TicketsConversation::where(array('TicketConversationID'=>$ticket_number))->pluck('TicketConversationID');
		}
		//$parent_id          =  	  $response_data['EmailParent'];	
		
		if($action_type=='forward'){ //attach current email attachments
			$data['uploadtext']  = 	 UploadFile::DownloadFileLocal($response_data->AttachmentPaths);
			Log::info(print_r($data,true));
		}
		return View::make('customers.tickets.ticketaction', compact('data','response_data','action_type','uploadtext','AccountEmail','parent_id'));  			
		*/
	}
	
	function UpdateTicketAttributes($id)
	{
		$this->IsValidLicense();
		$data 				= 		Input::all();  
		$data['admin'] 		= 		User::is_admin();		
		$response 			= 		NeonAPI::request('tickets/updateticketattributes/'.$id,$data,true,false,false);
		return json_response_api($response);  	
		/* $this->IsValidLicense();
		 if($id)
		 {
			   $ticketdata		=	 TicketsTable::find($id);
			   if($ticketdata)
			   {
				   if(!User::is_admin())
				   {
					  if($ticketdata->Agent!=user::get_userID())
					  {
						    return Response::json(array("status" => "failed", "message" =>"You have not access to update this ticket" ));
					  }
				   }
				   $data 	= 	Input::all();  
				   
				   $TicketData = array(
					"Status"=>$data['status'],
					"Priority"=>$data['priority'],
					"Group"=>$data['group'],
					"Agent"=>$data['agent'],				
					"updated_at"=>date("Y-m-d H:i:s"),
					"updated_by"=>User::get_user_full_name()
				);
				$ticketdata->update($TicketData);	
				return Response::json(array("status" => "success", "message" => "Ticket Successfully Updated")); 
			}			
		 }
		return Response::json(array("status" => "failed", "message" =>"invalid Ticket." ));*/
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
							$destinationPath 	= 	getenv("UPLOAD_PATH") . '/' . $amazonPath;
			
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
		
		 /*$this->IsValidLicense();
		 $data    =  Input::all();
		if($id)
		{
			$ticketdata		=	 TicketsTable::find($id);
			if($ticketdata)
			{
				try
				{				 
				  $rules = array(
						'email-to' =>'required',
						'Subject'=>'required',
						'Message'=>'required',					
					);
					
				 $messages = [
					 "email-to.required" => "The email recipient is required",
					 "Subject.required" => "The email Subject is required",
					 "Message.required" => "The email message field is required",				 
				];
		
					$validator = Validator::make($data, $rules,$messages);
					if ($validator->fails()) {
						return json_validator_response($validator);
					}
					$files					=	'';
					$attachmentsinfo        =	$data['attachmentsinfo']; 
					$FilesArray = array();
					if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
						$files_array = json_decode($attachmentsinfo,true);
					}
					
					
					if(!empty($files_array) && count($files_array)>0)
					{
						foreach($files_array as $key=> $array_file_data)
						{
							$file_name  		= 	basename($array_file_data['filepath']); 
							$amazonPath 		= 	AmazonS3::generate_upload_path(AmazonS3::$dir['TICKET_ATTACHMENT']);
							$destinationPath 	= 	getenv("UPLOAD_PATH") . '/' . $amazonPath;
			
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
						$files		=	serialize($FilesArray);
					}
					
					$ticketCoversationData = array(
						"TicketID"=>$id,
						"TicketTo"=>trim($data['email-to']),
						"Cc"=>trim($data['cc']),
						"Bcc"=>trim($data['bcc']),
						"Subject"=>trim($data['Subject']),
						"TicketMessage"=>trim($data['Message']),
						"TicketParentID"=>$data['TicketParent'],
						"AttachmentPaths"=>$files
					);
					
					 $data['EmailTo']  		  	= 	$data['email-to'];
					 $data['AttachmentPaths'] 	= 	$FilesArray;
					 $data['cc'] 				= 	trim($data['cc']);
					 $data['bcc'] 				= 	trim($data['bcc']);					 
					 $status 					= 	sendMail('emails.tickets.ticket', $data);
					if($status['status'] == 1)
					{
						TicketsConversation::create($ticketCoversationData);	
						if(!empty($files_array) && count($files_array)>0){	
							foreach($files_array as $key=> $array_file_data){
							@unlink($array_file_data['filepath']);	
							}
						}
							return Response::json(array("status" => "success", "message" => "Successfully Updated")); 		
					}else{
						 return Response::json(array("status" => "failed", "message" => "Problem Sending Email."));
					}
				}
				catch (Exception $e){
					DB::rollback();
					return Response::json(array("status" => "failed", "message" =>$e->getMessage() ));
				}
			}	
			 return Response::json(array("status" => "failed", "message" =>"invalid Ticket." ));
		}*/
		   
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
		
		$Ticketdata 	=   TicketsConversation::find($ticketID);	
				
		if($Ticketdata)
		{
			$attachments 	=   unserialize($Ticketdata->AttachmentPaths); print_r($attachments); exit;
			$attachment 	=   $attachments[$attachmentID]; echo $attachment; exit;
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
		$str =  json_response_api($response);    	Log::info(print_r($str,true));
		return $str;
		/*$Ticketdata 	=   TicketsTable::find($ticketID);					
		if($Ticketdata)
		{
			 $CloseStatus =  TicketsTable::getClosedTicketStatus(); 
			 $Ticketdata->update(array("Status"=>$CloseStatus));	
			 return Response::json(array("status" => "success", "message" => "Ticket Successfully Closed.","close_id"=>$CloseStatus)); 	
		}
		return Response::json(array("status" => "failed", "message" =>"invalid Ticket." ));*/
	}
}