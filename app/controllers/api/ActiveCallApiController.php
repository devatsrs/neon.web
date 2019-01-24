<?php

class ActiveCallApiController extends ApiController {

    /**
     * @Param mixed
     * AccountID/AccountNo
     * ConnectTime,CLI,CLD,CallType,UUID,VendorID,TrunkID,CLIPrefix,CLDPrefix,Rate,BuyingPrice
     *
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
            return Response::json(["status"=>"404", "message"=>"AccountID or AccountNo or AccountDynamicField Required."]);
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
            'VendorID' => 'required'

        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($AccountID) && !empty($CompanyID)){
            $IsCallexists=ActiveCall::where('UUID',$data['UUID'])->count();
            if($IsCallexists > 0){
                return Response::json(array("status" => "404", "message" => "Call with this UUID already exists."));
            }
            try{
                //check Balance
                //$AccountBalance = AccountBalance::getNewAccountExposure($CompanyID, $AccountID);
                $AccountBalance = AccountBalance::getBalanceAmount($AccountID);
                if($AccountBalance > 0){
                    if($data['CallType']==0){
                        $data['CallType']='Inbound';
                    }
                    if($data['CallType']==1){
                        $data['CallType']='Outbound';
                    }
                    $ActiveCallData=array();
                    $ActiveCallData['AccountID']=$AccountID;
                    $ActiveCallData['CompanyId']=$CompanyID;
                    $ActiveCallData['created_at']=date('Y-m-d H:i:s');
                    $ActiveCallData['created_by']="API";

                    $ActiveCallData['ConnectTime']=$data['ConnectTime'];
                    $ActiveCallData['CLI']=$data['CLI'];
                    $ActiveCallData['CLD']=$data['CLD'];
                    $ActiveCallData['CallType']=$data['CallType'];
                    $ActiveCallData['UUID']=$data['UUID'];
                    $ActiveCallData['VendorID']=$data['VendorID'];

                    /**
                     * TODO: Cost Manage
                     */

                    if ($ActiveCall = ActiveCall::create($ActiveCallData)) {
                        return Response::json(array("status" => "200","message"=>"Active Call Created Successfully.","data" => ["ActiveCallID"=>$ActiveCall->ActiveCallID]));
                    }else{
                        return Response::json(array("status" => "500", "message" => "Problem Creating Active Call."));
                    }

                }else{
                    return Response::json(array("status" => "404", "message" => "Account has not sufficient balance."));
                }

            }catch(Exception $e){
                Log::info($e->getTraceAsString());
                $reseponse = array("status" => "500", "message" => "Something Went Wrong. \n" . $e->getMessage());
                return $reseponse;
            }

        }else{
            return Response::json(["status"=>"404", "message"=>"Account or Company Not Found"]);
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
            return Response::json(["status"=>"404", "message"=>"AccountID Required"]);
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
            $ActiveCallobj = ActiveCall::where(['UUID' => $data['UUID'], 'AccountID' => $AccountID]);
            $Count = $ActiveCallobj->count();
            if ($Count > 0) {
                $UpdateData = array();
                $ActiveCall = $ActiveCallobj->first();
                $duration = strtotime($data['DisconnectTime']) - strtotime($ActiveCall->ConnectTime);
                $UpdateData['DisconnectTime'] = $data['DisconnectTime'];
                $UpdateData['Duration'] = $duration;
                $UpdateData['updated_by'] = "API";

                if ($ActiveCall->CallRecording == 1) {
                    //End Call Recording
                    $UpdateData['CallRecordingEndTime'] = $data['DisconnectTime'];
                    $UpdateData['CallRecording'] = 0;
                }

                if ($Result = $ActiveCall->update($UpdateData)) {
                    return Response::json(["status" => "200", "message" => "Record Updated Successfully", "data" => ['duration' => $duration]]);
                }

            } else {
                return Response::json(["status" => "404", "message" => "Record Not Found", "data" => []]);
            }
        }else{
            return Response::json(["status"=>"404", "message"=>"Account Not Found."]);
        }
    }

    public function blockCall(){
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
            return Response::json(["status"=>"404", "message"=>"AccountID Required"]);
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
            $ActiveCallobj = ActiveCall::where(['UUID' => $data['UUID'], 'AccountID' => $AccountID]);
            $Count = $ActiveCallobj->count();
            if ($Count > 0) {
                $UpdateData = array();
                $ActiveCall = $ActiveCallobj->first();
                $duration = strtotime($data['DisconnectTime']) - strtotime($ActiveCall->ConnectTime);
                $UpdateData['DisconnectTime'] = $data['DisconnectTime'];
                $UpdateData['Duration'] = $duration;
                $UpdateData['BlockReason'] = empty($data['BlockReason']) ? '' : $data['BlockReason'];
                $UpdateData['IsBlock'] = 1;
                $UpdateData['updated_by'] = "API";

                if ($ActiveCall->CallRecording == 1) {
                    //End Call Recording
                    $UpdateData['CallRecordingEndTime'] = $data['DisconnectTime'];
                    $UpdateData['CallRecording'] = 0;
                }

                if ($Result = $ActiveCall->update($UpdateData)) {
                    return Response::json(["status" => "200", "message" => "Call Blocked Successfully", "data" => ['duration' => $duration]]);
                }

            } else {
                return Response::json(["status" => "404", "message" => "Record Not Found"]);
            }
        }else{
            return Response::json(["status" => "404", "message" => "Account Not Found"]);
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
            return Response::json(["status"=>"404", "message"=>"AccountID or AccountNo Required"]);
        }

        //Validation
        $rules = array(
            'UUID' => 'required',
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
                    return Response::json(["status" => "404", "message" => "Recording Already Started"]);
                }

                $UpdateData['CallRecordingStartTime'] = date('Y-m-d H:i:s');
                $UpdateData['CallRecording'] = 1;
                $UpdateData['updated_by'] = "API";

                if ($Result = $ActiveCall->update($UpdateData)) {
                    return Response::json(["status" => "200", "message" => "Recording Start Successfully."]);
                } else {
                    return Response::json(["status" => "500", "message" => "Problem Updating Recording.", "data" => []]);
                }
            } else {
                return Response::json(["status" => "404", "message" => "Record Not Found", "data" => []]);
            }
        }else{
            return Response::json(["status" => "404", "message" => "Account Not Found"]);
        }

    }

    public function getBlockCalls(){
        $post_vars = json_decode(file_get_contents("php://input"));
        $data=json_decode(json_encode($post_vars),true);

        $StartDate 	 = 		!empty($data['StartDate'])?$data['StartDate']:'0000-00-00';
        $EndDate 	 = 		!empty($data['EndDate'])?$data['EndDate']:'0000-00-00';
        $AccountID=0;

        if(!empty($data['AccountID'])) {
            $AccountID = $data['AccountID'];
        }else if(!empty($data['AccountNo'])){
            $AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
        }else if(!empty($data['AccountDynamicField'])){
            $AccountID=Account::findAccountBySIAccountRef($data['AccountDynamicField']);

        }

        if(empty($AccountID)){
            $AccountID=0;
        }

        try {
            $query = "CALL prc_getBlockCall(" . $AccountID . ",'" . $StartDate . "','" . $EndDate . "')";
            //echo $query;die;
            $Result = DB::connection('speakIntelligentRoutingEngine')->select($query);
            $Response = json_decode(json_encode($Result), true);
            return Response::json(["status" => "200", "data" => $Response]);
        }catch(Exception $e){
            Log::info($e);
            $reseponse = array("status" => "500", "message" => "Something Went Wrong.");
            return $reseponse;
        }

    }

}
