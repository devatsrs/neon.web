<?php

class RateCustomerController extends \BaseController {

    private $trunks, $trunks_cache, $countries, $rate_sheet_formates;

    public function __construct() {

        $this->countries = Country::getCountryDropdownIDList();
        //$this->rate_sheet_formates = RateSheetFormate::getCustomerRateSheetFormatesDropdownList('customer');
    }

    public function search_ajax_datagrid($id,$type) {

        $data = Input::all();
        $data['iDisplayStart'] +=1;
        //$data['Effected_Rates_on_off'] = $data['Effected_Rates_on_off']!= 'true'?0:1;
        $data['Effected_Rates_on_off']=1;
        $data['Country']=$data['Country']!= ''?$data['Country']:'null';
        $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';

        $columns = array('RateID','Code','Description','Interval1','IntervalN','ConnectionFee','RoutinePlan','Rate','EffectiveDate','LastModifiedDate','LastModifiedBy','CustomerRateId');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = Customer::get_companyID();

        if($data['Effective'] == 'CustomDate') {
            $CustomDate = $data['CustomDate'];
        } else {
            $CustomDate = date('Y-m-d');
        }

        $query = "call prc_GetCustomerRate (".$companyID.",".$id.",".$data['Trunk'].",NULL,".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."','".$CustomDate."',".$data['Effected_Rates_on_off'].",'".intval($data['RoutinePlanFilter'])."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        //log::info("call prc_GetCustomerRate (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['Effected_Rates_on_off'].",'".intval($data['RoutinePlanFilter'])."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)");

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',2)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Outbound Rates.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Outbound Rates.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Customer Rates', function ($excel) use ($excel_data) {
                $excel->sheet('Customer Rates', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';
        //echo $query;exit;
        return DataTableSql::of($query)->make();
    }
    public  function search_customer_grid($id){
        $companyID = Customer::get_companyID();
        $data = Input::all();
        $opt = $data;
        $opt["CompanyID"] = $companyID;
        $opt["AccountID"] = $id;
        return Account::getCustomersGridPopup($opt);

    }

    // outbound rate display
    public function index() {
            $id = Customer::get_accountID();
            $Account = Account::find($id);
            $displayinbound = $Account->InboudRateTableID;
            if(empty($displayinbound)){
                $displayinbound = 0;
            }
            $displayservice = 0;
            if($this->displayServiceRate($id)){
                $displayservice=1;
            }

            $countries = $this->countries;
            $trunks = CustomerTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            $routine = CustomerTrunk::getRoutineDropdownIDList($id);
            $account_owners = User::getOwnerUsersbyRole();// not using
            $trunks_routing =$trunks;
            $trunks_routing[""] = 'Select';
            /*if(count($trunks) == 0){
                return  Redirect::to('customers_rates/settings/'.$id)->with('info_message', 'Please enable trunks against customer to setup rates');
            }*/
            $CurrencySymbol = Currency::getCurrencySymbol($Account->CurrencyId);
            return View::make('customer.customersrates.index', compact('id', 'trunks', 'countries','Account','routine','trunks_routing','account_owners','trunk_keys','CurrencySymbol','displayinbound','displayservice'));


    }

    /**
     * Trunk Setting only display when Customer login
     */
    public function settings() {

            if(is_reseller()){
                return  Redirect::to('customer/customers_rates/rate');
            }

            $id = Customer::get_accountID();
            $Account = Account::find($id);
            $displayinbound = $Account->InboudRateTableID;
            if(empty($displayinbound)){
                $displayinbound = 0;
            }
            $displayservice = 0;
            if($this->displayServiceRate($id)){
                $displayservice=1;
            }
            $company_id = Customer::get_companyID();
            $trunks = Trunk::getTrunkCacheObj($company_id);
            $customer_trunks = CustomerTrunk::getCustomerTrunksByTrunkAsKey($id);
            $codedecklist = BaseCodeDeck::getCodedeckIDList($company_id);
            $rate_tables =array();
            $rate_table = RateTable::where(["Status" => 1, "CompanyID" => $company_id,'CurrencyID'=>$Account->CurrencyId])->get();
            foreach($rate_table as $row){
                $rate_tables[$row->TrunkID][$row->CodeDeckId][] = ['text'=>$row->RateTableName,'value'=>$row->RateTableId];
            }
            $companygateway = CompanyGateway::getCompanyGatewayIdList();
            unset($companygateway['']);

            $activetrunks = array();
            $alltrunk = array();
            if(count($trunks)>0 && count($customer_trunks)>0){
                foreach($trunks as $trunk){
                    foreach($customer_trunks as $ct){
                        if($trunk->TrunkID==$ct->TrunkID && $ct->Status==1){
                            $alltrunk['Trunk']=$trunk->Trunk;
                            $alltrunk['Prefix']=$ct->Prefix;
                            $activetrunks[]=$alltrunk;
                        }
                    }
                }
            }
            return View::make('customer.customersrates.trunks', compact('id', 'trunks','activetrunks','displayinbound', 'customer_trunks','codedecklist','Account','rate_tables','Account','companygateway','displayservice'));
    }


    // inbound rate display
    public function inboundrate() {
        $id = Customer::get_accountID();
        $Account = Account::find($id);
        $displayinbound = $Account->InboudRateTableID;
        if(empty($displayinbound)){
            $displayinbound = 0;
        }
        if($displayinbound == 0){
            return  Redirect::to('customer/customers_rates')->with('info_message', Lang::get("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_MSG_NO_INBOUND_RATE_TABLE_ASSIGN"));
        }
        $displayservice = 0;
        if($this->displayServiceRate($id)){
            $displayservice=1;
        }
        $countries = $this->countries;
        $trunks = CustomerTrunk::getTrunkDropdownIDList($id);
        $trunk_keys = getDefaultTrunk($trunks);
        $routine = CustomerTrunk::getRoutineDropdownIDList($id);
        $account_owners = User::getOwnerUsersbyRole(); //not using
        $trunks_routing =$trunks;
        $trunks_routing[""] = 'Select';

        $CurrencySymbol = Currency::getCurrencySymbol($Account->CurrencyId);
        return View::make('customer.customersrates.inboundrate', compact('id', 'trunks', 'countries','Account','routine','trunks_routing','account_owners','trunk_keys','CurrencySymbol','displayinbound','displayservice'));


    }


    public function search_inbound_ajax_datagrid($id,$type) {

        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $Account = Account::find($id);
        $RateTableId = $Account->InboudRateTableID;
        //$data['Effected_Rates_on_off'] = $data['Effected_Rates_on_off']!= 'true'?0:1;
        $data['Effected_Rates_on_off']=1;
        $data['Country']=$data['Country']!= ''?$data['Country']:'null';
        $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';
        $data['Trunk'] = 'null';

        $columns = array('RateID','Code','Description','Interval1','IntervalN','ConnectionFee','Rate','EffectiveDate','LastModifiedDate','LastModifiedBy','CustomerRateId');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = Customer::get_companyID();

        $query = "call prc_CustomerPanel_GetinboundRate (".$companyID.",".$id.",".$RateTableId.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['Effected_Rates_on_off'].",'".intval($data['RoutinePlanFilter'])."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        //log::info("call prc_CustomerPanel_GetinboundRate (".$companyID.",".$id.",".$RateTableId.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['Effected_Rates_on_off'].",'".intval($data['RoutinePlanFilter'])."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)");

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Inbound Rates.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Inbound Rates.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Customer Rates', function ($excel) use ($excel_data) {
                $excel->sheet('Customer Rates', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';
        //echo $query;exit;
        return DataTableSql::of($query)->make();
    }

    public function servicerate(){
        $id = Customer::get_accountID();
        $Account = Account::find($id);
        $services = AccountService::select(['tblAccountService.ServiceID','tblService.ServiceName'])
                    ->join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')
                    ->where(["tblAccountService.AccountID"=>$id,"tblAccountService.Status"=>1])
                    ->lists('ServiceName','ServiceID');

        $Count = AccountTariff::where('AccountID',$id)->count();

        if($Count == 0 || count($services) == 0){
            return  Redirect::to('customer/customers_rates')->with('info_message', 'No Service assign');
        }

        $displayinbound = $Account->InboudRateTableID;
        if(empty($displayinbound)){
            $displayinbound = 0;
        }

        $countries = $this->countries;

        $services = array(""=> "Select")+$services;
        $type = array(''=>'Select',AccountTariff::OUTBOUND=>'Outbound',AccountTariff::INBOUND=>'Inbound');

        $CurrencySymbol = Currency::getCurrencySymbol($Account->CurrencyId);
        return View::make('customer.customersrates.servicerate', compact('id','countries','Account','CurrencySymbol','displayinbound','services','type'));


    }

    public function search_service_ajax_datagrid($id,$type) {

        $data = Input::all();
        $RateType = $data['RateType'];
        $ServiceID = $data['Service'];

        $RateTableID = AccountTariff::where(['AccountID'=>$id,'ServiceID'=>$ServiceID,'Type'=>$RateType])->pluck('RateTableID');
        if(empty($RateTableID)){
            $RateTableID = 0;
        }

        $data['iDisplayStart'] +=1;
        //$data['Effected_Rates_on_off'] = $data['Effected_Rates_on_off']!= 'true'?0:1;
        $data['Effected_Rates_on_off']=1;
        $data['Country']=$data['Country']!= ''?$data['Country']:'null';
        $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';
        $data['Trunk'] = 'null';

        $columns = array('RateID','Code','Description','Interval1','IntervalN','ConnectionFee','Rate','EffectiveDate','LastModifiedDate','LastModifiedBy','CustomerRateId');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = Customer::get_companyID();
        $data['RoutinePlanFilter'] = 0;
        $query = "call prc_CustomerPanel_GetinboundRate (".$companyID.",".$id.",".$RateTableID.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['Effected_Rates_on_off'].",'".intval($data['RoutinePlanFilter'])."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        //log::info("call prc_CustomerPanel_GetinboundRate (".$companyID.",".$id.",".$RateTableID.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".$data['Effected_Rates_on_off'].",'".intval($data['RoutinePlanFilter'])."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)");

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Service Rates.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Service Rates.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';
        return DataTableSql::of($query)->make();
    }

    /**
     * Service Rate only display when reseller login
    */
    public function displayServiceRate($id){
        $services = AccountService::select(['tblAccountService.ServiceID','tblService.ServiceName'])
            ->join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')
            ->where(["tblAccountService.AccountID"=>$id,"tblAccountService.Status"=>1])
            ->lists('ServiceName','ServiceID');

        $Count = AccountTariff::where('AccountID',$id)->count();
        if($Count == 0 || count($services) == 0 || Session::get('customer')==1){
            return false;
        }
        return true;
    }
}
