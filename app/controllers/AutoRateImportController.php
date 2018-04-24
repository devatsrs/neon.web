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
		$data['iDisplayStart'] +=1;
		$columns = array('AccountName','Trunk','Import File Templete','Subject Match','Sendor Match');
		$sort_column = $columns[$data['iSortCol_0']];
		$trunkId = ( !empty($data['TrunkID']) && $data['TrunkID'] != 'undefined' ) ? $data['TrunkID'] : 0;
		$TypePKID = !empty($data['TypePKID']) ? $data['TypePKID'] : 0;
		$search = !empty($data['Search']) ? $data['Search'] : '';
		$query = "call prc_getAutoImportSetting_AccountAndRateTable (".$data["SettingType"].",".$CompanyID.",".$trunkId.",".$TypePKID.",'".$search."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."' ";

		if( isset($data['Export']) && $data['Export'] == 1) {
			$excel_data  = DB::select($query.',1)');
			$excel_data = json_decode(json_encode($excel_data),true);
			foreach($excel_data as $rowno => $rows){
				foreach($rows as $colno => $colval){
					$excel_data[$rowno][$colno] = str_replace( "<br>" , "\n" ,$colval );
				}
			}

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
		$query .=',0)';

		\Illuminate\Support\Facades\Log::info($query);

		return DataTableSql::of($query)->make();

	}



	/* Use in Account Setting page*/
	public function accountSetting() {
		$companyID = User::get_companyID();
		$trunks = Trunk::getTrunkDropdownIDList();
		$trunk_keys = getDefaultTrunk($trunks);
		$RateGenerators = RateGenerator::where(["Status" => 1, "CompanyID" => $companyID])->lists("RateGeneratorName", "RateGeneratorId");
		$all_accounts = Account::getAccountIDList(['IsVendor'=>1]);
		$uploadtemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_VENDOR_RATE);
		return View::make('autoimport.account_setting', compact('trunks','RateGenerators','all_accounts','trunk_keys','uploadtemplate'));
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
		$uploadtemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_VENDOR_RATE);
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