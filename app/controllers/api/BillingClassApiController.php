<?php

class BillingClassApiController extends ApiController {


	public function getList()
	{
		$DropdownIDList = BillingClass::select('Name', 'BillingClassID','TaxRateID')->get();
		return Response::json(["status"=>"success", "data"=>$DropdownIDList]);
	}

	public function getTaxRateList()
	{
		$data = Input::all();
		$AccountTaxRate=array();
		$result 		=   BillingClass::where('BillingClassID',$data['BillingClassID'])->pluck('TaxRateID');
		$resultarray 	= 	explode(",",$result);

		foreach($resultarray as $resultdata)	{
			if(TaxRate::where(['TaxRateId'=>$resultdata])->count()){
				$AccountTaxRate[]  = $resultdata;
			}
		}
		return Response::json(["status"=>"success", "data"=>$AccountTaxRate]);
	}

	public function setLowBalanceNotification(){
		$data=Input::all();
		$PostData=array();

		$PostData['LowBalanceReminderStatus']=isset($data['Status'])?$data['Status']:0;
		$PostData['LowBalanceReminderSettings']['ReminderEmail']=isset($data['Email'])?$data['Email']:'';
		$PostData['LowBalanceReminderSettings']['Time']=isset($data['Period'])?$data['Period']:'';
		$PostData['LowBalanceReminderSettings']['Interval']=isset($data['Interval'])?$data['Interval']:'';
		$PostData['LowBalanceReminderSettings']['StartTime']=isset($data['StartTime'])?$data['StartTime']:'';
		$PostData['LowBalanceReminderSettings']['TemplateID']=isset($data['EmailTemplateID'])?$data['EmailTemplateID']:'';

		if(!empty($data['SendAccountOwner'])){
			$PostData['LowBalanceReminderSettings']['AccountManager']=$data['SendAccountOwner'];
		}
		$PostData['LowBalanceReminderSettings']['Day']=isset($data['Day'])?$data['Day']:["Mon"];

		try {
			if (!empty($data['BillingClassID'])) {
				//Update
				$BillingClass = BillingClass::findOrFail($data['BillingClassID']);

				if(!empty($BillingClass)){
					$LowBalanceReminderSettings = json_decode($BillingClass->LowBalanceReminderSettings);
					if (isset($LowBalanceReminderSettings->LastRunTime)) {
						$PostData['LowBalanceReminderSettings']['LastRunTime'] = $LowBalanceReminderSettings->LastRunTime;
					}
					if (isset($LowBalanceReminderSettings->NextRunTime)) {
						$PostData['LowBalanceReminderSettings']['NextRunTime'] = $LowBalanceReminderSettings->NextRunTime;
					}

					$PostData['LowBalanceReminderSettings'] = json_encode($PostData['LowBalanceReminderSettings']);
					$PostData['UpdatedBy'] = 'API';

					$BillingClass->update($PostData);
					return Response::json(["status"=>"success", "Message"=>"Updated Successfully."]);
				}else{
					return Response::json(["status"=>"failed", "Message"=>"Billing Class Not Found."]);
				}

			} else {
				// Create
				$rules = array(
					'CompanyID' => 'required',
				);

				$verifier = App::make('validation.presence');
				$verifier->setConnection('sqlsrv');

				$validator = Validator::make($data, $rules);
				$validator->setPresenceVerifier($verifier);

				if ($validator->fails()) {
					return json_validator_response($validator);
				}

				$PostData['CompanyID'] = $data['CompanyID'];
				$PostData['LowBalanceReminderSettings'] = json_encode($PostData['LowBalanceReminderSettings']);
				$PostData['CreatedBy'] = 'API';

				$BillingClass = BillingClass::create($PostData);
				return Response::json(["status"=>"success", "Message"=>"Inserted Successfully."]);
			}
		}catch (\Exception $e) {
			Log::info($e);
			return Response::json(["status"=>"failed", "Message"=>"Something Went Wrong. Exception Generated."]);
		}

	}


	public function getLowBalanceNotification(){
		$data=Input::all();
		$AccountID=0;
		if(!empty($data['CustomerID'])) {
			$AccountID = $data['CustomerID'];
		}else if(!empty($data['AccountNo'])){
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');

		}else{
			return Response::json(["status"=>"failed", "data"=>"CustomerID or AccountNo is Required"]);
		}

		if(!empty($AccountID) ){
			$BillingClass=DB::table("tblAccountBilling")->join('tblBillingClass','tblAccountBilling.BillingClassID','=','tblBillingClass.BillingClassID')->where(['tblAccountBilling.AccountID'=>$AccountID])->select('tblBillingClass.*')->first();
			return Response::json(["status"=>"success", "data"=>json_decode($BillingClass->LowBalanceReminderSettings)]);
		}else{
			return Response::json(["status"=>"failed", "data"=>"Account Not Found"]);
		}

	}


}
