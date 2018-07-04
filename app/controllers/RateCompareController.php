<?php

class RateCompareController extends \BaseController {

    public function index() {

            $trunks = Trunk::getTrunkDropdownIDList();
            $default_trunk = getDefaultTrunk($trunks);
            $codedecklist = BaseCodeDeck::getCodedeckIDList();
            $currencies = Currency::getCurrencyDropdownIDList();
            $CurrencyID = Company::where("CompanyID",User::get_companyID())->pluck("CurrencyId");

            $all_vendors = Account::getAccountIDList(['IsVendor'=>1]);
            if(!empty($all_vendors[''])){
                unset($all_vendors['']);
            }
            $all_customers = Account::getAccountIDList(['IsCustomer'=>1]);
            if(!empty($all_customers[''])){
                unset($all_customers['']);
            }
            $companyID = User::get_companyID();
            $DefaultCodedeck = BaseCodeDeck::where(["CompanyID"=>$companyID,"DefaultCodedeck"=>1])->pluck("CodeDeckId");

            $rate_table = RateTable::getRateTableList([]);

            $GroupBy =    NeonCookie::getCookie('_RateCompare_GroupBy');
            $Timezones = Timezones::getTimezonesIDList();

            return View::make('rate_compare.index', compact('trunks', 'currencies','CurrencyID','codedecklist', 'DefaultCodedeck' , 'default_trunk','all_vendors','all_customers','rate_table','GroupBy','Timezones'));
    }

    public function search_ajax_datagrid($type) {

        ini_set ( 'max_execution_time', 90);
        $companyID = User::get_companyID();
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['isExport'] = 0;

        $GroupBy = Invoice::getCookie('_RateCompare_GroupBy');
        if($data['GroupBy'] != $GroupBy) {
            NeonCookie::setCookie('_RateCompare_GroupBy',$data['GroupBy'],60);
        }

        $query = "call prc_RateCompare (".$companyID.",".$data['Trunk']."," . $data['Timezones'] . ",".$data['CodeDeck'].",'".$data['Currency']."','".$data['Code']."','".$data['Description']."','".$data['GroupBy']."','".$data['SourceVendors']."','".$data['SourceCustomers']."','".$data['SourceRateTables']."','".$data['DestinationVendors']."','".$data['DestinationCustomers']."','".$data['DestinationRateTables']."','".$data['Effective']."','".$data['SelectedEffectiveDate']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {

            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            foreach($excel_data as $rowno => $rows){
                $column_no = 0;
                foreach($rows as $col_name => $colval){
                    unset($excel_data[$rowno][$col_name]);
                    if($col_name =='Destination'){
                        $col_name = "";
                    }
                    $col_name = str_replace( "<br>" , "\n" ,$col_name );


                    // Add margin
                    $colum_margin = "margin_".$column_no++;
                    if(isset($data[$colum_margin]) && !empty($data[$colum_margin])){
                        $margin =  $data[$colum_margin];
                        $colval_array = explode( "<br>" , $colval );
                        if(isset($colval_array[0])) {
                            $Rate = $colval_array[0];
                            $EffectiveDate = $colval_array[1];
                            $Rate = $this->add_margin($margin, $Rate);
                            $excel_data[$rowno][$col_name] = $Rate . " \n " . $EffectiveDate;
                        }
                        //
                    }else {
                        $excel_data[$rowno][$col_name] = str_replace( "<br>" , "\n" ,$colval );
                    }



                }
            }

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RateCompare.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RateCompare.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }

        $query .=',0)';

        \Illuminate\Support\Facades\Log::info($query);

        return DataTableSql::of($query)->make();

    }


    public function rate_update(){

        $data = \Illuminate\Support\Facades\Input::all();

        $data['CompanyID'] =  User::get_companyID();

        if(isset($data["Action"]) && $data["Action"] == 'edit') {

            if (isset($data["GroupBy"]) && $data["GroupBy"] == 'code') {

                $rules = array(
                    'GroupBy' => 'required',
                    'Type' => 'required',
                    'TypeID' => 'required',
                    'Code' => 'required',
                    'Description' => 'required',
                    'NewDescription' => 'required',
                    'Rate' => 'required',
                    'EffectiveDate' => 'required',
                    'TrunkID' => 'required',
                    'Effective' => 'required',
                );
            } else {

                $rules = array(
                    'GroupBy' => 'required',
                    'Type' => 'required',
                    'TypeID' => 'required',
                    'Description' => 'required',
                    'NewDescription' => 'required',
                    'Rate' => 'required',
                    'TrunkID' => 'required',
                    'Effective' => 'required',
                );

            }


            $validator = Validator::make($data, $rules);

            if (!isset($data['SelectedEffectiveDate']) || empty($data['SelectedEffectiveDate'])) {
                $data['SelectedEffectiveDate'] = date('Y-m-d');
            }
            if ($validator->fails()) {

                return json_validator_response($validator);
            }


        }else if(isset($data["Action"]) && $data["Action"] == 'add') {

            //@TODO Add is yet to be done.
            /*
             *
            if (isset($data["GroupBy"]) && $data["GroupBy"] == 'code') {

                $rules = array(
                    'GroupBy' => 'required',
                    'Type' => 'required',
                    'TypeID' => 'required',
                    'Code' => 'required',
                    'Description' => 'required',
                    'Rate' => 'required',
                    'EffectiveDate' => 'required',
                    'TrunkID' => 'required',
                    'Effective' => 'required',
                );
            } else {

                $rules = array(
                    'GroupBy' => 'required',
                    'Type' => 'required',
                    'TypeID' => 'required',
                    'Description' => 'required',
                    'Rate' => 'required',
                    'EffectiveDate' => 'required',
                    'TrunkID' => 'required',
                    'Effective' => 'required',
                );
            }

            $validator = Validator::make($data, $rules);

            if (!isset($data['SelectedEffectiveDate']) || empty($data['SelectedEffectiveDate'])) {
                $data['SelectedEffectiveDate'] = date('Y-m-d');
            }
            if ($validator->fails()) {

                return json_validator_response($validator);
            }

            */
        }


        $query = "call prc_RateCompareRateUpdate (" . $data['CompanyID'] . ",'" . $data['GroupBy'] . "','" . $data['Type'] . "','" . $data['TypeID'] . "','" . $data['Rate'] . "','" . $data['Code'] . "','" . $data['Description'] . "','" . $data['NewDescription'] . "','" . $data['EffectiveDate'] . "','" . $data['TrunkID'] . "','" . $data['Effective'] . "','" . $data['SelectedEffectiveDate'] . "' );";
        \Illuminate\Support\Facades\Log::info($query);

        $result = DB::select($query);
        $result_array = json_decode(json_encode($result), true);

        if (count($result_array) > 0) {
            if (isset($result_array[0]["rows_update"])) {
                $message = $result_array[0]["rows_update"];
                 return Response::json(array("status" => "success", "message" => $message));
            }
        }
        return Response::json(array("status" => "success", "message" => "No Records updated."));

    }

    public function load_account_dropdown() {

        $data = \Illuminate\Support\Facades\Input::all();

        $data['CompanyID'] =  User::get_companyID();


        $select2_customer = $customers_array = Account::getAccountDropdownWithTrunk($data);

        /*$select2_customer = array_map(function($customers_array) {
            return array("id" => $customers_array["AccountID"],"text" => $customers_array["AccountName"]);
        },$customers_array);*/

        return Response::json(array("status" => "success", "message" => "" ,"data" =>  $select2_customer));

    }

    public function add_margin($margin, $rate) {

        if ( strpos("p",$margin)  !== FALSE ) {

            $numeric_margin = str_replace("p","",$margin);

            $new_rate = ($rate + ( $rate * $numeric_margin / 100 ));

        } else {

            $new_rate = $rate + $margin;

        }

        return number_format(doubleval($new_rate),6);

    }

}
