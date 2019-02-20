<?php

class AccountsPaymentProfileController extends \BaseController {

    // not using
    public function ajax_datagrid($AccountID){

        $CompanyID = User::get_companyID();

        $PaymentGatewayName = '';
        $PaymentGatewayID = '';
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
    // not using
    public function index($AccountID)
    {

        
        $PaymentGatewayID = '';
        $Account = Account::find($AccountID);
        $PaymentMethod = '';
        if(!empty($Account->PaymentMethod)){
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($Account->PaymentMethod);
            $PaymentMethod = $Account->PaymentMethod;
        }

        return View::make('accountpaymentprofile.index',compact('AccountID','PaymentGatewayID','PaymentMethod'));
    }
    // not using
    public function create()
    {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $CustomerID = $data['AccountID'];
        if($CustomerID > 0) {
            $PaymentGatewayID = $data['PaymentGatewayID'];
            if($data['PaymentGatewayID']==PaymentGateway::StripeACH){
                return AccountPaymentProfile::createBankProfile($CompanyID, $CustomerID,$PaymentGatewayID);
            }elseif($data['PaymentGatewayID']==PaymentGateway::SagePayDirectDebit){
                return AccountPaymentProfile::createSagePayProfile($CompanyID, $CustomerID,$PaymentGatewayID);
            }
            return AccountPaymentProfile::createProfile($CompanyID, $CustomerID,$PaymentGatewayID);
        }
        return array("status" => "failed", "message" => 'Account not found');
    }
      

      public static function getCardValue($id)
      {
        $Account = Account::find($id);
        if(!empty($Account->PaymentMethod)) {
        $PaymentGatewayID = PaymentGateway::where(['title' => $Account->PaymentMethod,'Status' =>1])->first();
        $data = AccountPaymentProfile::where(['AccountID' => $id, 'PaymentGatewayID' => $PaymentGatewayID->PaymentGatewayID, 'Status' => 1])->first();
        $json = json_decode($data->Options);
        return $json->CardID;
    } else { return '0';
      }
    }

    public function AddIngenico()
    {
        $data = Input::all();
        $value = $data['value'];
        $accountId = $data['accountId'];
        $companyId = $data['companyId'];
        $method = $data['method'];
        $PaymentGatewayID = 0;
        $Account = Account::find($accountId);
        $options = [
          'CardID' => $value
        ];
        if($Account->PaymentMethod == $method) {
            AccountPaymentProfile::where('AccountID',$accountId)->update(['isDefault' =>0]);
            $isDefault = 1;} else {$isDefault = 0;}
        $PaymentGatewayID = PaymentGateway::where(['title' => $Account->PaymentMethod,'Status' =>1])->first();
        AccountPaymentProfile::updateOrCreate([
        'CompanyID' => $companyId, 'AccountID' => $accountId, 'PaymentGatewayID' => $PaymentGatewayID->PaymentGatewayID
        ],[
            'Options' => json_encode($options), 'Status' => 1, 'isDefault' => $isDefault
        ]);

          return '<span style="background-color:green;color:white;padding:5px;"><i class"entypo-check"></i> Updated</span>';

        
    }
}