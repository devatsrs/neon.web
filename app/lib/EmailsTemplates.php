<?php 
class EmailsTemplates{

	protected $EmailSubject;
	protected $EmailTemplate;
	protected $Error;
	protected $CompanyName;
	
	 public function __construct($data = array()){
		 foreach($data as $key => $value){
			 $this->$key = $value;
		 }		 		 
		 $this->CompanyName = Company::getName();
	}
	
	static function SendinvoiceSingle($InvoiceID,$type="body",$data=array(),$postdata = array()){ 
				$message								=	 "";
				$replace_array							=	$data;
		
		/*try{*/
				$InvoiceData   							=  	 Invoice::find($InvoiceID);
				$AccoutData 							=	 Account::find($InvoiceData->AccountID);
				$EmailTemplate 							= 	 EmailTemplate::where(["SystemType"=>Invoice::EMAILTEMPLATE])->first();
				
				if($type=="subject"){
					if(isset($postdata['Subject']) && !empty($postdata['Subject'])){
						$EmailMessage							=	 $postdata['Subject'];
					}else{
						$EmailMessage							=	 $EmailTemplate->Subject;
					}
				}else{
					if(isset($postdata['Message']) && !empty($postdata['Message'])){
						$EmailMessage							=	 $postdata['Message'];
					}else{
						$EmailMessage							=	 $EmailTemplate->TemplateBody;
					}	
				}
				
				$replace_array								=	EmailsTemplates::setCompanyFields($replace_array);
				
				if($data['InvoiceURL']){		
					$replace_array['InvoiceLink'] 			= 	 $data['InvoiceURL'];
				}else{
					$replace_array['InvoiceLink'] 			= 	 URL::to('/invoice/'.$InvoiceID.'/invoice_preview');
				}
				$replace_array['FirstName']				=	 $AccoutData->FirstName;
				$replace_array['LastName']				=	 $AccoutData->LastName;
				$replace_array['Email']					=	 $AccoutData->Email;
				$replace_array['Address1']				=	 $AccoutData->Address1;
				$replace_array['Address2']				=	 $AccoutData->Address2;
				$replace_array['Address3']				=	 $AccoutData->Address3;		
				$replace_array['City']					=	 $AccoutData->City;
				$replace_array['State']					=	 $AccoutData->State;
				$replace_array['PostCode']				=	 $AccoutData->PostCode;
				$replace_array['Country']				=	 $AccoutData->Country;
				$replace_array['Address3']				=	 $AccoutData->Address3;
				$replace_array['InvoiceNumber']			=	 $InvoiceData->FullInvoiceNumber;		
				$replace_array['Currency']				=	 Currency::where(["CurrencyId"=>$AccoutData->CurrencyId])->pluck("Code");
				$RoundChargesAmount 					= 	 get_round_decimal_places($InvoiceData->AccountID);
				$replace_array['InvoiceGrandTotal']		=	 number_format($InvoiceData->GrandTotal,$RoundChargesAmount);
				$replace_array['AccountName']			=	 $AccoutData->AccountName;
				
				
				 
			$extra = [
				'{{FirstName}}',
				'{{LastName}}',
				'{{Email}}',
				'{{Address1}}',
				'{{Address2}}',
				'{{Address3}}',
				'{{City}}',
				'{{State}}',
				'{{PostCode}}',
				'{{Country}}',
				'{{InvoiceNumber}}',
				'{{InvoiceGrandTotal}}',
				'{{InvoiceOutstanding}}',
				'{{OutstandingExcludeUnbilledAmount}}',
				'{{Signature}}',
				'{{OutstandingIncludeUnbilledAmount}}',
				'{{BalanceThreshold}}',
				'{{Currency}}',
				'{{CompanyName}}',
				"{{CompanyVAT}}",
				"{{CompanyAddress}}",
				"{{AccountName}}",
				"{{InvoiceLink}}"
			];
			
			foreach($extra as $item){
				$item_name = str_replace(array('{','}'),array('',''),$item);
				if(array_key_exists($item_name,$replace_array)) {
					$EmailMessage = str_replace($item,$replace_array[$item_name],$EmailMessage);
				}
			} 
			return $EmailMessage; 
			
			/*	return array("error"=>"","status"=>"success","data"=>$EmailMessage,"from"=>$EmailTemplate->EmailFrom);	
			}catch (Exception $ex){
				return array("error"=>$ex->getMessage(),"status"=>"failed","data"=>"","from"=>$EmailTemplate->EmailFrom);	
			}*/
	}
	
	static function SendEstimateSingle($slug,$EstimateID,$type="body",$data = array(),$postdata = array()){
		 
			$message									=	 "";
			
			$replace_array								=	$data;
			$replace_array								=	EmailsTemplates::setCompanyFields($replace_array); 
		/*try{*/
				$EstimateData  							=  	 Estimate::find($EstimateID);
				$AccoutData 							=	 Account::find($EstimateData->AccountID);
				$EmailTemplate 							= 	 EmailTemplate::where(["SystemType"=>$slug])->first();
				
				
				/*if($type=="subject"){
					$EmailMessage						=	 $EmailTemplate->Subject;
				}else{
					$EmailMessage						=	 $EmailTemplate->TemplateBody;
				}*/
				
				if($type=="subject"){
					if(isset($postdata['Subject']) && !empty($postdata['Subject'])){
						$EmailMessage							=	 $postdata['Subject'];
					}else{
						$EmailMessage							=	 $EmailTemplate->Subject;
					}
				}else{
					if(isset($postdata['Message']) && !empty($postdata['Message'])){
						$EmailMessage							=	 $postdata['Message'];
					}else{
						$EmailMessage							=	 $EmailTemplate->TemplateBody;
					}	
				}			
				
				
				$replace_array['CompanyName']			=	 Company::getName($EstimateData->CompanyID);
				if(isset($data['EstimateURL'])){		
					$replace_array['EstimateLink'] 			= 	 $data['EstimateURL'];
				}else{
					$replace_array['EstimateLink'] 			= 	 URL::to('/estimate/'.$EstimateID.'/estimate_preview');
				}
				$replace_array['FirstName']				=	 $AccoutData->FirstName;
				$replace_array['LastName']				=	 $AccoutData->LastName;
				$replace_array['Email']					=	 $AccoutData->Email;
				$replace_array['Address1']				=	 $AccoutData->Address1;
				$replace_array['Address2']				=	 $AccoutData->Address2;
				$replace_array['Address3']				=	 $AccoutData->Address3;		
				$replace_array['City']					=	 $AccoutData->City;
				$replace_array['State']					=	 $AccoutData->State;
				$replace_array['PostCode']				=	 $AccoutData->PostCode;
				$replace_array['Country']				=	 $AccoutData->Country;
				$replace_array['Address3']				=	 $AccoutData->Address3;
				$replace_array['EstimateNumber']		=	 isset($data['EstimateNumber'])?$data['EstimateNumber']:$EstimateData->EstimateNumber;		
				$replace_array['Currency']				=	 Currency::where(["CurrencyId"=>$AccoutData->CurrencyId])->pluck("Code");
				$RoundChargesAmount 					= 	 get_round_decimal_places($EstimateData->AccountID);
				$replace_array['EstimateGrandTotal']	=	 number_format($EstimateData->GrandTotal,$RoundChargesAmount);
				$replace_array['AccountName']			=	 $AccoutData->AccountName;
				$replace_array['Comment']				=	 isset($data['Comment'])?$data['Comment']:'';
				
				 
			$extra = [
				'{{FirstName}}',
				'{{LastName}}',
				'{{Email}}',
				'{{Address1}}',
				'{{Address2}}',
				'{{Address3}}',
				'{{City}}',
				'{{State}}',
				'{{PostCode}}',
				'{{Country}}',
				'{{EstimateNumber}}',
				'{{EstimateGrandTotal}}',
				'{{OutstandingExcludeUnbilledAmount}}',
				'{{Signature}}',
				'{{OutstandingIncludeUnbilledAmount}}',
				'{{BalanceThreshold}}',
				'{{Currency}}',
				'{{CompanyName}}',
				"{{CompanyVAT}}",
				"{{CompanyAddress}}",
				"{{AccountName}}",
				"{{EstimateLink}}",
				"{{Comment}}",
				"{{Message}}",
				"{{User}}",
			];
			
			foreach($extra as $item){
				$item_name = str_replace(array('{','}'),array('',''),$item);
				if(array_key_exists($item_name,$replace_array)) {
					$EmailMessage = str_replace($item,$replace_array[$item_name],$EmailMessage);
				}
			} 
			return $EmailMessage; 
			
			/*	return array("error"=>"","status"=>"success","data"=>$EmailMessage,"from"=>$EmailTemplate->EmailFrom);	
			}catch (Exception $ex){
				return array("error"=>$ex->getMessage(),"status"=>"failed","data"=>"","from"=>$EmailTemplate->EmailFrom);	
			}*/
	}
	
	static function SendActiveCronJobEmail($slug,$Cronjob,$type="body",$data){
		
				$replace_array							=	 $data;
				$message								=	 "";		
				$EmailTemplate 							= 	 EmailTemplate::where(["SystemType"=>$slug])->first();
				if($type=="subject"){
					$EmailMessage						=	 $EmailTemplate->Subject;
				}else{
					$EmailMessage						=	 $EmailTemplate->TemplateBody;
				}
				$replace_array['CompanyName']			=	 Company::getName($Cronjob->CompanyID);				
				
				$extra = [
					'{{KillCommand}}',
					'{{ReturnStatus}}',
					'{{DetailOutput}}',
					'{{Minute}}',
					'{{JobTitle}}',
					'{{PID}}',
					'{{CompanyName}}',
					'{{Url}}',
				];
			
			foreach($extra as $item){
				$item_name = str_replace(array('{','}'),array('',''),$item);
				if(array_key_exists($item_name,$replace_array)) {

					if($item_name == 'DetailOutput'){
						$replace_array[$item_name] = implode("<br>",$replace_array[$item_name]);
						$EmailMessage = str_replace($item,$replace_array[$item_name],$EmailMessage);
					}else{
						$EmailMessage = str_replace($item,$replace_array[$item_name],$EmailMessage);
					}
				}
			} 
			return $EmailMessage; 	
	}
	
	static function SendRateSheetEmail($slug,$Ratesheet,$type="body",$data){
		
				$replace_array							=	 $data;				
				$message								=	 "";		
				$EmailTemplate 							= 	 EmailTemplate::where(["SystemType"=>$slug])->first();
				if($type=="subject"){
					$EmailMessage						=	 $EmailTemplate->Subject;
				}else{
					$EmailMessage						=	 $EmailTemplate->TemplateBody;
				}
				
				$extra = [
					'{{FirstName}}',
					'{{LastName}}',
					'{{RateTableName}}',
					'{{EffectiveDate}}',
					'{{RateGeneratorName}}',					
					'{{CompanyName}}',
				];
			
			foreach($extra as $item){
				$item_name = str_replace(array('{','}'),array('',''),$item);
				if(array_key_exists($item_name,$replace_array)) {					
					$EmailMessage = str_replace($item,$replace_array[$item_name],$EmailMessage);					
				}
			} 
			return $EmailMessage; 	
	}
	
	
	static function GetEmailTemplateFrom($slug){
		return EmailTemplate::where(["SystemType"=>$slug])->pluck("EmailFrom");
	}
	
	static function CheckEmailTemplateStatus($slug){
		return EmailTemplate::where(["SystemType"=>$slug])->pluck("Status");
	}
	static function setCompanyFields($array){
			$CompanyData							=	Company::find(User::get_companyID());
			$array['CompanyName']					=   Company::getName();
			$array['CompanyVAT']					=   $CompanyData->VAT;			
			$array['CompanyAddress1']				=   $CompanyData->Address1;
			$array['CompanyAddress2']				=   $CompanyData->Address1;
			$array['CompanyAddress3']				=   $CompanyData->Address1;
			$array['CompanyCity']					=   $CompanyData->City;
			$array['CompanyPostCode']				=   $CompanyData->PostCode;
			$array['CompanyCountry']				=   $CompanyData->Country;
			//$array['CompanyAddress']				=   Company::getCompanyFullAddress(User::get_companyID());
			return $array;
	}
}
?>