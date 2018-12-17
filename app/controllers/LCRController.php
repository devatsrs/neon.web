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

        $data['merge_timezones'] = $data['merge_timezones'] == 'true' ? 1 : 0;
        $data['Timezones'] = $data['merge_timezones'] == 1 ? $data['TimezonesMerged'] : $data['Timezones'];

        $query = "call prc_SIGetLCRwithPrefix (".$companyID.",".$data['Trunk'].",'".$data['Timezones']."',".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$AccountIDs."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."','".intval($data['LCRPosition'])."','".intval($data['vendor_block'])."','".$data['SelectedEffectiveDate']."' ,'".intval($data['show_all_vendor_codes'])."' ,'".$data['merge_timezones']."' ,'".intval($data['TakePrice'])."' ";

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

        return View::make('lcr.index', compact('trunks', 'currencies','CurrencyID','codedecklist','DefaultCodedeck','trunk_keys','LCRPosition','all_accounts','Timezones'));
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

        $data = Input::all();

        $preference =  !empty($data['preference']) ? $data['preference'] : 5;
        $username = User::get_user_full_name();

        RateTableRate::where(["RateTableRateID"=>$data["RateTableRateID"]])->update(["Preference"=>$preference,"ModifiedBy"=>$username]);

        $message =  "Preference Update Successfully";

        return json_encode(["status" => "success", "message" => $message,"preference"=>$preference]);

    }

}
