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
    const REJECTED 			= 	'rejected';
    const ITEM_ESTIMATE 	=	1;
	const ESTIMATE_TEMPLATE =	2;
	const EMAILTEMPLATE 		= "EstimateSingleSend";
	const EMAILTEMPLATEACCEPT 	= "EstimateSingleAccept";
	const EMAILTEMPLATEREJECT 	= "EstimateSingleReject";
	const EMAILTEMPLATECOMMENT 	= "EstimateSingleComment";
    //public static $estimate_status;
    public static $estimate_type = array(''=>'Select' ,self::ESTIMATE_OUT => 'Estimate Sent',self::ESTIMATE_IN=>'Estimate Received','All'=>'Both');
    public static $estimate_type_customer = array(''=>'Select' ,self::ESTIMATE_OUT => 'Estimate Received',self::ESTIMATE_IN=>'Estimate sent','All'=>'Both');

    public static function getEstimateEmailTemplate($data){

        $message = '[CompanyName] has sent you an estimate of [GrandTotal] [CurrencyCode], '. PHP_EOL. 'to download copy of your estimate please click the below link.';

        $message = str_replace("[CompanyName]",$data['CompanyName'],$message);
        $message = str_replace("[GrandTotal]",$data['GrandTotal'],$message);
        $message = str_replace("[CurrencyCode]",$data['CurrencyCode'],$message);
        return $message;
    }

    public static  function generate_pdf($EstimateID)
	{
        if($EstimateID>0)
		{
            $Estimate 				= 	Estimate::find($EstimateID);
            $EstimateDetail 		= 	EstimateDetail::where(["EstimateID" => $EstimateID])->get();
			$EstimateDetailItems 	= 	EstimateDetail::where(["EstimateID" => $EstimateID,"ProductType"=>Product::ITEM])->get();
			$EstimateDetailISubscription 	= 	EstimateDetail::where(["EstimateID" => $EstimateID,"ProductType"=>Product::SUBSCRIPTION])->get();

            $CompanyID = $Estimate->CompanyID;
			
            $EstimateItemTaxRates = DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->where(["EstimateID"=>$EstimateID,"EstimateTaxType"=>0])->orderby('EstimateTaxRateID')->get();
			  $EstimateSubscriptionTaxRates = DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->where(["EstimateID"=>$EstimateID,"EstimateTaxType"=>2])->orderby('EstimateTaxRateID')->get();
			//$EstimateAllTaxRates = DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->where(["EstimateID"=>$EstimateID,"EstimateTaxType"=>1])->orderby('EstimateTaxRateID')->get();
            $taxes 	  = TaxRate::getTaxRateDropdownIDListForInvoice(0,$CompanyID);
			$EstimateAllTaxRates = DB::connection('sqlsrv2')->table('tblEstimateTaxRate')
                    ->select('TaxRateID', 'Title', DB::Raw('sum(TaxAmount) as TaxAmount'))
                    ->where("EstimateID", $EstimateID)
                    ->where("EstimateTaxType", 1)
                    ->orderBy("EstimateTaxRateID", "asc")
                    ->groupBy("TaxRateID")                   
                    ->get();
            $Account 			= 	Account::find($Estimate->AccountID);
           // $AccountBilling 	=   AccountBilling::getBilling($Estimate->AccountID);
            $Currency 			= 	Currency::find($Account->CurrencyId);
            $CurrencyCode 		= 	!empty($Currency)?$Currency->Code:'';
			$CurrencySymbol 	=   Currency::getCurrencySymbol($Account->CurrencyId);
            
			//$InvoiceTemplateID  =	AccountBilling::getInvoiceTemplateID($Estimate->AccountID);
			$InvoiceTemplateID  =   self::GetEstimateInvoiceTemplateID($Estimate);
            $EstimateTemplate 	= 	InvoiceTemplate::find($InvoiceTemplateID);
			
            if (empty($EstimateTemplate->CompanyLogoUrl) || AmazonS3::unSignedUrl($EstimateTemplate->CompanyLogoAS3Key,$CompanyID) == '')
			{
                $as3url =  base_path().'/public/assets/images/250x100.png';
            }
			else
			{
                $as3url = (AmazonS3::unSignedUrl($EstimateTemplate->CompanyLogoAS3Key,$CompanyID));
            }
            $logo_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID) . '/logo/' . $CompanyID;
            @mkdir($logo_path, 0777, true);
            RemoteSSH::run("chmod -R 777 " . $logo_path);
            $logo = $logo_path  . '/'  . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));

            $EstimateTemplate->DateFormat 	= 	estimate_date_fomat($EstimateTemplate->DateFormat);
            $file_name 						= 	'Estimate--' .$Account->AccountName.'-' .date($EstimateTemplate->DateFormat) . '.pdf';
            $htmlfile_name 					= 	'Estimate--' .$Account->AccountName.'-' .date($EstimateTemplate->DateFormat) . '.html';
			$print_type = 'Estimate';

            $MultiCurrencies=array();
            $RoundChargesAmount = get_round_decimal_places($Account->AccountID);
            if($EstimateTemplate->ShowTotalInMultiCurrency==1){
                $MultiCurrencies = Invoice::getTotalAmountInOtherCurrency($Account->CompanyId,$Account->CurrencyId,$Estimate->GrandTotal,$RoundChargesAmount);
            }
            $body 	= 	View::make('estimates.pdf', compact('Estimate', 'EstimateDetail', 'Account', 'EstimateTemplate', 'CurrencyCode', 'logo','CurrencySymbol','print_type','EstimateItemTaxRates','EstimateSubscriptionTaxRates','EstimateAllTaxRates','taxes',"EstimateDetailItems","EstimateDetailISubscription","MultiCurrencies"))->render();
            $body 	= 	htmlspecialchars_decode($body); 
            $footer = 	View::make('estimates.pdffooter', compact('Estimate','print_type'))->render();
            $footer = 	htmlspecialchars_decode($footer);

            $header = View::make('estimates.pdfheader', compact('Estimate','print_type'))->render();
            $header = htmlspecialchars_decode($header);
			
            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['ESTIMATE_UPLOAD'],$Account->CompanyId,$Estimate->AccountID) ;
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

    public static function get_estimate_status()
	{
        $Company 		= 	Company::find(User::get_companyID());
		
        $invoiceStatus 	= 	explode(',',$Company->InvoiceStatus);
       $invoicearray 	= 	array(
	   										''=>'Select Estimate Status',
	   										self::DRAFT=>'Draft',
											self::SEND=>'Sent',
											self::ACCEPTED=>"Accepted",
											self::REJECTED=>"Rejected"
								);
	   
        foreach($invoiceStatus as $status)
		{
            $invoicearray[$status] = $status;
        }
		
        return $invoicearray;
    }

    public static function get_customer_estimate_status($CompanyID)
    {
        $Company 		= 	Company::find($CompanyID);

        $invoiceStatus 	= 	explode(',',$Company->InvoiceStatus);
        $invoicearray 	= 	array(
            ''=>'Select Estimate Status',
            self::DRAFT=>'Draft',
            self::SEND=>'Sent',
            self::ACCEPTED=>"Accepted",
            self::REJECTED=>"Rejected"
        );

        foreach($invoiceStatus as $status)
        {
            $invoicearray[$status] = $status;
        }

        return $invoicearray;
    }

    public static function getFullEstimateNumber($Estimate,$InvoiceTemplateID)
	{
        $EstimateNumberPrefix = '';
        if(!empty($InvoiceTemplateID))
		{
             $EstimateNumberPrefix = InvoiceTemplate::find($InvoiceTemplateID)->EstimateNumberPrefix;
        }
        return $EstimateNumberPrefix.$Estimate->EstimateNumber;
    }
	
	public static function GetEstimateBillingClass($Estimate)
	{
			if(isset($Estimate->BillingClassID))
			{
				$EstimateBillingClass	 =	 $Estimate->BillingClassID;
			}
			else
			{
				$AccountBilling 	  	=  	 AccountBilling::getBilling($Estimate->AccountID);
				$EstimateBillingClass 	= 	 $AccountBilling->BillingClassID;	
			}	
			return $EstimateBillingClass;
	}
	
	public static function GetEstimateInvoiceTemplateID($Estimate){
	  	$billingclass = 	self::GetEstimateBillingClass($Estimate);
		return BillingClass::getInvoiceTemplateID($billingclass);
	}

}