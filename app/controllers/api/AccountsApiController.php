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

	/**
	 * checkBalance():
	 * @Param mixed
	 * CustomerID/AccountNo
	 * @Response
	 * has_balance - 0/1
	 * amount
	 */

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
			$CompanyID = User::get_companyID();
			$CreatedBy = User::get_user_full_name();
			$ResellerData = [];
			//$data['Owner'] = $post_vars->Owner;

			/*else {

				$ResellerOwner = empty($accountData['ResellerOwner']) ? 0 : $accountData['ResellerOwner'];
				if($ResellerOwner>0){
					$Reseller = Reseller::getResellerDetails($ResellerOwner);
					if (!isset($Reseller)) {
						return Response::json(array("status" => "failed", "message" => "Reseller Account not found."));
					}
					$ResellerCompanyID = $Reseller->ChildCompanyID;
					Log::info('createAccount $ResellerOwner.' . $ResellerCompanyID);
					$ResellerUser =User::where('CompanyID',$ResellerCompanyID)->first();
					if (!isset($ResellerUser)) {
						return Response::json(array("status" => "failed", "message" => "Reseller Account not found."));
					}
					$ResellerUserID = $ResellerUser->UserID;
					Log::info('createAccount $ResellerUserID.' . $ResellerUserID);
					$companyID=$ResellerCompanyID;
					$data['Owner'] = $ResellerUserID;
				}
			}*/





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
				$ResellerData['Email'] = isset($accountData['Email']) ? $accountData['Email'] : '';
				$ResellerData['Password'] = isset($accountData['Password']) ? $accountData['Password'] : '';
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
						$ResellerData['Password'] = Crypt::encrypt($accountData['Password']);

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
			return Response::json(["status" => "failed", "message" => $ex->getMessage()]);
			//return  Response::json(array("status" => "failed", "message" => $ex->getMessage(),'LastID'=>'','newcreated'=>''));
		}

		//return Redirect::route('accounts.index')->with('success_message', 'Accounts Successfully Created');
	}
}