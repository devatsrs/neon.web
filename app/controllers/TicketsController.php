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
			
			$CompanyID 		 = 	 User::get_companyID(); 
			$data 			 = 	 array();	
			$status			 =   TicketsTable::getTicketStatus();
			$Priority		 =	 TicketPriority::getTicketPriority();
			$Groups			 =	 TicketGroups::getTicketGroups(); 
			$Agents			 = 	 User::getUserIDListAll(0);
			$Agents			 = 	 array("0"=> "Select")+$Agents;
			$Type			 =   TicketsTable::getTicketType();
			return View::make('tickets.index', compact('data','EscalationTimes_json','status','Priority','Groups','Agents','Type'));   
	  }	
	  
	  function ajax_datagrid($type)
	  {	
		   $CompanyID 				= 	User::get_companyID();       
		   $data 					= 	Input::all();
		   $data['iDisplayStart'] 	+=	1;
		   $search		 			=	isset($data['Search'])?$data['Search']:'';	   		   
		   $status					=	isset($data['status'])?$data['status']:0;		   
		   $Type					=	isset($data['type'])?$data['type']:0;
		   $priority				=	isset($data['priority'])?$data['priority']:0;
		   $Group					=	isset($data['group'])?$data['group']:0;
		   $agent					=	isset($data['agent'])?$data['agent']:0;
		   $columns 	 			= 	array('TicketID','Subject','Requester','Type','Status','Priority','Group','Agent');
		   $sort_column 			= 	$columns[$data['iSortCol_0']];
			
			$query 	= 	"call prc_GetSystemTicket (".$CompanyID.",'".$search."','".$status."','".$Type."','".$priority."','".$Group."','".$agent."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";  
	
			if(isset($data['Export']) && $data['Export'] == 1) {
				$excel_data  = DB::select($query.',1)');
				$excel_data = json_decode(json_encode($excel_data),true);
	
				if($type=='csv'){
					$file_path = getenv('UPLOAD_PATH') .'/Tickets.csv';
					$NeonExcel = new NeonExcelIO($file_path);
					$NeonExcel->download_csv($excel_data);
				}elseif($type=='xlsx'){
					$file_path = getenv('UPLOAD_PATH') .'/Tickets.xls';
					$NeonExcel = new NeonExcelIO($file_path);
					$NeonExcel->download_excel($excel_data);
				}          
			}
			$query .=',0)';  Log::info($query);
	
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
	  
	public function edit($id)
	{
		$this->IsValidLicense();
		
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
			return View::make('tickets.edit', compact('data','AllUsers','Agents','Ticketfields','CompanyID','agentsAll','htmlgroupID','htmlagentID','random_token','response_extensions','max_file_size','AllEmails','ticketSavedData','TicketID'));  
		}
	}
	  
	  function Store(){
	    $this->IsValidLicense();
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
       		 }    
	  }
	  
	  function Update($id){
	  
	    $this->IsValidLicense();
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
	
	public function delete($id)
    {
        if( $id > 0){
            try{
                DB::beginTransaction();
                TicketsTable::where(["TicketID"=>$id])->delete();
                TicketsDetails::where(["TicketID"=>$id])->delete();
                DB::commit();
                return Response::json(array("status" => "success", "message" => "Ticket Successfully Deleted"));

            }catch (Exception $e){
                DB::rollback();
                return Response::json(array("status" => "failed", "message" =>$e->getMessage() ));
            }

        }
    }

}