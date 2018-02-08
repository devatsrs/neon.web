<?php

class TicketsFieldsController extends \BaseController {

private $validlicense;	
	public function __construct(){
		parent::validateTicketLicence();
	 } 
	 
	
	function index(){
		return View::make('ticketsfields.index', compact('data','response_extensions','final'));   
	}
	
	function iframe(){	
		
		$data 			= 	 array();	
		$final			=	Session::get("ticketsfields");		
		$Checkboxfields =   json_encode(Ticketfields::$Checkboxfields);
		$Ticketfields	=	Session::get("ticketsfields");		
		$final		 	=   Ticketfields::OptimizeDbFields($Ticketfields);
		$finaljson		=   json_encode($final);		
		
		return View::make('ticketsfields.iframe', compact('data','Ticketfields',"Checkboxfields","finaljson"));   
	}
	
	function iframeSubmit(){ 
	 	$data = Input::all();
		////////////////////////////////////////////////////////////////////////////////////////////////
		//echo "<pre>"; print_r(json_decode($data['jsonData'])); echo "</pre>";exit;
		$ticket_type = 0; $else_type = 0;
		foreach(json_decode($data['jsonData']) as $jsonData)
		{	 
			$data		=	array();		
			if(isset($jsonData->action) && $jsonData->action=='create')
			{	
				$data['CustomerLabel']       			   = 		$jsonData->label_in_portal;
				$data['FieldDesc']       			  	   = 		$jsonData->description;
				$data['FieldHtmlType']        			   = 	 	Ticketfields::$TypeSave[$jsonData->type];				
				$data['FieldType']  		  			   = 		$jsonData->field_type;
				$data['AgentLabel']        			   	   = 		$jsonData->label;
				$data['FieldName']        			   	   = 		$jsonData->label;				
				$data['FieldDomType']       	  		   = 		$jsonData->type;				
				$data['AgentReqSubmit']       			   = 		isset($jsonData->required)?$jsonData->required:0;
				$data['AgentReqClose']       			   = 		isset($jsonData->required_for_closure)?$jsonData->required_for_closure:0;
				$data['CustomerDisplay']       			   = 		isset($jsonData->visible_in_portal)?$jsonData->visible_in_portal:0;
				$data['CustomerEdit']       			   = 		isset($jsonData->editable_in_portal)?$jsonData->editable_in_portal:0;
				$data['CustomerReqSubmit']       		   = 		isset($jsonData->required_in_portal)?$jsonData->required_in_portal:0;
				$data['FieldOrder']       		   		   = 		$jsonData->position;				
				$data['created_at']       		   		   = 		date("Y-m-d H:i:s");
				$data['created_by']       		   		   = 		User::get_user_full_name();			
				$TicketFieldsID 						   = 		Ticketfields::insertGetId($data);		
				
				foreach($jsonData->choices as $choices){							
					$choicesdata 							= 		array();
					$choicesdata['FieldsID']	     		= 		$TicketFieldsID;					
					$choicesdata['FieldType']	     		= 		1;					
					$choicesdata['FieldValueAgent']	     	= 		$choices->value;
					$choicesdata['FieldValueCustomer']	 	= 		$choices->value;
					$choicesdata['FieldOrder']			 	= 		isset($choices->position)?$choices->position:0;
					$choicesdata['created_at']       		= 		date("Y-m-d H:i:s");
					$choicesdata['created_by']       		= 		User::get_user_full_name();		
					 $id	=	TicketfieldsValues::insertGetId($choicesdata);				
					
				}	
			}
			
			if(isset($jsonData->action) && $jsonData->action=='edit')
			{	
				//$data['TicketFieldsID']       			   = 		$jsonData->id;
				$data['CustomerLabel']       			   = 		$jsonData->label_in_portal;
				$data['FieldDesc']       			  	   = 		$jsonData->description;
				$data['FieldHtmlType']        			   = 	 	Ticketfields::$TypeSave[$jsonData->type];				
				$data['FieldType']  		  			   = 		$jsonData->field_type;
				$data['AgentLabel']        			   	   = 		$jsonData->label;				
				$data['AgentReqSubmit']       			   = 		isset($jsonData->required)?$jsonData->required:0;
				$data['AgentReqClose']       			   = 		isset($jsonData->required_for_closure)?$jsonData->required_for_closure:0;
				$data['CustomerDisplay']       			   = 		isset($jsonData->visible_in_portal)?$jsonData->visible_in_portal:0;
				$data['CustomerEdit']       			   = 		isset($jsonData->editable_in_portal)?$jsonData->editable_in_portal:0;
				$data['CustomerReqSubmit']       		   = 		isset($jsonData->required_in_portal)?$jsonData->required_in_portal:0;
				$data['FieldOrder']       		   		   = 		isset($jsonData->position)?$jsonData->position:0;				
				$data['updated_at']       		   		   = 		date("Y-m-d H:i:s");
				$data['updated_by']       		   		   = 		User::get_user_full_name();			
				
	
				Ticketfields::find($jsonData->id)->update($data);	
				
				if(count($jsonData->choices)>0)
				{
					foreach($jsonData->choices as $key => $choices)
					{ 
						$choicesdata 	  = 	array();
						
						if($data['FieldType']=='default_status')
						{
							//'status_id'=>$TicketfieldsValuesData->ValuesID
							if($choices->deleted==1)
							{
								TicketfieldsValues::find($choices->status_id)->delete();  continue;
							}
							else
							{
								if(!isset($choices->status_id)){
								$choicesdata =  array('FieldValueAgent'=>$choices->name,'FieldValueCustomer'=>$choices->customer_display_name,"FieldSlaTime"=>$choices->stop_sla_timer,'FieldsID'=>$jsonData->id,"FieldType"=>1,"FieldOrder"=>$choices->position);	
									TicketfieldsValues::insert($choicesdata); continue;
								}
								else
								{
									if(isset($choices->position)){
									$choicesdata =  array('FieldValueAgent'=>$choices->name,'FieldValueCustomer'=>$choices->customer_display_name,"FieldSlaTime"=>$choices->stop_sla_timer,"FieldOrder"=>$choices->position);	
									}else{
									$choicesdata =  array('FieldValueAgent'=>$choices->name,'FieldValueCustomer'=>$choices->customer_display_name,"FieldSlaTime"=>$choices->stop_sla_timer);	
									}
									TicketfieldsValues::find($choices->status_id)->update($choicesdata); continue;							
								}
							}
						
						}
						else if($data['FieldType']=='default_ticket_type')
						{
							if($choices->_destroy==1)
							{
									TicketfieldsValues::find($choices->id)->delete(); continue;	
							}
							else
							{								
								if(!isset($choices->id)){
									$choicesdata =  array('FieldValueAgent'=>$choices->value,'FieldValueCustomer'=>$choices->value,'FieldOrder'=>$choices->position,'FieldsID'=>$jsonData->id,"FieldType"=>1);						
									TicketfieldsValues::insert($choicesdata); continue;
								}else{
									if(isset($choices->position)){
									$choicesdata =  array('FieldValueAgent'=>$choices->value,'FieldValueCustomer'=>$choices->value,'FieldOrder'=>$choices->position);						
									}else{
									$choicesdata =  array('FieldValueAgent'=>$choices->value,'FieldValueCustomer'=>$choices->value);					}
									TicketfieldsValues::find($choices->id)->update($choicesdata);  continue;	
								}
							}
						}
						else if($data['FieldType']=='default_priority')
						{
							continue;								
						}
						else if($data['FieldType']=='default_group')
						{
							continue;								
						}						
						else
						{							
							if($choices->_destroy==1)
							{
									TicketfieldsValues::find($choices->id)->delete(); continue;	
							}
							else
							{
								
								if(!isset($choices->id)){
									$choicesdata =  array('FieldValueAgent'=>$choices->value,'FieldValueCustomer'=>$choices->value,'FieldOrder'=>$choices->position,'FieldsID'=>$jsonData->id,"FieldType"=>1);						
									TicketfieldsValues::insert($choicesdata); continue;
								}
								else
								{ 
								   if(isset($choices->position)){
										$choicesdata =  array('FieldValueAgent'=>$choices->value,'FieldValueCustomer'=>$choices->value,'FieldOrder'=>$ticket_position);					}
									else{
										$choicesdata =  array('FieldValueAgent'=>$choices->value,'FieldValueCustomer'=>$choices->value);				
									}
									TicketfieldsValues::find($TicketfieldsValuesData->id)->update($choicesdata);  continue;	
								}
							}
							
						}						
						
					}
				}				
			}
			
			if(isset($jsonData->action) && $jsonData->action=='delete')
			{
				Ticketfields::find($jsonData->id)->delete();	
				TicketfieldsValues::where(["FieldsID"=>$jsonData->id])->delete();
			}
		}
		
		return	Redirect::to('/ticketsfields/iframe'); 	
	}
	
	
	function ajax_ticketsfields(){	
		
       $response 			=   NeonAPI::request('ticketsfields/getfields',array(),true,false,false);   		
	   $message 			= 	'';       
	    $TicketfieldsData	=	array();
		
        if($response->status!='failed') {
            $TicketfieldsData = $response->data;
        }else{
            $message = json_response_api($response,false,false);
        }
		
		$Ticketfields		=   Ticketfields::OptimizeDbFields($TicketfieldsData);
		
        return View::make('ticketsfields.board', compact('Ticketfields','message'))->render();
	}
	
	function Ajax_Ticketsfields_Choices(){
		
		$data    =  Input::all();
		$field   =  $data['type'];
		$values  =  json_decode($data['values']);
		
		if(!empty($field))
		{
			return View::make('ticketsfields.board_choices', compact('field','values'))->render();				
		}
	}
	
	function Save_Single_Field(){
		$postdata    =  Input::all();	
		 try
		 {		
				DB::beginTransaction();
				$data['CustomerLabel']       			   = 		$postdata['label_in_portal'];
				$data['FieldHtmlType']        			   = 	 	Ticketfields::$TypeSave[$postdata['type']];				
				$data['FieldType']  		  			   = 		$postdata['field_type'];
				
				if(isset($postdata['label'])){
					$data['AgentLabel']        			   = 		$postdata['label'];
					$data['FieldName']        			   = 		$postdata['label'];			
				}
				
				$data['FieldDomType']       	  		   = 		$postdata['type'];				
				$data['AgentReqSubmit']       			   = 		isset($postdata['required'])?$postdata['required']:0;
				$data['AgentReqClose']       			   = 		isset($postdata['required_for_closure'])?$postdata['required_for_closure']:0;
				$data['CustomerDisplay']       			   = 		isset($postdata['visible_in_portal'])?$postdata['visible_in_portal']:0;
				$data['CustomerEdit']       			   = 		isset($postdata['editable_in_portal'])?$postdata['editable_in_portal']:0;
				$data['CustomerReqSubmit']       		   = 		isset($postdata['required_in_portal'])?$postdata['required_in_portal']:0;
				$data['FieldOrder']       		   		   = 		$postdata['position'];	
				//
							
				if(!isset($postdata['id']) && empty($postdata['id']))
				{		$data['FieldStaticType']				   =		Ticketfields::FIELD_TYPE_DYNAMIC;	
						$data['created_at']       		   		   = 		date("Y-m-d H:i:s");
						$data['created_by']       		   		   = 		User::get_user_full_name();			
						$TicketFieldsID 						   = 		Ticketfields::insertGetId($data);	

						Translation::add_system_name("CUST_PANEL_PAGE_TICKET_FIELDS_".$TicketFieldsID, $data['CustomerLabel']);

						if(isset($postdata['choices']))
						{
							$choices 								   = 		json_decode($postdata['choices']);
							
							foreach($choices as $choices_data)
							{	
								$choicesdata 							=		array();
								$choicesdata['FieldsID']	     		= 		$TicketFieldsID;					
								$choicesdata['FieldType']	     		= 		1;					
								$choicesdata['FieldValueAgent']	     	= 		$choices_data->title;
								$choicesdata['FieldValueCustomer']	 	= 		$choices_data->title;
								$choicesdata['FieldOrder']			 	= 		isset($choices_data->FieldOrder)?$choices_data->FieldOrder:0;
								$choicesdata['created_at']       		= 		date("Y-m-d H:i:s");
								$choicesdata['created_by']       		= 		User::get_user_full_name();	

								$TicketfieldsValues =  TicketfieldsValues::insertGetId($choicesdata);
								Translation::add_system_name("CUST_PANEL_PAGE_TICKET_FIELDS_".$TicketFieldsID."_VALUE_".$TicketfieldsValues, $data['CustomerLabel']);
							}
						}
				}
				else
				{	
					$data['updated_at']       		   		   = 		date("Y-m-d H:i:s");
					$data['updated_by']       		   		   = 		User::get_user_full_name();			
					Ticketfields::find($postdata['id'])->update($data);	
					Translation::update_label(Translation::$default_lang_ISOcode, "CUST_PANEL_PAGE_TICKET_FIELDS_".$postdata['id'], $data['CustomerLabel']);

					if(($postdata['type']=='dropdown') && ($postdata['field_type']!='default_status'))
					{	
						$choices 		= 	json_decode($postdata['choices']);
						$ticket_types 	= 	TicketfieldsValues::where(["FieldsID"=>$postdata['id']])->get();
						$found = 0;
						foreach($choices as $choices_data){ 							
								
							$choicesdata 							=		array();
							$choicesdata['FieldsID']	     		= 		$postdata['id'];															
							$choicesdata['FieldValueAgent']	     	= 		$choices_data->title;
							$choicesdata['FieldValueCustomer']	 	= 		$choices_data->title;
							$choicesdata['FieldOrder']			 	= 		isset($choices_data->FieldOrder)?$choices_data->FieldOrder:0;
																
							if(!isset($choices_data->ValuesID)){ 
								$choicesdata['FieldType']	     		= 		1;	
								$choicesdata['created_at']       		= 		date("Y-m-d H:i:s");
								$choicesdata['created_by']       		= 		User::get_user_full_name();	
								$TicketfieldsValues=TicketfieldsValues::insertGetId($choicesdata);
								Translation::add_system_name("CUST_PANEL_PAGE_TICKET_FIELDS_".$choicesdata['FieldsID']."_VALUE_".$TicketfieldsValues, $choicesdata['FieldValueCustomer']);
								continue;				
							}
							
							if(!empty($choices_data->ValuesID)){ 
								$choicesdata['updated_at']       		= 		date("Y-m-d H:i:s");
								$choicesdata['updated_by']       		= 		User::get_user_full_name();	
								TicketfieldsValues::find($choices_data->ValuesID)->update($choicesdata);		
								Translation::update_label(Translation::$default_lang_ISOcode, "CUST_PANEL_PAGE_TICKET_FIELDS_".$choicesdata['FieldsID']."_VALUE_".$choices_data->ValuesID, $choicesdata['FieldValueCustomer']);
								continue;
							}								
						}
						if(isset($postdata['deleted_choices']) && !empty($postdata['deleted_choices'])){
								$deleted_choices = explode(",",$postdata['deleted_choices']);
								foreach($deleted_choices as $deleted_choices_data){									
									$TicketfieldsValues = TicketfieldsValues::find($deleted_choices_data);
									$fieldsID=$TicketfieldsValues->FieldsID;
									$TicketfieldsValues->delete();
									Translation::delete_label(Translation::$default_lang_ISOcode, "CUST_PANEL_PAGE_TICKET_FIELDS_".$fieldsID."_VALUE_".$deleted_choices_data);
								}
						}					
					}
					
					if(($postdata['type']=='dropdown') && ($postdata['field_type']=='default_status'))
					{	
						$choices 		= 	json_decode($postdata['choices']);
						$ticket_types 	= 	TicketfieldsValues::where(["FieldsID"=>$postdata['id']])->get();
						$found = 0;
						foreach($choices as $choices_data){ 							
							$choicesdata 							=		array();
							$choicesdata['FieldsID']	     		= 		$postdata['id'];
							$choicesdata['FieldValueAgent']	     	= 		$choices_data->title;
							$choicesdata['FieldValueCustomer']	 	= 		$choices_data->titlecustomer;
							$choicesdata['FieldSlaTime']	 		= 		!empty($choices_data->Stop_sla_timer)?$choices_data->Stop_sla_timer:0;							
							$choicesdata['FieldOrder']			 	= 		isset($choices_data->FieldOrder)?$choices_data->FieldOrder:0;
																
							if(!isset($choices_data->ValuesID)){ 
								$choicesdata['FieldType']	     		= 		1;					
								$choicesdata['created_at']       		= 		date("Y-m-d H:i:s");
								$choicesdata['created_by']       		= 		User::get_user_full_name();	
								TicketfieldsValues::insertGetId($choicesdata);	
								Translation::add_system_name("CUST_PANEL_PAGE_TICKET_FIELDS_".$choicesdata['FieldsID']."_VALUE_".$TicketfieldsValues, $choicesdata['FieldValueCustomer']);
								continue;				
							}
							
							if(!empty($choices_data->ValuesID)){ 
								$choicesdata['updated_at']       		= 		date("Y-m-d H:i:s");
								$choicesdata['updated_by']       		= 		User::get_user_full_name();	
								TicketfieldsValues::find($choices_data->ValuesID)->update($choicesdata);		
								Translation::update_label(Translation::$default_lang_ISOcode, "CUST_PANEL_PAGE_TICKET_FIELDS_".$choicesdata['FieldsID']."_VALUE_".$choices_data->ValuesID, $choicesdata['FieldValueCustomer']);
								continue;
							}								
						}
						if(isset($postdata['deleted_choices']) && !empty($postdata['deleted_choices'])){
								$deleted_choices = explode(",",$postdata['deleted_choices']);
								foreach($deleted_choices as $deleted_choices_data){									
									$TicketfieldsValues = TicketfieldsValues::find($deleted_choices_data);
									$fieldsID=$TicketfieldsValues->FieldsID;
									$TicketfieldsValues->delete();
									Translation::delete_label(Translation::$default_lang_ISOcode, "CUST_PANEL_PAGE_TICKET_FIELDS_".$fieldsID."_VALUE_".$deleted_choices_data);
								}
						}					
					}
					
											
				}

				 DB::commit();
				   return Response::json(["status" => "success", "message" => "Successfully updated."]);
			 } catch (Exception $ex) {
                    DB::rollback();
                    return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        	}				
	}
	
	function Update_Fields_Sorting(){
		$postdata    =  Input::all(); 
		if(isset($postdata['main_fields_sort']) && !empty($postdata['main_fields_sort']))
		{
			try
			{
				DB::beginTransaction();
				$main_fields_sort = json_decode($postdata['main_fields_sort']);
				foreach($main_fields_sort as $main_fields_sort_Data){ 
					Ticketfields::find($main_fields_sort_Data->data_id)->update(array("FieldOrder"=>$main_fields_sort_Data->FieldOrder));		
				}
				if(isset($postdata['deleted_main_fields']) && !empty($postdata['deleted_main_fields']))
				{
					$arr_language=Translation::getLanguageDropdownWithFlagList();

					$main_fields_delete = explode(",",$postdata['deleted_main_fields']);

					foreach($main_fields_delete as $main_fields_delete_data){ 
						Ticketfields::find($main_fields_delete_data)->delete();		
						TicketfieldsValues::where(["FieldsID"=>$main_fields_delete_data])->delete();

						foreach($arr_language as $lang_iso=>$value){
							Translation::delete_label($lang_iso, "CUST_PANEL_PAGE_TICKET_FIELDS_".$main_fields_delete_data);
						}
					}
				}

				DB::commit();
				 return Response::json(["status" => "success", "message" => "Successfully updated."]);
			} catch (Exception $ex) {
                    DB::rollback();
                    return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        	}	
		}
	}

}