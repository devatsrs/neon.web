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

	/**
	 * setLowBalanceNotification():
	 * @Param mixed
	 *BillingClassID,Status,Email,Period,Interval,StartTime,EmailTemplateID,Day,SendAccountOwner,CompanyID
	 * @Response
	 * Update Success
	 */
	public function setLowBalanceNotification(){
		$data=Input::all();
		$PostData=array();
		$AccountID=0;
		$CompanyID=0;

		if(!empty($data['AccountID'])) {
			$AccountID = $data['AccountID'];
			$Account = Account::where(["AccountID" => $data['AccountID']])->select('AccountID','CompanyId')->first();

			if(!empty($Account)){
				$AccountID=$Account->AccountID;
				$CompanyID=$Account->CompanyId;
			}
		}else if(!empty($data['AccountNo'])){
			$Account = Account::where(["Number" => $data['AccountNo']])->select('AccountID','CompanyId')->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
				$CompanyID=$Account->CompanyId;
			}
		}else{
			return Response::json(["status"=>"failed", "message"=>"AccountID OR AccountNo Required"]);
		}

		if(!empty($AccountID) && !empty($CompanyID)){
			try {
				$PostData['LowBalanceReminderStatus'] = isset($data['Status']) ? $data['Status'] : 0;
				$PostData['LowBalanceReminderSettings']['ReminderEmail'] = isset($data['Email']) ? $data['Email'] : '';
				$PostData['LowBalanceReminderSettings']['Time'] = isset($data['Period']) ? $data['Period'] : '';
				$PostData['LowBalanceReminderSettings']['Interval'] = isset($data['Interval']) ? $data['Interval'] : '';
				$PostData['LowBalanceReminderSettings']['StartTime'] = isset($data['StartTime']) ? $data['StartTime'] : '';
				$PostData['LowBalanceReminderSettings']['TemplateID'] = isset($data['EmailTemplateID']) ? $data['EmailTemplateID'] : '';

				if (!empty($data['SendCopyToAccountOwner'])) {
					$PostData['LowBalanceReminderSettings']['AccountManager'] = $data['SendCopyToAccountOwner'];
				}
				$PostData['LowBalanceReminderSettings']['Day'] = isset($data['Day']) ? $data['Day'] : ["Mon"];

				$BillingClassID=AccountBilling::getBillingClassID($AccountID);
				$BillingClass = BillingClass::find($BillingClassID);
				if (!empty($BillingClass)) {
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
					if(!empty($data['BalanceThreshold'])){
						$AccountBalance = AccountBalance::where('AccountID', $AccountID)->update(['BalanceThreshold'=>$data['BalanceThreshold']]);
					}
					return Response::json(["status"=>"success", "message"=>"Updated Successfully."]);
				}else{
					return Response::json(["status"=>"failed", "message"=>"Billing Class Not Set For This Account."]);
				}

			}catch (\Exception $e) {
				Log::info($e);
				return Response::json(["status"=>"failed", "message"=>"Something Went Wrong. Exception Generated."]);
			}

		}else{
			return Response::json(["status"=>"failed", "message"=>"Account or Company Not Found."]);
		}

	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * @Response
	 * Return LowBalance Setting
	 */

	public function getLowBalanceNotification(){
		$data=Input::all();
		$result=array();
		$AccountID=0;
		if(!empty($data['AccountID'])) {
			$AccountID = $data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		}

		if(empty($AccountID)){

			return Response::json(["status"=>"failed", "data"=>"AccountID or AccountNo is Required"]);
		}

		$BillingClassID=AccountBilling::getBillingClassID($AccountID);
		if($BillingClassID > 0){
			$BillingClass=BillingClass::find($BillingClassID);

			$result['BalanceThreshold']=AccountBalance::where('AccountID', $AccountID)->pluck('BalanceThreshold');
			$result['Status']=$BillingClass->LowBalanceReminderStatus;
			//$BillingClass=AccountBilling::join('tblBillingClass','tblAccountBilling.BillingClassID','=','tblBillingClass.BillingClassID')->where(['tblAccountBilling.AccountID'=>$AccountID])->select('tblBillingClass.*')->first();
			$result['BillingClass']=json_decode(json_encode($BillingClass->LowBalanceReminderSettings),true);

			return Response::json(["status"=>"success", "data"=>$result]);

		}else{
			return Response::json(["status"=>"failed", "data"=>"BillingClass Not Found"]);
		}


	}


}
