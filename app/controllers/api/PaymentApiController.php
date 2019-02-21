<?php
use app\controllers\api\Codes;

class PaymentApiController extends ApiController {


	public function getList()
	{
		return Response::json(["status"=>"success", "data"=>Payment::paymentList()]);
	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * StartDate,EndDate
	 * @Response
	 * 		It will give PaymentHistory.
	 */

	public function getPaymentHistory(){
		$post_vars = json_decode(file_get_contents("php://input"));
		$data=json_decode(json_encode($post_vars),true);
		$Result=[];
		$CompanyID=0;
		$AccountID=0;
		if(!empty($data['AccountID'])) {
			$CompanyID = Account::where(["AccountID" => $data['AccountID']])->pluck('CompanyId');
			$AccountID = $data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->select('CompanyId','AccountID')->first();

			if(!empty($Account)) {
				$CompanyID = $Account->CompanyId;
				$AccountID = $Account->AccountID;
			}else{
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}
			$Account = Account::where(["AccountID" => $AccountID])->first();
			$CompanyID = $Account->CompanyId;
			$AccountID = $Account->AccountID;
		}else{

			return Response::json(["ErrorMessage"=>"AccountID Required"],Codes::$Code402[0]);
		}

		$data['StartDate'] 	 = 		!empty($data['StartDate'])?$data['StartDate']:'0000-00-00';
		$data['EndDate'] 	 = 		!empty($data['EndDate'])?$data['EndDate']:'0000-00-00';

		if(!empty($AccountID) && !empty($CompanyID)){
			try {
				$query = "CALL prc_getTransactionHistory(" . $CompanyID . "," . $AccountID . ",'" . $data['StartDate'] . "','" . $data['EndDate'] . "')";
				//echo $query;die;
				$Result = DB::connection('sqlsrv2')->select($query);
				$Response = json_decode(json_encode($Result), true);
				return Response::json($Response,Codes::$Code200[0]);
			}catch(Exception $e){
				Log::info($e);
				$reseponse = array("ErrorMessage" => "Something Went Wrong.",Codes::$Code500[0]);
				return $reseponse;
			}
		}else{
			return Response::json(["ErrorMessage"=>"Account Not Found"],Codes::$Code402[0]);
		}

	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * Amount
	 * @Response
	 * RequestFundID
	 */

	public function requestFund(){
		$data=array();
		$post_vars = json_decode(file_get_contents("php://input"));
		if(!empty($post_vars)){
			$data=json_decode(json_encode($post_vars),true);
		}

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrv2');

		$rules = array(
			'Amount' => 'required',
		);
		$validator = Validator::make($data, $rules);
		$validator->setPresenceVerifier($verifier);

		if ($validator->fails()) {

			return Response::json([ "ErrorMessage" => $validator->messages()->first()],Codes::$Code402[0]);
		}

		$CompanyID=0;
		$AccountID=0;
		if(!empty($data['AccountID'])) {
			$CompanyID = Account::where(["AccountID" => $data['AccountID']])->pluck('CompanyId');
			$AccountID = $data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->select('CompanyId','AccountID')->first();

			if(!empty($Account)) {
				$CompanyID = $Account->CompanyId;
				$AccountID = $Account->AccountID;
			}else{
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}
			$Account = Account::where(["AccountID" => $AccountID])->first();
			if(!empty($Account)) {
				$CompanyID = $Account->CompanyId;
				$AccountID = $Account->AccountID;
			}

		}else{

			return Response::json(["ErrorMessage"=>"AccountID Required."],Codes::$Code402[0]);
		}

		if(!empty($AccountID) && !empty($CompanyID)){
			$data['CompanyID']=$CompanyID;
			$data['AccountID']=$AccountID;

			//if Auto payout is allowed
			$approved = !empty($data['Approved']) && $data['Approved'] == 1 ? 1 : 0;
			$paymentID = isset($data['PaymentID']) ? $data['PaymentID'] : false;
			$resp = ['status' => 'success'];
			if ($approved == 1) {
				$resp = AccountPayout::payout($data);
			}
			if($approved == 1){
				if($resp['status'] == "success") {
					AccountPayout::successPayoutCustomerEmail($data);
					$transactionID = @$resp['response']['balance_transaction'];
					$payoutID = @$resp['response']['balance_transaction'];
					$note = "Stripe payout_id: {$transactionID}, transaction_id: {$payoutID}";

					$data['Status'] 	 = 'Approved';
					$data['PaymentType'] = Payment::$action['Payment Out'];
					$data['PaymentDate'] = date('Y-m-d 00:00:00');
					$data['created_at']  = date("Y-m-d H:i:s");
					$data['CreatedBy'] 	 = 'API';
					$data['Notes'] 	 	 = $note;
					$data['IsOutPayment']= 1;
					unset($data['AccountNo']);
					unset($data['Approved']);
					unset($data['PaymentID']);
					unset($data['AccountDynamicField']);

					$Payment = $paymentID != false ? Payment::find($paymentID) : false;
					if($paymentID != false && $Payment != false){
						$Payment = $Payment->save($data);
					} else {
						$Payment = Payment::create($data);
					}

					if ($Payment != false) {
						return Response::json(array("RequestFundID" => $Payment->PaymentID),Codes::$Code200[0]);
					} else {
						return Response::json(array("ErrorMessage" => "Problem Creating Payment."),Codes::$Code500[0]);
					}

				} else {
					return Response::json(array("ErrorMessage" => @$resp['message']),Codes::$Code500[0]);
				}

			} else {
				$data['Status'] = 'Pending Approval';
				$data['PaymentType'] = Payment::$action['Payment Out'];
				$data['PaymentDate'] = date('Y-m-d 00:00:00');
				$data['created_at'] = date("Y-m-d H:i:s");
				$data['CreatedBy'] = 'API';
				$data['IsOutPayment'] = 1;
				unset($data['AccountNo']);
				unset($data['Approved']);
				unset($data['AccountDynamicField']);
				unset($data['PaymentID']);

				if ($Payment = Payment::create($data)) {
					return Response::json(array("RequestFundID" => $Payment->PaymentID),Codes::$Code200[0]);
				} else {
					return Response::json(array("ErrorMessage" => "Problem Creating Payment."),Codes::$Code500[0]);
				}
			}

		} else {
			return Response::json(["ErrorMessage"=>"Account Not Found"],Codes::$Code402[0]);
		}

	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * Amount, BillingClassID (optional)
	 *@Response
	 * PaymentResponse,InvoiceGeneration Response
	 */

	public function depositFund(){
		$post_vars = json_decode(file_get_contents("php://input"));
		$data=json_decode(json_encode($post_vars),true);

		$AccountID=0;
		$BillingClassID=0;
		$errors=[];

		if(!empty($data['AccountID'])) {
			$AccountID = $data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
			}
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
			}

		}else{
			return Response::json(["ErrorMessage"=>"AccountID OR AccountNo Required"],Codes::$Code402[0]);
		}

		$rules = array(
			'Amount' => 'required|numeric|min:1',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrv');

		$validator = Validator::make($data, $rules);
		$validator->setPresenceVerifier($verifier);

		if ($validator->fails()) {
			//return json_validator_response($validator);
			return Response::json([ "ErrorMessage" => $validator->messages()->first()],Codes::$Code402[0]);
		}

		$Account=Account::where('AccountID',$AccountID)->first();
		if(!empty($Account)){
			if(isset($data['BillingClassID']) && intval($data['BillingClassID']) > 0){
				$ExistBillingClass=BillingClass::where('BillingClassID',$data['BillingClassID'])->count();
				if($ExistBillingClass > 0){
					$BillingClassID=$data['BillingClassID'];
				}else{
					$errormsg="BillingClassID ".$data['BillingClassID']." Not set on this Account.";
					return Response::json(["ErrorMessage"=>$errormsg],Codes::$Code402[0]);
				}
			}else{
				$BillingClassID=AccountBilling::getBillingClassID($AccountID);
				if(empty($BillingClassID)){
					return Response::json(["ErrorMessage"=>"BillingClassID Not set on this Account."],Codes::$Code402[0]);
				}
			}

			//DeductTaxAmount
			$AmountExcludeTax=self::AmountExcludeTaxRate($BillingClassID,$data['Amount']);
			if($AmountExcludeTax > 0){
				Log::info("Original Amount = ".$data['Amount']);

				$data['Amount']=$data['Amount']-$AmountExcludeTax;

				Log::info("Amount Excluded Tax = ".$data['Amount']);

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
					$PaymentData['CreatedBy']="API";

					$PaymentResponse = $PaymentIntegration->paymentWithApiProfile($PaymentData);


					if(!empty($PaymentResponse['response_code']) && $PaymentResponse['response_code']==1){
						$ReturnData=array();
						$ReturnData['PaymentMethod']=$PaymentResponse['PaymentMethod'];
						$ReturnData['transaction_notes']=$PaymentResponse['transaction_notes'];
						$ReturnData['transaction_id']=$PaymentResponse['transaction_id'];
						//Payment Success
						Log::info("==== Payment success Log ====");
						Log::info(print_r($PaymentResponse,true));

						$InsertPayment=array();
						$InsertPayment['PaymentMethod']=$PaymentResponse['PaymentMethod'];
						$InsertPayment['transaction_notes']=$PaymentResponse['transaction_notes'];

						$PaymentID=self::PaymentLog($Account,$InsertPayment,$data);

						$InvoiceGenerate=self::GenerateInvoice($PaymentData['AccountID'],$PaymentData['outstanginamount'],$BillingClassID);

						if(!empty($PaymentID) && !empty($InvoiceGenerate['LastInvoiceID'])){
							$FullInvoiceNumber = Invoice::where(['InvoiceID'=>$InvoiceGenerate['LastInvoiceID']])->pluck('FullInvoiceNumber');
							$UpdateData=array();
							$UpdateData['InvoiceID'] = $InvoiceGenerate['LastInvoiceID'];
							$UpdateData['InvoiceNo'] = $FullInvoiceNumber;
							Payment::where(['PaymentID'=>$PaymentID])->update($UpdateData);
						}


						return Response::json(["PaymentResponse"=>$ReturnData,"InvoiceResponse"=>$InvoiceGenerate],Codes::$Code200[0]);

					}else{
						//Failed Payment
						return Response::json(["ErrorMessage"=>"Payment Failed.","PaymentResponse"=>$PaymentResponse],Codes::$Code402[0]);
					}

				}else{
					$errors[] = 'Payment Profile Not set:' . $Account->AccountName;
				}


			}else{
				$errors[]= 'Payment Method Not set OR Payment Method is not Stripe:' . $Account->AccountName;

			}
		}else{
			return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
		}

		if(!empty($errors)){
			return Response::json(["ErrorMessage"=>$errors],Codes::$Code402[0]);
		}
	}

	public function depositFund1()
	{
		$post_vars = json_decode(file_get_contents("php://input"));
		$data = json_decode(json_encode($post_vars), true);
		$BillingClassID=AccountBilling::getBillingClassID($data['AccountID']);
		if(empty($BillingClassID)){
			return Response::json(["ErrorMessage"=>"BillingClassID Not set on this Account."],Codes::$Code402[0]);
		}

		$InvoiceGenerate=self::GenerateInvoice($data['AccountID'],$data['amount'],$BillingClassID);
		Log::info('depositFund1:.' . print_r($InvoiceGenerate,true));
		if ($InvoiceGenerate["status"] == "success") {
			$InvoiceID = $InvoiceGenerate["LastInvoiceID"];
			$Invoice = Invoice::find($InvoiceID);
			$Company = Company::find($Invoice->CompanyID);
			$Message = "Customer Invoice Successfully Created";
			$Subject = "Customer Invoice";
			$Account=Account::find($Invoice->AccountID);
			$data['EmailTo'] 		= 	$Account->BillingEmail;
			$singleemail = $Account->BillingEmail;
			$data['InvoiceURL']		=   URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cview?email='.$singleemail);
			$body					=	EmailsTemplates::ReplaceEmail($singleemail,$Message);
			$data['Subject']		=	$Subject;
			$InvoiceBillingClass =	 Invoice::GetInvoiceBillingClass($Invoice);

			$invoicePdfSend = CompanySetting::getKeyVal('invoicePdfSend');
			if($invoicePdfSend!='Invalid Key' && $invoicePdfSend && !empty($Invoice->PDF) ){
				$data['AttachmentPaths']= array([
					"filename"=>pathinfo($Invoice->PDF, PATHINFO_BASENAME),
					"filepath"=>$Invoice->PDF
				]);
			}
			if(isset($postdata['email_from']) && !empty($postdata['email_from']))
			{
				$data['EmailFrom']	=	$postdata['email_from'];
			}else{
				$data['EmailFrom']	=	EmailsTemplates::GetEmailTemplateFrom(Invoice::EMAILTEMPLATE);
				$data['EmailFrom'] = 'aamar.nazir.codedesk@gmail.com';
			}

			Log::info('depositFund1:.1' . print_r($data,true));
			Log::info('depositFund1:.2' . $body);
			$status 				= 	$this->sendInvoiceMail($body,$data,0);
			Log::info('depositFund1:.3' . $status);
		}
	}
	function sendInvoiceMail($view,$data,$type=1)
	{

		$status = array('status' => 0, 'message' => 'Something wrong with sending mail.');
		if (isset($data['email_from'])) {
			$data['EmailFrom'] = $data['email_from'];
		}
		if (is_array($data['EmailTo'])) {
			$status = sendMail($view, $data, $type);
		} else {
			if (!empty($data['EmailTo'])) {
				$data['EmailTo'] = trim($data['EmailTo']);
				$status = sendMail($view, $data, 0);
			}
		}
		return $status;
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
			$Payment=Payment::create($PaymentInsertData);
			return $Payment->PaymentID;
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
			$InvoiceData["InvoiceStatus"] = Invoice::PAID;
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
				$error['ErrorMessage']="Problem Creating Invoice For Account ".$Account->AccountName;
				$error['status']="500";
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
					$error['ErrorMessage'] = 'Failed to generate Invoice PDF File';
					$error['status'] = '500';
					return $error;
				} else {

					$Invoice->update(["PDF" => $pdf_path]);
				}

				DB::connection('sqlsrv2')->commit();
				$SuccessMsg="Invoice Successfully Created.";
				$reseponse = array("status" => "success", "message" => $SuccessMsg,'LastInvoiceID'=>$Invoice->InvoiceID);
				return $reseponse;
			}else{
				$error['ErrorMessage']="Empty InvoiceID Found.";
				$error['status'] = '404';
				return $error;
			}

		}catch (Exception $e){
			Log::info($e);
			DB::connection('sqlsrv2')->rollback();
			$reseponse = array("ErrorMessage" => "Problem Creating Invoice. \n" . $e->getMessage());
			return $reseponse;
		}

	}

	public static function AmountExcludeTaxRate($BillingClassID,$Amount){
		$TotalTax=0;
		$TaxRates=BillingClass::getTaxRateType($BillingClassID,TaxRate::TAX_ALL);

		if(!empty($TaxRates)){

			foreach ($TaxRates as $TaxRateID) {

				$TaxRateData=TaxRate::find($TaxRateID);

				if(!empty($TaxRateData)){

					$TaxAmount=TaxRate::calculateProductTaxAmount($TaxRateID,$Amount);
					$TotalTax+=$TaxAmount;
				}
			}
		}
		return $TotalTax;
	}
}