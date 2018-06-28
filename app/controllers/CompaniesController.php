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
        $invoicePdfSend = CompanySetting::getKeyVal('invoicePdfSend');
        $AccountVerification = CompanySetting::getKeyVal('AccountVerification');
        $DefaultDashboard = CompanySetting::getKeyVal('DefaultDashboard') == 'Invalid Key' ? '' : CompanySetting::getKeyVal('DefaultDashboard');
        //$PincodeWidget = CompanySetting::getKeyVal('PincodeWidget') == 'Invalid Key' ? '' : CompanySetting::getKeyVal('PincodeWidget');
        $LastPrefixNo = LastPrefixNo::getLastPrefix();
        $dashboardlist = getDashBoards(); //Default Dashbaord functionality Added by Abubakar

        $COMPANY_SSH_VISIBLE = CompanyConfiguration::get('COMPANY_SSH_VISIBLE');
		$SSHCONF = CompanyConfiguration::get('SSH');
        if(!empty($SSHCONF)) {
            $SSHCONF = (array) json_decode($SSHCONF);
            $SSH['host']     = isset($SSHCONF['host']) ? $SSHCONF['host'] : '';
            $SSH['username'] = isset($SSHCONF['username']) ? $SSHCONF['username'] : '';
            $SSH['password'] = isset($SSHCONF['password']) ? $SSHCONF['password'] : '';
        } else {
            $SSH['host']     = '';
            $SSH['username'] = '';
            $SSH['password'] = '';
        }

        $DigitalSignature = CompanySetting::getKeyVal('DigitalSignature', $company_id);
        $UseDigitalSignature = CompanySetting::getKeyVal('UseDigitalSignature', $company_id);
        if($DigitalSignature=="Invalid Key"){
            $DigitalSignature=array();
            $DigitalSignature['positionLeft']=0;
            $DigitalSignature['positionTop']=0;
        }else{
            $DigitalSignature=json_decode($DigitalSignature, true);
        }

        return View::make('companies.edit')->with(compact('company', 'countries', 'currencies', 'timezones', 'InvoiceTemplates', 'LastPrefixNo', 'LicenceApiResponse', 'UseInBilling', 'dashboardlist', 'DefaultDashboard','RoundChargesAmount','RateSheetTemplate','RateSheetTemplateFile','AccountVerification','SSH','COMPANY_SSH_VISIBLE', 'DigitalSignature', 'UseDigitalSignature', 'invoicePdfSend'));

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
        $data['invoicePdfSend'] = isset($data['invoicePdfSend']) ? 1 : 0;
        $data['AccountVerification'] = isset($data['AccountVerification']) ? CompanySetting::ACCOUT_VARIFICATION_ON : CompanySetting::ACCOUT_VARIFICATION_OFF;
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
        $rules["signatureImage"]='mimes:jpeg,jpg,png,gif';
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if(empty($data['SMTPPassword'])){
            unset($data['SMTPPassword']);
        }
        $upload_path = CompanyConfiguration::get('UPLOAD_PATH')."/";
        $arrSignatureCertFile=CompanySetting::getKeyVal('DigitalSignature');
        if($arrSignatureCertFile=="Invalid Key"){
            $arrSignatureCertFile=array();
        }else{
            $arrSignatureCertFile=json_decode($arrSignatureCertFile, true);
        }

        if (Input::hasFile('signatureImage')) {
            $signatureImage = Input::file('signatureImage');
            $file_name = 'signatureImage.' . $signatureImage->getClientOriginalExtension();
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['DIGITAL_SIGNATURE_KEY'], '', $companyID, true);
            $destinationPath = $upload_path . $amazonPath;
            $signatureImage->move($destinationPath, $file_name);
            $arrSignatureCertFile['image'] = $file_name;
        }else{
			if(!array_key_exists("image",$arrSignatureCertFile)){
				$arrSignatureCertFile['image'] = '';
			}
        }

        $arrSignatureCertFile['positionLeft'] = !empty($data['signatureCertpPositionLeft']) ? $data['signatureCertpPositionLeft'] : 0;
        $arrSignatureCertFile['positionTop'] = !empty($data['signatureCertpPositionTop']) ? $data['signatureCertpPositionTop'] : 0;
        $arrSignatureCertFile = json_encode($arrSignatureCertFile);
        CompanySetting::setKeyVal('DigitalSignature',$arrSignatureCertFile, $companyID);
        CompanySetting::setKeyVal('UseDigitalSignature',isset($data["UseDigitalSignature"])?$data["UseDigitalSignature"]:0, $companyID);
        $data = cleanarray($data,['signatureCertFile','signatureCert','signatureImage','signatureCertPassword','signatureCertpPositionLeft','signatureCertpPositionTop','UseDigitalSignature']);

        if (Input::hasFile('RateSheetTemplateFile')) {
            $excel = Input::file('RateSheetTemplateFile');
            $ext = $excel->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("xls", "xlsx"))) {
                $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['RATESHEET_TEMPLATE']);
                $destinationPath = $upload_path . $amazonPath;
                $excel->move($destinationPath, $file_name);
                if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name;
                $RateSheetTemplateData['Excel'] = $fullPath;
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
        CompanySetting::setKeyVal('invoicePdfSend',$data['invoicePdfSend']);
        unset($data['invoicePdfSend']);
        CompanySetting::setKeyVal('AccountVerification',$data['AccountVerification']);
        unset($data['AccountVerification']);

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

        $COMPANY_SSH_VISIBLE = CompanyConfiguration::get('COMPANY_SSH_VISIBLE');
        if(isset($COMPANY_SSH_VISIBLE) && $COMPANY_SSH_VISIBLE == 1) {
            $SSHCONF = CompanyConfiguration::get('SSH');
			if(!empty($SSHCONF)) {
				$SSHCONF = (array) json_decode($SSHCONF);
                $SSH['host'] = isset($SSHCONF['host']) ? $SSHCONF['host'] : '';
                $SSH['username'] = isset($SSHCONF['username']) ? $SSHCONF['username'] : '';
                $SSH['password'] = isset($SSHCONF['password']) ? $SSHCONF['password'] : '';

                if(isset($data['SSH']['host']) && !empty($data['SSH']['host'])) {
                    $SSH['host'] = $data['SSH']['host'];
                }
                if(isset($data['SSH']['username']) && !empty($data['SSH']['username'])) {
                    $SSH['username'] = $data['SSH']['username'];
                }
                if(isset($data['SSH']['password']) && !empty($data['SSH']['password'])) {
                    $SSH['password'] = $data['SSH']['password'];
                }
                unset($data['SSH']);
                $SSH = json_encode($SSH);
                CompanyConfiguration::where('Key', 'SSH')->update(['Value'=>$SSH]);
                CompanyConfiguration::updateCompanyConfiguration($companyID);
            } else {
                $SSH['host'] = '';
                $SSH['username'] = '';
                $SSH['password'] = '';
            }
        }
		
        if ($company->update($data)) {

            if(CompanySetting::getKeyVal('UseDigitalSignature', $companyID)){
                $signaturePath =$upload_path . AmazonS3::generate_upload_path(AmazonS3::$dir['DIGITAL_SIGNATURE_KEY'], '', $companyID, true);
                $SERVER_NAME=preg_replace('/[^A-Za-z0-9\-]/', '', $_SERVER["SERVER_NAME"]);
                RemoteSSH::run([
                        'openssl req -new -newkey rsa:2048 -nodes -out '.$signaturePath.'digitalsignature.csr -keyout '.$signaturePath.'digitalsignature.key -subj "/C=GB/ST=London/L=London/O='.$_SERVER["SERVER_NAME"].'/OU='.$SERVER_NAME.'/CN='.$_SERVER["SERVER_NAME"].'"',
                        'openssl x509 -signkey '.$signaturePath.'digitalsignature.key -in '.$signaturePath.'digitalsignature.csr -req -days 365 -out '.$signaturePath.'digitalsignature.crt',
                        'openssl pkcs12 -inkey '.$signaturePath.'digitalsignature.key -in '.$signaturePath.'digitalsignature.crt -export -out '.$signaturePath.'digitalsignature.pfx -password pass:Welcome100'
                    ]);
            }

            return Response::json(array("status" => "success", "message" => "Company Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Company."));
        }

    }

    public function DownloadRateSheetTemplate(){
        $fileTemplate =  CompanySetting::getKeyVal('RateSheetTemplate');
        if($fileTemplate != 'Invalid Key') {
            $fileTemplate = json_decode($fileTemplate);
            $FilePath = $fileTemplate->Excel;
            $FilePath =  AmazonS3::preSignedUrl($FilePath);
            if(file_exists($FilePath)){
                download_file($FilePath);
            }elseif(is_amazon() == true){
                header('Location: '.$FilePath);
            }
            exit;
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

    public function DownloadDigitalSignature($file){
        $companyID = User::get_companyID();
        $upload_path = CompanyConfiguration::get('UPLOAD_PATH')."/";
        $signaturePath =$upload_path . AmazonS3::generate_upload_path(AmazonS3::$dir['DIGITAL_SIGNATURE_KEY'], '', $companyID, true);

        $DigitalSignature=CompanySetting::getKeyVal('DigitalSignature', $companyID);
        $DigitalSignature=json_decode($DigitalSignature, true);
        if(isset($DigitalSignature[$file])){
            $filePath = $signaturePath . $DigitalSignature[$file];
            download_file($filePath);
        }
    }
}