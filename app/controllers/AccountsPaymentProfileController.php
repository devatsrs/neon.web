<?php

class AccountsPaymentProfileController extends \BaseController {

    public function ajax_datagrid($AccountID){

        $CompanyID = User::get_companyID();

        $PaymentGatewayName = '';
        $PaymentGatewayID = '';
        //$PaymentGatewayID = PaymentGateway::getPaymentGatewayID();
        $account = Account::find($AccountID);
        if(!empty($account->PaymentMethod)){
            $PaymentGatewayName = $account->PaymentMethod;
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($PaymentGatewayName);
        }

        $carddetail = AccountPaymentProfile::select("tblAccountPaymentProfile.Title","tblAccountPaymentProfile.Status","tblAccountPaymentProfile.isDefault",DB::raw("'".$PaymentGatewayName."' as gateway"),"created_at","AccountPaymentProfileID","tblAccountPaymentProfile.Options");
        $carddetail->where(["tblAccountPaymentProfile.CompanyID"=>$CompanyID])
            ->where(["tblAccountPaymentProfile.AccountID"=>$AccountID])
            ->where(["tblAccountPaymentProfile.PaymentGatewayID"=>$PaymentGatewayID]);

        return Datatables::of($carddetail)->make();

    }

    /**
     * Call from Invoice Pay now button
     *
    */
    public function index($AccountID)
    {

        \Debugbar::disable();
        $PaymentGatewayID = '';
        $Account = Account::find($AccountID);
        $PaymentMethod = '';
        if(!empty($Account->PaymentMethod)){
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($Account->PaymentMethod);
            $PaymentMethod = $Account->PaymentMethod;
        }

        return View::make('accountpaymentprofile.index',compact('AccountID','PaymentGatewayID','PaymentMethod'));
    }
    public function create()
    {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $CustomerID = $data['AccountID'];
        if($CustomerID > 0) {
            $PaymentGatewayID = $data['PaymentGatewayID'];
            if($data['PaymentGatewayID']==PaymentGateway::StripeACH){
                return AccountPaymentProfile::createBankProfile($CompanyID, $CustomerID,$PaymentGatewayID);
            }
            return AccountPaymentProfile::createProfile($CompanyID, $CustomerID,$PaymentGatewayID);
        }
        return array("status" => "failed", "message" => 'Account not found');
    }
}