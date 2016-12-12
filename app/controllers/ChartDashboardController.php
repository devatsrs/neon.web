<?php

class ChartDashboardController extends BaseController {

    
    public function __construct() {

    }

    public function getHourlyData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['UserID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $query = "call prc_getHourlyReport ('". $companyID  . "','". $data['UserID']  . "','". $data['Admin']  . "','".$data['AccountID']."')";
        $HourlyChartData = DataTableSql::of($query, 'neon_report')->getProcResult(array('TotalCost','HourCost','TotalMinutes','HourMinutes'));
        $response['TotalCost'] = $HourlyChartData['data']['TotalCost'][0]->TotalCost;
        $response['TotalMinutes'] = $HourlyChartData['data']['TotalMinutes'][0]->TotalMinutes;
        $hourChartCost = $hourChartMinutes = array();
        foreach((array)$HourlyChartData['data']['HourMinutes'] as $HourMinute){
            $hourChartMinutes[] = $HourMinute->TotalMinutes;
        }
        foreach((array)$HourlyChartData['data']['HourCost'] as $HourCost){
            $hourChartCost[] = $HourCost->TotalCost;
        }
        $response['TotalMinutesChart'] =  implode(',',$hourChartMinutes);
        $response['TotalCostChart'] = implode(',',$hourChartCost);
        return $response;
    }
    /* all tab report */
    public function getReportData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['UserID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        if($data['chart_type'] == 'destination') {
            $query = "call prc_getDestinationReport ('" . $companyID . "','" . $data['UserID'] . "','" . $data['Admin'] . "','" . $data['AccountID'] . "')";
        }elseif($data['chart_type'] == 'prefix') {
            $query = "call prc_getPrefixReport ('" . $companyID . "','" . $data['UserID'] . "','" . $data['Admin'] . "','" . $data['AccountID'] . "')";
        }elseif($data['chart_type'] == 'trunk') {
            $query = "call prc_getTrunkReport ('" . $companyID . "','" . $data['UserID'] . "','" . $data['Admin'] . "','" . $data['AccountID'] . "')";
        }elseif($data['chart_type'] == 'gateway') {
            $query = "call prc_getGatewayReport ('" . $companyID . "','" . $data['UserID'] . "','" . $data['Admin'] . "','" . $data['AccountID'] . "')";
        }elseif($data['chart_type'] == 'account') {
            $query = "call prc_getAccountReport ('" . $companyID . "','" . $data['UserID'] . "','" . $data['Admin'] . "','" . $data['AccountID'] . "')";
        }
        $TopReports = DataTableSql::of($query, 'neon_report')->getProcResult(array('CallCount','CallCost','CallMinutes'));
        $customer = 1;
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
        $alldata['call_count_html'] = View::make('dashboard.grid', compact('alldata','data','customer'))->render();


        $indexcount = 0;
        $alldata['grid_type'] = 'cost';
        foreach((array)$TopReports['data']['CallCost'] as $CallCost){
            $alldata['call_cost'][$indexcount] = $CallCost->ChartVal;
            $alldata['call_cost_val'][$indexcount] = $CallCost->TotalCost;
            $alldata['call_cost_acd'][$indexcount] = $CallCost->ACD;
            $alldata['call_cost_asr'][$indexcount] = $CallCost->ASR;
            $indexcount++;
        }
        $alldata['call_cost_html'] = View::make('dashboard.grid', compact('alldata','data','customer'))->render();


        $indexcount = 0;
        $alldata['grid_type'] = 'minutes';
        foreach((array)$TopReports['data']['CallMinutes'] as $CallMinutes){

            $alldata['call_minutes'][$indexcount] = $CallMinutes->ChartVal;
            $alldata['call_minutes_val'][$indexcount] = $CallMinutes->TotalMinutes;
            $alldata['call_minutes_acd'][$indexcount] = $CallMinutes->ACD;
            $alldata['call_minutes_asr'][$indexcount] = $CallMinutes->ASR;
            $indexcount++;
        }
        $alldata['call_minutes_html'] = View::make('dashboard.grid', compact('alldata','data','customer'))->render();
        return chart_reponse($alldata);
    }
    public function getWorldMap(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['UserID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $query = "call prc_getWorldMap ('". $companyID  . "','". $data['UserID']  . "','". $data['Admin']  . "','".$data['AccountID']."')";
        $CountryChartData = DataTableSql::of($query, 'neon_report')->getProcResult(array('CountryCall'));
        $CountryCharts = $CountryColors = array();
        $chartColor = array('#3366cc','#ff9900','#dc3912','#109618','#66aa00','#dd4477','#0099c6','#990099','#ec3b83','#f56954','#0A1EFF','#050FFF','#0000FF');
        $count = 0;
        foreach((array)$CountryChartData['data']['CountryCall'] as $HourMinute){
            if(!isset($chartColor[$count])){
                $count = 0;
            }
            $CountryColors[$HourMinute->ISO_Code] = $chartColor[$count];
            $CountryCharts[$HourMinute->ISO_Code] = $HourMinute;
            $count++;
        }
        $response['CountryColor'] =  $CountryColors;
        $response['CountryChart'] =  $CountryCharts;
        return $response;
    }


}
