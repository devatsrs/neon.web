<?php

class LCRController extends \BaseController {

    public function search_ajax_datagrid($type) {
        ini_set ( 'max_execution_time', 90);
        $companyID = User::get_companyID();
        $data = Input::all();
        $data['Use_Preference'] = $data['Use_Preference'] == 'true' ? 1:0;
        $data['iDisplayStart'] +=1;
        $query = "call prc_GetLCR (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Country']."','".$data['Code']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."','".intval($data['Use_Preference'])."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            foreach($excel_data as $rowno => $rows){
                foreach($rows as $colno => $colval){
                    $excel_data[$rowno][$colno] = str_replace( "<br>" , "\n" ,$colval );
                }
            }

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/LCR.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/LCR.xlsx';
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

        return DataTableSql::of($query)->make();

    }

    public function index() {
            $trunks = Trunk::getTrunkDropdownIDList();
            $trunk_keys = getDefaultTrunk($trunks);
            $countries = Country::getCountryDropdownIDList();
            $codedecklist = BaseCodeDeck::getCodedeckIDList();

            return View::make('lcr.index', compact('trunks', 'countries','codedecklist','trunk_keys'));
    }

    public function exports(){


            $companyID = User::get_companyID();
            $data = Input::all();

            $data['iDisplayStart'] +=1;
            $query = "call prc_GetLCR (".$companyID.",".$data['Trunk'].",".$data['CodeDeck'].",'".$data['Country']."','".$data['Code']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$data['sSortDir_0']."',1)";

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
}
