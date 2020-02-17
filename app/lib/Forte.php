<?php

/*
*   Ctreated by : Visual Studio Code
*   User        : Muhammad Imran
*   Dtaed       : 07-02-2020
*/

class Forte 
{
    public $request;
    var $status;
    var $organizationID;
    var $locationID;
    var $ApiAccessID;
    var $apiSecureKey;
    var $hash;
    var $SandboxUrl;
    var $LiveUrl;
    var $ForteUrl;
    var $authToken;

    function __construct($CompanyID=0) 
    {
        $Forteobj = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$ForteSlug,$CompanyID);
        if ($Forteobj) {
            $this->SandboxUrl           =   "https://sandbox.forte.net/api/v3";
            $this->LiveUrl              =   "https://api.forte.net/v3";
            $this->organizationID 	    = 	$Forteobj->organizationID;
            $this->locationID 	        = 	$Forteobj->locationID;
            $this->ApiAccessID		    = 	$Forteobj->accessID;
            $this->apiSecureKey		    = 	$Forteobj->apiSecureKey;
            $this->forteDataLive        =   $Forteobj->forteDataLive;
            $this->authToken            =   base64_encode($this->ApiAccessID . ':' . $this->apiSecureKey);
            if ($this->forteDataLive == 1) {
                $this->ForteUrl         = 	$this->LiveUrl;
            } else {
                $this->ForteUrl         = 	$this->SandboxUrl;
            }
            $this->status               =   true;
        } else {
            $this->status               =   false;
        }
    }
    public function getApiData($data) 
    {
        //Address Info
        $AccountName    = $address = $physicalAddress = [];
        $firstName      = '';
        $lastName       = '';
        $AccountName    = explode(" ",$data['customer_name']);
        $total          = count($AccountName);
        if ($total > 0) {
            foreach($AccountName as $key=>$name) {
                if ($key == 0) {
                    $firstName = $name;
                } else {
                    if ($key == 1) {
                        $lastName =  $name;
                    } else {
                        $lastName .=  ' '.$name; 
                    }
                   
                }
            } 
        }

        $address = ['first_name' => $firstName, 'last_name' => $lastName];
        
        $echeck = [
            "sec_code"                  => "WEB",
            'account_type'              => $data['AccountHolderType'],
            'routing_number'            => $data['RoutingNumber'],
            'account_number'            => $data['AccountNumber'],
            'account_holder'            => $data['AccountHolderName']
        ];
     
        // $card = [
        //     'card_type' => $data['cardType'],
        //     'name_on_card' => $data['AccountHolderName'],
        //     'account_number' => $data['AccountNumber'],
        //     'expire_month' => $data['expireMonth'],
        //     'expire_year' => $data['expireYear'],
        //     'card_verification_value' => $data['CVVNumber']
        // ];
        //Credit Card Info
        $params = [
            'action'                    => $data['action'],  //sale, authorize, credit, void, capture, inquiry, verify, force, reverse
            'authorization_amount'      => $data['amount'],
            'billing_address'           => $address,
            'echeck'                    => $echeck     //change to 'echeck' => $echeck for an ACH transaction
        ];
        if($data['action'] == 'verify') {
            $physicalAddress = [
                'street_line1'=> $data['Address1'],
                'street_line2'=> $data['Address2'],
                'locality'=> $data['City'],
                'region'=> $data['State'],
                'postal_code'=> $data['PostCode']
            ];
            $address['phone'] = $data['Mobile'];
            $address['physicalAddress'] =  $physicalAddress;

            $params = [
                'action'                    => $data['action'],  //sale, authorize, credit, void, capture, inquiry, verify, force, reverse
                'authorization_amount'      => $data['amount'],
                'billing_address'           => $address,
                'echeck'                    => $echeck     //change to 'echeck' => $echeck for an ACH transaction
            ];
         }else {
            $params = [
                'action'                    => $data['action'],  //sale, authorize, credit, void, capture, inquiry, verify, force, reverse
                'authorization_amount'      => $data['amount'],
                'billing_address'           => $address,
                'echeck'                    => $echeck     //change to 'echeck' => $echeck for an ACH transaction
            ];
         }
        
        return $params;
    }
    public function doValidation($data)
    {
		$ValidationResponse = [];
		$rules = [
			'AccountNumber' => 'required|digits_between:6,19',
			'RoutingNumber' => 'required',
			'AccountHolderType' => 'required',
			'AccountHolderName' => 'required',
			//'Title' => 'required|unique:tblAutorizeCardDetail,NULL,CreditCardID,CompanyID,'.$CompanyID
        ];

		$validator = Validator::make($data, $rules);
		if ($validator->fails()) {
			$errors = "";
			foreach ($validator->messages()->all() as $error){
				$errors .= $error."<br>";
			}
			$ValidationResponse['status'] = 'failed';
			$ValidationResponse['message'] = $errors;
			return $ValidationResponse;
        }
        
		$CustomerID = $data['AccountID'];
		$account = Account::find($CustomerID);
		$CurrencyCode = Currency::getCurrency($account->CurrencyId);
		if (empty($CurrencyCode)) {
			$ValidationResponse['status'] = 'failed';
			$ValidationResponse['message'] = cus_lang("PAYMENT_MSG_NO_ACCOUNT_CURRENCY_AVAILABLE");
			return $ValidationResponse;
		}
		$data['currency'] = strtolower($CurrencyCode);
		$Country = $account->Country;
		if (!empty($Country)) {
			$CountryCode = Country::where(['Country'=>$Country])->pluck('ISO2');
		} else{
			$CountryCode = '';
		}
		if (empty($CountryCode)) {
			$ValidationResponse['status'] = 'failed';
			$ValidationResponse['message'] = cus_lang("PAYMENT_MSG_NO_ACCOUNT_COUNTRY_AVAILABLE");
			return $ValidationResponse;
		}
		$ValidationResponse['status'] = 'success';
		return $ValidationResponse;
	}
    public function verifyBankAccount($data)
    {
		try {
            $Account            = Account::find($data['AccountID']);
            $CurrencyID         = $Account->CurrencyId;
            $InvoiceCurrency    = Currency::getCurrency($CurrencyID);
            $accountname = empty($account->AccountName)?'':$account->AccountName;
            $data['customer_name'] = $accountname;
           
            if (is_int($data['GrandTotal'])) {
                $data['amount'] = str_replace(',', '', str_replace('.', '', $data['GrandTotal']));
                $data['amount'] = number_format((float)$Amount, 2, '.', '');
            } else {
                if($this->forteDataLive == 1) {
                    $data['amount'] = $data['GrandTotal']; // for live
                }else {
                    $data['mount'] = number_format(round($data['GrandTotal']), 2, '.', ''); // for testing
                }
            }
            $data['action'] = 'verify';
            $postData = $this->getApiData($data);
            $postUrl = $this->ForteUrl.'/organizations/org_'.$this->organizationID.'/locations/loc_'.$this->locationID.'/transactions/verify';
            try {
                $res = $this->sendCurlRequest($postUrl, $postdata);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if(!empty($res['status']) && $res['status']==1){
                $response['status']         = 'success';
                $response['note']           = 'Forte transaction_id '.$res['transactionID'];
                $response['transaction_id'] = $res['transactionID'];
                $response['amount']         = $res['responseData']['transactionAmount'];
                $response['response']       = $res;
            }else {
                $response['status']         = 'fail';
                $response['transaction_id'] = !empty($res['transactionID']) ? $res['transactionID'] : "";
                $response['error']          = $res['responseData']['responseMessage'];
                $response['response']       = $res;
                Log::info(print_r($res,true));
            }
        } catch (Exception $e) {
            log::info($e->getMessage());
            $response['status']             = 'fail';
            $response['error']              = $e->getMessage();
        }
        return $response;
	}
    public function paymentWithProfile($data)
    {
        $transactiondata = $Fortedata = $transactionResponse = [];
        $account = Account::find($data['AccountID']);
        $CustomerProfile                = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
        $ForteObj                       = json_decode($CustomerProfile->Options);
        

        $Fortedata['InvoiceNumber']  = $data['InvoiceNumber'];
        $Fortedata['GrandTotal']     = $data['outstanginamount'];
        $Fortedata['AccountID']      = $data['AccountID'];
        $Fortedata['action']         = 'sale';
        
        $postUrl = $this->ForteUrl.'/organizations/org_'.$this->organizationID.'/locations/loc_'.$this->locationID.'/transactions';
        $transaction = $this->payInvoice($postUrl, $Fortedata);
        echo "i am here payment with profile";
        print_r($transaction);
       
        if ($transaction['status']=='success') {
            $Status = TransactionLog::SUCCESS;
            $Notes  = 'Forte transaction_id ' . $transaction['transaction_id'];
            $transactionResponse['response_code']   = 1;
        } else{
            $Status = TransactionLog::FAILED;
            $Notes  = empty($transaction['error']) ? '' : $transaction['error'];
        }

        $transactionResponse['transaction_notes']   = $Notes;
        $transactionResponse['PaymentMethod']       = 'CREDIT CARD';
        $transactionResponse['failed_reason']       = $Notes;
        $transactionResponse['transaction_id']      = isset($transaction['transaction_id']) ? $transaction['transaction_id'] : '';
        $transactionResponse['Response']            = $transaction;

      
        $transactiondata['CompanyID']   = $account->CompanyId;
        $transactiondata['AccountID']   = $account->AccountID;
        $transactiondata['Notes']       = $Notes;

        if (!empty($transaction['transaction_id'])) {
            $transactiondata['Transaction'] = $transaction['transaction_id'];
        }
        if (!empty($transaction['amount'])) {
            $transactiondata['Amount'] = floatval($transaction['amount']);
        }

        $transactiondata['Status']      = $Status;
        $transactiondata['created_at']  = date('Y-m-d H:i:s');
        $transactiondata['updated_at']  = date('Y-m-d H:i:s');
        $transactiondata['CreatedBy']   = $data['CreatedBy'];
        $transactiondata['ModifyBy']    = $data['CreatedBy'];
        $transactiondata['Response']    = json_encode($transaction);
        TransactionLog::insert($transactiondata);
        return $transactionResponse;
    }

    public function payInvoice($postUrl, $data)
    {
        $profileOptions = [];
        try {
            $Account            = Account::join('tblAccountPaymentProfile','tblAccount.AccountID','=','tblAccountPaymentProfile.AccountID')->select('tblAccount.*', 'tblAccountPaymentProfile.Options')->where('tblAccount.AccountID', '=', $data['AccountID'])->first();
            $CurrencyID         = $Account->CurrencyId;
            echo "<pre>";
            print_r($Account);
            die('i am heresssss');
            $InvoiceCurrency    = Currency::getCurrency($CurrencyID);
            $accountname = !empty($account->AccountName)?$account->AccountName:'';
			$optionsData = $Account->Options;

            $data['customer_name'] = $accountname;
            if (is_int($data['GrandTotal'])) {
                $data['amount'] = str_replace(',', '', str_replace('.', '', $data['GrandTotal']));
                $data['amount'] = number_format((float)$Amount, 2, '.', '');
            } else {
                if($this->forteDataLive == 1) {
                    $data['amount'] = $data['GrandTotal']; // for live
                }else {
                    $data['amount'] = number_format(round($data['GrandTotal']), 2, '.', ''); // for testing
                }
            }
			$profileOptions = json_decode($optionsData, true);
			$data['AccountHolderType'] = $profileOptions['AccountHolderType'];
			$data['RoutingNumber'] = $profileOptions['RoutingNumber'];
			$data['AccountNumber'] = $profileOptions['AccountNumber'];
            $data['AccountHolderName'] = $profileOptions['AccountHolderName'];
            
            echo "<pre>".$account->AccountName;
            print_r($Account);
            die('i am heresssss');
            $postData = $this->getApiData($data);
			
            try {
                $res = $this->sendCurlRequest($postUrl, $postdata);
				echo "<pre> payInvoice res";
				print_r($res);
				
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }
           
            if(!empty($res['status']) && $res['status']==1){
                $response['status']         = 'success';
                $response['note']           = 'Forte transaction_id '.$res['transactionID'];
                $response['transaction_id'] = $res['transactionID'];
                $response['amount']         = $res['responseData']['transactionAmount'];
                $response['response']       = $res;
            }else {
                $response['status']         = 'fail';
                $response['transaction_id'] = !empty($res['transactionID']) ? $res['transactionID'] : "";
                $response['error']          = $res['responseData']['responseMessage'];
                $response['response']       = $res;
                Log::info(print_r($res,true));
            }
        } catch (Exception $e) {
            log::info($e->getMessage());
            $response['status']             = 'fail';
            $response['error']              = $e->getMessage();
        }
        return $response;
    }

    public function payInvoiceWithApi($data)
    {
        try {
            $data['invoiceCurrency']    = Currency::getCurrency($data['CurrencyId']);
            if (is_int($data['GrandTotal'])) {
                $data['amount'] = str_replace(',', '', str_replace('.', '', $data['GrandTotal']));
                $data['amount'] = number_format((float)$data['amount'], 2, '.', '');
            } else {
                if ($this->forteDataLive == 1) {
                    $data['amount'] = $data['GrandTotal']; // for live
                } else {
                    $data['amount'] = number_format(round($data['GrandTotal']), 2, '.', ''); // for testing
                }
            }

            $data['expire_month'] = $data['ExpirationMonth'];
            $data['expire_year']  = substr($data['ExpirationYear'], -2);
            $postUrl = $this->ForteUrl.'/organizations/org_'.$this->organizationID.'/locations/loc_'.$this->locationID.'/transactions';
            $postData = $this->getApiData($data);
         
            try {
                $res = $this->sendCurlRequest($postUrl, $postdata);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }

            if (!empty($res['status']) && $res['status']==1) {
                $response['status']         = 'success';
                $response['note']           = 'Forte transaction_id '.$res['transactionID'];
                $response['transaction_id'] = $res['transactionID'];
                $response['amount']         = $res['responseData']['transactionAmount'];
                $response['response']       = $res;
            } else {
                $response['status']         = 'fail';
                $response['transaction_id'] = !empty($res['transactionID']) ? $res['transactionID'] : "";
                $response['error']          = $res['responseData']['responseMessage'];
                $response['response']       = $res;
                Log::info(print_r($res,true));
            }
        } catch (Exception $e) {
            log::info($e->getMessage());
            $response['status']             = 'fail';
            $response['error']              = $e->getMessage();
        }
        return $response;
    }

    public function createProfile($data) 
    {
        $CustomerID         = $data['AccountID'];
        $CompanyID          = $data['CompanyID'];
        $PaymentGatewayID   = $data['PaymentGatewayID'];
        $isDefault = 1;
        $count = AccountPaymentProfile::where(['AccountID' => $CustomerID])
            ->where(['CompanyID' => $CompanyID])
            ->where(['PaymentGatewayID' => $PaymentGatewayID])
            ->where(['isDefault' => 1])
            ->count();

        if ($count>0) {
            $isDefault = 0;
        }
        $ForteResponse = $this->createForteProfile($data);
		
        if ($ForteResponse["status"] == "success") {
            $option = [
                'AccountHolderType'                 => $data['AccountHolderType'],
                'RoutingNumber'                     => $data['RoutingNumber'],
                'AccountNumber'                     => $data['AccountNumber'],
                'AccountHolderName'                 => $data['AccountHolderName']
            ];
            $CardDetail = [
                'Options' => json_encode($option),
                'Status' => 1,
                'isDefault' => $isDefault,
                'created_by' => Customer::get_accountName(),
                'CompanyID' => $CompanyID,
                'AccountID' => $CustomerID,
                'PaymentGatewayID' => $PaymentGatewayID
            ];
            if (AccountPaymentProfile::create($CardDetail)) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully Created"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Saving Payment Method Profile."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => $ForteResponse['error']));
        }
    }

    public function createForteProfile($data)
    {
        echo "<pre> createForteProfile";
        print_r($data);
        die();
        try {
            $postdata = [
                "organization_id"   => "org_".$this->organizationID,
                'account_number'    => $data['AccountNumber'],
                'routing_number'    => $data['RoutingNumber'],
                'account_type'      => $data['AccountHolderType'],
                'label'             => $data['AccountHolderName']
            ];
            $postUrl = $this->ForteUrl.'/organizations/org_'.$this->organizationID.'/bankaccounts';
            try {
                $res = $this->sendCurlRequest($postUrl, $postdata);
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                log::info($e->getMessage());
                $response['status']         = 'fail';
                $response['error']          = $e->getMessage();
            }
            
            if(!empty($res['status']) && $res['status']==1) {
                $response['status']         = 'success';
                $response['response']       = $res;
            } else {
                $response['status']         = 'fail';
                $response['response']       = $res;
                Log::info(print_r($res,true));
            }
        } catch (Exception $e) {
            log::info($e->getMessage());
            $response['status']             = 'fail';
            $response['error']              = $e->getMessage();
        }
        return $response;
    }
    public function doVerify($data)
    {
        $stripedata = [];
		if (empty($data['MicroDeposit1']) || empty($data['MicroDeposit2'])) {
			return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_MSG_BOTH_MICRODEPOSIT_REQUIRED")));
		}
		$cardID = $data['cardID'];
		$AccountPaymentProfile = AccountPaymentProfile::find($cardID);
		$options = json_decode($AccountPaymentProfile->Options,true);
		$CustomerProfileID = $options['CustomerProfileID'];
		$BankAccountID = $options['BankAccountID'];
		
		$stripedata['CustomerProfileID'] = $CustomerProfileID;
		$stripedata['BankAccountID'] = $BankAccountID;
		$stripedata['MicroDeposit1'] = $data['MicroDeposit1'];
		$stripedata['MicroDeposit2'] = $data['MicroDeposit2'];

		$StripeResponse = $this->verifyBankAccount($stripedata);
		if ($StripeResponse['status']== 'Success') {
			if ($StripeResponse['VerifyStatus']== 'verified') {
				$option = [
					'CustomerProfileID' => $CustomerProfileID,
					'BankAccountID' => $BankAccountID,
					'VerifyStatus' => $StripeResponse['VerifyStatus']
                ];
				$AccountPaymentProfile->update(array('Options' => json_encode($option)));
				return Response::json(array("status" => "success", "message" => cus_lang("PAYMENT_STRIPEACH_MSG_VERIFICATION_STATUS_IS").$StripeResponse['VerifyStatus']));
			} else {
				return Response::json(array("status" => "failed", "message" => cus_lang("PAYMENT_STRIPEACH_MSG_VERIFICATION_STATUS_IS").$StripeResponse['VerifyStatus']));
			}
		} else {
			return Response::json(array("status" => "failed", "message" => $StripeResponse['error']));
		}
	}
    public function sendCurlRequest($url,$postData) 
    {
        $httpHeader = [
            'Authorization: Basic '.$this->authToken,
            'X-Forte-Auth-Organization-id: org_'.$this->organizationID,
            'Accept:application/json',
            'Content-type: application/json'
        ];
        echo $url;
        die();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');  //POST, GET, PUT or DELETE (Create, Read, Update or Delete)

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));   //disable this line for GETs and DELETEs
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $data = json_decode($response);
        // Validate the response - the only successful code is 0
        $status = ((int)$info['http_code'] === 201) ? true : false;
        // Make the response a little more useable
         $res = [
            'status' => $status,
            'transactionID' => (isset($data->transactionID) ? $data->transactionID : null),
			'response' => $data->response->response_desc,
            'responseData' => $data
            ];
			
        return $res;
    }
    public function paymentValidateWithProfile($data)
    {
		$Response = array();
		$Response['status']= 'success';
        $account = Account::find($data['AccountID']);
		$CurrencyCode = Currency::getCurrency($account->CurrencyId);
		if (empty($CurrencyCode)) {
			$Response['status']='failed';
			$Response['message']= cus_lang("PAYMENT_MSG_NO_ACCOUNT_CURRENCY_AVAILABLE");
		}
		$CustomerProfile = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
		$StripeObj = json_decode($CustomerProfile->Options);
		if (empty($StripeObj)) {
			$Response['status']= 'failed';
			$Response['message']= cus_lang("PAYMENT_MSG_BANK_ACCOUNT_NOT_VERIFIED");
		}
		return $Response;
	}
    public function deleteForteProfile($Token)
    {
        $response['status']         = 'success';
        return $response;
    }
   
}