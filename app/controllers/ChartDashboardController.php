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
    /* trunk report */
    public function getTrunkData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['AccountID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $query = "call prc_getTrunkReport ('". $companyID  . "','". $data['UserID']  . "','". $data['Admin']  . "','".$data['AccountID']."')";
        $TrunkReports = DB::connection('neon_report')->select($query);

        /* get top two trunk and rest mark as other*/
        $FirstTrunk = $SecondTrunk = $TrunkHtml = '';
        $trunkcount = 1;
        $otherTrunkCost = $FirstTrunkCost = $SecondTrunkCost = 0;
        foreach((array)$TrunkReports as $TrunkReport){
            if($TrunkReport->Trunk != 'Other' && $trunkcount <= 2 ){
                if($trunkcount == 1){
                    $FirstTrunk = $TrunkReport->Trunk;
                    $FirstTrunkCost = $TrunkReport->TotalCost;
                }
                if($trunkcount == 2){
                    $SecondTrunk = $TrunkReport->Trunk;
                    $SecondTrunkCost = $TrunkReport->TotalCost;
                }
                $trunkcount++;
            }else{
                $otherTrunkCost += $TrunkReport->TotalCost;
            }
        }
        if(count($TrunkReports)) {
            $TrunkHtml = '<span style="color: #3399FF">&#9679;</span> ' . $FirstTrunk . ' - ' . $FirstTrunkCost . ' Sales <br><span style="color: #333399">&#9679;</span> ' . $SecondTrunk . ' - ' . $SecondTrunkCost . ' Sales<br><span style="color: #3366CC">&#9679;</span> Other - ' . $otherTrunkCost.' Sales';
        }else{
            $TrunkHtml = '<h3>NO DATA!!</h3>';
        }
        $TrunkReportarray[] = $FirstTrunkCost;
        $TrunkReportarray[] = $SecondTrunkCost;
        $TrunkReportarray[] = $otherTrunkCost;
        $response['TrunkReport'] = implode(',',$TrunkReportarray);
        $response['FirstTrunk'] = $FirstTrunk;
        $response['SecondTrunk'] = $SecondTrunk;
        $response['TrunkHtml'] = $TrunkHtml;
        return $response;
    }
    /* gateway report */
    public function getGatewayData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['AccountID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $query = "call prc_getGatewayReport ('". $companyID  . "','". $data['UserID']  . "','". $data['Admin']  . "','".$data['AccountID']."')";
        $GatewayReports = DB::connection('neon_report')->select($query);

        /* get gateway cost*/
        $GatewayHtml = '';
        $gatenames = array();
        $gatecost = array();
        $chartColor = array('#333399','#3399FF','#3366CC','#2D89FF','#287AFF','#236BFF','#1E5BFF','#194CFF','#143DFF','#0F2DFF','#0A1EFF','#050FFF','#0000FF');
        $indexcount = 0;
        foreach((array)$GatewayReports as $GatewayReport){
            $gatenames[$indexcount] = CompanyGateway::getCompanyGatewayName($GatewayReport->CompanyGatewayID);
            $gatecost[$indexcount] = $GatewayReport->TotalCost;
            $GatewayHtml .= '<span style="color:' . $chartColor[$indexcount] . ' ">&#9679;</span> ' . $gatenames[$indexcount] . ' - ' . $gatecost[$indexcount].' Sales<br>';
            $indexcount++;
        }
        if(empty($gatenames)){
            $GatewayHtml = '<h3>NO DATA!!</h3>';
        }
        $response['GatewayNames'] = implode(',',$gatenames);
        $response['GatewayCost'] = implode(',',$gatecost);
        $response['GatewayColors'] = implode(',',$chartColor);
        $response['GatewayHtml'] = $GatewayHtml;
        return $response;
    }
    /* prefix report */
    public function getPrefixData(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['AccountID'] = empty($data['AccountID'])?'0':$data['AccountID'];
        $data['UserID'] = empty($data['UserID'])?'0':$data['AccountID'];
        $data['Admin'] = empty($data['Admin'])?'0':$data['Admin'];
        $query = "call prc_getTopPrefix ('". $companyID  . "','". $data['UserID']  . "','". $data['Admin']  . "','".$data['AccountID']."')";
        $TopPrefixReports = DataTableSql::of($query, 'neon_report')->getProcResult(array('PrefixCallCount','PrefixCallCost','PrefixCallMinutes'));


        /* get gateway cost*/
        $GatewayHtml = '';
        $gatenames = array();
        $gatecost = array();
        $chartColor = array('#333399','#3399FF','#3366CC','#2D89FF','#287AFF','#236BFF','#1E5BFF','#194CFF','#143DFF','#0F2DFF','#0A1EFF','#050FFF','#0000FF');
        $indexcount = 0;
        $callcountprefix  =   $callcountprefixcost =  $callcostprefix = $callcostprefixcost = $callminutesprefix = $callminutesprefixcost = array();
        $PrefixCallCountHtml = $PrefixCallcostHtml =  $PrefixCallminutesHtml = '';
        foreach((array)$TopPrefixReports['data']['PrefixCallCount'] as $PrefixCallCount){
            $callcountprefix[$indexcount] = $PrefixCallCount->area_prefix;
            $callcountprefixcost[$indexcount] = $PrefixCallCount->CallCount;
            $PrefixCallCountHtml .= '<span style="color:' . $chartColor[$indexcount] . ' ">&#9679;</span> ' . $PrefixCallCount->area_prefix . ' - ' . $PrefixCallCount->CallCount.' No Of calls<br>';
            $indexcount++;
        }
        $indexcount = 0;
        foreach((array)$TopPrefixReports['data']['PrefixCallCost'] as $PrefixCallCost){
            $callcostprefix[$indexcount] = $PrefixCallCost->area_prefix;
            $callcostprefixcost[$indexcount] = $PrefixCallCost->TotalCost;
            $PrefixCallcostHtml .= '<span style="color:' . $chartColor[$indexcount] . ' ">&#9679;</span> ' . $PrefixCallCost->area_prefix . ' - ' . $PrefixCallCost->TotalCost.' Sales<br>';
            $indexcount++;
        }
        $indexcount = 0;
        foreach((array)$TopPrefixReports['data']['PrefixCallMinutes'] as $PrefixCallMinutes){
            $callminutesprefix[$indexcount] = $PrefixCallMinutes->area_prefix;
            $callminutesprefixcost[$indexcount] = $PrefixCallMinutes->TotalMinutes;
            $PrefixCallminutesHtml .= '<span style="color:' . $chartColor[$indexcount] . ' ">&#9679;</span> ' . $PrefixCallMinutes->area_prefix . ' - ' . $PrefixCallMinutes->TotalMinutes.' Minutes <br>';
            $indexcount++;
        }
        if(empty($callminutesprefix)){
            $PrefixCallminutesHtml = '<h3>NO DATA!!</h3>';
        }
        if(empty($callcostprefix)){
            $PrefixCallcostHtml = '<h3>NO DATA!!</h3>';
        }
        if(empty($callcountprefix)){
            $PrefixCallCountHtml = '<h3>NO DATA!!</h3>';
        }

        $response['PrefixCallCount'] = implode(',',$callcountprefix);
        $response['PrefixCallCountVal'] = implode(',',$callcountprefixcost);
        $response['PrefixCallCountHtml'] =  $PrefixCallCountHtml;

        $response['PrefixCallCost'] = implode(',',$callcostprefix);
        $response['PrefixCallCostVal'] = implode(',',$callcostprefixcost);
        $response['PrefixCallCostHtml'] = $PrefixCallcostHtml;

        $response['PrefixCallMinutes'] = implode(',',$callminutesprefix);
        $response['PrefixCallMinutesVal'] = implode(',',$callminutesprefixcost);
        $response['PrefixCallMinutesHtml'] = $PrefixCallminutesHtml;

        $response['PrefixColors'] = implode(',',$chartColor);

        return $response;
    }


}
