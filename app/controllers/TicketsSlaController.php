<?php

class TicketsSlaController extends \BaseController {

	public function __construct(){
		parent::validateTicketLicence();
  	}

	 public function ajax_datagrid($type='') { 
		$data 		= 	Input::all();  
		if($type)
		{
			$data['Export']		=	1; 
		}
	    $response 	=   NeonAPI::request('tickets/sla_policies/ajax_datagrid',$data,true); 
		 
		if(isset($data['Export']) && $data['Export'] == 1)
		{      
		 
			 $excel_data = $response->data;
        	 $excel_data = json_decode(json_encode($excel_data), true);

            if($type=='csv'){
                $file_path =  CompanyConfiguration::get('UPLOAD_PATH').'/sla_policies.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path =  CompanyConfiguration::get('UPLOAD_PATH') .'/sla_policies.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }          
        }	 
		
        return json_response_api($response,true,true,true);
    }
	
	
	public function index()
	{

		return View::make('ticketssla.index', compact(''));

	}

	public function add()
	{	
		$Groups			 			=	TicketGroups::getTicketGroups(0); 
		$TicketTypes				=	TicketsTable::getTicketType(0);
		$AccountList				=	Account::getAccountList(array());
		unset($AccountList['']);
		$EscalateTime				=	TicketSla::$EscalateTime;
		$priorities 				= 	NeonAPI::request('tickets/get_priorities',array(),true,true);
		
		 $agentsAll 				= 	TicketGroupAgents::select([DB::raw("concat(IFNULL(tblUser.FirstName,''),' ' ,IFNULL(tblUser.LastName,''))  AS FullName "),"tblUser.UserID"])
            ->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()                      
			->lists('FullName','UserID');
            
		$agentsAll 					=  array("0"=> "Assigned Agent")+$agentsAll;
		
		return View::make('ticketssla.create', compact('priorities','Groups','TicketTypes','AccountList','EscalateTime','agentsAll'));
	}
	
	function store(){
		$postdata 				= 		Input::all();  		
        $response 				= 		NeonAPI::request('tickets/sla_policies/store',$postdata,true,false,false);
		
        if(!empty($response) && $response->status == 'success'){
            $response->redirect =  URL::to('/tickets/sla_policies/');
        }
        return json_response_api($response);     
	
	}
	
	function edit($id){
		
		$Groups			 			=	TicketGroups::getTicketGroups(0); 
		$TicketTypes				=	TicketsTable::getTicketType(0);
		$AccountList				=	Account::getAccountList(array());
		unset($AccountList['']);
		$EscalateTime				=	TicketSla::$EscalateTime;
		$priorities 				= 	NeonAPI::request('tickets/get_priorities',array(),true,true);
		
		 $agentsAll 				= 	TicketGroupAgents::select([DB::raw("concat(IFNULL(tblUser.FirstName,''),' ' ,IFNULL(tblUser.LastName,''))  AS FullName "),"tblUser.UserID"])
            ->join('tblUser', 'tblUser.UserID', '=', 'tblTicketGroupAgents.UserID')->distinct()                      
			->lists('FullName','UserID');
            
		$agentsAll 					=   array("0"=> "Assigned Agent")+$agentsAll;		
		$Sla						=	TicketSla::find($id);
		$targetsData				=	TicketSlaTarget::ProcessTargets($id); 
		$slaApply					=	TicketSlaPolicyApplyTo::where(['TicketSlaID'=>$id])->first();
		
		$slaApplyGroup				=	isset($slaApply->GroupFilter)?explode(",",$slaApply->GroupFilter):array();		
		$slaApplyCompany			=	isset($slaApply->CompanyFilter)?explode(",",$slaApply->CompanyFilter):array();
		$slaApplyType				=	isset($slaApply->TypeFilter)?explode(",",$slaApply->TypeFilter):array();
		$RespondedVoilation			=	TicketSlaPolicyViolation::where(['TicketSlaID'=>$id,"VoilationType"=>TicketSlaPolicyViolation::$RespondedVoilationType])->select(['Time','Value'])->first();
		
		$ResolveVoilation			=	TicketSlaPolicyViolation::where(['TicketSlaID'=>$id,"VoilationType"=>TicketSlaPolicyViolation::$ResolvedVoilationType])->select(['Time','Value'])->get();
		//echo "<pre>";	print_r($RespondedValue); exit;
		return View::make('ticketssla.edit', compact('priorities','Groups','TicketTypes','AccountList','EscalateTime','agentsAll','Sla','targetsData','slaApplyGroup','slaApplyCompany','slaApplyType','RespondedVoilation','ResolveVoilation'));
	
	}
	
	
	function update($id)
	{
		$postdata 				= 		Input::all();
        $response 				= 		NeonAPI::request('tickets/sla_policies/update/'.$id,$postdata,true,false,false); 
        return json_response_api($response);
	}
	
	public function delete($id) {		
		$response 		= 		NeonAPI::request('tickets/sla_policies/delete/'.$id,array(),true,false,false); 
		return json_response_api($response);
    }
}