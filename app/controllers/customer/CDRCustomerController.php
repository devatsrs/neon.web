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
        $gateway = CompanyGateway::getCompanyGatewayIdList(); // not using
        $CompanyID = Customer::get_companyID();
        $rate_cdr = array();
        $Settings = CompanyGateway::where(array('Status'=>1,'CompanyID'=>$CompanyID))->lists('Settings', 'CompanyGatewayID');
        foreach($Settings as $CompanyGatewayID => $Setting){
            $Setting = json_decode($Setting);
            if(isset($Setting->RateCDR) && $Setting->RateCDR == 1){
                $rate_cdr[$CompanyGatewayID] =1;
            }else{
                $rate_cdr[$CompanyGatewayID] =0;
            }
        }
        $AccountID = Customer::get_accountID();
        $account                     = Account::find($AccountID);
        $CurrencyID 		 = 	 empty($account->CurrencyId)?'0':$account->CurrencyId;
        $trunks = Trunk::getTrunkDropdownList($CompanyID);
        $trunks = $trunks + array('Other'=>cus_lang("CUST_PANEL_PAGE_CDR_FILTER_FIELD_TRUNK_DLL_OTHER"));
        $Hide_AvgRateMinute = CompanyConfiguration::get('HIDE_AVGRATEMINUTE',$CompanyID);
        return View::make('customer.cdr.index',compact('dashboardData','account','gateway','rate_cdr','AccountID','CurrencyID','trunks','Hide_AvgRateMinute'));
    }
    public function ajax_datagrid($type){
        $data						 =   Input::all();
        $data['iDisplayStart'] 		+=	 1;
        $companyID 					 =	 Customer::get_companyID();
        $columns 					 = 	 array('UsageDetailID','AccountName','connect_time','disconnect_time','billed_duration','cost','cli','cld');
        $sort_column 				 = 	 $columns[$data['iSortCol_0']];
        $data['AccountID']           = Customer::get_accountID();
        $account                     = Account::find($data['AccountID']);
        $CurrencyId                  = $account->CurrencyId;
        $CurrencyID 		 = 	 empty($CurrencyId)?'0':$CurrencyId;
        $area_prefix = $Trunk = '';

        $ResellerID = 0;
        /*
        if(is_reseller()){
            log::info('Reseller');
            $ResellerID = Reseller::where(['AccountID'=>Customer::get_accountID()])->pluck('ResellerID');
            log::info('Reseller ID '.$ResellerID);
            $data['AccountID']=0;
        }*/
        $query = "call prc_GetCDR (".$companyID.",".(int)$data['CompanyGatewayID'].",'".$data['StartDate']."','".$data['EndDate']."',".(int)$data['AccountID'].",".(int)$ResellerID.",'".$data['CDRType']."' ,'".$data['CLI']."','".$data['CLD']."',".$data['zerovaluecost'].",".$CurrencyID.",'".$data['area_prefix']."','".$data['Trunk']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1,"")');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/CDR.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH'.$companyID) .'/CDR.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            else{
                //generate pdf
                $file_name = 'CDR--' . date('d-m-Y') . '.pdf';
                $htmlfile_name = 'CDR--' . date('d-m-Y') . '.html';
                //$body = View::make('customer.cdr.genpdf', compact('excel_data'));
                $body = self::generate_html($excel_data);
                $destination_dir = CompanyConfiguration::get('UPLOAD_PATH')."/";
                RemoteSSH::run("chmod -R 777 " . $destination_dir);
                $file_name = \Nathanmac\GUID\Facades\GUID::generate() .'-'. $file_name;
                $htmlfile_name = \Nathanmac\GUID\Facades\GUID::generate() .'-'. $htmlfile_name;
                Log::info($htmlfile_name);
                $local_file = $destination_dir .  $file_name;
                $local_htmlfile = $destination_dir .  $htmlfile_name;
                file_put_contents($local_htmlfile,$body);
                $output= "";
                if(getenv('APP_OS') == 'Linux'){
                    exec (base_path(). '/wkhtmltox/bin/wkhtmltopdf --header-spacing 3 --footer-spacing 1  "'.$local_htmlfile.'" "'.$local_file.'"',$output);
                    Log::info(base_path(). '/wkhtmltox/bin/wkhtmltopdf --header-spacing 3 --footer-spacing 1 "'.$local_htmlfile.'" "'.$local_file.'"',$output);

                }else{
                    exec (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --header-spacing 3 --footer-spacing 1 "'.$local_htmlfile.'" "'.$local_file.'"',$output);
                    Log::info (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --header-spacing 3 --footer-spacing 1 "'.$local_htmlfile.'" "'.$local_file.'"',$output);
                }
                Log::info($output);
                @unlink($local_htmlfile);
                $save_path = $destination_dir . $file_name;
                return Response::download($save_path);
                //return $save_path;
            }

        }
         $query .=',0,"")';
        log::info($query);
        return DataTableSql::of($query, 'sqlsrv2')->make();
    }

    public function generate_html($excel_data){
        $body = '<style>.bg_graycolor{
                background-color: #f5f5f6;
                font-family: Sans-Serif;
            }
            .bg_graycolor th, .bg_graycolor td{
                border: 1px solid #dddddd;
            }</style>
        <div class="row">
        <div class="col-md-12">
            <table class="table bg_graycolor" cellpadding="5" cellspacing="0">
                <thead>
                <tr>
                    <th width="20%">CLI</th>
                    <th>CLD</th>
                    <th>Connect Time</th>
                    <th>Disconnect Time</th>
                    <th width="10%">Billed Duration</th>
                    <th>Cost</th>
                </tr>
                </thead>
                <tbody>';
            foreach($excel_data as $ProductRow) {
                $body .= '<tr style="page-break-inside: avoid;">
                            <td class="desc" width="20%">'.$ProductRow["CLI"].'</td>
                            <td class="desc">'.$ProductRow["CLD"].'</td>
                            <td class="desc">'.$ProductRow["Connect Time"].'</td>
                            <td class="desc">'.$ProductRow["Disconnect Time"].'</td>
                            <td width="10%" class="desc">'.$ProductRow["Billed Duration (sec)"].'</td>
                            <td class="desc">'.$ProductRow["Cost"].'</td>
                        </tr>';
            }
                $body .='</tbody>
                <tfoot>
                <tr>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>';
        return $body;

    }

}
