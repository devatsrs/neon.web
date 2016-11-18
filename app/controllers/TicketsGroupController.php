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
		$AllUsers		= 	User::getUserIDListAll(1);
		$data 			= 	array();		
        return View::make('ticketgroups.group_create', compact('data','AllUsers','Agents'));  
	  }	
	  
	  function Edit($id){	   
		$this->IsValidLicense();
		$ticketdata		=	TicketGroups::find($id);		 
		$Groupagents	=	array();
		
		$Groupagentsdb	=	TicketGroupAgents::where(["GroupID"=>$id])->get(); 
		foreach($Groupagentsdb as $Groupagentsdata){
			$Groupagents[] = $Groupagentsdata->UserID;
		} 
		
		$Agents			= 	User::getUserIDListAll(0);
		$AllUsers		= 	User::getUserIDListAll(1);
		$data 			= 	array();		
        return View::make('ticketgroups.group_edit', compact('data','AllUsers','Agents','ticketdata','Groupagents'));  
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
            'GroupEmailAddress' => 'required|email|min:5',
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
				"GroupEmailAddress"=>$data['GroupEmailAddress'],
				"GroupAssignTime"=>$data['GroupAssignTime'],
				"GroupAssignEmail"=>$data['GroupAssignEmail'],
				"GroupAuomatedReply"=>$data['GroupAuomatedReply']
			);
			
			try{
				$GroupID = TicketGroups::insertGetId($GroupData);		
				if(is_array($data['GroupAgent'])){
					foreach($data['GroupAgent'] as $GroupAgents){
						$TicketGroupAgents =	array("GroupID"=>$GroupID,'UserID'=>$GroupAgents,"created_at"=>date("Y-m-d H:i:s"),"created_by"=>User::get_user_full_name());   
						TicketGroupAgents::Insert($TicketGroupAgents);
					}
				}		
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
            'GroupEmailAddress' => 'required|email|min:5',
            'GroupAssignEmail' => 'required',
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
			try{
				if(isset($TicketGroup->GroupID)){
					
					$grpagents = $data['GroupAgent'];
					unset($data['GroupAgent']);
					unset($data['_wysihtml5_mode']);
					
					$TicketGroup->update($data);  	 //update groups
					TicketGroupAgents::where(["GroupID" => $TicketGroup->GroupID])->delete(); //delete old group agents
					
					if(is_array($grpagents)){
						foreach($grpagents as $GroupAgents){	 //insert new agents						  
							$TicketGroupAgents =	array("GroupID"=>$TicketGroup->GroupID,'UserID'=>$GroupAgents,"updated_at"=>date("Y-m-d H:i:s"),"updated_by"=>User::get_user_full_name());   
							TicketGroupAgents::Insert($TicketGroupAgents);
						}
					}		
					return Response::json(array("status" => "success", "message" => "Group Successfully Updated",'LastID'=>$TicketGroup->GroupID));
				}
      		 }catch (Exception $ex){ 	
				 return Response::json(array("status" => "failed", "message" =>$ex->getMessage()));
       		 }  
	  }
}