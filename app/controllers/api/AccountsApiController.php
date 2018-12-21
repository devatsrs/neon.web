<?php

class AccountsApiController extends ApiController {

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

	public function checkBalance(){
		$data=Input::all();
		$Result=array();
		$AccountBalance=0;
		if(!empty($data['CustomerID'])) {
			$CompanyID = Account::where(["AccountID" => $data['CustomerID']])->pluck('CompanyId');

			if(intval($CompanyID) > 0){
				$AccountBalance = AccountBalance::getNewAccountExposure($CompanyID, $data['CustomerID']);
			}else{
				return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
			}

		}else if(!empty($data['AccountNo'])) {
			$Account = Account::where(["Number" => $data['AccountNo']])->select('CompanyId','AccountID')->first();

			if(!empty($Account)) {
				$CompanyID = $Account->CompanyId;
				$AccountID = $Account->AccountID;
				$AccountBalance = AccountBalance::getNewAccountExposure($CompanyID, $AccountID);
			}else{
				return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
			}

		}else {
			return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
		}

		if($AccountBalance > 0){
			$Result['has_balance']=1;
			$Result['amount']=$AccountBalance;
		}else{
			$Result['has_balance']=0;
			$Result['amount']=$AccountBalance;
		}

		return Response::json(["status"=>"success", "data"=>$Result]);
	}

	public function createAccount() {
		Log::info('createAccount:Create new Account.');
		try {
			$accountData = Input::all();
			$ServiceID = 0;
			$companyID = User::get_companyID();
			//$data['Owner'] = $post_vars->Owner;
			if (isset($accountData['OwnerID'])) {
				$data['Owner'] = $accountData['OwnerID'];
			}else {

				$ResellerOwner = empty($data['ResellerOwner']) ? 0 : $data['ResellerOwner'];
				if($ResellerOwner>0){
					$Reseller = Reseller::getResellerDetails($ResellerOwner);
					$ResellerCompanyID = $Reseller->ChildCompanyID;
					$ResellerUser =User::where('CompanyID',$ResellerCompanyID)->first();
					$ResellerUserID = $ResellerUser->UserID;
					$companyID=$ResellerCompanyID;
					$data['Owner'] = $ResellerUserID;
				}
			}
			if (isset($accountData['Currency'])) {
				$data['CurrencyId'] = $accountData['Currency'];
			}


			$data['Number'] = $accountData['Number'];
			$data['AccountName'] = $accountData['AccountName'];
			$data['FirstName'] = $accountData['FirstName'];
			$data['LastName'] = $accountData['LastName'];
			$data['Phone'] = $accountData['Phone'];
			$data['Address1'] = $accountData['Address1'];
			$data['Address2'] = $accountData['Address2'];
			$data['City'] = $accountData['City'];
			$data['Email'] = $accountData['Email'];
			$data['BillingEmail'] = $accountData['BillingEmail'];
			$data['OwnerID'] = $accountData['OwnerID'];
			$data['IsVendor'] = $accountData['IsVendor'];
			$data['IsCustomer'] = $accountData['IsCustomer'];
			$data['IsReseller'] = $accountData['IsReseller'];
			$data['Currency'] = $accountData['Currency'];
			$data['Country'] = $accountData['Country'];
			$data['CustomerPanelPassword'] = $accountData['CustomerPanelPassword'];
			$data['VatNumber'] = $accountData['VatNumber'];
			$data['Language']= $accountData['Language'];


			$data['CompanyID'] = $companyID;
			$data['AccountType'] = 1;
			$data['IsVendor'] = isset($data['IsVendor']) ? 1 : 0;
			$data['IsCustomer'] = isset($data['IsCustomer']) ? 1 : 0;
			$data['IsReseller'] = isset($data['IsReseller']) ? 1 : 0;
			$data['Billing'] = isset($data['Billing']) ? 1 : 0;
			$data['created_by'] = User::get_user_full_name();
			$data['AccountType'] = 1;
			$data['AccountName'] = trim($data['AccountName']);



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

			AccountBilling::$rulesAPI['billing_type'] = 'required';
			AccountBilling::$rulesAPI['billing_class'] = 'required';
			AccountBilling::$rulesAPI['billing_cycle'] = 'required';
			//AccountBilling::$rulesAPI['billing_cycle_options'] = 'required';


			$BillingSetting['billing_type'] = $accountData['BillingType'];
			$BillingSetting['billing_class']= $accountData['BillingClass'];
			$BillingSetting['billing_cycle']= $accountData['BillingCycleType'];
			$BillingSetting['billing_cycle_options']= $accountData['BillingCycleValue'];
			$BillingSetting['billing_start_date']= $accountData['BillingStartDate'];

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
			}else {
				return Response::json(["status" => "failed", "message" => "Please select the valid billing type"]);
			}

			$CurrenctCodeSql = Currency::where('Code',$data['CurrencyId']);
			Log::info('createAccount $CurrenctCodeSql.' . $CurrenctCodeSql->toSql());
			$CurrenctCodeResult = $CurrenctCodeSql->first();
			if (!isset($CurrenctCodeResult)) {
				return Response::json(["status"=>"failed", "message"=>"Please provide the valid currency"]);
			}

			$data['CurrencyId'] = $CurrenctCodeResult->CurrencyId;

			if(!empty($data['Country'])) {
				$Country = Country::where(['Country' => $data['Country']])->pluck('Country');
			}else{
				$Country='';
				return Response::json(["status"=>"failed", "message"=>"Please provide the valid country"]);
			}
			$dataAccount['Country']  = $Country; // change iso3 to title

			$LanguageCodeSql = Language::where('Language',$data['Language']);
			Log::info('createAccount $LanguageCodeSql.' . $LanguageCodeSql->toSql());
			$LanguageCodeResult = $LanguageCodeSql->first();
			if (!isset($LanguageCodeResult)) {
				return Response::json(["status"=>"failed", "message"=>"Please provide the valid Language"]);
			}

			$data['LanguageID'] = $LanguageCodeResult->LanguageID;
			Log::info('createAccount $data[\'LanguageID\'].' . $data['LanguageID']);
			if (isset($data['CustomerPanelPassword'])) {
				$data['password'] = Crypt::encrypt($data['CustomerPanelPassword']);
			}
			Log::info('createAccount1 $data[\'LanguageID\'].' . $data['LanguageID']);
			//
			if ($account = Account::create($data)) {


				if (trim(Input::get('Number')) == '') {
					CompanySetting::setKeyVal('LastAccountNo', $account->Number);
				}

				$AccountDetails=array();
				//$AccountDetails['ResellerOwner'] = $ResellerOwner;
				$AccountDetails['AccountID'] = $account->AccountID;
				AccountDetails::create($AccountDetails);


				$account->update($data);

				Log::info('createAccount done $data[\'LanguageID\'].' . $data['LanguageID']);



				$dataAccountBilling['BillingType'] = $BillingSetting['billing_type'];
				//get from billing class id

				Log::info('createAccount $BillingSetting[\'billing_class\'].' . $BillingSetting['billing_class']);
				$BillingClassSql = BillingClass::where('Name',$BillingSetting['billing_class']);
				Log::info('createAccount $LanguageCodeSql.' . $BillingClassSql->toSql());
				$BillingClass = $BillingClassSql->first();
				if (!isset($BillingClass)) {
					return Response::json(["status"=>"failed", "message"=>"Please select the valid billing class"]);
				}
				//$BillingClass = BillingClass::find($BillingSetting['billing_class']);
				Log::info('createAccount done $BillingClass.' . $BillingClass->Name);

				$dataAccountBilling['BillingClassID'] = $BillingSetting['billing_class'];
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
				if (isset($BillingSetting['billing_start_date'])) {
					$BillingStartDate = $BillingSetting['billing_start_date'];
				}else {
					$BillingStartDate = date('Y-m-d');
				}

				/**
				 *  if not first invoice generation*/
				Log::info($BillingCycleType.' '.$BillingCycleValue.' '.$BillingStartDate);
				$NextBillingDate = next_billing_date($BillingCycleType, $BillingCycleValue, strtotime($BillingStartDate));
				$NextChargedDate = date('Y-m-d', strtotime('-1 day', strtotime($NextBillingDate)));

				$dataAccountBilling['BillingStartDate'] = $BillingStartDate;
				$dataAccountBilling['LastInvoiceDate'] = $BillingStartDate;
				$dataAccountBilling['LastChargeDate'] = $BillingStartDate;
				$dataAccountBilling['NextInvoiceDate'] = $NextBillingDate;
				$dataAccountBilling['NextChargeDate'] = $NextChargedDate;
				/**
				 *  if not first invoice generation

				$dataAccountBilling['BillingStartDate'] = $BillingStartDate;
				$dataAccountBilling['LastInvoiceDate']  = $BillingStartDate;
				$dataAccountBilling['LastChargeDate']   = $BillingStartDate;
				$dataAccountBilling['NextInvoiceDate']  = $BillingStartDate;
				$dataAccountBilling['NextChargeDate']   = $BillingStartDate;
				 */
				Log::info(print_r($dataAccountBilling,true));

				AccountBilling::insertUpdateBilling($account->AccountID, $dataAccountBilling,0);
				AccountBilling::storeFirstTimeInvoicePeriod($account->AccountID, 0);
				CompanySetting::setKeyVal('LastAccountNo', $account->Number);

				return Response::json(array("status" => "success", "message" => "Account Successfully Created", 'Account ID' => $account->AccountID, 'redirect' => URL::to('/accounts/' . $account->AccountID . '/edit')));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Creating Account."));
			}

		} catch (Exception $ex) {
			return Response::json(["status" => "failed", "message" => $ex->getMessage()]);
			//return  Response::json(array("status" => "failed", "message" => $ex->getMessage(),'LastID'=>'','newcreated'=>''));
		}

		//return Redirect::route('accounts.index')->with('success_message', 'Accounts Successfully Created');
	}
}