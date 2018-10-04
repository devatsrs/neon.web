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
        $data['show_all_vendor_codes'] = $data['show_all_vendor_codes'] == 'true' ? 1:0;
        $data['iDisplayStart'] +=1;

        $LCRPosition = Invoice::getCookie('LCRPosition');
        if($data['LCRPosition'] != $LCRPosition){
            NeonCookie::setCookie('LCRPosition',$data['LCRPosition'],60);
        }
        $LCRGroupBy = Invoice::getCookie('LCRGroupBy');
        if($data['GroupBy'] != $LCRGroupBy){
            NeonCookie::setCookie('LCRGroupBy',$data['GroupBy'],60);
        }

        $data['merge_timezones'] = $data['merge_timezones'] == 'true' ? 1 : 0;
        $data['Timezones'] = $data['merge_timezones'] == 1 ? $data['TimezonesMerged'] : $data['Timezones'];

        if( $data['Policy'] == LCR::LCR ) {
            //log::info("call prc_GetLCR (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."',0)");
            $query = "call prc_GetLCR (".$companyID.",".$data['Trunk'].",'".$data['Timezones']."',".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".intval($data['vendor_block'])."','".$data['GroupBy']."','".$data['SelectedEffectiveDate']."','".intval($data['show_all_vendor_codes'])."' ,'".$data['merge_timezones']."' ,'".intval($data['TakePrice'])."' ";
        } else {
            //log::info("call prc_GetLCRwithPrefix (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".$data['GroupBy']."',0)");
            $query = "call prc_GetLCRwithPrefix (".$companyID.",".$data['Trunk'].",'".$data['Timezones']."',".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".intval($data['vendor_block'])."','".$data['GroupBy']."','".$data['SelectedEffectiveDate']."' ,'".intval($data['show_all_vendor_codes'])."' ,'".$data['merge_timezones']."' ,'".intval($data['TakePrice'])."' ";
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
        $Timezones = Timezones::getTimezonesIDList();
        $data=array();
        $data['IsVendor']=1;
        $all_accounts = Account::getAccountIDList($data);
        if(!empty($all_accounts[''])){
            unset($all_accounts['']);
        }
        $companyID = User::get_companyID();
        $DefaultCodedeck = BaseCodeDeck::where(["CompanyID"=>$companyID,"DefaultCodedeck"=>1])->pluck("CodeDeckId");
        $GroupBy =    NeonCookie::getCookie('LCRGroupBy');

        return View::make('lcr.index', compact('trunks', 'currencies','CurrencyID','codedecklist','DefaultCodedeck','trunk_keys','LCRPosition','all_accounts','GroupBy','Timezones'));
    }
    //not using
    public function exports(){


        $companyID = User::get_companyID();
        $data = Input::all();

        $data['iDisplayStart'] +=1;
        if( $data['Policy'] == LCR::LCR ) {
            $query = "call prc_GetLCR (" . $companyID . "," . $data['Trunk'] . ",".$data['Timezones']."," . $data['CodeDeck'] . ",'" . $data['Currency'] . "','" . $data['Code'] . "'," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . "," . $data['iDisplayLength'] . ",'" . $data['sSortDir_0'] . "',1)";
        }else{

            $query = "call prc_GetLCRwithPrefix (".$companyID.",".$data['Trunk'].",".$data['Timezones'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."',1)";
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

    public function ajax_customer_rate_grid(){
        $postdata = Input::all();
        if($postdata['GroupBy']=='code') {
            //@TODO: change : add customer trunk active , account active
            $result = DB::table("tblCustomerRate as cr")->select(DB::raw('max(cr.Rate) as Rate, acc.AccountName,acc.AccountID,c.Symbol'))
                ->join('tblRate as r', 'cr.RateID', '=', 'r.RateID')
                ->join('tblAccount as acc', 'cr.CustomerID', '=', 'acc.AccountID')
                ->join('tblCustomerTrunk as ct', 'acc.AccountID', '=', 'ct.AccountID')
                ->join('tblCurrency as c', 'c.CurrencyId', '=', 'acc.CurrencyId')
                ->join('tblTimezones as tz', 'tz.TimezonesID', '=', 'cr.TimezonesID')
                ->where('r.Code', '=', $postdata['code'])
                ->where('cr.TimezonesID', '=', $postdata['TimezonesID'])
                ->where('acc.Status', '=', '1')
                ->where('acc.IsCustomer', '=', '1')
                ->groupby('acc.AccountName')
                ->where('ct.Status', '=', '1')
                ->where ('cr.EffectiveDate', '<=' ,$postdata["effactdate"] )
                ->get();
        }else{
            $result = DB::table("tblCustomerRate as cr")->select(DB::raw('max(cr.Rate) as Rate, acc.AccountName, acc.AccountID,c.Symbol'))
                ->join('tblRate as r', 'cr.RateID', '=', 'r.RateID')
                ->join('tblAccount as acc', 'cr.CustomerID', '=', 'acc.AccountID')
                ->join('tblCustomerTrunk as ct', 'acc.AccountID', '=', 'ct.AccountID')
                ->join('tblCurrency as c', 'c.CurrencyId', '=', 'acc.CurrencyId')
                ->join('tblTimezones as tz', 'tz.TimezonesID', '=', 'cr.TimezonesID')
                ->where('r.Description', '=', $postdata['code'])
                ->where('cr.TimezonesID', '=', $postdata['TimezonesID'])
                ->where('acc.Status', '=', '1')
                ->where('acc.IsCustomer', '=', '1')
                ->where('ct.Status', '=', '1')
                ->where ('cr.EffectiveDate', '<=' ,$postdata["effactdate"] )
                ->groupby('acc.AccountName')
                ->get();
        }
        $data["decimalpoint"] =  get_round_decimal_places();
        $data["result"] = $result;
        return $data;
    }

    public function marginRateExport($type,$id)
    {

        //@TODO: // use POST json grid data - to avoid using procedure.

        ini_set('max_execution_time', 90);
        $companyID = User::get_companyID();
        $data = Input::all();
        $AccountIDs = empty($data['Accounts']) ? '' : $data['Accounts'];
        if ($AccountIDs == 'null') {
            $AccountIDs = '';
        }
        $data['Use_Preference'] = $data['Use_Preference'] == 'true' ? 1 : 0;
        $data['vendor_block'] = $data['vendor_block'] == 'true' ? 1 : 0;
        $data['iDisplayStart'] += 1;

        $LCRPosition = Invoice::getCookie('LCRPosition');
        if ($data['LCRPosition'] != $LCRPosition) {
            NeonCookie::setCookie('LCRPosition', $data['LCRPosition'], 60);
        }
        $LCRGroupBy = Invoice::getCookie('LCRGroupBy');
        if ($data['GroupBy'] != $LCRGroupBy) {
            NeonCookie::setCookie('LCRGroupBy', $data['GroupBy'], 60);
        }
        if( $data['Policy'] == LCR::LCR ) {
            $query = "call prc_GetLCR (".$companyID.",".$data['Trunk'].",".$data['Timezones'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".intval($data['vendor_block'])."','".$data['GroupBy']."','".$data['SelectedEffectiveDate']."' ";
        } else {
            $query = "call prc_GetLCRwithPrefix (".$companyID.",".$data['Trunk'].",".$data['Timezones'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".intval($data['vendor_block'])."','".$data['GroupBy']."','".$data['SelectedEffectiveDate']."' ";
        }
        $positiondata = DB::select($query . ',1)');


        if ($data['GroupBy'] == 'code'){

            $excel_data = DB::table("tblCustomerRate as cr")->select(DB::raw('max(cr.Rate) as Rate, acc.AccountName'))
                ->join('tblRate as r', 'cr.RateID', '=', 'r.RateID')
                ->join('tblAccount as acc', 'cr.CustomerID', '=', 'acc.AccountID')
                ->join('tblCustomerTrunk as ct', 'acc.AccountID', '=', 'ct.AccountID')
                ->where('r.Code', '=', $id)
                ->where('acc.Status', '=', '1')
                ->where('acc.IsCustomer', '=', '1')
                ->groupby('acc.AccountName')
                ->where('ct.Status', '=', '1')
                ->get();

        }else{

            $excel_data = DB::table("tblCustomerRate as cr")->select(DB::raw('max(cr.Rate) as Rate, acc.AccountName'))
                ->join('tblRate as r', 'cr.RateID', '=', 'r.RateID')
                ->join('tblAccount as acc', 'cr.CustomerID', '=', 'acc.AccountID')
                ->join('tblCustomerTrunk as ct', 'acc.AccountID', '=', 'ct.AccountID')
                ->where('r.Description', '=', $id)
                ->where('acc.Status', '=', '1')
                ->where('acc.IsCustomer', '=', '1')
                ->where('ct.Status', '=', '1')
                ->groupby('acc.AccountName')
                ->get();
        }

        $excel_data1 = json_decode(json_encode($excel_data), true);
        $result = array();
        $data = json_decode(json_encode($positiondata), true);
        $counts = count($data[0]);
        for($i=1;$i<=$counts-1;$i++){
            foreach ($excel_data1 as $rows) {
                $keyname = 'POSITION ' . $i . '';
                $vname = explode("<br>",$data[0][$keyname]);
                if(!empty($data[0][$keyname])) {
                    $temp['Customer'] = $rows['AccountName'];
                    $temp['Vendor'] = $vname[1];
                    $temp['CRate'] = $rows['Rate'];
                    $temp['Rate'] = $vname[0];
                    $margin = floatval($vname[0]) - floatval($rows['Rate']);
                    $margin = number_format((float)$margin, 6, '.', '');
                    $margin_percentage  = 100 - (floatval($rows['Rate'])) * 100/ floatval($vname[0]);
                    $temp['Vendor Detail'] = $margin.' ('.round($margin_percentage,2).' %)';
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


    }

    public function ajax_customer_rate_export(){

        $data = Input::all();
        $type = $data["type"];
        $decimalpoint = $data["customer"]["decimalpoint"];
        $customer = $data["customer"]["result"];
        $vendor = $data["vendor"];
        $rate = $data["rate"];
        $lenghtofarr =  sizeof($vendor);
        $result = array();
        foreach($customer as $customers){

            for($i=0;$i<$lenghtofarr;$i++) {
                $temp['Customer'] = $customers["AccountName"];
                $temp['CRate'] = $customers['Rate'];
                $temp['Vendor'] = $vendor[$i];
                $temp['Rate'] = $rate[$i];
                if($rate[$i] > 0){
                    $margin_percentage  = (floatval($customers['Rate'])) * 100/ floatval($rate[$i]) - 100;
                    $marginamt = floatval($customers['Rate']) - floatval($rate[$i]);
                    $margin = number_format((float)$marginamt, $decimalpoint, '.', '');
                }else{
                    $margin_percentage  = 0;
                    $margin = 0;
                }

                $temp['Margin'] = $margin ." ( ".sprintf('%0.2f', $margin_percentage) ."% )";
                array_push($result, $temp);
            }

        }

        if ($type == 'csv') {
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/LCR-Customer-Rate.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->write_excel($result);
            $filename = base64_encode('LCR-Customer-Rate.csv');
        } elseif ($type == 'xlsx') {
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') . '/LCR-Customer-Rate.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->write_excel($result);
            $filename = base64_encode('LCR-Customer-Rate.xls');
        }

        return json_encode(["fileurl"=>$filename]);



    }

    public function editPreference(){
        $postdata = Input::all();
        $preference =  !empty($postdata['preference']) ? $postdata['preference'] : 0;
        $acc_id =  $postdata['acc_id'];
        $trunk =  $postdata['trunk'];
        $Timezones =  $postdata['Timezones'];
        $rowcode = $postdata["rowcode"];
        $CodeDeckId = $postdata["CodeDeckId"];
        $description = $postdata["description"];
        $username = User::get_user_full_name();
        $query = "call prc_editpreference ('".$postdata["GroupBy"]."',".$preference.",".$acc_id.",".$trunk.",".$Timezones.",".$rowcode.",".$CodeDeckId.",'".$description."','".$username."')";
        //\Illuminate\Support\Facades\Log::info($query);
        $results = DB::select($query);
        $preference = isset($results[0]->Preference) ? $results[0]->Preference : '';
        try{
            $message =  "Preference Update Successfully";
            return json_encode(["status" => "success", "message" => $message,"preference"=>$preference]);
        }catch ( Exception $ex ){
            $message =  "Oops Somethings Wrong !";
            return json_encode(["status" => "fail", "message" => $message,"preference"=>$preference]);
        }
    }

}
