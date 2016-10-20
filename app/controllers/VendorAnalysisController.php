<?php

class VendorAnalysisController extends BaseController {

    
    public function __construct() {

    }

    public function index(){
        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = User::is_admin();
        $UserID  = User::get_userID();
        $where['Status'] = 1;
        $where['VerificationStatus'] = Account::VERIFIED;
        $where['CompanyID']=User::get_companyID();
        if(User::is('AccountManager')){
            $where['Owner'] = User::get_userID();
        }
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $Country = Country::getCountryDropdownIDList();
        $account = Account::getAccountIDList();
        $trunks = Trunk::getTrunkDropdownIDList();
        $currency = Currency::getCurrencyDropdownIDList();

        return View::make('vendoranalysis.index',compact('gateway','UserID','Country','account','DefaultCurrencyID','original_startdate','original_enddate','isAdmin','trunks','currency'));
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
        }
        $query .= "('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "' ,'".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','" . $data['UserID'] . "','" . $data['Admin'] . "'".",0,0,'',''";
        $query .= ",2)";
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
            $indexcount++;
        }
        $alldata['call_count_html'] = View::make('dashboard.grid', compact('alldata','data'))->render();


        $indexcount = 0;
        $alldata['grid_type'] = 'cost';
        foreach((array)$TopReports['data']['CallCost'] as $CallCost){
            $alldata['call_cost'][$indexcount] = $CallCost->ChartVal;
            $alldata['call_cost_val'][$indexcount] = $CallCost->TotalCost;
            $alldata['call_cost_acd'][$indexcount] = $CallCost->ACD;
            $alldata['call_cost_asr'][$indexcount] = $CallCost->ASR;
            $indexcount++;
        }
        $alldata['call_cost_html'] = View::make('dashboard.grid', compact('alldata','data'))->render();


        $indexcount = 0;
        $alldata['grid_type'] = 'minutes';
        foreach((array)$TopReports['data']['CallMinutes'] as $CallMinutes){

            $alldata['call_minutes'][$indexcount] = $CallMinutes->ChartVal;
            $alldata['call_minutes_val'][$indexcount] = $CallMinutes->TotalMinutes;
            $alldata['call_minutes_acd'][$indexcount] = $CallMinutes->ACD;
            $alldata['call_minutes_asr'][$indexcount] = $CallMinutes->ASR;

            $indexcount++;
        }
        $alldata['call_minutes_html'] = View::make('dashboard.grid', compact('alldata','data'))->render();
        return chart_reponse($alldata);
    }
    public function getAnalysisBarData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $Trunk = Trunk::getTrunkName($data['TrunkID']);
        $reponse = array();
        $report_type = get_report_type($data['StartDate'],$data['EndDate']);
        $query = "call prc_getVendorReportByTime ('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "','".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','" . $data['UserID'] . "','" . $data['Admin'] . "',".$report_type.")";
        $TopReports = DB::connection('neon_report')->select($query);
        $category = $counts = $minutes = $cost = array();
        $cat_index = 0;
        foreach($TopReports as $TopReport){
            $category[$cat_index] = $TopReport->category;
            $counts[$cat_index] = $TopReport->CallCount;
            $minutes[$cat_index] = $TopReport->TotalMinutes;
            $cost[$cat_index] = $TopReport->TotalCost;
            $cat_index++;
        }
        $reponse['categories'] = implode(',',$category);
        $reponse['CallCount'] = implode(',',$counts);
        $reponse['CallCost'] = implode(',',$cost);
        $reponse['CallMinutes'] = implode(',',$minutes);
        $reponse['Title'] = get_report_title($report_type);
        return $reponse;



    }
    public function ajax_datagrid($type){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = array('Country','CallCount','TotalMinutes','TotalCost','ACD','ASR');
        $Trunk = Trunk::getTrunkName($data['TrunkID']);
        $query = '';
        if($data['chart_type'] == 'destination') {
            $columns = array('Country','CallCount','TotalMinutes','TotalCost','ACD','ASR');
            $query = "call prc_getVendorDestinationReportAll ";
        }elseif($data['chart_type'] == 'prefix') {
            $columns = array('AreaPrefix','CallCount','TotalMinutes','TotalCost','ACD','ASR');
            $query = "call prc_getVendorPrefixReportAll ";
        }elseif($data['chart_type'] == 'trunk') {
            $columns = array('Trunk','CallCount','TotalMinutes','TotalCost','ACD','ASR');
            $query = "call prc_getVendorTrunkReportAll ";
        }elseif($data['chart_type'] == 'gateway') {
            $columns = array('Gateway','CallCount','TotalMinutes','TotalCost','ACD','ASR');
            $query = "call prc_getVendorGatewayReportAll ";
        }elseif($data['chart_type'] == 'account') {
            $columns = array('AccountName','CallCount','TotalMinutes','TotalCost','ACD','ASR');
            $query = "call prc_getVendorAccountReportAll ";
        }
        $sort_column = $columns[$data['iSortCol_0']];

        $query .= "('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "','".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','" . $data['UserID'] . "','" . $data['Admin'] . "'".",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('neon_report')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if ($type == 'csv') {
                $file_path = getenv('UPLOAD_PATH') . '/'.ucfirst($data['chart_type']).'Reports.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            } elseif ($type == 'xlsx') {
                $file_path = getenv('UPLOAD_PATH') . '/'.ucfirst($data['chart_type']).'Reports.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .= ",0)";
        return DataTableSql::of($query,'neon_report')->make();
    }


}
