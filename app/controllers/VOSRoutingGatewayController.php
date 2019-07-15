<?php

class VOSRoutingGatewayController extends \BaseController {

    public function index(){
        $CompanyID = User::get_companyID();
        return View::make('vosroutinggateway.index', compact('CompanyID'));
    }

    public function ajax_datagrid($type)
	{

        $data 							 = 		Input::all();
        $CompanyID 						 = 		User::get_companyID();
        $AccountIPActilead               =      UserActivity::UserActivitySaved($data,'View','AccountIP');
        $data['iDisplayStart'] 			+=		1;
        $data['AccountName'] = !empty($data['AccountName'])?$data['AccountName']:'';
        $data['RemoteIps'] = !empty($data['RemoteIps'])?$data['RemoteIps']:'';

        $columns = array('AccountName','Name','LockType','LineLimit','RoutePrefix','NumberPrefix','LocalIP','RemoteIps');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getVOSGatewayRouting(".$CompanyID.",'".$data['AccountName']."','".$data['RemoteIps']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $export_type['type'] = $type;
            $AccountIPActilead = UserActivity::UserActivitySaved($export_type,'Export','AccountIP');
            $excel_data  = DB::connection('sqlsrv')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/VOSRoutingGateways.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/VOSRoutingGateways.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrv')->make();

    }


}