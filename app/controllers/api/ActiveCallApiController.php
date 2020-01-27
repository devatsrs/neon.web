<?php
use app\controllers\api\Codes;

class ActiveCallApiController extends ApiController {

    /**
     * @Param mixed
     * AccountID/AccountNo
     * ConnectTime,CLI,CLD,CallType,UUID,VendorID,VendorConnectionName,VendorRate,VendorCLIPrefix,VendorCLDPrefix
     * OriginType : MOBILE, FIXED ,  OriginProvider: Sunrise, Swisscom
     * @Response
     * ActiveCallID
     */

    public function startCall(){
        $post_vars = json_decode(file_get_contents("php://input"));
        $data=json_decode(json_encode($post_vars),true);

        $CompanyID=0;
        $AccountID=0;

        if(!empty($data['AccountID'])) {

            $AccountID = $data['AccountID'];
        }else if(!empty($data['AccountNo'])){

            $AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');

        }else if(!empty($data['AccountDynamicField'])){
            $AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);

        }else{
            return Response::json(["ErrorMessage"=>"AccountID or AccountNo or AccountDynamicField Required."],Codes::$Code402[0]);
        }

        $Account=Account::where(["AccountID" => $AccountID]);
        if($Account->count() > 0){
            $Account = $Account->first();
            $CompanyID = $Account->CompanyId;
            $AccountID = $Account->AccountID;
        }

        //Validation
        $rules = array(
            'ConnectTime' => 'required',
            'CLI' => 'required',
            'CLD' => 'required',
            'CallType' => 'required',
            'UUID' => 'required',
            //'VendorID' => 'required'

        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($data['VendorID'])){
            $VendorAccount=Account::where(["AccountID" => $data['VendorID']])->first();
            if(empty($VendorAccount)){
                return Response::json(["ErrorMessage"=>"Vendor Account Not Found"],Codes::$Code402[0]);
            }
        }

        if(!empty($AccountID) && !empty($CompanyID)){
            $IsCallexists=ActiveCall::where('UUID',$data['UUID'])->count();
            if($IsCallexists > 0){
                return Response::json(array("ErrorMessage" => "Call with this UUID already exists."),Codes::$Code410[0]);
            }
            try{
                //check Balance
                //$AccountBalance = AccountBalance::getNewAccountExposure($CompanyID, $AccountID);
                $AccountBalance = AccountBalance::getBalanceAmount($AccountID);
                //log::info('Account Balance '.$AccountBalance);
                if($AccountBalance > 0){
                    if($data['CallType']==0){
                        $data['CallType']='Inbound';
                    }
                    if($data['CallType']==1){
                        $data['CallType']='Outbound';
                    }
                    $ActiveCallData=array();
                    $ActiveCallData['AccountID']=$AccountID;
                    $ActiveCallData['CompanyID']=$CompanyID;
                    $ActiveCallData['created_at']=date('Y-m-d H:i:s');
                    $ActiveCallData['created_by']="API";

                    $ActiveCallData['ConnectTime']=$data['ConnectTime'];
                    $ActiveCallData['CLI']=$data['CLI'];
                    $ActiveCallData['CLD']=$data['CLD'];
                    $ActiveCallData['CallType']=$data['CallType'];
                    $ActiveCallData['UUID']=$data['UUID'];
                    $ActiveCallData['VendorID']=empty($data['VendorID']) ? 0 : $data['VendorID'];
                    $ActiveCallData['VendorConnectionName']=empty($data['VendorConnectionName']) ? '' : $data['VendorConnectionName'];
                    $ActiveCallData['OriginType']=empty($data['OriginType']) ? '' : $data['OriginType'];
                    $ActiveCallData['OriginProvider']=empty($data['OriginProvider']) ? '' : $data['OriginProvider'];
                    $ActiveCallData['VendorRate'] = empty($data['VendorRate']) ? 0 : $data['VendorRate'];
                    $ActiveCallData['VendorCLIPrefix'] = empty($data['VendorCLIPrefix']) ? 'Other' : $data['VendorCLIPrefix'];
                    $ActiveCallData['VendorCLDPrefix'] = empty($data['VendorCLDPrefix']) ? 'Other' : $data['VendorCLDPrefix'];
                    $ActiveCallData['CallRecording'] = 0;
                    $ActiveCallData['Cost'] = 0;
                    $ActiveCallData['Duration'] = 0;
                    $ActiveCallData['billed_duration'] = 0;



                    DB::connection('sqlsrvroutingengine')->beginTransaction();
                    DB::connection('sqlsrv2')->beginTransaction();

                    if ($ActiveCall = ActiveCall::create($ActiveCallData)) {
                        $ActiveCallID = $ActiveCall->ActiveCallID;
                        $Response = ActiveCall::updateActiveCall($ActiveCallID);
                        //log::info(print_r($Response,true));
                        if(isset($Response['Status']) && $Response['Status']=='Success'){
                            //log::info('update call cost');
                            ActiveCall::getActiveCallCost($ActiveCallID);

                            DB::connection('sqlsrvroutingengine')->commit();
                            DB::connection('sqlsrv2')->commit();

                            return Response::json(array(["ActiveCallID"=>$ActiveCall->ActiveCallID]),Codes::$Code200[0]);
                        }else{
                            //log::info('delete call');
                            ActiveCall::where(['ActiveCallID'=>$ActiveCallID])->delete();
                            return Response::json(array("ErrorMessage" => $Response['Message']),Codes::$Code402[0]);
                        }

                    }else{
                        return Response::json(array("ErrorMessage" => "Problem Creating Active Call."),Codes::$Code500[0]);
                    }

                }else{
                    return Response::json(array("ErrorMessage" => "Account has not sufficient balance."),Codes::$Code402[0]);
                }

            }catch(Exception $e){
                DB::connection('sqlsrvroutingengine')->rollback();
                DB::connection('sqlsrv2')->rollback();
                Log::info($e->getTraceAsString());
                $reseponse = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
                return Response::json($reseponse,Codes::$Code500[0]);
            }

        }else{
            return Response::json(["ErrorMessage"=>"Account or Company Not Found"],Codes::$Code402[0]);
        }

    }

    public function endCall(){
        $post_vars = json_decode(file_get_contents("php://input"));
        $data=json_decode(json_encode($post_vars),true);

        $AccountID=0;

        if(!empty($data['AccountID'])) {
            $AccountID = $data['AccountID'];
        }else if(!empty($data['AccountNo'])){
            $AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
        }else if(!empty($data['AccountDynamicField'])){
            $AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);

        }else{
            return Response::json(["ErrorMessage"=>"AccountID Required"],Codes::$Code402[0]);
        }

        //Validation
        $rules = array(
            'UUID' => 'required',
            'DisconnectTime' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($AccountID)) {
            try{
                $ActiveCall = ActiveCall::where(['UUID' => $data['UUID'], 'AccountID' => $AccountID])->first();
                if(!empty($ActiveCall) && count($ActiveCall) > 0) {
                    $ActiveCallID = $ActiveCall->ActiveCallID;
                    $UpdateData = array();
                    $duration = strtotime($data['DisconnectTime']) - strtotime($ActiveCall->ConnectTime);
                    $UpdateData['DisconnectTime'] = $data['DisconnectTime'];
                    $UpdateData['Duration'] = $duration;
                    $UpdateData['updated_by'] = "API";

                    if ($ActiveCall->CallRecording == 1) {
                        //End Call Recording
                        $CallRecordingDuration = strtotime($data['DisconnectTime']) - strtotime($ActiveCall->CallRecordingStartTime);
                        $UpdateData['CallRecordingEndTime'] = $data['DisconnectTime'];
                        $UpdateData['CallRecordingDuration'] = $CallRecordingDuration;
                    }
                    DB::connection('sqlsrvcdr')->beginTransaction();
                    DB::connection('sqlsrvroutingengine')->beginTransaction();

                    if ($Result = $ActiveCall->update($UpdateData)) {
                        /**
                         * update cost
                         */

                        ActiveCall::getActiveCallCost($ActiveCallID);
                        ActiveCall::insertActiveCallCDR($ActiveCallID);
                        ActiveCall::where(['ActiveCallID'=>$ActiveCallID])->delete();


                        DB::connection('sqlsrvcdr')->commit();
                        DB::connection('sqlsrvroutingengine')->commit();

                        return Response::json(['duration' => $duration],Codes::$Code200[0]);
                    }

                } else {
                    return Response::json(["ErrorMessage" => "Record Not Found"],Codes::$Code402[0]);
                }

            }catch(Exception $e){
                DB::connection('sqlsrvcdr')->rollback();
                DB::connection('sqlsrvroutingengine')->rollback();
                Log::info($e->getTraceAsString());
                $reseponse = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
                return Response::json($reseponse,Codes::$Code500[0]);
            }

        }else{
            return Response::json(["ErrorMessage"=>"Account Not Found."],Codes::$Code402[0]);
        }
    }

    public function blockCall(){
        $post_vars = json_decode(file_get_contents("php://input"));
        $data=json_decode(json_encode($post_vars),true);
        Log::useFiles(storage_path() . '/logs/blockcalldata-' . date('Y-m-d') . '.log');
        Log::info("data: ". json_encode($data));

        $AccountID=0;

        if(!empty($data['AccountID'])) {
            $AccountID = $data['AccountID'];
        }else if(!empty($data['AccountNo'])){
            $AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
        }else if(!empty($data['AccountDynamicField'])){
            $AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);

        }else{
            return Response::json(["ErrorMessage"=>"AccountID Required"],Codes::$Code402[0]);
        }

        //Validation
        $rules = array(
            'UUID' => 'required',
            'DisconnectTime' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($AccountID)) {
            try {
                $ActiveCall = ActiveCall::where(['UUID' => $data['UUID'], 'AccountID' => $AccountID])->first();
                if (!empty($ActiveCall) && count($ActiveCall) > 0) {
                    $UpdateData = array();
                    $duration = strtotime($data['DisconnectTime']) - strtotime($ActiveCall->ConnectTime);
                    $UpdateData['DisconnectTime'] = $data['DisconnectTime'];
                    $UpdateData['Duration'] = $duration;
                    $UpdateData['BlockReason'] = empty($data['BlockReason']) ? '' : $data['BlockReason'];
                    $UpdateData['IsBlock'] = 1;
                    $UpdateData['updated_by'] = "API";

                    if ($ActiveCall->CallRecording == 1) {
                        //End Call Recording
                        $CallRecordingDuration = strtotime($data['DisconnectTime']) - strtotime($ActiveCall->CallRecordingStartTime);
                        $UpdateData['CallRecordingEndTime'] = $data['DisconnectTime'];
                        $UpdateData['CallRecordingDuration'] = $CallRecordingDuration;
                    }

                    DB::connection('sqlsrvcdr')->beginTransaction();
                    DB::connection('sqlsrvroutingengine')->beginTransaction();

                    if ($Result = $ActiveCall->update($UpdateData)) {
                        $ActiveCallID = $ActiveCall->ActiveCallID;

                        ActiveCall::getActiveCallCost($ActiveCallID);
                        ActiveCall::insertActiveCallCDR($ActiveCallID);
                        ActiveCall::where(['ActiveCallID'=>$ActiveCallID])->delete();

                        DB::connection('sqlsrvcdr')->commit();
                        DB::connection('sqlsrvroutingengine')->commit();

                        return Response::json(['duration' => $duration], Codes::$Code200[0]);
                    }

                } else {
                    return Response::json(["ErrorMessage" => "Record Not Found"], Codes::$Code402[0]);
                }
            }catch(Exception $e){
                DB::connection('sqlsrvcdr')->rollback();
                DB::connection('sqlsrvroutingengine')->rollback();

                Log::info($e->getTraceAsString());
                $reseponse = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
                return Response::json($reseponse,Codes::$Code500[0]);
            }
        }else{
            return Response::json(["ErrorMessage" => "Account Not Found"],Codes::$Code402[0]);
        }
    }

    public function startRecording(){
        $post_vars = json_decode(file_get_contents("php://input"));
        $data=json_decode(json_encode($post_vars),true);

        $AccountID=0;

        if(!empty($data['AccountID'])) {
            $AccountID = $data['AccountID'];
        }else if(!empty($data['AccountNo'])){
            $AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
        }else if(!empty($data['AccountDynamicField'])){
            $AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);

        }else{
            return Response::json(["ErrorMessage"=>"AccountID or AccountNo Required"],Codes::$Code402[0]);
        }

        //Validation
        $rules = array(
            'UUID' => 'required',
            'CallRecordingStartTime' => 'required'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($AccountID)) {
            $ActiveCallobj = ActiveCall::where(['UUID' => $data['UUID'], 'AccountID' => $AccountID]);
            $Count = $ActiveCallobj->count();
            if ($Count > 0) {
                $UpdateData = array();
                $ActiveCall = $ActiveCallobj->first();
                if ($ActiveCall->CallRecording == 1) {
                    return Response::json(["ErrorMessage" => "Recording Already Started"],Codes::$Code402[0]);
                }

                $UpdateData['CallRecordingStartTime'] = $data['CallRecordingStartTime'];
                $UpdateData['CallRecording'] = 1;
                $UpdateData['updated_by'] = "API";

                if ($Result = $ActiveCall->update($UpdateData)) {
                    return Response::json([],Codes::$Code200[0]);
                } else {
                    return Response::json(["ErrorMessage" => "Problem Updating Recording."],Codes::$Code500[0]);
                }
            } else {
                return Response::json(["ErrorMessage" => "Record Not Found"],Codes::$Code402[0]);
            }
        }else{
            return Response::json(["ErrorMessage" => "Account Not Found"],Codes::$Code402[0]);
        }

    }

    public function getBlockCalls(){
        if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
        $AccountID = 0;
        try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
        }
        
        if(!empty($data['AccountID'])) {
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
			}else {
				return Response::json(["ErrorMessage"=>"AccountID must be a mumber."],Codes::$Code400[0]);
			}
		}else if(!empty($data['AccountNo'])){
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo is required"],Codes::$Code400[0]);
			}
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo or AccountDynamicField is required."],Codes::$Code400[0]);
        }
        
        $rules = array(
            'StartDate' => 'required|date|date_format:Y-m-d',
            'EndDate' => 'required|date|date_format:Y-m-d',
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv');

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return Response::json([
                "ErrorMessage" => $validator->messages()->first()
            ],Codes::$Code400[0]);
        }

        if (strtotime($data['EndDate']) < strtotime($data['StartDate'])) {
            return  Response::json(["ErrorMessage" => "End date should be greater than or equal to start date."], Codes::$Code400[0]);
        }

        $StartDate 	 = 		!empty($data['StartDate'])?$data['StartDate']:'0000-00-00';
        $EndDate 	 = 		!empty($data['EndDate'])?$data['EndDate']:'0000-00-00';
       
        try {
            $query = "CALL prc_getBlockCall(" . $AccountID . ",'" . $StartDate . "','" . $EndDate . "')";
            //echo $query;die;
            $Result = DB::connection('sqlsrvroutingengine')->select($query);
            $Response = json_decode(json_encode($Result), true);
            return Response::json($Response,Codes::$Code200[0]);
        }catch(Exception $e){
            Log::info($e);
            $reseponse = array("ErrorMessage" => "Something Went Wrong.");
            return Response::json($reseponse,Codes::$Code500[0]);
        }

    }

    public function getCDR(){
        if(parent::checkJson() === false) {
			return Response::json(["ErrorMessage"=>"Content type must be: application/json"]);
		}
        $AccountID = 0;
        try {
			$post_vars = json_decode(file_get_contents("php://input"));
			$data=json_decode(json_encode($post_vars),true);
			$countValues = count($data);
			if ($countValues == 0) {
				return Response::json(["ErrorMessage"=>"Invalid Request"]);
			}	
		}catch(Exception $ex) {
			Log::info('Exception in updateAccount API.Invalid JSON' . $ex->getTraceAsString());
			return Response::json(["ErrorMessage"=>"Invalid Request"]);
        }

        if(!empty($data['AccountID'])) {
			if(is_numeric(trim($data['AccountID']))) {
				$AccountID = $data['AccountID'];
			}else {
				return Response::json(["ErrorMessage"=>"AccountID must be a mumber."],Codes::$Code400[0]);
			}
		}else if(!empty($data['AccountNo'])){
			$accountNo = trim($data['AccountNo']);
			if(empty($accountNo)){
				return Response::json(["ErrorMessage"=>"AccountNo is required"],Codes::$Code400[0]);
			}
			$AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
		}else if(!empty($data['AccountDynamicField'])){
			$AccountID = Account::findAccountBySIAccountRef($data['AccountDynamicField']);
		}else{
			return Response::json(["ErrorMessage"=>"AccountID or AccountNo or AccountDynamicField is required."],Codes::$Code400[0]);
        }

        $rules = array(
            'StartDate' => 'required|date|date_format:Y-m-d',
            'EndDate' => 'required|date|date_format:Y-m-d',
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv');

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return Response::json([
                "ErrorMessage" => $validator->messages()->first()
            ],Codes::$Code400[0]);
        }

        if (strtotime($data['EndDate']) < strtotime($data['StartDate'])) {
            return  Response::json(["ErrorMessage" => "End date should be greater then or equal to start date."], Codes::$Code400[0]);
        }

        $StartDate 	 = 		!empty($data['StartDate'])?$data['StartDate']:'0000-00-00';
        $EndDate 	 = 		!empty($data['EndDate'])?$data['EndDate']:'0000-00-00';
        
        try {
            $query = "CALL prc_getCallData(" . $AccountID . ",'" . $StartDate . "','" . $EndDate . "')";
            //echo $query;die;
            $Result = DB::connection('sqlsrvroutingengine')->select($query);
            $Response = json_decode(json_encode($Result), true);
            return Response::json($Response,Codes::$Code200[0]);
        }catch(Exception $e){
            Log::info($e);
            $reseponse = array("ErrorMessage" => "Something Went Wrong.");
            return Response::json($reseponse,Codes::$Code500[0]);
        }
    }


    /**
     * @Param mixed
     * AccountID/AccountNo
     * ConnectTime,CLI,CLD,CallType,UUID,VendorID,VendorConnectionName,VendorRate,VendorCLIPrefix,VendorCLDPrefix
     * OriginType : MOBILE, FIXED ,  OriginProvider: Sunrise, Swisscom
     * @Response
     * ActiveCallID
     */
    // import completed call
    public function ImportCDR(){
        $post_vars = json_decode(file_get_contents("php://input"));
        $data=json_decode(json_encode($post_vars),true);

        $CompanyID=0;
        $AccountID=0;

        if(!empty($data['AccountID'])) {
            $AccountID = $data['AccountID'];
        }else if(!empty($data['AccountNo'])){
            $AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
        }else if(!empty($data['AccountDynamicField'])){
            $AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);
        }else{
            return Response::json(["ErrorMessage"=>"AccountID or AccountNo or AccountDynamicField Required."],Codes::$Code402[0]);
        }

        $Account=Account::where(["AccountID" => $AccountID]);
        if($Account->count() > 0){
            $Account = $Account->first();
            $CompanyID = $Account->CompanyId;
            $AccountID = $Account->AccountID;
        }

        //Validation
        $rules = array(
            'ConnectTime' => 'required',
            'DisconnectTime' => 'required',
            //'Duration' => 'required',
            'CLI' => 'required',
            'CLD' => 'required',
            'CallType' => 'required',
            'UUID' => 'required',
            //'VendorID' => 'required'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($data['VendorID'])){
            $VendorAccount=Account::where(["AccountID" => $data['VendorID']])->first();
            if(empty($VendorAccount)){
                return Response::json(["ErrorMessage"=>"Vendor Account Not Found"],Codes::$Code402[0]);
            }
        }

        if(!empty($AccountID) && !empty($CompanyID)){
            $IsCallexists=UsageDetail::where('UUID',$data['UUID'])->count();
            if($IsCallexists > 0){
                return Response::json(array("ErrorMessage" => "Call with this UUID already exists."),Codes::$Code410[0]);
            }
            try{
                if($data['CallType']==0){
                    $data['CallType']='Inbound';
                }
                if($data['CallType']==1){
                    $data['CallType']='Outbound';
                }
                $duration = isset($data['Duration']) ? $data['Duration'] : strtotime($data['DisconnectTime']) - strtotime($data['ConnectTime']);
                $ActiveCallData=array();
                $ActiveCallData['AccountID']=$AccountID;
                $ActiveCallData['CompanyID']=$CompanyID;
                $ActiveCallData['created_at']=date('Y-m-d H:i:s');
                $ActiveCallData['created_by']="API";

                $ActiveCallData['ConnectTime']=$data['ConnectTime'];
                $ActiveCallData['DisconnectTime']=$data['DisconnectTime'];
                $ActiveCallData['Duration']=$duration;
                $ActiveCallData['CLI']=$data['CLI'];
                $ActiveCallData['CLD']=$data['CLD'];
                $ActiveCallData['CallType']=$data['CallType'];
                $ActiveCallData['UUID']=$data['UUID'];
                $ActiveCallData['VendorID']=empty($data['VendorID']) ? 0 : $data['VendorID'];
                $ActiveCallData['VendorConnectionName']=empty($data['VendorConnectionName']) ? '' : $data['VendorConnectionName'];
                $ActiveCallData['OriginType']=empty($data['OriginType']) ? '' : $data['OriginType'];
                $ActiveCallData['OriginProvider']=empty($data['OriginProvider']) ? '' : $data['OriginProvider'];
                $ActiveCallData['VendorRate'] = empty($data['VendorRate']) ? 0 : $data['VendorRate'];
                $ActiveCallData['VendorCLIPrefix'] = empty($data['VendorCLIPrefix']) ? 'Other' : $data['VendorCLIPrefix'];
                $ActiveCallData['VendorCLDPrefix'] = empty($data['VendorCLDPrefix']) ? 'Other' : $data['VendorCLDPrefix'];

                // if call recording is on and call recording start time is available
                if (!empty($data['CallRecording']) && $data['CallRecording'] == 1 && !empty($data['CallRecordingStartTime'])) {
                    $CallRecordingDuration = strtotime($data['DisconnectTime']) - strtotime($data['CallRecordingStartTime']);
                    $ActiveCallData['CallRecordingStartTime'] = $data['CallRecordingStartTime'];
                    $ActiveCallData['CallRecordingEndTime'] = $data['DisconnectTime'];
                    $ActiveCallData['CallRecordingDuration'] = $CallRecordingDuration;
                    $ActiveCallData['CallRecording'] = 1;
                } else {
                    $ActiveCallData['CallRecording'] = 0;
                }

                DB::connection('sqlsrvroutingengine')->beginTransaction();
                DB::connection('sqlsrv2')->beginTransaction();

                if ($ActiveCall = ActiveCall::create($ActiveCallData)) {
                    $ActiveCallID = $ActiveCall->ActiveCallID;
                    $Response = ActiveCall::updateActiveCall($ActiveCallID);
                    //log::info(print_r($Response,true));
                    if(isset($Response['Status']) && $Response['Status']=='Success'){
                        //log::info('update call cost');

                        ActiveCall::getActiveCallCost($ActiveCallID);
                        ActiveCall::insertActiveCallCDR($ActiveCallID);
                        ActiveCall::where(['ActiveCallID'=>$ActiveCallID])->delete();

                        DB::connection('sqlsrvroutingengine')->commit();
                        DB::connection('sqlsrv2')->commit();

                        return Response::json([],Codes::$Code200[0]);
                    }else{
                        //log::info('delete call');
                        ActiveCall::where(['ActiveCallID'=>$ActiveCallID])->delete();
                        return Response::json(array("ErrorMessage" => $Response['Message']),Codes::$Code402[0]);
                    }

                }else{
                    return Response::json(array("ErrorMessage" => "Problem Inserting Call."),Codes::$Code500[0]);
                }

            }catch(Exception $e){
                DB::connection('sqlsrvroutingengine')->rollback();
                DB::connection('sqlsrv2')->rollback();
                Log::info($e->getTraceAsString());
                $reseponse = array("ErrorMessage" => "Something Went Wrong. \n" . $e->getMessage());
                return Response::json($reseponse,Codes::$Code500[0]);
            }

        }else{
            return Response::json(["ErrorMessage"=>"Account or Company Not Found"],Codes::$Code402[0]);
        }

    }

}
