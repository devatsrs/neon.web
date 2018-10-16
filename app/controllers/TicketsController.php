<?php

class TicketsController extends \BaseController {

	public function __construct(){
		parent::validateTicketLicence();		 
	 } 	 
	
	 public function index(){	
		$postdata					= 	Input::all(); 
        $TicketPermission			=	TicketsTable::GetTicketAccessPermission();
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

        $data['AccessPermission'] 	=	 TicketsTable::GetTicketAccessPermission();
        $companyID 					= 	 User::get_companyID();
        $array						= 	 $this->GetResult($data);
        if($TicketPermission!=TicketsTable::TICKETGLOBALACCESS)	{
            $Groups	= TicketGroups::getTicketGroupsFromData($array->GroupsData);
        }

        $iDisplayLength 			= 	 $data['iDisplayLength'];
        $totalResults 				= 	 0;
		$OpenTicketStatus 			=	 TicketsTable::GetOpenTicketStatus();
		
		$overdue					=	 isset($postdata['overdue'])?$postdata['overdue']:0; 
		$duetoday					=	 isset($postdata['duetoday'])?$postdata['duetoday']:0;
		$overdueVal					=	 '';
		if($overdue ==1)
		{
			$overdueVal = TicketsTable::$DueFilter['Overdue'];
		}
		
		if($duetoday ==1)
		{
			$overdueVal = TicketsTable::$DueFilter['Today'];
		}
		//echo $overdueVal;exit;
        return View::make('tickets.index', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','EscalationTimes_json','status','Priority','Groups','Agents','Type',"Sortcolumns","per_page",'pagination',"ClosedTicketStatus","ResolvedTicketStatus",'OpenTicketStatus','overdueVal'));  

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
		$data['DueBy']				= 	 isset($data['formData']['DueBy'])?$data['formData']['DueBy']:'';		
		$data['iSortCol_0']			= 	 $data['sort_fld'];
		$data['sSortDir_0']			= 	 $data['sort_type'];
		$data['iDisplayLength'] 	= 	 $data['per_page'];
		$companyID					= 	 User::get_companyID();
		$array						=  	 $this->GetResult($data);

		if(isset($array->Code) && ($array->Code==400 || $array->Code==401)){
			\Illuminate\Support\Facades\Log::info("Ticket 401");
			\Illuminate\Support\Facades\Log::info(print_r($array,true));
			return json_response_api($array);
		}		
		if(isset($array->Code->error) && $array->Code->error=='token_expired'){
			\Illuminate\Support\Facades\Log::info("Ticket token_expired");
			\Illuminate\Support\Facades\Log::info(print_r($array,true));
			return json_response_api($array);  
		}	
		
		$resultpage  				=  	 $array->resultpage;			
		$result 					= 	 $array->ResultCurrentPage;
		$totalResults 				=    $array->totalcount; 
		$iTotalDisplayRecords 		= 	 $array->iTotalDisplayRecords;
		$iDisplayLength 			= 	 $data['iDisplayLength'];
		$Sortcolumns				=	 TicketsTable::$Sortcolumns;
		$pagination					=	 TicketsTable::$pagination;
		
		if(count($result)<1)
		{
			//if(isset($data['SearchStr']) && $data['SearchStr']!='' && $data['currentpage']==0){
				
				return json_encode(array("result"=>"No Result "));
			/*}else{			
				return '';
			}*/
		}
		 $ClosedTicketStatus   = TicketsTable::getClosedTicketStatus(true);
		 $ResolvedTicketStatus = TicketsTable::getResolvedTicketStatus(true);
		 TicketsTable::SetTicketSession($result);
       return   View::make('tickets.ajaxresults', compact('PageResult','result','iDisplayLength','iTotalDisplayRecords','totalResults','data','boxtype','TotalDraft','TotalUnreads','Sortcolumns','pagination','ClosedTicketStatus','ResolvedTicketStatus'));     
	   
	}
	
	function ajex_result_export(){
	    $postdata 					= 	Input::all(); 	
		$data['Search'] 			= 	 $postdata['Search'];
	
		if(isset($postdata['status']) && $postdata['status']!='null')
		{
			$data['status'] 			= 	 $postdata['status'];		
		}
		
		if(isset($postdata['priority']) && $postdata['priority']!='null')
		{
			$data['priority'] 			= 	 $postdata['priority'];		
		}
		
		if(isset($postdata['group']) && $postdata['group']!='null')
		{
			$data['group'] 			= 	 $postdata['group'];		
		}
		
		if(isset($postdata['agent']) && $postdata['agent']!='null')
		{
			$data['agent'] 			= 	 $postdata['agent'];		
		}
		
		if(isset($postdata['DueBy']) && $postdata['DueBy']!='null')
		{
			$data['DueBy'] 			= 	 $postdata['DueBy'];		
		}
		
		$data['iSortCol_0']			= 	 $postdata['sort_fld'];
		$data['sSortDir_0']			= 	 $postdata['sort_type'];
		$data['Export'] 			= 	 $postdata['Export'];		
		$data['iDisplayStart']		=	 0;
		$data['iDisplayLength']		=	 100;	
		$companyID					= 	 User::get_companyID();
		$array						=  	 $this->GetResult($data); 

		if(isset($array->Code) && ($array->Code==400 || $array->Code==401)){
			\Illuminate\Support\Facades\Log::info("Ticket 401");
			\Illuminate\Support\Facades\Log::info(print_r($array,true));
			return json_response_api($array);
		}		
		if(isset($array->Code->error) && $array->Code->error=='token_expired'){
			\Illuminate\Support\Facades\Log::info("Ticket token_expired");
			\Illuminate\Support\Facades\Log::info(print_r($array,true));
			return json_response_api($array);
		}	

		
		$resultpage  				=  	 $array->resultpage;			
		$result 					= 	 $array->ResultCurrentPage;		
		$type						=	 $postdata['export_type'];
		
		if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = $result;
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/tickets.csv';  
                $NeonExcel = new NeonExcelIO($file_path);
              return  $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/tickets.xls';
                $NeonExcel = new NeonExcelIO($file_path);
              return  $NeonExcel->download_excel($excel_data);
            }            
        }		
	}
	  
	  
	function GetResult($data){
		
		 if(User::is_admin())	{		
		   	$data['agent']					=	isset($data['agent'])?is_array($data['agent'])?implode(",",$data['agent']):'':'';
		 }else{
			 if( TicketsTable::GetTicketAccessPermission() == TicketsTable::TICKETRESTRICTEDACCESS)	{
			 	$data['agent']				=	user::get_userID();
			 }else{
				 $data['agent']					=	'';
			 }
		 }
		
        $response 				= 	NeonAPI::request('tickets/get_tickets',$data,true,false); 

		if($response->status=='success')
		{
			return $response->data;
		}else{
			return $response;
		}		
	}

		function add()
		{				
			$response 		=   NeonAPI::request('ticketsfields/getfields',array("fields"=>"simple"),true,false,false);   
			$data			=	array();	
			
			if($response->status=='success'){
				$Ticketfields = 	$response->data;
			}else{
				$Ticketfields =		array();
			}
		// echo "<pre>";	print_r($Ticketfields); exit;
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
			
			//$AllEmails 					= 	implode(",",(Messages::GetAllSystemEmailsWithName(0,true))); 
			$AllEmails 					= 	json_encode(Messages::GetAllSystemEmailsWithName(0,true)); 
			
			$default_status				=	TicketsTable::getDefaultStatus();
			
		   $agentsAll = DB::table('tblTicketGroupAgents')
            ->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()          
            ->select('tblUser.UserID', 'tblUser.FirstName', 'tblUser.LastName')
            ->get();
			$AllEmailsccbcc  =   json_encode(Messages::GetAllSystemEmails());				
			return View::make('tickets.create', compact('data','AllUsers','Agents','Ticketfields','CompanyID','agentsAll','htmlgroupID','htmlagentID','random_token','response_extensions','max_file_size','AllEmails','default_status',"AllEmailsccbcc"));  
	  }	
	  
	public function edit($id)
	{		
        $response  		    =  	  NeonAPI::request('tickets/edit/'.$id,array(),true);
	
		if(!empty($response) && $response->status == 'success' )
		{ 	
			$ResponseData				=	 $response->data;
			$TicketID 					=	 $id;
			$ticketdata					=	 $ResponseData->ticketdata;
			$ticketdetaildata			=	 $ResponseData->ticketdetaildata;								
			$Ticketfields	   			=	 $ResponseData->Ticketfields; 
			$Agents			   			= 	 $ResponseData->Agents;
			$AllUsers		   			= 	 $ResponseData->AllUsers; 
			$CompanyID 		   			= 	 User::get_companyID();	
			$htmlgroupID 	   			= 	 $ResponseData->htmlgroupID;
			$htmlagentID       			= 	 $ResponseData->htmlagentID;
			//$AllEmails 					= 	 implode(",",(Messages::GetAllSystemEmailsWithName(0,true))); 
			$AllEmails 					= 	json_encode(Messages::GetAllSystemEmailsWithName(0,true)); 
		    $agentsAll 					=	 $ResponseData->agentsAll;			
		    $ticketSavedData			= 	 json_decode(json_encode($ResponseData->ticketSavedData),true);
			$random_token	  			=	 get_random_number();
			
			$response_api_extensions 	=    Get_Api_file_extentsions();
		   if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}	
		    $response_extensions		=	json_encode($response_api_extensions['allowed_extensions']);
			$max_file_size				=	get_max_file_size();	
			$ticketSavedData['AttachmentPaths']	=	UploadFile::DownloadFileLocal($ticketdata->AttachmentPaths);
			$RequesterCC=$ticketdata->RequesterCC;
			
			return View::make('tickets.edit', compact('RequesterCC','data','AllUsers','Agents','Ticketfields','CompanyID','agentsAll','htmlgroupID','htmlagentID','random_token','response_extensions','max_file_size','AllEmails','ticketSavedData','TicketID'));
		}
		else
		{
            return view_response_api($response);
        }
	}
	  
	  function Store(){		  
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
		
        $response 			= 		NeonAPI::request('tickets/update/'.$id,$postdata,true,false,false); 
		return json_response_api($response);  		
	  }
	  
	  function UpdateDetailPage($id){
		$postdata 			= 	Input::all(); 		
		
		if(!isset($postdata['Ticket'])){
			return Response::json(array("status" => "failed", "message" =>"Please submit required fields."));
		}
		$postdata['Page']   =       'DetailPage';
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
	
	public function Delete($id)
    {
		$response  		    =  	  NeonAPI::request('tickets/delete/'.$id,array(),true,true); 
		return json_response_api($response);    
    }
	
	function Detail($id){
		$response =  NeonAPI::request('tickets/getticket/'.$id,array());
			
		if(!empty($response) && $response->status == 'success' )
		{
			   $ticketdata		=	 $response->data;
			  
			   if(!User::is_admin())
			   {
				  if($ticketdata->Agent!=user::get_userID())
				  {
						//App::abort(403, 'You have not access to' . Request::url());		
				  }
			   } 
		    $response 		=   NeonAPI::request('ticketsfields/GetDynamicFields',array(),true,false,false);   
			$data			=	array();	
			if($response->status=='success'){
				$ticketsfields = 	$response->data;
			}
			else{
				$ticketsfields = 	array();
			} 
			  
			$response_details 		=  NeonAPI::request('tickets/getticketdetailsdata',array("admin"=>User::is_admin(),"id"=>$id),true);
			//echo "<pre>"; 			print_r($response_details);			echo "here";  exit;
			if(!empty($response_details) && $response_details->status == 'success' )
			{  
				   $ResponseData				 =   $response_details->data;
				   $status			 			 =   $ResponseData->status;
				   $Priority		 			 =	 $ResponseData->Priority;
				   $Groups			 			 =	 $ResponseData->Groups; 
				   $Agents			 			 = 	 $ResponseData->Agents;
				   $TicketEmail			 		 = 	 $ResponseData->ticketemail;
				   $response_api_extensions 	 =   Get_Api_file_extentsions();
				   $max_file_size				 =	 get_max_file_size();	
				   $CloseStatus					 =   $ResponseData->CloseStatus;  //close status id for ticket 
					$response_extensions		 =	json_encode($response_api_extensions['allowed_extensions']);
					
					$TicketConversation			 =	$ResponseData->TicketConversation; 					
					//$NextTicket 				 =	$ResponseData->NextTicket;
					//$PrevTicket 				 =	$ResponseData->PrevTicket;
					$ticketSavedData			 = 	json_decode(json_encode($ResponseData->ticketSavedData),true);
					$CompanyID 		 			 = 	User::get_companyID(); 
					$agentsAll 					 =	$ResponseData->agentsAll;		
					
					$Requester					=	TicketsTable::SetRequester($ticketdata);
					//echo "<pre>";
					//print_r($RequesterAccount);
					$lead_owners 				 =  Lead::getLeadOwnersByRole();
		            $account_owners 			 =  Account::getAccountsOwnersByRole();
					
					 $NextTicket 				 =  TicketsTable::GetNextPageID($id); 
					 $PrevTicket 				 =	TicketsTable::GetPrevPageID($id);
					 $ticketemaildata			 =	AccountEmailLog::find($ticketdata->AccountEmailLogID); 
					 $ClosedTicketStatus   		 =  TicketsTable::getClosedTicketStatus();						 
					 $ResolvedTicketStatus   		 =  TicketsTable::getResolvedTicketStatus();

					//update new ticket off without updating updated_at
					if ( $response_details->data->ticketdata->Read == 0 ) {
						\Illuminate\Support\Facades\DB::statement("UPDATE  tblTickets SET `Read` = 1 where TicketID =" .$id);
					}

					$AllEmailsTo				= 	json_encode(Messages::GetAllSystemEmailsWithName(0,true));
					$TicketStatus				=	TicketsTable::getTicketStatusByID($ticketdata->Status);
					
					return View::make('tickets.detail', compact('data','ticketdata','status','Priority','Groups','Agents','response_extensions','max_file_size','TicketConversation',"NextTicket","PrevTicket",'CloseStatus','ticketsfields','ticketSavedData','CompanyID','agentsAll','lead_owners', 'account_owners','ticketemaildata','Requester','ClosedTicketStatus','ResolvedTicketStatus','AllEmailsTo','TicketStatus','TicketEmail'));
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
			$conversation		 =    $ResponseData['conversation'];  
			$response_data       =    $ResponseData['response_data']; 
			$AccountEmail 		 = 	  $ResponseData['AccountEmail'];	
			$parent_id			 =	  $ResponseData['parent_id'];
			$cc					 =	  $ResponseData['Cc'];
			$bcc				 =	  $ResponseData['Bcc'];
			$GroupEmail			 =	  $ResponseData['GroupEmail'];	
//			if($action_type=='forward'){ //attach current email attachments
				$data['uploadtext']  = 	 UploadFile::DownloadFileLocal($response_data['AttachmentPaths']);
//			}
			
			$FromEmails	 				=  TicketGroups::GetGroupsFrom();			
			$AllEmailsTo 				= 	json_encode(Messages::GetAllSystemEmails(0,true)); 	
	//		$AllEmails 					= 	json_encode(Messages::GetAllSystemEmails(0,true)); 
			$emailTemplates 	= 	EmailTemplate::GetUserDefinedTemplates();
			$EmailFooter="";
			if(Auth::check()){
				$EmailFooter = Auth::user()->EmailFooter;
			}
			$ticketStatusArr = TicketsTable::getTicketStatus(0);
			$statusUnResolvedKey = array_search(TicketfieldsValues::$Status_UnResolved, $ticketStatusArr);
			if($statusUnResolvedKey){
				unset($ticketStatusArr[$statusUnResolvedKey]);
			}
			return View::make('tickets.ticketaction', compact('data','response_data','action_type','uploadtext','AccountEmail','parent_id','FromEmails','cc','bcc','GroupEmail','conversation','AllEmailsTo', 'emailTemplates', 'EmailFooter', 'ticketStatusArr'));
		}else{
            return view_response_api($response);
        }		
	}
	// not in used
	function UpdateTicketAttributes($id)
	{
		$data 				= 		Input::all();  
		$data['admin'] 		= 		User::is_admin();		
		$response 			= 		NeonAPI::request('tickets/updateticketattributes/'.$id,$data,true,false,false);
		return json_response_api($response);  	
	}
	
	function ActionSubmit($id){
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
			$FilePath 		=  	AmazonS3::unSignedUrl($attachment['filepath']);
			
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
		$data 				= 		Input::all();
		$response  		    =  	  NeonAPI::request('tickets/closeticket/'.$ticketID,$data,true,true); 
		return json_response_api($response);    		
	}
	
	function ComposeEmail(){		 
		$data 						= 		Input::all();
		$random_token				=	 	get_random_number();
		$response_api_extensions 	=   	Get_Api_file_extentsions(); 
		if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); }		
		$response_extensions		=		json_encode($response_api_extensions['allowed_extensions']);
		$max_file_size				=		get_max_file_size();
		//$AllEmails 				= 		json_encode(Messages::GetAllSystemEmails()); 	
		$AllEmails 					= 	json_encode(Messages::GetAllSystemEmailsWithName(0,true)); 
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
		$default_status				=	TicketsTable::getDefaultEmailStatus();
		 $agentsAll = DB::table('tblTicketGroupAgents')
            ->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()          
            ->select('tblUser.UserID', 'tblUser.FirstName', 'tblUser.LastName')
            ->get();
			
		$FromEmails	 		= 	TicketGroups::GetGroupsFrom();			
		$emailTemplates 	= 	EmailTemplate::GetUserDefinedTemplates();
			//$FromEmails = json_encode($FromEmails);
		return View::make('tickets.compose', compact('data','random_token','response_extensions','max_file_size','AllEmails','ticketsfields','CompanyID','agentsAll','FromEmails','default_status','AllEmailsTo',"emailTemplates"));	
	}
	
	function SendMail(){		   
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
			
        $response 			= 		NeonAPI::request('tickets/SendMailTicket',$postdata,true,false,false);
		return json_response_api($response);     
	  }	
	  
	  function add_note(){
		$postdata 			= 		Input::all();  		
		$response 			= 		NeonAPI::request('tickets/add_note',$postdata,true,false,false);
		return json_response_api($response);     
	  }  

	function TicketsGlobalAccess(){
	}
	function TicketGroupAccess(){
	}
	function TicketRestrictedAccess(){
	}
	
	function GetUserGroups($data){
		$group = array();
		if(is_array($data)){
			foreach($data as $TicketData){
				if(!in_array($TicketData->GroupName,$group)){
					$group[]	 =  $TicketData->GroupName;
				}
			}
		}
		return $group;
	}
	
	function UpdateTicketDueTime($id){
		$postdata 			= 		Input::all();  
		$response 			= 		NeonAPI::request('tickets/updateticketduetime/'.$id,$postdata,true,false,false);
		return json_response_api($response);   
	}

    function BulkAction(){
        $data = Input::all();
        $response  		    =  	  NeonAPI::request('tickets/bulkactions',$data,true,true);
        return json_response_api($response);
    }

    function BulkDelete(){
        $data = Input::all();
        $response  		    =  	  NeonAPI::request('tickets/bulkdelete',$data,true,true);
        return json_response_api($response);
    }
	
	function Show_Log($id){
	    $TicketData = TicketsTable::find($id);
        return View::make('tickets.ticketslog', compact('TicketData','id'));    
	}	

	function BulkPickup()
	{
		$data = Input::all();

		if(!empty($data["SelectedIDs"]))
		{
			$Userid = User::get_userID();
			$arrTickets = TicketsTable::join('tblTicketGroupAgents', 'tblTickets.Group', '=', 'tblTicketGroupAgents.GroupID')
				->whereIn('TicketID', explode(",", $data["SelectedIDs"]))
				->where("tblTickets.Group", "!=", 0)
				->where("tblTicketGroupAgents.UserID", $Userid)
				->where("tblTickets.Agent", "!=", $Userid)
				->select(DB::raw('group_concat(TicketID) as TicketIDs'))
				->first();

			if(!empty($arrTickets->TicketIDs))
			{
				TicketsTable::whereIn("TicketID",explode(",", $arrTickets->TicketIDs))
							->update(["Agent"=>$Userid]);
			}

			return Response::json(array("status" => "success", "message" => "Bulk Pickup Successfully"));
		}
		return Response::json(array("status" => "failed", "message" => "Problem Bulk Pickup"));
	}
}