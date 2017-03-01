<?php

class CDRCustomerController extends BaseController {

    
    public function __construct() {

    }

    public function get_accounts($CompanyGatewayID){
        $account=GatewayAccount::getAccountNameByGatway($CompanyGatewayID);
        $html_text = '';
        foreach($account as $accountid =>$account_name){
            $html_text .= '<option value="' .$accountid. '">'.$account_name.'</option>';
        }
        echo $html_text;
    }
    public function index(){
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $rate_cdr = array();
        $Settings = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->lists('Settings', 'CompanyGatewayID');
        foreach($Settings as $CompanyGatewayID => $Setting){
            $Setting = json_decode($Setting);
            if(isset($Setting->RateCDR) && $Setting->RateCDR == 1){
                $rate_cdr[$CompanyGatewayID] =1;
            }else{
                $rate_cdr[$CompanyGatewayID] =0;
            }
        }
        $AccountID = User::get_userID();
        $account                     = Account::find($AccountID);
        $CurrencyID 		 = 	 empty($account->CurrencyId)?'0':$account->CurrencyId;
        $trunks = Trunk::getTrunkDropdownList();
        $trunks = $trunks + array('Other'=>'Other');
        return View::make('customer.cdr.index',compact('dashboardData','account','gateway','rate_cdr','AccountID','CurrencyID','trunks'));
    }
    public function ajax_datagrid($type){
        $data						 =   Input::all();
        $data['iDisplayStart'] 		+=	 1;
        $companyID 					 =	 User::get_companyID();
        $columns 					 = 	 array('UsageDetailID','AccountName','connect_time','disconnect_time','billed_duration','cost','cli','cld');
        $sort_column 				 = 	 $columns[$data['iSortCol_0']];
        $data['AccountID']           = User::get_userID();
        $account                     = Account::find($data['AccountID']);
        $CurrencyId                  = $account->CurrencyId;
        $CurrencyID 		 = 	 empty($CurrencyId)?'0':$CurrencyId;
        $area_prefix = $Trunk = '';

        $query = "call prc_GetCDR (".$companyID.",".(int)$data['CompanyGatewayID'].",'".$data['StartDate']."','".$data['EndDate']."',".(int)$data['AccountID'].",'".$data['CDRType']."' ,'".$data['CLI']."','".$data['CLD']."',".$data['zerovaluecost'].",".$CurrencyID.",'".$data['area_prefix']."','".$data['Trunk']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/CDR.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/CDR.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

        }
         $query .=',0)';
        return DataTableSql::of($query, 'sqlsrv2')->make();
    }

}
