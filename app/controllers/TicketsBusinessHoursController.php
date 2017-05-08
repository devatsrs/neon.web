<?php

class TicketsBusinessHoursController extends \BaseController {

	public function __construct(){
		parent::validateTicketLicence();
	 } 	 
	 
    public function ajax_datagrid($type='') { 
		$data 		= 	Input::all();  
		if($type)
		{
			$data['Export']		=	1; 
		}
	    $response 	=   NeonAPI::request('tickets/businesshours/ajax_datagrid',$data,true); 
		 
		if(isset($data['Export']) && $data['Export'] == 1)
		{      
		 
			 $excel_data = $response->data;
        	 $excel_data = json_decode(json_encode($excel_data), true);

            if($type=='csv'){
                $file_path =  CompanyConfiguration::get('UPLOAD_PATH').'/businesshours.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path =  CompanyConfiguration::get('UPLOAD_PATH') .'/businesshours.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }          
        }	 
		
        return json_response_api($response,true,true,true);
    }
	
	 public function exports($type) { 
            $companyID  =  User::get_companyID();
            $Data 		=  TicketBusinessHours::where(["CompanyID"=>$companyID])->select(["Name","Description",DB::raw("IsDefault as DefaultData")])->get();		
		    $excel_data =  json_decode(json_encode($Data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/businesshours.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/businesshours.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
    }
	
	 public function index(){		 		
     	 return View::make('ticketsbusinesshours.index');
	 }	
	  
	public function create() {		
		$timezones 	  		=  TimeZone::getTimeZoneDropdownList();
		$companyID 	  		=  User::get_companyID();      
		$TicketHours  		=  TicketBusinessHours::$TicketHours;      
		$TicketHoursType	=  TicketBusinessHours::$TicketHoursType;
		
		return View::make('ticketsbusinesshours.create', compact('companyID',"TicketHours","TicketHoursType","timezones"));
    } 	  
	
	public function store() {
		
		$postdata 				= 		Input::all();  		
        $response 				= 		NeonAPI::request('tickets/businesshours/store',$postdata,true,false,false);
		
        if(!empty($response) && $response->status == 'success'){
            $response->redirect =  URL::to('/businesshours/');
        }
        return json_response_api($response);     
	
	}
	
	public function delete($id) {		
		$response 		= 		NeonAPI::request('tickets/businesshours/delete/'.$id,array(),true,false,false); 
		return json_response_api($response);
    }
	
	 public function edit($id) {          
		
		$companyID 	  		=   User::get_companyID();      
		$TicketHours  		=   TicketBusinessHours::$TicketHours;      
		$TicketHoursType	=   TicketBusinessHours::$TicketHoursType;		
		$BusinessHoursData	=	TicketBusinessHours::find($id);	 
		$WorkingDaysData	=	TicketsWorkingDays::ProcessWorkingDays($id); 
		$HolidaysData		=	TicketBusinessHolidays::where(["BusinessHoursID"=>$id])->get();
		
		return View::make('ticketsbusinesshours.edit', compact('companyID',"TicketHours","TicketHoursType","BusinessHoursData","WorkingDaysData","HolidaysData"));
    }
	
	function update($id){
		$postdata 				= 		Input::all();
        $response 				= 		NeonAPI::request('tickets/businesshours/update/'.$id,$postdata,true,false,false); 
        return json_response_api($response);
	}

	
}