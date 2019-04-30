<?php

class VOSAccountBalanceController extends \BaseController {

    public function index(){
        $CompanyID = User::get_companyID();

        return View::make('vosaccountbalance.index', compact('CompanyID'));
    }

    public function ajax_datagrid($type)
	{
        $data 							 = 		Input::all();

        $CompanyID 						 = 		User::get_companyID();
        $data['iDisplayStart'] 			+=		1;
        $data['AccountName'] = !empty($data['AccountName'])?$data['AccountName']:'';
        $data['AccountNumber'] = !empty($data['AccountNumber'])?$data['AccountNumber']:'';

        $columns = array('AccountName','AccountNumber','AccountBalance');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getVOSAccountBalance(".$CompanyID.",'".$data['AccountName']."','".$data['AccountNumber']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/VOSAccountBalance.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/VOSAccountBalance.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrv')->make();
    }


}