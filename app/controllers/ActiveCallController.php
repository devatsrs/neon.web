<?php

class ActiveCallController extends \BaseController {

    public function index()
    {
        $id=0;
        $companyID = User::get_companyID();

        $accounts = Account::getAccountIDList();
        $trunks = Trunk::getTrunkDropdownIDList($companyID);
        $trunks = $trunks + array('Other'=>'Other');
        $Services = Service::getDropdownIDList($companyID);

        $gateway = CompanyGateway::getCompanyGatewayIdList($companyID);

        return View::make('ActiveCall.index', compact('accounts','trunks','Services','gateway'));
    }

    public function ajax_datagrid($type)
	{
        $data 							 = 		Input::all();

        $CompanyID 						 = 		User::get_companyID();
        $data['iDisplayStart'] 			+=		1;
        $data['AccountID'] 				 = 		$data['AccountID']!= ''?$data['AccountID']:0;
        $data['CLI']				     =		$data['CLI']!= ''?$data['CLI']:'';
        $data['CLD']				     =		$data['CLD']!= ''?$data['CLD']:'';
        $data['CLDPrefix']				 =		$data['CLDPrefix']!= ''?$data['CLDPrefix']:'';
        $data['CallType'] 				 = 		$data['CallType']!= ''?$data['CallType']:-1;
        $data['TrunkID'] 				 = 		$data['TrunkID']!= ''?$data['TrunkID']:0;
        $data['ServiceID'] 				 = 		$data['ServiceID']!= ''?$data['ServiceID']:0;
        $data['CompanyGatewayID']				 =		$data['CompanyGatewayID']!= ''?$data['CompanyGatewayID']:0;

        $columns = array('ActiveCallID','AccountName','CLI','CLD','CLDPrefix','ConnectTime','Cost','CompanyGatewayID');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getActiveCalls(".$CompanyID.",".$data['AccountID'].",'".$data['CLI']."','".$data['CLD']."','".$data['CLDPrefix']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".$data['CallType'].",".$data['TrunkID'].",".$data['ServiceID'].",".$data['CompanyGatewayID']."";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrvcdr')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/ActiveCall.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/ActiveCall.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrvcdr')->make();
    }



}