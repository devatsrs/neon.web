<?php

class AnalysisController extends BaseController {

    
    public function __construct() {

    }

    public function index(){
        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = 1;
        $UserID  = User::get_userID();
        $where['Status'] = 1;
        $where['VerificationStatus'] = Account::VERIFIED;
        $where['CompanyID']=User::get_companyID();
        if(User::is('AccountManager')){
            $where['Owner'] = User::get_userID();
            $isAdmin = 0;
        }
        $account_owners = User::getOwnerUsersbyRole();
        $gateway = CompanyGateway::getCompanyGatewayIdList($companyID);
        $Country = Country::getCountryDropdownIDList();
        $account = Account::getAccountIDList();
        $trunks = Trunk::getTrunkDropdownIDList($companyID);
        $currency = Currency::getCurrencyDropdownIDList($companyID);
        $timezones = TimeZone::getTimeZoneDropdownList();
        $MonitorDashboardSetting 	= 	array_filter(explode(',',CompanyConfiguration::get('MONITOR_DASHBOARD')));
        $reseller_owners = Reseller::getDropdownIDList($companyID);
        return View::make('analysis.index',compact('gateway','UserID','Country','account','DefaultCurrencyID','original_startdate','original_enddate','isAdmin','trunks','currency','timezones','MonitorDashboardSetting','account_owners','reseller_owners'));
    }
    /* all tab report */
    public function getAnalysisData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['UserID'] = empty($data['UserID'])?'0':$data['UserID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        $data['tag'] = 	 empty($data['tag'])?'':$data['tag'];
        $Trunk = Trunk::getTrunkName($data['TrunkID']);
        $query = '';
        $customer = 1;
        if($data['chart_type'] == 'destination') {
            $query = "call prc_getDestinationReportAll ";
        }elseif($data['chart_type'] == 'prefix') {
            $query = "call prc_getPrefixReportAll ";
        }elseif($data['chart_type'] == 'trunk') {
            $query = "call prc_getTrunkReportAll ";
        }elseif($data['chart_type'] == 'gateway') {
            $query = "call prc_getGatewayReportAll ";
        }elseif($data['chart_type'] == 'account') {
            $query = "call prc_getAccountReportAll ";
        }elseif($data['chart_type'] == 'description') {
            $query = "call prc_getDescReportAll ";
        }
        if(!empty($data['TimeZone'])) {
            $CompanyTimezone = Config::get('app.timezone');
            $data['StartDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['StartDate']);
            $data['EndDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['EndDate']);
        }

        $query .= "('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['ResellerOwner']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "' ,'".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','".$data['CDRType']."','" . $data['UserID'] . "','" . $data['Admin'] . "'".",0,0,'',''";
        $query .= ",2,'".$data['tag']."')";
        $TopReports = DataTableSql::of($query, 'neon_report')->getProcResult(array('CallCount','CallCost','CallMinutes'));

        $indexcount = 0;
        $alldata = array();
        $alldata['grid_type'] = 'call_count';
        $alldata['call_count_html'] = $alldata['call_cost_html'] =  $alldata['call_minutes_html'] = '';
        $alldata['call_count'] =  $alldata['call_cost'] = $alldata['call_minutes'] = array();
        foreach((array)$TopReports['data']['CallCount'] as $CallCount){
            $alldata['call_count'][$indexcount] = $CallCount->ChartVal;
            $alldata['call_count_val'][$indexcount] = $CallCount->CallCount;
            $alldata['call_count_acd'][$indexcount] = $CallCount->ACD;
            $alldata['call_count_asr'][$indexcount] = $CallCount->ASR;
            $alldata['call_count_mar'][$indexcount] = $CallCount->TotalMargin;
            $alldata['call_count_marp'][$indexcount] = $CallCount->MarginPercentage;
            $indexcount++;
        }
        $param_array = array_diff_key($data,array('map_url'=>0,'pageSize'=>0,'UserID'=>0,'Admin'=>0,'chart_type'=>0,'TimeZone'=>0,'CountryID'=>0));
        $alldata['call_count_html'] = View::make('dashboard.grid', compact('alldata','data','customer','param_array'))->render();


        $indexcount = 0;
        $alldata['grid_type'] = 'cost';
        foreach((array)$TopReports['data']['CallCost'] as $CallCost){
            $alldata['call_cost'][$indexcount] = $CallCost->ChartVal;
            $alldata['call_cost_val'][$indexcount] = $CallCost->TotalCost;
            $alldata['call_cost_acd'][$indexcount] = $CallCost->ACD;
            $alldata['call_cost_asr'][$indexcount] = $CallCost->ASR;
            $alldata['call_cost_mar'][$indexcount] = $CallCost->TotalMargin;
            $alldata['call_cost_marp'][$indexcount] = $CallCost->MarginPercentage;
            $indexcount++;
        }
        $alldata['call_cost_html'] = View::make('dashboard.grid', compact('alldata','data','customer','param_array'))->render();


        $indexcount = 0;
        $alldata['grid_type'] = 'minutes';
        foreach((array)$TopReports['data']['CallMinutes'] as $CallMinutes){

            $alldata['call_minutes'][$indexcount] = $CallMinutes->ChartVal;
            $alldata['call_minutes_val'][$indexcount] = $CallMinutes->TotalMinutes;
            $alldata['call_minutes_acd'][$indexcount] = $CallMinutes->ACD;
            $alldata['call_minutes_asr'][$indexcount] = $CallMinutes->ASR;
            $alldata['call_minutes_mar'][$indexcount] = $CallMinutes->TotalMargin;
            $alldata['call_minutes_marp'][$indexcount] = $CallMinutes->MarginPercentage;

            $indexcount++;
        }
        $alldata['call_minutes_html'] = View::make('dashboard.grid', compact('alldata','data','customer','param_array'))->render();
        $return=array();
        $return["data"]=chart_reponse($alldata);
        $return['html']=[
            "total_calls" => cus_lang("CUST_PANEL_PAGE_ANALYSIS_HEADER_ANALYSIS_DATA_LBL_TOTAL_CALLS"),
            "total_sales" => cus_lang("CUST_PANEL_PAGE_ANALYSIS_HEADER_ANALYSIS_DATA_LBL_TOTAL_SALES"),
            "total_minutes" => cus_lang("CUST_PANEL_PAGE_ANALYSIS_HEADER_ANALYSIS_DATA_LBL_TOTAL_MINUTES"),
        ];

        return $return;
    }
    public function getAnalysisBarData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $Trunk = Trunk::getTrunkName($data['TrunkID']);
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['CompanyGatewayID'] = empty($data['CompanyGatewayID'])?'0':$data['CompanyGatewayID'];
        $data['tag'] = 	 empty($data['tag'])?'':$data['tag'];


        $reponse = array();
        if(!empty($data['TimeZone'])) {
            $CompanyTimezone = Config::get('app.timezone');
            $data['StartDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['StartDate']);
            $data['EndDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['EndDate']);
        }
        $report_type = get_report_type($data['StartDate'],$data['EndDate']);
        $query = "call prc_getReportByTime ('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['ResellerOwner']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "','".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','".$data['CDRType']."','" . $data['UserID'] . "','" . $data['Admin'] . "',".$report_type.",'".$data['tag']."')";
        $TopReports = DB::connection('neon_report')->select($query);
        $series = $category1 = $category2 = $category3 = array();
        $cat_index = 0;
        foreach($TopReports as $TopReport){
            $category1[$cat_index]['name'] = $TopReport->category;
            $category1[$cat_index]['y'] = $TopReport->CallCount;

            $category2[$cat_index]['name'] = $TopReport->category;
            $category2[$cat_index]['y'] = $TopReport->TotalCost;

            $category3[$cat_index]['name'] = $TopReport->category;
            $category3[$cat_index]['y'] = $TopReport->TotalMinutes;

            if($report_type != 1) {
                $category1[$cat_index]['drilldown'] = $TopReport->category;
                $category2[$cat_index]['drilldown'] = $TopReport->category;
                $category3[$cat_index]['drilldown'] = $TopReport->category;
            }
            $cat_index++;
        }
        if(!empty($category1)) {
            $series[] = array('name' => cus_lang("CUST_PANEL_PAGE_ANALYSIS_LBL_CALL_COUNT"), 'data' => $category1, 'color' => '#3366cc');
            $series[] = array('name' => cus_lang("CUST_PANEL_PAGE_ANALYSIS_LBL_CALL_COST"), 'data' => $category2, 'color' => '#ff9900');
            $series[] = array('name' => cus_lang("CUST_PANEL_PAGE_ANALYSIS_LBL_CALL_MINUTES"), 'data' => $category3, 'color' => '#dc3912');
        }
        $reponse['series'] = $series;
        $reponse['Title'] = get_report_title($report_type);
        return json_encode($reponse,JSON_NUMERIC_CHECK);



    }
    public function ajax_datagrid($type){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = array('Country','CallCount','TotalMinutes','TotalCost','ACD','ASR');
        $Trunk = Trunk::getTrunkName($data['TrunkID']);
        $data['StartDate'] = empty($data['StartDate'])?date('Y-m-d 00:00:00'):$data['StartDate'];
        $data['EndDate'] = empty($data['EndDate'])?date('Y-m-d 23:59:59'):$data['EndDate'];
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        $data['tag'] = 	 empty($data['tag'])?'':$data['tag'];
        $query = '';
        if($data['chart_type'] == 'destination') {
            $columns = array('Country','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getDestinationReportAll ";
        }elseif($data['chart_type'] == 'prefix') {
            $columns = array('AreaPrefix','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getPrefixReportAll ";
        }elseif($data['chart_type'] == 'trunk') {
            $columns = array('Trunk','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getTrunkReportAll ";
        }elseif($data['chart_type'] == 'gateway') {
            $columns = array('Gateway','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getGatewayReportAll ";
        }elseif($data['chart_type'] == 'account') {
            $columns = array('AccountName','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getAccountReportAll ";
        }elseif($data['chart_type'] == 'description') {
            $columns = array('Description','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getDescReportAll ";
        }
        if(!empty($data['TimeZone'])) {
            $CompanyTimezone = Config::get('app.timezone');
            $data['StartDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['StartDate']);
            $data['EndDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['EndDate']);
        }
        $sort_column = $columns[$data['iSortCol_0']];

        $query .= "('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['ResellerOwner']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "','".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','".$data['CDRType']."','" . $data['UserID'] . "','" . $data['Admin'] . "'".",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        log::info($query);
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('neon_report')->select($query.',1,"'.$data['tag'].'")');
            $excel_data = json_decode(json_encode($excel_data),true);
            if ($type == 'csv') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/'.ucfirst($data['chart_type']).'Reports.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            } elseif ($type == 'xlsx') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/'.ucfirst($data['chart_type']).'Reports.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .= ",0,'".$data['tag']."')";
        return DataTableSql::of($query,'neon_report')->make();
    }
    public function customer_index(){
        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = 1;
        $UserID  = 0;
        $gateway = CompanyGateway::getCompanyGatewayIdList($companyID);
        $Country = Country::getCountryDropdownIDList();
        $trunks = Trunk::getTrunkDropdownIDList($companyID);
        $currency = Currency::getCurrencyDropdownIDList($companyID);
        $is_customer = Customer::get_currentUser()->IsCustomer;
        $is_vendor = Customer::get_currentUser()->IsVendor;
        $CurrencyID = Customer::get_currentUser()->CurrencyId;
        $timezones = TimeZone::getTimeZoneDropdownList();
        $MonitorDashboardSetting 	= 	array_filter(explode(',',CompanyConfiguration::get('CUSTOMER_MONITOR_DASHBOARD')));

        return View::make('customer.analysis.index',compact('gateway','UserID','Country','account','DefaultCurrencyID','original_startdate','original_enddate','isAdmin','trunks','currency','is_customer','is_vendor','CurrencyID','timezones','MonitorDashboardSetting'));
    }
    public function vendor_index(){
        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = 1;
        $UserID  = 0;
        $gateway = CompanyGateway::getCompanyGatewayIdList($companyID);
        $Country = Country::getCountryDropdownIDList();
        $trunks = Trunk::getTrunkDropdownIDList($companyID);
        $currency = Currency::getCurrencyDropdownIDList($companyID);
        $is_customer = Customer::get_currentUser()->IsCustomer;
        $is_vendor = Customer::get_currentUser()->IsVendor;
        $CurrencyID = Customer::get_currentUser()->CurrencyId;
        $timezones = TimeZone::getTimeZoneDropdownList();
        return View::make('customer.analysis.vendorindex',compact('gateway','UserID','Country','account','DefaultCurrencyID','original_startdate','original_enddate','isAdmin','trunks','currency','is_customer','is_vendor','CurrencyID','timezones'));
    }

    public function getAnalysisManager(){
        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID", $companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = 1;
        $UserID = User::get_userID();
        $where['Status'] = 1;
        $where['VerificationStatus'] = Account::VERIFIED;
        $where['CompanyID'] = User::get_companyID();
        if (User::is('AccountManager')) {
            $where['Owner'] = User::get_userID();
            $isAdmin = 0;
        }
        $users = User::getUserIDListAll(0);

        $gateway = CompanyGateway::getCompanyGatewayIdList($companyID);
        $Country = Country::getCountryDropdownIDList();
        $account = Account::getAccountIDList();
        $trunks = Trunk::getTrunkDropdownIDList($companyID);
        $currency = Currency::getCurrencyDropdownIDList($companyID);
        $timezones = TimeZone::getTimeZoneDropdownList();
        $MonitorDashboardSetting = array_filter(explode(',', CompanyConfiguration::get('MONITOR_DASHBOARD')));

        return View::make('analysis.accountmanagerindex', compact('gateway', 'UserID', 'Country', 'account', 'DefaultCurrencyID', 'original_startdate', 'original_enddate', 'isAdmin', 'trunks', 'currency', 'timezones', 'MonitorDashboardSetting', 'users'));

    }

    public function get_leads($type){
        $companyID = User::get_companyID();
        $data = Input::all();
        $account = Account::select('AccountName',DB::raw("concat(tblUser.FirstName,' ',tblUser.LastName) as AccountManager"),'LeadStatus', 'tblAccount.created_at')
            ->leftjoin('tblUser','tblUser.UserID','=','tblAccount.Owner')
            ->where(["AccountType"=> 0,"tblAccount.CompanyID"=>$companyID]);
        if(isset($data['Admin']) && isset($data['UserID']) && $data['Admin'] == '0' && $data['UserID'] > 0 ){
            $account->whereIn("tblAccount.Owner",explode(',',$data['UserID']));
        }
        if(isset($data['ActiveLead']) && $data['ActiveLead'] == 'Yes'){
            $account->where(["tblAccount.Status"=>1]);
        }else{
            $account->where(["tblAccount.Status"=>0]);
        }

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = $account->get();
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Leads.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Leads.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        return Datatables::of($account)
            ->editColumn('created_at', '{{\Carbon\Carbon::createFromTimeStamp(strtotime($created_at))->diffForHumans()}}')
            ->make();
    }

    public function get_account($type){
        $companyID = User::get_companyID();
        $data = Input::all();
        $account = Account::select('AccountName',DB::raw("concat(tblUser.FirstName,' ',tblUser.LastName) as AccountManager"), 'tblAccount.created_at')
            ->leftjoin('tblUser','tblUser.UserID','=','tblAccount.Owner')
            ->where(["AccountType"=> 1,"tblAccount.CompanyID"=>$companyID]);
        if(isset($data['Admin']) && isset($data['UserID']) && $data['Admin'] == '0' && $data['UserID'] > 0 ){
            $account->whereIn("tblAccount.Owner",explode(',',$data['UserID']));
        }
        if(isset($data['ActiveAccount']) && $data['ActiveAccount'] == 'Yes'){
            $account->where(["tblAccount.Status"=>1,"VerificationStatus"=>Account::VERIFIED]);
        }else{
            $account->where(["tblAccount.Status"=>0]);
        }

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = $account->get();
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Accounts.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Accounts.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }

        return Datatables::of($account)
            ->editColumn('created_at', '{{\Carbon\Carbon::createFromTimeStamp(strtotime($created_at))->diffForHumans()}}')
            ->make();
    }

    public function get_account_manager_revenue($type){
        $companyID = User::get_companyID();
        $data = Input::all();
        $columns = array('UserName','TIMEVAL','TotalCost','TotalMargin','MarginPercentage');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getAccountManager (" . $companyID . "," . intval($data['CurrencyID']) . ",'" . $data['StartDate'] . "','" . $data['EndDate'] . "','" . $data['UserID'] . "'," . $data['Admin'] . ",'" . $data['RevenueListType'] . "','".$sort_column."','".$data['sSortDir_0']."'";

        if (isset($data['Export']) && $data['Export'] == 1) {
            $excel_data = DB::connection('neon_report')->select($query . ',1)');
            $excel_data = json_decode(json_encode($excel_data), true);
            if ($type == 'csv') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/Revenue.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            } elseif ($type == 'xlsx') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/Revenue.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';
        return DataTableSql::of($query,'neon_report')->make();
    }

    public function get_account_manager_margin($type){
        $companyID = User::get_companyID();
        $data = Input::all();
        $columns = array('UserName','TIMEVAL','TotalCost','TotalMargin','MarginPercentage');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getAccountManager (" . $companyID . "," . intval($data['CurrencyID']) . ",'" . $data['StartDate'] . "','" . $data['EndDate'] . "','" . $data['UserID'] . "'," . $data['Admin'] . ",'" . $data['MarginListType'] . "','".$sort_column."','".$data['sSortDir_0']."'";

        if (isset($data['Export']) && $data['Export'] == 1) {
            $excel_data = DB::connection('neon_report')->select($query . ',1)');
            $excel_data = json_decode(json_encode($excel_data), true);
            if ($type == 'csv') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/Margin.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            } elseif ($type == 'xlsx') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/Margin.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';
        return DataTableSql::of($query,'neon_report')->make();

    }
    public function get_account_manager_revenue_report(){
        $companyID = User::get_companyID();
        $data = Input::all();
        $UserID = is_array($data['UserID'])?implode(',',$data['UserID']):intval($data['UserID']);
        $query = "call prc_getAccountManager (" . $companyID . "," . intval($data['CurrencyID']) . ",'" . $data['StartDate'] . "','" . $data['EndDate'] . "','" . $UserID . "'," . $data['Admin'] . ",'" . $data['RevenueListType'] . "','','',0)";
        $TopReports = DB::connection('neon_report')->select($query);
        $series = $category = array();
        foreach($TopReports as $TopReport){
            $category['name'][$TopReport->UserName] = 1;
            if (empty(${'count_' . $TopReport->UserName})) {
                ${'count_' . $TopReport->UserName} = 0;
            }
            $category[$TopReport->UserName][${'count_' . $TopReport->UserName}]['name'] = $TopReport->TIMEVAL;
            $category[$TopReport->UserName][${'count_' . $TopReport->UserName}]['y'] = $TopReport->TotalCost;
            ${'count_' . $TopReport->UserName}++;
        }
        if(isset($category['name'])) {
            foreach ($category['name'] as $manager => $index) {
                $series[] = array('name' => $manager, 'data' => $category[$manager]);
            }
        }
        $reponse['series'] = $series;
        return json_encode($reponse,JSON_NUMERIC_CHECK);

    }
    public function get_account_manager_margin_report(){
        $companyID = User::get_companyID();
        $data = Input::all();
        $UserID = is_array($data['UserID'])?implode(',',$data['UserID']):intval($data['UserID']);
        $query = "call prc_getAccountManager (" . $companyID . "," . intval($data['CurrencyID']) . ",'" . $data['StartDate'] . "','" . $data['EndDate'] . "','" . $UserID . "'," . $data['Admin'] . ",'" . $data['MarginListType'] . "','','',0)";
        $TopReports = DB::connection('neon_report')->select($query);
        $series = $category = array();
        $cat_index = 0;
        foreach($TopReports as $TopReport){
            $category['name'][$TopReport->UserName] = 1;
            if (empty(${'count_' . $TopReport->UserName})) {
                ${'count_' . $TopReport->UserName} = 0;
            }
            $category[$TopReport->UserName][${'count_' . $TopReport->UserName}]['name'] = $TopReport->TIMEVAL;
            $category[$TopReport->UserName][${'count_' . $TopReport->UserName}]['y'] = $TopReport->TotalMargin;
            ${'count_' . $TopReport->UserName}++;
        }
        if(isset($category['name'])) {
            foreach ($category['name'] as $manager => $index) {
                $series[] = array('name' => $manager, 'data' => $category[$manager]);
            }
        }
        $reponse['series'] = $series;
        return json_encode($reponse,JSON_NUMERIC_CHECK);
    }

    public function account_revenue_margin($type){
        $companyID = User::get_companyID();
        $data = Input::all();
        $columns = array('UserName','AccountName','TIMEVAL','TotalCost','TotalMargin','MarginPercentage');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getAccountReport (" . $companyID . "," . intval($data['CurrencyID']) . ",'" . $data['StartDate'] . "','" . $data['EndDate'] . "','" . $data['UserID'] . "'," . $data['Admin'] . ",'" . $data['AccountListType'] . "','".$sort_column."','".$data['sSortDir_0']."'";

        if (isset($data['Export']) && $data['Export'] == 1) {
            $excel_data = DB::connection('neon_report')->select($query . ',1)');
            $excel_data = json_decode(json_encode($excel_data), true);
            if ($type == 'csv') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/RevenueByAccount.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            } elseif ($type == 'xlsx') {
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/RevenueByAccount.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';
        return DataTableSql::of($query,'neon_report')->make();
    }

}
