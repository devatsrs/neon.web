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
		if(is_reseller()){
			$categories 			= 	Integration::where(["Slug"=>SiteIntegration::$PaymentSlug])
													->orWhere(["Slug"=>SiteIntegration::$AccountingSlug])
													->orderBy('Title', 'asc')->get();
		}else{
			$categories 			= 	Integration::where(["ParentID"=>0])->orderBy('Title', 'asc')->get();
		}
		$TaxLists =  TaxRate::where(["CompanyId" => $companyID, "Status" => 1])->get();
		//$companyID = 1;
		return View::make('integration.index', compact('categories',"companyID","GatewayConfiguration","Gateway","TaxLists"));
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
		
		if($data['firstcategory']=='support') {
            if($data['secondcategory']=='FreshDesk') {
                $FreshDeskDbData = IntegrationConfiguration::where(['CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']])->first();
				$rules = array(
					'FreshdeskDomain'	 => 'required',
					'FreshdeskEmail'	 => 'required|email',
					'Freshdeskkey'		 => 'required',
				);
				
				$messages = [
				 "FreshdeskDomain.required" => "The Domain field is required",
				 "FreshdeskEmail.required" => "The email field is required",
				 "Freshdeskkey.required" => "The key field is required",
				 
				];

                if(count($FreshDeskDbData)==0){
                    $rules['FreshdeskPassword'] = 'required';
                    $messages['FreshdeskPassword.required'] = 'required';
                }
		
				$validator = Validator::make($data, $rules,$messages);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
			
			
			$FreshdeskData = array(
					"FreshdeskDomain"=>$data['FreshdeskDomain'],
					"FreshdeskEmail"=>$data['FreshdeskEmail'],
					"Freshdeskkey"=>$data['Freshdeskkey'],
					"FreshdeskGroup"=>$data['FreshdeskGroup']
					
			);
            if(count($FreshDeskDbData) > 0 && empty($data['FreshdeskPassword'])){
                $setting = json_decode($FreshDeskDbData->Settings);
                $FreshdeskData['FreshdeskPassword'] = $setting->FreshdeskPassword;
            }else{
                $FreshdeskData['FreshdeskPassword'] = $data['FreshdeskPassword'];
            }
			
		  $data['Status'] = isset($data['Status'])?1:0;	
		  if($data['Status']==1){ //disable all other support subcategories
				$status =	array("Status"=>0);
				IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
		   }
			
			//$FreshDeskDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
			if(count($FreshDeskDbData)>0) {
				$SaveData = array("Settings"=>json_encode($FreshdeskData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
				IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$FreshDeskDbData->IntegrationConfigurationID))->update($SaveData);	
            } else {
				$SaveData = ["Settings"=>json_encode($FreshdeskData),
                            "IntegrationID"=>$data['secondcategoryid'],
                            "CompanyId"=>$companyID,
                            "created_by"=> User::get_user_full_name(),
                            "Status"=>$data['Status'],
                            'ParentIntegrationID'=>$data['firstcategoryid']
                ];
			 	IntegrationConfiguration::create($SaveData);
            }
			 return Response::json(array("status" => "success", "message" => "FreshDesk Settings Successfully Updated"));
			}
		}
		
		if($data['firstcategory']=='payment'){

			if($data['secondcategory']=='Authorize.net') {
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

				/*
				 if($data['Status']==1){ //disable all other payment subcategories
					$status =	array("Status"=>0);
					IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
		  		 }*/
				
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
					//'PaypalLogoUrl'	 => 'required',
				);
		
				$validator = Validator::make($data, $rules);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
				
				$data['Status'] 		= 	isset($data['Status'])?1:0;	
				$data['PaypalLive'] 	= 	isset($data['PaypalLive'])?1:0;	

				/*
				 if($data['Status']==1){ //disable all other payment subcategories
					$status =	array("Status"=>0);
					IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
		  		 }*/
				
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

			if($data['secondcategory']=='Stripe')
			{
				$rules = array(
					'SecretKey'	 => 'required',
					'PublishableKey'	 => 'required',
				);

				$validator = Validator::make($data, $rules);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$data['Status'] 		= 	isset($data['Status'])?1:0;
				/*
				if($data['Status']==1){ //disable all other payment subcategories
					$status =	array("Status"=>0);
					IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
				}*/

				$StripeData = array(
					"SecretKey"=>$data['SecretKey'],
					"PublishableKey"=>$data['PublishableKey']
				);

				$StripeDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($StripeDbData)>0)
				{
					$SaveData = array("Settings"=>json_encode($StripeData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$StripeDbData->IntegrationConfigurationID))->update($SaveData);

				}
				else
				{
					$SaveData = array("Settings"=>json_encode($StripeData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "Stripe Settings Successfully Updated"));
			}

			if($data['secondcategory']=='Stripe ACH')
			{
				$rules = array(
					'SecretKey'	 => 'required',
					'PublishableKey'	 => 'required',
				);

				$validator = Validator::make($data, $rules);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$data['Status'] 		= 	isset($data['Status'])?1:0;
				/*
				if($data['Status']==1){ //disable all other payment subcategories
					$status =	array("Status"=>0);
					IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
				}*/

				$StripeACHData = array(
					"SecretKey"=>$data['SecretKey'],
					"PublishableKey"=>$data['PublishableKey']
				);

				$StripeACHDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($StripeACHDbData)>0)
				{
					$SaveData = array("Settings"=>json_encode($StripeACHData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$StripeACHDbData->IntegrationConfigurationID))->update($SaveData);

				}
				else
				{
					$SaveData = array("Settings"=>json_encode($StripeACHData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "Stripe ACH Settings Successfully Updated"));
			}

			if($data['secondcategory']=='SagePay')
			{
				$rules = array(
					'ServiceKey'	 => 'required',
					'SoftwareVendorKey'	 => 'required',
				);

				$validator = Validator::make($data, $rules);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$data['Status'] 		= 	isset($data['Status'])?1:0;
				$data['isLive'] 		= 	isset($data['isLive'])?1:0;

				/*
				if($data['Status']==1){ //disable all other payment subcategories
					$status =	array("Status"=>0);
					IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
				}*/

				$SagePayData = array(
					"ServiceKey"=>$data['ServiceKey'],
					"SoftwareVendorKey"=>$data['SoftwareVendorKey'],
					"isLive"=>$data['isLive']
				);

				$SagePayDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($SagePayDbData)>0)
				{
					$SaveData = array("Settings"=>json_encode($SagePayData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$SagePayDbData->IntegrationConfigurationID))->update($SaveData);

				}
				else
				{
					$SaveData = array("Settings"=>json_encode($SagePayData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "SagePay Settings Successfully Updated"));
			}

			if($data['secondcategory']=='SagePay Direct Debit')
			{
				$rules = array(
					'SGDD_ServiceKey'	 => 'required',
					'SGDD_SoftwareVendorKey'	 => 'required',
					'SGDD_BatchUpload'=> 'required',
				);
				$messages = array(
				'SGDD_ServiceKey.required' =>'Service Key field is required',
				'SGDD_SoftwareVendorKey.required' =>'Software Vendor Key field is required',
				'SGDD_BatchUpload.required' =>'Batch Upload field is required',
				);

				$validator = Validator::make($data, $rules,$messages);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$data['Status'] 		= 	isset($data['SGDD_Status'])?1:0;
				//$data['isLive'] 		= 	isset($data['SGDD_isLive'])?1:0;

				/*
				if($data['Status']==1){ //disable all other payment subcategories
					$status =	array("Status"=>0);
					IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
				}*/

				$SagePayData = array(
					"ServiceKey"=>$data['SGDD_ServiceKey'],
					"SoftwareVendorKey"=>$data['SGDD_SoftwareVendorKey'],
					"BatchUpload"=>$data['SGDD_BatchUpload']
				);

				$SagePayDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($SagePayDbData)>0)
				{
					$SaveData = array("Settings"=>json_encode($SagePayData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$SagePayDbData->IntegrationConfigurationID))->update($SaveData);

				}
				else
				{
					$SaveData = array("Settings"=>json_encode($SagePayData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "SagePay Direct Debit Settings Successfully Updated"));
			}

			if($data['secondcategory']=='FideliPay')
			{
				$rules = array(
					'SourceKey'	 => 'required',
					'Pin'	 => 'required'
				);

				$validator = Validator::make($data, $rules);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$data['Status'] 		= 	isset($data['Status'])?1:0;

				/*
				if($data['Status']==1){ //disable all other payment subcategories
					$status =	array("Status"=>0);
					IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
				}*/

				$FideliPayData = array(
					"SourceKey"=>$data['SourceKey'],
					"Pin"=>$data['Pin']
				);

				$FideliPayDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($FideliPayDbData)>0)
				{
					$SaveData = array("Settings"=>json_encode($FideliPayData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$FideliPayDbData->IntegrationConfigurationID))->update($SaveData);

				}
				else
				{
					$SaveData = array("Settings"=>json_encode($FideliPayData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "FideliPay Settings Successfully Updated"));
			}

			if($data['secondcategory']=='MerchantWarrior')
			{
				$rules = array(
					'merchantUUID'	 => 'required',
					'apiKey'	 => 'required',
					'apiPassphrase'	 => 'required'
				);

				$validator = Validator::make($data, $rules);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$data['MerchantWarriorLive'] 		= 	isset($data['MerchantWarriorLive'])?1:0;
				$data['Status'] 		= 	isset($data['Status'])?1:0;

				$MerchantWarriorData = array(
					"merchantUUID"=>$data['merchantUUID'],
					"apiKey"=>$data['apiKey'],
					"apiPassphrase"=>$data['apiPassphrase'],
					"MerchantWarriorLive"=>$data['MerchantWarriorLive'],
				);

				$MerchantWarriorDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($MerchantWarriorDbData)>0)
				{
					$SaveData = array("Settings"=>json_encode($MerchantWarriorData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$MerchantWarriorDbData->IntegrationConfigurationID))->update($SaveData);
				}
				else
				{
					$SaveData = array("Settings"=>json_encode($MerchantWarriorData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "MerchantWarrior Settings Successfully Updated"));
			}

			if($data['secondcategory']=='PeleCard')
			{
				$PeleCardDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($PeleCardDbData)>0) { // update
					$rules = array(
						'terminalNumber' => 'required',
						'user' => 'required'
					);
				} else { // create
					$rules = array(
						'terminalNumber' => 'required',
						'user' => 'required',
						'password' => 'required'
					);
				}

				$validator = Validator::make($data, $rules);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$data['PeleCardLive'] 	= 	isset($data['PeleCardLive'])?1:0;
				$data['Status'] 		= 	isset($data['Status'])?1:0;

				$PeleCardData = array(
					"terminalNumber"	=>	$data['terminalNumber'],
					"user"				=>	$data['user'],
					"password"			=>	Crypt::encrypt($data['password']),
					"PeleCardLive"		=>	$data['PeleCardLive']
				);

				if(count($PeleCardDbData)>0 && empty($data['password'])) {
					$Settings = json_decode($PeleCardDbData->Settings);
					$PeleCardData['password'] = $Settings->password;
				}

				if(count($PeleCardDbData)>0) {
					$SaveData = array("Settings"=>json_encode($PeleCardData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$PeleCardDbData->IntegrationConfigurationID))->update($SaveData);
				} else {
					$SaveData = array("Settings"=>json_encode($PeleCardData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "PeleCard Settings Successfully Updated"));
			}
		}
		
		if($data['firstcategory']=='email') {
            if($data['secondcategory']=='Mandrill') {
                $MandrilDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				$rules = array(
					'MandrilSmtpServer'	 => 'required',
					'MandrilPort'	 => 'required',					
					'MandrilUserName'	 => 'required'
				);
                if(count($MandrilDbData)==0){
                    $rules['MandrilPassword'] = 'required';
                }
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
					"MandrilSSL"=>$data['MandrilSSL'],					
					);
                if(count($MandrilDbData)>0 && empty($data['MandrilPassword'])){
                    $setting = json_decode($MandrilDbData->Settings);
                    $MandrilData['MandrilPassword'] = $setting->MandrilPassword;
                }else{
                    $MandrilData['MandrilPassword'] = $data['MandrilPassword'];
                }
				 
				//$MandrilDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
				if(count($MandrilDbData)>0) {
						$SaveData = array("Settings"=>json_encode($MandrilData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$MandrilDbData->IntegrationConfigurationID))->update($SaveData);						
				} else {
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
					"SignatureVersion"=>$data['SignatureVersion'],
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
                $TrackingDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
				$rules = array(
					'EmailTrackingEmail'	 => 'required|email',
					//'EmailTrackingName'	 => 'required',					
					'EmailTrackingServer'	 => 'required',					
					//'EmailTrackingPassword'	 => 'required',
				);
                if(count($TrackingDbData)==0){
                    $rules['EmailTrackingPassword'] = 'required';
                }
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

                if(count($TrackingDbData)>0 && empty($data['EmailTrackingPassword'])){
                    $setting = json_decode($TrackingDbData->Settings);
                    $TrackingData['EmailTrackingPassword'] = $setting->EmailTrackingPassword;
                }else{
                    $TrackingData['EmailTrackingPassword'] = $data['EmailTrackingPassword'];
                }
				 
				//$TrackingDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
				if(count($TrackingDbData)>0) {
						$SaveData = array("Settings"=>json_encode($TrackingData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$TrackingDbData->IntegrationConfigurationID))->update($SaveData);						
				} else {
						$SaveData = array("Settings"=>json_encode($TrackingData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);						
						IntegrationConfiguration::create($SaveData);
				}
				 return Response::json(array("status" => "success", "message" => "Tracking Email Settings Successfully Updated"));
			}
		}

		if($data['firstcategory']=='calendar')
		{ 
			if($data['secondcategory']=='Exchange')
			{
                $outlookcalendarDBData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
				$rules = array(
					'OutlookCalendarEmail'	 => 'required|email',
					'OutlookCalendarServer'	 => 'required',					
					//'OutlookCalendarPassword'	 => 'required',
				);

				$messages = [
							 "OutlookCalendarEmail.required" => "The exchange email field is required",
							 "OutlookCalendarServer.required" => "The exchange server field is required",
							 //"OutlookCalendarPassword.required" => "The exchange password field is required"
							];
                if(count($outlookcalendarDBData)==0){
                    $rules['OutlookCalendarPassword'] = 'required';
                    $messages['OutlookCalendarPassword.required'] = 'The exchange password field is required';
                }
					
				$validator = Validator::make($data, $rules,$messages);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
				
				$data['Status'] 	= 	isset($data['Status'])?1:0;	
				
				$outlookcalendarData = array(
					"OutlookCalendarEmail"=>$data['OutlookCalendarEmail'],
					"OutlookCalendarServer"=>$data['OutlookCalendarServer'],
					);

                if(count($outlookcalendarDBData)>0 && empty($data['OutlookCalendarPassword'])){
                    $setting = json_decode($outlookcalendarDBData->Settings);
                    $outlookcalendarData['OutlookCalendarPassword'] = $setting->OutlookCalendarPassword;
                }else{
                    $outlookcalendarData['OutlookCalendarPassword'] = $data['OutlookCalendarPassword'];
                }
				 
				$outlookcalendarDBData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
				if(count($outlookcalendarDBData)>0) {
						$SaveData = array("Settings"=>json_encode($outlookcalendarData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
						IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$outlookcalendarDBData->IntegrationConfigurationID))->update($SaveData);						
				}else{	
						$SaveData = array("Settings"=>json_encode($outlookcalendarData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);						
						IntegrationConfiguration::create($SaveData);
				}
				 return Response::json(array("status" => "success", "message" => "Exchange Calendar Successfully Updated"));
            }
        }

		if($data['firstcategory']=='accounting')
		{
            if($data['secondcategory']=='QuickBook') {
                $QuickBookDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
				$rules = array(
					'QuickBookLoginID'	  => 'required',
					//'QuickBookPassqord'	  => 'required',
					//'OauthConsumerKey'	  => 'required',
					//'OauthConsumerSecret' => 'required',
					//'AppToken' => 'required',
				);

                if(count($QuickBookDbData)==0){
                    $rules['QuickBookPassqord'] = 'required';
                }

				$validator = Validator::make($data, $rules);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$QuickBook=CompanyConfiguration::get('QUICKBOOK');
				$QuickBook = json_decode($QuickBook,true);
				if(empty($QuickBook['OauthConsumerKey']) || empty($QuickBook['OauthConsumerSecret']) || empty($QuickBook['AppToken'])){
					return Response::json(array("status" => "failed", "message" => "Please Check QuickBook Configuration", "quickbookredirect" =>1));
				}

				$data['Status'] 				= 	isset($data['Status'])?1:0;
				$data['QuickBookSandbox'] 	= 	isset($QuickBook['Sandbox'])?1:0;
				$data['InvoiceAccount'] 	= 	isset($data['InvoiceAccount'])?$data['InvoiceAccount']:'';
				$data['PaymentAccount'] 	= 	isset($data['PaymentAccount'])?$data['PaymentAccount']:'';
				$data['OauthConsumerKey'] = $QuickBook['OauthConsumerKey'];
				$data['OauthConsumerSecret'] = $QuickBook['OauthConsumerSecret'];
				$data['AppToken'] = $QuickBook['AppToken'];

				/*
				$QuickBookData = array(
					"QuickBookLoginID"=>$data['QuickBookLoginID'],
					"QuickBookPassqord"=>$data['QuickBookPassqord'],
					"OauthConsumerKey"=>$data['OauthConsumerKey'],
					"OauthConsumerSecret"=>$data['OauthConsumerSecret'],
					"AppToken"=>$data['AppToken'],
					"QuickBookSandbox"=>$data['QuickBookSandbox'],
					"InvoiceAccount"=>$data['InvoiceAccount'],
					"PaymentAccount"=>$data['PaymentAccount'],
					"ExtraTax"=>$data['ExtraTax'],
					"GST"=>$data['GST'],
					"ItemTax"=>$data['ItemTax'],
					"TestTax"=>$data['TestTax']
				); */

				$QuickBookData = array();
				$QuickBookData = $data;
				unset($QuickBookData['firstcategory']);
				unset($QuickBookData['secondcategory']);
				unset($QuickBookData['firstcategoryid']);
				unset($QuickBookData['secondcategoryid']);
				unset($QuickBookData['Status']);
                if(count($QuickBookDbData)>0 && empty($QuickBookData['QuickBookPassqord'])){
                    $setting = json_decode($QuickBookDbData->Settings);
                    $QuickBookData['QuickBookPassqord'] = $setting->QuickBookPassqord;
                }

				//$QuickBookDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($QuickBookDbData)>0) {
					$SaveData = array("Settings"=>json_encode($QuickBookData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$QuickBookDbData->IntegrationConfigurationID))->update($SaveData);

				} else {
					$SaveData = array("Settings"=>json_encode($QuickBookData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "QuickBook Settings Successfully Updated", "quickbookredirect" =>1));

			}

			if($data['secondcategory']=='Quickbook Desktop') {

				$QuickBookDesktopDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();


				$data['Status'] 				= 	isset($data['Status'])?1:0;
				$data['QuickBookSandbox'] 	= 	isset($QuickBook['Sandbox'])?1:0;
				$data['InvoiceAccount'] 	= 	isset($data['InvoiceAccount'])?$data['InvoiceAccount']:'';
				$data['PaymentAccount'] 	= 	isset($data['PaymentAccount'])?$data['PaymentAccount']:'';

				$QuickBookData = array();
				$QuickBookData = $data;
				unset($QuickBookData['firstcategory']);
				unset($QuickBookData['secondcategory']);
				unset($QuickBookData['firstcategoryid']);
				unset($QuickBookData['secondcategoryid']);
				unset($QuickBookData['Status']);

				//$QuickBookDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();

				if(count($QuickBookDesktopDbData)>0) {
					$SaveData = array("Settings"=>json_encode($QuickBookData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$QuickBookDesktopDbData->IntegrationConfigurationID))->update($SaveData);

				} else {
					$SaveData = array("Settings"=>json_encode($QuickBookData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "QuickBook Desktop Settings Successfully Updated", "quickbookredirect" =>1));

			}

			if($data['secondcategory']=='Xero') {
				$XeroDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
				$rules = array(
					'ConsumerKey'	  => 'required',
					'ConsumerSecret' => 'required',
					//'AppToken' => 'required',
				);

				$validator = Validator::make($data, $rules);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$data['Status'] 				= 	isset($data['Status'])?1:0;
				$data['InvoiceAccount'] 	= 	isset($data['InvoiceAccount'])?$data['InvoiceAccount']:'';
				$data['PaymentAccount'] 	= 	isset($data['PaymentAccount'])?$data['PaymentAccount']:'';

				$fullPath = '';

				if (Input::hasFile('XeroFile')) {
					$upload_path = CompanyConfiguration::get('UPLOAD_PATH');
					$excel = Input::file('XeroFile');
					$ext = $excel->getClientOriginalExtension();
					if (in_array(strtolower($ext), array("pem"))) {
						$file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
						$amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['XERO_UPLOAD']);
						$destinationPath = $upload_path . '/' . $amazonPath;
						$excel->move($destinationPath, $file_name);
						if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
							return Response::json(array("status" => "failed", "message" => "Failed to upload."));
						}
						$fullPath = $amazonPath . $file_name;
						unset($data['XeroFile']);
					} else {
						return Response::json(array("status" => "failed", "message" => "Please select pem file."));
					}
				} else {
					unset($data['XeroFile']);
				}
				if(empty($fullPath)){
					if(count($XeroDbData)>0 && empty($XeroDbData['XeroFilePath'])){
						$setting = json_decode($XeroDbData->Settings);
						$fullPath = $setting->XeroFilePath;
					}else{
						return Response::json(array("status" => "failed", "message" => "Please select pem file."));
					}
				}

				$data['XeroFilePath'] = $fullPath;
				$XeroData = array();
				$XeroData = $data;
				unset($XeroData['firstcategory']);
				unset($XeroData['secondcategory']);
				unset($XeroData['firstcategoryid']);
				unset($XeroData['secondcategoryid']);
				unset($XeroData['Status']);

				if(count($XeroDbData)>0) {
					$SaveData = array("Settings"=>json_encode($XeroData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$XeroDbData->IntegrationConfigurationID))->update($SaveData);

				} else {
					$SaveData = array("Settings"=>json_encode($XeroData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
					IntegrationConfiguration::create($SaveData);
				}
				return Response::json(array("status" => "success", "message" => "Xero Settings Successfully Updated"));

			}
		}
	}
	
	function CheckImapConnection(){
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