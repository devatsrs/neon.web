<?php

class RecurringInvoice extends \Eloquent {
	
    protected $connection 	= 	'sqlsrv2';
    protected $fillable 	= 	[];
    protected $guarded 		= 	array('RecurringInvoiceID');
    protected $table 		= 	'tblRecurringInvoice';
    protected $primaryKey 	= 	"RecurringInvoiceID";
    const ACTIVE 			= 	1;
    const INACTIVE 				= 	0;
	
    //public static $estimate_status;
    //public static $estimate_type = array(''=>'Select' ,self::ESTIMATE_OUT => 'Estimate Sent',self::ESTIMATE_IN=>'Estimate Received','All'=>'Both');
    // static $estimate_type_customer = array(''=>'Select' ,self::ESTIMATE_OUT => 'Estimate Received',self::ESTIMATE_IN=>'Estimate sent','All'=>'Both');

    /*public static function getEstimateEmailTemplate($data){

        $message = '[CompanyName] has sent you an estimate of [GrandTotal] [CurrencyCode], '. PHP_EOL. 'to download copy of your estimate please click the below link.';

        $message = str_replace("[CompanyName]",$data['CompanyName'],$message);
        $message = str_replace("[GrandTotal]",$data['GrandTotal'],$message);
        $message = str_replace("[CurrencyCode]",$data['CurrencyCode'],$message);
        return $message;
    }*/

    public static  function generate_pdf($RecurringInvoiceID)
	{
        if($RecurringInvoiceID>0) {
            $RecurringInvoice 			= 	RecurringInvoice::find($RecurringInvoiceID);
            $RecurringInvoiceDetail 	= 	RecurringInvoiceDetail::where(["RecurringInvoiceID" => $RecurringInvoiceID])->get();
            $RecurringInvoiceTaxRates = DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->where(["RecurringInvoiceID"=>$RecurringInvoiceID,"RecurringInvoiceTaxType"=>0])->orderby('RecurringInvoiceTaxRateID')->get();
			$RecurringInvoiceAllTaxRates = DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->where(["RecurringInvoiceID"=>$RecurringInvoiceID,"RecurringInvoiceTaxType"=>1])->orderby('RecurringInvoiceTaxRateID')->get();
            $Account 			= 	Account::find($RecurringInvoice->AccountID);
            $AccountBilling = AccountBilling::getBilling($RecurringInvoice->AccountID);
            $Currency 			= 	Currency::find($Account->CurrencyId);
            $CurrencyCode 		= 	!empty($Currency)?$Currency->Code:'';
			$CurrencySymbol 	=   Currency::getCurrencySymbol($Account->CurrencyId);
            $RecurringInvoiceTemplate 	= 	InvoiceTemplate::find($RecurringInvoice->InvoiceTemplateID);
			
            if (empty($RecurringInvoiceTemplate->CompanyLogoUrl) || AmazonS3::unSignedUrl($RecurringInvoiceTemplate->CompanyLogoAS3Key) == '') {
                $as3url =  base_path().'/public/assets/images/250x100.png';
            } else {
                $as3url = (AmazonS3::unSignedUrl($RecurringInvoiceTemplate->CompanyLogoAS3Key));
            }
            $logo_path = CompanyConfiguration::get('UPLOAD_PATH') . '/logo/' . User::get_companyID();
            @mkdir($logo_path, 0777, true);
            RemoteSSH::run("chmod -R 777 " . $logo_path);
            $logo = $logo_path  . '/'  . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));

            $RecurringInvoiceTemplate->DateFormat 	= 	invoice_date_fomat($RecurringInvoiceTemplate->DateFormat);
            $file_name 						= 	'RecurringInvoice--' .$Account->AccountName.'-' .date($RecurringInvoiceTemplate->DateFormat) . '.pdf';
            $htmlfile_name 					= 	'RecurringInvoice--' .$Account->AccountName.'-' .date($RecurringInvoiceTemplate->DateFormat) . '.html';
			$print_type = 'RecurringInvoice';
            $body 	= 	View::make('RecurringInvoices.pdf', compact('RecurringInvoice', 'RecurringInvoiceDetail', 'Account', 'RecurringInvoiceTemplate', 'CurrencyCode', 'logo','CurrencySymbol','print_type','AccountBilling','RecurringInvoiceTaxRates','RecurringInvoiceAllTaxRates'))->render();
            $body 	= 	htmlspecialchars_decode($body); 
            $footer = 	View::make('RecurringInvoices.pdffooter', compact('RecurringInvoice','print_type'))->render();
            $footer = 	htmlspecialchars_decode($footer);

            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['RECURRING_INVOICE_UPLOAD'],$Account->CompanyId,$RecurringInvoice->AccountID) ;
            $destination_dir = CompanyConfiguration::get('UPLOAD_PATH') . '/'. $amazonPath;

			if (!file_exists($destination_dir)) {
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

    public static function get_recurringinvoices_status(){
        return [''=>'All',self::ACTIVE=>'Active',self::INACTIVE=>'InActive'];
    }

    public static function getRecurringInvoicesIDList(){
        $result = RecurringInvoice::select(array('Title', 'RecurringInvoiceID'))->orderBy('Title')->lists('Title', 'RecurringInvoiceID');
        $row = array(""=> "Select");
        if(!empty($result)){
            $row = array(""=> "Select")+$result;
        }
        return $row;
    }
	
	public static function getRecurringInvoices(){
        $compantID = User::get_companyID();
        $where = ['CompanyID'=>$compantID];      
	    $result = RecurringInvoice::select(array('Title', 'RecurringInvoiceID'))->where($where)->orderBy('Title')->lists('Title', 'RecurringInvoiceID');		        if(!empty($result)){
            $result = [''=>'Select'] + $result;
        }
        return $result;
    }

}