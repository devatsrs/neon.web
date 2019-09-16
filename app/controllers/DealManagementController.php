<?php

class DealManagementController extends \BaseController {


    /**
     * @param $type
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function ajax_datagrid($type = "") {
        $data       = Input::all();
        $CompanyID  = User::get_companyID();

        $data['iDisplayStart'] 		 	 =		0;
        $data['iDisplayStart'] 			+=		1;
        $data['iSortCol_0']			 	 =  	0;
        $data['sSortDir_0']			 	 =  	'desc';
        $query = "";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Accounts.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Accounts.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }

        return DataTableSql::of($query)->make();
    }



    /**
     * Display a listing of the resource.
     * GET /deal_management
     *
     * @return \Illuminate\Support\Facades\Response
     */

    public function index()
    {
        $CompanyID          = User::get_companyID();
        $codedecklist       = BaseCodeDeck::getCodedeckIDList();
        $DefaultCodedeck    = BaseCodeDeck::where([
            "CompanyID"       => $CompanyID,
            "DefaultCodedeck" => 1
        ])->pluck("CodeDeckId");
        $status_json = "";
        $accounts = Account::getAccountIDList();
        return View::make('dealmanagement.index', get_defined_vars());
    }


    public function create(){
        $CompanyID          = User::get_companyID();
        $codedecklist       = BaseCodeDeck::getCodedeckIDList();
        $DefaultCodedeck    = BaseCodeDeck::where([
            "CompanyID"       => $CompanyID,
            "DefaultCodedeck" => 1
        ])->pluck("CodeDeckId");
        $accounts = Account::getAccountIDList();
        return View::make('dealmanagement.create', get_defined_vars());
    }

    public function store(){


        $data = Input::all();
        unset($data['BarCode']);
        if($data){

        }
    }

    public function edit($id){

        $CompanyID          = User::get_companyID();
        $codedecklist       = BaseCodeDeck::getCodedeckIDList();
        $DefaultCodedeck    = BaseCodeDeck::where([
            "CompanyID"       => $CompanyID,
            "DefaultCodedeck" => 1
        ])->pluck("CodeDeckId");
        $accounts = Account::getAccountIDList();
        return View::make('dealmanagement.edit', get_defined_vars());
    }

    public function update($id){

    }

    public function delete($id){

    }

}