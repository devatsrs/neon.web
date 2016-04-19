<?php
class VendorRatesController extends \BaseController
{
    
    private $trunks, $countries , $rate_sheet_formates;
    public function __construct() {
        
         $this->countries = Country::getCountryDropdownIDList("All");
         $this->rate_sheet_formates = RateSheetFormate::getVendorRateSheetFormatesDropdownList();
        
         
    }

    public function search_ajax_datagrid($id) {

        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['Country']=$data['Country']!= 'All'?$data['Country']:'null';
        $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';

        $columns = array('VendorRateID','Code','Description','ConnectionFee','Interval1','IntervalN','Rate','EffectiveDate','updated_at','updated_by','VendorRateID');

        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetVendorRates (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";

        return DataTableSql::of($query)->make();
        
    }

    public function index($id) {
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
        $CurrencySymbol = Currency::getCurrencySymbol($Account->CurrencyId);
            $countries = $this->countries;
            return View::make('vendorrates.index', compact('id', 'trunks', 'trunk_keys', 'countries','Account','CurrencySymbol'));
    }

     
    
    public function upload($id) {
            $uploadtemplate = VendorFileUploadTemplate::getTemplateIDList();
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            $rate_sheet_formates = $this->rate_sheet_formates;
            return View::make('vendorrates.upload', compact('id', 'trunks', 'trunk_keys','rate_sheet_formates','Account','uploadtemplate'));
    }
    
    public function process_upload($id) {
        ini_set('max_execution_time', 0);
        if (Input::hasFile('excel')) {
            
            $data = Input::all();
            if (!isset($data['Trunk']) || empty($data['Trunk'])) {
                 return json_encode(["status" => "failed", "message" =>'Please Select a Trunk' ]);
            }else if (!isset($data['uploadtemplate']) || empty($data['uploadtemplate'])) {
                return json_encode(["status" => "failed", "message" =>'Please Select an upload template' ]);
            }

            $company_name = Account::getCompanyNameByID($id);
            $upload_path = Config::get('app.upload_path');
            $destinationPath = $upload_path . sprintf("\\%s\\", $company_name);
            $excel = Input::file('excel');
             // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();

            if (in_array($ext, array("csv", "xls", "xlsx"))) {
                $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                $excel->move($destinationPath, $file_name);
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD']) ;
                if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
                $data['full_path'] = $fullPath;
                $data["AccountID"] = $id;
                $data['codedeckid'] = VendorTrunk::where(["AccountID" => $id,'TrunkID'=>$data['Trunk']])->pluck("CodeDeckId");
                if (!isset($data['codedeckid']) || empty($data['codedeckid'])) {
                    return json_encode(["status" => "failed", "message" =>'Please Update a Codedeck in Setting' ]);
                }
                //Inserting Job Log
                try {
                    DB::beginTransaction();
                    unset($data['excel']);
                     //remove unnecesarry object
                    $result = Job::logJob("VU", $data);
                    
                    if ($result['status'] != "success") {
                        DB::rollback();
                        return json_encode(["status" => "failed", "message" => $result['message']]);
                    }
                    DB::commit();
                    return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
                }
                catch(Exception $ex) {
                    DB::rollback();
                    return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage() ]);
                }
            } else {
                echo json_encode(array("status" => "failed", "message" => "Please upload excel/csv file only."));
            }
        } else {
            echo json_encode(array("status" => "failed", "message" => "Please upload excel/csv file <5MB."));
        }
    }
    
    public function download($id) {
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            $rate_sheet_formates = $this->rate_sheet_formates;
            $downloadtype = [''=>'Select','xlsx'=>'EXCEL','csv'=>'CSV'];
            return View::make('vendorrates.download', compact('id', 'trunks', 'rate_sheet_formates','Account','downloadtype'));
    }
    
    public function process_download($id) {
        if (Request::ajax()) {
            
            $data = Input::all();
            
            $rules = array( 'isMerge' => 'required', 'Trunks' => 'required', 'Format' => 'required','filetype' => 'required' );
            if (!isset($data['isMerge'])) {
                $data['isMerge'] = 0;
            }


            $validator = Validator::make($data, $rules);
            
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if(!empty($data['filetype'])){
                $data['downloadtype'] = $data['filetype'];
                unset($data['filetype']);
            }

            
            //Inserting Job Log
            try {
                DB::beginTransaction();
                $data["AccountID"] = $id;
                $result = Job::logJob("VD", $data);
                
                if ($result['status'] != "success") {
                    DB::rollback();
                    return json_encode(["status" => "failed", "message" => $result['message']]);
                }
                DB::commit();
                return json_encode(["status" => "success", "message" => "File is added to queue for processing. You will be notified once file creation is completed. "]);
            }
            catch(Exception $ex) {
                DB::rollback();
                return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage() ]);
            }
        } else {
            echo json_encode(array("status" => "failed", "message" => "Access not allowed"));
        }
    }
    
    public function history($id) {
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            return View::make('vendorrates.history', compact('id','Account'));
    }

    public function history_ajax_datagrid($id) {
        $companyID = User::get_companyID();
        
        $RateSheetHistory = RateSheetHistory::join('tblJob','tblJob.JobID','=','tblRateSheetHistory.JobID')
                                            ->leftjoin('tblJobFile','tblJob.JobID','=','tblJobFile.JobID')
                                            ->where(["tblJob.CompanyID" => $companyID, "tblJob.AccountID" => $id])
                                   ->whereRaw("(tblRateSheetHistory.Type = 'VU' OR tblRateSheetHistory.Type = 'VD') ")
                                   ->select(array('tblJob.Title', 
                                                'tblRateSheetHistory.created_at as created_date','tblRateSheetHistory.CreatedBy',
                                                'tblRateSheetHistory.RateSheetHistoryID','tblRateSheetHistory.Type','tblJob.JobID','tblJob.OutputFilePath'));
        
        return Datatables::of($RateSheetHistory)->make();
    }
    public function show_history($id,$RateSheetHistoryID) {
        
        $history = RateSheetHistory::join('tblJob','tblJob.JobID','=','tblRateSheetHistory.JobID')
                                   ->where(["tblRateSheetHistory.RateSheetHistoryID" => $RateSheetHistoryID])
                                   ->select(
                                            'tblRateSheetHistory.Title',
                                            'tblRateSheetHistory.Description',
                                            'tblRateSheetHistory.Type',
                                            'tblJob.AccountID',
                                            'tblJob.Options',
                                            'tblJob.JobStatusMessage',
                                            'tblJob.OutputFilePath',
                                            'tblRateSheetHistory.created_at as created',
                                            'tblRateSheetHistory.JobID'
                                           )
                                    ->first();
        $job_file ='';
        if(isset($history->JobID)){
            $job_file = DB::table('tblJobFile')
                ->where("tblJobFile.JobID" , $history->JobID)
                ->first();
        }

        return View::make('vendorrates.show_history',compact('id','history','job_file'));
    }
    public function history_exports($id,$type) {
            $companyID = User::get_companyID();

            $RateSheetHistory = RateSheetHistory::join('tblJob', 'tblJob.JobID', '=', 'tblRateSheetHistory.JobID')
                ->where(["tblJob.CompanyID" => $companyID, "tblJob.AccountID" => $id])
                ->whereRaw("tblRateSheetHistory.Type = 'VU' OR tblRateSheetHistory.Type = 'VD' ")
                ->orderBy("tblRateSheetHistory.RateSheetHistoryID", "DESC")
                ->get(array('tblJob.Title', 'tblRateSheetHistory.created_at as created_date',
                ));

            $excel_data = json_decode(json_encode($RateSheetHistory),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Vendor Rates History.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Vendor Rates History.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*
            Excel::create('Vendor Rates History', function ($excel) use ($RateSheetHistory) {
                $excel->sheet('Vendor Rates History', function ($sheet) use ($RateSheetHistory) {
                    $sheet->fromArray($RateSheetHistory);
                });
            })->download('xls');*/
    }
    public function exports($id,$type) {
            $data = Input::all();
            $data['iDisplayStart'] +=1;
            $data['Country']=$data['Country']!= 'All'?$data['Country']:'null';
            $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
            $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';

            $columns = array('VendorRateID','Code','Description','Rate','EffectiveDate','updated_at','updated_by','VendorRateID');
            $sort_column = $columns[$data['iSortCol_0']];
            $companyID = User::get_companyID();

            $query = "call prc_GetVendorRates (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',1)";

            DB::setFetchMode( PDO::FETCH_ASSOC );
            $vendor_rates  = DB::select($query);
            DB::setFetchMode( Config::get('database.fetch'));

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Vendor Rates.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($vendor_rates);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Vendor Rates.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($vendor_rates);
            }


            /*Excel::create('Vendor Rates', function ($excel) use ($vendor_rates) {
                $excel->sheet('Vendor Rates', function ($sheet) use ($vendor_rates) {
                    $sheet->fromArray($vendor_rates);
                });
            })->download('xls');*/
    }

    public function uploaded_excel_file_download($id,$JobID){
            $filePath = JobFile::where(["JobID" => $JobID])->pluck("FilePath");
            Excel::load($filePath, function ($writer) {
                $writer->setFileName(basename($writer->getFileName()));
            })->download();
    }
    public function downloaded_excel_file_download($id,$JobID){
            $filePath = JobFile::where(["JobID" => $JobID])->pluck("FilePath");
            Excel::load($filePath, function ($writer) {
                $writer->setFileName(basename($writer->getFileName()));
            })->download();
    }
    public function download_sample_excel_file(){
            $filePath =  public_path() .'/uploads/sample_upload/VendorRateUploadSample.csv';
            download_file($filePath);

    }
    public function bulk_clear_rate($id){
        $data = Input::all();

        $rules = array('VendorRateID' => 'required', 'Trunk' => 'required',);

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return Redirect::back()->withInput(Input::all())->withErrors($validator);
        }
        $VendorIDs = explode(",", $data['VendorRateID']);
        if (VendorRate::whereIn('VendorRateID',$VendorIDs)->delete()) {
            return Response::json(array("status" => "success", "message" => "Vendor Rates Successfully Deleted."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Rates."));
        }

    }
    public function bulk_update($id){
        $data = Input::all();

        $rules = array(
            'VendorRateID' => 'required',
            'EffectiveDate' => 'required',
            'Rate' => 'required|numeric',
            'Interval1' => 'required|numeric',
            'IntervalN' => 'required|numeric',
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return Redirect::back()->withInput(Input::all())->withErrors($validator);
        }
        $username = User::get_user_full_name();
        $VendorIDs = explode(",", $data['VendorRateID']);
        //'Interval1'=> $data['Interval1'],'IntervalN'=> $data['IntervalN'],
        if (VendorRate::whereIn('VendorRateID',$VendorIDs)->update(['Interval1'=> $data['Interval1'],'IntervalN'=> $data['IntervalN'], 'updated_by'=>$username,'ConnectionFee'=>floatval($data['ConnectionFee']), 'EffectiveDate' => $data['EffectiveDate'],'Rate'=>$data['Rate']])) {
            return Response::json(array("status" => "success", "message" => "Vendor Rates Successfully Updated."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Rates."));
        }

    }
    public function  bulk_update_new($id){
        $data = Input::all();
        $company_id = User::get_companyID();
        $data['vendor'] = $id;
        $rules = array('EffectiveDate' => 'required','Rate' => 'required|numeric', 'Trunk' => 'required|numeric');//'Interval1' => 'required|numeric','IntervalN' => 'required|numeric'
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['Country'] = $data['Country'] == 'All'?'NULL':$data['Country'];
        $data['Code'] =  $data['Code'] == ''?'NULL':"'".$data['Code']."'";
        $data['Description'] = $data['Description'] == ''?'NULL':"'".$data['Description']."'";
        $username = User::get_user_full_name();
        //Inserting Job Log
        $results = DB::statement("call prc_VendorBulkRateUpdate ('".$data['vendor']."',".$data['Trunk'].",".$data['Code'].",".$data['Description'].",".$data['Country'].",".$company_id.",".$data['Rate'].",'".$data['EffectiveDate']."','".floatval($data['ConnectionFee'])."',".intval($data['Interval1']).",".intval($data['IntervalN']).",'".$username."','".$data['Effective']."',1)");

        if ($results) {
            return Response::json(array("status" => "success", "message" => "Vendors Rate Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Rate."));
        }
    }
    public function clear_all_vendorrate($id){
        $data = Input::all();
        $rules = array('Trunk' => 'required',);

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $company_id = User::get_companyID();
        if(!empty($data['criteria'])){
            $criteria = json_decode($data['criteria'],true);
            $criteria['Country'] = $criteria['Country'] == 'All'?'NULL':$criteria['Country'];
            $criteria['Code'] =  $criteria['Code'] == ''?'NULL':"'".$criteria['Code']."'";
            $criteria['Description'] = $criteria['Description'] == ''?'NULL':"'".$criteria['Description']."'";
            $username = User::get_user_full_name();
            $results = DB::statement("call prc_VendorBulkRateUpdate( '".$id."',".$data['Trunk'].",".$criteria['Code'].",".$criteria['Description'].",".$criteria['Country'].",".$company_id.",'0','','0','','','".$username."','".$criteria['Effective']."',2); ");
            if ($results) {
                return Response::json(array("status" => "success", "message" => "Vendor Rates Successfully Deleted."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Rate."));
            }
        }
    }
    public function settings($id){
            $codedecklist = BaseCodeDeck::getCodedeckIDList();
            $trunks = Trunk::getTrunkCacheObj();
            $vendor_trunks = VendorTrunk::getTrunksByTrunkAsKey($id);
            $Account = Account::find($id);
            $companygateway = CompanyGateway::getCompanyGatewayIdList();
            unset($companygateway['']);
            return View::make('vendorrates.setting', compact('id','codedecklist','Account','trunks','vendor_trunks','companygateway'));
    }
    public function  update_settings($id){
            $post_data = Input::all();
            if (!empty($post_data)) {

                $companyID = User::get_companyID();
                foreach ($post_data['VendorTrunk'] as $trunk => $data) {

                    if (isset($data['Status']) && $data['Status'] == 1) {

                        $VendorTrunk = new VendorTrunk();

                        $data['AccountID'] = $id;
                        $data['CompanyID'] = $companyID;
                        $data['TrunkID'] = $trunk;

                        //$data['Status'] = $data['Status'];
                        $data['CreatedBy'] = User::get_user_full_name();
                        $data['ModifiedBy'] = !empty($data['VendorTrunkID']) ? User::get_user_full_name() : '';
                        $data['UseInBilling'] = isset($data['UseInBilling']) ? 1 : 0;

                        $rules = array("CodeDeckId"=>"required","AccountID" => "required", "CompanyID" => "required", "TrunkID" => "required","Status" => "required");
                        $validator = Validator::make($data, $rules);

                        if ($validator->fails()) {
                            return Redirect::back()->withInput(Input::all())->withErrors($validator);
                        }
                        if( isset($data['CompanyGatewayID']) && is_array($data['CompanyGatewayID'])){
                            $data['CompanyGatewayIDs'] = implode(',', $data['CompanyGatewayID']);
                            unset($data['CompanyGatewayID']);
                        }else{
                            $data['CompanyGatewayIDs'] = '';
                        }


                        if (isset($data['VendorTrunkID']) && $data['VendorTrunkID'] > 0) {
                            $VendorTrunkID = $data['VendorTrunkID'];
                            unset($data['VendorTrunkID']);
                            VendorTrunk::find($VendorTrunkID)->update($data);
                        } else {
                            unset($data['VendorTrunkID']);
                            if ($VendorTrunk->insert($data)) {

                            } else {
                                return Redirect::back()->with('error_message', "Problem Creating Vendor Trunk for " . $trunk . " Trunk");
                            }
                        }
                    } else {

                        if (isset($data['VendorTrunkID']) && $data['VendorTrunkID'] > 0) {
                            $VendorTrunkID = $data['VendorTrunkID'];
                            VendorTrunk::find($VendorTrunkID)->update(['Status' => 0]);
                        }
                    }
                }
                //Success
                return Redirect::back()->with('success_message', "Vendor Trunk Saved");
            }
    }
    public function  delete_vendorrates($id){
            $data = Input::all();
            $rules = array('Trunkid' => 'required');
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if(isset($data['action']) && $data['action']=='check_count'){
                return VendorRate::where(["AccountID" =>$id ,'TrunkID'=>$data['Trunkid']])->count();
            }

            if (VendorRate::where(["AccountID" =>$id ,'TrunkID'=>$data['Trunkid']])->count()  == 0 || VendorRate::where(["AccountID" =>$id ,'TrunkID'=>$data['Trunkid']])->delete()) {
                return Response::json(array("status" => "success", "message" => "Vendor Rates Deleted Successfully"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Rates."));
            }
    }
    public function vendor_preference($id){
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            $countries = $this->countries;
            return View::make('vendorrates.preference', compact('id', 'trunks', 'trunk_keys', 'countries','Account'));
    }
    public function search_ajax_datagrid_preference($id,$type) {


        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['Country']=$data['Country']!= 'All'?$data['Country']:'null';
        $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';


        $columns = array('RateID','Code','Preference','Description','VendorPreferenceID');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetVendorPreference (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Vendor Preference.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Vendor Preference.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

        }
        $query .=',0)';
        return DataTableSql::of($query)->make();

    }
    public function bulk_update_preference($id){
        $data = Input::all();
        if(empty($data['Preference'])){
            $data['Preference']='0';
         //   return Response::json(array("status" => "failed", "message" => "Please Insert Preference."));
        }
        $company_id = User::get_companyID();
        $username = User::get_user_full_name();

        if($data['Action'] == 'bulk'){
            $data['Country'] = $data['Country']!= 'All'?$data['Country']:'null';
            $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
            $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';
            /*$exceldatas  = DB::select("call prc_GetVendorPreference( ".$company_id.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",0,0,'','',2)");
            $exceldatas = json_decode(json_encode($exceldatas),true);
            $RateID='';
            foreach($exceldatas as $exceldata){
                $RateID.= $exceldata['RateID'].',';
            }
            $RateID = rtrim($RateID,',');*/
            try{
                DB::statement("call prc_VendorPreferenceUpdateBySelectedRateId (".$company_id.",'".$id."','',".$data['Trunk'].",".$data['Preference'].",'".$username."',".$data['Country'].",".$data['Code'].",".$data['Description'].",1)");
                return Response::json(array("status" => "success", "message" => "Vendor Preference Updated Successfully"));
            }catch ( Exception $ex ){
                return Response::json(array("status" => "failed", "message" => "Error Updating Vendor Preference."));
            }
        }else{
            $RateID = $data['RateID'];
            if(!empty($RateID)){
                try{
                    DB::statement("call prc_VendorPreferenceUpdateBySelectedRateId (".$company_id.",'".$id."','".$RateID."',".$data['Trunk'].",".$data['Preference'].",'".$username."',null,null,null,0)");
                    return Response::json(array("status" => "success", "message" => "Vendor Preference Updated Successfully"));
                }catch ( Exception $ex ){
                    return Response::json(array("status" => "failed", "message" => "Error Updating Vendor Preference."));
                }

            }else{

                return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Preference."));
            }
        }

    }

    function ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TempFileName'];
            $grid = getFileContent($file_name, $data);
            $grid['filename'] = $data['TemplateFile'];
            $grid['tempfilename'] = $data['TempFileName'];
            if ($data['uploadtemplate'] > 0) {
                $VendorFileUploadTemplate = VendorFileUploadTemplate::find($data['uploadtemplate']);
                $grid['VendorFileUploadTemplate'] = json_decode(json_encode($VendorFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['VendorFileUploadTemplate']['Options'] = array();
            $grid['VendorFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['VendorFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function storeTemplate($id) {
        $data = Input::all();
        $CompanyID = User::get_companyID();

        $rules['selection.Code'] = 'required';
        $rules['selection.Description'] = 'required';
        $rules['selection.Rate'] = 'required';
        //$rules['selection.EffectiveDate'] = 'required';
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['codedeckid'] = VendorTrunk::where(["AccountID" => $id, 'TrunkID' => $data['Trunk']])->pluck("CodeDeckId");
        if (!isset($data['codedeckid']) || empty($data['codedeckid'])) {
            return json_encode(["status" => "failed", "message" => 'Please Update a Codedeck in Setting']);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = getenv('TEMP_PATH').'/' ;

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD']);
 
        $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload vendor rates file."));
        }
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            if (isset($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                $template = VendorFileUploadTemplate::find($data['uploadtemplate']);
                $template->update($save);
            } else {
                $template = VendorFileUploadTemplate::create($save);
            }
            $data['uploadtemplate'] = $template->VendorFileUploadTemplateID;
        }
        $save = array();
        $option["option"]=  $data['option'];
        $option["selection"] = $data['selection'];
        $save['Options'] = json_encode($option);
        $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path'] = $fullPath;
        $save["AccountID"] = $id;
        $save['codedeckid'] = $data['codedeckid'];
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        $save['Trunk'] = $data['Trunk'];
        $save['checkbox_replace_all'] = $data['checkbox_replace_all'];
        $save['checkbox_rates_with_effected_from'] = $data['checkbox_rates_with_effected_from'];
        $save['checkbox_add_new_codes_to_code_decks'] = $data['checkbox_add_new_codes_to_code_decks'];
            //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("VU", $save);
            if ($result['status'] != "success") {
                DB::rollback();
                return json_encode(["status" => "failed", "message" => $result['message']]);
            }
            DB::commit();
            @unlink($temp_path . $file_name);
            return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
        } catch (Exception $ex) {
            DB::rollback();
            return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }

    public function check_upload() {
        try {
            $data = Input::all();
            if (!isset($data['Trunk']) || empty($data['Trunk'])) {
                return json_encode(["status" => "failed", "message" => 'Please Select a Trunk']);
            } else if (Input::hasFile('excel')) {
                $upload_path = getenv('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array($ext, array("csv", "xls", "xlsx"))) {
                    $file_name_without_ext = GUID::generate();
                    $file_name = $file_name_without_ext . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else if (isset($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if (!empty($file_name)) {

                if ($data['uploadtemplate'] > 0) {
                    $VendorFileUploadTemplate = VendorFileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($VendorFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }
                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;//$upload_path.'\\'.'temp.'.$ext;
                $grid['filename'] = $file_name;
                if (!empty($VendorFileUploadTemplate)) {
                    $grid['VendorFileUploadTemplate'] = json_decode(json_encode($VendorFileUploadTemplate), true);
                    $grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        }catch(Exception $ex) {
		Log::info($ex);
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }
    /** search grid used for vendor*/
    public  function search_vendor_grid($id){
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $UserID = 0;
        $SelectedCodes = 0;
        $isCountry = 1;
        $countries = 0;
        $isall = 0;
        $criteria =0;

        if (User::is('AccountManager')) {
            $UserID = User::get_userID();
        }
        if (isset($data['OwnerFilter']) && $data['OwnerFilter'] != 0) {
            $UserID = $data['OwnerFilter'];
        }

        //block by contry
        if(isset($data['block_by']) && $data['block_by']=='country')
        {
            $isCountry=1;
            if(in_array(0,explode(',',$data['Country']))){
                $isall = 1;
            }elseif(!empty($data['Country'])){
                $isall = 0;
                $countries = $data['Country'];
            }

        }

        //block by code
        if(isset($data['block_by']) && $data['block_by']=='code')
        {
            $isCountry=0;
            $isall = 0;
            // by critearia
            if(!empty($data['criteria']) && $data['criteria']==1){
                if(!empty($data['Code']) || !empty($data['Country'])){
                    if(!empty($data['Code'])){
                        $criteria = 1;
                        $SelectedCodes = $data['Code'];
                    }else{
                        $criteria = 2;
                        if(!empty($data['Country'])){
                            $isall = 0;
                            $countries = $data['Country'];
                        }
                    }
                }else{
                    $criteria = 3;
                }

            }elseif(!empty($data['SelectedCodes'])){
                //by code
                $SelectedCodes = $data['SelectedCodes'];
                $criteria = 0;
            }

        }

        if($data['action'] == 'block'){
            $data['action'] = 0;
        }else{
            $data['action'] = 1;
        }

        $query = "call prc_GetBlockUnblockVendor (".$CompanyID.",".$UserID.",".$data['Trunk'].",'".$countries."','".$SelectedCodes."',".$isCountry.",".$data['action'].",".$isall.",".$criteria.")";
        //$accounts = DataTableSql::of($query)->getProcResult(array('AccountID','AccountName'));
        //return $accounts->make();
        return DataTableSql::of($query)->make();
    }

    public function vendordownloadtype($id,$type){
        if($type=='Vos 3.2'){
            $downloadtype = '<option value="">Select a Type</option><option value="txt">TXT</option>';
        }else{
            $downloadtype = '<option value="">Select a Type</option><option value="xlsx">EXCEL</option><option value="csv">CSV</option>';
        }
        return $downloadtype;
    }
}
