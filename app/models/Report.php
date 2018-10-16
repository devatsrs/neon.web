<?php
class Report extends \Eloquent {
    protected $guarded = array("ReportID");
    protected $table = "tblReport";
    protected $primaryKey = "ReportID";
    protected $connection = 'neon_report';
    protected $fillable = array(
        'CompanyID','Name','Settings','created_at'
    );

    public static $rules = array(
        'Name'=>'required',
    );

    public static $cube = array(
        'customercdr'=>'Customer CDR',
        'vendorcdr'=>'Vendor CDR',
        'summary'=>'Customer Summary',
        'vsummary'=>'Vendor Summary',
        'invoice' => 'Invoice',
        'tax' => 'Tax',
        'payment' => 'Payment',
        'account' => 'Account',
    );

    public static $dimension = array(
        'customercdr'=>array(
            'Date'=>array(
                'year' => 'Year',
                'quarter_of_year' => 'Quarter' ,
                'month_of_year' => 'Month',
                'week_of_year' => 'Week',
                'date' => 'Day',
                'hour' => 'Hour',
                'minute' => 'Minute',
            ),
            'Customer'=>array(
                'AccountID'=>'AccountName',
                'CurrencyID'=>'Currency',
                'Number'=>'Number',
                'Email'=>'Email',
                'IsVendor'=>'IsVendor',
                'IsCustomer'=>'IsCustomer',
                'Address1'=>'Address1',
                'City'=>'City',
                'State'=>'State',
                'PostCode'=>'PostCode',
                'Country'=>'Country',
                'BillingEmail'=>'BillingEmail',
                'VatNumber'=>'VatNumber',
                'TimeZone'=>'TimeZone',
                'tags'=>'Tag',
                'BillingType' => 'Billing Type',
                'BillingCycleType' => 'Billing Cycle Type',
                'BillingStartDate' => 'Billing Start Date',
                'BillingCycleValue' => 'Billing Cycle Start of Day',
                'BillingClassID' => 'Billing Class',
                'LastInvoiceDate' => 'Last Invoice Date',
                'NextInvoiceDate' => 'Next Invoice Date',
                'LastChargeDate' => 'Last Charge Date',
                'NextChargeDate' => 'Next Charge Date',
                'Billing' => 'Is Billing',
            ),
            'CDR'=>array(
                'connect_time'=>'Connect Time',
                'disconnect_time'=>'Disconnect Time',
                'area_prefix'=>'Area Prefix',
                'pincode'=>'Pin Code',
                'extension'=>'Extension',
                'cli'=>'CLI',
                'cld'=>'CLD',
                'remote_ip'=>'Remote IP',
                'trunk'=>'Trunk',
                'is_inbound'=>'Inbound',
                'disposition'=>'Disposition',
                'userfield'=>'User Field',
            ),
            'Owner'=>'Account Manager',
            'CompanyGatewayID' =>'Gateway',
            'CountryID' => 'Country',
            'DestinationBreak' => 'Destination Break',
            'GatewayAccountPKID' => 'Customer IP/CLI',
            'ServiceID' => 'Service Name',
        ),
        'vendorcdr'=>array(
            'Date'=>array(
                'year' => 'Year',
                'quarter_of_year' => 'Quarter' ,
                'month_of_year' => 'Month',
                'week_of_year' => 'Week',
                'date' => 'Day',
                'hour' => 'Hour',
                'minute' => 'Minute',
            ),
            'Vendor'=>array(
                'AccountID'=>'AccountName',
                'CurrencyID'=>'Currency',
                'Number'=>'Number',
                'Email'=>'Email',
                'IsVendor'=>'IsVendor',
                'IsCustomer'=>'IsCustomer',
                'Address1'=>'Address1',
                'City'=>'City',
                'State'=>'State',
                'PostCode'=>'PostCode',
                'Country'=>'Country',
                'BillingEmail'=>'BillingEmail',
                'VatNumber'=>'VatNumber',
                'TimeZone'=>'TimeZone',
                'tags'=>'Tag',
                'BillingType' => 'Billing Type',
                'BillingCycleType' => 'Billing Cycle Type',
                'BillingStartDate' => 'Billing Start Date',
                'BillingCycleValue' => 'Billing Cycle Start of Day',
                'BillingClassID' => 'Billing Class',
                'LastInvoiceDate' => 'Last Invoice Date',
                'NextInvoiceDate' => 'Next Invoice Date',
                'LastChargeDate' => 'Last Charge Date',
                'NextChargeDate' => 'Next Charge Date',
                'Billing' => 'Is Billing',
            ),
            'CDR'=>array(
                'connect_time'=>'Connect Time',
                'disconnect_time'=>'Disconnect Time',
                'area_prefix'=>'Area Prefix',
                'cli'=>'CLI',
                'cld'=>'CLD',
                'remote_ip'=>'Remote IP',
                'trunk'=>'Trunk'
            ),
            'Owner'=>'Account Manager',
            'CompanyGatewayID' =>'Gateway',
            'CountryID' => 'Country',
            'DestinationBreak' => 'Destination Break',
            'GatewayAccountPKID' => 'Customer IP/CLI',
            'ServiceID' => 'Service Name',
        ),
        'summary'=>array(
            'Date'=>array(
                'year' => 'Year',
                'quarter_of_year' => 'Quarter' ,
                'month_of_year' => 'Month',
                'week_of_year' => 'Week',
                'date' => 'Day',
                'hour' => 'Hour',
            ),
            'Customer'=>array(
                'AccountID'=>'AccountName',
                'CurrencyID'=>'Currency',
                'Number'=>'Number',
                'Email'=>'Email',
                'IsVendor'=>'IsVendor',
                'IsCustomer'=>'IsCustomer',
                'Address1'=>'Address1',
                'City'=>'City',
                'State'=>'State',
                'PostCode'=>'PostCode',
                'Country'=>'Country',
                'BillingEmail'=>'BillingEmail',
                'VatNumber'=>'VatNumber',
                'TimeZone'=>'TimeZone',
                'tags'=>'Tag',
                'BillingType' => 'Billing Type',
                'BillingCycleType' => 'Billing Cycle Type',
                'BillingStartDate' => 'Billing Start Date',
                'BillingCycleValue' => 'Billing Cycle Start of Day',
                'BillingClassID' => 'Billing Class',
                'LastInvoiceDate' => 'Last Invoice Date',
                'NextInvoiceDate' => 'Next Invoice Date',
                'LastChargeDate' => 'Last Charge Date',
                'NextChargeDate' => 'Next Charge Date',
                'Billing' => 'Is Billing',
            ),
            'VAccountID' =>'Vendor',
            'Owner'=>'Account Manager',
            'CompanyGatewayID' =>'Gateway',
            'Trunk' => 'Trunk',
            'CountryID' => 'Country',
            'AreaPrefix' => 'Prefix',
            'DestinationBreak' => 'Destination Break',
            'GatewayAccountPKID' => 'Customer IP/CLI',
            'GatewayVAccountPKID' => 'Vendor IP/CLI',
            'ServiceID' => 'Service Name',
        ),
        'vsummary'=>array(
            'Date'=>array(
                'year' => 'Year',
                'quarter_of_year' => 'Quarter' ,
                'month_of_year' => 'Month',
                'week_of_year' => 'Week',
                'date' => 'Day',
                'hour' => 'Hour',
            ),
            'Customer'=>array(
                'AccountID'=>'AccountName',
                'CurrencyID'=>'Currency',
                'Number'=>'Number',
                'Email'=>'Email',
                'IsVendor'=>'IsVendor',
                'IsCustomer'=>'IsCustomer',
                'Address1'=>'Address1',
                'City'=>'City',
                'State'=>'State',
                'PostCode'=>'PostCode',
                'Country'=>'Country',
                'BillingEmail'=>'BillingEmail',
                'VatNumber'=>'VatNumber',
                'TimeZone'=>'TimeZone',
                'tags'=>'Tag',
                'BillingType' => 'Billing Type',
                'BillingCycleType' => 'Billing Cycle Type',
                'BillingStartDate' => 'Billing Start Date',
                'BillingCycleValue' => 'Billing Cycle Start of Day',
                'BillingClassID' => 'Billing Class',
                'LastInvoiceDate' => 'Last Invoice Date',
                'NextInvoiceDate' => 'Next Invoice Date',
                'LastChargeDate' => 'Last Charge Date',
                'NextChargeDate' => 'Next Charge Date',
                'Billing' => 'Is Billing',
            ),
            'VAccountID' =>'Vendor',
            'Owner'=>'Account Manager',
            'CompanyGatewayID' =>'Gateway',
            'Trunk' => 'Trunk',
            'CountryID' => 'Country',
            'AreaPrefix' => 'Prefix',
            'DestinationBreak' => 'Destination Break',
            'GatewayAccountPKID' => 'Customer IP/CLI',
            'GatewayVAccountPKID' => 'Vendor IP/CLI',
            'ServiceID' => 'Service Name',
        ),
        'invoice'=>array(
            'Date'=>array(
                'year' => 'Year',
                'quarter_of_year' => 'Quarter' ,
                'month' => 'Month',
                'week_of_year' => 'Week',
                'date' => 'Day',
            ),
            'Customer'=>array(
                'AccountID'=>'AccountName',
                'Number'=>'Number',
                'Email'=>'Email',
                'IsVendor'=>'IsVendor',
                'IsCustomer'=>'IsCustomer',
                'Address1'=>'Address1',
                'City'=>'City',
                'State'=>'State',
                'PostCode'=>'PostCode',
                'Country'=>'Country',
                'BillingEmail'=>'BillingEmail',
                'VatNumber'=>'VatNumber',
                'TimeZone'=>'TimeZone',
                'tags'=>'Tag',
                'BillingType' => 'Billing Type',
                'BillingCycleType' => 'Billing Cycle Type',
                'BillingStartDate' => 'Billing Start Date',
                'BillingCycleValue' => 'Billing Cycle Start of Day',
                'BillingClassID' => 'Billing Class',
                'LastInvoiceDate' => 'Last Invoice Date',
                'NextInvoiceDate' => 'Next Invoice Date',
                'LastChargeDate' => 'Last Charge Date',
                'NextChargeDate' => 'Next Charge Date',
                'Billing' => 'Is Billing',
            ),
            'Owner'=>'Account Manager',
            'CurrencyID' =>'Currency Code',
            'InvoiceType' =>'Invoice Type',
            'FullInvoiceNumber' =>'Invoice Number',
            'InvoiceStatus' =>'Invoice Status',
            'IssueDate' =>'Invoice Date',
            'invoiceDueDate' =>'Invoice Due Date',
            'TaxRateID' => 'Tax Type',
            'ProductType'=> 'Charge Type',
            'Product' => array(
                'ProductID'=>'Product Name',
                'Code'=>'Product Code',
            ),
            'SubscriptionID' => 'Subscription Name',
            'ServiceID' => 'Service Name',

        ),
        'tax'=>array(
            'Date'=>array(
                'year' => 'Year',
                'quarter_of_year' => 'Quarter' ,
                'month' => 'Month',
                'week_of_year' => 'Week',
                'date' => 'Day',
            ),
            'Customer'=>array(
                'AccountID'=>'AccountName',
                'Number'=>'Number',
                'Email'=>'Email',
                'IsVendor'=>'IsVendor',
                'IsCustomer'=>'IsCustomer',
                'Address1'=>'Address1',
                'City'=>'City',
                'State'=>'State',
                'PostCode'=>'PostCode',
                'Country'=>'Country',
                'BillingEmail'=>'BillingEmail',
                'VatNumber'=>'VatNumber',
                'TimeZone'=>'TimeZone',
                'tags'=>'Tag',
                'BillingType' => 'Billing Type',
                'BillingCycleType' => 'Billing Cycle Type',
                'BillingStartDate' => 'Billing Start Date',
                'BillingCycleValue' => 'Billing Cycle Start of Day',
                'BillingClassID' => 'Billing Class',
                'LastInvoiceDate' => 'Last Invoice Date',
                'NextInvoiceDate' => 'Next Invoice Date',
                'LastChargeDate' => 'Last Charge Date',
                'NextChargeDate' => 'Next Charge Date',
                'Billing' => 'Is Billing',
            ),
            'Owner'=>'Account Manager',
            'CurrencyID' =>'Currency Code',
            'InvoiceType' =>'Invoice Type',
            'InvoiceStatus' =>'Invoice Status',
            'TaxRateID' => 'Tax Type',
            'SubscriptionID' => 'Subscription Name',
            'ServiceID' => 'Service Name',
        ),
        'payment'=>array(
            'Date'=>array(
                'year' => 'Year',
                'quarter_of_year' => 'Quarter' ,
                'month' => 'Month',
                'week_of_year' => 'Week',
                'date' => 'Day',
            ),
            'Customer'=>array(
                'AccountID'=>'AccountName',
                'Number'=>'Number',
                'Email'=>'Email',
                'IsVendor'=>'IsVendor',
                'IsCustomer'=>'IsCustomer',
                'Address1'=>'Address1',
                'City'=>'City',
                'State'=>'State',
                'PostCode'=>'PostCode',
                'Country'=>'Country',
                'BillingEmail'=>'BillingEmail',
                'VatNumber'=>'VatNumber',
                'TimeZone'=>'TimeZone',
                'tags'=>'Tag',
                'BillingType' => 'Billing Type',
                'BillingCycleType' => 'Billing Cycle Type',
                'BillingStartDate' => 'Billing Start Date',
                'BillingCycleValue' => 'Billing Cycle Start of Day',
                'BillingClassID' => 'Billing Class',
                'LastInvoiceDate' => 'Last Invoice Date',
                'NextInvoiceDate' => 'Next Invoice Date',
                'LastChargeDate' => 'Last Charge Date',
                'NextChargeDate' => 'Next Charge Date',
                'Billing' => 'Is Billing',
            ),
            'Owner'=>'Account Manager',
            'CurrencyID' =>'Currency Code',
            'PaymentType'=>'Payment Type',
            'PaymentMethod'=>'Payment Method'
        ),
        'account'=>array(
            'Account'=>array(
                'AccountName'=>'AccountName',
                'Number'=>'Number',
                'Email'=>'Email',
                'IsVendor'=>'IsVendor',
                'IsCustomer'=>'IsCustomer',
                'Address1'=>'Address1',
                'City'=>'City',
                'State'=>'State',
                'PostCode'=>'PostCode',
                'Country'=>'Country',
                'BillingEmail'=>'BillingEmail',
                'VatNumber'=>'VatNumber',
                'TimeZone'=>'TimeZone',
                'tags'=>'Tag',
                'Owner'=>'Account Manager',
                'CurrencyID' =>'Currency Code',
                'BillingType' => 'Billing Type',
                'BillingCycleType' => 'Billing Cycle Type',
                'BillingStartDate' => 'Billing Start Date',
                'BillingCycleValue' => 'Billing Cycle Start of Day',
                'BillingClassID' => 'Billing Class',
                'LastInvoiceDate' => 'Last Invoice Date',
                'NextInvoiceDate' => 'Next Invoice Date',
                'LastChargeDate' => 'Last Charge Date',
                'NextChargeDate' => 'Next Charge Date',
                'Billing' => 'Is Billing',
            ),
        ),
    );

    public static $measures = array(
        'customercdr'=>array(
            'billed_duration' => 'Billed Duration (sec)',
            'duration' => ' Duration (sec)',
            'duration1' => ' Billed Duration (min)',
            'duration2' => ' Duration (min)',
            'cost' => 'Cost',
            'UsageDetailID' => ' Call Count',
            'avgrate' => ' Average Rate(min)',
        ),
        'vendorcdr'=>array(
            'billed_duration' => 'Billed Duration (sec)',
            'duration' => ' Duration (sec)',
            'duration1' => ' Billed Duration (min)',
            'duration2' => ' Duration (min)',
            'selling_cost' => 'Selling Cost',
            'buying_cost' => 'Buying Cost',
            'VendorCDRID' => ' Call Count',
            'avgratevendorcdr' => ' Average Rate(min)',
        ),
        'summary'=>array(
            'TotalCharges' => 'Revenue',
            'TotalCost' => 'Cost',
            'TotalDuration' => ' Duration(sec)',
            'TotalDuration2' => 'Duration(min)',
            'TotalBilledDuration' => 'Billed Duration(sec)',
            'BilledDuration' => 'Billed Duration(min)',
            'NoOfCalls' => 'No Of Calls',
            'NoOfFailCalls' => 'No Of Failed Calls',
            'Margin' => 'Margin',
            'MarginPercentage' => 'Margin %',
            'ACD' => 'ACD',
            'ASR' => 'ASR',
            'avgratesummary' => ' Average Rate(min)',
        ),
        'vsummary'=>array(
            'TotalCharges' => 'Cost',
            'TotalSales' => 'Sales',
            'TotalDuration' => ' Duration(sec)',
            'TotalDuration2' => 'Duration(min)',
            'TotalBilledDuration' => 'Billed Duration(sec)',
            'BilledDuration' => 'Billed Duration(min)',
            'NoOfCalls' => 'No Of Calls',
            'NoOfFailCalls' => 'No Of Failed Calls',
            'Margin' => 'Margin',
            'MarginPercentage' => 'Margin %',
            'ACD' => 'ACD',
            'ASR' => 'ASR',
            'avgratesummary' => ' Average Rate(min)',
        ),
        'invoice'=>array(
            'GrandTotal' => 'Total',
            'PaidTotal' => 'Payment Amount',
            'OutStanding' => 'OutStanding Amount',
            'TotalTax' => 'Tax Total',
            'SubTotal' => 'Sub Total',
        ),
        'tax'=>array(
            'GrandTotal' => 'Total',
            'OutStanding' => 'OutStanding Amount',
            'TotalTax' => 'Tax Total',
            'SubTotal' => 'Sub Total',
            'TotalTaxSubTotal'=>'Total Tax + Subtotal',
            'ItemLineTotal' =>'Item LineTotal'
        ),
        'payment'=>array(
            'Amount' => 'Total',
        ),
        'account'=>array(
            'AccountID' => 'Count',
            'SOAOffset' => 'Invoice Outstanding',
            'UnbilledAmount' => 'Customer Unbilled Amount',
            'VendorUnbilledAmount' => 'Vendor Unbilled Amount',
            'BalanceAmount' => 'Account Exposure',
            'AvailableCreditLimit'=>'Available Credit Limit',
            'BalanceThreshold' => 'Balance Threshold',
            'NetUnbilledAmount' => 'Unbilled Amount',
            'PermanentCredit' => 'Permanent Credit',
        ),
    );

    public static $aggregator = array(
        '' => 'Actual',
        'sum' => 'Sum',
        'avg' => 'Average',
        'count' => 'Count',
        'count_distinct' => 'Count(Distinct)',
        'max' => 'Maximum',
        'min' => 'Minimum',
    );

    public static $condition = array(
        '=' => '=',
        '<>' => '<>',
        '<' => '<',
        '<=' => '<=',
        '>' => '>',
        '>=' => '>=',
        'null' => 'Null',
        'not_null' => 'Is not null',
        'range' => 'Range'
    );

    public static $top = array(
        'top' => 'Top',
        'bottom' => 'Bottom',

    );

    public static $date_fields = ['date'];

    const XLS = 'XLS';
    const PDF = 'PDF';
    const PNG = 'PNG';

    public static  function generateDynamicTable($CompanyID,$cube,$data=array(),$filters){
        $response = '';
        switch ($cube) {
            case 'summary':
                $response = ReportCustomerCDR::generateSummaryQuery($CompanyID,$data,$filters);
                break;
            case 'vsummary':
                $response = ReportVendorCDR::generateSummaryQuery($CompanyID,$data,$filters);
                break;
            case 'invoice':
                $response = ReportInvoice::generateQuery($CompanyID,$data,$filters);
                break;
            case 'tax':
                $response = ReportTax::generateQuery($CompanyID,$data,$filters);
                break;
            case 'payment':
                $response = ReportPayment::generateQuery($CompanyID,$data,$filters);
                break;
            case 'customercdr':
                $response = ReportCustomerCDRs::generateSummaryQuery($CompanyID,$data,$filters);
                break;
            case 'vendorcdr':
                $response = ReportVendorCDRs::generateSummaryQuery($CompanyID,$data,$filters);
                break;
            case 'account':
                $response = ReportAccount::generateQuery($CompanyID,$data,$filters);
                break;

        }
        return $response;
    }




    public static function getName($PKColumnName,$ID,$all_data){
        Invoice::multiLang_init();
        $name = $ID;
        switch ($PKColumnName) {
            case 'CompanyGatewayID':
                if($ID > 0 && isset($all_data['CompanyGateway'][$ID])) {
                    $name = $all_data['CompanyGateway'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'AccountID':
                if($ID > 0 && isset($all_data['Account'][$ID])) {
                    $name = $all_data['Account'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'VAccountID':
                if($ID > 0 && isset($all_data['Account'][$ID])) {
                    $name = $all_data['Account'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'CountryID':
                if($ID > 0 && isset($all_data['Country'][$ID])) {
                    $name = $all_data['Country'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'GatewayAccountPKID':
                if($ID > 0 && isset($all_data['GatewayAccountPKID'][$ID])) {
                    $name = $all_data['GatewayAccountPKID'][$ID];
                }else if($ID > 0 && !empty($all_data['AccountIP'][$ID])){
                    $all_data['GatewayAccountPKID'][$ID] = $name = $all_data['AccountIP'][$ID];
                }else if($ID > 0 && !empty($all_data['AccountCLI'][$ID])){
                    $all_data['GatewayAccountPKID'][$ID] = $name = $all_data['AccountCLI'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'GatewayVAccountPKID':
                if($ID > 0 && isset($all_data['GatewayAccountPKID'][$ID])) {
                    $name = $all_data['GatewayAccountPKID'][$ID];
                }else if($ID > 0 && !empty($all_data['AccountIP'][$ID])){
                    $all_data['GatewayAccountPKID'][$ID] = $name = $all_data['AccountIP'][$ID];
                }else if($ID > 0 && !empty($all_data['AccountCLI'][$ID])){
                    $all_data['GatewayAccountPKID'][$ID] = $name = $all_data['AccountCLI'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'InvoiceStatus':
                $invoice_status = Invoice::get_invoice_status();
                if(!empty($ID) && isset($invoice_status[$ID])){
                    $name = $invoice_status[$ID];
                }else if(!empty($ID)){
                    $name = $ID;
                }else{
                    $name = '';
                }
                break;
            case 'InvoiceType':
                $invoice_type = Invoice::$invoice_type;
                if(!empty($ID) && isset($invoice_type[$ID])){
                    $name = $invoice_type[$ID];
                }else if(!empty($ID)){
                    $name = $ID;
                }else{
                    $name = '';
                }
                break;
            case 'CurrencyID':
                if($ID > 0 && isset($all_data['Currency'][$ID])) {
                    $name = $all_data['Currency'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'Owner':
                if($ID > 0 && isset($all_data['AccountManager'][$ID])) {
                    $name = $all_data['AccountManager'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'TaxRateID':
                if($ID > 0 && isset($all_data['Tax'][$ID])) {
                    $name = $all_data['Tax'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'ProductID':
                if($ID > 0 && isset($all_data['Product'][$ID])) {
                    $name = $all_data['Product'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'ProductType':
                $invoice_type = Product::$AllProductTypes;
                if(!empty($ID) && isset($invoice_type[$ID])){
                    $name = $invoice_type[$ID];
                }else if(!empty($ID)){
                    $name = $ID;
                }else{
                    $name = '';
                }
                break;
            case 'PaymentMethod':
                $method = Payment::$method;
                if(!empty($ID) && isset($method[$ID])){
                    $name = $method[$ID];
                }else if(!empty($ID)){
                    $name = $ID;
                }else{
                    $name = '';
                }
                break;
            case 'PaymentType':
                $action = Payment::$action;
                if(!empty($ID) && isset($action[$ID])){
                    $name = $action[$ID];
                }else if(!empty($ID)){
                    $name = $ID;
                }else{
                    $name = '';
                }
                break;
            case 'ServiceID':
                if($ID > 0 && isset($all_data['Service'][$ID])) {
                    $name = $all_data['Service'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'SubscriptionID':
                if($ID > 0 && isset($all_data['Subscription'][$ID])) {
                    $name = $all_data['Subscription'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'BillingType':
                $billing_type = AccountApproval::$billing_type;
                if(!empty($ID) && isset($billing_type[$ID])){
                    $name = $billing_type[$ID];
                }else if(!empty($ID)){
                    $name = $ID;
                }else{
                    $name = '';
                }
                break;
            case 'BillingClassID':
                $BillingClass   = BillingClass::getDropdownIDList();
                if(!empty($ID) && isset($BillingClass[$ID])){
                    $name = $BillingClass[$ID];
                }else if(!empty($ID)){
                    $name = $ID;
                }else{
                    $name = '';
                }
                break;
            case 'BillingCycleType':
                $BillingCycleTypeArray = SortBillingType(1);
                if(!empty($ID)){
                    $name = $BillingCycleTypeArray[$ID];
                }else if(!empty($ID)){
                    $name = $ID;
                }else{
                    $name = '';
                }
                break;
            case 'Billing':
                if($ID==1){
                    $name = "On";
                }else if($ID==0){
                    $name = "Off";
                }else{
                    $name = '';
                }
                break;
        }
        return $name;
    }



    public static function getDataInArray($CompanyID,$PKColumnName,$search){
        $data_in_array = array();
        switch ($PKColumnName) {
            case 'CompanyGatewayID':
                $data_in_array = CompanyGateway::where(array('CompanyID'=>$CompanyID,'Status'=>1))->where('Title','like',str_replace('*','%',$search))->lists('CompanyGatewayID');
                break;
            case 'AccountID':
                $data_in_array = Account::where(array('CompanyID'=>$CompanyID,'Status'=>1,'VerificationStatus'=>2))->where('AccountName','like',str_replace('*','%',$search))->lists('AccountID');
                break;
            case 'VAccountID':
                $data_in_array = Account::where(array('CompanyID'=>$CompanyID,'Status'=>1,'VerificationStatus'=>2))->where('AccountName','like',str_replace('*','%',$search))->lists('AccountID');
                break;
            case 'CountryID':
                $data_in_array = Country::where('Country','like',str_replace('*','%',$search))->lists('CountryID');
                break;
            case 'GatewayAccountPKID':
                $data_in_array = GatewayAccount::where(array('CompanyID'=>$CompanyID))
                    ->where(function($where)use($search){
                        $where->where('AccountIP','like',str_replace('*','%',$search));
                        $where->orwhere('AccountCLI','like',str_replace('*','%',$search));
                    })
                    ->lists('GatewayAccountPKID');
                break;
            case 'GatewayVAccountPKID':
                $data_in_array = GatewayAccount::where(array('CompanyID'=>$CompanyID))
                    ->where(function($where)use($search){
                    $where->where('AccountIP','like',str_replace('*','%',$search));
                    $where->orwhere('AccountCLI','like',str_replace('*','%',$search));
                })->lists('GatewayAccountPKID');
                break;
            case 'CurrencyID':
                $data_in_array = Currency::where(array('CompanyId'=>$CompanyID,'Status'=>1))->where('Code','like',str_replace('*','%',$search))->lists('CurrencyID');
                break;
            case 'TaxRateID':
                $data_in_array = TaxRate::where(array('CompanyId'=>$CompanyID,'Status'=>1))->where('Title','like',str_replace('*','%',$search))->lists('TaxRateId');
                break;
            case 'ProductID':
                $data_in_array = Product::where(array('CompanyId'=>$CompanyID,'Active'=>1))->where('Name','like',str_replace('*','%',$search))->lists('ProductID');
                break;
            case 'ServiceID':
                $data_in_array = Service::where(array('CompanyID'=>$CompanyID,'Active'=>1))->where('ServiceName','like',str_replace('*','%',$search))->lists('ServiceID');
                break;
            case 'SubscriptionID':
                $data_in_array = BillingSubscription::where(array('CompanyId'=>$CompanyID))->where('Name','like',str_replace('*','%',$search))->lists('SubscriptionID');
                break;

        }
        return $data_in_array;
    }

    public static function getDropdownIDList($CompanyID){
        $DropdownIDList = Report::where(array("CompanyID"=>$CompanyID))->lists('Name', 'ReportID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

}