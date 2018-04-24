<?php

class AutoRateImportController extends \BaseController {

	public function __construct(){

	 }
	/* AutoInbox Setting  */
	public function index()
	{
		$companyID = User::get_companyID();
		$autoimportSetting = AutoImportInboxSetting::getAutoImportSetting($companyID);
		$autoimportSetting['copyNotification'] = $autoimportSetting[0]->SendCopyToAccount == 'Y' ? 'checked' : '';
		$autoimportSetting['encryption'] = $autoimportSetting[0]->encryption == 'ssl' ? 'checked' : '';
		return View::make('autoimport.auto_import_inbox_setting', compact('autoimportSetting'));

	}
	public function inboxSettingStoreAndUpdate()
	{
		$data = Input::all();
		$data['SendCopyToAccount'] = isset($data['SendCopyToAccount'])?'Y':'N';
		$data['encryption'] = isset($data['encryption']) ? 'ssl' : 'tls' ;
		$data['validate_cert'] = 'false';
		$rules = array(
			'port' => 'required',
			'host'=>'required',
			'encryption'=>'required',
			'validate_cert'=>'required',
			'username'=>'required',
			'password'=>'required'
		);
		$message = ['port.required'=>'Port field is required',
			'host.required'=>'host field is required',
			'encryption.required'=>'encryption field is required',
			'validate_cert.required'=>'validate_cert field is required',
			'username.required'=>'Username field is required',
			'password.required'=>'Password field is required'
		];

		$validator = Validator::make($data, $rules, $message);
		if ($validator->fails()) {
			return json_validator_response($validator);
		}

		if (!empty($data["CompanyID"])){

			$companyID = User::get_companyID();
			if (AutoImportInboxSetting::updateInboxImportSetting($companyID,$data)) {
				return Response::json(array("status" => "success", "message" => "Import Setting Update Successfully"));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Updating AutoImport Inbox Setting."));
			}

		}else{

			$companyID = User::get_companyID();
			$data["CompanyID"] = $companyID ;
			if (AutoImportInboxSetting::insert($data)) {
			return Response::json(array("status" => "success", "message" => "Import Setting Update Successfully"));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Insert AutoImport Setting."));
			}

		}


	}




	/* Search Grid for Setting (RateTable and Account )*/
	public function ajax_datagrid($type)
	{
		$CompanyID = User::get_companyID();
		$data = Input::all();

		if ($data["SettingType"] == 1) {  // AccountSetting


			$AutoImportSetting = AutoImportSetting::
			Join('tblTrunk', 'tblTrunk.TrunkID', '=', 'tblAutoImportSetting.TrunkID')
				->Join('tblAccount', 'tblAccount.AccountID', '=', 'tblAutoImportSetting.TypePKID')
				->Join('tblFileUploadTemplate', 'tblFileUploadTemplate.FileUploadTemplateID', '=', 'tblAutoImportSetting.ImportFileTempleteID');
				if($type=='csv' || $type=='xlsx') {
					$AutoImportSetting->select('tblAccount.AccountName', 'tblTrunk.Trunk as trunkName', 'tblFileUploadTemplate.Title', 'Subject', 'SendorEmail', 'FileName');
				}else{
					$AutoImportSetting->select('tblAccount.AccountName', 'tblTrunk.Trunk as trunkName', 'tblFileUploadTemplate.Title', 'Subject', 'SendorEmail', 'tblAccount.AccountID', 'tblTrunk.TrunkID', 'tblFileUploadTemplate.FileUploadTemplateID', 'FileName', 'AutoImportSettingID');
				}
				$AutoImportSetting->where("tblAutoImportSetting.CompanyId", $CompanyID)->where('tblAutoImportSetting.Type', '=', $data["SettingType"]);
			if ($data['TrunkID']) {
				$AutoImportSetting->where('tblAutoImportSetting.TrunkID', $data['TrunkID']);
			}

		}else { // RateTable Setting = 2

			$AutoImportSetting = AutoImportSetting::
				Join('tblRateTable', 'tblRateTable.RateTableId', '=', 'tblAutoImportSetting.TypePKID')
				->Join('tblFileUploadTemplate', 'tblFileUploadTemplate.FileUploadTemplateID', '=', 'tblAutoImportSetting.ImportFileTempleteID');
				if($type=='csv' || $type=='xlsx') {
					$AutoImportSetting->select('tblRateTable.RateTableName', 'tblFileUploadTemplate.Title', 'Subject', 'SendorEmail', 'FileName');
				}else{
					$AutoImportSetting->select('tblRateTable.RateTableName', 'tblFileUploadTemplate.Title', 'Subject', 'SendorEmail','tblRateTable.RateTableId', 'tblFileUploadTemplate.FileUploadTemplateID', 'FileName','AutoImportSettingID');
				}
				$AutoImportSetting->where("tblAutoImportSetting.CompanyId", $CompanyID)->where('tblAutoImportSetting.Type', '=', $data["SettingType"]);

		}

		if($data['TypePKID']){
			$AutoImportSetting->where('tblAutoImportSetting.TypePKID',$data['TypePKID']);
		}
		if($data['Search']!=''){
			$AutoImportSetting->WhereRaw('tblAutoImportSetting.Subject like "%'.$data['Search'].'%"');
		}

		/* Use for Export */
		if($type=='csv' || $type=='xlsx'){
			$AutoImportSetting = $AutoImportSetting->get();
			$excel_data = json_decode(json_encode($AutoImportSetting),true);


			if($type=='csv'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AutoImportSetting.csv';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_csv($excel_data);
			}elseif($type=='xlsx'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AutoImportSetting.xls';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_excel($excel_data);
			}
		}
		/* Use for Export */

		return Datatables::of($AutoImportSetting)->make();
	}



	/* Use in Account Setting page*/
	public function accountSetting() {
		$companyID = User::get_companyID();
		$trunks = Trunk::getTrunkDropdownIDList();
		$trunk_keys = getDefaultTrunk($trunks);
		$RateGenerators = RateGenerator::where(["Status" => 1, "CompanyID" => $companyID])->lists("RateGeneratorName", "RateGeneratorId");
		$all_accounts = Account::getAccountIDList(['IsVendor'=>1]);
		$uploadtemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_VENDOR_RATE));
		return View::make('autoimport.index', compact('trunks','RateGenerators','all_accounts','trunk_keys','uploadtemplate'));
	}

	public function accountSettingStore(){

		$data = Input::all();
		$rules = array(
			'TypePKID' => 'required',
			'TrunkID'=>'required',
			'ImportFileTempleteID'=>'required',
			'Subject'=>'required',
			'FileName'=>'required',
			'SendorEmail'=>'required'
		);
		$message = ['TypePKID.required'=>'Vendor field is required',
			'TrunkID.required'=>'Trunk field is required',
			'ImportFileTempleteID.required'=>'Upload Template field is required',
			'Subject.required'=>'Subject field is required',
			'FileName.required'=>'FileName field is required',
			'SendorEmail.required'=>'SendorEmail field is required'
		];

		$validator = Validator::make($data, $rules, $message);
		if ($validator->fails()) {
			return json_validator_response($validator);
		}

		if (!empty($data["AutoImportSettingID"])){

			if (AutoImportSetting::updateAccountImportSetting($data["AutoImportSettingID"],$data)) {
				return Response::json(array("status" => "success", "message" => "Account Auto Import Setting Created Successfully"));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Updating AutoImport Inbox Setting."));
			}

		}else{

			$companyID = User::get_companyID();
			$data["CompanyID"] = $companyID ;
			if (AutoImportSetting::insert($data)) {
				return Response::json(array("status" => "success", "message" => "Account AutoImport Setting created Successfully"));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Insert AutoImport Setting."));
			}

		}


	}



	/* Use In RateTable Setting page  */
	public function ratetableSetting() {
		$rateTable = RateTable::getRateTables();
		$uploadtemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_VENDOR_RATE));
		return View::make('autoimport.rate_table_setting', compact('rateTable','uploadtemplate'));
	}

	public function RateTableSettingStore(){

		$data = Input::all();
		$rules = array(
			'TypePKID' => 'required',
			'ImportFileTempleteID'=>'required',
			'Subject'=>'required',
			'FileName'=>'required',
			'SendorEmail'=>'required'
		);
		$message = ['TypePKID.required'=>'Vendor field is required',
			'ImportFileTempleteID.required'=>'Upload Template field is required',
			'Subject.required'=>'Subject field is required',
			'FileName.required'=>'FileName field is required',
			'SendorEmail.required'=>'SendorEmail field is required'
		];

		$validator = Validator::make($data, $rules, $message);
		if ($validator->fails()) {
			return json_validator_response($validator);
		}

		if (!empty($data["AutoImportSettingID"])){

			if (AutoImportSetting::updateRateTableImportSetting($data["AutoImportSettingID"],$data)) {
				return Response::json(array("status" => "success", "message" => "RateTable  AutoImport Setting Update Successfully"));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Updating AutoImport Inbox Setting."));
			}

		}else{

			$companyID = User::get_companyID();
			$data["CompanyID"] = $companyID ;
			if (AutoImportSetting::insert($data)) {
				return Response::json(array("status" => "success", "message" => "RateTable AutoImport Setting created Successfully"));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Insert AutoImport Setting."));
			}

		}


	}


	/* Use in Acccount Setting and RateTable Setting */
	public function Delete($id) {

		if ($id > 0) {
			AutoImportSetting::DeleteautoimportSetting($id);
			return Response::json(array("status" => "success", "message" => "Auto Import Setting Delete Successfully"));
		}

	}


}