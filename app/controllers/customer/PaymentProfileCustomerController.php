<?php
class PaymentProfileCustomerController extends \BaseController {


    public function ajax_datagrid($AccountID) {
        $data = Input::all();

        $PaymentGatewayName = '';
        $PaymentGatewayID='';

        $account = Account::find($AccountID);
        $CompanyID = $account->CompanyId;
        if(!empty($account->PaymentMethod)){
            $PaymentGatewayName = $account->PaymentMethod;
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($PaymentGatewayName);
        }

        $carddetail = AccountPaymentProfile::select("tblAccountPaymentProfile.Title","tblAccountPaymentProfile.Status","tblAccountPaymentProfile.isDefault",DB::raw("'".$PaymentGatewayName."' as gateway"),"created_at","AccountPaymentProfileID","tblAccountPaymentProfile.Options");
        $carddetail->where(["tblAccountPaymentProfile.CompanyID"=>$CompanyID])
            ->where(["tblAccountPaymentProfile.AccountID"=>$AccountID])
            ->where(["tblAccountPaymentProfile.PaymentGatewayID"=>$PaymentGatewayID])
            ->where(["tblAccountPaymentProfile.Status"=>AccountPaymentProfile::$StatusActive]);

        return Datatables::of($carddetail)->make();
    }
	/**
	 * Display a listing of the resource.
	 * GET /payments
	 *
	 * @return Response
	 */
	public function index()
	{
        Payment::multiLang_init();
        $currentmonth = date("n");
        $currentyear = date("Y");
        $CustomerID = Customer::get_accountID();
        $account = Account::find($CustomerID);
        return View::make('customer.paymentprofile.index',compact('currentmonth','currentyear','account'));
	}

    public function paynow($AccountID)
    {


        $PaymentGatewayID = '';
        $Account = Account::find($AccountID);
        $PaymentMethod = '';
        if(!empty($Account->PaymentMethod)){
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($Account->PaymentMethod);
            $PaymentMethod = $Account->PaymentMethod;
        }
        return View::make('customer.paymentprofile.paynow',compact('AccountID','PaymentGatewayID','PaymentMethod'));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /payments/create
	 *
	 * @return Response
	 */
    public function create()
    {
        $data = Input::all();
        $ProfileResponse = array();
        //$CompanyID = Customer::get_companyID();
        //$CustomerID = Customer::get_accountID();
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
                $ProfileResponse = $PaymentIntegration->createProfile($data);
            }
            return $ProfileResponse;
        }
    }

    public function update(){

        $data = Input::all();
		$CompanyID = Customer::get_companyID();
		$isAuthorizedNet  = 	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$AuthorizeSlug,$CompanyID);
		if(!$isAuthorizedNet){
			return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_MSG_PAYMENT_METHOD_NOT_INTEGRATED')));
		}
		
        $AuthorizeNet = new AuthorizeNet();
        $ProfileID = "";
        $PaymentProfile = AccountPaymentProfile::find($data['cardID']);
        $rules = array(
            'CardNumber' =>'required|digits_between:14,19',
            'ExpirationMonth'=>'required',
            'ExpirationYear'=>'required'
            //'Title' => 'required|unique:tblAutorizeCardDetail,NULL,CreditCardID,CompanyID,'.$CompanyID
        );
        $validator = Validator::make($data,$rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if(date("Y")==$data['ExpirationYear'] && date("m")>$data['ExpirationMonth']){
            return Response::json(array("status" => "failed", "message" => "Month must be after ".date("F")));
        }
        if(!empty($PaymentProfile)){
            $options = json_decode($PaymentProfile->Options);
            $ProfileID = $options->ProfileID;
            $PaymentProfileID = $options->PaymentProfileID;
        }else{
            return Response::json(array("status" => "failed", "message" => Lang::get('routes.MESSAGE_RECORD_NOT_FOUND')));
        }
        $title = $data['Title'];
        $result = $AuthorizeNet->UpdatePaymentProfile($ProfileID,$PaymentProfileID,$data);
        if($result["status"]=="success"){
            $CardDetail = array('Title'=>$title, 'created_by'=>Customer::get_accountName());
            if ($PaymentProfile->update($CardDetail)) {
                return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_MSG_PAYMENT_METHOD_PROFILE_SUCCESSFULLY_UPDATED')));
            } else {
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_MSG_PROBLEM_UPDATING_PAYMENT_METHOD_PROFILE')));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => (array)$result["message"]));
        }
    }

    public function delete($id){
        $data = Input::all();
        $ProfileID = "";
        $isDefault = 0;
        $ProfileResponse = array();
        $CompanyID = Customer::get_companyID();
        $AccountID = Customer::get_accountID();

        $PaymentProfile = AccountPaymentProfile::find($id);
        if(!empty($PaymentProfile)){
            $PaymentGatewayID = $PaymentProfile->PaymentGatewayID;
            if(!empty($PaymentGatewayID)){
                $data['AccountPaymentProfileID'] = $id;
                $data['CompanyID'] = $CompanyID;
                $data['AccountID'] = $AccountID;
                $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
                $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass,$CompanyID);
                $ProfileResponse = $PaymentIntegration->deleteProfile($data);

            }else{
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MODAL_ADD_NEW_CARD_MSG_PAYMENT_GATEWAY_NOT_SETUP')));
            }

        }else{
            return Response::json(array("status" => "failed", "message" => Lang::get('routes.MESSAGE_RECORD_NOT_FOUND')));
        }

        return $ProfileResponse;

    }

    public function set_default($id)
    {
        if ($id) {
            $data = Input::all();
            $card = AccountPaymentProfile::find($id);
            if($card->update(['isDefault'=>1])){
                /*
                if(!empty($data['AccountID'])){
                    $AccountID =$data['AccountID'];
                    $CompanyID = User::get_companyID();
                }else{
                    $CompanyID = Customer::get_companyID();
                    $AccountID = Customer::get_accountID();
                }*/
                $PaymentGatewayID = $card->PaymentGatewayID;
                $CompanyID = $card->CompanyID;
                $AccountID = $card->AccountID;
                AccountPaymentProfile::where(["CompanyID"=>$CompanyID,"PaymentGatewayID"=>$PaymentGatewayID])->where(["AccountID"=>$AccountID])->where('AccountPaymentProfileID','<>',$id)->update(['isDefault'=>0]);
                return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_BUTTON_SET_DEFAULT_MSG_PAYMENT_METHOD_PROFILE_SUCCESSFULLY_UPDATED')));
            } else {
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_BUTTON_SET_DEFAULT_MSG_PROBLEM_UPDATING_PAYMENT_METHOD_PROFILE')));
            }
        }
    }

    public function card_active_deactive($id,$action)
    {
        if ($id && $action) {
            $card = AccountPaymentProfile::findOrFail($id);
            if ($action == 'active') {
                $save['Status'] = 1;
            } else if ($action == 'deactive') {
                $save['Status'] = 0;
            }
            if($card->update($save)){
                return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MSG_CARD_ACTIVE_DEACTIVE_SUCCESSFULLY')));
            } else {
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MSG_CARD_ACTIVE_DEACTIVE_FAILED')));
            }
        }
    }

    public function verify_bankaccount(){
        $data = Input::all();
        $cardID = $data['cardID'];
        $CompanyID = Customer::get_companyID();
        $AccountPaymentProfile = AccountPaymentProfile::find($cardID);
        if(!empty($AccountPaymentProfile)){
            $PaymentGatewayID = $AccountPaymentProfile->PaymentGatewayID;
            if(!empty($PaymentGatewayID)){
                $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
                $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass,$CompanyID);
                $ProfileResponse = $PaymentIntegration->doVerify($data);

            }else{
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MSG_PAYMENT_GATEWAY_NOT_SETUP')));
            }

        }else{
            return Response::json(array("status" => "failed", "message" => Lang::get('routes.MESSAGE_RECORD_NOT_FOUND')));
        }

        return $ProfileResponse;
        /*
        if(!empty($data['Sage']) && $data['Sage']==1){
            return $this->verify_sagebankaccount($cardID);
        }
        if(empty($data['MicroDeposit1']) || empty($data['MicroDeposit2'])){
            return Response::json(array("status" => "failed", "message" => "Both MicroDeposit Required."));
        }
        $AccountPaymentProfile = AccountPaymentProfile::find($cardID);
        $options = json_decode($AccountPaymentProfile->Options,true);
        $CustomerProfileID = $options['CustomerProfileID'];
        $BankAccountID = $options['BankAccountID'];
        $stripedata = array();
        $stripedata['CustomerProfileID'] = $CustomerProfileID;
        $stripedata['BankAccountID'] = $BankAccountID;
        $stripedata['MicroDeposit1'] = $data['MicroDeposit1'];
        $stripedata['MicroDeposit2'] = $data['MicroDeposit2'];
        $stripepayment = new StripeACH();

        if(empty($stripepayment->status)){
            return Response::json(array("status" => "failed", "message" => "Stripe ACH Payment not setup correctly"));
        }
        $StripeResponse = $stripepayment->verifyBankAccount($stripedata);
        if($StripeResponse['status']=='Success'){
            if($StripeResponse['VerifyStatus']=='verified'){
                $option = array(
                    'CustomerProfileID' => $CustomerProfileID,
                    'BankAccountID' => $BankAccountID,
                    'VerifyStatus' => $StripeResponse['VerifyStatus']
                );
                $AccountPaymentProfile->update(array('Options' => json_encode($option)));

                return Response::json(array("status" => "success", "message" => "verification status is ".$StripeResponse['VerifyStatus']));
            }else{
                return Response::json(array("status" => "failed", "message" => "verification status is ".$StripeResponse['VerifyStatus']));
            }

        }else{
            return Response::json(array("status" => "failed", "message" => $StripeResponse['error']));
        }
        */
    }

    // not using
    public function verify_sagebankaccount($cardID){
        $AccountPaymentProfile = AccountPaymentProfile::find($cardID);
        $options = json_decode($AccountPaymentProfile->Options,true);
        $CompanyID = $AccountPaymentProfile->CompanyID;
        $sagedata = array();
        $sagepayment = new SagePayDirectDebit($CompanyID);
        if(empty($sagepayment->status)){
            return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_MSG_SAGE_DIRECT_DEBIT_NOT_SETUP_CORRECTLY')));
        }

        $sagedata['AccountNumber']=Crypt::decrypt($options['AccountNumber']);
        $sagedata['BranchCode']=Crypt::decrypt($options['BranchCode']);
        $sagedata['AccountType']=$options['AccountHolderType'];

        $SageResponse = $sagepayment->verifyBankAccount($sagedata);
        if($SageResponse['status']=='Success'){
            if($SageResponse['VerifyStatus']=='verified'){
                unset($options['VerifyStatus']);
                $options['VerifyStatus'] = $SageResponse['VerifyStatus'];
                $AccountPaymentProfile->update(array('Options' => json_encode($options)));

                return Response::json(array("status" => "success", "message" => "verification status is ".$SageResponse['VerifyStatus']));
            }else{
                return Response::json(array("status" => "failed", "message" => "verification status is ".$SageResponse['VerifyStatus']));
            }

        }else{
            return Response::json(array("status" => "failed", "message" => $SageResponse['error']));
        }
    }
}