<?php
use app\controllers\api\Codes;

class BillingClassApiController extends ApiController {


	public function getList()
	{
		$data=array();
		$DropdownIDList = BillingClass::select('Name', 'BillingClassID','TaxRateID')->get();
		foreach($DropdownIDList as $val){
			$arr=array();
			$arr["Name"]=$val["Name"];
			$arr["BillingClassID"]=$val["BillingClassID"];
			if($val["TaxRateID"]!=''){
				$arr["TaxRateID"]=explode(",",$val["TaxRateID"]);
			}else{
				$arr["TaxRateID"]=[];
			}

			array_push($data,$arr);
		}
		return Response::json($data,Codes::$Code200[0]);
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
	 * delLowBalanceNotification():
	 * @Param mixed
	 *
	 * @Response
	 * Update Success
	 */
        public function delLowBalanceNotification(){
            $post_vars = json_decode(file_get_contents("php://input"));
            $data=json_decode(json_encode($post_vars),true);
            try{
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
		}else if(!empty($data['AccountDynamicField'])){
                    $AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
                    if(empty($AccountID)){
                        return Response::json(["data"=>"Account Not Found."],Codes::$Code402[0]);
                    }
                    $Account = Account::where(["AccountID" => $AccountID])->first();
                    if(!empty($Account)){
                        $AccountID=$Account->AccountID;
                        $CompanyID=$Account->CompanyId;
                    }

		}else{
                    return Response::json(["ErrorMessage"=>"AccountID OR AccountNo Required"],Codes::$Code402[0]);
		}
                
                if(!empty($data['BalanceThreshold'])) {
                    $Threshold=$data['BalanceThreshold'];
                }else{
                   return Response::json(["ErrorMessage"=>"Threshold Required"],Codes::$Code402[0]);
                }
                AccountBalanceThreshold::where(array('AccountID'=>$AccountID,'BalanceThreshold'=>$Threshold))->delete();
                return Response::json(["status"=>"success"],Codes::$Code200[0]);
            }catch (\Exception $e) {
                    Log::info($e);
                    return Response::json(["ErrorMessage"=>"Something Went Wrong. Exception Generated."],Codes::$Code500[0]);
            }
        }
	/**
	 * setLowBalanceNotification():
	 * @Param mixed
	 *BillingClassID,Status,Email,Period,Interval,StartTime,EmailTemplateID,Day,SendAccountOwner,CompanyID
	 * @Response
	 * Update Success
	 */
	public function setLowBalanceNotification(){
		$post_vars = json_decode(file_get_contents("php://input"));
		$data=json_decode(json_encode($post_vars),true);
                
                foreach($data as $key=>$val){
                    //$AccountID = $val['AccountID'];
                     if(!empty($val['AccountID'])) {
                        $AccountID = $val['AccountID'];
                        $Account = Account::where(["AccountID" => $val['AccountID']])->select('AccountID','CompanyId')->first();

                        if(!empty($Account)){
                            $AccountID=$Account->AccountID;
                            $CompanyID=$Account->CompanyId;
                        }
                    }else if(!empty($val['AccountNo'])){
                        $Account = Account::where(["Number" => $val['AccountNo']])->select('AccountID','CompanyId')->first();
                        if(!empty($Account)){
                                $AccountID=$Account->AccountID;
                                $CompanyID=$Account->CompanyId;
                        }
                    }else if(!empty($val['AccountDynamicField'])){
                        $AccountID=Account::findAccountBySIAccountRef($val['AccountDynamicField']);
                        if(empty($AccountID)){
                            return Response::json(["data"=>"Account Not Found."],Codes::$Code402[0]);
                        }
                        $Account = Account::where(["AccountID" => $AccountID])->first();
                        if(!empty($Account)){
                                $AccountID=$Account->AccountID;
                                $CompanyID=$Account->CompanyId;
                        }
                    }else{
                            return Response::json(["ErrorMessage"=>"AccountID OR AccountNo Required"],Codes::$Code402[0]);
                    }
                    
                    foreach($val['BalanceThreshold'] as $keys=>$value){ 
                        
                        if(!empty($value['Threshold'])) {
                            $Threshold=$value['Threshold'];
                        }else{
                           return Response::json(["ErrorMessage"=>"Threshold Required"],Codes::$Code402[0]);
                        }
                        try{
                            $PostData=array();
                            $PostData['BalanceThreshold'] = $Threshold;
                            $PostData['AccountID'] = $AccountID;
                            $PostData['BalanceThresholdEmail'] = $value['Email'];
                            AccountBalanceThreshold::where(array('AccountID'=>$AccountID,'BalanceThresholdEmail'=>$value['Email'],'BalanceThreshold'=>$Threshold))->delete();
                            AccountBalanceThreshold::insert($PostData);
                        }catch (\Exception $e) {
                                Log::info($e);
                                return Response::json(["ErrorMessage"=>"Something Went Wrong. Exception Generated."],Codes::$Code500[0]);
                        }
                    }
                }
                return Response::json(["status"=>"success"],Codes::$Code200[0]);
                exit();
                //Below are the old code and logic----------------
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
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
			if(empty($AccountID)){
				return Response::json(["data"=>"Account Not Found."],Codes::$Code402[0]);
			}
			$Account = Account::where(["AccountID" => $AccountID])->first();
			if(!empty($Account)){
				$AccountID=$Account->AccountID;
				$CompanyID=$Account->CompanyId;
			}

		}else{
			return Response::json(["ErrorMessage"=>"AccountID OR AccountNo Required"],Codes::$Code402[0]);
		}

		if(!empty($AccountID) && !empty($CompanyID)){
			//Validation
			$rules = array(
				'Status' => 'required',
				'Period' => 'required',
				'Email' => 'required'
			);
			$validator = Validator::make($data, $rules);
			if ($validator->fails()) {
				return json_validator_response($validator);
			}

			try {
				$PostData['LowBalanceReminderStatus'] = isset($data['Status']) ? $data['Status'] : 0;
				$PostData['LowBalanceReminderSettings']['ReminderEmail'] = isset($data['Email']) ? $data['Email'] : '';
				$PostData['LowBalanceReminderSettings']['Time'] = isset($data['Period']) ? $data['Period'] : '';
				$PostData['LowBalanceReminderSettings']['Interval'] = isset($data['Interval']) ? $data['Interval'] : '';
				$PostData['LowBalanceReminderSettings']['StartTime'] = isset($data['StartTime']) ? $data['StartTime'] : '';
				$PostData['LowBalanceReminderSettings']['TemplateID'] = isset($data['EmailTemplateID']) ? $data['EmailTemplateID'] : '';

				if(!empty($data['Period']) && $data['Period']=='MONTHLY'){
					$PostData['LowBalanceReminderSettings']['StartDay']=!empty($data['StartDay'])?$data['StartDay']:'1';
				}

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
					return Response::json([],Codes::$Code200[0]);
				}else{
					return Response::json(["ErrorMessage"=>"Billing Class Not Set For This Account."],Codes::$Code402[0]);
				}

			}catch (\Exception $e) {
				Log::info($e);
				return Response::json(["ErrorMessage"=>"Something Went Wrong. Exception Generated."],Codes::$Code500[0]);
			}

		}else{
			return Response::json(["ErrorMessage"=>"Account or Company Not Found."],Codes::$Code402[0]);
		}

	}

	/**
	 * @Param mixed
	 * AccountID/AccountNo
	 * @Response
	 * Return LowBalance Setting
	 */

	public function getLowBalanceNotification(){
		$post_vars = json_decode(file_get_contents("php://input"));
		$data=json_decode(json_encode($post_vars),true);

		$result=array();
		$AccountID=0;
		if(!empty($data['AccountID'])) {
			$AccountID = $data['AccountID'];
		}else if(!empty($data['AccountNo'])){
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);

		}

		if(empty($AccountID)){

			return Response::json(["ErrorMessage"=>"AccountID or AccountNo is Required"],Codes::$Code402[0]);
		}

		$Account = Account::where(["AccountID" => $AccountID])->select('AccountID','CompanyId')->first();
                if(!empty($Account)){
			//$BillingClass=BillingClass::find($BillingClassID);

			//$result['BalanceThreshold']=AccountBalance::where('AccountID', $AccountID)->pluck('BalanceThreshold');
			//$result['Status']=$BillingClass->LowBalanceReminderStatus;
			//$BillingClass=AccountBilling::join('tblBillingClass','tblAccountBilling.BillingClassID','=','tblBillingClass.BillingClassID')->where(['tblAccountBilling.AccountID'=>$AccountID])->select('tblBillingClass.*')->first();
                        $AccountBalanceThreshold = AccountBalanceThreshold::where(array('AccountID' => $AccountID))->select('BalanceThreshold AS Threshold','BalanceThresholdEmail AS Email')->get(['BalanceThreshold','BalanceThresholdEmail']);
                        unset($AccountBalanceThreshold['created_at']);unset($AccountBalanceThreshold['updated_at']);
                        $result['BalanceThreshold']=json_decode($AccountBalanceThreshold);
                        
			//$result['BillingClass']=json_decode($BillingClass->LowBalanceReminderSettings);

			return Response::json($result,Codes::$Code200[0]);

		}else{
			return Response::json(["ErrorMessage"=>"AccountID Not Found"],Codes::$Code402[0]);
		}


	}


}
