<?php

class AccountApproval extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('AccountApprovalID');

    protected $table = 'tblAccountApproval';

    protected  $primaryKey = "AccountApprovalID";
    const VENDOR =1;
    const CUSTOMER = 2;
    const BOTH = 3;

    const BILLINGTYPE_PREPAID = 1;
    const BILLINGTYPE_POSTPAID = 2;
    const BILLINGTYPE_BOTH = 3;

    public  static $account_type = array(self::VENDOR=>'Vendor',self::CUSTOMER=>'Customer',self::BOTH=>'Customer & Vendor');
    public  static $billing_type = array(''=>'Select Billing Type',self::BILLINGTYPE_PREPAID=>'Prepaid',self::BILLINGTYPE_POSTPAID=>'Postpaid');
    public  static $billing_type_1 = array(self::BILLINGTYPE_PREPAID=>'Prepaid',self::BILLINGTYPE_POSTPAID=>'Postpaid',self::BILLINGTYPE_BOTH=>'Prepaid & Postpaid');

    public  static function getList($AccountID){
        $account = Account::find($AccountID);
        if($account->IsVendor ==1 || $account->IsCustomer ==1) {
            $accounttype = '';
            if(!($account->IsCustomer == 1 && $account->IsVendor == 1)){
                $accounttype = ' and (';
            }

            if ($account->IsVendor == 1 && $account->IsCustomer != 1) {
                $accounttype .= 'AccountType = ' . self::VENDOR;
            }
            if ($account->IsCustomer == 1 && $account->IsVendor != 1) {
                $accounttype .= 'AccountType = ' . self::CUSTOMER;
            }
            if (($account->IsVendor == 1 || $account->IsCustomer == 1) && !($account->IsCustomer == 1 && $account->IsVendor == 1)) {
                $accounttype .= ' or AccountType = ' . self::BOTH;
            }
            if(!($account->IsCustomer == 1 && $account->IsVendor == 1)){
                $accounttype .= ')';
            }
            $countrysql = '';
            if ($account->Country) {
                $countryid = Country::where(['country' => $account->Country])->pluck('CountryID');
                $countrysql = ' and (CountryId = ' . intval($countryid) . ' or CountryId is NULL or CountryId = 0)';
            }
            $billingsql = '';
            if ($account->BillingType) {
                $billingsql = ' and (BillingType = ' . $account->BillingType .' or BillingType = '.self::BILLINGTYPE_BOTH. ' or BillingType is NULL or BillingType = 0)';
            }
            return $AccountApproval = DB::select(DB::raw("select `Key`, DocumentFile,Required, Infomsg, AccountApprovalID from tblAccountApproval
          where CompanyID = :companyid and Status = 1 " . $accounttype . $countrysql.$billingsql),
                array(
                    'companyid' => User::get_companyID(),
                ));
        }
    }
    public  static function getRequiredList($AccountID){
        $account = Account::find($AccountID);
        if($account->IsVendor ==1 || $account->IsCustomer ==1) {
            $accounttype = '';
            if(!($account->IsCustomer == 1 && $account->IsVendor == 1)){
                $accounttype = ' and (';
            }

            if ($account->IsVendor == 1 && $account->IsCustomer != 1) {
                $accounttype .= 'AccountType = ' . self::VENDOR;
            }
            if ($account->IsCustomer == 1 && $account->IsVendor != 1) {
                $accounttype .= 'AccountType = ' . self::CUSTOMER;
            }
            if (($account->IsVendor == 1 || $account->IsCustomer == 1) && !($account->IsCustomer == 1 && $account->IsVendor == 1)) {
                $accounttype .= ' or AccountType = ' . self::BOTH;
            }
            if(!($account->IsCustomer == 1 && $account->IsVendor == 1)){
                $accounttype .= ')';
            }
            $countrysql = '';
            if ($account->Country) {
                $countryid = Country::where(['country' => $account->Country])->pluck('CountryID');
                $countrysql = ' and (CountryId = ' . intval($countryid) . ' or CountryId is NULL or CountryId = 0)';
            }
            $billingsql = '';
            if ($account->BillingType) {
                $billingsql = ' and (BillingType = ' . $account->BillingType .' or BillingType = '.self::BILLINGTYPE_BOTH. ' or BillingType is NULL or BillingType = 0)';
            }
            return $AccountApproval = DB::select(DB::raw("select `Key`, DocumentFile,Required, Infomsg, AccountApprovalID from tblAccountApproval
          where CompanyID = :companyid and Status = 1 and Required = 1" . $accounttype . $countrysql.$billingsql),
                array(
                    'companyid' => User::get_companyID(),
                ));
        }
    }
    public  static  function  checkForeignKeyById($id){
        $hasAccountApprovalList = AccountApprovalList::where("AccountApprovalID",$id)->count();
        if( intval($hasAccountApprovalList) > 0){
            return true;
        }else{
            return false;
        }

    }

}