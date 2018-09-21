<?php
use Illuminate\Support\Facades\Crypt;

class ProfileController extends \BaseController {

    var $countries;
    var $model = 'Account';
    public function __construct() {
        $this->countries = Country::getCountryDropdownList();
    }

    /**
     * Display the specified resource.
     * GET /accounts/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show() {
        $id = Customer::get_accountID();
        $account = Account::find($id);
        $companyID = $account->CompanyId;
        $AccountBilling = AccountBilling::getBilling($id);
        $account_owner = User::find($account->Owner);
        $contacts = Contact::where(["CompanyID" => $companyID, "Owner" => $id])->orderBy('FirstName', 'asc')->get();
        return View::make('customer.accounts.show', compact('account', 'contacts','account_owner','AccountBilling'));
    }

    /**
     * Show the form for editing the specified resource.
     * GET /accounts/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit() {
        $id =  Customer::get_accountID();
        $account = Account::find($id);
        $countries = $this->countries;
        $doc_status = Account::$doc_status;
        return View::make('customer.accounts.edit', compact('account', 'countries','doc_status'));
    }

    /**
     * Update the specified resource in storage.
     * PUT /accounts/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update() {
        $data = Input::all();
        $AccountID = Customer::get_accountID();
        $account = Account::find($AccountID);
        $companyID = $account->CompanyId;

        if(empty($data['password'])){ /* if empty, dont update password */
            unset($data['password']);
        }else{
            if($account->VerificationStatus == Account::VERIFIED && $account->Status == 1 ) {
                /* Send mail to Customer */
                $password       = $data['password'];
                //$data['password']       = Hash::make($password);
                $data['password']         = Crypt::encrypt($password);
            }
        }
        $CustomerPicture = Input::file('Picture');
        if (!empty($CustomerPicture)){

            $extension = '.'. Input::file('Picture')->getClientOriginalExtension();
            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['CUSTOMER_PROFILE_IMAGE'],$companyID,$AccountID) ;
            $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . "/". $amazonPath;
            $fileName = \Illuminate\Support\Str::slug($account->AccountName .'_'. str_random(4)) .$extension;
            $CustomerPicture->move($destinationPath,$fileName);

            if(!AmazonS3::upload($destinationPath.$fileName,$amazonPath)){
                return Response::json(array("status" => "failed", "message" => Lang::get('routes.MESSAGE_FAILED_TO_UPLOAD_FILE')));
            }

            $data['Picture'] = $amazonPath.$fileName;

            //Delete old picture
            if(!empty($account->Picture)){
                AmazonS3::delete($account->Picture);
            }
        }else{
            unset($data['Picture']);
        }
        if ($account->update($data)) {
            return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_PROFILE_MSG_ACCOUNT_SUCCESSFULLY_UPDATED')));
        } else {
            return Response::json(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_PROFILE_MSG_PROBLEM_UPDATING_ACCOUNT')));
        }
    }

    public function get_outstanding_amount($AccountID) {
        $data = Input::all();
        $account = Account::find($AccountID);
        $CompanyID = $account->CompanyId;
        $Invoiceids = $data['InvoiceIDs'];
        $outstanding = Account::getOutstandingInvoiceAmount($CompanyID, $AccountID, $Invoiceids, get_round_decimal_places($AccountID));
        //$outstanding =Account::getOutstandingAmount($companyID,$account->AccountID,get_round_decimal_places($account->AccountID));
        $currency = Currency::getCurrencySymbol($account->CurrencyId);
        $outstandingtext = $currency.$outstanding;
        echo json_encode(array("status" => "success", "message" => "","outstanding"=>$outstanding,"outstadingtext"=>$outstandingtext));
    }


}