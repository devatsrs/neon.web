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
				return Response::json(["status"=>"404", "data"=>"Account Not Found."]);
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
			return Response::json(["status"=>"200", "data"=>$Result]);
		}
		return Response::json(["status"=>"404", "data"=>"Account Not Found"]);
	}


	public function createAccountService()
	{
		Log::info('createAccountService:Add Product Service.');
		$message = '';
		$post_vars = json_decode(file_get_contents("php://input"));
		//$post_vars = Input::all();
		$accountData=json_decode(json_encode($post_vars),true);
		$CompanyID = User::get_companyID();
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
		try {
			Log::info('createAccountService:Data.' . json_encode($accountData));
			$data['AccountNo'] = isset($accountData['AccountNo']) ? $accountData['AccountNo'] : '';
			$data['AccountID'] = isset($accountData['AccountID']) ? $accountData['AccountID'] : '';
			$data['ServiceTemaplate'] = isset($accountData['ServiceTemaplate']) ? $accountData['ServiceTemaplate'] : '';
			$data['NumberPurchased'] = isset($accountData['NumberPurchased']) ? $accountData['NumberPurchased'] : '';
			$data['AccountDynamicField'] = isset($accountData['AccountDynamicField']) ? $accountData['AccountDynamicField'] : '';
			$data['InboundTariffCategory'] = isset($accountData['InboundTariffCategoryID']) ? $accountData['InboundTariffCategoryID'] :'';
			//$data['ServiceStartDate'] = isset($accountData['ServiceStartDate'])? strtotime($accountData['ServiceStartDate']) : '';
			//$data['ServiceEndDate'] = isset($accountData['ServiceEndDate'])? strtotime($accountData['ServiceEndDate']) : '';
			$AccountServiceContract['ContractStartDate'] = isset($accountData['ServiceStartDate']) ? $accountData['ServiceStartDate'] :'' ;
			$AccountServiceContract['ContractEndDate'] = isset($accountData['ServiceEndDate']) ? $accountData['ServiceEndDate'] : '';
			$AccountServiceContract['Duration'] = isset($accountData['ContractDuration']) ? $accountData['ContractDuration'] : '';
			$AccountServiceContract['ContractReason'] = isset($accountData['ContractFeeValue']) ? $accountData['ContractFeeValue'] : '';
			$AccountServiceContract['AutoRenewal'] = isset($accountData['AutoRenewal']) ? $accountData['AutoRenewal'] : '';
			$AccountServiceContract['ContractTerm'] = isset($accountData['ContractType']) ? $accountData['ContractType'] : '';
			$AccountSubscription["PackageSubscription"] = isset($accountData['PackageSubscriptionID']) ? $accountData['PackageSubscriptionID'] : '';

			if (!empty($AccountServiceContract['ContractStartDate']) && empty($AccountServiceContract['ContractEndDate'])) {
				return Response::json(["status" => Codes::$Code1001[0], "ErrorMessage"=>Codes::$Code1001[1]]);
			}
			if (!empty($AccountServiceContract['ContractStartDate']) && !empty($AccountServiceContract['ContractEndDate'])) {
				if ($AccountServiceContract['ContractStartDate'] > $AccountServiceContract['ContractEndDate']) {
					return Response::json(["status" => Codes::$Code1002[0], "ErrorMessage"=>Codes::$Code1002[1]]);
				}
				if (!empty($AccountServiceContract['ContractType']) && ($AccountServiceContract['ContractType'] < 1 || $AccountServiceContract['ContractType'] > 4)) {
					return Response::json(["status" => Codes::$Code1003[0], "ErrorMessage"=>Codes::$Code1003[1]]);
				}
				if (!empty($AccountServiceContract['AutoRenewal']) && ($AccountServiceContract['AutoRenewal'] != 0 && $AccountServiceContract['AutoRenewal'] != 1)) {
					return Response::json(["status" => Codes::$Code1004[0],"ErrorMessage"=>Codes::$Code1004[1]]);
				}
			}


			$rules = array(
				'AccountNo' =>      'required_without_all:AccountDynamicField,AccountID',
				'AccountID' =>      'required_without_all:AccountDynamicField,AccountNo',
				'AccountDynamicField' =>      'required_without_all:AccountNo,AccountID',
				'ServiceTemaplate' =>  'required',
				'NumberPurchased'=>'required',

			);


			$validator = Validator::make($data, $rules);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["status" => Codes::$Code402[0], "ErrorMessage" => $errors]);
			}

			if (!empty($accountData['AccountDynamicField'])) {
				$AccountIDRef = '';
				$AccountIDRef = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
				if (empty($AccountIDRef)) {
					return Response::json(["status" => Codes::$Code1000[0], "ErrorMessage" => Codes::$Code1000[1]]);
				}
				$data['AccountID'] = $AccountIDRef;
			}

			if (!empty($AccountSubscription['PackageSubscription'])) {
				$AccountSubscriptionDB = BillingSubscription::where(array('SubscriptionID' => $AccountSubscription['PackageSubscription']))->first();
				if (!isset($AccountSubscriptionDB) || $AccountSubscriptionDB == '') {
					return Response::json(["status" => Codes::$Code1005[0], "ErrorMessage" => Codes::$Code1005[1]]);
				}

				$DynamicFieldIDs = '';
				$DynamicFieldsExists=  DynamicFields::where('Type', 'subscription')->get();
				foreach ($DynamicFieldsExists as $DynamicFieldsExist) {
					$DynamicFieldIDs = $DynamicFieldIDs .$DynamicFieldsExist["DynamicFieldsID"] . ",";
				}
				Log::info('update $DynamicFieldIDs.' . $DynamicFieldIDs);
				$DynamicFieldIDs = explode(',', $DynamicFieldIDs);
				$DynamicSubscrioptionFields=  DynamicFieldsValue::where('ParentID', $AccountSubscriptionDB["SubscriptionID"])
					->whereIn('DynamicFieldsID',$DynamicFieldIDs);
				Log::info('update $DynamicFieldIDs.' . $DynamicSubscrioptionFields->toSql());
				$DynamicSubscrioptionFields = $DynamicSubscrioptionFields->get();
				Log::info('update $DynamicFieldIDs.' . count($DynamicSubscrioptionFields));
				unset($AccountSubscription['PackageSubscription']);
			}

			if (!empty($data['AccountNo'])) {
				$Account = Account::where(array('Number' => $data['AccountNo']))->first();
			}else {
				$Account = Account::find($data['AccountID']);
			}
			if (!$Account) {
				return Response::json(["status" => Codes::$Code1000[0], "ErrorMessage" => Codes::$Code1000[1]]);
			}
			$ServiceTemaplateData = $data['ServiceTemaplate'];

			$DynamicField = DynamicFields::where(["FieldName"=>$ServiceTemaplateData["Name"],"Type"=>ServiceTemplateTypes::DYNAMIC_TYPE])->pluck('DynamicFieldsID');
			if (empty($DynamicField)) {
				return Response::json(["status" => Codes::$Code1006[0], "ErrorMessage" => Codes::$Code1006[1]]);
			}

			$ServiceTemaplateReference = DynamicFieldsValue::where(["DynamicFieldsID"=>$DynamicField,"FieldValue"=>$ServiceTemaplateData["Value"]])->count();
			if ($ServiceTemaplateReference > 1) {
				return Response::json(["status" => Codes::$Code1007[0], "ErrorMessage" => Codes::$Code1007[1]]);
			}
			if(CLIRateTable::where(array('CompanyID'=>$CompanyID, 'CLI'=>$data['NumberPurchased']))->count()){
				$AccountID = CLIRateTable::where(array('CompanyID'=>$CompanyID,'CLI'=>$data['NumberPurchased']))->pluck('AccountID');
				$message .= $data['NumberPurchased'].' already exist against '.Account::getCompanyNameByID($AccountID).'.<br>';
				$message = 'Following CLI already exists.<br>'.$message;
				return Response::json(array("status" => Codes::$Code1008[0], "ErrorMessage" => Codes::$Code1008[1]));
			}

			$ServiceTemaplateReference = DynamicFieldsValue::where(["DynamicFieldsID"=>$DynamicField,"FieldValue"=>$ServiceTemaplateData["Value"]])->pluck('ParentID');
			$ServiceTemaplateReference = ServiceTemplate::find($ServiceTemaplateReference);
			Log::info('ServiceTemplateId' . $ServiceTemaplateReference->ServiceTemplateId);


			if (!empty($data['InboundTariffCategory'])) {
				$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['InboundTariffCategory']])->count();
				if ($InboundRateTableReference > 1) {
					return Response::json(["status" => Codes::$Code1009[0], "ErrorMessage" => Codes::$Code1009[1]]);
				}
				$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['InboundTariffCategory']])->pluck('RateTableId');
			}else {
				$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->count();
				if ($InboundRateTableReference > 1) {
					return Response::json(["status" => Codes::$Code1009[0], "ErrorMessage" => Codes::$Code1009[1]]);
				}
				$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->pluck('RateTableId');
			}



			if (!empty($ServiceTemaplateReference->ServiceId)) {

				$AccountService = AccountService::where(array('AccountID' => $Account->AccountID, 'CompanyID' => $CompanyID, 'ServiceID' => $ServiceTemaplateReference->ServiceId))->first();
				if (isset($AccountService) && $AccountService != '') {
					Log::info('AccountServiceID Update');
					AccountService::where(array('AccountID' => $Account->AccountID, 'CompanyID' => $CompanyID, 'ServiceID' => $ServiceTemaplateReference->ServiceId))
						->update(array('ServiceID' => $ServiceTemaplateReference->ServiceId, 'updated_at' => $date));
					$AccountService = AccountService::where(array('AccountID' => $Account->AccountID, 'CompanyID' => $CompanyID, 'ServiceID' => $ServiceTemaplateReference->ServiceId))->first();
				} else {
					Log::info('AccountServiceID Create');
					$servicedata['ServiceID'] = $ServiceTemaplateReference->ServiceId;
					$servicedata['AccountID'] = $Account->AccountID;
					$servicedata['CompanyID'] = $CompanyID;
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
					AccountServiceContract::create($AccountServiceContract);
				}

				if (isset($AccountSubscriptionDB) && $AccountSubscriptionDB != '') {
					$AccountSubscriptionExisting = AccountSubscription::where(array('AccountID' => $Account->AccountID, 'SubscriptionID' => $AccountSubscriptionDB["SubscriptionID"]))->first();

					$AccountSubscription["AccountID"] = $Account->AccountID;
					$AccountSubscription["SubscriptionID"] = $AccountSubscriptionDB["SubscriptionID"];
					$AccountSubscription["InvoiceDescription"] = $AccountSubscriptionDB["InvoiceLineDescription"];
					$AccountSubscription["Qty"] = 1;
					$AccountSubscription["StartDate"] = $date;
					$AccountSubscription["EndDate"] = $date;
					//$AccountSubscription["ExemptTax"] =  $AccountSubscriptionDB[];
					$AccountSubscription["ActivationFee"] = $AccountSubscriptionDB["ActivationFee"];
					$AccountSubscription["AnnuallyFee"] = $AccountSubscriptionDB["AnnuallyFee"];
					$AccountSubscription["QuarterlyFee"] = $AccountSubscriptionDB["QuarterlyFee"];
					$AccountSubscription["MonthlyFee"] = $AccountSubscriptionDB["MonthlyFee"];
					$AccountSubscription["WeeklyFee"] = $AccountSubscriptionDB["WeeklyFee"];
					$AccountSubscription["DailyFee"] = $AccountSubscriptionDB["DailyFee"];
					//$AccountSubscription["SequenceNo"] =  $AccountSubscriptionDB[];
					$AccountSubscription["ServiceID"] = $ServiceTemaplateReference->ServiceId;
					$AccountSubscription["Status"] = 1;
					$AccountSubscription["AccountServiceID"] = $AccountService->AccountServiceID;

					//$AccountSubscription["DiscountAmount"] =  $AccountSubscriptionDB[];
					//$AccountSubscription["DiscountType"] =  $AccountSubscriptionDB[];

					if (isset($AccountSubscriptionExisting) && $AccountSubscriptionExisting != '') {
						Log::info('AccountServiceID new 123' . $Account->AccountID . ' ' . $AccountSubscriptionDB["SubscriptionID"]);

						$AccountSubscriptionQueryDB = AccountSubscription::where(array('AccountID' => $Account->AccountID, 'SubscriptionID' => $AccountSubscriptionDB["SubscriptionID"]))
							->update($AccountSubscription);
						$AccountSubscriptionQueryDB = AccountSubscription::where(array('AccountID' => $Account->AccountID,
							'SubscriptionID' => $AccountSubscriptionDB["SubscriptionID"]))->first();
						Log::info('AccountServiceID new 123 ' . $AccountSubscriptionQueryDB["AccountSubscriptionID"]);
					} else {
						$AccountSubscriptionQueryDB = AccountSubscription::create($AccountSubscription);
					}

					if (count($DynamicSubscrioptionFields) > 0) {
						AccountSubsDynamicFields::where(array('AccountSubscriptionID'=>$AccountSubscriptionQueryDB["AccountSubscriptionID"]))->delete();
					}
					$AccountSubsDynamicFields = [];
					foreach($DynamicSubscrioptionFields as $DynamicSubscrioptionField) {
						$AccountSubsDynamicFields["AccountSubscriptionID"] = $AccountSubscriptionQueryDB["AccountSubscriptionID"];
						$AccountSubsDynamicFields["AccountID"] = $Account->AccountID;
						$AccountSubsDynamicFields["DynamicFieldsID"] = $DynamicSubscrioptionField["DynamicFieldsID"];
						$AccountSubsDynamicFields["FieldValue"] = $DynamicSubscrioptionField["FieldValue"];
						$AccountSubsDynamicFields["FieldOrder"] = $DynamicSubscrioptionField["FieldOrder"];
						AccountSubsDynamicFields::insert($AccountSubsDynamicFields);
					}

				}
			}

			$inbounddata = array();
			if (!empty($InboundRateTableReference)) {
				$inbounddata['CompanyID'] = $CompanyID;
				$inbounddata['AccountID'] = $Account->AccountID;
				$inbounddata['ServiceID'] = $ServiceTemaplateReference->ServiceId;
				$inbounddata['RateTableID'] = $InboundRateTableReference;
				$inbounddata['Type'] = AccountTariff::INBOUND;
			}

			$outbounddata = array();
			if (!empty($ServiceTemaplateReference->OutboundRateTableId)) {
				$outbounddata['CompanyID'] = $CompanyID;
				$outbounddata['AccountID'] = $Account->AccountID;
				$outbounddata['ServiceID'] = $ServiceTemaplateReference->ServiceId;
				$outbounddata['RateTableID'] = $ServiceTemaplateReference->OutboundRateTableId;
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


			$rate_tables['CLI'] = $data['NumberPurchased'];
			$rate_tables['RateTableID'] = $cliRateTableID;
			$rate_tables['AccountID'] = $Account->AccountID;
			$rate_tables['CompanyID'] = $CompanyID;
			$rate_tables['AccountServiceID'] = $AccountService->AccountServiceID;
			if (!empty($ServiceTemaplateReference->ServiceId)) {
				$rate_tables['ServiceID'] = $ServiceTemaplateReference->ServiceId;
			}
			CLIRateTable::insert($rate_tables);
			$message = "Account Service Successfully Added";






			return Response::json(array("status" => Codes::$Code200[0], "data" => $message));


		} catch (Exception $ex) {
			Log::info('createAccountService:Exception.' . $ex->getTraceAsString());
			return Response::json(["status" => Codes::$Code500[0], "ErrorMessage" => Codes::$Code500[1]]);
		}
	}



	public function createAccount() {
		Log::info('createAccount:Create new Account.');
		try {

			$post_vars = json_decode(file_get_contents("php://input"));
			//$post_vars = Input::all();
			$accountData=json_decode(json_encode($post_vars),true);
			//$accountData = Input::all();
			$ServiceID = 0;
			$CompanyID = User::get_companyID();
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
			$data['IsVendor'] = isset($accountData['IsVendor']) && $accountData['IsVendor'] == 1 ? 1 : 0;
			$data['IsCustomer'] = isset($accountData['IsCustomer']) && $accountData['IsCustomer'] == 1  ? 1 : 0;
			$data['IsReseller'] = isset($accountData['IsReseller']) && $accountData['IsReseller'] == 1 ? 1 : 0;
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
			$data['Status'] = isset($data['Status']) ? 1 : 0;

			if (empty($data['Number'])) {
				$data['Number'] = Account::getLastAccountNo();
			}
			$data['Number'] = trim($data['Number']);




			Account::$rules['AccountName'] = 'required|unique:tblAccount,AccountName,NULL,CompanyID,AccountType,1';
			Account::$rules['Number'] = 'required|unique:tblAccount,Number,NULL,CompanyID';



			$validator = Validator::make($data, Account::$rules, Account::$messages);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["status" => Codes::$Code402[0], "ErrorMessage" => $errors]);
			}

			if (isset($accountData['AccountDynamicField'])) {
				//$AccountReferenceArr = json_decode(json_encode(json_decode($accountData['AccountDynamicField'])), true);
				$AccountReferenceArr = json_decode(json_encode($accountData['AccountDynamicField']),true);
				for ($i =0; $i <count($AccountReferenceArr);$i++) {
					$AccountReference = $AccountReferenceArr[$i];
					$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),'Type'=>'account','Status'=>1,'FieldSlug'=>$AccountReference['Name']])->pluck('DynamicFieldsID');
					if(empty($DynamicFieldsID)) {
						return Response::json(array("status" => Codes::$Code1006[0], "ErrorMessage" => Codes::$Code1006[1]));
					}
				}
			}

			if($data['IsReseller']==1){

				$ResellerCount = Reseller::where('ChildCompanyID',$CompanyID)->count();
				if($ResellerCount>0){
					return Response::json(["status" => Codes::$Code1010[0], "ErrorMessage" => Codes::$Code1010[1]]);
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
						return Response::json(["status" => Codes::$Code402[0], "ErrorMessage" => $errors]);
					}
				}

				if(!empty($ResellerData['AllowWhiteLabel'])){
					if(empty($ResellerData['DomainUrl'])){
						$ResellerData['DomainUrl'] = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'WEB_URL'])->pluck('Value');
					}
					if(!Reseller::IsAllowDomainUrl($ResellerData['DomainUrl'],'')){
						return  Response::json(array("status" => Codes::$Code1011[0], "ErrorMessage" => Codes::$Code1011[1]));
					}
				}

			}
			$data['CurrencyId'] = Currency::where('CurrencyId',$data['CurrencyId'])->pluck('CurrencyId');
			if (!isset($data['CurrencyId'])) {
				return Response::json(["status"=>Codes::$Code1012[0], "ErrorMessage" => Codes::$Code1012[1]]);
			}
			$data['Country'] = Country::where(['CountryID' => $data['Country']])->pluck('Country');
			if (!isset($data['Country'])) {
				return Response::json(["status"=>Codes::$Code1013[0], "ErrorMessage" => Codes::$Code1013[1]]);
			}

			$data['LanguageID'] = Language::where('LanguageID',$data['Language'])->pluck('LanguageID');
			if (!isset($data['LanguageID'])) {
				return Response::json(["status"=>Codes::$Code1014[0], "ErrorMessage" => Codes::$Code1014[1]]);
			}

			$data['Owner'] = User::where('UserID',$data['Owner'])->pluck('UserID');
			if (!isset($data['Owner'])) {
				return Response::json(["status"=>Codes::$Code1019[0], "ErrorMessage" => Codes::$Code1019[1]]);
			}

			AccountBilling::$rulesAPI['billing_type'] = 'required';
			AccountBilling::$rulesAPI['billing_class'] = 'required';
			AccountBilling::$rulesAPI['billing_cycle'] = 'required';
			//AccountBilling::$rulesAPI['billing_cycle_options'] = 'required';


			$BillingSetting['billing_type'] = isset($accountData['BillingType']) ? $accountData['BillingType'] : '';
			$BillingSetting['billing_class']= isset($accountData['BillingClassID']) ? $accountData['BillingClassID'] : '';
			$BillingSetting['billing_cycle']= isset($accountData['BillingCycleType']) ? $accountData['BillingCycleType'] : '';
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
					return Response::json(["status" => Codes::$Code1015[0], "ErrorMessage" => Codes::$Code1015[1]]);
				}
				$validator = Validator::make($BillingSetting, AccountBilling::$rulesAPI);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["status" => Codes::$Code402[0], "ErrorMessage" => $errors]);
				}

				if ($BillingSetting['billing_type'] == "Prepaid") {
					$BillingSetting['billing_type'] = "1";
				} else if ($BillingSetting['billing_type'] == "Postpaid") {
					$BillingSetting['billing_type'] = "2";
				} else {
					return Response::json(["status" => Codes::$Code1016[0], "ErrorMessage" => Codes::$Code1016[1]]);
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
						return Response::json(["status" => Codes::$Code1017[0],"ErrorMessage" => Codes::$Code1017[1]]);
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
								return Response::json(array("status" => Codes::$Code500[0],"ErrorMessage" => Codes::$Code500[1]));
							}
						} catch (Exception $e) {
							try {
								DB::rollback();
							} catch (\Exception $err) {
								Log::error($err);
							}
							Log::error($e);
							return Response::json(array("status" => Codes::$Code500[0],"ErrorMessage" => Codes::$Code500[1]));
						}
					}
				}

				$AccountSuccessMessage['AccountID'] = $account->AccountID;
				$AccountSuccessMessage['redirect'] = URL::to('/accounts/' . $account->AccountID . '/edit');

				CompanySetting::setKeyVal('LastAccountNo', $account->Number);
				return Response::json(array("status" => Codes::$Code200[0], 'data' => $AccountSuccessMessage));
			} else {
				return Response::json(array("status" => Codes::$Code500[0],"ErrorMessage" => Codes::$Code500[1]));
			}

		} catch (Exception $ex) {
			Log::error("CreateAccountAPI Exception" . $ex->getTraceAsString());
			return Response::json(["status" => Codes::$Code500[0],"ErrorMessage" => Codes::$Code500[1]]);
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