<?php

class Invoice extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('InvoiceID');
    protected $table = 'tblInvoice';
    protected  $primaryKey = "InvoiceID";
    const  INVOICE_OUT = 1;
    const  INVOICE_IN= 2;
    const DRAFT = 'draft';
    const SEND = 'send';
    const AWAITING = 'awaiting';
    const CANCEL = 'cancel';
    const RECEIVED = 'received';
    const PAID = 'paid';
    const PARTIALLY_PAID = 'partially_paid';
    const ITEM_INVOICE =1;
    const POST = 'post';
	const EMAILTEMPLATE 		= "InvoiceSingleSend";
	
    //public static $invoice_status;
    public static $invoice_type = array(''=>'Select' ,self::INVOICE_OUT => 'Invoice Sent',self::INVOICE_IN=>'Invoice Received','All'=>'Both');
    public static $invoice_type_customer = array(''=>'Select' ,self::INVOICE_OUT => 'Invoice Received',self::INVOICE_IN=>'Invoice sent','All'=>'Both');
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
                                            '{Phone}'=>'Phone');

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

    public static  function generate_pdf($InvoiceID){  
        if($InvoiceID>0) {
            $Invoice = Invoice::find($InvoiceID);

            $language=Account::where("AccountID", $Invoice->AccountID)
                                ->join('tblLanguage', 'tblLanguage.LanguageID', '=', 'tblAccount.LanguageID')
                                ->join('tblTranslation', 'tblTranslation.LanguageID', '=', 'tblAccount.LanguageID')
                                ->select('tblLanguage.ISOCode', 'tblTranslation.Language', 'tblLanguage.is_rtl')
                                ->first();

            App::setLocale($language->ISOCode);

            $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $InvoiceID])->get();
            $InvoiceTaxRates = InvoiceTaxRate::where(["InvoiceID"=>$InvoiceID,"InvoiceTaxType"=>0])->orderby('InvoiceTaxRateID')->get();
			//$InvoiceAllTaxRates = InvoiceTaxRate::where(["InvoiceID"=>$InvoiceID,"InvoiceTaxType"=>1])->orderby('InvoiceTaxRateID')->get();
			$InvoiceAllTaxRates = DB::connection('sqlsrv2')->table('tblInvoiceTaxRate')
                    ->select('TaxRateID', 'Title', DB::Raw('sum(TaxAmount) as TaxAmount'))
                    ->where("InvoiceID", $InvoiceID)
                    ->orderBy("InvoiceTaxRateID", "asc")
                    ->groupBy("TaxRateID")                   
                    ->get();
			$Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $CurrencySymbol =  Currency::getCurrencySymbol($Account->CurrencyId);
            if(!empty($Invoice->RecurringInvoiceID) && $Invoice->RecurringInvoiceID > 0){
                $recurringInvoice = RecurringInvoice::find($Invoice->RecurringInvoiceID);
                $billingClass = BillingClass::where('BillingClassID',$recurringInvoice->BillingClassID)->first();
                $InvoiceTemplateID = $billingClass->InvoiceTemplateID;
                $PaymentDueInDays = $billingClass->PaymentDueInDays;
            }else{
				$BillingClassID = self::GetInvoiceBillingClass($Invoice);
				$InvoiceTemplateID = self::GetInvoiceTemplateID($Invoice);
                $PaymentDueInDays = BillingClass::getPaymentDueInDays($BillingClassID);
            }

            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl) || AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key,$Account->CompanyId) == '') {
                $as3url =  public_path("/assets/images/250x100.png");
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key,$Account->CompanyId));
            }
            $logo_path = CompanyConfiguration::get('UPLOAD_PATH',$Account->CompanyId) . '/logo/' . $Account->CompanyId;
            @mkdir($logo_path, 0777, true);
            RemoteSSH::run("chmod -R 777 " . $logo_path);
            $logo = $logo_path  . '/'  . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));
            @chmod($logo,0777);

            $InvoiceTemplate->DateFormat = invoice_date_fomat($InvoiceTemplate->DateFormat);

            $common_name = Str::slug($Account->AccountName.'-'.$Invoice->FullInvoiceNumber.'-'.date($InvoiceTemplate->DateFormat,strtotime($Invoice->IssueDate)).'-'.$InvoiceID);

            $file_name = 'Invoice--' .$common_name . '.pdf';
            $htmlfile_name = 'Invoice--' .$common_name . '.html';

			$print_type = 'Invoice';
            $body = View::make('invoices.pdf', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'CurrencyCode', 'logo','CurrencySymbol','print_type','InvoiceTaxRates','PaymentDueInDays','InvoiceAllTaxRates','language'))->render();

            $body = htmlspecialchars_decode($body);  
            $footer = View::make('invoices.pdffooter', compact('Invoice','print_type'))->render();
            $footer = htmlspecialchars_decode($footer);

            $header = View::make('invoices.pdfheader', compact('Invoice','print_type'))->render();
            $header = htmlspecialchars_decode($header);

            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['INVOICE_UPLOAD'],$Account->CompanyId,$Invoice->AccountID) ;
             $destination_dir = CompanyConfiguration::get('UPLOAD_PATH',$Account->CompanyId) . '/'. $amazonPath;
			
            if (!file_exists($destination_dir)) {
                mkdir($destination_dir, 0777, true);
            } 
            RemoteSSH::run("chmod -R 777 " . $destination_dir);

            $local_file = $destination_dir .  $file_name; 

            $local_htmlfile = $destination_dir .  $htmlfile_name; 
            file_put_contents($local_htmlfile,$body);
            @chmod($local_htmlfile,0777);
            $footer_name = 'footer-'. $common_name .'.html';
            $footer_html = $destination_dir.$footer_name;
            file_put_contents($footer_html,$footer);
            @chmod($footer_html,0777);

            $header_name = 'header-'. $common_name .'.html';
            $header_html = $destination_dir.$header_name;
            file_put_contents($header_html,$header);
            @chmod($footer_html,0777);

            $output= "";
           /* if(getenv('APP_OS') == 'Linux'){
                exec (base_path(). '/wkhtmltox/bin/wkhtmltopdf --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
            }else{
                exec (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe  --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" -- footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
            }*/
			 if(getenv('APP_OS') == 'Linux'){
                exec (base_path(). '/wkhtmltox/bin/wkhtmltopdf --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);

                 if(CompanySetting::getKeyVal('UseDigitalSignature', $Account->CurrencyId)!="Invalid Key"){
                     $newlocal_file = $destination_dir . str_replace(".pdf","-signature.pdf",$file_name);
                     $signaturePath = AmazonS3::preSignedUrl(AmazonS3::$dir['DIGITAL_SIGNATURE_KEY']);
					 $mypdfsignerOutput=RemoteSSH::run('mypdfsigner -i '.$local_file.' -o '.$newlocal_file.' -z '.$signaturePath.'mypdfsigner.conf -v -c -q');
					 Log::info($mypdfsignerOutput);
                     if(file_exists($newlocal_file)){
                         RemoteSSH::run('rm '.$local_file);
                         RemoteSSH::run('mv '.$newlocal_file.' '.$local_file);						 
                     }
                 }

            }else{
                exec (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
            }
            @chmod($local_file,0777);
            Log::info($output); 
            @unlink($local_htmlfile);
            @unlink($footer_html);
            @unlink($header_html);
            if (file_exists($local_file)) {
                $fullPath = $amazonPath . basename($local_file); //$destinationPath . $file_name;
                if (AmazonS3::upload($local_file, $amazonPath,$Account->CompanyId)) {
                    return $fullPath;
                }
            }
            return '';
        }
    }

    public static function get_invoice_status(){
        $Company = Company::find(User::get_companyID());
        $invoiceStatus = explode(',',$Company->InvoiceStatus);
       $invoicearray = array(''=>'Select Invoice Status',self::DRAFT=>'Draft',self::SEND=>'Sent',self::AWAITING=>'Awaiting Approval',self::CANCEL=>'Cancel',self::PAID=>'Paid',self::PARTIALLY_PAID=>'Partially Paid',self::POST=>'Post');
        foreach($invoiceStatus as $status){
            $invoicearray[$status] = $status;
        }
        return $invoicearray;
    }
    /**
     * not in use
    */
    public static function getFullInvoiceNumber($Invoice,$AccountBilling){
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
			}elseif(!empty($Invoice->RecurringInvoiceID) && (RecurringInvoice::where(["RecurringInvoiceID"=>$Invoice->RecurringInvoiceID])->count())>0){

                $InvoiceBillingClass = RecurringInvoice::where(["RecurringInvoiceID"=>$Invoice->RecurringInvoiceID])->pluck('BillingClassID');
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
            $isAccountUsageBilled = DB::connection('sqlsrv2')->select("SELECT COUNT(inv.InvoiceID) as count  FROM tblInvoice inv LEFT JOIN tblInvoiceDetail invd  ON invd.InvoiceID = inv.InvoiceID WHERE inv.CompanyID = " . $CompanyID . " AND inv.AccountID = " . $AccountID . " AND (('" . $StartDate . "' BETWEEN invd.StartDate AND invd.EndDate) OR('" . $EndDate . "' BETWEEN invd.StartDate AND invd.EndDate) OR (invd.StartDate BETWEEN '" . $StartDate . "' AND '" . $EndDate . "') ) and invd.ProductType = " . Product::USAGE . " and inv.InvoiceType = " . Invoice::INVOICE_OUT . " and inv.InvoiceStatus != '" . Invoice::CANCEL."' AND inv.ServiceID = $ServiceID");

            if (isset($isAccountUsageBilled[0]->count) && $isAccountUsageBilled[0]->count == 0) {
                return false;
            }
        }
        return true;
    }
}