<?php

class AccountsPaymentProfileController extends \BaseController {

    public function ajax_datagrid($AccountID){

        $CompanyID = User::get_companyID();
        $carddetail = AccountPaymentProfile::select("tblAccountPaymentProfile.Title as ProfileTitle","tblAccountPaymentProfile.Status","tblAccountPaymentProfile.isDefault","tblPaymentGateway.Title","created_at","AccountPaymentProfileID","tblAccountPaymentProfile.Options");
        $carddetail->join('tblPaymentGateway', function($join)
        {
            $join->on('tblPaymentGateway.PaymentGatewayID', '=', 'tblAccountPaymentProfile.PaymentGatewayID');

        })->where(["tblAccountPaymentProfile.CompanyID"=>$CompanyID])->where(["tblAccountPaymentProfile.AccountID"=>$AccountID]);

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
            return AccountPaymentProfile::createProfile($CompanyID, $CustomerID);
        }
        return array("status" => "failed", "message" => 'Account not found');
    }
}