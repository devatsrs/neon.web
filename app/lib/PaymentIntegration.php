<?php
/**
 * Created by PhpStorm.
 * User: Bhavin
 * Date: 15/09/2017
 * Time: 05:00 PM
 */



class PaymentIntegration {

	protected $request ;
	protected $status = false;

	function __Construct($PaymentGateway,$CompanyID){			
		$this->request = new $PaymentGateway($CompanyID);		
    }

	public function getStatus(){		
		return $this->request->status();
	}
	
	public function doValidation($data){
		return $this->request->doValidation($data);
	}
	
	public function doVerify($data){
		return $this->request->doVerify($data);
	}
	
	public function createProfile($data){
		return $this->request->createProfile($data);
	}
	
	public function deleteProfile($data){
		return $this->request->deleteProfile($data);
	}

	public function paymentValidateWithProfile($data){
		$response = array();
		if($data['PaymentGateway']=='Stripe' || $data['PaymentGateway']=='StripeACH'){
			return $this->request->paymentValidateWithProfile($data);
		}
		$response['status'] = 'success';
		return $response;
	}

	public function paymentWithProfile($data){
		$response = $this->paymentValidateWithProfile($data);
		if($response['status']=='failed'){
			return $response;
		}
		$transactionResponse = $this->request->paymentWithProfile($data);

		return $this->getpaymentResponse($transactionResponse,$data);
	}

	public function paymentValidateWithCreditCard($data){
		return $this->request->paymentValidateWithCreditCard($data);
	}

	public function paymentWithCreditCard($data){

		$response = $this->paymentValidateWithCreditCard($data);
		if($response['status']=='failed'){
			return $response;
		}
		log::info('Payment Validate sucessfully');
		$transactionResponse =  $this->request->paymentWithCreditCard($data);

		$transactionResponse['InvoiceID'] = $data['InvoiceID'];
		$transactionResponse['AccountID'] = $data['AccountID'];
		$transactionResponse['isInvoicePay'] = $data['isInvoicePay'];
		if(!$data['isInvoicePay']){
			$transactionResponse['custome_notes'] = $data['custome_notes'];
		}
		$transactionResponse['CreatedBy'] = 'customer';
		if($transactionResponse['status']=='success'){
			Payment::paymentSuccess($transactionResponse);
			return array("status" => "success", "message" => "Invoice paid successfully");
		}else{
			Payment::paymentFail($transactionResponse);
			return array("status" => "failed", "message" => $transactionResponse['transaction_notes']);
		}
	}
	
	public function paymentWithBankDetail($data){
		$response = $this->paymentValidateWithBankDetail($data);
		if($response['status']=='failed'){
			return $response;
		}
		$transactionResponse = $this->request->paymentWithBankDetail($data);
		$transactionResponse['InvoiceID'] = $data['InvoiceID'];
		$transactionResponse['AccountID'] = $data['AccountID'];
		$transactionResponse['isInvoicePay'] = $data['isInvoicePay'];
		if(!$data['isInvoicePay']){
			$transactionResponse['custome_notes'] = $data['custome_notes'];
		}
		$transactionResponse['CreatedBy'] = 'customer';
		if($transactionResponse['status']=='success'){
			Payment::paymentSuccess($transactionResponse);
			return array("status" => "success", "message" => "Invoice paid successfully");
		}else{
			Payment::paymentFail($transactionResponse);
			return array("status" => "failed", "message" => $transactionResponse['transaction_notes']);
		}
	}

	public function paymentValidateWithBankDetail($data){
		return $this->request->paymentValidateWithBankDetail($data);
	}
	
	public function paymentWithUrl($data){
		return $this->request->paymentWithUrl($data);
	}

	public function getpaymentResponse($transactionResponse,$data){

		$transactionResponse['CreatedBy'] = $data['CreatedBy'];
		$transactionResponse['custome_notes']='';
		if(isset($data['isInvoicePay']) && !$data['isInvoicePay']){
			if (isset($transactionResponse['response_code']) && $transactionResponse['response_code'] == 1) {
				$transactionResponse['Transaction'] = $transactionResponse['transaction_id'];


				$transactionResponse['Amount'] = $data['outstanginamount'];
				$transactionResponse['InvoiceID'] = $data['InvoiceIDs'];
				$transactionResponse['AccountID'] = $data['AccountID'];
				$transactionResponse['custome_notes'] = $data['custome_notes'];
				Payment::paymentSuccess($transactionResponse);
				return array("status" => "success", "message" => "All Invoice Paid Successfully");
			} else {
				$transactionResponse['Amount'] = $data['outstanginamount'];
				$transactionResponse['InvoiceID'] = $data['InvoiceIDs'];
				$transactionResponse['AccountID'] = $data['AccountID'];
				$transactionResponse['custome_notes'] = $data['custome_notes'];
				Payment::paymentFail($transactionResponse);

				return array("status" => "failed", "message" => "Transaction Failed :" . $transactionResponse['failed_reason']);

			}
		}

		$unPaidInvoices = DB::connection('sqlsrv2')->select('call prc_getPaymentPendingInvoice (' . $data['CompanyID'] . ',' . $data['AccountID'].',0,0)');
		if (isset($transactionResponse['response_code']) && $transactionResponse['response_code'] == 1) {
			$transactionResponse['Transaction'] = $transactionResponse['transaction_id'];
			foreach ($unPaidInvoices as $Invoiceid) {
				/**  Update Invoice as Paid */
				if (in_array($Invoiceid->InvoiceID, explode(',', $data['InvoiceIDs']))) {
					$transactionResponse['Amount'] = $Invoiceid->RemaingAmount;
					$transactionResponse['InvoiceID'] = $Invoiceid->InvoiceID;
					$transactionResponse['AccountID'] = $data['AccountID'];
					Payment::paymentSuccess($transactionResponse);
				}
			}
			return array("status" => "success", "message" => "All Invoice Paid Successfully");
		} else {
			foreach ($unPaidInvoices as $Invoiceid) {
				if (in_array($Invoiceid->InvoiceID, explode(',', $data['InvoiceIDs']))) {
					$transactionResponse['Amount'] = $Invoiceid->RemaingAmount;
					$transactionResponse['InvoiceID'] = $Invoiceid->InvoiceID;
					$transactionResponse['AccountID'] = $data['AccountID'];
					Payment::paymentFail($transactionResponse);
				}
			}
			return array("status" => "failed", "message" => "Transaction Failed :" . $transactionResponse['failed_reason']);
		}
	}
	public function paymentValidateWithApiCreditCard($data){
		return $this->request->paymentValidateWithApiCreditCard($data);
	}

	public function paymentWithApiCreditCard($data){
		$response = $this->paymentValidateWithApiCreditCard($data);
		if($response['status']=='failed'){
			return $response;
		}
		log::info('Payment Validate sucessfully');
		$transactionResponse =  $this->request->paymentWithApiCreditCard($data);
		return $transactionResponse;
	}
}