<?php

class IntegrationController extends \BaseController
{

    public function __construct()
	{
		
    }
    /**
     * Display a listing of the resource.
     * GET /integration
     *
     * @return Response
     */
    public function index()
	{
		$companyID  			= 	User::get_companyID();
	    $GatewayConfiguration 	= 	IntegrationConfiguration::GetGatewayConfiguration();
		$Gateway 				= 	Gateway::getGatWayList();		
		$categories 			= 	Integration::where(["CompanyID" => $companyID,"ParentID"=>0])->orderBy('Title', 'asc')->get();
		return View::make('integration.index', compact('categories',"companyID","GatewayConfiguration","Gateway"));
    }
	
	function Update(){
		$data 			 = 	Input::all();
		$companyID  	 = 	User::get_companyID();
		 $rules = array(
            'firstcategory' => 'required',
            'secondcategory' => 'required',         
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
		
		if($data['firstcategory']=='support')
		{ 

			if($data['secondcategory']=='freshdesk')
			{			
				$rules = array(
					'FreshdeskDomain'	 => 'required',
					'FreshdeskEmail'	 => 'required|email',
					'FreshdeskPassword'  => 'required',
					'Freshdeskkey'		 => 'required',
				);
		
				$validator = Validator::make($data, $rules);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
			}
			
			$FreshdeskData = array(
					"FreshdeskDomain"=>$data['FreshdeskDomain'],
					"FreshdeskEmail"=>$data['FreshdeskEmail'],
					"FreshdeskPassword"=>$data['FreshdeskPassword'],
					"Freshdeskkey"=>$data['Freshdeskkey'],
					"FreshdeskGroup"=>$data['FreshdeskGroup']
					
			);
			
		  $data['Status'] = isset($data['Status'])?1:0;	
		  if($data['Status']==1){ //disable all other support subcategories
				$status =	array("Status"=>0);
				IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
		   }
			
			$FreshDeskDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
			if(count($FreshDeskDbData)>0)
			{
				$SaveData = array("Settings"=>json_encode($FreshdeskData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
				IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$FreshDeskDbData->IntegrationConfigurationID))->update($SaveData);	
				
			}
			else
			{	
				$SaveData = array("Settings"=>json_encode($FreshdeskData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
			 	IntegrationConfiguration::create($SaveData);
			}
			 return Response::json(array("status" => "success", "message" => "FreshDesk Settings Successfully Updated"));
		}
		
		if($data['firstcategory']=='payment')
		{ 

			if($data['secondcategory']=='Authorize.net')
			{
				$rules = array(
					'AuthorizeLoginID'	 => 'required',
					'AuthorizeTransactionKey'	 => 'required',
				);
		
				$validator = Validator::make($data, $rules);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
				
				$data['Status'] 				= 	isset($data['Status'])?1:0;	
				$data['AuthorizeTestAccount'] 	= 	isset($data['AuthorizeTestAccount'])?1:0;	
				
				$AuthorizeData = array(
					"AuthorizeLoginID"=>$data['AuthorizeLoginID'],
					"AuthorizeTransactionKey"=>$data['AuthorizeTransactionKey'],
					"AuthorizeTestAccount"=>$data['AuthorizeTestAccount']					
					);
			
				 
				$AuthorizeDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
				if(count($AuthorizeDbData)>0)
				{
						$SaveData = array("Settings"=>json_encode($AuthorizeData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$AuthorizeDbData->IntegrationConfigurationID))->update($SaveData);	
						
				}
				else
				{	
						$SaveData = array("Settings"=>json_encode($AuthorizeData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::create($SaveData);
				}
				 return Response::json(array("status" => "success", "message" => "Authorize.net Settings Successfully Updated"));
			}
			
			if($data['secondcategory']=='Paypal')
			{
				$rules = array(
					'PaypalEmail'	 => 'required|email',
					'PaypalLogoUrl'	 => 'required',
				);
		
				$validator = Validator::make($data, $rules);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
				
				$data['Status'] 		= 	isset($data['Status'])?1:0;	
				$data['PaypalLive'] 	= 	isset($data['PaypalLive'])?1:0;	
				
				$PaypalData = array(
					"PaypalEmail"=>$data['PaypalEmail'],
					"PaypalLogoUrl"=>$data['PaypalLogoUrl'],
					"PaypalLive"=>$data['PaypalLive']					
					);
			
				$PaypalDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
				if(count($PaypalDbData)>0)
				{
						$SaveData = array("Settings"=>json_encode($PaypalData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$PaypalDbData->IntegrationConfigurationID))->update($SaveData);	
						
				}
				else
				{	
						$SaveData = array("Settings"=>json_encode($PaypalData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::create($SaveData);
				}
				 return Response::json(array("status" => "success", "message" => "Paypal Settings Successfully Updated"));
			}
		}
		
		if($data['firstcategory']=='email')
		{ 

			if($data['secondcategory']=='Mandrill')
			{
				$rules = array(
					'MandrilSmtpServer'	 => 'required',
					'MandrilPort'	 => 'required',					
					'MandrilUserName'	 => 'required',
					'MandrilPassword'	 => 'required',
				);
		
				$validator = Validator::make($data, $rules);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
				
				$data['Status'] 			= 	isset($data['Status'])?1:0;	
				$data['MandrilSSL'] 		= 	isset($data['MandrilSSL'])?1:0;	
				
				$MandrilData = array(
					"MandrilSmtpServer"=>$data['MandrilSmtpServer'],
					"MandrilPort"=>$data['MandrilPort'],
					"MandrilUserName"=>$data['MandrilUserName'],
					"MandrilPassword"=>$data['MandrilPassword'],
					"MandrilSSL"=>$data['MandrilSSL'],					
					);
			
				 
				$MandrilDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
				if(count($MandrilDbData)>0)
				{
						$SaveData = array("Settings"=>json_encode($MandrilData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$MandrilDbData->IntegrationConfigurationID))->update($SaveData);						
				}
				else
				{	
						$SaveData = array("Settings"=>json_encode($MandrilData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::create($SaveData);
				}
				 return Response::json(array("status" => "success", "message" => "Mandrill Settings Successfully Updated"));
			}
		}
		
		if($data['firstcategory']=='storage')
		{ 
			if($data['secondcategory']=='AmazonS3')
			{
				$rules = array(
					'AmazonKey'	 => 'required',
					'AmazonSecret'	 => 'required',					
					'AmazonAwsBucket'	 => 'required',
					'AmazonAwsUrl'	 => 'required',
					'AmazonAwsRegion'	 => 'required',
				);
		
				$validator = Validator::make($data, $rules);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
				
				$data['Status'] 	= 	isset($data['Status'])?1:0;	
				
				$MandrilData = array(
					"AmazonKey"=>$data['AmazonKey'],
					"AmazonSecret"=>$data['AmazonSecret'],
					"AmazonAwsBucket"=>$data['AmazonAwsBucket'],
					"AmazonAwsUrl"=>$data['AmazonAwsUrl'],
					"AmazonAwsRegion"=>$data['AmazonAwsRegion'],					
					);
				 
				$MandrilDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
				if(count($MandrilDbData)>0)
				{
						$SaveData = array("Settings"=>json_encode($MandrilData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$MandrilDbData->IntegrationConfigurationID))->update($SaveData);						
				}
				else
				{	
						$SaveData = array("Settings"=>json_encode($MandrilData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);						
						IntegrationConfiguration::create($SaveData);
				}
				 return Response::json(array("status" => "success", "message" => "AmazonS3 Settings Successfully Updated"));
			}
		}	
		
		if($data['firstcategory']=='emailtracking')
		{ 
			if($data['secondcategory']=='IMAP')
			{
				$rules = array(
					'EmailTrackingEmail'	 => 'required|email',
					//'EmailTrackingName'	 => 'required',					
					'EmailTrackingServer'	 => 'required',					
					'EmailTrackingPassword'	 => 'required',				
				);
		
				$validator = Validator::make($data, $rules);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
				
				$data['Status'] 	= 	isset($data['Status'])?1:0;	
				
				$TrackingData = array(
					"EmailTrackingEmail"=>$data['EmailTrackingEmail'],
					//"EmailTrackingName"=>$data['EmailTrackingName'],					
					"EmailTrackingServer"=>$data['EmailTrackingServer'],
					"EmailTrackingPassword"=>$data['EmailTrackingPassword'],
					);
				 
				$TrackingDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
				if(count($TrackingDbData)>0)
				{
						$SaveData = array("Settings"=>json_encode($TrackingData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$TrackingDbData->IntegrationConfigurationID))->update($SaveData);						
				}
				else
				{	
						$SaveData = array("Settings"=>json_encode($TrackingData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);						
						IntegrationConfiguration::create($SaveData);
				}
				 return Response::json(array("status" => "success", "message" => "Tracking Email Settings Successfully Updated"));
			}
		}

		if($data['firstcategory']=='calendar')
		{ 
			if($data['secondcategory']=='Outlook')
			{
				$rules = array(
					'OutlookCalendarEmail'	 => 'required|email',
					'OutlookCalendarServer'	 => 'required',					
					'OutlookCalendarPassword'	 => 'required',					
				);
		
				$validator = Validator::make($data, $rules);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
				
				$data['Status'] 	= 	isset($data['Status'])?1:0;	
				
				$outlookcalendarData = array(
					"OutlookCalendarEmail"=>$data['OutlookCalendarEmail'],
					"OutlookCalendarServer"=>$data['OutlookCalendarServer'],					
					"OutlookCalendarPassword"=>$data['OutlookCalendarPassword'],
					);
				 
				$outlookcalendarDBData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
				if(count($outlookcalendarDBData)>0)
				{
						$SaveData = array("Settings"=>json_encode($outlookcalendarData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$outlookcalendarDBData->IntegrationConfigurationID))->update($SaveData);						
				}else{	
						$SaveData = array("Settings"=>json_encode($outlookcalendarData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);						
						IntegrationConfiguration::create($SaveData);
				}
				 return Response::json(array("status" => "success", "message" => "Outlook Calendar Successfully Updated"));
			}
		}
	}
	
	function CheckImapConnection(){
		set_time_limit(0); 
		ini_set('max_execution_time', 0);
		$data 			 = 	Input::all();
		$companyID  	 = 	User::get_companyID();
		
		$rules = array(
			'EmailTrackingEmail'	 => 'required|email',
			'EmailTrackingServer'	 => 'required',					
			'EmailTrackingPassword'	 => 'required',				
		);

		$validator = Validator::make($data, $rules);
	
		if ($validator->fails()) {
			return json_validator_response($validator);
		}
	
		$ImapResult =   Imap::CheckConnection($data['EmailTrackingServer'],$data['EmailTrackingEmail'],$data['EmailTrackingPassword']); Log::info(print_r($ImapResult));
		 
		return Response::json($ImapResult);
	}
}