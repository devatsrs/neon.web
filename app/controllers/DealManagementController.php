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
            DB::raw('DATE_FORMAT(tblDeal.StartDate, "%Y-%m-%d") as StartDate'),
            DB::raw('DATE_FORMAT(tblDeal.EndDate, "%Y-%m-%d") as EndDate'),
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
            $deals->where(DB::raw('DATE(tblDeal.StartDate)'),'>=',$data['StartDate']);
        }

        if(isset($data['EndDate']) && $data['EndDate'] != ""){
            $deals->where(DB::raw('DATE(tblDeal.EndDate)'),'<=',$data['EndDate']);
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
        $Countries = Country::getCountryDropdownIDList();
        $Trunks = CustomerTrunk::getTrunkDropdownIDListAll();
        return View::make('dealmanagement.create', get_defined_vars());
    }

    public function store(){
        $data = Input::all();
        $validator = Validator::make($data, Deal::$rules);

        if ($validator->fails())
            return json_validator_response($validator);

        if($data['StartDate'] > $data['EndDate']){
            return Response::json(array("status" => "failed", "message" => "Dates are invalid"));
        }

        try{
            DB::beginTransaction();

            $dealData['Title']      = $data['Title'];
            $dealData['DealType']   = $data['DealType'];
            $dealData['AccountID']  = $data['AccountID'];
            $dealData['CodedeckID'] = $data['CodedeckID'];
            $dealData['AlertEmail'] = isset($data['AlertEmail']) ? $data['AlertEmail'] : "";
            $dealData['Status']     = $data['Status'];
            $dealData['StartDate']  = $data['StartDate'];
            $dealData['EndDate']    = $data['EndDate'];
            $dealData['TotalPL']    = 0;
            $dealData['CreatedBy']  = User::get_user_full_name();
            $deal = Deal::create($dealData);
            $DealID = $deal->DealID;

            DB::commit();
            $res = array("status" => "success", "message" => "Deal Successfully Created.", "redirect" => url("dealmanagement/$DealID/edit"));
        }catch (Exception $e){
            DB::rollback();
            $res = array("status" => "failed", "message" => "Something Went Wrong." . $e->getMessage());
        }

        return Response::json($res);
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
        $Countries = Country::getCountryDropdownIDList();
        $Trunks = CustomerTrunk::getTrunkDropdownIDListAll();
        $Deal = Deal::find($id);
        $CodeDeckID  = $Deal->CodedeckID;
        $DealDetails = DealDetail::where('DealID',$id)->get();
        $destinationBreaks = CodeDeck::where(['CodeDeckId' => $CodeDeckID])
            ->select('Description')->distinct()->lists("Description","Description");
        $destinationBreaks = !empty($destinationBreaks) ? ['' => 'Select'] + $destinationBreaks : ['' => 'Select'];
        $DealNotes = DealNote::where('DealID',$id)->get();
        return View::make('dealmanagement.edit', get_defined_vars());
    }

    public function update($id){

        $data  = Input::all();
        $rules =  Deal::$rules;
        $rules['Title']   = 'required|unique:tblDeal,Title,'.$id.',DealID';
        $rules['TotalPL'] = 'required';
        $validator = Validator::make($data,$rules);

        if ($validator->fails())
            return json_validator_response($validator);

        try{
            DB::beginTransaction();
            $dealData['Title']      = $data['Title'];
            $dealData['DealType']   = $data['DealType'];
            $dealData['AccountID']  = $data['AccountID'];
            $dealData['CodedeckID'] = $data['CodedeckID'];
            $dealData['AlertEmail'] = isset($data['AlertEmail']) ? $data['AlertEmail'] : "";
            $dealData['Status']     = $data['Status'];
            $dealData['StartDate']  = $data['StartDate'];
            $dealData['EndDate']    = $data['EndDate'];
            $dealData['TotalPL']    = $data['TotalPL'];
            $dealData['ModifiedBy'] = User::get_user_full_name();
            Deal::where('DealID', $id)->update($dealData);

            $dealDetailValid = Deal::dealDetailArray($id,$data);
            if($dealDetailValid['status'] == false)
                return Response::json(array(
                    "status" => "failed",
                    "message" => $dealDetailValid['message']
                ));

            $dealDetailData = $dealDetailValid['data'];
            DealDetail::where('DealID', $id)->delete();
            if(!empty($dealDetailData))
                DealDetail::insert($dealDetailData);

            $dealNoteData = Deal::dealNoteArray($id,$data);

            DealNote::where('DealID', $id)->delete();
            if(!empty($dealDetailData))
                DealNote::insert($dealNoteData);

            DB::commit();
            $res = array("status" => "success", "message" => "Deal Successfully Updated.");
        }catch (Exception $e){
            DB::rollback();
            $res = array("status" => "failed", "message" => "Something Went Wrong." . $e->getMessage());
        }

        return Response::json($res);
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



    function getDestinationBreak(){
        $data = Input::all();

        if(isset($data['id']) && !empty($data['id'])){
            $res = ["data" => []];
            $where = ['CodeDeckId' => $data['id']];
            if(isset($data['destination']) && $data['destination'] != 0)
                $where['CountryID'] = $data['destination'];

            $destinationBreaks = CodeDeck::where($where)
                ->select('Description')->distinct()->lists("Description","Description");

            if($destinationBreaks != false)
                $res = ['data' => $destinationBreaks];

            return Response::json($res);
        }
    }

    public function customer_report(){
        $data = array();
        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = 1;
        $UserID  = User::get_userID();
        $where['Status'] = 1;
        $where['VerificationStatus'] = Account::VERIFIED;
        $where['CompanyID']=User::get_companyID();
        if(User::is('AccountManager')){
            $where['Owner'] = User::get_userID();
            $isAdmin = 0;
        }
        $account_owners = User::getOwnerUsersbyRole();
        $gateway   = CompanyGateway::getCompanyGatewayIdList($companyID);
        $Country   = Country::getCountryDropdownIDList();
        $account   = Account::getAccountIDList();
        $trunks    = Trunk::getTrunkDropdownIDList($companyID);
        $currency  = Currency::getCurrencyDropdownIDList($companyID);
        $timezones = TimeZone::getTimeZoneDropdownList();
        $MonitorDashboardSetting = array_filter(explode(',',CompanyConfiguration::getValueConfigurationByKey('MONITOR_DASHBOARD',$companyID)));
        $reseller_owners = Reseller::getDropdownIDList($companyID);
        return View::make('dealmanagement.report.customer_report',compact('gateway','UserID','Country','account','DefaultCurrencyID','original_startdate','original_enddate','isAdmin','trunks','currency','timezones','MonitorDashboardSetting','account_owners','reseller_owners'));
    }

    public function vendor_report(){
        $data = array();
        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = 1;
        $UserID  = User::get_userID();
        $where['Status'] = 1;
        $where['VerificationStatus'] = Account::VERIFIED;
        $where['CompanyID']=User::get_companyID();
        if(User::is('AccountManager')){
            $where['Owner'] = User::get_userID();
            $isAdmin = 0;
        }
        $account_owners = User::getOwnerUsersbyRole();
        $gateway   = CompanyGateway::getCompanyGatewayIdList($companyID);
        $Country   = Country::getCountryDropdownIDList();
        $account   = Account::getAccountIDList();
        $trunks    = Trunk::getTrunkDropdownIDList($companyID);
        $currency  = Currency::getCurrencyDropdownIDList($companyID);
        $timezones = TimeZone::getTimeZoneDropdownList();
        $MonitorDashboardSetting = array_filter(explode(',',CompanyConfiguration::getValueConfigurationByKey('MONITOR_DASHBOARD',$companyID)));
        $reseller_owners = Reseller::getDropdownIDList($companyID);
        return View::make('dealmanagement.report.vendor_report',compact('gateway','UserID','Country','account','DefaultCurrencyID','original_startdate','original_enddate','isAdmin','trunks','currency','timezones','MonitorDashboardSetting','account_owners','reseller_owners'));
    }
}