<?php

class SummaryController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();

        $data['iDisplayStart'] +=1;
        $extra_field = '';
        $userID = User::get_userID();
        //$isAdmin = (User::is_admin() || User::is('RateManager'))?1:0;
        $isAdmin 					= 	(User::is_admin())?1:0;
        $columns = array('AccountName','AreaPrefix','Country','Description','NoOfCalls','TotalDuration','TotalDuration','TotalCharges');
        $companyID = User::get_companyID();
        $pr_name = 'call prc_getSummaryReportByPrefix (';
        $export_sheet = 'Prefix';
        if(isset($data['report']) && $data['report'] == 'country'){
            $columns = array('AccountName','Country','NoOfCalls','TotalDuration','TotalDuration','TotalCharges');
           $pr_name = 'call prc_getSummaryReportByCountry (';
            $export_sheet = 'Country';
            $extra_field = ",'".intval($data['CountryID'])."'";
        }elseif(isset($data['report']) && $data['report'] == 'pincode'){
            $columns = array('AccountName','Pincode','NoOfCalls','TotalDuration','TotalDuration','TotalCharges');
            $pr_name = 'call prc_getSummaryReportByPincode (';
            $export_sheet = 'Pincode';
            $extra_field = ",'".$data['Pincode']."'";
        }elseif(isset($data['report']) && $data['report'] == 'customer'){
            $columns = array('AccountName','NoOfCalls','TotalDuration','TotalDuration','TotalCharges');
            $pr_name = 'call prc_getSummaryReportByCustomer (';
            $export_sheet = 'Customer';
        }else{
            $extra_field = ",'".$data['Prefix']."'";
            $extra_field .= ",'".intval($data['CountryID'])."'";
        }
        $sort_column = $columns[$data['iSortCol_0']];
        $query = $pr_name.$companyID.",".intval($data['AccountID']).",".intval($data['GatewayID']).",'".$data['StartDate']."','".$data['EndDate']."'".$extra_field.",".intval($userID).",".intval($isAdmin).",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1){
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Summery Report By '.$export_sheet.'.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Summery Report By '.$export_sheet.'.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Summery Report By '.$export_sheet, function ($excel) use ($excel_data ,$export_sheet) {
                $excel->sheet('Summery Report By '.$export_sheet, function ($sheet) use ($excel_data ,$export_sheet) {
                    $sheet->fromArray($excel_data );
                });
            })->download('xls');*/
        }
        $query .=',0)';
        return DataTableSql::of($query,'sqlsrv2')->make();
    }

    public function index(){
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $Country = Country::getCountryDropdownIDList();
        $account = Account::getAccountIDList();
        return View::make('summary.index', compact('gateway','Country','account'));

    }
    public function summrybycountry(){
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $Country = Country::getCountryDropdownIDList();
        $account = Account::getAccountIDList();
        return View::make('summary.bycounry', compact('gateway','Country','account'));
    }
    public function summrybycustomer(){
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $account = Account::getAccountIDList();
        return View::make('summary.bycustomer', compact('gateway','account'));

    }
    public function summrybypincode(){
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $account = Account::getAccountIDList();
        return View::make('summary.bypincode', compact('gateway','account'));

    }

    public function daily_sales_report(){
        $data = Input::all();
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $account = GatewayAccount::getActiveAccountIDList(User::get_companyID(),array_keys($gateway));

        $start_date =  date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+1 day') );
        $accountid = '';
        if(isset($data['start_date'])){
            $start_date = $data['start_date'];
        }
        if(isset($data['end_date'])){
            $end_date = $data['end_date'];
        }
        if(isset($data['accountid'])){
            $accountid = $data['accountid'];
        }
        return View::make('summary.dialyreport', compact('gateway','account','start_date','end_date','accountid'));
    }
    public function daily_ajax_datagrid() {
        $data = Input::all();

        $data['iDisplayStart'] +=1;
        $userID = User::get_userID();
        //$isAdmin = (User::is_admin() || User::is('RateManager'))?1:0;
        $isAdmin 					= 	(User::is_admin())?1:0;
        $columns = array('AccountName','SalesDate','NoOfCalls','TotalDuration','TotalDuration','TotalCharges');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();
        $pr_name = 'call prc_getDailysalesReportByCustomer (';
        $export_sheet = 'customer';
        $query = $pr_name.$companyID.",'".$data['AccountID']."','".$data['GatewayID']."','".$data['StartDate']."','".$data['EndDate']."',".$userID.",".$isAdmin.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1){
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Daily sales By '.$export_sheet, function ($excel) use ($excel_data ,$export_sheet) {
                $excel->sheet('Daily sales By '.$export_sheet, function ($sheet) use ($excel_data ,$export_sheet) {
                    $sheet->fromArray($excel_data );
                });
            })->download('xls');
        }
        $query .=',0)';
        return DataTableSql::of($query,'sqlsrv2')->make();
    }
    public function temp_action(){
        $excel = Excel::load('C:/temp/123.xlsx', function($reader) {})->get();
        foreach($excel as $row){
            $data = array();
            $data['FirstName'] = $row['first_name'];
            $data['LastName'] = $row['last_name'];
            $data['Title'] = $row['title'];
            $data['AccountName'] = $row['company'];
            $data['Address1'] = $row['address'];
            $data['City'] = $row['city'];
            $data['State'] = $row['stateprovince'];
            $data['Country'] = $row['country'];
            $data['Phone'] = $row['phone'];
            $data['Fax'] = $row['fax'];
            $data['Email'] = $row['email'];
            $data['PostCode'] = $row['postal_code'];
            $data['created_at'] =date('Y-m-d H:i:s',strtotime($row['scan_date'].$row['scan_time']));
            $data['AccountType'] = 0;
            $data['CompanyId'] = 1;
            $data['Status'] = 1;
            $data['LeadSource'] = 'ITW-2015';
            $data['created_by'] = 'RateManagementSystem';
            if(Account::where(array('AccountName'=>$data['AccountName']))->count() == 0){
                Account::insert($data);
            }
        }
        echo 'added';
    }


}
