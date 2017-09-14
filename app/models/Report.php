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
        'summary'=>'Customer CDR',
        'vsummary'=>'Vendor CDR',
        'invoice' => 'Invoice',
        'payment' => 'Payment'
    );

    public static $dimension = array(
        'summary'=>array(
            'year' => 'Year',
            'quarter_of_year' => 'Quarter' ,
            'month_of_year' => 'Month',
            'week_of_year' => 'Week',
            'date' => 'Day',
            'AccountID' =>'Customer',
            'VAccountID' =>'Vendor',
            'CompanyGatewayID' =>'Gateway',
            'Trunk' => 'Trunk',
            'CountryID' => 'Country',
            'AreaPrefix' => 'Prefix',
            'GatewayAccountPKID' => 'Customer IP/CLI',
            'GatewayVAccountPKID' => 'Vendor IP/CLI'
        ),
        'vsummary'=>array(
            'year' => 'Year',
            'quarter_of_year' => 'Quarter' ,
            'month_of_year' => 'Month',
            'week_of_year' => 'Week',
            'date' => 'Day',
            'AccountID' =>'Customer',
            'VAccountID' =>'Vendor',
            'CompanyGatewayID' =>'Gateway',
            'Trunk' => 'Trunk',
            'CountryID' => 'Country',
            'AreaPrefix' => 'Prefix',
            'GatewayAccountPKID' => 'Customer IP/CLI',
            'GatewayVAccountPKID' => 'Vendor IP/CLI'
        ),
        'invoice'=>array(
            'year' => 'Year',
            'quarter_of_year' => 'Quarter' ,
            'month' => 'Month',
            'week_of_year' => 'Week',
            'date' => 'Day',
            'AccountID' =>'Account',
            'CurrencyID' =>'Currency',
            'InvoiceType' =>'Invoice Type',
            'InvoiceStatus' =>'Invoice Status',
            'TaxRateID' => 'Tax',
            'ProductType'=> 'Item/Subscription/Usage/Oneoffcharge',
            'ProductID' => 'Item',
        ),
        'payment'=>array(
            'year' => 'Year',
            'quarter_of_year' => 'Quarter' ,
            'month' => 'Month',
            'week_of_year' => 'Week',
            'date' => 'Day',
            'AccountID' =>'Account',
            'CurrencyID' =>'Currency',
            'PaymentType'=>'Payment Type',
            'PaymentMethod'=>'Payment Method'
        ),
    );

    public static $measures = array(
        'summary'=>array(
            'TotalCharges' => 'Cost',
            'TotalBilledDuration' => 'Duration',
            'NoOfCalls' => 'No Of Calls',
            'NoOfFailCalls' => 'No Of Failed Calls'
        ),
        'vsummary'=>array(
            'TotalCharges' => 'Cost',
            'TotalSales' => 'Sales',
            'TotalBilledDuration' => 'Duration',
            'NoOfCalls' => 'No Of Calls',
            'NoOfFailCalls' => 'No Of Failed Calls'
        ),
        'invoice'=>array(
            'GrandTotal' => 'Total/Item Total',
            'PaidTotal' => 'Payment',
            'OutStanding' => 'OutStanding',
            'TotalTax' => 'Tax Total',
        ),
        'payment'=>array(
            'Amount' => 'Total',
        ),
    );

    public static $aggregator = array(
        'SUM' => 'Sum',
        'AVG' => 'Average',
        'COUNT' => 'Count',
        'COUNT_DISTINCT' => 'Count(Distinct)',
        'MAX' => 'Maximum',
        'MIN' => 'Minimum',
    );

    public static $condition = array(
        '=' => '=',
        '<>' => '<>',
        '<' => '<',
        '<=' => '<=',
        '>' => '>',
        '>=' => '>=',
    );

    public static $top = array(
        'top' => 'Top',
        'bottom' => 'Bottom',

    );

    public static $date_fields = ['date'];



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
            case 'payment':
                $response = ReportPayment::generateQuery($CompanyID,$data,$filters);
                break;

        }
        return $response;
    }




    public static function getName($PKColumnName,$ID,$all_data){
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

        }
        return $data_in_array;
    }






}