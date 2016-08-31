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
            $Estimate 			= 	Estimate::find($EstimateID);
            $EstimateDetail 	= 	EstimateDetail::where(["EstimateID" => $EstimateID])->get();
            $EstimateTaxRates = DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->where("EstimateID",$EstimateID)->orderby('EstimateTaxRateID')->get();
            $Account 			= 	Account::find($Estimate->AccountID);
            $AccountBilling = AccountBilling::getBilling($Estimate->AccountID);
            $Currency 			= 	Currency::find($Account->CurrencyId);
            $CurrencyCode 		= 	!empty($Currency)?$Currency->Code:'';
			$CurrencySymbol 	=   Currency::getCurrencySymbol($Account->CurrencyId);
            $EstimateTemplate 	= 	InvoiceTemplate::find($AccountBilling->InvoiceTemplateID);
			
            if (empty($EstimateTemplate->CompanyLogoUrl) || AmazonS3::unSignedUrl($EstimateTemplate->CompanyLogoAS3Key) == '')
			{
                $as3url =  base_path().'/public/assets/images/250x100.png';
            }
			else
			{
                $as3url = (AmazonS3::unSignedUrl($EstimateTemplate->CompanyLogoAS3Key));
            }
            $logo_path = getenv('UPLOAD_PATH') . '/logo/' . User::get_companyID();
            @mkdir($logo_path, 0777, true);
            RemoteSSH::run("chmod -R 777 " . $logo_path);
            $logo = $logo_path  . '/'  . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));

            $EstimateTemplate->DateFormat 	= 	estimate_date_fomat($EstimateTemplate->DateFormat);
            $file_name 						= 	'Estimate--' .$Account->AccountName.'-' .date($EstimateTemplate->DateFormat) . '.pdf';
            $htmlfile_name 					= 	'Estimate--' .$Account->AccountName.'-' .date($EstimateTemplate->DateFormat) . '.html';
			$print_type = 'Estimate';
            $body 	= 	View::make('estimates.pdf', compact('Estimate', 'EstimateDetail', 'Account', 'EstimateTemplate', 'CurrencyCode', 'logo','CurrencySymbol','print_type','AccountBilling','EstimateTaxRates'))->render();
            $body 	= 	htmlspecialchars_decode($body);
            $footer = 	View::make('estimates.pdffooter', compact('Estimate','print_type'))->render();
            $footer = 	htmlspecialchars_decode($footer);

            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['ESTIMATE_UPLOAD'],$Account->CompanyId,$Estimate->AccountID) ;
            $destination_dir = getenv('UPLOAD_PATH') . '/'. $amazonPath;
            
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
            $output= "";
            if(getenv('APP_OS') == 'Linux'){
                exec (base_path(). '/wkhtmltox/bin/wkhtmltopdf --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);

            }else{
                exec (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
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
    public static function getFullEstimateNumber($Estimate,$AccountBilling)
	{
        $EstimateNumberPrefix = '';
        if(!empty($AccountBilling->InvoiceTemplateID))
		{
             $EstimateNumberPrefix = InvoiceTemplate::find($AccountBilling->InvoiceTemplateID)->EstimateNumberPrefix;
        }
        return $EstimateNumberPrefix.$Estimate->EstimateNumber;
    }

}