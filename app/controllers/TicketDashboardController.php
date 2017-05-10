<?php

class TicketDashboardController extends \BaseController {
	
	public function __construct(){
		parent::validateTicketLicence();
  	}

    public function ticketSummaryWidget(){
        $data['AccessPermission'] = TicketsTable::GetTicketAccessPermission();
        $response 				= 	NeonAPI::request('tickets/get_ticket_dashboard_summary',$data);
        return json_response_api($response);
    }

    public function ticketTimeLineWidget($start){
        $data 					   = 	Input::all();
        $companyID                 =    User::get_companyID();
        $data['iDisplayStart'] 	   =	$start;
        $data['iDisplayLength']    =    20;
        $data['AccessPermission']  = TicketsTable::GetTicketAccessPermission();
        $response 				= 	NeonAPI::request('tickets/get_ticket_dashboard_timeline_widget',$data);

        if($response->status=='success') {
            if(!isset($response->data)) {
                return  Response::json(array("status" => "failed", "message" => "No Result Found","scroll"=>"end"));
            } else {
                $response =  $response->data;
            }
        }else{
            return json_response_api($response,false,true);
        }

        $fieldValues = TicketfieldsValues::getFieldValueIDLIst();
        $fieldPriority = TicketPriority::getPriorityIDLIst();
        $agents = User::select(array(DB::raw("concat(tblUser.FirstName,' ',tblUser.LastName) as FullName"), 'UserID'))->where(['CompanyID'=>$companyID])->lists('FullName', 'UserID');
        $accounts = Account::where(['CompanyID'=>$companyID])->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
        $contacts = Contact::getContacts();
        $groups = TicketGroups::getTicketGroups_dropdown();
        return View::make('dashboard.show_ajax_ticket_timeline', compact('response','fieldValues','fieldPriority','agents','accounts','contacts','groups'));
    }
}