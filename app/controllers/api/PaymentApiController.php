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
		if(!empty($data['CustomerID'])) {
			$AccountID = $data['CustomerID'];
		}else if(!empty($data['AccountNo'])){
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');

		}else{
			return Response::json(["status"=>"failed", "data"=>"CustomerID OR AccountNo Required"]);
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
			$CompanyID=$Account->CompanyID;
			$PaymentMethod=$Account->PaymentMethod;
			if (!empty($PaymentMethod) && $PaymentMethod=='Stripe') {
				$PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($PaymentMethod);
				$CustomerProfile = AccountPaymentProfile::getActiveProfile($AccountID, $PaymentGatewayID);

				if (!empty($CustomerProfile)) {
					try {
						$transactionResponse = PaymentGateway::addTransactionStripeAPI($CustomerProfile->AccountPaymentProfileID, $data['Amount'], $Account);
						if (isset($transactionResponse['response_code']) && $transactionResponse['response_code'] == 1) {

							$transactiondata = array();
							$transactiondata['CompanyID'] = $Account->CompanyId;
							$transactiondata['AccountID'] = $Account->AccountID;
							$transactiondata['TransactionID'] = $transactionResponse['transaction_id'];
							$transactiondata['Notes'] = $transactionResponse['transaction_notes'];
							$transactiondata['Amount'] = floatval($data['Amount']);
							$transactiondata['Status'] = "Approved";
							$transactiondata['PaymentDate'] = date('Y-m-d H:i:s');
							$transactiondata['created_at'] = date('Y-m-d H:i:s');
							$transactiondata['updated_at'] = date('Y-m-d H:i:s');
							$transactiondata['CreatedBy'] = 'API';
							$transactiondata['ModifyBy'] = 'API';

							$Payment=Payment::insert($transactiondata);
							$Result=array();
							$Result['PaymentID']=$Payment->PaymentID;
							$Result['TransactionResponse']=$transactionResponse;

							return Response::json(array("status" => "success", "data" => $Result));

						}else{
							$errors[] = 'Transaction Failed :' . $Account->AccountName. ' Reason : ' . $transactionResponse['failed_reason'];
						}
					}catch(Exception $ev){
						Log::error($ev);
						$errors[] = 'Transaction Failed :' . $Account->AccountName . ' Reason : ' . $ev->getMessage();

					}
				}else{
					$errors[]= "Account Profile Not Set.";
				}

			}else{
				$errors[]= 'Payment Method Not set OR Payment Method is not Stripe:' . $Account->AccountName;

			}
		}else{
			return Response::json(["status"=>"failed", "data"=>"Account Not Found."]);
		}

		if(!empty($errors)){
			return Response::json(["status"=>"failed", "Messsage"=>$errors]);
		}


	}


}