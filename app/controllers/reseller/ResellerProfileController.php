<?php
use Illuminate\Support\Facades\Crypt;

class ResellerProfileController extends \BaseController {

    var $countries;
    //var $model = 'Account';
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
        $AccountID = Customer::get_accountID();
        $account = Account::find($AccountID);
        $companyID = $account->CompanyId;
        $AccountBilling = AccountBilling::getBilling($AccountID);
        $account_owner = User::find($account->Owner);
        $contacts = Contact::where(["CompanyID" => $companyID, "Owner" => $AccountID])->orderBy('FirstName', 'asc')->get();
        return View::make('resellerpanel.accounts.show', compact('account', 'contacts','account_owner','AccountBilling'));
    }

    /**
     * Show the form for editing the specified resource.
     * GET /accounts/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit() {
        $AccountID = Customer::get_accountID();
        $account = Account::find($AccountID);
        $countries = $this->countries;
        $doc_status = Account::$doc_status;
        return View::make('resellerpanel.accounts.edit', compact('account', 'countries','doc_status'));
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
                $data['password']       = Crypt::encrypt($password);
            }
        }
        $CustomerPicture = Input::file('Picture');
        if (!empty($CustomerPicture)){

            $extension = '.'. Input::file('Picture')->getClientOriginalExtension();
            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['CUSTOMER_PROFILE_IMAGE'],$companyID,$AccountID) ;
            $destinationPath = CompanyConfiguration::get('UPLOAD_PATH',$companyID) . "/". $amazonPath;
            $fileName = \Illuminate\Support\Str::slug($account->AccountName .'_'. str_random(4)) .$extension;
            $CustomerPicture->move($destinationPath,$fileName);

            if(!AmazonS3::upload($destinationPath.$fileName,$amazonPath,$companyID)){
                return Response::json(array("status" => "failed", "message" => "Failed to upload."));
            }

            $data['Picture'] = $amazonPath.$fileName;

            //Delete old picture
            if(!empty($account->Picture)){
                AmazonS3::delete($account->Picture,$companyID);
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