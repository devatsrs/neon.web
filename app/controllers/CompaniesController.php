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
        $RateSheetTemplate = CompanySetting::getKeyVal('RateSheetTemplate') != 'Invalid Key' ? (array) json_decode(CompanySetting::getKeyVal('RateSheetTemplate')) : '';
        $RateSheetTemplateFile = '';
        if($RateSheetTemplate != '') {
            $RateSheetTemplateFile = $RateSheetTemplate['Excel'];
            unset($RateSheetTemplate['Excel']);
        } else {
            $RateSheetTemplate['HeaderSize'] = "";
            $RateSheetTemplate['FooterSize'] = "";
        }

        $UseInBilling = CompanySetting::getKeyVal('UseInBilling');
        $DefaultDashboard = CompanySetting::getKeyVal('DefaultDashboard') == 'Invalid Key' ? '' : CompanySetting::getKeyVal('DefaultDashboard');
        //$PincodeWidget = CompanySetting::getKeyVal('PincodeWidget') == 'Invalid Key' ? '' : CompanySetting::getKeyVal('PincodeWidget');
        $LastPrefixNo = LastPrefixNo::getLastPrefix();
        $dashboardlist = getDashBoards(); //Default Dashbaord functionality Added by Abubakar
        return View::make('companies.edit')->with(compact('company', 'countries', 'currencies', 'timezones', 'InvoiceTemplates', 'LastPrefixNo', 'LicenceApiResponse', 'UseInBilling', 'dashboardlist', 'DefaultDashboard','RoundChargesAmount','RateSheetTemplate','RateSheetTemplateFile'));

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
//        echo "<pre>";print_r($data);exit();
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

        if (Input::hasFile('RateSheetTemplateFile')) {
            $rules['RateSheetTemplate.HeaderSize'] = 'required|numeric';
            $rules['RateSheetTemplate.FooterSize'] = 'required|numeric';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if(empty($data['SMTPPassword'])){
            unset($data['SMTPPassword']);
        }

        if (Input::hasFile('RateSheetTemplateFile')) {
            $upload_path = CompanyConfiguration::get('TEMP_PATH');
            $excel = Input::file('RateSheetTemplateFile');
            $ext = $excel->getClientOriginalExtension();
            if (in_array($ext, array("xls", "xlsx"))) {
                $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                $excel->move($upload_path, $file_name);
                $file_name = $upload_path . '/' . $file_name;
                $RateSheetTemplateData['Excel'] = $file_name;
                $RateSheetTemplateData['HeaderSize'] = $data['RateSheetTemplate']['HeaderSize'];
                $RateSheetTemplateData['FooterSize'] = $data['RateSheetTemplate']['FooterSize'];
                $RateSheetTemplateData = json_encode($RateSheetTemplateData);
                CompanySetting::setKeyVal('RateSheetTemplate',$RateSheetTemplateData);
                unset($data['RateSheetTemplate']);
                unset($data['RateSheetTemplateFile']);
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
            }
        } else {
            unset($data['RateSheetTemplate']);
            unset($data['RateSheetTemplateFile']);
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
        //If company timezone changes
        if($company->TimeZone != $data["Timezone"] ){
            CronJob::updateAllCronJobNextRunTime($companyID);
        }
		
		$data['IsSSL'] = isset($data['IsSSL'])?1:0;
		
        if ($company->update($data)) {
            return Response::json(array("status" => "success", "message" => "Company Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Company."));
        }

    }

    public function DownloadRateSheetTemplate(){
        $fileTemplate =  CompanySetting::getKeyVal('RateSheetTemplate');
        if($fileTemplate != 'Invalid Key') {
            $fileTemplate = json_decode($fileTemplate);
            $filePath = $fileTemplate->Excel;
            download_file($filePath);
        }
    }

    public function DownloadRateSheetTemplateDefault(){
        $filePath = public_path() .'/uploads/sample_upload/RateSheetTemplateDefault.xls';
        download_file($filePath);
    }

    function ValidateSmtp(){
		$data 				= 		Input::all();
        $companyID 			= 		User::get_companyID();
        $company 			=		Company::find($companyID);
        if(empty($data['SMTPPassword'])){
            $data['SMTPPassword'] = $company->SMTPPassword;
        }
		if($data['IsSSL']=='true'){
			$ssl = 1;
		}else{
			$ssl = 0;
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
		
		$checkValidation 	= 		ValidateSmtp($data['SMTPServer'],$data['Port'],$data['EmailFrom'],$ssl,$data['SMTPUsername'],$data['SMTPPassword'],$data['EmailFrom'],$data['SampleEmail']);
		
		$ResponseArray= array("response"=>$checkValidation,"status"=>"success");
		return json_encode($ResponseArray);
		
	}


}