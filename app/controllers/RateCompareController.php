<?php

class RateCompareController extends \BaseController {

    public function index() {
            $trunks = Trunk::getTrunkDropdownIDList();
            $trunk_keys = getDefaultTrunk($trunks);
            //$countries = Country::getCountryDropdownIDList();
            $codedecklist = BaseCodeDeck::getCodedeckIDList();
            $currencies = Currency::getCurrencyDropdownIDList();
            $CurrencyID = Company::where("CompanyID",User::get_companyID())->pluck("CurrencyId");
            $LCRPosition = NeonCookie::getCookie('LCRPosition',5);

            $all_vendors = Account::getAccountIDList(['IsVendor'=>1]);
            if(!empty($all_vendors[''])){
                unset($all_vendors['']);
            }
            $all_customers = Account::getAccountIDList(['IsCustomer'=>1]);
            if(!empty($all_customers[''])){
                unset($all_customers['']);
            }

            $rate_table = RateTable::getRateTableList([]);

            $GroupBy =    NeonCookie::getCookie('_RateCompare_GroupBy');

            return View::make('rate_compare.index', compact('trunks', 'currencies','CurrencyID','codedecklist','trunk_keys','LCRPosition','all_vendors','all_customers','rate_table','GroupBy'));
    }

    public function search_ajax_datagrid() {

        ini_set ( 'max_execution_time', 90);
        $companyID = User::get_companyID();
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['isExport'] = 0;

        $GroupBy = Invoice::getCookie('_RateCompare_GroupBy');
        if($data['GroupBy'] != $GroupBy) {
            NeonCookie::setCookie('_RateCompare_GroupBy',$data['GroupBy'],60);
        }

        $query = "call prc_RateCompare (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$data['GroupBy']."','".$data['SourceVendors']."','".$data['SourceCustomers']."','".$data['SourceRateTables']."','".$data['DestinationVendors']."','".$data['DestinationCustomers']."','".$data['DestinationRateTables']."','".$data['Effective']."','".$data['SelectedEffectiveDate']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."'";
        $query .=',0)';

        \Illuminate\Support\Facades\Log::info($query);

        return DataTableSql::of($query)->make();

    }


}
