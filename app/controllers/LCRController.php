<?php

class LCRController extends \BaseController {

    public function search_ajax_datagrid($type) {

        ini_set ( 'max_execution_time', 90);
        $companyID = User::get_companyID();
        $data = Input::all();
        $AccountIDs = empty($data['Accounts'])?'':$data['Accounts'];
        if($AccountIDs=='null'){
            $AccountIDs='';
        }
        $data['Use_Preference'] = $data['Use_Preference'] == 'true' ? 1:0;
        $data['vendor_block'] = $data['vendor_block'] == 'true' ? 1:0;
        $data['iDisplayStart'] +=1;

        $LCRPosition = Invoice::getCookie('LCRPosition');
        if($data['LCRPosition'] != $LCRPosition){
            NeonCookie::setCookie('LCRPosition',$data['LCRPosition'],60);
        }
        $LCRGroupBy = Invoice::getCookie('LCRGroupBy');
        if($data['GroupBy'] != $LCRGroupBy){
            NeonCookie::setCookie('LCRGroupBy',$data['GroupBy'],60);
        }

        if( $data['Policy'] == LCR::LCR ) {

            //log::info("call prc_GetLCR (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."',0)");
            $query = "call prc_GetLCR (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".intval($data['vendor_block'])."','".$data['GroupBy']."','".$data['SelectedEffectiveDate']."' ";
        } else {

            //log::info("call prc_GetLCRwithPrefix (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".$data['GroupBy']."',0)");
            $query = "call prc_GetLCRwithPrefix (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".intval($data['vendor_block'])."','".$data['GroupBy']."','".$data['SelectedEffectiveDate']."' ";

        }
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            foreach($excel_data as $rowno => $rows){
                foreach($rows as $colno => $colval){
                    $excel_data[$rowno][$colno] = str_replace( "<br>" , "\n" ,$colval );
                }
            }

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/LCR.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/LCR.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

            /*Excel::create('LCR', function ($excel) use ($excel_data) {
                $excel->sheet('LCR', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';

        \Illuminate\Support\Facades\Log::info($query);

        return DataTableSql::of($query)->make();

    }

    public function index() {
        $trunks = Trunk::getTrunkDropdownIDList();
        $trunk_keys = getDefaultTrunk($trunks);
        //$countries = Country::getCountryDropdownIDList();
        $codedecklist = BaseCodeDeck::getCodedeckIDList();
        $currencies = Currency::getCurrencyDropdownIDList();
        $CurrencyID = Company::where("CompanyID",User::get_companyID())->pluck("CurrencyId");
        $LCRPosition = NeonCookie::getCookie('LCRPosition',5);
        $data=array();
        $data['IsVendor']=1;
        $all_accounts = Account::getAccountIDList($data);
        if(!empty($all_accounts[''])){
            unset($all_accounts['']);
        }
        $GroupBy =    NeonCookie::getCookie('LCRGroupBy');

        return View::make('lcr.index', compact('trunks', 'currencies','CurrencyID','codedecklist','trunk_keys','LCRPosition','all_accounts','GroupBy'));
    }
    //not using
    public function exports(){


        $companyID = User::get_companyID();
        $data = Input::all();

        $data['iDisplayStart'] +=1;
        if( $data['Policy'] == LCR::LCR ) {
            $query = "call prc_GetLCR (" . $companyID . "," . $data['Trunk'] . "," . $data['CodeDeck'] . ",'" . $data['Currency'] . "','" . $data['Code'] . "'," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . "," . $data['iDisplayLength'] . ",'" . $data['sSortDir_0'] . "',1)";
        }else{

            $query = "call prc_GetLCRwithPrefix (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."',1)";
        }

        DB::setFetchMode( PDO::FETCH_ASSOC );
        $lcrs  = DB::select($query);
        DB::setFetchMode( Config::get('database.fetch'));

        Excel::create('LCR', function ($excel) use ($lcrs) {
            $excel->sheet('Rates Table', function ($sheet) use ($lcrs) {
                $sheet->setAutoSize(true);
                $sheet->fromArray($lcrs);
            });
        })->download('xls');


    }

    public function marginRate(){
        $postdata = Input::all();
            if($postdata['GroupBy']=='code') {
                $data = DB::table("tblCustomerRate as cr")->select('acc.AccountName', 'cr.Rate')
                    ->join('tblRate as r', 'cr.RateID', '=', 'r.RateID')
                    ->join('tblAccount as acc', 'cr.CustomerID', '=', 'acc.AccountID')
                    ->where('r.Code', '=', $postdata['code'])->get();
            }else{
                $data = DB::table("tblCustomerRate as cr")->select('acc.AccountName' , 'cr.Rate')
                    ->join('tblRate as r', 'cr.RateID', '=', 'r.RateID')
                    ->join('tblAccount as acc', 'cr.CustomerID', '=', 'acc.AccountID')
                    ->where('r.Description', '=', $postdata['code'])->groupby('acc.AccountName')->get();
            }
            return $data;
    }
    public function marginRateExport($type,$id){
        ini_set ( 'max_execution_time', 90);
        $companyID = User::get_companyID();
        $data = Input::all();
        $AccountIDs = empty($data['Accounts'])?'':$data['Accounts'];
        if($AccountIDs=='null'){
            $AccountIDs='';
        }
        $data['Use_Preference'] = $data['Use_Preference'] == 'true' ? 1:0;
        $data['vendor_block'] = $data['vendor_block'] == 'true' ? 1:0;
        $data['iDisplayStart'] +=1;

        $LCRPosition = Invoice::getCookie('LCRPosition');
        if($data['LCRPosition'] != $LCRPosition){
            NeonCookie::setCookie('LCRPosition',$data['LCRPosition'],60);
        }
        $LCRGroupBy = Invoice::getCookie('LCRGroupBy');
        if($data['GroupBy'] != $LCRGroupBy){
            NeonCookie::setCookie('LCRGroupBy',$data['GroupBy'],60);
        }
        $query = "call prc_GetLCRwithPrefix (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Currency']."','".trim($id)."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".intval($data['vendor_block'])."','".$data['GroupBy']."','".$data['SelectedEffectiveDate']."' ";
        $positiondata  = DB::select($query.',1)');
        $postdata = Input::all();
        /* use for export xls and csv */
        $excel_data = DB::table("tblCustomerRate as cr")->select('acc.AccountName', 'cr.Rate')
            ->join('tblRate as r', 'cr.RateID', '=', 'r.RateID')
            ->join('tblAccount as acc', 'cr.CustomerID', '=', 'acc.AccountID')
            ->where('r.Code', '=', $id)->get();
        $excel_data1 = json_decode(json_encode($excel_data), true);
        $result = array();
        $data = json_decode(json_encode($positiondata), true);
        $counts = count($data[0]);
        for($i=1;$i<=$counts-1;$i++){
            foreach ($excel_data1 as $rows) {
                $keyname = 'POSITION ' . $i . '';
                $vname = explode("<br>",$data[0][$keyname]);
                if(!empty($data[0][$keyname])) {
                    $temp['AccountName'] = $rows['AccountName'];
                    $temp['Vendor'] = $vname[1];
                    $temp['Rate'] = $vname[0];
                    $temp['CRate'] = $rows['Rate'];
                    $margin = floatval($vname[0]) - floatval($rows['Rate']);
                    $margin = number_format((float)$margin, 6, '.', '');
                    $margin_percentage  = 100 - (floatval($rows['Rate'])) * 100/ floatval($vname[0]);
                    $temp['Margin Detail'] = $margin.' ('.round($margin_percentage,2).' %)';
                    array_push($result, $temp);
                }
            }
        }
        if ($type == 'csv') {
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/LCR.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($result);
        } elseif ($type == 'xlsx') {
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/LCR.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($result);
        }
        /* use for export xls and csv */
    }

    public function editPreference(){
        $postdata = Input::all();
        $id =  $postdata['id'];
        $preference =  $postdata['preference'];
        $acc_id =  $postdata['acc_id'];
        $trunk =  $postdata['trunk'];
        $GroupBy =  $postdata['GroupBy'];
        $CodeDeckId =  $postdata['CodeDeckId'];
        $rowcode =  $postdata['rowcode'];
        $username = User::get_user_full_name();
        /* Get rateid */
        $test = DB::table('tblVendorRate')
            ->join('tblRate', 'tblVendorRate.RateId', '=', 'tblRate.RateId')
            ->where('tblVendorRate.AccountId', '=', $postdata["acc_id"])
            ->where('tblVendorRate.TrunkID', '=', $postdata["trunk"])
            ->where('tblRate.Code', '=', $postdata["rowcode"])
            ->limit(1)
            ->get();
        $RateId = $test[0]->RateID;
        /* Get rateid */
        $checkPreference = DB::table('tblVendorPreference')->select('VendorPreferenceID')
            ->where('AccountId','=',$acc_id)
            ->where('RateId','=',$RateId)
            ->where('TrunkID','=',$trunk)->get();
        if(!empty($checkPreference)){
            $VendorPreferenceID = $checkPreference[0]->VendorPreferenceID;
            $preference = $preference==''? 5 : $preference;
            DB::table('tblVendorPreference')->where('VendorPreferenceID','=',$VendorPreferenceID)->update(["Preference"=>$preference]);
            echo "Preference Update Successfully";
        }else{
            $preference = $preference==''? 5 : $preference;
            DB::table('tblVendorPreference')->insert(
                ["AccountId"=>$acc_id,"Preference"=>$preference,"RateId"=>$RateId,"TrunkID"=>$trunk,"CreatedBy"=>$username ]
            );
            echo "Preference Insert Successfully";
        }
        exit;
    }

}
