<?php

class ChartDashboardController extends BaseController {

    
    public function __construct() {

    }

    public function getHourlyData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['AccountID'];
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
        $data['UserID'] = empty($data['UserID'])?'0':$data['AccountID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        if($data['chart_type'] == 'destination') {
            $query = "call prc_getDestinationReport ('" . $companyID . "','" . $data['UserID'] . "','" . $data['Admin'] . "','" . $data['AccountID'] . "')";
        }elseif($data['chart_type'] == 'prefix') {
            $query = "call prc_getPrefixReport ('" . $companyID . "','" . $data['UserID'] . "','" . $data['Admin'] . "','" . $data['AccountID'] . "')";
        }elseif($data['chart_type'] == 'trunk') {
            $query = "call prc_getTrunkReport ('" . $companyID . "','" . $data['UserID'] . "','" . $data['Admin'] . "','" . $data['AccountID'] . "')";
        }elseif($data['chart_type'] == 'gateway') {
            $query = "call prc_getGatewayReport ('" . $companyID . "','" . $data['UserID'] . "','" . $data['Admin'] . "','" . $data['AccountID'] . "')";
        }
        $TopReports = DataTableSql::of($query, 'neon_report')->getProcResult(array('CallCount','CallCost','CallMinutes'));

        $indexcount = 0;
        $alldata = array();
        $alldata['grid_type'] = 'call_count';
        $alldata['call_count_html'] = $alldata['call_cost_html'] =  $alldata['call_minutes_html'] = '';
        foreach((array)$TopReports['data']['CallCount'] as $CallCount){
            $alldata['call_count'][$indexcount] = $CallCount->ChartVal;
            $alldata['call_count_val'][$indexcount] = $CallCount->CallCount;
            $alldata['call_count_acd'][$indexcount] = $CallCount->ACD;
            $indexcount++;
        }
        $alldata['call_count_html'] = View::make('dashboard.grid', compact('alldata','data'))->render();


        $indexcount = 0;
        $alldata['grid_type'] = 'cost';
        foreach((array)$TopReports['data']['CallCost'] as $CallCost){
            $alldata['call_cost'][$indexcount] = $CallCost->ChartVal;
            $alldata['call_cost_val'][$indexcount] = $CallCost->TotalCost;
            $alldata['call_cost_acd'][$indexcount] = $CallCost->ACD;
            $indexcount++;
        }
        $alldata['call_cost_html'] = View::make('dashboard.grid', compact('alldata','data'))->render();


        $indexcount = 0;
        $alldata['grid_type'] = 'minutes';
        foreach((array)$TopReports['data']['CallMinutes'] as $CallMinutes){

            $alldata['call_minutes'][$indexcount] = $CallMinutes->ChartVal;
            $alldata['call_minutes_val'][$indexcount] = $CallMinutes->TotalMinutes;
            $alldata['call_minutes_acd'][$indexcount] = $CallMinutes->ACD;
            $indexcount++;
        }
        $alldata['call_minutes_html'] = View::make('dashboard.grid', compact('alldata','data'))->render();
        return chart_reponse($alldata);
    }


}
