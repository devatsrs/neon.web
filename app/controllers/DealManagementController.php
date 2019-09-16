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
        $select = [
            'tblDeal.Title',
            'tblAccount.AccountName',
            'tblCodeDeck.CodeDeckName',
            'tblDeal.StartDate',
            'tblDeal.EndDate',
            'tblDeal.AlertEmail',
            'tblDeal.DealType',
            'tblDeal.Status'
        ];
        $deals = Deal::join("tblAccount", "tblAccount.AccountID","=","tblDeal.AccountID")
            ->join("tblCodeDeck", "tblCodeDeck.CodeDeckId","=","tblDeal.CodeDeckID")
            ->where(['tblDeal.Status' => $data['Status']]);

        if(isset($data['Search']) && $data['Search'] != ""){
            $deals->where('tblDeal.Title','like','%'.$data['Search'].'%');
        }

        if(isset($data['AccountID']) && $data['AccountID'] != ""){
            $deals->where('tblDeal.AccountID', $data['AccountID']);
        }

        if(isset($data['DealType']) && $data['DealType'] != ""){
            $deals->where('tblDeal.DealType', $data['DealType']);
        }

        if(isset($data['StartDate']) && $data['StartDate'] != ""){
            $deals->where('tblDeal.StartDate','>=',$data['StartDate']);
        }

        if(isset($data['EndDate']) && $data['EndDate'] != ""){
            $deals->where('tblDeal.EndDate','<=',$data['EndDate']);
        }

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data = $deals->select($select)->get();
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DealManagement.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/DealManagement.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }

        $select[] = 'tblDeal.DealID';
        $deals->select($select);

        return Datatables::of($deals)->make();
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
        $dealTypes = Deal::$TypeDropDown + ['' => "Both"];
        return View::make('dealmanagement.index', get_defined_vars());
    }


    public function create(){
        $CompanyID          = User::get_companyID();
        $codedecklist       = BaseCodeDeck::getCodedeckIDList();
        unset($codedecklist['']);
        $DefaultCodedeck    = BaseCodeDeck::where([
            "CompanyID"       => $CompanyID,
            "DefaultCodedeck" => 1
        ])->pluck("CodeDeckId");
        $Accounts = Account::getAccountIDList();
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
        unset($codedecklist['']);
        $DefaultCodedeck    = BaseCodeDeck::where([
            "CompanyID"       => $CompanyID,
            "DefaultCodedeck" => 1
        ])->pluck("CodeDeckId");
        $Accounts = Account::getAccountIDList();
        $Deal = Deal::find($id);
        $DealDetails = DealDetail::where('DealID',$id)->get();
        $DealNotes = DealNote::where('DealID',$id)->get();
        return View::make('dealmanagement.edit', get_defined_vars());
    }

    public function update($id){

    }

    public function delete($id)
    {
        $data['id'] = $id;
        $res = array("status" => "failed", "message" => "Invalid Request.");
        if( $id > 0){
            try{
                DB::beginTransaction();
                DealDetail::where(["DealID"=>$id])->delete();
                DealNote::where(["DealID"=>$id])->delete();
                Deal::find($id)->delete();
                DB::commit();

                UserActivity::UserActivitySaved($data,'Delete','Deal');
                $res = array("status" => "success", "message" => "Deal Successfully Deleted");
            }catch (Exception $e){
                DB::rollback();
                $res = array("status" => "failed", "message" => "Something Went Wrong." . $e->getMessage());
            }
        }

        return Response::json($res);
    }

}