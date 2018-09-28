<?php

class CreditNotes extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('CreditNotesID');
    protected $table = 'tblCreditNotes';
    protected  $primaryKey = "CreditNotesID";
    const  INVOICE_OUT = 1;
    const  INVOICE_IN= 2;
    const OPEN = 'open';
    const CLOSE = 'close';
    const DRAFT = 'draft';
    const SEND = 'send';
    const AWAITING = 'awaiting';
    const CANCEL = 'cancel';
    const RECEIVED = 'received';
    const PAID = 'paid';
    const PARTIALLY_PAID = 'partially_paid';
    const ITEM_INVOICE =1;
	const EMAILTEMPLATE 		= "CreditNotesSingleSend";
	
    //public static $invoice_status;
    //public static $invoice_type = array(''=>'Select' ,self::INVOICE_OUT => 'Invoice Sent',self::INVOICE_IN=>'Invoice Received','All'=>'Both');
    public static $creditnotes_status_customer = array(self::OPEN => 'Open',self::CLOSE=>'Close','All'=>'Both');
    public static $invoice_company_info = array(''=>'Select Company Info' ,'companyname' => 'Company Name','companyaddress'=>'Company Address','companyvatno'=>'Company Vat Number','companyemail'=>'Company Email');
    public static $invoice_account_info = array(''=>'Select Account Info' ,'{AccountName}' => 'Account Name',
                                            '{FirstName}'=>'First Name',
                                            '{LastName}'=>'Last Name',
                                            '{AccountNumber}'=>'Account Number',
                                            '{Address1}'=>'Address1',
                                            '{Address2}'=>'Address2',
                                            '{Address3}'=>'Address3',
                                            '{City}'=>'City',
                                            '{PostCode}'=>'PostCode',
                                            '{Country}'=>'Country',
                                            '{VatNumber}'=>'Vat Number',
                                            '{NominalCode}'=>'Nominal Code',
                                            '{Email}'=>'Email',
                                            '{Phone}'=>'Phone',
                                            '{AccountBalance}'=>'Account Balance');

    public static function multiLang_init(){
        Invoice::$invoice_type_customer = array(''=>cus_lang("DROPDOWN_OPTION_SELECT") ,self::INVOICE_OUT => cus_lang("CUST_PANEL_PAGE_INVOICE_FILTER_FIELD_TYPE_DDL_INVOICE_RECEIVED"),self::INVOICE_IN=>cus_lang("CUST_PANEL_PAGE_INVOICE_FILTER_FIELD_TYPE_DDL_INVOICE_SENT"),'All'=>cus_lang("CUST_PANEL_PAGE_INVOICE_FILTER_FIELD_TYPE_DDL_BOTH"));
    }

    public static function getInvoiceEmailTemplate($data){

        $message = '[CompanyName] has sent you an invoice of [GrandTotal] [CurrencyCode], '. PHP_EOL. 'to download copy of your invoice please click the below link.';

        $message = str_replace("[CompanyName]",$data['CompanyName'],$message);
        $message = str_replace("[GrandTotal]",$data['GrandTotal'],$message);
        $message = str_replace("[CurrencyCode]",$data['CurrencyCode'],$message);
        return $message;
    }

    public static function getNextInvoiceDate($AccountID){

        /**
         * Assumption : If Billing Cycle is 7 Days then Usage and Subscription both will be 7 Days and same for Monthly and other billing cycles..
        * */

        //set company billing timezone


        $Account = AccountBilling::select(["NextInvoiceDate","LastInvoiceDate","BillingStartDate"])->where("AccountID",$AccountID)->first()->toArray();

        $BillingCycle = AccountBilling::select(["BillingCycleType","BillingCycleValue"])->where("AccountID",$AccountID)->first()->toArray();
                        //"weekly"=>"Weekly", "monthly"=>"Monthly" , "daily"=>"Daily", "in_specific_days"=>"In Specific days", "monthly_anniversary"=>"Monthly anniversary");

        $NextInvoiceDate = "";
        $BillingStartDate = "";
        if(!empty($Account['LastInvoiceDate'])) {
            $BillingStartDate = strtotime($Account['LastInvoiceDate']);
        }else if(!empty($Account['BillingStartDate'])) {
            $BillingStartDate = strtotime($Account['BillingStartDate']);
        }else{
            return '';
        }

        $NextInvoiceDate = next_billing_date($BillingCycle['BillingCycleType'],$BillingCycle['BillingCycleValue'],$BillingStartDate);

        return $NextInvoiceDate;

    }

    public static  function generate_pdf($CreditNotesID)
    {
        if($CreditNotesID>0)
        {
            $CreditNotes 				= 	CreditNotes::find($CreditNotesID);
            $CreditNotesDetail 		= 	CreditNotesDetail::where(["CreditNotesID" => $CreditNotesID])->get();
            $CreditNotesDetailItems 	= 	CreditNotesDetail::where(["CreditNotesID" => $CreditNotesID,"ProductType"=>Product::ITEM])->get();
            $CreditNotesDetailISubscription 	= 	CreditNotesDetail::where(["CreditNotesID" => $CreditNotesID,"ProductType"=>Product::SUBSCRIPTION])->get();

            $CompanyID = $CreditNotes->CompanyID;

            $CreditNotesItemTaxRates = DB::connection('sqlsrv2')->table('tblCreditNotesTaxRate')->where(["CreditNotesID"=>$CreditNotesID,"CreditNotesTaxType"=>0])->orderby('CreditNotesTaxRateID')->get();
            $CreditNotesSubscriptionTaxRates = DB::connection('sqlsrv2')->table('tblCreditNotesTaxRate')->where(["CreditNotesID"=>$CreditNotesID,"CreditNotesTaxType"=>2])->orderby('CreditNotesTaxRateID')->get();
            //$CreditNotesAllTaxRates = DB::connection('sqlsrv2')->table('tblCreditNotesTaxRate')->where(["CreditNotesID"=>$CreditNotesID,"CreditNotesTaxType"=>1])->orderby('CreditNotesTaxRateID')->get();
            $taxes 	  = TaxRate::getTaxRateDropdownIDListForInvoice(0,$CompanyID);
            $CreditNotesAllTaxRates = DB::connection('sqlsrv2')->table('tblCreditNotesTaxRate')
                ->select('TaxRateID', 'Title', DB::Raw('sum(TaxAmount) as TaxAmount'))
                ->where("CreditNotesID", $CreditNotesID)
                ->where("CreditNotesTaxType", 1)
                ->orderBy("CreditNotesTaxRateID", "asc")
                ->groupBy("TaxRateID")
                ->get();
            $Account 			= 	Account::find($CreditNotes->AccountID);
            // $AccountBilling 	=   AccountBilling::getBilling($CreditNotes->AccountID);
            $Currency 			= 	Currency::find($Account->CurrencyId);
            $CurrencyCode 		= 	!empty($Currency)?$Currency->Code:'';
            $CurrencySymbol 	=   Currency::getCurrencySymbol($Account->CurrencyId);

            //$InvoiceTemplateID  =	AccountBilling::getInvoiceTemplateID($CreditNotes->AccountID);
            $InvoiceTemplateID  =   self::GetInvoiceTemplateID($CreditNotes);
            $CreditNotesTemplate 	= 	InvoiceTemplate::find($InvoiceTemplateID);

            if (empty($CreditNotesTemplate->CompanyLogoUrl) || AmazonS3::unSignedUrl($CreditNotesTemplate->CompanyLogoAS3Key,$CompanyID) == '')
            {
                $as3url =  base_path().'/public/assets/images/250x100.png';
            }
            else
            {
                $as3url = (AmazonS3::unSignedUrl($CreditNotesTemplate->CompanyLogoAS3Key,$CompanyID));
            }
            $logo_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID) . '/logo/' . $CompanyID;
            @mkdir($logo_path, 0777, true);
            RemoteSSH::run("chmod -R 777 " . $logo_path);
            $logo = $logo_path  . '/'  . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));

            $CreditNotesTemplate->DateFormat 	= 	invoice_date_fomat($CreditNotesTemplate->DateFormat);
            $file_name 						= 	'CreditNotes--' .$Account->AccountName.'-' .date($CreditNotesTemplate->DateFormat) . '.pdf';
            $htmlfile_name 					= 	'CreditNotes--' .$Account->AccountName.'-' .date($CreditNotesTemplate->DateFormat) . '.html';
            $MultiCurrencies=array();
            $RoundChargesAmount = get_round_decimal_places($Account->AccountID);
            if($CreditNotesTemplate->ShowTotalInMultiCurrency==1){
                $MultiCurrencies = Invoice::getTotalAmountInOtherCurrency($Account->CompanyId,$Account->CurrencyId,$CreditNotes->GrandTotal,$RoundChargesAmount);
            }
            $print_type = 'CreditNotes';
            $body 	= 	View::make('creditnotes.pdf', compact('CreditNotes', 'CreditNotesDetail', 'Account', 'CreditNotesTemplate', 'CurrencyCode', 'logo','CurrencySymbol','print_type','CreditNotesItemTaxRates','CreditNotesSubscriptionTaxRates','CreditNotesAllTaxRates','taxes',"CreditNotesDetailItems","CreditNotesDetailISubscription","MultiCurrencies"))->render();
            $body 	= 	htmlspecialchars_decode($body);
            $footer = 	View::make('creditnotes.pdffooter', compact('CreditNotes','print_type'))->render();
            $footer = 	htmlspecialchars_decode($footer);

            $header = View::make('creditnotes.pdfheader', compact('CreditNotes','print_type'))->render();
            $header = htmlspecialchars_decode($header);

            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['CREDITNOTES_UPLOAD'],$Account->CompanyId,$CreditNotes->AccountID) ;
            $destination_dir = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID) . '/'. $amazonPath;

            if (!file_exists($destination_dir))
            {
                mkdir($destination_dir, 0777, true);
            }
            RemoteSSH::run("chmod -R 777 " . $destination_dir);

            $file_name 			= 	\Nathanmac\GUID\Facades\GUID::generate() .'-'. $file_name;
            $htmlfile_name 		= 	\Nathanmac\GUID\Facades\GUID::generate() .'-'. $htmlfile_name;
            $local_file 		= 	$destination_dir .  $file_name;
            $local_htmlfile 	= 	$destination_dir .  $htmlfile_name;

            file_put_contents($local_htmlfile,$body);

            $footer_name 		= 	'footer-'. \Nathanmac\GUID\Facades\GUID::generate() .'.html';
            $footer_html 		= 	$destination_dir.$footer_name;
            file_put_contents($footer_html,$footer);

            $header_name = 'header-'. \Nathanmac\GUID\Facades\GUID::generate() .'.html';
            $header_html = $destination_dir.$header_name;
            file_put_contents($header_html,$header);


            $output= "";
            if(getenv('APP_OS') == 'Linux'){
                exec (base_path(). '/wkhtmltox/bin/wkhtmltopdf --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);

            }else{
                exec (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
            }
            Log::info($output);
            //  @unlink($local_htmlfile);
            //  @unlink($footer_html);
            //  @unlink($header_html);
            if (file_exists($local_file)) {
                $fullPath = $amazonPath . basename($local_file); //$destinationPath . $file_name;
                if (AmazonS3::upload($local_file, $amazonPath,$CompanyID)) {
                    return $fullPath;
                }
            }
            return '';
        }
    }

    public static function get_creditnotes_status(){
        $Company = Company::find(User::get_companyID());
        $invoiceStatus = explode(',',$Company->InvoiceStatus);
        $creditnotesarray = array(''=>'Select CreditNotes Status',self::OPEN=>'Open',self::CLOSE=>'Close');
        foreach($invoiceStatus as $status){
            $creditnotesarray[$status] = $status;
        }
        return $creditnotesarray;
    }
    /**
     * not in use
    */
    public static function getFullCreditNotesNumber($Invoice,$AccountBilling){
        $InvoiceNumberPrefix = '';
        if(!empty($AccountBilling->InvoiceTemplateID)) {
            $InvoiceNumberPrefix = InvoiceTemplate::find($AccountBilling->InvoiceTemplateID)->InvoiceNumberPrefix;
        }
        return $InvoiceNumberPrefix.$Invoice->InvoiceNumber;
    }

    public static function getCookie($name,$val=''){
        $cookie = 1;
        if(isset($_COOKIE[$name])){
            $cookie = $_COOKIE[$name];
        }
        return $cookie;
    }

    public static function setCookie($name,$value){
        setcookie($name,$value,strtotime( '+30 days' ),'/');
    }


    // for sample invoice template pdf
    public static function getInvoiceTo($Invoiceto){
        $Invoiceto = str_replace('{','',$Invoiceto);
        $Invoiceto = str_replace('}','',$Invoiceto);
        return $Invoiceto;
    }

    public static function create_accountdetails($AccountDetail){
        $Account = Account::find($AccountDetail->AccountID);
        $replace_array = array();
        $replace_array['FirstName'] = $Account->FirstName;
        $replace_array['LastName'] = $Account->LastName;
        $replace_array['AccountName'] = $Account->AccountName;
        $replace_array['AccountNumber'] = $Account->Number;
        $replace_array['VatNumber'] = $Account->VatNumber;
        $replace_array['NominalCode'] = $Account->NominalAnalysisNominalAccountNumber;
        $replace_array['Email'] = $Account->Email;
        $replace_array['Address1'] = $Account->Address1;
        $replace_array['Address2'] = $Account->Address2;
        $replace_array['Address3'] = $Account->Address3;
        $replace_array['City'] = $Account->City;
        $replace_array['State'] = $Account->State;
        $replace_array['PostCode'] = $Account->PostCode;
        $replace_array['Country'] = $Account->Country;
        $replace_array['Phone'] = $Account->Phone;
        $replace_array['Fax'] = $Account->Fax;
        $replace_array['Website'] = $Account->Website;
        $replace_array['Currency'] = Currency::getCurrencySymbol($Account->CurrencyId);
        $replace_array['CompanyName'] = Company::getName($Account->CompanyId);
        $replace_array['CompanyVAT'] = Company::getCompanyField($Account->CompanyId,"VAT");
        $replace_array['CompanyAddress'] = Company::getCompanyFullAddress($Account->CompanyId);

        return $replace_array;
    }


    public static function getInvoiceToByAccount($Message,$replace_array){
        $extra = [
            '{AccountName}',
            '{FirstName}',
            '{LastName}',
            '{AccountNumber}',
            '{VatNumber}',
            '{VatNumber}',
            '{NominalCode}',
            '{Phone}',
            '{Fax}',
            '{Website}',
            '{Email}',
            '{Address1}',
            '{Address2}',
            '{Address3}',
            '{City}',
            '{State}',
            '{PostCode}',
            '{Country}',
            '{Currency}',
            '{CompanyName}',
            '{CompanyVAT}',
            '{CompanyAddress}'
        ];

        foreach($extra as $item){
            $item_name = str_replace(array('{','}'),array('',''),$item);
            if(array_key_exists($item_name,$replace_array)) {
                $Message = str_replace($item,$replace_array[$item_name],$Message);
            }
        }
        return $Message;
    }
	
	public static function GetInvoiceBillingClass($Invoice)
	{
			if(!empty($Invoice->BillingClassID))
			{
				$InvoiceBillingClass	 =	 $Invoice->BillingClassID;
			}elseif(!empty($Invoice->RecurringCreditNotesID) && (RecurringInvoice::where(["RecurringCreditNotesID"=>$Invoice->RecurringCreditNotesID])->count())>0){

                $InvoiceBillingClass = RecurringInvoice::where(["RecurringCreditNotesID"=>$Invoice->RecurringCreditNotesID])->pluck('BillingClassID');
            }
			else
			{
				$AccountBilling 	  	=  	 AccountBilling::getBilling($Invoice->AccountID);
				$InvoiceBillingClass 	= 	 $AccountBilling->BillingClassID;	
			}	
			return $InvoiceBillingClass;
	}
	
	public static function GetInvoiceTemplateID($Invoice){
	  	$billingclass = 	self::GetInvoiceBillingClass($Invoice);
		return BillingClass::getInvoiceTemplateID($billingclass);
	}

    public static function checkIfAccountUsageAlreadyBilled($CompanyID,$AccountID,$StartDate,$EndDate,$ServiceID){

        if(!empty($CompanyID) && !empty($AccountID) && !empty($StartDate) && !empty($EndDate) ){

            //Check if Invoice Usage is alrady Created.
            $isAccountUsageBilled = DB::connection('sqlsrv2')->select("SELECT COUNT(inv.CreditNotesID) as count  FROM tblCreditNotes inv LEFT JOIN tblCreditNotesDetail invd  ON invd.CreditNotesID = inv.CreditNotesID WHERE inv.CompanyID = " . $CompanyID . " AND inv.AccountID = " . $AccountID . " AND (('" . $StartDate . "' BETWEEN invd.StartDate AND invd.EndDate) OR('" . $EndDate . "' BETWEEN invd.StartDate AND invd.EndDate) OR (invd.StartDate BETWEEN '" . $StartDate . "' AND '" . $EndDate . "') ) and invd.ProductType = " . Product::USAGE . " and inv.InvoiceType = " . Invoice::INVOICE_OUT . " and inv.InvoiceStatus != '" . Invoice::CANCEL."' AND inv.ServiceID = $ServiceID");

            if (isset($isAccountUsageBilled[0]->count) && $isAccountUsageBilled[0]->count == 0) {
                return false;
            }
        }
        return true;
    }

    public static function getInvoiceTaxRateAmount($CreditNotesID,$RoundChargesAmount){

        $InvoiceTaxRateAmount = 0;

        $InvoiceTaxRates = InvoiceTaxRate::where(["CreditNotesID" => $CreditNotesID])->get();

        if(!empty($InvoiceTaxRates) && count($InvoiceTaxRates)>0) {
            foreach ($InvoiceTaxRates as $InvoiceTaxRate) {
                $Title = $InvoiceTaxRate->Title;
                $TaxRateID = $InvoiceTaxRate->TaxRateID;
                $TaxAmount = number_format($InvoiceTaxRate->TaxAmount,$RoundChargesAmount, '.', '');
                $InvoiceTaxRateAmount+=	$TaxAmount;
            }
        }

        Log::info('InvoiceTaxAmount '.$InvoiceTaxRateAmount);

        return $InvoiceTaxRateAmount;
    }

    public static function GetCreditNotesBillingClass($CreditNotes)
    {
        if(isset($CreditNotes->BillingClassID))
        {
            $CreditNotesBillingClass	 =	 $CreditNotes->BillingClassID;
        }
        else
        {
            $AccountBilling 	  	=  	 AccountBilling::getBilling($CreditNotes->AccountID);
            $CreditNotesBillingClass 	= 	 $AccountBilling->BillingClassID;
        }
        return $CreditNotesBillingClass;
    }

    public static function get_round_decimal_places($CompanyID = 0,$AccountID = 0,$ServiceID=0) {
        $RoundChargesAmount = 2;
        if($AccountID>0){
            $RoundChargesAmount = AccountBilling::getRoundChargesAmount($AccountID,$ServiceID);
        }

        if (empty($RoundChargesAmount)) {
            $value = CompanySetting::getKeyVal($CompanyID,'RoundChargesAmount');
            $RoundChargesAmount = ($value !='Invalid Key')?$value:2;
        }
        return $RoundChargesAmount;
    }

    public static function CheckInvoiceFullPaid($CreditNotesID,$CompanyID){
        $Response = false;
        $InvoiceTotal = '';
        $PaymentTotal = '';
        if(!empty($CreditNotesID)){
            $Invoice = Invoice::find($CreditNotesID);
            $InvoiceTotal = $Invoice->GrandTotal;

            $PaymentTotal = Payment::where(['CompanyID' =>$CompanyID, 'CreditNotesID' => $CreditNotesID, 'Recall' => '0', 'Status' =>'Approved'])->sum('Amount');

            log::info('Invoice Total '.$InvoiceTotal);
            log::info('Payment Total '.$PaymentTotal);

            if(!empty($InvoiceTotal) && !empty($PaymentTotal) && $InvoiceTotal == $PaymentTotal){
                log::info('Total Matching');
                $Response = true;

            }
        }
        return $Response;
    }
}