<?php

class ReportController extends \BaseController {

    public function index(){
        $CompanyID = User::get_companyID();
        $reports = Report::getDropdownIDList($CompanyID);
        return View::make('report.index', compact('reports'));
    }

    public function create(){
       /* $data['column'] = array('AccountID');
        $data['row'] = array('Trunk','CompanyGatewayID');
        $data['sum'] = array('NoOfCalls','TotalCharges');
        $cube = 'summary';

        $response = Report::generateDynamicTable($CompanyID,$cube,$data);
        //print_r($response);exit;
        //echo generateReportTable($data,$response);
        echo generateReportTable2($data,$response);
        //exit;*/

        $dimensions = Report::$dimension;
        $measures = Report::$measures;
        $disable= '';
        $CompanyID = User::get_companyID();
        $reports = Report::getDropdownIDList($CompanyID);
        $Columns = $dimensions['summary']+Report::$measures['summary'];
        $report_settings =array();
        $report_settings['Cube'] = 'summary';
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $report_settings['filter_settings'] = '{"date":{"wildcard_match_val":"","start_date":"'.$original_startdate.'","end_date":"'.$original_enddate.'","condition":"none","top":"none"}}';
        $layout = 'layout.main_only_sidebar';
        /*if(Input::get('report') == 'run'){
            $layout = 'layout.main_only_sidebar';
        }*/
        return View::make('report.create', compact('dimensions','measures','Columns','reports','report_settings','disable','layout'));
    }
    public function edit($id){
        $report = Report::find($id);
        $ReportSchedule = ReportSchedule::where('ReportID',$id)->first();
        $reports = Report::getDropdownIDList($report->CompanyID);
        $report_settings = json_decode($report->Settings,true);
        $setting_rename = isset($report_settings['setting_rename'])?json_decode($report_settings['setting_rename'],true):array();
        $setting_ag = isset($report_settings['setting_ag'])?json_decode($report_settings['setting_ag'],true):array();
        $schedule_settings = array();
        if(!empty($ReportSchedule)) {
            $schedule_settings = json_decode($ReportSchedule->Settings, true);
        }

        $dimensions = Report::$dimension;
        $measures = Report::$measures;

        $disable= 'disabled';
        $Columns = $dimensions['summary']+Report::$measures['summary'];
        $layout = 'layout.main_only_sidebar';
        /*if(Input::get('report') == 'run'){
            $layout = 'layout.main_only_sidebar';
        }*/

        return View::make('report.create', compact('report','dimensions','measures','Columns','report_settings','report','disable','layout','schedule_settings','reports','ReportSchedule','setting_rename','setting_ag'));
    }

    public function report_store(){
        $postdata = Input::all();
        $response =  NeonAPI::request('report/store',$postdata,true,false,false);
        if(!empty($response->data)) {
            return Response::json(array("status" => $response->status, "message" => $response->message, 'LastID' => $response->data->ReportID, 'redirect' => URL::to('/report/edit/' . $response->data->ReportID)));
        }
        return json_response_api($response);
    }
    public function report_delete($id){
        $response =  NeonAPI::request('report/delete/'.$id,array(),'delete',false,false);
        return json_response_api($response);
    }

    public function report_update($id){
        $postdata = Input::all();
        $response =  NeonAPI::request('report/update/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
    public function ajax_datagrid($type) {

        $CompanyID = User::get_companyID();
        $reports = Report::
            leftJoin('tblReportSchedule','tblReportSchedule.ReportID','=',DB::raw('CAST(tblReport.ReportID AS CHAR(255))'))
            ->select('tblReport.Name','tblReport.ReportID','ReportScheduleID','tblReportSchedule.Status','tblReportSchedule.Settings')
            ->where("tblReport.CompanyID", $CompanyID);
        $data = Input::all();
        if(trim($data['Name']) != '') {
            $reports->where('tblReport.Name', 'like','%'.trim($data['Name']).'%');
        }
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = $reports->get();
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Reports.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Reports.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }

        return Datatables::of($reports)->make();
    }

    public function getdatagrid($id=0){
        $data = $data2 = Input::all();
        if($id>0){
            $report = Report::find($id);
            $data = json_decode($report->Settings,true);
            $filters = json_decode($data['filter_settings'],true);
			$StartDate = Input::get('StartDate');
			$EndDate = Input::get('EndDate');
            if(!empty($StartDate)) {
                if (isset($filters['date'])) {
                    $filters['date']['start_date'] = date('Y-m-d',strtotime($StartDate));
                    $filters['date']['end_date'] = date('Y-m-d',strtotime($EndDate));
                } else {
                    $filters['date']['wildcard_match_val'] = '';
                    $filters['date']['start_date'] = date('Y-m-d',strtotime($StartDate));
                    $filters['date']['end_date'] = date('Y-m-d',strtotime($EndDate));
                    $filters['date']['condition'] = 'none';
                    $filters['date']['top'] = 'none';
                }
                if(isset($data2['Time']) && $data2['Time'] == 'HOUR') {
                    if (date('Y-m-d', strtotime($StartDate)) == date('Y-m-d', strtotime($EndDate))) {
                        $filters['Hour']['Hour'] = range(date('H', strtotime($StartDate)), date('H', strtotime($EndDate)));
                    } else {
                        $filters['multiday_hour']['StartDate'] = range(date('H', strtotime($StartDate)), 23);
                        $filters['multiday_hour']['EndDate'] = range(0, date('H', strtotime($EndDate)));
                    }
                }
            }
            $data['filter_settings'] = json_encode($filters);
            $data['Export'] = 1;
            $data['Name'] = $report->Name;
        }
        $CompanyID = User::get_companyID();
        $cube = $data['Cube'];
        $filters = json_decode($data['filter_settings'],true);

        $data['column'] = array_filter(explode(",",$data['column']));
        //$data['sum'] = array_filter(explode(",",$data['Cube']));
        $data['row'] = array_filter(explode(",",$data['row']));
        $data['filter'] = array_filter(explode(",",$data['filter']));
        $data['sum'] = $response = array();

        $measures = array_keys(Report::$measures[$cube]);
        foreach($data['column'] as $measure){
            if(in_array($measure,$measures)){
                $data['sum'][] = $measure;
                if (($key = array_search($measure, $data['column'])) !== false) {
                    unset($data['column'][$key]);
                }
            }

        }
        /*foreach ($measures as $measure){
            if(in_array($measure,$data['column'])){
                $data['sum'][] = $measure;
            }
            if(in_array($measure,$data['row'])){
                $data['sum'][] = $measure;
            }
            if (($key = array_search($measure, $data['column'])) !== false) {
                unset($data['column'][$key]);
            }
            if (($key = array_search($measure, $data['row'])) !== false) {
                unset($data['row'][$key]);
            }
        }*/
        $data['column'] = array_values($data['column']);
        $data['row'] = array_values($data['row']);
        $all_data_list['CompanyGateway'] = CompanyGateway::getCompanyGatewayIdList($CompanyID);
        $all_data_list['Country'] = Country::getCountryDropdownIDList();
        $all_data_list['Currency'] = Currency::getCurrencyDropdownIDList($CompanyID);
        $all_data_list['Tax'] = TaxRate::getTaxRateDropdownIDList($CompanyID);
        $all_data_list['Product'] = Product::getProductDropdownList($CompanyID);
        $all_data_list['Account'] = Account::where(['Status'=>1,'CompanyID'=>$CompanyID,'AccountType'=>1,'VerificationStatus'=>Account::VERIFIED])->select(array('AccountName', 'AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
        //$all_data_list['Account'] = Account::getAccountIDList();
        $all_data_list['AccountIP'] = GatewayAccount::getAccountIPList($CompanyID);
        $all_data_list['AccountCLI'] = GatewayAccount::getAccountCLIList($CompanyID);
        $all_data_list['Service'] = Service::getDropdownIDList($CompanyID);
        $all_data_list['Subscription'] = BillingSubscription::getSubscriptionsList($CompanyID);
        $all_data_list['AccountManager'] = User::getOwnerUsersbyRole();

        
        if(count($data['sum']) || $cube == 'account') {
            $response = Report::generateDynamicTable($CompanyID, $cube, $data,$filters);
        }
        if(isset($data['Export']) && $data['Export'] == 1) {
			$data_type = Input::get('Type');
            $Type = !empty($data_type)?$data_type:Report::XLS;
            $data['Name'] = !empty($data['Name']) ? $data['Name'] : 'Report';
            $table = generateReportTable2($data, $response, $all_data_list);
            if($Type == Report::PDF) {
                $file = $data['Name'] . ".html";
                $file2 = $data['Name'] . ".pdf";
                $table = '<h2 style="text-align: center;">'.$data['Name'].'</h2>'.$table;
                $temp_path = CompanyConfiguration::get('TEMP_PATH') . '/';
                $local_htmlfile = $temp_path . $file;
                $local_file = $temp_path . $file2;
                file_put_contents($local_htmlfile, $table);
                if (getenv('APP_OS') == 'Linux') {
                    exec(base_path() . '/wkhtmltox/bin/wkhtmltopdf -O landscape "' . $local_htmlfile . '" "' . $local_file . '"', $output);
                } else {
                    exec(base_path() . '/wkhtmltopdf/bin/wkhtmltopdf.exe -O landscape "' . $local_htmlfile . '" "' . $local_file . '"', $output);
                }
                download_file($local_file);
            }else if($Type == Report::PNG) {
                $file = $data['Name'] . ".html";
                $file2 = $data['Name'] . ".png";
                $table = '<h2 style="text-align: center;">'.$data['Name'].'</h2>'.$table;
                $temp_path = CompanyConfiguration::get('TEMP_PATH') . '/';
                $local_htmlfile = $temp_path . $file;
                $local_file = $temp_path . $file2;
                file_put_contents($local_htmlfile, $table);
                if (getenv('APP_OS') == 'Linux') {
                    exec(base_path() . '/wkhtmltox/bin/wkhtmltoimage "' . $local_htmlfile . '" "' . $local_file . '"', $output);
                } else {
                    exec(base_path() . '/wkhtmltopdf/bin/wkhtmltoimage.exe "' . $local_htmlfile . '" "' . $local_file . '"', $output);
                }
                download_file($local_file);
            }else{
                $file = $data['Name'] . ".xls";
                header("Content-type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=\"".$file."\"");
                echo $table;
            }
            exit;
        }
        return json_encode(generateReportTable2($data,$response,$all_data_list));
    }

    public function getdatalist(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] =1;
        $data['iDisplayLength'] =1;
        $ColName = $data['filter_col_name'];
        $search = $data['sSearch'] = '';
        if(in_array($ColName,array('InvoiceType','InvoiceStatus','ProductType','PaymentMethod','PaymentType','Owner'))){
            return generate_manual_datatable_response($ColName);
        }
        $Accountschema = array_keys(Report::$dimension['summary']['Customer']);
        if(in_array($ColName,$Accountschema) && $ColName != 'AccountID'){
            $accounts = Account::where(["AccountType" => 1, "CompanyID" => $CompanyID, "Status" => 1])
				->whereNotNull($ColName)
                ->select(array($ColName.' as 2',$ColName))
                ->distinct()
                ->orderBy($ColName);
            if(!empty($search)){
                $accounts->where($ColName,'like','%'.$search.'%');
            }
            return Datatables::of($accounts)->make();
        }
        $query = "CALL prc_getDistinctList('".$CompanyID."','".$ColName."','".$search."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].")";
        return DataTableSql::of($query,'neon_report')->make();
    }

    public function schedule_history(){
        $data = Input::all();
        $data['StartDateDefault'] 	  	= 	date("Y-m-d",strtotime(''.date('Y-m-d').' -1 months'));
        $data['EndDateDefault']  	= 	date('Y-m-d');
        $CompanyID = User::get_companyID();
        $Reports = Report::getDropdownIDList($CompanyID);
        $ReportSchedules = ReportSchedule::getDropdownIDList($CompanyID);
        return View::make('report.history', compact('Reports','data','ReportSchedules'));
    }
    public function schedule_history_datagrid($type) {
        $getdata = Input::all();
        $response =  NeonAPI::request('report/history_schedule',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Report History', function ($excel) use ($excel_data) {
                $excel->sheet('Report History', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return json_response_api($response,true,true,true);
    }

    public function schedule(){
        $CompanyID = User::get_companyID();
        $reports = Report::getDropdownIDList($CompanyID);
        return View::make('report.schedule', compact('reports'));
    }
    public function ajax_schedule_datagrid($type) {

        $CompanyID = User::get_companyID();
        $reports = ReportSchedule::where("tblReportSchedule.CompanyID", $CompanyID);
        $data = Input::all();
        if(trim($data['Name']) != '') {
            $reports->where('tblReportSchedule.Name', 'like','%'.trim($data['Name']).'%');
        }
        if(isset($data['Export']) && $data['Export'] == 1) {
            $reports->select('tblReportSchedule.Name',DB::raw('(SELECT GROUP_CONCAT(tblReport.Name) FROM tblReport WHERE FIND_IN_SET(tblReport.ReportID,tblReportSchedule.ReportID)) as Reports '),'tblReportSchedule.Status');
            $excel_data  = $reports->get();
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Report Schedule.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Report Schedule.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $reports->select('tblReportSchedule.Name',DB::raw('(SELECT GROUP_CONCAT(tblReport.Name) FROM tblReport WHERE FIND_IN_SET(tblReport.ReportID,tblReportSchedule.ReportID)) as ReportName '),'tblReportSchedule.Status','tblReportSchedule.Settings','tblReportSchedule.ReportID','ReportScheduleID');

        return Datatables::of($reports)->make();
    }

    public function add_schedule(){
        $postdata = Input::all();
        $CompanyID = User::get_companyID();
        $Schedule = isset($postdata['Status'])?1:0;
        CronJob::create_system_report_alert_job($CompanyID,$Schedule);
        $response =  NeonAPI::request('report/add_schedule',$postdata,'put',false,false);
        return json_response_api($response);
    }

    public function update_schedule($id){
        $postdata = Input::all();
        $CompanyID = User::get_companyID();
        $Schedule = isset($postdata['Status'])?1:0;
        CronJob::create_system_report_alert_job($CompanyID,$Schedule);
        $response =  NeonAPI::request('report/update_schedule/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }

    public function status_update($id){
        $postdata = Input::all();
        if (ReportSchedule::where('ReportScheduleID',$id)->update(array('Status'=>$postdata['Status']))) {
            return Response::json(array("status" => "success", "message" => "Report Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Report."));
        }
    }

    public function schedule_delete($id){
        $postdata = Input::all();
        $CompanyID = User::get_companyID();
        $Schedule = isset($postdata['Status'])?1:0;
        CronJob::create_system_report_alert_job($CompanyID,$Schedule);
        $response =  NeonAPI::request('report/delete_schedule/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
    public function schedule_download($index){
        $CompanyID = User::get_companyID();
        $explods_array = explode('-',$index);

        $AttachmentPaths = AccountEmailLog::where("AccountEmailLogID", $explods_array[0])->pluck('AttachmentPaths');
        $OutputFilePath = isset(explode(',',$AttachmentPaths)[$explods_array[1]])?explode(',',$AttachmentPaths)[$explods_array[1]]:'';
        $FilePath =  AmazonS3::preSignedUrl($OutputFilePath,$CompanyID);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }elseif(is_amazon($CompanyID) == true){
            header('Location: '.$FilePath);
        }
        exit;
    }
}