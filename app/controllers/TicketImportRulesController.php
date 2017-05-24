<?php

class TicketImportRulesController extends \BaseController {

	public function __construct(){
		parent::validateTicketLicence();
  	}

	 public function ajax_datagrid($type='') { 
	
		$data 		= 	Input::all();  
		if($type)
		{
			$data['Export']		=	1; 
		}
	    $response 	=   NeonAPI::request('tickets/importrules/ajax_datagrid',$data,true); 
		 
		if(isset($data['Export']) && $data['Export'] == 1)
		{      
		 
			 $excel_data = $response->data;
        	 $excel_data = json_decode(json_encode($excel_data), true);

            if($type=='csv'){
                $file_path =  CompanyConfiguration::get('UPLOAD_PATH').'/importrules.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path =  CompanyConfiguration::get('UPLOAD_PATH') .'/importrules.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }          
        }	 
		
        return json_response_api($response,true,true,true);
    }
	
	public function index()
	{
		$RuleStatusDropdown = json_encode(TicketImportRule::$RuleStatusDropdown);
		return View::make('ticketimportrules.index', compact('RuleStatusDropdown'));
	}

	public function add()
	{ 	
		$SubjectOrDescriptionID =  TicketImportRuleConditionType::GetSubjectOrDescriptionID();
		return View::make('ticketimportrules.create', compact('SubjectOrDescriptionID'));
	}
	
	function store(){
		
		$postdata 				= 		Input::all();  	
        $response 				= 		NeonAPI::request('tickets/importrules/store',$postdata,true,false,false);
		
        if(!empty($response) && $response->status == 'success'){
           // $response->redirect =  URL::to('/tickets/importrules/');
        }
        return json_response_api($response);     
	
	}
	
	function GetData(){
		$postdata 				= 		Input::all();  		
		if(isset($postdata['Counter']) && isset($postdata['DataType']))
		{
			$Rules				=	TicketImportRuleActionType::GetAllRules();
			$Conditions			=	TicketImportRuleConditionType::GetAllConditions();  //print_r($Rules); exit;
			$Groups			 	=	TicketGroups::getTicketGroups(0); 
			$priority 			= 	TicketPriority::getPriorityIDLIst();
			$status			 	=   TicketsTable::getTicketStatusSelectable(0); 
			$type				=	TicketsTable::getTicketType(0); 
			$agentsAll 			= 	TicketGroupAgents::select([DB::raw("concat(IFNULL(tblUser.FirstName,''),' ' ,IFNULL(tblUser.LastName,''))  AS FullName "),"tblUser.UserID"])
				->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()                      
				->lists('FullName','UserID');   
			$counter  =  (int)$postdata['Counter']+1;
			$DataType =  $postdata['DataType'];
				
			return View::make('ticketimportrules.add_new_condition_rule', compact('priority','Groups','agentsAll',"priorities","status","type","Conditions","Rules","counter","DataType"));
		}
		
	}
	
	function edit($id)
	{
		$EditImportData				=  TicketImportRule::find($id);	
		$EditImportCondition		=  TicketImportRuleCondition::GetImportRulesCondition($id);
		$EditImportAction			=  TicketImportRuleAction::GetImportRulesAction($id);			
		$SubjectOrDescriptionID 	=  TicketImportRuleConditionType::GetSubjectOrDescriptionID();
		
		$Rules						=	TicketImportRuleActionType::GetAllRules();
		$Conditions					=	TicketImportRuleConditionType::GetAllConditions();  //print_r($Rules); exit;
		$Groups			 			=	TicketGroups::getTicketGroups(0); 
		$priority 					= 	TicketPriority::getPriorityIDLIst();
		$status			 			=   TicketsTable::getTicketStatusSelectable(0); 
		$type						=	TicketsTable::getTicketType(0); 
		$agentsAll 					= 	TicketGroupAgents::select([DB::raw("concat(IFNULL(tblUser.FirstName,''),' ' ,IFNULL(tblUser.LastName,''))  AS FullName "),"tblUser.UserID"])
			->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()                      
			->lists('FullName','UserID');   
		
		return View::make('ticketimportrules.edit', compact('SubjectOrDescriptionID',"EditImportData","EditImportCondition","EditImportAction",'priority','Groups','agentsAll',"priorities","status","type","Conditions","Rules"));
	}	
	
	function update($id)
	{
		$postdata 				= 		Input::all();
        $response 				= 		NeonAPI::request('tickets/importrules/update/'.$id,$postdata,true,false,false); 
        return json_response_api($response);
	}
	
	public function delete($id) {		
		$response 		= 		NeonAPI::request('tickets/importrules/delete/'.$id,array(),true,false,false); 
		return json_response_api($response);
    }
	
	function CloneRule($id)
	{
		$EditImportData				=  TicketImportRule::find($id);	
		$EditImportCondition		=  TicketImportRuleCondition::GetImportRulesCondition($id);
		$EditImportAction			=  TicketImportRuleAction::GetImportRulesAction($id);			
		$SubjectOrDescriptionID 	=  TicketImportRuleConditionType::GetSubjectOrDescriptionID();
		
		$Rules						=	TicketImportRuleActionType::GetAllRules();
		$Conditions					=	TicketImportRuleConditionType::GetAllConditions();  //print_r($Rules); exit;
		$Groups			 			=	TicketGroups::getTicketGroups(0); 
		$priority 					= 	TicketPriority::getPriorityIDLIst();
		$status			 			=   TicketsTable::getTicketStatusSelectable(0); 
		$type						=	TicketsTable::getTicketType(0); 
		$agentsAll 					= 	TicketGroupAgents::select([DB::raw("concat(IFNULL(tblUser.FirstName,''),' ' ,IFNULL(tblUser.LastName,''))  AS FullName "),"tblUser.UserID"])
			->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()                      
			->lists('FullName','UserID');   
		
		return View::make('ticketimportrules.clone', compact('SubjectOrDescriptionID',"EditImportData","EditImportCondition","EditImportAction",'priority','Groups','agentsAll',"priorities","status","type","Conditions","Rules"));
	}	
}