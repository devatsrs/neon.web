<?php

class ChartDashboardController extends BaseController {

    
    public function __construct() {

    }

    public function getHourlyData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['UserID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $data['CDRType'] = empty($data['CDRType'])?'':$data['CDRType'];
        $query = "call prc_getHourlyReport ('". $companyID  . "','". $data['UserID']  . "','". $data['Admin']  . "','".$data['AccountID']."','".$data['ResellerOwner']."','".date('Y-m-d 00:00:00') . "','".date('Y-m-d 23:59:59') . "','". $data['CDRType']  . "')";
        $HourlyChartData = DataTableSql::of($query, 'neon_report')->getProcResult(array('TotalCost','HourCost','TotalMinutes','HourMinutes'));
        $response['TotalCost'] = $HourlyChartData['data']['TotalCost'][0]->TotalCost;
        $response['TotalMinutes'] = $HourlyChartData['data']['TotalMinutes'][0]->TotalMinutes;
        $hourChartCost = $hourChartMinutes = $minutesTitle = $costTitle = array();
        foreach((array)$HourlyChartData['data']['HourMinutes'] as $HourMinute){
            $hourChartMinutes[] = $HourMinute->TotalMinutes;
            $minutesTitle[] = $HourMinute->HOUR.' Hour';
        }
        foreach((array)$HourlyChartData['data']['HourCost'] as $HourCost){
            $hourChartCost[] = $HourCost->TotalCost;
            $costTitle[] = $HourCost->HOUR.' Hour';
        }
        $response['TotalMinutesChart'] =  implode(',',$hourChartMinutes);
        $response['TotalCostChart'] = implode(',',$hourChartCost);
        $response['costTitle'] = implode(',',$costTitle);
        $response['minutesTitle'] = implode(',',$minutesTitle);
        $response['no_data'] = cus_lang("MESSAGE_DATA_NOT_AVAILABLE");
        return $response;
    }
    /* all tab report */
    public function getReportData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['UserID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $query = '';
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
        $query .= "('" . $companyID . "','0','" . intval($data['AccountID']) ."','" . intval($data['ResellerOwner']) ."','0','".date('Y-m-d 00:00:00') . "','".date('Y-m-d 23:59:59') . "' ,'','','0','','" . $data['UserID'] . "','" . $data['Admin'] . "'".",0,0,'','',2,'')";
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
        return chart_reponse($alldata);
    }
    public function getWorldMap(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['UserID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $data['StartDate'] = empty($data['StartDate'])?date('Y-m-d 00:00:00'):$data['StartDate'];
        $data['EndDate'] = empty($data['EndDate'])?date('Y-m-d 23:59:59'):$data['EndDate'];
        $data['CompanyGatewayID'] = empty($data['CompanyGatewayID'])?'0':$data['CompanyGatewayID'];
        $data['CurrencyID'] = empty($data['CurrencyID'])?'0':$data['CurrencyID'];
        $data['CountryID'] = empty($data['CountryID'])?'0':$data['CountryID'];
        $data['Prefix'] = empty($data['Prefix'])?'':$data['Prefix'];
        $data['CDRType'] = empty($data['CDRType'])?'':$data['CDRType'];
        $Trunk = empty($data['TrunkID'])?'':Trunk::getTrunkName($data['TrunkID']);
        $data['tag'] = 	 (isset($data['tag']) && !empty($data['tag']))?$data['tag']:'';
        $query = "call prc_getWorldMap ('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['ResellerOwner']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "','".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','".$data['CDRType']."','" . $data['UserID'] . "','" . $data['Admin'] . "','".$data['tag']."')";
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
        $response['lang_labels'] =  [
            "calls"=>Lang::get("routes.PAGE_DASHBOARD_DATA_WORLDMAP_LBL_CALLS"),
            "cost"=>Lang::get("routes.PAGE_DASHBOARD_DATA_WORLDMAP_LBL_COST"),
            "minutes"=>Lang::get("routes.PAGE_DASHBOARD_DATA_WORLDMAP_LBL_MINUTES"),
            "acd"=>Lang::get("routes.PAGE_DASHBOARD_DATA_WORLDMAP_LBL_ACD"),
            "asr"=>Lang::get("routes.PAGE_DASHBOARD_DATA_WORLDMAP_LBL_ASR"),
            "totalmargin"=>Lang::get("routes.PAGE_DASHBOARD_DATA_WORLDMAP_LBL_TOTAL_MARGIN"),
            "margin"=>Lang::get("routes.PAGE_DASHBOARD_DATA_WORLDMAP_LBL_MARGIN"),
        ];
        return $response;
    }
    public function getVendorWorldMap(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['UserID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $data['StartDate'] = empty($data['StartDate'])?date('Y-m-d 00:00:00'):$data['StartDate'];
        $data['EndDate'] = empty($data['EndDate'])?date('Y-m-d 23:59:59'):$data['EndDate'];
        $data['CompanyGatewayID'] = empty($data['CompanyGatewayID'])?'0':$data['CompanyGatewayID'];
        $data['CurrencyID'] = empty($data['CurrencyID'])?'0':$data['CurrencyID'];
        $data['CountryID'] = empty($data['CountryID'])?'0':$data['CountryID'];
        $data['Prefix'] = empty($data['Prefix'])?'':$data['Prefix'];
        $data['tag']=isset($data['tag']) && $data['tag']!=''?$data['tag']:'';

        $Trunk = empty($data['TrunkID'])?'':Trunk::getTrunkName($data['TrunkID']);
        $query = "call prc_getVendorWorldMap ('" . $companyID . "','".intval($data['CompanyGatewayID']) . "','" . intval($data['AccountID']) ."','" . intval($data['CurrencyID']) ."','".$data['StartDate'] . "','".$data['EndDate'] . "','".$data['Prefix']."','".$Trunk."','".intval($data['CountryID']) . "','" . $data['UserID'] . "','" . $data['Admin'] . "','".$data['tag']."')";
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

    public function getMonitorDashboradCall(){
        $data = Input::all();
        $companyID = User::get_companyID();
        //$data['StartDate'] = '2016-12-01';
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['ResellerOwner'] = empty($data['ResellerOwner'])?'0':$data['ResellerOwner'];
        $data['StartDate'] = empty($data['StartDate'])?date('Y-m-d 00:00:00'):$data['StartDate'];
        $data['EndDate'] = empty($data['EndDate'])?date('Y-m-d 23:59:59'):$data['EndDate'];
        $data['Type'] = empty($data['Type'])?'':$data['Type'];
        $data['tag']=isset($data['tag']) && $data['tag']!=''?$data['tag']:'';
        $html = '';

        $query = "call prc_RetailMonitorCalls ('" . $companyID . "','".intval($data['AccountID']) . "','".intval($data['ResellerOwner']) . "','".$data['StartDate'] . "','".$data['EndDate'] . "','".$data['Type']."','".$data['tag']."')";
        //log::info($query);
        $RetailMonitorCalls = DB::connection('sqlsrvcdr')->select($query);
        $count = 1;
        foreach($RetailMonitorCalls as $RetailMonitorCall){
            if($data['Type'] == 'call_duraition') {
                $html .= '<tr>
                        <td>' . $count . '</td>
                        <td>' . $RetailMonitorCall->cld . '</td>
                        <td>' . $RetailMonitorCall->billed_duration . '</td>
                    </tr>';
            }
            if($data['Type'] == 'call_cost') {
                $html .= '<tr>
                        <td>' . $count . '</td>
                        <td>' . $RetailMonitorCall->cld . '</td>
                        <td>' . $RetailMonitorCall->cost . '</td>
                        <td>' . $RetailMonitorCall->billed_duration . '</td>
                    </tr>';

            }
            if($data['Type'] == 'most_dialed') {
                $html .= '<tr>
                        <td>' . $count . '</td>
                        <td>' . $RetailMonitorCall->cld . '</td>
                        <td>' . $RetailMonitorCall->dail_count . '</td>
                        <td>' . $RetailMonitorCall->billed_duration . '</td>
                    </tr>';

            }
            $count++;
        }
        if(empty($html)){
            $html = '<tr><td colspan="'.($data['Type'] == 'call_cost'?5:4).'" valign="top">'.Lang::get("routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_NO_DATA").'</td></tr>';
        }
        $response['html'] = $html;
        return $response;
    }


}
