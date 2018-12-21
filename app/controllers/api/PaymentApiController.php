<?php

class PaymentApiController extends ApiController {


	public function getList()
	{
		return Response::json(["status"=>"success", "data"=>Payment::paymentList()]);
	}

	public function getPaymentHistory(){
		$data=Input::all();
		$Result=[];
		$CompanyID=0;
		$AccountID=0;
		if(!empty($data['CustomerID'])) {
			$CompanyID = Account::where(["AccountID" => $data['CustomerID']])->pluck('CompanyId');
			$AccountID = $data['CustomerID'];
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->select('CompanyId','AccountID')->first();

			if(!empty($Account)) {
				$CompanyID = $Account->CompanyId;
				$AccountID = $Account->AccountID;
			}else{
				return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
			}
		}else{
			return Response::json(["status"=>"failed", "data"=>"CustomerID Required"]);
		}

		if(!empty($AccountID) && !empty($CompanyID)){
			if(!empty($data['StartDate']) && !empty($data['EndDate'])){
				$Result=Payment::where(['CompanyID'=>$CompanyID,'AccountID'=>$AccountID])
					->whereBetween('PaymentDate',[$data['StartDate'],$data['EndDate']])
					->get();
			}else if(!empty($data['StartDate'])){
				$Result=Payment::where(['CompanyID'=>$CompanyID,'AccountID'=>$AccountID])
					->whereDate('PaymentDate','>=',$data['StartDate'])
					->get();
			}else{
				$Result=Payment::where(['CompanyID'=>$CompanyID,'AccountID'=>$AccountID])
					->get();
			}
			return Response::json(["status"=>"success", "data"=>$Result]);
		}else{
			return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
		}


	}

	public function requestFund(){
		$data=Input::all();

		$rules = array(
			'Amount' => 'required',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrv2');

		$validator = Validator::make($data, $rules);
		$validator->setPresenceVerifier($verifier);

		if ($validator->fails()) {
			return json_validator_response($validator);
		}

		$CompanyID=0;
		$AccountID=0;
		if(!empty($data['CustomerID'])) {
			$CompanyID = Account::where(["AccountID" => $data['CustomerID']])->pluck('CompanyId');
			$AccountID = $data['CustomerID'];
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->select('CompanyId','AccountID')->first();

			if(!empty($Account)) {
				$CompanyID = $Account->CompanyId;
				$AccountID = $Account->AccountID;
			}else{
				return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
			}
		}else{
			return Response::json(["status"=>"failed", "data"=>"CustomerID Required"]);
		}

		if(!empty($AccountID) && !empty($CompanyID)){
			$data['CompanyId']=$CompanyID;
			$data['Status']='Pending Approval';
			$data['PaymentType']='Payment Out';
			$data['PaymentDate']=date('Y-m-d 00:00:00');
			$data['created_at']=date("Y-m-d H:i:s");
			$data['CreatedBy']='API';
			$data['AccountID']=$AccountID;
			$data['IsOutPayment']=1;
			unset($data['CustomerID']);
			unset($data['AccountNo']);

			if ($Payment = Payment::create($data)) {
				return Response::json(array("status" => "success", "data" => ["RequestFundID"=>$Payment->PaymentID]));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Creating Payment."));
			}

		}else{
			return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
		}


	}

	public function depositFund(){
		$data=Input::all();
		$AccountID=0;
		$BillingClassID=0;
		$errors=[];

		if(!empty($data['CustomerID'])) {
			$AccountID = $data['CustomerID'];
		}else if(!empty($data['AccountNo'])){
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		}else{
			return Response::json(["status"=>"failed", "message"=>"CustomerID OR AccountNo Required"]);
		}

		$rules = array(
			'Amount' => 'required|numeric',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrv');

		$validator = Validator::make($data, $rules);
		$validator->setPresenceVerifier($verifier);

		if ($validator->fails()) {
			return json_validator_response($validator);
		}

		$Account=Account::where('AccountID',$AccountID)->first();
		if(!empty($Account)){
			if(isset($data['BillingClassID']) && intval($data['BillingClassID']) > 0){
				$ExistBillingClass=BillingClass::where('BillingClassID',$data['BillingClassID'])->count();
				if($ExistBillingClass > 0){
					$BillingClassID=$data['BillingClassID'];
				}else{
					$errormsg="BillingClassID ".$data['BillingClassID']." Not set on this Account.";
					return Response::json(["status"=>"failed", "message"=>$errormsg]);
				}
			}else{
				$BillingClassID=AccountBilling::getBillingClassID($AccountID);
			}

			$PaymentData=array();
			$CompanyID=$Account->CompanyID;
			$PaymentMethod=$Account->PaymentMethod;
			if (!empty($PaymentMethod) && ($PaymentMethod=='Stripe' || $PaymentMethod=='AuthorizeNet' || $PaymentMethod=='StripeACH')) {
				$PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($PaymentMethod);

				$PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
				$PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $CompanyID);
				$CustomerProfile = AccountPaymentProfile::getActiveProfile($Account->AccountID, $PaymentGatewayID);
				if (!empty($CustomerProfile)){
					$PaymentData['AccountID']=$Account->AccountID;
					$PaymentData['AccountPaymentProfileID']=$CustomerProfile->AccountPaymentProfileID;
					$PaymentData['outstanginamount']=$data['Amount'];
					$PaymentData['PaymentGateway']=$PaymentMethod;

					$PaymentResponse = $PaymentIntegration->paymentWithApiProfile($PaymentData);

					if(!empty($PaymentResponse['Response']['response_code']) && $PaymentResponse['Response']['response_code']==1){
						//Payment Success
						Log::info("==== Payment success Log ====");
						Log::info(print_r($PaymentResponse,true));
						self::PaymentLog($Account,$PaymentResponse,$data);
						$InvoiceGenerate=self::GenerateInvoice($PaymentData['AccountID'],$PaymentData['outstanginamount'],$BillingClassID);

						return Response::json(["status"=>"success","PaymentResponse"=>$PaymentResponse,"InvoiceResponse"=>$InvoiceGenerate]);

					}else{
						//Failed Payment
						return Response::json(["status"=>"failed", "message"=>"Payment Failed.","PaymentResponse"=>$PaymentResponse]);
					}
					
				}else{
					$errors[] = 'Payment Profile Not set:' . $Account->AccountName;
				}


			}else{
				$errors[]= 'Payment Method Not set OR Payment Method is not Stripe:' . $Account->AccountName;

			}
		}else{
			return Response::json(["status"=>"failed", "message"=>"Account Not Found."]);
		}

		if(!empty($errors)){
			return Response::json(["status"=>"failed", "message"=>$errors]);
		}


	}

	public static function PaymentLog($Account,$PaymentResponse,$data){
		if(!empty($PaymentResponse)){
			$PaymentInsertData=array();
			$PaymentInsertData['CompanyID'] = $Account->CompanyId;
			$PaymentInsertData['AccountID'] = $Account->AccountID;
			$PaymentInsertData['PaymentDate'] = date('Y-m-d');
			$PaymentInsertData['PaymentMethod'] = $PaymentResponse['PaymentMethod'];
			$PaymentInsertData['CurrencyID'] = $Account->CurrencyId;
			$PaymentInsertData['PaymentType'] = 'Payment In';
			$PaymentInsertData['Notes'] = $PaymentResponse['transaction_notes'];
			$PaymentInsertData['Amount'] = floatval($data['Amount']);
			$PaymentInsertData['Status'] = 'Approved';
			$PaymentInsertData['created_at'] = date('Y-m-d H:i:s');
			$PaymentInsertData['updated_at'] = date('Y-m-d H:i:s');
			$PaymentInsertData['CreatedBy'] = 'API';
			$PaymentInsertData['ModifyBy'] = 'API';
			Payment::insert($PaymentInsertData);
		}
	}


	public static function GenerateInvoice($AccountID,$Amount,$BillingClassID){
		$created_at=date('Y-m-d H:i:s');
		$Account=Account::where('AccountID',$AccountID)->first();
		$CompanyID=$Account->CompanyId;
		try {
			DB::connection('sqlsrv2')->beginTransaction();

			$InvoiceTemplateID = BillingClass::getInvoiceTemplateID($BillingClassID);
			$InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
			$message = $InvoiceTemplate->InvoiceTo;
			$replace_array = Invoice::create_accountdetails($Account);
			$text = Invoice::getInvoiceToByAccount($message, $replace_array);
			$InvoiceToAddress = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
			$Terms = $InvoiceTemplate->Terms;
			$FooterTerm = $InvoiceTemplate->FooterTerm;

			$LastInvoiceNumber = InvoiceTemplate::getNextInvoiceNumber($InvoiceTemplateID);
			$FullInvoiceNumber = $InvoiceTemplate->InvoiceNumberPrefix . $LastInvoiceNumber;
			$InvoiceData["InvoiceNumber"] = $LastInvoiceNumber;
			$InvoiceData["CompanyID"] = $CompanyID;
			$InvoiceData["AccountID"] = intval($AccountID);
			$InvoiceData["Address"] = $InvoiceToAddress;        //change
			$InvoiceData["IssueDate"] = date('Y-m-d');  //today
			$InvoiceData["PONumber"] = ''; //blank
			$InvoiceData["SubTotal"] = str_replace(",", "", $Amount);
			//$InvoiceData["TotalDiscount"] = str_replace(",","",$data["TotalDiscount"]);
			$InvoiceData["TotalDiscount"] = 0;
			//$InvoiceData["TotalTax"] = str_replace(",","",$data["TotalTax"]);
			$InvoiceData["TotalTax"] = 0;
			$InvoiceData["GrandTotal"] = floatval(str_replace(",", "", $Amount));
			$InvoiceData["CurrencyID"] = $Account->CurrencyId;
			$InvoiceData["InvoiceType"] = Invoice::INVOICE_OUT;
			$InvoiceData["InvoiceStatus"] = Invoice::AWAITING;
			$InvoiceData["ItemInvoice"] = Invoice::ITEM_INVOICE;
			$InvoiceData["Note"] = 'API'; //static api generation
			$InvoiceData["Terms"] = $Terms;  //change
			$InvoiceData["CreatedBy"] = 'API';
			//$InvoiceData['InvoiceTotal'] = str_replace(",","",$data["GrandTotal"]);
			$InvoiceData['InvoiceTotal'] = 0;
			$InvoiceData['BillingClassID'] = $BillingClassID;
			$InvoiceData["FullInvoiceNumber"] = $FullInvoiceNumber;

			$InvoiceData["FooterTerm"] = $FooterTerm;


			//print_r($InvoiceData);die;
			//log::info(print_r($InvoiceData, true));
			$Invoice = Invoice::create($InvoiceData);

			if(empty($Invoice)){
				//$reseponse = array("status" => "failed", "message" => "Problem Creating Invoice. ");
				$error['message']="Problem Creating Invoice For Account ".$Account->AccountName;
				$error['status']="failed";
				return $error;

			}

			//Store Last Invoice Number.
			InvoiceTemplate::find($InvoiceTemplateID)->update(array("LastInvoiceNumber" => $LastInvoiceNumber));
			$InvoiceID = $Invoice->InvoiceID;
			log::info('InvoiceID ' . $InvoiceID);

			//InvoiceDetail
			if (!empty($InvoiceID)) {
				$InvoiceDetailData = array();
				$ProductID = Product::where(['CompanyId' => $CompanyID, 'Code' => 'topup'])->pluck('ProductID');
				if (empty($ProductID)) {
					$ProductData = array();
					$ProductData['CompanyID'] = $CompanyID;
					$ProductData['Name'] = 'TopUp';
					$ProductData['Amount'] = '0.00';
					$ProductData['Description'] = 'TopUp';
					$ProductData['Code'] = 'topup';
					$product = Product::create($ProductData);
					$ProductID = $product->ProductID;
				}

				$InvoiceDetailData['InvoiceID'] = $InvoiceID;
				$InvoiceDetailData['ProductID'] = $ProductID;
				$InvoiceDetailData['Description'] = 'TopUp';
				$InvoiceDetailData['Price'] = $Amount;
				$InvoiceDetailData['Qty'] = 1;
				$InvoiceDetailData['TaxAmount'] = 0;
				$InvoiceDetailData['LineTotal'] = $Amount;
				$InvoiceDetailData['StartDate'] = '';
				$InvoiceDetailData['EndDate'] = '';
				$InvoiceDetailData['Discount'] = 0;
				$InvoiceDetailData['TaxRateID'] = 0;
				$InvoiceDetailData['TaxRateID2'] = 0;
				$InvoiceDetailData['CreatedBy'] = "API";
				$InvoiceDetailData['ModifiedBy'] = "API";
				$InvoiceDetailData['created_at'] = $created_at;
				$InvoiceDetailData['updated_at'] = $created_at;
				$InvoiceDetailData['ProductType'] = Product::ITEM;
				$InvoiceDetailData['ServiceID'] = 0;
				$InvoiceDetailData['AccountSubscriptionID'] = 0;
				InvoiceDetail::create($InvoiceDetailData);

				//For Tax Rate
				$TaxRates=BillingClass::getTaxRateType($BillingClassID,TaxRate::TAX_ALL);

				if(!empty($TaxRates)){
					foreach ($TaxRates as $TaxRateID) {
						$TaxRateData=TaxRate::find($TaxRateID);

						if(!empty($TaxRateData)){
							$InvoiceTaxRates = array();
							$InvoiceTaxRates['InvoiceID'] = $InvoiceID;
							$InvoiceTaxRates['InvoiceDetailID'] = 0;
							$InvoiceTaxRates['TaxRateID'] = $TaxRateID;
							$TaxAmount=TaxRate::calculateProductTaxAmount($TaxRateID,$Amount);
							$InvoiceTaxRates['TaxAmount'] = $TaxAmount;
							$InvoiceTaxRates['Title'] = $TaxRateData->Title;
							$InvoiceTaxRates['InvoiceTaxType'] = 1;

							InvoiceTaxRate::create($InvoiceTaxRates);
						}

					}
				}

				//StockHistory
				$StockHistory=array();
				$temparray=array();
				$InvoiceDetailStockData=InvoiceDetail::where(['InvoiceID'=>$InvoiceID,'ProductType'=>1])->get();

				if(!empty($InvoiceDetailStockData) && count($InvoiceDetailStockData)>0) {
					foreach ($InvoiceDetailStockData as $CheckInvoiceHistory) {
						$ProductID = intval($CheckInvoiceHistory->ProductID);
						$Qty = intval($CheckInvoiceHistory->Qty);
						$temparray['CompanyID'] = $CompanyID;
						$temparray['ProductID'] = $ProductID;
						$temparray['InvoiceID'] = $InvoiceID;
						$temparray['Qty'] = $Qty;
						$temparray['Reason'] = '';
						$temparray['InvoiceNumber'] = $InvoiceData["FullInvoiceNumber"];
						$temparray['created_by'] = "API";
						array_push($StockHistory, $temparray);
					}

					$historyData=StockHistoryCalculations($StockHistory);
				}

				$invoiceloddata = array();
				$invoiceloddata['InvoiceID'] = $InvoiceID;
				$invoiceloddata['Note'] = 'Created By ' . "API";
				$invoiceloddata['created_at'] = $created_at;
				$invoiceloddata['InvoiceLogStatus'] = InVoiceLog::CREATED;
				$Log=InVoiceLog::insert($invoiceloddata);

				$pdf_path = Invoice::generate_pdf($Invoice->InvoiceID);

				if (empty($pdf_path)) {
					$error['message'] = 'Failed to generate Invoice PDF File';
					$error['status'] = 'failed';
					return $error;
				} else {

					$Invoice->update(["PDF" => $pdf_path]);
				}

				DB::connection('sqlsrv2')->commit();
				$SuccessMsg="Invoice Successfully Created.";
				$reseponse = array("status" => "success", "message" => $SuccessMsg,'LastInvoiceID'=>$Invoice->InvoiceID);
				return $reseponse;
			}else{
				$error['message']="Empty InvoiceID Found.";
				$error['status'] = 'failed';
				return $error;
			}

		}catch (Exception $e){
			Log::info($e);
			print_r( $e->getMessage());
			DB::connection('sqlsrv2')->rollback();
			$reseponse = array("status" => "failed", "message" => "Problem Creating Invoice. \n" . $e->getMessage());
			return $reseponse;
		}

	}

}