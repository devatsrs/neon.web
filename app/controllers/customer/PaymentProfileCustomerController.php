<?php
class PaymentProfileCustomerController extends \BaseController {


    public function ajax_datagrid() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $AccountID = User::get_userID();
        $PaymentGatewayName = '';
        $PaymentGatewayID = PaymentGateway::getPaymentGatewayID();
        if(!empty($PaymentGatewayID)){
            $PaymentGatewayName = PaymentGateway::$paymentgateway_name[$PaymentGatewayID];
        }
        $carddetail = AccountPaymentProfile::select("tblAccountPaymentProfile.Title","tblAccountPaymentProfile.Status","tblAccountPaymentProfile.isDefault",DB::raw("'".$PaymentGatewayName."' as gateway"),"created_at","AccountPaymentProfileID");
        $carddetail->where(["tblAccountPaymentProfile.CompanyID"=>$CompanyID])
            ->where(["tblAccountPaymentProfile.AccountID"=>$AccountID])
            ->where(["tblAccountPaymentProfile.PaymentGatewayID"=>$PaymentGatewayID]);

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
        $currentmonth = date("n");
        $currentyear = date("Y");
        return View::make('customer.paymentprofile.index',compact('currentmonth','currentyear'));
	}

    public function paynow()
    {
        return View::make('customer.paymentprofile.paynow',compact('currentmonth','currentyear'));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /payments/create
	 *
	 * @return Response
	 */
    public function create()
    {
        $CompanyID = Customer::get_companyID();
        $CustomerID = Customer::get_accountID();
        return AccountPaymentProfile::createProfile($CompanyID,$CustomerID);
    }

    public function update(){

        $data = Input::all();
		
		$isAuthorizedNet  = 	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$AuthorizeSlug);
		if(!$isAuthorizedNet){
			return Response::json(array("status" => "failed", "message" => "Payment Method Not Integrated"));
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
            return Response::json(array("status" => "failed", "message" => "Record Not Found"));
        }
        $title = $data['Title'];
        $result = $AuthorizeNet->UpdatePaymentProfile($ProfileID,$PaymentProfileID,$data);
        if($result["status"]=="success"){
            $CardDetail = array('Title'=>$title, 'created_by'=>Customer::get_accountName());
            if ($PaymentProfile->update($CardDetail)) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Payment Method Profile."));
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

                if($PaymentGatewayID==PaymentGateway::Authorize){
                    $ProfileResponse = AccountPaymentProfile::deleteAuthorizeProfile($CompanyID, $AccountID,$id);
                }
                if($PaymentGatewayID==PaymentGateway::Stripe){
                    $ProfileResponse = AccountPaymentProfile::deleteStripeProfile($CompanyID, $AccountID,$id);
                }

            }else{
                return Response::json(array("status" => "failed", "message" => "Payment Gateway not setup"));
            }

        }else{
            return Response::json(array("status" => "failed", "message" => "Record Not Found"));
        }

        return $ProfileResponse;

    }

    public function set_default($id)
    {
        if ($id) {
            $data = Input::all();
            $card = AccountPaymentProfile::findOrFail($id);
            if($card->update(['isDefault'=>1])){
                if(!empty($data['AccountID'])){
                    $AccountID =$data['AccountID'];
                    $CompanyID = User::get_companyID();
                }else{
                    $CompanyID = Customer::get_companyID();
                    $AccountID = Customer::get_accountID();
                }
                $PaymentGatewayID = $card->PaymentGatewayID;
                AccountPaymentProfile::where(["CompanyID"=>$CompanyID,"PaymentGatewayID"=>$PaymentGatewayID])->where(["AccountID"=>$AccountID])->where('AccountPaymentProfileID','<>',$id)->update(['isDefault'=>'0']);
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Payment Method Profile."));
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
                return Response::json(array("status" => "success", "message" => "Card Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Card."));
            }
        }
    }
}