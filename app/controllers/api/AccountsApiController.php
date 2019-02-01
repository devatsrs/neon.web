<?php

use app\controllers\api\Codes;

class AccountsApiController extends ApiController {

	private static $PaymentMethod = ["AuthorizeNet","AuthorizeNetEcheck",
	"FideliPay","Paypal","PeleCard","SagePay","SagePayDirectDebit","Stripe","StripeACH","FastPay",
	"MerchantWarrior","Wire Transfer","Other"];
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


	public function createAccountService()
	{
		// <!--"PackageSubscriptionID":"13" -->
		Log::info('createAccountService:Add Product Service.');
		$message = '';
		$post_vars = '';
		$accountData = '';
		$DefaultSubscriptionID = '';
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
			Log::info('createAccountService:Data.' . json_encode($accountData));
			$data['AccountNo'] = isset($accountData['AccountNo']) ? $accountData['AccountNo'] : '';
			$data['AccountID'] = isset($accountData['AccountID']) ? $accountData['AccountID'] : '';
			$data['ServiceTemaplateDynamicField'] = isset($accountData['ServiceTemaplateDynamicField']) ? $accountData['ServiceTemaplateDynamicField'] : '';
			$data['NumberPurchased'] = isset($accountData['NumberPurchased']) ? $accountData['NumberPurchased'] : '';
			$data['AccountDynamicField'] = isset($accountData['AccountDynamicField']) ? $accountData['AccountDynamicField'] : '';
			$data['InboundTariffCategoryID'] = isset($accountData['InboundTariffCategoryID']) ? $accountData['InboundTariffCategoryID'] :'';
			//$data['ServiceStartDate'] = isset($accountData['ServiceStartDate'])? strtotime($accountData['ServiceStartDate']) : '';
			//$data['ServiceEndDate'] = isset($accountData['ServiceEndDate'])? strtotime($accountData['ServiceEndDate']) : '';
			$AccountServiceContract['ContractStartDate'] = isset($accountData['ContractStartDate']) ? $accountData['ContractStartDate'] :'' ;
			$AccountServiceContract['ContractEndDate'] = isset($accountData['ContractEndDate']) ? $accountData['ContractEndDate'] : '';
			$AccountServiceContract['Duration'] = isset($accountData['ContractDuration']) ? $accountData['ContractDuration'] : '';
			$AccountServiceContract['ContractReason'] = isset($accountData['ContractFeeValue']) ? $accountData['ContractFeeValue'] : '';
			$AccountServiceContract['AutoRenewal'] = isset($accountData['AutoRenewal']) ? $accountData['AutoRenewal'] : '';
			$AccountServiceContract['ContractTerm'] = isset($accountData['ContractType']) ? $accountData['ContractType'] : '';
			//$AccountSubscription["PackageSubscription"] = isset($accountData['PackageSubscriptionID']) ? $accountData['PackageSubscriptionID'] : '';
			$ServiceTitle = isset($accountData['ServiceTitle']) ? $accountData['ServiceTitle'] : '';
			$Packagedata['PackageID'] = isset($accountData['PackageID']) ? $accountData['PackageID'] :'';
			$Packagedata['InvoicePackageDescription'] = isset($accountData['InvoicePackageDescription']) ? $accountData['InvoicePackageDescription'] :'';
			if (!empty($Packagedata['PackageID'])) {
				$PackagedataRecord =  Package::find($Packagedata['PackageID']);
				if (!isset($PackagedataRecord)) {
					return Response::json(["ErrorMessage" => Codes::$Code1031[1]], Codes::$Code1031[0]);
				}
			}
			if (!empty($AccountServiceContract['ContractStartDate']) && empty($AccountServiceContract['ContractEndDate'])) {
				return Response::json(["ErrorMessage"=>Codes::$Code1001[1]],Codes::$Code1001[0]);
			}
			if (!empty($AccountServiceContract['ContractStartDate']) && !empty($AccountServiceContract['ContractEndDate'])) {
				$checkDate = strtotime($AccountServiceContract['ContractStartDate']);
				Log::info('createAccountService:Add Product Service123.' . $checkDate);
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
				if (!empty($AccountServiceContract['ContractType']) && ($AccountServiceContract['ContractType'] < 1 || $AccountServiceContract['ContractType'] > 4)) {
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
				'ServiceTemaplateDynamicField' =>  'required',
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

			$ServiceTemaplateReference = ServiceTemplate::findServiceTemplateByDynamicField($data['ServiceTemaplateDynamicField']);
			if (empty($ServiceTemaplateReference)) {
				return Response::json(array("ErrorMessage" => Codes::$Code1021[1]),Codes::$Code1021[0]);
			}
			$ServiceTemaplateReference = ServiceTemplate::find($ServiceTemaplateReference);




				//unset($AccountSubscription['PackageSubscription']);


			if (!empty($data['AccountNo'])) {
				$Account = Account::where(array('Number' => $data['AccountNo']))->first();
			}else {
				$Account = Account::find($data['AccountID']);
			}
			if (!$Account) {
				return Response::json(["ErrorMessage" => Codes::$Code1000[1]],Codes::$Code1000[0]);
			}




			$NumberPurchasedRef=json_decode(json_encode($data['NumberPurchased']),true);

			Log::info('CreateAccountService:$NumberPurchasedRef .' . count($NumberPurchasedRef));


			for ($i =0; $i <count($NumberPurchasedRef);$i++) {
				$NumberPurchased = $NumberPurchasedRef[$i];
				Log::info('CreateAccountService:$NumberPurchasedRef .' . $NumberPurchased["Number"]);
				if(CLIRateTable::where(array('CompanyID'=>$CompanyID, 'CLI'=>$NumberPurchased["Number"]))->count()){
					//$AccountID = CLIRateTable::where(array('CompanyID'=>$CompanyID,'CLI'=>$data['NumberPurchased']))->pluck('AccountID');
					//$message .= $data['NumberPurchased'].' already exist against '.Account::getCompanyNameByID($AccountID).'.<br>';
					//$message = 'Following CLI already exists.<br>'.$message;
					return Response::json(array("ErrorMessage" => Codes::$Code1008[1]),Codes::$Code1008[0]);
				}
			}




			Log::info('ServiceTemplateId' . $ServiceTemaplateReference->ServiceTemplateId);


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

			Log::info('$InboundRateTableReference' . $InboundRateTableReference . ' ' . $data['InboundTariffCategoryID']);

			$DynamicFieldIDs = '';
			$DynamicFieldsExists=  DynamicFields::where('Type', 'subscription')->get();
			foreach ($DynamicFieldsExists as $DynamicFieldsExist) {
				$DynamicFieldIDs = $DynamicFieldIDs .$DynamicFieldsExist["DynamicFieldsID"] . ",";
			}
			Log::info('update $DynamicFieldIDs.' . $DynamicFieldIDs);
			$DynamicFieldIDs = explode(',', $DynamicFieldIDs);

			if (!empty($ServiceTemaplateReference->ServiceId)) {

					$AccountService = AccountService::where(array('AccountID' => $Account->AccountID, 'CompanyID' => $CompanyID, 'ServiceID' => $ServiceTemaplateReference->ServiceId))->first();
					if (isset($AccountService) && $AccountService != '') {
						Log::info('AccountServiceID Update');
						AccountService::where(array('AccountID' => $Account->AccountID, 'CompanyID' => $CompanyID, 'ServiceID' => $ServiceTemaplateReference->ServiceId))
							->update(array(
								'ServiceID' => $ServiceTemaplateReference->ServiceId,
								'ServiceTitle'=> $ServiceTitle,
								'updated_at' => $date));
						$AccountService = AccountService::where(array('AccountID' => $Account->AccountID, 'CompanyID' => $CompanyID, 'ServiceID' => $ServiceTemaplateReference->ServiceId))->first();
					} else {
						Log::info('AccountServiceID Create');
						$servicedata['ServiceID'] = $ServiceTemaplateReference->ServiceId;
						$servicedata['AccountID'] = $Account->AccountID;
						$servicedata['CompanyID'] = $CompanyID;
						$servicedata["ServiceTitle"] = $ServiceTitle;
						$AccountService = AccountService::create($servicedata);
					}
					Log::info('AccountServiceID ' . $AccountService->AccountServiceID);

					$AccountServiceContractExisting = AccountServiceContract::where(array('AccountServiceID' => $AccountService->AccountServiceID))->first();
					if (isset($AccountServiceContractExisting) && $AccountServiceContractExisting != '') {


						$AccountServiceContract["AccountServiceID"] = $AccountService->AccountServiceID;
						$AccountServiceContract["Duration"] = empty($AccountServiceContract['ContractDuration']) ? $ServiceTemaplateReference->ContractDuration : $AccountServiceContract['ContractDuration'];
						$AccountServiceContract["ContractReason"] = empty($AccountServiceContract['ContractReason']) ? $ServiceTemaplateReference->CancellationFee : $AccountServiceContract['ContractReason'];
						$AccountServiceContract["AutoRenewal"] = empty($AccountServiceContract["AutoRenewal"]) ? $ServiceTemaplateReference->AutomaticRenewal : $AccountServiceContract["AutoRenewal"];
						$AccountServiceContract["ContractTerm"] = empty($AccountServiceContract["ContractTerm"]) ? $ServiceTemaplateReference->CancellationCharges : $AccountServiceContract["ContractTerm"];
						$AccountServiceContract["updated_at"] = $date;
						//Log::info('AccountServiceID update records ' . $AccountServiceContract["FixedFee"] . ' ' . $AccountServiceContract["FixedFee"]);
						AccountServiceContract::where(array('AccountServiceID' => $AccountService->AccountServiceID))
							->update($AccountServiceContract);
					} else {
						Log::info('AccountServiceID new' . $AccountService->AccountServiceID);
						$AccountServiceContract["AccountServiceID"] = $AccountService->AccountServiceID;
						$AccountServiceContract["Duration"] = empty($AccountServiceContract['ContractDuration']) ? $ServiceTemaplateReference->ContractDuration : $AccountServiceContract['ContractDuration'];
						$AccountServiceContract["ContractReason"] = empty($AccountServiceContract['ContractReason']) ? $ServiceTemaplateReference->CancellationFee : $AccountServiceContract['ContractReason'];
						$AccountServiceContract["AutoRenewal"] = empty($AccountServiceContract["AutoRenewal"]) ? $ServiceTemaplateReference->AutomaticRenewal : $AccountServiceContract["AutoRenewal"];
						$AccountServiceContract["ContractTerm"] = empty($AccountServiceContract["ContractTerm"]) ? $ServiceTemaplateReference->CancellationCharges : $AccountServiceContract["ContractTerm"];
						Log::info('AccountServiceContract Done' . print_r($AccountServiceContract, true));
						AccountServiceContract::create($AccountServiceContract);
						Log::info('AccountServiceContract Done');
					}

			}

			$OutboundDiscountPlan = $ServiceTemaplateReference->OutboundDiscountPlanId;
			$InboundDiscountPlan = $ServiceTemaplateReference->InboundDiscountPlanId;

				$AccountSubscriptionID = 0;
				$AccountName = '';
				$AccountCLI = '';
				$SubscriptionDiscountPlanID = 0;
			if (!empty($OutboundDiscountPlan)) {
				$AccountDiscountPlan['AccountID'] = $Account->AccountID;
				$AccountDiscountPlan['DiscountPlanID'] = $OutboundDiscountPlan;
				$AccountDiscountPlan['Type'] = AccountDiscountPlan::OUTBOUND;
				$AccountDiscountPlan['ServiceID'] = $ServiceTemaplateReference->ServiceId;
				$AccountDiscountPlan['AccountSubscriptionID'] = $AccountSubscriptionID;
				$AccountDiscountPlan['AccountName'] = $AccountName;
				$AccountDiscountPlan['AccountCLI'] = $AccountCLI;
				$AccountDiscountPlan['SubscriptionDiscountPlanID'] = $SubscriptionDiscountPlanID;
				$AccountDiscountPlanExists = AccountDiscountPlan::where(array('AccountID' => $Account->AccountID, 'Type' => AccountDiscountPlan::OUTBOUND))->count();
				if ($AccountDiscountPlanExists == 0) {
					AccountDiscountPlan::create($AccountDiscountPlan);
				} else {
					AccountDiscountPlan::where(array('AccountID' => $Account->AccountID, 'Type' => AccountDiscountPlan::OUTBOUND))
						->update($AccountDiscountPlan);
				}
			}

			if (!empty($InboundDiscountPlan)) {
				$AccountInboudDiscountPlan = AccountDiscountPlan::where(array('AccountID' => $Account->AccountID,'Type'=>AccountDiscountPlan::INBOUND))->count();
				$AccountDiscountPlan['AccountID'] = $Account->AccountID;
				$AccountDiscountPlan['ServiceID'] = $ServiceTemaplateReference->ServiceId;
				$AccountDiscountPlan['AccountSubscriptionID'] = $AccountSubscriptionID;
				$AccountDiscountPlan['AccountName'] = $AccountName;
				$AccountDiscountPlan['AccountCLI'] = $AccountCLI;
				$AccountDiscountPlan['SubscriptionDiscountPlanID'] = $SubscriptionDiscountPlanID;
				$AccountDiscountPlan['Type'] = AccountDiscountPlan::INBOUND;
				$AccountDiscountPlan['DiscountPlanID'] =$InboundDiscountPlan;
				$AccountDiscountPlanExists = AccountDiscountPlan::where(array('AccountID' => $Account->AccountID, 'Type' => AccountDiscountPlan::INBOUND))->count();
				if ($AccountDiscountPlanExists == 0) {
					AccountDiscountPlan::create($AccountDiscountPlan);
				}else {
					AccountDiscountPlan::where(array('AccountID' => $Account->AccountID,'Type'=>AccountDiscountPlan::INBOUND))
						->update($AccountDiscountPlan);
				}

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

			if(!empty($InboundRateTableReference)){
				$count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $Account->AccountID, 'ServiceID' => $inbounddata['ServiceID'], 'Type' => AccountTariff::INBOUND))->count();
				if(!empty($count) && $count>0){
					AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $Account->AccountID, 'ServiceID' => $inbounddata['ServiceID'], 'Type' => AccountTariff::INBOUND))
						->update(array('RateTableID' => $InboundRateTableReference, 'updated_at' => $date));
				}else{
					$inbounddata['created_at'] = $date;
					AccountTariff::create($inbounddata);
				}
			}

			if(!empty($ServiceTemaplateReference->OutboundRateTableId)){
				$count = AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $Account->AccountID, 'ServiceID' => $outbounddata['ServiceID'], 'Type' => AccountTariff::OUTBOUND))->count();
				if(!empty($count) && $count>0){
					AccountTariff::where(array('CompanyID' => $CompanyID, 'AccountID' => $Account->AccountID, 'ServiceID' => $outbounddata['ServiceID'], 'Type' => AccountTariff::OUTBOUND))
						->update(array('RateTableID' => $ServiceTemaplateReference->OutboundRateTableId, 'updated_at' => $date));
				}else{
					$outbounddata['created_at'] = $date;
					AccountTariff::create($outbounddata);
				}
			}



			$AccountAuthenticate = array();
			$AccountAuthenticate['CustomerAuthRule'] = 'CLI';
			$AccountAuthenticate['CustomerAuthValue'] = '';

			if(!empty($data['ServiceID'])){

				if(AccountAuthenticate::where(array('AccountID'=>$Account->AccountID,'ServiceID'=>$ServiceTemaplateReference->ServiceId))->count()){
					AccountAuthenticate::where(array('AccountID'=>$Account->AccountID,'ServiceID'=>$ServiceTemaplateReference->ServiceId))->update($AccountAuthenticate);
				}else{
					$AccountAuthenticate['AccountID'] = $Account->AccountID;
					$AccountAuthenticate['CompanyID'] = $CompanyID;
					$AccountAuthenticate['ServiceID'] = $ServiceTemaplateReference->ServiceId;
					AccountAuthenticate::insert($AccountAuthenticate);
				}

			}else{
				if(AccountAuthenticate::where(array('AccountID'=>$Account->AccountID,'ServiceID'=>0))->count()){
					AccountAuthenticate::where(array('AccountID'=>$Account->AccountID,'ServiceID'=>0))->update($AccountAuthenticate);
				}else{
					$AccountAuthenticate['AccountID'] = $Account->AccountID;
					$AccountAuthenticate['CompanyID'] = $CompanyID;
					AccountAuthenticate::insert($AccountAuthenticate);
				}
			}

			$cliRateTableID = 0;
			if (!empty($ServiceTemaplateReference->OutboundRateTableId)) {
				$cliRateTableID = $ServiceTemaplateReference->OutboundRateTableId;
			}


			/*if (!empty($ServiceTemaplateReference->city_tariff)) {
				$RateTableDIDRates = RateTableDIDRate::where(array('CityTariff' => $ServiceTemaplateReference->city_tariff))->get();
				foreach($RateTableDIDRates as $RateTableDIDRate) {
					$AccountSubscriptionDB = BillingSubscription::where(array('SubscriptionID' => $DefaultSubscriptionID))->first();
					Log::info('update $DynamicFieldIDs12.' . $AccountSubscriptionDB);
					if (!isset($AccountSubscriptionDB) || $AccountSubscriptionDB == '') {
						return Response::json(["ErrorMessage" => Codes::$Code1005[1]],Codes::$Code1005[0]);
					}

					$this->createAccountSubscriptionFromRateTable($Account,$AccountSubscriptionDB,
						$date,$ServiceTemaplateReference,$AccountService,
						$DefaultSubscriptionID,$DynamicFieldIDs,$RateTableDIDRate,'');
				}
			}*/

			$SubscriptionSequence = 0;
			$AccountSubscriptionLast = AccountSubscription::where(array('AccountID' => $Account->AccountID))
										->orderByRaw('SequenceNo desc')
										->first();
			if (isset($AccountSubscriptionLast)) {
				$SubscriptionSequence = $AccountSubscriptionLast["SequenceNo"];
			}
			$AccountSubscriptionTemplates = ServiceTemapleSubscription::where(array('ServiceTemplateID' => $ServiceTemaplateReference->ServiceTemplateId))->get();
			if (isset($AccountSubscriptionTemplates) && count($AccountSubscriptionTemplates) > 0) {
				foreach ($AccountSubscriptionTemplates as $AccountSubscriptionTemplate) {
					$AccountSubscriptionExisting = AccountSubscription::where(array('AccountID' => $Account->AccountID, 'SubscriptionID' => $AccountSubscriptionTemplate["SubscriptionId"]))->first();
					Log::info('Subsctiption IDs from template.' . $AccountSubscriptionTemplate["SubscriptionId"]);
					$AccountSubscriptionDB = BillingSubscription::where(array('SubscriptionID' => $AccountSubscriptionTemplate["SubscriptionId"]))->first();
					$this->createAccountSubscription($Account,$AccountSubscriptionDB,
						$date,$ServiceTemaplateReference,$AccountService,
						$AccountSubscriptionTemplate["SubscriptionId"],$DynamicFieldIDs,++$SubscriptionSequence);
				}
			}

			if (count($NumberPurchasedRef) > 0) {
				$AccountSubscriptionDB = BillingSubscription::where(array('SubscriptionID' => $DefaultSubscriptionID))->first();
				//Log::info('update $DynamicFieldIDs12.' . $AccountSubscriptionDB);
				if (!isset($AccountSubscriptionDB) || $AccountSubscriptionDB == '') {
					return Response::json(["ErrorMessage" => Codes::$Code1005[1]], Codes::$Code1005[0]);
				}

				for ($i = 0; $i < count($NumberPurchasedRef); $i++) {
					$NumberPurchased = $NumberPurchasedRef[$i];
					Log::info('CreateAccountService:$NumberPurchasedRef .' . $NumberPurchased["Number"]);
					$rate_tables['CLI'] = $NumberPurchased["Number"];
					$rate_tables['RateTableID'] = $cliRateTableID;
					$rate_tables['AccountID'] = $Account->AccountID;
					$rate_tables['CompanyID'] = $CompanyID;
					$rate_tables['CityTariff'] = $ServiceTemaplateReference->city_tariff;
					$rate_tables['AccountServiceID'] = $AccountService->AccountServiceID;
					if (!empty($ServiceTemaplateReference->ServiceId)) {
						$rate_tables['ServiceID'] = $ServiceTemaplateReference->ServiceId;
					}
					CLIRateTable::insert($rate_tables);


					if (!empty($DefaultSubscriptionID) && !empty($InboundRateTableReference)) {
						$RateTableDIDRates = RateTableDIDRate::
						Join('tblRate', 'tblRateTableDIDRate.RateID', '=', 'tblRate.RateID')
							->select(['tblRateTableDIDRate.OneOffCost', 'tblRateTableDIDRate.MonthlyCost',
							'tblRateTableDIDRate.OneOffCostCurrency','tblRateTableDIDRate.MonthlyCostCurrency']);
						$RateTableDIDRates = $RateTableDIDRates->whereRaw('\'' . $NumberPurchased["Number"] . '\'' . ' like  CONCAT(tblRate.Code,"%")');
						$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.CityTariff" => $ServiceTemaplateReference->city_tariff]);
						$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.RateTableId" => $InboundRateTableReference]);
						$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.ApprovedStatus" => 1]);
						Log::info('$RateTableDIDRates.' . $RateTableDIDRates->toSql());
						$RateTableDIDRates = $RateTableDIDRates->get();
						//$RateTableDIDRates = RateTableDIDRate::where(array('CityTariff' => $ServiceTemaplateReference->city_tariff))->get();
						foreach ($RateTableDIDRates as $RateTableDIDRate) {
							$this->createAccountSubscriptionFromRateTable($Account, $AccountSubscriptionDB,
								$date, $ServiceTemaplateReference, $AccountService,
								$DefaultSubscriptionID, $DynamicFieldIDs,
								$RateTableDIDRate, $NumberPurchased["InvoiceNoDescription"],++$SubscriptionSequence);
						}
					}
				}
			}

			if (!empty($DefaultSubscriptionID) && !empty($PackagedataRecord)) {

				if(!empty($PackagedataRecord["PackageId"]) && !empty($PackagedataRecord["RateTableId"])) {
					$AccountServicePackage = AccountServicePackage::where(['AccountID' => $Account->AccountID, 'AccountServiceID' => $AccountService->AccountServiceID]);
					if ($AccountServicePackage->count() > 0) {
						//Update
						$AccountServicePackage->update(['PackageId' => $PackagedataRecord["PackageId"], 'RateTableID' => $PackagedataRecord["RateTableId"]]);

					} else {
						//Create
						$packagedata = array();
						$packagedata['AccountID'] = $Account->AccountID;
						$packagedata['AccountServiceID'] = $AccountService->AccountServiceID;
						$packagedata['CompanyID'] = $CompanyID;
						$packagedata['PackageId'] = $PackagedataRecord["PackageId"];
						$packagedata['RateTableID'] = $PackagedataRecord["RateTableId"];
						$packagedata['created_at'] = date('Y-m-d H:i:s');

						AccountServicePackage::create($packagedata);

					}
				}

				$RateTableDIDRates = RateTableDIDRate::
				Join('tblRate', 'tblRateTableDIDRate.RateID', '=', 'tblRate.RateID')
					->select(['tblRateTableDIDRate.OneOffCost', 'tblRateTableDIDRate.MonthlyCost',
						'tblRateTableDIDRate.OneOffCostCurrency','tblRateTableDIDRate.MonthlyCostCurrency']);
				$RateTableDIDRates = $RateTableDIDRates->where(["tblRate.Code" => $PackagedataRecord["Name"]]);
				$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.RateTableId" => $PackagedataRecord["RateTableId"]]);
				$RateTableDIDRates = $RateTableDIDRates->where(["tblRateTableDIDRate.ApprovedStatus" => 1]);
				Log::info('Package $RateTableDIDRates.' . $RateTableDIDRates->toSql());
				$RateTableDIDRates = $RateTableDIDRates->get();
				//$RateTableDIDRates = RateTableDIDRate::where(array('CityTariff' => $ServiceTemaplateReference->city_tariff))->get();
				foreach ($RateTableDIDRates as $RateTableDIDRate) {
					$this->createAccountSubscriptionFromRateTable($Account, $AccountSubscriptionDB,
						$date, $ServiceTemaplateReference, $AccountService,
						$DefaultSubscriptionID, $DynamicFieldIDs,
						$RateTableDIDRate, $Packagedata["InvoicePackageDescription"],++$SubscriptionSequence);
				}
			}
			$message = "Account Service Successfully Added";






			return Response::json(array("data" => $message),Codes::$Code200[0]);


		} catch (Exception $ex) {
			Log::info('createAccountService:Exception.' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage" => Codes::$Code500[1]],Codes::$Code500[0]);
		}
	}


	public function createAccountSubscriptionFromRateTable($Account,$AccountSubscriptionDB,
											  $date,$ServiceTemaplateReference,$AccountService,$SubscriptionId,
											  $DynamicFieldIDs,$RateTableDIDRate,$InvoiceLineDescriptionAPI,$SubscriptionSequence) {


		//Log::info('$InvoiceLineDescriptionAPI .' . $InvoiceLineDescriptionAPI);
		$monthly = $RateTableDIDRate["MonthlyCost"];
		$InvoiceLineDescription = empty($InvoiceLineDescriptionAPI) ? $AccountSubscriptionDB["InvoiceLineDescription"] : $InvoiceLineDescriptionAPI;
		//Log::info('$InvoiceLineDescriptionAPI .' . $InvoiceLineDescription);
		$AccountSubscription["AccountID"] = $Account->AccountID;
		$AccountSubscription["SubscriptionID"] = $AccountSubscriptionDB["SubscriptionID"];
		$AccountSubscription["InvoiceDescription"] = $InvoiceLineDescription;
		$AccountSubscription["Qty"] = 1;
	//	$AccountSubscription["StartDate"] = $date;
	//	$AccountSubscription["EndDate"] = $date;
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
		//$AccountSubscription["StartDate"] = $date;
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


	public function createAccount() {
		Log::info('createAccount:Create new Account.');
		$post_vars = '';
		$accountData = [];
		try {

			try {
				$post_vars = json_decode(file_get_contents("php://input"));
				//$post_vars = Input::all();
				$accountData=json_decode(json_encode($post_vars),true);
				$countValues = count($accountData);
				if ($countValues == 0) {
					Log::info('Exception in Routing API.Invalid JSON');
					return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
				}
			}catch(Exception $ex) {
				Log::info('Exception in Routing API.Invalid JSON' . $ex->getTraceAsString());
				return Response::json(["ErrorMessage"=>Codes::$Code400[1]],Codes::$Code400[0]);
			}


			//$post_vars = Input::all();

			//$accountData = Input::all();
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
			//$data['Owner'] = $post_vars->Owner;

			$data['Number'] = isset($accountData['AccountNo']) ? $accountData['AccountNo'] : '';
			$data['FirstName'] = isset($accountData['FirstName']) ? $accountData['FirstName'] : '';
			$data['LastName'] = isset($accountData['LastName']) ? $accountData['LastName'] : '';
			$data['Phone'] = isset($accountData['Phone']) ? $accountData['Phone'] : '';
			$data['Address1'] = isset($accountData['Address1']) ? $accountData['Address1'] : '';
			$data['Address2'] = isset($accountData['Address2']) ? $accountData['Address2'] : '';
			$data['City'] = isset($accountData['City']) ? $accountData['City'] : '';
			$data['Email'] = isset($accountData['Email']) ? $accountData['Email'] : '';
			$data['BillingEmail'] = isset($accountData['BillingEmail']) ? $accountData['BillingEmail'] : '';
			$data['Owner'] = isset($accountData['OwnerID']) ? $accountData['OwnerID'] : '';
			$data['CurrencyId'] = isset($accountData['CurrencyID']) ? $accountData['CurrencyID'] : '';
			$data['Country'] = isset($accountData['CountryID']) ? $accountData['CountryID'] : '';
			$data['password'] = isset($accountData['CustomerPanelPassword']) ? Crypt::encrypt($accountData['CustomerPanelPassword']) :'';
			$data['VatNumber'] = isset($accountData['VatNumber']) ? $accountData['VatNumber'] : '';
			$data['Language']= isset($accountData['LanguageID']) ? $accountData['LanguageID'] : '';

			$data['CompanyID'] = $CompanyID;
			$data['AccountType'] = 1;
			$data['IsVendor'] = isset($accountData['IsVendor']);
			if (!empty($accountData['IsVendor']) && ($accountData['IsVendor'] != 0 && $accountData['IsVendor'] != 1)) {
				return Response::json(["status" => Codes::$Code1025[0],"ErrorMessage"=>Codes::$Code1025[1]]);
			}else {
				$data['IsVendor'] = 0;
			}
			$data['IsCustomer'] = isset($accountData['IsCustomer']);
			if (!empty($accountData['IsCustomer']) && ($accountData['IsCustomer'] != 0 && $accountData['IsCustomer'] != 1)) {
				return Response::json(["status" => Codes::$Code1024[0],"ErrorMessage"=>Codes::$Code1024[1]]);
			}else {
				$data['IsReseller'] = 0;
			}
			$data['IsReseller'] = $accountData['IsReseller'];
			if (!empty($accountData['IsReseller']) && ($accountData['IsReseller'] != 0 && $accountData['IsReseller'] != 1)) {
				return Response::json(["status" => Codes::$Code1023[0],"ErrorMessage"=>Codes::$Code1023[1]]);
			}else {
				$data['IsReseller'] = 0;
			}
			//Log::info('createAccount:Create new Account Reseller0.' . $accountData['IsReseller'] . ' ' . $data['IsReseller']);

			$data['Billing'] = isset($data['Billing']) && $data['Billing'] == 1 ? 1 : 0;
			$data['created_by'] = $CreatedBy;
			$data['AccountType'] = 1;
			$data['AccountName'] = isset($accountData['AccountName']) ? trim($accountData['AccountName']) : '';
			$data['PaymentMethod'] = isset($accountData['PaymentMethodID']) ? $accountData['PaymentMethodID'] : '' ;



			$AccountPaymentAutomation['AutoTopup']= isset($accountData['AutoTopup']) ? $accountData['AutoTopup'] :'';
			$AccountPaymentAutomation['MinThreshold']= isset($accountData['MinThreshold']) ? $accountData['MinThreshold'] : '';
			$AccountPaymentAutomation['TopupAmount']= isset($accountData['TopupAmount']) ? $accountData['TopupAmount'] : '';
			$AccountPaymentAutomation['AutoOutpayment']= isset($accountData['AutoOutpayment']) ? $accountData['AutoOutpayment'] : '';
			$AccountPaymentAutomation['OutPaymentThreshold']= isset($accountData['OutPaymentThreshold']) ? $accountData['OutPaymentThreshold'] : '';
			$AccountPaymentAutomation['OutPaymentAmount']= isset($accountData['OutPaymentAmount']) ? $accountData['OutPaymentAmount'] : '';

			if (!empty($data['PaymentMethod']) && !in_array($data['PaymentMethod'], AccountsApiController::$PaymentMethod)) {
				return Response::json(array("status" => Codes::$Code1020[0], "ErrorMessage" => Codes::$Code1020[1]));
			}

			if (!empty($AccountPaymentAutomation['AutoTopup']) && $AccountPaymentAutomation['AutoTopup'] == 1) {
				$rules = [];
				$rules['MinThreshold'] = 'required';
				$rules['TopupAmount'] = 'required';
				$messages = array(
					'MinThreshold.required' =>'MinThreshold field is required if AutoTopup is ON',
					'TopupAmount.required' =>'TopupAmount field is required if AutoTopup is ON',

				);
				$validator = Validator::make($AccountPaymentAutomation, $rules, $messages);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["status" => Codes::$Code402[0], "ErrorMessage" => $errors]);
				}
			}

			if (!empty($AccountPaymentAutomation['AutoOutpayment']) && $AccountPaymentAutomation['AutoOutpayment'] == 1) {
				$rules = [];
				$rules['OutPaymentThreshold'] = 'required';
				$rules['OutPaymentAmount'] = 'required';
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
					return Response::json(["status" => Codes::$Code402[0], "ErrorMessage" => $errors]);
				}
			}

			// If Reseller on backend customer is on

			if($data['IsReseller']==1){
				$data['IsCustomer']=1;
				$data['IsVendor']=0;
			}

			unset($data['ResellerOwner']);

			//when account varification is off in company setting then varified the account by default.
			$AccountVerification =  CompanySetting::getKeyVal('AccountVerification');

			if ( $AccountVerification != CompanySetting::ACCOUT_VARIFICATION_ON ) {
				$data['VerificationStatus'] = Account::VERIFIED;
			}



			if (strpbrk($data['AccountName'], '\/?*:|"<>')) {
				return Response::json(array("status" => Codes::$Code1018[0], "ErrorMessage" => Codes::$Code1018[1]));
			}
			$data['Status'] = 1;

			if (empty($data['Number'])) {
				$data['Number'] = Account::getLastAccountNo();
			}
			$data['Number'] = trim($data['Number']);




			Account::$rules['AccountName'] = 'required';
			Account::$rules['Number'] = 'required';






			$validator = Validator::make($data, Account::$rules, Account::$messages);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
			}

			$AccountName = Account::where(['AccountName'=>$data["AccountName"],'CompanyID'=>$CompanyID,'AccountType'=>1])->count();
			if ($AccountName > 0) {
				return Response::json(["ErrorMessage"=>Codes::$Code1029[1]],Codes::$Code410[0]);
			}

			$AccountNumber = Account::where(['Number'=>$data["Number"],'CompanyID'=>$CompanyID])->count();
			if ($AccountNumber > 0) {
				return Response::json(["ErrorMessage"=>Codes::$Code1030[1]],Codes::$Code410[0]);
			}


			if (isset($accountData['AccountDynamicField'])) {
				//$AccountReferenceArr = json_decode(json_encode(json_decode($accountData['AccountDynamicField'])), true);
				$AccountReferenceArr = json_decode(json_encode($accountData['AccountDynamicField']),true);
				for ($i =0; $i <count($AccountReferenceArr);$i++) {
					$AccountReference = $AccountReferenceArr[$i];
					$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),'Type'=>'account','Status'=>1,'FieldSlug'=>$AccountReference['Name']])->pluck('DynamicFieldsID');
					if(empty($DynamicFieldsID)) {
						return Response::json(["ErrorMessage" => Codes::$Code1006[1]],Codes::$Code1006[0]);
					}
				}
			}

			Log::info('createAccount:Create new Account Reseller.' . $data['IsReseller']);
			if($data['IsReseller']==1){

				$ResellerCount = Reseller::where('ChildCompanyID',$CompanyID)->count();
				if($ResellerCount>0){
					return Response::json(["ErrorMessage" => Codes::$Code1010[1]],Codes::$Code1010[0]);
				}

				Log::info("Read the reseller fields1");
				Reseller::$rules['Email'] = 'required|email';
				Reseller::$rules['Password'] ='required|min:3';

				Log::info("Read the reseller fields2");
				$ResellerData['CompanyID'] = $CompanyID;
				$CurrentTime = date('Y-m-d H:i:s');

				if(empty($CreatedBy)){
					$CreatedBy = 'system';
				}
				$ResellerData['AccountID'] = $data['Number'];
				Reseller::$rules['AccountID'] = 'required|unique:tblReseller,AccountID';
				Reseller::$rules['Email'] = 'required|email';
				Reseller::$rules['Password'] ='required|min:3';
				$ResellerData['Email'] = isset($accountData['ReSellerEmail']) ? $accountData['ReSellerEmail'] : '';
				$ResellerData['Password'] = isset($accountData['ReSellerPassword']) ? $accountData['ReSellerPassword'] : '';
				$ResellerData['AllowWhiteLabel'] = isset($accountData['ReSellerAllowWhiteLabel']) ? 1 : 0;
				$ResellerData['DomainUrl'] = isset($accountData['ReSellerDomainUrl']) ? $accountData['ReSellerDomainUrl'] : '' ;
				Reseller::$messages['Email.required'] = 'The Reseller Email is Required.';
				Reseller::$messages['Password.required'] = 'The Reseller Password is Required.';
				if($data['IsReseller']==1) {
					$validator = Validator::make($ResellerData, Reseller::$rules, Reseller::$messages);
					if ($validator->fails()) {
						$errors = "";
						foreach ($validator->messages()->all() as $error) {
							$errors .= $error . "<br>";
						}
						return Response::json(["ErrorMessage" => $errors],Codes::$Code402[0]);
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
			$data['CurrencyId'] = Currency::where('CurrencyId',$data['CurrencyId'])->pluck('CurrencyId');
			if (!isset($data['CurrencyId'])) {
				return Response::json(["ErrorMessage" => Codes::$Code1012[1],Codes::$Code1012[0]]);
			}
			$data['Country'] = Country::where(['CountryID' => $data['Country']])->pluck('Country');
			if (!isset($data['Country'])) {
				return Response::json(["ErrorMessage" => Codes::$Code1013[1]],Codes::$Code1013[0]);
			}

			$data['LanguageID'] = Language::where('LanguageID',$data['Language'])->pluck('LanguageID');
			if (!isset($data['LanguageID'])) {
				return Response::json(["ErrorMessage" => Codes::$Code1014[1]],Codes::$Code1014[0]);
			}

			$data['Owner'] = User::where('UserID',$data['Owner'])->pluck('UserID');
			if (!isset($data['Owner'])) {
				return Response::json(["ErrorMessage" => Codes::$Code1019[1]],Codes::$Code1019[0]);
			}

			AccountBilling::$rulesAPI['billing_type'] = 'required';
			AccountBilling::$rulesAPI['billing_class'] = 'required';
			AccountBilling::$rulesAPI['billing_cycle'] = 'required';
			//AccountBilling::$rulesAPI['billing_cycle_options'] = 'required';


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
				$validator = Validator::make($BillingSetting, AccountBilling::$rulesAPI);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["ErrorMessage" => $errors,Codes::$Code402[0]]);
				}

				if (!empty($BillingSetting['billing_type']) && ($BillingSetting['billing_type'] != 1 && $BillingSetting['billing_type'] != 2)) {
					return Response::json(["ErrorMessage" => Codes::$Code1016[1]],Codes::$Code1016[0]);
				}

				if (!empty($BillingSetting['billing_cycle'])
					&& ($BillingSetting['billing_cycle'] < 1 || $BillingSetting['billing_cycle'] > 8)) {
					return Response::json(["ErrorMessage" => Codes::$Code1026[1]],Codes::$Code1026[0]);
				}

				$BillingCycleTypeID[0] = "Daily";
				$BillingCycleTypeID[1] = "Fortnightly";
				$BillingCycleTypeID[2] = "In Specific days";
				$BillingCycleTypeID[3] = "Manual";
				$BillingCycleTypeID[4] = "Monthly";
				$BillingCycleTypeID[5] = "Monthly anniversary";
				$BillingCycleTypeID[6] = "Quarterly";
				$BillingCycleTypeID[7] = "Weekly";
				$BillingCycleTypeID[8] = "Yearly";

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
						$validValues = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
						$BillingCycleOptions = explode(',', $BillingSetting['billing_cycle_options']);
						foreach($BillingCycleOptions as $BillingCycleOption) {
							if (!in_array($BillingCycleOption, $validValues)) {
								return Response::json(["ErrorMessage" => Codes::$Code1028[1]],Codes::$Code1028[0]);
							}
						}
					}
				}


			}

			if ($account = Account::create($data)) {
				if (trim(Input::get('Number')) == '') {
					CompanySetting::setKeyVal('LastAccountNo', $account->Number);
				}
				$AccountDetails=array();
				$AccountDetails['AccountID'] = $account->AccountID;
				AccountDetails::create($AccountDetails);
				$account->update($data);

				if (isset($accountData['AccountDynamicField'])) {
					//$AccountReferenceArr = json_decode(json_encode(json_decode($accountData['AccountDynamicField'])), true);
					$AccountReferenceArr = json_decode(json_encode($accountData['AccountDynamicField']),true);
					for ($i =0; $i <count($AccountReferenceArr);$i++) {
						$AccountReference = $AccountReferenceArr[$i];
						$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),'Type'=>'account','Status'=>1,'FieldSlug'=>$AccountReference['Name']])->pluck('DynamicFieldsID');
							$DynamicFields['ParentID'] = $account->AccountID;
							$DynamicFields['DynamicFieldsID'] = $DynamicFieldsID;
							$DynamicFields['CompanyID'] = $CompanyID;
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
					$dataAccountBilling['BillingType'] = $BillingSetting['billing_type'];
					$BillingClassSql = BillingClass::where('BillingClassID', $BillingSetting['billing_class']);
					$BillingClass = $BillingClassSql->first();
					if (!isset($BillingClass)) {
						return Response::json(["ErrorMessage" => Codes::$Code1017[1]],Codes::$Code1017[0]);
					}

					$dataAccountBilling['BillingClassID'] = $BillingClass->BillingClassID;
					$dataAccountBilling['BillingTimezone'] = $BillingClass->BillingTimezone;
					$dataAccountBilling['SendInvoiceSetting'] = empty($BillingClass->SendInvoiceSetting) ? 'after_admin_review' : $BillingClass->SendInvoiceSetting;
					$dataAccountBilling['AutoPaymentSetting'] = empty($BillingClass->AutoPaymentSetting) ? 'never' : $BillingClass->AutoPaymentSetting;
					$dataAccountBilling['AutoPayMethod'] = empty($BillingClass->AutoPayMethod) ? 0 : $BillingClass->AutoPayMethod;
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


					///**
					//*  if not first invoice generation
					Log::info('Billing Date ' . $BillingCycleType . ' ' . $BillingCycleValue . ' ' . $BillingStartDate);
					$NextBillingDate = next_billing_date($BillingCycleType, $BillingCycleValue, strtotime($BillingStartDate));
					$NextChargedDate = date('Y-m-d', strtotime('-1 day', strtotime($NextBillingDate)));

					$dataAccountBilling['BillingStartDate'] = $BillingStartDate;
					$dataAccountBilling['LastInvoiceDate'] = $BillingStartDate;
					$dataAccountBilling['LastChargeDate'] = $BillingStartDate;
					if (isset($BillingSetting['NextInvoiceDate']) && $BillingSetting['NextInvoiceDate'] != '') {
						$NextBillingDate = $BillingSetting['NextInvoiceDate'];
					}

					$dataAccountBilling['NextInvoiceDate'] = $NextBillingDate;
					$dataAccountBilling['NextChargeDate'] = $NextChargedDate;

					//if not first invoice generation

					//$dataAccountBilling['BillingStartDate'] = $BillingStartDate;
					//$dataAccountBilling['LastInvoiceDate']  = $BillingStartDate;
					//$dataAccountBilling['LastChargeDate']   = $BillingStartDate;
					//$dataAccountBilling['NextInvoiceDate']  = $BillingStartDate;
					//$dataAccountBilling['NextChargeDate']   = $BillingStartDate;
					//
					Log::info(print_r($dataAccountBilling, true));

					AccountBilling::insertUpdateBilling($account->AccountID, $dataAccountBilling, 0);
					AccountBilling::storeFirstTimeInvoicePeriod($account->AccountID, 0);

				}
				if($data['IsReseller']==1) {

					$items = '';//;empty($data['reseller-item']) ? '' : array_filter($data['reseller-item']);
					$subscriptions = '';//empty($data['reseller-subscription']) ? '' : array_filter($data['reseller-subscription']);
					//$trunks = empty($data['reseller-trunk']) ? '' : array_filter($data['reseller-trunk']);
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
						//$data['Status'] = isset($data['Status']) ? 1 : 0;


						//$accountData['ReSellerEmail'] $accountData['ReSellerPassword']
						//$data['Password'] = Hash::make($data['Password']);
						$ResellerData['Password'] = Crypt::encrypt($accountData['ReSellerPassword']);

						$Account = $account;
						$ResellerData['AllowWhiteLabel'] = isset($accountData['ReSellerAllowWhiteLabel']) ? 1 : 0;
						$ResellerData['DomainUrl'] = $accountData['ReSellerDomainUrl'];
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
							$CompanyData['TimeZone'] = 'Etc/GMT';
							$CompanyData['created_at'] = $CurrentTime;
							$CompanyData['created_by'] = $CreatedBy;

							DB::beginTransaction();

							if ($ChildCompany = Company::create($CompanyData)) {
								$ChildCompanyID = $ChildCompany->CompanyID;

								log::info('Child Company ID ' . $ChildCompanyID);

								$JobStatusMessage = DB::select("CALL  prc_insertResellerData ($CompanyID,$ChildCompanyID,'" . $AccountName . "','" . $FirstName . "','" . $LastName . "',$AccountID,'" . $Email . "','" . $Password . "',$is_product,'" . $productids . "',$is_subscription,'" . $subscriptionids . "',$is_trunk,'" . $trunkids . "',$AllowWhiteLabel)");
								Log::info("CALL  prc_insertResellerData ($CompanyID,$ChildCompanyID,'" . $AccountName . "','" . $FirstName . "','" . $LastName . "',$AccountID,'" . $Email . "','" . $Password . "',$is_product,'" . $productids . "',$is_subscription,'" . $subscriptionids . "',$is_trunk,'" . $trunkids . "')");
								Log::info($JobStatusMessage);

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
									DB::commit();
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
				$AccountSuccessMessage['redirect'] = URL::to('/accounts/' . $account->AccountID . '/edit');

				CompanySetting::setKeyVal('LastAccountNo', $account->Number);
				return Response::json(array('data' => $AccountSuccessMessage),Codes::$Code200[0]);
			} else {
				return Response::json(array("ErrorMessage" => Codes::$Code500[1]),Codes::$Code500[0]);
			}

		} catch (Exception $ex) {
			Log::error("CreateAccountAPI Exception" . $ex->getTraceAsString());
			return Response::json(["ErrorMessage" => Codes::$Code500[1]],Codes::$Code500[0]);
			//return  Response::json(array("status" => "failed", "message" => $ex->getMessage(),'LastID'=>'','newcreated'=>''));
		}

		//return Redirect::route('accounts.index')->with('success_message', 'Accounts Successfully Created');
	}
	public function getPaymentMethodList()
	{
		Log::info('getPaymentMethodList for Account.');

		return Response::json(array("status" => "success", "PaymentMethod" => AccountsApiController::$PaymentMethod));
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

	
}