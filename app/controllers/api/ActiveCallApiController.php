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
        $data=Input::all();
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
                return Response::json(["status"=>"failed", "message"=>"Account Not Found."]);
            }
        }else{
            return Response::json(["status"=>"failed", "message"=>"AccountID or AccountNo Required."]);
        }

        //Validation
        $rules = array(
            'ConnectTime' => 'required',
            'CLI' => 'required',
            'CLD' => 'required',
            'CallType' => 'required',
            'UUID' => 'required',
            'TrunkID' => 'required',
            'VendorID' => 'required',
            'CLIPrefix' => 'required',
            'CLDPrefix' => 'required'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($AccountID) && !empty($CompanyID)){
            try{
                //check Balance
                $AccountBalance = AccountBalance::getNewAccountExposure($CompanyID, $AccountID);
                if($AccountBalance > 0){
                    $ActiveCallData=array();
                    $ActiveCallData['AccountID']=$AccountID;
                    $ActiveCallData['created_at']=date('Y-m-d H:i:s');
                    $ActiveCallData['created_by']="API";

                    $ActiveCallData['ConnectTime']=$data['ConnectTime'];
                    $ActiveCallData['CLI']=$data['CLI'];
                    $ActiveCallData['CLD']=$data['CLD'];
                    $ActiveCallData['CallType']=$data['CallType'];
                    $ActiveCallData['UUID']=$data['UUID'];
                    $ActiveCallData['TrunkID']=$data['TrunkID'];
                    $ActiveCallData['VendorID']=$data['VendorID'];
                    $ActiveCallData['CLIPrefix']=$data['CLIPrefix'];
                    $ActiveCallData['CLDPrefix']=$data['CLDPrefix'];

                    /**
                     * TODO: Cost Manage
                     */

                    if ($ActiveCall = ActiveCall::create($ActiveCallData)) {
                        return Response::json(array("status" => "success","message"=>"Active Call Created Successfully.","data" => ["ActiveCallID"=>$ActiveCall->ActiveCallID]));
                    }else{
                        return Response::json(array("status" => "failed", "message" => "Problem Creating Active Call."));
                    }

                }else{
                    return Response::json(array("status" => "failed", "message" => "Account has not sufficient balance."));
                }

            }catch(Exception $e){
                Log::info($e->getMessage());
                $reseponse = array("status" => "failed", "message" => "Something Went Wrong. \n" . $e->getMessage());
                return $reseponse;
            }

        }else{
            return Response::json(["status"=>"failed", "message"=>"Account or Company Not Found"]);
        }

    }

    public function endCall(){
        $data=Input::all();
        $CompanyID=0;
        $AccountID=0;

        if(!empty($data['AccountID'])) {
            $CompanyID = Account::where(["AccountID" => $data['AccountID']])->pluck('CompanyId');
            $AccountID = $data['AccountID'];
        }else if(!empty($data['AccountNo'])){
            $AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
        }else{
            return Response::json(["status"=>"failed", "message"=>"AccountID Required"]);
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

        $ActiveCallobj=ActiveCall::where(['UUID'=>$data['UUID'],'AccountID'=>$AccountID]);
        $Count=$ActiveCallobj->count();
        if($Count > 0){
            $UpdateData=array();
            $ActiveCall=$ActiveCallobj->first();
            $duration = strtotime($data['DisconnectTime']) - strtotime($ActiveCall->ConnectTime);
            $UpdateData['DisconnectTime']=$data['DisconnectTime'];
            $UpdateData['Duration']=$duration;
            $UpdateData['updated_by']="API";

            if($Result=$ActiveCall->update($UpdateData)){
                return Response::json(["status"=>"success", "message"=>"Record Updated Successfully","data"=>['duration'=>$duration]]);
            }

        }else{
            return Response::json(["status"=>"failed", "message"=>"Record Not Found","data"=>[]]);
        }
    }

    public function startRecording(){
        $data=Input::all();
        $AccountID=0;

        if(!empty($data['AccountID'])) {
            $AccountID = $data['AccountID'];
        }else if(!empty($data['AccountNo'])){
            $AccountID = Account::where(["Number" => $data['AccountNo']])->pluck('AccountID');
        }else{
            return Response::json(["status"=>"failed", "message"=>"AccountID or AccountNo Required"]);
        }

        //Validation
        $rules = array(
            'UUID' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $ActiveCallobj=ActiveCall::where(['UUID'=>$data['UUID'],'AccountID'=>$AccountID]);
        $Count=$ActiveCallobj->count();
        if($Count > 0){
            $UpdateData=array();
            $ActiveCall=$ActiveCallobj->first();
            if($ActiveCall->CallRecording == 1){
                return Response::json(["status"=>"failed", "message"=>"Recording Already Started"]);
            }

            $UpdateData['CallRecordingStartTime']=date('Y-m-d H:i:s');
            $UpdateData['CallRecording']=1;
            $UpdateData['updated_by']="API";

            if($Result=$ActiveCall->update($UpdateData)){
                return Response::json(["status"=>"success", "message"=>"Recording Start Successfully."]);
            }else{
                return Response::json(["status"=>"failed", "message"=>"Problem Updating Recording.","data"=>[]]);
            }
        }else{
            return Response::json(["status"=>"failed", "message"=>"Record Not Found","data"=>[]]);
        }

    }

}
