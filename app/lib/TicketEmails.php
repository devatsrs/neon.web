<?php 
class TicketEmails{

	protected $TriggerTypes;
	protected $Agent;
	protected $Client;
	protected $TicketID;
	protected $TicketData;
	protected $EmailTemplate;
	protected $Error;

	 public function __construct($data = array()){
		 foreach($data as $key => $value){
			 $this->$key = $value;
		 }		 		 
		 $this->TriggerEmail();
	 }
	 
	 public function TriggerEmail(){
		try
		{
			$this->TicketData	  	=  		TicketsTable::find($this->TicketID);	 				
			if(is_array($this->TriggerType))
			{
				foreach($this->TriggerType as $TriggerType){
					if(function_exists(TicketEmails::$TriggerType())){						
						$this->$TriggerType();
					}
					
				}
			}else{
				if(function_exists($this->TriggerType)){
					$this->$this->TriggerType;
				}
			}
			
		}
		catch(\Exception $ex)
		{
			Log::error("could not Trigger");
			Log::error($ex);		
			return $ex;
		}
	 }	
	 
	 function ReplaceArray($Ticketdata){
        $replace_array = array();
		if(isset($Ticketdata) && !empty($Ticketdata)){			
			$replace_array['Subject'] 			 = 		$Ticketdata->Subject;
			$replace_array['TicketID'] 			 = 		$Ticketdata->TicketID;
			$replace_array['Requester'] 		 = 		$Ticketdata->Requester;
			$replace_array['RequesterName'] 	 = 		$Ticketdata->RequesterName;
			$replace_array['Status'] 			 = 		isset($Ticketdata->Status)?TicketsTable::getTicketStatusByID($Ticketdata->Status):TicketsTable::getDefaultStatus();
			$replace_array['Priority']	 		 = 		TicketPriority::getPriorityStatusByID($Ticketdata->Priority);
			$replace_array['Description'] 	 	 = 		$Ticketdata->Description;
			$replace_array['Group'] 			 = 		isset($Ticketdata->Group)?TicketGroups::where(['GroupID'=>$Ticketdata->Group])->pluck("GroupName"):'';
			$replace_array['Type'] 				 = 		isset($Ticketdata->Type)?TicketsTable::getTicketTypeByID($Ticketdata->Type):'';
			$replace_array['Date']				 = 		$Ticketdata->created_at;
		}    
		$Signature 			= 	'';
		$JobLoggedUser 		= 	User::find(User::get_userID());
		
        if(!empty($JobLoggedUser)){
          if(isset($JobLoggedUser->EmailFooter) && trim($JobLoggedUser->EmailFooter) != '')
            {
                $Signature = $JobLoggedUser->EmailFooter;
            }
        }
		
        $replace_array['Signature']= $Signature;		
        return $replace_array;
    }	
	
	function template_var_replace($EmailMessage,$replace_array){

		$extra = [
			'{{Subject}}',
			'{{TicketID}}',
			'{{Requester}}',
			'{{RequesterName}}',
			'{{Status}}',
			'{{Priority}}',
			'{{Description}}',
			'{{Group}}',
			'{{Type}}',
			'{{Date}}',
			'{{Signature}}'
		];
	
		foreach($extra as $item){
			$item_name = str_replace(array('{','}'),array('',''),$item);
			if(array_key_exists($item_name,$replace_array)) {
				$EmailMessage = str_replace($item,$replace_array[$item_name],$EmailMessage);
			}
		}
		return $EmailMessage;
	} 
	
	protected function AgentNewTicketCreated(){
		
			$slug					=		"AgentNewTicketCreated";
			
			if(!$this->CheckBasicRequirments())
			{
				return $this->Error;
			}
			
			$this->EmailTemplate  	=		EmailTemplate::where(["SystemType"=>$slug])->first();									
		 	$replace_array			= 		$this->ReplaceArray($this->TicketData);
		    $finalBody 				= 		$this->template_var_replace($this->EmailTemplate->TemplateBody,$replace_array);
			$finalSubject			= 		$this->template_var_replace($this->EmailTemplate->Subject,$replace_array);					
	}
	
	protected function SetError($error){
		$this->Error = $error;
	}
	public function GetError(){
		return $this->Error;
	}
	
	protected function CheckBasicRequirments(){
				
		if(!isset($this->TicketData->Agent)){
			$this->SetError("No Agent Found");				
		}
		else
		{
			$agent =  User::find($this->TicketData->Agent);
			if(!$agent)
			{
				$this->SetError("Invalid Agent");					
			}
			$this->Agent = $agent;				
		}
		
		if(!isset($this->EmailFrom) || empty($this->EmailFrom))
		{
			if(!isset($this->TicketData->Group))
			{
				$this->SetError("No group Found");		
				
			}
			else
			{
				$group =  TicketGroups::find($this->TicketData->Group);
				if(!$group)
				{
					$this->SetError("Invalid Group");						
				}
				$this->Group = $group;
			}
		}
		else
		{
			$group  = 	TicketGroups::where(["GroupEmailAddress"=>$this->EmailFrom])->first();
			if(!$group)
			{
				$this->SetError("Invalid Group");				
			}
			$this->Group = $group;
		}
		
		$this->EmailTemplate  		=		EmailTemplate::where(["SystemType"=>$this->slug])->first();									
		if(!$this->EmailTemplate){
			$this->SetError("No email template found.");				
		}
		if(!$this->EmailTemplate->Status){
			$this->SetError("Email template status disabled");				
		}
		
		if($this->GetError()){
			return false;
		}		
		return true;
	}
}
?>