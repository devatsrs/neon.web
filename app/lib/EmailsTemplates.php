<?php 
class EmailsTemplates{

	protected $EmailSubject;
	protected $EmailTemplate;
	protected $Error;
	protected $CompanyName;
	protected $AccountID;
	//@Todo:Place all replaceable variables to one array and remove unnecessary code and functions.
	static $fields = array(
				"{{AccountName}}",
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
				'{{Signature}}',
				'{{Currency}}',
				'{{OutstandingExcludeUnbilledAmount}}',
				'{{OutstandingIncludeUnbilledAmount}}',
				'{{BalanceThreshold}}',
				'{{CompanyName}}',
				"{{CompanyVAT}}",
				"{{CompanyAddress1}}",
				"{{CompanyAddress2}}",
				"{{CompanyAddress3}}",
				"{{CompanyCity}}",
				"{{CompanyPostCode}}",
				"{{CompanyCountry}}",
				"{{User}}",
				"{{Logo}}",
                "{{InvoiceLink}}",
                "{{InvoiceNumber}}",
                "{{DisputeAmount}}"
				);
	
	
	 public function __construct($data = array()){
		 foreach($data as $key => $value){
			 $this->$key = $value;
		 }		 		 
		 $this->CompanyName = Company::getName();
	}
	//@TODO:Use render function instead making each separate function.
	static function SendinvoiceSingle($InvoiceID,$type="body",$data=array(),$postdata = array()){ 
	
				$companyID								=	User::get_companyID();
				$message								=	 "";
				$replace_array							=	$data;
				$InvoiceData   							=  	Invoice::find($InvoiceID);
				$EmailTemplate 							= 	EmailTemplate::where(["SystemType"=>Invoice::EMAILTEMPLATE])->first();
				$replace_array							=	EmailsTemplates::setCompanyFields($replace_array,$InvoiceData->CompanyID);
				$replace_array 							=	EmailsTemplates::setAccountFields($replace_array,$InvoiceData->AccountID);
				$replace_array['InvoiceOutstanding'] 	=	Account::getOutstandingInvoiceAmount($companyID, $InvoiceData->AccountID, $InvoiceID, get_round_decimal_places($InvoiceData->AccountID));
			
				
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
				
				

				if($data['InvoiceURL']){		
					$replace_array['InvoiceLink'] 			= 	 $data['InvoiceURL'];
				}else{
					$replace_array['InvoiceLink'] 			= 	 URL::to('/invoice/'.$InvoiceID.'/invoice_preview');
				}
				$replace_array['InvoiceNumber']			=	 $InvoiceData->FullInvoiceNumber;		
				$RoundChargesAmount 					= 	 get_round_decimal_places($InvoiceData->AccountID);
				$replace_array['InvoiceGrandTotal']		=	 number_format($InvoiceData->GrandTotal,$RoundChargesAmount);
				
			
				
			$extraSpecific = [
				'{{InvoiceNumber}}',
				'{{InvoiceGrandTotal}}',
				'{{InvoiceOutstanding}}',
				"{{InvoiceLink}}"
			];
			
			$extraDefault	=	EmailsTemplates::$fields;
			
			$extra = array_merge($extraDefault,$extraSpecific);
			
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
		 
			$message								=	"";
			$EstimateData  							=  	Estimate::find($EstimateID);
			$replace_array							=	$data;
			$replace_array							=	EmailsTemplates::setCompanyFields($replace_array,$EstimateData->CompanyID); 
			$replace_array 							=	EmailsTemplates::setAccountFields($replace_array,$EstimateData->AccountID);
			$AccoutData 							=	Account::find($EstimateData->AccountID);
			$EmailTemplate 							= 	EmailTemplate::where(["SystemType"=>$slug])->first();
				
			$InvoiceTemplateID 		= 	AccountBilling::getInvoiceTemplateID($EstimateData->AccountID);
			$EstimateNumber			=   Estimate::getFullEstimateNumber($EstimateData,$InvoiceTemplateID);
			
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
				$replace_array['EstimateLink'] 		= 	 $data['EstimateURL'];
			}else{
				$replace_array['EstimateLink'] 		= 	 URL::to('/estimate/'.$EstimateID.'/estimate_preview');
			}
			
			$replace_array['EstimateNumber']		=	 isset($data['EstimateNumber'])?$data['EstimateNumber']:$EstimateNumber;		
			$RoundChargesAmount 					= 	 get_round_decimal_places($EstimateData->AccountID);
			$replace_array['EstimateGrandTotal']	=	 number_format($EstimateData->GrandTotal,$RoundChargesAmount);
			$replace_array['Comment']				=	 isset($data['Comment'])?$data['Comment']:EmailsTemplates::GetEstimateComments($EstimateID);
			
				 
			$extraSpecific = [
				'{{EstimateNumber}}',
				'{{EstimateGrandTotal}}',				
				"{{EstimateLink}}",
				"{{Comment}}",
				"{{Message}}",
			];

			
			$extraDefault	=	EmailsTemplates::$fields;
			
			$extra = array_merge($extraDefault,$extraSpecific);
		
			
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
    //Generic function for render the email template
    static function render($type="body",$data=array()){
        $extraSpecific                          = [];
        $replace_array							=	$data;
        $InvoiceData   							=  	isset($data['Invoice'])?$data['Invoice']:'';
        $EmailTemplate                          =   isset($data['EmailTemplate'])?$data['EmailTemplate']:'';

        if($type=="subject"){
            if(isset($data['Subject']) && !empty($data['Subject'])){
                $EmailMessage							=	 $data['Subject'];
            }elseif(!empty($EmailTemplate)){
                $EmailMessage							=	 $EmailTemplate->Subject;
            }else{
                return '';
            }
        }else{
            if(isset($data['Message']) && !empty($data['Message'])){
                $EmailMessage							=	 $data['Message'];
            }elseif(!empty($EmailTemplate)){
                $EmailMessage							=	 $EmailTemplate->TemplateBody;
            }else{
                return '';
            }
        }

        if(!empty($InvoiceData)) {
            $replace_array['InvoiceLink'] = URL::to('/invoice/' . $InvoiceData->InvoiceID . '/invoice_preview');
            $replace_array['InvoiceNumber'] = $InvoiceData->FullInvoiceNumber;
        }

        $EmailMessage = EmailsTemplates::var_replace($replace_array,$EmailMessage);
        return $EmailMessage;
    }
    //Generic function for replace variables
    static function var_replace($data,$EmailMessage){
        $replace_array = $data;
        if(isset($data['CompanyID'])){
            $replace_array							=	EmailsTemplates::setCompanyFields($replace_array,$data['CompanyID']);
        }
        if(isset($data['AccountID'])){
            $replace_array 							=	EmailsTemplates::setAccountFields($replace_array,$data['AccountID']);
        }

        $fields	=	EmailsTemplates::$fields;

        foreach($fields as $item){
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
	
	static function setCompanyFields($array,$Companyd = 0){
			if($Companyd){
				$CompanyData							=	Company::find($Companyd);
			}else{
				$CompanyData							=	Company::find(User::get_companyID());
			}
			$array['CompanyName']					=   $CompanyData->CompanyName;
			$array['CompanyVAT']					=   $CompanyData->VAT;			
			$array['CompanyAddress1']				=   $CompanyData->Address1;
			$array['CompanyAddress2']				=   $CompanyData->Address1;
			$array['CompanyAddress3']				=   $CompanyData->Address1;
			$array['CompanyCity']					=   $CompanyData->City;
			$array['CompanyPostCode']				=   $CompanyData->PostCode;
			$array['CompanyCountry']				=   $CompanyData->Country;
			$array['Logo']							=   Session::get('user_site_configrations.Logo');
			
			//$array['CompanyAddress']				=   Company::getCompanyFullAddress(User::get_companyID());
			return $array;
	}
	
	static function setAccountFields($array,$AccountID){
			$companyID						=	 User::get_companyID();
			$AccoutData 					= 	 Account::find($AccountID);			
			$array['AccountName']			=	 $AccoutData->AccountName;
			$array['FirstName']				=	 $AccoutData->FirstName;
			$array['LastName']				=	 $AccoutData->LastName;
			$array['Email']					=	 $AccoutData->Email;
			$array['Address1']				=	 $AccoutData->Address1;
			$array['Address2']				=	 $AccoutData->Address2;
			$array['Address3']				=	 $AccoutData->Address3;		
			$array['City']					=	 $AccoutData->City;
			$array['State']					=	 $AccoutData->State;
			$array['PostCode']				=	 $AccoutData->PostCode;
			$array['Country']				=	 $AccoutData->Country;
			$array['Currency']				=	 Currency::where(["CurrencyId"=>$AccoutData->CurrencyId])->pluck("Code");
			$array['OutstandingExcludeUnbilledAmount'] = Account::getOutstandingAmount($companyID, $AccountID, get_round_decimal_places($AccountID));
			$array['OutstandingIncludeUnbilledAmount'] = AccountBalance::getBalanceAmount($AccountID);
			$array['BalanceThreshold'] 				   = AccountBalance::getBalanceThresholdAmount($AccountID);
			$UserID = user::get_userID();
			   if(!empty($UserID)){
				   $UserData = user::find($UserID);
				   $array['User'] 							   = User::get_user_full_name();
				  if(isset($UserData->EmailFooter) && trim($UserData->EmailFooter) != '')
					{
						$array['Signature']= $UserData->EmailFooter;	
					}
	        	}
			return $array;
	}
	
	static function GetEstimateComments($EstimateID){
		 $str = '';
	 	 $EstimateComments = EstimateLog::get_comments($EstimateID);
		  foreach($EstimateComments as $EstimateComment)
		  {
                   $str .= $EstimateComment->Note.'<br>';
                   $str .= $EstimateComment->created_at.'<br><br>'; 
		  }
		 return $str; 
	}
	
	static function ReplaceEmail($Email,$body){
		return $EmailMessage = str_replace('#email',$Email,$body);					
	}
}
?>