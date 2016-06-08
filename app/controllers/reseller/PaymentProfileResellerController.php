<?php
class PaymentProfileResellerController extends \BaseController {


    public function ajax_datagrid() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $AccountID = User::get_userID();
        $carddetail = AccountPaymentProfile::select("tblAccountPaymentProfile.Title","tblAccountPaymentProfile.Status","tblAccountPaymentProfile.isDefault","tblPaymentGateway.Title as gateway","created_at","AccountPaymentProfileID");
        $carddetail->join('tblPaymentGateway', function($join)
        {
            $join->on('tblPaymentGateway.PaymentGatewayID', '=', 'tblAccountPaymentProfile.PaymentGatewayID');

        })->where(["tblAccountPaymentProfile.CompanyID"=>$CompanyID])->where(["tblAccountPaymentProfile.AccountID"=>$AccountID]);

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
        return View::make('reseller.paymentprofile.index',compact('currentmonth','currentyear'));
	}

    public function paynow()
    {
        return View::make('reseller.paymentprofile.paynow',compact('currentmonth','currentyear'));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /payments/create
	 *
	 * @return Response
	 */
    public function create()
    {
        $CompanyID = Reseller::get_companyID();
        $CustomerID = Reseller::get_accountID();
        return AccountPaymentProfile::createProfile($CompanyID,$CustomerID);
    }

    public function update(){

        $data = Input::all();
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
            $CardDetail = array('Title'=>$title, 'created_by'=>Reseller::get_accountName());
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
        $CompanyID = Reseller::get_companyID();
        $AccountID = Reseller::get_accountID();
        $AuthorizeNet = new AuthorizeNet();
        $count = AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->count();
        $PaymentProfile = AccountPaymentProfile::find($id);
        if(!empty($PaymentProfile)){
            $options = json_decode($PaymentProfile->Options);
            $ProfileID = $options->ProfileID;
            $PaymentProfileID = $options->PaymentProfileID;
            $isDefault = $PaymentProfile->isDefault;
        }else{
            return Response::json(array("status" => "failed", "message" => "Record Not Found"));
        }
        if($isDefault==1){
            if($count!=1){
                return Response::json(array("status" => "failed", "message" => "You can not delete default profile. Please set as default an other profile first."));
            }
        }
        $result = $AuthorizeNet->DeletePaymentProfile($ProfileID,$PaymentProfileID);
        if($result["status"]=="success"){
            if ($PaymentProfile->delete()) {
                if($count==1){
                    $result =  $AuthorizeNet->deleteProfile($ProfileID);
                    if($result["status"]=="success"){
                        return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted. Profile deleted too."));
                    }
                }else{
                    return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted"));
                }
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem deleting Payment Method Profile."));
            }
        }elseif($result["code"]=='E00040'){
            if ($PaymentProfile->delete()) {
                return Response::json(array("status" => "success", "message" => "Payment Method Profile Successfully deleted"));
            }else{
                return Response::json(array("status" => "failed", "message" => "Problem deleting Payment Method Profile."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => (array)$result["message"]));
        }
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
                    $CompanyID = Reseller::get_companyID();
                    $AccountID = Reseller::get_accountID();
                }
                AccountPaymentProfile::where(["CompanyID"=>$CompanyID])->where(["AccountID"=>$AccountID])->where('AccountPaymentProfileID','<>',$id)->update(['isDefault'=>'0']);
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