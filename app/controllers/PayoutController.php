<?php
class PayoutController extends \BaseController {

    /**
     * Show the form for creating a new resource.
     * GET /payments/create
     *
     * @return Response
     */
    public function create()
    {
        $data = Input::all();
        $AccountResponse = array();
        $CustomerID = $data['AccountID'];
        if($CustomerID > 0) {
            if(empty($data['PaymentGatewayID']))
                $data['PaymentGatewayID'] = PaymentGateway::getPaymentGatewayIDByName("Stripe");;

            if(empty($data['PaymentGatewayID']) || empty($data['CompanyID'])){
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_MSG_PLEASE_SELECT_PAYMENT_GATEWAY')));
            }

            /*$validate = $this->validation($data);

            if($validate['status'] == 'failed') {
                return Response::json($validate);
            }*/

            $CompanyID = $data['CompanyID'];
            $PaymentGatewayID = $data['PaymentGatewayID'];
            $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
            $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass,$CompanyID);

            /*if($data['PayoutType'] == "bank")
                $Response = AccountPayout::bankValidation($data);
            else
                $Response = AccountPayout::cardValidation($data);*/

            $Response = $PaymentIntegration->doValidation($data);

            if($Response['status'] == 'failed'){
                return  Response::json(array("status" => "failed", "message" => $Response['message']));
            } elseif($Response['status'] == 'success'){
                $AccountResponse = $PaymentIntegration->createAccount($data);
            }
        }
        return $AccountResponse;
    }

    public function update_profile()
    {
        $data = Input::all();
        $ProfileResponse = array();
        $CustomerID = $data['AccountID'];
        if($CustomerID > 0) {
            if(empty($data['PaymentGatewayID']) || empty($data['CompanyID'])){
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_MSG_PLEASE_SELECT_PAYMENT_GATEWAY')));
            }
            $CompanyID = $data['CompanyID'];
            $PaymentGatewayID=$data['PaymentGatewayID'];
            $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
            $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass,$CompanyID);
            $Response = $PaymentIntegration->doValidation($data);
            if($Response['status']=='failed'){
                return  Response::json(array("status" => "failed", "message" => $Response['message']));
            }elseif($Response['status']=='success'){
                $ProfileResponse = $PaymentIntegration->updateAccount($data);
            }
            return $ProfileResponse;
        }
    }

    /**
     * @param $data
     * @return array
     */
    public function validation($data){
        $ValidationResponse = array();
        $rules = array(
            'DOB' => 'required|date|date_format:Y-m-d',
            'PayoutType' => 'required|in:card,bank'
        );
        $validator = Validator::make($data, $rules);
        $validator->setAttributeNames(['DOB' => "Date of Birth"]);
        if ($validator->fails()) {
            $errors = "";
            foreach ($validator->messages()->all() as $error){
                $errors .= $error."<br>";
            }
            $ValidationResponse['status'] = 'failed';
            $ValidationResponse['message'] = $errors;
        } else
            $ValidationResponse['status'] = 'success';
        return $ValidationResponse;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id){
        $data = Input::all();
        $CompanyID = Customer::get_companyID();
        $AccountID = Customer::get_accountID();

        $AccountProfile = AccountPayout::find($id);
        if(!empty($AccountProfile)){
            $PaymentGatewayID = $AccountProfile->PaymentGatewayID;
            if(!empty($PaymentGatewayID)){
                $data['AccountPayoutID'] = $id;
                $data['CompanyID'] = $CompanyID;
                $data['AccountID'] = $AccountID;
                $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
                $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass,$CompanyID);
                $ProfileResponse = $PaymentIntegration->deleteAccount($data);

            } else {
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_MSG_PAYMENT_GATEWAY_NOT_SETUP')));
            }

        }else{
            return Response::json(array("status" => "failed", "message" => Lang::get('routes.MESSAGE_RECORD_NOT_FOUND')));
        }

        return $ProfileResponse;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function set_default($id)
    {
        if ($id) {
            $account = AccountPayout::find($id);
            if($account->update(['isDefault' => 1])){
                $PaymentGatewayID = $account->PaymentGatewayID;
                $CompanyID = $account->CompanyID;
                $AccountID = $account->AccountID;
                AccountPayout::where([
                    "CompanyID" => $CompanyID,
                    "PaymentGatewayID" => $PaymentGatewayID,
                    "AccountID" => $AccountID
                ])->where('AccountPayoutID','<>',$id)
                    ->update(['isDefault' => 0]);

                return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYOUT_BUTTON_SET_DEFAULT_MSG_PAYOUT_SUCCESSFULLY_UPDATED')));
            } else {
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYOUT_BUTTON_SET_DEFAULT_MSG_PROBLEM_UPDATING_PAYOUT')));
            }
        }
    }


    /**
     * @param $id
     * @param $action
     * @return mixed
     */
    public function payout_active_deactive($id,$action)
    {
        if ($id && $action) {
            $account = AccountPayout::findOrFail($id);
            if ($action == 'active') {
                $save['Status'] = 1;
            } else {
                $save['Status'] = 0;
            }
            if($account->update($save)){
                return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYOUT_MSG_ACCOUNT_ACTIVE_DEACTIVE_SUCCESSFULLY')));
            } else {
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYOUT_MSG_ACCOUNT_ACTIVE_DEACTIVE_FAILED')));
            }
        }
    }
}