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
    const ITEM_INVOICE =1;
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
            $print_type = 'CreditNotes';
            $body 	= 	View::make('CreditNotes.pdf', compact('CreditNotes', 'CreditNotesDetail', 'Account', 'CreditNotesTemplate', 'CurrencyCode', 'logo','CurrencySymbol','print_type','CreditNotesItemTaxRates','CreditNotesSubscriptionTaxRates','CreditNotesAllTaxRates','taxes',"CreditNotesDetailItems","CreditNotesDetailISubscription"))->render();
            $body 	= 	htmlspecialchars_decode($body);
            $footer = 	View::make('CreditNotes.pdffooter', compact('CreditNotes','print_type'))->render();
            $footer = 	htmlspecialchars_decode($footer);

            $header = View::make('CreditNotes.pdfheader', compact('CreditNotes','print_type'))->render();
            $header = htmlspecialchars_decode($header);

            $amazonPath = AmazonS3::generate_path(AmazonS3::$dir['INVOICE_UPLOAD'],$Account->CompanyId,$CreditNotes->AccountID) ;
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
        $CreditNotesStatus = explode(',',$Company->CreditNotesStatus);
        $creditnotesarray = array(''=>'Select CreditNotes Status',self::OPEN=>'Open',self::CLOSE=>'Close');
        foreach($CreditNotesStatus as $status){
            $creditnotesarray[$status] = $status;
        }
        return $creditnotesarray;
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

    public function ExportInvoices($CreditNotesIDs){
        //$CreditNotesID = '62987';
        $CreditNotesIDs = $CreditNotesIDs['Invoices'];
        Log::info('Invoice IDs : '.print_r($CreditNotesIDs,true));


        if(count($CreditNotesIDs) > 0) {
            Log::info('count invoice id : '.count($CreditNotesIDs));
            foreach ($CreditNotesIDs as $CreditNotesID) {
                $myfile = fopen("newfile_".$CreditNotesID.".iif", "w") or die("Unable to open file!");
                $acctxt = "!ACCNT\tNAME\tACCNTTYPE\tDESC\tACCNUM\tEXTRA\n";
                fwrite($myfile, $acctxt);
                $itemtxt = "!INVITEM\tNAME\tINVITEMTYPE\tDESC\tPURCHASEDESC\tACCNT\tASSETACCNT\tCOGSACCNT\tPRICE\tCOST\tTAXABLE\tPAYMETH\tTAXVEND\tTAXDIST\tPREFVEND\tREORDERPOINT\tEXTRA\n";
                fwrite($myfile, $itemtxt);

                $Invoice = Invoice::find($CreditNotesID);
                $AccountID = $Invoice->AccountID;
                $Account = Account::find($AccountID);
                Log::info("Account Details ". print_r($Account,true));
                $InvoiceDetails = InvoiceDetail::where(["CreditNotesID" => $CreditNotesID])->get();
                if (!empty($InvoiceDetails) && count($InvoiceDetails) > 0) {
                    foreach ($InvoiceDetails as $InvoiceDetail) {
                        Log::info('Product id' . $InvoiceDetail->ProductID);
                        Log::info('Product Type' . $InvoiceDetail->ProductType);
                        $ItemID = '';
                        $ProductID = $InvoiceDetail->ProductID;
                        $ProductType = $InvoiceDetail->ProductType;
                        if (!empty($ProductType)) {
                            $ProductName = Product::getProductName($ProductID, $ProductType);
                        }
                        else{
                            $ProductName = " ";
                        }

                        if($InvoiceDetail->TaxRateID != 0 || $InvoiceDetail->TaxRateID2 != 0)
                        {
                            $TAXABLE = 'Y';
                        }
                        else
                        {
                            $TAXABLE = 'N';
                        }
                        $InvoiceTaxRates = InvoiceTaxRate::where(["CreditNotesID" => $CreditNotesID])->get();

                        if (!empty($InvoiceTaxRates) && count($InvoiceTaxRates) > 0) {
                            foreach ($InvoiceTaxRates as $InvoiceTaxRate) {
                                $Title = $InvoiceTaxRate->Title;
                                $TaxAmount = $InvoiceTaxRate->TaxAmount;
                            }
                        }

                        $itemarray['INVITEM'] = "INVITEM";
                        $itemarray['NAME'] = $ProductName;
                        $itemarray['INVITEMTYPE'] = "SERV";
                        $itemarray['DESC'] = $InvoiceDetail->Description;
                        $itemarray['PURCHASEDESC'] = " ";
                        $itemarray['ACCNT'] = " ";
                        $itemarray['ASSETACCNT'] = " ";
                        $itemarray['COGSACCNT'] = " ";
                        $itemarray['PRICE'] = $InvoiceDetail->Price;
                        $itemarray['COST'] = " ";
                        $itemarray['TAXABLE'] = $TAXABLE;
                        $itemarray['PAYMETH'] = " ";
                        $itemarray['TAXVEND'] = " ";
                        $itemarray['TAXDIST'] = " ";
                        $itemarray['PREFVEND'] = " ";
                        $itemarray['REORDERPOINT'] = " ";
                        $itemarray['EXTRA'] = " ";

                        $items_list = implode("\t", $itemarray)."\n";
                        fwrite($myfile, $items_list);
                        $itemarray = array();
                    }
                }

                $custarray['CUST'] = "CUST";
                $custarray['NAME'] = $Account->AccountName;
                $custarray['BADDR1'] = $Account->Address1;
                $custarray['BADDR2'] = $Account->Address2;
                $custarray['BADDR3'] = $Account->Address3;
                $custarray['BADDR4'] = " ";
                $custarray['BADDR5'] = " ";
                $custarray['SADDR1'] = $Account->Address1;
                $custarray['SADDR2'] = $Account->Address2;
                $custarray['SADDR3'] = $Account->Address3;
                $custarray['SADDR4'] = " ";
                $custarray['SADDR5'] = " ";
                $custarray['PHONE1'] = $Account->Phone;
                $custarray['PHONE2'] = " ";
                $custarray['FAXNUM'] = $Account->Fax;
                $custarray['EMAIL'] = $Account->Email;
                $custarray['NOTE'] = " ";
                $custarray['CONT1'] = " ";
                $custarray['CONT2'] = " ";
                $custarray['CTYPE'] = " ";
                $custarray['TERMS'] = " ";
                $custarray['TEXABLE'] = " ";
                $custarray['LIMIT'] = " ";
                $custarray['RESALENUM'] = " ";
                $custarray['REP'] = " ";
                $custarray['TAXITEM'] = " ";
                $custarray['NOTEPAD'] = " ";
                $custarray['SALUTATION'] = " ";
                $custarray['COMPANYNAME'] = " ";
                $custarray['FIRSTNAME'] = $Account->FirstName;
                $custarray['MIDINIT'] = " ";
                $custarray['LASTNAME'] = $Account->LastName;

                $cust_list = implode("\t", $custarray)."\n";

                $classtxt = "!CLASS\tNAME\n";
                fwrite($myfile, $classtxt);
                $custtxt = "!CUST\tNAME\tBADDR1\tBADDR2\tBADDR3\tBADDR4\tBADDR5\tSADDR1\tSADDR2\tSADDR3\tSADDR4\tSADDR5\tPHONE1\tPHONE2\tFAXNUM\tEMAIL\tNOTE\tCONT1\tCONT2\tCTYPE\tTERMS\tTAXABLE\tLIMIT\tRESALENUM\tREP\tTAXITEM\tNOTEPAD\tSALUTATION\tCOMPANYNAME\tFIRSTNAME\tMIDINIT\tLASTNAME\n";
                fwrite($myfile, $custtxt);
                fwrite($myfile, $cust_list);
                $vendtxt = "!VEND\tNAME\tPRINTAS\tADDR1\tADDR2\tADDR3\tADDR4\tADDR5\tVTYPE\tCONT1\tCONT2\tPHONE1\tPHONE2\tFAXNUM\tEMAIL\tNOTE\tTAXID\tLIMIT\tTERMS\tNOTEPAD\tSALUTATION\tCOMPANYNAME\tFIRSTNAME\tMIDINIT\tLASTNAME\n";
                fwrite($myfile, $vendtxt);
                $transactiontxt = "!TRNS\tTRNSID\tTRNSTYPE\tDATE\tACCNT\tNAME\tCLASS\tAMOUNT\tDOCNUM\tMEMO\tCLEAR\tTOPRINT\tNAMEISTAXABLE\tADDR1\tADDR3\tTERMS\n";
                fwrite($myfile, $transactiontxt);
                $spltxt = "!SPL\tSPLID\tTRNSTYPE\tDATE\tACCNT\tNAME\tCLASS\tAMOUNT\tDOCNUM\tMEMO\tCLEAR\tQNTY\tPRICE\tINVITEM\tTAXABLE\tEXTRA\n";
                fwrite($myfile, $spltxt);

                fclose($myfile);
            }
        }


        Log::info('-- Export Invoice Done --');

    }

    /**
     * IIF File Format For Journal Export
     * !TRNS	TRNSID	TRNSTYPE	DATE	ACCNT	CLASS	AMOUNT	DOCNUM	MEMO
    !SPL	SPLID	TRNSTYPE	DATE	ACCNT	CLASS	AMOUNT	DOCNUM	MEMO
    !ENDTRNS
    TRNS		GENERAL JOURNAL	7/1/1998	Checking		650
    SPL		GENERAL JOURNAL	7/1/1998	Expense Account		-650
    ENDTRNS

     */
    public function ExportJournals($Options){
        //$CreditNotesID = '62987';
        $Invoices = $Options['Invoices'];
        $CompanyID = $Options['CompanyID'];
        Log::info('Invoice IDs : '.print_r($Invoices,true));
        Log::info('count invoice id : '.count($Invoices));

        $response = array();
        $response['msg'] = '';
        if(!empty($Invoices) && count($Invoices)>0) {

            $PaidInvoices = array();
            foreach ($Invoices as $Invoice) {
                if (!empty($Invoice))
                    $InvoiceData = array();
                $InvoiceData = Invoice::find($Invoice);
                $InvoiceFullNumber = $InvoiceData->FullInvoiceNumber;
                log::info('Invoice ID : ' . $Invoice);
                $CheckInvoiceFullPaid = $this->CheckInvoiceFullPaid($Invoice, $CompanyID);
                if (!empty($CheckInvoiceFullPaid)) {
                    $PaidInvoices[] = $Invoice;
                } else {
                    $response['msg'] = $InvoiceFullNumber . '(Invoice) not fully paid. ';
                }
            }
            //log::info("paid invoices =".print_r($PaidInvoices,true));
            if (!empty($PaidInvoices) && count($PaidInvoices) > 0) {
                /**
                 * New Change Start - datewise array of paid invoices
                 **/
                $NewPaidInvoices = array();
                foreach ($PaidInvoices as $paidInvoice) {
                    $Payments = Payment::where(['CreditNotesID' => $paidInvoice])->first();
                    $PaymentDate = $Payments->PaymentDate;
                    $PaymentDate = date('Y-m-d', strtotime($PaymentDate));
                    $NewPaidInvoices[$PaymentDate][] = $paidInvoice;
                }
                //echo "<pre>";print_r($NewPaidInvoices);

                if (!empty($NewPaidInvoices) && count($NewPaidInvoices) > 0) {
                    // Check Invoice and Payment Mapping

                    $QuickBookData = SiteIntegration::CheckIntegrationConfiguration(true, SiteIntegration::$QuickBookDesktopSlug, $CompanyID);
                    $QuickBookData = json_decode(json_encode($QuickBookData), true);
                    log::info(print_r($QuickBookData, true));

                    $UPLOAD_PATH = CompanyConfiguration::get('UPLOAD_PATH',$Options['CompanyID']). "/journal_".date('ymdhis')."/";
                    mkdir($UPLOAD_PATH);
                    foreach ($NewPaidInvoices as $key => $NewPaidInvoice) {

                        //create iif file at upload path
                        $filename = "journal_" . $key . ".iif";
                        $myfile = fopen($UPLOAD_PATH.$filename, "w") or die("Unable to open file!");
                        $transactiontxt = "!TRNS\tTRNSID\tTRNSTYPE\tDATE\tACCNT\tCLASS\tAMOUNT\tDOCNUM\tMEMO\n";
                        fwrite($myfile, $transactiontxt);
                        $spltxt = "!SPL\tSPLID\tTRNSTYPE\tDATE\tACCNT\tCLASS\tAMOUNT\tDOCNUM\tMEMO\n!ENDTRNS\n";
                        fwrite($myfile, $spltxt);

                        $ndate = date('m/d/Y', strtotime($key));
                        log::info('Journal Payment Date ' . $key);
                        $InvoiceDatas = Invoice::find($NewPaidInvoice);
                        
                        //get data of each invoices
                        foreach($InvoiceDatas as $InvoiceData) {

                            $RoundChargesAmount = $this->get_round_decimal_places($InvoiceData->CompanyID, $InvoiceData->AccountID, $InvoiceData->ServiceID);
                            $PaymentTotal = number_format($InvoiceData->SubTotal, $RoundChargesAmount, '.', '');
                            log::info('PaymentTotal ' . $PaymentTotal);
                            $InvoiceTaxRateAmount = $this->getInvoiceTaxRateAmount($InvoiceData->CreditNotesID, $RoundChargesAmount);
                            $InvoiceGrantTotal = $PaymentTotal + $InvoiceTaxRateAmount;
                            log::info('InvoiceGrantTotal ' . $InvoiceGrantTotal);

                            $InvoiceFullNumber = $InvoiceData->FullInvoiceNumber;
                            $invoicedescription = $InvoiceFullNumber . ' (Invoice)';
                            $paymentdescription = $InvoiceFullNumber . ' (Payment)';

                            $transarray = array();
                            $transarray['TRNS'] = 'TRNS';
                            $transarray['TRNSID'] = ' ';
                            $transarray['TRNSTYPE'] = 'GENERAL JOURNAL';
                            $transarray['DATE'] = $ndate;
                            $transarray['ACCNT'] = $QuickBookData['InvoiceAccount'];
                            $transarray['CLASS'] = ' ';
                            $transarray['AMOUNT'] = $InvoiceGrantTotal;
                            $transarray['DOCNUM'] = ' ';
                            $transarray['MEMO'] = $invoicedescription;

                            $trans_list = implode("\t", $transarray) . "\n";
                            fwrite($myfile, $trans_list);

                            $splarray = array();
                            $splarray['TRNS'] = 'SPL';
                            $splarray['TRNSID'] = ' ';
                            $splarray['TRNSTYPE'] = 'GENERAL JOURNAL';
                            $splarray['DATE'] = $ndate;
                            $splarray['ACCNT'] = $QuickBookData['PaymentAccount'];
                            $splarray['CLASS'] = ' ';
                            $splarray['AMOUNT'] = "-" . $PaymentTotal;
                            $splarray['DOCNUM'] = ' ';
                            $splarray['MEMO'] = $paymentdescription;

                            $spl_list = implode("\t", $splarray) . "\n";
                            fwrite($myfile, $spl_list);

                            $InvoiceTaxRates = InvoiceTaxRate::where(["CreditNotesID" => $InvoiceData->CreditNotesID])->get();

                            if (!empty($InvoiceTaxRates) && count($InvoiceTaxRates) > 0) {
                                foreach ($InvoiceTaxRates as $InvoiceTaxRate) {
                                    $Title = $InvoiceTaxRate->Title;
                                    $TaxRateID = $InvoiceTaxRate->TaxRateID;
                                    $TaxAmount = number_format($InvoiceTaxRate->TaxAmount, $RoundChargesAmount, '.', '');
                                    log::info($QuickBookData['Tax'][$TaxRateID]);

                                    $taxdescription = $InvoiceFullNumber . ' ' . $Title . ' (Tax)';

                                    $taxarray = array();
                                    $taxarray['TRNS'] = 'SPL';
                                    $taxarray['TRNSID'] = ' ';
                                    $taxarray['TRNSTYPE'] = 'GENERAL JOURNAL';
                                    $taxarray['DATE'] = $ndate;
                                    $taxarray['ACCNT'] = $QuickBookData['Tax'][$TaxRateID];
                                    $taxarray['CLASS'] = ' ';
                                    $taxarray['AMOUNT'] = "-" . $TaxAmount;
                                    $taxarray['DOCNUM'] = ' ';
                                    $taxarray['MEMO'] = $taxdescription;

                                    $tax_list = implode("\t", $taxarray) . "\n";
                                    fwrite($myfile, $tax_list);

                                }
                            }
                        }
                        fwrite($myfile, 'ENDTRNS');
                        fclose($myfile);


                    } // newpaidinvoice over
                    $local_zip_file = CompanyConfiguration::get('UPLOAD_PATH',$Options['CompanyID']). "/journal_".date('ymdhis').".zip";
                    Zipper::make($local_zip_file)->add($UPLOAD_PATH)->close();
                    $response['status'] = 'success';
                    $response['msg'] .= 'Journal created';
                    $response['redirect'] = $local_zip_file;
                    return $response;
                    // End Create Journal
                } else {
                    $response['status'] = 'failed';
                    $response['msg'] .= 'Journal creation failed';
                    return $response;
                }

            } else {
                $response['status'] = 'failed';
                $response['msg'] .= 'Journal creation failed';
                return $response;
            }

            Log::info('-- Export Invoice Done --');
        }
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