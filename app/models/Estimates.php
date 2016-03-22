<?php

class Estimate extends \Eloquent {
	
    protected $connection 	= 	'sqlsrv2';
    protected $fillable 	= 	[];
    protected $guarded 		= 	array('EstimateID');
    protected $table 		= 	'tblEstimate';
    protected $primaryKey 	= 	"EstimateID";
    const  ESTIMATE_OUT 	= 	1;
    const  ESTIMATE_IN		=	2;
    const DRAFT 			= 	'draft';
    const SEND 				= 	'send';
    const ACCEPTED 			= 	'accepted';
    const ITEM_ESTIMATE 	=	1;
	const ESTIMATE_TEMPLATE =	2;
	
    //public static $estimate_status;
    public static $estimate_type = array(''=>'Select an Estimate Type' ,self::ESTIMATE_OUT => 'Estimate Sent',self::ESTIMATE_IN=>'Estimate Received','All'=>'Both');
    public static $estimate_type_customer = array(''=>'Select an Estimate Type' ,self::ESTIMATE_OUT => 'Estimate Received',self::ESTIMATE_IN=>'Estimate sent','All'=>'Both');

    public static function getEstimateEmailTemplate($data){

        $message = '[CompanyName] has sent you an estimate of [GrandTotal] [CurrencyCode], '. PHP_EOL. 'to download copy of your estimate please click the below link.';

        $message = str_replace("[CompanyName]",$data['CompanyName'],$message);
        $message = str_replace("[GrandTotal]",$data['GrandTotal'],$message);
        $message = str_replace("[CurrencyCode]",$data['CurrencyCode'],$message);
        return $message;
    }

    public static function getLastInvoiceDate($AccountID){

        /**
         *   Get EndDate from InvoiceDetail
         *      Where ProductType is USAGE = Product::USAGE OR SUBSCRIPTION = Product::SUBSCRIPTION
         */



        if($AccountID > 0) {

            $LastInvoiceDate = Account::where("AccountID",$AccountID)->pluck("LastInvoiceDate");
            if(!empty($LastInvoiceDate)) {
                return $LastInvoiceDate;
            }

            /*$LastInvoiceDate = Invoice::join('tblInvoiceDetail', 'tblInvoice.InvoiceID', '=', 'tblInvoiceDetail.InvoiceID')
                ->where("tblInvoice.AccountID", $AccountID)
                ->whereRaw("(tblInvoiceDetail.ProductType = " . Product::USAGE . ' OR ' . "tblInvoiceDetail.ProductType = " . Product::SUBSCRIPTION . ")")// Only take Invoice which has Usage Item
                ->orderby("tblInvoiceDetail.EndDate","desc")
                ->pluck("EndDate");

            return strtotime("Y-m-d", strtotime( "+1 Day", $LastInvoiceDate));*/
        }

    }

    public static function getNextInvoiceDate($AccountID){

        /**
         * Assumption : If Billing Cycle is 7 Days then Usage and Subscription both will be 7 Days and same for Monthly and other billing cycles..
        * */

        $Account = Account::select(["NextInvoiceDate","LastInvoiceDate","BillingStartDate"])->where("AccountID",$AccountID)->first()->toArray();

        $BillingCycle = Account::select(["BillingCycleType","BillingCycleValue"])->where("AccountID",$AccountID)->first()->toArray();
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

        if(isset($BillingCycle['BillingCycleType'])) {

            $BillingTimezone = CompanySetting::getKeyVal("BillingTimezone");

            if($BillingTimezone != 'Invalid Key'){
                date_default_timezone_set($BillingTimezone);
            }

            switch ($BillingCycle['BillingCycleType']) {
                case 'weekly':
                    if (!empty($BillingCycle['BillingCycleValue'])) {
                        $NextInvoiceDate = date("Y-m-d", strtotime("next " . $BillingCycle['BillingCycleValue'],$BillingStartDate));
                    }
                    break;
                case 'monthly':
                        $NextInvoiceDate = date("Y-m-d", strtotime("first day of next month ",$BillingStartDate));
                    break;
                case 'daily':
                        $NextInvoiceDate = date("Y-m-d", strtotime("+1 Days",$BillingStartDate));
                    break;
                case 'in_specific_days':
                    if (!empty($BillingCycle['BillingCycleValue'])) {
                            $NextInvoiceDate = date("Y-m-d", strtotime("+" . intval($BillingCycle['BillingCycleValue']) . " Day",$BillingStartDate));
                    }
                    break;
                case 'monthly_anniversary':
                        $NextInvoiceDate = date("Y-m-d", strtotime("+1 month +1 Day",$BillingStartDate));
                    break;
                case 'fortnightly':
                    $fortnightly_day = date("d", $BillingStartDate);
                    if($fortnightly_day > 15){
                        $NextInvoiceDate = date("Y-m-d", strtotime("first day of next month ",$BillingStartDate));
                    }else{
                        $NextInvoiceDate = date("Y-m-16", $BillingStartDate);
                    }
                    break;
                case 'quarterly':
                    $quarterly_month = date("m", $BillingStartDate);
                    if($quarterly_month < 4){
                        $NextInvoiceDate = date("Y-m-d", strtotime("first day of april ",$BillingStartDate));
                    }else if($quarterly_month > 3 && $quarterly_month < 7) {
                        $NextInvoiceDate = date("Y-m-d", strtotime("first day of july ",$BillingStartDate));
                    }else if($quarterly_month > 6 && $quarterly_month < 10) {
                        $NextInvoiceDate = date("Y-m-d", strtotime("first day of october ",$BillingStartDate));
                    }else if($quarterly_month > 9){
                        $NextInvoiceDate = date("Y-01-01", strtotime('+1 year ',$BillingStartDate));
                    }
                    break;
            }

            date_default_timezone_set(Config::get("app.timezone"));
        }

        return $NextInvoiceDate;

    }

    public static  function generate_pdf($EstimateID)
	{
        if($EstimateID>0)
		{
            $Estimate 			= 	Estimate::find($EstimateID);
            $EstimateDetail 	= 	EstimateDetail::where(["EstimateID" => $EstimateID])->get();
            $Account 			= 	Account::find($Estimate->AccountID);
            $Currency 			= 	Currency::find($Account->CurrencyId);
            $CurrencyCode 		= 	!empty($Currency)?$Currency->Code:'';
            $EstimateTemplate 	= 	InvoiceTemplate::find($Account->InvoiceTemplateID);
			
            if (empty($EstimateTemplate->CompanyLogoUrl) || AmazonS3::unSignedUrl($EstimateTemplate->CompanyLogoAS3Key) == '')
			{
                $as3url =  base_path().'/public/assets/images/250x100.png';
            }
			else
			{
                $as3url = (AmazonS3::unSignedUrl($EstimateTemplate->CompanyLogoAS3Key));
            }
			
            $logo = getenv('UPLOAD_PATH') . '/' . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));

            $EstimateTemplate->DateFormat 	= 	estimate_date_fomat($EstimateTemplate->DateFormat);
            $file_name 						= 	'Estimate--' .$Account->AccountName.'-' .date($EstimateTemplate->DateFormat) . '.pdf';
            $htmlfile_name 					= 	'Estimate--' .$Account->AccountName.'-' .date($EstimateTemplate->DateFormat) . '.html';

            $body 	= 	View::make('estimates.pdf', compact('Estimate', 'EstimateDetail', 'Account', 'EstimateTemplate', 'CurrencyCode', 'logo'))->render();
            $body 	= 	htmlspecialchars_decode($body);
            $footer = 	View::make('estimates.pdffooter', compact('Estimate'))->render();
            $footer = 	htmlspecialchars_decode($footer);

            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['ESTIMATE_UPLOAD'],$Account->CompanyId,$Estimate->AccountID) ;
            $destination_dir = getenv('UPLOAD_PATH') . '/'. $amazonPath;
            
			if (!file_exists($destination_dir))
			{
                mkdir($destination_dir, 0777, true);
            }
			
            $file_name 			= 	\Nathanmac\GUID\Facades\GUID::generate() .'-'. $file_name;
            $htmlfile_name 		= 	\Nathanmac\GUID\Facades\GUID::generate() .'-'. $htmlfile_name;
            $local_file 		= 	$destination_dir .  $file_name;
			$local_htmlfile 	= 	$destination_dir .  $htmlfile_name;
		
			file_put_contents($local_htmlfile,$body);

			$footer_name 		= 	'footer-'. \Nathanmac\GUID\Facades\GUID::generate() .'.html';
            $footer_html 		= 	$destination_dir.$footer_name;
			
            file_put_contents($footer_html,$footer);
            $output= "";
            if(getenv('APP_OS') == 'Linux'){
                exec (base_path(). '/wkhtmltox/bin/wkhtmltopdf --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
                Log::info(base_path(). '/wkhtmltox/bin/wkhtmltopdf --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);

            }else{
                exec (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
                Log::info (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
            }
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

    public static function get_estimate_status()
	{
        $Company 		= 	Company::find(User::get_companyID());
		
        $invoiceStatus 	= 	explode(',',$Company->InvoiceStatus);
       $invoicearray 	= 	array(
	   										''=>'Select Estimate Status',
	   										self::DRAFT=>'Draft',
											self::SEND=>'Sent',
											self::ACCEPTED=>"Accepted"
								);
	   
        foreach($invoiceStatus as $status)
		{
            $invoicearray[$status] = $status;
        }
		
        return $invoicearray;
    }
    public static function getFullEstimateNumber($Estimate,$Account)
	{
        $EstimateNumberPrefix = '';
        if(!empty($Account->EstimateTemplateID))
		{
            $EstimateNumberPrefix = InvoiceTemplate::find($Account->InvoiceTemplateID)->EstimateNumberPrefix;
        }
        return $EstimateNumberPrefix.$Estimate->EstimateNumber;
    }

}