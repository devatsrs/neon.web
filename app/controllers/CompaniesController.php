<?php

class CompaniesController extends \BaseController {


	/**
	 * Show the form for editing the specified resource.
	 * GET /companies/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit(){
        $LicenceApiResponse = Company::ValidateLicenceKey();
        $company_id = User::get_companyID();
        $company = Company::find($company_id);
        $countries = Country::getCountryDropdownList();
        $currencies = Currency::getCurrencyDropdownIDList();
        $timezones = TimeZone::getTimeZoneDropdownList();
        $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();

        if ($company->CustomerAccountPrefix == '') {
            $LastPrefixNo = DB::table('tblGlobalSetting')->where(["Key" => 'Default_Customer_Trunk_Prefix'])->first();
            $company->CustomerAccountPrefix = $LastPrefixNo->Value;
        }
        $RoundChargesAmount = CompanySetting::getKeyVal('RoundChargesAmount');
        $UseInBilling = CompanySetting::getKeyVal('UseInBilling');
        $DefaultDashboard = CompanySetting::getKeyVal('DefaultDashboard') == 'Invalid Key' ? '' : CompanySetting::getKeyVal('DefaultDashboard');
        //$PincodeWidget = CompanySetting::getKeyVal('PincodeWidget') == 'Invalid Key' ? '' : CompanySetting::getKeyVal('PincodeWidget');
        $LastPrefixNo = LastPrefixNo::getLastPrefix();
        $dashboardlist = getDashBoards(); //Default Dashbaord functionality Added by Abubakar
        return View::make('companies.edit')->with(compact('company', 'countries', 'currencies', 'timezones', 'InvoiceTemplates', 'LastPrefixNo', 'LicenceApiResponse', 'UseInBilling', 'dashboardlist', 'DefaultDashboard','RoundChargesAmount'));

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
        //$data['PincodeWidget'] = isset($data['PincodeWidget']) ? 1 : 0;
        $data['updated_by'] = User::get_user_full_name();
        $rules = array(
            'CompanyName' => 'required|min:3|unique:tblCompany,CompanyName,'.$companyID.',CompanyID',
            //'Port' => 'required|numeric',
            'CurrencyId' => 'required'
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if(empty($data['SMTPPassword'])){
            unset($data['SMTPPassword']);
        }
        CompanySetting::setKeyVal('UseInBilling',$data['UseInBilling']);
        unset($data['UseInBilling']);
        CompanySetting::setKeyVal('DefaultDashboard',$data['DefaultDashboard']);//Added by Abubakar
        unset($data['DefaultDashboard']);
        CompanySetting::setKeyVal('RoundChargesAmount',$data['RoundChargesAmount']);
        unset($data['RoundChargesAmount']);
        //CompanySetting::setKeyVal('PincodeWidget',$data['PincodeWidget']);//Added by Girish
        //unset($data['PincodeWidget']);
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
        if(empty($data['SMTPPassword'])){
            $data['SMTPPassword'] = $company->SMTPPassword;
        }
		
		 $rules = array(
            'SMTPServer' => 'required',
            'Port' => 'required|numeric',
            'EmailFrom' => 'required',
            'SMTPUsername' => 'required',
			'SMTPPassword' => 'required',
			'IsSSL' => 'required',
			"SampleEmail" =>'required', 
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
		
		$checkValidation 	= 		ValidateSmtp($data['SMTPServer'],$data['Port'],$data['EmailFrom'],$data['IsSSL']==1?1:0,$data['SMTPUsername'],$data['SMTPPassword'],$data['EmailFrom'],$data['SampleEmail']);
		
		$ResponseArray= array("response"=>$checkValidation,"status"=>"success");
		return json_encode($ResponseArray);
		
	}


}