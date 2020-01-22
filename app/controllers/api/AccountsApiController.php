<?php

use app\controllers\api\Codes;

class AccountsApiController extends ApiController {

	public static $API_PaymentMethod = array(
		'1' => 'WireTransfer',
		'2'=>'DirectDebit',
		'3'=>'Ingenico',
		'4'=>'Other',
	);

	public static $API_PayoutMethod = array(
		'1' => 'WireTransfer',	
	);

	

	public function validEmail() {
		$data = Input::all();
		$CompanyID = User::get_companyID();
		
		$AccountID = Account::where('CompanyId',$CompanyID)
							->where('Email',$data['email'])
							->orWhere('BillingEmail', $data['email'])->pluck('AccountID');
		if($AccountID){
			return Response::json(["status"=>"failed", "data"=>"Account already Exists"]);
		}
		return Response::json(["status"=>"success", "data"=>"Account Not Found"]);
	}

	/**
	 * checkBalance():
	 * @Param mixed
	 * AccountID/AccountNo
	 * @Response
	 * has_balance - 0/1
	 * amount
	 */

	public function checkBalance(){
		$Result=array();
		//$data=Input::all();
		$post_vars = json_decode(file_get_contents("php://input"));
		$data=json_decode(json_encode($post_vars),true);


		$Account = array();
		if(!empty($data['AccountID'])) {
			$Account = Account::where(["AccountID" => $data['AccountID']])->first();
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
		}else if(!empty($data['AccountDynamicField'])){

			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found"],Codes::$Code402[0]);
			}
			$Account = Account::where(["AccountID" => $AccountID])->first();
		}

		if(!empty($Account) && count($Account)>0){
			$AccountBalance = AccountBalance::getBalanceAmount($Account->AccountID);
			if($AccountBalance > 0){
				$Result['has_balance']=1;
				$Result['amount']=$AccountBalance;
			}else{
				$Result['has_balance']=0;
				$Result['amount']=$AccountBalance;
			}
			return Response::json($Result, Codes::$Code200[0]);
		}
		return Response::json(["ErrorMessage"=>"Account Not Found"],Codes::$Code402[0]);
	}


	public function UpdateNumberStatus()
	{
		//Log::info('UpdateNumberStatus:Update CLI Status.');
		$CompanyID = User::get_companyID();
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			//$post_vars = Input::all();
			$accountData=json_decode(json_encode($post_vars),true);
			$countValues = count($accountData);
			if ($countValues == 0) {
				//Log::info('Exception in UpdateNumberStatus.Invalid JSON String');
				return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
			}
		}catch(Exception $ex) {
			Log::info('Exception in UpdateNumberStatus.Invalid JSON String' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
		}

		try {
			$data['AccountNo'] = isset($accountData['AccountNo']) ? $accountData['AccountNo'] : '';
			$data['AccountID'] = isset($accountData['AccountID']) ? $accountData['AccountID'] : '';
			$data['Number'] = isset($accountData['Number']) ? $accountData['Number'] : '';
			$data['Status'] = isset($accountData['Status']) ? $accountData['Status'] : '';



			$rules = array(
				'AccountNo' =>      'required_without_all:AccountDynamicField,AccountID',
				'AccountID' =>      'required_without_all:AccountDynamicField,AccountNo',
				'AccountDynamicField' =>      'required_without_all:AccountNo,AccountID',
				'Number'=>'required',
				'Status'=>'required',

			);


			$validator = Validator::make($data, $rules);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
			}

			if (!empty($accountData['AccountDynamicField'])) {
				$AccountIDRef = '';
				$AccountIDRef = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
				if (empty($AccountIDRef)) {
					return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
				}
				$data['AccountID'] = $AccountIDRef;
			}

			if (!empty($data['AccountNo'])) {
				$Account = Account::where(array('Number' => $data['AccountNo'],'CompanyId' => $CompanyID))->first();
			}else {
				$Account = Account::find($data['AccountID']);
			}
			if (!$Account) {
				return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
			}

			$CompanyID = $Account->CompanyId;

//		if (!isset($NumberPurchased["Status"])) {
//			return Response::json(["ErrorMessage" => Codes::$Code1043[1]],Codes::$Code1043[0]);
//		}

			if ($accountData['Status'] == '' || ($accountData['Status'] != 0 && $accountData['Status'] != 1)) {
				return Response::json(["ErrorMessage"=>Codes::$Code1044[1]],Codes::$Code1044[0]);
			}


			if ($accountData["Status"] == 0){
				$CLIRateTable = CLIRateTable::where(array('CompanyID' => $CompanyID, 'CLI' => $accountData["Number"],
					'AccountID' => $Account->AccountID,'Status' => 1))->first();
				if (!$CLIRateTable) {
					return Response::json(["ErrorMessage" => Codes::$Code1041[1]], Codes::$Code1041[0]);
				}
				$CLIRateTableFields["Status"] = $accountData['Status'];
				$CLIRateTable->update($CLIRateTableFields);
			}else {
				$CLIRateTableCount = CLIRateTable::where(array('CompanyID' => $CompanyID, 'CLI' => $accountData["Number"],
					'AccountID' => $Account->AccountID,'Status' => 0))->count();
				if ($CLIRateTableCount > 1) {
					return Response::json(["ErrorMessage" => Codes::$Code1045[1]], Codes::$Code1045[0]);
				}
				$CLIRateTable = CLIRateTable::where(array('CompanyID' => $CompanyID, 'CLI' => $accountData["Number"],
					'AccountID' => $Account->AccountID,'Status' => 0))->first();
				$CLIRateTableFields["Status"] = $accountData['Status'];
				$CLIRateTable->update($CLIRateTableFields);
			}




		}catch(Exception $ex) {
			Log::info('Exception in UpdateNumberStatus.' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>Codes::$Code500[1]],Codes::$Code500[0]);
		}

	}

	public function UpdateNumberPackage()
	{
		// <!--"PackageSubscriptionID":"13" -->
		//Log::info('UpdateNumberPackage:Update Number Package.');
		$message = '';
		$post_vars = '';
		$accountData = '';
		$DefaultSubscriptionID = '';
		$DefaultSubscriptionPackageID = '';
		$ServiceTitle = '';
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			//$post_vars = Input::all();
			$accountData=json_decode(json_encode($post_vars),true);
			$countValues = count($accountData);
			if ($countValues == 0) {
				//Log::info('Exception in UpdateNumberPackage.Invalid JSON String');
				return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
			}
		}catch(Exception $ex) {
			Log::info('Exception in UpdateNumberPackage.Invalid JSON String' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
		}




		//$post_vars = Input::all();

		$CompanyID = User::get_companyID();
		$DefaultSubscriptionID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_ID'])->pluck('Value');
		$DefaultSubscriptionPackageID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_PACKAGE_ID'])->pluck('Value');
		//Log::info('UpdateNumberPackage:Add Product Service.' . '$DefaultSubscriptionID:' . $DefaultSubscriptionID .
		//	' ' . '$DefaultSubscriptionPackageID' . ' ' . $DefaultSubscriptionPackageID);
		$CreatedBy = User::get_user_full_name();
		$date = date('Y-m-d H:i:s');
		$InboundRateTableReference = '';
		$AccountService = '';
		$AccountServiceContract = [];
		$AccountSubscription = [];
		$AccountSubscriptionDB = '';
		$AccountReferenceObj = '';
		$DynamicFieldsExist = '';
		$DynamicSubscrioptionFields = '';
		$PackagedataRecord = '';
		try {


			Log::info('UpdateNumberPackage:Data.' . json_encode($accountData));
			$data['AccountNo'] = isset($accountData['AccountNo']) ? $accountData['AccountNo'] : '';
			$data['AccountID'] = isset($accountData['AccountID']) ? $accountData['AccountID'] : '';
			$data['Number'] = isset($accountData['Number']) ? $accountData['Number'] : '';
			$data['AccountDynamicField'] = isset($accountData['AccountDynamicField']) ? $accountData['AccountDynamicField'] : '';
			$data['Package'] = isset($accountData['Package']) ? $accountData['Package'] : '';

			$rules = array(
				'AccountNo' =>      'required_without_all:AccountDynamicField,AccountID',
				'AccountID' =>      'required_without_all:AccountDynamicField,AccountNo',
				'AccountDynamicField' =>      'required_without_all:AccountNo,AccountID',
				'Number'=>'required',
				'Package'=>'required',

			);


			$validator = Validator::make($data, $rules);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
			}

			if (!empty($accountData['AccountDynamicField'])) {
				$AccountIDRef = '';
				$AccountIDRef = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
				if (empty($AccountIDRef)) {
					return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
				}
				$data['AccountID'] = $AccountIDRef;
			}

			if (!empty($data['AccountNo'])) {
				$Account = Account::where(array('Number' => $data['AccountNo'],'CompanyId' => $CompanyID))->first();
			}else {
				$Account = Account::find($data['AccountID']);
			}
			if (!$Account) {
				return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
			}

			$CompanyID = $Account->CompanyId;

			$DefaultSubscriptionIDSave = $DefaultSubscriptionID;
			$DefaultSubscriptionPackageIDSave = $DefaultSubscriptionPackageID;
			$DefaultSubscriptionID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_ID'])->pluck('Value');
			$DefaultSubscriptionPackageID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_PACKAGE_ID'])->pluck('Value');
			if (empty($DefaultSubscriptionID)) {
				$CompanyConfigurationSave['CompanyID'] = $CompanyID;
				$CompanyConfigurationSave['Key'] = 'DEFAULT_SUBSCRIPTION_ID';
				$CompanyConfigurationSave['Value'] = $DefaultSubscriptionIDSave;
				CompanyConfiguration::create($CompanyConfigurationSave);
				$DefaultSubscriptionID = $DefaultSubscriptionIDSave;
			}

			if (empty($DefaultSubscriptionPackageID)) {
				$CompanyConfigurationSave['CompanyID'] = $CompanyID;
				$CompanyConfigurationSave['Key'] = 'DEFAULT_SUBSCRIPTION_PACKAGE_ID';
				$CompanyConfigurationSave['Value'] = $DefaultSubscriptionPackageIDSave;
				CompanyConfiguration::create($CompanyConfigurationSave);
				$DefaultSubscriptionPackageID = $DefaultSubscriptionPackageIDSave;
			}





			$NumberPurchased=json_decode(json_encode($data['Package']),true);

			//Log::info('UpdateNumberPackage:$NumberPurchasedRef .' . count($NumberPurchased));

			$NumberPurchaseds = [];


			$CLIRateTable = CLIRateTable::where(array('CompanyID'=>$CompanyID, 'CLI'=>$data["Number"],
				'AccountID'=>$Account->AccountID,'Status'=>1))->first();
			if(!$CLIRateTable){
				return Response::json(array("ErrorMessage" => Codes::$Code1041[1]),Codes::$Code1041[0]);
			}

			if (!empty($NumberPurchased['PackageSubcriptionDynamicField'])) {
				$PackagedataRecord =  Package::findPackageByDynamicField($NumberPurchased['PackageSubcriptionDynamicField']);
				if (empty($PackagedataRecord)) {
					return Response::json(["ErrorMessage" => Codes::$Code1031[1]], Codes::$Code1031[0]);
				}
				$PackagedataRecord = Package::where(array('PackageId' => $PackagedataRecord,'CompanyID' => $CompanyID))->first();

				if (!isset($PackagedataRecord) || $PackagedataRecord == '') {
					return Response::json(["ErrorMessage" => Codes::$Code1031[1]], Codes::$Code1031[0]);
				}
				//$PackagedataRecord = Package::find($PackagedataRecord);
				$NumberPurchased["PackageID"] = $PackagedataRecord["PackageId"];
				$NumberPurchased["PackageRateTableID"] = $PackagedataRecord["RateTableId"];
			}




			if (!isset($NumberPurchased['PackageSubscriptionStartDate']) || empty($NumberPurchased['PackageSubscriptionStartDate'])) {
				return Response::json(["ErrorMessage"=>Codes::$Code1040[1]],Codes::$Code1040[0]);
			}
			if (isset($NumberPurchased['PackageSubscriptionEndDate'])&& !empty($NumberPurchased['PackageSubscriptionEndDate'])) {
				if ($NumberPurchased['PackageSubscriptionStartDate'] > $NumberPurchased['PackageSubscriptionEndDate']) {
					return Response::json(["ErrorMessage" => Codes::$Code1002[1]], Codes::$Code1002[0]);
				}
			}

			DB::beginTransaction();
			$CLIRateTableFields["PackageID"] = $NumberPurchased["PackageID"];
			$CLIRateTableFields["PackageRateTableID"] = $NumberPurchased["PackageRateTableID"];
			$CLIRateTable->update($CLIRateTableFields);

			$AccountService = AccountService::where(array('AccountServiceID' => $CLIRateTable->AccountServiceID))->first();


			$DynamicFieldIDs = '';
			$DynamicFieldsExists=  DynamicFields::where('Type', 'subscription')->get();
			foreach ($DynamicFieldsExists as $DynamicFieldsExist) {
				$DynamicFieldIDs = $DynamicFieldIDs .$DynamicFieldsExist["DynamicFieldsID"] . ",";
			}
			//Log::info('update $DynamicFieldIDs.' . $DynamicFieldIDs);
			if ($DynamicFieldIDs != '') {
				$DynamicFieldIDs = explode(',', $DynamicFieldIDs);
			}else {
				$DynamicFieldIDs = [];
			}

			//Log::info('NumberPurchased CLI and Package Description' . print_r($NumberPurchased,true));
			$SubscriptionSequence = 0;
			$AccountSubscriptionLast = AccountSubscription::where(array('AccountID' => $Account->AccountID,
				'AccountServiceID'=> $AccountService->AccountServiceID))
				->orderByRaw('SequenceNo desc')
				->first();
			if (isset($AccountSubscriptionLast)) {
				$SubscriptionSequence = $AccountSubscriptionLast["SequenceNo"];
			}
			if (!empty($DefaultSubscriptionPackageID)) {



				$RateTablePKGRates = RateTablePKGRate::
				Join('tblRate', 'tblRateTablePKGRate.RateID', '=', 'tblRate.RateID')
					->select(['tblRateTablePKGRate.OneOffCost', 'tblRateTablePKGRate.MonthlyCost',
						'tblRateTablePKGRate.OneOffCostCurrency','tblRateTablePKGRate.MonthlyCostCurrency']);
				$RateTablePKGRates = $RateTablePKGRates->where(["tblRate.Code" => $PackagedataRecord["Name"]]);
				$RateTablePKGRates = $RateTablePKGRates->where(["tblRateTablePKGRate.RateTableId" => $PackagedataRecord["RateTableId"]]);
				$RateTablePKGRates = $RateTablePKGRates->where(["tblRateTablePKGRate.ApprovedStatus" => 1]);
				$RateTablePKGRates = $RateTablePKGRates->whereRaw("tblRateTablePKGRate.EffectiveDate <= NOW()");
				$RateTablePKGRates = $RateTablePKGRates->whereRaw("tblRateTablePKGRate.MonthlyCost is not null");
				//Log::info('Package $RateTablePkgRates.' . $RateTablePKGRates->toSql());
				$RateTablePKGRates = $RateTablePKGRates->get();
				//$RateTableDIDRates = RateTableDIDRate::where(array('CityTariff' => $ServiceTemaplateReference->city_tariff))->get();
				foreach ($RateTablePKGRates as $RateTablePKGRate) {
					$this->createAccountSubscriptionForChangePackage($Account, $AccountSubscriptionDB,
						$NumberPurchased["PackageSubscriptionStartDate"],$NumberPurchased["PackageSubscriptionEndDate"],
						$AccountService,
						$DefaultSubscriptionPackageID, $DynamicFieldIDs,
						$RateTablePKGRate, $NumberPurchased["InvoicePackageDescription"],++$SubscriptionSequence);
				}
			}

			DB::commit();
			return Response::json(json_decode('{}'),Codes::$Code200[0]);


		} catch (Exception $ex) {
			DB::rollback();
			Log::info('UpdateNumberPackage:Exception.' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage" => Codes::$Code500[1]],Codes::$Code500[0]);
		}
	}

	public function UpdateNumber()
	{
		// <!--"PackageSubscriptionID":"13" -->
		//	Log::info('UpdateNumber:Update Number.');
		$message = '';
		$post_vars = '';
		$accountData = '';
		$DefaultSubscriptionID = '';
		$DefaultSubscriptionPackageID = '';
		$ServiceTitle = '';
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			//$post_vars = Input::all();
			$accountData=json_decode(json_encode($post_vars),true);
			$countValues = count($accountData);
			if ($countValues == 0) {
				//Log::info('Exception in UpdateNumber.Invalid JSON String');
				return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
			}
		}catch(Exception $ex) {
			Log::info('Exception in UpdateNumber.Invalid JSON String' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
		}




		//$post_vars = Input::all();

		$CompanyID = User::get_companyID();
		$DefaultSubscriptionID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_ID'])->pluck('Value');
		$DefaultSubscriptionPackageID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_PACKAGE_ID'])->pluck('Value');
		//Log::info('UpdateNumber:Add Product Service.' . '$DefaultSubscriptionID:' . $DefaultSubscriptionID .
		//	' ' . '$DefaultSubscriptionPackageID' . ' ' . $DefaultSubscriptionPackageID);
		$CreatedBy = User::get_user_full_name();
		$date = date('Y-m-d H:i:s');

		try {


			//Log::info('UpdateNumberPackage:Data.' . json_encode($accountData));
			$data['AccountNo'] = isset($accountData['AccountNo']) ? $accountData['AccountNo'] : '';
			$data['AccountID'] = isset($accountData['AccountID']) ? $accountData['AccountID'] : '';
			$data['AccountDynamicField'] = isset($accountData['AccountDynamicField']) ? $accountData['AccountDynamicField'] : '';
			$data['NumberPurchased'] = isset($accountData['NewNumber']) ? $accountData['NewNumber'] : '';
			$data['OldNumber'] = isset($accountData['OldNumber']) ? $accountData['OldNumber'] : '';
			$data['ProductDynamicField'] = isset($accountData['ProductDynamicField']) ? $accountData['ProductDynamicField'] : '';
			$data['InboundTariffCategoryID'] = isset($accountData['InboundTariffCategoryID']) ? $accountData['InboundTariffCategoryID'] :'';

			$rules = array(
				'AccountNo' =>      'required_without_all:AccountDynamicField,AccountID',
				'AccountID' =>      'required_without_all:AccountDynamicField,AccountNo',
				'AccountDynamicField' =>      'required_without_all:AccountNo,AccountID',
				'NumberPurchased'=>'required',
				'OldNumber'=>'required',
				'ProductDynamicField'=>'required',

			);


			$validator = Validator::make($data, $rules);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
			}

			if (!empty($accountData['AccountDynamicField'])) {
				$AccountIDRef = '';
				$AccountIDRef = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
				if (empty($AccountIDRef)) {
					return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
				}
				$data['AccountID'] = $AccountIDRef;
			}

			if (!empty($data['AccountNo'])) {
				$Account = Account::where(array('Number' => $data['AccountNo'],'CompanyId' => $CompanyID))->first();
			}else {
				$Account = Account::find($data['AccountID']);
			}
			if (!$Account) {
				return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
			}

			$CompanyID = $Account->CompanyId;

			$DefaultSubscriptionIDSave = $DefaultSubscriptionID;
			$DefaultSubscriptionPackageIDSave = $DefaultSubscriptionPackageID;
			$DefaultSubscriptionID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_ID'])->pluck('Value');
			$DefaultSubscriptionPackageID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_PACKAGE_ID'])->pluck('Value');
			if (empty($DefaultSubscriptionID)) {
				$CompanyConfigurationSave['CompanyID'] = $CompanyID;
				$CompanyConfigurationSave['Key'] = 'DEFAULT_SUBSCRIPTION_ID';
				$CompanyConfigurationSave['Value'] = $DefaultSubscriptionIDSave;
				CompanyConfiguration::create($CompanyConfigurationSave);
				$DefaultSubscriptionID = $DefaultSubscriptionIDSave;
			}

			if (empty($DefaultSubscriptionPackageID)) {
				$CompanyConfigurationSave['CompanyID'] = $CompanyID;
				$CompanyConfigurationSave['Key'] = 'DEFAULT_SUBSCRIPTION_PACKAGE_ID';
				$CompanyConfigurationSave['Value'] = $DefaultSubscriptionPackageIDSave;
				CompanyConfiguration::create($CompanyConfigurationSave);
				$DefaultSubscriptionPackageID = $DefaultSubscriptionPackageIDSave;
			}


			$ServiceTemaplateReference = ServiceTemplate::findServiceTemplateByDynamicField($data['ProductDynamicField']);
			if (empty($ServiceTemaplateReference)) {
				return Response::json(array("ErrorMessage" => Codes::$Code1021[1]),Codes::$Code1021[0]);
			}

			$ServiceTemaplateReference = ServiceTemplate::where(array('ServiceTemplateId' => $ServiceTemaplateReference,'CompanyID' => $CompanyID))->first();

			if (!isset($ServiceTemaplateReference) || $ServiceTemaplateReference == '') {
				return Response::json(array("ErrorMessage" => Codes::$Code1021[1]),Codes::$Code1021[0]);
			}

			$ProductCountryPrefix = '';
			$ProductCountry = Country::where(array('Country' => $ServiceTemaplateReference->country))->first();
			if (!isset($ProductCountry) || $ProductCountry == '') {
				return Response::json(array("ErrorMessage" => Codes::$Code1046[1]),Codes::$Code1046[0]);
			}

			if (substr($ServiceTemaplateReference->prefixName,0,1) == "0") {
				$ProductCountryPrefix = $ProductCountry->Prefix . substr($ServiceTemaplateReference->prefixName,1,strlen($ServiceTemaplateReference->prefixName));
			} else {
				$ProductCountryPrefix = $ProductCountry->Prefix .  empty($ServiceTemaplateReference->prefixName) ? "" : $ServiceTemaplateReference->prefixName;
			}

			//Log::info('$ServiceTemaplateReference' . $ServiceTemaplateReference->ServiceTemplateId . ' ' . $ProductCountryPrefix);

			if (!empty($data['InboundTariffCategoryID'])) {
				$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['InboundTariffCategoryID']])->count();
				if ($InboundRateTableReference > 1) {
					return Response::json(["ErrorMessage" => Codes::$Code1009[1]],Codes::$Code1009[0]);
				}
				$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['InboundTariffCategoryID']])->pluck('RateTableId');
			}else {
				$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->count();
				if ($InboundRateTableReference > 1) {
					return Response::json(["ErrorMessage" => Codes::$Code1009[1]],Codes::$Code1009[0]);
				}
				$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->pluck('RateTableId');
			}

			//Log::info('$InboundRateTableReference' . $InboundRateTableReference . ' ' . $data['InboundTariffCategoryID']);

			$DynamicFieldIDs = '';
			$DynamicFieldsExists=  DynamicFields::where('Type', 'subscription')->get();
			foreach ($DynamicFieldsExists as $DynamicFieldsExist) {
				$DynamicFieldIDs = $DynamicFieldIDs .$DynamicFieldsExist["DynamicFieldsID"] . ",";
			}
			//Log::info('update $DynamicFieldIDs.' . $DynamicFieldIDs);
			$DynamicFieldIDs = explode(',', $DynamicFieldIDs);



			$NumberPurchased=json_decode(json_encode($data['NumberPurchased']),true);

			//Log::info('Update Number :$NumberPurchasedRef .' . count($NumberPurchased));

			if (!isset($NumberPurchased['NumberSubscriptionStartDate']) || empty($NumberPurchased['NumberSubscriptionStartDate'])) {
				return Response::json(["ErrorMessage"=>Codes::$Code1038[1]],Codes::$Code1038[0]);
			}
			if (isset($NumberPurchased['NumberSubscriptionEndDate']) && !empty($NumberPurchased['NumberSubscriptionEndDate'])) {
				if ($NumberPurchased['NumberSubscriptionStartDate'] > $NumberPurchased['NumberSubscriptionEndDate']) {
					return Response::json(["ErrorMessage" => Codes::$Code1002[1]], Codes::$Code1002[0]);
				}
			}

			$NumberPurchaseds = [];

			//Log::info('UpdateNumber:$NumberPurchasedRef .' . $data['OldNumber']);
			$CLIRateTable = CLIRateTable::where(array('CompanyID'=>$CompanyID, 'CLI'=>$data['OldNumber'],
				'AccountID'=>$Account->AccountID))->first();
			if(!$CLIRateTable){
				return Response::json(array("ErrorMessage" => Codes::$Code1041[1]),Codes::$Code1041[0]);
			}

			$AccountService = AccountService::where(array('AccountServiceID' => $CLIRateTable->AccountServiceID))->first();
			$SubscriptionSequence = 0;
			$AccountSubscriptionLast = AccountSubscription::where(array('AccountID' => $Account->AccountID,
				'AccountServiceID'=> $AccountService->AccountServiceID))
				->orderByRaw('SequenceNo desc')
				->first();
			if (isset($AccountSubscriptionLast)) {
				$SubscriptionSequence = $AccountSubscriptionLast["SequenceNo"];
			}

			$AccountSubscriptionDB = BillingSubscription::where(array('SubscriptionID' => $DefaultSubscriptionID))->first();
			//Log::info('update $DynamicFieldIDs12.' . $AccountSubscriptionDB);
			if (!isset($AccountSubscriptionDB) || $AccountSubscriptionDB == '') {
				return Response::json(["ErrorMessage" => Codes::$Code1005[1]], Codes::$Code1005[0]);
			}

			DB::beginTransaction();
			$VendorIDDIDRateList = '';
			if (!empty($DefaultSubscriptionID) && !empty($InboundRateTableReference)) {
				$RateTableDIDRates = RateTableDIDRate::
				Join('tblRate', 'tblRateTableDIDRate.RateID', '=', 'tblRate.RateID')
					->select(['tblRateTableDIDRate.OneOffCost', 'tblRateTableDIDRate.MonthlyCost',
						'tblRateTableDIDRate.OneOffCostCurrency','tblRateTableDIDRate.MonthlyCostCurrency','tblRateTableDIDRate.VendorID']);
				$RateTableDIDRates = $RateTableDIDRates->whereRaw('\'' . $ProductCountryPrefix . '\'' . ' like  CONCAT(tblRate.Code,"%")');
				$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.CityTariff" => $ServiceTemaplateReference->city_tariff]);
				$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.RateTableId" => $InboundRateTableReference]);
				$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.ApprovedStatus" => 1]);
				$RateTableDIDRates = $RateTableDIDRates->whereRaw("tblRateTableDIDRate.EffectiveDate <= NOW()");
				$RateTableDIDRates = $RateTableDIDRates->whereRaw("tblRateTableDIDRate.MonthlyCost is not null");
				//Log::info('$RateTableDIDRates CLI.' . $RateTableDIDRates->toSql());
				$RateTableDIDRates = $RateTableDIDRates->get();
				//$RateTableDIDRates = RateTableDIDRate::where(array('CityTariff' => $ServiceTemaplateReference->city_tariff))->get();
				foreach ($RateTableDIDRates as $RateTableDIDRate) {
					$this->createAccountSubscriptionFromRateTable($Account, $AccountSubscriptionDB,
						$NumberPurchased["NumberSubscriptionStartDate"],$NumberPurchased["NumberSubscriptionEndDate"] ,$ServiceTemaplateReference, $AccountService,
						$DefaultSubscriptionID, $DynamicFieldIDs,
						$RateTableDIDRate, $NumberPurchased["InvoiceNoDescription"],++$SubscriptionSequence);
					$VendorIDDIDRateList = $RateTableDIDRate["VendorID"];
				}
			}

			$CLIRateTableFields['VendorID'] = $VendorIDDIDRateList;
			$CLIRateTableFields["CLI"] = $NumberPurchased["Number"];
			$CLIRateTableFields['DIDCategoryID'] = $data['InboundTariffCategoryID'];
			$CLIRateTableFields['Prefix'] = $ProductCountryPrefix;
			//Log::info('UpdateNumber:' . print_r($CLIRateTableFields,true));
			$CLIRateTable->update($CLIRateTableFields);


			DB::commit();
			return Response::json(json_decode('{}'),Codes::$Code200[0]);


		} catch (Exception $ex) {
			DB::rollback();
			Log::info('UpdateNumber:Exception.' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage" => Codes::$Code500[1]],Codes::$Code500[0]);
		}
	}
	/*public function createAccountService1()
	{
		$message = '';
		$post_vars = '';
		$accountData = '';
		$DefaultSubscriptionID = '';
		$DefaultSubscriptionPackageID = '';
		$ServiceTitle = '';
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			//$post_vars = Input::all();
			$accountData=json_decode(json_encode($post_vars),true);
			$countValues = count($accountData);
			if ($countValues == 0) {
				Log::info('Exception in createAccountService.Invalid JSON String');
				return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
			}
		}catch(Exception $ex) {
			Log::info('Exception in RcreateAccountService.Invalid JSON String' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
		}

		$CompanyID = User::get_companyID();
		$CreatedBy = User::get_user_full_name();
		$date = date('Y-m-d H:i:s');
		$accdata = array();
		$data['AccountNo'] = isset($accountData['AccountNo']) ? $accountData['AccountNo'] : '';
		$data['AccountID'] = isset($accountData['AccountID']) ? $accountData['AccountID'] : '';
		$data['NumberPurchased'] = isset($accountData['NumberPurchased']) ? $accountData['NumberPurchased'] : '';
		$data['AccountDynamicField'] = isset($accountData['AccountDynamicField']) ? $accountData['AccountDynamicField'] : '';

		$accdata['ServiceOrderID'] = empty($accountData['ServiceOrderID']) ? '' : $accountData['ServiceOrderID'];
		$accdata['ServiceTitle'] = empty($accountData['ServiceTitle']) ? '' : $accountData['ServiceTitle'];
		$accdata['ServiceDescription'] = empty($accountData['ServiceDescription']) ? '' : $accountData['ServiceDescription'];
		$accdata['ServiceTitleShow'] = isset($accountData['ServiceTitleShow']) ? 1 : 0;
		$accdata['ServiceID'] = empty($accountData['ServiceID']) ? '' : $accountData['ServiceID'];
		$data['ServiceID'] = $accdata['ServiceID'];
		$rules = array(
			'AccountNo' =>      'required_without_all:AccountDynamicField,AccountID',
			'AccountID' =>      'required_without_all:AccountDynamicField,AccountNo',
			'AccountDynamicField' =>      'required_without_all:AccountNo,AccountID',
			'ServiceTitleShow'=>'in:1,0',
			'ServiceID' =>      'required',
		);

		$validator = Validator::make($data, $rules);

		if ($validator->fails()) {
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
		}

		$AccountServiceID = Service::where(array('ServiceID' => $accdata['ServiceID']));
		if ($AccountServiceID->count() == 0) {
			return Response::json(array("ErrorMessage" => Codes::$Code1047[1]),Codes::$Code1047[0]);
		}

		if (!empty($accountData['AccountDynamicField'])) {
			$AccountIDRef = '';
			$AccountIDRef = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if (empty($AccountIDRef)) {
				return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
			}
			$data['AccountID'] = $AccountIDRef;
		}

		if (!empty($data['AccountNo'])) {
			$Account = Account::where(array('Number' => $data['AccountNo'],'CompanyId' => $CompanyID))->first();
		}else {
			$Account = Account::find($data['AccountID']);
		}
		if (!$Account) {
			return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
		}
		$CompanyID = $Account->CompanyId;
		$NumberPurchasedRef = [];
		if (!empty($data['NumberPurchased'])) {
			$NumberPurchasedRef = json_decode(json_encode($data['NumberPurchased']), true);
		}
		$PackagePurchasedRef = [];
		if (isset($accountData['PackagePurchased'])) {
			$PackagePurchasedRef = json_decode(json_encode($accountData['PackagePurchased']), true);
		}
		$NumberPurchaseds = [];
		$PackagePurchaseds = [];
		for ($i =0; $i <count($PackagePurchasedRef);$i++) {
			$PackagePurchaseds = $PackagePurchasedRef[$i];
			if (!empty($PackagePurchaseds['PackageDynamicField'])) {
				$PackagedataRecord =  Package::findPackageByDynamicField($PackagePurchaseds['PackageDynamicField']);
				if (empty($PackagedataRecord)) {
					return Response::json(["ErrorMessage" => Codes::$Code1031[1]], Codes::$Code1031[0]);
				}
				$PackagedataRecord = Package::where(array('PackageId' => $PackagedataRecord,'CompanyID' => $CompanyID))->first();

				if (!isset($PackagedataRecord) || $PackagedataRecord == '') {
					return Response::json(["ErrorMessage" => Codes::$Code1031[1]], Codes::$Code1031[0]);
				}
			}
			if (!empty($PackagePurchaseds['PackageRateTableID'])) {
				$AccountServiceID = RateTable::where('Type', '=', RateGenerator::Package)
					->where("AppliedTo", "!=", RateTable::APPLIED_TO_VENDOR)
					->where('RateTableId', '=', $PackagePurchaseds['PackageRateTableID'])
					->where('CompanyId', '=', $CompanyID);
				if ($AccountServiceID->count() == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1048[1]), Codes::$Code1048[0]);
				}
			}
			if (!empty($PackagePurchaseds['PackageDiscountPlanID'])) {
				$PackagePlanID = DiscountPlan::verifyDiscountPlanID($CompanyID,(int)$Account->CurrencyId,RateType::PACKAGE_ID,$PackagePurchaseds['PackageDiscountPlanID']);
				if ($PackagePlanID == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1049[1]), Codes::$Code1049[0]);
				}
			}

			if (!isset($PackagePurchaseds['PackageStartDate']) || empty($PackagePurchaseds['PackageStartDate'])) {
				return Response::json(["ErrorMessage"=>Codes::$Code1040[1]],Codes::$Code1040[0]);
			}
			if (!isset($PackagePurchaseds['PackageEndDate']) || empty($PackagePurchaseds['PackageEndDate'])) {
				return Response::json(["ErrorMessage"=>Codes::$Code1055[1]],Codes::$Code1055[0]);
			}
			if (isset($PackagePurchaseds['PackageEndDate']) && !empty($PackagePurchaseds['PackageEndDate'])) {
				if ($PackagePurchaseds['PackageStartDate'] > $PackagePurchaseds['PackageStartDate']) {
					return Response::json(["ErrorMessage" => Codes::$Code1002[1]], Codes::$Code1002[0]);
				}
			}
		}


		for ($i =0; $i <count($NumberPurchasedRef);$i++) {
			$NumberPurchased = $NumberPurchasedRef[$i];

			if (!empty($NumberPurchased['AccessRateTableID'])) {
				$AccountServiceID = RateTable::where('Type', '=', RateGenerator::DID)
					->where("AppliedTo", "!=", RateTable::APPLIED_TO_VENDOR)
					->where('RateTableId', '=', $NumberPurchased['AccessRateTableID'])
					->where('CompanyId', '=', $CompanyID);
				if ($AccountServiceID->count() == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1048[1]), Codes::$Code1048[0]);
				}
			}
			if (!empty($NumberPurchased['AccessDiscountPlanID'])) {
				$NumberPlanID = DiscountPlan::verifyDiscountPlanID($CompanyID,(int)$Account->CurrencyId,RateType::DID_ID,$NumberPurchased['AccessDiscountPlanID']);
				if ($NumberPlanID == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1049[1]), Codes::$Code1049[0]);
				}
			}
			if (!empty($NumberPurchased['TerminationDiscountPlanID'])) {
				$NumberPlanID = DiscountPlan::verifyDiscountPlanID($CompanyID,(int)$Account->CurrencyId,RateType::VOICECALL_ID,$NumberPurchased['TerminationDiscountPlanID']);
				if ($NumberPlanID == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1049[1]), Codes::$Code1049[0]);
				}
			}

			if (!empty($NumberPurchased['TerminationRateTableID'])) {
				$AccountServiceID = RateTable::where('Type', '=', RateGenerator::VoiceCall)
					->where("AppliedTo", "!=", RateTable::APPLIED_TO_VENDOR)
					->where('RateTableId', '=', $NumberPurchased['TerminationRateTableID'])
					->where('CompanyId', '=', $CompanyID);
				if ($AccountServiceID->count() == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1048[1]), Codes::$Code1048[0]);
				}
			}
			if (!empty($NumberPurchased['CountryID'])) {
				$ProductCountry = Country::where(array('CountryID' => $NumberPurchased['CountryID']))->first();
				if (!isset($ProductCountry) || $ProductCountry == '') {
					return Response::json(array("ErrorMessage" => Codes::$Code1050[1]), Codes::$Code1050[0]);
				}
			}
			if (!empty($NumberPurchased['Type'])) {
				$NumberData = ServiceTemplate::verifyAccessTypeDD($CompanyID,$NumberPurchased['Type']);
				if ($NumberData == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1051[1]), Codes::$Code1051[0]);
				}
			}
			if (!empty($NumberPurchased['City'])) {
				$NumberData = ServiceTemplate::verifyCityDD($CompanyID,$NumberPurchased['City']);
				if ($NumberData == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1053[1]), Codes::$Code1053[0]);
				}
			}
			if (!empty($NumberPurchased['Prefix'])) {
				$NumberData = ServiceTemplate::verifyPrefixDD($CompanyID,$NumberPurchased['Prefix']);
				if ($NumberData == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1052[1]), Codes::$Code1052[0]);
				}
			}

			if (!empty($NumberPurchased['Tariff'])) {
				$NumberData = ServiceTemplate::verifyTariffDD($CompanyID,$NumberPurchased['Tariff']);
				if ($NumberData == 0) {
					return Response::json(array("ErrorMessage" => Codes::$Code1054[1]), Codes::$Code1054[0]);
				}
			}

			if (!isset($NumberPurchased['NumberStartDate']) || empty($NumberPurchased['NumberStartDate'])) {
				return Response::json(["ErrorMessage"=>Codes::$Code1038[1]],Codes::$Code1038[0]);
			}
			if (!isset($NumberPurchased['NumberEndDate']) || empty($NumberPurchased['NumberEndDate'])) {
				return Response::json(["ErrorMessage"=>Codes::$Code1039[1]],Codes::$Code1039[0]);
			}
			if (isset($NumberPurchased['NumberEndDate']) && !empty($NumberPurchased['NumberEndDate'])) {
				if ($NumberPurchased['NumberStartDate'] > $NumberPurchased['NumberEndDate']) {
					return Response::json(["ErrorMessage" => Codes::$Code1002[1]], Codes::$Code1002[0]);
				}
			}
		}

		try{
		DB::beginTransaction();


			$accdata['AccountID'] = $Account->AccountID;
			$accdata['CompanyID'] = $CompanyID;
			$accdata['Status'] = 1;

		$AccountService = AccountService::create($accdata);

		for ($i = 0; $i < count($NumberPurchasedRef); $i++) {
			$NumberPurchased = $NumberPurchasedRef[$i];
			$rate_tables = [];
			$check = CLIRateTable::where([
				'CompanyID' =>  $CompanyID,
				'AccountID' =>  $Account->AccountID,
				'AccountServiceID' =>  $AccountService ->AccountServiceID,
				'Status'    =>  1
			])->whereRaw("'" . $NumberPurchased['NumberStartDate'] . "'" .  " >= NumberStartDate")
				->whereRaw("'" .$NumberPurchased['NumberEndDate']. "'" . " <= NumberEndDate");

			if($check->count() > 0){
				return Response::json(array("ErrorMessage" => Codes::$Code1008[1]),Codes::$Code1008[0]);
			}

			$rate_tables['CLI'] = !empty($NumberPurchased['Number']) ? $NumberPurchased['Number'] : '';
			$rate_tables['RateTableID'] = !empty($NumberPurchased['AccessRateTableID']) ? $NumberPurchased['AccessRateTableID'] : 0;
			$rate_tables['AccessDiscountPlanID'] = !empty($NumberPurchased['AccessDiscountPlanID']) ? $NumberPurchased['AccessDiscountPlanID'] : 0;
			$rate_tables['TerminationRateTableID'] = !empty($NumberPurchased['TerminationRateTableID']) ? $NumberPurchased['TerminationRateTableID'] : 0;
			$rate_tables['TerminationDiscountPlanID'] = !empty($NumberPurchased['TerminationDiscountPlanID']) ? $NumberPurchased['TerminationDiscountPlanID'] : 0;
			$rate_tables['CountryID'] = !empty($NumberPurchased['CountryID']) ? $NumberPurchased['CountryID'] : 0;
			$rate_tables['NumberStartDate'] = !empty($NumberPurchased['NumberStartDate']) ? $NumberPurchased['NumberStartDate'] : '';
			$rate_tables['NumberEndDate'] = !empty($NumberPurchased['NumberEndDate']) ? $NumberPurchased['NumberEndDate'] : '';
			$rate_tables['NoType'] = !empty($NumberPurchased['Type']) ? $NumberPurchased['Type'] : '';
			$rate_tables['Prefix'] = !empty($NumberPurchased['Prefix'])?$NumberPurchased['Prefix']:'';
			$rate_tables['ContractID'] = !empty($NumberPurchased['ContractID'])?$NumberPurchased['ContractID']:'';
			$rate_tables['City'] = !empty($NumberPurchased['City'])?$NumberPurchased['City']:'';
			$rate_tables['Tariff'] = !empty($NumberPurchased['Tariff'])?$NumberPurchased['Tariff']:'';
			$rate_tables['AccountID'] = $Account->AccountID;
			$rate_tables['CompanyID'] = $CompanyID;


			$rate_tables['Status'] = isset($NumberPurchased['Status']) ? 1 : 0;
			$rate_tables['ServiceID'] = $accdata['ServiceID'];

			$rate_tables['AccountServiceID'] =$AccountService ->AccountServiceID;

			Log::info('createAccountService:rate_tables.' . print_r($rate_tables,true));
			CLIRateTable::insert($rate_tables);
		}

			for ($i =0; $i <count($PackagePurchasedRef);$i++) {
				$PackagePurchaseds = $PackagePurchasedRef[$i];
				$rate_tables = [];
				$check = AccountServicePackage::where([
					'CompanyID'=>$CompanyID,
					'AccountID' =>  $Account->AccountID,
					'AccountServiceID' =>  $AccountService ->AccountServiceID,
					'Status'=>1
				])->whereRaw("'" . $PackagePurchaseds['PackageStartDate'] . "'" .  " >= PackageStartDate")
					->whereRaw("'" .$PackagePurchaseds['PackageEndDate']. "'" . " <= PackageEndDate");

				if($check->count() > 0){
					return Response::json(array("ErrorMessage" => Codes::$Code1056[1]),Codes::$Code1056[0]);
				}

				if (!empty($PackagePurchaseds['PackageDynamicField'])) {
					$PackagedataRecord =  Package::findPackageByDynamicField($PackagePurchaseds['PackageDynamicField']);
					if (empty($PackagedataRecord)) {
						return Response::json(["ErrorMessage" => Codes::$Code1031[1]], Codes::$Code1031[0]);
					}
					$PackagedataRecord = Package::where(array('PackageId' => $PackagedataRecord,'CompanyID' => $CompanyID))->first();

					$rate_tables['PackageID'] = $PackagedataRecord->PackageId;
				}

				$rate_tables['RateTableID'] = !empty($PackagePurchaseds['PackageRateTableID']) ? $PackagePurchaseds['PackageRateTableID'] : 0;
				$rate_tables['PackageDiscountPlanID'] = !empty($PackagePurchaseds['PackageDiscountPlanID']) ? $PackagePurchaseds['PackageDiscountPlanID'] : 0;
				$rate_tables['PackageStartDate'] = $PackagePurchaseds['PackageStartDate'] ;
				$rate_tables['PackageEndDate'] = $PackagePurchaseds['PackageEndDate'];
				$rate_tables['ContractID'] = !empty($PackagePurchaseds['ContractID'])?$PackagePurchaseds['ContractID']:'';
				$rate_tables['AccountID'] = $Account->AccountID;
				$rate_tables['CompanyID'] = $CompanyID;
				$rate_tables['created_at'] = $date;
				$rate_tables['created_by'] = $CreatedBy;
				$rate_tables['updated_at'] = $date;
				$rate_tables['updated_by'] = $CreatedBy;

				$rate_tables['Status'] = isset($PackagePurchaseds['Status']) ? 1 : 0;
				$rate_tables['ServiceID'] = $accdata['ServiceID'];
				$rate_tables['AccountServiceID'] = $AccountService ->AccountServiceID;
				AccountServicePackage::insert($rate_tables);
			}
		$message = "Account Service Successfully Added";




		DB::commit();

		return Response::json(json_decode('{}'),Codes::$Code200[0]);


	} catch (Exception $ex) {
		DB::rollback();
		Log::info('createAccountService:Exception.' . $ex->getTraceAsString());
		return Response::json(["ErrorMessage" => Codes::$Code500[1]],Codes::$Code500[0]);
	}
}*/
	public function createAccountService()
	{
		// <!--"PackageSubscriptionID":"13" -->
		//Log::info('createAccountService:Add Product Service.');
		$message = '';
		$post_vars = '';
		$accountData = '';
		$DefaultSubscriptionID = '';
		$DefaultSubscriptionPackageID = '';
		$ServiceTitle = '';
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			//$post_vars = Input::all();
			$accountData=json_decode(json_encode($post_vars),true);
			$countValues = count($accountData);
			if ($countValues == 0) {
				Log::info('Exception in createAccountService.Invalid JSON String');
				return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
			}
		}catch(Exception $ex) {
			Log::info('Exception in RcreateAccountService.Invalid JSON String' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
		}




		//$post_vars = Input::all();

		$CompanyID = User::get_companyID();
		$DefaultSubscriptionID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_ID'])->pluck('Value');
		$DefaultSubscriptionPackageID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_PACKAGE_ID'])->pluck('Value');
		//Log::info('createAccountService:Add Product Service.' . '$DefaultSubscriptionID:' . $DefaultSubscriptionID .
		//	' ' . '$DefaultSubscriptionPackageID' . ' ' . $DefaultSubscriptionPackageID);
		$CreatedBy = User::get_user_full_name();
		$date = date('Y-m-d H:i:s');
		$InboundRateTableReference = '';
		$AccountService = '';
		$AccountServiceContract = [];
		$AccountSubscription = [];
		$AccountSubscriptionDB = '';
		$AccountReferenceObj = '';
		$DynamicFieldsExist = '';
		$DynamicSubscrioptionFields = '';
		$PackagedataRecord = '';
		$Packagedata = [];
		try {


			//Log::info('createAccountService:Data.' . json_encode($accountData));
			$data['AccountNo'] = isset($accountData['AccountNo']) ? $accountData['AccountNo'] : '';
			$data['AccountID'] = isset($accountData['AccountID']) ? $accountData['AccountID'] : '';
			$data['NumberPurchased'] = isset($accountData['NumberPurchased']) ? $accountData['NumberPurchased'] : '';
			$data['AccountDynamicField'] = isset($accountData['AccountDynamicField']) ? $accountData['AccountDynamicField'] : '';
			$data['OrderTypeId'] = isset($accountData['OrderTypeId']) ? $accountData['OrderTypeId'] :'';
			$AccountServiceContract['ContractStartDate'] = isset($accountData['ContractStartDate']) ? $accountData['ContractStartDate'] :'' ;
			$AccountServiceContract['ContractEndDate'] = isset($accountData['ContractEndDate']) ? $accountData['ContractEndDate'] : '';
			$AccountServiceContract['Duration'] = isset($accountData['ContractDuration']) ? $accountData['ContractDuration'] : '';
			$AccountServiceContract['ContractReason'] = isset($accountData['ContractFeeValue']) ? $accountData['ContractFeeValue'] : '';
			$AccountServiceContract['AutoRenewal'] = isset($accountData['AutoRenewal']) ? $accountData['AutoRenewal'] : '';
			$AccountServiceContract['ContractTerm'] = isset($accountData['ContractType']) ? $accountData['ContractType'] : '';
			$ServiceTitle = isset($accountData['ServiceTitle']) ? $accountData['ServiceTitle'] : '';

			if (!empty($AccountServiceContract['ContractStartDate']) && empty($AccountServiceContract['ContractEndDate'])) {
				return Response::json(["ErrorMessage"=>Codes::$Code1001[1]],Codes::$Code1001[0]);
			}
			if (!empty($AccountServiceContract['ContractStartDate']) && !empty($AccountServiceContract['ContractEndDate'])) {
				$checkDate = strtotime($AccountServiceContract['ContractStartDate']);
				//Log::info('createAccountService:Add Product Service123.' . $checkDate);
				if (empty($checkDate)) {
					return Response::json(["ErrorMessage"=>Codes::$Code1022[1]],Codes::$Code1022[0]);
				}
				$checkDate = strtotime($AccountServiceContract['ContractEndDate']);
				if (empty($checkDate)) {
					return Response::json(["ErrorMessage"=>Codes::$Code1022[1]],Codes::$Code1022[0]);
				}

				if ($AccountServiceContract['ContractStartDate'] > $AccountServiceContract['ContractEndDate']) {
					return Response::json(["ErrorMessage"=>Codes::$Code1002[1]],Codes::$Code1002[0]);
				}

				if (!empty($AccountServiceContract['ContractTerm']) && ($AccountServiceContract['ContractTerm'] < 1 || $AccountServiceContract['ContractTerm'] > 5)) {
					return Response::json(["ErrorMessage"=>Codes::$Code1003[1]],Codes::$Code1003[0]);
				}
				if (!empty($AccountServiceContract['AutoRenewal']) && ($AccountServiceContract['AutoRenewal'] != 0 && $AccountServiceContract['AutoRenewal'] != 1)) {
					return Response::json(["ErrorMessage"=>Codes::$Code1004[1]],Codes::$Code1004[0]);
				}
			}


			$rules = array(
				'AccountNo' =>      'required_without_all:AccountDynamicField,AccountID',
				'AccountID' =>      'required_without_all:AccountDynamicField,AccountNo',
				'AccountDynamicField' =>      'required_without_all:AccountNo,AccountID',
				'NumberPurchased'=>'required',

			);


			$validator = Validator::make($data, $rules);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
			}

			if (!empty($accountData['AccountDynamicField'])) {
				$AccountIDRef = '';
				$AccountIDRef = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
				if (empty($AccountIDRef)) {
					return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
				}
				$data['AccountID'] = $AccountIDRef;
			}

			if (!empty($data['AccountNo'])) {
				$Account = Account::where(array('Number' => $data['AccountNo'],'CompanyId' => $CompanyID))->first();
			}else {
				$Account = Account::find($data['AccountID']);
			}
			if (!$Account) {
				return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
			}


			$CompanyID = $Account->CompanyId;
			$DefaultSubscriptionIDSave = $DefaultSubscriptionID;
			$DefaultSubscriptionPackageIDSave = $DefaultSubscriptionPackageID;
			$DefaultSubscriptionID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_ID'])->pluck('Value');
			$DefaultSubscriptionPackageID = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'DEFAULT_SUBSCRIPTION_PACKAGE_ID'])->pluck('Value');
			if (empty($DefaultSubscriptionID)) {
				$CompanyConfigurationSave['CompanyID'] = $CompanyID;
				$CompanyConfigurationSave['Key'] = 'DEFAULT_SUBSCRIPTION_ID';
				$CompanyConfigurationSave['Value'] = $DefaultSubscriptionIDSave;
				CompanyConfiguration::create($CompanyConfigurationSave);
				$DefaultSubscriptionID = $DefaultSubscriptionIDSave;
			}

			if (empty($DefaultSubscriptionPackageID)) {
				$CompanyConfigurationSave['CompanyID'] = $CompanyID;
				$CompanyConfigurationSave['Key'] = 'DEFAULT_SUBSCRIPTION_PACKAGE_ID';
				$CompanyConfigurationSave['Value'] = $DefaultSubscriptionPackageIDSave;
				CompanyConfiguration::create($CompanyConfigurationSave);
				$DefaultSubscriptionPackageID = $DefaultSubscriptionPackageIDSave;
			}

			$ServiceTemaplateReference = '';
			$ProductCountryPrefix = '';



			$NumberPurchasedRef=json_decode(json_encode($data['NumberPurchased']),true);

			//Log::info('CreateAccountService:$NumberPurchasedRef .' . count($NumberPurchasedRef));

			$NumberPurchaseds = [];
			for ($i =0; $i <count($NumberPurchasedRef);$i++) {
				$NumberPurchased = $NumberPurchasedRef[$i];
				//Log::info('CreateAccountService:$NumberPurchasedRef .' . $NumberPurchased["Number"]);
				$ServiceTemaplateReference = ServiceTemplate::findServiceTemplateByDynamicField($NumberPurchased['NumberDynamicField']);
				if (empty($ServiceTemaplateReference)) {
					return Response::json(array("ErrorMessage" => Codes::$Code1021[1]),Codes::$Code1021[0]);
				}

				$ServiceTemaplateReference = ServiceTemplate::where(array('ServiceTemplateId' => $ServiceTemaplateReference,'CompanyID' => $CompanyID))->first();

				if (!isset($ServiceTemaplateReference) || $ServiceTemaplateReference == '') {
					return Response::json(array("ErrorMessage" => Codes::$Code1021[1]),Codes::$Code1021[0]);
				}

				if (!empty($data['OrderTypeId'])) {
					$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['OrderTypeId']])->count();
					if ($InboundRateTableReference > 1) {
						return Response::json(["ErrorMessage" => Codes::$Code1009[1]],Codes::$Code1009[0]);
					}
					$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['OrderTypeId']])->pluck('RateTableId');
				}else {
					$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->count();
					if ($InboundRateTableReference > 1) {
						return Response::json(["ErrorMessage" => Codes::$Code1009[1]],Codes::$Code1009[0]);
					}
					$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->pluck('RateTableId');
				}

				//Log::info('$InboundRateTableReference' . $InboundRateTableReference . ' ' . $data['OrderTypeId']);

				$ProductCountry = Country::where(array('Country' => $ServiceTemaplateReference->country))->first();
				if (!isset($ProductCountry) || $ProductCountry == '') {
					return Response::json(array("ErrorMessage" => Codes::$Code1046[1]),Codes::$Code1046[0]);
				}

				if(CLIRateTable::where(array('CompanyID'=>$CompanyID, 'CLI'=>$NumberPurchased["Number"],
					'AccountID'=>$Account->AccountID,'Status'=>1))->count()){
					//$AccountID = CLIRateTable::where(array('CompanyID'=>$CompanyID,'CLI'=>$data['NumberPurchased']))->pluck('AccountID');
					//$message .= $data['NumberPurchased'].' already exist against '.Account::getCompanyNameByID($AccountID).'.<br>';
					//$message = 'Following CLI already exists.<br>'.$message;
					return Response::json(array("ErrorMessage" => Codes::$Code1008[1]),Codes::$Code1008[0]);
				}

				if (!empty($NumberPurchased['PackageDynamicField'])) {
					$PackagedataRecord =  Package::findPackageByDynamicField($NumberPurchased['PackageDynamicField']);
					if (empty($PackagedataRecord)) {
						return Response::json(["ErrorMessage" => Codes::$Code1031[1]], Codes::$Code1031[0]);
					}
					$PackagedataRecord = Package::where(array('PackageId' => $PackagedataRecord,'CompanyID' => $CompanyID))->first();

					if (!isset($PackagedataRecord) || $PackagedataRecord == '') {
						return Response::json(["ErrorMessage" => Codes::$Code1031[1]], Codes::$Code1031[0]);
					}
					//$PackagedataRecord = Package::find($PackagedataRecord);
					$NumberPurchased["PackageID"] = $PackagedataRecord["PackageId"];
					$NumberPurchased["PackageRateTableID"] = $PackagedataRecord["RateTableId"];
				}
				$NumberPurchased["Status"] = 1;

				if (!isset($NumberPurchased['NumberStartDate']) || empty($NumberPurchased['NumberStartDate'])) {
					return Response::json(["ErrorMessage"=>Codes::$Code1038[1]],Codes::$Code1038[0]);
				}
				if (isset($NumberPurchased['NumberEndDate']) && !empty($NumberPurchased['NumberEndDate'])) {
					if ($NumberPurchased['NumberStartDate'] > $NumberPurchased['NumberEndDate']) {
						return Response::json(["ErrorMessage" => Codes::$Code1002[1]], Codes::$Code1002[0]);
					}
				}

				if (!isset($NumberPurchased['PackageStartDate']) || empty($NumberPurchased['PackageStartDate'])) {
					return Response::json(["ErrorMessage"=>Codes::$Code1040[1]],Codes::$Code1040[0]);
				}
				if (isset($NumberPurchased['PackageEndDate'])&& !empty($NumberPurchased['PackageEndDate'])) {
					if ($NumberPurchased['PackageStartDate'] > $NumberPurchased['PackageEndDate']) {
						return Response::json(["ErrorMessage" => Codes::$Code1002[1]], Codes::$Code1002[0]);
					}
				}

				//if (count($NumberPurchaseds) == 0) {
				//	$NumberPurchaseds[count($NumberPurchaseds)] = $NumberPurchased;
				//}else {
				$NumberPurchaseds[count($NumberPurchaseds)] = $NumberPurchased;
				//}


				//Log::info('NumberPurchased CLI and Package Description' . print_r($NumberPurchaseds,true));
			}




			//Log::info('ServiceTemplateId' . $ServiceTemaplateReference->ServiceTemplateId . ' ' . $ProductCountryPrefix);




			$DynamicFieldIDs = '';
			$DynamicFieldsExists=  DynamicFields::where('Type', 'subscription')->get();
			foreach ($DynamicFieldsExists as $DynamicFieldsExist) {
				$DynamicFieldIDs = $DynamicFieldIDs .$DynamicFieldsExist["DynamicFieldsID"] . ",";
			}
			//Log::info('update $DynamicFieldIDs.' . $DynamicFieldIDs);
			$DynamicFieldIDs = explode(',', $DynamicFieldIDs);

			DB::beginTransaction();






			$cliRateTableID = 0;




			$VendorIDDIDRateList = '';

			if (count($NumberPurchaseds) > 0) {
				$AccountSubscriptionDB = BillingSubscription::where(array('SubscriptionID' => $DefaultSubscriptionID))->first();
				//Log::info('update $DynamicFieldIDs12.' . $AccountSubscriptionDB);
				if (!isset($AccountSubscriptionDB) || $AccountSubscriptionDB == '') {
					return Response::json(["ErrorMessage" => Codes::$Code1005[1]], Codes::$Code1005[0]);
				}

				for ($i = 0; $i < count($NumberPurchaseds); $i++) {
					$NumberPurchased = $NumberPurchaseds[$i];
					$VendorIDDIDRateList = '';
					Log::info('CreateAccountService:$NumberPurchasedRef .' . print_r($NumberPurchased,true));
					$ServiceTemaplateReference = ServiceTemplate::findServiceTemplateByDynamicField($NumberPurchased['NumberDynamicField']);
					$ServiceTemaplateReference = ServiceTemplate::where(array('ServiceTemplateId' => $ServiceTemaplateReference,'CompanyID' => $CompanyID))->first();
					if (!empty($data['OrderTypeId'])) {
						$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['OrderTypeId']])->count();
						$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['OrderTypeId']])->pluck('RateTableId');
					}else {
						$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->count();
						$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->pluck('RateTableId');
					}

					//Log::info('$InboundRateTableReference' . $InboundRateTableReference . ' ' . $data['OrderTypeId']);
					if (!empty($ServiceTemaplateReference->ServiceId)) {


						Log::info('AccountServiceID Create');
						$servicedata['ServiceID'] = $ServiceTemaplateReference->ServiceId;
						$servicedata['AccountID'] = $Account->AccountID;
						$servicedata['CompanyID'] = $CompanyID;
						$servicedata["ServiceTitle"] = $ServiceTitle;
						$AccountService = AccountService::create($servicedata);
						//}
						//Log::info('AccountServiceID ' . $AccountService->AccountServiceID);


						//Log::info('AccountServiceID new' . $AccountService->AccountServiceID);
						$AccountServiceContract["AccountServiceID"] = $AccountService->AccountServiceID;
						$AccountServiceContract["Duration"] = empty($AccountServiceContract['ContractDuration']) ? $ServiceTemaplateReference->ContractDuration : $AccountServiceContract['ContractDuration'];
						$AccountServiceContract["ContractReason"] = empty($AccountServiceContract['ContractReason']) ? $ServiceTemaplateReference->CancellationFee : $AccountServiceContract['ContractReason'];
						$AccountServiceContract["AutoRenewal"] = empty($AccountServiceContract["AutoRenewal"]) ? $ServiceTemaplateReference->AutomaticRenewal : $AccountServiceContract["AutoRenewal"];
						$AccountServiceContract["ContractTerm"] = empty($AccountServiceContract["ContractTerm"]) ? $ServiceTemaplateReference->CancellationCharges : $AccountServiceContract["ContractTerm"];
						//Log::info('AccountServiceContract Done' . print_r($AccountServiceContract, true));
						AccountServiceContract::create($AccountServiceContract);
						//Log::info('AccountServiceContract Done');
					}

					$OutboundDiscountPlan = $ServiceTemaplateReference->OutboundDiscountPlanId;
					$InboundDiscountPlan = $ServiceTemaplateReference->InboundDiscountPlanId;
					//Log::info('ServiceTemaplateReference OutboundDiscountPlan and InboundDiscountPlan' .
					//	$ServiceTemaplateReference->ServiceTemplateId . ' ' . $OutboundDiscountPlan . ' ' . $InboundDiscountPlan);

					$AccountSubscriptionID = 0;
					$AccountName = '';
					$AccountCLI = '';
					$SubscriptionDiscountPlanID = 0;
					if (!empty($OutboundDiscountPlan)) {
						//	$AccountDiscountPlanSearch = AccountDiscountPlan::where(array('AccountID' => $Account->AccountID,'Type' => AccountDiscountPlan::OUTBOUND))->first();
						$AccountDiscountPlan['AccountID'] = $Account->AccountID;
						$AccountDiscountPlan['DiscountPlanID'] = $OutboundDiscountPlan;
						$AccountDiscountPlan['Type'] = AccountDiscountPlan::OUTBOUND;
						$AccountDiscountPlan['ServiceID'] = $ServiceTemaplateReference->ServiceId;
						$AccountDiscountPlan['AccountSubscriptionID'] = $AccountSubscriptionID;
						$AccountDiscountPlan['AccountName'] = $AccountName;
						$AccountDiscountPlan['AccountCLI'] = $AccountCLI;
						$AccountDiscountPlan['SubscriptionDiscountPlanID'] = $SubscriptionDiscountPlanID;
						$AccountDiscountPlan['AccountServiceID'] = $AccountService->AccountServiceID;
						//$AccountDiscountPlanExists = AccountDiscountPlan::where(array('AccountID' => $Account->AccountID, 'Type' => AccountDiscountPlan::OUTBOUND))->count();
						//if ($AccountDiscountPlanExists == 0) {
						//Log::info('Account Discount Plan ' . print_r($AccountDiscountPlan,true));
						//if (isset($AccountDiscountPlanSearch)) {
						//	$AccountDiscountPlanSearch->update($AccountDiscountPlan);
						//}else {
						AccountDiscountPlan::create($AccountDiscountPlan);
						//}
						//} else {
						//	AccountDiscountPlan::where(array('AccountID' => $Account->AccountID, 'Type' => AccountDiscountPlan::OUTBOUND))
						//		->update($AccountDiscountPlan);
						//}
					}

					if (!empty($InboundDiscountPlan)) {
						//$AccountInboudDiscountPlan = AccountDiscountPlan::where(array('AccountID' => $Account->AccountID,'Type'=>AccountDiscountPlan::INBOUND))->first();
						$AccountDiscountPlan['AccountID'] = $Account->AccountID;
						$AccountDiscountPlan['ServiceID'] = $ServiceTemaplateReference->ServiceId;
						$AccountDiscountPlan['AccountSubscriptionID'] = $AccountSubscriptionID;
						$AccountDiscountPlan['AccountName'] = $AccountName;
						$AccountDiscountPlan['AccountCLI'] = $AccountCLI;
						$AccountDiscountPlan['SubscriptionDiscountPlanID'] = $SubscriptionDiscountPlanID;
						$AccountDiscountPlan['Type'] = AccountDiscountPlan::INBOUND;
						$AccountDiscountPlan['DiscountPlanID'] =$InboundDiscountPlan;
						$AccountDiscountPlan['AccountServiceID'] = $AccountService->AccountServiceID;
						//$AccountDiscountPlanExists = AccountDiscountPlan::where(array('AccountID' => $Account->AccountID, 'Type' => AccountDiscountPlan::INBOUND))->count();
						//if ($AccountDiscountPlanExists == 0) {
						//if (isset($AccountInboudDiscountPlan)) {
						//	$AccountInboudDiscountPlan->update($AccountDiscountPlan);
						//}else {
						AccountDiscountPlan::create($AccountDiscountPlan);
						//}

						//}else {
						//	AccountDiscountPlan::where(array('AccountID' => $Account->AccountID,'Type'=>AccountDiscountPlan::INBOUND))
						//		->update($AccountDiscountPlan);
						//}

					}



					$inbounddata = array();
					if (!empty($InboundRateTableReference)) {
						$inbounddata['CompanyID'] = $CompanyID;
						$inbounddata['AccountID'] = $Account->AccountID;
						$inbounddata['ServiceID'] = $ServiceTemaplateReference->ServiceId;
						$inbounddata['RateTableID'] = $InboundRateTableReference;
						$inbounddata['AccountServiceID'] = $AccountService->AccountServiceID;
						$inbounddata['Type'] = AccountTariff::INBOUND;
					}

					$outbounddata = array();
					if (!empty($ServiceTemaplateReference->OutboundRateTableId)) {
						$outbounddata['CompanyID'] = $CompanyID;
						$outbounddata['AccountID'] = $Account->AccountID;
						$outbounddata['ServiceID'] = $ServiceTemaplateReference->ServiceId;
						$outbounddata['RateTableID'] = $ServiceTemaplateReference->OutboundRateTableId;
						$outbounddata['AccountServiceID'] = $AccountService->AccountServiceID;
						$outbounddata['Type'] = AccountTariff::OUTBOUND;
					}

					/*if(!empty($InboundRateTableReference)){
                        //$count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $Account->AccountID, 'ServiceID' => $inbounddata['ServiceID'], 'Type' => AccountTariff::INBOUND))->count();
                        //if(!empty($count) && $count>0){
                        //	AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $Account->AccountID, 'ServiceID' => $inbounddata['ServiceID'], 'Type' => AccountTariff::INBOUND))
                        //		->update(array('RateTableID' => $InboundRateTableReference, 'updated_at' => $date));
                        //}else{
                            $inbounddata['created_at'] = $date;
                            AccountTariff::create($inbounddata);
                        //}
                    }*/

					if(!empty($ServiceTemaplateReference->OutboundRateTableId)){
						//$count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $Account->AccountID, 'ServiceID' => $outbounddata['ServiceID'], 'Type' => AccountTariff::OUTBOUND))->count();
						//if(!empty($count) && $count>0){
						//	AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $Account->AccountID, 'ServiceID' => $outbounddata['ServiceID'], 'Type' => AccountTariff::OUTBOUND))
						//		->update(array('RateTableID' => $ServiceTemaplateReference->OutboundRateTableId, 'updated_at' => $date));
						//}else{
						$outbounddata['created_at'] = $date;
						AccountTariff::create($outbounddata);
						//}
					}
					$ProductCountry = Country::where(array('Country' => $ServiceTemaplateReference->country))->first();
					if (substr($ServiceTemaplateReference->prefixName,0,1) == "0") {
						$ProductCountryPrefix = $ProductCountry->Prefix . substr($ServiceTemaplateReference->prefixName,1,strlen($ServiceTemaplateReference->prefixName));
					} else {
						$ProductCountryPrefix = $ProductCountry->Prefix .  empty($ServiceTemaplateReference->prefixName) ? "" : $ServiceTemaplateReference->prefixName;
					}

					$SubscriptionSequence = 0;
					$AccountSubscriptionLast = AccountSubscription::where(array('AccountID' => $Account->AccountID,
						'AccountServiceID'=> $AccountService->AccountServiceID))
						->orderByRaw('SequenceNo desc')
						->first();
					if (isset($AccountSubscriptionLast)) {
						$SubscriptionSequence = $AccountSubscriptionLast["SequenceNo"];
					}
					$AccountSubscriptionTemplates = ServiceTemapleSubscription::where(array('ServiceTemplateID' => $ServiceTemaplateReference->ServiceTemplateId))->get();
					if (isset($AccountSubscriptionTemplates) && count($AccountSubscriptionTemplates) > 0) {
						foreach ($AccountSubscriptionTemplates as $AccountSubscriptionTemplate) {
							$AccountSubscriptionExisting = AccountSubscription::where(array('AccountID' => $Account->AccountID, 'SubscriptionID' => $AccountSubscriptionTemplate["SubscriptionId"]))->first();
							//Log::info('Subsctiption IDs from template.' . $AccountSubscriptionTemplate["SubscriptionId"]);
							$AccountSubscriptionDB = BillingSubscription::where(array('SubscriptionID' => $AccountSubscriptionTemplate["SubscriptionId"]))->first();
							$this->createAccountSubscription($Account,$AccountSubscriptionDB,
								$date,$ServiceTemaplateReference,$AccountService,
								$AccountSubscriptionTemplate["SubscriptionId"],$DynamicFieldIDs,++$SubscriptionSequence);
						}
					}

					$AccountAuthenticate = array();
					$AccountAuthenticate['CustomerAuthRule'] = 'CLI';
					$AccountAuthenticate['CustomerAuthValue'] = '';
					$AccountAuthenticate['AccountID'] = $Account->AccountID;
					$AccountAuthenticate['CompanyID'] = $CompanyID;
					$AccountAuthenticate['ServiceID'] = $ServiceTemaplateReference->ServiceId;
					AccountAuthenticate::insert($AccountAuthenticate);


					$rate_tables['CLI'] = $NumberPurchased["Number"];
					if (!empty($InboundRateTableReference)) {
						$rate_tables['RateTableID'] = $InboundRateTableReference;
					}

					$rate_tables['AccountID'] = $Account->AccountID;
					$rate_tables['CompanyID'] = $CompanyID;
					$rate_tables['CityTariff'] = $ServiceTemaplateReference->city_tariff;
					$rate_tables['AccountServiceID'] = $AccountService->AccountServiceID;
					if (!empty($NumberPurchased["PackageID"])) {
						$rate_tables['PackageID'] = $NumberPurchased["PackageID"];
					}
					if (!empty($NumberPurchased["PackageRateTableID"])) {
						$rate_tables['PackageRateTableID'] = $NumberPurchased["PackageRateTableID"];
					}

					$rate_tables['Status'] = $NumberPurchased["Status"];
					if (!empty($ServiceTemaplateReference->ServiceId)) {
						$rate_tables['ServiceID'] = $ServiceTemaplateReference->ServiceId;
					}
					$rate_tables['DIDCategoryID'] = $data['OrderTypeId'];



					if (!empty($DefaultSubscriptionID) && !empty($InboundRateTableReference)) {
						$RateTableDIDRates = RateTableDIDRate::
						Join('tblRate', 'tblRateTableDIDRate.RateID', '=', 'tblRate.RateID')
							->select(['tblRateTableDIDRate.OneOffCost', 'tblRateTableDIDRate.MonthlyCost',
								'tblRateTableDIDRate.OneOffCostCurrency','tblRateTableDIDRate.MonthlyCostCurrency','tblRateTableDIDRate.VendorID']);
						$RateTableDIDRates = $RateTableDIDRates->whereRaw('\'' . $ProductCountryPrefix . '\'' . ' like  CONCAT(tblRate.Code,"%")');
						$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.CityTariff" => $ServiceTemaplateReference->city_tariff]);
						$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.RateTableId" => $InboundRateTableReference]);
						$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.ApprovedStatus" => 1]);
						$RateTableDIDRates = $RateTableDIDRates->whereRaw("tblRateTableDIDRate.EffectiveDate <= NOW()");
						$RateTableDIDRates = $RateTableDIDRates->whereRaw("tblRateTableDIDRate.MonthlyCost is not null");
						//Log::info('$RateTableDIDRates CLI.' . $RateTableDIDRates->toSql());
						$RateTableDIDRates = $RateTableDIDRates->get();
						//$RateTableDIDRates = RateTableDIDRate::where(array('CityTariff' => $ServiceTemaplateReference->city_tariff))->get();
						foreach ($RateTableDIDRates as $RateTableDIDRate) {
							$this->createAccountSubscriptionFromRateTable($Account, $AccountSubscriptionDB,
								$NumberPurchased["NumberStartDate"],$NumberPurchased["NumberEndDate"] ,$ServiceTemaplateReference, $AccountService,
								$DefaultSubscriptionID, $DynamicFieldIDs,
								$RateTableDIDRate, $NumberPurchased["NumberDescription"],++$SubscriptionSequence);
							$VendorIDDIDRateList = $RateTableDIDRate["VendorID"];
						}
					}

					$rate_tables['Prefix'] = $ProductCountryPrefix;
					$rate_tables['VendorID'] = $VendorIDDIDRateList;
					$rate_tables['NoType'] = $ServiceTemaplateReference->accessType;
					CLIRateTable::insert($rate_tables);

					if (!empty($DefaultSubscriptionPackageID)) {



						$RateTablePKGRates = RateTablePKGRate::
						Join('tblRate', 'tblRateTablePKGRate.RateID', '=', 'tblRate.RateID')
							->select(['tblRateTablePKGRate.OneOffCost', 'tblRateTablePKGRate.MonthlyCost',
								'tblRateTablePKGRate.OneOffCostCurrency','tblRateTablePKGRate.MonthlyCostCurrency']);
						$RateTablePKGRates = $RateTablePKGRates->where(["tblRate.Code" => $PackagedataRecord["Name"]]);
						$RateTablePKGRates = $RateTablePKGRates->where(["tblRateTablePKGRate.RateTableId" => $PackagedataRecord["RateTableId"]]);
						$RateTablePKGRates = $RateTablePKGRates->where(["tblRateTablePKGRate.ApprovedStatus" => 1]);
						$RateTablePKGRates = $RateTablePKGRates->whereRaw("tblRateTablePKGRate.EffectiveDate <= NOW()");
						$RateTablePKGRates = $RateTablePKGRates->whereRaw("tblRateTablePKGRate.MonthlyCost is not null");
						//Log::info('Package $RateTablePkgRates.' . $RateTablePKGRates->toSql());
						$RateTablePKGRates = $RateTablePKGRates->get();
						//$RateTableDIDRates = RateTableDIDRate::where(array('CityTariff' => $ServiceTemaplateReference->city_tariff))->get();
						foreach ($RateTablePKGRates as $RateTablePKGRate) {
							$this->createAccountSubscriptionFromRateTable($Account, $AccountSubscriptionDB,
								$NumberPurchased["PackageStartDate"],$NumberPurchased["PackageEndDate"], $ServiceTemaplateReference, $AccountService,
								$DefaultSubscriptionPackageID, $DynamicFieldIDs,
								$RateTablePKGRate, $NumberPurchased["PackageDescription"],++$SubscriptionSequence);
						}
					}

				}
			}


			$message = "Account Service Successfully Added";




			DB::commit();

			return Response::json(json_decode('{}'),Codes::$Code200[0]);


		} catch (Exception $ex) {
			DB::rollback();
			Log::info('createAccountService:Exception.' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage" => Codes::$Code500[1]],Codes::$Code500[0]);
		}
	}



	public function createAccountSubscriptionForChangePackage($Account,$AccountSubscriptionDB,
														   $startDate,$endDate,$AccountService,$SubscriptionId,
														   $DynamicFieldIDs,$RateTableDIDRate,$InvoiceLineDescriptionAPI,$SubscriptionSequence) {


		//Log::info('$InvoiceLineDescriptionAPI .' . $InvoiceLineDescriptionAPI);
		$monthly = $RateTableDIDRate["MonthlyCost"];
		$InvoiceLineDescription = empty($InvoiceLineDescriptionAPI) ? $AccountSubscriptionDB["InvoiceLineDescription"] : $InvoiceLineDescriptionAPI;
		//Log::info('$InvoiceLineDescriptionAPI .' . $InvoiceLineDescription);
		$AccountSubscription["AccountID"] = $Account->AccountID;
		$AccountSubscription["SubscriptionID"] = $SubscriptionId;
		$AccountSubscription["InvoiceDescription"] = $InvoiceLineDescription;
		$AccountSubscription["Qty"] = 1;
		$AccountSubscription["StartDate"] = $startDate;
		if (!empty($endDate)) {
			$AccountSubscription["EndDate"] = $endDate;
		}
		//$AccountSubscription["ExemptTax"] =  $AccountSubscriptionDB[];
		$AccountSubscription["ActivationFee"] = $RateTableDIDRate["OneOffCost"];
		$AccountSubscription["AnnuallyFee"] = $monthly * 12;
		$AccountSubscription["QuarterlyFee"] = $monthly * 3;
		$AccountSubscription["MonthlyFee"] = $monthly;
		$AccountSubscription["WeeklyFee"] = $monthly / 30 * 7;
		$AccountSubscription["DailyFee"] = $monthly / 30;
		$AccountSubscription["SequenceNo"] =  $SubscriptionSequence;
		$AccountSubscription["ServiceID"] = $AccountService->ServiceID;
		$AccountSubscription["Status"] = 1;
		$AccountSubscription["AccountServiceID"] = $AccountService->AccountServiceID;
		$AccountSubscription["OneOffCurrencyID"] = $RateTableDIDRate->OneOffCostCurrency;
		$AccountSubscription["RecurringCurrencyID"] = $RateTableDIDRate->MonthlyCostCurrency;

		//$AccountSubscription["DiscountAmount"] =  $AccountSubscriptionDB[];
		//$AccountSubscription["DiscountType"] =  $AccountSubscriptionDB[];

		$AccountSubscriptionQueryDB = AccountSubscription::create($AccountSubscription);




	}

	public function createAccountSubscriptionFromRateTable($Account,$AccountSubscriptionDB,
											  $startDate,$endDate,$ServiceTemaplateReference,$AccountService,$SubscriptionId,
											  $DynamicFieldIDs,$RateTableDIDRate,$InvoiceLineDescriptionAPI,$SubscriptionSequence) {


		//Log::info('$InvoiceLineDescriptionAPI .' . $InvoiceLineDescriptionAPI);
		$monthly = $RateTableDIDRate["MonthlyCost"];
		$InvoiceLineDescription = empty($InvoiceLineDescriptionAPI) ? $AccountSubscriptionDB["InvoiceLineDescription"] : $InvoiceLineDescriptionAPI;
		//Log::info('$InvoiceLineDescriptionAPI .' . $InvoiceLineDescription);
		$AccountSubscription["AccountID"] = $Account->AccountID;
		$AccountSubscription["SubscriptionID"] = $SubscriptionId;
		$AccountSubscription["InvoiceDescription"] = $InvoiceLineDescription;
		$AccountSubscription["Qty"] = 1;
		$AccountSubscription["StartDate"] = $startDate;
		if (!empty($endDate)) {
			$AccountSubscription["EndDate"] = $endDate;
		}
		//$AccountSubscription["ExemptTax"] =  $AccountSubscriptionDB[];
		$AccountSubscription["ActivationFee"] = $RateTableDIDRate["OneOffCost"];
		$AccountSubscription["AnnuallyFee"] = $monthly * 12;
		$AccountSubscription["QuarterlyFee"] = $monthly * 3;
		$AccountSubscription["MonthlyFee"] = $monthly;
		$AccountSubscription["WeeklyFee"] = $monthly / 30 * 7;
		$AccountSubscription["DailyFee"] = $monthly / 30;
		$AccountSubscription["SequenceNo"] =  $SubscriptionSequence;
		$AccountSubscription["ServiceID"] = $ServiceTemaplateReference->ServiceId;
		$AccountSubscription["Status"] = 1;
		$AccountSubscription["AccountServiceID"] = $AccountService->AccountServiceID;
		$AccountSubscription["OneOffCurrencyID"] = $RateTableDIDRate->OneOffCostCurrency;
		$AccountSubscription["RecurringCurrencyID"] = $RateTableDIDRate->MonthlyCostCurrency;

		//$AccountSubscription["DiscountAmount"] =  $AccountSubscriptionDB[];
		//$AccountSubscription["DiscountType"] =  $AccountSubscriptionDB[];

		$AccountSubscriptionQueryDB = AccountSubscription::create($AccountSubscription);


		$DynamicSubscrioptionFields=  DynamicFieldsValue::where('ParentID', $AccountSubscriptionDB["SubscriptionID"])
			->whereIn('DynamicFieldsID',$DynamicFieldIDs);
		Log::info('update $DynamicFieldIDs.' . $DynamicSubscrioptionFields->toSql());
		$DynamicSubscrioptionFields = $DynamicSubscrioptionFields->get();
		Log::info('update $DynamicFieldIDs.' . count($DynamicSubscrioptionFields));

		if (count($DynamicSubscrioptionFields) > 0) {
			AccountSubsDynamicFields::where(array('AccountSubscriptionID' => $AccountSubscriptionQueryDB["AccountSubscriptionID"]))->delete();
		}
		$AccountSubsDynamicFields = [];
		foreach ($DynamicSubscrioptionFields as $DynamicSubscrioptionField) {
			$AccountSubsDynamicFields["AccountSubscriptionID"] = $AccountSubscriptionQueryDB["AccountSubscriptionID"];
			$AccountSubsDynamicFields["AccountID"] = $Account->AccountID;
			$AccountSubsDynamicFields["DynamicFieldsID"] = $DynamicSubscrioptionField["DynamicFieldsID"];
			$AccountSubsDynamicFields["FieldValue"] = $DynamicSubscrioptionField["FieldValue"];
			$AccountSubsDynamicFields["FieldOrder"] = $DynamicSubscrioptionField["FieldOrder"];
			AccountSubsDynamicFields::insert($AccountSubsDynamicFields);
		}
	}
	public function createAccountSubscription($Account,$AccountSubscriptionDB,
											  $date,$ServiceTemaplateReference,$AccountService,
											  $SubscriptionId,$DynamicFieldIDs,$SubscriptionSequence) {



		$AccountSubscription["AccountID"] = $Account->AccountID;
		$AccountSubscription["SubscriptionID"] = $AccountSubscriptionDB["SubscriptionID"];
		$AccountSubscription["InvoiceDescription"] = $AccountSubscriptionDB["InvoiceLineDescription"];
		$AccountSubscription["Qty"] = 1;
		$AccountSubscription["StartDate"] = $date;
		//$AccountSubscription["EndDate"] = $date;
		//$AccountSubscription["ExemptTax"] =  $AccountSubscriptionDB[];
		$AccountSubscription["ActivationFee"] = $AccountSubscriptionDB["ActivationFee"];
		$AccountSubscription["AnnuallyFee"] = $AccountSubscriptionDB["AnnuallyFee"];
		$AccountSubscription["QuarterlyFee"] = $AccountSubscriptionDB["QuarterlyFee"];
		$AccountSubscription["MonthlyFee"] = $AccountSubscriptionDB["MonthlyFee"];
		$AccountSubscription["WeeklyFee"] = $AccountSubscriptionDB["WeeklyFee"];
		$AccountSubscription["DailyFee"] = $AccountSubscriptionDB["DailyFee"];
		$AccountSubscription["SequenceNo"] =  $SubscriptionSequence;
		$AccountSubscription["ServiceID"] = $ServiceTemaplateReference->ServiceId;
		$AccountSubscription["Status"] = 1;
		$AccountSubscription["AccountServiceID"] = $AccountService->AccountServiceID;

		//$AccountSubscription["DiscountAmount"] =  $AccountSubscriptionDB[];
		//$AccountSubscription["DiscountType"] =  $AccountSubscriptionDB[];

		if (isset($AccountSubscriptionExisting) && $AccountSubscriptionExisting != '') {
			Log::info('AccountServiceID new 123' . $Account->AccountID . ' ' . $SubscriptionId);

			$AccountSubscriptionQueryDB = AccountSubscription::where(array('AccountID' => $Account->AccountID, 'SubscriptionID' => $SubscriptionId))
				->update($AccountSubscription);
			$AccountSubscriptionQueryDB = AccountSubscription::where(array('AccountID' => $Account->AccountID,
				'SubscriptionID' => $SubscriptionId))->first();
			Log::info('AccountServiceID new 123 ' . $AccountSubscriptionQueryDB["AccountSubscriptionID"]);
		} else {
			$AccountSubscriptionQueryDB = AccountSubscription::create($AccountSubscription);
		}


		$DynamicSubscrioptionFields=  DynamicFieldsValue::where('ParentID', $AccountSubscriptionDB["SubscriptionID"])
			->whereIn('DynamicFieldsID',$DynamicFieldIDs);
		Log::info('update $DynamicFieldIDs.' . $DynamicSubscrioptionFields->toSql());
		$DynamicSubscrioptionFields = $DynamicSubscrioptionFields->get();
		Log::info('update $DynamicFieldIDs.' . count($DynamicSubscrioptionFields));

		if (count($DynamicSubscrioptionFields) > 0) {
			AccountSubsDynamicFields::where(array('AccountSubscriptionID' => $AccountSubscriptionQueryDB["AccountSubscriptionID"]))->delete();
		}
		$AccountSubsDynamicFields = [];
		foreach ($DynamicSubscrioptionFields as $DynamicSubscrioptionField) {
			$AccountSubsDynamicFields["AccountSubscriptionID"] = $AccountSubscriptionQueryDB["AccountSubscriptionID"];
			$AccountSubsDynamicFields["AccountID"] = $Account->AccountID;
			$AccountSubsDynamicFields["DynamicFieldsID"] = $DynamicSubscrioptionField["DynamicFieldsID"];
			$AccountSubsDynamicFields["FieldValue"] = $DynamicSubscrioptionField["FieldValue"];
			$AccountSubsDynamicFields["FieldOrder"] = $DynamicSubscrioptionField["FieldOrder"];
			AccountSubsDynamicFields::insert($AccountSubsDynamicFields);
		}
	}



	//cacc
	public function createAccount() {
		$post_vars = '';
		$accountData = [];
		$PaymentProfile = [];
		try {

			try {
				$post_vars = json_decode(file_get_contents("php://input"));
				$accountData=json_decode(json_encode($post_vars),true);
				$countValues = count($accountData);
				if ($countValues == 0) {
					return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
				}
			}catch(Exception $ex) {
				Log::info('Exception in Routing API.Invalid JSON' . $ex->getTraceAsString());
				return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
			}

			$ServiceID = 0;
			$LogonUser = User::getUserInfo();
			$CompanyID = $LogonUser["CompanyID"];
			Log::info('createAccount:User:.CompanyID' . $CompanyID);
			$CreatedBy = User::get_user_full_name();
			$ResellerData = [];
			$AccountPaymentAutomation = [];
			$AccountReferenceObj = '';
			$DynamicFields = '';
			$date = date('Y-m-d H:i:s.000');
			$DynamicFieldsExist = '';
			$Reseller = [];

			$data['Number']   = isset($accountData['AccountNo']) ? $accountData['AccountNo'] : '';
			$data['Phone']    = isset($accountData['Phone']) ? $accountData['Phone'] : '';
			$data['Address1'] = isset($accountData['Address1']) ? $accountData['Address1'] : '';
			$data['Address2'] = isset($accountData['Address2']) ? $accountData['Address2'] : '';
			$data['Address3'] = isset($accountData['Address3']) ? $accountData['Address3'] : '';
			$data['City'] 	  = isset($accountData['City']) ? $accountData['City'] : '';
			$data['Email']    = isset($accountData['Email']) ? $accountData['Email'] : '';
			$data['BillingAddress1'] = isset($accountData['BillingAddress1']) ? $accountData['BillingAddress1'] : '';
			$data['BillingAddress2'] = isset($accountData['BillingAddress2']) ? $accountData['BillingAddress2'] : '';
			$data['BillingAddress3'] = isset($accountData['BillingAddress3']) ? $accountData['BillingAddress3'] : '';
			$data['BillingPostCode'] = isset($accountData['BillingPostCode']) ? $accountData['BillingPostCode'] : '';
			$data['BillingCity'] = isset($accountData['BillingCity']) ? $accountData['BillingCity'] : '';
			$data['BillingCountry'] = isset($accountData['BillingCountryIso2']) ? $accountData['BillingCountryIso2'] : '';
			$data['DifferentBillingAddress'] = isset($accountData['DifferentBillingAddress']) ? $accountData['DifferentBillingAddress'] : '';
			$data['BillingEmail'] = isset($accountData['BillingEmail']) ? $accountData['BillingEmail'] : '';
			$data['PostCode'] = isset($accountData['PostCode']) ? $accountData['PostCode'] : '';
			$data['Owner'] = isset($accountData['OwnerID']) ? $accountData['OwnerID'] : '';
			$data['CurrencyId'] = isset($accountData['Currency']) ? $accountData['Currency'] : '';
			$data['Country'] = isset($accountData['CountryIso2']) ? $accountData['CountryIso2'] : '';
			$data['VatNumber'] = isset($accountData['VatNumber']) ? $accountData['VatNumber'] : '';
			$data['Language']= isset($accountData['LanguageIso2']) ? $accountData['LanguageIso2'] : '';
			$data['tags']= isset($accountData['tags']) ? $accountData['tags'] : '';
			$data['PartnerID']= isset($accountData['PartnerID']) ? $accountData['PartnerID'] : '';
			$data['AffiliateAccounts'] = isset($accountData['AffiliateAccounts']) ? $accountData['AffiliateAccounts'] : '';
			$data['DurationMonths'] = isset($accountData['DurationMonths']) ? $accountData['DurationMonths'] : '';
			$data['CommissionPercentage'] = isset($accountData['CommissionPercentage']) ? $accountData['CommissionPercentage'] : '';
			$data['AccountType'] = 1;
			$data['IsVendor'] = isset($accountData['IsVendor']);

			$rules = array(
				'CommissionPercentage' => 'numeric',
				'DurationMonths'       => 'numeric',
				'Email'                => 'email',
				'AutoTopup'            => 'numeric',
				'AutoOutpayment'       => 'numeric',
				'Active'               => 'numeric',
				'PayoutMethodID'       => 'numeric',
				'PaymentMethodID'      => 'numeric',
				'BillingTypeID'        => 'numeric'
			);

			$validator = Validator::make($accountData, $rules);
			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error){
					$errors .= $error."<br>";
				}
				return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
			}

			if (isset($accountData['Active'])) {
				if(in_array($accountData['Active'],[0,1])) {
					$data['Status'] = $accountData['Active'];
				} else {
					return Response::json(["ErrorMessage" => "Active value must be 0 or 1"],Codes::$Code1063[0]);
				}
			}

			if($data['DifferentBillingAddress'] === 0) {
				$data['BillingAddress1'] = $data['Address1'];
				$data['BillingAddress2'] = $data['Address2'];
				$data['BillingAddress3'] = $data['Address3'];
				$data['BillingCity']     = $data['City'];
				$data['BillingPostCode'] = '';
				$data['BillingCountry']  = $data['Country'];	
			}else if(!empty($data['BillingAddress1']) || !empty($data['BillingAddress2']) || !empty($data['BillingAddress3']) || !empty($data['BillingCity']) || !empty($data['BillingPostCode']) || !empty($accountData['BillingCountryIso2'])) {
				$data['DifferentBillingAddress'] = 1;
			}
			$ResellerOwner = '';
			if (!empty($accountData['AccountResellerDynamicField'])) {
				$AccountIDRef = '';
				$AccountIDRef = Account::findAccountBySIAccountRef($accountData['AccountResellerDynamicField']);
				if (empty($AccountIDRef)) {
					return Response::json(["ErrorMessage" => Codes::$Code1035[1]],Codes::$Code1035[0]);
				}
				$ResellerOwner = $AccountIDRef;
			}

			if (!empty($ResellerOwner)) {
				$Account = Account::find($ResellerOwner);
				if (!$Account || $Account['IsReseller'] != 1) {
					return Response::json(["ErrorMessage" => Codes::$Code1035[1]], Codes::$Code1035[0]);
				}
			}

			if(!empty($ResellerOwner) &&  $ResellerOwner>0){
				$Reseller = Reseller::where('AccountID',$ResellerOwner)->first();
				if (!isset($Reseller)) {
					return Response::json(["ErrorMessage" => Codes::$Code1035[1]],Codes::$Code1035[0]);
				}

				$ResellerCompanyID = $Reseller->ChildCompanyID;
				$ResellerUser = User::where('CompanyID',$ResellerCompanyID)->first();
				if (isset($ResellerUser)) {
					$ResellerUserID = $ResellerUser->UserID;
					$data['Owner'] = $ResellerUserID;
				}

				$CompanyID=$ResellerCompanyID;
			}

			$data['CompanyID'] = $CompanyID;

			try {
				$data['TimeZone'] = $accountData['AccountTimeZones'];
				TimeZone::$timeZones[$accountData['AccountTimeZones']];
			}catch(Exception $ex) {
				unset($data['TimeZone']);
			}

			if (!empty($accountData['IsVendor']) && ($accountData['IsVendor'] != 0 && $accountData['IsVendor'] != 1)) {
				return Response::json(["ErrorMessage" => Codes::$Code1025[1]],Codes::$Code1025[0]);
			}else {
				$data['IsVendor'] = isset($accountData['IsVendor']) ? $accountData['IsVendor'] : 0;
			}
			$data['IsCustomer'] = isset($accountData['IsCustomer']) ? $accountData['IsCustomer'] : 0;
			if (!empty($accountData['IsCustomer']) && ($accountData['IsCustomer'] != 0 && $accountData['IsCustomer'] != 1)) {
				return Response::json(["ErrorMessage" => Codes::$Code1024[1]],Codes::$Code1024[0]);
			}else {
				$data['IsReseller'] = 0;
			}

			if (!empty($accountData['IsPartner']) && ($accountData['IsPartner'] != 0 && $accountData['IsPartner'] != 1)) {
				return Response::json(["ErrorMessage" => Codes::$Code1023[1]],Codes::$Code1023[0]);
			} else {
				$data['IsReseller'] = isset($accountData['IsPartner']) ? $accountData['IsPartner'] : 0;
			}

			if (isset($accountData['IsAffiliateAccount']) && !empty($accountData['IsAffiliateAccount']) && ($accountData['IsAffiliateAccount'] != 0 && $accountData['IsAffiliateAccount'] != 1)) {
				return Response::json(["ErrorMessage" => Codes::$Code1065[1]],Codes::$Code1065[0]);

			}else {
				$data['IsAffiliateAccount'] = isset($accountData['IsAffiliateAccount']) ? $accountData['IsAffiliateAccount'] : 0;
			}

			if ($data['IsAffiliateAccount'] == 1) {
				$rules = [];
				$rules['AffiliateAccounts'] = 'required';
				

				$validator = Validator::make($data, $rules);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
				}


				if(!preg_match('/^[0-9,]+$/', $data['AffiliateAccounts'])){
					return Response::json(array("ErrorMessage" => Codes::$Code1066[1]),Codes::$Code1066[0]);
				}
			}

			$data['Billing'] = isset($data['Billing']) && $data['Billing'] == 1 ? 1 : 0;
			$data['created_by'] = $CreatedBy;
			$data['AccountType'] = 1;
			$data['AccountName'] = isset($accountData['AccountName']) ? trim($accountData['AccountName']) : '';
			$data['PaymentMethod'] = isset($accountData['PaymentMethodID']) ? $accountData['PaymentMethodID'] : '' ;
			$data['PayoutMethod'] = isset($accountData['PayoutMethodID']) ? $accountData['PayoutMethodID'] : '' ;


			$BankPaymentDetails['AccountNumber'] = isset($accountData['AccountNumber']) ? $accountData['AccountNumber'] : '' ;
			$BankPaymentDetails['RoutingNumber'] = isset($accountData['RoutingNumber']) ? $accountData['RoutingNumber'] : '' ;
			$BankPaymentDetails['AccountHolderType'] = isset($accountData['AccountHolderType']) ? $accountData['AccountHolderType'] : '' ;//company,individual
			$BankPaymentDetails['AccountHolderName'] = isset($accountData['AccountHolderName']) ? $accountData['AccountHolderName'] : '' ;

			$BankPaymentDetails['CardNumber'] = isset($accountData['CardNumber']) ? $accountData['CardNumber'] : '' ;
			$BankPaymentDetails['CardType'] = isset($accountData['CardType']) ? $accountData['CardType'] : '' ;//Discover,MasterCard,Visa
			$BankPaymentDetails['ExpirationMonth'] = isset($accountData['ExpirationMonth']) ? $accountData['ExpirationMonth'] : '' ;
			$BankPaymentDetails['ExpirationYear'] = isset($accountData['ExpirationYear']) ? $accountData['ExpirationYear'] : '' ;
			$BankPaymentDetails['NameOnCard'] = isset($accountData['NameOnCard']) ? $accountData['NameOnCard'] : '' ;
			$BankPaymentDetails['CVVNumber'] = isset($accountData['CVVNumber']) ? $accountData['CVVNumber'] : '' ;

			//stripe = credit stipeAch = bank
			if (isset($data['PaymentMethod']) && $data['PaymentMethod'] != '') {
				if ($data['PaymentMethod'] <0 || $data['PaymentMethod'] > count(AccountsApiController::$API_PaymentMethod)) {
					return Response::json(["ErrorMessage" => Codes::$Code1020[1]],Codes::$Code1020[0]);

				}
			}

			if (isset($data['PayoutMethod']) && $data['PayoutMethod'] != '') {
				if ($data['PayoutMethod'] <0 || $data['PayoutMethod'] > count(AccountsApiController::$API_PayoutMethod)) {
					return Response::json(["ErrorMessage" => Codes::$Code1058[1]],Codes::$Code1058[0]);

				}
			}

			if (!empty($data['PaymentMethod'])) {
				$data['PaymentMethod'] = AccountsApiController::$API_PaymentMethod[$data['PaymentMethod']];
			}

			if (!empty($data['PayoutMethod'])) {
				$data['PayoutMethod'] = AccountsApiController::$API_PayoutMethod[$data['PayoutMethod']];
			}

			if(isset($accountData['AutoTopup']) && $accountData['AutoTopup'] > 1){
				return Response::json(["ErrorMessage" => 'Auto Top Up Value Should Be 0 Or 1'],Codes::$Code400[0]);
			}
			if(isset($accountData['AutoOutpayment']) && $accountData['AutoOutpayment'] > 1){
				return Response::json(["ErrorMessage" => 'Auto Out payment Value Should Be 0 Or 1'],Codes::$Code400[0]);
			}

			$AccountPaymentAutomation['AutoTopup']= isset($accountData['AutoTopup']) ? $accountData['AutoTopup'] :'';
			$AccountPaymentAutomation['MinThreshold']= isset($accountData['MinThreshold']) ? $accountData['MinThreshold'] : '';
			$AccountPaymentAutomation['TopupAmount']= isset($accountData['TopupAmount']) ? $accountData['TopupAmount'] : '';
			$AccountPaymentAutomation['AutoOutpayment']= isset($accountData['AutoOutpayment']) ? $accountData['AutoOutpayment'] : '';
			$AccountPaymentAutomation['OutPaymentThreshold']= isset($accountData['OutPaymentThreshold']) ? $accountData['OutPaymentThreshold'] : '';
			$AccountPaymentAutomation['OutPaymentAmount']= isset($accountData['OutPaymentAmount']) ? $accountData['OutPaymentAmount'] : '';


			if (!empty($AccountPaymentAutomation['AutoTopup']) && $AccountPaymentAutomation['AutoTopup'] == 1) {
				$rules = [];
				$rules['MinThreshold'] = 'required|numeric';
				$rules['TopupAmount'] = 'required|numeric';
				$messages = array(
					'MinThreshold.required' => 'MinThreshold field is required if AutoTopup is ON',
					'TopupAmount.required' => 'TopupAmount field is required if AutoTopup is ON',
				);
				$validator = Validator::make($AccountPaymentAutomation, $rules, $messages);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);

				}
			}

			if (isset($data['PaymentMethod'])) {
				if ($data['PaymentMethod'] == "Stripe" || $data['PaymentMethod'] == "StripeACH") {
					$BankPaymentDetails['CardToken'] = isset($accountData['CardToken']) ? $accountData['CardToken'] : '' ;
					if ($data['PaymentMethod'] == "Stripe" && empty($BankPaymentDetails['CardToken'])) {
						$CardValidationResponse = AccountPayout::cardValidation($BankPaymentDetails);
						if ($CardValidationResponse["status"] == "failed") {
							return Response::json(["ErrorMessage" => $CardValidationResponse["message"]], Codes::$Code400[0]);
						}
						$CardType = array("Discover", "MasterCard", "Visa");
						if (!in_array($BankPaymentDetails['CardType'], $CardType)) {
							return Response::json(["ErrorMessage" => Codes::$Code1036[1]], Codes::$Code1036[0]);
						}
					} else if ($data['PaymentMethod'] == "StripeACH" && empty($BankPaymentDetails['CardToken'])) {
						$validator = Validator::make($BankPaymentDetails, AccountPayout::$AccountPayoutBankRules);
						if ($validator->fails()) {
							$errors = "";
							foreach ($validator->messages()->all() as $error) {
								$errors .= $error . "<br>";
							}
							return Response::json(["ErrorMessage" => $errors], Codes::$Code400[0]);

						}
						$AccountHolderType = array("individual", "company");
						if (!in_array($BankPaymentDetails['AccountHolderType'], $AccountHolderType)) {
							return Response::json(["ErrorMessage" => Codes::$Code1037[1]], Codes::$Code1037[0]);
						}
					}

				} elseif ($data['PaymentMethod'] == "Ingenico") {


					$rules = [];
					$rules = array(
						'CardToken'         => 'required',
						'CardHolderName'    => 'required',
						'ExpirationMonth'   => 'required|numeric',
						'ExpirationYear'    => 'required|numeric',
						'LastDigit'         => 'required|digits:4',
					);

					$PaymentProfile['CardToken'] = isset($accountData['CardToken']) ? $accountData['CardToken'] : '' ;
					$PaymentProfile['CardHolderName'] = isset($accountData['CardHolderName']) ? $accountData['CardHolderName'] : '' ;
					$PaymentProfile['ExpirationMonth'] = isset($accountData['ExpirationMonth']) ? $accountData['ExpirationMonth'] : '' ;
					$PaymentProfile['ExpirationYear'] = isset($accountData['ExpirationYear']) ? $accountData['ExpirationYear'] : '' ;
					$PaymentProfile['LastDigit'] = isset($accountData['LastDigit']) ? $accountData['LastDigit'] : '' ;
					$PaymentProfile['Title'] = isset($accountData['CardTitle']) ? $accountData['CardTitle'] : '' ;
					$validator = Validator::make($PaymentProfile, $rules);
					if ($validator->fails()) {
						$errors = "";
						foreach ($validator->messages()->all() as $error) {
							$errors .= $error . "<br>";
						}
						return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);

					}
				}else if ($data['PaymentMethod'] == "DirectDebit" || $data['PaymentMethod'] == "WireTransfer") {
					$rules = array(
						'BankAccount'       => 'required',
						'AccountHolderName' => 'required',
						'Title'             => 'required'

					);
					$messages = array(
						'Title.required' =>'The Title Field Is Required For Payment',
					);
					$PaymentProfile['BankAccount'] = isset($accountData['BankAccount']) ? $accountData['BankAccount'] : '' ;
					$PaymentProfile['BIC'] = isset($accountData['BIC']) ? $accountData['BIC'] : '' ;
					$PaymentProfile['AccountHolderName'] = isset($accountData['AccountHolderName']) ? $accountData['AccountHolderName'] : '' ;
					$PaymentProfile['MandateCode'] = isset($accountData['MandateCode']) ? $accountData['MandateCode'] : '' ;
					$PaymentProfile['Title'] = isset($accountData['Title']) ? $accountData['Title'] : '' ;

					$validator = Validator::make($PaymentProfile, $rules , $messages);
					if ($validator->fails()) {
						$errors = "";
						foreach ($validator->messages()->all() as $error){
							$errors .= $error."<br>";
						}

						return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
					}
				}
			}


			if (isset($data['PayoutMethod'])) {
				if ($data['PayoutMethod'] == "WireTransfer") {
					$rules = array(
						'BankAccount'       => 'required',
						'AccountHolderName' => 'required',
						'Title'             => 'required'

					);
					$messages = array(
						'Title.required' =>'The Title Field Is Required For Payout',
					);
					$PayoutProfile['BankAccount'] = isset($accountData['PayoutBankAccount']) ? $accountData['PayoutBankAccount'] : '' ;
					$PayoutProfile['BIC'] = isset($accountData['PayoutBIC']) ? $accountData['PayoutBIC'] : '' ;
					$PayoutProfile['AccountHolderName'] = isset($accountData['PayoutAccountHolderName']) ? $accountData['PayoutAccountHolderName'] : '' ;
					$PayoutProfile['MandateCode'] = isset($accountData['PayoutMandateCode']) ? $accountData['PayoutMandateCode'] : '' ;
					$PayoutProfile['Title'] = isset($accountData['PayoutTitle']) ? $accountData['PayoutTitle'] : '' ;

					$validator = Validator::make($PayoutProfile, $rules);
					if ($validator->fails()) {
						$errors = "";
						foreach ($validator->messages()->all() as $error){
							$errors .= $error."<br>";
						}

						return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
					}
				}
			}

			if (!empty($AccountPaymentAutomation['AutoOutpayment']) && $AccountPaymentAutomation['AutoOutpayment'] == 1) {
				$rules = [];
				$rules['OutPaymentThreshold'] = 'required|numeric';
				$rules['OutPaymentAmount'] = 'required|numeric';
				$messages = array(
					'OutPaymentThreshold.required' =>'OutPaymentThreshold field is required if AutoOutpayment is ON',
					'OutPaymentAmount.required' =>'OutPaymentAmount field is required if AutoOutpayment is ON',

				);
				$validator = Validator::make($AccountPaymentAutomation, $rules, $messages);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);

				}
			}

			if($data['IsCustomer'] == 1 || $data['AffiliateAccounts'] == 1){
				$ResellerID = DynamicFieldsValue::where(['DynamicFieldsID' => 93 , 'FieldValue' => $data['PartnerID']])->first();
				if(!$ResellerID){
					return Response::json(["ErrorMessage" => Codes::$Code1059[1]],Codes::$Code1059[0]);
				}

				$Reseller = Reseller::where('AccountID',$ResellerID->ParentID)->first();
				if(!$Reseller){
					return Response::json(["ErrorMessage" => 'Please enter the valid Partner ID'],Codes::$Code1059[0]);
				}

				$data['CompanyID'] = $Reseller->ChildCompanyID;
				$data['Owner']     = $Reseller->ResellerID;
			}

			// If Reseller on backend customer is on

			if($data['IsReseller'] == 0 && $data['IsCustomer'] == 0 && $data['IsVendor'] == 0 && $data['IsAffiliateAccount'] == 0){
				return Response::json(["ErrorMessage" => Codes::$Code1060[1]],Codes::$Code1060[0]);
			}

			if($data['IsReseller'] == 1 && ($data['IsCustomer'] == 1 || $data['IsVendor'] == 1 || $data['IsAffiliateAccount'] == 1)){
				return Response::json(["ErrorMessage" => Codes::$Code1061[1]],Codes::$Code1061[0]);
			}
			if($data['IsReseller']==1){
				$data['IsCustomer']         = 0;
				$data['IsVendor']           = 0;
				$data['IsAffiliateAccount'] = 0;
			}

			unset($data['ResellerOwner']);

			//when account varification is off in company setting then varified the account by default.
			$AccountVerification =  CompanySetting::getKeyVal('AccountVerification');

			if ( $AccountVerification != CompanySetting::ACCOUT_VARIFICATION_ON ) {
				$data['VerificationStatus'] = Account::VERIFIED;
			}

			if (strpbrk($data['AccountName'], '\/?*:|"<>')) {
				return Response::json(["ErrorMessage" => Codes::$Code1018[1]],Codes::$Code1018[0]);

			}


			if (empty($data['Number'])) {
				$data['Number'] = Account::getLastAccountNo();
			}
			$data['Number'] = trim($data['Number']);


			Account::$APIrules['AccountName'] = 'required';
			Account::$APIrules['Number'] = 'required';
			Account::$APIrules['BillingEmail'] = 'required';

			if($data['IsCustomer'] == 1 || $data['AffiliateAccounts'] == 1){
				Account::$APIrules['PartnerID'] = 'required|numeric';
			}

			$validator = Validator::make($data, Account::$APIrules, Account::$messages);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
			}

			$AccountName = Account::where(['AccountName'=>$data["AccountName"],'CompanyID'=>$CompanyID,'AccountType'=>1])->count();
			if ($AccountName > 0) {
				return Response::json(["ErrorMessage"=>Codes::$Code1029[1]],Codes::$Code400[0]);
			}

			$AccountNumber = Account::where(['Number'=>$data["Number"],'CompanyID'=>$CompanyID])->count();
			if ($AccountNumber > 0) {
				return Response::json(["ErrorMessage"=>Codes::$Code1030[1]],Codes::$Code400[0]);
			}

			$CustomerVal = false;
			$CustomerDynamicID = '';
			if (isset($accountData['AccountDynamicField']) && count($accountData['AccountDynamicField']) > 0) {
				//$AccountReferenceArr = json_decode(json_encode(json_decode($accountData['AccountDynamicField'])), true);
				$AccountReferenceArr = json_decode(json_encode($accountData['AccountDynamicField']),true);

				for ($i =0; $i <count($AccountReferenceArr);$i++) {
					$AccountReference = $AccountReferenceArr[$i];
					if($AccountReference['Name'] == 'CustomerID' && !empty($AccountReference['Value'])){
						$CustomerVal = true;
						$CustomerDynamicID = $AccountReference['Value'];
					}
					$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),
						'Type'=>'account','Status'=>1])
						->whereRaw('REPLACE(FieldName," ","") = '. "'". str_replace(" ", "", $AccountReference['Name']) . "'")
						->pluck('DynamicFieldsID');
					if(empty($DynamicFieldsID)) {
						return Response::json(["ErrorMessage" => Codes::$Code1006[1]],Codes::$Code1006[0]);
					}
				}
				if(!$CustomerVal){
					return Response::json(["ErrorMessage" => Codes::$Code1062[1]],Codes::$Code1062[0]);
				}

				$CustomerID = $CustomerDynamicID;
				$data['CustomerID'] = $CustomerID;
				$FieldsID = DB::table('tblDynamicFields')->where(['FieldSlug'=>'CustomerID'])->pluck('DynamicFieldsID');
				$check = DynamicFieldsValue::where(['DynamicFieldsID'=>$FieldsID , 'FieldValue' => $CustomerID])->count();
				if($check > 0){
					return Response::json(["ErrorMessage" => Codes::$Code1063[1]],Codes::$Code1063[0]);
				}
			}else{
				return Response::json(["ErrorMessage" => Codes::$Code1064[1]],Codes::$Code1064[0]);
			}

			
			if($data['IsReseller']==1){

				$ResellerCount = Reseller::where('ChildCompanyID',$CompanyID)->count();
				if($ResellerCount>0){
					return Response::json(["ErrorMessage" => Codes::$Code1010[1]],Codes::$Code1010[0]);
				}

				
				Reseller::$rules['Email'] = 'required|email';
				Reseller::$rules['Password'] ='required|min:3';

				
				$ResellerData['CompanyID'] = $CompanyID;
				$CurrentTime = date('Y-m-d H:i:s');

				if(empty($CreatedBy)){
					$CreatedBy = 'system';
				}
				$ResellerData['AccountID'] = $data['Number'];
				Reseller::$rules['AccountID'] = 'required|unique:tblReseller,AccountID';
				Reseller::$rules['Email'] = 'required|email';
				Reseller::$rules['Password'] ='required|min:3';
				$ResellerData['Email'] = isset($accountData['PartnerEmail']) ? $accountData['PartnerEmail'] : '';
				$ResellerData['Password'] = isset($accountData['PartnerPanelPassword']) ? $accountData['PartnerPanelPassword'] : '';
				$ResellerData['AllowWhiteLabel'] = isset($accountData['ResellerAllowWhiteLabel']) ? 1 : 0;
				$ResellerData['DomainUrl'] = isset($accountData['ResellerDomainUrl']) ? $accountData['ResellerDomainUrl'] : '' ;
				Reseller::$messages['Email.required'] = 'The Partner Email is Required.';
				Reseller::$messages['Password.required'] = 'The Partner Password is Required.';
				if($data['IsReseller']==1) {
					$validator = Validator::make($ResellerData, Reseller::$rules, Reseller::$messages);
					if ($validator->fails()) {
						$errors = "";
						foreach ($validator->messages()->all() as $error) {
							$errors .= $error . "<br>";
						}
						return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
					}
				}

				if(!empty($ResellerData['AllowWhiteLabel'])){
					if(empty($ResellerData['DomainUrl'])){
						$ResellerData['DomainUrl'] = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'WEB_URL'])->pluck('Value');
					}
					if(!Reseller::IsAllowDomainUrl($ResellerData['DomainUrl'],'')){
						return  Response::json(array("ErrorMessage" => Codes::$Code1011[1]),Codes::$Code1011[0]);
					}
				}

			}
			$data['CurrencyId'] = Currency::where('Code',$data['CurrencyId'])->pluck('CurrencyId');
			if (!isset($data['CurrencyId'])) {
				return Response::json(["ErrorMessage" => Codes::$Code1012[1],Codes::$Code1012[0]]);
			}
			$data['Country'] = Country::where(['ISO2' => $data['Country']])->pluck('Country');
			if (!isset($data['Country'])) {
				return Response::json(["ErrorMessage" => Codes::$Code1013[1]],Codes::$Code1013[0]);
			}
			
			// $data['BillingCountry']= Country::where(['ISO2' => $data['BillingCountry']])->pluck('Country');
			// if (!isset($data['BillingCountry'])) {
			// 	return Response::json(["ErrorMessage" => Codes::$Code1013[1]],Codes::$Code1013[0]);
			// }

			if (isset($accountData['BillingCountryIso2']) && !empty($accountData['BillingCountryIso2'])) {
				$data['BillingCountry'] = Country::where(['ISO2' => $accountData['BillingCountryIso2']])->pluck('Country');
				if (!isset($data['BillingCountry'])) {
					return Response::json(["ErrorMessage" => Codes::$Code1013[1]], Codes::$Code1013[0]);
				}
			}
			
			$data['LanguageID'] = Language::where('ISOCode',$data['Language'])->pluck('LanguageID');
			unset($data['Language']);
			if (!isset($data['LanguageID'])) {
				return Response::json(["ErrorMessage" => Codes::$Code1014[1]],Codes::$Code1014[0]);
			}

			if (isset($data['Owner']) && !empty($data['Owner'])) {
				$data['Owner'] = Reseller::where('ResellerID', $data['Owner'])->pluck('ResellerID');
				if (!isset($data['Owner'])) {
					return Response::json(["ErrorMessage" => Codes::$Code1019[1]], Codes::$Code1019[0]);
				}
			}

			
			$BillingCycleTypeID[0] = "daily";
			$BillingCycleTypeID[1] = "fortnightly";
			$BillingCycleTypeID[2] = "in_specific_days";
			$BillingCycleTypeID[3] = "manual";
			$BillingCycleTypeID[4] = "monthly";
			$BillingCycleTypeID[5] = "monthly_anniversary";
			$BillingCycleTypeID[6] = "quarterly";
			$BillingCycleTypeID[7] = "weekly";
			$BillingCycleTypeID[8] = "yearly";

			$BillingSetting['billing_type'] = isset($accountData['BillingTypeID']) ? $accountData['BillingTypeID'] : '';
			$BillingSetting['billing_class']= isset($accountData['BillingClassID']) ? $accountData['BillingClassID'] : '';
			$BillingSetting['billing_cycle']= isset($accountData['BillingCycleTypeID']) ? $accountData['BillingCycleTypeID'] : '';
			$BillingSetting['billing_cycle_options']= isset($accountData['BillingCycleValue']) ? $accountData['BillingCycleValue'] :'';
			$BillingSetting['billing_start_date']=  isset($accountData['BillingStartDate']) ? $accountData['BillingStartDate'] : '';
			$BillingSetting['NextInvoiceDate']= isset($accountData['NextInvoiceDate']) ? $accountData['NextInvoiceDate'] : '';

			if (!empty($BillingSetting['billing_type']) ||
				!empty($BillingSetting['billing_class']) ||
				!empty($BillingSetting['billing_cycle']) ||!empty($BillingSetting['NextInvoiceDate']) ||
				!empty($BillingSetting['billing_start_date'])
			) {
				$data['Billing'] = 1;
			}

			if ($data['Billing'] == 1) {
				if (isset($BillingSetting['NextInvoiceDate']) && $BillingSetting['NextInvoiceDate'] != '' &&
					isset($BillingSetting['billing_start_date']) && $BillingSetting['billing_start_date'] != '' && strtotime($BillingSetting['NextInvoiceDate']) < strtotime($BillingSetting['billing_start_date'])
				) {
					return Response::json(["ErrorMessage" => Codes::$Code1015[1],Codes::$Code1015[0]]);
				}
				AccountBilling::$rulesAPI['billing_type'] = 'required';
				AccountBilling::$rulesAPI['billing_start_date'] = 'required|date_format:Y-m-d';
				$validator = Validator::make($BillingSetting, AccountBilling::$rulesAPI);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
				}

				if (!empty($BillingSetting['billing_type']) && ($BillingSetting['billing_type'] != 1 && $BillingSetting['billing_type'] != 2)) {
					return Response::json(["ErrorMessage" => Codes::$Code1016[1]],Codes::$Code1016[0]);
				}


				if ($data['Billing'] == 1) {
					$dataAccountBilling['BillingType'] = $BillingSetting['billing_type'];
					if (isset($data['PaymentMethod'])) {
						$BillingSetting['billing_class'] = $dataAccountBilling['BillingType']  == 1? "Prepaid":"Postpaid";
						$BillingSetting['billing_class'] = strtolower($BillingSetting['billing_class'] .'-'. $data['PaymentMethod']);
						Log::info("PaymentMethod " .  $BillingSetting['billing_class'] . ' ' . $CompanyID);
						$BillingClassSql = BillingClass::where('Name', $BillingSetting['billing_class'])
							->where('CompanyID', '=', $CompanyID);
						//dd($dataAccountBilling['BillingType']);
						$BillingClass = $BillingClassSql->first();
						if (!isset($BillingClass)) {
							return Response::json(["ErrorMessage" => Codes::$Code1017[1]], Codes::$Code1017[0]);
						}else {
							$BillingSetting['billing_class'] = $BillingClass['BillingClassID'];
						}
					}
				}
				if (!empty($BillingSetting['billing_cycle'])
					&& ($BillingSetting['billing_cycle'] < 1 || $BillingSetting['billing_cycle'] > 8)) {
					return Response::json(["ErrorMessage" => Codes::$Code1026[1]],Codes::$Code1026[0]);
				}



				if ($BillingSetting['billing_cycle'] == 2 || $BillingSetting['billing_cycle'] == 5 || $BillingSetting['billing_cycle'] == 7) {
					if (empty($BillingSetting['billing_cycle_options'])) {
						return Response::json(["ErrorMessage" => Codes::$Code1027[1]],Codes::$Code1027[0]);
					}

					if ($BillingSetting['billing_cycle'] == 2 || $BillingSetting['billing_cycle'] == 5 ) {
						$checkDate = strtotime($BillingSetting['billing_cycle_options']);
						if (empty($checkDate)) {
							return Response::json(["ErrorMessage" => Codes::$Code1022[1]],Codes::$Code1022[0]);
						}
					}

					if ($BillingSetting['billing_cycle'] == 7) {
						$validValues = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
						if (!in_array($BillingSetting['billing_cycle_options'], $validValues)) {
							return Response::json(["ErrorMessage" => Codes::$Code1028[1]],Codes::$Code1028[0]);
						}
					}
				} else {
					$BillingSetting['billing_cycle_options'] = '';
				}
			}

			DB::beginTransaction();

			Log::info("CreateAccountAPI Account" . print_r($data,true));

			if ($account = Account::create($data)) {

				Log::info("CreateAccountAPI Account Created" );

				if (trim($data['Number']) == '') {
					CompanySetting::setKeyVal('LastAccountNo', $account->Number);
				}
				$AccountDetails=array();
				$AccountDetails['AccountID'] = $account->AccountID;
				AccountDetails::create($AccountDetails);
				$AccountBalance['AccountID'] =  $account->AccountID;
				$AccountBalance['PermanentCredit'] =  0;
				$AccountBalance['TemporaryCredit'] =  0;
				$AccountBalance['TemporaryCreditDateTime'] =  $date;
				$AccountBalance['BalanceThreshold'] =  0;
				$AccountBalance['BalanceAmount'] =  0;
				$AccountBalance['EmailToCustomer'] =  0;
				$AccountBalance['UnbilledAmount'] =  0;
				$AccountBalance['SOAOffset'] =  0;
				$AccountBalance['VendorUnbilledAmount'] =  0;
				$AccountBalance['OutPayment'] =  0;
				AccountBalance::create($AccountBalance);
				$AccountBalanceThreshold['AccountID'] =  $account->AccountID;
				$AccountBalanceThreshold['BalanceThreshold'] =  0;
				$AccountBalanceThreshold['BalanceThresholdEmail'] =  '';
				AccountBalanceThreshold::create($AccountBalanceThreshold);
				$account->update($data);

				if ($data['IsAffiliateAccount'] == 1) {
					
					$AffiliateAccount = array();
					$AffiliateAccount['AffiliateAccounts'] = $data['AffiliateAccounts'];

					$Affiliate = AffiliateAccount::where('AccountID',$account->AccountID)->first();
					if($Affiliate){
						$Affiliate->update($AffiliateAccount);
					}else{
						$AffiliateAccount['AccountID'] = $account->AccountID;
						AffiliateAccount::create($AffiliateAccount);
					}
				}

				if (isset($data['PaymentMethod'])) {
					if ($data['PaymentMethod'] == "Stripe" || $data['PaymentMethod'] == "StripeACH") {
						$BankPaymentDetails['PaymentGatewayID'] = PaymentGateway::getPaymentGatewayIDByName("Stripe");
						$BankPaymentDetails['CompanyID'] = $CompanyID;
						if (!empty($BankPaymentDetails['CardNumber'])) {
							$BankPaymentDetails['PayoutType'] = "card";
							$BankPaymentDetails['Title'] = isset($BankPaymentDetails['NameOnCard']) ? $BankPaymentDetails['NameOnCard'] : 'CardTitle';
						} else {
							$BankPaymentDetails['Title'] = isset($BankPaymentDetails['AccountHolderName']) ? $BankPaymentDetails['AccountHolderName'] : 'AccountTitle';
							$BankPaymentDetails['PayoutType'] = "bank";
						}
						$BankPaymentDetails['AccountID'] = $account->AccountID;

						$BankPaymentDetails['CustomerAccountName'] = $account->AccountName;

						$PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($BankPaymentDetails['PaymentGatewayID']);
						$PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $CompanyID);
						if ( empty($BankPaymentDetails['CardToken'])) {
							$AccountResponse = $PaymentIntegration->createAccount($BankPaymentDetails);
						}else {
							$AccountResponse = $PaymentIntegration->createAccountWithToken($BankPaymentDetails);
						}
						$AccountResponse = $AccountResponse->getContent();
						$AccountResponse = json_decode($AccountResponse);
						$AccountResponse = json_decode(json_encode($AccountResponse), true);
						$AccountResponse = json_decode(json_encode($AccountResponse), true);
						if ($AccountResponse["status"] == 'failed') {
							DB::rollback();
							return Response::json(["ErrorMessage" => $AccountResponse["message"]], Codes::$Code1033[0]);
						}

					}else if ($data['PaymentMethod'] == "Ingenico") {
						$isDefault = 1;
						$PaymentGatewayID = 10;
						$count = AccountPaymentProfile::where(['AccountID' =>  $account->AccountID])
							->where(['CompanyID' => $account->CompanyID])
							->where(['PaymentGatewayID' => $PaymentGatewayID])
							->where(['isDefault' => 1])
							->count();

						if($count>0){
							$isDefault = 0;
						}

						$option = array(
							'CardToken'       => $PaymentProfile['CardToken'],
							'CardHolderName'  => $PaymentProfile['CardHolderName'],
							'ExpirationMonth' => $PaymentProfile['ExpirationMonth'],
							'ExpirationYear'  => $PaymentProfile['ExpirationYear'],
							'LastDigit'       => $PaymentProfile['LastDigit'],
						);

						$CardDetail = array('Title' => $PaymentProfile['Title'],
							'Options' => json_encode($option),
							'Status' => 1,
							'isDefault' => $isDefault,
							'created_by' => $CreatedBy,
							'CompanyID' => $account->CompanyID,
							'AccountID' =>  $account->AccountID,
							'PaymentGatewayID' => $PaymentGatewayID);
						AccountPaymentProfile::create($CardDetail);
					} else if ($data['PaymentMethod'] == "DirectDebit") {
						$isDefault = 1;
						$PaymentGatewayID = 12;
						$count = AccountPaymentProfile::where(['AccountID' => $account->AccountID])
							->where(['CompanyID' => $account->CompanyID])
							->where(['PaymentGatewayID' => $PaymentGatewayID])
							->where(['isDefault' => 1])
							->count();

						if($count>0){
							$isDefault = 0;
						}

						$option = array(
							'BankAccount'       => $PaymentProfile['BankAccount'],
							'BIC'               => $PaymentProfile['BIC'],
							'AccountHolderName' => $PaymentProfile['AccountHolderName'],
							'MandateCode'       => $PaymentProfile['MandateCode'],
						);
						$CardDetail = array('Title' => $PaymentProfile['Title'],
							'Options' => json_encode($option),
							'Status' => 1,
							'isDefault' => $isDefault,
							'created_by' => $CreatedBy,
							'CompanyID' => $account->CompanyID,
							'AccountID' => $account->AccountID,
							'PaymentGatewayID' => $PaymentGatewayID);
						AccountPaymentProfile::create($CardDetail);
					}else if ($data['PaymentMethod'] == "WireTransfer") {
						$isDefault = 1;
						$PaymentGatewayID = 11;
						$count = AccountPaymentProfile::where(['AccountID' => $account->AccountID])
							->where(['CompanyID' => $account->CompanyID])
							->where(['PaymentGatewayID' => $PaymentGatewayID])
							->where(['isDefault' => 1])
							->count();

						if($count>0){
							$isDefault = 0;
						}

						$option = array(
							'BankAccount'       => $PaymentProfile['BankAccount'],
							'BIC'               => $PaymentProfile['BIC'],
							'AccountHolderName' => $PaymentProfile['AccountHolderName'],
							'MandateCode'       => $PaymentProfile['MandateCode'],
						);
						$CardDetail = array('Title' => $PaymentProfile['Title'],
							'Options' => json_encode($option),
							'Status' => 1,
							'isDefault' => $isDefault,
							'created_by' => $CreatedBy,
							'CompanyID' => $account->CompanyID,
							'AccountID' => $account->AccountID,
							'PaymentGatewayID' => $PaymentGatewayID);
						AccountPaymentProfile::create($CardDetail);
					}
				}

				if (isset($data['PayoutMethod'])) {
					if ($data['PayoutMethod'] == "WireTransfer") {
						$isDefault = 1;
						$PaymentGatewayID = 11;
						$count = AccountPayout::where(['AccountID' => $account->AccountID])
							->where(['CompanyID' => $account->CompanyID])
							->where(['PaymentGatewayID' => $PaymentGatewayID])
							->where(['isDefault' => 1])
							->count();

						if($count>0){
							$isDefault = 0;
						}

						$option = array(
							'BankAccount'       => $PayoutProfile['BankAccount'],
							'BIC'               => $PayoutProfile['BIC'],
							'AccountHolderName' => $PayoutProfile['AccountHolderName'],
							'MandateCode'       => $PayoutProfile['MandateCode'],
						);
						$CardDetail = array('Title' => $PayoutProfile['Title'],
							'Options' => json_encode($option),
							'Status' => 1,
							'isDefault' => $isDefault,
							'created_by' => $CreatedBy,
							'CompanyID' => $account->CompanyID,
							'AccountID' => $account->AccountID,
							'PaymentGatewayID' => $PaymentGatewayID);
						AccountPayout::create($CardDetail);
					}
				}

				if (isset($accountData['AccountDynamicField'])) {
					$AccountReferenceArr = json_decode(json_encode($accountData['AccountDynamicField']),true);
					for ($i =0; $i <count($AccountReferenceArr);$i++) {
						$AccountReference = $AccountReferenceArr[$i];
						$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),
							'Type'=>'account','Status'=>1])
							->whereRaw('REPLACE(FieldName," ","") = '. "'". str_replace(" ", "", $AccountReference['Name']) . "'")
							->pluck('DynamicFieldsID');
						$DynamicFields['ParentID'] = $account->AccountID;
						$DynamicFields['DynamicFieldsID'] = $DynamicFieldsID;
						$DynamicFields['CompanyID'] =  $account->CompanyID;
						$DynamicFields['created_at'] = $date;
						$DynamicFields['created_by'] = $CreatedBy;
						$DynamicFields['FieldValue'] = $AccountReference["Value"];
						DB::table('tblDynamicFieldsValue')->insert($DynamicFields);
					}
				}

				if (!empty($AccountPaymentAutomation['AutoTopup']) && $AccountPaymentAutomation['AutoTopup'] == 1 ||
					!empty($AccountPaymentAutomation['AutoOutpayment']) && $AccountPaymentAutomation['AutoOutpayment'] == 1) {
					$AccountPaymentAutomation['AccountID'] = $account->AccountID;
					AccountPaymentAutomation::create($AccountPaymentAutomation);
				}
				if ($data['Billing'] == 1) {
					$TaxRateCalculation = [];
					$TaxRateID = [];
					$TaxRateCalculation['CompanyID'] = $CompanyID;
					$TaxRateCalculation['Country'] = $data['Country'];
					$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),
						'Type'=>'account','Status'=>1])
						->whereRaw('REPLACE(FieldName," ","") = '. "'". str_replace(" ", "", "Register Dutch Foundation") . "'")
						->pluck('DynamicFieldsID');
					$DynamicFieldsValue = DynamicFieldsValue::where('CompanyID',$CompanyID)
						->where('ParentID',$account->AccountID)
						->where('DynamicFieldsID',$DynamicFieldsID)->pluck('FieldValue');
					$TaxRateCalculation['RegisterDutchFoundation'] = !empty($DynamicFieldsValue) ? $DynamicFieldsValue : 0;
					$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),
						'Type'=>'account','Status'=>1])
						->whereRaw('REPLACE(FieldName," ","") = '. "'". str_replace(" ", "", "Dutch Provider") . "'")
						->pluck('DynamicFieldsID');
					$DynamicFieldsValue = DynamicFieldsValue::where('CompanyID',$CompanyID)
						->where('ParentID',$account->AccountID)
						->where('DynamicFieldsID',$DynamicFieldsID)->pluck('FieldValue');

					$TaxRateCalculation['DutchProvider'] = !empty($DynamicFieldsValue) ? $DynamicFieldsValue : 0;
					$TaxRateCalculation['IsCustomer']    = $data['IsCustomer'];
					$TaxRateCalculation['IsReseller']    = $data['IsReseller'];
					$TaxRateCalculation['PartnerID']     = $data['PartnerID'];
					$TaxRateID['TaxRateID'] = $this->getAccountTaxes($TaxRateCalculation);
					$account->update($TaxRateID);

					$dataAccountBilling['BillingType'] = $BillingSetting['billing_type'];
					$BillingClassSql = BillingClass::where('BillingClassID', $BillingSetting['billing_class'])->where('CompanyID','=',$CompanyID);
					$BillingClass = $BillingClassSql->first();
					if (!isset($BillingClass)) {
						return Response::json(["ErrorMessage" => Codes::$Code1017[1]],Codes::$Code1017[0]);
					}

					$dataAccountBilling['BillingClassID'] = $BillingClass->BillingClassID;
					$dataAccountBilling['BillingTimezone'] = $BillingClass->BillingTimezone;
					$dataAccountBilling['SendInvoiceSetting'] = empty($BillingClass->SendInvoiceSetting) ? 'after_admin_review' : $BillingClass->SendInvoiceSetting;

					if(isset($accountData['AutoPay'])) {

						// Auto Pay Value must be in (0,1,2)
						$AutoPayIndex = $accountData['AutoPay'];
						$AutoPayArr = [0=>'never',1=>'invoiceday',2=>'duedate'];

						if(in_array($AutoPayIndex,[0,1,2])){
							$dataAccountBilling['AutoPaymentSetting'] = $AutoPayArr[$AutoPayIndex];
							// Auto Pay Method = 2 (Preferred)
							$dataAccountBilling['AutoPayMethod'] = 2;
						} else {
							return Response::json(["ErrorMessage" => "Please enter valid Auto Pay value."],Codes::$Code400[0]);
						}
					} else {
						$dataAccountBilling['AutoPaymentSetting'] = empty($BillingClass->AutoPaymentSetting) ? 'never' : $BillingClass->AutoPaymentSetting;

						$dataAccountBilling['AutoPayMethod'] = empty($BillingClass->AutoPayMethod) ? 2 : $BillingClass->AutoPayMethod;

					}
					//get from billing class id over
					$dataAccountBilling['BillingCycleType'] = $BillingSetting['billing_cycle'];
					$dataAccountBilling['BillingCycleValue'] = empty($BillingSetting['billing_cycle_options']) ? '' : $BillingSetting['billing_cycle_options'];
					// set as first invoice generate
					$BillingCycleType = $BillingSetting['billing_cycle'];
					$BillingCycleValue = $BillingSetting['billing_cycle_options'];
					if (isset($BillingSetting['billing_start_date']) && $BillingSetting['billing_start_date'] != '') {
						$BillingStartDate = $BillingSetting['billing_start_date'];
					} else {
						$BillingStartDate = date('Y-m-d');
					}


					$NextBillingDate = next_billing_date($BillingCycleTypeID[4], $BillingCycleValue, strtotime($BillingStartDate));
					$NextChargedDate = date('Y-m-d', strtotime('-1 day', strtotime($NextBillingDate)));

					$dataAccountBilling['BillingStartDate'] = $accountData['BillingStartDate'];
					$dataAccountBilling['LastInvoiceDate'] = $BillingStartDate;
					$dataAccountBilling['LastChargeDate'] = $BillingStartDate;
					if (isset($BillingSetting['NextInvoiceDate']) && $BillingSetting['NextInvoiceDate'] != '') {
						$NextBillingDate = $BillingSetting['NextInvoiceDate'];
					}

					$dataAccountBilling['NextInvoiceDate'] = $NextBillingDate;
					$dataAccountBilling['NextChargeDate'] = $NextChargedDate;
					$dataAccountBilling['BillingCycleType'] = $BillingCycleTypeID[4];

					AccountBilling::insertUpdateBilling($account->AccountID, $dataAccountBilling, 0);
					AccountBilling::storeFirstTimeInvoicePeriod($account->AccountID, 0);

				}
				if($data['IsReseller']==1) {

					$items = '';
					$subscriptions = '';
					$trunks = '';
					$is_product = 0;
					$is_subscription = 0;
					$is_trunk = 0;
					$productids = '';
					$subscriptionids = '';
					$trunkids = '';
					if (!empty($items)) {
						$is_product = 1;
						$productids = implode(',', $items);
					}
					if (!empty($subscriptions)) {
						$is_subscription = 1;
						$subscriptionids = implode(',', $subscriptions);
					}
					if (!empty($trunks)) {
						$is_trunk = 1;
						$trunkids = implode(',', $trunks);
					}

					if (!empty($ResellerData)) {
						//$CompanyID = User::get_companyID();
						$data['CompanyID'] = $CompanyID;
						$CurrentTime = date('Y-m-d H:i:s');
						if (empty($CreatedBy)) {
							$CreatedBy = 'system';
						}

						$ResellerData['Password'] = Crypt::encrypt($accountData['PartnerPanelPassword']);
						$Account = $account;
						$ResellerData['AllowWhiteLabel'] = isset($accountData['ResellerAllowWhiteLabel']) ? 1 : 0;
						$ResellerData['DomainUrl'] = isset($accountData['ResellerDomainUrl']) ? $accountData['ResellerDomainUrl'] : '';
						$AccountID = $account->AccountID;
						$Email = $ResellerData['Email'];
						$Password = $ResellerData['Password'];
						$AllowWhiteLabel = $ResellerData['AllowWhiteLabel'];
						$AccountName = $Account->AccountName;
						if (!empty($Account->FirstName) && !empty($Account->LastName)) {
							$FirstName = empty($Account->FirstName) ? '' : $Account->FirstName;
							$LastName = empty($Account->LastName) ? '' : $Account->LastName;
						} else {
							$FirstName = $AccountName;
							$LastName = 'Reseller';
						}

						try {
							$CompanyData = array();
							$CompanyData['CompanyName'] = $AccountName;
							$CompanyData['CustomerAccountPrefix'] = '22221';
							$CompanyData['FirstName'] = $FirstName;
							$CompanyData['LastName'] = $LastName;
							$CompanyData['Email'] = $ResellerData['Email'];
							$CompanyData['Status'] = '1';
							$CompanyData['CurrencyId'] = $Account->CurrencyId;
							$CompanyData['Country'] = $Account->Country;
							$CompanyData['TimeZone'] = Company::getCompanyTimeZone($CompanyID);
							$CompanyData['created_at'] = $CurrentTime;
							$CompanyData['created_by'] = $CreatedBy;

							if ($ChildCompany = Company::create($CompanyData)) {
								$ChildCompanyID = $ChildCompany->CompanyID;


								$JobStatusMessage = DB::select("CALL  prc_insertResellerData ($CompanyID,$ChildCompanyID,'" . $AccountName . "','" . $FirstName . "','" . $LastName . "',$AccountID,'" . $Email . "','" . $Password . "',$is_product,'" . $productids . "',$is_subscription,'" . $subscriptionids . "',$is_trunk,'" . $trunkids . "',$AllowWhiteLabel , '' , '' , '' ,'' ,'', '')");

								if (count($JobStatusMessage)) {
									throw  new \Exception($JobStatusMessage[0]->Message);
								} else {
									if (!empty($data['DomainUrl'])) {
										$DomainUrl = rtrim($data['DomainUrl'], "/");
										CompanyConfiguration::where(['Key' => 'WEB_URL', 'CompanyID' => $ChildCompany->CompanyID])->update(['Value' => $DomainUrl]);
									} else {
										$ResellerDomain = CompanyConfiguration::where(['CompanyID' => $CompanyID, 'Key' => 'WEB_URL'])->pluck('Value');
										CompanyConfiguration::where(['Key' => 'WEB_URL', 'CompanyID' => $ChildCompany->CompanyID])->update(['Value' => $ResellerDomain]);
									}
									CompanyGateway::createDefaultCronJobs($ChildCompanyID);

								}

							} else {
								return Response::json(array("ErrorMessage" => Codes::$Code500[1]),Codes::$Code500[0]);
							}
						} catch (Exception $e) {
							try {
								DB::rollback();
							} catch (\Exception $err) {
								Log::error($err);
							}
							Log::error($e);
							return Response::json(array("ErrorMessage" => Codes::$Code500[1]),Codes::$Code500[0]);
						}
					}
				}

				$AccountSuccessMessage['AccountID'] = $account->AccountID;

				CompanySetting::setKeyVal('LastAccountNo', $account->Number);
				DB::commit();
				return Response::json($AccountSuccessMessage,Codes::$Code200[0]);
			} else {
				DB::rollback();
				return Response::json(array("ErrorMessage" => Codes::$Code500[1]),Codes::$Code500[0]);
			}

		} catch (Exception $ex) {
			DB::rollback();
			Log::error("CreateAccountAPI Exception" . $ex->getTraceAsString());
			return Response::json(["ErrorMessage" => Codes::$Code500[1]],Codes::$Code500[0]);
		}
	}

		public function getPaymentToken() {
			Log::info('createAccount:Create new Account.');
			$post_vars = '';
			$accountData = [];
			$BillingClass = [];
			$BankPaymentDetails = [];
			$PaymentProfile = [];


				try {
					$post_vars = json_decode(file_get_contents("php://input"));
					//$post_vars = Input::all();
					$accountData=json_decode(json_encode($post_vars),true);
					$countValues = count($accountData);
					if ($countValues == 0) {
						Log::info('Exception in updateAccount API.Invalid JSON');
						return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
					}
				}catch(Exception $ex) {
					Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
					return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
				}

			$accountInfo = Account::where(["AccountID" => $accountData["AccountID"]])->first();


		if (empty($accountInfo)) {
			return Response::json(["ErrorMessage"=>Codes::$Code1000[1]],Codes::$Code1000[0]);
		}
	$data['PaymentMethod'] = AccountsApiController::$API_PaymentMethod[$accountData['PaymentMethodID']];

	if (isset($data['PaymentMethod']) ) {
	if ($data['PaymentMethod'] == "Stripe") {
		$BankPaymentDetails['CardNumber'] = isset($accountData['CardNumber']) ? $accountData['CardNumber'] : '' ;
		$BankPaymentDetails['CardType'] = isset($accountData['CardType']) ? $accountData['CardType'] : '' ;//Discover,MasterCard,Visa
		$BankPaymentDetails['ExpirationMonth'] = isset($accountData['ExpirationMonth']) ? $accountData['ExpirationMonth'] : '' ;
		$BankPaymentDetails['ExpirationYear'] = isset($accountData['ExpirationYear']) ? $accountData['ExpirationYear'] : '' ;
		$BankPaymentDetails['NameOnCard'] = isset($accountData['NameOnCard']) ? $accountData['NameOnCard'] : '' ;
		$BankPaymentDetails['CVVNumber'] = isset($accountData['CVVNumber']) ? $accountData['CVVNumber'] : '' ;
		$CardValidationResponse = AccountPayout::cardValidation($BankPaymentDetails);
		if ($CardValidationResponse["status"] == "failed") {
		return Response::json(["ErrorMessage" => $CardValidationResponse["message"]],Codes::$Code402[0]);
		}
		$CardType = array("Discover", "MasterCard", "Visa");
		if (!in_array($BankPaymentDetails['CardType'], $CardType)) {
			return Response::json(["ErrorMessage" => Codes::$Code1036[1]],Codes::$Code1036[0]);
		}
	}else if ($data['PaymentMethod'] == "StripeACH") {
		$BankPaymentDetails['AccountNumber'] = isset($accountData['AccountNumber']) ? $accountData['AccountNumber'] : '' ;
		$BankPaymentDetails['RoutingNumber'] = isset($accountData['RoutingNumber']) ? $accountData['RoutingNumber'] : '' ;
		$BankPaymentDetails['AccountHolderType'] = isset($accountData['AccountHolderType']) ? $accountData['AccountHolderType'] : '' ;//company,individual
		$BankPaymentDetails['AccountHolderName'] = isset($accountData['AccountHolderName']) ? $accountData['AccountHolderName'] : '' ;
		$validator = Validator::make($BankPaymentDetails, AccountPayout::$AccountPayoutBankRules);
		if ($validator->fails()) {
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);

		}
		$AccountHolderType = array("individual","company");
		if (!in_array($BankPaymentDetails['AccountHolderType'], $AccountHolderType)) {
			return Response::json(["ErrorMessage" => Codes::$Code1037[1]],Codes::$Code1037[0]);
		}


	}

		$CompanyID = $accountInfo->CompanyId;
		$BankPaymentDetails['PaymentGatewayID'] = PaymentGateway::getPaymentGatewayIDByName("Stripe");
		$BankPaymentDetails['CompanyID'] = $CompanyID;
		if (!empty($BankPaymentDetails['CardNumber'])) {
			$BankPaymentDetails['PayoutType'] = "card";
			$BankPaymentDetails['Title'] = $BankPaymentDetails['NameOnCard'];
		} else {
			$BankPaymentDetails['Title'] = $BankPaymentDetails['AccountHolderName'];
			$BankPaymentDetails['PayoutType'] = "bank";
		}
		$BankPaymentDetails['AccountID'] = $accountInfo->AccountID;

		$BankPaymentDetails['CustomerAccountName'] = $accountInfo->AccountName;


		$StripeBilling = new StripeBilling($CompanyID);
		$AccountResponse = $StripeBilling->getTestingToken($BankPaymentDetails);
		return Response::json(["ErrorMessage" => $AccountResponse],Codes::$Code1037[0]);
	}
		}

	public function getAccountTaxes($data = [] , $Account = []){
		$Taxes = '';
		$CompanyID = User::get_companyID();
		$Country = $data['Country'];

		$CustomerAccount = isset($data['IsCustomer']) && $data['IsCustomer'] == 1 ? 1 : 0;
		$PartnerAccount =  isset($data['IsReseller']) && $data['IsReseller'] == 1 ? 1 : 0;
		$RegisterDutchFoundation = 0;
		$DutchProvider = 0;
		if($PartnerAccount == 1){
			if($Country != "NETHERLANDS"){
				return false;
			}
		}else if($CustomerAccount == 1){

			if(isset($data['PartnerID']) && !empty($data['PartnerID'])){
				$ResellerID = DynamicFieldsValue::where(['DynamicFieldsID' => 93 , 'FieldValue' => $data['PartnerID']])->first();
				if(!$ResellerID){
					return false;
				}


			}
			if(isset($ResellerID) && $ResellerID){
				$Reseller = Reseller::where('AccountID',$ResellerID->ParentID)->first();
			}
			if(!empty($Account)){
				$Reseller = Reseller::where('ChildCompanyID',$Account->CompanyId)->first();
			}

			if(isset($Reseller) && $Reseller){
				$AccountCountry = Account::where('AccountID',$Reseller->AccountID)->pluck('Country');

				if($Country != $AccountCountry){
					return false;
				}
			}
		}else{
			return false;
		}


		if(isset($data['RegisterDutchFoundation']) && $data['RegisterDutchFoundation'] == 1){
			$RegisterDutchFoundation=1;
		}
		if(isset($data['DutchProvider']) && $data['DutchProvider'] == 1){
			$DutchProvider=1;
		}



		// if($Country=='NETHERLANDS'){
		//     $EUCountry = 'NL';
		// }else{
		$EUCountry = Country::where('Country',$data['Country'])->pluck('ISO2');
		//dd($EUCountry);
		//$EUCountry = $EUCountry;
		// $EUCountry = empty($EUCountry) ? 'NEU' : 'EU';
		//}
		$Results = TaxRate::where(['DutchProvider'=>$DutchProvider,'DutchFoundation'=>$RegisterDutchFoundation,'Country'=>$EUCountry,'CompanyId'=>$CompanyID,'Status'=>1])->get();
		//log::info(print_r($Results,true));
		if(!empty($Results)){
			foreach($Results as $result){
				$Taxes.=$result->TaxRateId.',';
			}
			$Taxes = rtrim($Taxes, ',');
		}
		$Taxes = explode(",", $Taxes);
		return implode(',',$Taxes);
	}

	public function updateAccount() {
		$post_vars = '';
		$accountData = [];
		$BillingClass = [];
		$BankPaymentDetails = [];
		$PaymentProfile = [];
		try {

			try {
				$post_vars = json_decode(file_get_contents("php://input"));
				$accountData=json_decode(json_encode($post_vars),true);
				$countValues = count($accountData);
				if ($countValues == 0) {					
					return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
				}
			}catch(Exception $ex) {
				Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
				return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
			}


			$ServiceID = 0;
			$LogonUser = User::getUserInfo();
			$CompanyID = $LogonUser["CompanyID"];
			$CreatedBy = User::get_user_full_name();
			$ResellerData = [];
			$AccountPaymentAutomation = [];
			$AccountReferenceObj = '';
			$DynamicFields = '';
			$accountInfo = [];
			$date = date('Y-m-d H:i:s.000');
			$DynamicFieldsExist = '';
			$Reseller = [];

			$rules = array(
				'AccountNo' => 'required_without_all:AccountID,AccountDynamicField',
				'AccountID' => 'required_without_all:AccountNo,AccountDynamicField',
				'AccountDynamicField' => 'required_without_all:AccountNo,AccountID',
			);
			$validator = Validator::make($accountData, $rules);


			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
			}

			$profiles = '';
			$RoutingProfileId = array();
			$CustomerProfileAccountID = '';
			if (isset($accountData["AccountNo"]) && $accountData["AccountNo"] != '') {
				$accountInfo = Account::where(["Number" => $accountData["AccountNo"]])->first();
			} elseif (isset($accountData["AccountID"]) && $accountData["AccountID"] != ''){
				$accountInfo = Account::where(["AccountID" => $accountData["AccountID"]])->first();
			} elseif (isset($accountData["AccountDynamicField"]) && $accountData["AccountDynamicField"] != ''){
				$AccountID = Account::findAccountBySIAccountRef($accountData['AccountDynamicField']);
				if (!empty($AccountID)) {
					$accountInfo = Account::where(["AccountID" => $AccountID])->first();
				}
			}

			if (empty($accountInfo)) {
				return Response::json(["ErrorMessage"=>Codes::$Code1000[1]],Codes::$Code1000[0]);
			}

			$data['AccountID'] = $accountInfo->AccountID;
			$CompanyID = $accountInfo->CompanyId;
			$data['CompanyID'] =$accountInfo->CompanyId;
			$data['Number'] =$accountInfo->Number;

			if (isset($accountData['AccountNoUpdate']) && !empty($accountData['AccountNoUpdate'])) {
				$newNumber = $accountData['AccountNoUpdate'];
				if(Account::isAccountNumberExist($newNumber,$accountInfo->AccountID)){
					return Response::json(["ErrorMessage"=>"The number has already been taken."],Codes::$Code1000[0]);
				} else {
					$data['Number'] = $newNumber;
				}
			}

			if (isset($accountData['FirstName']) && !empty($accountData['FirstName'])) {
				$data['FirstName'] = $accountData['FirstName'];
			}
			if (isset($accountData['LastName']) && !empty($accountData['LastName'])) {
				$data['LastName'] = $accountData['LastName'];
			}
			if (isset($accountData['Phone']) && !empty($accountData['Phone'])) {
				$data['Phone'] = $accountData['Phone'];
			}
			if (isset($accountData['Address1']) && !empty($accountData['Address1'])) {
				$data['Address1'] = $accountData['Address1'];
			}
			if (isset($accountData['Address2']) && !empty($accountData['Address2'])) {
				$data['Address2'] = $accountData['Address2'];
			}
			if (isset($accountData['Address3']) && !empty($accountData['Address3'])) {
				$data['Address3'] = $accountData['Address3'];
			}
			if (isset($accountData['PostCode']) && !empty($accountData['PostCode'])) {
				$data['PostCode'] = $accountData['PostCode'];
			}

			if (isset($accountData['City']) && !empty($accountData['City'])) {
				$data['City'] = $accountData['City'];
			}
			if (isset($accountData['Email']) && !empty($accountData['Email'])) {
				$data['Email'] = $accountData['Email'];
			}
			if (isset($accountData['BillingEmail'])) {
				$rules = array(
					'BillingEmail'       => 'required',
				);
				$data['BillingEmail'] = $accountData['BillingEmail'];

				$validator = Validator::make($data, $rules);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error){
						$errors .= $error."<br>";
					}

					return Response::json(["ErrorMessage" => $errors],400);
				}

			}

			if (isset($accountData['BillingAddress1'])) {
				$data['BillingAddress1'] = $accountData['BillingAddress1'];
				$data['DifferentBillingAddress'] = 1;
			}
			if (isset($accountData['BillingAddress2'])) {
				$data['BillingAddress2'] = $accountData['BillingAddress2'];
				$data['DifferentBillingAddress'] = 1;
			}
			if (isset($accountData['BillingAddress3'])) {
				$data['BillingAddress3'] = $accountData['BillingAddress3'];
				$data['DifferentBillingAddress'] = 1;
			}
			if (isset($accountData['BillingPostCode'])) {
				$data['BillingPostCode'] = $accountData['BillingPostCode'];
				$data['DifferentBillingAddress'] = 1;
			}
			if (isset($accountData['BillingCity'])) {
				$data['BillingCity'] = $accountData['BillingCity'];
				$data['DifferentBillingAddress'] = 1;
			}
			if (isset($accountData['BillingCountryIso2'])) {
				$data['BillingCountry'] = $accountData['BillingCountryIso2'];
				$data['DifferentBillingAddress'] = 1;
			}

			if(isset($accountData['AutoPay'])) {
				// Auto Pay Value must be in (0,1,2)
				$AutoPayIndex = $accountData['AutoPay'];
				$AutoPayArr = [0=>'never',1=>'invoiceday',2=>'duedate'];

				if(in_array($AutoPayIndex,[0,1,2])){
					$dataAccountBilling['AutoPaymentSetting'] = $AutoPayArr[$AutoPayIndex];

					if($AutoPayIndex != 0)
						$dataAccountBilling['AutoPayMethod'] = 2;
					else
						$dataAccountBilling['AutoPayMethod'] = 0;

					AccountBilling::where('AccountID',$accountInfo->AccountID)->update($dataAccountBilling);
				} else {
					return Response::json(["ErrorMessage" => "Please enter valid Auto Pay value."],Codes::$Code400[0]);
				}
			}

			$data['DurationMonths'] = isset($accountData['DurationMonths']) ? $accountData['DurationMonths'] : '';
			$data['CommissionPercentage'] = isset($accountData['CommissionPercentage']) ? $accountData['CommissionPercentage'] : '';

			$rules = array(
				'CommissionPercentage' => 'numeric',
				'DurationMonths'       => 'numeric',
				'Email'                => 'email',
				'MinThreshold'         => 'numeric',
				'TopupAmount'          => 'numeric',
				'OutPaymentThreshold'  => 'numeric',
				'OutPaymentAmount'     => 'numeric',
				'AutoTopup'            => 'numeric',
				'AutoOutpayment'       => 'numeric',
				'PaymentMethodID'      => 'numeric',
				'PayoutMethodID'       => 'numeric',
				'Active'               => 'numeric'
			);

			$validator = Validator::make($accountData, $rules);
			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error){
					$errors .= $error."<br>";
				}

				return Response::json(["ErrorMessage" => $errors],400);
			}
				
			$data['AffiliateAccounts'] = isset($accountData['AffiliateAccounts']) ? $accountData['AffiliateAccounts'] : '';
			
			if ($accountInfo->IsAffiliateAccount == 1) {		
				if(isset($accountData['AffiliateAccounts'])){
					if(!preg_match('/^[0-9,]+$/', $data['AffiliateAccounts'])){
						return Response::json(array("ErrorMessage" => Codes::$Code1066[1]),Codes::$Code1066[0]);
					}
				}
				if(isset($accountData['AffiliateAccounts']) && !empty($accountData['AffiliateAccounts'])){
					$AffiliateAccount = array();
					$AffiliateAccount['AffiliateAccounts'] = $data['AffiliateAccounts'];
					
					$Affiliate = AffiliateAccount::where('AccountID',$accountInfo->AccountID)->first();
					if($Affiliate){
						$Affiliate->update($AffiliateAccount);
					}else{
						$AffiliateAccount['AccountID'] = $accountInfo->AccountID;
						AffiliateAccount::create($AffiliateAccount);
					}
				}
			}

			if (isset($accountData['AccountName']) && !empty($accountData['AccountName'])) {
				$data['AccountName'] = $accountData['AccountName'];
				if (strpbrk($data['AccountName'], '\/?*:|"<>')) {
					return Response::json(["ErrorMessage" => Codes::$Code1018[1]], Codes::$Code1018[0]);
				}
				$AccountName = Account::where(['AccountName' => $data["AccountName"], 'CompanyID' => $CompanyID, 'AccountType' => 1])->where('AccountID' ,'!=', $accountInfo->AccountID)->count();
				if ($AccountName > 0) {
					return Response::json(["ErrorMessage" => Codes::$Code1029[1]], Codes::$Code410[0]);
				}
			}

			if (isset($accountData['CountryIso2']) && !empty($accountData['CountryIso2'])) {
				$data['Country'] = isset($accountData['CountryIso2']) ? $accountData['CountryIso2'] : '';
			}

			if (isset($accountData['VatNumber']) && !empty($accountData['VatNumber'])) {
				$data['VatNumber'] = isset($accountData['VatNumber']) ? $accountData['VatNumber'] : '';
			}
			if (isset($accountData['LanguageIso2']) && !empty($accountData['LanguageIso2'])) {
				$data['Language']= isset($accountData['LanguageIso2']) ? $accountData['LanguageIso2'] : '';
			}
			if (isset($accountData['tags']) && !empty($accountData['tags'])) {
				$data['tags']= isset($accountData['tags']) ? $accountData['tags'] : '';
			}
			if (isset($accountData['Active'])) {
				if(in_array($accountData['Active'],[0,1])) {
					$data['Status'] = $accountData['Active'];
				} else {
					return Response::json(["ErrorMessage" => "Active value must be 0 or 1"],Codes::$Code1063[0]);
				}
			}
			if (isset($accountData['AccountTimeZones']) && !empty($accountData['AccountTimeZones'])) {
				$data['TimeZone']= isset($accountData['AccountTimeZones']) ? $accountData['AccountTimeZones'] : '';
			}
			if (isset($accountData['PartnerID']) && !empty($accountData['PartnerID'])) {
				$data['PartnerID']= isset($accountData['PartnerID']) ? $accountData['PartnerID'] : '';
			}

			//when account varification is off in company setting then varified the account by default.
			$AccountVerification =  CompanySetting::getKeyVal('AccountVerification');
			if ( $AccountVerification != CompanySetting::ACCOUT_VARIFICATION_ON ) {
				$data['VerificationStatus'] = Account::VERIFIED;
			}

			if (!empty($data['Country'])) {
				$data['Country'] = Country::where(['ISO2' => $data['Country']])->pluck('Country');
				if (!isset($data['Country'])) {
					return Response::json(["ErrorMessage" => Codes::$Code1013[1]], Codes::$Code1013[0]);
				}
			}
			
			if (isset($accountData['BillingCountryIso2']) && !empty($accountData['BillingCountryIso2'])) {
				$data['BillingCountry'] = Country::where(['ISO2' => $accountData['BillingCountryIso2']])->pluck('Country');
				if (!isset($data['BillingCountry'])) {
					return Response::json(["ErrorMessage" => Codes::$Code1013[1]], Codes::$Code1013[0]);
				}
			}

			if (!empty($data['Language'])) {
				$data['LanguageID'] = Language::where('ISOCode', $data['Language'])->pluck('LanguageID');
				if (!isset($data['LanguageID'])) {
					return Response::json(["ErrorMessage" => Codes::$Code1014[1]], Codes::$Code1014[0]);
				}
			}

			if (isset($accountData['AccountDynamicFieldUpdate'])) {
				$CustomerDynamicID = '';
				$AccountReferenceArr = json_decode(json_encode($accountData['AccountDynamicFieldUpdate']),true);
				for ($i =0; $i <count($AccountReferenceArr);$i++) {
					$AccountReference = $AccountReferenceArr[$i];
					if($AccountReference['Name'] == 'CustomerID' && !empty($AccountReference['Value'])){
						$CustomerVal = true;
						$CustomerDynamicID = $AccountReference['Value'];
					}
					if($AccountReference['Name'] == 'CustomerID' && empty($AccountReference['Value'])){
						return Response::json(["ErrorMessage" => Codes::$Code1062[1]],Codes::$Code1062[0]);
					}
					$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),
						'Type'=>'account','Status'=>1])
						->whereRaw('REPLACE(FieldName," ","") = '. "'". str_replace(" ", "", $AccountReference['Name']) . "'")
						->pluck('DynamicFieldsID');
					if(empty($DynamicFieldsID)) {
						return Response::json(["ErrorMessage" => Codes::$Code1006[1]],Codes::$Code1006[0]);
					}
				}

				$CustomerID = $CustomerDynamicID;	
				if(!empty($CustomerID)){
					$data['CustomerID'] = $CustomerID;
					$FieldsID = DB::table('tblDynamicFields')->where(['FieldSlug'=>'CustomerID'])->pluck('DynamicFieldsID');
					$check = DynamicFieldsValue::where(['DynamicFieldsID'=>$FieldsID , 'FieldValue' => $CustomerID])->where('ParentID', '!=' ,$accountInfo->AccountID)->count();
					if($check > 0){
						return Response::json(["ErrorMessage" => Codes::$Code1063[1]],Codes::$Code1063[0]);
					}
				}
			}


			if (isset($accountData['PaymentMethodID']) && !empty($accountData['PaymentMethodID'])) {
				$data['PaymentMethod'] = $accountData['PaymentMethodID'];

				if (isset($data['PaymentMethod']) && $data['PaymentMethod'] != '') {
					if ($data['PaymentMethod'] <0 || $data['PaymentMethod'] > count(AccountsApiController::$API_PaymentMethod)) {
						return Response::json(["ErrorMessage" => Codes::$Code1020[1]],Codes::$Code1020[0]);

					}
				}

				try {
					TimeZone::$timeZones[$data['TimeZone']];
				}catch(Exception $ex) {
					unset($data['TimeZone']);
				}

				$data['PaymentMethod'] = AccountsApiController::$API_PaymentMethod[$data['PaymentMethod']];

				if (isset($data['PaymentMethod']) ) {
					$BankPaymentDetails['CardToken'] = isset($accountData['CardToken']) ? $accountData['CardToken'] : '' ;

					if ($data['PaymentMethod'] == "Stripe" && empty($BankPaymentDetails['CardToken'] )) {

						$BankPaymentDetails['CardNumber'] = isset($accountData['CardNumber']) ? $accountData['CardNumber'] : '' ;
						$BankPaymentDetails['CardType'] = isset($accountData['CardType']) ? $accountData['CardType'] : '' ;//Discover,MasterCard,Visa
						$BankPaymentDetails['ExpirationMonth'] = isset($accountData['ExpirationMonth']) ? $accountData['ExpirationMonth'] : '' ;
						$BankPaymentDetails['ExpirationYear'] = isset($accountData['ExpirationYear']) ? $accountData['ExpirationYear'] : '' ;
						$BankPaymentDetails['NameOnCard'] = isset($accountData['NameOnCard']) ? $accountData['NameOnCard'] : '' ;
						$BankPaymentDetails['CVVNumber'] = isset($accountData['CVVNumber']) ? $accountData['CVVNumber'] : '' ;
						$CardValidationResponse = AccountPayout::cardValidation($BankPaymentDetails);
						if ($CardValidationResponse["status"] == "failed") {
							return Response::json(["ErrorMessage" => $CardValidationResponse["message"]],Codes::$Code400[0]);
						}
						$CardType = array("Discover", "MasterCard", "Visa");
						if (!in_array($BankPaymentDetails['CardType'], $CardType)) {
							return Response::json(["ErrorMessage" => Codes::$Code1036[1]],Codes::$Code1036[0]);
						}
					}else if ($data['PaymentMethod'] == "StripeACH" && empty($BankPaymentDetails['CardToken'] )) {
						$BankPaymentDetails['AccountNumber'] = isset($accountData['AccountNumber']) ? $accountData['AccountNumber'] : '' ;
						$BankPaymentDetails['RoutingNumber'] = isset($accountData['RoutingNumber']) ? $accountData['RoutingNumber'] : '' ;
						$BankPaymentDetails['AccountHolderType'] = isset($accountData['AccountHolderType']) ? $accountData['AccountHolderType'] : '' ;//company,individual
						$BankPaymentDetails['AccountHolderName'] = isset($accountData['AccountHolderName']) ? $accountData['AccountHolderName'] : '' ;
						$validator = Validator::make($BankPaymentDetails, AccountPayout::$AccountPayoutBankRules);
						if ($validator->fails()) {
							$errors = "";
							foreach ($validator->messages()->all() as $error) {
								$errors .= $error . "<br>";
							}
							return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);

						}
						$AccountHolderType = array("individual","company");
						if (!in_array($BankPaymentDetails['AccountHolderType'], $AccountHolderType)) {
							return Response::json(["ErrorMessage" => Codes::$Code1037[1]],Codes::$Code1037[0]);
						}
					}else if ($data['PaymentMethod'] == "Ingenico") {
						$rules = [];
						$rules = array(
							'CardToken'         => 'required',
							'CardHolderName'    => 'required',
							'ExpirationMonth'   => 'required|numeric',
							'ExpirationYear'    => 'required|numeric',
							'LastDigit'         => 'required|digits:4',
						);

						$PaymentProfile['CardToken'] = isset($accountData['CardToken']) ? $accountData['CardToken'] : '' ;
						$PaymentProfile['CardHolderName'] = isset($accountData['CardHolderName']) ? $accountData['CardHolderName'] : '' ;
						$PaymentProfile['ExpirationMonth'] = isset($accountData['ExpirationMonth']) ? $accountData['ExpirationMonth'] : '' ;
						$PaymentProfile['ExpirationYear'] = isset($accountData['ExpirationYear']) ? $accountData['ExpirationYear'] : '' ;
						$PaymentProfile['LastDigit'] = isset($accountData['LastDigit']) ? $accountData['LastDigit'] : '' ;
						$PaymentProfile['Title'] = isset($accountData['CardTitle']) ? $accountData['CardTitle'] : '' ;
						$validator = Validator::make($PaymentProfile, $rules);
						if ($validator->fails()) {
							$errors = "";
							foreach ($validator->messages()->all() as $error) {
								$errors .= $error . "<br>";
							}
							return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);

						}
					}else if ($data['PaymentMethod'] == "DirectDebit" || $data['PaymentMethod'] == "WireTransfer") {
						$rules = array(
							'BankAccount'       => 'required',
							'AccountHolderName' => 'required',
							'Title'             => 'required'
						);
						$messages = array(
							"Title.required" => "The Payment Title Field Is Required"
						);
						$PaymentProfile['BankAccount'] = isset($accountData['BankAccount']) ? $accountData['BankAccount'] : '' ;
						$PaymentProfile['BIC'] = isset($accountData['BIC']) ? $accountData['BIC'] : '' ;
						$PaymentProfile['AccountHolderName'] = isset($accountData['AccountHolderName']) ? $accountData['AccountHolderName'] : '' ;
						$PaymentProfile['MandateCode'] = isset($accountData['MandateCode']) ? $accountData['MandateCode'] : '' ;
						$PaymentProfile['Title'] = isset($accountData['Title']) ? $accountData['Title'] : '' ;

						$validator = Validator::make($PaymentProfile, $rules , $messages);
						if ($validator->fails()) {
							$errors = "";
							foreach ($validator->messages()->all() as $error){
								$errors .= $error."<br>";
							}

							return Response::json(["ErrorMessage" => $errors], Codes::$Code400[0]);
						}
					}
				}
			}

			if (isset($accountData['PayoutMethodID']) && !empty($accountData['PayoutMethodID'])) {
				$data['PayoutMethod'] = $accountData['PayoutMethodID'];
				if (isset($data['PayoutMethod']) && $data['PayoutMethod'] != '') {
					if ($data['PayoutMethod'] <0 || $data['PayoutMethod'] > count(AccountsApiController::$API_PayoutMethod)) {
						return Response::json(["ErrorMessage" => Codes::$Code1058[1]],Codes::$Code1058[0]);

					}
				}

				try {
					TimeZone::$timeZones[$data['TimeZone']];
				}catch(Exception $ex) {
					unset($data['TimeZone']);
				}

				$data['PayoutMethod'] = AccountsApiController::$API_PayoutMethod[$data['PayoutMethod']];
				if (isset($data['PayoutMethod']) ) {
					if ($data['PayoutMethod'] == "WireTransfer") {
						$rules = array(
							'BankAccount'       => 'required',
							'AccountHolderName' => 'required',
							'Title'             => 'required'
						);

						$messages = array(
							"Title.required" => "The Payout Title Field Is Required"
						);
						$PayoutProfile['BankAccount'] = isset($accountData['PayoutBankAccount']) ? $accountData['PayoutBankAccount'] : '' ;
						$PayoutProfile['BIC'] = isset($accountData['PayoutBIC']) ? $accountData['PayoutBIC'] : '' ;
						$PayoutProfile['AccountHolderName'] = isset($accountData['PayoutAccountHolderName']) ? $accountData['PayoutAccountHolderName'] : '' ;
						$PayoutProfile['MandateCode'] = isset($accountData['PayoutMandateCode']) ? $accountData['PayoutMandateCode'] : '' ;
						$PayoutProfile['Title'] = isset($accountData['PayoutTitle']) ? $accountData['PayoutTitle'] : '' ;

						$validator = Validator::make($PayoutProfile, $rules,$messages);
						if ($validator->fails()) {
							$errors = "";
							foreach ($validator->messages()->all() as $error){
								$errors .= $error."<br>";
							}

							return Response::json(["ErrorMessage" => $errors], Codes::$Code400[0]);
						}
					}
				}
			}
			
			if(isset($accountData['AutoTopup']) && $accountData['AutoTopup'] > 1){
				return Response::json(["ErrorMessage" => 'Auto Top Up Value Should Be 0 Or 1'], Codes::$Code400[0]);
			}
			if(isset($accountData['AutoOutpayment']) && $accountData['AutoOutpayment'] > 1){
				return Response::json(["ErrorMessage" => 'Auto Out Payment Value Should Be 0 Or 1'], Codes::$Code400[0]);
			}

			if (!empty($accountData['AutoTopup']) && $accountData['AutoTopup'] == 1) {
				$rules = [];
				$rules['MinThreshold'] = 'required|numeric';
				$rules['TopupAmount'] = 'required|numeric';
				$messages = array(
					'MinThreshold.required' =>'MinThreshold field is required if AutoTopup is ON',
					'TopupAmount.required' =>'TopupAmount field is required if AutoTopup is ON',

				);
				$validator = Validator::make($accountData, $rules, $messages);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);

				}
			}
			if (!empty($accountData['AutoOutpayment']) && $accountData['AutoOutpayment'] == 1) {
				$rules = [];
				$rules['OutPaymentThreshold'] = 'required|numeric';
				$rules['OutPaymentAmount'] = 'required|numeric';
				$messages = array(
					'OutPaymentThreshold.required' =>'OutPaymentThreshold field is required if AutoOutpayment is ON',
					'OutPaymentAmount.required' =>'OutPaymentAmount field is required if AutoOutpayment is ON',

				);
				$validator = Validator::make($accountData, $rules, $messages);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);

				}
			}

			if (isset($accountData['AutoTopup']) && $accountData['AutoTopup'] == 1 || isset($accountData['AutoOutpayment']) && $accountData['AutoOutpayment'] == 1)
			{
				$AccountPaymentAutomation['AccountID'] = $accountInfo->AccountID;
				if(isset($accountData['AutoTopup'])){
					$AccountPaymentAutomation['AutoTopup']= isset($accountData['AutoTopup']) ? $accountData['AutoTopup'] :'';
				}
				if(isset($accountData['MinThreshold']) && $accountData['AutoTopup'] == 1){
					$AccountPaymentAutomation['MinThreshold']= $accountData['MinThreshold'];

				}
				if(isset($accountData['TopupAmount']) && $accountData['AutoTopup'] == 1){
					$AccountPaymentAutomation['TopupAmount'] = $accountData['TopupAmount'];

				}
				if(isset($accountData['AutoOutpayment'])){
					$AccountPaymentAutomation['AutoOutpayment']= isset($accountData['AutoOutpayment']) ? $accountData['AutoOutpayment'] : 0;

				}
				if(isset($accountData['OutPaymentThreshold']) && $accountData['AutoOutpayment'] == 1){
					$AccountPaymentAutomation['OutPaymentThreshold'] = $accountData['OutPaymentThreshold'];

				}
				if(isset($accountData['OutPaymentAmount']) && $accountData['AutoOutpayment'] == 1){
					$AccountPaymentAutomation['OutPaymentAmount'] = $accountData['OutPaymentAmount'];

				}

				$automation = AccountPaymentAutomation::where('AccountID' , $accountInfo->AccountID)->first();
				if($automation){
					$automation->update($AccountPaymentAutomation);
				}else{
					AccountPaymentAutomation::create($AccountPaymentAutomation);
				}
			}

			DB::beginTransaction();

			if (isset($accountData['AccountDynamicFieldUpdate'])) {

				$AccountReferenceArr = json_decode(json_encode($accountData['AccountDynamicFieldUpdate']),true);
				for ($i =0; $i < count($AccountReferenceArr);$i++) {
					$AccountReference = $AccountReferenceArr[$i];
					$DynamicFieldsID = DynamicFields::where([
						'CompanyID'=>User::get_companyID(),
						'Type'=>'account',
						'Status'=>1
					])
						->whereRaw('REPLACE(FieldName," ","") = '. "'". str_replace(" ", "", $AccountReference['Name']) . "'")
						->pluck('DynamicFieldsID');
					$DynamicFieldsValue = DynamicFieldsValue::where(['ParentID'=>$accountInfo->AccountID,'DynamicFieldsID'=>$DynamicFieldsID])->first();
					$DynamicFields['ParentID']  = $accountInfo->AccountID;
					$DynamicFields['DynamicFieldsID'] = $DynamicFieldsID;
					$DynamicFields['CompanyID']  = $CompanyID;
					$DynamicFields['created_at'] = $date;
					$DynamicFields['created_by'] = $CreatedBy;
					$DynamicFields['FieldValue'] = $AccountReference["Value"];
					if (isset($DynamicFieldsValue)) {
						$DynamicFieldsUpdate['FieldValue'] = $AccountReference["Value"];
						$DynamicFieldsValue->update($DynamicFieldsUpdate);
					} else {
						DB::table('tblDynamicFieldsValue')->insert($DynamicFields);
					}
				}
			}

			$TaxRateCalculation = [];
			$TaxRateCalculation['CompanyID'] = $CompanyID;
			$TaxRateCalculation['Country'] = isset($data['Country']) ? $data['Country']  : $accountInfo->Country;
			$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),
				'Type'=>'account','Status'=>1])
				->whereRaw('REPLACE(FieldName," ","") = '. "'". str_replace(" ", "", "Register Dutch Foundation") . "'")
				->pluck('DynamicFieldsID');
			$DynamicFieldsValue = DynamicFieldsValue::where('CompanyID',$CompanyID)
				->where('ParentID',$accountInfo->AccountID)
				->where('DynamicFieldsID',$DynamicFieldsID)->pluck('FieldValue');
			$TaxRateCalculation['RegisterDutchFoundation'] = !empty($DynamicFieldsValue) ? $DynamicFieldsValue : 0;
			$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),
				'Type'=>'account','Status'=>1])
				->whereRaw('REPLACE(FieldName," ","") = '. "'". str_replace(" ", "", "Dutch Provider") . "'")
				->pluck('DynamicFieldsID');
			$DynamicFieldsValue = DynamicFieldsValue::where('CompanyID',$CompanyID)
				->where('ParentID',$accountInfo->AccountID)
				->where('DynamicFieldsID',$DynamicFieldsID)->pluck('FieldValue');
			$TaxRateCalculation['DutchProvider'] = !empty($DynamicFieldsValue) ? $DynamicFieldsValue : 0;
			$TaxRateCalculation['IsCustomer']    = $accountInfo->IsCustomer;
			$TaxRateCalculation['IsReseller'] = $accountInfo->IsReseller;

			$data['TaxRateID'] = $this->getAccountTaxes($TaxRateCalculation , $accountInfo);
			$accountInfo->update($data);

			if(empty($accountInfo->BillingAddress1) && empty($accountInfo->BillingAddress2) && empty($accountInfo->BillingAddress3) && empty($accountInfo->BillingCity) && empty($accountInfo->BillingPostCode) && empty($accountInfo->BillingCountry)){
				$accountInfo->update(['DifferentBillingAddress' => 0]);
			}


			if (isset($data['PaymentMethod'])) {
				if ($data['PaymentMethod'] == "Stripe" || $data['PaymentMethod'] == "StripeACH") {
					$AccountPayoutSql = '';
					if ($data['PaymentMethod'] == "Stripe") {
						$AccountPayoutSql = AccountPayout::where(['AccountID' => $accountInfo->AccountID, 'Type' => 'card', 'Status' => '1'])->first();
					} else if ($data['PaymentMethod'] == "StripeACH") {
						$AccountPayoutSql = AccountPayout::where(['AccountID' => $accountInfo->AccountID, 'Type' => 'bank', 'Status' => '1'])->first();
					}
					if (isset($AccountPayoutSql)) {
						$AccountPayout['Status'] = 0;
						$AccountPayoutSql->update($AccountPayout);
					}
					$BankPaymentDetails['PaymentGatewayID'] = PaymentGateway::getPaymentGatewayIDByName("Stripe");
					$BankPaymentDetails['CompanyID'] = $CompanyID;
					if (!empty($BankPaymentDetails['CardNumber'])) {
						$BankPaymentDetails['PayoutType'] = "card";
						$BankPaymentDetails['Title'] =isset($BankPaymentDetails['NameOnCard']) ? $BankPaymentDetails['NameOnCard'] : 'CardTitle';
					} else {
						$BankPaymentDetails['Title'] =isset($BankPaymentDetails['AccountHolderName']) ? $BankPaymentDetails['AccountHolderName'] : 'AccountTitle';
						$BankPaymentDetails['PayoutType'] = "bank";
					}
					$BankPaymentDetails['AccountID'] = $accountInfo->AccountID;

					$BankPaymentDetails['CustomerAccountName'] = $accountInfo->AccountName;

					$PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($BankPaymentDetails['PaymentGatewayID']);
					$PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $CompanyID);
					if (empty($BankPaymentDetails['CardToken'])) {
						$AccountResponse = $PaymentIntegration->createAccount($BankPaymentDetails);
					}else {
						$AccountResponse = $PaymentIntegration->createAccountWithToken($BankPaymentDetails);
					}
					$AccountResponse = $AccountResponse->getContent();
					$AccountResponse = json_decode($AccountResponse);
					$AccountResponse = json_decode(json_encode($AccountResponse), true);
					$AccountResponse = json_decode(json_encode($AccountResponse), true);
					if ($AccountResponse["status"] == 'failed') {
						DB::rollback();
						return Response::json(["ErrorMessage" => $AccountResponse["message"]], Codes::$Code1033[0]);
					}

				}else if ($data['PaymentMethod'] == "Ingenico") {
					$isDefault = 1;
					$PaymentGatewayID = 10;
					$count = AccountPaymentProfile::where(['AccountID' =>  $accountInfo->AccountID])
						->where(['CompanyID' => $CompanyID])
						->where(['PaymentGatewayID' => $PaymentGatewayID])
						->where(['isDefault' => 1])
						->count();

					if($count>0){
						$isDefault = 0;
					}

					AccountPaymentProfile::where(['AccountID' =>  $accountInfo->AccountID])
						->where(['CompanyID' => $CompanyID])
						->where(['PaymentGatewayID' => $PaymentGatewayID])
						->delete();

					$option = array(
						'CardToken'       => $PaymentProfile['CardToken'],
						'CardHolderName'  => $PaymentProfile['CardHolderName'],
						'ExpirationMonth' => $PaymentProfile['ExpirationMonth'],
						'ExpirationYear'  => $PaymentProfile['ExpirationYear'],
						'LastDigit'       => $PaymentProfile['LastDigit'],
					);

					$CardDetail = array('Title' => $PaymentProfile['Title'],
						'Options' => json_encode($option),
						'Status' => 1,
						'isDefault' => $isDefault,
						'created_by' => $CreatedBy,
						'CompanyID' => $CompanyID,
						'AccountID' =>  $accountInfo->AccountID,
						'PaymentGatewayID' => $PaymentGatewayID);
					AccountPaymentProfile::create($CardDetail);
				}else if ($data['PaymentMethod'] == "DirectDebit") {
					$isDefault = 1;
					$PaymentGatewayID = 12;
					$count = AccountPaymentProfile::where(['AccountID' => $accountInfo->AccountID])
						->where(['CompanyID' => $CompanyID])
						->where(['PaymentGatewayID' => $PaymentGatewayID])
						->where(['isDefault' => 1])
						->count();

					if($count>0){
						$isDefault = 0;
					}

					AccountPaymentProfile::where(['AccountID' =>  $accountInfo->AccountID])
						->where(['CompanyID' => $CompanyID])
						->where(['PaymentGatewayID' => $PaymentGatewayID])
						->delete();

					$option = array(
						'BankAccount'       => $PaymentProfile['BankAccount'],
						'BIC'               => $PaymentProfile['BIC'],
						'AccountHolderName' => $PaymentProfile['AccountHolderName'],
						'MandateCode'       => $PaymentProfile['MandateCode'],
					);
					$CardDetail = array('Title' => $PaymentProfile['Title'],
						'Options' => json_encode($option),
						'Status' => 1,
						'isDefault' => $isDefault,
						'created_by' => $CreatedBy,
						'CompanyID' => $CompanyID,
						'AccountID' => $accountInfo->AccountID,
						'PaymentGatewayID' => $PaymentGatewayID);
					AccountPaymentProfile::create($CardDetail);
				}else if ($data['PaymentMethod'] == "WireTransfer") {
					$isDefault = 1;
					$PaymentGatewayID = 11;
					$count = AccountPaymentProfile::where(['AccountID' => $accountInfo->AccountID])
						->where(['CompanyID' => $CompanyID])
						->where(['PaymentGatewayID' => $PaymentGatewayID])
						->where(['isDefault' => 1])
						->count();

					if($count>0){
						$isDefault = 0;
					}

					AccountPaymentProfile::where(['AccountID' =>  $accountInfo->AccountID])
						->where(['CompanyID' => $CompanyID])
						->where(['PaymentGatewayID' => $PaymentGatewayID])
						->delete();

					$option = array(
						'BankAccount'       => $PaymentProfile['BankAccount'],
						'BIC'               => $PaymentProfile['BIC'],
						'AccountHolderName' => $PaymentProfile['AccountHolderName'],
						'MandateCode'       => $PaymentProfile['MandateCode'],
					);
					$CardDetail = array('Title' => $PaymentProfile['Title'],
						'Options' => json_encode($option),
						'Status' => 1,
						'isDefault' => $isDefault,
						'created_by' => $CreatedBy,
						'CompanyID' => $CompanyID,
						'AccountID' => $accountInfo->AccountID,
						'PaymentGatewayID' => $PaymentGatewayID);
					AccountPaymentProfile::create($CardDetail);
				}
			}


			if (isset($data['PayoutMethod'])) {
				if ($data['PayoutMethod'] == "WireTransfer") {
					$isDefault = 1;
					$PaymentGatewayID = 11;
					$count = AccountPayout::where(['AccountID' => $accountInfo->AccountID])
						->where(['CompanyID' => $CompanyID])
						->where(['PaymentGatewayID' => $PaymentGatewayID])
						->where(['isDefault' => 1])
						->count();

					if($count>0){
						$isDefault = 0;
					}

					AccountPayout::where(['AccountID' =>  $accountInfo->AccountID])
						->where(['CompanyID' => $CompanyID])
						->where(['PaymentGatewayID' => $PaymentGatewayID])
						->delete();

					$option = array(
						'BankAccount'       => $PayoutProfile['BankAccount'],
						'BIC'               => $PayoutProfile['BIC'],
						'AccountHolderName' => $PayoutProfile['AccountHolderName'],
						'MandateCode'       => $PayoutProfile['MandateCode'],
					);
					$CardDetail = array('Title' => $PayoutProfile['Title'],
						'Options' => json_encode($option),
						'Status' => 1,
						'isDefault' => $isDefault,
						'created_by' => $CreatedBy,
						'CompanyID' => $CompanyID,
						'AccountID' => $accountInfo->AccountID,
						'PaymentGatewayID' => $PaymentGatewayID);
					AccountPayout::create($CardDetail);
				}
			}
			$AccountSuccessMessage['AccountID'] = $accountInfo->AccountID;

			DB::commit();
			return Response::json($AccountSuccessMessage,Codes::$Code200[0]);


		} catch (Exception $ex) {
			DB::rollback();
			Log::error("CreateAccountAPI Exception" . $ex->getTraceAsString());
			return Response::json(["ErrorMessage" => Codes::$Code500[1]],Codes::$Code500[0]);

		}
	}

	public function getPaymentMethodList()
	{
		Log::info('getPaymentMethodList for Account.');
		return Response::json(AccountsApiController::$API_PaymentMethod,Codes::$Code200[0]);

	}

	public function GetAccount()
	{
		$data = Input::all();
		try {
			$rules = array(
				'AccountNo' => 'required_without_all:AccountDynamicField,AccountID',
				'AccountID' => 'required_without_all:AccountDynamicField,AccountNo',
				'AccountDynamicField' => 'required_without_all:AccountNo,AccountID',
			);


			$validator = Validator::make($data, $rules);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["status" => "failed", "message" => $errors]);
			}

			if (!empty($data['AccountDynamicField'])) {
				$AccountIDRef = '';
					$AccountIDRef = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
					if (empty($AccountIDRef)) {
						return Response::json(["status" => "failed", "message" => "Please provide the correct Account ID"]);
					}


				$data['AccountID'] = $AccountIDRef;

				if (empty($data['AccountID'])) {
					return Response::json(["status" => "failed", "message" => "No Account Found for the Reference"]);
				}
			}

			if (!empty($data['AccountNo'])) {
				$Account = Account::where(array('Number' => $data['AccountNo']))->first();
			}else {
				$Account = Account::find($data['AccountID']);
			}

			if (count($Account) > 0) {
				return Response::json(["status"=>"success", "AccountID"=>$Account->AccountID,"AccountNo"=>$Account->Number]);
			} else {
				return Response::json(["status" => "failed", "message" => "Account not found against the reference"]);
			}
		}catch (Exception $ex) {
			Log::info('GetAccount:Exception.' . $ex->getTraceAsString());
			return Response::json(["status" => "failed", "message" => $ex->getMessage()]);
		}
	}


	// add Additional charges
	public function CreateCharge(){
		if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
		$recurringName = 'Recurring';
		$CompanyID=0;
		$AccountID=0;

		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
		}
		//strtolower
		$data['ChargeCode'] = strtolower('One-Off');
		
		if(!empty($data['AccountID'])) {
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
			}else {
				return Response::json(["ErrorMessage" => "AccountID must be a mumber."],Codes::$Code400[0]);
			}	
		}else if(!empty($data['AccountNo'])){
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo can not be empty."],Codes::$Code400[0]);
			}
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
		}else{
			return Response::json(["ErrorMessage" => "AccountID or AccountNo or AccountDynamicField Required."],Codes::$Code400[0]);
		}

		$Account = Account::where(["AccountID" => $AccountID]);
		if($Account->count() > 0){
			$Account = $Account->first();
			$CompanyID = $Account->CompanyId;
			$AccountID = $Account->AccountID;
		}

		//Validation
		$rules = array(
			'Description' 	=> 'required',
			'ChargeType' 	=> 'required|in:0,1',
			'Currency' 		=> 'required',
			'Amount' 		=> 'required|numeric|min:0'
		);

		if(isset($data['ChargeType']) && intval($data['ChargeType']) != 0){
			$rules['StartDate'] = 'required|date|date_format:Y-m-d';
			$rules['EndDate'] 	= 'required|date|date_format:Y-m-d';
			$rules['Frequency'] = 'required|in:0,1,2,3';
		} else {
			$rules['Date'] = 'required|date|date_format:Y-m-d';
		}

		$validator = Validator::make($data, $rules);
		if ($validator->fails()) {
			return Response::json([
				"ErrorMessage" => $validator->messages()->first()
			],Codes::$Code400[0]);
		}
		if(!empty($data['StartDate']) && !empty($data['EndDate'])) {
			$startDate = strtotime($data['StartDate']);
			$endDate = strtotime($data['EndDate']);
			if($startDate > $endDate) {
				return Response::json(["ErrorMessage"=>"Start Date must be less than Current Date."],Codes::$Code400[0]);
			}
		}
		$CurrentDate = date('Y-m-d H:i:s');
		$CreatedBy 	 = 'API';
		try {
			DB::connection('sqlsrv2')->beginTransaction();
			if (!empty($AccountID) && !empty($CompanyID)) {
				$CurrencyID = Currency::where(["Code" => $data['Currency']])->pluck('CurrencyID');
				if (!empty($CurrencyID)) {
					// if One-Off Cost
					if($data['ChargeType'] == 0) {
						$product = Product::where('Code', Product::ONEOFFCHARGECODE)
							->where("CompanyId", $CompanyID);

						if ($product->count() > 0) {
							$product = $product->first();
							if ($product->Active != 1) {
								$product->Active = 1;
								$product->save();
							}
						} else {
							// add product
							$product_data['CompanyId'] 		= $CompanyID;
							//$product_data['CurrencyID'] 	= $CurrencyID;
							$product_data['Name'] 			= Product::$AllProductTypes[Product::ONEOFFCHARGE];
							$product_data['Code'] 			= Product::ONEOFFCHARGECODE;
							$product_data['Description'] 	= $data['Description'];
							$product_data['Amount'] 		= $data['Amount'];
							$product_data['Active'] 		= 1;
							$product_data['CreatedBy'] 		= $CreatedBy;
							$product_data['created_at'] 	= $CurrentDate;

							$product = Product::create($product_data);
						}

						$ProductID = $product->ProductID;

						$ChargeData['AccountID'] 	= $AccountID;
						$ChargeData['ProductID'] 	= $ProductID;
						$ChargeData['Price'] 		= $data['Amount'];
						$ChargeData['Description']	= $data['Description'];
						$ChargeData['DiscountType']	= 'Flat';
						$ChargeData['TaxAmount']	= 0;
						$ChargeData['Qty'] 			= 1;
						$ChargeData['Date'] 		= !empty($data['Date']) ? date('Y-m-d', strtotime($data['Date'])) : $ChargeData;
						$ChargeData['CreatedBy'] 	= $CreatedBy;
						$ChargeData['created_at'] 	= $CurrentDate;
						$ChargeData['CurrencyID'] 	= $CurrencyID;
						$ChargeData['AccountServiceID'] = 0;
						$ChargeData['ServiceID'] 	= 0;

						if (AccountOneOffCharge::create($ChargeData)) {
							DB::connection('sqlsrv2')->commit();
							return Response::json((object)['status' => 'success'], Codes::$Code200[0]);
						} else {
							return Response::json(array("ErrorMessage" => "Problem Inserting Additional Charge."), Codes::$Code500[0]);
						}
					} else {

						if (strtotime($data['EndDate']) < strtotime($data['StartDate'])) {
							return  Response::json(["ErrorMessage" => "End date should be greater then or equal to start date."], Codes::$Code400[0]);
						}

						$frequency = (int)$data['Frequency'];
						$frequencyArr = ['Daily', 'Weekly', 'Monthly', 'Yearly'];
						$feeArr = ['DailyFee', 'WeeklyFee', 'MonthlyFee', 'AnnuallyFee'];
						$frequencyType = $frequencyArr[$frequency];
						$feeType = $feeArr[$frequency];

						// add subscription/recurring
						$recurring = BillingSubscription::where(["CompanyId" => $CompanyID, "Name" => $recurringName]);
						$Costs = AccountSubscription::calculateCost($feeType, $data['Amount']);

						Log::info("Account One Off Charge ." . $recurring->count());
						if ($recurring->count() > 0) {
							$recurring = $recurring->first();
						} else {
							$recurring_data['CompanyId'] 				= $CompanyID;
							$recurring_data['Name'] 					= $recurringName;
							$recurring_data['Description'] 				= $recurringName;
							$recurring_data['InvoiceLineDescription'] 	= $data['Description'];
							$recurring_data['CreatedBy'] 				= $CreatedBy;
							$recurring_data['created_at'] 				= $CurrentDate;
							$recurring_data['Advance'] 					= 1;
							$ChargeData['OneOffCurrencyID'] 			= $CurrencyID;
							$ChargeData['RecurringCurrencyID'] 			= $CurrencyID;


							$recurring_data['DailyFee'] 				= $Costs['DailyFee'];
							$recurring_data['WeeklyFee'] 				= $Costs['WeeklyFee'];
							$recurring_data['MonthlyFee'] 				= $Costs['MonthlyFee'];
							$recurring_data['QuarterlyFee'] 			= $Costs['QuarterlyFee'];
							$recurring_data['AnnuallyFee'] 				= $Costs['AnnuallyFee'];

							$recurring = BillingSubscription::create($recurring_data);
							Log::info("Account One Off Charge created." );
						}

						$AccountRecurringID = $recurring->SubscriptionID;
						Log::info("Account One Off Charge created." . $AccountRecurringID);

						$ChargeData['AccountID'] 		= $AccountID;
						$ChargeData['SubscriptionID'] 	= $AccountRecurringID;
						$ChargeData['StartDate'] 		= !empty($data['StartDate']) ? date('Y-m-d', strtotime($data['StartDate'])) : $CurrentDate;
						$ChargeData['EndDate'] 			= !empty($data['EndDate']) ? date('Y-m-d', strtotime($data['EndDate'])) : $ChargeData;
						$ChargeData['Qty'] 				= 1;
						$ChargeData['CreatedBy'] 		= $CreatedBy;
						$ChargeData['created_at'] 		= $CurrentDate;
						$ChargeData['OneOffCurrencyID'] = $CurrencyID;
						$ChargeData['RecurringCurrencyID'] = $CurrencyID;
						$ChargeData['InvoiceDescription']  = $data['Description'];
						$ChargeData['DailyFee'] 		= $Costs['DailyFee'];
						$ChargeData['WeeklyFee'] 		= $Costs['WeeklyFee'];
						$ChargeData['MonthlyFee'] 		= $Costs['MonthlyFee'];
						$ChargeData['QuarterlyFee'] 	= $Costs['QuarterlyFee'];
						$ChargeData['AnnuallyFee'] 		= $Costs['AnnuallyFee'];
						$ChargeData['ActivationFee'] 	= 1;
						$ChargeData['DiscountType'] 	= 'Flat';
						$ChargeData['Frequency'] 		= $frequencyType;
						$ChargeData['AccountServiceID'] = 0;

						Log::info("Account One Off Charge created." . print_r($ChargeData,true));
						if (AccountSubscription::create($ChargeData)) {
							DB::connection('sqlsrv2')->commit();
							return Response::json((object)['status' => 'success'],Codes::$Code200[0]);
						} else {
							return Response::json(array("ErrorMessage" => "Problem Inserting Additional Charge."), Codes::$Code500[0]);
						}
					}
				} else {
					return Response::json(["ErrorMessage" => "Currency Not Found"], Codes::$Code400[0]);
				}
			} else {
				return Response::json(["ErrorMessage" => "Account or Company Not Found"], Codes::$Code400[0]);
			}
		} catch (Exception $e) {
			DB::connection('sqlsrv2')->rollback();
			Log::info($e->getTraceAsString());
			$reseponse = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
			return Response::json($reseponse, Codes::$Code500[0]);
		}
	}

	// New API to create account service and add number by vasim seta @2019-12-30
	public function addNewAccountService() {
		$post_vars 	= json_decode(file_get_contents("php://input"));
		$data		= json_decode(json_encode($post_vars),true);

		$rules = array(
			'AccountID' 						=> 'required_without_all:AccountDynamicField,AccountNo|numeric',
			/*'AccountNo' 						=> 'required_without_all:AccountDynamicField,AccountID',
			'AccountDynamicField' 				=> 'required_without_all:AccountNo,AccountID',*/
			'OrderID'							=> 'required|numeric',
			'Numbers'							=> 'required|array',
		);

		$msg = array(
			'AccountID.required_without_all'  	=> "Any one field Account Number, AccountID or AccountDynamicField is required.",
			'AccountID.numeric'  				=> "The AccountID must be a number.",
			'OrderID.required'  				=> "The OrderID field is required.",
			'OrderID.numeric'  					=> "The OrderID must be a number.",
			'Numbers.required'  				=> "The Numbers field is required.",
			'Numbers.array'  					=> "The Numbers must be an array.",
		);

		if(isset($data['Numbers']) && is_array($data['Numbers']) && count($data['Numbers']) > 0) {
			$rules_numbers = $msg__numbers = [];
			foreach ($data['Numbers'] as $key => $value) {
				$rules_numbers = array(
					'Numbers.'.$key.'.NumberPurchased'			=> 'required|numeric',
					'Numbers.'.$key.'.ProductID'				=> 'required|numeric',
					'Numbers.'.$key.'.PackageProductID'			=> 'required|numeric',
					'Numbers.'.$key.'.InboundTariffCategoryID'	=> 'required|numeric',
					'Numbers.'.$key.'.PackageContractID'		=> 'required|numeric',
					'Numbers.'.$key.'.NumberContractID'			=> 'required|numeric',
					'Numbers.'.$key.'.ContractStartDate'		=> 'required|date|date_format:Y-m-d|after:'.date('Y-m-d',strtotime("-1 days")),
					'Numbers.'.$key.'.ContractEndDate'			=> 'required|date|date_format:Y-m-d|after:Numbers.'.$key.'.ContractStartDate',
					'Numbers.'.$key.'.PackageStartDate'			=> 'required|date|date_format:Y-m-d|after:'.date('Y-m-d',strtotime("-1 days")),
					'Numbers.'.$key.'.PackageEndDate'			=> 'required|date|date_format:Y-m-d|after:Numbers.'.$key.'.PackageStartDate',
				);

				$msg__numbers =  [
					'Numbers.'.$key.'.NumberPurchased.required'  		=> "The Numbers[".$key."][NumberPurchased] field is required.",
					'Numbers.'.$key.'.NumberPurchased.numeric'  		=> "The Numbers[".$key."][NumberPurchased] must be a number.",
					'Numbers.'.$key.'.ProductID.required'				=> "The Numbers[".$key."][ProductID] field is required.",
					'Numbers.'.$key.'.ProductID.numeric'  				=> "The Numbers[".$key."][ProductID] must be a number.",
					'Numbers.'.$key.'.PackageProductID.required'		=> "The Numbers[".$key."][PackageProductID] field is required.",
					'Numbers.'.$key.'.PackageProductID.numeric'  		=> "The Numbers[".$key."][PackageProductID] must be a number.",
					'Numbers.'.$key.'.InboundTariffCategoryID.required'	=> "The Numbers[".$key."][InboundTariffCategoryID] field is required.",
					'Numbers.'.$key.'.InboundTariffCategoryID.numeric'  => "The Numbers[".$key."][InboundTariffCategoryID] must be a number.",
					'Numbers.'.$key.'.PackageContractID.required'		=> "The Numbers[".$key."][PackageContractID] field is required.",
					'Numbers.'.$key.'.PackageContractID.numeric'  		=> "The Numbers[".$key."][PackageContractID] must be a number.",
					'Numbers.'.$key.'.NumberContractID.required'		=> "The Numbers[".$key."][NumberContractID] field is required.",
					'Numbers.'.$key.'.NumberContractID.numeric'  		=> "The Numbers[".$key."][NumberContractID] must be a number.",
					'Numbers.'.$key.'.ContractStartDate.required'		=> "The Numbers[".$key."][ContractStartDate] field is required.",
					'Numbers.'.$key.'.ContractStartDate.after'			=> "Past dates not allowed for Numbers[".$key."][ContractStartDate]",
					'Numbers.'.$key.'.ContractEndDate.required'			=> "The Numbers[".$key."][ContractEndDate] field is required.",
					'Numbers.'.$key.'.ContractEndDate.after'			=> "ContractEndDate must be a date after ContractStartDate.",
					'Numbers.'.$key.'.PackageStartDate.required'		=> "The Numbers[".$key."][PackageStartDate] field is required.",
					'Numbers.'.$key.'.PackageStartDate.after'			=> "Past dates not allowed for Numbers[".$key."][PackageStartDate]",
					'Numbers.'.$key.'.PackageEndDate.required'			=> "The Numbers[".$key."][PackageEndDate] field is required.",
					'Numbers.'.$key.'.PackageEndDate.after'				=> "PackageEndDate must be a date after PackageStartDate.",
				];
				$rules 	+= $rules_numbers;
				$msg 	+= $msg__numbers;
			}
		}

		$validator = Validator::make($data, $rules, $msg);

		if ($validator->fails()) {
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
		}

		$CompanyID=0;
		$AccountID=0;
		if(!empty($data['AccountID'])) {
			$AccountID = $data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo or AccountDynamicField Required."],Codes::$Code400[0]);
		}

		$Account = Account::find($AccountID);
		if($Account){
			$CompanyID 	= $Account->CompanyId;
			$AccountID 	= $Account->AccountID;
			$ProductData = [];

			// find package and access, termination ratetable for numbers and return error if not found.
			foreach ($data['Numbers'] as $key => $number_data) {
				//$Package_q = "SELECT RateTableId FROM tblPackage WHERE CompanyID=" . $CompanyID . " AND PackageId=" . $number_data['PackageId'];
				$Package_q = "SELECT p.RateTableId,p.PackageId
							  FROM tblDynamicFields df
							  INNER JOIN tblDynamicFieldsValue dfv ON dfv.DynamicFieldsID = df.DynamicFieldsID
							  INNER JOIN tblPackage p ON p.PackageId = dfv.ParentID
							  WHERE df.CompanyID = " . $CompanyID . " AND df.Type = 'package' AND df.FieldName = 'PackageProductID' AND dfv.FieldValue = '" . $number_data['PackageProductID'] . "'";

				$Package = DB::select($Package_q);

				if (!empty($Package[0]->RateTableId)) {
					$ProductData[$key]['PackageRateTableId'] = $Package[0]->RateTableId;
					$ProductData[$key]['PackageId'] = $Package[0]->PackageId;
				} else {
					return Response::json(["ErrorMessage" => "Package RateTable Not Found. PackageProductID: " . $number_data['PackageProductID']], Codes::$Code400[0]);
				}

				$ServiceTemplate_q = "SELECT it.RateTableId,st.OutboundRateTableId,st.City,st.Tariff,st.prefixName,st.country,st.countryCode,st.accessType
									  FROM tblDynamicFields df
									  INNER JOIN tblDynamicFieldsValue dfv ON dfv.DynamicFieldsID = df.DynamicFieldsID
									  INNER JOIN tblServiceTemplate st ON st.ServiceTemplateID = dfv.ParentID
									  LEFT JOIN tblServiceTemapleInboundTariff it ON it.ServiceTemplateID = st.ServiceTemplateID AND it.DIDCategoryId = " . $number_data['InboundTariffCategoryID'] . "
									  WHERE df.CompanyID = " . $CompanyID . " AND df.Type = 'serviceTemplate' AND df.FieldName = 'ProductID' AND dfv.FieldValue = '" . $number_data['ProductID'] . "'";

				$ServiceTemplate = DB::select($ServiceTemplate_q);
				if(empty($ServiceTemplate[0])) {
					return Response::json(["ErrorMessage" => "Product not found. ProductID: " . $data['ProductID']], Codes::$Code400[0]);
				}
				$ProductData[$key]['ServiceTemplate'] = $ServiceTemplate = $ServiceTemplate[0];

				if(empty($ServiceTemplate->country)) {
					return Response::json(["ErrorMessage" => "Country Not Found against ProductID: " . $number_data['ProductID']], Codes::$Code400[0]);
				}
				if(empty($ServiceTemplate->accessType)) {
					return Response::json(["ErrorMessage" => "AccessType Not Found against ProductID: " . $number_data['ProductID']], Codes::$Code400[0]);
				}
				if(empty($ServiceTemplate->prefixName)) {
					return Response::json(["ErrorMessage" => "Prefix Not Found against ProductID: " . $number_data['ProductID']], Codes::$Code400[0]);
				}

				if (!empty($ServiceTemplate->OutboundRateTableId) && !empty($ServiceTemplate->RateTableId)) {
					$ProductData[$key]['TerminationRateTableID'] = $ServiceTemplate->OutboundRateTableId;
					$ProductData[$key]['AccessRateTableID'] 	 = $ServiceTemplate->RateTableId;
				} else {
					if (empty($ServiceTemplate->OutboundRateTableId)) {
						return Response::json(["ErrorMessage" => "Termination RateTable Not Found. ProductID: " . $number_data['ProductID']], Codes::$Code400[0]);
					} else {
						return Response::json(["ErrorMessage" => "Access RateTable Not Found. ProductID: " . $number_data['ProductID'].", InboundTariffCategoryID: " . $number_data['InboundTariffCategoryID']], Codes::$Code400[0]);
					}
				}

				$AccountService = AccountService::where(['AccountID'=>$AccountID,'ServiceOrderID'=>$data['OrderID'],'Status'=>1,'CancelContractStatus'=>0]);

				// if AccountService exist then check below conditions
				// Date Period must not conflict for the same number, same account and same account service.
				if($AccountService->count() > 0) {
					$AccountService = $AccountService->first();

					// same condition as in front-end
					$checkCLIRateTable = CLIRateTable::where([
						'CompanyID' 		=> $CompanyID,
						'AccountID' 		=> $AccountID,
						'AccountServiceID' 	=> $AccountService->AccountServiceID,
						'CLI' 				=> $number_data['NumberPurchased'],
						'Status' 			=> 1
					])->where(function($q) use ($number_data) {
						$q->whereBetween('NumberStartDate', array($number_data['ContractStartDate'], $number_data['ContractEndDate']));
						$q->orWhereBetween('NumberEndDate', array($number_data['ContractStartDate'], $number_data['ContractEndDate']));
						$q->orWhereRaw("'".$number_data['ContractStartDate']."' between NumberStartDate and NumberEndDate");
					});

					// if number exist between given date
					if($checkCLIRateTable->count() > 0) {
						$date_error = 'Number '. $number_data['NumberPurchased'] . ' already exist between contract start date '.$number_data['ContractStartDate'] . ' and contract end date ' .$number_data['ContractEndDate'];
						return Response::json(["ErrorMessage" => $date_error],Codes::$Code400[0]);
					}
				}
			} // foreach

			$AllServices = Service::where('Status', 1);
			if($AllServices->count() > 0) {
				$ServiceID = $AllServices->first()->ServiceID;

				try {
					DB::beginTransaction();

					$AccountService = AccountService::where(['AccountID'=>$AccountID,'ServiceOrderID'=>$data['OrderID'],'Status'=>1,'CancelContractStatus'=>0]);

					$AccountServiceData = [];
					$AccountServiceData['CompanyID'] 			= $CompanyID;
					$AccountServiceData['AccountID'] 			= $AccountID;
					$AccountServiceData['ServiceID'] 			= $ServiceID;
					$AccountServiceData['ServiceOrderID'] 		= $data['OrderID'];
					$AccountServiceData['ServiceTitle'] 		= !empty($data['ServiceTitle']) ? trim($data['ServiceTitle']) : '';
					$AccountServiceData['ServiceDescription'] 	= !empty($data['ServiceDescription']) ? trim($data['ServiceDescription']) : '';
					$AccountServiceData['ServiceTitleShow'] 	= isset($data['ServiceTitleShow']) && $data['ServiceTitleShow'] == 1 ? 1 : 0;
					$AccountServiceData['Status'] 				= 1;

					if($AccountService->count() > 0) { // update if exist
						$AccountService = $AccountService->first();
						$AccountService->update($AccountServiceData);
					} else { // create if not exist
						$AccountService = AccountService::create($AccountServiceData);
					}

					if($AccountService) {
						foreach ($data['Numbers'] as $key => $number_data) {
							$data_pkg = [];
							$data_pkg['CompanyID'] 			= $CompanyID;
							$data_pkg['AccountID'] 			= $AccountID;
							$data_pkg['ServiceID'] 			= $ServiceID;
							$data_pkg['ContractID'] 		= $number_data['PackageContractID'];
							$data_pkg['PackageId'] 			= $ProductData[$key]['PackageId'];
							$data_pkg['PackageStartDate'] 	= $number_data['PackageStartDate'];
							$data_pkg['PackageEndDate'] 	= $number_data['PackageEndDate'];
							$data_pkg['AccountServiceID'] 	= $AccountService->AccountServiceID;
							$data_pkg['RateTableID'] 		= $ProductData[$key]['PackageRateTableId'];
							$data_pkg['Status'] 			= 1;

							$AccountServicePackage = AccountServicePackage::create($data_pkg);

							if ($AccountServicePackage) {
								$ProductCountry = Country::where(array('Country' => $ProductData[$key]['ServiceTemplate']->country));

								if($ProductCountry->count() == 0) {
									return Response::json(["ErrorMessage" => "Country Not Found against ProductID: " . $number_data['ProductID']], Codes::$Code400[0]);
								}
								$ProductCountry = $ProductCountry->first();

								$City 		= !empty($ProductData[$key]['ServiceTemplate']->City) ? $ProductData[$key]['ServiceTemplate']->City : '';
								$Tariff 	= !empty($ProductData[$key]['ServiceTemplate']->Tariff) ? $ProductData[$key]['ServiceTemplate']->Tariff : '';
								$accessType = !empty($ProductData[$key]['ServiceTemplate']->accessType) ? $ProductData[$key]['ServiceTemplate']->accessType : '';
								$prefixName = !empty($ProductData[$key]['ServiceTemplate']->prefixName) ? $ProductData[$key]['ServiceTemplate']->prefixName : '';
								$AreaPrefix = !empty($ProductData[$key]['ServiceTemplate']->countryCode) ? $ProductData[$key]['ServiceTemplate']->countryCode  : ''. ltrim($ProductData[$key]['ServiceTemplate']->prefixName, '0');

								$VendorID = RateTableDIDRate::Join('tblRate', 'tblRateTableDIDRate.RateID', '=', 'tblRate.RateID')
									->select(['tblRateTableDIDRate.VendorID'])
									->where([
										"tblRateTableDIDRate.RateTableId" 	=> $ProductData[$key]['AccessRateTableID'],
										"tblRateTableDIDRate.City" 			=> $City,
										"tblRateTableDIDRate.Tariff" 		=> $Tariff,
										"tblRateTableDIDRate.AccessType" 	=> $accessType,
										"tblRate.Code" 						=> $AreaPrefix
									])
									->where("tblRateTableDIDRate.EffectiveDate", '<=', date('Y-m-d'))
									->whereNotNull('tblRateTableDIDRate.MonthlyCost')
									->max('VendorID');
								$VendorID = !empty($VendorID) ? $VendorID : 0;

								$data_cli = [];
								$data_cli['CompanyID'] 				= $CompanyID;
								$data_cli['AccountID'] 				= $AccountID;
								$data_cli['ServiceID'] 				= $ServiceID;
								$data_cli['AccountServiceID'] 		= $AccountService->AccountServiceID;
								$data_cli['CLI'] 					= $number_data['NumberPurchased'];
								$data_cli['NumberStartDate'] 		= $number_data['ContractStartDate'];
								$data_cli['NumberEndDate'] 			= $number_data['ContractEndDate'];
								$data_cli['ContractID'] 			= $number_data['NumberContractID'];
								$data_cli['RateTableID'] 			= $ProductData[$key]['AccessRateTableID']; // Default Access Rate Table
								$data_cli['TerminationRateTableID'] = $ProductData[$key]['TerminationRateTableID']; // Default Termination Rate Table
								$data_cli['CountryID'] 				= $ProductCountry->CountryID;
								$data_cli['City'] 					= $City;
								$data_cli['Tariff'] 				= $Tariff;
								$data_cli['NoType'] 				= $accessType;
								$data_cli['PrefixWithoutCountry'] 	= $prefixName;
								$data_cli['Prefix'] 				= $AreaPrefix;
								$data_cli['VendorID'] 				= $VendorID;
								$data_cli['AccountServicePackageID']= $AccountServicePackage->AccountServicePackageID;

								CLIRateTable::create($data_cli);
							} else {
								return Response::json(["ErrorMessage" => "Error while creating Service Package."], Codes::$Code500[0]);
							}
						}

						DB::commit();
						return Response::json(["SuccessMessage" => "Account Service created successfully."],Codes::$Code200[0]);

					} else {
						return Response::json(["ErrorMessage"=>"Error while creating Account Service."],Codes::$Code500[0]);
					}
				} catch (Exception $e) {
					DB::rollback();
					Log::info($e->getTraceAsString());
					$response = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
					return Response::json($response, Codes::$Code500[0]);
				}
			} else {
				// No service exist error
				return Response::json(["ErrorMessage" => "Service Not Found"], Codes::$Code400[0]);
			}
		} else {
			// Account Not Found Error
			return Response::json(["ErrorMessage" => "Account Not Found"], Codes::$Code400[0]);
		}
	}

	// New API to update account service tariff by vasim seta @2020-01-01
	public function updateTariff() {
		if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
		$CompanyID=0;
		$AccountID=0;
		$AccountFindType = '';
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
		}
		
		if(!empty($data['AccountID'])) {
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
				$AccountFindType = 'AccountID';
			}else {
				return Response::json(["ErrorMessage"=>"AccountID must be a mumber."],Codes::$Code400[0]);
			}
		}else if(!empty($data['AccountNo'])){
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo can not be empty"],Codes::$Code400[0]);
			}
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
			$AccountFindType = 'AccountNo';
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			$AccountFindType = 'AccountDynamicField';
		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo or AccountDynamicField Required."],Codes::$Code400[0]);
		}
		$rules = array(
			'OrderID'							=> 'required|numeric',
			'NumberPurchased'					=> 'required|numeric',
			'ProductID'							=> 'required|numeric',
			'InboundTariffCategoryID'			=> 'required|numeric',
			'NumberContractID'					=> 'required|numeric',
			'NewNumberContractID'				=> 'required|numeric|in:1,4,7',
			'ContractStartDate'					=> 'required|date|date_format:Y-m-d|after:'.date('Y-m-d',strtotime("-1 days")),
			'ContractEndDate'					=> 'required|date|date_format:Y-m-d|after:ContractStartDate',
		);

		$msg = array(
			'OrderID.required'  				=> "The OrderID field is required.",
			'OrderID.numeric'  					=> "The OrderID must be a number.",
			'NumberPurchased.required'  		=> "The NumberPurchased field is required.",
			'NumberPurchased.numeric'  			=> "The NumberPurchased must be a number.",
			'ProductID.required'				=> "The ProductID field is required.",
			'ProductID.numeric'  				=> "The ProductID must be a number.",
			'InboundTariffCategoryID.required'	=> "The InboundTariffCategoryID field is required.",
			'InboundTariffCategoryID.numeric'  	=> "The InboundTariffCategoryID must be a number.",
			'NumberContractID.required'  		=> "The NumberContractID field is required.",
			'NumberContractID.numeric'  		=> "The NumberContractID must be a number.",
			'NewNumberContractID.required'  	=> "The NewNumberContractID field is required.",
			'NewNumberContractID.numeric'  		=> "The NewNumberContractID must be a number.",
			'ContractStartDate.required'		=> "The ContractStartDate field is required.",
			'ContractStartDate.after'			=> "Past dates not allowed for ContractStartDate.",
			'ContractEndDate.required'			=> "The ContractEndDate field is required.",
			'ContractEndDate.after'				=> "ContractEndDate must be a date after ContractStartDate.",
		);

		$validator = Validator::make($data, $rules, $msg);
		if ($validator->fails()) {
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
		}

		$Account = Account::find($AccountID);
		if($Account) {
			$CompanyID = $Account->CompanyId;
			$AccountID = $Account->AccountID;
		} else {
			// Account Not Found Error
			return Response::json(["ErrorMessage" => "Account Not Found."], Codes::$Code400[0]);
		}

		$AccountService = AccountService::where(['AccountID'=>$AccountID,'ServiceOrderID'=>$data['OrderID'],'Status'=>1,'CancelContractStatus'=>0]);

		// if AccountService exist
		if($AccountService->count() > 0) {
			$AccountService = $AccountService->first();
			$ServiceID = $AccountService->ServiceID;

			$checkCLIRateTable = CLIRateTable::where([
				'CompanyID' 		=> $CompanyID,
				'AccountID' 		=> $AccountID,
				'AccountServiceID' 	=> $AccountService->AccountServiceID,
				'ContractID' 		=> $data['NumberContractID'],
				'CLI' 				=> $data['NumberPurchased'],
				'Status' 			=> 1
			]);

			// if number exist
			if($checkCLIRateTable->count() > 0) {

				$ServiceTemplate_q = "SELECT it.RateTableId,st.OutboundRateTableId,st.City,st.Tariff,st.prefixName,st.country,st.countryCode,st.accessType
									  FROM tblDynamicFields df
									  INNER JOIN tblDynamicFieldsValue dfv ON dfv.DynamicFieldsID = df.DynamicFieldsID
									  INNER JOIN tblServiceTemplate st ON st.ServiceTemplateID = dfv.ParentID
									  LEFT JOIN tblServiceTemapleInboundTariff it ON it.ServiceTemplateID = st.ServiceTemplateID AND it.DIDCategoryId = " . $data['InboundTariffCategoryID'] . "
									  WHERE df.CompanyID = " . $CompanyID . " AND df.Type = 'serviceTemplate' AND df.FieldName = 'ProductID' AND dfv.FieldValue = '" . $data['ProductID'] . "'";

				$ServiceTemplate = DB::select($ServiceTemplate_q);
				if(empty($ServiceTemplate[0])) {
					return Response::json(["ErrorMessage" => "Product not found. ProductID: " . $data['ProductID']], Codes::$Code400[0]);
				}
				$ServiceTemplate = $ServiceTemplate[0];

				if(empty($ServiceTemplate->country)) {
					return Response::json(["ErrorMessage" => "Country Not Found against ProductID: " . $data['ProductID']], Codes::$Code400[0]);
				}
				if(empty($ServiceTemplate->accessType)) {
					return Response::json(["ErrorMessage" => "AccessType Not Found against ProductID: " . $data['ProductID']], Codes::$Code400[0]);
				}
				if(empty($ServiceTemplate->prefixName)) {
					return Response::json(["ErrorMessage" => "Prefix Not Found against ProductID: " . $data['ProductID']], Codes::$Code400[0]);
				}

				$TerminationRateTableID = $AccessRateTableID = 0;
				if (!empty($ServiceTemplate->OutboundRateTableId) && !empty($ServiceTemplate->RateTableId)) {
					$TerminationRateTableID = $ServiceTemplate->OutboundRateTableId;
					$AccessRateTableID = $ServiceTemplate->RateTableId;
				} else {
					if (empty($ServiceTemplate->OutboundRateTableId)) {
						return Response::json(["ErrorMessage" => "Termination RateTable Not Found. ProductID: " . $data['ProductID']], Codes::$Code400[0]);
					} else {
						return Response::json(["ErrorMessage" => "Access RateTable Not Found. ProductID: " . $data['ProductID'].", InboundTariffCategoryID: " . $data['InboundTariffCategoryID']], Codes::$Code400[0]);
					}
				}

				try {
					DB::beginTransaction();

					$checkCLIRateTable = $checkCLIRateTable->first();
					$AccountServicePackageID = $checkCLIRateTable->AccountServicePackageID;

					if(strtotime($checkCLIRateTable->NumberStartDate) > strtotime(date('Y-m-d'))) {
						// if old NumberStartDate is future date then end it same day
						$checkCLIRateTable->update(['NumberEndDate'=>$checkCLIRateTable->NumberStartDate,'Status'=>0]);
					}
					else if(strtotime($checkCLIRateTable->NumberEndDate) > strtotime($data['ContractStartDate'])) {
						// if old NumberStartDate is current or past date then check if it's old NumberEndDate is > new NumberStartDate
						// if yes then update old NumberEndDate = New NumberStartDate
						$update_data = [];
						if($data['ContractStartDate'] == date('Y-m-d')) {
							$update_data['Status'] = 0;
						}
						$update_data['NumberEndDate'] = $data['ContractStartDate'];
						$checkCLIRateTable->update($update_data);
					}

					$ProductCountry = Country::where(array('Country' => $ServiceTemplate->country));
					if($ProductCountry->count() == 0) {
						return Response::json(["ErrorMessage" => "Country Not Found against ProductID: " . $data['ProductID']], Codes::$Code400[0]);
					}
					$ProductCountry = $ProductCountry->first();

					$City 		= !empty($ServiceTemplate->City) ? $ServiceTemplate->City : '';
					$Tariff 	= !empty($ServiceTemplate->Tariff) ? $ServiceTemplate->Tariff : '';
					$accessType = !empty($ServiceTemplate->accessType) ? $ServiceTemplate->accessType : '';
					$prefixName = !empty($ServiceTemplate->prefixName) ? $ServiceTemplate->prefixName : '';
					$AreaPrefix = !empty($ServiceTemplate->countryCode) ? $ServiceTemplate->countryCode  : ''. ltrim($ServiceTemplate->prefixName, '0');

					$VendorID = RateTableDIDRate::Join('tblRate', 'tblRateTableDIDRate.RateID', '=', 'tblRate.RateID')
						->select(['tblRateTableDIDRate.VendorID'])
						->where([
							"tblRateTableDIDRate.RateTableId" 	=> $AccessRateTableID,
							"tblRateTableDIDRate.City" 			=> $City,
							"tblRateTableDIDRate.Tariff" 		=> $Tariff,
							"tblRateTableDIDRate.AccessType" 	=> $accessType,
							"tblRate.Code" 						=> $AreaPrefix
						])
						->where("tblRateTableDIDRate.EffectiveDate", '<=', date('Y-m-d'))
						->whereNotNull('tblRateTableDIDRate.MonthlyCost')
						->max('VendorID');
					$VendorID = !empty($VendorID) ? $VendorID : 0;

					$data_cli = [];
					$data_cli['CompanyID'] 				= $CompanyID;
					$data_cli['AccountID'] 				= $AccountID;
					$data_cli['ServiceID'] 				= $ServiceID;
					$data_cli['AccountServiceID'] 		= $AccountService->AccountServiceID;
					$data_cli['CLI'] 					= $data['NumberPurchased'];
					$data_cli['NumberStartDate'] 		= $data['ContractStartDate'];
					$data_cli['NumberEndDate'] 			= $data['ContractEndDate'];
					$data_cli['ContractID'] 			= $data['NewNumberContractID'];
					$data_cli['RateTableID'] 			= $AccessRateTableID; // Default Access Rate Table
					$data_cli['TerminationRateTableID'] = $TerminationRateTableID; // Default Termination Rate Table
					$data_cli['CountryID'] 				= $ProductCountry->CountryID;
					$data_cli['City'] 					= $City;
					$data_cli['Tariff'] 				= $Tariff;
					$data_cli['NoType'] 				= $accessType;
					$data_cli['PrefixWithoutCountry'] 	= $prefixName;
					$data_cli['Prefix'] 				= $AreaPrefix;
					$data_cli['VendorID'] 				= $VendorID;
					$data_cli['AccountServicePackageID']= $AccountServicePackageID;

					CLIRateTable::create($data_cli);
					DB::commit();
					return Response::json(["SuccessMessage" => "Number Tariff updated successfully."],Codes::$Code200[0]);
				} catch(Exception $e) {
					DB::rollback();
					Log::info($e->getTraceAsString());
					$response = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
					return Response::json($response, Codes::$Code500[0]);
				}
			} else {
				$number_error = 'Number '. $data['NumberPurchased'] . ' not found against '.$AccountFindType.': '.json_encode($data[$AccountFindType]).', OrderID: '. $data['OrderID'];
				return Response::json(["ErrorMessage" => $number_error],Codes::$Code400[0]);
			}
		} else {
			$error = 'Account Service not found for OrderID: '. $data['OrderID'];
			return Response::json(["ErrorMessage" => $error],Codes::$Code400[0]);
		}
	}

	// New API to update account service by vasim seta @2020-01-02
	public function updateAccountService() {
		if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
		$CompanyID=0;
		$AccountID=0;
		$AccountFindType = '';
		$today = date('Y-m-d');
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
		}
		
		
		if(!empty($data['AccountID'])) {
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
				$AccountFindType = 'AccountID';
			}else {
				return Response::json(["ErrorMessage"=>"AccountID must be a mumber."],Codes::$Code400[0]);
			}
			
		}else if(!empty($data['AccountNo'])){
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo can not be empty"],Codes::$Code400[0]);
			}
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
			$AccountFindType = 'AccountNo';
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			$AccountFindType = 'AccountDynamicField';
		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo or AccountDynamicField Required."],Codes::$Code400[0]);
		}
		

		$rules = array(
			'OrderID'							=> 'required|numeric',
			'NumberContractID'					=> 'required|numeric',
			'NumberPurchased'					=> 'required|numeric',
			'ContractEndDate'					=> 'required|date|date_format:Y-m-d|after:'.date('Y-m-d',strtotime("-1 days")),
		);

		$msg = array(
			'OrderID.required'  				=> "The OrderID field is required.",
			'OrderID.numeric'  					=> "The OrderID must be a number.",
			'NumberContractID.required'  		=> "The NumberContractID field is required.",
			'NumberContractID.numeric'  		=> "The NumberContractID must be a number.",
			'NumberPurchased.required'  		=> "The NumberPurchased field is required.",
			'NumberPurchased.numeric'  			=> "The NumberPurchased must be a number.",
			'ContractEndDate.required'			=> "The ContractEndDate field is required.",
			'ContractEndDate.after'				=> "Past dates not allowed for ContractEndDate.",
		);

		$validator = Validator::make($data, $rules, $msg);
		if ($validator->fails()) {
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
		}

		$Account = Account::find($AccountID);
		if($Account) {
			$CompanyID = $Account->CompanyId;
			$AccountID = $Account->AccountID;
		} else {
			// Account Not Found Error
			return Response::json(["ErrorMessage" => "Account Not Found."], Codes::$Code400[0]);
		}

		$AccountService = AccountService::where(['AccountID'=>$AccountID,'ServiceOrderID'=>$data['OrderID'],'Status'=>1,'CancelContractStatus'=>0]);
		// if AccountService exist
		if($AccountService->count() > 0) {
			$AccountService = $AccountService->first();

			$CLIRateTable = CLIRateTable::where([
				'CompanyID' 		=> $CompanyID,
				'AccountID' 		=> $AccountID,
				'AccountServiceID' 	=> $AccountService->AccountServiceID,
				'ContractID' 		=> $data['NumberContractID'],
				'CLI' 				=> $data['NumberPurchased'],
				'Status' 			=> 1
			]);

			// if number exist
			if($CLIRateTable->count() > 0) {
				try {
					DB::beginTransaction();

					$CLIRateTable = $CLIRateTable->first();

					if(strtotime($data['ContractEndDate']) < strtotime($CLIRateTable->NumberStartDate)) {
						// if given EndDate is < existing NumberStartDate then end it same day
						$data['ContractEndDate'] = $CLIRateTable->NumberStartDate;
					}
					$update_data = [];
					// if EndDate is today or if EndDate is future and ends on same day as StartDate
					if($data['ContractEndDate'] == $today || (strtotime($data['ContractEndDate']) > strtotime($today) && $data['ContractEndDate'] == $CLIRateTable->NumberStartDate)) {
						$update_data['Status'] = 0;
					}
					$update_data['NumberEndDate'] = $data['ContractEndDate'];
					$CLIRateTable->update($update_data);

					DB::commit();
					return Response::json(["SuccessMessage" => "Number updated successfully."],Codes::$Code200[0]);
				} catch(Exception $e) {
					DB::rollback();
					Log::info($e->getTraceAsString());
					$response = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
					return Response::json($response, Codes::$Code500[0]);
				}
			} else {
				$number_error = 'Number '. $data['NumberPurchased'] . ' not found against '.$AccountFindType.': '.json_encode($data[$AccountFindType]).', OrderID: '. $data['OrderID'];
				return Response::json(["ErrorMessage" => $number_error],Codes::$Code400[0]);
			}
		} else {
			$error = 'Account Service not found for OrderID: '. $data['OrderID'];
			return Response::json(["ErrorMessage" => $error],Codes::$Code400[0]);
		}
	}

	// New API to update account service package by vasim seta @2020-01-02
	public function updateServicePackage() {
		if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
		$CompanyID=0;
		$AccountID=0;
		$AccountFindType = '';
		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
		}

		
		if(!empty($data['AccountID'])) {
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
				$AccountFindType = 'AccountID';
			}else {
				return Response::json(["ErrorMessage"=>"AccountID must be a mumber."],Codes::$Code400[0]);
			}
		}else if(!empty($data['AccountNo'])){
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo can not be empty"],Codes::$Code400[0]);
			}
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
			$AccountFindType = 'AccountNo';
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			$AccountFindType = 'AccountDynamicField';
		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo or AccountDynamicField Required."],Codes::$Code400[0]);
		}

		$rules = array(
			'OrderID'							=> 'required|numeric',
			'NumberContractID'					=> 'required|numeric',
			'NumberPurchased'					=> 'required|numeric',
			'TestNumberContractID'				=> 'required|numeric',
			'TestNumberPurchased'				=> 'required|numeric',
			'UpdatePackageDate'					=> 'required|date|date_format:Y-m-d|after:'.date('Y-m-d',strtotime("-1 days")),
		);

		$msg = array(
			'OrderID.required'  				=> "The OrderID field is required.",
			'OrderID.numeric'  					=> "The OrderID must be a number.",
			'NumberContractID.required'  		=> "The NumberContractID field is required.",
			'NumberContractID.numeric'  		=> "The NumberContractID must be a number.",
			'NumberPurchased.required'  		=> "The NumberPurchased field is required.",
			'NumberPurchased.numeric'  			=> "The NumberPurchased must be a number.",
			'TestNumberContractID.required'  	=> "The TestNumberContractID field is required.",
			'TestNumberContractID.numeric'  	=> "The TestNumberContractID must be a number.",
			'TestNumberPurchased.required'  	=> "The TestNumberPurchased field is required.",
			'TestNumberPurchased.numeric'  		=> "The TestNumberPurchased must be a number.",
			'UpdatePackageDate.required'		=> "The UpdatePackageDate field is required.",
			'UpdatePackageDate.after'			=> "Past dates not allowed for UpdatePackageDate.",
		);

		$validator = Validator::make($data, $rules, $msg);
		if ($validator->fails()) {
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
		}
		$Account = Account::find($AccountID);
		if($Account) {
			$CompanyID = $Account->CompanyId;
			$AccountID = $Account->AccountID;
		} else {
			// Account Not Found Error
			return Response::json(["ErrorMessage" => "Account Not Found."], Codes::$Code400[0]);
		}

		$AccountService = AccountService::where(['AccountID'=>$AccountID,'ServiceOrderID'=>$data['OrderID'],'Status'=>1,'CancelContractStatus'=>0]);

		// if AccountService exist then check below conditions
		if($AccountService->count() > 0) {
			$AccountService = $AccountService->first();

			$CLIRateTable = CLIRateTable::where([
				'CompanyID' 		=> $CompanyID,
				'AccountID' 		=> $AccountID,
				'AccountServiceID' 	=> $AccountService->AccountServiceID,
				'ContractID' 		=> $data['NumberContractID'],
				'CLI' 				=> $data['NumberPurchased'],
				'Status' 			=> 1
			]);

			$TestCLIRateTable = CLIRateTable::where([
				'CompanyID' 		=> $CompanyID,
				'AccountID' 		=> $AccountID,
				'AccountServiceID' 	=> $AccountService->AccountServiceID,
				'ContractID' 		=> $data['TestNumberContractID'],
				'CLI' 				=> $data['TestNumberPurchased'],
				'Status' 			=> 1
			]);

			// if number and test number exist
			if($CLIRateTable->count() > 0 && $TestCLIRateTable->count() > 0) {

				try {
					DB::beginTransaction();

					$CLIRateTable 		= $CLIRateTable->first();
					$TestCLIRateTable 	= $TestCLIRateTable->first();

					$data_cli = [];
					$data_cli['CompanyID'] 				= $CLIRateTable->CompanyID;
					$data_cli['AccountID'] 				= $CLIRateTable->AccountID;
					$data_cli['ServiceID'] 				= $CLIRateTable->ServiceID;
					$data_cli['AccountServiceID'] 		= $CLIRateTable->AccountServiceID;
					$data_cli['CLI'] 					= $CLIRateTable->CLI;
					$data_cli['NumberStartDate'] 		= $CLIRateTable->NumberStartDate;
					$data_cli['NumberEndDate'] 			= $CLIRateTable->NumberEndDate;
					$data_cli['ContractID'] 			= $CLIRateTable->ContractID;
					$data_cli['RateTableID'] 			= $CLIRateTable->RateTableID;
					$data_cli['TerminationRateTableID'] = $CLIRateTable->TerminationRateTableID;
					$data_cli['CountryID'] 				= $CLIRateTable->CountryID;
					$data_cli['City'] 					= $CLIRateTable->City;
					$data_cli['Tariff'] 				= $CLIRateTable->Tariff;
					$data_cli['NoType'] 				= $CLIRateTable->NoType;
					$data_cli['PrefixWithoutCountry'] 	= $CLIRateTable->PrefixWithoutCountry;
					$data_cli['Prefix'] 				= $CLIRateTable->Prefix;
					$data_cli['VendorID'] 				= $CLIRateTable->VendorID;
					$data_cli['AccountServicePackageID']= $TestCLIRateTable->AccountServicePackageID;

					$update_data['Status'] 			= 0;
					$update_data['NumberEndDate'] 	= $data['UpdatePackageDate'];
					$CLIRateTable->update($update_data);
					$TestCLIRateTable->update($update_data);

					CLIRateTable::create($data_cli);

					DB::commit();
					return Response::json(["SuccessMessage" => "Package updated successfully."],Codes::$Code200[0]);
				} catch(Exception $e) {
					DB::rollback();
					Log::info($e->getTraceAsString());
					$response = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
					return Response::json($response, Codes::$Code500[0]);
				}
			} else {
				$number_error = '';
				if($CLIRateTable->count() == 0)
					$number_error = 'Number '. $data['NumberPurchased'] . ' not found against '.$AccountFindType.': '.json_encode($data[$AccountFindType]).', OrderID: '. $data['OrderID'];
				if($TestCLIRateTable->count() == 0)
					$number_error = 'Number '. $data['NumberPurchased'] . ' not found against '.$AccountFindType.': '.json_encode($data[$AccountFindType]).', OrderID: '. $data['OrderID'];
				return Response::json(["ErrorMessage" => $number_error],Codes::$Code400[0]);
			}
		} else {
			$error = 'Account Service not found for OrderID: '. $data['OrderID'];
			return Response::json(["ErrorMessage" => $error],Codes::$Code400[0]);
		}
	}

	// New API to transfer number from one account to another by vasim seta @2020-01-03
	public function transferServiceNumber() {
		if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
		$FromCompanyID=$ToCompanyID=0;
		$FromAccountID=$ToAccountID=0;
		$FromAccountFindType=$ToAccountFindType='';

		try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
		}

		if(!empty($data['FromAccountID'])) {
			if(is_numeric(trim($data['FromAccountID']))) {
				$FromAccountID = $data['FromAccountID'];
				$FromAccountFindType = 'FromAccountID';
			}else {
				return Response::json(["ErrorMessage"=>"FromAccountID must be a mumber."],Codes::$Code400[0]);
			}
			
		}else if(!empty($data['FromAccountNo'])){
			$accountNo = trim($data['FromAccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"FromAccountNo can not be empty"],Codes::$Code400[0]);
			}
			$FromAccountID = Account::where(["Number" => $data['FromAccountNo']])->pluck('AccountID');
			$FromAccountFindType = 'FromAccountNo';
		}else if(!empty($data['FromAccountDynamicField'])){
			$FromAccountID = Account::findAccountBySIAccountRef($data['FromAccountDynamicField']);
			$FromAccountFindType = 'FromAccountDynamicField';
		}else{
			return Response::json(["ErrorMessage"=>"FromAccountID or FromAccountNo or FromAccountDynamicField Required."],Codes::$Code400[0]);
		}
		$FromAccount = Account::find($FromAccountID);
		if($FromAccount) {
			$FromCompanyID = $FromAccount->CompanyId;
			$FromAccountID = $FromAccount->AccountID;
		} else {
			// Account Not Found Error
			return Response::json(["ErrorMessage" => "From Account Not Found."], Codes::$Code400[0]);
		}

		if(!empty($data['ToAccountID'])) {
			if(is_numeric(trim($data['ToAccountID']))) {
				$ToAccountID = $data['ToAccountID'];
				$ToAccountFindType = 'ToAccountID';
			}else {
				return Response::json(["ErrorMessage"=>"ToAccountID must be a mumber."],Codes::$Code400[0]);
			}
		}else if(!empty($data['ToAccountNo'])){
			$accountNo = trim($data['ToAccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"ToAccountNo can not be empty"],Codes::$Code400[0]);
			}
			$ToAccountID = Account::where(["Number" => $data['ToAccountNo']])->pluck('AccountID');
			$ToAccountFindType = 'ToAccountNo';
		}else if(!empty($data['ToAccountDynamicField'])){
			$ToAccountID = Account::findAccountBySIAccountRef($data['ToAccountDynamicField']);
			$ToAccountFindType = 'ToAccountDynamicField';
		}else{
			return Response::json(["ErrorMessage"=>"ToAccountID or ToAccountNo or ToAccountDynamicField Required."],Codes::$Code400[0]);
		}
		$ToAccount = Account::find($ToAccountID);
		if($ToAccount) {
			$ToCompanyID = $ToAccount->CompanyId;
			$ToAccountID = $ToAccount->AccountID;
		} else {
			// Account Not Found Error
			return Response::json(["ErrorMessage" => "To Account Not Found."], Codes::$Code400[0]);
		}

		$rules = array(
			'FromOrderID'						=> 'required|numeric',
			'ToOrderID'							=> 'required|numeric',
			'NumberContractID'					=> 'required|numeric',
			'NumberPurchased'					=> 'required|numeric',
			'ContractStartDate'					=> 'required|date|date_format:Y-m-d|after:'.date('Y-m-d',strtotime("-1 days")),
			'ContractEndDate'					=> 'required|date|date_format:Y-m-d|after:ContractStartDate',
		);

		$msg = array(
			'FromOrderID.required'  			=> "The FromOrderID field is required.",
			'FromOrderID.numeric'  				=> "The FromOrderID must be a number.",
			'ToOrderID.required'  				=> "The ToOrderID field is required.",
			'ToOrderID.numeric'  				=> "The ToOrderID must be a number.",
			'NumberContractID.required'  		=> "The NumberContractID field is required.",
			'NumberContractID.numeric'  		=> "The NumberContractID must be a number.",
			'NumberPurchased.required'  		=> "The NumberPurchased field is required.",
			'NumberPurchased.numeric'  			=> "The NumberPurchased must be a number.",
			'ContractStartDate.required'		=> "The ContractStartDate field is required.",
			'ContractStartDate.after'			=> "Past dates not allowed for ContractStartDate.",
			'ContractEndDate.required'			=> "The ContractEndDate field is required.",
			'ContractEndDate.after'				=> "ContractEndDate must be a date after ContractStartDate.",
		);

		$validator = Validator::make($data, $rules, $msg);
		if ($validator->fails()) {
			$errors = "";
			foreach ($validator->messages()->all() as $error) {
				$errors .= $error . "<br>";
			}
			return Response::json(["ErrorMessage" => $errors],Codes::$Code400[0]);
		}

		
		$OldAccountService = AccountService::where(['AccountID'=>$FromAccountID,'ServiceOrderID'=>$data['FromOrderID'],'Status'=>1,'CancelContractStatus'=>0]);

		// if AccountService exist
		if($OldAccountService->count() > 0) {
			$OldAccountService = $OldAccountService->first();

			$OldCLIRateTable = CLIRateTable::where([
				'CompanyID' 		=> $FromCompanyID,
				'AccountID' 		=> $FromAccountID,
				'AccountServiceID' 	=> $OldAccountService->AccountServiceID,
				'ContractID' 		=> $data['NumberContractID'],
				'CLI' 				=> $data['NumberPurchased'],
				'Status' 			=> 1
			]);

			// if number exist
			if($OldCLIRateTable->count() > 0) {
				$OldCLIRateTable = $OldCLIRateTable->first();

				try {
					DB::beginTransaction();

					$AccountServiceData = [];
					$AccountServiceData['CompanyID'] 			= $ToCompanyID;
					$AccountServiceData['AccountID'] 			= $ToAccountID;
					$AccountServiceData['ServiceID'] 			= $OldAccountService->ServiceID;
					$AccountServiceData['ServiceOrderID'] 		= $data['ToOrderID'];
					$AccountServiceData['ServiceTitle'] 		= !empty($data['ServiceTitle']) ? trim($data['ServiceTitle']) : '';
					$AccountServiceData['ServiceDescription'] 	= !empty($data['ServiceDescription']) ? trim($data['ServiceDescription']) : '';
					$AccountServiceData['ServiceTitleShow'] 	= isset($data['ServiceTitleShow']) && $data['ServiceTitleShow'] == 1 ? 1 : 0;
					$AccountServiceData['Status'] 				= 1;

					$NewAccountService = AccountService::where(['AccountID'=>$ToAccountID,'ServiceOrderID'=>$data['ToOrderID'],'Status'=>1,'CancelContractStatus'=>0]);
					if($NewAccountService->count() > 0) {
						$NewAccountService = $NewAccountService->first();
						$checkNewCLIRateTable = CLIRateTable::where([
							'CompanyID' 		=> $ToCompanyID,
							'AccountID' 		=> $ToAccountID,
							'AccountServiceID' 	=> $NewAccountService->AccountServiceID,
							'ContractID' 		=> $data['NumberContractID'],
							'CLI' 				=> $data['NumberPurchased'],
							'Status' 			=> 1
						])->where(function($q) use ($data) {
							$q->whereBetween('NumberStartDate', array($data['ContractStartDate'], $data['ContractEndDate']));
							$q->orWhereBetween('NumberEndDate', array($data['ContractStartDate'], $data['ContractEndDate']));
							$q->orWhereRaw("'".$data['ContractStartDate']."' between NumberStartDate and NumberEndDate");
						});
						// check if number already exist which is being transfer to ToAccount then fail the request.
						if($checkNewCLIRateTable->count() > 0) {
							$date_error = 'Number '. $data['NumberPurchased'] . ' already exist between contract start date '.$data['ContractStartDate'] . ' and contract end date ' .$data['ContractEndDate'] . ' against ToAccount:'.$data[$ToAccountFindType];
							return Response::json(["ErrorMessage" => $date_error],Codes::$Code400[0]);
						}

						$NewAccountService->update($AccountServiceData);
					} else { // create if not exist
						$NewAccountService = AccountService::create($AccountServiceData);
					}

					$OldAccountServicePackage = AccountServicePackage::find($OldCLIRateTable->AccountServicePackageID);
					$PackageName = Package::find($OldAccountServicePackage->PackageId)->Name;
					$ToPackage = Package::where(['CompanyID'=>$ToCompanyID,'Name'=>$PackageName]);

					if($ToPackage->count() == 0) {
						$pkg_error = 'Package '.$PackageName.' not found for ToAccount:'.$data[$ToAccountFindType];
						return Response::json(["ErrorMessage" => $pkg_error],Codes::$Code400[0]);
					}
					$data_pkg = [];
					$data_pkg['CompanyID'] 			= $ToCompanyID;
					$data_pkg['AccountID'] 			= $ToAccountID;
					$data_pkg['AccountServiceID'] 	= $NewAccountService->AccountServiceID;
					$data_pkg['ServiceID'] 			= $OldAccountServicePackage->ServiceID;
					$data_pkg['ContractID'] 		= $OldAccountServicePackage->ContractID;
					$data_pkg['PackageId'] 			= $ToPackage->first()->PackageId;
					$data_pkg['PackageStartDate'] 	= $OldAccountServicePackage->PackageStartDate;
					$data_pkg['PackageEndDate'] 	= $OldAccountServicePackage->PackageEndDate;
					$data_pkg['RateTableID'] 		= $OldAccountServicePackage->RateTableID;
					$data_pkg['Status'] 			= 1;

					$AccountServicePackage = AccountServicePackage::create($data_pkg);

					$data_cli = [];
					$data_cli['CompanyID'] 				= $ToCompanyID;
					$data_cli['AccountID'] 				= $ToAccountID;
					$data_cli['AccountServiceID'] 		= $NewAccountService->AccountServiceID;
					$data_cli['NumberStartDate'] 		= $data['ContractStartDate'];
					$data_cli['NumberEndDate'] 			= $data['ContractEndDate'];
					$data_cli['ServiceID'] 				= $OldCLIRateTable->ServiceID;
					$data_cli['CLI'] 					= $OldCLIRateTable->CLI;
					$data_cli['ContractID'] 			= $OldCLIRateTable->ContractID;
					$data_cli['RateTableID'] 			= $OldCLIRateTable->RateTableID;
					$data_cli['TerminationRateTableID'] = $OldCLIRateTable->TerminationRateTableID;
					$data_cli['CountryID'] 				= $OldCLIRateTable->CountryID;
					$data_cli['City'] 					= $OldCLIRateTable->City;
					$data_cli['Tariff'] 				= $OldCLIRateTable->Tariff;
					$data_cli['NoType'] 				= $OldCLIRateTable->NoType;
					$data_cli['PrefixWithoutCountry'] 	= $OldCLIRateTable->PrefixWithoutCountry;
					$data_cli['Prefix'] 				= $OldCLIRateTable->Prefix;
					$data_cli['VendorID'] 				= $OldCLIRateTable->VendorID;
					$data_cli['AccountServicePackageID']= $AccountServicePackage->AccountServicePackageID;

					if($data['ContractStartDate'] == date('Y-m-d')) {
						$update_data['Status'] = 0;
					}
					$update_data['NumberEndDate'] 	= $data['ContractStartDate'];
					$OldCLIRateTable->update($update_data);

					CLIRateTable::create($data_cli);

					DB::commit();
					return Response::json(["SuccessMessage" => "Service Number transferred successfully."],Codes::$Code200[0]);
				} catch(Exception $e) {
					DB::rollback();
					Log::info($e->getTraceAsString());
					$response = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
					return Response::json($response, Codes::$Code500[0]);
				}
			} else {
				$number_error = 'Number '. $data['NumberPurchased'] . ' not found against '.$FromAccountFindType.': '.$data[$FromAccountFindType].', FromOrderID: '. $data['FromOrderID'];
				return Response::json(["ErrorMessage" => $number_error],Codes::$Code400[0]);
			}
		} else {
			$error = 'Account Service not found for FromOrderID: '. $data['FromOrderID'];
			return Response::json(["ErrorMessage" => $error],Codes::$Code400[0]);
		}
	}

}