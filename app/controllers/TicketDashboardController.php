<?php

class TicketDashboardController extends \BaseController {

    public function ticketSummaryWidget(){
        $data['AccessPermission'] = TicketsTable::GetTicketAccessPermission();
        $response 				= 	NeonAPI::request('tickets/get_ticket_dashboard_summary',$data);
        return json_response_api($response);
    }

    public function ticketTimeLineWidget($start){
        $data 					   = 	Input::all();
        $data['iDisplayStart'] 	   =	$start;
        $data['iDisplayLength']    =    10;
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
        return View::make('dashboard.show_ajax_ticket_timeline', compact('response','fieldValues','fieldPriority'));
    }
}