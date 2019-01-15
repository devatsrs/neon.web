<?php

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
		$data=Input::all();
		$Result=array();

		$Account = array();
		if(!empty($data['AccountID'])) {
			$Account = Account::where(["AccountID" => $data['AccountID']])->first();
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
		}else if(!empty($data['DynamicFields'])){
			$AccountID=Account::findAccountBySIAccountRef($data['DynamicFields']);
			if(empty($AccountID)){
				return Response::json(["status"=>"failed", "data"=>"Account Not Found."]);
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
			return Response::json(["status"=>"success", "data"=>$Result]);
		}
		return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
	}


	public function createAccountService()
	{
		Log::info('createAccountService:Add Product Service.');
		$message = '';
		$accountData = Input::all();
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
			$data['Number'] = $accountData['Number'];
			$data['ServiceTemaplate'] = $accountData['ServiceTemaplate'];
			$data['NumberPurchased'] = $accountData['NumberPurchased'];
			$data['DynamicFields'] = $accountData['DynamicFields'];
			$data['InboundTariffCategory'] = isset($accountData['InboundTariffCategoryId']) ? $accountData['InboundTariffCategoryId'] :'';
			//$data['ServiceStartDate'] = isset($accountData['ServiceStartDate'])? strtotime($accountData['ServiceStartDate']) : '';
			//$data['ServiceEndDate'] = isset($accountData['ServiceEndDate'])? strtotime($accountData['ServiceEndDate']) : '';
			$AccountServiceContract['ContractStartDate'] = $accountData['ServiceStartDate'];
			$AccountServiceContract['ContractEndDate'] = $accountData['ServiceEndDate'];
			$AccountServiceContract['Duration'] = $accountData['Duration'];
			$AccountServiceContract['ContractReason'] = $accountData['ContractFeeValue'];
			$AccountServiceContract['AutoRenewal'] = $accountData['AutoRenewal'];
			$AccountServiceContract['ContractTerm'] = $accountData['ContractType'];
			$AccountSubscription["PaymentSubscription"] = $accountData['PaymentSubscription'];

			if (!empty($AccountServiceContract['ContractStartDate']) && empty($AccountServiceContract['ContractEndDate'])) {
				return Response::json(["status" => "failed", "message" => "Please specified the Service End Data"]);
			}
			if (!empty($AccountServiceContract['ContractStartDate']) && !empty($AccountServiceContract['ContractEndDate'])) {
				if ($AccountServiceContract['ContractStartDate'] > $AccountServiceContract['ContractEndDate']) {
					return Response::json(["status" => "failed", "message" => "End Date should be greater then start date"]);
				}
				if (!empty($AccountServiceContract['ContractType']) && ($AccountServiceContract['ContractType'] < 1 || $AccountServiceContract['ContractType'] > 4)) {
					return Response::json(["status" => "failed", "message" => "The value of ContractType must be between 1 and 4"]);
				}
				if (!empty($AccountServiceContract['AutoRenewal']) && ($AccountServiceContract['AutoRenewal'] != 0 && $AccountServiceContract['AutoRenewal'] != 1)) {
					return Response::json(["status" => "failed", "message" => "The value of AutoRenewal must be between 0 or 1"]);
				}
			}

			Account::$rules['AccountName'] = 'required|unique:tblAccount,AccountName,NULL,CompanyID,AccountType,1';
			Account::$rules['Number'] = 'required|unique:tblAccount,Number,NULL,CompanyID';
			$rules = array(
				'Number' =>      'required_without_all:DynamicFields',
				'DynamicFields' =>      'required_without_all:Number',
				'ServiceTemaplate' =>  'required',
				'NumberPurchased'=>'required',

			);


			$validator = Validator::make($data, $rules);

			if ($validator->fails()) {
				$errors = "";
				foreach ($validator->messages()->all() as $error) {
					$errors .= $error . "<br>";
				}
				return Response::json(["status" => "failed", "message" => $errors]);
			}

			if (!empty($accountData['DynamicFields'])) {
				$data['Number'] = Account::findAccountBySIAccountRef($accountData['DynamicFields']);
				if (empty($data['Number'])) {
					return Response::json(["status" => "failed", "message" => "No Account Found for the Reference"]);
				}
			}

			if (!empty($AccountSubscription['PaymentSubscription'])) {
				$AccountSubscriptionDB = BillingSubscription::where(array('Name' => $AccountSubscription['PaymentSubscription']))->first();
				if (!isset($AccountSubscriptionDB) || $AccountSubscriptionDB == '') {
					return Response::json(["status" => "failed", "message" => "Please provide the correct account subscription"]);
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
				unset($AccountSubscription['PaymentSubscription']);
			}

			$Account = Account::find($data['Number']);
			if (!$Account) {
				return Response::json(["status" => "failed", "message" => "Please enter the valid account number"]);
			}
			$ServiceTemaplateData = json_decode($data['ServiceTemaplate'],true);

			$DynamicField = DynamicFields::where(["FieldName"=>$ServiceTemaplateData["Name"],"Type"=>ServiceTemplateTypes::DYNAMIC_TYPE])->pluck('DynamicFieldsID');
			if (empty($DynamicField)) {
				return Response::json(["status" => "failed", "message" => "Please provide the valid dynamic field"]);
			}

			$ServiceTemaplateReference = DynamicFieldsValue::where(["DynamicFieldsID"=>$DynamicField,"FieldValue"=>$ServiceTemaplateData["Value"]])->count();
			if ($ServiceTemaplateReference > 1) {
				return Response::json(["status" => "failed", "message" => "More then one service template, please provide the unique product reference"]);
			}
			if(CLIRateTable::where(array('CompanyID'=>$CompanyID, 'CLI'=>$data['NumberPurchased']))->count()){
				$AccountID = CLIRateTable::where(array('CompanyID'=>$CompanyID,'CLI'=>$data['NumberPurchased']))->pluck('AccountID');
				$message .= $data['NumberPurchased'].' already exist against '.Account::getCompanyNameByID($AccountID).'.<br>';
				$message = 'Following CLI already exists.<br>'.$message;
				return Response::json(array("status" => "error", "message" => $message));
			}

			$ServiceTemaplateReference = DynamicFieldsValue::where(["DynamicFieldsID"=>$DynamicField,"FieldValue"=>$ServiceTemaplateData["Value"]])->pluck('ParentID');
			$ServiceTemaplateReference = ServiceTemplate::find($ServiceTemaplateReference);
			Log::info('ServiceTemplateId' . $ServiceTemaplateReference->ServiceTemplateId);


			if (!empty($data['InboundTariffCategory'])) {
				$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['InboundTariffCategory']])->count();
				if ($InboundRateTableReference > 1) {
					return Response::json(["status" => "failed", "message" => "More then one Inbound Tariff found against the Category"]);
				}
				$InboundRateTableReference = ServiceTemapleInboundTariff::where(["ServiceTemplateID"=>$ServiceTemaplateReference->ServiceTemplateId,"DIDCategoryId"=>$data['InboundTariffCategory']])->pluck('RateTableId');
			}else {
				$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->count();
				if ($InboundRateTableReference > 1) {
					return Response::json(["status" => "failed", "message" => "More then one Inbound Tariff found against the Category"]);
				}
				$InboundRateTableReference = ServiceTemapleInboundTariff::where("ServiceTemplateID",'=',$ServiceTemaplateReference->ServiceTemplateId)->WhereNull('DIDCategoryId')->pluck('RateTableId');
			}



			if (!empty($ServiceTemaplateReference->ServiceId)) {
				$AccountService = AccountService::where(array('AccountID' => $Account->AccountID, 'CompanyID' => $CompanyID, 'ServiceID' => $ServiceTemaplateReference->ServiceId))->first();
				if (isset($AccountService) && $AccountService != '') {
					AccountService::where(array('AccountID' => $Account->AccountID, 'CompanyID' => $CompanyID, 'ServiceID' => $ServiceTemaplateReference->ServiceId))
						->update(array('ServiceID' => $ServiceTemaplateReference->ServiceId, 'updated_at' => $date));
					$AccountService = AccountService::where(array('AccountID' => $Account->AccountID, 'CompanyID' => $CompanyID, 'ServiceID' => $ServiceTemaplateReference->ServiceId))->first();
				} else {
					$servicedata['ServiceID'] = $ServiceTemaplateReference->ServiceId;
					$servicedata['AccountID'] = $Account->AccountID;
					$servicedata['CompanyID'] = $CompanyID;
					$AccountService = AccountService::insert($servicedata);
				}
				Log::info('AccountServiceID ' . $AccountService->AccountServiceID);

				$AccountServiceContractExisting = AccountServiceContract::where(array('AccountServiceID' => $AccountService->AccountServiceID))->first();
				if (isset($AccountServiceContractExisting) && $AccountServiceContractExisting != '') {



					$AccountServiceContract["AccountServiceID"] = $AccountService->AccountServiceID;
					$AccountServiceContract["Duration"] = empty($AccountServiceContract['Duration']) ? $ServiceTemaplateReference->ContractDuration : $AccountServiceContract['Duration'];
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
					$AccountServiceContract["Duration"] = empty($AccountServiceContract['Duration']) ? $ServiceTemaplateReference->ContractDuration : $AccountServiceContract['Duration'];
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
						AccountSubsDynamicFields::where(array('AccountSubscriptionID'=>$AccountSubscriptionDB["AccountSubscriptionID"]))->delete();
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






			return Response::json(array("status" => "success", "message" => $message));


		} catch (Exception $ex) {
			Log::info('createAccountService:Exception.' . $ex->getTraceAsString());
			return Response::json(["status" => "failed", "message" => $ex->getMessage()]);
		}
	}



	public function createAccount() {
		Log::info('createAccount:Create new Account.');
		try {
			$accountData = Input::all();
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

			$data['Number'] = $accountData['Number'];
			$data['FirstName'] = $accountData['FirstName'];
			$data['LastName'] = $accountData['LastName'];
			$data['Phone'] = $accountData['Phone'];
			$data['Address1'] = $accountData['Address1'];
			$data['Address2'] = $accountData['Address2'];
			$data['City'] = $accountData['City'];
			$data['Email'] = $accountData['Email'];
			$data['BillingEmail'] = $accountData['BillingEmail'];
			$data['Owner'] = $accountData['OwnerID'];
			$data['CurrencyId'] = $accountData['Currency'];
			$data['Country'] = $accountData['Country'];
			$data['password'] = isset($accountData['CustomerPanelPassword']) ? Crypt::encrypt($accountData['CustomerPanelPassword']) :'';
			$data['VatNumber'] = $accountData['VatNumber'];
			$data['Language']= $accountData['Language'];

			$data['CompanyID'] = $CompanyID;
			$data['AccountType'] = 1;
			$data['IsVendor'] = isset($accountData['IsVendor']) ? 1 : 0;
			$data['IsCustomer'] = isset($accountData['IsCustomer']) ? 1 : 0;
			$data['IsReseller'] = isset($accountData['IsReseller']) ? 1 : 0;
			$data['Billing'] = isset($data['Billing']) ? 1 : 0;
			$data['created_by'] = $CreatedBy;
			$data['AccountType'] = 1;
			$data['AccountName'] = isset($accountData['AccountName']) ? trim($accountData['AccountName']) : '';
			$data['PaymentMethod'] = $accountData['PaymentMethod'];



			$AccountPaymentAutomation['AutoTopup']= $accountData['AutoTopup'];
			$AccountPaymentAutomation['MinThreshold']= $accountData['MinThreshold'];
			$AccountPaymentAutomation['TopupAmount']= $accountData['TopupAmount'];
			$AccountPaymentAutomation['AutoOutpayment']= $accountData['AutoOutpayment'];
			$AccountPaymentAutomation['OutPaymentThreshold']= $accountData['OutPaymentThreshold'];
			$AccountPaymentAutomation['OutPaymentAmount']= $accountData['OutPaymentAmount'];

			if (!empty($data['PaymentMethod']) && !in_array($data['PaymentMethod'], AccountsApiController::$PaymentMethod)) {
				return Response::json(array("status" => "failed", "message" => "Please enter the valid payment method."));
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
					return Response::json(["status" => "failed", "message" => $errors]);
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
					return Response::json(["status" => "failed", "message" => $errors]);
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
				return Response::json(array("status" => "failed", "message" => "Account Name contains illegal character."));
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
				return Response::json(["status" => "failed", "message" => $errors]);
			}

			if($data['IsReseller']==1){

				$ResellerCount = Reseller::where('ChildCompanyID',$CompanyID)->count();
				if($ResellerCount>0){
					return Response::json(["status" => "failed", "message" => "Reseller user can not create reseller"]);
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
				$ResellerData['DomainUrl'] = $accountData['ReSellerDomainUrl'];
				Reseller::$messages['Email.required'] = 'The Reseller Email is Required.';
				Reseller::$messages['Password.required'] = 'The Reseller Password is Required.';
				$validator = Validator::make($ResellerData, Reseller::$rules, Reseller::$messages);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["status" => "failed", "message" => $errors]);
				}

				if(!empty($ResellerData['AllowWhiteLabel'])){
					if(empty($ResellerData['DomainUrl'])){
						$ResellerData['DomainUrl'] = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'WEB_URL'])->pluck('Value');
					}
					if(!Reseller::IsAllowDomainUrl($ResellerData['DomainUrl'],'')){
						return  Response::json(array("status" => "failed", "message" => "please setup different domain for your reseller."));
					}
				}

			}
			$data['CurrencyId'] = Currency::where('Code',$data['CurrencyId'])->pluck('CurrencyId');
			if (!isset($data['CurrencyId'])) {
				return Response::json(["status"=>"failed", "message"=>"Please provide the valid currency"]);
			}
			$data['Country'] = Country::where(['Country' => $data['Country']])->pluck('Country');
			if (!isset($data['Country'])) {
				return Response::json(["status"=>"failed", "message"=>"Please provide the valid country"]);
			}

			$data['LanguageID'] = Language::where('Language',$data['Language'])->pluck('LanguageID');
			if (!isset($data['LanguageID'])) {
				return Response::json(["status"=>"failed", "message"=>"Please provide the valid Language"]);
			}

			AccountBilling::$rulesAPI['billing_type'] = 'required';
			AccountBilling::$rulesAPI['billing_class'] = 'required';
			AccountBilling::$rulesAPI['billing_cycle'] = 'required';
			//AccountBilling::$rulesAPI['billing_cycle_options'] = 'required';


			$BillingSetting['billing_type'] = $accountData['BillingType'];
			$BillingSetting['billing_class']= $accountData['BillingClass'];
			$BillingSetting['billing_cycle']= $accountData['BillingCycleType'];
			$BillingSetting['billing_cycle_options']= $accountData['BillingCycleValue'];
			$BillingSetting['billing_start_date']= $accountData['BillingStartDate'];
			$BillingSetting['NextInvoiceDate']= $accountData['NextInvoiceDate'];

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
					return Response::json(["status" => "failed", "message" => "NextInvoiceDate Should be greater than BillingStartDate"]);
				}
				$validator = Validator::make($BillingSetting, AccountBilling::$rulesAPI);
				if ($validator->fails()) {
					$errors = "";
					foreach ($validator->messages()->all() as $error) {
						$errors .= $error . "<br>";
					}
					return Response::json(["status" => "failed", "message" => $errors]);
				}

				if ($BillingSetting['billing_type'] == "Prepaid") {
					$BillingSetting['billing_type'] = "1";
				} else if ($BillingSetting['billing_type'] == "Postpaid") {
					$BillingSetting['billing_type'] = "2";
				} else {
					return Response::json(["status" => "failed", "message" => "Please select the valid billing type"]);
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

				if (isset($accountData['DynamicFields'])) {
					$AccountReferenceArr = json_decode(json_encode(json_decode($accountData['DynamicFields'])), true);
					$AccountReference = $AccountReferenceArr[0];
					$DynamicFieldsID = DynamicFields::where(['CompanyID'=>User::get_companyID(),'Type'=>'account','Status'=>1,'FieldSlug'=>$AccountReference['Name']])->pluck('DynamicFieldsID');
					if(!empty($DynamicFieldsID)) {
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
					$BillingClassSql = BillingClass::where('Name', $BillingSetting['billing_class']);
					$BillingClass = $BillingClassSql->first();
					if (!isset($BillingClass)) {
						return Response::json(["status" => "failed", "message" => "Please select the valid billing class"]);
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
								return Response::json(array("status" => "failed", "message" => "Problem Creating Account."));
							}
						} catch (Exception $e) {
							try {
								DB::rollback();
							} catch (\Exception $err) {
								Log::error($err);
							}
							Log::error($e);
							return Response::json(array("status" => "failed", "message" => "Problem Creating Account."));
						}
					}
				}
				CompanySetting::setKeyVal('LastAccountNo', $account->Number);
				return Response::json(array("status" => "success", "message" => "Account Successfully Created", 'Account ID' => $account->AccountID, 'redirect' => URL::to('/accounts/' . $account->AccountID . '/edit')));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Creating Account."));
			}

		} catch (Exception $ex) {
			Log::error("CreateAccountAPI Exception" . $ex->getTraceAsString());
			return Response::json(["status" => "failed", "message" => $ex->getMessage()]);
			//return  Response::json(array("status" => "failed", "message" => $ex->getMessage(),'LastID'=>'','newcreated'=>''));
		}

		//return Redirect::route('accounts.index')->with('success_message', 'Accounts Successfully Created');
	}
	public function getPaymentMethodList()
	{
		Log::info('getPaymentMethodList for Account.');

		return Response::json(array("status" => "success", "PaymentMethod" => AccountsApiController::$PaymentMethod));
	}

	public function callAccountBalanceAPI()
	{
		$accountresponse = array();

		//https://appcenter.intuit.com/Playground/OAuth/AccessGranted?ia=true&oauth_token=lvprdco5CjnH7fx5z6P9RRHFm9AUrRHhhoH3UdCwjoGRrLEv&oauth_verifier=0hzsvq6&realmId=193514449127769&dataSource=QBO
		//$query = 'query?query='.urlencode('Select * from Customer');
		//$query = 'account/1';


		$url = "http://speakintelligence.neon-soft.com/api/checkBalance";
		$curl = curl_init();

		$postdata = array(
			'AccountID'                => '6767'
		);

		$auth = base64_encode('saeedsumera@hotmail.com:Welcome100');
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query($postdata, '', '&'),
			CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic " . $auth,
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			$accountresponse["error"] = $err;
			//echo "cURL Error #:" . $err;
		} else {
			$accountresponse["response"] = $response;
			//echo $response;
		}
		return Response::json(array("status" => "success", "PaymentMethod" => $accountresponse));
		//return ;

		/*
		$URL = 'https://sandbox-quickbooks.api.intuit.com';

		$companyid = '193514342633202';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $LicenceVerifierURL);
		curl_setopt($ch, CURLOPT_VERBOSE, '1');
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);//TRUE to automatically set the Referer: field in requests where it follows a Location: redirect.
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);//TRUE to force the connection to explicitly close when it has finished processing, and not be pooled for reuse.
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);//TRUE to force the use of a new connection instead of a cached one.


		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		// curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		//NVPRequest for submitting to server
		$nvpreq = "json=" . json_encode($post);

		//$nvpreq = http_build_query($post);

		////setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

		//getting response from server
		$response = curl_exec($ch);

		// echo $response;
		return $response; */

	}
}