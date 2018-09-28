<?php

class VendorAnalysisController extends BaseController {

    
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
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $Country = Country::getCountryDropdownIDList();
        $account = Account::getAccountIDList();
        $trunks = Trunk::getTrunkDropdownIDList();
        $currency = Currency::getCurrencyDropdownIDList();
        $timezones = TimeZone::getTimeZoneDropdownList();

        return View::make('vendoranalysis.index',compact('gateway','UserID','Country','account','DefaultCurrencyID','original_startdate','original_enddate','isAdmin','trunks','currency','timezones','account_owners'));
    }
    /* all tab report */
    public function getAnalysisData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['UserID'] = empty($data['UserID'])?'0':$data['UserID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $Trunk = Trunk::getTrunkName($data['TrunkID']);
        $query = '';
        if($data['chart_type'] == 'destination') {
            $query = "call prc_getVendorDestinationReportAll ";
        }elseif($data['chart_type'] == 'prefix') {
            $query = "call prc_getVendorPrefixReportAll ";
        }elseif($data['chart_type'] == 'trunk') {
            $query = "call prc_getVendorTrunkReportAll ";
        }elseif($data['chart_type'] == 'gateway') {
            $query = "call prc_getVendorGatewayReportAll ";
        }elseif($data['chart_type'] == 'account') {
            $query = "call prc_getVendorAccountReportAll ";
        }elseif($data['chart_type'] == 'description') {
            $query = "call prc_getVendorDescReportAll ";
        }
        if(!empty($data['TimeZone'])) {
            $CompanyTimezone = Config::get('app.timezone');
            $data['StartDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['StartDate']);
            $data['EndDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['EndDate']);
        }
        $data['tag']=isset($data['tag']) && $data['tag']!=''?$data['tag']:'';

        $query .= "('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "' ,'".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','" . $data['UserID'] . "','" . $data['Admin'] . "'".",0,0,'',''";
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
        $alldata['call_count_html'] = View::make('dashboard.grid', compact('alldata','data','param_array'))->render();


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
        $alldata['call_cost_html'] = View::make('dashboard.grid', compact('alldata','data','param_array'))->render();


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
        $alldata['call_minutes_html'] = View::make('dashboard.grid', compact('alldata','data','param_array'))->render();

        $return=array();
        $return['data']=chart_reponse($alldata);
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
        $data['tag']=isset($data['tag']) && $data['tag']!=''?$data['tag']:'';
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $reponse = array();
        if(!empty($data['TimeZone'])) {
            $CompanyTimezone = Config::get('app.timezone');
            $data['StartDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['StartDate']);
            $data['EndDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['EndDate']);
        }
        $report_type = get_report_type($data['StartDate'],$data['EndDate']);
        $query = "call prc_getVendorReportByTime ('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "','".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','" . $data['UserID'] . "','" . $data['Admin'] . "',".$report_type.",'".$data['tag']."')";
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
        $columns = array('Country','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
        $Trunk = Trunk::getTrunkName($data['TrunkID']);
        $query = '';
        if($data['chart_type'] == 'destination') {
            $columns = array('Country','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getVendorDestinationReportAll ";
        }elseif($data['chart_type'] == 'prefix') {
            $columns = array('AreaPrefix','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getVendorPrefixReportAll ";
        }elseif($data['chart_type'] == 'trunk') {
            $columns = array('Trunk','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getVendorTrunkReportAll ";
        }elseif($data['chart_type'] == 'gateway') {
            $columns = array('Gateway','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getVendorGatewayReportAll ";
        }elseif($data['chart_type'] == 'account') {
            $columns = array('AccountName','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getVendorAccountReportAll ";
        }elseif($data['chart_type'] == 'description') {
            $columns = array('Description','CallCount','TotalMinutes','TotalCost','ACD','ASR','TotalMargin','MarginPercentage');
            $query = "call prc_getVendorDescReportAll ";
        }
        if(!empty($data['TimeZone'])) {
            $CompanyTimezone = Config::get('app.timezone');
            $data['StartDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['StartDate']);
            $data['EndDate'] = change_timezone($data['TimeZone'], $CompanyTimezone, $data['EndDate']);
        }
        $sort_column = $columns[$data['iSortCol_0']];
        $data['tag']=isset($data['tag']) && $data['tag']!=''?$data['tag']:'';

        $query .= "('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "','".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','" . $data['UserID'] . "','" . $data['Admin'] . "'".",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
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


}
