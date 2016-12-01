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
    //public static $invoice_status;
    public static $invoice_type = array(''=>'Select' ,self::INVOICE_OUT => 'Invoice Sent',self::INVOICE_IN=>'Invoice Received','All'=>'Both');
    public static $invoice_type_customer = array(''=>'Select' ,self::INVOICE_OUT => 'Invoice Received',self::INVOICE_IN=>'Invoice sent','All'=>'Both');

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
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $InvoiceID])->get();
            $InvoiceTaxRates = InvoiceTaxRate::where("InvoiceID",$InvoiceID)->orderby('InvoiceTaxRateID')->get();
            $Account = Account::find($Invoice->AccountID);
            $AccountBilling = AccountBilling::getBilling($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $CurrencySymbol =  Currency::getCurrencySymbol($Account->CurrencyId);
            $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($Invoice->AccountID);
            $PaymentDueInDays = AccountBilling::getPaymentDueInDays($Invoice->AccountID);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl) || AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key) == '') {
                $as3url =  public_path("/assets/images/250x100.png");
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }
            $logo_path = getenv('UPLOAD_PATH') . '/logo/' . $Account->CompanyId;
            @mkdir($logo_path, 0777, true);
            RemoteSSH::run("chmod -R 777 " . $logo_path);
            $logo = $logo_path  . '/'  . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));
            @chmod($logo,0777);

            $InvoiceTemplate->DateFormat = invoice_date_fomat($InvoiceTemplate->DateFormat);
            $file_name = 'Invoice--' .$Account->AccountName.'-' .date($InvoiceTemplate->DateFormat) . '.pdf';
            $htmlfile_name = 'Invoice--' .$Account->AccountName.'-' .date($InvoiceTemplate->DateFormat) . '.html';

			$print_type = 'Invoice';
            $body = View::make('invoices.pdf', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'CurrencyCode', 'logo','CurrencySymbol','print_type','AccountBilling','InvoiceTaxRates','PaymentDueInDays'))->render();

            $body = htmlspecialchars_decode($body);
            $footer = View::make('invoices.pdffooter', compact('Invoice','print_type'))->render();
            $footer = htmlspecialchars_decode($footer);

            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['INVOICE_UPLOAD'],$Account->CompanyId,$Invoice->AccountID) ;
             $destination_dir = getenv('UPLOAD_PATH') . '/'. $amazonPath;
			
            if (!file_exists($destination_dir)) {
                mkdir($destination_dir, 0777, true);
            } Log::info('destination_dir'); Log::info($destination_dir);
            RemoteSSH::run("chmod -R 777 " . $destination_dir);
            $file_name = \Nathanmac\GUID\Facades\GUID::generate() .'-'. $file_name;
            $htmlfile_name = \Nathanmac\GUID\Facades\GUID::generate() .'-'. $htmlfile_name;
            $local_file = $destination_dir .  $file_name; Log::info('local_file'); Log::info($local_file);

            $local_htmlfile = $destination_dir .  $htmlfile_name; Log::info('local_htmlfile'); Log::info($local_htmlfile);
            file_put_contents($local_htmlfile,$body);
            @chmod($local_htmlfile,0777);
            $footer_name = 'footer-'. \Nathanmac\GUID\Facades\GUID::generate() .'.html';
            $footer_html = $destination_dir.$footer_name;
            file_put_contents($footer_html,$footer);
            @chmod($footer_html,0777);
            $output= "";
            if(getenv('APP_OS') == 'Linux'){
                exec (base_path(). '/wkhtmltox/bin/wkhtmltopdf --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);

            }else{
                exec (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
            }
            @chmod($local_file,0777);
            Log::info($output);
            @unlink($local_htmlfile);
            @unlink($footer_html);
            if (file_exists($local_file)) {
                $fullPath = $amazonPath . basename($local_file); //$destinationPath . $file_name;
                if (AmazonS3::upload($local_file, $amazonPath)) {
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


}