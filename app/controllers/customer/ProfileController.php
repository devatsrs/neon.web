<?php

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
        $id = User::get_userID();
        $companyID = User::get_companyID();
        $account = Account::find($id);
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
        $id = User::get_userID();
        $companyID = User::get_companyID();
        $account = Account::find($id);
        $countries = $this->countries;

        $currencies = Currency::getCurrencyDropdownIDList();
        $taxrates = TaxRate::getTaxRateDropdownIDList();
        $timezones = TimeZone::getTimeZoneDropdownList();
        $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();

        $doc_status = Account::$doc_status;
        return View::make('customer.accounts.edit', compact('account', 'account_owners', 'countries','doc_status','currencies','timezones','taxrates','InvoiceTemplates'));
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
        $id = User::get_userID();
        $companyID = User::get_companyID();
        $account = Account::find($id);

        if(empty($data['password'])){ /* if empty, dont update password */
            unset($data['password']);
        }else{
            if($account->VerificationStatus == Account::VERIFIED && $account->Status == 1 ) {
                /* Send mail to Customer */
                $password       = $data['password'];
                $data['password']       = Hash::make($password);
            }
        }
        $CustomerPicture = Input::file('Picture');
        if (!empty($CustomerPicture)){

            $extension = '.'. Input::file('Picture')->getClientOriginalExtension();
            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['CUSTOMER_PROFILE_IMAGE'],User::get_companyID(),User::get_userID()) ;
            $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . "/". $amazonPath;
            $fileName = \Illuminate\Support\Str::slug($account->AccountName .'_'. str_random(4)) .$extension;
            $CustomerPicture->move($destinationPath,$fileName);

            if(!AmazonS3::upload($destinationPath.$fileName,$amazonPath)){
                return Response::json(array("status" => "failed", "message" => "Failed to upload."));
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
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Account."));
        }
    }

    public function get_outstanding_amount() {
        $data = Input::all();
        $id = User::get_userID();
        $account = Account::find($id);
        $companyID = User::get_companyID();
        $Invoiceids = $data['InvoiceIDs'];
        $outstanding = Account::getOutstandingInvoiceAmount($companyID, $account->AccountID, $Invoiceids, get_round_decimal_places($account->AccountID));
        //$outstanding =Account::getOutstandingAmount($companyID,$account->AccountID,get_round_decimal_places($account->AccountID));
        $currency = Currency::getCurrencySymbol($account->CurrencyId);
        $outstandingtext = $currency.$outstanding;
        echo json_encode(array("status" => "success", "message" => "","outstanding"=>$outstanding,"outstadingtext"=>$outstandingtext));
    }


}