<?php

class CompaniesController extends \BaseController {


	/**
	 * Show the form for editing the specified resource.
	 * GET /companies/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit()
	{
        $LicenceApiResponse=Company::ValidateLicenceKey();
        $company_id = User::get_companyID();
        $company = Company::find($company_id);
        $countries = Country::getCountryDropdownList();
        $currencies = Currency::getCurrencyDropdownIDList();
        $timezones = TimeZone::getTimeZoneDropdownList();
        $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();

        if($company->CustomerAccountPrefix == ''){
            $LastPrefixNo = DB::table('tblGlobalSetting')->where(["Key" => 'Default_Customer_Trunk_Prefix'])->first();
            $company->CustomerAccountPrefix = $LastPrefixNo->Value;
        }
        $BillingTimezone =  CompanySetting::getKeyVal('BillingTimezone');
        $CDRType = CompanySetting::getKeyVal('CDRType');
        $RoundChargesAmount = CompanySetting::getKeyVal('RoundChargesAmount');
        $PaymentDueInDays = CompanySetting::getKeyVal('PaymentDueInDays');
        $BillingCycleType = CompanySetting::getKeyVal('BillingCycleType');
        $BillingCycleValue = CompanySetting::getKeyVal('BillingCycleValue');
        $InvoiceTemplateID = CompanySetting::getKeyVal('InvoiceTemplateID');
        $SalesTimeZone = CompanySetting::getKeyVal('SalesTimeZone');
        $UseInBilling = CompanySetting::getKeyVal('UseInBilling');
        $RateGenerationEmail = CompanySetting::getKeyVal('RateGenerationEmail')== 'Invalid Key'?'':CompanySetting::getKeyVal('RateGenerationEmail');
        $InvoiceGenerationEmail = CompanySetting::getKeyVal('InvoiceGenerationEmail')== 'Invalid Key'?'':CompanySetting::getKeyVal('InvoiceGenerationEmail');
        $DefaultDashboard = CompanySetting::getKeyVal('DefaultDashboard')=='Invalid Key'?'':CompanySetting::getKeyVal('DefaultDashboard');
        $PincodeWidget = CompanySetting::getKeyVal('PincodeWidget')=='Invalid Key'?'':CompanySetting::getKeyVal('PincodeWidget');
        $DefaultTextRate = CompanySetting::getKeyVal('DefaultTextRate')=='Invalid Key'?'':CompanySetting::getKeyVal('DefaultTextRate');
        $LastPrefixNo = LastPrefixNo::getLastPrefix();
        $dashboardlist = getDashBoards(); //Default Dashbaord functionality Added by Abubakar
        $taxrates = TaxRate::getTaxRateDropdownIDList();//Default TaxRate functionality Added by Abubakar
        if(isset($taxrates[""])){unset($taxrates[""]);}
        return View::make('companies.edit')->with(compact('company', 'countries','currencies','timezones','InvoiceTemplates','BillingTimezone','CDRType','RoundChargesAmount','PaymentDueInDays','BillingCycleType','BillingCycleValue','InvoiceTemplateID','RateGenerationEmail','InvoiceGenerationEmail','LastPrefixNo','LicenceApiResponse','SalesTimeZone','UseInBilling','dashboardlist','DefaultDashboard','PincodeWidget','taxrates','DefaultTextRate'));

    }

	/**
	 * Update the specified resource in storage.
	 * PUT /companies/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update()
	{
        $data = Input::all();
        $companyID = User::get_companyID();
        $company = Company::find($companyID);
        $data['UseInBilling'] = isset($data['UseInBilling']) ? 1 : 0;
        $data['PincodeWidget'] = isset($data['PincodeWidget']) ? 1 : 0;
        $data['updated_by'] = User::get_user_full_name();
        $rules = array(
            'CompanyName' => 'required|min:3|unique:tblCompany,CompanyName,'.$companyID.',CompanyID',
            //'Port' => 'required|numeric',
            'BillingTimezone' => 'required',
            'CurrencyId' => 'required'
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        CompanySetting::setKeyVal('UseInBilling',$data['UseInBilling']);
        unset($data['UseInBilling']);
        CompanySetting::setKeyVal('SalesTimeZone',$data['SalesTimeZone']);
        unset($data['SalesTimeZone']);
        CompanySetting::setKeyVal('BillingTimezone',$data['BillingTimezone']);
        unset($data['BillingTimezone']);
        CompanySetting::setKeyVal('CDRType',$data['CDRType']);
        unset($data['CDRType']);
        CompanySetting::setKeyVal('RoundChargesAmount',$data['RoundChargesAmount']);
        unset($data['RoundChargesAmount']);
        CompanySetting::setKeyVal('PaymentDueInDays',$data['PaymentDueInDays']);
        unset($data['PaymentDueInDays']);
        CompanySetting::setKeyVal('BillingCycleType',$data['BillingCycleType']);
        unset($data['BillingCycleType']);
        if(isset($data['BillingCycleValue'])) {
            CompanySetting::setKeyVal('BillingCycleValue', $data['BillingCycleValue']);
            unset($data['BillingCycleValue']);
        }
        CompanySetting::setKeyVal('InvoiceTemplateID',$data['InvoiceTemplateID']);
        unset($data['InvoiceTemplateID']);
        CompanySetting::setKeyVal('RateGenerationEmail',$data['RateGenerationEmail']);
        unset($data['RateGenerationEmail']);
        CompanySetting::setKeyVal('InvoiceGenerationEmail',$data['InvoiceGenerationEmail']);
        unset($data['InvoiceGenerationEmail']);
        CompanySetting::setKeyVal('DefaultDashboard',$data['DefaultDashboard']);//Added by Abubakar
        unset($data['DefaultDashboard']);
        CompanySetting::setKeyVal('PincodeWidget',$data['PincodeWidget']);//Added by Girish
        unset($data['PincodeWidget']);
        if(!isset($data['DefaultTextRate'])) {
            $data['DefaultTextRate'] = '';
        }
		if(isset($data['DefaultTextRate']) && is_array($data['DefaultTextRate'])){
			CompanySetting::setKeyVal('DefaultTextRate', implode(',', $data['DefaultTextRate']));//Added by Abubakar
		}
		unset($data['DefaultTextRate']);

        LastPrefixNo::updateLastPrefixNo($data['LastPrefixNo']);
        unset($data['LastPrefixNo']);

        if(!empty($data['CurrencyId'])){
            //add default currency value in exchange rate
            $CurrencyCon = array();
            $CurrencyCon['CurrencyID'] = $data['CurrencyId'];
            $CurrencyCon['Value'] = '1.000000';
            $CurrencyCon['EffectiveDate'] = date('Y-m-d H:i:s');
            $CurrencyCon['CompanyID'] = $companyID;
            $CurrencyConversion = CurrencyConversion::select('Value','EffectiveDate')->where(array('CompanyId' => $companyID, 'CurrencyID' => $data['CurrencyId']))->first();
            if(count($CurrencyConversion)>0){
                $cval = $CurrencyConversion->Value;
                if($cval!='1.000000'){
                    CurrencyConversion::where(array('CompanyId' => $companyID, 'CurrencyID' => $data['CurrencyId']))->update($CurrencyCon);
                }
            }else{
                CurrencyConversion::create($CurrencyCon);
            }
        }

        if ($company->update($data)) {
            return Response::json(array("status" => "success", "message" => "Company Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Company."));
        }

    }
	
	function ValidateSmtp(){
		$data 				= 		Input::all();
        $companyID 			= 		User::get_companyID();
        $company 			=		Company::find($companyID);
		
		 $rules = array(
            'SMTPServer' => 'required',
            'Port' => 'required|numeric',
            'EmailFrom' => 'required',
            'SMTPUsername' => 'required',
			'SMTPPassword' => 'required',
			'IsSSL' => 'required',
			"Email" =>'required', 
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
		
		$checkValidation 	= 		ValidateSmtp($data['SMTPServer'],$data['Port'],$data['EmailFrom'],$data['CompanyName'],$data['IsSSL']==1?1:0,$data['SMTPUsername'],$data['SMTPPassword'],$data['EmailFrom'],$data['CompanyName'],$data['Email']);
		
		$ResponseArray= array("response"=>$checkValidation,"status"=>"success");
		return json_encode($ResponseArray);
		
	}


}