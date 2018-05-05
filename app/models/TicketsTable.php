<?php
class TicketsTable extends \Eloquent 
{
    protected $guarded = array("TicketID");

    protected $table = 'tblTickets';

    protected $primaryKey = "TicketID";
	
    static  $FreshdeskTicket  			= 	1;
    static  $SystemTicket 				= 	0;
	
	const TICKET						=	0;
	const EMAIL							=	1;
	const TIMELINEEMAIL					=	1;
	const TIMELINENOTE					=	2;
	const TICKETGLOBALACCESS			=	1;
	const TICKETGROUPACCESS				=	2;
	const TICKETRESTRICTEDACCESS		=	3;
	
	static  $defaultSortField 			= 	'created_at';
	static  $defaultSortType 			= 	'desc';
	static  $pagination 				= 	array("10"=>"10","25"=>"25","50"=>"50","100"=>"100");
	static  $SortcolumnsCustomer		=	array("created_at"=>"Date Created","subject"=>"Subject","status"=>"Status","updated_at"=>"Last Modified");
	static  $Sortcolumns				=	array("created_at"=>"Date Created","subject"=>"Subject","status"=>"Status","group"=>"Group","updated_at"=>"Last Modified");
	
	static  $DueFilter					=	array("Overdue"=>"Overdue","Today"=>"Today","Tomorrow"=>"Tomorrow","Next_8_hours"=>"Next 8 hours");

    public static function boot()
    {
        parent::boot();

        static::creating(function($page)
        {
            // do stuff
        });

        static::updating(function($page)
        {
            \Illuminate\Support\Facades\Log::info(print_r($page,true));
           \Illuminate\Support\Facades\Log::info('before saving');
        });

        static::updated(function($page)
        {
            \Illuminate\Support\Facades\Log::info('after saving');
        });
    }

	static function GetAgentSubmitRules(){
		 $rules 	 =  array();
		 $messages	 =  array();
		 $fields 	 = 	Ticketfields::where(['AgentReqSubmit'=>1])->get();
		 
		foreach($fields as $fieldsdata)	 
		{
			$rules[$fieldsdata->FieldType] = 'required';
			$messages[$fieldsdata->FieldType.".required"] = "The ".$fieldsdata->AgentLabel." field is required";
		}
		
		return array("rules"=>$rules,"messages"=>$messages);
	}

	
	static function getClosedTicketStatus($text = false){
		 $ValuesID =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD])->where(['tblTicketfieldsValues.FieldValueAgent'=>TicketfieldsValues::$Status_Closed])->pluck($text?Session::get('customer')?"FieldValueCustomer":"FieldValueAgent":'ValuesID');			
			return $ValuesID;
	}
	
		
	static function getResolvedTicketStatus($text =false){
		 $ValuesID =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD])->where(['tblTicketfieldsValues.FieldValueAgent'=>TicketfieldsValues::$Status_Resolved])->pluck($text?Session::get('customer')?"FieldValueCustomer":"FieldValueAgent":'ValuesID');			
			return $ValuesID;
	}
	
	static function GetOpenTicketStatus($text =false){
		 $ValuesID =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD])->where(['tblTicketfieldsValues.FieldValueAgent'=>TicketfieldsValues::$Status_Open])->pluck($text?Session::get('customer')?"FieldValueCustomer":"FieldValueAgent":'ValuesID');			
			return $ValuesID;
	}
	
	static function getTicketStatus($select=1){
		//TicketfieldsValues::WHERE
		 $row =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD])->lists('FieldValueAgent','ValuesID');
        if($select==1) {
            $row = array("0" => cus_lang("DROPDOWN_OPTION_SELECT")) + $row;
        }
			return $row;
	}
	
	static function getTicketStatusSelectable($select=1){
		//TicketfieldsValues::WHERE
		 $row =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')->where('tblTicketfieldsValues.FieldValueAgent',"!=",TicketfieldsValues::$Status_UnResolved)
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD])->lists('FieldValueAgent','ValuesID');
        if($select==1) {
            $row = array("0" => cus_lang("DROPDOWN_OPTION_SELECT")) + $row;
        }
			return $row;
	}

    static function getTicketStatusOnHold(){
        $row =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD,'tblTicketfieldsValues.FieldSlaTime'=>0])
            ->where('tblTicketfieldsValues.FieldType','!=',0)->lists('FieldValueAgent','ValuesID');
        return $row;
    }
	
	static function getCustomerTicketStatus(){
		//TicketfieldsValues::WHERE
		 $row =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD])
			 ->select('ValuesID',"FieldsID")->get();
		$return=array();
		foreach ($row as $val) {
			$return[$val->ValuesID]=cus_lang("CUST_PANEL_PAGE_TICKET_FIELDS_".$val->FieldsID."_VALUE_".$val->ValuesID);
		}

		$return = array("0"=> cus_lang("DROPDOWN_OPTION_SELECT"))+$return;
		return $return;
	}
	
	static function getTicketType($select=1){
		//TicketfieldsValues::WHERE
		 $row =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_TYPE_FLD])->lists('FieldValueAgent','ValuesID');
		if($select==1) {
			$row = array("0"=> cus_lang("DROPDOWN_OPTION_SELECT"))+$row;
		}
		return $row;
	}
	
	static function getTicketTypeByID($id,$fld='FieldValueAgent'){
		//TicketfieldsValues::WHERE
			$ValuesID =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_TYPE_FLD])->where(['tblTicketfieldsValues.ValuesID'=>$id])->pluck($fld);			
			return $ValuesID;
	}
	
	static function getTicketStatusByID($id,$fld='FieldValueAgent'){
		//TicketfieldsValues::WHERE
		 $ValuesID =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD])->where(['tblTicketfieldsValues.ValuesID'=>$id])->pluck($fld);			
			return $ValuesID;
	}
	
	
	static function getDefaultStatus($text = false){			
		//TicketfieldsValues::WHERE
		 $ValuesID =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD])->where(['tblTicketfieldsValues.FieldValueAgent'=>Ticketfields::TICKET_SYSTEM_STATUS_DEFAULT,'tblTicketfieldsValues.FieldType'=>Ticketfields::FIELD_TYPE_STATIC])->pluck($text?Session::get('customer')?"FieldValueCustomer":"FieldValueAgent":'ValuesID');			
			return $ValuesID;
	
	}
	static function getDefaultEmailStatus($text = false){
		//TicketfieldsValues::WHERE
		 $ValuesID =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
            ->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD])->where(['tblTicketfieldsValues.FieldValueAgent'=>Ticketfields::TICKET_SYSTEM_Email_STATUS_DEFAULT,'tblTicketfieldsValues.FieldType'=>Ticketfields::FIELD_TYPE_STATIC])->pluck($text?Session::get('customer')?"FieldValueCustomer":"FieldValueAgent":'ValuesID');
			return $ValuesID;

	}
	
	static function SetUpdateValues($TicketData,$ticketdetaildata,$Ticketfields){
			//$TicketData  = '';
			$data = array();
			
			foreach($Ticketfields as $TicketfieldsData)
			{	
				if(in_array($TicketfieldsData->FieldType,Ticketfields::$staticfields))
				{		
					if($TicketfieldsData->FieldType=='default_requester')
					{ 			
						$data[$TicketfieldsData->FieldType] = $TicketData->RequesterName." <".$TicketData->Requester.">";
					}
					
					if($TicketfieldsData->FieldType=='default_subject')
					{
						$data[$TicketfieldsData->FieldType] = $TicketData->Subject;
					}
					
					if($TicketfieldsData->FieldType=='default_ticket_type')
					{
						$data[$TicketfieldsData->FieldType] = $TicketData->Type;
					}
					
					if($TicketfieldsData->FieldType=='default_status')
					{
						$data[$TicketfieldsData->FieldType] = $TicketData->Status;
					}	
					
					if($TicketfieldsData->FieldType=='default_status')
					{
						$data[$TicketfieldsData->FieldType] = $TicketData->Status;
					}
					
					if($TicketfieldsData->FieldType=='default_priority')
					{
						$data[$TicketfieldsData->FieldType] = $TicketData->Priority;
					}
					
					if($TicketfieldsData->FieldType=='default_group')
					{
						$data[$TicketfieldsData->FieldType] = $TicketData->Group;
					}
					
					if($TicketfieldsData->FieldType=='default_agent')
					{
						$data[$TicketfieldsData->FieldType] = $TicketData->Agent;
					}
					
					if($TicketfieldsData->FieldType=='default_description')
					{
						$data[$TicketfieldsData->FieldType] = $TicketData->Description;
					}
				}else{
					foreach($ticketdetaildata as $ticketdetail){						
						if($TicketfieldsData->TicketFieldsID == $ticketdetail->FieldID){
							$data[$TicketfieldsData->FieldType] = $ticketdetail->FieldValue; break;
						}else{
							
							if(($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXT) || ($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTAREA) || ($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DATE)){
								$data[$TicketfieldsData->FieldType] =  '';
							}else{
								$data[$TicketfieldsData->FieldType] =  0;
							}
						}
					}
				}
				
			}
			
			$data['AttachmentPaths']  = 	 UploadFile::DownloadFileLocal($TicketData->AttachmentPaths);	
			//Log::info(print_r($data,true));	
			return $data;
	}
	
	static function checkTicketFieldPermission($array,$type="agent"){
		if($type=='agent'){
			return true;
		}
		
		if($type=='customer'){
			if(isset($array->CustomerDisplay) && $array->CustomerDisplay==1){
				return true;
			}
		}
 	    return false;
	}
	static function SetTicketSession($result){
		$session_ticket_array = [];
		foreach($result as $resultData){
			$session_ticket_array[] = 	 $resultData->TicketID; 
		}
		Session::set("TicketsIDs", $session_ticket_array);			
	}
	
	static function GetNextPageID($id){
		$NextID		 	 = 	'';
		$TicketsIDsArray =  Session::get("TicketsIDs");			
		//echo "<pre>"; print_r($TicketsIDsArray); exit;
		if(count($TicketsIDsArray)>0){
			if(in_array($id,$TicketsIDsArray)){
				$CurrentIndex 	= 	array_search($id,$TicketsIDsArray);
				if(isset($TicketsIDsArray[$CurrentIndex+1])){
					$NextID 	  	=   $TicketsIDsArray[$CurrentIndex+1];
				}
			}
		}
		return $NextID;
	}
	
	static function GetPrevPageID($id){
		$NextID		 	 = 	'';
		$TicketsIDsArray =  Session::get("TicketsIDs");			
		
		if(count($TicketsIDsArray)>0){
			if(in_array($id,$TicketsIDsArray)){
				$CurrentIndex 	= 	array_search($id,$TicketsIDsArray);
				if(isset($TicketsIDsArray[$CurrentIndex-1])){
					$NextID 	  	=   $TicketsIDsArray[$CurrentIndex-1];
				}
			}
		}
		return $NextID;	
	}
	
	static function GetTicketAccessPermission(){
		if(User::is_admin()){
			return TicketsTable::TICKETGLOBALACCESS;
		}		
		if(User::checkCategoryPermission('Tickets','All')){
			return TicketsTable::TICKETGLOBALACCESS;
		}
		if(User::checkCategoryPermission('Tickets','View.GlobalAccess')){
			return TicketsTable::TICKETGLOBALACCESS;
		}else if(User::checkCategoryPermission('Tickets','View.GroupAccess')){
			return TicketsTable::TICKETGROUPACCESS;		
		}else{
			return TicketsTable::TICKETRESTRICTEDACCESS;
		}
	}
	
	static function SetRequester($TicketData){
		$Requester = array();
		if($TicketData->AccountID){
				$data = 	DB::table('tblAccount')->where(['AccountID'=>$TicketData->AccountID])->get(array("AccountName"));
				$url = URL::to('/') . '/accounts/'.$TicketData->AccountID . '/show';
				$Requester = array("Title"=>$data[0]->AccountName,"Email"=>$TicketData->Requester,"URL"=>$url,"Contact"=>0);
		}
		if($TicketData->ContactID){
				$data = 	DB::table('tblContact')->where(['ContactID'=>$TicketData->ContactID])->get(array("FirstName","LastName","Owner"));
				$url = URL::to('/') . '/contacts/' . $TicketData->ContactID . '/show';
				$Requester = array("Title"=>$data[0]->FirstName.'&nbsp;'.$data[0]->LastName,"Email"=>$TicketData->Requester,"URL"=>$url,"Contact"=>0,"Owner"=>$data[0]->Owner);
		}
		if($TicketData->UserID){
				$data = 	DB::table('tblUser')->where(['UserID'=>$TicketData->UserID])->get(array("FirstName","LastName"));
				$url = "#";
				$Requester = array("Title"=>$data[0]->FirstName.'&nbsp;'.$data[0]->LastName,"Email"=>$TicketData->Requester,"URL"=>$url,"Contact"=>0);
		}
		return $Requester;
	}

	static function getTicketStatusWithSLAOff(){
		$row =  TicketfieldsValues::join('tblTicketfields','tblTicketfields.TicketFieldsID','=','tblTicketfieldsValues.FieldsID')
			->where(['tblTicketfields.FieldType'=>Ticketfields::TICKET_SYSTEM_STATUS_FLD,'tblTicketfieldsValues.FieldSlaTime'=>0])
			->lists('FieldValueAgent','ValuesID');
		return $row;
	}


}