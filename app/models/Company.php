<?php

class Company extends \Eloquent {
    protected $guarded = array();

    protected $table = 'tblCompany';

    protected  $primaryKey = "CompanyID";

    const BILLING_STARTTIME = 1;
    const BILLING_ENDTIME = 2;
    const BILLING_SETUPTIME = 3;

    const LICENCE_ALL = 3;
    const LICENCE_BILLING = 2;
    const LICENCE_RM = 1;

    public static $billing_time = array(''=>'Select',self::BILLING_STARTTIME=>'Start Time',self::BILLING_ENDTIME=>'End Time',self::BILLING_SETUPTIME=>'Setup Time');
    public static $BillingCycleType =
        array(
             ""=>"Select",
            "weekly"=>"Weekly",
            "fortnightly"=>"Fortnightly",
            "monthly"=>"Monthly" ,
            "quarterly"=>"Quarterly",
            "daily"=>"Daily",
            "in_specific_days"=>"In Specific days",
            "monthly_anniversary"=>"Monthly anniversary",
            "yearly" => "Yearly"
        );
    public static $BillingCycleType2 =
        array(
            ""=>"Select",
            "weekly"=>"Weekly",
            "fortnightly"=>"Fortnightly",
            "monthly"=>"Monthly" ,
            "quarterly"=>"Quarterly",
            "daily"=>"Daily",
            "in_specific_days"=>"In Specific days",
            "monthly_anniversary"=>"Monthly anniversary",
            "yearly" => "Yearly",
            "manual" => "Manual",
        );

    // CDR Rerate Based on Charge code or Prefix
    const CHARGECODE =1;
    const PREFIX =2;
    public static $rerate_format = array(''=>'Select',self::CHARGECODE=>'Charge Code',self::PREFIX=>'Prefix');

    public static $date_format = array( 'd-m-Y'=>'dd-mm-yyyy (31-12-2015)',
                                        'm-d-Y'=>'mm-dd-yyyy (12-31-2015)',
                                        'Y-m-d'=>'yyyy-mm-dd (2015-12-31)',
                                        'd-m-y'=>'dd-mm-yy (31-12-15)',
                                        'm-d-y'=>'mm-dd-yy (12-31-15)',
                                        'y-m-d'=>'yy-mm-dd (15-12-31)',
                                        'd-M-Y'=>'dd-mmm-yyyy (31-DEC-2015)',
                                        'M-d-Y'=>'mmm-dd-yyyy (DEC-31-2015)',
                                        'Y-M-d'=>'yyyy-mmm-dd (2015-DEC-31)',
                                        'd-M-y'=>'dd-mmm-yy (31-DEC-15)',
                                        'M-d-y'=>'mmm-dd-yy (DEC-31-15)',
                                        'y-M-d'=>'yy-mmm-dd (15-DEC-31)',
                                        'd-F-Y'=>'dd-mmmm-yyyy (31-DECEMBER-2015)',
                                        'F-d-Y'=>'mmmm-dd-yyyy (DECEMBER-31-2015)',
                                        'Y-F-d'=>'yyyy-mmmm-dd (2015-DECEMBER-31)',
                                        'd-F-y'=>'dd-mmmm-yy (31-DECEMBER-15)',
                                        'F-d-y'=>'mmmm-dd-yy (DECEMBER-31-15)',
                                        'y-F-d'=>'yy-mmmm-dd (15-DECEMBER-31)',
                                      );
    public static $dialcode_separator = array(''=>'Skip loading',' '=>'Space',';'=>'SemiColon(;)',','=>'Comma(,)','|'=>'Pipe(|)');

    public static function getName($companyID=0){
        if($companyID>0){
            return Company::find($companyID)->CompanyName;
        }else{
            return Company::find(User::get_companyID())->CompanyName;
        }

    }

    public static function ValidateLicenceKey(){
        $LICENCE_KEY = getenv('LICENCE_KEY');
        $result = array();
        $result['LicenceHost'] = $_SERVER['HTTP_HOST'];
        $result['LicenceIP'] = $_SERVER['SERVER_ADDR'];
        $result['LicenceKey'] = $LICENCE_KEY;
        $result['Type'] = '';
        $result['LicenceProperties'] = '';
        $company_id = User::get_companyID();
        //$result['CompanyName'] = company::getName($company_id);
        $result['CompanyName'] = getenv('COMPANY_NAME');
        //$result['CompanyName'] = 'abc';
        if(!empty($LICENCE_KEY)) {

            $post = array("host" => $_SERVER['HTTP_HOST'], "ip" => $_SERVER['SERVER_ADDR'], "licence_key" => getenv('LICENCE_KEY'), "company_name" => $result['CompanyName']);
            $response = call_api($post);
            if (!empty($response)) {
                $response = json_decode($response,TRUE);
                $result['Status'] = $response['Status'] ;
                $result['Message'] = $response['Message'];
                $result['ExpiryDate'] = $response['ExpiryDate'];
                $result['Type'] = $response['Type'];
                $result['LicenceProperties'] = $response['LicenceProperties'];
            }else{
                \Illuminate\Support\Facades\Log::info("ValidateLicenceKey response");
                //\Illuminate\Support\Facades\Log::info($response);
                $result['Status'] = 0 ;
                $result['Message'] = 'Unable To Validate Licence';
                $result['ExpiryDate']='';
            }

        }else{

            $result['Message'] = "Licence key not found";
            $result['Status'] = "Licence key not found";
            $result['ExpiryDate'] = "";

            \Illuminate\Support\Facades\Log::info("Licence key not found");

        }

        return $result;

    }

    public static function getCompanyField($companyID,$field = "") {
        if(!empty($field)) {

            if ($companyID > 0) {
                return Company::where("CompanyID",$companyID)->pluck($field);
            } else {
                return Company::find(User::get_companyID())->CompanyName;
            }
        }
    }
    public static function getEmail($CompanyID){
        if($CompanyID > 0){
            $Email = Company::where("CompanyID",$CompanyID)->pluck("Email");
            return explode(',',$Email);
        }
    }

    public static function getLicenceType(){

        $LicenceApiResponse = Session::get('LicenceApiResponse','');
        if(!empty($LicenceApiResponse) && isset($LicenceApiResponse['Type'])) {
            if ($LicenceApiResponse['Type'] == Company::LICENCE_ALL) {
                return Company::LICENCE_ALL;
            }
            if ($LicenceApiResponse['Type'] == Company::LICENCE_BILLING) {
                return Company::LICENCE_BILLING;
            }
            if ($LicenceApiResponse['Type'] == Company::LICENCE_RM) {
                return Company::LICENCE_RM;
            }
        }
        return '';

    }

    public static function isBillingLicence($billing=0){

        $LicenceApiResponse = Session::get('LicenceApiResponse','');
        if(!empty($LicenceApiResponse) && isset($LicenceApiResponse['Type'])) {
            if ($billing == 0 && $LicenceApiResponse['Type'] == Company::LICENCE_ALL || $LicenceApiResponse['Type'] == Company::LICENCE_BILLING) {
                return true;
            }else if ($billing == 1 && $LicenceApiResponse['Type'] == Company::LICENCE_BILLING) {
                return true;
            }
        }
        return false;

    }

    public static function isRMLicence(){

        $LicenceApiResponse = Session::get('LicenceApiResponse','');
        if(!empty($LicenceApiResponse) && isset($LicenceApiResponse['Type'])) {
            if ($LicenceApiResponse['Type'] == Company::LICENCE_ALL || $LicenceApiResponse['Type'] == Company::LICENCE_RM) {
                return true;
            }
        }
        return false;

    }

    public static function getLicenceResponse() {

        $LicenceApiResponse = Session::get('LicenceApiResponse','');

        if(empty($LicenceApiResponse) || (!empty($LicenceApiResponse) && isset($LicenceApiResponse["Status"]) &&  $LicenceApiResponse["Status"] != 1) ) {  // if first time login ... // check if status is empty then also try again
            $valresponse = Company::ValidateLicenceKey();
            Session::set('LicenceApiResponse', $valresponse);
            $LicenceApiResponse = $valresponse;
            //Log response;
            //\Illuminate\Support\Facades\Log::info($LicenceApiResponse);

        }
        return $LicenceApiResponse;
    }

    public static function getCompanyTimeZone($companyID=0){
            if($companyID>0){
                return Company::find($companyID)->TimeZone;
            }else{
                return Company::find(User::get_companyID())->TimeZone;
            }
    }
	
	 public static function getCompanyFullAddress($companyID=0){
		 if($companyID>0){
			 $companyData = Company::find($companyID);
		 }else{
			$companyData = Company::find(User::get_companyID());
		}
        $Address = "";
        $Address .= !empty($companyData->Address1) ? $companyData->Address1 . ',' . PHP_EOL : '';
        $Address .= !empty($companyData->Address2) ? $companyData->Address2 . ',' . PHP_EOL : '';
        $Address .= !empty($companyData->Address3) ? $companyData->Address3 . ',' . PHP_EOL : '';
        $Address .= !empty($companyData->City) ? $companyData->City . ',' . PHP_EOL : '';
        $Address .= !empty($companyData->PostCode) ? $companyData->PostCode . ',' . PHP_EOL : '';
        $Address .= !empty($companyData->Country) ? $companyData->Country : '';
        return $Address;
    }
}