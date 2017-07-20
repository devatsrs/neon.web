<?php

class AccountsPaymentProfileController extends \BaseController {

    public function ajax_datagrid($AccountID){

        $CompanyID = User::get_companyID();

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
    public function index($AccountID)
    {

        \Debugbar::disable();

        return View::make('accountpaymentprofile.index',compact('AccountID'));
    }
    public function create()
    {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $CustomerID = $data['AccountID'];
        if($CustomerID > 0) {
            if($data['PaymentGatewayID']==PaymentGateway::StripeACH){
                return AccountPaymentProfile::createBankProfile($CompanyID, $CustomerID);
            }
            return AccountPaymentProfile::createProfile($CompanyID, $CustomerID);
        }
        return array("status" => "failed", "message" => 'Account not found');
    }
}