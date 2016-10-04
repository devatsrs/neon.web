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
		if($data['firstcategory']=='accounting')
		{

			if($data['secondcategory']=='QuickBook')
			{
				$rules = array(
					'QuickBookLoginID'	  => 'required',
					'QuickBookPassqord'	  => 'required',
					'OauthConsumerKey'	  => 'required',
					'OauthConsumerSecret' => 'required',
					'AppToken' => 'required',
				);

				$validator = Validator::make($data, $rules);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$data['Status'] 				= 	isset($data['Status'])?1:0;
				$data['QuickBookSandbox'] 	= 	isset($data['QuickBookSandbox'])?1:0;

				$QuickBookData = array(
					"QuickBookLoginID"=>$data['QuickBookLoginID'],
					"QuickBookPassqord"=>$data['QuickBookPassqord'],
					"OauthConsumerKey"=>$data['OauthConsumerKey'],
					"OauthConsumerSecret"=>$data['OauthConsumerSecret'],
					"AppToken"=>$data['AppToken'],
					"QuickBookSandbox"=>$data['QuickBookSandbox']
				);

				$QuickBookDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($QuickBookDbData)>0)
				{
					$SaveData = array("Settings"=>json_encode($QuickBookData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$QuickBookDbData->IntegrationConfigurationID))->update($SaveData);

				}
				else
				{
					$SaveData = array("Settings"=>json_encode($QuickBookData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "QuickBook Settings Successfully Updated", "quickbookredirect" =>1));
			}
		}
	}
}